<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        // 5 percobaan pertama lolos middleware throttle (respons biasa:
        // redirect sukses ATAU 302 dengan error kredensial).
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'nobody@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        // Percobaan ke-6 kena throttle: 429 Too Many Requests.
        $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_valid_login_still_works_within_rate_limit(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_registration_is_rate_limited(): void
    {
        // 10 percobaan register per menit (throttle:10,1). Pakai payload
        // yang gagal validasi biar user tetap guest — percobaan tetap
        // dihitung throttle karena middleware jalan sebelum controller.
        for ($i = 0; $i < 10; $i++) {
            $this->post('/register', [
                'name' => 'Spammer '.$i,
                'email' => 'spam@example.com',
                // password sengaja tidak diisi -> validasi gagal, 302 back
            ])->assertStatus(302);
        }

        $this->post('/register', [
            'name' => 'User overflow',
            'email' => 'overflow@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(429);
    }
}
