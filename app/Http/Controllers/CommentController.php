<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Ticket;
use App\UserRole;

class CommentController extends Controller
{
    /**
     * Store a newly created comment on the given ticket.
     *
     * Komentar publik boleh dibuat siapapun yang dapat melihat ticket.
     * Komentar internal (is_internal) hanya untuk technician/admin — jika user
     * lain mengirim is_internal, nilainya dipaksa false (tidak dipercaya).
     */
    public function store(StoreCommentRequest $request, Ticket $ticket)
    {
        $data = $request->validated();

        if ($request->user()->role === UserRole::User) {
            $data['is_internal'] = false;
        }

        // Dibuat via relasi requester: user_id di-set oleh Laravel sebagai
        // foreign key (bukan lewat mass assignment), sesuai pola TicketController.
        $request->user()->comments()->create([
            'ticket_id' => $ticket->id,
            ...$data,
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Komentar ditambahkan.');
    }
}
