@extends('layouts/layoutMaster')

@section('title', 'Manajemen Kasus BK & Eskalasi Komdis')

@section('content')
<div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
        <div class="das-hero__identity">
            <div class="das-hero__logo-wrapper">
                <div class="das-hero__logo-placeholder">
                    <i class="ti tabler-briefcase text-warning"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>

            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot bg-warning"></span>
                    <a href="javascript:void(0)" class="text-white text-decoration-none">BK & Komdis</a> / Kasus Siswa
                </div>
                <h4 class="das-hero__title text-gradient-gold">Daftar Kasus Bimbingan Konseling</h4>
                <p class="das-hero__subtitle">Pencatatan, penanganan kasus siswa, serta penanganan eskalasi ke Komisi Disiplin (Komdis).</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-warning d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKasus">
                <i class="ti tabler-plus fs-5"></i>
                <span>Tambah Kasus Baru</span>
            </button>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Total Kasus</span>
                    <h4 class="mb-0 fw-bold text-primary">24</h4>
                </div>
                <div class="avatar avatar-md bg-label-primary rounded">
                    <i class="ti tabler-folder fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Proses Penanganan</span>
                    <h4 class="mb-0 fw-bold text-info">8</h4>
                </div>
                <div class="avatar avatar-md bg-label-info rounded">
                    <i class="ti tabler-progress fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Eskalasi Komdis</span>
                    <h4 class="mb-0 fw-bold text-danger">3</h4>
                </div>
                <div class="avatar avatar-md bg-label-danger rounded">
                    <i class="ti tabler-gavel fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Selesai / Closed</span>
                    <h4 class="mb-0 fw-bold text-success">13</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded">
                    <i class="ti tabler-circle-check fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Card --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label fw-medium">Cari Siswa / Kasus</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti tabler-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nama, NIS, atau judul kasus..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label fw-medium">Kategori</label>
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="Kedisiplinan">Kedisiplinan</option>
                    <option value="Akademik">Akademik</option>
                    <option value="Sosial Emosional">Sosial Emosional</option>
                    <option value="Berat / Perundungan">Berat / Perundungan</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label fw-medium">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="Terbuka">Terbuka (Open)</option>
                    <option value="Dalam Proses">Dalam Proses</option>
                    <option value="Eskalasi Komdis">Eskalasi Komdis</option>
                    <option value="Selesai">Selesai</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="ti tabler-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Table Kasus BK --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Reg</th>
                    <th>Siswa</th>
                    <th>Judul Kasus & Kategori</th>
                    <th>Tanggal Pelaporan</th>
                    <th>Tingkat Keparahan</th>
                    <th>Status Kasus</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="fw-bold text-primary">#BK-2026-089</span></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded-circle bg-label-danger">MA</span>
                            </div>
                            <div>
                                <h6 class="mb-0 text-truncate">Muhammad Albar</h6>
                                <small class="text-muted">XII RPL 1 • NIS: 2122045</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="fw-medium text-body d-block">Perkelahian di Area Parkir</span>
                        <small class="badge bg-label-warning">Berat / Perundungan</small>
                    </td>
                    <td>04 Aug 2026</td>
                    <td><span class="badge bg-danger">Tinggi (High)</span></td>
                    <td><span class="badge bg-label-danger"><i class="ti tabler-gavel me-1"></i>Eskalasi Komdis</span></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti tabler-dots-vertical fs-5"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalEditKasus"><i class="ti tabler-pencil me-2 text-warning"></i>Edit Kasus</a>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalEskalasiKomdis"><i class="ti tabler-arrow-up-right me-2"></i>Eskalasi ke Komdis</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-success" href="javascript:void(0)"><i class="ti tabler-circle-check me-2"></i>Tandai Selesai</a>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><span class="fw-bold text-primary">#BK-2026-088</span></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded-circle bg-label-info">SN</span>
                            </div>
                            <div>
                                <h6 class="mb-0 text-truncate">Siti Nurhaliza</h6>
                                <small class="text-muted">XI TKJ 2 • NIS: 2223012</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="fw-medium text-body d-block">Keterlambatan Berulang (5x)</span>
                        <small class="badge bg-label-info">Kedisiplinan</small>
                    </td>
                    <td>06 Aug 2026</td>
                    <td><span class="badge bg-warning">Sedang (Medium)</span></td>
                    <td><span class="badge bg-label-info"><i class="ti tabler-clock me-1"></i>Dalam Proses</span></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti tabler-dots-vertical fs-5"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalEditKasus"><i class="ti tabler-pencil me-2 text-warning"></i>Edit Kasus</a>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalEskalasiKomdis"><i class="ti tabler-arrow-up-right me-2"></i>Eskalasi ke Komdis</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-success" href="javascript:void(0)"><i class="ti tabler-circle-check me-2"></i>Tandai Selesai</a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL TAMBAH KASUS --}}
<div class="modal fade" id="modalTambahKasus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="ti tabler-plus text-warning me-2"></i>Tambah Kasus BK Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                @csrf
                <div class="modal-body row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Pilih Siswa</label>
                        <select name="siswa_id" class="form-select" required>
                            <option value="">-- Pilih Siswa --</option>
                            <option value="1">Muhammad Albar (XII RPL 1)</option>
                            <option value="2">Siti Nurhaliza (XI TKJ 2)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Kategori Kasus</label>
                        <select name="kategori" class="form-select" required>
                            <option value="Kedisiplinan">Kedisiplinan</option>
                            <option value="Akademik">Akademik</option>
                            <option value="Sosial Emosional">Sosial Emosional</option>
                            <option value="Berat / Perundungan">Berat / Perundungan</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium required">Judul Kasus</label>
                        <input type="text" name="judul" class="form-control" placeholder="Ringkasan singkat masalah..." required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Tingkat Keparahan</label>
                        <select name="tingkat" class="form-select" required>
                            <option value="Rendah">Rendah (Low)</option>
                            <option value="Sedang">Sedang (Medium)</option>
                            <option value="Tinggi">Tinggi (High)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Tanggal Kejadian</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Deskripsi & Kronologi Kasus</label>
                        <textarea name="deskripsi" class="form-control" rows="4" placeholder="Detail kronologi atau laporan awal kasus..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="ti tabler-device-floppy me-1"></i>Simpan Kasus</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL ESKALASI KOMDIS --}}
<div class="modal fade" id="modalEskalasiKomdis" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-label-danger border-bottom">
                <h5 class="modal-title fw-bold text-danger"><i class="ti tabler-arrow-up-right me-2"></i>Eskalasi Kasus ke Komdis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                @csrf
                <div class="modal-body row g-3">
                    <div class="alert alert-warning mb-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="ti tabler-alert-triangle fs-3 me-2"></i>
                            <small>Kasus ini akan dilimpahkan ke Komisi Disiplin (Komdis) untuk tindakan/sidang disiplin lebih lanjut.</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Nomor Kasus</label>
                        <input type="text" class="form-control" value="#BK-2026-089 (Muhammad Albar)" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium required">Alasan Eskalasi ke Komdis</label>
                        <textarea name="alasan_eskalasi" class="form-control" rows="3" placeholder="Tentukan pertimbangan atau pelanggaran berat yang membutuhkan komdis..." required></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Rekomendasi Tindakan BK</label>
                        <input type="text" name="rekomendasi" class="form-control" placeholder="Contoh: Pemanggilan Orang Tua & Sidang Komdis">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="ti tabler-gavel me-1"></i>Proses Eskalasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
