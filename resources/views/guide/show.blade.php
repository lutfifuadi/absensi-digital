@extends('layouts.guide')

@section('title', $guide->title . ' — Panduan Aplikasi Presensi')

@section('meta_description', $guide->excerpt ?? strip_tags(substr($guide->content ?? '', 0, 160)))

@php
    $activeCategory = $guide->category->slug ?? '';
    $activeArticle = $guide->slug ?? '';
    $breadcrumbItems = [
        ['label' => $guide->category->name ?? 'Kategori', 'url' => route('guide.category', $guide->category->slug ?? '')],
        ['label' => $guide->title],
    ];
@endphp

@section('content')
    @php
        $roles = $guide->role_target ? explode(',', $guide->role_target) : [];
        $categoryName = $guide->category->name ?? 'Kategori';
        $categorySlug = $guide->category->slug ?? '';
        $catIcon = ($guide->category->icon ?? false) ? ('tabler:' . $guide->category->icon) : 'tabler:folder';
        $updatedAt = $guide->updated_at ? $guide->updated_at->format('d M Y') : ($guide->published_at ? $guide->published_at->format('d M Y') : now()->format('d M Y'));
    @endphp

    {{-- ===== ARTICLE HEADER ===== --}}
    <article>
        {{-- Category badge + metadata --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <a href="{{ route('guide.category', $categorySlug) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 hover:bg-brand-100 dark:hover:bg-brand-900/50 transition-colors">
                <span class="iconify w-3.5 h-3.5" data-icon="{{ $catIcon }}"></span>
                {{ $categoryName }}
            </a>
            <span class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Diperbarui {{ $updatedAt }}
            </span>
            @if($guide->author)
                <span class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ $guide->author->name }}
                </span>
            @endif
        </div>

        {{-- Title --}}
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-3 leading-tight">
            {{ $guide->title }}
        </h1>

        {{-- Role targets --}}
        @if(count($roles) > 0)
            <div class="flex flex-wrap gap-2 mb-6">
                <span class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Panduan untuk:
                </span>
                @foreach($roles as $role)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                        {{ trim(ucfirst($role)) }}
                    </span>
                @endforeach
            </div>
        @endif

        {{-- ===== EXCERPT / INTRO ===== --}}
        @if($guide->excerpt)
            <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 leading-relaxed mb-6 border-l-4 border-brand-400 pl-4 bg-brand-50/30 dark:bg-brand-900/10 py-2.5 pr-4 rounded-r-lg">
                {{ $guide->excerpt }}
            </p>
        @endif

        {{-- ===== TABLE OF CONTENTS ===== --}}
        @php
            // Parse Markdown content to HTML to match headings for TOC
            $parsedHtml = \Illuminate\Support\Str::markdown($guide->content ?? '');
            preg_match_all('/<h([2-3])\s*(?:id="([^"]+)"[^>]*)?>(.*?)<\/h[2-3]>/i', $parsedHtml, $matches, PREG_SET_ORDER);
            $headings = [];
            foreach ($matches as $m) {
                // If there's no ID, generate one from the title text
                $id = !empty($m[2]) ? $m[2] : \Illuminate\Support\Str::slug(strip_tags($m[3]));
                $headings[] = ['level' => (int)$m[1], 'id' => $id, 'text' => strip_tags($m[3])];
            }
        @endphp
        <x-guide-toc :headings="$headings" />

        {{-- ===== ARTICLE CONTENT (Prose-like typography) ===== --}}
        <div class="prose-custom max-w-none text-sm sm:text-base leading-relaxed text-slate-700 dark:text-slate-300 space-y-4
            [&_h2]:text-lg [&_h2]:sm:text-xl [&_h2]:font-bold [&_h2]:text-slate-900 [&_h2]:dark:text-white [&_h2]:mt-8 [&_h2]:mb-3 [&_h2]:pb-2 [&_h2]:border-b [&_h2]:border-slate-200 [&_h2]:dark:border-slate-700
            [&_h3]:text-base [&_h3]:sm:text-lg [&_h3]:font-semibold [&_h3]:text-slate-800 [&_h3]:dark:text-slate-200 [&_h3]:mt-6 [&_h3]:mb-2
            [&_p]:mb-3 [&_p]:text-sm [&_p]:sm:text-base
            [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:mb-3 [&_ul>li]:mb-1 [&_ul>li]:text-sm [&_ul>li]:sm:text-base
            [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:mb-3 [&_ol>li]:mb-1 [&_ol>li]:text-sm [&_ol>li]:sm:text-base
            [&_code]:bg-slate-100 [&_code]:dark:bg-slate-800 [&_code]:text-brand-600 [&_code]:dark:text-brand-400 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded [&_code]:text-xs [&_code]:font-mono
            [&_pre]:bg-slate-900 [&_pre]:dark:bg-slate-800 [&_pre]:text-slate-100 [&_pre]:p-3 [&_pre]:rounded-lg [&_pre]:overflow-x-auto [&_pre]:text-xs [&_pre]:mb-4
            [&_pre_code]:bg-transparent [&_pre_code]:text-inherit [&_pre_code]:p-0 [&_pre_code]:rounded-none
            [&_blockquote]:border-l-4 [&_blockquote]:border-slate-300 [&_blockquote]:dark:border-slate-600 [&_blockquote]:pl-4 [&_blockquote]:italic [&_blockquote]:text-slate-500 [&_blockquote]:dark:text-slate-400 [&_blockquote]:my-4 [&_blockquote]:text-sm
            [&_img]:rounded-lg [&_img]:shadow-sm [&_img]:my-4 [&_img]:max-w-full [&_img]:h-auto
            [&_a]:text-brand-600 [&_a]:dark:text-brand-400 [&_a]:underline [&_a]:hover:text-brand-700 [&_a]:dark:hover:text-brand-300 [&_a]:font-medium
            [&_hr]:border-slate-200 [&_hr]:dark:border-slate-700 [&_hr]:my-6
            [&_table]:w-full [&_table]:border-collapse [&_table]:my-4 [&_table]:text-sm
            [&_th]:bg-slate-50 [&_th]:dark:bg-slate-800 [&_th]:px-3 [&_th]:py-2 [&_th]:text-left [&_th]:font-semibold [&_th]:text-slate-700 [&_th]:dark:text-slate-300 [&_th]:border [&_th]:border-slate-200 [&_th]:dark:border-slate-700
            [&_td]:px-3 [&_td]:py-2 [&_td]:border [&_td]:border-slate-200 [&_td]:dark:border-slate-700
        ">
            @if($guide->content)
                {!! \Illuminate\Support\Str::markdown($guide->content) !!}
            @else
                <p>Konten artikel belum tersedia.</p>
            @endif
        </div>
    </article>

    {{-- ===== FEEDBACK SECTION ===== --}}
    <div x-data="feedbackComponent()"
         class="mt-12 p-6 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 text-center">

        <template x-if="!submitted">
            <div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Apakah artikel ini membantu?</p>
                <div class="flex items-center justify-center gap-3">
                    <button @click="submitFeedback('yes')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 hover:border-brand-300 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                        </svg>
                        Ya, membantu
                    </button>
                    <button @click="submitFeedback('no')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400 hover:border-red-300 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                        </svg>
                        Tidak
                    </button>
                </div>
            </div>
        </template>

        <template x-if="submitted">
            <div class="flex items-center justify-center gap-3 text-sm" x-transition:enter="transition ease-out duration-300">
                <template x-if="feedback === 'yes'">
                    <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">Terima kasih atas masukannya!</span>
                    </div>
                </template>
                <template x-if="feedback === 'no'">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 font-medium">Terima kasih atas masukannya.</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Kami akan berusaha memperbaiki artikel ini.</p>
                    </div>
                </template>
            </div>
        </template>
    </div>

    <script>
        function feedbackComponent() {
            return {
                submitted: false,
                feedback: null,

                submitFeedback(value) {
                    this.feedback = value;
                    this.submitted = true;

                    // Optional: send feedback via AJAX
                    // fetch('/guide/feedback', {
                    //     method: 'POST',
                    //     headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    //     body: JSON.stringify({ article: '{{ $guide->slug }}', helpful: value === 'yes' })
                    // }).catch(() => {});
                }
            };
        }
    </script>

    {{-- ===== COPY LINK BUTTON ===== --}}
    <div x-data="{ copied: false }"
         class="mt-6 flex items-center justify-end gap-2">
        <button @click="
            navigator.clipboard.writeText(window.location.href);
            copied = true;
            setTimeout(() => copied = false, 2000);
        " class="inline-flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
            <template x-if="!copied">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            </template>
            <template x-if="copied">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </template>
            <span x-text="copied ? 'Tersalin!' : 'Salin tautan artikel'"></span>
        </button>
    </div>

    {{-- ===== PREV / NEXT NAVIGATION (from related guides) ===== --}}
    @php
        $prevGuide = null;
        $nextGuide = null;
        if (isset($relatedGuides) && $relatedGuides->count() > 0) {
            $prevGuide = $relatedGuides->first();
            if ($relatedGuides->count() > 1) {
                $nextGuide = $relatedGuides->skip(1)->first();
            }
        }
    @endphp
    <x-guide-prev-next
        :prev="$prevGuide ? ['slug' => $prevGuide->slug, 'title' => $prevGuide->title, 'category_slug' => $prevGuide->category->slug ?? ''] : null"
        :next="$nextGuide ? ['slug' => $nextGuide->slug, 'title' => $nextGuide->title, 'category_slug' => $nextGuide->category->slug ?? ''] : null" />

    {{-- ===== RELATED ARTICLES ===== --}}
    @if(isset($relatedGuides) && $relatedGuides->count() > 2)
        <section class="mt-10 pt-8 border-t border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Artikel Terkait</h3>
            <div class="grid sm:grid-cols-2 gap-3">
                @foreach($relatedGuides->skip(2)->take(4) as $related)
                    <a href="{{ route('guide.show', [$related->category->slug ?? $categorySlug, $related->slug]) }}"
                       class="group p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-brand-300 dark:hover:border-brand-700 transition-all duration-200">
                        <h4 class="text-sm font-medium text-slate-700 dark:text-slate-300 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors truncate">
                            {{ $related->title }}
                        </h4>
                        @if($related->excerpt)
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 line-clamp-1">{{ $related->excerpt }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash) {
            const el = document.querySelector(window.location.hash);
            if (el) {
                setTimeout(() => {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 200);
            }
        }
    });
</script>
@endpush
