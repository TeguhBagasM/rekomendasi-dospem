@props(['title', 'dosen'])

<div class="rounded-xl border border-gray-200 bg-slate-50 p-4 shadow-sm">
    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $title }}</div>
    @if ($dosen)
        <div class="mt-3 space-y-2 text-sm text-slate-700">
            <div><span class="font-semibold text-slate-900">Nama:</span> {{ $dosen->nama ?? '-' }}</div>
            <div><span class="font-semibold text-slate-900">NIDN:</span> {{ $dosen->nidn ?? '-' }}</div>
            <div><span class="font-semibold text-slate-900">Jabatan Fungsional:</span> {{ $dosen->jabatan_fungsional ?? '-' }}</div>
            <div><span class="font-semibold text-slate-900">Program Studi:</span> {{ $dosen->programStudi?->program_studi ?? '-' }}</div>
            <div><span class="font-semibold text-slate-900">No HP:</span> {{ $dosen->no_hp ?? '-' }}</div>
            <div>
                <span class="font-semibold text-slate-900">Bidang Keahlian:</span>
                {{ $dosen->bidangKeahlians?->pluck('nama')->filter()->implode(', ') ?: '-' }}
            </div>
        </div>
    @else
        <div class="mt-3 text-sm text-slate-500">-</div>
    @endif
</div>