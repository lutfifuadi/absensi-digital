@extends('layouts.guide')

@section('title', 'Hasil Pencarian: ' . $keyword . ' — Panduan Aplikasi Presensi')

@section('meta_description', 'Hasil pencarian panduan untuk "' . $keyword . '"')

@php
    $activeCategory = '';
    $activeArticle = '';
    $breadcrumbItems = [
        ['label' => 'Pencarian: ' . $keyword],
    ];
@endphp

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white mb-2">
            Hasil Pencarian
        </h1>
        <p class="text-slate-500 dark:text-slate-400">
            Menampilkan hasil untuk "<strong class="text-brand-600 dark:text-brand-400">{{ $keyword }}</strong>"
            — {{ $guides->total() }} ditemukan
        </p>
    </div>

    @if($guides->count() > 0)
        <div class="space-y-3">
            @foreach($guides as $guide)
                <a href="{{ route('guide.show', [$guide->category->slug ?? '', $guide->slug]) }}"
                   class="group block p-5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-sm transition-all duration-200">

                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400">
                            <span class="iconify w-5 h-5" data-icon="tabler:file-text"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                @if($guide->category)
                                    <span class="text-xs text-brand-500 font-medium">{{ $guide->category->name }}</span>
                                    <span class="text-slate-300 dark:text-slate-600">&middot;</span>
                                @endif
                            </div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                                {{ $guide->title }}
                            </h3>
                            @if($guide->excerpt)
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 line-clamp-2">
                                    {{ $guide->excerpt }}
                                </p>
                            @endif
                        </div>
                        <svg class="w-5 h-5 flex-shrink-0 text-slate-300 dark:text-slate-600 group-hover:text-brand-500 group-hover:translate-x-0.5 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>

        @if($guides->hasPages())
            <div class="mt-8">
                {{ $guides->onEachSide(1)->links() }}
            </div>
        @endif
    @else
        {{-- Empty state --}}
        <div class="text-center py-16">
            <span class="iconify w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" data-icon="tabler:search-off"></span>
            <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-1">Tidak ditemukan</h3>
            <p class="text-sm text-slate-400 dark:text-slate-500 max-w-md mx-auto">
                Tidak ada hasil untuk "<strong>{{ $keyword }}</strong>". Coba gunakan kata kunci lain yang lebih umum.
            </p>
            <a href="{{ route('guide.index') }}" class="inline-flex items-center gap-2 mt-6 text-sm text-brand-600 dark:text-brand-400 hover:underline font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Beranda Panduan
            </a>
        </div>
    @endif
@endsection
