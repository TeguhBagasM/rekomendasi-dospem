<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Rekomendasi Dospem
            </h2>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('rekomendasi.generate') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                        Generate Batch
                    </button>
                </form>

                <a href="{{ route('rekomendasi.export') }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Export Excel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
             @if (session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                    <div class="font-semibold mb-2">Terjadi kesalahan pada file yang di-import:</div>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <form method="POST" action="{{ route('rekomendasi.import') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Import Excel Final</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="mt-1 block w-full text-sm text-gray-700" required>
                    </div>
                    <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
                        Import Finalisasi
                    </button>
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Judul</th>
                                    <th class="px-4 py-3">Mahasiswa</th>
                                    <th class="px-4 py-3">Rekomendasi Dospem 1</th>
                                    <th class="px-4 py-3">Rekomendasi Dospem 2</th>
                                    <th class="px-4 py-3">Dospem 1 Final</th>
                                    <th class="px-4 py-3">Dospem 2 Final</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($pengajuans as $pengajuan)
                                    @php
                                        $recommendation = $recommendations[$pengajuan->id] ?? null;
                                        $finalAssignment = $pengajuan->penetapanDospembing;
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-4 align-top">{{ $pengajuan->judul_skripsi }}</td>
                                        <td class="px-4 py-4 align-top">
                                            <div class="font-medium">{{ $pengajuan->mahasiswa?->nama ?? '-' }}</div>
                                            <div class="text-gray-500">{{ $pengajuan->mahasiswa?->nim ?? '-' }} | {{ $pengajuan->mahasiswa?->programStudi?->program_studi ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-4 align-top">{{ $recommendation?->rekomendasiDospem1?->nama ?? '-' }}</td>
                                        <td class="px-4 py-4 align-top">{{ $recommendation?->rekomendasiDospem2?->nama ?? '-' }}</td>
                                        <td class="px-4 py-4 align-top">{{ $finalAssignment?->dospem1?->nama ?? '-' }}</td>
                                        <td class="px-4 py-4 align-top">{{ $finalAssignment?->dospem2?->nama ?? '-' }}</td>
                                        <td class="px-4 py-4 align-top">
                                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $finalAssignment ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ $finalAssignment ? 'Final' : 'Menunggu Import' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada pengajuan disetujui.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>