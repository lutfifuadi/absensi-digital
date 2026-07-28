<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\JadwalPelajaran;
use Carbon\Carbon;

class LiveBoardWaka extends Component
{
    public $filterJam = 'semua';
    public $lastUpdate;

    public function getJadwalsProperty()
    {
        $hariIni = Carbon::now();
        $namaHari = $hariIni->locale('id')->isoFormat('dddd');

        $query = JadwalPelajaran::with(['kelas', 'guru', 'monitoring' => function ($q) use ($hariIni) {
            $q->whereDate('tanggal', $hariIni->toDateString());
        }])->where('hari', $namaHari)->orderBy('jam_mulai');

        if ($this->filterJam !== 'semua') {
            $jam = explode('-', $this->filterJam);
            if (count($jam) == 2) {
                $query->where('jam_mulai', '>=', trim($jam[0]))
                      ->where('jam_mulai', '<=', trim($jam[1]));
            }
        }

        return $query->get();
    }

    public function setFilterJam($jam)
    {
        $this->filterJam = $jam;
    }

    public function render()
    {
        $jadwals = $this->jadwals;
        $this->lastUpdate = Carbon::now()->format('H:i:s');
        
        $stats = [
            'hadir' => 0,
            'tidak_hadir' => 0,
            'terlambat' => 0,
            'belum_dimonitor' => 0
        ];

        foreach ($jadwals as $j) {
            if (!$j->monitoring) {
                $stats['belum_dimonitor']++;
            } else {
                if (isset($stats[$j->monitoring->status])) {
                    $stats[$j->monitoring->status]++;
                }
            }
        }

        $allJadwals = JadwalPelajaran::where('hari', Carbon::now()->locale('id')->isoFormat('dddd'))->orderBy('jam_mulai')->get();
        $jamGroups = $allJadwals->map(function ($item) {
            return Carbon::parse($item->jam_mulai)->format('H:i') . ' - ' . Carbon::parse($item->jam_selesai)->format('H:i');
        })->unique()->values();

        return view('livewire.live-board-waka', [
            'jadwals' => $jadwals,
            'stats' => $stats,
            'jamGroups' => $jamGroups
        ]);
    }
}