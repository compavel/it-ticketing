<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kategori Ticket') }}
            </h2>
            @can('create', \App\Models\Category::class)
                <a href="{{ route('categories.create') }}"
                   class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                    </svg>
                    {{ __('Tambah Kategori') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @if ($categories->isEmpty())
                    <div class="p-12 text-center">
                        <p class="text-sm text-gray-500">{{ __('Belum ada kategori.') }}</p>
                    </div>
                @else
                    <ul role="list" class="divide-y divide-gray-200">
                        @foreach ($categories as $category)
                            <li class="px-4 py-4 sm:px-6 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900">
                                        {{ $category->name }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ $category->tickets_count }} {{ __('ticket terkait') }}
                                    </p>
                                </div>

                                @can('update', $category)
                                    <div class="flex shrink-0 items-center gap-2">
                                        <a href="{{ route('categories.edit', $category) }}"
                                           class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                            {{ __('Edit') }}
                                        </a>

                                        @can('delete', $category)
                                            <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                                  data-confirm="{{ __('Hapus kategori ":name"?', ['name' => $category->name]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 shadow-sm ring-1 ring-inset ring-red-100 hover:bg-red-100">
                                                    {{ __('Hapus') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
