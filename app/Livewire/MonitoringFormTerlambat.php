<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\JadwalPelajaran;
use App\Models\MonitoringKehadiranGuru;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MonitoringFormTerlambat extends Component
{
    public $jadwalId;
    public $monitoringId;
    public $jadwal;
    public $show = false;

    public $lama_terlambat = 15;
    public $keterangan_lain = '';
    
    protected $listeners = ['openFormTerlambat'];

    protected $rules = [
        'lama_terlambat' => 'required|integer|min:1|max:120',
        'keterangan_lain' => 'nullable|string|max:500',
    ];

    public function openFormTerlambat($jadwalId, $monitoringId = null)
    {
        $this->jadwalId = $jadwalId;
        $this->monitoringId = $monitoringId;
        $this->jadwal = JadwalPelajaran::with(['kelas', 'guru'])->find($jadwalId);
        
        if ($monitoringId) {
            $m = MonitoringKehadiranGuru::find($monitoringId);
            if ($m && $m->status === 'terlambat') {
                $this->lama_terlambat = $m->lama_terlambat ?? 15;
                $this->keterangan_lain = $m->keterangan_lain;
            }
        } else {
            $this->resetForm();
        }
        
        $this->show = true;
    }

    public function resetForm()
    {
        $this->lama_terlambat = 15;
        $this->keterangan_lain = '';
    }

    public function incrementTime()
    {
        if ($this->lama_terlambat < 115) {
            $this->lama_terlambat += 5;
        } else {
            $this->lama_terlambat = 120;
        }
    }

    public function decrementTime()
    {
        if ($this->lama_terlambat > 5) {
            $this->lama_terlambat -= 5;
        } else {
            $this->lama_terlambat = 1;
        }
    }

    public function close()
    {
        $this->show = false;
        $this->dispatch('openStatusModal', $this->jadwalId, $this->monitoringId);
    }

    public function save()
    {
        $this->validate();

        MonitoringKehadiranGuru::updateOrCreate(
            [
                'jadwal_pelajaran_id' => $this->jadwalId,
                'tanggal' => Carbon::now()->toDateString()
            ],
            [
                'status' => 'terlambat',
                'keterangan' => null,
                'keterangan_lain' => $this->keterangan_lain,
                'lama_terlambat' => $this->lama_terlambat,
                'ada_pengganti' => false,
                'guru_pengganti_id' => null,
                'guru_pengganti_nama' => null,
                'dicatat_oleh' => Auth::id()
            ]
        );

        $this->dispatch('monitoringUpdated');
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.monitoring-form-terlambat');
    }
}