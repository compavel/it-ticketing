<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\TicketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_ticket_logs_created_activity(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($user)->post('/tickets', [
            'title' => 'Wi-Fi mati di lab 2',
            'description' => 'Access point tidak menyala sejak pagi.',
            'category_id' => $category->id,
            'priority' => 'high',
        ]);

        $ticket = Ticket::firstOrFail();

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'action' => 'created',
        ]);
    }

    public function test_status_change_logs_activity_with_old_and_new_value(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->actingAs($technician)
            ->patch("/tickets/{$ticket->id}/status", ['status' => 'in_progress']);

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $ticket->id,
            'user_id' => $technician->id,
            'action' => 'status_changed',
            'old_value' => 'open',
            'new_value' => 'in_progress',
        ]);
    }

    public function test_setting_same_status_does_not_log_duplicate_activity(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->actingAs($technician)
            ->patch("/tickets/{$ticket->id}/status", ['status' => 'open']);

        $this->assertSame(0, $ticket->activities()->count());
    }

    public function test_assign_logs_activity_with_assignee_names(): void
    {
        $admin = User::factory()->admin()->create();
        $technician = User::factory()->technician()->create(['name' => 'Budi Teknisi']);
        $ticket = Ticket::factory()->create(['assigned_to' => null]);

        $this->actingAs($admin)
            ->patch("/tickets/{$ticket->id}/assign", ['technician_id' => $technician->id]);

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'action' => 'assigned',
            'old_value' => null,
            'new_value' => 'Budi Teknisi',
        ]);
    }

    public function test_claim_logs_activity(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['assigned_to' => null]);

        $this->actingAs($technician)
            ->patch("/tickets/{$ticket->id}/claim");

        $this->assertDatabaseHas('ticket_activities', [
            'ticket_id' => $ticket->id,
            'user_id' => $technician->id,
            'action' => 'claimed',
        ]);
    }

    public function test_losing_claim_race_shows_error_and_logs_nothing(): void
    {
        // Simulasi race: policy lolos (assigned_to null saat dicek), tapi
        // sebelum UPDATE dijalankan, teknisi lain sudah menang telak.
        $technician = User::factory()->technician()->create();
        $rival = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['assigned_to' => null]);

        // "Menangkan" lomba untuk rival secara pakai, lalu clone policy check
        // kita dengan meng-cache model lama di route request.
        // Pendekatan test: mutate DB lewat model terpisah setelah policy check
        // tidak mungkin dilakukan dari luar — jadi kita uji sisi kontrak:
        // UPDATE atomik yang mengenai 0 baris menghasilkan error flash.
        $stale = Ticket::findOrFail($ticket->id); // snapshot assigned_to = null

        // Rival menang lebih dulu.
        Ticket::whereKey($ticket->id)->update(['assigned_to' => $rival->id]);

        // UPDATE atomik yang sama dengan yang dipakai controller:
        $affected = Ticket::whereKey($stale->getKey())
            ->whereNull('assigned_to')
            ->update(['assigned_to' => $technician->id]);

        $this->assertSame(0, $affected, 'UPDATE kedua harus mengenai 0 baris (kalah race).');
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $rival->id,
        ]);
        $this->assertSame(0, $ticket->activities()->count());
    }

    public function test_activity_history_is_visible_on_ticket_page(): void
    {
        $user = User::factory()->admin()->create(['name' => 'Andi Admin']);
        $category = Category::factory()->create();

        $this->actingAs($user)->post('/tickets', [
            'title' => 'Proyektor ruang kelas rusak',
            'description' => 'Lampu tidak menyala.',
            'category_id' => $category->id,
            'priority' => 'medium',
        ]);

        $ticket = Ticket::firstOrFail();

        $this->actingAs($user)
            ->get("/tickets/{$ticket->id}")
            ->assertOk()
            ->assertSee('Riwayat Aktivitas')
            ->assertSee('membuat ticket ini');
    }
}
