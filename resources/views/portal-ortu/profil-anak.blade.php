@extends('layouts/layoutMaster')

@section('title', 'Profil Anak - ' . $anak->nama_lengkap)

@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ortu.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Profil Anak</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row gy-4">
    <!-- User Sidebar -->
    <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
        <!-- User Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="user-avatar-section">
                    <div class="d-flex align-items-center flex-column">
                        @if($anak->foto)
                            <img class="img-fluid rounded mb-3 pt-1" src="{{ asset('storage/' . $anak->foto) }}" height="100" width="100" alt="User avatar" />
                        @else
                            <div class="avatar avatar-xl mb-3">
                                <span class="avatar-initial rounded bg-label-primary">{{ substr($anak->nama_lengkap, 0, 2) }}</span>
                            </div>
                        @endif
                        <div class="user-info text-center">
                            <h4 class="mb-2">{{ $anak->nama_lengkap }}</h4>
                            <span class="badge bg-label-secondary">Siswa ({{ $anak->status }})</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-around flex-wrap mt-4 py-3">
                    <div class="d-flex align-items-start me-4 mt-3 gap-2">
                        <span class="badge bg-label-primary p-2 rounded"><i class="ti tabler-checkbox ti-sm"></i></span>
                        <div>
                            <p class="mb-0 fw-bold">NISN</p>
                            <small>{{ $anak->nisn }}</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mt-3 gap-2">
                        <span class="badge bg-label-primary p-2 rounded"><i class="ti tabler-briefcase ti-sm"></i></span>
                        <div>
                            <p class="mb-0 fw-bold">Kelas</p>
                            <small>{{ $anak->kelas->nama ?? '-' }}</small>
                        </div>
                    </div>
                </div>
                <p class="mt-4 small text-uppercase text-muted">Informasi Detail</p>
                <div class="info-container">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <span class="fw-bold me-1">NIS:</span>
                            <span>{{ $anak->nis }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="fw-bold me-1">Jenis Kelamin:</span>
                            <span class="text-capitalize">{{ $anak->jenis_kelamin }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="fw-bold me-1">Tempat, Tgl Lahir:</span>
                            <span>
                                {{ $anak->tempat_lahir ?? '-' }}
                                @if(!empty($anak->tanggal_lahir))
                                    , {{ \Carbon\Carbon::parse($anak->tanggal_lahir)->translatedFormat('d M Y') }}
                                @endif
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="fw-bold me-1">Alamat:</span>
                            <span>{{ $anak->alamat }}</span>
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
        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Informasi Akademik</h5>
            </div>
            <div class="card-body pt-3">
                <div class="row gy-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase">Tahun Akademik</label>
                        <p class="fw-bold">{{ $anak->tahunAkademik->tahun ?? '-' }} ({{ $anak->tahunAkademik->semester ?? '-' }})</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase">Wali Kelas</label>
                        <p class="fw-bold text-primary">{{ $anak->kelas->guru->nama ?? 'Belum Ditentukan' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Quick Action</h5>
            </div>
            <div class="card-body pt-3">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('ortu.anak.absensi', $anak->id) }}" class="btn btn-primary w-100">
                            <i class="ti tabler-calendar-stats me-1"></i> Lihat Absensi
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('ortu.izin-sakit.create', ['siswa_id' => $anak->id]) }}" class="btn btn-warning w-100">
                            <i class="ti tabler-file-plus me-1"></i> Ajukan Izin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ User Content -->
</div>
@endsection
