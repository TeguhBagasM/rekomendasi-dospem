<x-app-layout>
    <x-page-header
        title="Tambah Program Studi"
        description="Tambahkan data program studi, kode prodi, fakultas, jenjang, dan kaprodi."
        icon="fa-circle-plus"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Data Akademik', 'url' => route('prodi.index')],
            ['label' => 'Tambah Program Studi'],
        ]"
    />

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                            <i class="fa-solid fa-folder-plus text-sm"></i>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Form Data Program Studi</h3>
                            <p class="text-sm text-gray-500">Isi field berikut untuk membuat data prodi baru.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('prodi.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="program_studi" value="Program Studi" />
                            <x-text-input id="program_studi" name="program_studi" type="text" class="mt-1 block w-full" :value="old('program_studi')" required autofocus />
                            <x-input-error :messages="$errors->get('program_studi')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="kode_prodi" value="Kode Prodi" />
                            <x-text-input id="kode_prodi" name="kode_prodi" type="text" class="mt-1 block w-full" :value="old('kode_prodi')" required />
                            <x-input-error :messages="$errors->get('kode_prodi')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="fakultas" value="Fakultas" />
                            <x-text-input id="fakultas" name="fakultas" type="text" class="mt-1 block w-full" :value="old('fakultas')" required />
                            <x-input-error :messages="$errors->get('fakultas')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="jenjang" value="Jenjang" />
                            <select id="jenjang" name="jenjang" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Pilih Jenjang</option>
                                <option value="D3" @selected(old('jenjang') === 'D3')>D3</option>
                                <option value="S1" @selected(old('jenjang') === 'S1')>S1</option>
                                <option value="S2" @selected(old('jenjang') === 'S2')>S2</option>
                                <option value="S3" @selected(old('jenjang') === 'S3')>S3</option>
                            </select>
                            <x-input-error :messages="$errors->get('jenjang')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="kaprodi" value="Kaprodi" />
                            <x-text-input id="kaprodi" name="kaprodi" type="text" class="mt-1 block w-full" :value="old('kaprodi')" required />
                            <x-input-error :messages="$errors->get('kaprodi')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>Simpan</x-primary-button>
                            <a href="{{ route('prodi.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>