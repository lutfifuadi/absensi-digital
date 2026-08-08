@extends('layouts/layoutMaster')

@section('title', 'Jurnal & Log Bimbingan Konseling')

@section('content')
<div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
        <div class="das-hero__identity">
            <div class="das-hero__logo-wrapper">
                <div class="das-hero__logo-placeholder">
                    <i class="ti tabler-notebook text-info"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>

            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot bg-info"></span>
                    <a href="javascript:void(0)" class="text-white text-decoration-none">BK & Komdis</a> / Log Konseling
                </div>
                <h4 class="das-hero__title text-gradient-gold">Jurnal Bimbingan Konseling</h4>
                <p class="das-hero__subtitle">Catatan harian sesi konseling siswa, penanganan pribadi, dan evaluasi berkala.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-info d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCatatKonseling">
                <i class="ti tabler-plus fs-5"></i>
                <span>Catat Sesi Konseling</span>
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
                    <span class="text-muted d-block mb-1">Total Sesi Selesai</span>
                    <h4 class="mb-0 fw-bold text-info">42</h4>
                </div>
                <div class="avatar avatar-md bg-label-info rounded">
                    <i class="ti tabler-messages fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Log Sifat Privat</span>
                    <h4 class="mb-0 fw-bold text-danger">18</h4>
                </div>
                <div class="avatar avatar-md bg-label-danger rounded">
                    <i class="ti tabler-lock fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Log Sifat Publik / Tim</span>
                    <h4 class="mb-0 fw-bold text-success">24</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded">
                    <i class="ti tabler-eye fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Bulan Ini</span>
                    <h4 class="mb-0 fw-bold text-primary">12 Sesi</h4>
                </div>
                <div class="avatar avatar-md bg-label-primary rounded">
                    <i class="ti tabler-calendar-event fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table Log Konseling --}}
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-list me-2 text-info"></i>Daftar Jurnal Bimbingan</h5>
        <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" placeholder="Cari siswa atau topik..." style="width: 220px;">
            <select class="form-select form-select-sm" style="width: 150px;">
                <option value="">Semua Akses</option>
                <option value="privat">Privat (BK Only)</option>
                <option value="publik">Publik / Tim</option>
            </select>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tanggal & Waktu</th>
                    <th>Siswa</th>
                    <th>Guru Konselor</th>
                    <th>Topik / Ringkasan</th>
                    <th>Kategori Sesi</th>
                    <th>Aksesibilitas</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <span class="fw-semibold text-body d-block">08 Aug 2026</span>
                        <small class="text-muted">09:30 WIB</small>
                    </td>
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
                    <td>Bu Dra. Rahmawati</td>
                    <td>
                        <span class="fw-medium text-body d-block">Konseling Motivasi Belajar</span>
                        <small class="text-muted">Pembahasan mengenai penurunan keaktifan kelas</small>
                    </td>
                    <td><span class="badge bg-label-info">Individu</span></td>
                    <td>
                        <span class="badge bg-label-danger d-inline-flex align-items-center gap-1">
                            <i class="ti tabler-lock fs-6"></i> Privat (Hanya BK)
                        </span>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-icon btn-label-info" data-bs-toggle="modal" data-bs-target="#modalDetailKonseling" title="Detail Jurnal">
                            <i class="ti tabler-eye"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="fw-semibold text-body d-block">05 Aug 2026</span>
                        <small class="text-muted">13:15 WIB</small>
                    </td>
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
                    <td>Pak Ahmad S.Pd</td>
                    <td>
                        <span class="fw-medium text-body d-block">Mediasi & Resolusi Konflik</span>
                        <small class="text-muted">Sesi mediasi pasca kejadian perkelahian</small>
                    </td>
                    <td><span class="badge bg-label-warning">Kelompok / Mediasi</span></td>
                    <td>
                        <span class="badge bg-label-success d-inline-flex align-items-center gap-1">
                            <i class="ti tabler-world fs-6"></i> Publik / Tim Sekolah
                        </span>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-icon btn-label-info" data-bs-toggle="modal" data-bs-target="#modalDetailKonseling" title="Detail Jurnal">
                            <i class="ti tabler-eye"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL CATAT KONSELING --}}
<div class="modal fade" id="modalCatatKonseling" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="ti tabler-notebook text-info me-2"></i>Catat Sesi Bimbingan Konseling</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                @csrf
                <div class="modal-body row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Siswa Konseli</label>
                        <select name="siswa_id" class="form-select" required>
                            <option value="">-- Pilih Siswa --</option>
                            <option value="1">Siti Nurhaliza (XI TKJ 2)</option>
                            <option value="2">Muhammad Albar (XII RPL 1)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Jenis Sesi</label>
                        <select name="jenis_sesi" class="form-select" required>
                            <option value="Individu">Konseling Individu</option>
                            <option value="Kelompok">Konseling Kelompok / Mediasi</option>
                            <option value="Karir">Bimbingan Karir / Studi</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Tanggal & Waktu Sesi</label>
                        <input type="datetime-local" name="waktu_sesi" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Sifat Catatan (Privasi)</label>
                        <select name="sifat_privasi" class="form-select" required>
                            <option value="privat">🔒 Privat (Hanya Guru BK)</option>
                            <option value="publik">🌐 Publik / Tim (Wali Kelas & Kesiswaan)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium required">Topik Bimbingan</label>
                        <input type="text" name="topik" class="form-control" placeholder="Garis besar pembahasan..." required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium required">Hasil & Catatan Konseling</label>
                        <textarea name="catatan" class="form-control" rows="4" placeholder="Detail evaluasi, temuan psikologis, dan kesepakatan tindak lanjut..." required></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Rencana Tindak Lanjut (RTL)</label>
                        <input type="text" name="rtl" class="form-control" placeholder="Tindakan selanjutnya (misal: Sesi 2 minggu depan, panggilan orang tua)">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info"><i class="ti tabler-device-floppy me-1"></i>Simpan Jurnal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
