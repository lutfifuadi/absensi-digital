<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\UploadBatch;
use App\Services\BatchUploadService;
use App\Services\GoogleDriveConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UploadMassalController extends Controller
{
    /**
     * The batch upload service instance.
     *
     * @var BatchUploadService
     */
    protected BatchUploadService $batchUploadService;

    /**
     * Create a new controller instance.
     *
     * @param BatchUploadService $batchUploadService
     */
    public function __construct(BatchUploadService $batchUploadService)
    {
        $this->batchUploadService = $batchUploadService;
    }

    /**
     * Show the upload massal dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Cek status koneksi Google Drive
        $config = GoogleDriveConfigService::getConfig();
        $driveConnected = $config['is_connected'] ?? false;

        return view('admin.upload-massal.index', compact('driveConnected'));
    }

    /**
     * Process batch files upload.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array', 'max:500'],
            'files.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'nama_batch' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $batch = $this->batchUploadService->createBatchFromFiles(
            $request->file('files'),
            $user,
            $request->input('nama_batch')
        );

        return response()->json([
            'success' => true,
            'message' => 'Batch upload berhasil dibuat.',
            'redirect_url' => route('admin.upload-massal.batches.show', $batch->id),
        ]);
    }

    /**
     * Process ZIP file import.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importZip(Request $request)
    {
        $request->validate([
            'file_zip' => ['required', 'file', 'mimes:zip', 'max:102400'],
            'nama_batch' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $batch = $this->batchUploadService->createBatchFromZip(
            $request->file('file_zip'),
            $user,
            $request->input('nama_batch')
        );

        return response()->json([
            'success' => true,
            'message' => 'File ZIP berhasil diupload dan sedang diproses.',
            'redirect_url' => route('admin.upload-massal.batches.show', $batch->id),
        ]);
    }

    /**
     * Show list of upload batches.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function batches(Request $request)
    {
        $query = UploadBatch::query()->orderBy('created_at', 'desc');

        /** @var \App\Models\User $user */
        $user = $request->user();

        // Jika role user bukan Super Admin, filter batch milik user
        if (!$user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        }

        $batches = $query->paginate(10);

        return view('admin.upload-massal.batches', compact('batches'));
    }

    /**
     * Show detail progress of a batch.
     *
     * @param UploadBatch $batch
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showBatch(UploadBatch $batch)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika role user bukan Super Admin dan batch bukan miliknya, abort 403
        if (!$user->isSuperAdmin() && $batch->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke batch ini.');
        }

        return view('admin.upload-massal.batch-detail', compact('batch'));
    }

    /**
     * Get JSON items in a batch (for DataTable/AJAX).
     *
     * @param UploadBatch $batch
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchItems(UploadBatch $batch, Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika role user bukan Super Admin dan batch bukan miliknya, abort 403
        if (!$user->isSuperAdmin() && $batch->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke batch ini.');
        }

        $query = $batch->items()->with('siswa.kelas');

        // Dukung search nama file/siswa
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('original_filename', 'like', "%{$search}%")
                  ->orWhereHas('siswa', function ($sq) use ($search) {
                      $sq->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nisn', 'like', "%{$search}%");
                  });
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $items = $query->paginate($request->input('per_page', 10));

        return response()->json($items);
    }

    /**
     * Get batch progress status in real-time.
     *
     * @param UploadBatch $batch
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchProgress(UploadBatch $batch)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika role user bukan Super Admin dan batch bukan miliknya, abort 403
        if (!$user->isSuperAdmin() && $batch->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke batch ini.');
        }

        return response()->json([
            'status' => $batch->status,
            'total_items' => $batch->total_items,
            'success_count' => $batch->success_count,
            'failed_count' => $batch->failed_count,
            'progress_percent' => $batch->progressPercent(),
        ]);
    }

    /**
     * Retry failed items in a batch.
     *
     * @param UploadBatch $batch
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function retryBatch(UploadBatch $batch, Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika role user bukan Super Admin dan batch bukan miliknya, abort 403
        if (!$user->isSuperAdmin() && $batch->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke batch ini.');
        }

        $itemIds = $request->input('item_ids'); // Opsional array of item IDs
        
        $count = $this->batchUploadService->retryFailedItems($batch, $itemIds);

        $message = "Berhasil memproses ulang {$count} item yang gagal.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $count,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Cancel a batch in progress.
     *
     * @param UploadBatch $batch
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function cancelBatch(UploadBatch $batch, Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika role user bukan Super Admin dan batch bukan miliknya, abort 403
        if (!$user->isSuperAdmin() && $batch->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke batch ini.');
        }

        try {
            $this->batchUploadService->cancelBatch($batch);
            $message = 'Proses batch upload berhasil dibatalkan.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Check student data by NISN.
     *
     * @param string $nisn
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStudent($nisn)
    {
        $siswa = Siswa::with('kelas')->where('nisn', $nisn)->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nama' => $siswa->nama_lengkap,
                'kelas' => $siswa->kelas?->nama ?? '-',
            ],
        ]);
    }
}
