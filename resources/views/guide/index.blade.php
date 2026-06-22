@extends('layouts.guide')

@section('title', 'Panduan Aplikasi Presensi')

@section('meta_description', 'Panduan lengkap penggunaan Aplikasi Presensi untuk semua pengguna — admin, guru, siswa, dan orang tua.')

@php
    $activeCategory = '';
    $activeArticle = '';
    $breadcrumbItems = [];
@endphp

@section('content')
    {{-- ===== HERO SECTION ===== --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-8 sm:p-12 mb-10">
        {{-- Subtle pattern overlay --}}
        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.3) 1px, transparent 0); background-size: 20px 20px;"></div>

        <div class="relative z-10 text-center max-w-2xl mx-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/20 text-brand-300 text-xs font-medium mb-4 border border-brand-500/30">
                <span class="iconify w-3.5 h-3.5" data-icon="tabler:book"></span>
                <span>Panduan Lengkap</span>
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-4">
                Selamat Datang di<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-300 to-brand-100">Panduan Presensi</span>
            </h1>

            <p class="text-base sm:text-lg text-slate-300 mb-6 max-w-lg mx-auto leading-relaxed">
                Pelajari cara menggunakan semua fitur Aplikasi Presensi. Pilih kategori di bawah atau cari langsung.
            </p>

            {{-- Search in hero --}}
            <div class="max-w-md mx-auto">
                <x-guide-search placeholder="Cari panduan, fitur, atau cara..." />
            </div>
        </div>
    </section>

    {{-- ===== KATEGORI GRID ===== --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Kategori Panduan</h2>
            <span class="text-sm text-slate-400 dark:text-slate-500">{{ $categories->count() }} kategori</span>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($categories as $category)
                <x-guide-card
                    :title="$category->name"
                    :description="$category->description ?? 'Pelajari lebih lanjut tentang ' . $category->name"
                    :icon="$category->icon ? ('tabler:' . $category->icon) : 'tabler:folder'"
                    :href="route('guide.category', $category->slug)"
                    :articleCount="$category->guides_count ?? 0"
                    variant="category" />
            @empty
                {{-- Fallback if no categories from DB --}}
                @php
                    $fallbackCategories = [
                        (object)['slug' => 'pengaturan-akun', 'icon' => 'user-cog', 'name' => 'Pengaturan Akun', 'description' => 'Kelola akun, password, dan profil pengguna', 'guides_count' => 3],
                        (object)['slug' => 'absensi-siswa', 'icon' => 'clipboard-check', 'name' => 'Absensi Siswa', 'description' => 'Panduan absensi siswa via QR dan mandiri', 'guides_count' => 3],
                        (object)['slug' => 'absensi-guru', 'icon' => 'chalkboard', 'name' => 'Absensi Guru', 'description' => 'Absensi dan pengajuan izin untuk guru', 'guides_count' => 2],
                        (object)['slug' => 'laporan', 'icon' => 'chart-bar', 'name' => 'Laporan & Statistik', 'description' => 'Cetak laporan dan export data absensi', 'guides_count' => 2],
                        (object)['slug' => 'notifikasi', 'icon' => 'bell', 'name' => 'Notifikasi & WA Gateway', 'description' => 'Konfigurasi notifikasi WhatsApp otomatis', 'guides_count' => 2],
                        (object)['slug' => 'pengaturan', 'icon' => 'settings', 'name' => 'Pengaturan Sistem', 'description' => 'Manajemen tahun akademik dan pengguna sistem', 'guides_count' => 2],
                    ];
                @endphp
                @foreach($fallbackCategories as $cat)
                    <x-guide-card
                        :title="$cat->name"
                        :description="$cat->description"
                        :icon="'tabler:' . $cat->icon"
                        :href="route('guide.category', $cat->slug)"
                        :articleCount="$cat->guides_count"
                        variant="category" />
                @endforeach
            @endforelse
        </div>
    </section>

    {{-- ===== PANDUAN POPULER ===== --}}
    <section class="mb-12">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Panduan Populer</h2>
            </div>
            @if($featuredGuides->count() > 0)
                <span class="text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full font-medium">Featured</span>
            @endif
        </div>

        @if($featuredGuides->count() > 0)
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach($featuredGuides as $guide)
                    <x-guide-card
                        :title="$guide->title"
                        :description="$guide->excerpt ?? ''"
                        :icon="$guide->featured_image ? 'tabler:photo' : 'tabler:file-text'"
                        :href="route('guide.show', [$guide->category->slug ?? '', $guide->slug])"
                        :roles="$guide->role_target ? explode(',', $guide->role_target) : []"
                        variant="featured" />
                @endforeach
            </div>
        @else
            {{-- Default featured articles --}}
            @php
                $defaultFeatured = [
                    (object)['title' => 'Cara Scan QR Code', 'excerpt' => 'Panduan lengkap melakukan scan QR Code untuk absensi siswa dan guru.', 'category_slug' => 'absensi-siswa', 'slug' => 'cara-scan-qr', 'roles' => ['Siswa', 'Petugas Piket']],
                    (object)['title' => 'Konfigurasi WA Gateway', 'excerpt' => 'Cara mengatur dan mengaktifkan WhatsApp Gateway untuk notifikasi otomatis.', 'category_slug' => 'notifikasi', 'slug' => 'konfigurasi-wa', 'roles' => ['Admin']],
                    (object)['title' => 'Login Pertama Kali', 'excerpt' => 'Panduan login untuk pertama kali setelah akun dibuat oleh admin.', 'category_slug' => 'pengaturan-akun', 'slug' => 'login-pertama-kali', 'roles' => ['Semua Pengguna']],
                    (object)['title' => 'Cetak Laporan Bulanan', 'excerpt' => 'Cetak laporan rekap absensi bulanan untuk seluruh siswa atau per kelas.', 'category_slug' => 'laporan', 'slug' => 'cetak-laporan', 'roles' => ['Admin', 'Operator']],
                ];
            @endphp
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach($defaultFeatured as $article)
                    <x-guide-card
                        :title="$article->title"
                        :description="$article->excerpt"
                        icon="tabler:file-text"
                        :href="route('guide.show', [$article->category_slug, $article->slug])"
                        :roles="$article->roles"
                        variant="featured" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- ===== CTA BANTUAN ===== --}}
    <section class="rounded-2xl bg-gradient-to-r from-brand-600 to-brand-700 p-8 sm:p-10 text-center mb-8">
        <h2 class="text-2xl font-bold text-white mb-2">Masih butuh bantuan?</h2>
        <p class="text-brand-100 mb-6 max-w-md mx-auto">Tim support kami siap membantu Anda dengan pertanyaan atau kendala teknis.</p>
        <a href="{{ route('public.bantuan') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-white text-brand-700 font-semibold rounded-xl hover:bg-brand-50 transition-all duration-200 shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            Hubungi Support
        </a>
    </section>
@endsection
