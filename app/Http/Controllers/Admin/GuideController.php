<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuideRequest;
use App\Http\Requests\UpdateGuideRequest;
use App\Models\Guide;
use App\Models\GuideCategory;
use App\Services\GuideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuideController extends Controller
{
    public function __construct(
        private GuideService $guideService
    ) {}

    /**
     * Display a listing of the guides.
     */
    public function index(Request $request)
    {
        $query = Guide::with(['category', 'author'])
            ->withCount('category');

        // Filter by search
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filter by category
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = $request->input('per_page', 15);
        $guides = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'html' => view('admin.guide.table', compact('guides'))->render(),
            ]);
        }

        $categories = GuideCategory::ordered()->get();

        return view('admin.guide.index', compact('guides', 'categories'));
    }

    /**
     * Show the form for creating a new guide.
     */
    public function create()
    {
        $categories = GuideCategory::ordered()->get();
        $roles = [
            'public'        => 'Publik',
            'siswa'         => 'Siswa',
            'guru'          => 'Guru',
            'wali_kelas'    => 'Wali Kelas',
            'orang_tua'     => 'Orang Tua',
            'staff_tu'       => 'Staff TU',
            'operator'      => 'Operator',
            'admin_sekolah' => 'Admin Sekolah',
            'super_admin'   => 'Super Admin',
        ];

        return view('admin.guide.form', compact('categories', 'roles'));
    }

    /**
     * Store a newly created guide in storage.
     */
    public function store(StoreGuideRequest $request)
    {
        try {
            $data = $request->validated();

            // Convert role_target array to comma-separated string
            if (isset($data['role_target']) && is_array($data['role_target'])) {
                $data['role_target'] = implode(',', array_filter($data['role_target']));
            }

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                $data['featured_image'] = $request->file('featured_image')
                    ->store('guides/featured', 'public');
            }

            // Auto-generate excerpt from content if not provided
            if (empty($data['excerpt']) && !empty($data['content'])) {
                $data['excerpt'] = Str::limit(strip_tags($data['content']), 200);
            }

            $guide = $this->guideService->createGuide($data, auth()->id());

            return redirect()
                ->route('admin.guides.index')
                ->with('success', "Panduan '{$guide->title}' berhasil dibuat.");
        } catch (\Exception $e) {
            Log::error('Gagal membuat panduan: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan panduan. Silakan coba lagi.');
        }
    }

    /**
     * Show the form for editing the specified guide.
     */
    public function edit(Guide $guide)
    {
        $guide->load(['category', 'author']);
        $categories = GuideCategory::ordered()->get();
        $roles = [
            'public'        => 'Publik',
            'siswa'         => 'Siswa',
            'guru'          => 'Guru',
            'wali_kelas'    => 'Wali Kelas',
            'orang_tua'     => 'Orang Tua',
            'staff_tu'       => 'Staff TU',
            'operator'      => 'Operator',
            'admin_sekolah' => 'Admin Sekolah',
            'super_admin'   => 'Super Admin',
        ];

        return view('admin.guide.form', compact('guide', 'categories', 'roles'));
    }

    /**
     * Update the specified guide in storage.
     */
    public function update(UpdateGuideRequest $request, Guide $guide)
    {
        try {
            $data = $request->validated();

            // Convert role_target array to comma-separated string
            if (isset($data['role_target']) && is_array($data['role_target'])) {
                $data['role_target'] = implode(',', array_filter($data['role_target']));
            }

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                // Delete old image if exists
                if ($guide->featured_image) {
                    Storage::disk('public')->delete($guide->featured_image);
                }
                $data['featured_image'] = $request->file('featured_image')
                    ->store('guides/featured', 'public');
            }

            // Auto-generate excerpt from content if not provided
            if (empty($data['excerpt']) && !empty($data['content'])) {
                $data['excerpt'] = Str::limit(strip_tags($data['content']), 200);
            }

            $this->guideService->updateGuide($guide, $data);

            return redirect()
                ->route('admin.guides.index')
                ->with('success', "Panduan '{$guide->title}' berhasil diupdate.");
        } catch (\Exception $e) {
            Log::error('Gagal mengupdate panduan: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate panduan. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified guide from storage (soft delete).
     */
    public function destroy(Guide $guide)
    {
        try {
            $title = $guide->title;
            $this->guideService->deleteGuide($guide);

            return redirect()
                ->route('admin.guides.index')
                ->with('success', "Panduan '{$title}' berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error('Gagal menghapus panduan: ' . $e->getMessage());
            return back()
                ->with('error', 'Gagal menghapus panduan. Silakan coba lagi.');
        }
    }
}
