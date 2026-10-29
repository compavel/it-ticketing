<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            // Pelaku aksi. user_id pernah null kalau suatu saat ada aksi sistem,
            // saat ini selalu terisi (actor = user yang melakukan aksi).
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30);        // created | status_changed | assigned | claimed
            $table->string('old_value', 50)->nullable();
            $table->string('new_value', 50)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            // Halaman detail ticket menampilkan riwayat -> filter per ticket.
            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }
};
