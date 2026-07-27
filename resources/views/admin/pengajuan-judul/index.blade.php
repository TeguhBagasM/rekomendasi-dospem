<x-app-layout>
    <x-page-header
        title="Manajemen Pengajuan Judul"
        description="Kelola, cari, dan export data pengajuan judul skripsi."
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Admin'],
            ['label' => 'Manajemen Pengajuan Judul'],
        ]"
    />

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ALERTS --}}
            @if (session('success'))
                <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 ring-1 ring-inset ring-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('import_errors') && count(session('import_errors')) > 0)
                <div class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
                    <p class="font-semibold mb-1">Beberapa baris dilewati saat import:</p>
                    <ul class="list-disc list-inside space-y-0.5 max-h-40 overflow-y-auto">
                        @foreach (session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-inset ring-red-200">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- TOOLBAR --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Data Pengajuan Judul</h3>

                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                    {{-- Secondary actions: template / export / import pengajuan judul --}}
                    <details class="relative">
                        <summary class="flex list-none items-center justify-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 cursor-pointer">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                            Aksi Lainnya
                        </summary>

                        <div class="absolute right-0 z-10 mt-2 w-full min-w-[16rem] sm:w-80 rounded-lg bg-white p-2 shadow-lg ring-1 ring-gray-200">
                            <a href="{{ route('admin.pengajuan-judul.template') }}"
                               class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-file-arrow-down w-4 text-gray-400"></i>
                                Download Template
                            </a>

                            <a href="{{ route('admin.pengajuan-judul.export', $filters) }}"
                               class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-file-export w-4 text-gray-400"></i>
                                Export Excel
                            </a>

                            <div class="my-2 border-t border-gray-100"></div>

                            <form action="{{ route('admin.pengajuan-judul.import') }}" method="POST" enctype="multipart/form-data" class="space-y-2 px-3 py-2">
                                @csrf
                                <label class="block text-xs font-medium text-gray-600">Import Excel (.xlsx/.xls/.csv)</label>
                                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                                       class="block w-full text-xs text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-medium hover:file:bg-gray-200">
                                <p class="text-xs text-gray-500">Gunakan template resmi. Import butuh Gelombang aktif.</p>
                                <button type="submit" class="w-full rounded-md bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                    Proses Import
                                </button>
                            </form>
                        </div>
                    </details>

                    {{-- Primary actions --}}
                    <a href="{{ route('admin.validasi-pengajuan.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                        <i class="fa-solid fa-gavel"></i>
                        Validasi Pengajuan
                    </a>

                    <form method="POST" action="{{ route('rekomendasi.generate') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700 sm:w-auto">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            Generate Rekomendasi
                        </button>
                    </form>

                    <a href="{{ route('rekomendasi.export') }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-white px-4 py-2 text-sm font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-300 hover:bg-emerald-50 sm:w-auto">
                        <i class="fa-solid fa-file-export"></i>
                        Export Rekomendasi Dospem
                    </a>

                    {{-- Import Finalisasi Rekomendasi Dospem --}}
                    <details class="relative">
                        <summary class="flex list-none items-center justify-center gap-2 rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 cursor-pointer w-full sm:w-auto">
                            <i class="fa-solid fa-file-import"></i>
                            Import Finalisasi Dospem
                        </summary>

                        <div class="absolute right-0 z-10 mt-2 w-full min-w-[16rem] sm:w-80 rounded-lg bg-white p-2 shadow-lg ring-1 ring-gray-200">
                            <form action="{{ route('rekomendasi.import') }}" method="POST" enctype="multipart/form-data" class="space-y-2 px-3 py-2">
                                @csrf
                                <label class="block text-xs font-medium text-gray-600">Import Hasil Finalisasi (.xlsx/.xls/.csv)</label>
                                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                                       class="block w-full text-xs text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-medium hover:file:bg-gray-200">
                                <p class="text-xs text-gray-500">Unggah file rekomendasi yang sudah difinalisasi untuk disimpan sebagai keputusan akhir.</p>
                                <button type="submit" class="w-full rounded-md bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                                    Proses Import Finalisasi
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="bg-white rounded-lg shadow-sm p-4">
                <form action="{{ route('admin.pengajuan-judul.index') }}" method="GET"
                      class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Cari Nama/NIM</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Bagas / 221103..."
                               class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Angkatan</label>
                        <select name="angkatan" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Semua</option>
                            @foreach ($angkatans as $angkatan)
                                <option value="{{ $angkatan }}" @selected(($filters['angkatan'] ?? null) == $angkatan)>{{ $angkatan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select name="status" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Semua</option>
                            <option value="pending" @selected(($filters['status'] ?? null) == 'pending')>Pending</option>
                            <option value="disetujui" @selected(($filters['status'] ?? null) == 'disetujui')>Disetujui</option>
                            <option value="ditolak" @selected(($filters['status'] ?? null) == 'ditolak')>Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Gelombang</label>
                        <select name="gelombang" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Semua</option>
                            @foreach ($gelombangs as $gelombang)
                                <option value="{{ $gelombang->id }}" @selected(($filters['gelombang'] ?? null) == $gelombang->id)>{{ $gelombang->nama }} ({{ $gelombang->periode_akademik_label }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 w-full">
                            <i class="fa-solid fa-filter mr-2"></i> Terapkan
                        </button>
                        <a href="{{ route('admin.pengajuan-judul.index') }}" class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- INFO STRIP --}}
            <div class="text-sm text-gray-600 flex flex-wrap items-center justify-between gap-2">
                <span>
                    Total Data: <strong>{{ $pengajuans->total() }}</strong>
                    &middot; Menampilkan <strong>{{ $pengajuans->count() }}</strong> data
                    &middot; Halaman <strong>{{ $pengajuans->currentPage() }}</strong> dari <strong>{{ $pengajuans->lastPage() }}</strong>
                </span>
            </div>

            @php
                $statusColors = [
                    'pending' => 'bg-amber-100 text-amber-700',
                    'disetujui' => 'bg-emerald-100 text-emerald-700',
                    'ditolak' => 'bg-red-100 text-red-700',
                ];
            @endphp

            {{-- DATA: TABLE (md and up) --}}
            <div class="hidden md:block bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-white bg-gray-900">
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">NIM</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Program Studi</th>
                                <th class="px-4 py-3">Angkatan</th>
                                <th class="px-4 py-3">Judul Skripsi</th>
                                <th class="px-4 py-3">Gelombang</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Validator</th>
                                <th class="px-4 py-3">Tgl Validasi</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($pengajuans as $pengajuan)
                                <tr class="text-gray-700 hover:bg-gray-50 transition">
                                    <td class="px-4 py-4">{{ $pengajuans->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">{{ $pengajuan->mahasiswa?->nim ?? '-' }}</td>
                                    <td class="px-4 py-4">{{ $pengajuan->mahasiswa?->nama ?? '-' }}</td>
                                    <td class="px-4 py-4">{{ $pengajuan->mahasiswa?->programStudi?->program_studi ?? '-' }}</td>
                                    <td class="px-4 py-4">{{ $pengajuan->mahasiswa?->angkatan ?? '-' }}</td>
                                    <td class="px-4 py-4 max-w-xs truncate" title="{{ $pengajuan->judul_skripsi }}">{{ $pengajuan->judul_skripsi }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">
                                            {{ $pengajuan->gelombang?->nama ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusColors[$pengajuan->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ ucfirst($pengajuan->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">{{ $pengajuan->validator?->name ?? '-' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">{{ $pengajuan->validated_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <a href="{{ route('pengajuan.show', $pengajuan) }}"
                                           aria-label="Lihat detail pengajuan {{ $pengajuan->mahasiswa?->nama }}"
                                           class="inline-flex items-center justify-center rounded-md p-2 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-800">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada pengajuan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($pengajuans->hasPages())
                    <div class="px-4 py-4 border-t border-gray-100">
                        {{ $pengajuans->links() }}
                    </div>
                @endif
            </div>

            {{-- DATA: CARDS (mobile only) --}}
            <div class="md:hidden space-y-3">
                @forelse ($pengajuans as $pengajuan)
                    <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-100 p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $pengajuan->mahasiswa?->nama ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $pengajuan->mahasiswa?->nim ?? '-' }} &middot; Angkatan {{ $pengajuan->mahasiswa?->angkatan ?? '-' }}</p>
                            </div>
                            <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusColors[$pengajuan->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($pengajuan->status) }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-700">{{ $pengajuan->judul_skripsi }}</p>

                        <dl class="grid grid-cols-2 gap-2 text-xs text-gray-500">
                            <div>
                                <dt class="text-gray-400">Program Studi</dt>
                                <dd class="text-gray-700">{{ $pengajuan->mahasiswa?->programStudi?->program_studi ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-400">Gelombang</dt>
                                <dd>
                                    <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700">
                                        {{ $pengajuan->gelombang?->nama ?? '-' }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-400">Validator</dt>
                                <dd class="text-gray-700">{{ $pengajuan->validator?->name ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-400">Tgl Validasi</dt>
                                <dd class="text-gray-700">{{ $pengajuan->validated_at?->format('d M Y') ?? '-' }}</dd>
                            </div>
                        </dl>

                        <div class="pt-2 border-t border-gray-100 flex justify-end">
                            <a href="{{ route('pengajuan.show', $pengajuan) }}"
                               class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                <i class="fa-solid fa-eye"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg shadow-sm p-8 text-center text-sm text-gray-500">
                        Belum ada pengajuan.
                    </div>
                @endforelse

                @if ($pengajuans->hasPages())
                    <div class="pt-2">
                        {{ $pengajuans->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>