<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_dashboard_and_is_redirected_to_login(): void
    {
        $response = $this->get('/app/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_screen_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('IT Asset Management');
        $response->assertSee('Masuk ke Dashboard');
    }

    public function test_user_can_authenticate_using_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@itam.local',
            'password' => bcrypt('secret1234'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@itam.local',
            'password' => 'secret1234',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/app/dashboard');
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@itam.local',
            'password' => bcrypt('secret1234'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@itam.local',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_login_attempts_are_rate_limited_against_brute_force(): void
    {
        User::factory()->create([
            'email' => 'target@itam.local',
            'password' => bcrypt('password'),
        ]);

        RateLimiter::clear('target@itam.local|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'target@itam.local',
                'password' => 'wrong-password-'.$i,
            ]);
        }

        $this->assertGuest();

        // 6th attempt hits rate limit
        $response = $this->post('/login', [
            'email' => 'target@itam.local',
            'password' => 'wrong-password-6',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_visiting_login_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/app/dashboard');
    }
}
