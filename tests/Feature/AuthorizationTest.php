<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_access_asset_write_routes(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get(route('web.assets.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('web.assets.import'))->assertForbidden();
        $this->actingAs($staff)->post(route('web.assignments.store'))->assertForbidden();
        $this->actingAs($staff)->post(route('web.disposals.store'))->assertForbidden();
    }

    public function test_technician_can_access_asset_write_routes(): void
    {
        $technician = User::factory()->technician()->create();

        $this->actingAs($technician)->get(route('web.assets.create'))->assertOk();
    }

    public function test_staff_cannot_access_master_data_and_user_management(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get(route('web.users.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('web.locations.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('web.categories.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('web.company.edit'))->assertForbidden();
    }

    public function test_staff_can_read_asset_catalog_dashboard_and_reports(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get(route('web.dashboard'))->assertOk();
        $this->actingAs($staff)->get(route('web.assets.index'))->assertOk();
        $this->actingAs($staff)->get(route('web.assets.scan'))->assertOk();
        $this->actingAs($staff)->get(route('web.reports.index'))->assertOk();
    }

    public function test_staff_has_read_only_api_access(): void
    {
        Sanctum::actingAs(User::factory()->staff()->create());

        $this->getJson('/api/v1/assets')->assertOk();

        $this->postJson('/api/v1/assets', [])->assertForbidden();
        $this->postJson('/api/v1/locations', [])->assertForbidden();
        $this->postJson('/api/v1/assignments/assign', [])->assertForbidden();
        $this->postJson('/api/v1/disposals', [])->assertForbidden();
    }

    public function test_technician_cannot_manage_master_data_via_api(): void
    {
        Sanctum::actingAs(User::factory()->technician()->create());

        $this->postJson('/api/v1/locations', [])->assertForbidden();
        $this->postJson('/api/v1/asset-categories', [])->assertForbidden();
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete(route('web.users.destroy', $admin));

        $response->assertRedirect(route('web.users.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put(route('web.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => UserRole::Staff->value,
        ]);

        $response->assertRedirect(route('web.users.index'));
        $response->assertSessionHas('error');
        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
    }

    public function test_admin_can_update_role_of_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($admin)->put(route('web.users.update', $staff), [
            'name' => $staff->name,
            'email' => $staff->email,
            'role' => UserRole::Technician->value,
        ]);

        $response->assertRedirect(route('web.users.index'));
        $response->assertSessionHas('success');
        $this->assertSame(UserRole::Technician, $staff->fresh()->role);
    }

    public function test_asset_code_rejects_unsafe_characters(): void
    {
        Storage::fake('public');

        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/v1/assets', [
            'asset_code' => '<script>alert(1)</script>',
            'name' => 'Laptop Uji',
            'category_id' => 1,
            'location_id' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('asset_code');
    }

    public function test_security_headers_are_applied_to_responses(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $policy = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($policy);
        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("frame-ancestors 'self'", $policy);
        $this->assertMatchesRegularExpression("/script-src [^;]*'nonce-[A-Za-z0-9+\\/=]+'/", $policy);
    }

    public function test_staff_only_sees_assets_in_their_location(): void
    {
        Storage::fake('public');

        $locationA = Location::create(['name' => 'Kantor A', 'address' => 'Jl. A No. 1']);
        $locationB = Location::create(['name' => 'Kantor B', 'address' => 'Jl. B No. 2']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);

        $ownAsset = Asset::create([
            'asset_code' => 'LPT-A-001',
            'name' => 'Laptop Cabang A',
            'category_id' => $category->id,
            'location_id' => $locationA->id,
            'status' => AssetStatus::Available,
        ]);
        $otherAsset = Asset::create([
            'asset_code' => 'LPT-B-002',
            'name' => 'Laptop Cabang B',
            'category_id' => $category->id,
            'location_id' => $locationB->id,
            'status' => AssetStatus::Available,
        ]);

        $staff = User::factory()->staff()->create(['location_id' => $locationA->id]);

        $this->actingAs($staff)->get(route('web.assets.index'))
            ->assertOk()
            ->assertSee($ownAsset->asset_code)
            ->assertDontSee($otherAsset->asset_code);

        $this->actingAs($staff)->get(route('web.assets.show', $otherAsset))->assertForbidden();

        $this->actingAs($staff)->get(route('web.assets.scan', ['code' => $otherAsset->asset_code]))
            ->assertOk()
            ->assertDontSee($otherAsset->name);
    }

    public function test_admin_sees_assets_from_all_locations(): void
    {
        Storage::fake('public');

        $locationA = Location::create(['name' => 'Kantor A', 'address' => 'Jl. A No. 1']);
        $locationB = Location::create(['name' => 'Kantor B', 'address' => 'Jl. B No. 2']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);

        Asset::create([
            'asset_code' => 'LPT-A-001',
            'name' => 'Laptop Cabang A',
            'category_id' => $category->id,
            'location_id' => $locationA->id,
            'status' => AssetStatus::Available,
        ]);
        Asset::create([
            'asset_code' => 'LPT-B-002',
            'name' => 'Laptop Cabang B',
            'category_id' => $category->id,
            'location_id' => $locationB->id,
            'status' => AssetStatus::Available,
        ]);

        $admin = User::factory()->admin()->create(['location_id' => $locationA->id]);

        $this->actingAs($admin)->get(route('web.assets.index'))
            ->assertOk()
            ->assertSee('LPT-A-001')
            ->assertSee('LPT-B-002');
    }

    public function test_api_asset_reads_are_scoped_to_user_location(): void
    {
        Storage::fake('public');

        $locationA = Location::create(['name' => 'Kantor A', 'address' => 'Jl. A No. 1']);
        $locationB = Location::create(['name' => 'Kantor B', 'address' => 'Jl. B No. 2']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);

        Asset::create([
            'asset_code' => 'LPT-A-001',
            'name' => 'Laptop Cabang A',
            'category_id' => $category->id,
            'location_id' => $locationA->id,
            'status' => AssetStatus::Available,
        ]);
        $otherAsset = Asset::create([
            'asset_code' => 'LPT-B-002',
            'name' => 'Laptop Cabang B',
            'category_id' => $category->id,
            'location_id' => $locationB->id,
            'status' => AssetStatus::Available,
        ]);

        Sanctum::actingAs(User::factory()->staff()->create(['location_id' => $locationA->id]));

        $list = $this->getJson('/api/v1/assets')->assertOk();
        $codes = collect($list->json('data'))->pluck('asset_code')->all();
        $this->assertSame(['LPT-A-001'], $codes);

        $this->getJson("/api/v1/assets/{$otherAsset->id}")->assertForbidden();
        $this->getJson('/api/v1/assets/scan?code='.$otherAsset->asset_code)->assertNotFound();
    }

    public function test_operational_lists_are_scoped_to_user_location(): void
    {
        [$locationA, $locationB, $assetA, $assetB] = $this->seedTwoBranchAssets();

        $owner = User::factory()->staff()->create();
        $admin = User::factory()->admin()->create();

        AssetAssignment::create([
            'asset_id' => $assetA->id,
            'user_id' => $owner->id,
            'assigned_date' => now()->toDateString(),
            'status' => 'active',
            'assigned_by' => $admin->id,
        ]);
        AssetAssignment::create([
            'asset_id' => $assetB->id,
            'user_id' => $owner->id,
            'assigned_date' => now()->toDateString(),
            'status' => 'active',
            'assigned_by' => $admin->id,
        ]);

        $staff = User::factory()->staff()->create(['location_id' => $locationA->id]);

        $this->actingAs($staff)->get(route('web.assignments.index'))
            ->assertOk()
            ->assertSee($assetA->asset_code)
            ->assertDontSee($assetB->asset_code);

        Sanctum::actingAs($staff);

        $assignments = collect($this->getJson('/api/v1/assignments')->assertOk()->json('data'))
            ->pluck('asset.asset_code')
            ->all();
        $this->assertSame([$assetA->asset_code], $assignments);

        $stock = collect($this->getJson('/api/v1/reports/stock-by-location')->assertOk()->json('data'))
            ->pluck('assets_count', 'name')
            ->all();
        $this->assertSame(1, (int) $stock[$locationA->name]);
        $this->assertSame(0, (int) $stock[$locationB->name]);
    }

    public function test_technician_cannot_operate_assets_outside_their_location(): void
    {
        [$locationA, $locationB, $assetA, $assetB] = $this->seedTwoBranchAssets();

        $technician = User::factory()->technician()->create(['location_id' => $locationA->id]);
        $borrower = User::factory()->staff()->create(['location_id' => $locationA->id]);

        Sanctum::actingAs($technician);

        $this->postJson('/api/v1/assignments/assign', [
            'asset_id' => $assetB->id,
            'user_id' => $borrower->id,
            'assigned_date' => now()->toDateString(),
        ])->assertForbidden();

        $this->postJson('/api/v1/mutations', [
            'asset_id' => $assetB->id,
            'to_location_id' => $locationA->id,
            'mutation_date' => now()->toDateString(),
            'reason' => 'Uji lintas cabang',
        ])->assertForbidden();

        $this->postJson('/api/v1/maintenances', [
            'asset_id' => $assetB->id,
            'maintenance_date' => now()->toDateString(),
            'description' => 'Uji lintas cabang',
        ])->assertForbidden();

        $this->postJson('/api/v1/disposals', [
            'asset_id' => $assetB->id,
            'disposal_type' => 'disposal',
            'date' => now()->toDateString(),
        ])->assertForbidden();

        $this->actingAs($technician)->post(route('web.assignments.store'), [
            'asset_id' => $assetB->id,
            'user_id' => $borrower->id,
            'assigned_date' => now()->toDateString(),
        ])->assertForbidden();

        $this->actingAs($technician)->post(route('web.assignments.store'), [
            'asset_id' => $assetA->id,
            'user_id' => $borrower->id,
            'assigned_date' => now()->toDateString(),
        ])->assertRedirect(route('web.assignments.index'));
    }

    public function test_api_asset_history_requires_location_access(): void
    {
        [, , $assetA, $assetB] = $this->seedTwoBranchAssets();

        $staffA = User::factory()->staff()->create(['location_id' => $assetA->location_id]);

        Sanctum::actingAs($staffA);

        $this->getJson("/api/v1/reports/assets/{$assetA->id}/history")->assertOk();
        $this->getJson("/api/v1/reports/assets/{$assetB->id}/history")->assertForbidden();
    }

    /**
     * @return array{0: Location, 1: Location, 2: Asset, 3: Asset}
     */
    private function seedTwoBranchAssets(): array
    {
        Storage::fake('public');

        $locationA = Location::create(['name' => 'Kantor A', 'address' => 'Jl. A No. 1']);
        $locationB = Location::create(['name' => 'Kantor B', 'address' => 'Jl. B No. 2']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);

        $assetA = Asset::create([
            'asset_code' => 'LPT-A-001',
            'name' => 'Laptop Cabang A',
            'category_id' => $category->id,
            'location_id' => $locationA->id,
            'status' => AssetStatus::Available,
        ]);
        $assetB = Asset::create([
            'asset_code' => 'LPT-B-002',
            'name' => 'Laptop Cabang B',
            'category_id' => $category->id,
            'location_id' => $locationB->id,
            'status' => AssetStatus::Available,
        ]);

        return [$locationA, $locationB, $assetA, $assetB];
    }
}
