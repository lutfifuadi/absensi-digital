@extends('layouts/layoutMaster')

@section('title', 'Profil Anak - ' . $anak->nama_lengkap)

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 text-white overflow-hidden shadow-sm"
            style="background: linear-gradient(135deg, #7367f0 0%, #4338ca 100%); border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                            style="width:52px;height:52px;border-radius:10px !important;background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);">
                            <i class="ti tabler-user text-white fs-3"></i>
                        </div>
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.8;">
                                    <li class="breadcrumb-item"><a href="{{ route('ortu.dashboard') }}"
                                            class="text-white text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Profil Anak</li>
                                </ol>
                            </nav>
                            <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Profil Anak</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row gy-4">
    <!-- User Sidebar -->
    <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
        <!-- User Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="user-avatar-section">
                    <div class="d-flex align-items-center flex-column">
                        @if($anak->foto)
                            <img class="img-fluid rounded mb-3 pt-1" src="{{ asset('storage/' . $anak->foto) }}" height="120" width="120" alt="User avatar" style="object-fit: cover; border: 3px solid rgba(115, 103, 240, 0.2);" />
                        @else
                            <div class="avatar avatar-xl mb-3" style="width: 100px; height: 100px;">
                                <span class="avatar-initial rounded-circle bg-label-primary fs-2">{{ substr($anak->nama_lengkap, 0, 2) }}</span>
                            </div>
                        @endif
                        <div class="user-info text-center">
                            <h5 class="mb-1 fw-bold">{{ $anak->nama_lengkap }}</h5>
                            <span class="badge bg-label-secondary px-3 py-1.5 rounded-pill text-capitalize" style="font-size: 0.75rem;">Siswa ({{ $anak->status }})</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-around flex-wrap mt-4 py-2 border-top border-bottom">
                    <div class="d-flex align-items-center py-2 gap-2">
                        <span class="badge bg-label-primary p-2 rounded-circle"><i class="ti tabler-number fs-6"></i></span>
                        <div>
                            <p class="mb-0 fw-bold small text-muted">NISN</p>
                            <small class="fw-bold">{{ $anak->nisn }}</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center py-2 gap-2">
                        <span class="badge bg-label-success p-2 rounded-circle"><i class="ti tabler-door fs-6"></i></span>
                        <div>
                            <p class="mb-0 fw-bold small text-muted">Kelas</p>
                            <small class="fw-bold">{{ $anak->kelas->nama ?? '-' }}</small>
                        </div>
                    </div>
                </div>
                <p class="mt-4 small text-uppercase text-muted fw-bold" style="letter-spacing: 0.5px;">Informasi Detail</p>
                <div class="info-container">
                    <ul class="list-unstyled">
                        <li class="mb-2.5 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-muted small">NIS:</span>
                            <span class="fw-semibold">{{ $anak->nis }}</span>
                        </li>
                        <li class="mb-2.5 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-muted small">Jenis Kelamin:</span>
                            <span class="badge bg-label-info text-capitalize">{{ $anak->jenis_kelamin }}</span>
                        </li>
                        <li class="mb-2.5 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-muted small">Tempat, Tgl Lahir:</span>
                            <span class="fw-semibold text-end">
                                {{ $anak->tempat_lahir ?? '-' }}@if(!empty($anak->tanggal_lahir)), {{ \Carbon\Carbon::parse($anak->tanggal_lahir)->translatedFormat('d M Y') }}@endif
                            </span>
                        </li>
                        <li class="mb-2.5">
                            <span class="fw-medium text-muted small d-block mb-1">Alamat:</span>
                            <span class="fw-semibold text-muted d-block small" style="line-height: 1.4;">{{ $anak->alamat }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /User Card -->
    </div>
    <!--/ User Sidebar -->

    <!-- User Content -->
    <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
        <!-- User Tabs -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-school me-2 text-primary"></i>Informasi Akademik</h5>
            </div>
            <div class="card-body pt-4">
                <div class="row g-4">
                    <div class="col-md-6 col-12">
                        <label class="form-label text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Tahun Akademik</label>
                        <p class="fw-bold mb-0 text-white">{{ $anak->tahunAkademik->tahun ?? '-' }} ({{ $anak->tahunAkademik->semester ?? '-' }})</p>
                    </div>
                    <div class="col-md-6 col-12">
                        <label class="form-label text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Wali Kelas</label>
                        <p class="fw-bold text-primary mb-0"><i class="ti tabler-user-check me-1 fs-5"></i>{{ $anak->kelas->guru->nama ?? 'Belum Ditentukan' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-rocket me-2 text-warning"></i>Quick Action</h5>
            </div>
            <div class="card-body pt-4">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('ortu.anak.absensi', $anak->id) }}" class="btn btn-primary w-100 py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="ti tabler-calendar-stats fs-5"></i> Lihat Absensi
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('ortu.izin-sakit.create', ['siswa_id' => $anak->id]) }}" class="btn btn-warning text-white w-100 py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="ti tabler-file-plus fs-5"></i> Ajukan Izin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ User Content -->
</div>
@endsection
@endsection
