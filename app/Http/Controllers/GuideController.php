<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\Models\GuideCategory;
use App\Services\GuideService;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function __construct(
        private GuideService $guideService
    ) {}

    /**
     * Tampilkan halaman utama panduan.
     *
     * Menampilkan daftar kategori panduan beserta featured guide.
     */
    public function index()
    {
        $categories = $this->guideService->getCategoriesWithGuideCount();
        $featuredGuides = $this->guideService->getFeaturedGuides();

        return view('guide.index', compact('categories', 'featuredGuides'));
    }

    /**
     * Tampilkan detail panduan berdasarkan slug.
     *
     * @param  string  $categorySlug
     * @param  string  $slug
     */
    public function show(string $categorySlug, string $slug)
    {
        $guide = $this->guideService->getGuideByCategoryAndSlug($categorySlug, $slug);

        if (!$guide) {
            abort(404, 'Panduan tidak ditemukan.');
        }

        // Ambil guide lain di kategori yang sama sebagai rekomendasi
        $relatedGuides = Guide::with(['category', 'author'])
            ->published()
            ->where('category_id', $guide->category_id)
            ->where('id', '!=', $guide->id)
            ->orderBy('order')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        return view('guide.show', compact('guide', 'relatedGuides'));
    }

    /**
     * Tampilkan daftar panduan per kategori.
     *
     * @param  string  $slug  Slug kategori
     */
    public function category(string $categorySlug)
    {
        $category = $this->guideService->getCategoryBySlug($categorySlug);

        if (!$category) {
            abort(404, 'Kategori panduan tidak ditemukan.');
        }

        $guides = $this->guideService->getGuidesByCategory($category);

        return view('guide.category', compact('category', 'guides'));
    }

    /**
     * Pencarian konten panduan.
     *
     * @param  Request  $request
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'max:200'],
        ]);

        $keyword = $request->input('q');
        $guides = $this->guideService->searchGuides($keyword);

        return view('guide.search', compact('guides', 'keyword'));
    }
}
