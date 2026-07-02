@props(['title', 'description', 'icon' => 'fa-solid fa-circle-info'])

<div class="rounded-xl border border-dashed border-gray-200 bg-slate-50 px-5 py-8 text-center shadow-sm">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="mt-4 text-sm font-semibold text-slate-900">{{ $title }}</div>
    <div class="mt-1 text-sm text-slate-500">{{ $description }}</div>
</div>