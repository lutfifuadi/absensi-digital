@extends('layouts/layoutMaster')

@section('title', 'Riwayat Pelanggaran Siswa — BK')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <style>
    .form-control, .form-select, .btn {
      border-radius: 5px !important;
    }
  </style>
@endsection

@section('content')

  {{-- HERO HEADER --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-alert-triangle text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Bimbingan &amp; Konseling (BK)
          </div>
          <h4 class="das-hero__title text-gradient-gold">Riwayat Pelanggaran Siswa</h4>
          <p class="das-hero__subtitle">Pantau dan kelola seluruh pencatatan pelanggaran siswa lintas kelas secara konsisten.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('bk.pelanggaran.create') }}" class="das-btn das-btn--danger">
          <i class="ti tabler-plus me-1"></i> Catat Pelanggaran Baru
        </a>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px; background: var(--das-success-soft); color: var(--das-success);">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- FILTER PANEL --}}
  <div class="das-panel mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__body p-4">
      <form method="GET" action="{{ route('bk.pelanggaran.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fw-semibold small text-body-secondary" for="search">Cari Siswa / NIS</label>
          <input type="text" id="search" name="search" class="form-control" placeholder="Nama / NIS..." value="{{ $filters['search'] ?? '' }}">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold small text-body-secondary" for="kelas_id">Filter Kelas</label>
          <select id="kelas_id" name="kelas_id" class="form-select">
            <option value="">-- Semua Kelas --</option>
            @foreach($kelases as $kelas)
              <option value="{{ $kelas->id }}" {{ ($filters['kelas_id'] ?? '') == $kelas->id ? 'selected' : '' }}>
                {{ $kelas->nama ?? $kelas->nama_kelas }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold small text-body-secondary" for="kategori_id">Kategori Pelanggaran</label>
          <select id="kategori_id" name="kategori_id" class="form-select">
            <option value="">-- Semua Kategori --</option>
            @foreach($kategories as $kat)
              <option value="{{ $kat->id }}" {{ ($filters['kategori_id'] ?? '') == $kat->id ? 'selected' : '' }}>
                {{ $kat->nama ?? $kat->nama_kategori }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="das-btn das-btn--primary flex-grow-1">
            <i class="ti tabler-filter me-1"></i> Filter
          </button>
          <a href="{{ route('bk.pelanggaran.index') }}" class="das-btn das-btn--secondary">
            <i class="ti tabler-refresh"></i>
          </a>
        </div>

      </form>
    </div>
  </div>

  {{-- DATA PANEL & TABLE --}}
  <div class="das-panel" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
      style="border-color:rgba(255,255,255,0.08) !important; background:transparent;">
      <div class="d-flex align-items-center gap-2">
        <i class="ti tabler-list-check text-info"></i>
        <h6 class="das-panel__title mb-0">Daftar Kejadian Pelanggaran</h6>
      </div>
      <span class="badge bg-label-danger fw-bold" style="font-size:0.75rem;">Total: {{ number_format($pelanggaran->total()) }} Kejadian</span>
    </div>

    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
        <thead>
          <tr class="table-light">
            <th>Tanggal</th>
            <th>Siswa &amp; Kelas</th>
            <th>Kategori &amp; Jenis Pelanggaran</th>
            <th class="text-center">Poin</th>
            <th>Pencatat</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pelanggaran as $item)
            <tr>
              <td>
                <span class="fw-semibold text-body">{{ $item->tanggal_kejadian ? \Carbon\Carbon::parse($item->tanggal_kejadian)->translatedFormat('d M Y') : '-' }}</span>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar avatar-sm flex-shrink-0">
                    <span class="avatar-initial rounded-circle bg-label-danger fw-bold">
                      {{ strtoupper(substr($item->siswa->nama_lengkap ?? 'S', 0, 2)) }}
                    </span>
                  </div>
                  <div>
                    <strong class="text-body d-block">{{ $item->siswa->nama_lengkap ?? '-' }}</strong>
                    <small class="text-body-secondary">NIS: {{ $item->siswa->nis ?? '-' }} &bull; <span class="badge bg-label-info fw-semibold">{{ $item->siswa->kelas->nama ?? $item->siswa->kelas->nama_kelas ?? '-' }}</span></small>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge bg-label-warning me-1 fw-semibold">{{ $item->jenisPelanggaran->kategori->nama ?? $item->jenisPelanggaran->kategori->nama_kategori ?? '-' }}</span>
                <div class="fw-semibold text-body mt-1">{{ $item->jenisPelanggaran->nama ?? $item->jenisPelanggaran->nama_jenis ?? '-' }}</div>
              </td>
              <td class="text-center">
                <span class="badge bg-danger fs-6 fw-bold">+{{ $item->poin_saat_itu }}</span>
              </td>
              <td>
                <small class="text-body-secondary fw-medium">{{ $item->pencatat->name ?? '-' }}</small>
              </td>
              <td class="text-end">
                <a href="{{ route('bk.pelanggaran.show', $item->id) }}" class="btn btn-sm btn-label-primary btn-icon" title="Detail Pelanggaran">
                  <i class="ti tabler-eye"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-5 text-body-secondary">
                <i class="ti tabler-file-search d-block mb-2 text-secondary fs-1"></i>
                Tidak ada data pelanggaran siswa yang sesuai.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer d-flex justify-content-end p-3 border-top" style="border-color:rgba(255,255,255,0.08) !important;">
      {{ $pelanggaran->withQueryString()->links() }}
    </div>
  </div>

@endsection

