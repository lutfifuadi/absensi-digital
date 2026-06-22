@props([
    'items' => [], // [['label' => '...', 'url' => '...'], ...]
])

<nav aria-label="Breadcrumb" class="mb-6">
    <ol class="flex items-center flex-wrap gap-1.5 text-sm">
        {{-- Home --}}
        <li>
            <a href="{{ route('guide.index') }}" class="text-slate-400 dark:text-slate-500 hover:text-brand-600 dark:hover:text-brand-400 transition-colors duration-150 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="hidden sm:inline">Beranda</span>
            </a>
        </li>

        {{-- Dynamic breadcrumb items --}}
        @if(count($items) > 0)
            @foreach($items as $index => $item)
                <li class="flex items-center gap-1.5">
                    {{-- Separator --}}
                    <svg class="w-4 h-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>

                    @if($loop->last || !($item['url'] ?? false))
                        {{-- Current page (not clickable) --}}
                        <span class="text-slate-700 dark:text-slate-300 font-medium truncate max-w-[200px]" aria-current="page">
                            {{ $item['label'] }}
                        </span>
                    @else
                        {{-- Parent page (clickable) --}}
                        <a href="{{ $item['url'] }}" class="text-slate-400 dark:text-slate-500 hover:text-brand-600 dark:hover:text-brand-400 transition-colors duration-150 truncate max-w-[200px]">
                            {{ $item['label'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        @endif
    </ol>
</nav>
