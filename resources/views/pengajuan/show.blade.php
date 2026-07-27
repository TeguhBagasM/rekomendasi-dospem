<x-app-layout>
    <x-page-header
        title="Detail Pengajuan"
        description="Informasi lengkap pengajuan judul skripsi."
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Pengajuan Judul', 'url' => route('pengajuan.index')],
            ['label' => 'Detail Pengajuan'],
        ]"
    />

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6" x-data>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Detail Pengajuan</h3>
                            <p class="text-sm text-gray-500">{{ $pengajuan->mahasiswa?->nama ?? '-' }}</p>
                        </div>
                        <a href="{{ route('pengajuan.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Kembali</a>
                    </div>

                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm font-medium text-gray-500">Nama Mahasiswa</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pengajuan->mahasiswa?->nama ?? '-' }}</dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm font-medium text-gray-500">NIM</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pengajuan->mahasiswa?->nim ?? '-' }}</dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4 sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Judul</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pengajuan->judul_skripsi }}</dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm font-medium text-gray-500">File Proposal</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if ($pengajuan->file_proposal)
                                    <a href="{{ asset('storage/' . $pengajuan->file_proposal) }}" target="_blank" rel="noopener" class="font-medium text-indigo-600 hover:text-indigo-800">Download proposal</a>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-{{ $pengajuan->status_badge }}-100 text-{{ $pengajuan->status_badge }}-700">
                                    {{ ucfirst($pengajuan->status) }}
                                </span>
                            </dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4 sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Alasan Penolakan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pengajuan->alasan_penolakan ?? '-' }}</dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm font-medium text-gray-500">Validator</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pengajuan->validator?->name ?? '-' }}</dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <dt class="text-sm font-medium text-gray-500">Tanggal Validasi</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $pengajuan->validated_at?->format('d M Y H:i') ?? '-' }}</dd>
                        </div>

                        @php
                            $penetapan = $pengajuan->penetapanDospembing;
                        @endphp

                        @if ($penetapan?->dospem1 || $penetapan?->dospem2)
                            <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 sm:col-span-2">
                                <h4 class="text-sm font-semibold text-indigo-900 mb-2">Dosen Pembimbing Final</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 bg-white rounded-md p-3 border border-indigo-100">
                                    <div>
                                        <span class="text-xs font-medium text-indigo-500 block">Dosen Pembimbing 1</span>
                                        <span class="text-sm font-semibold text-gray-900 block mt-1">{{ $penetapan?->dospem1?->nama ?? '-' }}</span>
                                        <span class="text-xs text-gray-500 block mt-0.5">NIDN: {{ $penetapan?->dospem1?->nidn ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs font-medium text-indigo-500 block">Dosen Pembimbing 2</span>
                                        <span class="text-sm font-semibold text-gray-900 block mt-1">{{ $penetapan?->dospem2?->nama ?? '-' }}</span>
                                        <span class="text-xs text-gray-500 block mt-0.5">NIDN: {{ $penetapan?->dospem2?->nidn ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </dl>

                    <div class="flex flex-wrap items-center gap-3">
                        @if ((auth()->user()->isAdmin() || auth()->user()->isDosen() || (auth()->user()->isMahasiswa() && $pengajuan->mahasiswa?->user_id === auth()->id())) && $pengajuan->isPending())
                            <a href="{{ route('pengajuan.edit', $pengajuan) }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Edit</a>

                            <form id="delete-pengajuan-show-{{ $pengajuan->id }}" action="{{ route('pengajuan.destroy', $pengajuan) }}" method="POST">
                                @csrf
                                @method('DELETE')
                            </form>
                            <button type="button"
                                    @click="$dispatch('open-confirm', { name: 'delete', formId: 'delete-pengajuan-show-{{ $pengajuan->id }}', title: 'Hapus Pengajuan', description: 'Apakah Anda yakin ingin menghapus pengajuan ini? Data yang sudah dihapus tidak dapat dikembalikan.' })"
                                    class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                Hapus
                            </button>
                        @endif

                        @if ((auth()->user()->isAdmin() || auth()->user()->isDosen()) && $pengajuan->isPending())
                            <form id="approve-pengajuan-show-{{ $pengajuan->id }}" action="{{ route('admin.validasi-pengajuan.approve', $pengajuan) }}" method="POST">
                                @csrf
                            </form>
                            <button type="button"
                                    @click="$dispatch('open-confirm', { name: 'success', formId: 'approve-pengajuan-show-{{ $pengajuan->id }}', title: 'Setujui Pengajuan', description: 'Apakah Anda yakin ingin menyetujui pengajuan ini?' })"
                                    class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                Approve
                            </button>

                            <button type="button" x-data x-on:click="$dispatch('open-reject')" class="inline-flex items-center rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                                Reject
                            </button>
                        @endif
                    </div>

                    @if ((auth()->user()->isAdmin() || auth()->user()->isDosen()) && $pengajuan->isPending())
                        <div x-data="{ open: false }" x-on:open-reject.window="open = true" x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6" style="display: none;">
                            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="text-lg font-semibold text-gray-900">Tolak Pengajuan</h4>
                                    <button type="button" x-on:click="open = false" class="text-gray-500 hover:text-gray-700">&times;</button>
                                </div>

                                <form action="{{ route('admin.validasi-pengajuan.reject', $pengajuan) }}" method="POST" class="mt-4 space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label>
                                        <textarea name="alasan_penolakan" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                                    </div>

                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" x-on:click="open = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                                        <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Tolak</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>