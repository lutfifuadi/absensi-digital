<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Pengaturan;
use App\Models\User;
use App\Support\QrCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class WaliKelasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $sortBy = $request->query('sort_by', 'nama_lengkap');
        $sortDir = $request->query('sort_dir', 'asc');
        $perPage = (int) $request->query('per_page', 10);

        $allowedSorts = ['nama_lengkap', 'nip', 'mata_pelajaran', 'status', 'email'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'nama_lengkap';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        $query = User::query()
            ->select('users.*')
            ->leftJoin('guru', 'users.id', '=', 'guru.user_id')
            ->with('guru')
            ->withRole(User::ROLE_WALI_KELAS);

        // Filter search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('guru.nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('guru.nip', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($status) {
            if ($status === 'belum lengkap') {
                $query->whereNull('guru.id');
            } else {
                $query->where('guru.status', $status);
            }
        }

        // Sorting
        if ($sortBy === 'nama_lengkap') {
            // Urutkan berdasarkan nama lengkap guru, jika tidak ada pakai name user
            $query->orderBy(DB::raw('COALESCE(guru.nama_lengkap, users.name)'), $sortDir);
        } elseif ($sortBy === 'nip') {
            $query->orderBy('guru.nip', $sortDir);
        } elseif ($sortBy === 'mata_pelajaran') {
            $query->orderBy('guru.mata_pelajaran', $sortDir);
        } elseif ($sortBy === 'status') {
            $query->orderBy('guru.status', $sortDir);
        } elseif ($sortBy === 'email') {
            $query->orderBy('users.email', $sortDir);
        } else {
            $query->orderBy('users.name', $sortDir);
        }

        $waliKelasUsers = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.wali-kelas.table', compact('waliKelasUsers', 'sortBy', 'sortDir'));
        }

        return view('admin.wali-kelas.index', compact('waliKelasUsers', 'sortBy', 'sortDir'));
    }

    public function destroy(Guru $guru)
    {
        if (! $guru->user || ! $guru->user->isRole(User::ROLE_WALI_KELAS)) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data yang dihapus bukan merupakan wali kelas.'], 403);
            }
            abort(403, 'Data yang dihapus bukan merupakan wali kelas.');
        }

        $guru->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Wali kelas berhasil dihapus.']);
        }

        return redirect()->route('admin.wali-kelas.index')->with('success', 'Wali kelas berhasil dihapus.');
    }

    /**
     * Hapus user wali kelas yang belum memiliki profil guru.
     */
    public function destroyUser(User $user)
    {
        if (! $user->isRole(User::ROLE_WALI_KELAS)) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Aksi ini hanya diizinkan untuk akun wali kelas.'], 403);
            }
            abort(403, 'Aksi ini hanya diizinkan untuk akun wali kelas.');
        }

        if ($user->guru) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gunakan tombol hapus pada profil untuk menghapus wali kelas yang sudah memiliki profil.'], 400);
            }
            return redirect()->route('admin.wali-kelas.index')
                ->with('error', 'Gunakan tombol hapus pada profil untuk menghapus wali kelas yang sudah memiliki profil.');
        }

        $user->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Akun wali kelas berhasil dihapus.']);
        }

        return redirect()->route('admin.wali-kelas.index')->with('success', 'Akun wali kelas berhasil dihapus.');
    }

    /**
     * Cetak kartu QR wali kelas (Semua) -> download PDF.
     */
    public function cetakQr(Request $request)
    {
        $guruList = Guru::whereHas('user', fn ($q) => $q->where('role', User::ROLE_WALI_KELAS))
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $template = \App\Models\IdCardTemplate::where('type', 'wali_kelas')->active()->first()
            ?? \App\Models\IdCardTemplate::where('type', 'guru')->active()->first();

        if (! $template) {
            $qrImages    = $guruList->mapWithKeys(fn (Guru $g) => [
                $g->id => QrCodeGenerator::renderDataUri($g->qr_code ?? QrCodeGenerator::generate('GURU'), 160),
            ]);
            $namaSekolah = \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';

            return \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'admin.guru.kartu-qr-pdf',
                compact('guruList', 'namaSekolah', 'qrImages')
            )->setPaper('a4', 'portrait')->download('kartu-qr-wali-kelas-semua.pdf');
        }

        $config   = $template->config;
        $entities = $guruList;

        return \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.id-card-templates.pdf',
            compact('template', 'config', 'entities')
        )->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
         ->download('kartu-identitas-wali-kelas-semua.pdf');
    }

    /**
     * Generate & download kartu QR untuk satu wali kelas.
     */
    public function generateQrSatu(Guru $guru)
    {
        if (! $guru->qr_code) {
            $guru->update(['qr_code' => QrCodeGenerator::generate('GURU')]);
        }

        $template = \App\Models\IdCardTemplate::where('type', 'wali_kelas')->active()->first()
            ?? \App\Models\IdCardTemplate::where('type', 'guru')->active()->first();

        if (! $template) {
            $namaSekolah = \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';
            $qrImage     = QrCodeGenerator::renderDataUri($guru->qr_code, 200);

            return \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'admin.guru.kartu-qr-satu-pdf',
                compact('guru', 'namaSekolah', 'qrImage')
            )->setPaper([0, 0, 226.77, 283.46])
             ->download("kartu-qr-{$guru->nip}.pdf");
        }

        $config   = $template->config;
        $entities = collect([$guru]);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.id-card-templates.pdf',
            compact('template', 'config', 'entities')
        )->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
         ->download("kartu-identitas-{$guru->nip}.pdf");
    }
}
