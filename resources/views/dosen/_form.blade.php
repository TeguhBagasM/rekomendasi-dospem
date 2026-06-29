{{-- 
    Shared form partial untuk create & edit dosen.
    Variables yang dibutuhkan:
    - $action      : string  - URL form action
    - $method      : string  - 'POST' atau 'PUT'
    - $programStudis : Collection
    - $bidangKeahlians : Collection (semua bidang keahlian)
    - $dosen       : Dosen|null (null saat create)
--}}

@php
    $isEdit        = isset($dosen) && $dosen->exists;
    $submitLabel   = $isEdit ? 'Update' : 'Simpan';

    // Bidang keahlian yang sudah dipilih
    $selectedBidang = old(
        'bidang_keahlian_ids',
        $isEdit ? $dosen->bidangKeahlians->pluck('id')->toArray() : []
    );

    // Profile data
    $profileTopik  = old('topik_metode',      $isEdit ? ($dosen->profile?->topik_metode ?? '')      : '');
    $profileJudul  = old('judul_penelitian',   $isEdit ? ($dosen->profile?->judul_penelitian ?? '')   : '');

    // Tentukan tab aktif saat ada error
    $tabWithError = 'tab1';
    if ($errors->hasAny(['bidang_keahlian_ids'])) {
        $tabWithError = 'tab2';
    } elseif ($errors->hasAny(['topik_metode', 'judul_penelitian'])) {
        $tabWithError = 'tab3';
    }

    $jabatanOptions = [
        'Tenaga Pengajar' => 'Tenaga Pengajar',
        'Asisten Ahli'  => 'Asisten Ahli',
        'Lektor'        => 'Lektor',
        'Lektor Kepala' => 'Lektor Kepala',
        'Guru Besar'    => 'Guru Besar',
    ];

    $pendidikanOptions = [
        'S1'   => 'S1',
        'S2'   => 'S2',
        'S3'   => 'S3',
        'Prof' => 'Profesor',
    ];
@endphp

<div
    x-data="{ activeTab: '{{ $errors->any() ? $tabWithError : 'tab1' }}' }"
    class="space-y-0"
>
    <form
        method="POST"
        action="{{ $action }}"
        class="space-y-0"
    >
        @csrf
        @if ($method === 'PUT')
            @method('PUT')
        @endif

        {{-- ===== TAB NAVIGATION ===== --}}
        <div class="border-b border-gray-200 bg-gray-50 px-6 pt-6">
            <nav class="-mb-px flex gap-1 overflow-x-auto" aria-label="Tabs">

                {{-- Tab 1 --}}
                <button
                    type="button"
                    @click="activeTab = 'tab1'"
                    :class="activeTab === 'tab1'
                        ? 'border-indigo-600 text-indigo-600 bg-white'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="inline-flex shrink-0 items-center gap-2 border-b-2 px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap rounded-t-md"
                >
                    <i class="fa-solid fa-user"></i>
                    Data Dosen
                    @if ($errors->hasAny(['nidn','nama','program_studi_id','jabatan_fungsional','pendidikan_terakhir','no_hp','kuota_dospem1','kuota_dospem2']))
                        <span class="inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                    @endif
                </button>

                {{-- Tab 2 --}}
                <button
                    type="button"
                    @click="activeTab = 'tab2'"
                    :class="activeTab === 'tab2'
                        ? 'border-indigo-600 text-indigo-600 bg-white'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="inline-flex shrink-0 items-center gap-2 border-b-2 px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap rounded-t-md"
                >
                    <i class="fa-solid fa-tags"></i>
                    Bidang Keahlian
                    @if ($errors->has('bidang_keahlian_ids'))
                        <span class="inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                    @endif
                </button>

                {{-- Tab 3 --}}
                <button
                    type="button"
                    @click="activeTab = 'tab3'"
                    :class="activeTab === 'tab3'
                        ? 'border-indigo-600 text-indigo-600 bg-white'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="inline-flex shrink-0 items-center gap-2 border-b-2 px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap rounded-t-md"
                >
                    <i class="fa-solid fa-flask"></i>
                    Profil Penelitian
                    @if ($errors->hasAny(['topik_metode','judul_penelitian']))
                        <span class="inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                    @endif
                </button>

            </nav>
        </div>

        {{-- ===== TAB CONTENT ===== --}}
        <div class="p-6 space-y-6">

            {{-- ---------------------------------------- --}}
            {{-- TAB 1 : Data Dosen --}}
            {{-- ---------------------------------------- --}}
            <div x-show="activeTab === 'tab1'" x-cloak>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- NIDN --}}
                    <div>
                        <x-input-label for="nidn" value="NIDN" />
                        <x-text-input
                            id="nidn"
                            name="nidn"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('nidn', $isEdit ? $dosen->nidn : '')"
                            required
                            autofocus
                            placeholder="0123456789"
                        />
                        <x-input-error :messages="$errors->get('nidn')" class="mt-2" />
                    </div>

                    {{-- Nama Lengkap --}}
                    <div>
                        <x-input-label for="nama" value="Nama Lengkap" />
                        <x-text-input
                            id="nama"
                            name="nama"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('nama', $isEdit ? $dosen->nama : '')"
                            required
                            placeholder="Dr. Budi Santoso, M.Kom."
                        />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    {{-- Program Studi --}}
                    <div>
                        <x-input-label for="program_studi_id" value="Program Studi" />
                        <select
                            id="program_studi_id"
                            name="program_studi_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                            <option value="">-- Pilih Program Studi --</option>
                            @foreach ($programStudis as $prodi)
                                <option
                                    value="{{ $prodi->id }}"
                                    @selected(old('program_studi_id', $isEdit ? $dosen->program_studi_id : '') == $prodi->id)
                                >
                                    {{ $prodi->program_studi }} - {{ $prodi->jenjang }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('program_studi_id')" class="mt-2" />
                    </div>

                    {{-- Jabatan Fungsional --}}
                    <div>
                        <x-input-label for="jabatan_fungsional" value="Jabatan Fungsional" />
                        <select
                            id="jabatan_fungsional"
                            name="jabatan_fungsional"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach ($jabatanOptions as $val => $label)
                                <option
                                    value="{{ $val }}"
                                    @selected(old('jabatan_fungsional', $isEdit ? $dosen->jabatan_fungsional : '') === $val)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('jabatan_fungsional')" class="mt-2" />
                    </div>

                    {{-- Pendidikan Terakhir --}}
                    <div>
                        <x-input-label for="pendidikan_terakhir" value="Pendidikan Terakhir" />
                        <select
                            id="pendidikan_terakhir"
                            name="pendidikan_terakhir"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                            <option value="">-- Pilih Pendidikan --</option>
                            @foreach ($pendidikanOptions as $val => $label)
                                <option
                                    value="{{ $val }}"
                                    @selected(old('pendidikan_terakhir', $isEdit ? $dosen->pendidikan_terakhir : '') === $val)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('pendidikan_terakhir')" class="mt-2" />
                    </div>

                    {{-- No HP --}}
                    <div>
                        <x-input-label for="no_hp" value="Nomor HP" />
                        <x-text-input
                            id="no_hp"
                            name="no_hp"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('no_hp', $isEdit ? $dosen->no_hp : '')"
                            placeholder="08123456789"
                        />
                        <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                    </div>

                    {{-- Kuota Dospem 1 --}}
                    <div>
                        <x-input-label for="kuota_dospem1" value="Kuota Dospem 1" />
                        <x-text-input
                            id="kuota_dospem1"
                            name="kuota_dospem1"
                            type="number"
                            min="0"
                            class="mt-1 block w-full"
                            :value="old('kuota_dospem1', $isEdit ? $dosen->kuota_dospem1 : 5)"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">Maksimum mahasiswa sebagai Pembimbing 1</p>
                        <x-input-error :messages="$errors->get('kuota_dospem1')" class="mt-2" />
                    </div>

                    {{-- Kuota Dospem 2 --}}
                    <div>
                        <x-input-label for="kuota_dospem2" value="Kuota Dospem 2" />
                        <x-text-input
                            id="kuota_dospem2"
                            name="kuota_dospem2"
                            type="number"
                            min="0"
                            class="mt-1 block w-full"
                            :value="old('kuota_dospem2', $isEdit ? $dosen->kuota_dospem2 : 10)"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">Maksimum mahasiswa sebagai Pembimbing 2</p>
                        <x-input-error :messages="$errors->get('kuota_dospem2')" class="mt-2" />
                    </div>

                </div>
            </div>

            {{-- ---------------------------------------- --}}
            {{-- TAB 2 : Bidang Keahlian --}}
            {{-- ---------------------------------------- --}}
            <div x-show="activeTab === 'tab2'" x-cloak>
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-900">Pilih Bidang Keahlian</h4>
                    <p class="text-xs text-gray-500 mt-1">
                        Pilih satu atau lebih bidang keahlian yang sesuai dengan dosen ini.
                    </p>
                </div>

                @if ($bidangKeahlians->isEmpty())
                    <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center">
                        <i class="fa-solid fa-tags text-2xl text-gray-300 mb-2"></i>
                        <p class="text-sm text-gray-500">Belum ada data bidang keahlian.</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Tambahkan bidang keahlian terlebih dahulu di menu Manajemen Bidang Keahlian.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($bidangKeahlians as $bidang)
                            @php
                                $checked = in_array($bidang->id, $selectedBidang);
                            @endphp
                            <label
                                for="bidang_{{ $bidang->id }}"
                                class="relative flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors
                                    {{ $checked
                                        ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500'
                                        : 'border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50/50'
                                    }}"
                                x-data
                                :class="$el.querySelector('input').checked
                                    ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500'
                                    : 'border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50/50'"
                            >
                                <input
                                    type="checkbox"
                                    id="bidang_{{ $bidang->id }}"
                                    name="bidang_keahlian_ids[]"
                                    value="{{ $bidang->id }}"
                                    {{ $checked ? 'checked' : '' }}
                                    @change="
                                        $el.closest('label').classList.toggle('border-indigo-500', $el.checked);
                                        $el.closest('label').classList.toggle('bg-indigo-50', $el.checked);
                                        $el.closest('label').classList.toggle('ring-1', $el.checked);
                                        $el.closest('label').classList.toggle('ring-indigo-500', $el.checked);
                                        $el.closest('label').classList.toggle('border-gray-200', !$el.checked);
                                        $el.closest('label').classList.toggle('bg-white', !$el.checked);
                                    "
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <div class="min-w-0 flex-1">
                                    <span class="block text-sm font-medium text-gray-900">
                                        {{ $bidang->nama }}
                                    </span>
                                </div>
                                {{-- Checkmark icon saat aktif --}}
                                <div
                                    x-show="$el.closest('label').querySelector('input').checked"
                                    class="shrink-0"
                                >
                                    <i class="fa-solid fa-circle-check text-indigo-500 text-lg"></i>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif

                <x-input-error :messages="$errors->get('bidang_keahlian_ids')" class="mt-3" />
            </div>

            {{-- ---------------------------------------- --}}
            {{-- TAB 3 : Profil Penelitian --}}
            {{-- ---------------------------------------- --}}
            <div x-show="activeTab === 'tab3'" x-cloak>
                <div class="space-y-5">

                    {{-- Topik & Metode --}}
                    <div>
                        <x-input-label for="topik_metode" value="Topik dan Metode yang Dikuasai" />
                        <p class="mt-0.5 text-xs text-gray-500">
                            Pisahkan dengan koma. Digunakan untuk pencocokan topik skripsi mahasiswa.
                        </p>
                        <textarea
                            id="topik_metode"
                            name="topik_metode"
                            rows="4"
                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            placeholder="random forest, decision tree, naive bayes, CNN, YOLO, klasifikasi, prediksi"
                        >{{ $profileTopik }}</textarea>
                        <x-input-error :messages="$errors->get('topik_metode')" class="mt-2" />
                    </div>

                    {{-- Judul Penelitian --}}
                    <div>
                        <x-input-label for="judul_penelitian" value="Judul Publikasi atau Penelitian Relevan" />
                        <p class="mt-0.5 text-xs text-gray-500">
                            Satu judul per baris. Digunakan sebagai referensi keahlian dosen.
                        </p>
                        <textarea
                            id="judul_penelitian"
                            name="judul_penelitian"
                            rows="6"
                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            placeholder="Implementasi YOLO untuk Deteksi Objek&#10;Sistem Rekomendasi Berbasis Collaborative Filtering&#10;Analisis Sentimen Menggunakan BERT"
                        >{{ $profileJudul }}</textarea>
                        <x-input-error :messages="$errors->get('judul_penelitian')" class="mt-2" />
                    </div>

                    {{-- Info box --}}
                    <div class="rounded-lg bg-blue-50 border border-blue-100 p-4 flex gap-3">
                        <i class="fa-solid fa-circle-info text-blue-400 mt-0.5 shrink-0"></i>
                        <p class="text-xs text-blue-700 leading-relaxed">
                            Data profil penelitian digunakan oleh sistem untuk menghitung
                            <strong>TF-IDF</strong> dan <strong>Cosine Similarity</strong>
                            antara topik penelitian mahasiswa dengan keahlian dosen
                            dalam proses generate rekomendasi dosen pembimbing.
                        </p>
                    </div>

                </div>
            </div>

        </div>

        {{-- ===== FOOTER ACTIONS ===== --}}
        <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
            <div class="flex items-center justify-between">
                <a
                    href="{{ route('dosen.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali ke Daftar Dosen
                </a>

                <div class="flex items-center gap-3">
                    {{-- Tab navigation helper --}}
                    <div class="hidden sm:flex items-center gap-1 text-xs text-gray-400">
                        <button type="button" @click="activeTab = 'tab1'"
                            :class="activeTab === 'tab1' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'"
                            class="h-2 w-2 rounded-full transition-colors">
                        </button>
                        <button type="button" @click="activeTab = 'tab2'"
                            :class="activeTab === 'tab2' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'"
                            class="h-2 w-2 rounded-full transition-colors">
                        </button>
                        <button type="button" @click="activeTab = 'tab3'"
                            :class="activeTab === 'tab3' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'"
                            class="h-2 w-2 rounded-full transition-colors">
                        </button>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-md bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition-colors"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        {{ $submitLabel }}
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>