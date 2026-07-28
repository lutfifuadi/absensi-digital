<div>
    <!-- Tabs -->
    <div class="d-flex gap-2 mb-3 overflow-auto pb-1" style="white-space: nowrap;">
        <button wire:click="setFilter('semua')" class="btn btn-sm rounded-pill {{ $filter === 'semua' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Semua
        </button>
        <button wire:click="setFilter('belum')" class="btn btn-sm rounded-pill {{ $filter === 'belum' ? 'btn-danger' : 'btn-outline-secondary' }}">
            Belum ({{ $count['belum'] }}) 🔴
        </button>
        <button wire:click="setFilter('sudah')" class="btn btn-sm rounded-pill {{ $filter === 'sudah' ? 'btn-success' : 'btn-outline-secondary' }}">
            Sudah ({{ $count['sudah'] }}) ✓
        </button>
    </div>

    @if($groupedJadwals->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="ti ti-calendar-off mb-2" style="font-size: 3rem;"></i>
            <p>Tidak ada jadwal pelajaran hari ini 🎉<br>atau sesuai filter yang dipilih.</p>
        </div>
    @else
        @foreach($groupedJadwals as $jam => $jadwals)
            <div class="mb-4">
                <div class="bg-light px-3 py-2 rounded-top border fw-semibold text-muted mb-0 d-flex align-items-center" style="font-size: 12px;">
                    <i class="ti ti-clock me-1"></i> JAM {{ $jam }}
                </div>
                <div class="border border-top-0 rounded-bottom p-2 bg-white">
                    @foreach($jadwals as $j)
                        @php
                            $monitoring = $j->monitoring;
                            $status = $monitoring ? $monitoring->status : null;
                            $borderColor = 'border-secondary';
                            $bgColor = 'bg-white';
                            $icon = 'ti-circle';
                            $iconColor = 'text-muted';
                            
                            if ($status == 'hadir') {
                                $borderColor = 'border-success';
                                $bgColor = 'bg-success-subtle';
                                $icon = 'ti-check';
                                $iconColor = 'text-success';
                            } elseif ($status == 'tidak_hadir') {
                                $borderColor = 'border-danger';
                                $bgColor = 'bg-danger-subtle';
                                $icon = 'ti-x';
                                $iconColor = 'text-danger';
                            } elseif ($status == 'terlambat') {
                                $borderColor = 'border-warning';
                                $bgColor = 'bg-warning-subtle';
                                $icon = 'ti-clock';
                                $iconColor = 'text-warning';
                            }
                        @endphp
                        <div class="card mb-2 border {{ $borderColor }} shadow-none">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 fw-bold d-flex align-items-center gap-1">
                                            <i class="ti {{ $icon }} {{ $iconColor }}"></i> 
                                            {{ $j->kelas->nama_kelas }}
                                        </h6>
                                        <div class="text-muted" style="font-size: 13px;">
                                            {{ $j->mata_pelajaran }} • {{ $j->guru->nama ?? 'Belum ada guru' }}
                                        </div>
                                    </div>
                                    @if($monitoring)
                                        <button wire:click="openStatusModal({{ $j->id }}, {{ $monitoring->id }})" class="btn btn-sm btn-light py-1 px-2 text-primary">
                                            <i class="ti ti-pencil"></i> Edit
                                        </button>
                                    @endif
                                </div>

                                @if($monitoring)
                                    <div class="mt-2 text-{{ $status == 'hadir' ? 'success' : ($status == 'tidak_hadir' ? 'danger' : 'warning') }} fw-medium" style="font-size: 14px;">
                                        {{ str_replace('_', ' ', strtoupper($status)) }}
                                        @if($monitoring->keterangan) — {{ ucfirst($monitoring->keterangan) }} @endif
                                        @if($status == 'terlambat') ({{ $monitoring->lama_terlambat }} menit) @endif
                                    </div>
                                @else
                                    <button wire:click="openStatusModal({{ $j->id }})" class="btn btn-primary w-100 mt-2 btn-sm fw-semibold">
                                        <i class="ti ti-check mb-1"></i> Catat Kehadiran
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    <!-- Sticky Footer Summary -->
    <div class="fixed-bottom bg-white border-top p-2 shadow-sm d-flex justify-content-around text-center" style="font-size: 12px; z-index: 1000; padding-bottom: env(safe-area-inset-bottom);">
        <div><div class="fw-bold text-success">{{ $count['hadir'] }}</div><span class="text-muted">Hadir</span></div>
        <div><div class="fw-bold text-danger">{{ $count['tidakHadir'] }}</div><span class="text-muted">Tdk Hadir</span></div>
        <div><div class="fw-bold text-warning">{{ $count['terlambat'] }}</div><span class="text-muted">Terlambat</span></div>
    </div>
    <div style="height: 60px;"></div> <!-- Spacer for footer -->
</div>