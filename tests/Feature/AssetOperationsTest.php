<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\AssignmentStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssetOperationsTest extends TestCase
{
    use RefreshDatabase;

    private Location $locationA;

    private Location $locationB;

    private AssetCategory $category;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locationA = Location::create(['name' => 'Kantor Jakarta', 'address' => 'Jl. Sudirman']);
        $this->locationB = Location::create(['name' => 'Kantor Bandung', 'address' => 'Jl. Asia Afrika']);
        $this->category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $this->user = User::factory()->admin()->create(['location_id' => $this->locationA->id]);

        Sanctum::actingAs($this->user);
    }

    public function test_can_assign_and_return_asset_with_status_transitions(): void
    {
        $asset = Asset::create([
            'asset_code' => 'LPT-001',
            'name' => 'ThinkPad T14',
            'category_id' => $this->category->id,
            'location_id' => $this->locationA->id,
            'status' => AssetStatus::Available,
        ]);

        $assignResponse = $this->postJson('/api/v1/assignments/assign', [
            'asset_id' => $asset->id,
            'user_id' => $this->user->id,
            'assigned_date' => '2026-10-01',
            'notes' => 'Peminjaman untuk development tim frontend',
        ]);

        $assignResponse->assertCreated()
            ->assertJsonPath('data.status', 'active');

        $this->assertEquals(AssetStatus::Deployed, $asset->fresh()->status);

        $assignmentId = $assignResponse->json('data.id');

        $returnResponse = $this->postJson("/api/v1/assignments/{$assignmentId}/return", [
            'returned_date' => '2026-10-25',
            'notes' => 'Dikembalikan dalam kondisi baik',
        ]);

        $returnResponse->assertOk()
            ->assertJsonPath('data.status', 'returned');

        $this->assertEquals(AssetStatus::Available, $asset->fresh()->status);
    }

    public function test_database_rejects_second_active_assignment_for_same_asset(): void
    {
        $asset = Asset::create([
            'asset_code' => 'LPT-002',
            'name' => 'ThinkPad X1',
            'category_id' => $this->category->id,
            'location_id' => $this->locationA->id,
            'status' => AssetStatus::Available,
        ]);

        $attributes = [
            'asset_id' => $asset->id,
            'user_id' => $this->user->id,
            'assigned_date' => '2026-10-01',
            'status' => AssignmentStatus::Active,
        ];

        AssetAssignment::create($attributes);

        $this->expectException(QueryException::class);

        AssetAssignment::create($attributes);
    }

    public function test_cannot_assign_asset_when_not_available(): void
    {
        $asset = Asset::create([
            'asset_code' => 'LPT-002',
            'name' => 'MacBook Pro',
            'category_id' => $this->category->id,
            'location_id' => $this->locationA->id,
            'status' => AssetStatus::Maintenance,
        ]);

        $response = $this->postJson('/api/v1/assignments/assign', [
            'asset_id' => $asset->id,
            'user_id' => $this->user->id,
            'assigned_date' => '2026-10-01',
        ]);

        $response->assertStatus(422);
    }

    public function test_can_mutate_asset_between_locations_with_db_transaction(): void
    {
        $asset = Asset::create([
            'asset_code' => 'LPT-003',
            'name' => 'Dell Latitude',
            'category_id' => $this->category->id,
            'location_id' => $this->locationA->id,
            'status' => AssetStatus::Available,
        ]);

        $mutationResponse = $this->postJson('/api/v1/mutations', [
            'asset_id' => $asset->id,
            'to_location_id' => $this->locationB->id,
            'mutation_date' => '2026-10-20',
            'reason' => 'Kebutuhan workstation cabang Bandung',
            'processed_by' => $this->user->id,
        ]);

        $mutationResponse->assertCreated()
            ->assertJsonPath('data.from_location_id', $this->locationA->id)
            ->assertJsonPath('data.to_location_id', $this->locationB->id);

        $this->assertEquals($this->locationB->id, $asset->fresh()->location_id);
        $this->assertDatabaseHas('asset_mutations', [
            'asset_id' => $asset->id,
            'from_location_id' => $this->locationA->id,
            'to_location_id' => $this->locationB->id,
        ]);
    }

    public function test_cannot_mutate_asset_with_end_of_life_status(): void
    {
        $asset = Asset::create([
            'asset_code' => 'LPT-004',
            'name' => 'Scrapped ThinkPad',
            'category_id' => $this->category->id,
            'location_id' => $this->locationA->id,
            'status' => AssetStatus::Disposed,
        ]);

        $response = $this->postJson('/api/v1/mutations', [
            'asset_id' => $asset->id,
            'to_location_id' => $this->locationB->id,
            'mutation_date' => '2026-10-20',
            'reason' => 'Coba mutasi',
            'processed_by' => $this->user->id,
        ]);

        $response->assertStatus(422);
    }
}
