<?php

namespace App\Services;

use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\MonitoringKehadiranGuru;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * KehadiranGuruService — penilaian kehadiran guru berdasarkan tipe kepegawaian (PRD-007).
 *
 * Aturan inti:
 * - Guru FULL TIME : dinilai dari `absensi_guru` harian (perilaku lama, tidak berubah).
 * - Guru PART TIME : dinilai dari `monitoring_kehadiran_guru` per slot jam mengajar
 *   (join `jadwal_pelajaran.guru_id`). `absensi_guru` masuk-pulang TIDAK wajib dan
 *   TIDAK memengaruhi status part time.
 *
 * Seluruh logika di sini diikat feature toggle `fitur_status_kepegawaian`:
 * bila toggle OFF, seluruh guru diperlakukan sebagai full time (perilaku lama).
 */
class KehadiranGuruService
{
    public function __construct(
        private MonitoringService $monitoringService
    ) {
    }

    /**
     * Cek apakah fitur status kepegawaian aktif.
     */
    public function fiturAktif(): bool
    {
        return feature('fitur_status_kepegawaian');
    }

    /**
     * Apakah guru merupakan part time (dengan mempertimbangkan feature toggle)?
     *
     * @param int|Guru $guruIdOrModel
     */
    public function isPartTime(int|Guru $guruIdOrModel): bool
    {
        if (! $this->fiturAktif()) {
            return false;
        }

        $tipe = $guruIdOrModel instanceof Guru
            ? $guruIdOrModel->tipe_kepegawaian
            : Guru::where('id', $guruIdOrModel)->value('tipe_kepegawaian');

        return $tipe === 'part_time';
    }

    /**
     * Ringkasan slot jam mengajar part time untuk satu tanggal.
     *
     * Sumber: `jadwal_pelajaran` (guru_id + hari) di-join ke
     * `monitoring_kehadiran_guru` (jadwal_pelajaran_id + tanggal).
     *
     * Return:
     * [
     *   'total_slot'      => int,  // jumlah slot terjadwal hari itu
     *   'hadir'           => int,  // slot berstatus hadir
     *   'terlambat'       => int,  // slot berstatus terlambat
     *   'tidak_hadir'     => int,  // slot berstatus tidak_hadir
     *   'belum_dimonitor' => int,  // slot terjadwal tanpa record monitoring
     *   'persentase_hadir'=> float,// (hadir+terlambat)/total_slot * 100
     * ]
     */
    public function getKehadiranPartTimeHarian(int $guruId, string $tanggal): array
    {
        $hari = $this->monitoringService->getHariIndo($tanggal);

        $jadwal = JadwalPelajaran::where('guru_id', $guruId)
            ->where('hari', $hari)
            ->with(['monitoring' => function ($q) use ($tanggal) {
                $q->where('tanggal', $tanggal);
            }])
            ->get();

        $summary = [
            'total_slot'       => 0,
            'hadir'            => 0,
            'terlambat'        => 0,
            'tidak_hadir'      => 0,
            'belum_dimonitor'  => 0,
            'persentase_hadir' => 0,
        ];

        foreach ($jadwal as $j) {
            $summary['total_slot']++;

            $mon = $j->monitoring->first();
            if ($mon && in_array($mon->status, ['hadir', 'terlambat', 'tidak_hadir'], true)) {
                $summary[$mon->status]++;
            } else {
                $summary['belum_dimonitor']++;
            }
        }

        $summary['persentase_hadir'] = $summary['total_slot'] > 0
            ? round((($summary['hadir'] + $summary['terlambat']) / $summary['total_slot']) * 100, 1)
            : 0;

        return $summary;
    }

    /**
     * Agregat slot part time per bulan (untuk rekap guru self & dashboard).
     *
     * Jadwal pelajaran bersifat mingguan (`hari`), sehingga total slot terjadwal
     * dihitung dari seluruh tanggal dalam bulan yang harinya cocok dengan jadwal.
     *
     * Return:
     * [
     *   'total_slot'       => int,
     *   'hadir'            => int,
     *   'terlambat'        => int,
     *   'tidak_hadir'      => int,
     *   'belum_dimonitor'  => int,
     *   'persentase_hadir' => float,
     *   'detail'           => [ [tanggal, jam_mulai, jam_selesai, kelas, mata_pelajaran, status, keterangan], ... ],
     * ]
     */
    public function getSlotPartTimeBulanan(int $guruId, int $bulan, int $tahun): array
    {
        $start = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $end   = $start->copy()->endOfMonth();

        // Seluruh jadwal guru (mingguan, semua hari)
        $jadwal = JadwalPelajaran::with('kelas')->where('guru_id', $guruId)->get();

        // Peta tanggal => hari (Bahasa Indonesia) dalam bulan
        $dates = [];
        $tmp   = $start->copy();
        while ($tmp->lte($end)) {
            $dates[$tmp->toDateString()] = $this->monitoringService->getHariIndo($tmp->toDateString());
            $tmp->addDay();
        }

        $monRaw = MonitoringKehadiranGuru::whereIn('jadwal_pelajaran_id', $jadwal->pluck('id'))
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('jadwal_pelajaran_id');

        $summary = [
            'total_slot'       => 0,
            'hadir'            => 0,
            'terlambat'        => 0,
            'tidak_hadir'      => 0,
            'belum_dimonitor'  => 0,
            'persentase_hadir' => 0,
        ];

        $detail = [];

        foreach ($jadwal as $j) {
            foreach ($dates as $tgl => $hari) {
                if ($hari !== $j->hari) {
                    continue;
                }

                $summary['total_slot']++;

                $mon = $monRaw->get($j->id, collect())
                    ->first(fn ($m) => $m->tanggal->toDateString() === $tgl);

                $statusSlot = 'belum_dimonitor';
                if ($mon && in_array($mon->status, ['hadir', 'terlambat', 'tidak_hadir'], true)) {
                    $statusSlot = $mon->status;
                    $summary[$mon->status]++;
                } else {
                    $summary['belum_dimonitor']++;
                }

                $detail[] = [
                    'tanggal'           => $tgl,
                    'jadwal_pelajaran_id' => $j->id,
                    'jam_mulai'         => $j->jam_mulai ? Carbon::parse($j->jam_mulai)->format('H:i') : '-',
                    'jam_selesai'       => $j->jam_selesai ? Carbon::parse($j->jam_selesai)->format('H:i') : '-',
                    'kelas'             => $j->kelas ? $j->kelas->nama_kelas : '-',
                    'mata_pelajaran'    => $j->mata_pelajaran,
                    'status'            => $statusSlot,
                    'keterangan'        => $mon?->keterangan,
                ];
            }
        }

        // Urutkan detail per tanggal & jam
        usort($detail, function ($a, $b) {
            return strcmp($a['tanggal'], $b['tanggal'])
                ?: strcmp($a['jam_mulai'], $b['jam_mulai']);
        });

        $summary['persentase_hadir'] = $summary['total_slot'] > 0
            ? round((($summary['hadir'] + $summary['terlambat']) / $summary['total_slot']) * 100, 1)
            : 0;

        $summary['detail'] = $detail;

        return $summary;
    }

    /**
     * Status "dianggap hadir" seorang guru pada satu tanggal.
     *
     * - FULL TIME  : status `absensi_guru` hari itu (hadir/terlambat/sakit/izin/alpha),
     *                atau null bila tidak ada record absensi.
     * - PART TIME  : dinilai dari ringkasan slot:
     *      'hadir'          — seluruh slot tercatat hadir
     *      'terlambat'      — seluruh slot tercatat terlambat
     *      'tidak_hadir'    — seluruh slot tercatat tidak_hadir
     *      'sesuai_jadwal'  — ada slot terjadwal & terisi monitoring (sebagian hadir/terlambat)
     *      'belum_dimonitor'- ada slot terjadwal tapi belum ada monitoring sama sekali
     *      null             — tidak ada slot terjadwal hari itu (tidak dihitung)
     *
     * Return: string status atau null.
     */
    public function isGuruDianggapHadirHariIni(int $guruId, string $tanggal): ?string
    {
        $guru = Guru::find($guruId);
        if (! $guru) {
            return null;
        }

        // Toggle OFF → semua guru diperlakukan full time (perilaku lama)
        if (! $this->fiturAktif() || $guru->tipe_kepegawaian !== 'part_time') {
            $absensi = AbsensiGuru::where('guru_id', $guruId)
                ->whereDate('tanggal', $tanggal)
                ->first();

            return $absensi ? strtolower($absensi->status) : null;
        }

        $slot = $this->getKehadiranPartTimeHarian($guruId, $tanggal);

        if ($slot['total_slot'] === 0) {
            return null; // BR-05: tanpa slot terjadwal → tidak dihitung
        }

        if ($slot['hadir'] === $slot['total_slot']) {
            return 'hadir';
        }
        if ($slot['terlambat'] === $slot['total_slot']) {
            return 'terlambat';
        }
        if ($slot['tidak_hadir'] === $slot['total_slot']) {
            return 'tidak_hadir';
        }
        if ($slot['hadir'] + $slot['terlambat'] > 0) {
            return 'sesuai_jadwal';
        }

        return 'belum_dimonitor';
    }

    /**
     * Rekap slot per guru untuk rentang tanggal (halaman rekap monitoring admin).
     *
     * Setiap baris = ringkasan slot mengajar satu guru dalam rentang tanggal.
     * Dapat difilter kategori `tipe_kepegawaian` (full_time/part_time).
     *
     * Return: Collection of [
     *   guru_id, nama, tipe_kepegawaian, total_slot, hadir, terlambat,
     *   tidak_hadir, belum_dimonitor, persentase_hadir
     * ] (hanya guru yang memiliki >= 1 slot terjadwal di rentang).
     */
    public function getRekapSlotPerGuru(array $filters): Collection
    {
        $dari  = $filters['tanggal_dari'] ?? date('Y-m-d');
        $sampai = $filters['tanggal_sampai'] ?? $dari;
        $tipe  = $filters['tipe_kepegawaian'] ?? null;

        $guruQuery = Guru::query();
        if ($tipe === 'full_time') {
            $guruQuery->where('tipe_kepegawaian', 'full_time');
        } elseif ($tipe === 'part_time') {
            $guruQuery->where('tipe_kepegawaian', 'part_time');
        }
        $gurus = $guruQuery->get();

        if ($gurus->isEmpty()) {
            return collect();
        }

        // Peta tanggal => hari dalam rentang
        $dates = [];
        $periode = Carbon::parse($dari)->startOfDay();
        $akhir   = Carbon::parse($sampai)->endOfDay();
        while ($periode->lte($akhir)) {
            $dates[$periode->toDateString()] = $this->monitoringService->getHariIndo($periode->toDateString());
            $periode->addDay();
        }
        $hariNames = array_values(array_unique(array_values($dates)));

        $jadwal = JadwalPelajaran::whereIn('guru_id', $gurus->pluck('id'))
            ->whereIn('hari', $hariNames)
            ->get();

        $jadwalPerGuru = $jadwal->groupBy('guru_id');

        $monRaw = MonitoringKehadiranGuru::whereIn('jadwal_pelajaran_id', $jadwal->pluck('id'))
            ->whereBetween('tanggal', [$dari, $sampai])
            ->get()
            ->groupBy('jadwal_pelajaran_id');

        $hasil = [];

        foreach ($gurus as $guru) {
            $row = [
                'guru_id'          => $guru->id,
                'nama'             => $guru->nama_lengkap ?? $guru->nama,
                'tipe_kepegawaian' => $guru->tipe_kepegawaian ?? 'full_time',
                'total_slot'       => 0,
                'hadir'            => 0,
                'terlambat'        => 0,
                'tidak_hadir'      => 0,
                'belum_dimonitor'  => 0,
                'persentase_hadir' => 0,
            ];

            foreach ($jadwalPerGuru->get($guru->id, collect()) as $j) {
                // Jumlah tanggal dalam rentang yang harinya cocok dengan jadwal
                $jumlahHari = 0;
                foreach ($dates as $tgl => $hari) {
                    if ($hari === $j->hari) {
                        $jumlahHari++;
                    }
                }
                $row['total_slot'] += $jumlahHari;

                foreach ($monRaw->get($j->id, collect()) as $m) {
                    if (! isset($dates[$m->tanggal->toDateString()])) {
                        continue;
                    }
                    if (in_array($m->status, ['hadir', 'terlambat', 'tidak_hadir'], true)) {
                        $row[$m->status]++;
                    }
                }
            }

            $row['belum_dimonitor'] = max(
                0,
                $row['total_slot'] - $row['hadir'] - $row['terlambat'] - $row['tidak_hadir']
            );
            $row['persentase_hadir'] = $row['total_slot'] > 0
                ? round((($row['hadir'] + $row['terlambat']) / $row['total_slot']) * 100, 1)
                : 0;

            if ($row['total_slot'] > 0) {
                $hasil[] = $row;
            }
        }

        usort($hasil, fn ($a, $b) => strcmp($a['nama'], $b['nama']));

        return collect($hasil);
    }
}
