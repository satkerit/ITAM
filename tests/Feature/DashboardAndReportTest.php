<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
use App\Enums\DisposalType;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDisposal;
use App\Models\Location;
use App\Models\MaintenanceLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardAndReportTest extends TestCase
{
    use RefreshDatabase;

    private Location $locJakarta;

    private Location $locSurabaya;

    private AssetCategory $catLaptop;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locJakarta = Location::create(['name' => 'Jakarta Hub', 'address' => 'Sudirman']);
        $this->locSurabaya = Location::create(['name' => 'Surabaya Hub', 'address' => 'Pemuda']);
        $this->catLaptop = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $this->user = User::factory()->admin()->create();

        Sanctum::actingAs($this->user);

        $asset1 = Asset::create([
            'asset_code' => 'LPT-JKT-001',
            'name' => 'MacBook Pro',
            'category_id' => $this->catLaptop->id,
            'location_id' => $this->locJakarta->id,
            'status' => AssetStatus::Available,
            'purchase_cost' => 25000000,
        ]);

        $asset2 = Asset::create([
            'asset_code' => 'LPT-SBY-001',
            'name' => 'ThinkPad X1',
            'category_id' => $this->catLaptop->id,
            'location_id' => $this->locSurabaya->id,
            'status' => AssetStatus::Maintenance,
            'purchase_cost' => 20000000,
        ]);

        MaintenanceLog::create([
            'asset_id' => $asset2->id,
            'maintenance_date' => '2026-10-10',
            'description' => 'Servis baterai',
            'cost' => 1200000,
        ]);

        $assetSold = Asset::create([
            'asset_code' => 'LPT-OLD-001',
            'name' => 'Old Laptop',
            'category_id' => $this->catLaptop->id,
            'location_id' => $this->locJakarta->id,
            'status' => AssetStatus::Sold,
            'purchase_cost' => 10000000,
        ]);

        AssetDisposal::create([
            'asset_id' => $assetSold->id,
            'disposal_type' => DisposalType::Sale,
            'date' => '2026-10-15',
            'value' => 3500000,
            'authorized_by' => $this->user->id,
        ]);
    }

    public function test_can_fetch_dashboard_summary(): void
    {
        $response = $this->getJson('/api/v1/dashboard/summary');

        $response->assertOk()
            ->assertJsonPath('summary.total_assets', 3)
            ->assertJsonPath('summary.total_asset_value', 55000000)
            ->assertJsonPath('summary.total_maintenance_cost', 1200000)
            ->assertJsonStructure([
                'summary' => ['total_assets', 'total_asset_value', 'total_maintenance_cost', 'status_breakdown'],
                'by_location',
                'by_category',
            ]);
    }

    public function test_can_fetch_stock_by_location(): void
    {
        $response = $this->getJson('/api/v1/reports/stock-by-location');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'assets_count', 'available_count', 'deployed_count', 'maintenance_count', 'assets_sum_purchase_cost'],
                ],
            ]);
    }

    public function test_can_fetch_disposals_report(): void
    {
        $response = $this->getJson('/api/v1/reports/disposals?disposal_type=sale');

        $response->assertOk()
            ->assertJsonPath('total_disposal_value', 3500000)
            ->assertJsonCount(1, 'disposals.data');
    }
}
