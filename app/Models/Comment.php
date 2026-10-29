<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan mass assignment:
 * - `body` & `is_internal` — input user, divalidasi Form Request.
 * - `ticket_id` — dari route yang sudah lolos TicketPolicy::view().
 * - `user_id` SENGAJA tidak di-fillable: penulis komentar harus berasal dari
 *   session, selalu diisi lewat relasi `$user->comments()->create(...)`.
 */
#[Fillable(['ticket_id', 'body', 'is_internal'])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
