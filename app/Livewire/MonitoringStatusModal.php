<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\JadwalPelajaran;
use App\Models\MonitoringKehadiranGuru;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MonitoringStatusModal extends Component
{
    public $jadwalId;
    public $monitoringId;
    public $jadwal;
    public $show = false;

    protected $listeners = ['openStatusModal'];

    public function openStatusModal($jadwalId, $monitoringId = null)
    {
        $this->jadwalId = $jadwalId;
        $this->monitoringId = $monitoringId;
        $this->jadwal = JadwalPelajaran::with(['kelas', 'guru'])->find($jadwalId);
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->jadwalId = null;
        $this->monitoringId = null;
        $this->jadwal = null;
    }

    public function setHadir()
    {
        MonitoringKehadiranGuru::updateOrCreate(
            [
                'jadwal_pelajaran_id' => $this->jadwalId,
                'tanggal' => Carbon::now()->toDateString()
            ],
            [
                'status' => 'hadir',
                'keterangan' => null,
                'keterangan_lain' => null,
                'lama_terlambat' => null,
                'ada_pengganti' => false,
                'guru_pengganti_id' => null,
                'guru_pengganti_nama' => null,
                'dicatat_oleh' => Auth::id()
            ]
        );

        $this->dispatch('monitoringUpdated');
        $this->close();
    }

    public function setTidakHadir()
    {
        $this->close();
        $this->dispatch('openFormTidakHadir', $this->jadwalId, $this->monitoringId);
    }

    public function setTerlambat()
    {
        $this->close();
        $this->dispatch('openFormTerlambat', $this->jadwalId, $this->monitoringId);
    }

    public function render()
    {
        return view('livewire.monitoring-status-modal');
    }
}