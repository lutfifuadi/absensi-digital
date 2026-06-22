<?php

namespace App\Services;

use App\Models\Guide;
use App\Models\GuideCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GuideService
{
    /**
     * Ambil daftar kategori utama (root) beserta jumlah guide aktif di dalamnya
     * untuk ditampilkan di halaman index.
     *
     * @param  int|null  $schoolId
     * @return Collection
     */
    public function getCategoriesWithGuideCount(?int $schoolId = null): Collection
    {
        $cacheKey = 'guide_categories_with_count_' . ($schoolId ?? 'all');

        return Cache::remember($cacheKey, 3600, function () use ($schoolId) {
            return GuideCategory::withCount(['guides' => function ($query) {
                $query->published();
            }])
                ->ordered()
                ->get();
        });
    }

    /**
     * Ambil featured guide untuk ditampilkan di halaman utama panduan.
     *
     * @param  int|null  $limit
     * @return Collection
     */
    public function getFeaturedGuides(int $limit = 6): Collection
    {
        return Guide::with(['category', 'author'])
            ->featured()
            ->limit($limit)
            ->get();
    }

    /**
     * Ambil detail guide berdasarkan slug, lengkap dengan relasi.
     *
     * @param  string  $slug
     * @return Guide|null
     */
    public function getGuideBySlug(string $slug): ?Guide
    {
        return Guide::with(['category', 'author'])
            ->published()
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Ambil detail guide berdasarkan slug dalam suatu kategori.
     *
     * @param  string  $categorySlug
     * @param  string  $guideSlug
     * @return Guide|null
     */
    public function getGuideByCategoryAndSlug(string $categorySlug, string $guideSlug): ?Guide
    {
        return Guide::with(['category', 'author'])
            ->published()
            ->whereHas('category', function ($query) use ($categorySlug) {
                $query->where('slug', $categorySlug);
            })
            ->where('slug', $guideSlug)
            ->first();
    }

    /**
     * Ambil kategori berdasarkan slug.
     *
     * @param  string  $slug
     * @return GuideCategory|null
     */
    public function getCategoryBySlug(string $slug): ?GuideCategory
    {
        return GuideCategory::where('slug', $slug)->first();
    }

    /**
     * Ambil guide per kategori (dengan pagination).
     *
     * @param  GuideCategory  $category
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getGuidesByCategory(GuideCategory $category, int $perPage = 12): LengthAwarePaginator
    {
        return Guide::with(['category', 'author'])
            ->published()
            ->where('category_id', $category->id)
            ->orderBy('order')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Pencarian konten panduan.
     *
     * @param  string  $keyword
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function searchGuides(string $keyword, int $perPage = 12): LengthAwarePaginator
    {
        return Guide::with(['category', 'author'])
            ->published()
            ->search($keyword)
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Simpan guide baru (admin).
     *
     * @param  array  $data
     * @param  int    $authorId
     * @return Guide
     *
     * @throws \Exception
     */
    public function createGuide(array $data, int $authorId): Guide
    {
        return DB::transaction(function () use ($data, $authorId) {
            $data['author_id'] = $authorId;
            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

            // Pastikan slug unik
            $baseSlug = $data['slug'];
            $counter = 1;
            while (Guide::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $baseSlug . '-' . $counter;
                $counter++;
            }

            if (!isset($data['excerpt'])) {
                $data['excerpt'] = Str::limit(strip_tags($data['content'] ?? ''), 200);
            }

            if (($data['status'] ?? '') === 'published' && !isset($data['published_at'])) {
                $data['published_at'] = now();
            }

            $guide = Guide::create($data);

            Log::info('Guide created', [
                'guide_id' => $guide->id,
                'author_id' => $authorId,
                'title' => $guide->title,
            ]);

            $this->clearGuideCache();

            return $guide;
        });
    }

    /**
     * Perbarui guide yang sudah ada (admin).
     *
     * @param  Guide  $guide
     * @param  array  $data
     * @return Guide
     *
     * @throws \Exception
     */
    public function updateGuide(Guide $guide, array $data): Guide
    {
        return DB::transaction(function () use ($guide, $data) {
            // Update slug jika title berubah dan slug tidak diset manual
            if (isset($data['title']) && !isset($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            if (isset($data['slug']) && $data['slug'] !== $guide->slug) {
                $baseSlug = $data['slug'];
                $counter = 1;
                while (Guide::where('slug', $data['slug'])->where('id', '!=', $guide->id)->exists()) {
                    $data['slug'] = $baseSlug . '-' . $counter;
                    $counter++;
                }
            }

            if (!isset($data['excerpt']) && isset($data['content'])) {
                $data['excerpt'] = Str::limit(strip_tags($data['content']), 200);
            }

            if (($data['status'] ?? '') === 'published' && !$guide->published_at) {
                $data['published_at'] = now();
            }

            $guide->update($data);

            Log::info('Guide updated', [
                'guide_id' => $guide->id,
                'title' => $guide->title,
            ]);

            $this->clearGuideCache();

            return $guide->fresh();
        });
    }

    /**
     * Hapus guide (soft delete).
     *
     * @param  Guide  $guide
     * @return bool
     */
    public function deleteGuide(Guide $guide): bool
    {
        $result = $guide->delete();

        if ($result) {
            Log::info('Guide deleted', ['guide_id' => $guide->id, 'title' => $guide->title]);
            $this->clearGuideCache();
        }

        return $result;
    }

    /**
     * Buat kategori baru (admin).
     *
     * @param  array  $data
     * @return GuideCategory
     */
    public function createCategory(array $data): GuideCategory
    {
        $category = GuideCategory::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'order' => $data['order'] ?? 0,
        ]);

        $this->clearGuideCache();

        return $category;
    }

    /**
     * Perbarui kategori (admin).
     *
     * @param  GuideCategory  $category
     * @param  array  $data
     * @return GuideCategory
     */
    public function updateCategory(GuideCategory $category, array $data): GuideCategory
    {
        $category->update($data);
        $this->clearGuideCache();

        return $category->fresh();
    }

    /**
     * Hapus kategori (admin).
     *
     * @param  GuideCategory  $category
     * @return bool
     */
    public function deleteCategory(GuideCategory $category): bool
    {
        $result = $category->delete();
        $this->clearGuideCache();

        return $result;
    }

    /**
     * Bersihkan cache terkait panduan.
     */
    private function clearGuideCache(): void
    {
        Cache::forget('guide_categories_with_count_all');
    }
}
