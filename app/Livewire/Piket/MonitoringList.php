<?php

namespace App\Livewire\Piket;

use App\Services\MonitoringService;
use Livewire\Component;

class MonitoringList extends Component
{
    public string $tanggal;
    public string $filter = 'all';
    public ?int $selectedJadwalId = null;
    public ?int $selectedMonitoringId = null;

    // Form fields
    public string $status = 'hadir';
    public string $keterangan = '';
    public string $keteranganLain = '';
    public ?int $lamaTerlambat = 15;
    public ?int $guruPenggantiId = null;
    public string $guruPenggantiNama = '';

    // Modal state
    public bool $showStatusModal = false;

    public function mount()
    {
        $this->tanggal = date('Y-m-d');
    }

    public function setFilter(string $filter)
    {
        $this->filter = $filter;
    }

    public function openModal(int $jadwalId, ?int $monitoringId = null)
    {
        $this->selectedJadwalId = $jadwalId;
        $this->selectedMonitoringId = $monitoringId;

        if ($monitoringId) {
            $mon = \App\Models\MonitoringKehadiranGuru::find($monitoringId);
            if ($mon) {
                $this->status = $mon->status;
                $this->keterangan = $mon->keterangan ?? '';
                $this->keteranganLain = $mon->keterangan_lain ?? '';
                $this->lamaTerlambat = $mon->lama_terlambat ?? 15;
                $this->guruPenggantiId = $mon->guru_pengganti_id;
                $this->guruPenggantiNama = $mon->guru_pengganti_nama ?? '';
            }
        } else {
            $this->resetForm();
        }

        $this->showStatusModal = true;
    }

    public function closeModal()
    {
        $this->showStatusModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->status = 'hadir';
        $this->keterangan = '';
        $this->keteranganLain = '';
        $this->lamaTerlambat = 15;
        $this->guruPenggantiId = null;
        $this->guruPenggantiNama = '';
    }

    public function selectStatus(string $status)
    {
        $this->status = $status;
        if ($status === 'hadir') {
            $this->saveMonitoring();
        }
    }

    public function saveMonitoring()
    {
        $rules = [
            'status' => 'required|in:hadir,tidak_hadir,terlambat',
        ];

        if ($this->status === 'tidak_hadir') {
            $rules['keterangan'] = 'required|in:sakit,izin,dinas_luar,alfa';
        }
        if ($this->status === 'terlambat') {
            $rules['lamaTerlambat'] = 'required|integer|min:1|max:120';
        }

        $this->validate($rules, [
            'keterangan.required' => 'Keterangan wajib diisi untuk status Tidak Hadir.',
            'lamaTerlambat.required' => 'Lama keterlambatan wajib diisi untuk status Terlambat.',
        ]);

        $service = app(MonitoringService::class);
        $payload = [
            'jadwal_pelajaran_id' => $this->selectedJadwalId,
            'tanggal' => $this->tanggal,
            'status' => $this->status,
            'keterangan' => $this->keterangan ?: null,
            'keterangan_lain' => $this->keteranganLain ?: null,
            'lama_terlambat' => $this->lamaTerlambat,
            'guru_pengganti_id' => $this->guruPenggantiId,
            'guru_pengganti_nama' => $this->guruPenggantiNama ?: null,
        ];

        try {
            if ($this->selectedMonitoringId) {
                $service->updateMonitoring($this->selectedMonitoringId, $payload, auth()->user());
                session()->flash('success', 'Data monitoring berhasil diperbarui.');
            } else {
                $service->storeMonitoring($payload, auth()->id());
                session()->flash('success', 'Catatan kehadiran berhasil disimpan.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $service = app(MonitoringService::class);
        $data = $service->getSchedulesWithMonitoring($this->tanggal, $this->filter);

        return view('livewire.piket.monitoring-list', [
            'data' => $data,
        ]);
    }
}
