<x-app-layout>
    <x-page-header
        title="Rekomendasi Dospem"
        description="Generate, lihat, dan finalisasi rekomendasi dosen pembimbing."
        icon="fa-wand-magic-sparkles"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Rekomendasi Dospem'],
        ]"
    />

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    {{-- Header & Tombol Aksi --}}
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                                <i class="fa-solid fa-list text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Daftar Rekomendasi</h3>
                                <p class="text-sm text-gray-500">Total Data: {{ $pengajuans->count() }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <form method="POST" action="{{ route('rekomendasi.generate') }}">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                    <i class="fa-solid fa-wand-magic-sparkles mr-2"></i> Generate Batch
                                </button>
                            </form>

                            <a href="{{ route('rekomendasi.export') }}"
                               class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-file-export mr-2"></i> Export Excel
                            </a>

                            <button type="button"
                                    onclick="document.getElementById('modal-import-rekomendasi').classList.remove('hidden')"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-file-import mr-2"></i> Import Finalisasi
                            </button>
                        </div>
                    </div>

                    {{-- Flash Message --}}
                    @if (session('success'))
                        <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                            <div class="font-semibold mb-2">Terjadi kesalahan pada file yang di-import:</div>
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Tabel --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-white bg-gray-900">
                                    <th class="px-4 py-3">Judul</th>
                                    <th class="px-4 py-3">Mahasiswa</th>
                                    <th class="px-4 py-3">Rekomendasi Dospem 1</th>
                                    <th class="px-4 py-3">Rekomendasi Dospem 2</th>
                                    <th class="px-4 py-3">Dospem 1 Final</th>
                                    <th class="px-4 py-3">Dospem 2 Final</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($pengajuans as $pengajuan)
                                    @php
                                        $recommendation = $recommendations[$pengajuan->id] ?? null;
                                        $finalAssignment = $pengajuan->penetapanDospembing;
                                    @endphp
                                    <tr class="text-gray-700 hover:bg-gray-50 transition">
                                        <td class="px-4 py-4 align-top">{{ $pengajuan->judul_skripsi }}</td>
                                        <td class="px-4 py-4 align-top">
                                            <div class="font-medium text-gray-900">{{ $pengajuan->mahasiswa?->nama ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ $pengajuan->mahasiswa?->nim ?? '-' }} | {{ $pengajuan->mahasiswa?->programStudi?->program_studi ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-4 align-top">{{ $recommendation?->rekomendasiDospem1?->nama ?? '-' }}</td>
                                        <td class="px-4 py-4 align-top">{{ $recommendation?->rekomendasiDospem2?->nama ?? '-' }}</td>
                                        <td class="px-4 py-4 align-top">{{ $finalAssignment?->dospem1?->nama ?? '-' }}</td>
                                        <td class="px-4 py-4 align-top">{{ $finalAssignment?->dospem2?->nama ?? '-' }}</td>
                                        <td class="px-4 py-4 align-top">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $finalAssignment ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                                {{ $finalAssignment ? 'Final' : 'Menunggu Import' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7"
                                            class="px-4 py-8 text-center text-sm text-gray-500">
                                            Belum ada pengajuan disetujui.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal Import --}}
    <div id="modal-import-rekomendasi" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Import Finalisasi Dospem</h3>
                <button type="button"
                        onclick="document.getElementById('modal-import-rekomendasi').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p class="text-sm text-gray-500">
                Unggah file rekomendasi yang sudah difinalisasi untuk disimpan sebagai keputusan akhir.
            </p>

            <form action="{{ route('rekomendasi.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="block w-full text-sm text-gray-600 border border-gray-300 rounded-md cursor-pointer focus:outline-none">
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button"
                            onclick="document.getElementById('modal-import-rekomendasi').classList.add('hidden')"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
