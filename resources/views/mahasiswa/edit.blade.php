<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Mahasiswa') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('mahasiswa.update', $mahasiswa) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="nim" value="NIM" />
                            <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full" :value="old('nim', $mahasiswa->nim)" required autofocus />
                            <x-input-error :messages="$errors->get('nim')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="nama" value="Nama" />
                            <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" :value="old('nama', $mahasiswa->nama)" required />
                            <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="program_studi" value="Program Studi" />
                            <x-text-input id="program_studi" name="program_studi" type="text" class="mt-1 block w-full" :value="old('program_studi', $mahasiswa->program_studi)" required />
                            <x-input-error :messages="$errors->get('program_studi')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="angkatan" value="Angkatan" />
                            <x-text-input id="angkatan" name="angkatan" type="text" class="mt-1 block w-full" :value="old('angkatan', $mahasiswa->angkatan)" required />
                            <x-input-error :messages="$errors->get('angkatan')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email akun (opsional)" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $mahasiswa->email)" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="no_hp" value="No HP (opsional)" />
                            <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full" :value="old('no_hp', $mahasiswa->no_hp)" />
                            <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                        </div>
                        <div class="flex items-center gap-3">
                            <x-primary-button>Update</x-primary-button>
                            <a href="{{ route('mahasiswa.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>