@extends('layouts/layoutMaster')

@section('title', 'Pemutihan Poin Siswa')

@section('content')
<div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
        <div class="das-hero__identity">
            <div class="das-hero__logo-wrapper">
                <div class="das-hero__logo-placeholder">
                    <i class="ti tabler-sparkles text-success"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>

            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot bg-success"></span>
                    <a href="javascript:void(0)" class="text-white text-decoration-none">BK & Komdis</a> / Pemutihan Poin
                </div>
                <h4 class="das-hero__title text-gradient-gold">Pemutihan Poin Pelanggaran</h4>
                <p class="das-hero__subtitle">Prosedur pengurangan poin pelanggaran siswa melalui program kebaikan, prestasi, atau sanksi sosial edukatif.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-success d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEksekusiPemutihan">
                <i class="ti tabler-minus fs-5"></i>
                <span>Eksekusi Pemutihan</span>
            </button>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Total Poin Diputihkan</span>
                    <h4 class="mb-0 fw-bold text-success">1,450 Poin</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded">
                    <i class="ti tabler-arrow-down-left fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Siswa Terbantu</span>
                    <h4 class="mb-0 fw-bold text-primary">38 Siswa</h4>
                </div>
                <div class="avatar avatar-md bg-label-primary rounded">
                    <i class="ti tabler-users fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-xl-4">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted d-block mb-1">Metode Terpopuler</span>
                    <h4 class="mb-0 fw-bold text-info">Kegiatan Keagamaan & Sosial</h4>
                </div>
                <div class="avatar avatar-md bg-label-info rounded">
                    <i class="ti tabler-heart fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table Pemutihan Poin --}}
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-list me-2 text-success"></i>Daftar Pemutihan Poin</h5>
        <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" placeholder="Cari siswa..." style="width: 200px;">
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Poin Dikurangi</th>
                    <th>Metode Pemutihan</th>
                    <th>Keterangan / Aktivitas</th>
                    <th>Petugas / Guru</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>07 Aug 2026</td>
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
                    <td><span class="badge bg-label-success fw-bold">-25 Poin</span></td>
                    <td><span class="badge bg-label-info">Kerja Bakti Perpustakaan</span></td>
                    <td>Membersihkan dan merapikan buku perpustakaan sekolah selama 3 hari berturut-turut.</td>
                    <td>Pak Ahmad S.Pd</td>
                    <td class="text-end">
                        <span class="text-success small fw-semibold"><i class="ti tabler-circle-check me-1"></i>Approved</span>
                    </td>
                </tr>
                <tr>
                    <td>03 Aug 2026</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded-circle bg-label-warning">SN</span>
                            </div>
                            <div>
                                <h6 class="mb-0 text-truncate">Siti Nurhaliza</h6>
                                <small class="text-muted">XI TKJ 2</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-label-success fw-bold">-10 Poin</span></td>
                    <td><span class="badge bg-label-primary">Piket Shalat Berjamaah</span></td>
                    <td>Membantu merapikan barisan & mukena di Mushola sekolah.</td>
                    <td>Bu Dra. Rahmawati</td>
                    <td class="text-end">
                        <span class="text-success small fw-semibold"><i class="ti tabler-circle-check me-1"></i>Approved</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL EKSEKUSI PEMUTIHAN --}}
<div class="modal fade" id="modalEksekusiPemutihan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="ti tabler-sparkles text-success me-2"></i>Eksekusi Pemutihan Poin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                @csrf
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label fw-medium required">Pilih Siswa</label>
                        <select name="siswa_id" class="form-select" required>
                            <option value="">-- Pilih Siswa (Akumulasi Poin Aktif) --</option>
                            <option value="1">Muhammad Albar (Poin Aktif: 75)</option>
                            <option value="2">Siti Nurhaliza (Poin Aktif: 30)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Jumlah Pengurangan Poin</label>
                        <input type="number" name="jumlah_poin" class="form-control" placeholder="Contoh: 15" min="1" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Metode Pemutihan</label>
                        <select name="metode" class="form-select" required>
                            <option value="Kerja Bakti">Kerja Bakti Sekolah</option>
                            <option value="Prestasi Akademik">Prestasi / Lomba</option>
                            <option value="Kegiatan Ibadah">Piket &amp; Kegiatan Mushola</option>
                            <option value="Lainnya">Lainnya / Diskresi BK</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium required">Detail Aktivitas & Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Sebutkan jenis aktivitas kebaikan yang dilakukan siswa..." required></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium required">Tanggal Pelaksanaan</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="ti tabler-check me-1"></i>Eksekusi Pengurangan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
