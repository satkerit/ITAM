<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_company_profile_edit_screen_renders_successfully(): void
    {
        $response = $this->get(route('web.company.edit'));

        $response->assertStatus(200);
        $response->assertSee('Profil Perusahaan');
        $response->assertSee('PT Solusi Teknologi Nusantara');
        $response->assertSee('Identitas Visual & Media', false);
    }

    public function test_can_update_company_profile_basic_info_and_contacts(): void
    {
        $response = $this->put(route('web.company.update'), [
            'name' => 'PT Nusantara Asset Digital',
            'tagline' => 'Next Generation IT Lifecycle',
            'description' => 'Kantor transformasi digital modern.',
            'email' => 'halo@nusantara-asset.id',
            'phone' => '+62 21 8899 0011',
            'mobile' => '+62 811 2233 4455',
            'website' => 'https://nusantara-asset.id',
            'tax_id' => '09.876.543.2-123.000',
            'address' => 'Cyber 2 Tower Lt. 28, Jl. HR Rasuna Said',
            'city' => 'Jakarta Selatan',
            'postal_code' => '12950',
            'country' => 'Indonesia',
        ]);

        $response->assertRedirect(route('web.company.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('company_profiles', [
            'id' => 1,
            'name' => 'PT Nusantara Asset Digital',
            'email' => 'halo@nusantara-asset.id',
            'city' => 'Jakarta Selatan',
            'mobile' => '+62 811 2233 4455',
        ]);
    }

    public function test_can_upload_logo_and_office_photo(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('company-logo.png', 400, 400);
        $office = UploadedFile::fake()->image('office-building.jpg', 1200, 800);

        $response = $this->put(route('web.company.update'), [
            'name' => 'PT Solusi Teknologi Nusantara',
            'logo' => $logo,
            'office_photo' => $office,
        ]);

        $response->assertRedirect(route('web.company.edit'));

        $profile = CompanyProfile::current();
        $this->assertNotNull($profile->logo_path);
        $this->assertNotNull($profile->office_photo_path);

        Storage::disk('public')->assertExists($profile->logo_path);
        Storage::disk('public')->assertExists($profile->office_photo_path);
    }

    public function test_unauthenticated_user_cannot_access_or_update_company_profile(): void
    {
        auth()->logout();

        $this->get(route('web.company.edit'))->assertRedirect('/login');
        $this->put(route('web.company.update'), ['name' => 'Hacked Inc'])->assertRedirect('/login');
    }
}
