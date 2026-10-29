<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\TicketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_comment(): void
    {
        $ticket = Ticket::factory()->create();

        $this->post("/tickets/{$ticket->id}/comments", ['body' => 'Halo'])
            ->assertRedirect('/login');
    }

    public function test_user_can_add_public_comment(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/comments", ['body' => 'Sudah saya cek, masih error.']);

        $response->assertRedirect("/tickets/{$ticket->id}");

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => 'Sudah saya cek, masih error.',
            'is_internal' => false,
        ]);
    }

    public function test_user_cannot_force_internal_comment(): void
    {
        // User biasa mengirim is_internal=1 — nilai itu TIDAK dipercaya.
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/comments", [
                'body' => 'Catatan internal sembunyi-sembunyi.',
                'is_internal' => 1,
            ]);

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'body' => 'Catatan internal sembunyi-sembunyi.',
            'is_internal' => false,
        ]);
    }

    public function test_technician_can_add_internal_comment(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($technician)
            ->post("/tickets/{$ticket->id}/comments", [
                'body' => 'Adaptor POE kemungkinan mati, bawa spare besok.',
                'is_internal' => 1,
            ]);

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'body' => 'Adaptor POE kemungkinan mati, bawa spare besok.',
            'is_internal' => true,
        ]);
    }

    public function test_user_cannot_comment_on_ticket_they_cannot_view(): void
    {
        $user = User::factory()->create();
        $otherTicket = Ticket::factory()->create();

        $this->actingAs($user)
            ->post("/tickets/{$otherTicket->id}/comments", ['body' => 'Nyasar'])
            ->assertForbidden();

        $this->assertSame(0, Comment::count());
    }

    public function test_comment_requires_body(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->from("/tickets/{$ticket->id}")
            ->post("/tickets/{$ticket->id}/comments", ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->assertSame(0, Comment::count());
    }

    public function test_internal_comment_is_hidden_from_requester(): void
    {
        $requester = User::factory()->create();
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['user_id' => $requester->id]);

        $technician->comments()->create([
            'ticket_id' => $ticket->id,
            'body' => 'RAHASIA-INTERNAL-CATATAN',
            'is_internal' => true,
        ]);

        $this->actingAs($requester)
            ->get("/tickets/{$ticket->id}")
            ->assertOk()
            ->assertDontSee('RAHASIA-INTERNAL-CATATAN')
            ->assertDontSee('Internal — catatan teknisi');
    }

    public function test_internal_comment_is_visible_to_technician(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create();

        $technician->comments()->create([
            'ticket_id' => $ticket->id,
            'body' => 'RAHASIA-INTERNAL-CATATAN',
            'is_internal' => true,
        ]);

        $this->actingAs($technician)
            ->get("/tickets/{$ticket->id}")
            ->assertOk()
            ->assertSee('RAHASIA-INTERNAL-CATATAN')
            ->assertSee('Internal — catatan teknisi');
    }

    public function test_comment_count_is_not_leaked_to_requester(): void
    {
        $requester = User::factory()->create();
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['user_id' => $requester->id]);

        $technician->comments()->create([
            'ticket_id' => $ticket->id,
            'body' => 'Internal satu',
            'is_internal' => true,
        ]);
        $requester->comments()->create([
            'ticket_id' => $ticket->id,
            'body' => 'Publik satu',
            'is_internal' => false,
        ]);

        // Requester hanya melihat 1 komentar publik — internal tidak dihitung.
        $this->actingAs($requester)
            ->get("/tickets/{$ticket->id}")
            ->assertOk()
            ->assertSee('(1)', false)
            ->assertDontSee('(2)', false);
    }

    public function test_status_closed_shows_final_note_to_technician(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Closed]);

        $this->actingAs($technician)
            ->get("/tickets/{$ticket->id}")
            ->assertOk()
            ->assertSee('tidak bisa diubah lagi')
            ->assertDontSee('Ubah Status');
    }
}
