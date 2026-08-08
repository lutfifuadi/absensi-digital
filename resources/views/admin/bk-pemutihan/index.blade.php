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
                <div class="das-hero__logo-placeholder d-flex align-items-center justify-content-center">
                    <i class="ti tabler-sparkles text-success" style="font-size: 2rem;"></i>
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
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Total Poin Diputihkan</span>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['total_poin'] ?? 0) }} Poin</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-arrow-down-left fs-3 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Siswa Terbantu</span>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($stats['siswa_count'] ?? 0) }} Siswa</h4>
                </div>
                <div class="avatar avatar-md bg-label-primary rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-users fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-xl-4">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Total Transaksi Log</span>
                    <h4 class="mb-0 fw-bold text-info">{{ number_format($stats['total_log'] ?? 0) }} Log</h4>
                </div>
                <div class="avatar avatar-md bg-label-info rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-heart fs-3 text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table Pemutihan Poin --}}
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-list me-2 text-success"></i>Daftar Pemutihan Poin</h5>
        <form method="GET" action="{{ route('admin.bk-pemutihan.index') }}" class="d-flex align-items-center gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari siswa..." value="{{ request('search') }}" style="width: 200px;">
        </form>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Poin Dikurangi</th>
                    <th>Keterangan / Alasan</th>
                    <th>Petugas / BK</th>
                    <th class="text-end">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $item)
                    <tr>
                        <td>{{ $item->tanggal_pemutihan ? $item->tanggal_pemutihan->format('d M Y') : '—' }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2 d-flex align-items-center justify-content-center">
                                    <span class="avatar-initial rounded-circle bg-label-success">
                                        {{ strtoupper(substr($item->siswa?->nama_lengkap ?? 'S', 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-truncate text-white">{{ $item->siswa?->nama_lengkap ?? '—' }}</h6>
                                    <small class="text-white-50">{{ $item->siswa?->kelas?->nama ?? '—' }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-label-success fw-bold">-{{ $item->poin_yang_diputihkan }} Poin</span></td>
                        <td><span class="text-white">{{ $item->alasan_pemutihan }}</span></td>
                        <td><span class="text-white-50">{{ $item->diprosesOleh?->nama_lengkap ?? 'Admin BK' }}</span></td>
                        <td class="text-end">
                            <span class="text-success small fw-semibold"><i class="ti tabler-circle-check me-1"></i>Approved</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ti tabler-sparkles-off fs-1 text-white-50 mb-2"></i>
                                <h6 class="text-white mb-1">Belum Ada Pemutihan Poin</h6>
                                <p class="text-white-50 small mb-0">Belum terdapat riwayat pemutihan atau pemotongan poin pelanggaran siswa.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
        <div class="card-footer py-3 border-top border-secondary">
            {{ $logs->links() }}
        </div>
    @endif
</div>

{{-- MODAL EKSEKUSI PEMUTIHAN --}}
<div class="modal fade" id="modalEksekusiPemutihan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1) !important;">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title fw-bold text-white"><i class="ti tabler-sparkles text-success me-2"></i>Eksekusi Pemutihan Poin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.bk-pemutihan.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 row g-3 text-white">
                    <div class="col-12">
                        <label class="form-label fw-medium text-white">Pilih Siswa <span class="text-danger">*</span></label>
                        <select name="siswa_id" class="form-select bg-dark text-white border-secondary" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_lengkap }} ({{ $s->kelas?->nama ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Jumlah Pengurangan Poin <span class="text-danger">*</span></label>
                        <input type="number" name="poin_yang_diputihkan" class="form-control bg-dark text-white border-secondary" placeholder="Contoh: 15" min="1" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Tanggal Pemutihan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pemutihan" class="form-control bg-dark text-white border-secondary" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-white">Alasan / Program Pemutihan <span class="text-danger">*</span></label>
                        <textarea name="alasan_pemutihan" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Deskripsi kegiatan atau pertimbangan pemutihan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-outline-secondary text-white" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="ti tabler-device-floppy me-1"></i>Eksekusi Pemutihan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
