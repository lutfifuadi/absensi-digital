<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePengaduanRequest;
use App\Models\Pengaduan;
use App\Models\LogPengaduan;
use App\Jobs\SendPengaduanStatusJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengaduanController extends Controller
{
    /**
     * Display a listing of pengaduan.
     */
    public function index(Request $request)
    {
        $query = Pengaduan::query();

        // Filter by status
        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        // Search by kode_unik or nama_lengkap
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_unik', 'like', "%{$search}%")
                  ->orWhere('nama_lengkap', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDir = $request->sort_dir ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        // Stats
        $stats = [
            'total' => Pengaduan::count(),
            'baru' => Pengaduan::where('status', 'baru')->count(),
            'diproses' => Pengaduan::where('status', 'diproses')->count(),
            'selesai' => Pengaduan::where('status', 'selesai')->count(),
            'ditolak' => Pengaduan::where('status', 'ditolak')->count(),
        ];

        $pengaduan = $query->paginate($request->per_page ?? 10)->withQueryString();

        return view('admin.pengaduan.index', compact('pengaduan', 'stats'));
    }

    /**
     * Display the specified pengaduan.
     */
    public function show(Pengaduan $pengaduan)
    {
        $pengaduan->load('logs');

        // Determine next available statuses based on current status
        $availableStatuses = [];
        if ($pengaduan->status === 'baru') {
            $availableStatuses = ['diproses'];
        } elseif ($pengaduan->status === 'diproses') {
            $availableStatuses = ['selesai', 'ditolak'];
        }

        return view('admin.pengaduan.show', compact('pengaduan', 'availableStatuses'));
    }

    /**
     * Update the status of pengaduan.
     */
    public function updateStatus(Request $request, Pengaduan $pengaduan)
    {
        $request->validate([
            'status' => 'required|in:diproses,selesai,ditolak',
            'catatan' => 'required_if:status,selesai,ditolak|nullable|string|max:500',
        ]);

        $oldStatus = $pengaduan->status;

        // Validate flow sesuai PRD:
        // baru  → diproses / ditolak
        // diproses → selesai / ditolak
        // selesai / ditolak → final (tidak bisa diubah)
        if ($oldStatus === 'baru' && !in_array($request->status, ['diproses', 'ditolak'])) {
            return back()->with('error', 'Status Baru hanya bisa diubah ke Diproses atau Ditolak.');
        }
        if ($oldStatus === 'diproses' && !in_array($request->status, ['selesai', 'ditolak'])) {
            return back()->with('error', 'Status Diproses hanya bisa diubah ke Selesai atau Ditolak.');
        }
        if (in_array($oldStatus, ['selesai', 'ditolak'])) {
            return back()->with('error', 'Status sudah final, tidak bisa diubah lagi.');
        }

        // Update pengaduan
        $pengaduan->update([
            'status' => $request->status,
            'catatan_admin' => $request->catatan,
            'verified_at' => in_array($request->status, ['selesai', 'ditolak']) ? now() : null,
        ]);

        // Create log
        LogPengaduan::create([
            'pengaduan_id' => $pengaduan->id,
            'status_dari' => $oldStatus,
            'status_ke' => $request->status,
            'catatan' => $request->catatan,
            'diubah_oleh' => 'admin:' . auth()->id(),
        ]);

        // Dispatch job notifikasi WA via queue
        SendPengaduanStatusJob::dispatch(
            $pengaduan->nomor_wa,
            $pengaduan->kode_unik,
            $request->status,
            $request->catatan ?? ''
        );

        return redirect()->route('admin.pengaduan.show', $pengaduan->id)
            ->with('success', 'Status pengaduan berhasil diperbarui.');
    }

    /**
     * Update status pengaduan via API/PUT (PRD-002).
     * Menggunakan FormRequest validasi dan mengirim notifikasi WA via queue.
     */
    public function update(UpdatePengaduanRequest $request, Pengaduan $pengaduan)
    {
        $validated = $request->validated();
        $currentStatus = $pengaduan->status;
        $newStatus = $validated['status'];

        // Validasi transisi status
        $allowedTransitions = [
            'baru'     => ['diproses', 'ditolak'],
            'diproses' => ['selesai', 'ditolak'],
            'selesai'  => [],
            'ditolak'  => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            return response()->json([
                'message' => "Tidak dapat mengubah status dari '{$currentStatus}' ke '{$newStatus}'.",
            ], 422);
        }

        try {
            DB::transaction(function () use ($pengaduan, $validated, $currentStatus, $newStatus) {
                $pengaduan->update([
                    'status'        => $newStatus,
                    'catatan_admin' => $validated['catatan'] ?? $pengaduan->catatan_admin,
                    'verified_at'   => in_array($newStatus, ['selesai', 'ditolak']) ? now() : null,
                ]);

                LogPengaduan::create([
                    'pengaduan_id' => $pengaduan->id,
                    'status_dari'  => $currentStatus,
                    'status_ke'    => $newStatus,
                    'catatan'      => $validated['catatan'] ?? '',
                    'diubah_oleh'  => auth()->user()->name ?? 'admin',
                ]);
            });

            // Dispatch job notifikasi WA via queue
            SendPengaduanStatusJob::dispatch(
                $pengaduan->nomor_wa,
                $pengaduan->kode_unik,
                $newStatus,
                $validated['catatan'] ?? ''
            );

            $pengaduan->load('logs');

            return response()->json([
                'message' => 'Status pengaduan berhasil diperbarui.',
                'data'    => $pengaduan,
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PengaduanController@update Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui pengaduan.',
            ], 500);
        }
    }

    /**
     * Reset all pengaduan and log_pengaduan data.
     */
    public function reset()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang diizinkan mereset data.');
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Pengaduan::truncate();
            LogPengaduan::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->route('admin.pengaduan.index')
                ->with('success', 'Semua data pengaduan dan riwayat log berhasil direset.');
        } catch (\Exception $e) {
            Log::error('Admin PengaduanController@reset Error: ' . $e->getMessage());

            return back()->with('error', 'Gagal mereset data pengaduan.');
        }
    }
}
