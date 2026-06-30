<x-app-layout>
    <x-page-header
        title="Tambah Pengajuan Judul"
        description="Ajukan judul skripsi beserta proposal PDF."
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Pengajuan Judul', 'url' => route('pengajuan.index')],
            ['label' => 'Tambah Pengajuan'],
        ]"
    />

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (! $gelombang)
                {{-- ==================== EMPTY STATE: TIDAK ADA GELOMBANG AKTIF ==================== --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex flex-col items-center justify-center text-center px-6 py-16">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-600 ring-1 ring-amber-100 mb-5">
                            <i class="fa-solid fa-calendar-xmark text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Pendaftaran pengajuan judul skripsi belum dibuka</h3>
                        <p class="mt-2 max-w-md text-sm text-gray-500">
                            Silakan menunggu hingga Tim Program Studi membuka gelombang pengajuan.
                            Silakan menghubungi Tim Program Studi apabila membutuhkan informasi lebih lanjut.
                        </p>
                        
                            <a href="{{ route('pengajuan.index') }}"
                            class="mt-6 inline-flex items-center gap-2 rounded-md bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700"
                        >
                            <i class="fa-solid fa-arrow-left"></i>
                            Kembali ke Daftar Pengajuan
                        </a>
                    </div>
                </div>
            @else
                {{-- ==================== FORM PENGAJUAN ==================== --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div class="mb-6 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                <i class="fa-solid fa-circle-check text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Gelombang Pengajuan Saat Ini</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $gelombang->nama }}</p>
                                <p class="text-xs text-gray-600 mt-0.5">
                                    {{ $gelombang->tanggal_mulai->translatedFormat('d F Y') }}
                                    &ndash;
                                    {{ $gelombang->tanggal_selesai->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        </div>

                        <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                            @csrf
                            <div>
                                <x-input-label for="judul_skripsi" value="Judul Skripsi" />
                                <textarea
                                    id="judul_skripsi"
                                    name="judul_skripsi"
                                    rows="1"
                                    x-data
                                    x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                                    x-on:input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                    class="mt-1 block w-full resize-none overflow-hidden rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                    autofocus
                                >{{ old('judul_skripsi') }}</textarea>
                                <x-input-error :messages="$errors->get('judul_skripsi')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="file_proposal" value="File Proposal PDF" />
                                <input id="file_proposal" name="file_proposal" type="file" accept="application/pdf" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <x-input-error :messages="$errors->get('file_proposal')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-3">
                                <x-primary-button>Simpan</x-primary-button>
                                <a href="{{ route('pengajuan.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>