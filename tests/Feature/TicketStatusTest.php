<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\TicketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_technician_can_update_ticket_status(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $response = $this->actingAs($technician)
            ->patch("/tickets/{$ticket->id}/status", ['status' => TicketStatus::InProgress->value]);

        $response->assertRedirect("/tickets/{$ticket->id}");
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::InProgress->value,
        ]);
    }

    public function test_admin_can_update_ticket_status(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->actingAs($admin)
            ->patch("/tickets/{$ticket->id}/status", ['status' => TicketStatus::Resolved->value])
            ->assertRedirect("/tickets/{$ticket->id}");

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Resolved->value,
        ]);
    }

    public function test_regular_user_cannot_update_ticket_status(): void
    {
        // Requester itu sendiri pun tidak boleh mengubah status.
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch("/tickets/{$ticket->id}/status", ['status' => TicketStatus::Resolved->value])
            ->assertForbidden();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Open->value,
        ]);
    }

    public function test_status_update_requires_valid_status_value(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->actingAs($technician)
            ->patch("/tickets/{$ticket->id}/status", ['status' => 'done'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Open->value,
        ]);
    }

    public function test_status_update_requires_status_field(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($technician)
            ->patch("/tickets/{$ticket->id}/status", [])
            ->assertSessionHasErrors('status');
    }

    public function test_closed_ticket_is_final_even_for_technician(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Closed]);

        $this->actingAs($technician)
            ->patch("/tickets/{$ticket->id}/status", ['status' => TicketStatus::InProgress->value])
            ->assertForbidden();

        // Status tetap closed.
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Closed->value,
        ]);
    }

    public function test_guest_cannot_update_status(): void
    {
        $ticket = Ticket::factory()->create();

        $this->patch("/tickets/{$ticket->id}/status", ['status' => 'open'])
            ->assertRedirect('/login');
    }
}
