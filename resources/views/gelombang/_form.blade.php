@php
    $isEdit      = isset($gelombang) && $gelombang->exists;
    $submitLabel = $isEdit ? 'Update' : 'Simpan';
    $periodeOptions = \App\Models\Gelombang::periodeAkademikOptions();
@endphp

<div class="p-6 space-y-6">
    <div>
        <x-input-label for="periode_akademik" value="Periode Akademik" />
        <select
            id="periode_akademik"
            name="periode_akademik"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
            autofocus
        >
            <option value="">-- Pilih Periode Akademik --</option>
            @foreach ($periodeOptions as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('periode_akademik', $isEdit ? $gelombang->periode_akademik : '') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('periode_akademik')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="nama" value="Nama Gelombang" />
        <x-text-input
            id="nama"
            name="nama"
            type="text"
            class="mt-1 block w-full"
            :value="old('nama', $isEdit ? $gelombang->nama : '')"
            placeholder="Contoh: Gelombang 1"
            required
        />
        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
    </div>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="tanggal_mulai" value="Tanggal Mulai" />
            <x-text-input
                id="tanggal_mulai"
                name="tanggal_mulai"
                type="date"
                class="mt-1 block w-full"
                :value="old('tanggal_mulai', $isEdit ? $gelombang->tanggal_mulai?->format('Y-m-d') : '')"
                required
            />
            <x-input-error :messages="$errors->get('tanggal_mulai')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="tanggal_selesai" value="Tanggal Selesai" />
            <x-text-input
                id="tanggal_selesai"
                name="tanggal_selesai"
                type="date"
                class="mt-1 block w-full"
                :value="old('tanggal_selesai', $isEdit ? $gelombang->tanggal_selesai?->format('Y-m-d') : '')"
                required
            />
            <x-input-error :messages="$errors->get('tanggal_selesai')" class="mt-2" />
        </div>
    </div>
    <div>
        <label for="is_active" class="inline-flex items-center gap-2 cursor-pointer">
            <input
                id="is_active"
                type="checkbox"
                name="is_active"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                @checked(old('is_active', $isEdit ? $gelombang->is_active : false))
            />
            <span class="text-sm font-medium text-gray-700">Jadikan Gelombang Aktif</span>
        </label>
        <p class="mt-1 text-xs text-gray-500">
            <i class="fa-solid fa-circle-info mr-1"></i>
            Jika dicentang, seluruh gelombang lain akan otomatis dinonaktifkan.
        </p>
        <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
    </div>
</div>
<div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
    <div class="flex items-center justify-between">
        <a
            href="{{ route('gelombang.index') }}"
            class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke Daftar Gelombang
        </a>
        <x-primary-button>
            <i class="fa-solid fa-floppy-disk mr-2"></i>
            {{ $submitLabel }}
        </x-primary-button>
    </div>
</div>
