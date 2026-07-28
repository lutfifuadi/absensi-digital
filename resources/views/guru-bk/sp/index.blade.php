@extends('layouts/layoutMaster')

@section('title', 'Surat Peringatan (SP) Siswa — BK')

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
            <i class="ti tabler-file-certificate text-warning"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Bimbingan &amp; Konseling (BK)
          </div>
          <h4 class="das-hero__title text-gradient-gold">Daftar Surat Peringatan (SP)</h4>
          <p class="das-hero__subtitle">Surat Peringatan formal yang telah diterbitkan oleh Guru BK / Sekolah.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('bk.sp.create') }}" class="das-btn das-btn--warning">
          <i class="ti tabler-file-certificate me-1"></i> Terbitkan SP Baru
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
      <form method="GET" action="{{ route('bk.sp.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fw-semibold small text-body-secondary" for="search">Cari Siswa / NIS</label>
          <input type="text" id="search" name="search" class="form-control" placeholder="Nama / NIS..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold small text-body-secondary" for="level_sp">Level SP</label>
          <select id="level_sp" name="level_sp" class="form-select">
            <option value="">-- Semua Level --</option>
            <option value="SP1" {{ ($filters['level_sp'] ?? '') == 'SP1' ? 'selected' : '' }}>SP1</option>
            <option value="SP2" {{ ($filters['level_sp'] ?? '') == 'SP2' ? 'selected' : '' }}>SP2</option>
            <option value="SP3" {{ ($filters['level_sp'] ?? '') == 'SP3' ? 'selected' : '' }}>SP3</option>
          </select>
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
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="das-btn das-btn--primary flex-grow-1">
            <i class="ti tabler-filter me-1"></i> Filter
          </button>
          <a href="{{ route('bk.sp.index') }}" class="das-btn das-btn--secondary">
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
        <i class="ti tabler-certificate text-warning"></i>
        <h6 class="das-panel__title mb-0">Daftar Surat Peringatan Diterbitkan</h6>
      </div>
      <span class="badge bg-label-warning fw-bold" style="font-size:0.75rem;">Total: {{ number_format($spList->total()) }} SP</span>
    </div>

    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
        <thead>
          <tr class="table-light">
            <th>Tingkat SP</th>
            <th>Siswa &amp; Kelas</th>
            <th>Poin Saat SP</th>
            <th>Tanggal Penerbitan</th>
            <th>Penerbit</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($spList as $sp)
            <tr>
              <td>
                <span class="badge bg-warning text-dark fw-bold px-3 py-1 fs-6">{{ $sp->level_sp }}</span>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar avatar-sm flex-shrink-0">
                    <span class="avatar-initial rounded-circle bg-label-warning fw-bold">
                      {{ strtoupper(substr($sp->siswa->nama_lengkap ?? 'S', 0, 2)) }}
                    </span>
                  </div>
                  <div>
                    <strong class="text-body d-block">{{ $sp->siswa->nama_lengkap ?? '-' }}</strong>
                    <small class="text-body-secondary">NIS: {{ $sp->siswa->nis ?? '-' }} &bull; <span class="badge bg-label-info fw-semibold">{{ $sp->siswa->kelas->nama ?? $sp->siswa->kelas->nama_kelas ?? '-' }}</span></small>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge bg-danger fs-6 fw-bold">{{ $sp->total_poin_saat_sp }} Poin</span>
              </td>
              <td>
                <span class="fw-semibold text-body">{{ $sp->tanggal_sp ? \Carbon\Carbon::parse($sp->tanggal_sp)->translatedFormat('d M Y') : '-' }}</span>
              </td>
              <td>
                <small class="text-body-secondary fw-medium">{{ $sp->penerbit->name ?? 'Sistem / Auto' }}</small>
              </td>
              <td>
                <small class="text-body-secondary text-truncate d-inline-block" style="max-width: 250px;">{{ $sp->catatan_tambahan ?? '-' }}</small>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-5 text-body-secondary">
                <i class="ti tabler-file-off d-block mb-2 text-secondary fs-1"></i>
                Belum ada Surat Peringatan (SP) yang diterbitkan.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer d-flex justify-content-end p-3 border-top" style="border-color:rgba(255,255,255,0.08) !important;">
      {{ $spList->withQueryString()->links() }}
    </div>
  </div>

@endsection

