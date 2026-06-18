<?php

namespace App\Services;

use App\Models\Ekskul;
use App\Models\EkskulAbsensi;
use App\Models\EkskulAnggota;
use App\Models\ActivityLog;
use App\Jobs\SendEkskulAlphaNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EkskulAbsensiService
{
    /**
     * Ambil data absensi pada tanggal tertentu.
     *
     * @param  int     $ekskulId
     * @param  string  $tanggal   Format: Y-m-d
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAbsensiPerTanggal(int $ekskulId, string $tanggal)
    {
        return EkskulAbsensi::with(['siswa:id,nama_lengkap,nis,kelas_id', 'pembina:id,nama_lengkap'])
            ->where('ekskul_id', $ekskulId)
            ->whereDate('tanggal', $tanggal)
            ->get();
    }

    /**
     * Simpan data absensi untuk banyak siswa sekaligus.
     *
     * @param  int     $ekskulId
     * @param  string  $tanggal   Format: Y-m-d
     * @param  array   $data      ['absensi' => [ ['siswa_id' => 1, 'status' => 'hadir'], ... ]]
     * @param  int|null $pembinaId
     * @return int
     */
    public function simpanAbsensi(int $ekskulId, string $tanggal, array $data, ?int $pembinaId = null): int
    {
        $saved = 0;

        DB::transaction(function () use ($ekskulId, $tanggal, $data, $pembinaId, &$saved) {
            foreach ($data['absensi'] as $item) {
                if (empty($item['siswa_id'])) {
                    continue;
                }

                EkskulAbsensi::updateOrCreate(
                    [
                        'ekskul_id' => $ekskulId,
                        'siswa_id'  => $item['siswa_id'],
                        'tanggal'   => $tanggal,
                    ],
                    [
                        'status'      => $item['status'],
                        'jam_absen'   => $item['jam_absen'] ?? now()->format('H:i'),
                        'pembina_id'  => $pembinaId,
                        'keterangan'  => $item['keterangan'] ?? null,
                    ]
                );

                $saved++;
            }
        });

        ActivityLog::record(
            'create',
            'ekskul_absensi',
            "Absensi ekskul ID {$ekskulId} untuk tanggal {$tanggal}: {$saved} data tersimpan",
            null,
            ['jumlah' => $saved, 'tanggal' => $tanggal]
        );

        // Dispatch notifikasi WhatsApp untuk setiap siswa yang berstatus alpha
        foreach ($data['absensi'] as $item) {
            if (!empty($item['siswa_id']) && ($item['status'] ?? '') === 'alpha') {
                SendEkskulAlphaNotification::dispatch(
                    $item['siswa_id'],
                    $ekskulId,
                    $tanggal
                );
            }
        }

        return $saved;
    }

    /**
     * Rekap absensi per bulan.
     *
     * @param  int     $ekskulId
     * @param  int     $bulan    1-12
     * @param  int     $tahun
     * @return array
     */
    public function getRekap(int $ekskulId, int $bulan, int $tahun): array
    {
        $ekskul = Ekskul::findOrFail($ekskulId);

        // Gunakan whereBetween agar composite index (ekskul_id, tanggal) digunakan optimal
        $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endDate   = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        $absensi = EkskulAbsensi::where('ekskul_id', $ekskulId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with('siswa:id,nama_lengkap,nis')
            ->get();

        // Grup per tanggal
        $rekapPerTanggal = $absensi->groupBy(function ($item) {
            return $item->tanggal->format('Y-m-d');
        })->map(function ($items, $date) {
            return [
                'tanggal' => $date,
                'hadir'    => $items->where('status', 'hadir')->count(),
                'izin'     => $items->where('status', 'izin')->count(),
                'sakit'    => $items->where('status', 'sakit')->count(),
                'alpha'    => $items->where('status', 'alpha')->count(),
                'terlambat' => $items->where('status', 'terlambat')->count(),
                'total'    => $items->count(),
            ];
        })->values();

        $total = [
            'hadir'    => $absensi->where('status', 'hadir')->count(),
            'izin'     => $absensi->where('status', 'izin')->count(),
            'sakit'    => $absensi->where('status', 'sakit')->count(),
            'alpha'    => $absensi->where('status', 'alpha')->count(),
            'terlambat' => $absensi->where('status', 'terlambat')->count(),
            'total'    => $absensi->count(),
        ];

        return [
            'ekskul'           => $ekskul,
            'bulan'            => $bulan,
            'tahun'            => $tahun,
            'rekap_per_tanggal' => $rekapPerTanggal,
            'total'            => $total,
        ];
    }

    /**
     * Rekap absensi per siswa (semua siswa) dalam satu ekskul per bulan.
     * Digunakan untuk tabel rekap dan export.
     *
     * @param  int  $ekskulId
     * @param  int  $bulan
     * @param  int  $tahun
     * @return \Illuminate\Support\Collection
     */
    public function getRekapPerSiswa(int $ekskulId, int $bulan, int $tahun): \Illuminate\Support\Collection
    {
        // Gunakan whereBetween agar composite index (ekskul_id, tanggal) digunakan optimal
        $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endDate   = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        $absensiRecords = EkskulAbsensi::with(['siswa:id,nama_lengkap,nis,kelas_id', 'siswa.kelas:id,nama'])
            ->where('ekskul_id', $ekskulId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        return $absensiRecords->groupBy('siswa_id')->map(function ($records) {
            $siswa = $records->first()->siswa;
            $totalCount = $records->count();
            $hadir = $records->where('status', 'hadir')->count();
            $izin = $records->where('status', 'izin')->count();
            $sakit = $records->where('status', 'sakit')->count();
            $alpha = $records->where('status', 'alpha')->count();
            $terlambat = $records->where('status', 'terlambat')->count();
            $persentase = $totalCount > 0 ? round(($hadir / $totalCount) * 100, 1) : 0;

            return (object) [
                'siswa'       => $siswa,
                'total'       => $totalCount,
                'hadir'       => $hadir,
                'izin'        => $izin,
                'sakit'       => $sakit,
                'alpha'       => $alpha,
                'terlambat'   => $terlambat,
                'persentase'  => $persentase,
            ];
        })->sortByDesc('persentase')->values();
    }

    /**
     * Rekap absensi per siswa dalam satu ekskul.
     *
     * @param  int  $ekskulId
     * @param  int  $siswaId
     * @return array
     */
    public function getRekapPerSiswaDetail(int $ekskulId, int $siswaId): array
    {
        $ekskul = Ekskul::findOrFail($ekskulId);

        $absensi = EkskulAbsensi::where('ekskul_id', $ekskulId)
            ->where('siswa_id', $siswaId)
            ->orderBy('tanggal')
            ->get();

        $total = [
            'hadir'    => $absensi->where('status', 'hadir')->count(),
            'izin'     => $absensi->where('status', 'izin')->count(),
            'sakit'    => $absensi->where('status', 'sakit')->count(),
            'alpha'    => $absensi->where('status', 'alpha')->count(),
            'terlambat' => $absensi->where('status', 'terlambat')->count(),
            'total'    => $absensi->count(),
        ];

        return [
            'ekskul'  => $ekskul,
            'siswa_id' => $siswaId,
            'absensi' => $absensi,
            'total'   => $total,
        ];
    }

    /**
     * Generate token QR Code unik untuk pertemuan hari ini.
     * Token ringkas dengan HMAC signature — cocok untuk QR code.
     *
     * @param  int     $ekskulId
     * @param  string  $tanggal   Format: Y-m-d
     * @return array
     */
    public function generateQRCode(int $ekskulId, string $tanggal): array
    {
        $ekskul = Ekskul::findOrFail($ekskulId);

        // Payload compact: ekskul_id|tanggal|expiry
        $payload = implode('|', [
            $ekskulId,
            $tanggal,
            now()->addHours(6)->timestamp,
        ]);

        $signature = hash_hmac('sha256', $payload, config('app.key'));
        $token = rtrim(strtr(base64_encode($payload . '|' . $signature), '+/', '-_'), '=');

        return [
            'ekskul_id' => $ekskulId,
            'ekskul'    => $ekskul->nama,
            'tanggal'   => $tanggal,
            'token'     => $token,
            'url'       => url("/api/ekskul/absensi/scan/{$token}"),
        ];
    }

    /**
     * Verifikasi token QR Code dan kembalikan payload-nya.
     *
     * @param  string  $token
     * @return array{ekskul_id: int, tanggal: string}|null
     */
    public function verifyQRToken(string $token): ?array
    {
        try {
            $decoded = base64_decode(strtr($token, '-_', '+/'));
            if (!$decoded) {
                return null;
            }

            $parts = explode('|', $decoded);
            if (count($parts) < 4) {
                return null;
            }

            $signature = array_pop($parts);
            $payload   = implode('|', $parts);

            // Verifikasi signature
            if (!hash_equals(hash_hmac('sha256', $payload, config('app.key')), $signature)) {
                return null;
            }

            $payloadParts = explode('|', $payload);
            if (count($payloadParts) !== 3) {
                return null;
            }

            [$ekskulId, $tanggal, $expires] = $payloadParts;

            // Cek expiry
            if (now()->timestamp > (int) $expires) {
                return null;
            }

            return [
                'ekskul_id' => (int) $ekskulId,
                'tanggal'   => $tanggal,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Catat absensi individu siswa via scan QR.
     *
     * @param  int     $ekskulId
     * @param  string  $tanggal
     * @param  int     $siswaId
     * @param  int|null $pembinaId
     * @return array{success: bool, message: string, status?: string}
     */
    public function recordScanAbsensi(int $ekskulId, string $tanggal, int $siswaId, ?int $pembinaId = null): array
    {
        // Cek apakah siswa adalah anggota aktif ekskul ini
        $anggota = EkskulAnggota::where('ekskul_id', $ekskulId)
            ->where('siswa_id', $siswaId)
            ->where('status', 'aktif')
            ->first();

        if (!$anggota) {
            return [
                'success' => false,
                'message' => 'Kamu bukan anggota ekskul ini.',
            ];
        }

        // Cek apakah sudah absen hari ini
        $existing = EkskulAbsensi::where('ekskul_id', $ekskulId)
            ->where('siswa_id', $siswaId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'already' => true,
                'status'  => $existing->status,
                'message' => 'Kamu sudah tercatat hadir hari ini.',
            ];
        }

        // Catat absensi
        $absensi = EkskulAbsensi::create([
            'ekskul_id'  => $ekskulId,
            'siswa_id'   => $siswaId,
            'tanggal'    => $tanggal,
            'status'     => 'hadir',
            'jam_absen'  => now()->format('H:i'),
            'pembina_id' => $pembinaId,
        ]);

        return [
            'success' => true,
            'status'  => 'hadir',
            'message' => 'Absensi berhasil dicatat.',
            'jam'     => $absensi->jam_absen,
        ];
    }
}
