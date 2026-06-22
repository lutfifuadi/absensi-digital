@props([
    'prev' => null, // ['slug' => '...', 'title' => '...', 'category_slug' => '...'] or null
    'next' => null, // ['slug' => '...', 'title' => '...', 'category_slug' => '...'] or null
])

<div class="flex flex-col sm:flex-row gap-4 mt-10 pt-8 border-t border-slate-200 dark:border-slate-700">
    {{-- Previous Article --}}
    <div class="flex-1">
        @if($prev)
            <a href="{{ route('guide.show', [$prev['category_slug'] ?? '', $prev['slug']]) }}"
               class="group block p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-brand-300 dark:hover:border-brand-700 hover:bg-brand-50/30 dark:hover:bg-brand-900/10 transition-all duration-200">
                <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500 mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span>Sebelumnya</span>
                </div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors duration-150 truncate">
                    {{ $prev['title'] }}
                </p>
            </a>
        @else
            <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500 mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span>Sebelumnya</span>
                </div>
                <p class="text-sm text-slate-400 dark:text-slate-500 italic">Ini artikel pertama</p>
            </div>
        @endif
    </div>

    {{-- Next Article --}}
    <div class="flex-1">
        @if($next)
            <a href="{{ route('guide.show', [$next['category_slug'] ?? '', $next['slug']]) }}"
               class="group block p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-brand-300 dark:hover:border-brand-700 hover:bg-brand-50/30 dark:hover:bg-brand-900/10 transition-all duration-200 text-right">
                <div class="flex items-center justify-end gap-2 text-xs text-slate-400 dark:text-slate-500 mb-1">
                    <span>Selanjutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors duration-150 truncate">
                    {{ $next['title'] }}
                </p>
            </a>
        @else
            <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-right">
                <div class="flex items-center justify-end gap-2 text-xs text-slate-400 dark:text-slate-500 mb-1">
                    <span>Selanjutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <p class="text-sm text-slate-400 dark:text-slate-500 italic">Tidak ada artikel lagi</p>
            </div>
        @endif
    </div>
</div>
