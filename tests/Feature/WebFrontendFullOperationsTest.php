<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\AssignmentStatus;
use App\Enums\DisposalType;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\MaintenanceLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebFrontendFullOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Location $loc1;

    protected Location $loc2;

    protected AssetCategory $category;

    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loc1 = Location::create(['name' => 'Kantor Pusat Jakarta', 'address' => 'Jl. Sudirman No 1']);
        $this->loc2 = Location::create(['name' => 'Kantor Cabang Surabaya', 'address' => 'Jl. Basuki Rahmat No 2']);
        $this->category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $this->user = User::factory()->create(['location_id' => $this->loc1->id]);
        $this->actingAs($this->user);

        $this->asset = Asset::create([
            'location_id' => $this->loc1->id,
            'category_id' => $this->category->id,
            'name' => 'MacBook Pro M3 Max',
            'asset_code' => 'LPT-2026-OPTEST',
            'status' => AssetStatus::Available,
            'purchase_date' => '2026-01-10',
            'purchase_cost' => 35000000,
        ]);
    }

    public function test_can_create_new_asset_via_web_form(): void
    {
        $response = $this->post(route('web.assets.store'), [
            'name' => 'Dell Latitude 7440',
            'asset_code' => 'LPT-NEW-001',
            'category_id' => $this->category->id,
            'location_id' => $this->loc1->id,
            'serial_number' => 'SN-DELL-999',
            'purchase_date' => '2026-02-01',
            'purchase_cost' => 20000000,
            'status' => 'available',
            'notes' => 'Unit baru pengadaan divisi IT',
        ]);

        $newAsset = Asset::where('asset_code', 'LPT-NEW-001')->first();
        $this->assertNotNull($newAsset);
        $response->assertRedirect(route('web.assets.show', $newAsset));
        $this->assertDatabaseHas('assets', ['asset_code' => 'LPT-NEW-001']);
    }

    public function test_can_process_asset_checkout_and_checkin_via_web(): void
    {
        // 1. Checkout (Assign)
        $response = $this->post(route('web.assignments.store'), [
            'asset_id' => $this->asset->id,
            'user_id' => $this->user->id,
            'assigned_date' => '2026-03-01',
            'notes' => 'Peminjaman untuk keperluan dinas',
        ]);

        $response->assertRedirect(route('web.assignments.index'));
        $this->assertDatabaseHas('assets', [
            'id' => $this->asset->id,
            'status' => AssetStatus::Deployed->value,
        ]);

        $assignment = AssetAssignment::where('asset_id', $this->asset->id)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals(AssignmentStatus::Active, $assignment->status);

        // 2. Checkin (Return)
        $responseReturn = $this->post(route('web.assignments.checkin', $assignment), [
            'returned_date' => '2026-03-10',
            'notes' => 'Dikembalikan dalam kondisi prima',
        ]);

        $responseReturn->assertRedirect(route('web.assignments.index'));
        $this->assertDatabaseHas('assets', [
            'id' => $this->asset->id,
            'status' => AssetStatus::Available->value,
        ]);
        $assignment->refresh();
        $this->assertEquals(AssignmentStatus::Returned, $assignment->status);
    }

    public function test_can_process_asset_mutation_via_web(): void
    {
        $response = $this->post(route('web.mutations.store'), [
            'asset_id' => $this->asset->id,
            'to_location_id' => $this->loc2->id,
            'mutation_date' => '2026-03-02',
            'reason' => 'Transfer unit untuk cabang Surabaya',
            'notes' => 'Dikirim via ekspedisi resi JNE-9988',
        ]);

        $response->assertRedirect(route('web.mutations.index'));
        $this->assertDatabaseHas('assets', [
            'id' => $this->asset->id,
            'location_id' => $this->loc2->id,
        ]);
        $this->assertDatabaseHas('asset_mutations', [
            'asset_id' => $this->asset->id,
            'from_location_id' => $this->loc1->id,
            'to_location_id' => $this->loc2->id,
        ]);
    }

    public function test_can_log_and_complete_maintenance_via_web(): void
    {
        // 1. Log maintenance
        $response = $this->post(route('web.maintenances.store'), [
            'asset_id' => $this->asset->id,
            'service_date' => '2026-03-03',
            'issue_description' => 'Keyboard tombol spasial macet',
            'cost' => 450000,
            'vendor' => 'Mitra Care Apple',
        ]);

        $response->assertRedirect(route('web.maintenances.index'));
        $this->assertDatabaseHas('assets', [
            'id' => $this->asset->id,
            'status' => AssetStatus::Maintenance->value,
        ]);

        $maintenance = MaintenanceLog::where('asset_id', $this->asset->id)->first();
        $this->assertNotNull($maintenance);
        $this->assertEquals(MaintenanceStatus::InProgress, $maintenance->status);

        // 2. Complete maintenance
        $responseComplete = $this->post(route('web.maintenances.complete', $maintenance), [
            'completion_date' => '2026-03-05',
            'action_taken' => 'Ganti topcase keyboard baru',
            'cost' => 500000,
        ]);

        $responseComplete->assertRedirect(route('web.maintenances.index'));
        $this->assertDatabaseHas('assets', [
            'id' => $this->asset->id,
            'status' => AssetStatus::Available->value,
        ]);
        $maintenance->refresh();
        $this->assertEquals(MaintenanceStatus::Completed, $maintenance->status);
    }

    public function test_can_process_asset_disposal_via_web(): void
    {
        $response = $this->post(route('web.disposals.store'), [
            'asset_id' => $this->asset->id,
            'disposal_type' => 'sale',
            'disposal_date' => '2026-03-05',
            'sale_amount' => 18000000,
            'reason' => 'Unit lelang karyawan',
            'notes' => 'BAST-LELANG-001',
        ]);

        $response->assertRedirect(route('web.disposals.index'));
        $this->assertDatabaseHas('assets', [
            'id' => $this->asset->id,
            'status' => AssetStatus::Sold->value,
        ]);
        $this->assertDatabaseHas('asset_disposals', [
            'asset_id' => $this->asset->id,
            'disposal_type' => DisposalType::Sale->value,
            'value' => 18000000,
        ]);
    }
}
