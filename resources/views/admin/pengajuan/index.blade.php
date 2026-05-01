<x-app-layout>
    <x-page-header
        title="Validasi Pengajuan Judul"
        description="Kelola pengajuan judul skripsi tanpa pindah halaman."
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Admin'],
            ['label' => 'Validasi Pengajuan'],
        ]"
    />

    @php
        $statusClasses = [
            'pending' => 'bg-amber-100 text-amber-800 ring-amber-200',
            'disetujui' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            'ditolak' => 'bg-red-100 text-red-800 ring-red-200',
        ];
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="grid grid-cols-1 lg:grid-cols-[360px_minmax(0,1fr)] min-h-[calc(100vh-220px)]">
                    <aside class="border-b lg:border-b-0 lg:border-r border-gray-200 bg-gray-50/70">
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Daftar Pengajuan</h3>
                                    <p class="text-sm text-gray-500">Pending diutamakan di atas.</p>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-gray-900 px-3 py-1 text-xs font-semibold text-white">{{ $pengajuans->count() }}</span>
                            </div>
                        </div>

                        <div class="max-h-[70vh] overflow-y-auto">
                            @forelse ($pengajuans as $pengajuan)
                                <a href="{{ route('admin.pengajuan.index', ['id' => $pengajuan->id]) }}"
                                   class="block border-b border-gray-200 px-4 py-4 transition hover:bg-white {{ (int) $selectedId === $pengajuan->id ? 'bg-sky-50 ring-1 ring-inset ring-sky-200' : '' }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="truncate font-semibold text-gray-900">{{ $pengajuan->mahasiswa?->nama ?? '-' }}</div>
                                            <div class="mt-1 line-clamp-2 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($pengajuan->judul_skripsi, 72) }}</div>
                                        </div>
                                        <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses[$pengajuan->status] ?? 'bg-gray-100 text-gray-700 ring-gray-200' }}">
                                            {{ ucfirst($pengajuan->status) }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div class="p-6 text-sm text-gray-500">Belum ada pengajuan.</div>
                            @endforelse
                        </div>
                    </aside>

                    <section class="p-6 lg:p-8">
                        @if ($selectedPengajuan)
                            <div class="space-y-6" x-data="{ rejectOpen: false }">
                                <div>
                                    <div class="flex flex-wrap items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Mahasiswa</p>
                                            <h2 class="mt-1 text-2xl font-bold text-gray-900">{{ $selectedPengajuan->mahasiswa?->nama ?? '-' }}</h2>
                                            <p class="mt-1 text-sm text-gray-500">{{ $selectedPengajuan->mahasiswa?->nim ?? '-' }}</p>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses[$selectedPengajuan->status] ?? 'bg-gray-100 text-gray-700 ring-gray-200' }}">
                                                {{ ucfirst($selectedPengajuan->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                    <p class="text-sm font-medium text-gray-500">Judul Lengkap</p>
                                    <p class="mt-2 text-base leading-7 text-gray-900">{{ $selectedPengajuan->judul_skripsi }}</p>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                    <p class="text-sm font-medium text-gray-500">File Proposal</p>
                                    <div class="mt-2">
                                        @if ($selectedPengajuan->file_proposal)
                                            <a href="{{ asset('storage/' . $selectedPengajuan->file_proposal) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                                Buka Proposal
                                            </a>
                                        @else
                                            <p class="text-sm text-gray-500">Tidak ada file proposal.</p>
                                        @endif
                                    </div>
                                </div>

                                @if ($selectedPengajuan->alasan_penolakan)
                                    <div class="rounded-xl border border-red-200 bg-red-50 p-5">
                                        <p class="text-sm font-medium text-red-700">Alasan Penolakan</p>
                                        <p class="mt-2 text-sm leading-6 text-red-900">{{ $selectedPengajuan->alasan_penolakan }}</p>
                                    </div>
                                @endif

                                <div class="flex flex-wrap items-center gap-3">
                                    @if ($selectedPengajuan->status === 'pending')
                                        <form action="{{ route('admin.pengajuan.approve', $selectedPengajuan) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                                ACC
                                            </button>
                                        </form>

                                        <button type="button" @click="rejectOpen = !rejectOpen" class="inline-flex items-center rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                                            TOLAK
                                        </button>
                                    @endif
                                </div>

                                <div x-show="rejectOpen" x-transition style="display: none;" class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                                    <form action="{{ route('admin.pengajuan.reject', $selectedPengajuan) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label for="alasan_penolakan" class="block text-sm font-medium text-gray-700">Alasan Penolakan</label>
                                            <textarea id="alasan_penolakan" name="alasan_penolakan" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Kirim Penolakan</button>
                                            <button type="button" @click="rejectOpen = false" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="flex h-full items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50 p-10 text-center">
                                <div>
                                    <p class="text-lg font-semibold text-gray-900">Belum ada pengajuan dipilih</p>
                                    <p class="mt-2 text-sm text-gray-500">Klik salah satu item di sidebar untuk melihat detail.</p>
                                </div>
                            </div>
                        @endif
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>