@extends('layouts/layoutMaster')

@section('title', 'Master Data')

@section('page-style')
  <style>
    .master-menu-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    .master-menu-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25) !important;
    }

    .master-menu-card .card-body {
      position: relative;
      overflow: hidden;
    }

    .master-menu-card .card-body::before {
      content: '';
      position: absolute;
      top: -30px;
      right: -30px;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      opacity: 0.07;
      background: currentColor;
    }

    .sub-item-link {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      transition: background 0.15s ease, transform 0.15s ease;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.04);
    }

    .sub-item-link:hover {
      background: rgba(255, 255, 255, 0.1);
      transform: translateX(3px);
    }

    .section-label {
      font-size: 0.68rem;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      opacity: 0.5;
      margin-bottom: 12px;
      padding-left: 2px;
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
            <i class="ti tabler-database"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Sistem Administrasi Sekolah
          </div>
          <h4 class="das-hero__title text-gradient-gold">Master Data</h4>
          <p class="das-hero__subtitle">Kelola seluruh data inti sekolah dalam satu panel terintegrasi.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: DATA AKADEMIK
  ═══════════════════════════════════════════════════════ --}}
  <div class="section-label">Data Akademik</div>
  <div class="row g-4 mb-5">
    @php
      $akademik = [
        ['title' => 'Tahun Ajaran', 'desc' => 'Kelola tahun ajaran dan status aktif.', 'icon' => 'tabler-calendar-stats', 'color' => 'warning', 'route' => route('admin.tahun-akademik.index')],
        ['title' => 'Kelas', 'desc' => 'Kelola rombongan belajar dan wali kelas.', 'icon' => 'tabler-door', 'color' => 'info', 'route' => route('admin.kelas.index')],
        ['title' => 'Jadwal Pelajaran', 'desc' => 'Atur jadwal pelajaran per kelas dan guru.', 'icon' => 'tabler-calendar-time', 'color' => 'primary', 'route' => route('admin.jadwal.index')],
        ['title' => 'Data Kegiatan Khusus', 'desc' => 'Kelola kegiatan khusus, ujian, dan ekstrakurikuler.', 'icon' => 'tabler-calendar-event', 'color' => 'secondary', 'route' => route('admin.kegiatan.index')],
      ];
    @endphp

    @foreach ($akademik as $item)
      <div class="col-sm-6 col-xl-4">
        <a href="{{ $item['route'] }}" class="text-decoration-none h-100 d-block">
          <div class="das-panel h-100 master-menu-card">
            <div class="das-panel__body p-4">
              <div class="avatar avatar-md mb-3">
                <span class="avatar-initial rounded bg-label-{{ $item['color'] }} shadow-sm">
                  <i class="ti {{ $item['icon'] }} fs-4"></i>
                </span>
              </div>
              <h6 class="mb-1 text-white fw-bold">{{ $item['title'] }}</h6>
              <p class="text-white-50 mb-3 small">{{ $item['desc'] }}</p>
              <span class="das-chip --{{ $item['color'] }} small">Buka <i class="ti tabler-arrow-right ms-1"></i></span>
            </div>
          </div>
        </a>
      </div>
    @endforeach
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: DATA PENGGUNA
  ═══════════════════════════════════════════════════════ --}}
  <div class="section-label">Data Pengguna</div>
  <div class="row g-4 mb-5">
    @php
      $pengguna = [
        ['title' => 'Siswa', 'desc' => 'Kelola biodata, kelas, dan QR code siswa.', 'icon' => 'tabler-users', 'color' => 'primary', 'route' => route('admin.siswa.index'), 'btn' => 'Kelola Siswa'],
        ['title' => 'Guru', 'desc' => 'Kelola biodata guru dan QR code absensi.', 'icon' => 'tabler-chalkboard-teacher', 'color' => 'success', 'route' => route('admin.guru.index'), 'btn' => 'Kelola Guru'],
        ['title' => 'Wali Kelas', 'desc' => 'Kelola biodata wali kelas dan QR code absensi.', 'icon' => 'tabler-users-group', 'color' => 'info', 'route' => route('admin.wali-kelas.index'), 'btn' => 'Kelola Wali Kelas'],
        ['title' => 'Staff TU', 'desc' => 'Kelola biodata staff dan QR code absensi.', 'icon' => 'tabler-briefcase', 'color' => 'warning', 'route' => route('admin.staff-tata-usaha.index'), 'btn' => 'Kelola Staff TU'],
        ['title' => 'Role', 'desc' => 'Kelola role sistem dan lihat jumlah user tiap role.', 'icon' => 'tabler-shield-check', 'color' => 'secondary', 'route' => route('admin.role.index'), 'btn' => 'Kelola Role'],
      ];
    @endphp

    @foreach ($pengguna as $item)
      <div class="col-sm-6 col-xl-4">
        <div class="das-panel h-100 master-menu-card">
          <div class="das-panel__body p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded bg-label-{{ $item['color'] }} shadow-sm">
                  <i class="ti {{ $item['icon'] }} fs-4"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-0 text-white fw-bold">{{ $item['title'] }}</h6>
                <small class="text-white-50">Manajemen {{ $item['title'] }}</small>
              </div>
            </div>
            <p class="text-white-50 small mb-3">{{ $item['desc'] }}</p>
            <a href="{{ $item['route'] }}" class="sub-item-link text-white w-100 d-flex align-items-center gap-2">
              <i class="ti tabler-external-link text-{{ $item['color'] }}"></i> {{ $item['btn'] }}
            </a>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 4: ABSENSI & PELAPORAN
  ═══════════════════════════════════════════════════════ --}}
  <div class="section-label">Absensi & Pelaporan</div>
  <div class="row g-4">
    {{-- Absensi --}}
    <div class="col-sm-6 col-xl-4">
      <div class="das-panel h-100 master-menu-card">
        <div class="das-panel__body p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="avatar avatar-md">
              <span class="avatar-initial rounded bg-label-danger shadow-sm">
                <i class="ti tabler-clipboard-check fs-4"></i>
              </span>
            </div>
            <div>
              <h6 class="mb-0 text-white fw-bold">Absensi</h6>
              <small class="text-white-50">Log Kehadiran</small>
            </div>
          </div>
          <p class="text-white-50 small mb-3">Akses data absensi harian seluruh entitas.</p>
          <div class="d-flex flex-column gap-2">
            <a href="{{ route('admin.absensi-siswa.index') }}" class="sub-item-link text-white">
              <i class="ti tabler-users text-primary"></i> Absensi Siswa
            </a>
            <a href="{{ route('admin.absensi-guru.index') }}" class="sub-item-link text-white">
              <i class="ti tabler-user-check text-success"></i> Absensi Guru
            </a>
            <a href="{{ route('admin.absensi-staff.index') }}" class="sub-item-link text-white">
              <i class="ti tabler-briefcase text-warning"></i> Absensi Staff TU
            </a>
          </div>
        </div>
      </div>
    </div>

    {{-- Izin & Sakit --}}
    <div class="col-sm-6 col-xl-4">
      <div class="das-panel h-100 master-menu-card">
        <div class="das-panel__body p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="avatar avatar-md">
              <span class="avatar-initial rounded bg-label-info shadow-sm">
                <i class="ti tabler-notes-medical fs-4"></i>
              </span>
            </div>
            <div>
              <h6 class="mb-0 text-white fw-bold">Izin & Sakit</h6>
              <small class="text-white-50">Pengajuan & Verifikasi</small>
            </div>
          </div>
          <p class="text-white-50 small mb-3">Verifikasi berkas pengajuan izin dan sakit.</p>
          <a href="{{ route('admin.izin-sakit.index') }}" class="sub-item-link text-white w-100 mt-2">
            <i class="ti tabler-file-medical text-info"></i> Kelola Izin & Sakit
          </a>
        </div>
      </div>
    </div>

    {{-- Laporan --}}
    <div class="col-sm-6 col-xl-4">
      <div class="das-panel h-100 master-menu-card">
        <div class="das-panel__body p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="avatar avatar-md">
              <span class="avatar-initial rounded bg-label-secondary shadow-sm">
                <i class="ti tabler-chart-bar fs-4"></i>
              </span>
            </div>
            <div>
              <h6 class="mb-0 text-white fw-bold">Laporan & Rekap</h6>
              <small class="text-white-50">Analitik & Export</small>
            </div>
          </div>
          <p class="text-white-50 small mb-3">Generate laporan kehadiran bulanan.</p>
          <a href="{{ route('admin.laporan.index') }}" class="sub-item-link text-white w-100 mt-2">
            <i class="ti tabler-table-export text-secondary"></i> Buka Modul Laporan
          </a>
        </div>
      </div>
    </div>
  </div>

@endsection
