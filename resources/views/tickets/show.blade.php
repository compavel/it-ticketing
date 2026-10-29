<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">
                {{ __('Ticket') }} #{{ $ticket->id }} — {{ $ticket->title }}
            </h2>
            <div class="flex shrink-0 items-center gap-2">
                <x-ticket-priority :priority="$ticket->priority" />
                <x-ticket-status :status="$ticket->status" />
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Detail ticket --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3 mb-6">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Kategori') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $ticket->category->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Dibuat oleh') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $ticket->requester->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Dibuat') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $ticket->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Penanggung Jawab') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $ticket->assignee?->name ?? __('Belum ditugaskan') }}
                            </dd>
                        </div>
                        @if ($ticket->updated_at != $ticket->created_at)
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Terakhir Diubah') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ticket->updated_at->format('d M Y, H:i') }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">{{ __('Deskripsi') }}</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $ticket->description }}</p>
                    </div>
                </div>
            </div>

            {{-- Placeholder komentar (Fitur 3) --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">{{ __('Komentar') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Fitur komentar akan tersedia di fase berikutnya.') }}</p>
                </div>
            </div>

            <div>
                <a href="{{ route('tickets.index') }}"
                   class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                    &larr; {{ __('Kembali ke daftar ticket') }}
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
