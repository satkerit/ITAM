<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebMasterDataManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create();
        $this->actingAs($admin);
    }

    public function test_location_crud_and_referential_integrity(): void
    {
        $response = $this->get(route('web.locations.index'));
        $response->assertStatus(200);
        $response->assertSee('Lokasi');

        // Store
        $response = $this->post(route('web.locations.store'), [
            'name' => 'Cabang Surabaya',
            'address' => 'Jl. Pemuda No. 45',
            'description' => 'Kantor Operasional Regional Timur',
        ]);
        $response->assertRedirect(route('web.locations.index'));
        $this->assertDatabaseHas('locations', ['name' => 'Cabang Surabaya']);

        $location = Location::where('name', 'Cabang Surabaya')->first();

        // Update
        $response = $this->put(route('web.locations.update', $location), [
            'name' => 'Cabang Surabaya Pusat',
            'address' => 'Jl. Basuki Rahmat',
            'description' => 'Kantor Baru',
        ]);
        $response->assertRedirect(route('web.locations.index'));
        $this->assertDatabaseHas('locations', ['name' => 'Cabang Surabaya Pusat']);

        // Referential integrity protection test: user linked
        $user = User::factory()->create(['location_id' => $location->id]);
        $response = $this->delete(route('web.locations.destroy', $location));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('locations', ['id' => $location->id]);

        $user->delete();

        // Delete successful
        $response = $this->delete(route('web.locations.destroy', $location));
        $response->assertRedirect(route('web.locations.index'));
        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_category_crud_and_referential_integrity(): void
    {
        $response = $this->get(route('web.categories.index'));
        $response->assertStatus(200);
        $response->assertSee('Kategori');

        // Store
        $response = $this->post(route('web.categories.store'), [
            'name' => 'Tablet PC',
            'code' => 'TAB',
            'description' => 'Perangkat iPad dan Android tablet',
        ]);
        $response->assertRedirect(route('web.categories.index'));
        $this->assertDatabaseHas('asset_categories', ['code' => 'TAB']);

        $category = AssetCategory::where('code', 'TAB')->first();

        // Update
        $response = $this->put(route('web.categories.update', $category), [
            'name' => 'Mobile Tablet',
            'code' => 'TAB',
            'description' => 'Perangkat tablet operasional lapangan',
        ]);
        $response->assertRedirect(route('web.categories.index'));
        $this->assertDatabaseHas('asset_categories', ['name' => 'Mobile Tablet']);

        // Referential integrity protection test: asset linked
        $loc = Location::create(['name' => 'Pusat', 'address' => 'Jakarta']);
        $asset = Asset::create([
            'location_id' => $loc->id,
            'category_id' => $category->id,
            'name' => 'iPad Pro 11',
            'asset_code' => 'TAB-001',
            'status' => 'available',
            'purchase_date' => '2026-01-01',
            'purchase_cost' => 15000000,
        ]);

        $response = $this->delete(route('web.categories.destroy', $category));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('asset_categories', ['id' => $category->id]);

        $asset->delete();

        // Delete successful
        $response = $this->delete(route('web.categories.destroy', $category));
        $response->assertRedirect(route('web.categories.index'));
        $this->assertDatabaseMissing('asset_categories', ['id' => $category->id]);
    }

    public function test_user_rbac_crud_and_filtering(): void
    {
        $location = Location::create(['name' => 'Headquarter', 'address' => 'Jl. Sudirman 100']);

        $response = $this->get(route('web.users.index'));
        $response->assertStatus(200);
        $response->assertSee('Pengguna');

        // Store user with RBAC role
        $response = $this->post(route('web.users.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@itam.local',
            'password' => 'secret12345',
            'role' => UserRole::Technician->value,
            'location_id' => $location->id,
        ]);
        $response->assertRedirect(route('web.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'budi.santoso@itam.local',
            'role' => UserRole::Technician->value,
            'location_id' => $location->id,
        ]);

        $user = User::where('email', 'budi.santoso@itam.local')->first();

        // Update user
        $response = $this->put(route('web.users.update', $user), [
            'name' => 'Budi Santoso MT',
            'email' => 'budi.santoso@itam.local',
            'role' => UserRole::Admin->value,
            'location_id' => $location->id,
        ]);
        $response->assertRedirect(route('web.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Budi Santoso MT',
            'role' => UserRole::Admin->value,
        ]);

        // Filter search test
        $response = $this->get(route('web.users.index', ['role' => 'admin', 'search' => 'Budi']));
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso MT');

        // Delete user
        $response = $this->delete(route('web.users.destroy', $user));
        $response->assertRedirect(route('web.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
