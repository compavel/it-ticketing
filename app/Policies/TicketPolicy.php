<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\TicketStatus;
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
     * Hanya technician dan admin yang boleh update ticket (mis. ganti status).
     * Ticket yang sudah `closed` bersifat final: statusnya tidak bisa diubah
     * lagi — kalau ternyata masih ada masalah baru, buat ticket baru.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->role !== UserRole::User
            && $ticket->status !== TicketStatus::Closed;
    }

    /**
     * Determine whether the user can see internal (technician-only) notes.
     *
     * Dipakai di view untuk menyembunyikan komentar internal dari requester.
     * Berbeda dengan update(): tidak peduli ticket closed atau tidak, teknisi
     * tetap boleh membaca catatan internal untuk riwayat.
     */
    public function viewInternal(User $user, Ticket $ticket): bool
    {
        return $user->role !== UserRole::User;
    }
}
