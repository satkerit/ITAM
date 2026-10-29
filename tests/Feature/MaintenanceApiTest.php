<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MaintenanceApiTest extends TestCase
{
    use RefreshDatabase;

    private Location $location;

    private AssetCategory $category;

    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->location = Location::create(['name' => 'HQ Service Center', 'address' => 'Gatot Subroto']);
        $this->category = AssetCategory::create(['name' => 'Printer', 'code' => 'PRT']);
        $this->technician = User::factory()->create(['role' => 'technician']);

        Sanctum::actingAs($this->technician);
    }

    public function test_can_create_maintenance_and_complete_with_cost_and_status_sync(): void
    {
        $asset = Asset::create([
            'asset_code' => 'PRT-001',
            'name' => 'Epson L3210',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'status' => AssetStatus::Available,
        ]);

        $storeResponse = $this->postJson('/api/v1/maintenances', [
            'asset_id' => $asset->id,
            'maintenance_date' => '2026-10-10',
            'description' => 'Ganti printhead dan pembersihan tangki tinta',
            'cost' => 150000,
            'vendor_name' => 'Service Center Epson Resmi',
            'performed_by' => $this->technician->id,
        ]);

        $storeResponse->assertCreated()
            ->assertJsonPath('data.status', 'in_progress');

        $this->assertEquals(AssetStatus::Maintenance, $asset->fresh()->status);

        $maintenanceId = $storeResponse->json('data.id');

        $completeResponse = $this->postJson("/api/v1/maintenances/{$maintenanceId}/complete", [
            'completed_date' => '2026-10-12',
            'cost' => 250000,
            'notes' => 'Tambahan pergantian roller paper',
        ]);

        $completeResponse->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.cost', '250000.00');

        $this->assertEquals(AssetStatus::Available, $asset->fresh()->status);

        $this->assertDatabaseHas('maintenance_logs', [
            'id' => $maintenanceId,
            'status' => MaintenanceStatus::Completed->value,
            'cost' => 250000.00,
        ]);
    }

    public function test_cannot_service_sold_or_disposed_asset(): void
    {
        $asset = Asset::create([
            'asset_code' => 'PRT-002',
            'name' => 'Broken Printer',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'status' => AssetStatus::Sold,
        ]);

        $response = $this->postJson('/api/v1/maintenances', [
            'asset_id' => $asset->id,
            'maintenance_date' => '2026-10-10',
            'description' => 'Servis aset terjual',
        ]);

        $response->assertStatus(422);
    }
}
