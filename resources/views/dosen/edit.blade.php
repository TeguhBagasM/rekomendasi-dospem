<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Dosen') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('dosen.update', $dosen) }}" class="space-y-6 max-w-3xl">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="nidn" value="NIDN" />
                            <x-text-input id="nidn" name="nidn" type="text" class="mt-1 block w-full" :value="old('nidn', $dosen->nidn)" required autofocus />
                            <x-input-error :messages="$errors->get('nidn')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="nama" value="Nama" />
                            <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" :value="old('nama', $dosen->nama)" required />
                            <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="jabatan" value="Jabatan" />
                            <x-text-input id="jabatan" name="jabatan" type="text" class="mt-1 block w-full" :value="old('jabatan', $dosen->jabatan)" />
                            <x-input-error :messages="$errors->get('jabatan')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="program_studi" value="Program Studi" />
                            <x-text-input id="program_studi" name="program_studi" type="text" class="mt-1 block w-full" :value="old('program_studi', $dosen->program_studi)" />
                            <x-input-error :messages="$errors->get('program_studi')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="kuota_bimbingan" value="Kuota Bimbingan" />
                            <x-text-input id="kuota_bimbingan" name="kuota_bimbingan" type="number" min="0" class="mt-1 block w-full" :value="old('kuota_bimbingan', $dosen->kuota_bimbingan)" required />
                            <x-input-error :messages="$errors->get('kuota_bimbingan')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="no_hp" value="No HP" />
                            <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full" :value="old('no_hp', $dosen->no_hp)" />
                            <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>Update</x-primary-button>
                            <a href="{{ route('dosen.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Keahlian Dosen</h3>
                            <p class="text-sm text-gray-500">Kelola daftar keahlian untuk dosen ini.</p>
                        </div>
                        <div class="rounded-full bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700">
                            Total: {{ $dosen->keahlian->count() }}
                        </div>
                    </div>

                    <div class="grid gap-4">
                        @forelse ($dosen->keahlian as $keahlian)
                            <div class="rounded-lg border border-gray-200 p-4 space-y-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $keahlian->bidang_keahlian }}</h4>
                                        <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $keahlian->tipe_faktor === 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $keahlian->tipe_label }}
                                        </span>
                                    </div>

                                    <form action="{{ route('keahlian.destroy', $keahlian->id) }}" method="POST" onsubmit="return confirm('Hapus keahlian ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-md bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                                            Hapus
                                        </button>
                                    </form>
                                </div>

                                <form method="POST" action="{{ route('keahlian.update', $keahlian->id) }}" class="grid gap-4 md:grid-cols-2">
                                    @csrf
                                    @method('PUT')

                                    <div class="md:col-span-2">
                                        <x-input-label :value="__('Bidang Keahlian')" for="bidang_keahlian_{{ $keahlian->id }}" />
                                        <x-text-input id="bidang_keahlian_{{ $keahlian->id }}" name="bidang_keahlian" type="text" class="mt-1 block w-full" :value="old('bidang_keahlian', $keahlian->bidang_keahlian)" required />
                                        <x-input-error :messages="$errors->get('bidang_keahlian')" class="mt-2" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <x-input-label :value="__('Deskripsi Keahlian')" for="deskripsi_keahlian_{{ $keahlian->id }}" />
                                        <textarea id="deskripsi_keahlian_{{ $keahlian->id }}" name="deskripsi_keahlian" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('deskripsi_keahlian', $keahlian->deskripsi_keahlian) }}</textarea>
                                        <x-input-error :messages="$errors->get('deskripsi_keahlian')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label :value="__('Tipe Faktor')" for="tipe_faktor_{{ $keahlian->id }}" />
                                        <select id="tipe_faktor_{{ $keahlian->id }}" name="tipe_faktor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                            <option value="1" @selected(old('tipe_faktor', $keahlian->tipe_faktor) == 1)>Core</option>
                                            <option value="2" @selected(old('tipe_faktor', $keahlian->tipe_faktor) == 2)>Secondary</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('tipe_faktor')" class="mt-2" />
                                    </div>

                                    <div class="flex items-end justify-end md:col-span-2">
                                        <x-primary-button>Simpan Perubahan</x-primary-button>
                                    </div>
                                </form>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500">
                                Belum ada keahlian untuk dosen ini.
                            </div>
                        @endforelse
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4">
                        <h4 class="mb-4 text-base font-semibold text-gray-900">Tambah Keahlian Baru</h4>

                        <form method="POST" action="{{ route('keahlian.store', $dosen) }}" class="grid gap-4 md:grid-cols-2">
                            @csrf

                            <div class="md:col-span-2">
                                <x-input-label for="bidang_keahlian" value="Bidang Keahlian" />
                                <x-text-input id="bidang_keahlian" name="bidang_keahlian" type="text" class="mt-1 block w-full" :value="old('bidang_keahlian')" required />
                                <x-input-error :messages="$errors->get('bidang_keahlian')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="deskripsi_keahlian" value="Deskripsi Keahlian" />
                                <textarea id="deskripsi_keahlian" name="deskripsi_keahlian" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('deskripsi_keahlian') }}</textarea>
                                <x-input-error :messages="$errors->get('deskripsi_keahlian')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="tipe_faktor" value="Tipe Faktor" />
                                <select id="tipe_faktor" name="tipe_faktor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="1" @selected(old('tipe_faktor', 1) == 1)>Core</option>
                                    <option value="2" @selected(old('tipe_faktor') == 2)>Secondary</option>
                                </select>
                                <x-input-error :messages="$errors->get('tipe_faktor')" class="mt-2" />
                            </div>

                            <div class="flex items-end justify-end">
                                <x-primary-button>Tambah Keahlian</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>