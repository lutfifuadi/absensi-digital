<div>
    @if($show)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable fixed-bottom m-0" style="max-width: 100%; align-items: flex-end;">
                <div class="modal-content rounded-top-4 border-0">
                    <div class="modal-header border-0 pb-0 justify-content-center pt-2">
                        <div style="width: 40px; height: 5px; background: #e0e0e0; border-radius: 5px;"></div>
                    </div>
                    <div class="modal-header border-0 pb-2">
                        <h5 class="modal-title fw-bold">Status Kehadiran</h5>
                        <button type="button" class="btn-close" wire:click="close" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-0 pb-4">
                        @if($jadwal)
                            <div class="mb-3 text-center">
                                <h6 class="fw-bold mb-1">{{ $jadwal->kelas->nama_kelas }} — {{ $jadwal->mata_pelajaran }}</h6>
                                <p class="text-muted mb-0" style="font-size: 14px;">{{ $jadwal->guru->nama ?? 'Belum ada guru' }} • {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</p>
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            <button wire:click="setHadir" class="btn btn-lg btn-success text-start py-3 fs-5 position-relative">
                                <span class="d-flex align-items-center">
                                    <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                                        <i class="ti ti-check"></i>
                                    </div>
                                    HADIR
                                </span>
                            </button>
                            
                            <button wire:click="setTidakHadir" class="btn btn-lg btn-danger text-start py-3 fs-5">
                                <span class="d-flex align-items-center">
                                    <div class="bg-white text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                                        <i class="ti ti-x"></i>
                                    </div>
                                    TIDAK HADIR
                                </span>
                            </button>
                            
                            <button wire:click="setTerlambat" class="btn btn-lg btn-warning text-start py-3 fs-5 text-dark">
                                <span class="d-flex align-items-center">
                                    <div class="bg-white text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                                        <i class="ti ti-clock"></i>
                                    </div>
                                    TERLAMBAT
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>