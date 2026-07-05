<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;

class SiswaService
{
    /**
     * Pindah kelas siswa dalam tahun ajaran yang sama.
     * Data siswa tetap utuh — hanya kelas_id yang diperbarui.
     *
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
     *
     * @throws \InvalidArgumentException
     */
    public function naikKelas(Siswa $siswa, int $kelasIdBaru, int $tahunAkademikIdBaru): Siswa
    {
        $kelasBaru = Kelas::find($kelasIdBaru);
        $tahunAkademikBaru = TahunAkademik::find($tahunAkademikIdBaru);

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
                'kelas_id' => $kelasBaru->id,
                'tahun_akademik_id' => $tahunAkademikBaru->id,
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
     * @return int Jumlah siswa yang dipindahkan
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
                'kelas_id' => $kelasTujuan->id,
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

        $kelasAsalList = Kelas::where('tahun_akademik_id', $taAsal->id)
            ->whereHas('siswa', function ($q) {
                $q->where('status', 'aktif');
            })
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        // Pre-count active students per kelas in a single query — eliminates N+1
        $activeSiswaCounts = Siswa::whereIn('kelas_id', $kelasAsalList->pluck('id'))
            ->where('status', 'aktif')
            ->selectRaw('kelas_id, COUNT(*) as total')
            ->groupBy('kelas_id')
            ->pluck('total', 'kelas_id');

        $kelasTujuanMap = Kelas::where('tahun_akademik_id', $taTujuan->id)
            ->get()
            ->groupBy(fn ($k) => $k->tingkat.'|'.$k->jurusan);

        // Sort kelas tujuan dalam setiap grup by nama ASC agar urutan mapping konsisten
        $kelasTujuanMap = $kelasTujuanMap->map(fn ($group) => $group->sortBy('nama')->values());

        $detail = [];
        $totalSiswa = 0;
        $positionCounter = [];

        foreach ($kelasAsalList as $kelas) {
            $tingkatSekarang = $kelas->tingkat;
            $nextTingkat = match ($tingkatSekarang) {
                'X' => 'XI',
                'XI' => 'XII',
                'XII' => null,
                default => null,
            };

            $siswaCount = $activeSiswaCounts[$kelas->id] ?? 0;

            $kelasTujuan = null;
            $keterangan = '';

            if ($nextTingkat === null) {
                $keterangan = 'Lulus — status akan diubah menjadi alumni';
            } else {
                $key = $nextTingkat.'|'.$kelas->jurusan;
                $kelasTujuanList = $kelasTujuanMap->get($key);

                if ($kelasTujuanList !== null) {
                    $idx = $positionCounter[$key] ?? 0;
                    $positionCounter[$key] = $idx + 1;
                    $kelasTujuan = $kelasTujuanList->get($idx);

                    if (! $kelasTujuan) {
                        $keterangan = "Kelas {$nextTingkat} {$kelas->jurusan} ke-".($idx + 1).' tidak tersedia di TA tujuan';
                    }
                } else {
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
        set_time_limit(300);
        ini_set('memory_limit', '512M');

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
            ->groupBy(fn ($k) => $k->tingkat.'|'.$k->jurusan);

        // Sort kelas tujuan dalam setiap grup by nama ASC agar urutan mapping konsisten
        $kelasTujuanMap = $kelasTujuanMap->map(fn ($group) => $group->sortBy('nama')->values());

        $success = 0;
        $failed = 0;
        $details = [];
        $positionCounter = [];

        // --- PASS 1: Kumpulkan data (read-only) — tidak ada DB writes ---
        $siswaAlumniIds = [];         // siswa XII → alumni
        $kelompokPromosi = [];        // [kelasTujuanId => ['ids' => [...], 'kelas_tujuan_id' => ..., 'tahun_akademik_tujuan_id' => ...]]
        $riwayatAlumniData = [];      // bulk insert rows for alumni
        $riwayatPromosiData = [];     // bulk insert rows for promoted students

        foreach ($kelasAsalList as $kelas) {
            $tingkatSekarang = $kelas->tingkat;
            $nextTingkat = match ($tingkatSekarang) {
                'X' => 'XI',
                'XI' => 'XII',
                'XII' => null,
                default => null,
            };

            $siswaIds = Siswa::where('kelas_id', $kelas->id)
                ->where('status', 'aktif')
                ->pluck('id')
                ->toArray();

            $siswaCount = count($siswaIds);
            if ($siswaCount === 0) {
                continue;
            }

            if ($nextTingkat === null) {
                // XII → alumni
                $siswaAlumniIds = array_merge($siswaAlumniIds, $siswaIds);

                foreach ($siswaIds as $sid) {
                    $riwayatAlumniData[] = [
                        'siswa_id' => $sid,
                        'kelas_asal_id' => $kelas->id,
                        'kelas_tujuan_id' => null,
                        'tahun_akademik_asal_id' => $taAsal->id,
                        'tahun_akademik_tujuan_id' => null,
                        'status_awal' => 'aktif',
                        'status_akhir' => 'alumni',
                        'keterangan' => 'Lulus — naik kelas massal',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $success += $siswaCount;
                $details[] = [
                    'kelas_asal' => $kelas->nama,
                    'tingkat' => $tingkatSekarang,
                    'jumlah' => $siswaCount,
                    'sukses' => $siswaCount,
                    'gagal' => 0,
                    'kelas_tujuan' => 'ALUMNI',
                ];
            } else {
                $key = $nextTingkat.'|'.$kelas->jurusan;
                $kelasTujuanList = $kelasTujuanMap->get($key);

                if ($kelasTujuanList !== null) {
                    $idx = $positionCounter[$key] ?? 0;
                    $positionCounter[$key] = $idx + 1;
                    $kelasTujuan = $kelasTujuanList->get($idx);
                } else {
                    $kelasTujuan = null;
                }

                if (! $kelasTujuan) {
                    // Tidak ada kelas tujuan di TA baru → seluruh kelas gagal
                    $failed += $siswaCount;
                    $details[] = [
                        'kelas_asal' => $kelas->nama,
                        'tingkat' => $tingkatSekarang,
                        'jumlah' => $siswaCount,
                        'sukses' => 0,
                        'gagal' => $siswaCount,
                        'kelas_tujuan' => '—',
                    ];

                    continue;
                }

                // X→XI atau XI→XII — kelompokkan per kelas tujuan
                $tujuanId = $kelasTujuan->id;
                if (! isset($kelompokPromosi[$tujuanId])) {
                    $kelompokPromosi[$tujuanId] = [
                        'ids' => [],
                        'kelas_tujuan_id' => $tujuanId,
                        'tahun_akademik_tujuan_id' => $taTujuan->id,
                    ];
                }
                $kelompokPromosi[$tujuanId]['ids'] = array_merge(
                    $kelompokPromosi[$tujuanId]['ids'],
                    $siswaIds
                );

                foreach ($siswaIds as $sid) {
                    $riwayatPromosiData[] = [
                        'siswa_id' => $sid,
                        'kelas_asal_id' => $kelas->id,
                        'kelas_tujuan_id' => $tujuanId,
                        'tahun_akademik_asal_id' => $taAsal->id,
                        'tahun_akademik_tujuan_id' => $taTujuan->id,
                        'status_awal' => 'aktif',
                        'status_akhir' => 'aktif',
                        'keterangan' => "Naik dari {$tingkatSekarang} ke {$nextTingkat}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $success += $siswaCount;
                $details[] = [
                    'kelas_asal' => $kelas->nama,
                    'tingkat' => $tingkatSekarang,
                    'jumlah' => $siswaCount,
                    'sukses' => $siswaCount,
                    'gagal' => 0,
                    'kelas_tujuan' => $kelasTujuan->nama,
                ];
            }
        }

        // --- PASS 2: Eksekusi semua DB writes dalam satu transaksi ---
        DB::transaction(function () use ($siswaAlumniIds, $kelompokPromosi, $riwayatAlumniData, $riwayatPromosiData) {
            // 1. Bulk update: XII → alumni (1 query untuk semua)
            if (! empty($siswaAlumniIds)) {
                // Ambil data user_id dan ortu_user_id sebelum diupdate
                $siswaAlumniUsers = DB::table('siswa')
                    ->whereIn('id', $siswaAlumniIds)
                    ->select('user_id', 'ortu_user_id')
                    ->get();

                $siswaUserIds = [];
                $ortuUserIds = [];
                foreach ($siswaAlumniUsers as $sau) {
                    if ($sau->user_id) {
                        $siswaUserIds[] = $sau->user_id;
                    }
                    if ($sau->ortu_user_id) {
                        $ortuUserIds[] = $sau->ortu_user_id;
                    }
                }
                $siswaUserIds = array_unique($siswaUserIds);
                $ortuUserIds = array_unique($ortuUserIds);

                DB::table('siswa')
                    ->whereIn('id', $siswaAlumniIds)
                    ->update([
                        'status' => 'alumni',
                        'kelas_id' => null,
                        'tahun_akademik_id' => null,
                    ]);

                // Nonaktifkan Akun Siswa (user_id)
                if (! empty($siswaUserIds)) {
                    DB::table('users')
                        ->whereIn('id', $siswaUserIds)
                        ->update(['status' => 'nonaktif']);
                }

                // Nonaktifkan Akun Orang Tua (ortu_user_id) jika tidak memiliki anak aktif/nonaktif lain (bukan alumni)
                if (! empty($ortuUserIds)) {
                    $ortuWithActiveChildren = DB::table('siswa')
                        ->whereIn('ortu_user_id', $ortuUserIds)
                        ->whereIn('status', ['aktif', 'nonaktif'])
                        ->pluck('ortu_user_id')
                        ->unique()
                        ->toArray();

                    $ortuToDeactivate = array_diff($ortuUserIds, $ortuWithActiveChildren);

                    if (! empty($ortuToDeactivate)) {
                        DB::table('users')
                            ->whereIn('id', $ortuToDeactivate)
                            ->update(['status' => 'nonaktif']);
                    }
                }
            }

            // 2. Bulk update per kelompok promosi: X→XI, XI→XII (1 query per kelas tujuan)
            foreach ($kelompokPromosi as $data) {
                DB::table('siswa')
                    ->whereIn('id', $data['ids'])
                    ->update([
                        'kelas_id' => $data['kelas_tujuan_id'],
                        'tahun_akademik_id' => $data['tahun_akademik_tujuan_id'],
                    ]);
            }

            // 3. Bulk insert semua riwayat sekaligus (1 query untuk semua)
            $allRiwayat = array_merge($riwayatAlumniData, $riwayatPromosiData);
            if (! empty($allRiwayat)) {
                DB::table('riwayat_kenaikan_kelas')->insert($allRiwayat);
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
