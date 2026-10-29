<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\TicketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketAssignmentTest extends TestCase
{
    use RefreshDatabase;

    // ---------- Claim (teknisi ambil sendiri) ----------

    public function test_technician_can_claim_unassigned_ticket(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['assigned_to' => null]);

        $this->actingAs($technician)
            ->patch("/tickets/{$ticket->id}/claim")
            ->assertRedirect("/tickets/{$ticket->id}");

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $technician->id,
        ]);
    }

    public function test_technician_cannot_claim_already_assigned_ticket(): void
    {
        $technician = User::factory()->technician()->create();
        $other = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['assigned_to' => $other->id]);

        $this->actingAs($technician)
            ->patch("/tickets/{$ticket->id}/claim")
            ->assertForbidden();

        // Ticket tetap milik teknisi lain.
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $other->id,
        ]);
    }

    public function test_user_cannot_claim_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['assigned_to' => null]);

        $this->actingAs($user)
            ->patch("/tickets/{$ticket->id}/claim")
            ->assertForbidden();
    }

    public function test_closed_ticket_cannot_be_claimed(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Closed,
            'assigned_to' => null,
        ]);

        $this->actingAs($technician)
            ->patch("/tickets/{$ticket->id}/claim")
            ->assertForbidden();
    }

    public function test_guest_cannot_claim(): void
    {
        $ticket = Ticket::factory()->create();

        $this->patch("/tickets/{$ticket->id}/claim")->assertRedirect('/login');
    }

    // ---------- Assign (admin tugaskan ke teknisi) ----------

    public function test_admin_can_assign_ticket_to_technician(): void
    {
        $admin = User::factory()->admin()->create();
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['assigned_to' => null]);

        $this->actingAs($admin)
            ->patch("/tickets/{$ticket->id}/assign", ['technician_id' => $technician->id])
            ->assertRedirect("/tickets/{$ticket->id}");

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $technician->id,
        ]);
    }

    public function test_admin_can_reassign_ticket(): void
    {
        $admin = User::factory()->admin()->create();
        $first = User::factory()->technician()->create();
        $second = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['assigned_to' => $first->id]);

        $this->actingAs($admin)
            ->patch("/tickets/{$ticket->id}/assign", ['technician_id' => $second->id])
            ->assertRedirect("/tickets/{$ticket->id}");

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $second->id,
        ]);
    }

    public function test_technician_cannot_assign_ticket_to_other(): void
    {
        // Teknisi boleh claim (ambil sendiri), tapi TIDAK boleh menugaskan orang.
        $technician = User::factory()->technician()->create();
        $other = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['assigned_to' => null]);

        $this->actingAs($technician)
            ->patch("/tickets/{$ticket->id}/assign", ['technician_id' => $other->id])
            ->assertForbidden();
    }

    public function test_assign_requires_technician_role_target(): void
    {
        // Admin salah pilih target: user biasa tidak valid jadi penanggung jawab.
        $admin = User::factory()->admin()->create();
        $regular = User::factory()->create();
        $ticket = Ticket::factory()->create(['assigned_to' => null]);

        $this->actingAs($admin)
            ->patch("/tickets/{$ticket->id}/assign", ['technician_id' => $regular->id])
            ->assertSessionHasErrors('technician_id');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => null,
        ]);
    }

    public function test_assign_requires_technician_id(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($admin)
            ->patch("/tickets/{$ticket->id}/assign", [])
            ->assertSessionHasErrors('technician_id');
    }

    public function test_assign_rejects_nonexistent_user(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($admin)
            ->patch("/tickets/{$ticket->id}/assign", ['technician_id' => 99999])
            ->assertSessionHasErrors('technician_id');
    }
}
