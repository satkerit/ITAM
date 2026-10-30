<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebFrontendFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_web_frontend_pages_render_successfully(): void
    {
        $location = Location::create(['name' => 'Kantor Pusat', 'address' => 'Jl. Sudirman']);
        $category = AssetCategory::create(['name' => 'Laptop', 'code' => 'LPT']);
        $user = User::factory()->technician()->create(['location_id' => $location->id]);

        $this->actingAs($user);

        $asset = Asset::create([
            'location_id' => $location->id,
            'category_id' => $category->id,
            'name' => 'MacBook Pro M3',
            'asset_code' => 'LPT-2026-TEST',
            'status' => 'available',
            'purchase_date' => '2026-01-15',
            'purchase_cost' => 25000000,
        ]);

        // Dashboard
        $response = $this->get('/app/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Executive Dashboard & Overview');

        // Asset Catalog
        $response = $this->get('/app/assets');
        $response->assertStatus(200);
        $response->assertSee('Katalog Perangkat & Hardware');
        $response->assertSee('MacBook Pro M3');

        // Asset Create Form
        $response = $this->get('/app/assets/create');
        $response->assertStatus(200);
        $response->assertSee('Registrasi Aset IT Baru');

        // Asset Detail Show
        $response = $this->get('/app/assets/'.$asset->id);
        $response->assertStatus(200);
        $response->assertSee('Detail Informasi Perangkat');
        $response->assertSee('LPT-2026-TEST');

        // Asset Scan Page
        $response = $this->get('/app/assets/scan');
        $response->assertStatus(200);
        $response->assertSee('Audit Fisik & Pemindai QR Code');

        // Operations: Assignments
        $response = $this->get('/app/assignments');
        $response->assertStatus(200);
        $response->assertSee('Kelola sirkulasi penugasan perangkat');

        // Operations: Mutations
        $response = $this->get('/app/mutations');
        $response->assertStatus(200);
        $response->assertSee('Mutasi Antar Lokasi');

        // Operations: Maintenances
        $response = $this->get('/app/maintenances');
        $response->assertStatus(200);
        $response->assertSee('Pantau perbaikan hardware');

        // Operations: Disposals
        $response = $this->get('/app/disposals');
        $response->assertStatus(200);
        $response->assertSee('Terminal akhir siklus hidup aset');

        // Reports: Stock and lifecycle
        $response = $this->get('/app/reports');
        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi sebaran stok fisik kantor cabang');
    }
}
