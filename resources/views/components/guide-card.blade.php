@props([
    'title',
    'description' => '',
    'icon' => null,
    'href' => '#',
    'articleCount' => null,
    'variant' => 'category', // 'category' | 'article' | 'featured'
    'roles' => [],
])

@php
    $variantClasses = [
        'category' => 'border-t-4 border-brand-500',
        'article' => 'border-l-4 border-brand-400',
        'featured' => 'border-t-4 border-amber-400 ring-1 ring-amber-200 dark:ring-amber-800',
    ];
@endphp

<a href="{{ $href }}"
   class="group block p-5 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 {{ $variantClasses[$variant] ?? $variantClasses['category'] }} hover:shadow-md hover:border-brand-300 dark:hover:border-brand-700 transition-all duration-200">

    <div class="flex items-start gap-4">
        {{-- Icon --}}
        @if($icon)
            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 group-hover:scale-110 transition-transform duration-200">
                <span class="iconify w-5 h-5" data-icon="{{ $icon }}"></span>
            </div>
        @endif

        <div class="flex-1 min-w-0">
            {{-- Title --}}
            <h3 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors duration-150">
                {{ $title }}
            </h3>

            {{-- Description --}}
            @if($description)
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 line-clamp-2">
                    {{ $description }}
                </p>
            @endif

            {{-- Article count or Roles --}}
            <div class="mt-2 flex items-center gap-3 flex-wrap">
                @if($articleCount !== null)
                    <span class="inline-flex items-center gap-1 text-xs text-slate-400 dark:text-slate-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ $articleCount }} artikel
                    </span>
                @endif

                @if(count($roles) > 0)
                    <div class="flex flex-wrap gap-1">
                        @foreach($roles as $role)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                {{ $role }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Arrow --}}
        <svg class="w-5 h-5 flex-shrink-0 text-slate-300 dark:text-slate-600 group-hover:text-brand-500 group-hover:translate-x-0.5 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
</a>
