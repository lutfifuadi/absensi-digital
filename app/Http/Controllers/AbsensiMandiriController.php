<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiMandiriController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $user = Auth::user();
        if ($user->role !== 'siswa') {
            return response()->json(['success' => false, 'message' => 'Hanya siswa yang dapat absen mandiri.']);
        }

        $izinkan = Pengaturan::where('key', 'izinkan_lokasi_absensi_mandiri')->value('value');
        if ($izinkan !== 'Ya') {
            return response()->json(['success' => false, 'message' => 'Absensi mandiri dinonaktifkan oleh sekolah.']);
        }

        $siswa = Siswa::where('user_id', $user->id)->first();
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data profil siswa tidak ditemukan.']);
        }

        $settings = Pengaturan::whereIn('key', [
            'jam_masuk', 'jam_batas_masuk', 'jam_pulang', 'jam_mulai_pulang', 'jam_akhir_pulang', 'toleransi_terlambat',
            'latitude', 'longitude', 'radius_jarak_absen', 'minimal_akurasi_gps', 'deteksi_fake_gps'
        ])->pluck('value', 'key');

        $jamMasuk       = $settings['jam_masuk']       ?? '07:00';
        $jamBatasMasuk  = $settings['jam_batas_masuk'] ?? '08:00';
        $jamMulaiPulang = $settings['jam_mulai_pulang'] ?? '14:00';
        $jamAkhirPulang = $settings['jam_akhir_pulang'] ?? '17:00';
        $toleransi      = (int)($settings['toleransi_terlambat'] ?? 15);

        // Gunakan jam khusus kelas jika diatur
        if ($siswa->kelas_id) {
            $kelas = Kelas::find($siswa->kelas_id);
            if ($kelas && $kelas->kustomisasi_jam) {
                if ($kelas->jam_masuk) {
                    $jamMasuk = \Carbon\Carbon::parse($kelas->jam_masuk)->format('H:i');
                    $jamBatasMasuk = \Carbon\Carbon::parse($kelas->jam_masuk)->addMinutes($toleransi)->format('H:i');
                }
                if ($kelas->jam_pulang) {
                    $jamMulaiPulang = \Carbon\Carbon::parse($kelas->jam_pulang)->format('H:i');
                }
            }
        }

        $schoolLat      = $settings['latitude']           ?? '-6.922405';
        $schoolLng      = $settings['longitude']          ?? '107.5717651';
        $maxRadius      = $settings['radius_jarak_absen'] ?? 900;
        $maxAccuracy    = $settings['minimal_akurasi_gps'] ?? 100;
        $detectFake     = ($settings['deteksi_fake_gps']  ?? 'Ya') === 'Ya';

        $currentTime = now()->format('H:i');
        $currentTimeFull = now();
        $tanggal     = now()->toDateString();
        
        // --- TIME CONTEXT ---
        $timeContext = 'normal';
        $jamInteger = (int)str_replace(':', '', $currentTime);
        if ($jamInteger >= 1400) {
            $timeContext = 'checkout';
        } elseif ($jamInteger >= 600 && $jamInteger <= 659) {
            $timeContext = 'early';
        } elseif ($jamInteger >= 701 && $jamInteger <= 730) {
            $timeContext = 'normal';
        } elseif ($jamInteger > 730) {
            $timeContext = 'late';
        }
        
        // --- MILESTONE CHECK ---
        $milestoneType = null;
        $streak = $this->calculateStreak($siswa->id);
        if ($streak >= 30) {
            $milestoneType = 'streak_30';
        } elseif ($streak >= 10) {
            $milestoneType = 'streak_10';
        } elseif ($streak >= 5) {
            $milestoneType = 'streak_5';
        }
        
        $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // 1. Distance & Accuracy Validation
        $accuracy = $request->accuracy ?? 999;
        if ($accuracy > $maxAccuracy) {
            return response()->json([
                'success' => false,
                'message' => "Akurasi GPS Anda terlalu rendah ({$accuracy}m). Minimal akurasi: {$maxAccuracy}m."
            ]);
        }

        if ($detectFake && $accuracy == 0) {
            return response()->json(['success' => false, 'message' => 'Terdeteksi penggunaan Fake GPS.']);
        }

        $distance = $this->calculateDistance($request->lat, $request->lng, $schoolLat, $schoolLng);

        if ($distance > $maxRadius) {
            return response()->json([
                'success' => false, 
                'message' => 'Anda berada di luar jangkauan area sekolah! Jarak: ' . round($distance) . 'm (Maks: ' . $maxRadius . 'm).'
            ]);
        }

        // --- LOGIKA PULANG ---
        if ($absensi && $currentTime >= $jamMulaiPulang) {
            if ($currentTime > $jamAkhirPulang) {
                return response()->json(['success' => false, 'message' => 'Sesi absen pulang sudah ditutup.']);
            }
            if ($absensi->jam_pulang) {
                return response()->json(['success' => false, 'message' => 'Anda sudah absen pulang hari ini.']);
            }

            $absensi->update(['jam_pulang' => $currentTime]);
            ActivityLog::record('update', 'absensi_mandiri', "Siswa {$siswa->nama_lengkap} absen pulang mandiri.");

            return response()->json([
                'success' => true,
                'message' => 'Berhasil absen pulang! Hati-hati di jalan.',
                'jam_pulang' => $currentTime,
                'milestone_type' => $milestoneType,
                'time_context' => $timeContext
            ]);
        }

        if ($absensi) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan absen masuk hari ini.']);
        }

        // --- LOGIKA MASUK ---
        if ($currentTime > $jamBatasMasuk) {
            return response()->json(['success' => false, 'message' => 'Sesi absen masuk sudah ditutup.']);
        }

        $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i');
        $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

        AbsensiSiswa::create([
            'siswa_id'    => $siswa->id,
            'kelas_id'    => $siswa->kelas_id,
            'tanggal'     => $tanggal,
            'jam_masuk'   => $currentTime,
            'status'      => $status,
            'keterangan'  => 'Absen Mandiri (Radius: ' . round($distance) . 'm)',
            'metode'      => 'mandiri',
        ]);

        ActivityLog::record('create', 'absensi_mandiri', "Siswa {$siswa->nama_lengkap} absen masuk mandiri (" . ucfirst($status) . ").");

        return response()->json([
            'success' => true,
            'message' => 'Berhasil absen masuk! Selamat belajar.',
            'jam_masuk' => $currentTime,
            'status' => $status,
            'milestone_type' => $milestoneType,
            'time_context' => $timeContext
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        }
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        // 1 mile is 1.609344 km
        return ($miles * 1.609344 * 1000); // return in meters
    }

    private function calculateStreak($siswaId)
    {
        $absensis = AbsensiSiswa::where('siswa_id', $siswaId)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->whereDate('tanggal', '<=', now()->toDateString())
            ->orderBy('tanggal', 'desc')
            ->pluck('tanggal')
            ->toArray();

        if (empty($absensis)) {
            return 0;
        }

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        
        $lastAttendanceDate = $absensis[0];
        if ($lastAttendanceDate !== $today && $lastAttendanceDate !== $yesterday) {
            return 0;
        }

        $streak = 0;
        $expectedDate = $lastAttendanceDate;
        
        foreach ($absensis as $date) {
            if ($date === $expectedDate) {
                $streak++;
                $expectedDate = date('Y-m-d', strtotime('-1 day', strtotime($expectedDate)));
            } else {
                break;
            }
        }

        return $streak;
    }
}
