<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\TicketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_dashboard_shows_only_own_ticket_stats(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        // 2 ticket milik user, 1 milik orang lain (tidak boleh dihitung).
        Ticket::factory()->count(2)->create(['user_id' => $user->id]);
        Ticket::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Total Ticket Saya');
        $response->assertDontSee('Total Semua Ticket');
        // Total hanya menghitung ticket milik user (2), bukan semua (3).
        $response->assertViewHas('total', 2);
        // User biasa tidak melihat daftar ticket belum ditugaskan.
        $response->assertDontSee('Ticket Belum Ditugaskan');
    }

    public function test_technician_dashboard_shows_global_stats_and_unassigned_queue(): void
    {
        $technician = User::factory()->technician()->create();

        Ticket::factory()->create([
            'status' => TicketStatus::Open,
            'assigned_to' => null,
        ]);
        Ticket::factory()->create([
            'status' => TicketStatus::Open,
            'assigned_to' => $technician->id,
        ]);

        $response = $this->actingAs($technician)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Total Semua Ticket');
        $response->assertSee('Ticket Belum Ditugaskan');
        // Semua ticket (termasuk milik orang lain) dihitung: 2.
        $response->assertViewHas('total', 2);
        // Hanya 1 ticket yang belum ditugaskan.
        $response->assertSee('(1)', false);
        $response->assertSee('Ambil');
    }

    public function test_admin_sees_assign_form_on_unassigned_ticket(): void
    {
        $admin = User::factory()->admin()->create();
        $technician = User::factory()->technician()->create(['name' => 'Budi Teknisi']);

        Ticket::factory()->create(['assigned_to' => null]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Tugaskan');
        $response->assertSee('Budi Teknisi');
    }

    public function test_unassigned_queue_shows_empty_state_when_all_assigned(): void
    {
        $technician = User::factory()->technician()->create();

        Ticket::factory()->create(['assigned_to' => $technician->id]);

        $this->actingAs($technician)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Semua ticket sudah ada penanggung jawab');
    }
}
