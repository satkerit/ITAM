<?php

namespace Tests\Feature;

use App\Enums\UserRole;
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
    }
}
