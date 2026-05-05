@extends('layouts/layoutMaster')

@section('title', 'Dashboard Staff TU')

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .glass-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2) !important;
      background: rgba(255, 255, 255, 0.06) !important;
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
            <i class="ti tabler-briefcase text-warning"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Panel Staff TU
          </div>
          <h4 class="das-hero__title text-gradient-gold">Selamat Datang, {{ $user->name }}</h4>
          <p class="das-hero__subtitle">Kelola kehadiran personal Anda secara mandiri.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="das-chip --primary p-2">
          <i class="ti tabler-calendar-stats me-1"></i> {{ now()->locale('id')->translatedFormat('l, d F Y') }}
        </div>
      </div>
    </div>
  </div>

  {{-- STATS SECTION --}}
  <div class="row gy-4 mb-5">
    <div class="col-6 col-md-4">
      <div class="das-panel h-100">
        <div class="das-panel__body py-4 text-center">
          <div class="avatar avatar-lg mx-auto mb-3">
            <span class="avatar-initial rounded bg-label-success shadow-sm">
              <i class="ti tabler-calendar-check fs-3"></i>
            </span>
          </div>
          <h5 class="mb-1 text-white fw-bold">{{ $hadir_saya ? 'Hadir' : 'Belum Absen' }}</h5>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Presensi Hari Ini</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4">
      <div class="das-panel h-100">
        <div class="das-panel__body py-4 text-center">
          <div class="avatar avatar-lg mx-auto mb-3">
            <span class="avatar-initial rounded bg-label-info shadow-sm">
              <i class="ti tabler-notebook fs-3"></i>
            </span>
          </div>
          <h4 class="mb-1 text-white fw-bold">{{ $total_absen_bulan_ini }}</h4>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Hadir Bulan Ini</small>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="das-panel h-100">
        <div class="das-panel__body py-4 text-center">
          <div class="avatar avatar-lg mx-auto mb-3">
            <span class="avatar-initial rounded bg-label-warning shadow-sm">
              <i class="ti tabler-clock fs-3"></i>
            </span>
          </div>
          <h4 class="mb-1 text-white fw-bold">{{ $total_izin_bulan_ini }}</h4>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Izin / Sakit</small>
        </div>
      </div>
    </div>
  </div>

  {{-- PERSONAL ACCESS --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-user-cog text-warning"></i> Akses Personal
      </h6>
    </div>
    <div class="das-panel__body p-4">
      <div class="row gy-4">
        <div class="col-md-6">
          <div class="d-flex align-items-center justify-content-between p-3 rounded" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);">
            <div>
              <h6 class="text-white fw-bold mb-1">Riwayat Absensi</h6>
              <p class="text-white-50 small mb-0">Daftar kehadiran harian Anda.</p>
            </div>
            <a href="{{ route('admin.absensi-staff.index') }}" class="btn das-btn --success">Riwayat</a>
          </div>
        </div>
        <div class="col-md-6">
          <div class="d-flex align-items-center justify-content-between p-3 rounded" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);">
            <div>
              <h6 class="text-white fw-bold mb-1">Izin & Sakit</h6>
              <p class="text-white-50 small mb-0">Ajukan izin berhalangan hadir.</p>
            </div>
            <a href="{{ route('admin.izin-sakit.index') }}" class="btn das-btn --info">Kelola Izin</a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
