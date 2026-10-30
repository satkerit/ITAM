<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AssetImportDepreciationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
    }

    public function test_category_csv_import_and_template(): void
    {
        $csv = "name,code,description\nLaptop,LPT,Perangkat laptop\nServer,SVR,Unit server\n";
        $file = UploadedFile::fake()->createWithContent('kategori.csv', $csv);

        $response = $this->post(route('web.categories.import'), ['file' => $file]);
        $response->assertRedirect(route('web.categories.index'));
        $this->assertDatabaseHas('asset_categories', ['code' => 'LPT']);
        $this->assertDatabaseHas('asset_categories', ['code' => 'SVR']);

        $this->get(route('web.categories.template'))->assertStatus(200);
    }

    public function test_asset_csv_import_matches_category_and_location(): void
    {
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT', 'schema_fields' => []]);
        Location::create(['name' => 'Kantor Pusat', 'address' => 'Jakarta']);

        $csv = "asset_code,name,category_code,location_name,serial_number,purchase_date,purchase_cost,useful_life_months,status,notes\n".
            "LPT-2026-001,Dell Latitude,LPT,Kantor Pusat,SN1,2026-01-15,15000000,48,available,Unit baru\n".
            "BAD-001,Unknown,ZZZ,Kantor Pusat,,,???,,available,\n";
        $file = UploadedFile::fake()->createWithContent('aset.csv', $csv);

        $response = $this->post(route('web.assets.import'), ['file' => $file]);
        $response->assertRedirect(route('web.assets.index'));

        $this->assertDatabaseHas('assets', [
            'asset_code' => 'LPT-2026-001',
            'category_id' => $category->id,
            'useful_life_months' => 48,
        ]);
        $this->assertDatabaseMissing('assets', ['asset_code' => 'BAD-001']);

        $this->get(route('web.assets.template'))->assertStatus(200);
    }

    public function test_depreciation_accessors(): void
    {
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT', 'schema_fields' => []]);
        $location = Location::create(['name' => 'Pusat', 'address' => 'Jakarta']);

        $asset = Asset::create([
            'asset_code' => 'LPT-001',
            'name' => 'Dell',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'purchase_date' => now()->subMonths(12)->toDateString(),
            'purchase_cost' => 12000000,
            'useful_life_months' => 24,
            'status' => 'available',
        ]);

        $this->assertEquals(500000, $asset->monthly_depreciation);
        $this->assertEquals(6000000, $asset->accumulated_depreciation);
        $this->assertEquals(6000000, $asset->book_value);

        // Tanpa lama susut -> null
        $asset->useful_life_months = 0;
        $this->assertNull($asset->monthly_depreciation);
    }
}
