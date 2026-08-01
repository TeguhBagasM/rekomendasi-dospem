{{-- Searchable dosen combobox (Alpine.js).
     Param: $name (name form input), $options (koleksi {id, nama, sisa_kuota, skor}),
            $initial (id dosen terpilih), $model ('d1'|'d2'), $disabled (bool), $placeholder. --}}
<div
    x-data="dosenSelect(@js($options), {{ (int) $initial ?: 'null' }}, {{ $disabled ? 'true' : 'false' }})"
    x-modelable="selectedId"
    x-model="{{ $model }}"
    @click.outside="open && close()"
    @scroll.window="open && close()"
    @resize.window="open && close()"
    class="relative min-w-0"
>
    <input type="hidden" name="{{ $name }}" :value="selectedId">

    {{-- Trigger --}}
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        :disabled="disabled"
        class="flex w-full items-center justify-between gap-2 rounded-xl border bg-white px-3 py-2 text-left text-sm shadow-sm transition
               focus:border-gray-900 focus:ring focus:ring-gray-200
               disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400"
        :class="open ? 'border-gray-900 ring-2 ring-gray-200' : 'border-gray-300'"
    >
        <template x-if="selected">
            <span class="min-w-0 truncate font-medium text-gray-900" x-text="selected.nama"></span>
        </template>
        <span x-show="!selected" class="truncate text-gray-400">{{ $placeholder }}</span>

        <span class="flex shrink-0 items-center gap-2">
            <template x-if="selected">
                <span
                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset"
                    :class="selected.sisa_kuota === 0 ? 'bg-red-50 text-red-600 ring-red-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'"
                    x-text="'Sisa: ' + selected.sisa_kuota"
                ></span>
            </template>
            <template x-if="selected">
                <i class="fa-solid fa-xmark text-xs text-gray-400 hover:text-gray-600" @click.stop="clear()"></i>
            </template>
            <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform" :class="open && 'rotate-180'"></i>
        </span>
    </button>

    {{-- Panel dropdown --}}
    <div
        x-show="open"
        x-cloak
        style="display: none;"
        class="fixed z-[70] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xl"
        :style="panelStyle"
    >
        <div class="border-b border-gray-100 p-2">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                <input
                    type="text"
                    x-ref="searchInput"
                    x-model="query"
                    @keydown="onKeydown($event)"
                    placeholder="Cari nama / inisial dosen..."
                    class="w-full rounded-lg border-gray-300 py-2 pl-9 pr-8 text-sm shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-200"
                >
                <button type="button" x-show="query" @click="query = ''"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <div x-ref="panelList" class="max-h-72 overflow-y-auto py-1">
            {{-- Mode pencarian --}}
            <template x-if="query.trim()">
                <div>
                    <ul class="divide-y divide-gray-50">
                        <template x-for="o in matches.slice(0, 8)" :key="o.id">
                            <li>
                                <button
                                    type="button"
                                    :data-option-id="o.id"
                                    @click="select(o.id)"
                                    @mouseenter="highlightedId = o.id"
                                    :class="highlightedId === o.id ? 'bg-sky-50' : ''"
                                    class="flex w-full items-center justify-between gap-3 px-3 py-2.5 text-left text-sm transition hover:bg-sky-50"
                                >
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium text-gray-900" x-text="o.nama"></span>
                                        <span class="block text-xs text-gray-500" x-text="'Sisa Kuota: ' + o.sisa_kuota"></span>
                                    </span>
                                    <span x-show="o.skor !== null"
                                          class="inline-flex shrink-0 items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20"
                                          x-text="'Match: ' + Number(o.skor).toFixed(3)"></span>
                                </button>
                            </li>
                        </template>
                    </ul>
                    <p x-show="matches.length === 0" class="px-3 py-6 text-center text-sm text-gray-500">Tidak ada dosen yang cocok.</p>
                    <p x-show="matches.length > 8" class="px-3 py-2 text-center text-xs text-gray-400">Ketik lebih spesifik untuk hasil lebih baik.</p>
                </div>
            </template>

            {{-- Mode default: rekomendasi teratas + dosen lainnya --}}
            <template x-if="!query.trim()">
                <div>
                    <template x-if="topRanked.length">
                        <div>
                            <div class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Rekomendasi Teratas</div>
                            <ul class="divide-y divide-gray-50">
                                <template x-for="(o, i) in topRanked" :key="o.id">
                                    <li>
                                        <button
                                            type="button"
                                            :data-option-id="o.id"
                                            @click="select(o.id)"
                                            @mouseenter="highlightedId = o.id"
                                            :class="highlightedId === o.id ? 'bg-sky-50' : ''"
                                            class="flex w-full items-center justify-between gap-3 px-3 py-2.5 text-left text-sm transition hover:bg-sky-50"
                                        >
                                            <span class="flex min-w-0 items-center gap-2.5">
                                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-gray-900 text-[10px] font-bold text-white"
                                                      x-text="i + 1"></span>
                                                <span class="min-w-0">
                                                    <span class="block truncate font-medium text-gray-900" x-text="o.nama"></span>
                                                    <span class="block text-xs" :class="o.sisa_kuota === 0 ? 'font-medium text-red-600' : 'text-gray-500'"
                                                          x-text="'Sisa Kuota: ' + o.sisa_kuota"></span>
                                                </span>
                                            </span>
                                            <span class="inline-flex shrink-0 items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20"
                                                  x-text="'Match Skor: ' + Number(o.skor).toFixed(3)"></span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <template x-if="others.length">
                        <div>
                            <div class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Dosen Lainnya</div>
                            <ul class="divide-y divide-gray-50">
                                <template x-for="o in others.slice(0, 5)" :key="o.id">
                                    <li>
                                        <button
                                            type="button"
                                            :data-option-id="o.id"
                                            @click="select(o.id)"
                                            @mouseenter="highlightedId = o.id"
                                            :class="highlightedId === o.id ? 'bg-sky-50' : ''"
                                            class="flex w-full items-center justify-between gap-3 px-3 py-2.5 text-left text-sm transition hover:bg-sky-50"
                                        >
                                            <span class="min-w-0">
                                                <span class="block truncate font-medium text-gray-900" x-text="o.nama"></span>
                                                <span class="block text-xs" :class="o.sisa_kuota === 0 ? 'font-medium text-red-600' : 'text-gray-500'"
                                                      x-text="'Sisa Kuota: ' + o.sisa_kuota"></span>
                                            </span>
                                            <span x-show="o.skor !== null"
                                                  class="inline-flex shrink-0 items-center rounded-full bg-gray-50 px-2 py-0.5 text-[11px] font-medium text-gray-600 ring-1 ring-inset ring-gray-200"
                                                  x-text="'Match: ' + Number(o.skor).toFixed(3)"></span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                            <p x-show="others.length > 5" class="px-3 py-2 text-center text-xs text-gray-400"
                               x-text="'… ' + (others.length - 5) + ' dosen lainnya — ketik untuk mencari'"></p>
                        </div>
                    </template>

                    <p x-show="options.length === 0" class="px-3 py-6 text-center text-sm text-gray-500">Tidak ada dosen tersedia.</p>
                </div>
            </template>
        </div>
    </div>
</div>
