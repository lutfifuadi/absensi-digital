<div wire:poll.30s>
    <!-- Header -->
    <div class="bg-primary text-white p-3 d-flex justify-content-between align-items-center shadow-sm">
        <div>
            <h4 class="mb-0 fw-bold d-flex align-items-center gap-2">
                <i class="ti ti-school fs-3"></i> 
                LIVE BOARD MONITORING KEHADIRAN GURU
            </h4>
            <div class="opacity-75 fs-6 mt-1">
                {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}
            </div>
        </div>
        <div class="text-end">
            <h2 class="mb-0 fw-bold">{{ \Carbon\Carbon::now()->format('H:i') }}</h2>
            <div class="opacity-75" style="font-size: 14px;">Waktu Saat Ini</div>
        </div>
    </div>

    <!-- Jam Filter -->
    <div class="bg-white border-bottom p-2 px-4 shadow-sm d-flex gap-2 overflow-auto" style="white-space: nowrap;">
        <button wire:click="setFilterJam('semua')" class="btn btn-sm rounded-pill fw-medium {{ $filterJam === 'semua' ? 'btn-dark' : 'btn-outline-secondary' }}">
            Semua Jam ▼
        </button>
        @foreach($jamGroups as $jam)
            <button wire:click="setFilterJam('{{ $jam }}')" class="btn btn-sm rounded-pill fw-medium {{ $filterJam === $jam ? 'btn-dark' : 'btn-outline-secondary' }}">
                {{ $jam }}
            </button>
        @endforeach
    </div>

    <!-- Grid Layout -->
    <div class="container-fluid p-4" style="height: calc(100vh - 220px); overflow-y: auto;">
        @if($jadwals->isEmpty())
            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                <i class="ti ti-layout-grid-add" style="font-size: 5rem; opacity: 0.2;"></i>
                <h4 class="mt-3">Tidak ada data untuk jam yang dipilih</h4>
            </div>
        @else
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 row-cols-xxl-6 g-3">
                @foreach($jadwals as $j)
                    @php
                        $monitoring = $j->monitoring;
                        $status = $monitoring ? $monitoring->status : null;
                        
                        // Default (Belum Dimonitor)
                        $bgColor = 'bg-secondary';
                        $bgSubtle = 'bg-secondary-subtle';
                        $borderColor = 'border-secondary';
                        $textColor = 'text-white';
                        $icon = 'ti-clock';
                        $statusText = 'BELUM DIMONITOR';
                        $descText = '🕐 Sejak ' . \Carbon\Carbon::parse($j->jam_mulai)->format('H:i');
                        
                        if ($status == 'hadir') {
                            $bgColor = 'bg-success';
                            $bgSubtle = 'bg-success-subtle';
                            $borderColor = 'border-success';
                            $textColor = 'text-white';
                            $icon = 'ti-check';
                            $statusText = 'HADIR';
                            $descText = '';
                        } elseif ($status == 'tidak_hadir') {
                            $bgColor = 'bg-danger';
                            $bgSubtle = 'bg-danger-subtle';
                            $borderColor = 'border-danger';
                            $textColor = 'text-white';
                            $icon = 'ti-x';
                            $statusText = 'TIDAK HADIR';
                            $descText = ucfirst($monitoring->keterangan);
                        } elseif ($status == 'terlambat') {
                            $bgColor = 'bg-warning';
                            $bgSubtle = 'bg-warning-subtle';
                            $borderColor = 'border-warning';
                            $textColor = 'text-dark';
                            $icon = 'ti-alert-circle';
                            $statusText = 'TERLAMBAT';
                            $descText = $monitoring->lama_terlambat . ' menit';
                        }
                    @endphp
                    
                    <div class="col">
                        <div class="card h-100 border {{ $borderColor }} shadow-sm overflow-hidden" style="border-width: 2px !important;">
                            <!-- Header Kartu -->
                            <div class="card-header {{ $bgColor }} {{ $textColor }} border-0 py-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold fs-5">{{ $j->kelas->nama_kelas }}</span>
                                <span class="badge bg-white text-dark shadow-sm">{{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}</span>
                            </div>
                            
                            <!-- Body Kartu -->
                            <div class="card-body p-3 d-flex flex-column justify-content-center">
                                <h6 class="card-subtitle mb-1 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ $j->mata_pelajaran }}</h6>
                                <h5 class="card-title fw-bold mb-3">{{ $j->guru->nama ?? 'Belum ada guru' }}</h5>
                                
                                <div class="mt-auto">
                                    <div class="d-flex align-items-center gap-2 fw-bold {{ $status == 'terlambat' ? 'text-warning' : ($status == 'hadir' ? 'text-success' : ($status == 'tidak_hadir' ? 'text-danger' : 'text-secondary')) }}">
                                        <i class="ti {{ $icon }} fs-4"></i>
                                        <span class="fs-6">{{ $statusText }}</span>
                                    </div>
                                    @if($descText)
                                        <div class="small text-muted mt-1 fw-medium">{{ $descText }}</div>
                                    @endif
                                    
                                    @if($status == 'tidak_hadir' && $monitoring->ada_pengganti)
                                        <div class="small mt-2 pt-2 border-top text-primary fw-bold">
                                            <i class="ti ti-replace me-1"></i> 🔄 {{ $monitoring->guru_pengganti_nama ?? ($monitoring->guruPengganti->nama ?? 'Pengganti') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Sticky Footer Rekap -->
    <div class="fixed-bottom bg-dark text-white p-3 shadow-lg d-flex justify-content-between align-items-center">
        <div class="d-flex gap-4 fs-5 fw-bold">
            <span class="text-success"><i class="ti ti-check bg-white text-success rounded-circle px-1 me-1"></i> HADIR: {{ $stats['hadir'] }}</span>
            <span class="text-danger"><i class="ti ti-x bg-white text-danger rounded-circle px-1 me-1"></i> TIDAK HADIR: {{ $stats['tidak_hadir'] }}</span>
            <span class="text-warning"><i class="ti ti-clock bg-white text-warning rounded-circle px-1 me-1"></i> TERLAMBAT: {{ $stats['terlambat'] }}</span>
            <span class="text-secondary"><i class="ti ti-square bg-white text-secondary rounded-circle px-1 me-1"></i> BELUM DIMONITOR: {{ $stats['belum_dimonitor'] }}</span>
        </div>
        <div class="text-muted small d-flex align-items-center gap-2">
            <div class="spinner-grow spinner-grow-sm text-primary" role="status" style="width: 10px; height: 10px;"></div>
            🔄 Auto-refresh: 30 detik (Terakhir: {{ $lastUpdate }})
        </div>
    </div>
</div>