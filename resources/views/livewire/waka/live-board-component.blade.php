<div class="live-board-wrapper bg-dark text-white min-vh-100 p-3" wire:poll.30s>
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-secondary">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="ti tabler-device-tv fs-2"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0 text-white tracking-wide">LIVE BOARD MONITORING KEHADIRAN GURU</h3>
                <div class="text-white-50 fs-6">
                    <i class="ti tabler-calendar me-1"></i>{{ $liveData['hari'] }}, {{ \Carbon\Carbon::parse($liveData['tanggal'])->translatedFormat('d F Y') }}
                </div>
            </div>
        </div>

        <div class="text-end">
            <div class="badge bg-primary text-white fs-5 px-3 py-2 fw-bold shadow-sm" id="live-clock">
                {{ \Carbon\Carbon::now()->format('H:i:s') }}
            </div>
            <div class="text-white-50 mt-1 small">
                <i class="ti tabler-refresh me-1"></i>Auto-refresh 30s &bull; Terakhir: {{ \Carbon\Carbon::parse($liveData['generated_at'])->format('H:i:s') }}
            </div>
        </div>
    </div>

    {{-- Filter Time Slots --}}
    <div class="d-flex gap-2 overflow-auto mb-4 pb-2">
        <button wire:click="setJamFilter('all')" class="btn btn-sm {{ $jamFilter === 'all' ? 'btn-primary' : 'btn-outline-light' }} px-3 py-2 fw-bold fs-6">
            Semua Jam
        </button>
        @foreach($liveData['jam_tersedia'] as $jam)
            <button wire:click="setJamFilter('{{ $jam }}')" class="btn btn-sm {{ $jamFilter === $jam ? 'btn-primary' : 'btn-outline-light' }} px-3 py-2 fw-bold fs-6">
                Jam {{ $jam }}
            </button>
        @endforeach
    </div>

    {{-- Content Grid --}}
    @forelse($liveData['kelas_per_jam'] as $group)
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <span class="badge bg-gradient bg-primary text-white fs-6 px-3 py-2 me-2">
                    <i class="ti tabler-clock me-1"></i> JAM {{ $group['jam_mulai'] }} - {{ $group['jam_selesai'] }}
                </span>
                <hr class="flex-grow-1 border-secondary my-0 opacity-25">
            </div>

            <div class="row row-cols-1 row-cols-md-3 row-cols-xl-6 g-3">
                @foreach($group['kelas'] as $c)
                    @php
                        $st = $c['status'];
                        $bgColor = match($st) {
                            'hadir' => '#16a34a',
                            'tidak_hadir' => '#dc2626',
                            'terlambat' => '#d97706',
                            default => '#4b5563',
                        };
                        $textColor = $st === 'terlambat' ? '#ffffff' : '#ffffff';
                    @endphp

                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm" style="background-color: {{ $bgColor }}; color: {{ $textColor }}; min-height: 160px;">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h4 class="fw-extrabold mb-0 text-white tracking-wide" style="font-size: 1.25rem;">{{ $c['nama_kelas'] }}</h4>
                                        <span class="fs-6">
                                            @if($st === 'hadir') ✅
                                            @elseif($st === 'tidak_hadir') ❌
                                            @elseif($st === 'terlambat') ⏱
                                            @else ⬜
                                            @endif
                                        </span>
                                    </div>
                                    <div class="fw-semibold text-white-50 small mb-1">{{ $c['mata_pelajaran'] }}</div>
                                    <div class="fw-bold text-white fs-6 text-truncate mb-2" title="{{ $c['nama_guru'] }}">{{ $c['nama_guru'] }}</div>
                                </div>

                                <div>
                                    <div class="fw-bold text-uppercase tracking-wider px-2 py-1 rounded text-center" style="background: rgba(0,0,0,0.2); font-size: 0.85rem;">
                                        @if($st === 'hadir')
                                            HADIR
                                        @elseif($st === 'tidak_hadir')
                                            TIDAK HADIR
                                            @if($c['keterangan'])
                                                <span class="d-block text-warning small fw-normal">({{ strtoupper($c['keterangan']) }})</span>
                                            @endif
                                            @if($c['guru_pengganti_nama'])
                                                <span class="d-block text-white small fw-normal"><i class="ti tabler-user-check me-1"></i>{{ $c['guru_pengganti_nama'] }}</span>
                                            @endif
                                        @elseif($st === 'terlambat')
                                            TERLAMBAT
                                            @if($c['lama_terlambat'])
                                                <span class="d-block text-white small fw-normal">({{ $c['lama_terlambat'] }} Menit)</span>
                                            @endif
                                        @else
                                            BELUM DIMONITOR
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-white-50">
            <i class="ti tabler-calendar-off fs-1 mb-2"></i>
            <h4 class="fw-bold text-white">Tidak ada data kelas untuk ditampilkan</h4>
            <p>Tidak ada jadwal pelajaran aktif pada jam/tanggal ini.</p>
        </div>
    @endforelse

    {{-- Bottom Summary Banner --}}
    <div class="fixed-bottom bg-black text-white p-3 border-top border-secondary opacity-95">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="d-flex gap-4 align-items-center">
                <span class="fw-bold text-uppercase text-white-50">REKAP HARI INI:</span>
                <span class="fs-5 text-success fw-bold">✅ HADIR: {{ $liveData['summary']['hadir'] }}</span>
                <span class="fs-5 text-danger fw-bold">❌ TDK HADIR: {{ $liveData['summary']['tidak_hadir'] }}</span>
                <span class="fs-5 text-warning fw-bold">⏱ TERLAMBAT: {{ $liveData['summary']['terlambat'] }}</span>
                <span class="fs-5 text-secondary fw-bold">⬜ BELUM DIMONITOR: {{ $liveData['summary']['belum_dimonitor'] }}</span>
            </div>
            <div class="text-white-50 small">
                SMA NEGERI E-ABSENSI &bull; LIVE MONITORING BOARD
            </div>
        </div>
    </div>
</div>
