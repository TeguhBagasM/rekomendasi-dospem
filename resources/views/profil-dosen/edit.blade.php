<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Profil Saya
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 p-4 text-sm text-green-700">
                    <i class="fa-solid fa-circle-check mr-1"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                    <p class="font-medium mb-1">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i>
                        Terdapat kesalahan pada form:
                    </p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">

                {{-- Header ringkas identitas dosen --}}
                <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-indigo-600 text-white flex items-center justify-center text-lg font-semibold shrink-0">
                        {{ strtoupper(substr($dosen->nama, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-base font-semibold text-gray-900 truncate">{{ $dosen->nama }}</p>
                        <p class="text-sm text-gray-500">NIDN {{ $dosen->nidn }}</p>
                    </div>
                </div>

                @php
                    $profileTopik = old('topik_metode', $dosen->profile?->topik_metode ?? '');
                    $profileJudul = old('judul_penelitian', $dosen->profile?->judul_penelitian ?? '');
                    $selectedBidang = old(
                        'bidang_keahlian_ids',
                        $dosen->bidangKeahlians->pluck('id')->toArray()
                    );

                    $tabWithError = 'tab1';
                    if ($errors->has('bidang_keahlian_ids')) {
                        $tabWithError = 'tab2';
                    } elseif ($errors->hasAny(['topik_metode', 'judul_penelitian'])) {
                        $tabWithError = 'tab3';
                    }
                @endphp

                <div x-data="{ activeTab: '{{ $errors->any() ? $tabWithError : 'tab1' }}' }">
                    <form method="POST" action="{{ route('profile-dosen.update') }}">
                        @csrf
                        @method('PUT')

                        {{-- ===== TAB NAVIGATION ===== --}}
                        <div class="border-b border-gray-200 bg-gray-50 px-6 pt-6">
                            <nav class="-mb-px flex gap-1 overflow-x-auto" aria-label="Tabs">

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
                                </button>

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
                                    @if ($errors->hasAny(['topik_metode', 'judul_penelitian']))
                                        <span class="inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                                    @endif
                                </button>

                            </nav>
                        </div>

                        <div class="p-6 space-y-6">

                            {{-- ============ TAB 1 : Data Dosen ============ --}}
                            <div x-show="activeTab === 'tab1'" x-cloak>
                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                                    <div>
                                        <x-input-label value="NIDN" />
                                        <input
                                            type="text"
                                            value="{{ $dosen->nidn }}"
                                            disabled
                                            class="mt-1 block w-full rounded-md border-gray-200 bg-gray-100 text-gray-500 shadow-sm text-sm cursor-not-allowed"
                                        />
                                    </div>

                                    <div>
                                        <x-input-label value="Nama" />
                                        <input
                                            type="text"
                                            value="{{ $dosen->nama }}"
                                            disabled
                                            class="mt-1 block w-full rounded-md border-gray-200 bg-gray-100 text-gray-500 shadow-sm text-sm cursor-not-allowed"
                                        />
                                    </div>

                                    <div>
                                        <x-input-label value="Program Studi" />
                                        <input
                                            type="text"
                                            value="{{ $dosen->programStudi?->program_studi ?? '-' }}"
                                            disabled
                                            class="mt-1 block w-full rounded-md border-gray-200 bg-gray-100 text-gray-500 shadow-sm text-sm cursor-not-allowed"
                                        />
                                    </div>

                                    <div>
                                        <x-input-label value="Jabatan Fungsional" />
                                        <input
                                            type="text"
                                            value="{{ $dosen->jabatan_fungsional ?? '-' }}"
                                            disabled
                                            class="mt-1 block w-full rounded-md border-gray-200 bg-gray-100 text-gray-500 shadow-sm text-sm cursor-not-allowed"
                                        />
                                    </div>

                                    <div>
                                        <x-input-label value="Pendidikan Terakhir" />
                                        <input
                                            type="text"
                                            value="{{ $dosen->pendidikan_terakhir ?? '-' }}"
                                            disabled
                                            class="mt-1 block w-full rounded-md border-gray-200 bg-gray-100 text-gray-500 shadow-sm text-sm cursor-not-allowed"
                                        />
                                    </div>

                                    {{-- Satu-satunya field editable di Tab 1 --}}
                                    <div>
                                        <x-input-label for="no_hp" value="Nomor HP" />
                                        <x-text-input
                                            id="no_hp"
                                            name="no_hp"
                                            type="text"
                                            class="mt-1 block w-full"
                                            :value="old('no_hp', $dosen->no_hp)"
                                            placeholder="08123456789"
                                        />
                                        <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                                    </div>

                                </div>

                                <p class="mt-4 text-xs text-gray-400">
                                    <i class="fa-solid fa-lock mr-1"></i>
                                    Data administratif hanya dapat diubah oleh Admin Program Studi. Hubungi admin jika ada kesalahan data.
                                </p>
                            </div>

                            {{-- ============ TAB 2 : Bidang Keahlian ============ --}}
                            <div x-show="activeTab === 'tab2'" x-cloak>
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-900">Pilih Bidang Keahlian</h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Pilih satu atau lebih bidang keahlian yang sesuai dengan keahlian Anda.
                                        Data ini digunakan dalam proses pencocokan topik skripsi mahasiswa.
                                    </p>
                                </div>

                                @if ($bidangKeahlians->isEmpty())
                                    <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center">
                                        <i class="fa-solid fa-tags text-2xl text-gray-300 mb-2"></i>
                                        <p class="text-sm text-gray-500">Belum ada data bidang keahlian.</p>
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
                                                <div x-show="$el.closest('label').querySelector('input').checked" class="shrink-0">
                                                    <i class="fa-solid fa-circle-check text-indigo-500 text-lg"></i>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif

                                <x-input-error :messages="$errors->get('bidang_keahlian_ids')" class="mt-3" />
                            </div>

                            {{-- ============ TAB 3 : Profil Penelitian ============ --}}
                            <div x-show="activeTab === 'tab3'" x-cloak>
                                <div class="space-y-5">

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

                                    <div>
                                        <x-input-label for="judul_penelitian" value="Judul Penelitian / Publikasi" />
                                        <p class="mt-0.5 text-xs text-gray-500">
                                            Satu judul per baris. Digunakan sebagai referensi keahlian Anda.
                                        </p>
                                        <textarea
                                            id="judul_penelitian"
                                            name="judul_penelitian"
                                            rows="6"
                                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            placeholder="Implementasi YOLO untuk Deteksi Objek&#10;Sistem Rekomendasi Berbasis Collaborative Filtering"
                                        >{{ $profileJudul }}</textarea>
                                        <x-input-error :messages="$errors->get('judul_penelitian')" class="mt-2" />
                                    </div>

                                    <div class="rounded-lg bg-blue-50 border border-blue-100 p-4 flex gap-3">
                                        <i class="fa-solid fa-circle-info text-blue-400 mt-0.5 shrink-0"></i>
                                        <p class="text-xs text-blue-700 leading-relaxed">
                                            Data ini digunakan oleh sistem untuk menghitung
                                            <strong>TF-IDF</strong> dan <strong>Cosine Similarity</strong>
                                            antara topik penelitian mahasiswa dengan keahlian Anda
                                            saat admin menjalankan proses generate rekomendasi.
                                        </p>
                                    </div>

                                </div>
                            </div>

                        </div>

                        {{-- ===== FOOTER ===== --}}
                        <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="hidden sm:flex items-center gap-1 text-xs text-gray-400">
                                    <button type="button" @click="activeTab = 'tab1'"
                                        :class="activeTab === 'tab1' ? 'bg-indigo-600' : 'bg-gray-200'"
                                        class="h-2 w-2 rounded-full transition-colors"></button>
                                    <button type="button" @click="activeTab = 'tab2'"
                                        :class="activeTab === 'tab2' ? 'bg-indigo-600' : 'bg-gray-200'"
                                        class="h-2 w-2 rounded-full transition-colors"></button>
                                    <button type="button" @click="activeTab = 'tab3'"
                                        :class="activeTab === 'tab3' ? 'bg-indigo-600' : 'bg-gray-200'"
                                        class="h-2 w-2 rounded-full transition-colors"></button>
                                </div>

                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-2 rounded-md bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition-colors"
                                >
                                    <i class="fa-solid fa-floppy-disk"></i>
                                    Simpan
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>