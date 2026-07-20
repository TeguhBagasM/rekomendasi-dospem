@props(['title', 'dosen'])

<div class="rounded-xl border border-gray-200 bg-slate-50 p-4 shadow-sm">
    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $title }}</div>
    @if ($dosen)
        <div class="mt-3 space-y-2 text-sm text-slate-700">
            <div><span class="font-semibold text-slate-900">Nama:</span> {{ $dosen->nama ?? '-' }}</div>
            <div><span class="font-semibold text-slate-900">NIDN:</span> {{ $dosen->nidn ?? '-' }}</div>
            <div><span class="font-semibold text-slate-900">Program Studi:</span> {{ $dosen->programStudi?->program_studi ?? '-' }}</div>
            <div>
                <span class="font-semibold text-slate-900">No HP:</span>
                @if ($dosen->no_hp)
                    @php
                        $cleanHp = preg_replace('/[^0-9]/', '', $dosen->no_hp);
                        $waNumber = str_starts_with($cleanHp, '62') ? $cleanHp : '62' . ltrim($cleanHp, '0');
                    @endphp
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="text-blue-600 hover:underline">{{ $dosen->no_hp }}</a>
                @else
                    -
                @endif
            </div>
            <div>
                <span class="font-semibold text-slate-900">Email:</span>
                @if ($dosen->user?->email)
                    <a href="mailto:{{ $dosen->user->email }}" class="text-blue-600 hover:underline">{{ $dosen->user->email }}</a>
                @else
                    -
                @endif
            </div>
        </div>
    @else
        <div class="mt-3 text-sm text-slate-500">-</div>
    @endif
</div>