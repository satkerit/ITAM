<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\AssignmentStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetQrPrintTest extends TestCase
{
    use RefreshDatabase;

    private function makeAsset(string $code, Location $location, AssetCategory $category): Asset
    {
        return Asset::create([
            'asset_code' => $code,
            'name' => 'Aset '.$code,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'status' => AssetStatus::Available,
        ]);
    }

    public function test_prints_only_selected_assets(): void
    {
        $location = Location::create(['name' => 'Kantor Pusat', 'address' => 'Jl. Sudirman']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $selected = $this->makeAsset('LPT-001', $location, $category);
        $this->makeAsset('LPT-002', $location, $category);

        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('web.assets.print', ['ids' => [$selected->id]]));

        $response->assertOk()
            ->assertSee('LPT-001')
            ->assertDontSee('LPT-002')
            ->assertSee('<svg', false);
    }

    public function test_prints_all_assets_matching_filters_without_ids(): void
    {
        $hq = Location::create(['name' => 'Kantor Pusat', 'address' => 'Jl. Sudirman']);
        $branch = Location::create(['name' => 'Kantor Bali', 'address' => 'Jl. Sunset']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $this->makeAsset('LPT-HQ', $hq, $category);
        $this->makeAsset('LPT-BALI', $branch, $category);

        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('web.assets.print', ['location_id' => $hq->id]));

        $response->assertOk()
            ->assertSee('LPT-HQ')
            ->assertDontSee('LPT-BALI');
    }

    public function test_qr_code_is_generated_from_asset_code_on_save(): void
    {
        Storage::fake('public');

        $location = Location::create(['name' => 'Kantor Pusat', 'address' => 'Jl. Sudirman']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $asset = $this->makeAsset('LPT-001', $location, $category);

        $this->assertSame('qrcodes/LPT-001.svg', $asset->qr_code_path);
        Storage::disk('public')->assertExists('qrcodes/LPT-001.svg');

        $asset->update(['asset_code' => 'LPT-999']);

        $this->assertSame('qrcodes/LPT-999.svg', $asset->fresh()->qr_code_path);
        Storage::disk('public')->assertExists('qrcodes/LPT-999.svg');
        Storage::disk('public')->assertMissing('qrcodes/LPT-001.svg');
    }

    public function test_asset_missing_qr_path_is_backfilled_on_save(): void
    {
        Storage::fake('public');

        $location = Location::create(['name' => 'Kantor Pusat', 'address' => 'Jl. Sudirman']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $asset = $this->makeAsset('LPT-001', $location, $category);

        // Simulasikan data lama yang belum punya QR (bypass event model).
        Asset::whereKey($asset->id)->update(['qr_code_path' => null]);
        Storage::disk('public')->delete('qrcodes/LPT-001.svg');

        $asset->refresh()->save();

        $this->assertSame('qrcodes/LPT-001.svg', $asset->fresh()->qr_code_path);
        Storage::disk('public')->assertExists('qrcodes/LPT-001.svg');
    }

    public function test_qr_file_is_regenerated_when_missing_on_disk(): void
    {
        Storage::fake('public');

        $location = Location::create(['name' => 'Kantor Pusat', 'address' => 'Jl. Sudirman']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $asset = $this->makeAsset('LPT-001', $location, $category);

        Storage::disk('public')->delete('qrcodes/LPT-001.svg');
        $this->assertSame('qrcodes/LPT-001.svg', $asset->qr_code_path);

        $asset->save();

        Storage::disk('public')->assertExists('qrcodes/LPT-001.svg');
    }

    public function test_asset_detail_page_shows_qr_image_with_enum_assignment_status(): void
    {
        Storage::fake('public');

        $location = Location::create(['name' => 'Kantor Pusat', 'address' => 'Jl. Sudirman']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $asset = $this->makeAsset('LPT-001', $location, $category);
        $borrower = User::factory()->create();

        AssetAssignment::create([
            'asset_id' => $asset->id,
            'user_id' => $borrower->id,
            'assigned_date' => now()->toDateString(),
            'status' => AssignmentStatus::Active,
            'assigned_by' => $borrower->id,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('web.assets.show', $asset))
            ->assertOk()
            ->assertSee('<svg', false)
            ->assertDontSee('<img src', false)
            ->assertSee('Active');
    }

    public function test_scan_result_shows_detailed_asset_information(): void
    {
        $location = Location::create(['name' => 'Kantor Pusat', 'address' => 'Jl. Sudirman']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $asset = $this->makeAsset('LPT-001', $location, $category);
        $asset->update([
            'serial_number' => 'SN-XYZ-998877',
            'purchase_store' => 'Toko Komputer Maju',
            'purchase_cost' => 12500000,
            'purchase_date' => '2025-03-15',
            'specifications' => ['processor' => 'Intel i5', 'ram' => '16GB'],
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('web.assets.scan', ['code' => 'LPT-001']))
            ->assertOk()
            ->assertSee('SN-XYZ-998877')
            ->assertSee('Toko Komputer Maju')
            ->assertSee('Rp 12.500.000')
            ->assertSee('15 Mar 2025')
            ->assertSee('Intel i5')
            ->assertSee('16GB');
    }

    public function test_scan_rejects_malicious_code_input(): void
    {
        $location = Location::create(['name' => 'Kantor Pusat', 'address' => 'Jl. Sudirman']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $this->makeAsset('LPT-001', $location, $category);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('web.assets.scan', ['code' => "<script>alert(1)</script>' OR 1=1--"]))
            ->assertSessionHasErrors('code')
            ->assertDontSee('<script>alert(1)</script>', false);

        $this->actingAs($admin)
            ->getJson('/api/v1/assets/scan?'.http_build_query(['code' => "LPT-001' OR '1'='1"]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_label_size_option_is_applied(): void
    {
        $location = Location::create(['name' => 'Kantor Pusat', 'address' => 'Jl. Sudirman']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $asset = $this->makeAsset('LPT-001', $location, $category);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('web.assets.print', ['ids' => [$asset->id], 'size' => 'large']))
            ->assertOk()
            ->assertSee('labels size-large', false);
    }
}
