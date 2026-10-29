<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tickets') }}
            </h2>
            <a href="{{ route('tickets.create') }}"
               class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                </svg>
                {{ __('Buat Ticket') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Filter bar --}}
            <form method="GET" action="{{ route('tickets.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2">
                    <label for="q" class="sr-only">{{ __('Search') }}</label>
                    <input type="text" name="q" id="q" value="{{ request('q') }}"
                           placeholder="{{ __('Cari judul atau deskripsi...') }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <label for="status" class="sr-only">{{ __('Status') }}</label>
                    <select name="status" id="status"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">{{ __('Semua Status') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                {{ ucfirst(str_replace('_', ' ', $status->value)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="category_id" class="sr-only">{{ __('Category') }}</label>
                    <select name="category_id" id="category_id"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">{{ __('Semua Kategori') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <select name="priority" id="priority"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">{{ __('Semua Prioritas') }}</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>
                                {{ ucfirst($priority->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-5 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center rounded-md bg-gray-800 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700">
                        {{ __('Filter') }}
                    </button>
                    @if (request()->hasAny(['q', 'status', 'category_id', 'priority']))
                        <a href="{{ route('tickets.index') }}"
                           class="inline-flex items-center rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </form>

            {{-- Ticket list --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @if ($tickets->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 8.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v8.25A2.25 2.25 0 0 0 6 16.5h2.25m8.25-8.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-7.5A2.25 2.25 0 0 1 8.25 18v-1.5m8.25-8.25h-6a2.25 2.25 0 0 0-2.25 2.25v6" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ __('Belum ada ticket') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ request()->hasAny(['q', 'status', 'category_id', 'priority'])
                                ? __('Tidak ada ticket yang cocok dengan filter.')
                                : __('Mulai dengan membuat ticket baru.') }}
                        </p>
                    </div>
                @else
                    <ul role="list" class="divide-y divide-gray-200">
                        @foreach ($tickets as $ticket)
                            <li>
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="block hover:bg-gray-50 px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <p class="truncate text-sm font-semibold text-indigo-600">
                                                    #{{ $ticket->id }} — {{ $ticket->title }}
                                                </p>
                                            </div>
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
                                            <x-ticket-status :status="$ticket->status" />
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="px-4 py-3 sm:px-6">
                        {{ $tickets->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
