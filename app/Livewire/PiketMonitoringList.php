<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\JadwalPelajaran;
use App\Models\MonitoringKehadiranGuru;
use Carbon\Carbon;

class PiketMonitoringList extends Component
{
    public $filter = 'semua';
    protected $listeners = ['monitoringUpdated' => '$refresh'];

    public function setFilter($val)
    {
        $this->filter = $val;
    }

    public function openStatusModal($jadwalId, $monitoringId = null)
    {
        $this->dispatch('openStatusModal', $jadwalId, $monitoringId);
    }

    public function render()
    {
        $hariIni = Carbon::now();
        $namaHari = $hariIni->locale('id')->isoFormat('dddd');

        $jadwalQuery = JadwalPelajaran::with(['kelas', 'guru', 'monitoring' => function ($q) use ($hariIni) {
            $q->whereDate('tanggal', $hariIni->toDateString());
        }])->where('hari', $namaHari)->orderBy('jam_mulai');

        $jadwals = $jadwalQuery->get();
        
        $belum = $jadwals->whereNull('monitoring')->count();
        $sudah = $jadwals->whereNotNull('monitoring')->count();
        $total = $jadwals->count();

        $hadir = $jadwals->where('monitoring.status', 'hadir')->count();
        $tidakHadir = $jadwals->where('monitoring.status', 'tidak_hadir')->count();
        $terlambat = $jadwals->where('monitoring.status', 'terlambat')->count();

        if ($this->filter === 'belum') {
            $jadwals = $jadwals->whereNull('monitoring');
        } elseif ($this->filter === 'sudah') {
            $jadwals = $jadwals->whereNotNull('monitoring');
        }

        $groupedJadwals = $jadwals->groupBy(function ($item) {
            return Carbon::parse($item->jam_mulai)->format('H:i') . ' - ' . Carbon::parse($item->jam_selesai)->format('H:i');
        });

        return view('livewire.piket-monitoring-list', [
            'groupedJadwals' => $groupedJadwals,
            'count' => compact('total', 'belum', 'sudah', 'hadir', 'tidakHadir', 'terlambat')
        ]);
    }
}