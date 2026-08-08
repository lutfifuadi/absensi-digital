@extends('layouts/layoutMaster')

@section('title', 'Dashboard Bimbingan Konseling (BK)')

@section('page-style')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
<link rel="stylesheet" href="{{ asset('css/dashboards/guru-bk.css') }}?v=1.1">
@endsection

@section('content')
{{-- HERO HEADER --}}
<div class="das-hero mb-4">
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__scanline" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner">
        <div class="das-hero__identity">
            <div class="das-hero__logo-placeholder" aria-hidden="true" style="background: rgba(115, 103, 240, 0.14); border-color: rgba(115, 103, 240, 0.3); color: #7367f0;">
                <i class="ti tabler-user-heart"></i>
            </div>
            <div class="das-hero__meta">
                <div class="das-hero__badge" style="color: #7367f0; background: rgba(115, 103, 240, 0.14); border-color: rgba(115, 103, 240, 0.25);">
                    <span class="das-hero__pulse-dot" style="background:#7367f0;" aria-hidden="true"></span>
                    Portal Guru Bimbingan Konseling (BK)
                </div>
                <h1 class="das-hero__school">Dashboard Guru BK</h1>
                <p class="das-hero__welcome">
                    Selamat bekerja kembali. Pantau perkembangan kesejahteraan psikososial &amp; kedisiplinan siswa hari ini.
                </p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="{{ url('admin/bk-kasus') }}" class="das-btn das-btn--danger d-inline-flex align-items-center gap-1 text-decoration-none">
                        <i class="ti tabler-plus"></i> Catat Kasus
                    </a>
                    <a href="{{ url('admin/bk-log-konseling') }}" class="das-btn das-btn--info d-inline-flex align-items-center gap-1 text-decoration-none">
                        <i class="ti tabler-notebook"></i> Jurnal Konseling
                    </a>
                    <a href="{{ url('admin/bk-pemutihan') }}" class="das-btn das-btn--success d-inline-flex align-items-center gap-1 text-decoration-none">
                        <i class="ti tabler-sparkles"></i> Pemutihan Poin
                    </a>
                </div>
            </div>
        </div>

        <div class="das-hero__clock" role="status">
            <div class="das-hero__date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
            <div class="das-hero__time">
                <span id="bk-live-clock">{{ now()->format('H:i:s') }}</span>
                <span class="das-hero__live-badge" style="color:#7367f0; background:rgba(115,103,240,0.14); border-color:rgba(115,103,240,0.3);"><span class="das-hero__pulse-dot" style="background:#7367f0;" aria-hidden="true"></span>LIVE</span>
            </div>
            <div class="das-hero__tz">WIB</div>
        </div>
    </div>
</div>

{{-- KPI STATS --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm bg-body h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="ti tabler-alert-triangle fs-4"></i>
                        </span>
                    </div>
                    <h3 class="mb-0 fw-bold text-body">12</h3>
                </div>
                <p class="mb-1 text-muted text-nowrap fw-semibold">Rujukan Kasus Masuk</p>
                <p class="mb-0 text-warning small"><i class="ti tabler-clock me-1"></i>Butuh Tindak Lanjut</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm bg-body h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="ti tabler-notebook fs-4"></i>
                        </span>
                    </div>
                    <h3 class="mb-0 fw-bold text-body">8</h3>
                </div>
                <p class="mb-1 text-muted text-nowrap fw-semibold">Konseling Selesai Pekan Ini</p>
                <p class="mb-0 text-info small"><i class="ti tabler-check me-1"></i>Tercatat di Jurnal</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm bg-body h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="ti tabler-arrow-narrow-down fs-4"></i>
                        </span>
                    </div>
                    <h3 class="mb-0 fw-bold text-body">180</h3>
                </div>
                <p class="mb-1 text-muted text-nowrap fw-semibold">Poin Diputihkan</p>
                <p class="mb-0 text-success small"><i class="ti tabler-sparkles me-1"></i>Pemulihan Perilaku</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm bg-body h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class="ti tabler-gavel fs-4"></i>
                        </span>
                    </div>
                    <h3 class="mb-0 fw-bold text-body">3</h3>
                </div>
                <p class="mb-1 text-muted text-nowrap fw-semibold">Eskalasi Sidang Komdis</p>
                <p class="mb-0 text-danger small"><i class="ti tabler-shield-alert me-1"></i>Pelanggaran Berat</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Kasus Butuh Tindakan --}}
    <div class="col-12 col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-clipboard-list me-2 text-warning"></i>Kasus Butuh Tindakan Segera</h5>
                <span class="badge bg-label-warning">Prioritas</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-danger font-semibold">MA</span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-body">Muhammad Albar (XII RPL 1)</h6>
                                    <small class="text-muted">Kasus: Perkelahian di Parkir</small>
                                </div>
                            </div>
                            <span class="badge bg-label-danger">Eskalasi Komdis</span>
                        </div>
                        <p class="mb-2 text-muted small">Telah dirujuk oleh Wali Kelas untuk penyusunan berkas sidang kedisiplinan bersama Orang Tua.</p>
                        <div class="d-flex gap-2">
                            <a href="{{ url('admin/bk-kasus') }}" class="btn btn-xs btn-label-danger">Proses Eskalasi</a>
                            <a href="{{ url('admin/bk-log-konseling') }}" class="btn btn-xs btn-outline-secondary">Lihat Histori Konseling</a>
                        </div>
                    </div>
                    <div class="list-group-item p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-warning font-semibold">SN</span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-body">Siti Nurhaliza (XI TKJ 2)</h6>
                                    <small class="text-muted">Kasus: Keterlambatan Berulang</small>
                                </div>
                            </div>
                            <span class="badge bg-label-warning">Binaan BK</span>
                        </div>
                        <p class="mb-2 text-muted small">Akumulasi point pelanggaran mencapai 30 poin. Butuh konseling preventif dan bimbingan kedisiplinan kelas.</p>
                        <div class="d-flex gap-2">
                            <a href="{{ url('admin/bk-log-konseling') }}" class="btn btn-xs btn-label-warning">Panggil Siswa / Catat Sesi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tren Kasus & Info BK --}}
    <div class="col-12 col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-chart-bar me-2 text-info"></i>Persentase Kategori Masalah</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-medium text-body">Kedisiplinan &amp; Keterlambatan</span>
                        <span class="small text-muted">55%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 55%" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-medium text-body">Akademik &amp; Kerapian</span>
                        <span class="small text-muted">25%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-medium text-body">Sosial &amp; Pertikaian</span>
                        <span class="small text-muted">15%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-medium text-body">Lain-lain</span>
                        <span class="small text-muted">5%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-secondary" role="progressbar" style="width: 5%" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="alert alert-info mt-4 mb-0" role="alert">
                    <h6 class="alert-heading fw-bold mb-1"><i class="ti tabler-info-circle me-1"></i>Informasi Bimbingan</h6>
                    <small class="d-block">Pastikan setiap catatan konseling yang bersifat rahasia diset dengan opsi <strong>Privat</strong> pada jurnal konseling.</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    setInterval(function() {
        let clock = document.getElementById('bk-live-clock');
        if (clock) {
            let now = new Date();
            let h = String(now.getHours()).padStart(2, '0');
            let m = String(now.getMinutes()).padStart(2, '0');
            let s = String(now.getSeconds()).padStart(2, '0');
            clock.textContent = h + ':' + m + ':' + s;
        }
    }, 1000);
</script>
@endpush
@endsection
