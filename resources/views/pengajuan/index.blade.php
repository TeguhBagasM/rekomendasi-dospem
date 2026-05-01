<x-app-layout>
    <x-page-header
        title="Pengajuan Judul Skripsi"
        description="Kelola pengajuan judul dan proposal skripsi."
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Pengajuan Judul'],
        ]"
    />

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Pengajuan</h3>
                            <p class="text-sm text-gray-500">Total pengajuan: {{ $pengajuans->count() }}</p>
                        </div>

                        @if (auth()->user()->isMahasiswa())
                            <a href="{{ route('pengajuan.create') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                <i class="fa-solid fa-plus mr-2"></i> Tambah Pengajuan
                            </a>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="{{ $pengajuans->isNotEmpty() ? 'js-datatable' : '' }} min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-white bg-gray-900">
                                    <th class="px-4 py-3">Mahasiswa</th>
                                    <th class="px-4 py-3">Judul</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($pengajuans as $pengajuan)
                                    <tr class="text-sm text-gray-700">
                                        <td class="px-4 py-4">
                                            <div class="font-medium text-gray-900">{{ $pengajuan->mahasiswa?->nama ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ $pengajuan->mahasiswa?->nim ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-4">{{ $pengajuan->judul_skripsi }}</td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-{{ $pengajuan->status_badge }}-100 text-{{ $pengajuan->status_badge }}-700">
                                                {{ ucfirst($pengajuan->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end gap-3 flex-wrap">
                                                <a href="{{ route('pengajuan.show', $pengajuan) }}" class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                                    <i class="fa-solid fa-eye mr-1"></i> Detail
                                                </a>

                                                @if ((auth()->user()->isAdmin() || auth()->user()->isKaprodi() || (auth()->user()->isMahasiswa() && $pengajuan->mahasiswa?->user_id === auth()->id())) && $pengajuan->isPending())
                                                    <a href="{{ route('pengajuan.edit', $pengajuan) }}" class="inline-flex items-center font-medium text-emerald-600 hover:text-emerald-800">
                                                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                    </a>

                                                    <form action="{{ route('pengajuan.destroy', $pengajuan) }}" method="POST" onsubmit="return confirm('Hapus pengajuan ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center font-medium text-red-600 hover:text-red-800">
                                                            <i class="fa-solid fa-trash mr-1"></i> Hapus
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ((auth()->user()->isAdmin() || auth()->user()->isKaprodi()) && $pengajuan->isPending())
                                                    <form action="{{ route('pengajuan.approve', $pengajuan) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center font-medium text-blue-600 hover:text-blue-800">
                                                            <i class="fa-solid fa-check mr-1"></i> Approve
                                                        </button>
                                                    </form>

                                                    <button type="button" class="inline-flex items-center font-medium text-amber-600 hover:text-amber-800" data-bs-toggle="collapse" data-bs-target="#rejectForm{{ $pengajuan->id }}" aria-expanded="false" aria-controls="rejectForm{{ $pengajuan->id }}">
                                                        <i class="fa-solid fa-xmark mr-1"></i> Reject
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    @if ((auth()->user()->isAdmin() || auth()->user()->isKaprodi()) && $pengajuan->isPending())
                                        <tr class="bg-gray-50">
                                            <td colspan="4" class="px-4 py-0">
                                                <div id="rejectForm{{ $pengajuan->id }}" class="collapse py-4">
                                                    <form action="{{ route('pengajuan.reject', $pengajuan) }}" method="POST" class="space-y-3 rounded-lg border border-amber-200 bg-white p-4">
                                                        @csrf
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label>
                                                            <textarea name="alasan_penolakan" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                                                        </div>
                                                        <div class="flex items-center gap-3">
                                                            <button type="submit" class="inline-flex items-center rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">Tolak</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada pengajuan.</td>
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