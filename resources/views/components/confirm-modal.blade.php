@props([
    'name' => 'delete',
    'title' => 'Konfirmasi',
    'description' => 'Apakah Anda yakin ingin melakukan aksi ini?',
    'type' => 'danger',
])

@php
    $colorMap = [
        'danger' => [
            'iconBg' => 'bg-red-100',
            'iconText' => 'text-red-600',
            'btnBg' => 'bg-red-600',
            'btnHover' => 'hover:bg-red-700',
            'btnFocus' => 'focus:ring-red-500',
            'icon' => 'fa-solid fa-trash-can',
        ],
        'warning' => [
            'iconBg' => 'bg-amber-100',
            'iconText' => 'text-amber-600',
            'btnBg' => 'bg-amber-600',
            'btnHover' => 'hover:bg-amber-700',
            'btnFocus' => 'focus:ring-amber-500',
            'icon' => 'fa-solid fa-triangle-exclamation',
        ],
        'info' => [
            'iconBg' => 'bg-sky-100',
            'iconText' => 'text-sky-600',
            'btnBg' => 'bg-sky-600',
            'btnHover' => 'hover:bg-sky-700',
            'btnFocus' => 'focus:ring-sky-500',
            'icon' => 'fa-solid fa-circle-question',
        ],
        'success' => [
            'iconBg' => 'bg-emerald-100',
            'iconText' => 'text-emerald-600',
            'btnBg' => 'bg-emerald-600',
            'btnHover' => 'hover:bg-emerald-700',
            'btnFocus' => 'focus:ring-emerald-500',
            'icon' => 'fa-solid fa-circle-check',
        ],
        'logout' => [
            'iconBg' => 'bg-orange-100',
            'iconText' => 'text-orange-600',
            'btnBg' => 'bg-orange-600',
            'btnHover' => 'hover:bg-orange-700',
            'btnFocus' => 'focus:ring-orange-500',
            'icon' => 'fa-solid fa-right-from-bracket',
        ],
    ];

    $colors = $colorMap[$type] ?? $colorMap['danger'];
@endphp

<div
    x-data="{
        open: false,
        formId: '',
        title: @js($title),
        description: @js($description),
        submit() {
            const form = document.getElementById(this.formId);
            if (form) {
                form.requestSubmit();
            }
            this.close();
        },
        close() {
            this.open = false;
            this.formId = '';
        }
    }"
    x-on:open-confirm.window="
        if ($event.detail.name === '{{ $name }}') {
            open = true;
            formId = $event.detail.formId || '';
            if ($event.detail.title) title = $event.detail.title;
            if ($event.detail.description) description = $event.detail.description;
        }
    "
    x-on:keydown.escape.window="open && close()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center px-4 py-6"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $name }}-modal-title"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"
        @click="close()"
    ></div>

    {{-- Panel --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        class="relative w-full max-w-md transform rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
    >
        <div class="flex items-start gap-4">
            {{-- Icon --}}
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $colors['iconBg'] }}">
                <i class="{{ $colors['icon'] }} text-xl {{ $colors['iconText'] }}"></i>
            </div>

            {{-- Content --}}
            <div class="min-w-0 flex-1 pt-0.5">
                <h3
                    id="{{ $name }}-modal-title"
                    class="text-lg font-semibold text-gray-900"
                    x-text="title"
                ></h3>
                <p class="mt-2 text-sm text-gray-500 leading-relaxed" x-text="description"></p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-6 flex items-center justify-end gap-3">
            <button
                type="button"
                @click="close()"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
            >
                Batal
            </button>
            <button
                type="button"
                @click="submit()"
                class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $colors['btnBg'] }} {{ $colors['btnHover'] }} {{ $colors['btnFocus'] }}"
            >
                Konfirmasi
            </button>
        </div>
    </div>
</div>
