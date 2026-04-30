@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
    'icon' => null,
])

<div class="border-b bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            @foreach ($breadcrumbs as $breadcrumb)
                @if (! $loop->first)
                    <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                @endif

                @if (! $loop->last && filled($breadcrumb['url'] ?? null))
                    <a href="{{ $breadcrumb['url'] }}" class="font-medium text-gray-500 hover:text-gray-700">
                        {{ $breadcrumb['label'] }}
                    </a>
                @else
                    <span class="font-medium text-gray-700">{{ $breadcrumb['label'] }}</span>
                @endif
            @endforeach
        </nav>

        <div class="mt-3 flex items-center gap-4">
            @if ($icon)
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                    <i class="fa-solid {{ $icon }} text-lg"></i>
                </div>
            @endif

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $title }}</h1>
                @if ($description)
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600">{{ $description }}</p>
                @endif
            </div>
        </div>
    </div>
</div>