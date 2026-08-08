@extends('layouts/layoutMaster')

@section('title', 'Sidang Disiplin Komdis')

@section('content')
<div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
        <div class="das-hero__identity">
            <div class="das-hero__logo-wrapper">
                <div class="das-hero__logo-placeholder">
                    <i class="ti tabler-gavel text-danger"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>

            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot bg-danger"></span>
                    <a href="javascript:void(0)" class="text-white text-decoration-none">BK & Komdis</a> / Sidang Disiplin
                </div>
                <h4 class="das-hero__title text-gradient-gold">Sidang Komisi Disiplin (Komdis)</h4>
                <p class="das-hero__subtitle">Manajemen agenda sidang kedisiplinan siswa, status peninjauan, serta pembuatan berita acara sidang.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-danger d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSidang">
                <i class="ti tabler-plus fs-5"></i>
                <span>Jadwalkan Sidang</span>
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
                    <span class="text-muted d-block mb-1">Agenda Sidang</span>
                    <h4 class="mb-0 fw-bold text-danger">2 Sidang</h4>
                </div>
                <div class="avatar avatar-md bg-label-danger rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-calendar-time fs-3 text-danger"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Menunggu Keputusan</span>
                    <h4 class="mb-0 fw-bold text-warning">{{ number_format($sidangs->whereIn('status', ['terjadwal', 'berjalan', 'ditunda'])->count()) }} Sidang</h4>
                </div>
                <div class="avatar avatar-md bg-label-warning rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-hourglass-high fs-3 text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Selesai / Putusan</span>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($sidangs->where('status', 'selesai')->count()) }} Sidang</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-checkbox fs-3 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Total Sidang</span>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($sidangs->total()) }} Sidang</h4>
                </div>
                <div class="avatar avatar-md bg-label-primary rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-gavel fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table Sidang Disiplin --}}
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-list me-2 text-danger"></i>Daftar Sidang Disiplin</h5>
        <form method="GET" action="{{ route('admin.komdis-sidang.index') }}" class="d-flex align-items-center gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari sidang..." value="{{ request('search') }}" style="width: 200px;">
        </form>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Waktu Sidang</th>
                    <th>Siswa Terduga</th>
                    <th>Kasus Rujukan</th>
                    <th>Pihak Terlibat</th>
                    <th>Status Sidang</th>
                    <th>Keputusan / Sanksi</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sidangs as $item)
                    <tr>
                        <td>
                            <span class="fw-semibold text-white d-block">{{ $item->tanggal_sidang ? $item->tanggal_sidang->format('d M Y') : '—' }}</span>
                            <small class="text-white-50">{{ $item->waktu_sidang ?? '-' }} {{ $item->lokasi_sidang ? '(' . $item->lokasi_sidang . ')' : '' }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2 d-flex align-items-center justify-content-center">
                                    <span class="avatar-initial rounded-circle bg-label-danger">
                                        {{ strtoupper(substr($item->siswa?->nama_lengkap ?? 'S', 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-truncate text-white">{{ $item->siswa?->nama_lengkap ?? '—' }}</h6>
                                    <small class="text-white-50">{{ $item->siswa?->kelas?->nama ?? '—' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium text-white d-block">{{ $item->agenda }}</span>
                            <small class="text-white-50">{{ \Illuminate\Support\Str::limit($item->deskripsi_pelanggaran, 40) }}</small>
                        </td>
                        <td><span class="text-white-50">Siswa, Ortu, Komdis</span></td>
                        <td>
                            @php
                                $statusBadge = match($item->status) {
                                    'terjadwal' => 'danger',
                                    'berjalan' => 'warning',
                                    'ditunda' => 'info',
                                    'selesai' => 'success',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-label-{{ $statusBadge }}">{{ ucfirst($item->status) }}</span>
                        </td>
                        <td>
                            <span class="text-white-50">{{ \Illuminate\Support\Str::limit($item->keputusan_sidang ?? '- Belum Diputuskan -', 35) }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.komdis-sidang.show', $item->id) }}" class="btn btn-sm btn-icon btn-label-info" title="Detail / Putusan Sidang">
                                <i class="ti tabler-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ti tabler-gavel fs-1 text-white-50 mb-2"></i>
                                <h6 class="text-white mb-1">Belum Ada Agenda Sidang</h6>
                                <p class="text-white-50 small mb-0">Belum terdapat jadwal sidang komisi disiplin yang tercatat di sistem.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($sidangs->hasPages())
        <div class="card-footer py-3 border-top border-secondary">
            {{ $sidangs->links() }}
        </div>
    @endif
</div>

{{-- MODAL TAMBAH AGENDA SIDANG --}}
<div class="modal fade" id="modalTambahSidang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="ti tabler-calendar-plus text-danger me-2"></i>Jadwalkan Sidang Disiplin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                @csrf
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label fw-medium required">Pilih Kasus Rujukan BK</label>
                        <select name="kasus_id" class="form-select" required>
                            <option value="">-- Pilih Kasus Eskalasi --</option>
                            <option value="1">#BK-2026-089 - Muhammad Albar (Perkelahian)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Tanggal Sidang</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Waktu / Jam</label>
                        <input type="time" name="jam" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium required">Tempat / Ruangan</label>
                        <input type="text" name="tempat" class="form-control" placeholder="Contoh: Ruang Sidang Komdis / Ruang BK" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Undangan Pihak Terlibat</label>
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pihak[]" value="Orang Tua" id="chkOrtu" checked>
                                <label class="form-check-label" for="chkOrtu">Orang Tua / Wali</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pihak[]" value="Wali Kelas" id="chkWali" checked>
                                <label class="form-check-label" for="chkWali">Wali Kelas</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pihak[]" value="Kepala Sekolah" id="chkKepsek">
                                <label class="form-check-label" for="chkKepsek">Kepala Sekolah</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="ti tabler-calendar-time me-1"></i>Jadwalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL BERITA ACARA --}}
<div class="modal fade" id="modalBeritaAcara" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-label-dark border-bottom">
                <h5 class="modal-title fw-bold text-dark"><i class="ti tabler-file-text me-2 text-danger"></i>Berita Acara &amp; Hasil Putusan Sidang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                @csrf
                <div class="modal-body row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium">Siswa Terduga</label>
                        <input type="text" class="form-control" value="Muhammad Albar" disabled>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium">Nomor Agenda</label>
                        <input type="text" class="form-control" value="#KOMDIS-2026-004" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium required">Berita Acara Sidang (Kronologi &amp; Keterangan Sidang)</label>
                        <textarea name="berita_acara" class="form-control" rows="4" placeholder="Tuliskan poin penting yang disampaikan saat sidang berlangsung, pembelaan siswa, dan keterangan saksi/orang tua..." required></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Putusan / Sanksi</label>
                        <select name="sanksi_id" class="form-select" required>
                            <option value="SP1">Surat Peringatan 1 (SP 1)</option>
                            <option value="SP2">Surat Peringatan 2 (SP 2)</option>
                            <option value="SP3">Surat Peringatan 3 (SP 3 / Dikeluarkan)</option>
                            <option value="Sanksi Sosial">Sanksi Sosial / Skorsing 3 Hari</option>
                            <option value="Bebas">Bebas dari Sanksi (Binaan BK)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium required">Status Keputusan</label>
                        <select name="status" class="form-select" required>
                            <option value="Selesai">Selesai (Inkracht)</option>
                            <option value="Menunggu Keputusan">Menunggu Peninjauan Kepala Sekolah</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger"><i class="ti tabler-device-floppy me-1"></i>Simpan Hasil Sidang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
