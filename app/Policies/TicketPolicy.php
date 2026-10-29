<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\UserRole;

class TicketPolicy
{
    /**
     * Determine whether the user can view the ticket.
     *
     * Sebuah ticket bisa dilihat oleh:
     * - pemilik ticket (requester)
     * - teknisi yang ditugaskan (assignee)
     * - siapapun dengan role selain User (technician & admin) — karena mereka
     *   memang bertugas menghandle semua ticket
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id
            || $ticket->assigned_to === $user->id
            || $user->role !== UserRole::User;
    }

    /**
     * Determine whether the user can create tickets.
     *
     * Semua role boleh bikin ticket — termasuk teknisi/admin kalau mereka
     * nemu masalah yang perlu di-log.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the ticket.
     *
     * Hanya technician dan admin yang boleh update ticket (mis. ganti status,
     * assign teknisi). Role User tidak boleh.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->role !== UserRole::User;
    }
}
