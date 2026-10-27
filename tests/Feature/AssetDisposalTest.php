<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\DisposalType;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetDisposalTest extends TestCase
{
    use RefreshDatabase;

    private Location $location;

    private AssetCategory $category;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->location = Location::create(['name' => 'HQ Central', 'address' => 'Jl. Thamrin']);
        $this->category = AssetCategory::create(['name' => 'Server', 'code' => 'SRV']);
        $this->manager = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_dispose_asset_via_sale_and_update_to_sold_status(): void
    {
        $asset = Asset::create([
            'asset_code' => 'SRV-001',
            'name' => 'Dell PowerEdge R740',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'status' => AssetStatus::Available,
        ]);

        $response = $this->postJson('/api/v1/disposals', [
            'asset_id' => $asset->id,
            'disposal_type' => DisposalType::Sale->value,
            'date' => '2026-10-25',
            'value' => 15000000.00,
            'buyer_name' => 'PT Daur Ulang Hardware',
            'authorized_by' => $this->manager->id,
            'notes' => 'Dijual lelang server lama penggantian cluster baru',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.disposal_type', 'sale')
            ->assertJsonPath('data.value', '15000000.00');

        $this->assertEquals(AssetStatus::Sold, $asset->fresh()->status);
    }

    public function test_cannot_dispose_asset_while_deployed(): void
    {
        $asset = Asset::create([
            'asset_code' => 'SRV-002',
            'name' => 'Backup Server',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'status' => AssetStatus::Deployed,
        ]);

        $response = $this->postJson('/api/v1/disposals', [
            'asset_id' => $asset->id,
            'disposal_type' => DisposalType::Disposal->value,
            'date' => '2026-10-25',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Asset cannot be disposed while still deployed to an employee. Return it first.');
    }

    public function test_cannot_dispose_already_disposed_asset(): void
    {
        $asset = Asset::create([
            'asset_code' => 'SRV-003',
            'name' => 'Old Server Rack',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'status' => AssetStatus::Disposed,
        ]);

        $response = $this->postJson('/api/v1/disposals', [
            'asset_id' => $asset->id,
            'disposal_type' => DisposalType::Disposal->value,
            'date' => '2026-10-25',
        ]);

        $response->assertStatus(422);
    }
}
