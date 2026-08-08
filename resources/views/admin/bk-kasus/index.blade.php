@extends('layouts/layoutMaster')

@section('title', 'Manajemen Kasus BK & Eskalasi Komdis')

@section('page-style')
<link rel="stylesheet" href="{{ asset('css/dashboards/guru-bk.css') }}?v=1.2">
@endsection

@section('content')
<div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
        <div class="das-hero__identity">
            <div class="das-hero__logo-wrapper">
                <div class="das-hero__logo-placeholder d-flex align-items-center justify-content-center">
                    <i class="ti tabler-briefcase text-warning" style="font-size: 2rem;"></i>
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
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Total Kasus</span>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($stats['total'] ?? 0) }}</h4>
                </div>
                <div class="avatar avatar-md bg-label-primary rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-folder fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Proses Penanganan</span>
                    <h4 class="mb-0 fw-bold text-info">{{ number_format($stats['proses'] ?? 0) }}</h4>
                </div>
                <div class="avatar avatar-md bg-label-info rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-progress fs-3 text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Eskalasi Komdis</span>
                    <h4 class="mb-0 fw-bold text-danger">{{ number_format($stats['eskalasi'] ?? 0) }}</h4>
                </div>
                <div class="avatar avatar-md bg-label-danger rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-gavel fs-3 text-danger"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Selesai / Closed</span>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['selesai'] ?? 0) }}</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-circle-check fs-3 text-success"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Card --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.bk-kasus.index') }}" class="row g-3">
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
                    <option value="disiplin" {{ request('kategori') === 'disiplin' ? 'selected' : '' }}>Kedisiplinan</option>
                    <option value="akademik" {{ request('kategori') === 'akademik' ? 'selected' : '' }}>Akademik</option>
                    <option value="sosial" {{ request('kategori') === 'sosial' ? 'selected' : '' }}>Sosial Emosional</option>
                    <option value="pribadi" {{ request('kategori') === 'pribadi' ? 'selected' : '' }}>Pribadi</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label fw-medium">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="terbuka" {{ request('status') === 'terbuka' ? 'selected' : '' }}>Terbuka (Open)</option>
                    <option value="dalam_proses" {{ request('status') === 'dalam_proses' ? 'selected' : '' }}>Dalam Proses</option>
                    <option value="eskalasi_komdis" {{ request('status') === 'eskalasi_komdis' ? 'selected' : '' }}>Eskalasi Komdis</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
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
                @forelse($kasusList as $item)
                    <tr>
                        <td><span class="fw-bold text-primary">#BK-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2 d-flex align-items-center justify-content-center">
                                    <span class="avatar-initial rounded-circle bg-label-warning">
                                        {{ strtoupper(substr($item->siswa?->nama_lengkap ?? 'S', 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-truncate text-white">{{ $item->siswa?->nama_lengkap ?? '—' }}</h6>
                                    <small class="text-white-50">{{ $item->siswa?->kelas?->nama ?? '—' }} • NIS: {{ $item->siswa?->nis ?? '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium text-white d-block">{{ $item->judul_kasus }}</span>
                            <small class="badge bg-label-warning">{{ ucfirst($item->kategori) }}</small>
                        </td>
                        <td>{{ $item->tanggal_lapor ? $item->tanggal_lapor->format('d M Y') : '—' }}</td>
                        <td>
                            @php
                                $keparahanBadge = match($item->tingkat_keparahan) {
                                    'tinggi' => 'danger',
                                    'sedang' => 'warning',
                                    default => 'info'
                                };
                            @endphp
                            <span class="badge bg-{{ $keparahanBadge }}">{{ ucfirst($item->tingkat_keparahan) }}</span>
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $item->status === 'eskalasi_komdis' ? 'danger' : ($item->status === 'selesai' ? 'success' : 'info') }}">
                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <span class="text-white-50 small">-</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ti tabler-folder-off fs-1 text-white-50 mb-2"></i>
                                <h6 class="text-white mb-1">Belum Ada Kasus Terdaftar</h6>
                                <p class="text-white-50 small mb-0">Belum terdapat data kasus BK yang tercatat di sistem.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($kasusList->hasPages())
        <div class="card-footer py-3 border-top border-secondary">
            {{ $kasusList->links() }}
        </div>
    @endif
</div>

{{-- MODAL TAMBAH KASUS --}}
<div class="modal fade" id="modalTambahKasus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1) !important;">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title fw-bold text-white"><i class="ti tabler-plus text-warning me-2"></i>Tambah Kasus BK Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.bk-kasus.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 row g-3 text-white">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Pilih Siswa <span class="text-danger">*</span></label>
                        <select name="siswa_id" class="form-select bg-dark text-white border-secondary" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_lengkap }} ({{ $s->kelas?->nama ?? 'Tanpa Kelas' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Kategori Kasus <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select bg-dark text-white border-secondary" required>
                            <option value="disiplin">Kedisiplinan</option>
                            <option value="akademik">Akademik</option>
                            <option value="sosial">Sosial Emosional</option>
                            <option value="pribadi">Pribadi</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-white">Judul Kasus <span class="text-danger">*</span></label>
                        <input type="text" name="judul_kasus" class="form-control bg-dark text-white border-secondary" placeholder="Ringkasan singkat masalah..." required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Tingkat Keparahan <span class="text-danger">*</span></label>
                        <select name="tingkat_keparahan" class="form-select bg-dark text-white border-secondary" required>
                            <option value="rendah">Rendah (Low)</option>
                            <option value="sedang">Sedang (Medium)</option>
                            <option value="tinggi">Tinggi (High)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Tanggal Kejadian <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_lapor" class="form-control bg-dark text-white border-secondary" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-white">Deskripsi & Kronologi Kasus</label>
                        <textarea name="deskripsi" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Detail kronologi atau laporan awal kasus..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-outline-secondary text-white" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="ti tabler-device-floppy me-1"></i>Simpan Kasus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
