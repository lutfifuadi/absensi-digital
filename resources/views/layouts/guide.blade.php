<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="guideApp()"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'Aplikasi Presensi') }}</title>
    <meta name="description" content="@yield('meta_description', 'Panduan lengkap Aplikasi Presensi')">

    {{-- Tailwind CSS via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                    },
                    maxWidth: {
                        '4xl': '56rem',
                    },
                },
            },
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Iconify for Tabler Icons --}}
    <script src="https://cdn.jsdelivr.net/npm/@iconify/iconify@4.x/dist/iconify.min.js"></script>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @stack('styles')
</head>
<body class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans antialiased">

    <script>
        // Dark mode init — prevent flicker
        if (localStorage.getItem('guide-theme') === 'dark' || (!('guide-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    {{-- ===== Alpine.js Root State ===== --}}
    <script>
        function guideApp() {
            return {
                darkMode: document.documentElement.classList.contains('dark'),
                sidebarOpen: false,
                searchQuery: '',
                searchResults: [],
                showSearch: false,

                init() {
                    this.$watch('darkMode', val => {
                        document.documentElement.classList.toggle('dark', val);
                        localStorage.setItem('guide-theme', val ? 'dark' : 'light');
                    });
                },

                toggleDark() {
                    this.darkMode = !this.darkMode;
                },

                toggleSidebar() {
                    this.sidebarOpen = !this.sidebarOpen;
                },

                closeSidebar() {
                    this.sidebarOpen = false;
                },

                performSearch(query) {
                    this.searchQuery = query;
                    if (query.length < 2) {
                        this.searchResults = [];
                        this.showSearch = false;
                        return;
                    }
                    // Dispatch custom event for search — the search component handles filtering
                    this.showSearch = true;
                },

                goToSearchResult(url) {
                    window.location.href = url;
                }
            };
        }
    </script>

    {{-- ===== HEADER ===== --}}
    <header class="sticky top-0 z-40 w-full border-b border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Left: Hamburger (mobile) + Logo --}}
                <div class="flex items-center gap-3">
                    <button @click="toggleSidebar()"
                            class="lg:hidden inline-flex items-center justify-center p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            aria-label="Buka sidebar navigasi">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <a href="{{ route('guide.index') }}" class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white">
                        <svg class="w-7 h-7 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span>Panduan</span>
                    </a>
                </div>

                {{-- Center: Search bar (desktop) --}}
                <div class="hidden md:flex flex-1 max-w-lg mx-6">
                    <x-guide-search />
                </div>

                {{-- Right: Actions --}}
                <div class="flex items-center gap-2">
                    {{-- Search icon (mobile) --}}
                    <button @click="showSearch = !showSearch"
                            class="md:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                            aria-label="Cari panduan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    {{-- Dark mode toggle --}}
                    <button @click="toggleDark()"
                            class="p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            aria-label="Toggle dark mode"
                            x-text="darkMode ? '☀️' : '🌙'">
                    </button>
                </div>
            </div>

            {{-- Mobile search (expandable) --}}
            <div x-show="showSearch"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="md:hidden pb-4">
                <x-guide-search />
            </div>
        </div>
    </header>

    {{-- ===== MAIN LAYOUT ===== --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen"
             @click="closeSidebar()"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm lg:hidden">
        </div>

        <div class="flex gap-8 py-8">

            @php
                $activeCategory = $activeCategory ?? '';
                $activeArticle = $activeArticle ?? '';
            @endphp

            {{-- ===== SIDEBAR (Left Column) ===== --}}
            <aside class="w-64 flex-shrink-0 hidden lg:block">
                <div class="sticky top-24">
                    <x-guide-sidebar :activeCategory="$activeCategory" :activeArticle="$activeArticle" />
                </div>
            </aside>

            {{-- Mobile sidebar drawer --}}
            <aside x-show="sidebarOpen"
                   x-transition:enter="transition ease-out duration-300"
                   x-transition:enter-start="-translate-x-full"
                   x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition ease-in duration-200"
                   x-transition:leave-start="translate-x-0"
                   x-transition:leave-end="-translate-x-full"
                   class="fixed inset-y-0 left-0 z-40 w-72 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 shadow-xl lg:hidden overflow-y-auto">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-bold text-lg text-slate-900 dark:text-white">Panduan</span>
                        <button @click="closeSidebar()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <x-guide-sidebar :activeCategory="$activeCategory" :activeArticle="$activeArticle" />
                </div>
            </aside>

            {{-- ===== CONTENT (Right Column) ===== --}}
            <main class="flex-1 min-w-0 max-w-4xl">
                {{-- Breadcrumb --}}
                <x-guide-breadcrumb :items="$breadcrumbItems ?? []" />

                {{-- Page Content --}}
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ===== FOOTER ===== --}}
    <footer class="border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Aplikasi Presensi') }}. All rights reserved.
                </p>
                <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                    <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <span>Butuh bantuan? <a href="{{ route('public.bantuan') }}" class="text-brand-600 dark:text-brand-400 hover:underline font-medium">Hubungi support</a></span>
                </div>
            </div>
        </div>
    </footer>

    {{-- Scroll to top button --}}
    <button x-data="{ visible: false }"
            x-init="window.addEventListener('scroll', () => visible = window.scrollY > 400)"
            x-show="visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-6 right-6 z-50 p-3 rounded-full bg-brand-600 text-white shadow-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-colors"
            aria-label="Scroll to top">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    @stack('scripts')
</body>
</html>
