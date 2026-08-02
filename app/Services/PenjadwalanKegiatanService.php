<?php

namespace App\Services;

use App\Models\JadwalKegiatan;
use App\Models\Kegiatan;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PenjadwalanKegiatanService
{
    /**
     * Normalisasi hari ke format huruf kecil Indonesia: 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'.
     */
    public static function normalizeHari($dateOrString): string
    {
        if ($dateOrString instanceof Carbon || $dateOrString instanceof \DateTimeInterface) {
            $map = [
                0 => 'ahad',
                1 => 'senin',
                2 => 'selasa',
                3 => 'rabu',
                4 => 'kamis',
                5 => 'jumat',
                6 => 'sabtu',
            ];
            return $map[(int)$dateOrString->format('w')] ?? '';
        }

        $str = strtolower(trim((string)$dateOrString));
        $aliases = [
            'monday'    => 'senin',
            'tuesday'   => 'selasa',
            'wednesday' => 'rabu',
            'thursday'  => 'kamis',
            'friday'    => 'jumat',
            'saturday'  => 'sabtu',
            'sunday'    => 'ahad',
            'minggu'    => 'ahad',
            'ahad'      => 'ahad',
        ];

        return $aliases[$str] ?? $str;
    }

    /**
     * Cek apakah jadwal kegiatan berulang aktif pada tanggal tertentu.
     */
    public function isJadwalAktifOnDate(JadwalKegiatan $jadwal, Carbon $date): bool
    {
        if (!$jadwal->is_aktif) {
            return false;
        }

        $targetYmd = $date->format('Y-m-d');
        if ($jadwal->tanggal_mulai && $targetYmd < $jadwal->tanggal_mulai->format('Y-m-d')) {
            return false;
        }

        if ($jadwal->tanggal_selesai && $targetYmd > $jadwal->tanggal_selesai->format('Y-m-d')) {
            return false;
        }

        // Cek jika terhubung ke EkskulJadwal
        if ($jadwal->ekskul_jadwal_id && $jadwal->ekskulJadwal) {
            $hariJadwalEkskul = static::normalizeHari($jadwal->ekskulJadwal->hari);
            $hariTarget = static::normalizeHari($date);
            return $hariJadwalEkskul === $hariTarget;
        }

        // Cek berdasarkan tipe_jadwal
        if (in_array($jadwal->tipe_jadwal, ['mingguan_1_hari', 'mingguan_multi_hari'])) {
            $hariArray = is_array($jadwal->hari) ? array_map([static::class, 'normalizeHari'], $jadwal->hari) : [];
            $hariTarget = static::normalizeHari($date);
            return in_array($hariTarget, $hariArray);
        }

        if ($jadwal->tipe_jadwal === 'tanggal_kalender') {
            $tglArray = is_array($jadwal->tanggal_kalender) ? array_map('intval', $jadwal->tanggal_kalender) : [];
            $tglTarget = (int) $date->format('j');
            return in_array($tglTarget, $tglArray);
        }

        return false;
    }

    /**
     * Generate & Sync sesi kegiatan harian secara Eager/On-The-Fly.
     * Bersifat idempotent & real-time — membuat sesi baru, meng-update sesi yang berubah,
     * dan membersihkan sesi non-aktif (jika belum ada absensi).
     */
    public function generateSesiForDate($date = null): int
    {
        $targetDate = $date ? Carbon::parse($date)->startOfDay() : Carbon::today();
        $activeTaId = TahunAkademik::where('is_aktif', true)->first()?->id;

        $jadwals = JadwalKegiatan::with(['ekskulJadwal'])->get();
        $generatedCount = 0;

        foreach ($jadwals as $jadwal) {
            $existing = Kegiatan::where('jadwal_kegiatan_id', $jadwal->id)
                ->whereDate('tanggal_pelaksanaan', $targetDate)
                ->first();

            $isEligible = $jadwal->is_aktif;
            if ($jadwal->tahun_akademik_id && $activeTaId && $jadwal->tahun_akademik_id != $activeTaId) {
                $isEligible = false;
            }
            if (!$this->isJadwalAktifOnDate($jadwal, $targetDate)) {
                $isEligible = false;
            }

            // Jika jadwal tidak berlaku/non-aktif pada hari ini
            if (!$isEligible) {
                if ($existing && \App\Models\AbsensiKegiatan::where('kegiatan_id', $existing->id)->count() === 0) {
                    $existing->delete();
                }
                continue;
            }

            // Override waktu & lokasi jika menggunakan ekskul_jadwal_id
            $waktuMulai = $jadwal->waktu_mulai;
            $waktuSelesai = $jadwal->waktu_selesai;
            $lokasi = $jadwal->lokasi;

            if ($jadwal->ekskul_jadwal_id && $jadwal->ekskulJadwal) {
                $waktuMulai = $jadwal->ekskulJadwal->jam_mulai ?? $waktuMulai;
                $waktuSelesai = $jadwal->ekskulJadwal->jam_selesai ?? $waktuSelesai;
                $lokasi = $jadwal->ekskulJadwal->lokasi ?? $lokasi;
            }

            $payload = [
                'nama_kegiatan' => $jadwal->nama_kegiatan,
                'jenis' => $jadwal->jenis,
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
                'lokasi' => $lokasi,
                'keterangan' => $jadwal->keterangan,
                'is_wajib' => $jadwal->is_wajib,
                'target_peserta' => $jadwal->target_peserta,
                'target_tingkat' => $jadwal->target_tingkat,
                'target_jurusan' => $jadwal->target_jurusan,
                'target_gender' => $jadwal->target_gender,
                'target_siswa' => $jadwal->target_siswa,
                'tahun_akademik_id' => $jadwal->tahun_akademik_id ?: $activeTaId,
            ];

            if ($existing) {
                // Update sesi eksis jika ada perubahan definisi di JadwalKegiatan
                $existing->update($payload);
            } else {
                // Buat sesi kegiatan baru
                $prefix = !empty($jadwal->qr_code_prefix) ? $jadwal->qr_code_prefix : 'KGT';
                $qrCode = strtoupper($prefix . '-' . $targetDate->format('Ymd') . '-' . Str::random(5));

                Kegiatan::create(array_merge($payload, [
                    'jadwal_kegiatan_id' => $jadwal->id,
                    'tanggal_pelaksanaan' => $targetDate->format('Y-m-d'),
                    'tanggal_selesai' => $targetDate->format('Y-m-d'),
                    'qr_code_kegiatan' => $qrCode,
                ]));

                $generatedCount++;
            }
        }

        return $generatedCount;
    }
}
