@extends('layouts/layoutMaster')

@section('title', 'Sanksi Aktif & Surat Peringatan (SP)')

@section('content')
<div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
        <div class="das-hero__identity">
            <div class="das-hero__logo-wrapper">
                <div class="das-hero__logo-placeholder">
                    <i class="ti tabler-file-certificate text-danger"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>

            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot bg-danger"></span>
                    <a href="javascript:void(0)" class="text-white text-decoration-none">BK & Komdis</a> / Sanksi & SP
                </div>
                <h4 class="das-hero__title text-gradient-gold">Daftar Sanksi Aktif &amp; Surat Peringatan (SP)</h4>
                <p class="das-hero__subtitle">Pemantauan masa aktif sanksi, tingkatan SP (1, 2, 3), dan riwayat pembinaan kedisiplinan.</p>
            </div>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Sanksi Aktif</span>
                    <h4 class="mb-0 fw-bold text-danger">3 Siswa</h4>
                </div>
                <div class="avatar avatar-md bg-label-danger rounded">
                    <i class="ti tabler-circle-x fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Aktif SP 1</span>
                    <h4 class="mb-0 fw-bold text-warning">5 Siswa</h4>
                </div>
                <div class="avatar avatar-md bg-label-warning rounded">
                    <i class="ti tabler-file-description fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Aktif SP 2 &amp; 3</span>
                    <h4 class="mb-0 fw-bold text-danger">1 Siswa</h4>
                </div>
                <div class="avatar avatar-md bg-label-danger rounded">
                    <i class="ti tabler-shield-alert fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Sanksi Selesai</span>
                    <h4 class="mb-0 fw-bold text-success">17 Sanksi</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded">
                    <i class="ti tabler-circle-check fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sanksi & SP Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-list me-2 text-danger"></i>Status Sanksi &amp; Surat Peringatan</h5>
        <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" placeholder="Cari siswa..." style="width: 200px;">
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Siswa</th>
                    <th>Tingkat SP</th>
                    <th>Deskripsi Sanksi</th>
                    <th>Masa Berlaku / Skorsing</th>
                    <th>Status Sanksi</th>
                    <th>Dokumen SP</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded-circle bg-label-danger">MA</span>
                            </div>
                            <div>
                                <h6 class="mb-0 text-truncate">Muhammad Albar</h6>
                                <small class="text-muted">XII RPL 1</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-danger">SP 1</span></td>
                    <td>Skorsing Akademik &amp; Wajib Lapor Harian</td>
                    <td>
                        <span class="fw-semibold text-body d-block">10 Aug 2026 - 13 Aug 2026</span>
                        <small class="text-muted">3 Hari Skorsing</small>
                    </td>
                    <td><span class="badge bg-label-danger"><span class="pulse-dot bg-danger"></span>Aktif</span></td>
                    <td>
                        <a href="javascript:void(0)" class="btn btn-xs btn-label-danger d-inline-flex align-items-center gap-1">
                            <i class="ti tabler-download fs-6"></i> Unduh SP (PDF)
                        </a>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-label-success" onclick="confirmSanksiSelesai()">Selesaikan</button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded-circle bg-label-primary">SN</span>
                            </div>
                            <div>
                                <h6 class="mb-0 text-truncate">Siti Nurhaliza</h6>
                                <small class="text-muted">XI TKJ 2</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-secondary">Bukan SP</span></td>
                    <td>Sanksi Sosial Pembersihan Mushola</td>
                    <td>
                        <span class="fw-semibold text-body d-block">03 Aug 2026 - 05 Aug 2026</span>
                        <small class="text-muted">Telah Terlewati</small>
                    </td>
                    <td><span class="badge bg-label-success">Selesai</span></td>
                    <td><span class="text-muted">-</span></td>
                    <td class="text-end">
                        <span class="text-muted small">No Action</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmSanksiSelesai() {
        if (confirm("Apakah Anda yakin ingin menandai sanksi ini telah selesai dan dipatuhi oleh siswa?")) {
            alert("Status sanksi berhasil diubah menjadi selesai.");
        }
    }
</script>
@endsection
