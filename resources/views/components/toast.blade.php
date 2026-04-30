@props([
    'type' => 'success',
    'title' => null,
    'message',
])

@php
    $isSuccess = $type === 'success';
    $title = $title ?? ($isSuccess ? 'Berhasil' : 'Gagal');
    $wrapperClasses = $isSuccess
        ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
        : 'border-rose-200 bg-rose-50 text-rose-900';
    $iconClasses = $isSuccess
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-rose-100 text-rose-700';
    $messageClasses = $isSuccess
        ? 'text-emerald-800'
        : 'text-rose-800';
    $icon = $isSuccess ? 'fa-circle-check' : 'fa-circle-xmark';
@endphp

<div
    x-data="{ open: true }"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
    x-init="setTimeout(() => open = false, 3500)"
    class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-2xl border shadow-lg"
    role="alert"
>
    <div class="flex items-start gap-3 px-4 py-3 {{ $wrapperClasses }}">
        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $iconClasses }}">
            <i class="fa-solid {{ $icon }} text-base"></i>
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold leading-5">{{ $title }}</p>
            <p class="mt-1 text-sm leading-5 {{ $messageClasses }}">{{ $message }}</p>
        </div>

        <button type="button" @click="open = false" class="ml-2 inline-flex h-8 w-8 items-center justify-center rounded-full text-current/70 transition hover:bg-black/5 hover:text-current" aria-label="Tutup notifikasi">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>
</div>