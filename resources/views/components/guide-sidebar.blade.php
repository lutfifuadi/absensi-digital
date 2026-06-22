@props([
    'categories' => null,
    'activeCategory' => '',
    'activeArticle' => '',
])

@php
    // If categories not passed as prop, inject from service
    if ($categories === null) {
        $guideService = app(\App\Services\GuideService::class);
        $sidebarCategories = $guideService->getCategoriesWithGuideCount();
    } else {
        $sidebarCategories = $categories;
    }
@endphp

<div class="space-y-1" x-data="{ activeCategory: '{{ $activeCategory }}', activeArticle: '{{ $activeArticle }}' }">
    {{-- Sidebar Header --}}
    <div class="mb-4">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kategori Panduan</h3>
    </div>

    @forelse($sidebarCategories as $category)
        @php
            $catIcon = $category->icon ? ('tabler:' . $category->icon) : 'tabler:folder';
        @endphp
        <div x-data="{ open: '{{ $activeCategory }}' === '{{ $category->slug }}' }" class="mb-1">
            {{-- Category Header --}}
            <a href="{{ route('guide.category', $category->slug) }}"
               @click.prevent="open = !open"
               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150"
               :class="{
                   'bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300': '{{ $activeCategory }}' === '{{ $category->slug }}',
                   'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200': '{{ $activeCategory }}' !== '{{ $category->slug }}'
               }">
                <span class="flex items-center gap-2.5">
                    <span class="iconify w-4 h-4" data-icon="{{ $catIcon }}"></span>
                    <span>{{ $category->name }}</span>
                    @if($category->guides_count > 0)
                        <span class="text-xs text-slate-400 dark:text-slate-500">({{ $category->guides_count }})</span>
                    @endif
                </span>
                <svg class="w-4 h-4 transition-transform duration-200"
                     :class="{ 'rotate-90': open }"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Sub-articles loaded dynamically via guides count --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="ml-3 mt-1 space-y-0.5 border-l-2 border-slate-200 dark:border-slate-700 pl-3">

                @if($category->guides_count > 0)
                    @php
                        // Load guides for this category for sidebar navigation links
                        $categoryGuides = app(\App\Services\GuideService::class)->getGuidesByCategory($category, 20);
                    @endphp
                    @foreach($categoryGuides as $guide)
                        <a href="{{ route('guide.show', [$category->slug, $guide->slug]) }}"
                           class="block px-3 py-1.5 text-sm rounded-md transition-colors duration-150"
                           :class="{
                               'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/20 font-medium': '{{ $activeArticle }}' === '{{ $guide->slug }}',
                               'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50': '{{ $activeArticle }}' !== '{{ $guide->slug }}'
                           }">
                            {{ $guide->title }}
                        </a>
                    @endforeach
                @else
                    <p class="px-3 py-1.5 text-xs text-slate-400 dark:text-slate-500 italic">Belum ada artikel</p>
                @endif
            </div>
        </div>
    @empty
        {{-- Fallback: static categories when DB is empty --}}
        @php
            $fallback = [
                (object)['slug' => 'pengaturan-akun', 'icon' => 'user-cog', 'name' => 'Pengaturan Akun', 'guides_count' => 3],
                (object)['slug' => 'absensi-siswa', 'icon' => 'clipboard-check', 'name' => 'Absensi Siswa', 'guides_count' => 3],
                (object)['slug' => 'absensi-guru', 'icon' => 'chalkboard', 'name' => 'Absensi Guru', 'guides_count' => 2],
                (object)['slug' => 'laporan', 'icon' => 'chart-bar', 'name' => 'Laporan & Statistik', 'guides_count' => 2],
                (object)['slug' => 'notifikasi', 'icon' => 'bell', 'name' => 'Notifikasi & WA Gateway', 'guides_count' => 2],
                (object)['slug' => 'pengaturan', 'icon' => 'settings', 'name' => 'Pengaturan Sistem', 'guides_count' => 2],
            ];
        @endphp
        @foreach($fallback as $cat)
            <div x-data="{ open: '{{ $activeCategory }}' === '{{ $cat->slug }}' }" class="mb-1">
                <a href="{{ route('guide.category', $cat->slug) }}"
                   @click.prevent="open = !open"
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150"
                   :class="{
                       'bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300': '{{ $activeCategory }}' === '{{ $cat->slug }}',
                       'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200': '{{ $activeCategory }}' !== '{{ $cat->slug }}'
                   }">
                    <span class="flex items-center gap-2.5">
                        <span class="iconify w-4 h-4" data-icon="tabler:{{ $cat->icon }}"></span>
                        <span>{{ $cat->name }}</span>
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200"
                         :class="{ 'rotate-90': open }"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @endforeach
    @endforelse

    {{-- Separator --}}
    <div class="my-4 border-t border-slate-200 dark:border-slate-700"></div>

    {{-- Additional Links --}}
    <a href="{{ route('guide.index') }}"
       class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span>Beranda Panduan</span>
    </a>
    <a href="{{ route('public.bantuan') }}"
       class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
        <span>Hubungi Support</span>
    </a>
</div>
