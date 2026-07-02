@props(['card'])

<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $card['title'] }}</p>
            <div class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ $card['value'] }}</div>
            <p class="mt-2 text-sm text-slate-500">{{ $card['description'] }}</p>
        </div>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $card['iconBg'] ?? 'bg-sky-50' }} {{ $card['iconText'] ?? 'text-sky-700' }}">
            <i class="{{ $card['icon'] }} text-base"></i>
        </div>
    </div>
</div>