<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Category;
use App\Models\Ticket;
use App\TicketPriority;
use App\TicketStatus;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    /**
     * Display a listing of the tickets.
     *
     * Scope list ticket berdasar role:
     * - User: hanya ticket miliknya sendiri
     * - Technician/Admin: semua ticket
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Ticket::with(['category', 'requester', 'assignee'])
            ->latest();

        if ($user->role === UserRole::User) {
            $query->where('user_id', $user->id);
        }

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->query('status'));
        })
            ->when($request->filled('category_id'), function ($q) use ($request) {
                $q->where('category_id', $request->query('category_id'));
            })
            ->when($request->filled('priority'), function ($q) use ($request) {
                $q->where('priority', $request->query('priority'));
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('title', 'like', '%'.$request->query('q').'%')
                        ->orWhere('description', 'like', '%'.$request->query('q').'%');
                });
            });

        return view('tickets.index', [
            'tickets' => $query->paginate(10)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'statuses' => TicketStatus::cases(),
            'priorities' => TicketPriority::cases(),
        ]);
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        Gate::authorize('create', Ticket::class);

        return view('tickets.create', [
            'categories' => Category::orderBy('name')->get(),
            'priorities' => TicketPriority::cases(),
        ]);
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        $ticket = $request->user()->requestedTickets()->create([
            ...$request->validated(),
            'status' => TicketStatus::Open,
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket berhasil dibuat.');
    }

    /**
     * Display the specified ticket.
     */
    public function show(Request $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        return view('tickets.show', [
            'ticket' => $ticket->load(['category', 'requester', 'assignee', 'comments.author']),
        ]);
    }
}
