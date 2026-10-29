<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\TicketStatus;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     *
     * Scope statistik mengikuti role (cek TicketPolicy::viewAll()):
     * - User: hanya ticket miliknya sendiri
     * - Technician/Admin: semua ticket + daftar ticket belum ditugaskan
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $seeAll = Gate::allows('viewAll', Ticket::class);

        $baseQuery = $seeAll
            ? Ticket::query()
            : Ticket::where('user_id', $user->id);

        // Hitung per status dalam satu query (bukan loop query N+1).
        $statusCounts = $baseQuery->clone()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Normalisasi: pastikan semua status selalu ada di kartu (0 jika kosong).
        $counts = collect(TicketStatus::cases())
            ->mapWithKeys(fn ($status) => [
                $status->value => (int) ($statusCounts[$status->value] ?? 0),
            ]);

        // Ticket belum ditugaskan hanya relevan untuk technician/admin.
        $unassigned = $seeAll
            ? Ticket::with(['category', 'requester'])
                ->whereNull('assigned_to')
                ->latest()
                ->get()
            : collect();

        // Daftar teknisi untuk form assign (hanya dipakai admin — dicek di view).
        $technicians = $seeAll
            ? User::where('role', '!=', UserRole::User->value)
                ->orderBy('name')
                ->get()
            : collect();

        return view('dashboard', [
            'counts' => $counts,
            'total' => $counts->sum(),
            'unassigned' => $unassigned,
            'technicians' => $technicians,
        ]);
    }
}
