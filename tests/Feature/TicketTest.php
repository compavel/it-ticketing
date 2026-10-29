<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\TicketPriority;
use App\TicketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_ticket_pages(): void
    {
        $ticket = Ticket::factory()->create();

        $this->get('/tickets')->assertRedirect('/login');
        $this->get('/tickets/create')->assertRedirect('/login');
        $this->get("/tickets/{$ticket->id}")->assertRedirect('/login');
        $this->post('/tickets')->assertRedirect('/login');
    }

    public function test_user_can_create_ticket(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Wifi lab 3 mati total',
            'description' => 'Access point di lab 3 tidak menyala sejak pagi, indicator lamp off.',
            'category_id' => $category->id,
            'priority' => TicketPriority::High->value,
        ]);

        $ticket = Ticket::first();

        $this->assertNotNull($ticket);
        $response->assertRedirect("/tickets/{$ticket->id}");

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'Wifi lab 3 mati total',
            'category_id' => $category->id,
            'priority' => TicketPriority::High->value,
            'status' => TicketStatus::Open->value,
            'user_id' => $user->id,
            'assigned_to' => null,
        ]);
    }

    public function test_create_ticket_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/tickets/create')->post('/tickets', [
            'title' => 'abc',
            'description' => 'abc',
            'category_id' => 999999,
            'priority' => 'urgent',
        ]);

        $response->assertRedirect('/tickets/create');
        $response->assertSessionHasErrors(['title', 'description', 'category_id', 'priority']);

        $this->assertSame(0, Ticket::count());
    }

    public function test_status_defaults_to_open_and_priority_defaults_to_medium(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($user)->post('/tickets', [
            'title' => 'Printer ruang TU error',
            'description' => 'Printer tidak bisa mencetak, muncul error paper jam terus.',
            'category_id' => $category->id,
            'priority' => TicketPriority::Medium->value,
        ]);

        $this->assertDatabaseHas('tickets', [
            'status' => TicketStatus::Open->value,
            'priority' => TicketPriority::Medium->value,
        ]);
    }

    public function test_user_only_sees_own_tickets_in_index(): void
    {
        $user = User::factory()->create();
        $ownTicket = Ticket::factory()->create(['user_id' => $user->id]);
        $otherTicket = Ticket::factory()->create();

        $response = $this->actingAs($user)->get('/tickets');

        $response->assertOk();
        $response->assertSee($ownTicket->title);
        $response->assertDontSee($otherTicket->title);
    }

    public function test_technician_sees_all_tickets_in_index(): void
    {
        $technician = User::factory()->technician()->create();
        $ticketA = Ticket::factory()->create();
        $ticketB = Ticket::factory()->create();

        $response = $this->actingAs($technician)->get('/tickets');

        $response->assertOk();
        $response->assertSee($ticketA->title);
        $response->assertSee($ticketB->title);
    }

    public function test_index_can_filter_by_status(): void
    {
        $user = User::factory()->create();
        $openTicket = Ticket::factory()->create(['user_id' => $user->id]);
        $closedTicket = Ticket::factory()
            ->status(TicketStatus::Closed)
            ->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/tickets?status=closed');

        $response->assertOk();
        $response->assertSee($closedTicket->title);
        $response->assertDontSee($openTicket->title);
    }

    public function test_user_cannot_view_other_users_ticket(): void
    {
        $user = User::factory()->create();
        $otherTicket = Ticket::factory()->create();

        $this->actingAs($user)
            ->get("/tickets/{$otherTicket->id}")
            ->assertForbidden();
    }

    public function test_technician_can_view_any_ticket(): void
    {
        $technician = User::factory()->technician()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($technician)
            ->get("/tickets/{$ticket->id}")
            ->assertOk()
            ->assertSee($ticket->title);
    }

    public function test_ticket_page_shows_status_and_priority_badges(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()
            ->status(TicketStatus::InProgress)
            ->create(['user_id' => $user->id, 'priority' => TicketPriority::High]);

        $this->actingAs($user)
            ->get("/tickets/{$ticket->id}")
            ->assertOk()
            ->assertSee('In Progress')
            ->assertSee('High');
    }
}
