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
                <div class="das-hero__logo-placeholder d-flex align-items-center justify-content-center">
                    <i class="ti tabler-shield-x text-danger" style="font-size: 2rem;"></i>
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
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Sanksi Aktif</span>
                    <h4 class="mb-0 fw-bold text-danger">{{ number_format($stats['sanksi_aktif'] ?? 0) }} Sanksi</h4>
                </div>
                <div class="avatar avatar-md bg-label-danger rounded d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                    <i class="ti tabler-alert-circle text-danger" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Aktif SP 1</span>
                    <h4 class="mb-0 fw-bold text-warning">{{ number_format($stats['sp1_aktif'] ?? 0) }} Siswa</h4>
                </div>
                <div class="avatar avatar-md bg-label-warning rounded d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                    <i class="ti tabler-file-text text-warning" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Aktif SP 2 &amp; 3</span>
                    <h4 class="mb-0 fw-bold text-danger">{{ number_format($stats['sp23_aktif'] ?? 0) }} Siswa</h4>
                </div>
                <div class="avatar avatar-md bg-label-danger rounded d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                    <i class="ti tabler-shield-x text-danger" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Sanksi Selesai</span>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['sanksi_selesai'] ?? 0) }} Sanksi</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                    <i class="ti tabler-circle-check text-success" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sanksi & SP Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-list me-2 text-danger"></i>Status Sanksi &amp; Surat Peringatan</h5>
        <form method="GET" action="{{ route('admin.komdis-sanksi.index') }}" class="d-flex align-items-center gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari siswa..." value="{{ request('search') }}" style="width: 200px;">
        </form>
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
                @forelse($sanksis as $item)
                    <tr>
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
                            @php
                                $spBadge = match(true) {
                                    str_contains($item->nama_sanksi, 'SP 3') => 'danger',
                                    str_contains($item->nama_sanksi, 'SP 2') => 'danger',
                                    str_contains($item->nama_sanksi, 'SP 1') => 'warning',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $spBadge }}">{{ str_contains($item->nama_sanksi, 'SP') ? \Illuminate\Support\Str::limit($item->nama_sanksi, 12) : 'Sanksi' }}</span>
                        </td>
                        <td>
                            <span class="fw-medium text-white d-block">{{ $item->nama_sanksi }}</span>
                            <small class="text-white-50">{{ \Illuminate\Support\Str::limit($item->deskripsi_sanksi ?? '—', 50) }}</small>
                        </td>
                        <td>
                            <span class="fw-semibold text-white d-block">
                                {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d M Y') : '—' }}
                                {{ $item->tanggal_selesai ? ' - ' . $item->tanggal_selesai->format('d M Y') : '' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $item->status === 'aktif' ? 'danger' : ($item->status === 'selesai' ? 'success' : 'secondary') }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-white-50">-</span>
                        </td>
                        <td class="text-end">
                            @if($item->status === 'aktif')
                                <form action="{{ route('admin.komdis-sanksi.update', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="nama_sanksi" value="{{ $item->nama_sanksi }}">
                                    <input type="hidden" name="tanggal_mulai" value="{{ $item->tanggal_mulai ? $item->tanggal_mulai->format('Y-m-d') : now()->format('Y-m-d') }}">
                                    <input type="hidden" name="status" value="selesai">
                                    <button type="submit" class="btn btn-sm btn-label-success" onclick="return confirm('Apakah Anda yakin ingin menandai sanksi ini telah selesai?')">Selesaikan</button>
                                </form>
                            @else
                                <span class="text-white-50 small">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ti tabler-shield-off fs-1 text-white-50 mb-2"></i>
                                <h6 class="text-white mb-1">Belum Ada Data Sanksi</h6>
                                <p class="text-white-50 small mb-0">Belum terdapat data sanksi kedisiplinan atau SP yang tercatat di sistem.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($sanksis->hasPages())
        <div class="card-footer py-3 border-top border-secondary">
            {{ $sanksis->links() }}
        </div>
    @endif
</div>
@endsection
