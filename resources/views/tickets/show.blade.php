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

            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

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

            {{-- Update status (technician / admin saja) --}}
            @can('update', $ticket)
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('Ubah Status') }}</h3>
                        <form method="POST" action="{{ route('tickets.status', $ticket) }}"
                              class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            @csrf
                            @method('PATCH')

                            <div class="flex-1">
                                <label for="status" class="sr-only">{{ __('Status baru') }}</label>
                                <select name="status" id="status" required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    @foreach (\App\TicketStatus::cases() as $status)
                                        <option value="{{ $status->value }}" @selected($ticket->status === $status)>
                                            {{ ucfirst(str_replace('_', ' ', $status->value)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                {{ __('Simpan Status') }}
                            </button>
                        </form>
                        <p class="mt-2 text-xs text-gray-500">
                            {{ __('Alur: open → in_progress → resolved → closed. Status closed bersifat final.') }}
                        </p>
                    </div>
                </div>
            @else
                @can('viewInternal', $ticket)
                    @if ($ticket->status === \App\TicketStatus::Closed)
                        <div class="rounded-md bg-gray-100 p-4">
                            <p class="text-sm text-gray-600">
                                {{ __('Ticket ini sudah ditutup (closed) dan statusnya tidak bisa diubah lagi.') }}
                            </p>
                        </div>
                    @endif
                @endcan
            @endcan

            {{-- Komentar --}}
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">
                        {{ __('Komentar') }}
                        <span class="ml-1 font-normal text-gray-500">({{ $ticket->comments->count() }})</span>
                    </h3>

                    @if ($ticket->comments->isEmpty())
                        <p class="text-sm text-gray-500 mb-6">{{ __('Belum ada komentar.') }}</p>
                    @else
                        <ul class="space-y-4 mb-6">
                            @foreach ($ticket->comments as $comment)
                                <li class="rounded-lg border p-4 {{ $comment->is_internal ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-white' }}">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ $comment->author->name }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $comment->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    @if ($comment->is_internal)
                                        <span class="mb-2 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                            {{ __('Internal — catatan teknisi') }}
                                        </span>
                                    @endif

                                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $comment->body }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    {{-- Form komentar --}}
                    <form method="POST" action="{{ route('tickets.comments.store', $ticket) }}"
                          class="border-t border-gray-200 pt-6">
                        @csrf

                        <label for="body" class="block text-sm font-medium text-gray-700">
                            {{ __('Tulis komentar') }}
                        </label>
                        <textarea name="body" id="body" rows="3" required
                                  placeholder="{{ __('Tanggapan untuk ticket ini...') }}"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('body') }}</textarea>

                        @error('body')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        @can('viewInternal', $ticket)
                            <label class="mt-3 flex items-center gap-2">
                                <input type="hidden" name="is_internal" value="0">
                                <input type="checkbox" name="is_internal" value="1"
                                       {{ old('is_internal') ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">
                                    {{ __('Tandai sebagai komentar internal (tidak terlihat oleh requester)') }}
                                </span>
                            </label>
                        @endcan

                        <div class="mt-3 flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700">
                                {{ __('Kirim Komentar') }}
                            </button>
                        </div>
                    </form>
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
