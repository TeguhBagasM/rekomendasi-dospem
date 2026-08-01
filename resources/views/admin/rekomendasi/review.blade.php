<x-app-layout>
    <x-page-header
        title="Tinjau & Edit Draft Rekomendasi Dospem"
        description="Tinjau hasil perhitungan, sesuaikan pilihan dosen pembimbing, lalu finalisasi penetapan."
        icon="fa-clipboard-list"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Rekomendasi Dospem', 'url' => route('rekomendasi.index')],
            ['label' => 'Tinjau & Edit Draft'],
        ]"
    />

    @php
        $statusBadges = [
            'draft' => ['label' => 'Draft', 'classes' => 'bg-amber-50 text-amber-700 ring-amber-600/20'],
            'exported' => ['label' => 'Draft Terekspor', 'classes' => 'bg-sky-50 text-sky-700 ring-sky-600/20'],
            'imported' => ['label' => 'Diimport', 'classes' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20'],
            'finalized' => ['label' => 'Finalized', 'classes' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'],
        ];
        $status = $statusBadges[$riwayat->status] ?? $statusBadges['draft'];
        $isFinalized = $riwayat->status === 'finalized';
    @endphp

    <script type="application/json" id="dosen-options-data">{!! $dosensJson !!}</script>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="space-y-6">

                {{-- Flash Messages --}}
                @if (session('success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Header Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-gray-900 px-3 py-1 text-xs font-semibold text-white">
                                    <i class="fa-solid fa-layer-group mr-1.5"></i> Gelombang: {{ $gelombang?->nama ?? '-' }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $status['classes'] }}">
                                    <i class="fa-solid fa-circle-info mr-1.5"></i> {{ $status['label'] }}
                                </span>
                                @if ($isFinalized)
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        <i class="fa-solid fa-lock mr-1.5"></i> Draft Terkunci
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600">
                                <span><i class="fa-solid fa-calendar-day mr-1.5 text-gray-400"></i> Generate: {{ $riwayat->generated_at?->format('d M Y H:i') ?? '-' }}</span>
                                <span><i class="fa-solid fa-user-gear mr-1.5 text-gray-400"></i> Oleh: {{ $riwayat->generatedBy?->name ?? '-' }}</span>
                                <span><i class="fa-solid fa-file-lines mr-1.5 text-gray-400"></i> {{ $recommendations->count() }} Pengajuan</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.pengajuan-judul.index') }}"
                               class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                            </a>

                            <a href="{{ route('rekomendasi.exportDraft', $riwayat->id) }}"
                               class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                                <i class="fa-solid fa-file-export mr-2"></i> Export Draft
                            </a>

                            @if ($isFinalized)
                                <span class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm">
                                    <i class="fa-solid fa-check mr-2"></i> Sudah Di-finalisasi
                                </span>
                            {{-- @else
                                <button type="button"
                                        @click="$dispatch('open-confirm', { name: 'warning', formId: 'finalize-form', title: 'Finalisasi Penetapan Dospem', description: 'Seluruh hasil draft di atas akan dikunci sebagai penetapan resmi dan kuota bimbingan dosen akan dihitung terpakai. Lanjutkan?' })"
                                        class="inline-flex items-center rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700">
                                    <i class="fa-solid fa-stamp mr-2"></i> Finalisasi Penetapan
                                </button> --}}
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Hidden form untuk confirm modal finalisasi --}}
                @if (! $isFinalized)
                    <form id="finalize-form" method="POST" action="{{ route('rekomendasi.finalize', $riwayat->id) }}" class="hidden">
                        @csrf
                    </form>
                @endif

                {{-- Tabel Utama --}}
                @if ($isFinalized)
                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                        <i class="fa-solid fa-lock mr-1.5"></i>
                        Riwayat ini sudah di-finalisasi. Dropdown dikunci dan tidak dapat diubah lagi.
                    </div>
                @endif

                <form method="POST" action="{{ route('rekomendasi.bulkUpdateDraft', $riwayat->id) }}">
                    @csrf
                    @method('PATCH')

                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-4">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Daftar Draft Rekomendasi</h3>
                                <p class="text-sm text-gray-500">Ubah dropdown untuk menyesuaikan pilihan dosen, lalu simpan.</p>
                            </div>
                            @if (! $isFinalized)
                                <button type="submit"
                                        class="inline-flex items-center rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700">
                                    <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Semua Perubahan
                                </button>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        <th class="px-6 py-3">Mahasiswa & Judul</th>
                                        <th class="px-6 py-3">Dospem 1</th>
                                        <th class="px-6 py-3">Dospem 2</th>
                                        <th class="px-6 py-3 text-center">Detail Match</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($recommendations as $rekomendasi)
                                        @php
                                            $pengajuan = $rekomendasi->pengajuan;
                                            $mahasiswa = $pengajuan?->mahasiswa;
                                            $skorDospem1 = $rekomendasi->hasilPerhitungans
                                                ->where('jenis_dospem', 'dospem1')
                                                ->mapWithKeys(fn ($h) => [(int) $h->dosen_id => (float) $h->final_score]);
                                            $skorDospem2 = $rekomendasi->hasilPerhitungans
                                                ->where('jenis_dospem', 'dospem2')
                                                ->mapWithKeys(fn ($h) => [(int) $h->dosen_id => (float) $h->final_score]);
                                            $hasil1 = $rekomendasi->hasilPerhitungans->where('jenis_dospem', 'dospem1')->sortByDesc('final_score')->values();
                                            $hasil2 = $rekomendasi->hasilPerhitungans->where('jenis_dospem', 'dospem2')->sortByDesc('final_score')->values();
                                            $dospem1Options = $dosensWithQuota->map(fn ($dosen) => [
                                                'id' => (int) $dosen->id,
                                                'nama' => $dosen->nama,
                                                'sisa_kuota' => (int) $dosen->sisa_kuota_dospem1,
                                                'skor' => $skorDospem1[$dosen->id] ?? null,
                                            ])->values();
                                            $dospem2Options = $dosensWithQuota->map(fn ($dosen) => [
                                                'id' => (int) $dosen->id,
                                                'nama' => $dosen->nama,
                                                'sisa_kuota' => (int) $dosen->sisa_kuota_dospem2,
                                                'skor' => $skorDospem2[$dosen->id] ?? null,
                                            ])->values();
                                        @endphp

                                        <tr x-data="{
                                            dosens: JSON.parse(document.getElementById('dosen-options-data').textContent),
                                            d1: {{ $rekomendasi->rekomendasi_dospem1_id ?? 'null' }},
                                            d2: {{ $rekomendasi->rekomendasi_dospem2_id ?? 'null' }},
                                            get dosen1() { return this.dosens.find(d => d.id == this.d1) ?? null; },
                                            get dosen2() { return this.dosens.find(d => d.id == this.d2) ?? null; },
                                            get manual1() {
                                                const orig = {{ $rekomendasi->ringkasan['original_rekomendasi_dospem1_id'] ?? 'null' }};
                                                return this.d1 !== null && orig !== null && this.d1 != orig;
                                            },
                                            get manual2() {
                                                const orig = {{ $rekomendasi->ringkasan['original_rekomendasi_dospem2_id'] ?? 'null' }};
                                                return this.d2 !== null && orig !== null && this.d2 != orig;
                                            }
                                        }" class="align-top transition hover:bg-gray-50/60">
                                            {{-- Kolom 1: Mahasiswa --}}
                                            <td class="px-6 py-5">
                                                <div class="font-semibold text-gray-900">{{ $mahasiswa?->nama ?? '-' }}</div>
                                                <div class="mt-0.5 text-xs font-medium text-gray-500">{{ $mahasiswa?->nim ?? '-' }} · {{ $mahasiswa?->programStudi?->program_studi ?? '-' }}</div>
                                                <div class="mt-2 text-sm leading-relaxed text-gray-700">
                                                    <span class="font-medium text-gray-900">Judul:</span>
                                                    <span class="block">{{ $pengajuan?->judul_skripsi ?? '-' }}</span>
                                                </div>
                                                @if ($rekomendasi->ringkasan['manual_override'] ?? false)
                                                    <span class="mt-2 inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                        <i class="fa-solid fa-pen mr-1"></i> Ada Penyesuaian Manual
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Kolom 2: Dospem 1 --}}
                                            <td class="px-6 py-5">
                                                <select name="rows[{{ $rekomendasi->id }}][rekomendasi_dospem1_id]" x-model="d1"
                                                        {{ $isFinalized ? 'disabled' : '' }}
                                                        class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-200">
                                                    <option value="">-- Pilih Dosen Pembimbing 1 --</option>
                                                    @foreach ($dosensWithQuota as $dosen)
                                                        <option value="{{ $dosen->id }}" {{ $dosen->sisa_kuota_dospem1 === 0 ? 'class=text-red-600' : '' }}>
                                                            {{ $dosen->nama }} - Sisa Kuota: {{ $dosen->sisa_kuota_dospem1 }} (Skor Matched: {{ $skorDospem1[$dosen->id] ?? '-' }})
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <div class="mt-2 space-y-1.5" x-show="dosen1">
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700 ring-1 ring-inset ring-sky-600/20">
                                                            <i class="fa-solid fa-graduation-cap mr-1"></i> <span x-text="dosen1.bidang_keahlian || 'Tanpa rumpun ilmu'"></span>
                                                        </span>
                                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                                            <span x-text="dosen1.jabatan_fungsional || '-'"></span>
                                                        </span>
                                                    </div>
                                                    <div x-show="dosen1.dokumen_tfidf" class="max-h-10 overflow-hidden text-[11px] leading-tight text-gray-500">
                                                        <span class="font-medium text-gray-600">TF-IDF:</span> <span x-text="dosen1.dokumen_tfidf"></span>
                                                    </div>
                                                    <span x-show="manual1"
                                                          class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                        <i class="fa-solid fa-pen mr-1"></i> Diubah Manual
                                                    </span>
                                                    <span x-show="dosen1 && dosen1.sisa_dospem1 === 0"
                                                          class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
                                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Kuota Habis
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- Kolom 3: Dospem 2 --}}
                                            <td class="px-6 py-5">
                                                <select name="rows[{{ $rekomendasi->id }}][rekomendasi_dospem2_id]" x-model="d2"
                                                        {{ $isFinalized ? 'disabled' : '' }}
                                                        class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-200">
                                                    <option value="">-- Pilih Dosen Pembimbing 2 --</option>
                                                    @foreach ($dosensWithQuota as $dosen)
                                                        <option value="{{ $dosen->id }}" {{ $dosen->sisa_kuota_dospem2 === 0 ? 'class=text-red-600' : '' }}>
                                                            {{ $dosen->nama }} - Sisa Kuota: {{ $dosen->sisa_kuota_dospem2 }} (Skor Matched: {{ $skorDospem2[$dosen->id] ?? '-' }})
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <div class="mt-2 space-y-1.5" x-show="dosen2">
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700 ring-1 ring-inset ring-sky-600/20">
                                                            <i class="fa-solid fa-graduation-cap mr-1"></i> <span x-text="dosen2.bidang_keahlian || 'Tanpa rumpun ilmu'"></span>
                                                        </span>
                                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                                            <span x-text="dosen2.jabatan_fungsional || '-'"></span>
                                                        </span>
                                                    </div>
                                                    <div x-show="dosen2.dokumen_tfidf" class="max-h-10 overflow-hidden text-[11px] leading-tight text-gray-500">
                                                        <span class="font-medium text-gray-600">TF-IDF:</span> <span x-text="dosen2.dokumen_tfidf"></span>
                                                    </div>
                                                    <span x-show="manual2"
                                                          class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                        <i class="fa-solid fa-pen mr-1"></i> Diubah Manual
                                                    </span>
                                                    <span x-show="dosen2 && dosen2.sisa_dospem2 === 0"
                                                          class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
                                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Kuota Habis
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- Kolom 4: Detail Match --}}
                                            <td class="px-6 py-5 text-center">
                                                <button type="button"
                                                        @click="$dispatch('open-detail-{{ $rekomendasi->id }}')"
                                                        class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white p-2.5 text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900"
                                                        title="Lihat breakdown perhitungan">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">
                                                Belum ada rekomendasi draft pada riwayat ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if (! $isFinalized && $recommendations->isNotEmpty())
                            <div class="flex justify-end border-t border-gray-200 px-6 py-4">
                                <button type="submit"
                                        class="inline-flex items-center rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700">
                                    <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Semua Perubahan
                                </button>
                            </div>
                        @endif
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- ===================== MODAL DETAIL PERHITUNGAN ===================== --}}
    @foreach ($recommendations as $rekomendasi)
        @php
            $pengajuan = $rekomendasi->pengajuan;
            $mahasiswa = $pengajuan?->mahasiswa;
            $hasil1 = $rekomendasi->hasilPerhitungans->where('jenis_dospem', 'dospem1')->sortByDesc('final_score')->values();
            $hasil2 = $rekomendasi->hasilPerhitungans->where('jenis_dospem', 'dospem2')->sortByDesc('final_score')->values();
            $selected1 = $dosensWithQuota->firstWhere('id', $rekomendasi->rekomendasi_dospem1_id);
            $selected2 = $dosensWithQuota->firstWhere('id', $rekomendasi->rekomendasi_dospem2_id);
        @endphp

        <div x-data="{ open: false }"
             x-on:open-detail-{{ $rekomendasi->id }}.window="open = true"
             x-on:keydown.escape.window="open && (open = false)"
             x-show="open"
             x-cloak
             style="display: none;"
             class="fixed inset-0 z-[90] flex items-center justify-center px-4 py-6"
             role="dialog"
             aria-modal="true"
             aria-labelledby="detail-modal-title-{{ $rekomendasi->id }}">
            <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <div class="min-w-0">
                        <h3 id="detail-modal-title-{{ $rekomendasi->id }}" class="text-lg font-semibold text-gray-900">
                            Detail Perhitungan Profile Matching
                        </h3>
                        <p class="mt-0.5 line-clamp-2 text-sm text-gray-600">{{ $pengajuan?->judul_skripsi ?? '-' }}</p>
                    </div>
                    <button type="button" @click="open = false"
                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-200 hover:text-gray-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="max-h-[75vh] space-y-6 overflow-y-auto px-6 py-5">
                    {{-- Informasi Mahasiswa --}}
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-1 text-sm text-gray-600">
                        <span class="font-medium text-gray-900">{{ $mahasiswa?->nama ?? '-' }}</span>
                        <span><i class="fa-solid fa-id-badge mr-1.5 text-gray-400"></i> NIM: {{ $mahasiswa?->nim ?? '-' }}</span>
                        <span><i class="fa-solid fa-building-columns mr-1.5 text-gray-400"></i> {{ $mahasiswa?->programStudi?->program_studi ?? '-' }}</span>
                    </div>

                    {{-- Perbandingan Judul vs Dokumen TF-IDF Dosen Terpilih --}}
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-xl border border-gray-200 p-4">
                            <h4 class="mb-2 text-sm font-semibold text-gray-900">
                                <i class="fa-solid fa-file-lines mr-1.5 text-sky-600"></i> Dokumen Mahasiswa
                            </h4>
                            <p class="text-sm leading-relaxed text-gray-700">{{ $pengajuan?->judul_skripsi ?? '-' }}</p>
                            @if ($pengajuan?->file_proposal)
                                <a href="{{ asset('storage/'.$pengajuan->file_proposal) }}" target="_blank"
                                   class="mt-2 inline-flex items-center text-xs font-medium text-sky-600 hover:text-sky-700">
                                    <i class="fa-solid fa-paperclip mr-1"></i> Lihat File Proposal
                                </a>
                            @endif
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-xl border border-sky-200 bg-sky-50/50 p-4">
                                <h4 class="mb-1 text-sm font-semibold text-gray-900">
                                    <i class="fa-solid fa-user-tie mr-1.5 text-sky-600"></i> Dokumen TF-IDF Dospem 1: {{ $selected1?->nama ?? '-' }}
                                </h4>
                                <p class="text-xs text-gray-600">Rumpun: {{ $selected1?->bidangKeahlians?->pluck('nama')->join(', ') ?? '-' }}</p>
                                <p class="mt-1 break-words text-xs leading-relaxed text-gray-500">
                                    {{ $selected1?->profile?->dokumen_tfidf ?? 'Dokumen TF-IDF belum tersedia.' }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                                <h4 class="mb-1 text-sm font-semibold text-gray-900">
                                    <i class="fa-solid fa-user-tie mr-1.5 text-gray-600"></i> Dokumen TF-IDF Dospem 2: {{ $selected2?->nama ?? '-' }}
                                </h4>
                                <p class="text-xs text-gray-600">Rumpun: {{ $selected2?->bidangKeahlians?->pluck('nama')->join(', ') ?? '-' }}</p>
                                <p class="mt-1 break-words text-xs leading-relaxed text-gray-500">
                                    {{ $selected2?->profile?->dokumen_tfidf ?? 'Dokumen TF-IDF belum tersedia.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Breakdown Dospem 1 --}}
                    <div>
                        <h4 class="mb-2 text-sm font-semibold text-gray-900">Kandidat Dospem 1</h4>
                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                            <table class="min-w-full text-xs">
                                <thead class="bg-gray-50 text-left font-semibold text-gray-500">
                                    <tr>
                                        <th class="px-3 py-2.5">Rank</th>
                                        <th class="px-3 py-2.5">Nama Dosen</th>
                                        <th class="px-3 py-2.5">Skor</th>
                                        <th class="px-3 py-2.5">Kesesuaian Topik</th>
                                        <th class="px-3 py-2.5">Pendidikan</th>
                                        <th class="px-3 py-2.5">Kuota</th>
                                        <th class="px-3 py-2.5">Jabatan</th>
                                        <th class="px-3 py-2.5">CF</th>
                                        <th class="px-3 py-2.5">SF</th>
                                        <th class="px-3 py-2.5">Final Score</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($hasil1 as $h)
                                        <tr class="{{ $h->dosen_id === (int) $rekomendasi->rekomendasi_dospem1_id ? 'bg-sky-50 font-medium' : 'hover:bg-gray-50' }}">
                                            <td class="px-3 py-2">{{ $h->ranking }}</td>
                                            <td class="px-3 py-2">
                                                {{ $h->dosen?->nama ?? '-' }}
                                                @if ($h->dosen_id === (int) $rekomendasi->rekomendasi_dospem1_id)
                                                    <span class="ml-1 inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-semibold text-sky-700">Terpilih</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">{{ number_format((float) $h->topic_similarity, 4) }}</td>
                                            <td class="px-3 py-2">{{ $h->topic_scale }}</td>
                                            <td class="px-3 py-2">{{ $h->pendidikan_scale }}</td>
                                            <td class="px-3 py-2">{{ $h->quota_scale }}</td>
                                            <td class="px-3 py-2">{{ $h->jabatan_scale }}</td>
                                            <td class="px-3 py-2">{{ number_format((float) $h->cf_score, 4) }}</td>
                                            <td class="px-3 py-2">{{ number_format((float) $h->sf_score, 4) }}</td>
                                            <td class="px-3 py-2 font-semibold">{{ number_format((float) $h->final_score, 4) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="10" class="px-3 py-4 text-center text-gray-500">Tidak ada data perhitungan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tabel Breakdown Dospem 2 --}}
                    <div>
                        <h4 class="mb-2 text-sm font-semibold text-gray-900">Kandidat Dospem 2</h4>
                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                            <table class="min-w-full text-xs">
                                <thead class="bg-gray-50 text-left font-semibold text-gray-500">
                                    <tr>
                                        <th class="px-3 py-2.5">Rank</th>
                                        <th class="px-3 py-2.5">Nama Dosen</th>
                                        <th class="px-3 py-2.5">Skor</th>
                                        <th class="px-3 py-2.5">Kesesuaian Topik</th>
                                        <th class="px-3 py-2.5">Pendidikan</th>
                                        <th class="px-3 py-2.5">Kuota</th>
                                        <th class="px-3 py-2.5">Jabatan</th>
                                        <th class="px-3 py-2.5">CF</th>
                                        <th class="px-3 py-2.5">SF</th>
                                        <th class="px-3 py-2.5">Skor Final</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($hasil2 as $h)
                                        <tr class="{{ $h->dosen_id === (int) $rekomendasi->rekomendasi_dospem2_id ? 'bg-sky-50 font-medium' : 'hover:bg-gray-50' }}">
                                            <td class="px-3 py-2">{{ $h->ranking }}</td>
                                            <td class="px-3 py-2">
                                                {{ $h->dosen?->nama ?? '-' }}
                                                @if ($h->dosen_id === (int) $rekomendasi->rekomendasi_dospem2_id)
                                                    <span class="ml-1 inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-semibold text-sky-700">Terpilih</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">{{ number_format((float) $h->topic_similarity, 4) }}</td>
                                            <td class="px-3 py-2">{{ $h->topic_scale }}</td>
                                            <td class="px-3 py-2">{{ $h->pendidikan_scale }}</td>
                                            <td class="px-3 py-2">{{ $h->quota_scale }}</td>
                                            <td class="px-3 py-2">{{ $h->jabatan_scale }}</td>
                                            <td class="px-3 py-2">{{ number_format((float) $h->cf_score, 4) }}</td>
                                            <td class="px-3 py-2">{{ number_format((float) $h->sf_score, 4) }}</td>
                                            <td class="px-3 py-2 font-semibold">{{ number_format((float) $h->final_score, 4) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="10" class="px-3 py-4 text-center text-gray-500">Tidak ada data perhitungan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</x-app-layout>
