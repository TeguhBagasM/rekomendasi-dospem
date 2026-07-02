@php
    $user = auth()->user();
    $title = $user?->isAdmin()
        ? 'Dashboard Admin'
        : ($user?->isDosen() ? 'Dashboard Dosen' : ($user?->isMahasiswa() ? 'Dashboard Mahasiswa' : 'Dashboard'));
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">{{ $title }}</h2>
                <p class="mt-1 text-sm text-slate-500">Ringkasan aktivitas dan status terbaru.</p>
            </div>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        @if ($user?->isAdmin())
            @include('dashboard.admin')
        @elseif ($user?->isDosen())
            @include('dashboard.dosen')
        @elseif ($user?->isMahasiswa())
            @include('dashboard.mahasiswa')
        @else
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-600">Akses dashboard tidak tersedia.</p>
            </div>
        @endif
    </div>
</x-app-layout>
