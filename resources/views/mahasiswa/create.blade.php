<x-app-layout>
    <x-page-header
        title="Tambah Mahasiswa"
        description="Tambahkan data mahasiswa baru ke sistem."
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Data Akademik', 'url' => route('mahasiswa.index')],
            ['label' => 'Tambah Mahasiswa'],
        ]"
    />

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('mahasiswa.store') }}" class="space-y-6">
                        @csrf
                        <div>
                            <x-input-label for="nim" value="NIM" />
                            <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full" :value="old('nim')" required autofocus />
                            <x-input-error :messages="$errors->get('nim')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="nama" value="Nama" />
                            <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" :value="old('nama')" required />
                            <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="program_studi_id" value="Program Studi" />
                            <select id="program_studi_id" name="program_studi_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Pilih program studi</option>
                                @foreach ($programStudis as $programStudi)
                                    <option value="{{ $programStudi->id }}" @selected(old('program_studi_id') == $programStudi->id)>
                                        {{ $programStudi->program_studi }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('program_studi_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="angkatan" value="Angkatan" />
                            <x-text-input id="angkatan" name="angkatan" type="text" class="mt-1 block w-full" :value="old('angkatan')" required />
                            <x-input-error :messages="$errors->get('angkatan')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email akun (opsional)" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="no_hp" value="No HP (opsional)" />
                            <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full" :value="old('no_hp')" />
                            <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                        </div>
                        <div class="flex items-center gap-3">
                            <x-primary-button>Simpan</x-primary-button>
                            <a href="{{ route('mahasiswa.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>