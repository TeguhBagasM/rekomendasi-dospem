<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Rekomendasi Dospem
            </h2>

            <a href="{{ route('rekomendasi.export') }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Export Excel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Judul</th>
                                    <th class="px-4 py-3">Mahasiswa</th>
                                    <th class="px-4 py-3">Dospem 1</th>
                                    <th class="px-4 py-3">Dospem 2</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($pengajuans as $pengajuan)
                                    @php
                                        $recommendation = $recommendations[$pengajuan->id] ?? ['dospem1' => null, 'dospem2' => null];
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-4 align-top">{{ $pengajuan->judul_skripsi }}</td>
                                        <td class="px-4 py-4 align-top">
                                            <div class="font-medium">{{ $pengajuan->mahasiswa?->nama ?? '-' }}</div>
                                            <div class="text-gray-500">{{ $pengajuan->mahasiswa?->nim ?? '-' }} | {{ $pengajuan->mahasiswa?->programStudi?->program_studi ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-4 align-top">{{ $pengajuan->dospem1?->nama ?? ($recommendation['dospem1']?->nama ?? '-') }}</td>
                                        <td class="px-4 py-4 align-top">{{ $pengajuan->dospem2?->nama ?? ($recommendation['dospem2']?->nama ?? '-') }}</td>
                                        <td class="px-4 py-4 align-top">
                                            <form method="POST" action="{{ route('rekomendasi.generate', $pengajuan) }}">
                                                @csrf
                                                <button type="submit" class="rounded-md bg-slate-900 px-3 py-2 text-white hover:bg-slate-700">
                                                    Generate
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada pengajuan disetujui.</td>
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