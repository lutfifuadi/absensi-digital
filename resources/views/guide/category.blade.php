@extends('layouts.guide')

@section('title', $category->name . ' — Panduan Aplikasi Presensi')

@section('meta_description', 'Panduan ' . $category->name . ' — artikel lengkap tentang ' . $category->name . ' di Aplikasi Presensi.')

@php
    $activeCategory = $category->slug ?? '';
    $activeArticle = '';
    $breadcrumbItems = [
        ['label' => $category->name],
    ];
@endphp

@section('content')
    @php
        $catIcon = $category->icon ? ('tabler:' . $category->icon) : 'tabler:folder';
    @endphp

    {{-- Category Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-2">
            <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center">
                <span class="iconify w-6 h-6 text-brand-600 dark:text-brand-400" data-icon="{{ $catIcon }}"></span>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ $category->name }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ $category->description ?? '' }}
                    &middot; {{ $guides->total() }} artikel
                </p>
            </div>
        </div>
    </div>

    {{-- Article List --}}
    @if($guides->count() > 0)
        <div class="space-y-3">
            @foreach($guides as $guide)
                <a href="{{ route('guide.show', [$category->slug, $guide->slug]) }}"
                   class="group block p-5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-sm transition-all duration-200">

                    <div class="flex items-start gap-4">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 group-hover:text-brand-500 group-hover:bg-brand-50 dark:group-hover:bg-brand-900/20 transition-all duration-200">
                            <span class="iconify w-5 h-5" data-icon="tabler:file-text"></span>
                        </div>

                        <div class="flex-1 min-w-0">
                            {{-- Title --}}
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors duration-150">
                                {{ $guide->title }}
                            </h3>

                            {{-- Excerpt --}}
                            @if($guide->excerpt)
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 line-clamp-2">
                                    {{ $guide->excerpt }}
                                </p>
                            @endif

                            {{-- Role targets --}}
                            @if($guide->role_target)
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach(explode(',', $guide->role_target) as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400">
                                            {{ trim(ucfirst($role)) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Arrow --}}
                        <svg class="w-5 h-5 flex-shrink-0 text-slate-300 dark:text-slate-600 group-hover:text-brand-500 group-hover:translate-x-0.5 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($guides->hasPages())
            <div class="mt-8">
                {{ $guides->onEachSide(1)->links() }}
            </div>
        @endif
    @else
        {{-- Empty state --}}
        <div class="text-center py-16">
            <span class="iconify w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" data-icon="tabler:file-off"></span>
            <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-1">Belum ada artikel</h3>
            <p class="text-sm text-slate-400 dark:text-slate-500">Kategori ini belum memiliki artikel panduan. Silakan cek kembali nanti.</p>
        </div>
    @endif
@endsection
