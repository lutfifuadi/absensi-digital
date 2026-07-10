<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKegiatan;
use App\Models\Kegiatan;
use App\Models\Pengaturan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PublicKegiatanController extends Controller
{
    /**
     * Tampilkan halaman login scan publik.
     */
    public function index(Request $request)
    {
        // Cek apakah session kegiatan_public_authenticated bernilai true
        if (session('kegiatan_public_authenticated') === true) {
            return redirect()->route('public.kegiatan.scan');
        }

        return view('public.kegiatan.login');
    }

    /**
     * Proses autentikasi password.
     */
    public function auth(Request $request)
    {
        // Validasi field password
        $request->validate([
            'password' => 'required|string',
        ]);

        // Ambil hash password dari setting password_unlock_scan_qr di tabel pengaturan
        $storedValue = Pengaturan::where('key', 'password_unlock_scan_qr')->value('value');

        // Jika setting di database tidak ada atau bernilai kosong/null, fallback gunakan password default 'kegiatan2026'
        $passwordToCheck = !empty($storedValue) ? $storedValue : 'kegiatan2026';

        $isValid = false;

        // Lakukan verifikasi password:
        // Jika passwordToCheck tampaknya adalah hash (biasanya 60 karakter dimulai dengan $2y$), gunakan Hash::check.
        // Sebagai alternatif tambahan, jika storedValue kosong/null, passwordToCheck adalah 'kegiatan2026' (plain text).
        // Kita juga mendukung check plain text jika hash check gagal atau jika nilai tersimpan berupa plain text.
        if (empty($storedValue)) {
            // fallback plain text 'kegiatan2026'
            if ($request->password === 'kegiatan2026') {
                $isValid = true;
            }
        } else {
            // Cek apakah berupa bcrypt hash
            if (strlen($passwordToCheck) === 60 && str_starts_with($passwordToCheck, '$2y$')) {
                if (Hash::check($request->password, $passwordToCheck)) {
                    $isValid = true;
                }
            } else {
                // Jika tersimpan plain text, bandingkan langsung
                if ($request->password === $passwordToCheck) {
                    $isValid = true;
                }
            }
        }

        if ($isValid) {
            session(['kegiatan_public_authenticated' => true]);
            return redirect()->route('public.kegiatan.scan');
        }

        return back()->withErrors(['password' => 'Password salah. Silakan coba lagi.'])->withInput();
    }

    /**
     * Halaman scan barcode / QR.
     */
    public function scan(Request $request)
    {
        // Cek apakah session kegiatan_public_authenticated true
        if (session('kegiatan_public_authenticated') !== true) {
            return redirect()->route('public.kegiatan.index');
        }

        // Ambil list kegiatan dari database
        $kegiatans = Kegiatan::latest('tanggal_pelaksanaan')->get();

        return view('public.kegiatan.scan', compact('kegiatans'));
    }

    /**
     * Proses scanning absensi (AJAX JSON).
     */
    public function process(Request $request)
    {
        // Cek apakah session kegiatan_public_authenticated true
        if (session('kegiatan_public_authenticated') !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid. Silakan masuk kembali.'
            ], 401);
        }

        // Validasi input qr_code dan kegiatan_id
        $request->validate([
            'qr_code' => 'required|string',
            'kegiatan_id' => 'required|exists:kegiatan,id',
        ]);

        // Cari siswa berdasarkan qr_code (mendukung pencarian fallback ke nisn dan nis jika qr_code tidak persis sama)
        $siswa = Siswa::with('kelas')
            ->where(function ($q) use ($request) {
                $q->where('qr_code', $request->qr_code)
                  ->orWhere('nisn', $request->qr_code)
                  ->orWhere('nis', $request->qr_code);
            })
            ->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu Siswa tidak terdaftar!'
            ], 404);
        }

        // Ambil model Kegiatan berdasarkan kegiatan_id
        $kegiatan = Kegiatan::findOrFail($request->kegiatan_id);
        $isTarget = false;

        // Lakukan validasi target peserta kegiatan (sama persis dengan logika target di AbsensiKegiatanController@store)
        // 1. Check Level (Tingkat)
        if ($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) {
            if ($siswa->kelas && in_array($siswa->kelas->tingkat, $kegiatan->target_tingkat)) {
                $isTarget = true;
            }
        }

        // 2. Check Jurusan
        if (!$isTarget && $kegiatan->target_jurusan && count($kegiatan->target_jurusan) > 0) {
            if ($siswa->kelas && in_array($siswa->kelas->jurusan, $kegiatan->target_jurusan)) {
                $isTarget = true;
            }
        }

        // 3. Check Specific Class (ID Kelas)
        if (!$isTarget && $kegiatan->target_peserta && count($kegiatan->target_peserta) > 0) {
            if (in_array($siswa->kelas_id, $kegiatan->target_peserta)) {
                $isTarget = true;
            }
        }

        // 4. If no target defined, assume ALL students
        if (!$kegiatan->target_tingkat && !$kegiatan->target_jurusan && !$kegiatan->target_peserta) {
            $isTarget = true;
        }

        if (!$isTarget) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak termasuk dalam target peserta kegiatan ini.'
            ], 403);
        }

        // Cek apakah siswa sudah melakukan absensi untuk kegiatan ini
        $already = AbsensiKegiatan::where('kegiatan_id', $request->kegiatan_id)
            ->where('siswa_id', $siswa->id)
            ->exists();

        if ($already) {
            $totalHadir = AbsensiKegiatan::where('kegiatan_id', $request->kegiatan_id)->count();
            return response()->json([
                'success' => false,
                'is_new' => false,
                'message' => 'Siswa sudah melakukan absensi pada kegiatan ini.',
                'siswa_nama' => $siswa->nama_lengkap,
                'siswa_kelas' => $siswa->kelas?->nama ?? '-',
                'total_hadir' => $totalHadir
            ], 422);
        }

        // Catat kehadiran
        AbsensiKegiatan::create([
            'kegiatan_id' => $request->kegiatan_id,
            'siswa_id' => $siswa->id,
            'jam_absen' => now(),
            'status' => 'hadir',
        ]);

        $totalHadir = AbsensiKegiatan::where('kegiatan_id', $request->kegiatan_id)->count();

        return response()->json([
            'success' => true,
            'is_new' => true,
            'message' => 'Absensi ' . $siswa->nama_lengkap . ' berhasil dicatat.',
            'siswa_nama' => $siswa->nama_lengkap,
            'siswa_nisn' => $siswa->nisn,
            'siswa_kelas' => $siswa->kelas?->nama ?? '-',
            'waktu' => now()->format('H:i:s'),
            'total_hadir' => $totalHadir
        ], 200);
    }

    /**
     * Proses logout scan publik.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('kegiatan_public_authenticated');

        return redirect()->route('public.kegiatan.index')->with('success', 'Sesi scan publik berhasil keluar.');
    }
}
