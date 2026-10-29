<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Kategori') }} — {{ $category->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('categories.update', $category) }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Nama Kategori') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('Slug akan digenerate ulang mengikuti nama baru.') }}
                    </p>

                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('categories.index') }}"
                       class="text-sm font-semibold text-gray-600 hover:text-gray-500">
                        {{ __('Batal') }}
                    </a>
                    <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        {{ __('Simpan Perubahan') }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
