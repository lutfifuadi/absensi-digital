@props([
    'headings' => [], // [['id' => '...', 'text' => '...', 'level' => 2], ...]
    'title' => 'Daftar Isi',
])

@php
    // If no headings passed, component renders nothing meaningful
@endphp

@if(count($headings) > 0)
    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-8"
         x-data="{ activeId: '' }"
         x-init="
            $nextTick(() => {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            activeId = entry.target.id;
                        }
                    });
                }, { rootMargin: '-80px 0px -60% 0px' });

                document.querySelectorAll('[id]').forEach(el => {
                    if (el.id && /^heading-/.test(el.id)) {
                        observer.observe(el);
                    }
                });
            });
         ">

        {{-- Header --}}
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $title }}</h3>
        </div>

        {{-- Heading list --}}
        <nav class="space-y-0.5">
            @foreach($headings as $heading)
                @php
                    $isH3 = ($heading['level'] ?? 2) === 3;
                @endphp
                <a href="#{{ $heading['id'] }}"
                   @click.prevent="
                       const el = document.getElementById('{{ $heading['id'] }}');
                       if (el) {
                           el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                           history.pushState(null, '', '#' + '{{ $heading['id'] }}');
                       }
                   "
                   x-init=""
                   :class="{
                       'text-brand-600 dark:text-brand-400 font-medium border-l-2 border-brand-500': activeId === '{{ $heading['id'] }}',
                       'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 border-l-2 border-transparent hover:border-slate-300 dark:hover:border-slate-600': activeId !== '{{ $heading['id'] }}'
                   }"
                   class="block text-sm py-1 pl-3 transition-all duration-150 {{ $isH3 ? 'ml-3 text-xs' : '' }}">
                    {{ $heading['text'] }}
                </a>
            @endforeach
        </nav>
    </div>
@endif
