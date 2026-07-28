<?php

namespace App\Services\Api\V2\Monitoring;

use App\Models\JadwalPelajaran;
use App\Models\MonitoringKehadiranGuru;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonitoringService
{
    /**
     * Ambil daftar monitoring hari ini.
     *
     * @param string $filter
     * @return array
     */
    public function getTodayMonitoringList(string $filter = 'all'): array
    {
        $today = Carbon::today();
        $hariIni = $this->getIndonesianDayName($today->dayOfWeek);

        $jadwals = JadwalPelajaran::with(['kelas', 'guru'])
            ->where('hari', $hariIni)
            ->get();

        $jadwalIds = $jadwals->pluck('id');
        
        $monitorings = MonitoringKehadiranGuru::whereIn('jadwal_pelajaran_id', $jadwalIds)
            ->whereDate('tanggal', $today)
            ->with(['guruPengganti', 'pencatat'])
            ->get()
            ->keyBy('jadwal_pelajaran_id');

        $result = [];
        $summary = [
            'hadir' => 0,
            'tidak_hadir' => 0,
            'terlambat' => 0,
            'belum_dimonitor' => 0,
        ];

        foreach ($jadwals as $jadwal) {
            $monitoring = $monitorings->get($jadwal->id);

            $status = $monitoring ? $monitoring->status : 'belum_dimonitor';

            if ($status === 'hadir') $summary['hadir']++;
            elseif ($status === 'tidak_hadir') $summary['tidak_hadir']++;
            elseif ($status === 'terlambat') $summary['terlambat']++;
            else $summary['belum_dimonitor']++;

            if ($filter === 'belum' && $monitoring) continue;
            if ($filter === 'sudah' && !$monitoring) continue;

            $result[] = [
                'jadwal_pelajaran_id' => $jadwal->id,
                'jam_mulai' => Carbon::parse($jadwal->jam_mulai)->format('H:i'),
                'jam_selesai' => Carbon::parse($jadwal->jam_selesai)->format('H:i'),
                'kelas' => [
                    'id' => $jadwal->kelas->id,
                    'nama_kelas' => $jadwal->kelas->nama_kelas,
                ],
                'guru' => $jadwal->guru ? [
                    'id' => $jadwal->guru->id,
                    'nama' => $jadwal->guru->nama,
                ] : null,
                'mata_pelajaran' => $jadwal->mata_pelajaran,
                'monitoring' => $monitoring ? [
                    'id' => $monitoring->id,
                    'status' => $monitoring->status,
                    'keterangan' => $monitoring->keterangan,
                    'keterangan_lain' => $monitoring->keterangan_lain,
                    'lama_terlambat' => $monitoring->lama_terlambat,
                    'ada_pengganti' => $monitoring->ada_pengganti,
                    'guru_pengganti_nama' => $monitoring->guru_pengganti_nama ?? ($monitoring->guruPengganti ? $monitoring->guruPengganti->nama : null),
                    'dicatat_oleh' => $monitoring->pencatat ? $monitoring->pencatat->name : null,
                    'created_at' => $monitoring->created_at->format('Y-m-d\TH:i:s'),
                ] : null,
            ];
        }

        return [
            'tanggal' => $today->format('Y-m-d'),
            'hari' => $hariIni,
            'summary' => $summary,
            'jam_pelajaran' => collect($result)->sortBy('jam_mulai')->values()->all(),
        ];
    }
    
    /**
     * Ambil data live board.
     *
     * @param string $jamFilter
     * @return array
     */
    public function getLiveBoardData(string $jamFilter = 'all'): array
    {
        $today = Carbon::today();
        $hariIni = $this->getIndonesianDayName($today->dayOfWeek);
        
        $query = JadwalPelajaran::with(['kelas', 'guru'])
            ->where('hari', $hariIni);
            
        if ($jamFilter !== 'all') {
            $parts = explode('-', $jamFilter);
            if (count($parts) === 2) {
                $query->where('jam_mulai', $parts[0])
                      ->where('jam_selesai', $parts[1]);
            }
        }
        
        $jadwals = $query->get();
        $jadwalIds = $jadwals->pluck('id');
        
        $monitorings = MonitoringKehadiranGuru::whereIn('jadwal_pelajaran_id', $jadwalIds)
            ->whereDate('tanggal', $today)
            ->with(['guruPengganti'])
            ->get()
            ->keyBy('jadwal_pelajaran_id');
            
        // Setup summary
        $summary = [
            'hadir' => 0,
            'tidak_hadir' => 0,
            'terlambat' => 0,
            'belum_dimonitor' => 0,
        ];
        
        // Setup class by jam
        $kelasPerJamMap = [];
        $jamTersedia = [];
        
        foreach ($jadwals as $jadwal) {
            $monitoring = $monitorings->get($jadwal->id);
            $status = $monitoring ? $monitoring->status : 'belum_dimonitor';
            
            // Update summary
            if ($status === 'hadir') $summary['hadir']++;
            elseif ($status === 'tidak_hadir') $summary['tidak_hadir']++;
            elseif ($status === 'terlambat') $summary['terlambat']++;
            else $summary['belum_dimonitor']++;
            
            $jamKey = Carbon::parse($jadwal->jam_mulai)->format('H:i') . '-' . Carbon::parse($jadwal->jam_selesai)->format('H:i');
            
            if (!isset($kelasPerJamMap[$jamKey])) {
                $kelasPerJamMap[$jamKey] = [
                    'jam_mulai' => Carbon::parse($jadwal->jam_mulai)->format('H:i'),
                    'jam_selesai' => Carbon::parse($jadwal->jam_selesai)->format('H:i'),
                    'kelas' => [],
                ];
                $jamTersedia[] = $jamKey;
            }
            
            $kelasPerJamMap[$jamKey]['kelas'][] = [
                'jadwal_pelajaran_id' => $jadwal->id,
                'nama_kelas' => $jadwal->kelas->nama_kelas,
                'nama_guru' => $jadwal->guru ? $jadwal->guru->nama : null,
                'mata_pelajaran' => $jadwal->mata_pelajaran,
                'status' => $status,
                'keterangan' => $monitoring ? $monitoring->keterangan : null,
                'guru_pengganti_nama' => $monitoring ? ($monitoring->guru_pengganti_nama ?? ($monitoring->guruPengganti ? $monitoring->guruPengganti->nama : null)) : null,
                'lama_terlambat' => $monitoring ? $monitoring->lama_terlambat : null,
            ];
        }
        
        sort($jamTersedia);
        usort($kelasPerJamMap, function($a, $b) {
            return strcmp($a['jam_mulai'], $b['jam_mulai']);
        });

        return [
            'tanggal' => $today->format('Y-m-d'),
            'hari' => $hariIni,
            'generated_at' => now()->format('Y-m-d\TH:i:s'),
            'summary' => $summary,
            'jam_tersedia' => $jamTersedia,
            'kelas_per_jam' => array_values($kelasPerJamMap),
        ];
    }
    
    private function getIndonesianDayName(int $dayOfWeek): string
    {
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        return $days[$dayOfWeek] ?? '';
    }
}
