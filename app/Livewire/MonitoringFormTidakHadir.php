<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\JadwalPelajaran;
use App\Models\MonitoringKehadiranGuru;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MonitoringFormTidakHadir extends Component
{
    public $jadwalId;
    public $monitoringId;
    public $jadwal;
    public $show = false;

    public $keterangan = '';
    public $keterangan_lain = '';
    public $guru_pengganti_id = null;
    public $guru_pengganti_nama = '';
    
    protected $listeners = ['openFormTidakHadir', 'guruSelected'];

    protected $rules = [
        'keterangan' => 'required|in:sakit,izin,dinas_luar,alfa',
        'keterangan_lain' => 'nullable|string|max:500',
    ];

    public function openFormTidakHadir($jadwalId, $monitoringId = null)
    {
        $this->jadwalId = $jadwalId;
        $this->monitoringId = $monitoringId;
        $this->jadwal = JadwalPelajaran::with(['kelas', 'guru'])->find($jadwalId);
        
        if ($monitoringId) {
            $m = MonitoringKehadiranGuru::find($monitoringId);
            if ($m && $m->status === 'tidak_hadir') {
                $this->keterangan = $m->keterangan;
                $this->keterangan_lain = $m->keterangan_lain;
                $this->guru_pengganti_id = $m->guru_pengganti_id;
                $this->guru_pengganti_nama = $m->guru_pengganti_nama;
            }
        } else {
            $this->resetForm();
        }
        
        $this->show = true;
    }

    public function guruSelected($id, $nama)
    {
        $this->guru_pengganti_id = $id;
        $this->guru_pengganti_nama = $nama;
    }

    public function resetForm()
    {
        $this->keterangan = '';
        $this->keterangan_lain = '';
        $this->guru_pengganti_id = null;
        $this->guru_pengganti_nama = '';
    }

    public function close()
    {
        $this->show = false;
        $this->dispatch('openStatusModal', $this->jadwalId, $this->monitoringId);
    }

    public function save()
    {
        $this->validate();

        $ada_pengganti = !empty($this->guru_pengganti_id) || !empty($this->guru_pengganti_nama);

        MonitoringKehadiranGuru::updateOrCreate(
            [
                'jadwal_pelajaran_id' => $this->jadwalId,
                'tanggal' => Carbon::now()->toDateString()
            ],
            [
                'status' => 'tidak_hadir',
                'keterangan' => $this->keterangan,
                'keterangan_lain' => $this->keterangan_lain,
                'lama_terlambat' => null,
                'ada_pengganti' => $ada_pengganti,
                'guru_pengganti_id' => $this->guru_pengganti_id,
                'guru_pengganti_nama' => $this->guru_pengganti_nama,
                'dicatat_oleh' => Auth::id()
            ]
        );

        $this->dispatch('monitoringUpdated');
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.monitoring-form-tidak-hadir');
    }
}