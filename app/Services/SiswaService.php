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
}
