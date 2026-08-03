<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\AbsensiGuru;
use App\Models\AbsensiStaff;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StaffTataUsaha;
use App\Support\QrScanLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PublicAbsensiCepatController extends Controller
{
    private function getCachedSettings()
    {
        /** @var \App\Services\SettingsManager $sm */
        $sm = app(\App\Services\SettingsManager::class);
        return $sm->all();
    }

    public function index()
    {
        $settingsManager = app(\App\Services\SettingsManager::class);
        if (!$settingsManager->getBool('fitur_absensi_cepat_publik')) {
            abort(404, 'Fitur Absensi Cepat Publik dinonaktifkan.');
        }

        if (auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin_sekolah'])) {
            session(['absensi_cepat_authenticated' => true]);
            return redirect()->route('public.absensi-cepat.dashboard');
        }

        if (session('absensi_cepat_authenticated')) {
            return redirect()->route('public.absensi-cepat.dashboard');
        }

        return view('public.absensi-cepat.index');
    }

    public function auth(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $ip = $request->ip();
        $storedHash = setting('password_absensi_cepat');

        if (!$storedHash) {
            // Fallback to scan_qr password if password_absensi_cepat is empty
            $storedHash = setting('password_unlock_scan_qr');
        }

        if (!$storedHash) {
            return back()->withErrors(['password' => 'Password Absensi Cepat belum diatur oleh admin sekolah.']);
        }

        if (!Hash::check($request->password, $storedHash) && $request->password !== $storedHash) {
            QrScanLogger::error('ABSENSI_CEPAT_LOGIN_FAILED', [
                'ip'  => $ip,
                'ket' => 'Password absensi cepat salah',
            ]);

            return back()->withErrors(['password' => 'Password salah. Silakan coba lagi.']);
        }

        QrScanLogger::info('ABSENSI_CEPAT_LOGIN_SUCCESS', [
            'ip'  => $ip,
            'ket' => 'Sesi absensi cepat publik berhasil dibuka',
        ]);

        session(['absensi_cepat_authenticated' => true]);

        return redirect()->route('public.absensi-cepat.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('absensi_cepat_authenticated');
        return redirect()->route('public.absensi-cepat.index')->with('success', 'Berhasil keluar dari sesi Absensi Cepat.');
    }

    public function dashboard()
    {
        $kelasList = Kelas::orderBy('nama', 'asc')->get();
        return view('public.absensi-cepat.dashboard', compact('kelasList'));
    }

    public function getSiswaByKelas($kelasId)
    {
        $tanggal = now()->toDateString();
        
        $siswaList = Siswa::where('kelas_id', $kelasId)
            ->where(function($q) {
                $q->where('status', 'aktif')->orWhereNull('status');
            })
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $absensiMap = AbsensiSiswa::whereIn('siswa_id', $siswaList->pluck('id'))
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        $statusMapFromDb = [
            'hadir' => 'H',
            'sakit' => 'S',
            'izin' => 'I',
            'alpha' => 'A',
            'terlambat' => 'T',
        ];

        $data = $siswaList->map(function ($siswa) use ($absensiMap, $statusMapFromDb) {
            $abs = $absensiMap->get($siswa->id);
            $dbStatus = $abs ? $abs->status : 'hadir';
            $mappedStatus = $statusMapFromDb[$dbStatus] ?? 'H';

            return [
                'id' => $siswa->id,
                'nis' => $siswa->nis ?? $siswa->nisn ?? '-',
                'nama_lengkap' => $siswa->nama_lengkap,
                'foto' => $siswa->foto ? asset('storage/' . $siswa->foto) : null,
                'status' => $mappedStatus, // 'H', 'S', 'I', 'A', 'T'
                'keterangan' => $abs ? $abs->keterangan : '',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function processQr(Request $request)
    {
        $data = $request->validate([
            'qr_code' => 'required|string|max:255',
        ]);

        $qrCode = trim($data['qr_code']);
        $tanggal = now()->toDateString();
        $currentTime = now()->format('H:i:s');
        $settings = $this->getCachedSettings();

        $jamMasuk = $settings['jam_masuk'] ?? '07:00';
        $toleransi = (int)($settings['toleransi_terlambat'] ?? 15);
        $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i:s');
        $jenjang = strtolower($settings['jenjang'] ?? 'SMA/MA/SMK');

        // 1. Check Siswa (by qr_code, NIS, NISN, or name)
        $siswa = Siswa::with('kelas')
            ->where('qr_code', $qrCode)
            ->orWhere('nis', $qrCode)
            ->orWhere('nisn', $qrCode)
            ->orWhere('nama_lengkap', 'LIKE', "%{$qrCode}%")
            ->first();

        if ($siswa) {
            $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)
                ->where('tanggal', $tanggal)
                ->first();

            $status = ($currentTime <= $limitHadir) ? 'hadir' : 'terlambat';
            if (in_array($jenjang, ['sd/mi', 'smp/mts']) && $status === 'terlambat') {
                $status = 'hadir';
            }

            if ($absensi) {
                $absensi->update([
                    'jam_masuk' => $absensi->jam_masuk ?? $currentTime,
                    'status' => $status,
                    'metode' => 'qr_scan',
                ]);
            } else {
                $absensi = AbsensiSiswa::create([
                    'siswa_id' => $siswa->id,
                    'kelas_id' => $siswa->kelas_id,
                    'tanggal' => $tanggal,
                    'jam_masuk' => $currentTime,
                    'status' => $status,
                    'metode' => 'qr_scan',
                ]);
            }

            $fotoUrl = $siswa->foto ? asset('storage/' . $siswa->foto) : asset('assets/img/avatars/1.png');
            $statusCode = $status === 'hadir' ? 'H' : 'T';

            return response()->json([
                'success' => true,
                'role' => 'Siswa',
                'name' => $siswa->nama_lengkap,
                'sub_info' => ($siswa->kelas ? $siswa->kelas->nama : 'Siswa') . ' • NIS: ' . ($siswa->nis ?? '-'),
                'status' => ucfirst($status),
                'status_code' => $statusCode,
                'jam' => $currentTime,
                'foto' => $fotoUrl,
                'message' => "Presensi {$siswa->nama_lengkap} berhasil dicatat!",
            ]);
        }

        // 2. Check Guru
        $guru = Guru::where('qr_code', $qrCode)
            ->orWhere('nip', $qrCode)
            ->orWhere('nama_lengkap', 'LIKE', "%{$qrCode}%")
            ->first();

        if ($guru) {
            $absensi = AbsensiGuru::where('guru_id', $guru->id)
                ->where('tanggal', $tanggal)
                ->first();

            $jamMasukGuru = $settings['jam_masuk_guru'] ?? $jamMasuk;
            $toleransiGuru = (int)($settings['toleransi_guru'] ?? $toleransi);
            $limitHadirGuru = \Carbon\Carbon::createFromFormat('H:i', $jamMasukGuru)->addMinutes($toleransiGuru)->format('H:i:s');

            $status = ($currentTime <= $limitHadirGuru) ? 'hadir' : 'terlambat';

            if ($absensi) {
                $absensi->update([
                    'jam_masuk' => $absensi->jam_masuk ?? $currentTime,
                    'status' => $status,
                    'metode' => 'qr_scan',
                ]);
            } else {
                $absensi = AbsensiGuru::create([
                    'guru_id' => $guru->id,
                    'tanggal' => $tanggal,
                    'jam_masuk' => $currentTime,
                    'status' => $status,
                    'metode' => 'qr_scan',
                ]);
            }

            $fotoUrl = $guru->foto ? asset('storage/' . $guru->foto) : asset('assets/img/avatars/1.png');
            $statusCode = $status === 'hadir' ? 'H' : 'T';

            return response()->json([
                'success' => true,
                'role' => 'Guru',
                'name' => $guru->nama_lengkap,
                'sub_info' => 'NIP: ' . ($guru->nip ?? '-'),
                'status' => ucfirst($status),
                'status_code' => $statusCode,
                'jam' => $currentTime,
                'foto' => $fotoUrl,
                'message' => "Presensi Guru {$guru->nama_lengkap} berhasil dicatat!",
            ]);
        }

        // 3. Check Staff
        $staff = StaffTataUsaha::where('qr_code', $qrCode)
            ->orWhere('nip', $qrCode)
            ->orWhere('nama_lengkap', 'LIKE', "%{$qrCode}%")
            ->first();

        if ($staff) {
            $absensi = AbsensiStaff::where('staff_id', $staff->id)
                ->where('tanggal', $tanggal)
                ->first();

            $jamMasukGuru = $settings['jam_masuk_guru'] ?? $jamMasuk;
            $toleransiGuru = (int)($settings['toleransi_guru'] ?? $toleransi);
            $limitHadirGuru = \Carbon\Carbon::createFromFormat('H:i', $jamMasukGuru)->addMinutes($toleransiGuru)->format('H:i:s');

            $status = ($currentTime <= $limitHadirGuru) ? 'hadir' : 'terlambat';

            if ($absensi) {
                $absensi->update([
                    'jam_masuk' => $absensi->jam_masuk ?? $currentTime,
                    'status' => $status,
                    'metode' => 'qr_scan',
                ]);
            } else {
                $absensi = AbsensiStaff::create([
                    'staff_id' => $staff->id,
                    'tanggal' => $tanggal,
                    'jam_masuk' => $currentTime,
                    'status' => $status,
                    'metode' => 'qr_scan',
                ]);
            }

            $fotoUrl = $staff->foto ? asset('storage/' . $staff->foto) : asset('assets/img/avatars/1.png');
            $statusCode = $status === 'hadir' ? 'H' : 'T';

            return response()->json([
                'success' => true,
                'role' => 'Staff TU',
                'name' => $staff->nama_lengkap,
                'sub_info' => 'NIP: ' . ($staff->nip ?? '-'),
                'status' => ucfirst($status),
                'status_code' => $statusCode,
                'jam' => $currentTime,
                'foto' => $fotoUrl,
                'message' => "Presensi Staff {$staff->nama_lengkap} berhasil dicatat!",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data NIS / NIP / QR Code / Nama tidak ditemukan!',
        ], 404);
    }

    public function storeBulk(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'absensi' => 'required|array',
            'absensi.*.siswa_id' => 'required|exists:siswa,id',
            'absensi.*.status' => 'required|in:H,S,I,A,T',
            'absensi.*.keterangan' => 'nullable|string|max:255',
        ]);

        $tanggal = now()->toDateString();
        $currentTime = now()->format('H:i:s');
        $kelasId = $data['kelas_id'];
        $settings = $this->getCachedSettings();
        $jenjang = strtolower($settings['jenjang'] ?? 'SMA/MA/SMK');

        $statusMap = [
            'H' => 'hadir',
            'S' => 'sakit',
            'I' => 'izin',
            'A' => 'alpha',
            'T' => 'terlambat',
        ];

        $countHadir = 0;
        $countSakit = 0;
        $countIzin = 0;
        $countAlpha = 0;
        $countTerlambat = 0;

        DB::transaction(function () use ($data, $tanggal, $currentTime, $kelasId, $statusMap, $jenjang, &$countHadir, &$countSakit, &$countIzin, &$countAlpha, &$countTerlambat) {
            foreach ($data['absensi'] as $item) {
                $statusInput = $item['status'];
                $dbStatus = $statusMap[$statusInput] ?? 'hadir';
                $keterangan = $item['keterangan'] ?? null;
                $siswaId = $item['siswa_id'];

                if (in_array($jenjang, ['sd/mi', 'smp/mts']) && $dbStatus === 'terlambat') {
                    $dbStatus = 'hadir';
                }

                switch ($dbStatus) {
                    case 'hadir': $countHadir++; break;
                    case 'sakit': $countSakit++; break;
                    case 'izin': $countIzin++; break;
                    case 'alpha': $countAlpha++; break;
                    case 'terlambat': $countTerlambat++; break;
                }

                $absensi = AbsensiSiswa::where('siswa_id', $siswaId)
                    ->where('tanggal', $tanggal)
                    ->first();

                if ($absensi) {
                    $absensi->update([
                        'status' => $dbStatus,
                        'keterangan' => $keterangan,
                        'jam_masuk' => in_array($dbStatus, ['hadir', 'terlambat']) ? ($absensi->jam_masuk ?? $currentTime) : null,
                        'metode' => 'manual',
                    ]);
                } else {
                    AbsensiSiswa::create([
                        'siswa_id' => $siswaId,
                        'kelas_id' => $kelasId,
                        'tanggal' => $tanggal,
                        'jam_masuk' => in_array($dbStatus, ['hadir', 'terlambat']) ? $currentTime : null,
                        'status' => $dbStatus,
                        'keterangan' => $keterangan,
                        'metode' => 'manual',
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Absensi bulk kelas berhasil disimpan!',
            'stats' => [
                'total' => count($data['absensi']),
                'hadir' => $countHadir,
                'sakit' => $countSakit,
                'izin' => $countIzin,
                'alpha' => $countAlpha,
                'terlambat' => $countTerlambat,
            ],
        ]);
    }
}
