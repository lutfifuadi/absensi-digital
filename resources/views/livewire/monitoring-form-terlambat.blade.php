<div>
    @if($show)
        <div class="position-fixed top-0 start-0 w-100 h-100 bg-white" style="z-index: 1050; overflow-y: auto; padding-bottom: env(safe-area-inset-bottom);">
            <!-- Header -->
            <div class="bg-warning text-dark p-3 d-flex align-items-center sticky-top shadow-sm">
                <button wire:click="close" class="btn btn-link text-dark p-0 me-3">
                    <i class="ti ti-arrow-left fs-4"></i>
                </button>
                <div>
                    <h6 class="mb-0 fw-bold">Terlambat</h6>
                    @if($jadwal)
                        <small class="opacity-75">{{ $jadwal->guru->nama ?? 'Belum ada guru' }}</small>
                    @endif
                </div>
            </div>

            <div class="p-3">
                <form wire:submit.prevent="save">
                    <!-- Lama Keterlambatan -->
                    <div class="mb-4 text-center">
                        <label class="form-label fw-bold">Lama Keterlambatan*</label>
                        
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" wire:model="lama_terlambat" class="form-control text-center fs-3 fw-bold py-2" min="1" max="120">
                                <span class="input-group-text">menit</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" wire:click="decrementTime" class="btn btn-outline-secondary px-4 py-2" style="min-width: 44px;">
                                <i class="ti ti-minus"></i> 5
                            </button>
                            <button type="button" wire:click="incrementTime" class="btn btn-outline-secondary px-4 py-2" style="min-width: 44px;">
                                <i class="ti ti-plus"></i> 5
                            </button>
                        </div>
                        @error('lama_terlambat') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Keterangan / Alasan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea wire:model="keterangan_lain" class="form-control" rows="3" placeholder="Alasan keterlambatan..."></textarea>
                        @error('keterangan_lain') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold mt-4">
                        <span wire:loading.remove wire:target="save">SIMPAN MONITORING</span>
                        <span wire:loading wire:target="save"><i class="ti ti-loader ti-spin"></i> MENYIMPAN...</span>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>