@props([
    'type' => 'info', // 'info' | 'warning' | 'tip'
    'title' => null,
])

@php
    $config = [
        'info' => [
            'icon' => 'tabler:info-circle',
            'border' => 'border-l-4 border-blue-400',
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'text' => 'text-blue-800 dark:text-blue-200',
            'iconColor' => 'text-blue-500 dark:text-blue-400',
        ],
        'warning' => [
            'icon' => 'tabler:alert-triangle',
            'border' => 'border-l-4 border-amber-400',
            'bg' => 'bg-amber-50 dark:bg-amber-900/20',
            'text' => 'text-amber-800 dark:text-amber-200',
            'iconColor' => 'text-amber-500 dark:text-amber-400',
        ],
        'tip' => [
            'icon' => 'tabler:bulb',
            'border' => 'border-l-4 border-emerald-400',
            'bg' => 'bg-emerald-50 dark:bg-emerald-900/20',
            'text' => 'text-emerald-800 dark:text-emerald-200',
            'iconColor' => 'text-emerald-500 dark:text-emerald-400',
        ],
    ];

    $c = $config[$type] ?? $config['info'];
@endphp

<div class="{{ $c['border'] }} {{ $c['bg'] }} rounded-r-xl p-4 my-6">
    <div class="flex gap-3">
        {{-- Icon --}}
        <div class="flex-shrink-0 mt-0.5">
            <span class="iconify w-5 h-5 {{ $c['iconColor'] }}" data-icon="{{ $c['icon'] }}"></span>
        </div>

        <div class="flex-1">
            {{-- Title (optional) --}}
            @if($title)
                <h4 class="text-sm font-semibold {{ $c['text'] }} mb-1">{{ $title }}</h4>
            @endif

            {{-- Content --}}
            <div class="text-sm {{ $c['text'] }} leading-relaxed [&>p]:mb-2 [&>p:last-child]:mb-0">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
