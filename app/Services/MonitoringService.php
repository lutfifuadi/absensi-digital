<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\MonitoringKehadiranGuru;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use ValidationException;

class MonitoringService
{
    /**
     * Dapatkan nama hari Bahasa Indonesia berdasarkan tanggal (YYYY-MM-DD).
     */
    public function getHariIndo(?string $tanggal = null): string
    {
        $date = $tanggal ? Carbon::parse($tanggal) : Carbon::today();
        $map = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return $map[$date->dayOfWeekIso] ?? 'Senin';
    }

    /**
     * Dapatkan daftar jadwal pelajaran untuk tanggal tertentu beserta data monitoringnya.
     */
    public function getSchedulesWithMonitoring(?string $tanggal = null, string $filter = 'all'): array
    {
        $tanggal = $tanggal ?: date('Y-m-d');
        $hari = $this->getHariIndo($tanggal);

        $schedules = JadwalPelajaran::with([
            'kelas',
            'guru',
            'monitoring' => function ($query) use ($tanggal) {
                $query->where('tanggal', $tanggal)->with(['guruPengganti', 'pencatat']);
            },
        ])
            ->where('hari', $hari)
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $summary = [
            'total' => $schedules->count(),
            'hadir' => 0,
            'tidak_hadir' => 0,
            'terlambat' => 0,
            'belum_dimonitor' => 0,
        ];

        $items = [];
        foreach ($schedules as $s) {
            $mon = $s->monitoring->first();
            $statusStr = $mon ? $mon->status : 'belum_dimonitor';

            if ($mon) {
                if ($mon->status === 'hadir') {
                    $summary['hadir']++;
                } elseif ($mon->status === 'tidak_hadir') {
                    $summary['tidak_hadir']++;
                } elseif ($mon->status === 'terlambat') {
                    $summary['terlambat']++;
                }
            } else {
                $summary['belum_dimonitor']++;
            }

            // Filter item jika ada
            if ($filter === 'sudah' && !$mon) {
                continue;
            }
            if ($filter === 'belum' && $mon) {
                continue;
            }

            $items[] = [
                'jadwal_pelajaran_id' => $s->id,
                'jam_mulai' => Carbon::parse($s->jam_mulai)->format('H:i'),
                'jam_selesai' => Carbon::parse($s->jam_selesai)->format('H:i'),
                'kelas' => [
                    'id' => $s->kelas_id,
                    'nama_kelas' => $s->kelas ? $s->kelas->nama_kelas : '-',
                ],
                'guru' => [
                    'id' => $s->guru_id,
                    'nama' => $s->guru ? $s->guru->nama : 'Belum ada guru',
                ],
                'mata_pelajaran' => $s->mata_pelajaran,
                'monitoring' => $mon ? [
                    'id' => $mon->id,
                    'status' => $mon->status,
                    'keterangan' => $mon->keterangan,
                    'keterangan_lain' => $mon->keterangan_lain,
                    'lama_terlambat' => $mon->lama_terlambat,
                    'ada_pengganti' => $mon->ada_pengganti,
                    'guru_pengganti_id' => $mon->guru_pengganti_id,
                    'guru_pengganti_nama' => $mon->guru_pengganti_nama ?: ($mon->guruPengganti ? $mon->guruPengganti->nama : null),
                    'dicatat_oleh' => $mon->pencatat ? $mon->pencatat->name : '-',
                    'created_at' => $mon->created_at ? $mon->created_at->toIso8601String() : null,
                ] : null,
            ];
        }

        return [
            'tanggal' => $tanggal,
            'hari' => $hari,
            'summary' => $summary,
            'jam_pelajaran' => $items,
        ];
    }

    /**
     * Simpan entri monitoring baru (Piket / Admin).
     */
    public function storeMonitoring(array $data, int $userId): MonitoringKehadiranGuru
    {
        $jadwalId = $data['jadwal_pelajaran_id'];
        $tanggal = $data['tanggal'] ?? date('Y-m-d');

        // Cek duplikat entri per BR-01
        $existing = MonitoringKehadiranGuru::where('jadwal_pelajaran_id', $jadwalId)
            ->where('tanggal', $tanggal)
            ->first();

        if ($existing) {
            throw new \Exception("Monitoring untuk kelas dan jam pelajaran ini pada tanggal {$tanggal} sudah pernah dicatat.");
        }

        $status = $data['status'];
        $keterangan = $data['keterangan'] ?? null;
        $lamaTerlambat = $data['lama_terlambat'] ?? null;
        $guruPenggantiId = $data['guru_pengganti_id'] ?? null;
        $guruPenggantiNama = $data['guru_pengganti_nama'] ?? null;

        if ($guruPenggantiId && !$guruPenggantiNama) {
            $g = Guru::find($guruPenggantiId);
            if ($g) {
                $guruPenggantiNama = $g->nama;
            }
        }

        $adaPengganti = !empty($guruPenggantiId) || !empty($guruPenggantiNama);

        return MonitoringKehadiranGuru::create([
            'jadwal_pelajaran_id' => $jadwalId,
            'tanggal' => $tanggal,
            'status' => $status,
            'keterangan' => $keterangan,
            'keterangan_lain' => $data['keterangan_lain'] ?? null,
            'lama_terlambat' => $status === 'terlambat' ? (int) $lamaTerlambat : null,
            'ada_pengganti' => $adaPengganti,
            'guru_pengganti_id' => $guruPenggantiId,
            'guru_pengganti_nama' => $guruPenggantiNama,
            'dicatat_oleh' => $userId,
        ]);
    }

    /**
     * Update entri monitoring.
     */
    public function updateMonitoring(int $id, array $data, User $user): MonitoringKehadiranGuru
    {
        $mon = MonitoringKehadiranGuru::findOrFail($id);

        // BR-02: Guru Piket hanya boleh mengedit monitoring hari ini
        if (!$user->isSuperAdmin() && !$user->isRole('admin_sekolah') && $mon->tanggal->format('Y-m-d') !== date('Y-m-d')) {
            throw new \Exception("Guru piket hanya diperbolehkan mengubah data monitoring hari ini.");
        }

        $status = $data['status'];
        $keterangan = $data['keterangan'] ?? null;
        $lamaTerlambat = $data['lama_terlambat'] ?? null;
        $guruPenggantiId = $data['guru_pengganti_id'] ?? null;
        $guruPenggantiNama = $data['guru_pengganti_nama'] ?? null;

        if ($guruPenggantiId && !$guruPenggantiNama) {
            $g = Guru::find($guruPenggantiId);
            if ($g) {
                $guruPenggantiNama = $g->nama;
            }
        }

        $adaPengganti = !empty($guruPenggantiId) || !empty($guruPenggantiNama);

        $mon->update([
            'status' => $status,
            'keterangan' => $keterangan,
            'keterangan_lain' => $data['keterangan_lain'] ?? null,
            'lama_terlambat' => $status === 'terlambat' ? (int) $lamaTerlambat : null,
            'ada_pengganti' => $adaPengganti,
            'guru_pengganti_id' => $guruPenggantiId,
            'guru_pengganti_nama' => $guruPenggantiNama,
        ]);

        return $mon;
    }

    /**
     * Data Live Board untuk WAKA Kurikulum.
     */
    public function getLiveBoardData(string $jamFilter = 'all'): array
    {
        $tanggal = date('Y-m-d');
        $hari = $this->getHariIndo($tanggal);

        $schedules = JadwalPelajaran::with([
            'kelas',
            'guru',
            'monitoring' => function ($q) use ($tanggal) {
                $q->where('tanggal', $tanggal)->with(['guruPengganti']);
            },
        ])
            ->where('hari', $hari)
            ->orderBy('jam_mulai', 'asc')
            ->orderBy('kelas_id', 'asc')
            ->get();

        $summary = [
            'hadir' => 0,
            'tidak_hadir' => 0,
            'terlambat' => 0,
            'belum_dimonitor' => 0,
        ];

        // Kelompokkan per jam_mulai - jam_selesai
        $grouped = [];
        $jamTersedia = [];

        foreach ($schedules as $s) {
            $jamKey = Carbon::parse($s->jam_mulai)->format('H:i') . '-' . Carbon::parse($s->jam_selesai)->format('H:i');
            if (!in_array($jamKey, $jamTersedia, true)) {
                $jamTersedia[] = $jamKey;
            }

            $mon = $s->monitoring->first();
            if ($mon) {
                if ($mon->status === 'hadir') {
                    $summary['hadir']++;
                } elseif ($mon->status === 'tidak_hadir') {
                    $summary['tidak_hadir']++;
                } elseif ($mon->status === 'terlambat') {
                    $summary['terlambat']++;
                }
            } else {
                $summary['belum_dimonitor']++;
            }

            if ($jamFilter !== 'all' && $jamKey !== $jamFilter) {
                continue;
            }

            if (!isset($grouped[$jamKey])) {
                $grouped[$jamKey] = [
                    'jam_mulai' => Carbon::parse($s->jam_mulai)->format('H:i'),
                    'jam_selesai' => Carbon::parse($s->jam_selesai)->format('H:i'),
                    'label_jam' => $jamKey,
                    'kelas' => [],
                ];
            }

            $grouped[$jamKey]['kelas'][] = [
                'jadwal_pelajaran_id' => $s->id,
                'nama_kelas' => $s->kelas ? $s->kelas->nama_kelas : '-',
                'nama_guru' => $s->guru ? $s->guru->nama : 'Belum ada guru',
                'mata_pelajaran' => $s->mata_pelajaran,
                'status' => $mon ? $mon->status : 'belum_dimonitor',
                'keterangan' => $mon ? $mon->keterangan : null,
                'keterangan_lain' => $mon ? $mon->keterangan_lain : null,
                'guru_pengganti_nama' => $mon ? ($mon->guru_pengganti_nama ?: ($mon->guruPengganti ? $mon->guruPengganti->nama : null)) : null,
                'lama_terlambat' => $mon ? $mon->lama_terlambat : null,
                'jam_mulai_raw' => $s->jam_mulai,
            ];
        }

        return [
            'tanggal' => $tanggal,
            'hari' => $hari,
            'generated_at' => Carbon::now()->toIso8601String(),
            'summary' => $summary,
            'jam_tersedia' => $jamTersedia,
            'kelas_per_jam' => array_values($grouped),
        ];
    }

    /**
     * Data rekap monitoring untuk Admin.
     */
    public function getAdminRekap(array $filters, int $perPage = 25)
    {
        $query = MonitoringKehadiranGuru::with([
            'jadwalPelajaran.kelas',
            'jadwalPelajaran.guru',
            'guruPengganti',
            'pencatat',
        ]);

        if (!empty($filters['tanggal_dari'])) {
            $query->where('tanggal', '>=', $filters['tanggal_dari']);
        }
        if (!empty($filters['tanggal_sampai'])) {
            $query->where('tanggal', '<=', $filters['tanggal_sampai']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['kelas_id'])) {
            $query->whereHas('jadwalPelajaran', function ($q) use ($filters) {
                $q->where('kelas_id', $filters['kelas_id']);
            });
        }
        if (!empty($filters['guru_id'])) {
            $query->whereHas('jadwalPelajaran', function ($q) use ($filters) {
                $q->where('guru_id', $filters['guru_id']);
            });
        }
        if (!empty($filters['tipe_kepegawaian'])) {
            $query->whereHas('jadwalPelajaran.guru', function ($q) use ($filters) {
                $q->where('tipe_kepegawaian', $filters['tipe_kepegawaian']);
            });
        }

        return $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Rekap kehadiran mengajar mandiri untuk Guru (Portal Guru).
     */
    public function getGuruSelfRekap(int $guruId, ?int $bulan = null, ?int $tahun = null)
    {
        $bulan = $bulan ?: (int) date('m');
        $tahun = $tahun ?: (int) date('Y');

        return MonitoringKehadiranGuru::with([
            'jadwalPelajaran.kelas',
            'guruPengganti',
        ])
            ->whereHas('jadwalPelajaran', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    /**
     * Autocomplete pencarian guru untuk guru pengganti.
     */
    public function searchGuru(string $q)
    {
        if (strlen($q) < 2) {
            return [];
        }

        return Guru::where('nama_lengkap', 'like', "%{$q}%")
            ->select('id', 'nama_lengkap as nama', 'nip')
            ->limit(10)
            ->get();
    }
}
