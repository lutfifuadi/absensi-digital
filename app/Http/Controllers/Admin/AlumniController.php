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
            // Ambil daftar orang tua terkait sebelum menghapus siswa
            $parents = $siswa->ortu;

            // Detach pivot relasi orang tua dan null-kan direct foreign key untuk mencegah lock-wait di MySQL
            $siswa->ortu()->detach();
            $siswa->update(['ortu_user_id' => null]);

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

            // Hapus siswa (yang akan memicu cascade delete di database dan booted callback)
            $siswa->delete();

            // Loop untuk setiap orang tua
            foreach ($parents as $parent) {
                $hasOtherChildren = DB::table('siswa_ortu')->where('ortu_user_id', $parent->id)->exists();
                if (!$hasOtherChildren) {
                    $parent->delete();
                }
            }
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

        $alumniIds = $alumni->pluck('id')->toArray();
        $alumniUserIds = $alumni->whereNotNull('user_id')->pluck('user_id')->toArray();

        // Cari semua orang tua dari alumni tersebut
        $parentUserIds = DB::table('siswa_ortu')
            ->whereIn('siswa_id', $alumniIds)
            ->pluck('ortu_user_id')
            ->unique()
            ->toArray();

        // Tentukan orang tua mana yang tidak memiliki anak aktif/non-aktif lain selain alumni yang dihapus
        $parentUserIdsToDelete = [];
        if (!empty($parentUserIds)) {
            $parentUserIdsToDelete = DB::table('users')
                ->whereIn('id', $parentUserIds)
                ->whereNotExists(function ($query) use ($alumniIds) {
                    $query->select(DB::raw(1))
                        ->from('siswa_ortu')
                        ->whereColumn('siswa_ortu.ortu_user_id', 'users.id')
                        ->whereNotIn('siswa_ortu.siswa_id', $alumniIds);
                })
                ->pluck('id')
                ->toArray();
        }

        DB::transaction(function () use ($alumniIds, $alumniUserIds, $parentUserIdsToDelete) {
            // 1. Detach/hapus relasi pivot di siswa_ortu
            DB::table('siswa_ortu')->whereIn('siswa_id', $alumniIds)->delete();

            // 2. Hapus log aktivitas user jika ada (jika user didelete, agar FK tidak constraint)
            $allUserIdsToDelete = array_merge($alumniUserIds, $parentUserIdsToDelete);
            if (!empty($allUserIdsToDelete)) {
                DB::table('activity_logs')->whereIn('user_id', $allUserIdsToDelete)->delete();
            }

            // 3. Hapus user siswa alumni dan user orang tua yang tidak memiliki anak lain
            if (!empty($allUserIdsToDelete)) {
                DB::table('users')->whereIn('id', $allUserIdsToDelete)->delete();
            }

            // 4. Hapus data absensi harian siswa
            DB::table('absensi_siswa')->whereIn('siswa_id', $alumniIds)->delete();

            // 5. Hapus data absensi kegiatan siswa
            DB::table('absensi_kegiatan')->whereIn('siswa_id', $alumniIds)->delete();

            // 6. Hapus izin sakit
            DB::table('izin_sakit')->whereIn('reference_id', $alumniIds)->where('tipe', 'siswa')->delete();

            // 7. Hapus riwayat kenaikan kelas
            DB::table('riwayat_kenaikan_kelas')->whereIn('siswa_id', $alumniIds)->delete();

            // 8. Hapus data ekskul, badges, leaderboards
            DB::table('ekskul_anggota')->whereIn('siswa_id', $alumniIds)->delete();
            DB::table('ekskul_absensi')->whereIn('siswa_id', $alumniIds)->delete();
            DB::table('student_badges')->whereIn('siswa_id', $alumniIds)->delete();
            DB::table('student_leaderboards')->whereIn('siswa_id', $alumniIds)->delete();
            DB::table('upload_batch_items')->whereIn('siswa_id', $alumniIds)->update(['siswa_id' => null]);

            // 9. Hapus siswa alumni
            DB::table('siswa')->whereIn('id', $alumniIds)->delete();

            // 10. Record single bulk activity log
            ActivityLog::record(
                'delete',
                'alumni',
                "Hapus massal alumni: Berhasil menghapus " . count($alumniIds) . " data alumni beserta akun terkait.",
                null,
                null
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Semua data alumni berhasil dihapus.',
        ]);
    }
}
