<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
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
        // Ticket dibuat via relasi requester supaya user_id diisi otomatis dari
        // session (bukan mass assignment). `status` tidak di-fillable, jadi
        // di-set eksplisit di sini.
        $ticket = $request->user()->requestedTickets()->make($request->validated());
        $ticket->status = TicketStatus::Open;
        $ticket->save();

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

        $ticket->load(['category', 'requester', 'assignee', 'comments.author']);

        // Komentar internal hanya untuk technician/admin. Di-filter di sini
        // (bukan di view) supaya count & tampilan konsisten.
        if (! $request->user()->can('viewInternal', $ticket)) {
            $ticket->setRelation(
                'comments',
                $ticket->comments->where('is_internal', false)->values(),
            );
        }

        return view('tickets.show', [
            'ticket' => $ticket,
            'technicians' => $request->user()->can('assign', $ticket)
                ? User::where('role', '!=', UserRole::User->value)->orderBy('name')->get()
                : collect(),
        ]);
    }

    /**
     * Update the ticket's status.
     *
     * Authorization (technician/admin & bukan ticket closed) ditangani oleh
     * TicketPolicy::update() — meliputi aturan "closed = final".
     */
    public function updateStatus(UpdateStatusRequest $request, Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        // status sengaja TIDAK masuk $fillable (field sensitif): di-set
        // eksplisit di sini, bukan lewat mass assignment.
        $ticket->status = $request->validated('status');
        $ticket->save();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Status ticket diperbarui.');
    }

    /**
     * Assign the ticket to a chosen technician (admin only).
     *
     * Authorization di TicketPolicy::assign(). Target teknisi divalidasi
     * AssignTicketRequest (harus user dengan role technician/admin).
     * `assigned_to` tidak di-fillable — di-set eksplisit.
     */
    public function assign(AssignTicketRequest $request, Ticket $ticket)
    {
        $ticket->assigned_to = $request->validated('technician_id');
        $ticket->save();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket ditugaskan.');
    }

    /**
     * Claim the unassigned ticket for the current technician.
     *
     * Authorization di TicketPolicy::claim() (hanya ticket yang belum
     * ditugaskan). `assigned_to` di-set eksplisit dari session.
     */
    public function claim(Request $request, Ticket $ticket)
    {
        Gate::authorize('claim', $ticket);

        $ticket->assigned_to = $request->user()->id;
        $ticket->save();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket berhasil diambil.');
    }
}
