<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGuideCategoryRequest;
use App\Http\Requests\Admin\UpdateGuideCategoryRequest;
use App\Models\GuideCategory;
use App\Services\GuideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GuideCategoryController extends Controller
{
    public function __construct(
        private GuideService $guideService
    ) {}

    /**
     * Display a listing of guide categories.
     */
    public function index(Request $request)
    {
        $query = GuideCategory::with(['parent', 'children'])
            ->withCount('guides');

        // Filter by search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $categories = $query->ordered()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.guide-category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $parentCategories = GuideCategory::root()->ordered()->get();

        return view('admin.guide-category.form', compact('parentCategories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreGuideCategoryRequest $request)
    {
        try {
            $data = $request->validated();
            $category = $this->guideService->createCategory($data);

            return redirect()
                ->route('admin.guide-categories.index')
                ->with('success', "Kategori '{$category->name}' berhasil dibuat.");
        } catch (\Exception $e) {
            Log::error('Gagal membuat kategori panduan: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan kategori. Silakan coba lagi.');
        }
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(GuideCategory $guideCategory)
    {
        $parentCategories = GuideCategory::root()
            ->where('id', '!=', $guideCategory->id)
            ->ordered()
            ->get();

        return view('admin.guide-category.form', [
            'category' => $guideCategory,
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateGuideCategoryRequest $request, GuideCategory $guideCategory)
    {
        try {
            $data = $request->validated();

            // Auto-generate slug if name changed but slug not set
            if (empty($data['slug']) && isset($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $this->guideService->updateCategory($guideCategory, $data);

            return redirect()
                ->route('admin.guide-categories.index')
                ->with('success', "Kategori '{$guideCategory->name}' berhasil diupdate.");
        } catch (\Exception $e) {
            Log::error('Gagal mengupdate kategori panduan: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate kategori. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(GuideCategory $guideCategory)
    {
        try {
            // Prevent deletion if category has guides
            if ($guideCategory->guides()->count() > 0) {
                return back()->with('error', 
                    "Kategori '{$guideCategory->name}' tidak dapat dihapus karena masih memiliki " .
                    $guideCategory->guides()->count() . " panduan. Pindahkan atau hapus panduan terlebih dahulu.");
            }

            // Reassign children to parent if exists
            if ($guideCategory->children()->count() > 0) {
                $parentId = $guideCategory->parent_id;
                $guideCategory->children()->update(['parent_id' => $parentId]);
            }

            $name = $guideCategory->name;
            $this->guideService->deleteCategory($guideCategory);

            return redirect()
                ->route('admin.guide-categories.index')
                ->with('success', "Kategori '{$name}' berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error('Gagal menghapus kategori panduan: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus kategori. Silakan coba lagi.');
        }
    }
}
