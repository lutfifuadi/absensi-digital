<div>
    @if($show)
        <div class="position-fixed top-0 start-0 w-100 h-100 bg-white" style="z-index: 1050; overflow-y: auto; padding-bottom: env(safe-area-inset-bottom);">
            <!-- Header -->
            <div class="bg-danger text-white p-3 d-flex align-items-center sticky-top shadow-sm">
                <button wire:click="close" class="btn btn-link text-white p-0 me-3">
                    <i class="ti ti-arrow-left fs-4"></i>
                </button>
                <div>
                    <h6 class="mb-0 fw-bold">Tidak Hadir</h6>
                    @if($jadwal)
                        <small class="opacity-75">{{ $jadwal->guru->nama ?? 'Belum ada guru' }}</small>
                    @endif
                </div>
            </div>

            <div class="p-3">
                <form wire:submit.prevent="save">
                    <!-- Keterangan -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Keterangan* <span class="text-muted fw-normal">(pilih satu)</span></label>
                        <div class="d-grid gap-2">
                            <label class="btn btn-outline-danger text-start @if($keterangan == 'sakit') active @endif">
                                <input type="radio" wire:model="keterangan" value="sakit" class="d-none"> 
                                <i class="ti ti-activity me-2"></i> Sakit
                            </label>
                            <label class="btn btn-outline-danger text-start @if($keterangan == 'izin') active @endif">
                                <input type="radio" wire:model="keterangan" value="izin" class="d-none"> 
                                <i class="ti ti-mail me-2"></i> Izin
                            </label>
                            <label class="btn btn-outline-danger text-start @if($keterangan == 'dinas_luar') active @endif">
                                <input type="radio" wire:model="keterangan" value="dinas_luar" class="d-none"> 
                                <i class="ti ti-briefcase me-2"></i> Dinas Luar
                            </label>
                            <label class="btn btn-outline-danger text-start @if($keterangan == 'alfa') active @endif">
                                <input type="radio" wire:model="keterangan" value="alfa" class="d-none"> 
                                <i class="ti ti-alert-triangle me-2"></i> Alfa (Tanpa Keterangan)
                            </label>
                        </div>
                        @error('keterangan') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <!-- Guru Pengganti -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Guru Pengganti <span class="text-muted fw-normal">(opsional)</span></label>
                        @livewire('guru-search-autocomplete', ['initialId' => $guru_pengganti_id, 'initialName' => $guru_pengganti_nama])
                        
                        <div class="mt-2">
                            <label class="form-label small text-muted">Atau ketik nama manual jika tidak ada di daftar:</label>
                            <input type="text" wire:model="guru_pengganti_nama" class="form-control form-control-sm" placeholder="Nama guru pengganti...">
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Catatan Tambahan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea wire:model="keterangan_lain" class="form-control" rows="3" placeholder="Tulis catatan jika ada..."></textarea>
                        @error('keterangan_lain') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold">
                        <span wire:loading.remove wire:target="save">SIMPAN MONITORING</span>
                        <span wire:loading wire:target="save"><i class="ti ti-loader ti-spin"></i> MENYIMPAN...</span>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>