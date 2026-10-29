<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Ticket Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($errors->any())
                        <div class="mb-6 rounded-md bg-red-50 p-4">
                            <div class="text-sm font-medium text-red-800">
                                <p>{{ __('Terdapat kesalahan pada form:') }}</p>
                                <ul class="mt-1 list-inside list-disc">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tickets.store') }}">
                        @csrf

                        <div class="space-y-6">
                            {{-- Title --}}
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">
                                    {{ __('Judul') }}
                                </label>
                                <input type="text" name="title" id="title" required maxlength="255"
                                       value="{{ old('title') }}"
                                       placeholder="{{ __('Contoh: Wifi lab 3 mati total') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>

                            {{-- Category --}}
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">
                                    {{ __('Kategori') }}
                                </label>
                                <select name="category_id" id="category_id" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">{{ __('Pilih kategori...') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Priority --}}
                            <div>
                                <span class="block text-sm font-medium text-gray-700">{{ __('Prioritas') }}</span>
                                <div class="mt-2 space-y-2">
                                    @foreach ($priorities as $priority)
                                        <label class="flex items-center gap-3">
                                            <input type="radio" name="priority" value="{{ $priority->value }}"
                                                   {{ old('priority', 'medium') === $priority->value ? 'checked' : '' }}
                                                   class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-gray-700">
                                                {{ match ($priority->value) {
                                                    'low' => __('Low — gangguan minor, bisa ditunda'),
                                                    'medium' => __('Medium — mengganggu aktivitas kerja'),
                                                    'high' => __('High — operasional terhenti total'),
                                                } }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Description --}}
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    {{ __('Deskripsi Gangguan') }}
                                </label>
                                <textarea name="description" id="description" rows="6" required
                                          placeholder="{{ __('Jelaskan lokasi, gejala, dan apa yang sudah dicoba...') }}"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ __('Minimal 10 karakter. Sertakan lokasi & gejala supaya teknisi cepat tanggap.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <a href="{{ route('tickets.index') }}"
                               class="text-sm font-semibold text-gray-700 hover:text-gray-900">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                {{ __('Kirim Ticket') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
