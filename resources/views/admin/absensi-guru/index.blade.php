@extends('layouts/layoutMaster')

@section('title', 'Absensi Guru')

@section('page-style')
  <style>
    .guru-row-hover {
      transition: background 0.15s ease;
    }

    .guru-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .guru-action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      transition: all 0.2s ease;
      border: none;
      background: rgba(255, 255, 255, 0.05);
      color: inherit;
    }

    .guru-action-btn:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.1);
    }
  </style>
@endsection

@section('content')
  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-school text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Absensi Guru
          </div>
          <h4 class="das-hero__title text-gradient-gold">Absensi Guru</h4>
          <p class="das-hero__subtitle">Pantau dan kelola absensi guru dengan mudah.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.absensi-guru.create') }}" class="btn das-btn --primary">
          <i class="ti tabler-plus me-1"></i> Tambah Absensi
        </a>
      </div>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Absensi Guru
      </h6>
      <span class="das-chip --info">{{ count($absensi) }} Absensi</span>
    </div>
    <div class="das-panel__body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="color:inherit;">
          <thead
            style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
            <tr>
              <th class="ps-4 py-3" style="width:46px;">#</th>
              <th class="py-3">Guru</th>
              <th class="py-3 text-center">Tanggal</th>
              <th class="py-3 text-center">Jam Masuk</th>
              <th class="py-3 text-center">Jam Pulang</th>
              <th class="py-3 text-center">Status</th>
              <th class="py-3 text-center d-none d-md-table-cell">Metode</th>
              <th class="py-3 text-center d-none d-lg-table-cell">Keterangan</th>
              <th class="py-3 pe-4 text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($absensi as $item)
              <tr class="guru-row-hover">
                <td class="ps-4 text-white-50 small">{{ $loop->iteration }}</td>
                <td>
                  <div class="fw-medium">{{ $item->guru->nama_lengkap ?? '-' }}</div>
                </td>
                <td class="text-center">{{ $item->tanggal->format('d M Y') }}</td>
                <td class="text-center">
                  <span class="text-warning fw-bold">{{ $item->jam_masuk ?? '-' }}</span>
                </td>
                <td class="text-center">
                  <span class="text-info fw-bold">{{ $item->jam_pulang ?? '-' }}</span>
                </td>
                <td class="text-center">
                  <span
                    class="badge bg-label-{{ match ($item->status) {
                        'hadir' => 'success',
                        'sakit' => 'info',
                        'izin' => 'warning',
                        'alpha' => 'danger',
                        'terlambat' => 'secondary',
                        default => 'dark',
                    } }} text-capitalize px-2">{{ ucfirst($item->status) }}</span>
                </td>
                <td class="text-center d-none d-md-table-cell">
                  <span class="badge bg-label-{{ $item->metode === 'qr' ? 'primary' : 'secondary' }} px-2 py-1">
                    {{ strtoupper($item->metode) }}
                  </span>
                </td>
                <td class="text-center d-none d-lg-table-cell">{{ $item->keterangan ?: '–' }}</td>
                <td class="pe-4 text-end">
                  <div class="d-flex justify-content-end gap-1">
                    <a href="{{ route('admin.absensi-guru.edit', $item) }}" class="guru-action-btn text-warning"
                      title="Edit" data-bs-toggle="tooltip">
                      <i class="ti tabler-pencil fs-5"></i>
                    </a>
                    <form action="{{ route('admin.absensi-guru.destroy', $item) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Yakin ingin menghapus absensi guru ini?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="guru-action-btn text-danger" title="Hapus" data-bs-toggle="tooltip">
                        <i class="ti tabler-trash fs-5"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5">
                  <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                    <i class="ti tabler-calendar-off" style="font-size:2.5rem;"></i>
                    <span class="small">Belum ada data absensi guru.</span>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
