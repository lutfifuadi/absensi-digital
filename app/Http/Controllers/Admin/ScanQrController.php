<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\AbsensiKegiatan;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiStaff;
use App\Models\Guru;
use App\Models\Kegiatan;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\StaffTataUsaha;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScanQrController extends Controller
{
    public function index()
    {
        $kegiatans = Kegiatan::whereDate('tanggal_pelaksanaan', today())->get();

        return view('admin.scan-qr.index', compact('kegiatans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'scan_type' => 'required|in:siswa,guru,pegawai,kegiatan-khusus',
            'qr_code' => 'required|string|alpha_dash|max:255',
            'kegiatan_id' => 'required_if:scan_type,kegiatan-khusus|nullable|exists:kegiatan,id',
        ]);

        switch ($data['scan_type']) {
            case 'siswa':
                $result = $this->processSiswaScan($data['qr_code']);
                break;
            case 'guru':
                $result = $this->processGuruScan($data['qr_code']);
                break;
            case 'pegawai':
                $result = $this->processStaffScan($data['qr_code']);
                break;
            case 'kegiatan-khusus':
                $result = $this->processKegiatanScan($data['qr_code'], $data['kegiatan_id']);
                break;
            default:
                $result = ['error' => 'Jenis scan tidak valid.'];
                break;
        }

        return redirect()->route('admin.scan-qr.index')->with($result);
    }

    private function processSiswaScan(string $qrCode): array
    {
        $siswa = Siswa::where('qr_code', $qrCode)->first();

        if (! $siswa) {
            return ['error' => 'QR code siswa tidak dikenal. Pastikan QR code valid.'];
        }

        $tanggal = now()->toDateString();
        $currentTime = now()->format('H:i');
        $settings = Pengaturan::whereIn('key', [
            'jam_masuk',
            'jam_batas_masuk',
            'jam_pulang',
            'jam_mulai_pulang',
            'jam_akhir_pulang',
            'toleransi_terlambat',
        ])->pluck('value', 'key');

        $jamMasuk = $settings['jam_masuk'] ?? '07:00';
        $jamBatasMasuk = $settings['jam_batas_masuk'] ?? '08:00';
        $jamMulaiPulang = $settings['jam_mulai_pulang'] ?? '14:00';
        $jamAkhirPulang = $settings['jam_akhir_pulang'] ?? '17:00';
        $toleransi = (int) ($settings['toleransi_terlambat'] ?? 15);

        $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if ($absensi && $currentTime >= $jamMulaiPulang) {
            if ($currentTime > $jamAkhirPulang) {
                return ['error' => 'Sesi pulang sudah berakhir (Batas: ' . $jamAkhirPulang . ').'];
            }

            if ($absensi->jam_pulang) {
                return ['error' => 'Siswa ' . $siswa->nama_lengkap . ' sudah melakukan scan pulang pada jam ' . $absensi->jam_pulang . '.'];
            }

            $absensi->update(['jam_pulang' => $currentTime]);
            return ['success' => 'Jam pulang ' . $siswa->nama_lengkap . ' berhasil dicatat.'];
        }

        if ($absensi) {
            return ['error' => 'Absensi siswa ' . $siswa->nama_lengkap . ' sudah dicatat untuk hari ini.'];
        }

        if ($currentTime > $jamBatasMasuk) {
            return ['error' => 'Sesi masuk sudah berakhir (Batas: ' . $jamBatasMasuk . ').'];
        }

        $status = 'hadir';
        $limitHadir = Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i');
        if ($currentTime > $limitHadir) {
            $status = 'terlambat';
        }

        AbsensiSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $siswa->kelas_id,
            'tanggal' => $tanggal,
            'jam_masuk' => $currentTime,
            'status' => $status,
            'keterangan' => 'Absensi otomatis via QR scanner',
            'guru_id' => null,
            'metode' => 'qr',
        ]);

        return ['success' => 'Absensi ' . $siswa->nama_lengkap . ' berhasil dicatat.' . ($status === 'terlambat' ? ' (TERLAMBAT)' : '')];
    }

    private function processGuruScan(string $qrCode): array
    {
        $guru = Guru::where('qr_code', $qrCode)->first();

        if (! $guru) {
            return ['error' => 'QR code guru tidak dikenal. Pastikan QR code valid.'];
        }

        $tanggal = now()->toDateString();
        $currentTime = now()->format('H:i');
        $jamMulaiPulang = Pengaturan::where('key', 'jam_mulai_pulang')->value('value') ?? '14:00';

        $absensi = AbsensiGuru::where('guru_id', $guru->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if ($absensi && $currentTime >= $jamMulaiPulang) {
            if ($absensi->jam_pulang) {
                return ['error' => 'Guru ' . $guru->nama_lengkap . ' sudah melakukan scan pulang pada jam ' . $absensi->jam_pulang . '.'];
            }

            $absensi->update(['jam_pulang' => $currentTime]);
            return ['success' => 'Jam pulang ' . $guru->nama_lengkap . ' berhasil dicatat.'];
        }

        if ($absensi) {
            return ['error' => 'Absensi guru ' . $guru->nama_lengkap . ' sudah dicatat untuk hari ini.'];
        }

        AbsensiGuru::create([
            'guru_id' => $guru->id,
            'tanggal' => $tanggal,
            'jam_masuk' => $currentTime,
            'status' => 'hadir',
            'keterangan' => 'Absensi QR via Admin',
            'metode' => 'qr',
        ]);

        return ['success' => 'Absensi ' . $guru->nama_lengkap . ' berhasil dicatat.'];
    }

    private function processStaffScan(string $qrCode): array
    {
        $staff = StaffTataUsaha::where('qr_code', $qrCode)->first();

        if (! $staff) {
            return ['error' => 'QR code pegawai tidak dikenal. Pastikan QR code valid.'];
        }

        $tanggal = now()->toDateString();
        $currentTime = now()->format('H:i');
        $jamMulaiPulang = Pengaturan::where('key', 'jam_mulai_pulang')->value('value') ?? '14:00';

        $absensi = AbsensiStaff::where('staff_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if ($absensi && $currentTime >= $jamMulaiPulang) {
            if ($absensi->jam_pulang) {
                return ['error' => 'Pegawai ' . $staff->nama_lengkap . ' sudah melakukan scan pulang pada jam ' . $absensi->jam_pulang . '.'];
            }

            $absensi->update(['jam_pulang' => $currentTime]);
            return ['success' => 'Jam pulang ' . $staff->nama_lengkap . ' berhasil dicatat.'];
        }

        if ($absensi) {
            return ['error' => 'Absensi pegawai ' . $staff->nama_lengkap . ' sudah dicatat untuk hari ini.'];
        }

        AbsensiStaff::create([
            'staff_id' => $staff->id,
            'tanggal' => $tanggal,
            'jam_masuk' => $currentTime,
            'status' => 'hadir',
            'keterangan' => 'Absensi QR via Admin',
            'metode' => 'qr',
        ]);

        return ['success' => 'Absensi ' . $staff->nama_lengkap . ' berhasil dicatat.'];
    }

    private function processKegiatanScan(string $qrCode, int $kegiatanId): array
    {
        $kegiatan = Kegiatan::find($kegiatanId);

        if (! $kegiatan) {
            return ['error' => 'Kegiatan khusus tidak ditemukan.'];
        }

        $siswa = Siswa::where('qr_code', $qrCode)->first();

        if (! $siswa) {
            return ['error' => 'QR code siswa tidak dikenal. Pastikan QR code valid.'];
        }

        $already = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('siswa_id', $siswa->id)
            ->whereDate('jam_absen', today())
            ->exists();

        if ($already) {
            return ['error' => 'Siswa sudah melakukan absensi kegiatan ini.'];
        }

        AbsensiKegiatan::create([
            'kegiatan_id' => $kegiatan->id,
            'siswa_id' => $siswa->id,
            'jam_absen' => now(),
            'status' => 'HADIR',
            'keterangan' => 'Absensi kegiatan khusus via Admin QR',
        ]);

        return ['success' => 'Absensi kegiatan ' . $kegiatan->nama . ' untuk ' . $siswa->nama_lengkap . ' berhasil dicatat.'];
    }
}
