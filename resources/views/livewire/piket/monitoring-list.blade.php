<div class="monitoring-piket-container position-relative pb-5">
    {{-- Top Alert Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="ti tabler-circle-check me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="ti tabler-alert-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Header Bar --}}
    <div class="card mb-3 shadow-sm border-0 sticky-top bg-white" style="top: 0; z-index: 10;">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="fw-bold mb-0 text-primary">Monitoring Piket</h5>
                    <small class="text-muted"><i class="ti tabler-calendar me-1"></i>{{ $data['hari'] }}, {{ \Carbon\Carbon::parse($data['tanggal'])->translatedFormat('d F Y') }}</small>
                </div>
                <div>
                    <input type="date" wire:model.live="tanggal" class="form-control form-control-sm border-primary">
                </div>
            </div>

            {{-- Pill Filters --}}
            <div class="d-flex gap-2 nav-pills mt-2">
                <button wire:click="setFilter('all')" class="btn btn-sm flex-fill {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Semua ({{ $data['summary']['total'] }})
                </button>
                <button wire:click="setFilter('belum')" class="btn btn-sm flex-fill {{ $filter === 'belum' ? 'btn-danger' : 'btn-outline-danger' }}">
                    Belum ({{ $data['summary']['belum_dimonitor'] }})
                </button>
                <button wire:click="setFilter('sudah')" class="btn btn-sm flex-fill {{ $filter === 'sudah' ? 'btn-success' : 'btn-outline-success' }}">
                    Sudah ({{ $data['summary']['hadir'] + $data['summary']['tidak_hadir'] + $data['summary']['terlambat'] }})
                </button>
            </div>
        </div>
    </div>

    {{-- Card Items List --}}
    <div class="monitoring-items">
        @forelse($data['jam_pelajaran'] as $item)
            @php
                $mon = $item['monitoring'];
                $status = $mon ? $mon['status'] : 'belum';
                $cardBg = match($status) {
                    'hadir' => 'bg-success text-white',
                    'tidak_hadir' => 'bg-danger text-white',
                    'terlambat' => 'bg-warning text-dark',
                    default => 'bg-light text-dark border-secondary border-dashed',
                };
                $badgeBg = match($status) {
                    'hadir' => 'badge bg-white text-success fw-bold',
                    'tidak_hadir' => 'badge bg-white text-danger fw-bold',
                    'terlambat' => 'badge bg-dark text-warning fw-bold',
                    default => 'badge bg-secondary text-white fw-bold',
                };
            @endphp

            <div class="card mb-3 shadow-sm border-0 {{ $status === 'belum' ? 'border-2' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-label-primary px-3 py-2 fs-6 fw-bold">
                            <i class="ti tabler-clock me-1"></i>{{ $item['jam_mulai'] }} - {{ $item['jam_selesai'] }}
                        </span>
                        <span class="{{ $badgeBg }} px-2 py-1">
                            @if($status === 'hadir') ✅ HADIR
                            @elseif($status === 'tidak_hadir') ❌ TIDAK HADIR
                            @elseif($status === 'terlambat') ⏱ TERLAMBAT
                            @else ⬜ BELUM DIMONITOR
                            @endif
                        </span>
                    </div>

                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="fw-bold mb-1 text-dark">{{ $item['kelas']['nama_kelas'] }}</h5>
                            <p class="mb-1 text-secondary fw-semibold">{{ $item['mata_pelajaran'] }} &bull; {{ $item['guru']['nama'] }}</p>

                            @if($mon)
                                <div class="mt-2 p-2 rounded bg-label-secondary small">
                                    @if($mon['status'] === 'tidak_hadir')
                                        <div class="text-danger fw-bold"><i class="ti tabler-x me-1"></i>Keterangan: {{ strtoupper($mon['keterangan']) }}</div>
                                        @if($mon['guru_pengganti_nama'])
                                            <div class="text-dark mt-1"><i class="ti tabler-user-check me-1"></i>Pengganti: {{ $mon['guru_pengganti_nama'] }}</div>
                                        @endif
                                    @elseif($mon['status'] === 'terlambat')
                                        <div class="text-warning fw-bold text-dark"><i class="ti tabler-clock me-1"></i>Terlambat: {{ $mon['lama_terlambat'] }} Menit</div>
                                    @endif

                                    @if($mon['keterangan_lain'])
                                        <div class="text-muted fst-italic mt-1"><i class="ti tabler-notes me-1"></i>"{{ $mon['keterangan_lain'] }}"</div>
                                    @endif
                                    <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                        <i class="ti tabler-user me-1"></i>Dicatat oleh: {{ $mon['dicatat_oleh'] }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="mt-3">
                        @if($mon)
                            <button wire:click="openModal({{ $item['jadwal_pelajaran_id'] }}, {{ $mon['id'] }})"
                                    class="btn btn-outline-primary w-100 py-2 fw-bold" style="min-height: 44px;">
                                <i class="ti tabler-pencil me-1"></i> Edit Monitoring
                            </button>
                        @else
                            <button wire:click="openModal({{ $item['jadwal_pelajaran_id'] }})"
                                    class="btn btn-primary w-100 py-2 fw-bold shadow-sm" style="min-height: 44px;">
                                <i class="ti tabler-circle-check me-1"></i> Catat Kehadiran
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-5 text-center shadow-sm border-0">
                <i class="ti tabler-calendar-off text-muted mb-2" style="font-size: 3rem;"></i>
                <h5 class="fw-bold text-secondary">Tidak ada jadwal pelajaran</h5>
                <p class="text-muted mb-0">Tidak ditemukan jadwal pelajaran yang sesuai dengan filter hari/tanggal ini.</p>
            </div>
        @endforelse
    </div>

    {{-- Bottom Sticky Summary Bar --}}
    <div class="fixed-bottom bg-dark text-white p-3 shadow-lg border-top border-secondary" style="z-index: 100;">
        <div class="container d-flex justify-content-around text-center">
            <div>
                <span class="fs-6 text-success fw-bold">✅ {{ $data['summary']['hadir'] }}</span>
                <div class="text-white-50 small" style="font-size: 0.75rem;">Hadir</div>
            </div>
            <div>
                <span class="fs-6 text-danger fw-bold">❌ {{ $data['summary']['tidak_hadir'] }}</span>
                <div class="text-white-50 small" style="font-size: 0.75rem;">Tdk Hadir</div>
            </div>
            <div>
                <span class="fs-6 text-warning fw-bold">⏱ {{ $data['summary']['terlambat'] }}</span>
                <div class="text-white-50 small" style="font-size: 0.75rem;">Terlambat</div>
            </div>
            <div>
                <span class="fs-6 text-light fw-bold">⬜ {{ $data['summary']['belum_dimonitor'] }}</span>
                <div class="text-white-50 small" style="font-size: 0.75rem;">Belum</div>
            </div>
        </div>
    </div>

    {{-- Bottom Sheet / Status Modal --}}
    @if($showStatusModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6);" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-3 shadow">
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title fw-bold text-white mb-0">
                            <i class="ti tabler-clipboard-check me-1"></i> Form Monitoring Kehadiran
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        {{-- Pilih Status Main Buttons --}}
                        <label class="form-label fw-bold mb-2">Pilih Status Kehadiran Guru*:</label>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <button type="button" wire:click="selectStatus('hadir')"
                                        class="btn w-100 py-3 fw-bold {{ $status === 'hadir' ? 'btn-success text-white shadow' : 'btn-outline-success' }}"
                                        style="min-height: 50px;">
                                    ✅ HADIR
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="button" wire:click="selectStatus('tidak_hadir')"
                                        class="btn w-100 py-3 fw-bold {{ $status === 'tidak_hadir' ? 'btn-danger text-white shadow' : 'btn-outline-danger' }}"
                                        style="min-height: 50px;">
                                    ❌ TIDAK HADIR
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="button" wire:click="selectStatus('terlambat')"
                                        class="btn w-100 py-3 fw-bold {{ $status === 'terlambat' ? 'btn-warning text-dark shadow' : 'btn-outline-warning text-dark' }}"
                                        style="min-height: 50px;">
                                    ⏱ TERLAMBAT
                                </button>
                            </div>
                        </div>

                        {{-- Additional Form for Tidak Hadir --}}
                        @if($status === 'tidak_hadir')
                            <div class="card p-3 bg-label-danger border-danger mb-3">
                                <label class="form-label fw-bold text-danger">Keterangan Ketidakhadiran*:</label>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @foreach(['sakit' => 'Sakit', 'izin' => 'Izin', 'dinas_luar' => 'Dinas Luar', 'alfa' => 'Alfa'] as $val => $lbl)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" wire:model.live="keterangan" id="ket_{{ $val }}" value="{{ $val }}">
                                            <label class="form-check-label fw-bold" for="ket_{{ $val }}">{{ $lbl }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('keterangan') <span class="text-danger small fw-bold">{{ $message }}</span> @enderror

                                <div class="mb-2">
                                    <label class="form-label fw-bold text-dark">Guru Pengganti (Opsional):</label>
                                    <input type="text" wire:model.live="guruPenggantiNama" class="form-control" placeholder="Ketik nama guru pengganti...">
                                </div>
                            </div>
                        @endif

                        {{-- Additional Form for Terlambat --}}
                        @if($status === 'terlambat')
                            <div class="card p-3 bg-label-warning border-warning mb-3">
                                <label class="form-label fw-bold text-dark">Lama Keterlambatan (Menit)*:</label>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <button type="button" class="btn btn-secondary px-3" wire:click="$set('lamaTerlambat', max(1, $lamaTerlambat - 5))">- 5m</button>
                                    <input type="number" wire:model.live="lamaTerlambat" class="form-control text-center fw-bold fs-5" min="1" max="120" style="max-width: 120px;">
                                    <button type="button" class="btn btn-secondary px-3" wire:click="$set('lamaTerlambat', min(120, $lamaTerlambat + 5))">+ 5m</button>
                                </div>
                                @error('lamaTerlambat') <span class="text-danger small fw-bold">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        {{-- Catatan Tambahan --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Tambahan (Opsional):</label>
                            <textarea wire:model.live="keteranganLain" class="form-control" rows="2" placeholder="Catatan atau keterangan penting..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-label-secondary" wire:click="closeModal">Batal</button>
                        <button type="button" class="btn btn-primary px-4 fw-bold" wire:click="saveMonitoring" style="min-height: 44px;">
                            <i class="ti tabler-device-floppy me-1"></i> Simpan Monitoring
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
