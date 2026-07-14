<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tahunLulus = $request->query('tahun_lulus');
        $perPage = (int) $request->query('per_page', 10);

        $siswa = Siswa::with(['kelas', 'tahunAkademik'])
            ->where('status', 'alumni')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when($tahunLulus, function ($query, $tahunLulus) {
                $query->whereHas('riwayatKenaikanKelas', function ($q) use ($tahunLulus) {
                    $q->where('status_akhir', 'alumni')
                        ->where('tahun_akademik_asal_id', $tahunLulus);
                });
            })
            ->orderBy('nama_lengkap')
            ->paginate($perPage)
            ->withQueryString();

        $tahunAkademikOptions = TahunAkademik::orderBy('tanggal_mulai', 'desc')->get();

        if ($request->ajax()) {
            return view('admin.alumni.table', compact('siswa'))->with('alumni', $siswa)->render();
        }

        return view('admin.alumni.index', compact('siswa', 'tahunAkademikOptions'))->with('alumni', $siswa);
    }

    public function show(Siswa $siswa)
    {
        if ($siswa->status !== 'alumni') {
            abort(404);
        }

        $siswa->load([
            'user',
            'ortu',
            'riwayatKenaikanKelas' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'riwayatKenaikanKelas.kelasAsal',
            'riwayatKenaikanKelas.kelasTujuan',
            'riwayatKenaikanKelas.tahunAkademikAsal',
            'riwayatKenaikanKelas.tahunAkademikTujuan',
        ]);

        return view('admin.alumni.show', compact('siswa'))->with('alumni', $siswa);
    }

    public function destroy(Siswa $siswa)
    {
        if ($siswa->status !== 'alumni') {
            abort(404);
        }

        DB::transaction(function () use ($siswa) {
            // Hapus user jika ada
            if ($siswa->user) {
                $siswa->user()->delete();
            }

            // Record log sebelum dihapus
            ActivityLog::record(
                'delete',
                'alumni',
                "Hapus alumni: {$siswa->nama_lengkap} (NISN: {$siswa->nisn})",
                $siswa->toArray(),
                null
            );

            // Hapus siswa (yang akan menicu cascade delete di database dan booted callback)
            $siswa->delete();
        });

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data alumni berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.alumni.index')
            ->with('success', 'Data alumni berhasil dihapus.');
    }

    public function destroyAll()
    {
        $alumni = Siswa::where('status', 'alumni')->get();

        if ($alumni->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data alumni yang dapat dihapus.',
            ], 404);
        }

        DB::transaction(function () use ($alumni) {
            $alumni->each(function ($siswa) {
                // Hapus user jika ada
                if ($siswa->user) {
                    $siswa->user()->delete();
                }

                // Record log sebelum dihapus
                ActivityLog::record(
                    'delete',
                    'alumni',
                    "Hapus alumni (Massal): {$siswa->nama_lengkap} (NISN: {$siswa->nisn})",
                    $siswa->toArray(),
                    null
                );

                // Hapus siswa (yang akan memicu cascade delete di database dan booted callback)
                $siswa->delete();
            });
        });

        return response()->json([
            'success' => true,
            'message' => 'Semua data alumni berhasil dihapus.',
        ]);
    }
}
