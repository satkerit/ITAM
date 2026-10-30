<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssetApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->admin()->create());
    }

    public function test_can_create_asset_with_json_spec_and_generate_qr(): void
    {
        Storage::fake('public');

        $location = Location::create([
            'name' => 'Headquarters',
            'address' => 'Sudirman St.',
        ]);

        $category = AssetCategory::create([
            'name' => 'Laptop',
            'code' => 'LPT',
        ]);

        $payload = [
            'asset_code' => 'LPT-2026-001',
            'name' => 'MacBook Pro M3 16-inch',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'status' => 'available',
            'serial_number' => 'C02G1234ABCD',
            'purchase_date' => '2026-01-15',
            'purchase_cost' => 34500000.00,
            'specifications' => [
                'cpu' => 'Apple M3 Pro',
                'ram' => '36GB',
                'ssd' => '1TB',
            ],
            'notes' => 'Aset divisi engineering',
        ];

        $response = $this->postJson('/api/v1/assets', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.asset_code', 'LPT-2026-001')
            ->assertJsonPath('data.status', 'available')
            ->assertJsonPath('data.specifications.ram', '36GB');

        $this->assertDatabaseHas('assets', [
            'asset_code' => 'LPT-2026-001',
            'name' => 'MacBook Pro M3 16-inch',
        ]);

        Storage::disk('public')->assertExists('qrcodes/LPT-2026-001.svg');
    }

    public function test_can_lookup_asset_via_scan_endpoint(): void
    {
        $location = Location::create([
            'name' => 'Headquarters',
            'address' => 'Sudirman St.',
        ]);

        $category = AssetCategory::create([
            'name' => 'Laptop',
            'code' => 'LPT',
        ]);

        $asset = Asset::create([
            'asset_code' => 'LPT-2026-002',
            'name' => 'Dell XPS 15',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'status' => AssetStatus::Available,
        ]);

        $scanResponse = $this->getJson("/api/v1/assets/scan?code={$asset->asset_code}");
        $scanResponse->assertOk()
            ->assertJsonPath('data.asset_code', 'LPT-2026-002')
            ->assertJsonPath('data.name', 'Dell XPS 15');

        $notFoundResponse = $this->getJson('/api/v1/assets/scan?code=NONEXISTENT');
        $notFoundResponse->assertNotFound();
    }

    public function test_can_update_and_filter_assets(): void
    {
        $location1 = Location::create(['name' => 'HQ', 'address' => 'Addr 1']);
        $location2 = Location::create(['name' => 'Branch', 'address' => 'Addr 2']);
        $category = AssetCategory::create(['name' => 'Monitor', 'code' => 'MON']);

        $asset1 = Asset::create([
            'asset_code' => 'MON-001',
            'name' => 'Dell 27 Inch 4K',
            'category_id' => $category->id,
            'location_id' => $location1->id,
            'status' => AssetStatus::Available,
        ]);

        $asset2 = Asset::create([
            'asset_code' => 'MON-002',
            'name' => 'LG UltraWide 34',
            'category_id' => $category->id,
            'location_id' => $location2->id,
            'status' => AssetStatus::Deployed,
        ]);

        $updateResponse = $this->putJson("/api/v1/assets/{$asset1->id}", [
            'status' => 'maintenance',
        ]);
        $updateResponse->assertOk()
            ->assertJsonPath('data.status', 'maintenance');

        $filterResponse = $this->getJson('/api/v1/assets?status=maintenance');
        $filterResponse->assertOk()
            ->assertJsonCount(1, 'data');

        $searchResponse = $this->getJson('/api/v1/assets?search=UltraWide');
        $searchResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.asset_code', 'MON-002');
    }
}
