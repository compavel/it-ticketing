<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Kartu statistik per status --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach (\App\TicketStatus::cases() as $status)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                        <p class="text-sm font-medium text-gray-500">
                            {{ ucfirst(str_replace('_', ' ', $status->value)) }}
                        </p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $counts[$status->value] ?? 0 }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    {{ auth()->user()->can('viewAll', \App\Models\Ticket::class)
                        ? __('Total Semua Ticket')
                        : __('Total Ticket Saya') }}
                </p>
                <p class="text-3xl font-semibold text-indigo-600">{{ $total }}</p>
            </div>

            {{-- Ticket belum ditugaskan (technician / admin) --}}
            @can('viewAll', \App\Models\Ticket::class)
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">
                            {{ __('Ticket Belum Ditugaskan') }}
                            <span class="ml-1 font-normal text-gray-500">({{ $unassigned->count() }})</span>
                        </h3>
                    </div>

                    @if ($unassigned->isEmpty())
                        <div class="p-8 text-center">
                            <p class="text-sm text-gray-500">{{ __('Semua ticket sudah ada penanggung jawab. Kerja bagus!') }}</p>
                        </div>
                    @else
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach ($unassigned as $ticket)
                                <li class="px-4 py-4 sm:px-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('tickets.show', $ticket) }}"
                                           class="block text-sm font-semibold text-indigo-600 hover:text-indigo-500 truncate">
                                            #{{ $ticket->id }} — {{ $ticket->title }}
                                        </a>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                                            <span>{{ $ticket->category->name }}</span>
                                            <span aria-hidden="true">&middot;</span>
                                            <span>{{ $ticket->requester->name }}</span>
                                            <span aria-hidden="true">&middot;</span>
                                            <span>{{ $ticket->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <x-ticket-priority :priority="$ticket->priority" />

                                        {{-- Claim: teknisi ambil sendiri --}}
                                        @can('claim', $ticket)
                                            <form method="POST" action="{{ route('tickets.claim', $ticket) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">
                                                    {{ __('Ambil') }}
                                                </button>
                                            </form>
                                        @endcan

                                        {{-- Assign: admin pilih teknisi --}}
                                        @can('assign', $ticket)
                                            <form method="POST" action="{{ route('tickets.assign', $ticket) }}"
                                                  class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <label for="technician-{{ $ticket->id }}" class="sr-only">
                                                    {{ __('Pilih teknisi') }}
                                                </label>
                                                <select name="technician_id" id="technician-{{ $ticket->id }}" required
                                                        class="rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    @foreach ($technicians as $technician)
                                                        <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit"
                                                        class="inline-flex items-center rounded-md bg-gray-800 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-gray-700">
                                                    {{ __('Tugaskan') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            {{-- Ticket milik user biasa --}}
            @cannot('viewAll', \App\Models\Ticket::class)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-sm text-gray-600">
                        {{ __('Lihat semua ticket Anda di halaman ') }}
                        <a href="{{ route('tickets.index') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">
                            {{ __('Tickets') }}
                        </a>.
                    </p>
                </div>
            @endcannot

        </div>
    </div>
</x-app-layout>
