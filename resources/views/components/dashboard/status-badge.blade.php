@props(['status'])

@php
    $badgeClass = match ($status) {
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'ditolak' => 'bg-rose-50 text-rose-700 ring-rose-200',
        default => 'bg-slate-100 text-slate-700 ring-slate-200',
    };

    $label = match ($status) {
        'pending' => 'Pending',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
        default => ucfirst((string) $status),
    };
@endphp

<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset {{ $badgeClass }}">{{ $label }}</span>