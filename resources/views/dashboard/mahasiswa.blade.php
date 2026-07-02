@php
    $summaryCards = $summaryCards ?? [];
    $timeline = $timeline ?? collect();
@endphp

<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($summaryCards as $card)
            <x-dashboard.metric-card :card="$card" />
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Timeline Mahasiswa</h3>
                    <p class="text-xs text-slate-500">Status proses skripsi</p>
                </div>
            </div>

            @if ($timeline->isEmpty())
                <x-dashboard.empty-state title="Belum ada data." description="Timeline belum bisa ditampilkan." icon="fa-solid fa-route" />
            @else
                <div class="space-y-4">
                    @foreach ($timeline as $item)
                        <div class="flex gap-4">
                            <div class="relative flex flex-col items-center">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full border {{ $item['done'] ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 bg-white text-gray-400' }}">
                                    <i class="fa-solid fa-circle text-[8px]"></i>
                                </span>
                                @if (! $loop->last)
                                    <span class="mt-1 h-full w-px bg-gray-200"></span>
                                @endif
                            </div>
                            <div class="pb-4">
                                <div class="text-sm font-semibold {{ $item['done'] ? 'text-slate-900' : 'text-slate-500' }}">{{ $item['label'] }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $item['description'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-slate-900">Informasi Dosen Pembimbing</h3>
                    <p class="text-xs text-slate-500">Menampilkan penetapan final jika tersedia</p>
                </div>

                @if (! $hasPenetapan)
                    <x-dashboard.empty-state title="Belum ada dosen pembimbing yang ditetapkan." description="Tunggu proses finalisasi penetapan dosen." icon="fa-solid fa-user-slash" />
                @else
                    <div class="space-y-4">
                        <x-dashboard.supervisor-card title="Dospem 1" :dosen="$dospem1" />
                        <x-dashboard.supervisor-card title="Dospem 2" :dosen="$dospem2" />
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>