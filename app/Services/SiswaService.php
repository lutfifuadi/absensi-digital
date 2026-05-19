<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Kelas;
use App\Models\RiwayatKenaikanKelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;

class SiswaService
{
    /**
     * Pindah kelas siswa dalam tahun ajaran yang sama.
     * Data siswa tetap utuh — hanya kelas_id yang diperbarui.
     *
     * @param  Siswa  $siswa
     * @param  int    $kelasIdBaru
     * @return Siswa
     *
     * @throws \InvalidArgumentException
     */
    public function pindahKelas(Siswa $siswa, int $kelasIdBaru): Siswa
    {
        $kelasBaru = Kelas::find($kelasIdBaru);

        if (! $kelasBaru) {
            throw new \InvalidArgumentException('Kelas tujuan tidak ditemukan.');
        }

        if ($kelasBaru->id === $siswa->kelas_id) {
            throw new \InvalidArgumentException('Siswa sudah berada di kelas tersebut.');
        }

        // Pastikan kelas tujuan berada di tahun akademik yang sama
        if ($kelasBaru->tahun_akademik_id !== $siswa->tahun_akademik_id) {
            throw new \InvalidArgumentException('Kelas tujuan tidak berada di tahun akademik yang sama dengan siswa saat ini.');
        }

        $old = $siswa->only(['kelas_id', 'tahun_akademik_id']);

        DB::transaction(function () use ($siswa, $kelasBaru) {
            $siswa->update(['kelas_id' => $kelasBaru->id]);
        });

        $siswa->refresh();

        ActivityLog::record(
            'update',
            'siswa',
            "Pindah kelas: {$siswa->nama_lengkap} (NIS: {$siswa->nis}) → {$kelasBaru->nama}",
            $old,
            ['kelas_id' => $kelasBaru->id, 'tahun_akademik_id' => $siswa->tahun_akademik_id]
        );

        return $siswa;
    }

    /**
     * Naik kelas siswa ke tahun ajaran baru.
     * Data siswa tetap utuh — kelas_id dan tahun_akademik_id diperbarui.
     *
     * @param  Siswa         $siswa
     * @param  int           $kelasIdBaru
     * @param  int           $tahunAkademikIdBaru
     * @return Siswa
     *
     * @throws \InvalidArgumentException
     */
    public function naikKelas(Siswa $siswa, int $kelasIdBaru, int $tahunAkademikIdBaru): Siswa
    {
        $kelasBaru          = Kelas::find($kelasIdBaru);
        $tahunAkademikBaru  = TahunAkademik::find($tahunAkademikIdBaru);

        if (! $kelasBaru) {
            throw new \InvalidArgumentException('Kelas tujuan tidak ditemukan.');
        }

        if (! $tahunAkademikBaru) {
            throw new \InvalidArgumentException('Tahun akademik tujuan tidak ditemukan.');
        }

        // Validasi: kelas tujuan harus berada di tahun akademik yang dituju
        if ($kelasBaru->tahun_akademik_id !== $tahunAkademikBaru->id) {
            throw new \InvalidArgumentException('Kelas tujuan tidak berada di tahun akademik yang dipilih.');
        }

        // Tidak boleh naik ke tahun akademik yang sama
        if ($tahunAkademikBaru->id === $siswa->tahun_akademik_id) {
            throw new \InvalidArgumentException('Tahun akademik tujuan harus berbeda dari tahun akademik siswa saat ini.');
        }

        $old = $siswa->only(['kelas_id', 'tahun_akademik_id']);

        DB::transaction(function () use ($siswa, $kelasBaru, $tahunAkademikBaru) {
            $siswa->update([
                'kelas_id'           => $kelasBaru->id,
                'tahun_akademik_id'  => $tahunAkademikBaru->id,
            ]);
        });

        $siswa->refresh();

        ActivityLog::record(
            'update',
            'siswa',
            "Naik kelas: {$siswa->nama_lengkap} (NIS: {$siswa->nis}) → {$kelasBaru->nama} / TA: {$tahunAkademikBaru->nama}",
            $old,
            ['kelas_id' => $kelasBaru->id, 'tahun_akademik_id' => $tahunAkademikBaru->id]
        );

        return $siswa;
    }

    /**
     * Pindah kelas siswa secara massal dari satu kelas ke kelas lain.
     *
     * @param  int  $kelasIdAsal
     * @param  int  $kelasIdTujuan
     * @return int  Jumlah siswa yang dipindahkan
     *
     * @throws \InvalidArgumentException
     */
    public function pindahKelasMassal(int $kelasIdAsal, int $kelasIdTujuan): int
    {
        $kelasAsal = Kelas::findOrFail($kelasIdAsal);
        $kelasTujuan = Kelas::findOrFail($kelasIdTujuan);

        if ($kelasAsal->id === $kelasTujuan->id) {
            throw new \InvalidArgumentException('Kelas tujuan tidak boleh sama dengan kelas asal.');
        }

        if ($kelasAsal->tahun_akademik_id !== $kelasTujuan->tahun_akademik_id) {
            throw new \InvalidArgumentException('Kelas asal dan tujuan harus berada dalam tahun akademik yang sama.');
        }

        $siswaCount = Siswa::where('kelas_id', $kelasIdAsal)->count();

        if ($siswaCount === 0) {
            throw new \InvalidArgumentException('Tidak ada siswa di kelas asal.');
        }

        DB::transaction(function () use ($kelasIdAsal, $kelasTujuan) {
            Siswa::where('kelas_id', $kelasIdAsal)->update([
                'kelas_id' => $kelasTujuan->id
            ]);
        });

        ActivityLog::record(
            'update',
            'kelas',
            "Pindah kelas massal: {$siswaCount} siswa dari {$kelasAsal->nama} ke {$kelasTujuan->nama}",
            ['kelas_id_asal' => $kelasIdAsal],
            ['kelas_id_tujuan' => $kelasIdTujuan]
        );

        return $siswaCount;
    }

    public function previewNaikKelasMassal(int $tahunAkademikAsalId, int $tahunAkademikTujuanId): array
    {
        $taAsal = TahunAkademik::findOrFail($tahunAkademikAsalId);
        $taTujuan = TahunAkademik::findOrFail($tahunAkademikTujuanId);

        $kelasAsalList = Kelas::withCount('siswa')
            ->where('tahun_akademik_id', $taAsal->id)
            ->whereHas('siswa', function ($q) {
                $q->where('status', 'aktif');
            })
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $kelasTujuanMap = Kelas::where('tahun_akademik_id', $taTujuan->id)
            ->get()
            ->groupBy(fn($k) => $k->tingkat . '|' . $k->jurusan);

        $detail = [];
        $totalSiswa = 0;

        foreach ($kelasAsalList as $kelas) {
            $tingkatSekarang = $kelas->tingkat;
            $nextTingkat = match ($tingkatSekarang) {
                'X' => 'XI',
                'XI' => 'XII',
                'XII' => null,
                default => null,
            };

            $siswaCount = Siswa::where('kelas_id', $kelas->id)
                ->where('status', 'aktif')
                ->count();

            $kelasTujuan = null;
            $keterangan = '';

            if ($nextTingkat === null) {
                $keterangan = 'Lulus — status akan diubah menjadi alumni';
            } else {
                $key = $nextTingkat . '|' . $kelas->jurusan;
                $kelasTujuan = $kelasTujuanMap->get($key)?->first();
                if (! $kelasTujuan) {
                    $keterangan = "Kelas {$nextTingkat} {$kelas->jurusan} tidak ditemukan di TA tujuan";
                }
            }

            $detail[] = [
                'kelas_asal' => $kelas,
                'tingkat' => $tingkatSekarang,
                'jumlah_siswa' => $siswaCount,
                'kelas_tujuan' => $kelasTujuan,
                'next_tingkat' => $nextTingkat,
                'keterangan' => $keterangan,
                'bisa_diproses' => $nextTingkat === null || $kelasTujuan !== null,
            ];

            $totalSiswa += $siswaCount;
        }

        return [
            'ta_asal' => $taAsal,
            'ta_tujuan' => $taTujuan,
            'detail' => $detail,
            'total_siswa' => $totalSiswa,
        ];
    }

    public function naikKelasMassal(int $tahunAkademikAsalId, int $tahunAkademikTujuanId): array
    {
        $taAsal = TahunAkademik::findOrFail($tahunAkademikAsalId);
        $taTujuan = TahunAkademik::findOrFail($tahunAkademikTujuanId);

        $kelasAsalList = Kelas::where('tahun_akademik_id', $taAsal->id)
            ->whereHas('siswa', function ($q) {
                $q->where('status', 'aktif');
            })
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $kelasTujuanMap = Kelas::where('tahun_akademik_id', $taTujuan->id)
            ->get()
            ->groupBy(fn($k) => $k->tingkat . '|' . $k->jurusan);

        $success = 0;
        $failed = 0;
        $details = [];

        DB::transaction(function () use ($kelasAsalList, $kelasTujuanMap, $taAsal, $taTujuan, &$success, &$failed, &$details) {
            foreach ($kelasAsalList as $kelas) {
                $tingkatSekarang = $kelas->tingkat;
                $nextTingkat = match ($tingkatSekarang) {
                    'X' => 'XI',
                    'XI' => 'XII',
                    'XII' => null,
                    default => null,
                };

                $siswaList = Siswa::where('kelas_id', $kelas->id)
                    ->where('status', 'aktif')
                    ->get();

                $kelasTujuan = null;
                $keterangan = '';

                if ($nextTingkat === null) {
                    $keterangan = 'Lulus — status diubah menjadi alumni';
                } else {
                    $key = $nextTingkat . '|' . $kelas->jurusan;
                    $kelasTujuan = $kelasTujuanMap->get($key)?->first();
                }

                $kelasSiswaSukses = 0;
                $kelasSiswaGagal = 0;

                foreach ($siswaList as $siswa) {
                    $oldStatus = $siswa->status;
                    $oldKelasId = $siswa->kelas_id;
                    $oldTaId = $siswa->tahun_akademik_id;

                    try {
                        if ($nextTingkat === null) {
                            // XII → alumni
                            $siswa->update([
                                'status' => 'alumni',
                                'kelas_id' => null,
                                'tahun_akademik_id' => null,
                            ]);

                            RiwayatKenaikanKelas::create([
                                'siswa_id' => $siswa->id,
                                'kelas_asal_id' => $oldKelasId,
                                'kelas_tujuan_id' => null,
                                'tahun_akademik_asal_id' => $taAsal->id,
                                'tahun_akademik_tujuan_id' => null,
                                'status_awal' => $oldStatus,
                                'status_akhir' => 'alumni',
                                'keterangan' => 'Lulus — naik kelas massal',
                            ]);
                        } elseif ($kelasTujuan) {
                            // X→XI atau XI→XII
                            $siswa->update([
                                'kelas_id' => $kelasTujuan->id,
                                'tahun_akademik_id' => $taTujuan->id,
                            ]);

                            RiwayatKenaikanKelas::create([
                                'siswa_id' => $siswa->id,
                                'kelas_asal_id' => $oldKelasId,
                                'kelas_tujuan_id' => $kelasTujuan->id,
                                'tahun_akademik_asal_id' => $taAsal->id,
                                'tahun_akademik_tujuan_id' => $taTujuan->id,
                                'status_awal' => $oldStatus,
                                'status_akhir' => 'aktif',
                                'keterangan' => "Naik dari {$tingkatSekarang} ke {$nextTingkat}",
                            ]);
                        } else {
                            $kelasSiswaGagal++;
                            continue;
                        }

                        $kelasSiswaSukses++;
                    } catch (\Exception $e) {
                        $kelasSiswaGagal++;
                        \Log::error("Gagal naik kelas siswa {$siswa->id}: {$e->getMessage()}");
                    }
                }

                $success += $kelasSiswaSukses;
                $failed += $kelasSiswaGagal;

                $details[] = [
                    'kelas_asal' => $kelas->nama,
                    'tingkat' => $tingkatSekarang,
                    'jumlah' => $siswaList->count(),
                    'sukses' => $kelasSiswaSukses,
                    'gagal' => $kelasSiswaGagal,
                    'kelas_tujuan' => $kelasTujuan?->nama ?? ($nextTingkat === null ? 'ALUMNI' : '—'),
                ];
            }
        });

        ActivityLog::record(
            'update',
            'siswa',
            "Naik kelas massal: {$success} siswa sukses, {$failed} gagal dari TA {$taAsal->nama} ke {$taTujuan->nama}",
            ['tahun_akademik_asal_id' => $taAsal->id, 'tahun_akademik_tujuan_id' => $taTujuan->id],
            ['success' => $success, 'failed' => $failed]
        );

        return [
            'success' => $success,
            'failed' => $failed,
            'details' => $details,
            'ta_asal' => $taAsal,
            'ta_tujuan' => $taTujuan,
        ];
    }
}
