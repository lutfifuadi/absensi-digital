@props([
    'number' => 1,
    'title' => null,
    'active' => false,
])

<div class="flex gap-4 py-3 {{ $active ? 'opacity-100' : 'opacity-85' }}">
    {{-- Step Number Badge --}}
    <div class="flex-shrink-0 relative">
        <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold {{ $active ? 'bg-brand-600 text-white shadow-md shadow-brand-200 dark:shadow-brand-900/30' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">
            {{ $number }}
        </div>
        {{-- Connector line (not for last item — use CSS to hide on last) --}}
        <div class="absolute top-9 left-1/2 -translate-x-1/2 w-0.5 h-full bg-slate-200 dark:bg-slate-700 {{ $attributes->get('class', '') }}" style="display: {{ $attributes->has('last') ? 'none' : 'block' }}; z-index: -1;"></div>
    </div>

    {{-- Step Content --}}
    <div class="flex-1 pb-6">
        @if($title)
            <h4 class="text-base font-semibold text-slate-900 dark:text-white mb-1">{{ $title }}</h4>
        @endif
        <div class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            {{ $slot }}
        </div>
    </div>
</div>
