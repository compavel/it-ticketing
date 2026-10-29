<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit log perubahan pada ticket.
 *
 * Catatan mass assignment: `action`, `old_value`, `new_value`, `description`
 * divalidasi/diisi eksplisit oleh controller. `user_id` (pelaku) SENGAJA tidak
 * di-fillable — selalu diisi lewat relasi `$user->activities()->create(...)`,
 * sama seperti komentar. `ticket_id` dari route yang sudah lolos policy.
 */
#[Fillable(['ticket_id', 'action', 'old_value', 'new_value', 'description'])]
class TicketActivity extends Model
{
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
