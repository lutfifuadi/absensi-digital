@extends('layouts/layoutMaster')

@section('title', 'Dashboard Utama — ' . ($pengaturanArr['nama_sekolah'] ?? 'Sistem Absensi'))

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER — identitas sekolah + jam live
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__scanline" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner">
      {{-- Identitas --}}
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          @if (isset($pengaturanArr['logo_sekolah']))
            <img src="{{ asset('uploads/logo/' . $pengaturanArr['logo_sekolah']) }}" alt="Logo {{ $pengaturanArr['nama_sekolah'] ?? 'sekolah' }}" class="das-hero__logo">
          @else
            <div class="das-hero__logo-placeholder">
              <i class="ti tabler-school" aria-hidden="true"></i>
            </div>
          @endif
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="das-hero__pulse-dot" aria-hidden="true"></span>
            Sistem Administrasi Sekolah
          </div>
          <h1 class="das-hero__school">{{ $pengaturanArr['nama_sekolah'] ?? $pengaturanArr['nama_lembaga'] ?? 'Sistem Absensi' }}</h1>
          <p class="das-hero__welcome">Selamat datang kembali, <strong>{{ $user->name }}</strong> <span aria-hidden="true">👋</span></p>
        </div>
      </div>

      {{-- Clock --}}
      <div class="das-hero__clock" role="status" aria-live="off">
        <div class="das-hero__date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        <div class="das-hero__time">
          <span id="live-clock">00:00:00</span>
          <span class="das-hero__live-badge"><span class="das-hero__pulse-dot" aria-hidden="true"></span>LIVE</span>
        </div>
        <div class="das-hero__tz">WAKTU INDONESIA BARAT (WIB)</div>
      </div>
    </div>
  </div>{{-- /das-hero --}}


  {{-- ═══════════════════════════════════════════════════════
       SECTION 1B: STATS ROW — 4 Card Statistik Dinamis & Interaktif
       ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    {{-- Card 1: Tingkat Kehadiran --}}
    <div class="col-lg-3 col-sm-6">
      <a href="{{ route('admin.laporan.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-success h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="ti tabler-percentage fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $tingkatKehadiran }}%</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Tingkat Kehadiran</p>
            <p class="mb-0">
              <span class="text-success fw-medium me-2">{{ $hadirCount + $terlambatCount }} Siswa</span>
              <small class="text-body-secondary">hadir & terlambat</small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 2: Siswa Terlambat --}}
    <div class="col-lg-3 col-sm-6">
      <a href="{{ route('admin.absensi-siswa.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-warning h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="ti tabler-clock-exclamation fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $terlambatCount }}</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Siswa Terlambat</p>
            <p class="mb-0">
              <span class="text-warning fw-medium me-2">Evaluasi Kehadiran</span>
              <small class="text-body-secondary">butuh tindakan</small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 3: Izin & Sakit --}}
    <div class="col-lg-3 col-sm-6">
      <a href="{{ route('admin.izin-sakit.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-info h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="ti tabler-clipboard-check fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $izinCount + $sakitCount }}</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Izin & Sakit</p>
            <p class="mb-0">
              <span class="text-info fw-medium me-2">Keterangan Resmi</span>
              <small class="text-body-secondary">sakit/izin</small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 4: Belum Presensi --}}
    <div class="col-lg-3 col-sm-6">
      <a href="#" class="text-decoration-none stats-card-link" data-bs-toggle="modal" data-bs-target="#modalBelumAbsen">
        <div class="card card-grad-danger h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-danger">
                  <i class="ti tabler-user-question fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $belumAbsen }}</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Belum Presensi</p>
            <p class="mb-0">
              <span class="text-danger fw-medium me-2">Tindakan Segera</span>
              <small class="text-body-secondary">butuh konfirmasi</small>
            </p>
          </div>
        </div>
      </a>
    </div>
  </div>{{-- /row g-6 mb-6 (Stats Row) --}}


  {{-- ═══════════════════════════════════════════════════════
       SECTION 1C: INFO AKADEMIK + QUICK MENU
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    {{-- Card Informasi Akademik --}}
    <div class="col-lg-8">
      <div class="card card-grad-primary h-100">
        <div class="card-body">
          <div class="row align-items-center">
            {{-- Kiri: Detail Kelas --}}
            <div class="col-12 col-md-6 mb-4 mb-md-0">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="ti tabler-school fs-4"></i>
                  </span>
                </div>
                <div>
                  <h6 class="mb-0">Informasi Akademik</h6>
                  <small class="text-body-secondary">Tahun Ajaran {{ $tahunAkademikAktif->nama ?? 'Aktif' }} {{ $tahunAkademikAktif->semester ?? '' }}</small>
                </div>
              </div>
              <div class="d-flex flex-wrap gap-0">
                <div class="text-center px-3 border-end border-secondary border-opacity-25 pe-4">
                  <h3 class="mb-0 text-primary fw-bold">{{ $totalKelas }}</h3>
                  <small class="text-body-secondary">Total Kelas</small>
                </div>
                <div class="text-center px-3 border-end border-secondary border-opacity-25 pe-4">
                  <h3 class="mb-0 text-success fw-bold">{{ $totalSiswa }}</h3>
                  <small class="text-body-secondary">Total Siswa</small>
                </div>
                <div class="text-center px-3 border-end border-secondary border-opacity-25 pe-4">
                  <h3 class="mb-0 text-warning fw-bold">{{ $totalSiswaWajibAbsen ?? $totalSiswa }}</h3>
                  <small class="text-body-secondary">Wajib Absen</small>
                </div>
                <div class="text-center px-3">
                  <h3 class="mb-0 text-info fw-bold">{{ $totalGuru }}</h3>
                  <small class="text-body-secondary">Total Guru</small>
                </div>
              </div>
            </div>

            {{-- Kanan: Tahun Ajaran Active Badge --}}
            <div class="col-12 col-md-6 text-md-end">
              <div class="d-inline-flex align-items-center gap-2 p-3 rounded-3 bg-label-primary bg-opacity-10 shadow-sm">
                <i class="ti tabler-calendar-stats fs-2 text-primary"></i>
                <div class="text-start">
                  <small class="text-body-secondary d-block">Semester Aktif</small>
                  <span class="fw-bold fs-5">{{ $tahunAkademikAktif->semester ?? 'Ganjil' }} {{ $tahunAkademikAktif->nama ?? date('Y') }}</span>
                  <br>
                  <small class="text-body-secondary">
                    {{ $tahunAkademikAktif ? \Carbon\Carbon::parse($tahunAkademikAktif->tanggal_mulai)->translatedFormat('d M Y') : '-' }}
                    —
                    {{ $tahunAkademikAktif ? \Carbon\Carbon::parse($tahunAkademikAktif->tanggal_selesai)->translatedFormat('d M Y') : '-' }}
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Card Quick Menu --}}
    <div class="col-lg-4">
      <div class="card card-grad-gold h-100">
        <div class="card-body d-flex flex-column justify-content-center">
          <div class="das-quick-v2 d-grid gap-2">
            @php
              $quickLinks = [
                ['icon' => 'tabler-database', 'label' => 'Master', 'route' => route('admin.master-data'), 'color' => 'primary'],
                ['icon' => 'tabler-school', 'label' => 'Absensi', 'route' => route('admin.absensi-siswa.index'), 'color' => 'success'],
                ['icon' => 'tabler-report-analytics', 'label' => 'Laporan', 'route' => route('admin.laporan.index'), 'color' => 'warning'],
                ['icon' => 'tabler-clipboard-check', 'label' => 'Izin', 'route' => route('admin.izin-sakit.index'), 'color' => 'danger'],
                ['icon' => 'tabler-users', 'label' => 'Users', 'route' => route('admin.users.index'), 'color' => 'dark'],
                ['icon' => 'tabler-settings', 'label' => 'Settings', 'route' => route('admin.pengaturan.index'), 'color' => 'info'],
                ['icon' => 'tabler-cloud-download', 'label' => 'Update', 'route' => route('admin.update.index'), 'color' => 'primary'],
                ['icon' => 'tabler-scan', 'label' => 'Scan', 'route' => route('public.kegiatan.index'), 'color' => 'danger'],
              ];
            @endphp
            @foreach ($quickLinks as $link)
              <a href="{{ $link['route'] }}"
                 class="d-flex flex-column align-items-center gap-1 p-3 rounded-2 bg-label-{{ $link['color'] }} text-decoration-none"
                 style="min-width:70px">
                <i class="ti {{ $link['icon'] }} fs-4"></i>
                <small class="fw-medium text-body" style="font-size:0.7rem">{{ $link['label'] }}</small>
              </a>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>


  {{-- ================================================================
     SECTION 2: ANALYTICS GRID -- Vuexy-native Bootstrap row
     ================================================================ --}}
  <div class="row g-6">

    {{-- -- Row 1, Col 1: School Overview (static card) -- --}}
    <div class="col-xl-6 col-12">
      <div class="card swiper-card-advance-bg h-100">
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <h5 class="text-white mb-0">{{ $pengaturanArr['nama_sekolah'] ?? 'Sekolah' }}</h5>
              <small class="text-white opacity-75">Sistem Presensi Digital</small>
            </div>
            <div class="col-lg-7 col-md-9 col-12 pt-md-9">
              <h6 class="text-white mt-0 mt-md-3 mb-4">Statistik Hari Ini</h6>
              <div class="row">
                <div class="col-6">
                  <ul class="list-unstyled mb-0">
                    <li class="d-flex mb-4 align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg fs-4 fw-bold">{{ $hadirCount + $terlambatCount }}</p>
                      <p class="mb-0 text-white">Hadir</p>
                    </li>
                    <li class="d-flex align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg fs-4 fw-bold">{{ $sakitCount + $izinCount }}</p>
                      <p class="mb-0 text-white">Izin/Sakit</p>
                    </li>
                  </ul>
                </div>
                <div class="col-6">
                  <ul class="list-unstyled mb-0">
                    <li class="d-flex mb-4 align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg fs-4 fw-bold">{{ $alphaCount }}</p>
                      <p class="mb-0 text-white">Alpha</p>
                    </li>
                    <li class="d-flex align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg fs-4 fw-bold">{{ $belumAbsen }}</p>
                      <p class="mb-0 text-white">Belum Absen</p>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-lg-5 col-md-3 col-12 my-4 my-md-0 text-center">
              <div class="d-flex align-items-center justify-content-center h-100">
                <div class="text-center opacity-30">
                  <i class="ti tabler-school" style="font-size: 8rem; color: #D4A94A;"></i>
                </div>
              </div>
            </div>
          </div>
          {{-- Bottom mini-stats: Guru & Staff --}}
          <div class="row mt-4 pt-2 border-top border-white border-opacity-10">
            <div class="col-6">
              <p class="text-white mb-0 small opacity-75">
                <i class="ti tabler-chalkboard-teacher me-1"></i> Guru: {{ $absensiGuruHariIni }}/{{ $totalGuru }}
              </p>
            </div>
            <div class="col-6">
              <p class="text-white mb-0 small opacity-75">
                <i class="ti tabler-user-check me-1"></i> Staff: {{ $absensiStaffHariIni }}/{{ $totalStaff }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- -- Row 1, Col 3: Overview Kehadiran -- --}}
    @php
      $totalWajib = $totalSiswa ?? ($hadirCount + $terlambatCount + $sakitCount + $izinCount + $alphaCount + $belumAbsen);
      $hadirTotal = $hadirCount + $terlambatCount;
      $hadirPct = $totalWajib > 0 ? round(($hadirTotal / $totalWajib) * 100, 1) : 0;
      $tidakPct = $totalWajib > 0 ? round((($totalWajib - $hadirTotal) / $totalWajib) * 100, 1) : 0;
    @endphp
    <div class="col-xl-6 col-sm-6">
      <div class="card card-grad-primary h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between">
            <p class="mb-0 text-body">Overview Kehadiran</p>
            <p class="card-text fw-medium text-success">{{ $hadirPct }}%</p>
          </div>
          <h4 class="card-title mb-1">{{ $hadirTotal }} <small class="text-body fw-normal">dari {{ $totalWajib }}</small></h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-4">
              <div class="d-flex gap-2 align-items-center mb-2">
                <span class="badge bg-label-success p-1 rounded"><i class="ti tabler-circle-check icon-sm"></i></span>
                <p class="mb-0">Hadir</p>
              </div>
              <h5 class="mb-0 pt-1 text-success">{{ $hadirPct }}%</h5>
              <small class="text-body-secondary">{{ $hadirTotal }}</small>
            </div>
            <div class="col-4">
              <div class="divider divider-vertical">
                <div class="divider-text">
                  <span class="badge-divider-bg bg-label-secondary">VS</span>
                </div>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="d-flex gap-2 justify-content-end align-items-center mb-2">
                <p class="mb-0">Tidak</p>
                <span class="badge bg-label-danger p-1 rounded"><i class="ti tabler-ban icon-sm"></i></span>
              </div>
              <h5 class="mb-0 pt-1 text-danger">{{ $tidakPct }}%</h5>
              <small class="text-body-secondary">{{ $totalWajib - $hadirTotal }}</small>
            </div>
          </div>
          <div class="d-flex align-items-center mt-6">
            <div class="progress w-100" style="height: 8px; border-radius: 4px;">
              <div class="progress-bar bg-success" style="width: {{ $hadirPct }}%" role="progressbar" aria-valuenow="{{ $hadirPct }}" aria-valuemin="0" aria-valuemax="100"></div>
              <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $tidakPct }}%" aria-valuenow="{{ $tidakPct }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- -- Row 2, Col 1: Tren Kehadiran (7 Hari) — selaras dengan siswa dashboard -- --}}
    <div class="col-md-6">
      <div class="siswa-chart-card h-100">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot das-panel__icon-dot--success"></span>
            Tren Kehadiran — 7 Hari Terakhir
          </div>
          <a href="{{ route('admin.laporan.index') }}" class="das-btn das-btn--ghost" style="font-size:0.72rem;">
            <i class="ti tabler-external-link me-1"></i> Lihat Semua
          </a>
        </div>
        <div class="das-chart-mount">
          <div id="chartKehadiranMingguan" style="min-height:200px;width:100%;"></div>
        </div>
        <div class="px-4 pb-4 pt-2 border-top" style="border-color: rgba(231,236,245,0.06) !important;">
          <div class="row text-center g-3">
            <div class="col-4">
              <div class="p-2 rounded-2" style="background: rgba(47, 191, 113, 0.05); border: 1px solid rgba(47, 191, 113, 0.1);">
                <small class="text-body-secondary d-block mb-1 text-uppercase text-nowrap" style="font-size: 0.65rem; letter-spacing: 0.5px;">Rerata Hadir</small>
                <h5 class="mb-0 text-success fw-bold text-nowrap" style="font-size: 0.95rem;">{{ $rataRataHadir }}</h5>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-2" style="background: rgba(58, 183, 224, 0.05); border: 1px solid rgba(58, 183, 224, 0.1);">
                <small class="text-body-secondary d-block mb-1 text-uppercase text-nowrap" style="font-size: 0.65rem; letter-spacing: 0.5px;">Hari Terbaik</small>
                <h5 class="mb-0 text-info fw-bold text-nowrap" style="font-size: 0.95rem;">{{ $hariTerbaik }}</h5>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-2" style="background: rgba(239, 90, 90, 0.05); border: 1px solid rgba(239, 90, 90, 0.1);">
                <small class="text-body-secondary d-block mb-1 text-uppercase text-nowrap" style="font-size: 0.65rem; letter-spacing: 0.5px;">Ketidakhadiran</small>
                <h5 class="mb-0 text-danger fw-bold text-nowrap" style="font-size: 0.95rem;">{{ $tingkatKetidakhadiran }}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- -- Row 2, Col 2: Attendance Tracker -- --}}
    <div class="col-md-6">
      <div class="card card-grad-success h-100">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title mb-0">
            <h5 class="mb-1">Attendance Tracker</h5>
            <p class="card-subtitle">Hari Ini</p>
          </div>
          <span class="badge bg-label-primary">{{ now()->translatedFormat('d F Y') }}</span>
        </div>
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-6 col-12">
              <div class="mb-4">
                <span class="text-body-secondary small text-uppercase letter-spacing-1 d-block mb-1">Total Hadir Hari Ini</span>
                <h2 class="mb-0 fw-black text-success" style="font-size: 2.2rem; font-family: 'Plus Jakarta Sans', sans-serif;">{{ $hadirCount + $terlambatCount }}</h2>
              </div>
              @php
                $trackerItems = [
                  ['label' => 'Hadir', 'val' => $hadirCount, 'color' => 'success', 'icon' => 'tabler-circle-check'],
                  ['label' => 'Terlambat', 'val' => $terlambatCount, 'color' => 'warning', 'icon' => 'tabler-clock-exclamation'],
                  ['label' => 'Sakit', 'val' => $sakitCount, 'color' => 'info', 'icon' => 'tabler-heart'],
                  ['label' => 'Izin', 'val' => $izinCount, 'color' => 'warning', 'icon' => 'tabler-clipboard-check'],
                  ['label' => 'Alpha', 'val' => $alphaCount, 'color' => 'danger', 'icon' => 'tabler-ban'],
                  ['label' => 'Belum Absen', 'val' => $belumAbsen, 'color' => 'dark', 'icon' => 'tabler-user-question'],
                ];
              @endphp
              <div class="row g-2">
                @foreach ($trackerItems as $item)
                  <div class="col-6">
                    <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); transition: all 0.2s ease;">
                      <div class="badge rounded bg-label-{{ $item['color'] }} p-1 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; flex-shrink: 0;">
                        <i class="ti {{ $item['icon'] }}" style="font-size: 0.95rem;"></i>
                      </div>
                      <div class="overflow-hidden">
                        <small class="text-body-secondary d-block text-truncate" style="font-size: 0.68rem; font-weight: 500;">{{ $item['label'] }}</small>
                        <h6 class="mb-0 fw-bold" style="font-size: 0.88rem; line-height: 1.2;">{{ $item['val'] }}</h6>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
            <div class="col-md-6 col-12 d-flex justify-content-center align-items-center mt-3 mt-md-0">
              @if ($totalAbsensiHariIni > 0 || $totalSiswa > 0)
                <div id="chartDonutStatus" class="w-100" style="max-width: 250px;"></div>
              @else
                <div class="d-flex flex-column align-items-center justify-content-center py-5 text-body-secondary">
                  <i class="ti tabler-chart-pie fs-1 mb-2" aria-hidden="true"></i>
                  <span>Belum ada data</span>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- -- Row 4, Col 1: Metode Absensi -- --}}
    <div class="col-xxl-4 col-md-6 col-12">
      <div class="card card-grad-warning h-100">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title mb-0">
            <h5 class="mb-1">Metode Absensi</h5>
            <p class="card-subtitle">Cara masuk hari ini</p>
          </div>
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            @forelse ($metodeAbsensi ?? [] as $metode)
              @php
                $iconMap = [
                  'qr' => 'tabler-qrcode',
                  'manual' => 'tabler-keyboard',
                  'face' => 'tabler-scan',
                  'fingerprint' => 'tabler-fingerprint',
                  'kartu' => 'tabler-credit-card',
                ];
                $metodeIcon = $iconMap[$metode['key']] ?? 'tabler-device-analytics';
              @endphp
              <li class="das-metode-item mb-3 p-2 rounded-2 d-flex align-items-center">
                <div class="badge bg-label-secondary text-body p-2 me-4 rounded">
                  <i class="ti {{ $metodeIcon }} icon-md"></i>
                </div>
                <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                  <div class="me-2">
                    <h6 class="mb-0">{{ $metode['label'] }}</h6>
                  </div>
                  <div class="d-flex align-items-center">
                    <span class="badge bg-label-secondary rounded-pill fw-bold">{{ $metode['total'] }}</span>
                  </div>
                </div>
              </li>
            @empty
              <li class="d-flex align-items-center justify-content-center py-4 text-body-secondary">
                <span>Belum ada data</span>
              </li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    {{-- -- Row 4, Col 2: Log Absensi Real-time -- --}}
    <div class="col-xxl-8">
      <div class="card card-grad-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div class="card-title mb-0">
            <h5 class="mb-1">Log Absensi Real-time</h5>
            <p class="card-subtitle">5 Absensi Terakhir</p>
          </div>
          <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2" onclick="refreshDashboardData()" title="Refresh">
            <i class="ti tabler-refresh icon-md"></i>
          </button>
        </div>
        <div class="table-responsive mb-4">
          <table class="table datatable-project table-sm table-hover das-log-table">
            <thead style="background: rgba(255,255,255,0.04); border-top: 1px solid rgba(255,255,255,0.08);">
              <tr>
                <th class="fw-medium">Waktu</th>
                <th class="fw-medium">Nama Siswa</th>
                <th class="fw-medium">Kelas</th>
                <th class="fw-medium">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recentLogs ?? [] as $log)
                <tr>
                  <td class="text-body-secondary font-monospace">
                    <small>{{ $log->jam_masuk ?? ($log->created_at ? $log->created_at->format('H:i:s') : '-') }}</small>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="https://ui-avatars.com/api/?name={{ urlencode($log->siswa->nama_lengkap ?? 'Unknown') }}&background=2FBF71&color=fff&size=28"
                           class="rounded-circle me-2" width="28" height="28" alt="" loading="lazy">
                      <span>{{ $log->siswa->nama_lengkap ?? '-' }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-label-info rounded-pill">{{ $log->siswa->kelas->nama ?? $log->kelas->nama ?? '-' }}</span>
                  </td>
                  <td>
                    @php
                      $statusMap = [
                        'hadir' => ['badge' => 'bg-label-success', 'icon' => 'tabler-circle-check'],
                        'terlambat' => ['badge' => 'bg-label-warning', 'icon' => 'tabler-clock-exclamation'],
                        'sakit' => ['badge' => 'bg-label-info', 'icon' => 'tabler-heart'],
                        'izin' => ['badge' => 'bg-label-warning', 'icon' => 'tabler-clipboard-check'],
                        'alpha' => ['badge' => 'bg-label-danger', 'icon' => 'tabler-ban'],
                      ];
                      $status = $statusMap[$log->status] ?? ['badge' => 'bg-label-secondary', 'icon' => 'tabler-question-mark'];
                    @endphp
                    <span class="badge {{ $status['badge'] }} rounded-pill">
                      <i class="ti {{ $status['icon'] }} me-1"></i>
                      {{ ucfirst($log->status ?? '-') }}
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-body-secondary py-4">Belum ada log absensi</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>{{-- /row g-6 --}}


  {{-- ═══════════════════════════════════════════════════════
       MODAL: BELUM ABSEN
  ═══════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalBelumAbsen" tabindex="-1" aria-hidden="true" aria-labelledby="modalBelumAbsenLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content das-modal">
        <div class="das-modal__head">
          <h5 class="das-modal__title" id="modalBelumAbsenLabel"><i class="ti tabler-user-question me-2" aria-hidden="true"></i>Siswa Belum Absen</h5>
          <button type="button" class="das-modal__close" data-bs-dismiss="modal" aria-label="Tutup"><i class="ti tabler-x" aria-hidden="true"></i></button>
        </div>
        <div class="das-modal__body">
          <div class="das-modal__stat">
            <div class="das-modal__stat-val">{{ $belumAbsen }}</div>
            <div class="das-modal__stat-label">Total Siswa Belum Absen Hari Ini</div>
            <div class="das-modal__stat-warn"><i class="ti tabler-alert-circle" aria-hidden="true"></i> Segera lakukan follow up.</div>
          </div>
          
          {{-- Search Input --}}
          <div class="mb-4">
            <div class="input-group input-group-merge" style="border: 1px solid rgba(231, 236, 245, 0.08); border-radius: 5px; overflow: hidden; background: rgba(255, 255, 255, 0.02);">
              <span class="input-group-text border-0 bg-transparent" id="siswa-search-icon" style="padding-left: 1rem;"><i class="ti tabler-search text-body-secondary fs-4"></i></span>
              <input type="text" id="search-siswa-belum-absen" class="form-control border-0 bg-transparent text-white focus:ring-0" placeholder="Cari nama siswa atau kelas..." aria-label="Cari nama siswa atau kelas..." aria-describedby="siswa-search-icon" style="box-shadow: none; font-size: 0.88rem; padding: 0.6rem 0.5rem;">
            </div>
          </div>

          {{-- Table Data --}}
          <div class="table-responsive das-table-wrap mb-4" style="border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 5px; background: var(--das-surface-2);">
            <table class="table table-hover das-table mb-0">
              <thead>
                <tr style="background: rgba(255, 255, 255, 0.03);">
                  <th class="py-3 text-white text-center" style="width: 50px;">#</th>
                  <th class="py-3 text-white">Nama Siswa</th>
                  <th class="py-3 text-white">Kelas</th>
                  <th class="py-3 text-white">Wali Kelas</th>
                  <th class="py-3 text-white">No HP Orang Tua</th>
                  <th class="py-3 text-white text-center" style="width: 130px;">Aksi</th>
                </tr>
              </thead>
              <tbody id="tbody-siswa-belum-absen">
                <!-- Data loaded via AJAX -->
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          <div id="pagination-siswa-belum-absen" class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2">
            <!-- Pagination loaded here -->
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection


@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
@endsection


@section('page-script')
  <script>
    /* ── LIVE CLOCK ── */
    (function() {
      function updateClock() {
        const el = document.getElementById('live-clock');
        if (el) {
          el.textContent = new Date().toLocaleTimeString('id-ID', {
            hour12: false
          });
        }
      }
      updateClock();
      setInterval(updateClock, 1000);
    })();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {

      /* ── SKELETON LOADING ── */
      setTimeout(() => {
        document.querySelectorAll('[data-skeleton]').forEach(el => {
          el.classList.add('--loaded');
        });
      }, 350);

      /* ── COUNTER ANIMATION (requestAnimationFrame, respects reduced motion) ── */
      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      document.querySelectorAll('.counter-value').forEach(counter => {
        const target = +counter.getAttribute('data-target');
        if (!target || target === 0) { counter.innerText = target || 0; return; }
        if (prefersReducedMotion) { counter.innerText = target; return; }
        animateCounter(counter, target, 1000);
      });

      function animateCounter(el, target, duration = 1000) {
        const start = performance.now();
        function step(now) {
          const elapsed = now - start;
          const progress = Math.min(elapsed / duration, 1);
          el.innerText = Math.floor(progress * target);
          if (progress < 1) requestAnimationFrame(step);
          else el.innerText = target;
        }
        requestAnimationFrame(step);
      }

      /* ── CHART THEME (shared tokens) ── */
      const chartFont = 'Inter, sans-serif';

      /* ── APEX: DONUT ── */
      @php
        $series = [$hadirCount, $sakitCount, $izinCount, $alphaCount, $terlambatCount];
        $labels = ['Hadir', 'Sakit', 'Izin', 'Alpha', 'Terlambat'];
      @endphp
      const donutEl = document.querySelector('#chartDonutStatus');
      let chartDonut;
      if (donutEl) {
        chartDonut = new ApexCharts(donutEl, {
          chart: { type: 'donut', height: 240, background: 'transparent', fontFamily: chartFont },
          theme: { mode: 'dark' },
          series: @json($series),
          labels: @json($labels),
          colors: ['#2FBF71', '#3AB7E0', '#F0A63B', '#EF5A5A', '#8B96AB'],
          legend: { show: false },
          dataLabels: { enabled: false },
          stroke: { show: true, width: 3, colors: ['#121B2E'] },
          plotOptions: {
            pie: {
              donut: {
                size: '78%',
                labels: {
                  show: true,
                  total: {
                    show: true,
                    label: 'Total',
                    color: '#8B96AB',
                    formatter: () => '{{ $totalAbsensiHariIni }}'
                  },
                  value: { color: '#E7ECF5', fontWeight: 700 }
                }
              }
            }
          },
          tooltip: { theme: 'dark', y: { formatter: v => v + ' Siswa' } },
          responsive: [
            { breakpoint: 576, options: { chart: { height: 200 } } },
            { breakpoint: 400, options: { chart: { height: 180 } } }
          ]
        });
        chartDonut.render();
      }

      /* ── APEX: AREA WEEKLY ── */
      const weeklyEl = document.querySelector('#chartKehadiranMingguan');
      let chartWeekly;
      if (weeklyEl) {
        chartWeekly = new ApexCharts(weeklyEl, {
          series: [
            { name: 'Hadir', data: @json($chartHadir) },
            { name: 'Sakit', data: @json($chartSakit) },
            { name: 'Izin', data: @json($chartIzin) },
            { name: 'Alpha', data: @json($chartAlpha) }
          ],
          chart: {
            type: 'area',
            height: 200,
            background: 'transparent',
            fontFamily: chartFont,
            toolbar: { show: false },
            animations: { enabled: !prefersReducedMotion, easing: 'easeinout', speed: 800 }
          },
          theme: { mode: 'dark' },
          stroke: { curve: 'smooth', width: 2.5 },
          fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.04, stops: [0, 90, 100] }
          },
          dataLabels: { enabled: false },
          colors: ['#2FBF71', '#3AB7E0', '#F0A63B', '#EF5A5A'],
          xaxis: {
            categories: @json($chartDays),
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#8B96AB', fontSize: '11px' } }
          },
          yaxis: { labels: { style: { colors: '#8B96AB' } } },
          grid: { borderColor: 'rgba(231,236,245,0.06)', strokeDashArray: 4 },
          legend: { position: 'top', horizontalAlign: 'right', labels: { colors: '#8B96AB' }, markers: { radius: 4 } },
          tooltip: { theme: 'dark', y: { formatter: v => v + ' Siswa' } },
          responsive: [
            { breakpoint: 768, options: { chart: { height: 180 }, legend: { position: 'bottom', horizontalAlign: 'center' } } },
            { breakpoint: 480, options: { chart: { height: 160 } } }
          ]
        });
        chartWeekly.render();
      }

      /* ── REFRESH DASHBOARD ── */
      window.refreshDashboardData = async function() {
        const btn = document.querySelector('.das-icon-btn') || document.querySelector('[onclick="refreshDashboardData()"]');
        if (btn) btn.classList.add('--spinning');
        try {
          const resp = await fetch("{{ route('admin.dashboard.refresh-stats') }}");
          const data = await resp.json();

          document.querySelectorAll('.counter-value').forEach(el => {
            const target = parseInt(el.getAttribute('data-target'));
            animateCounter(el, target, 600);
          });

          if (chartDonut) {
            chartDonut.updateSeries([data.hadirCount, data.sakitCount, data.izinCount, data.alphaCount, data.terlambatCount]);
          }
          if (chartWeekly) {
            chartWeekly.updateSeries([
              { name: 'Hadir', data: data.chartHadir },
              { name: 'Sakit', data: data.chartSakit },
              { name: 'Izin', data: data.chartIzin },
              { name: 'Alpha', data: data.chartAlpha }
            ]);
          }

        } catch (e) {
          console.error('Refresh error:', e);
        } finally {
          if (btn) btn.classList.remove('--spinning');
        }
      };

      /* ── POLLING: auto-refresh every 60s ── */
      setInterval(() => {
        if (typeof refreshDashboardData === 'function') {
          refreshDashboardData();
        }
      }, 60000);

      /* ── AJAX MODAL SISWA BELUM ABSEN ── */
      const modalBelumAbsen = document.getElementById('modalBelumAbsen');
      const searchInput = document.getElementById('search-siswa-belum-absen');
      const tbody = document.getElementById('tbody-siswa-belum-absen');
      const paginationContainer = document.getElementById('pagination-siswa-belum-absen');
      
      let currentPage = 1;
      let searchQuery = '';
      let searchTimeout = null;

      async function fetchSiswaBelumAbsen(page = 1, search = '') {
        // Tampilkan loading skeleton/indicator
        tbody.innerHTML = `
          <tr>
            <td colspan="6" class="text-center py-5">
              <div class="spinner-border text-primary spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <span class="text-body-secondary">Memuat data siswa...</span>
            </td>
          </tr>
        `;
        
        try {
          const url = new URL("{{ route('admin.dashboard.siswa-belum-absen') }}");
          url.searchParams.append('page', page);
          url.searchParams.append('search', search);
          url.searchParams.append('per_page', 5); // 5 data per halaman agar pas di modal

          const response = await fetch(url.toString(), {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          });
          const res = await response.json();

          if (res.success) {
            renderTableData(res.data, res.meta);
            renderPagination(res.meta);
          } else {
            showError('Gagal memuat data.');
          }
        } catch (error) {
          console.error('Fetch error:', error);
          showError('Terjadi kesalahan jaringan.');
        }
      }

      function renderTableData(data, meta) {
        if (data.length === 0) {
          tbody.innerHTML = `
            <tr>
              <td colspan="6" class="text-center text-body-secondary py-5">
                <i class="ti tabler-search-off fs-1 d-block mb-2"></i>
                Tidak ada siswa belum absen yang ditemukan.
              </td>
            </tr>
          `;
          return;
        }

        const startIdx = (meta.current_page - 1) * meta.per_page;
        tbody.innerHTML = data.map((siswa, index) => {
          const waButton = siswa.wa_url && siswa.wa_url !== '#'
            ? `<a href="${siswa.wa_url}" target="_blank" class="btn btn-sm d-inline-flex align-items-center justify-content-center gap-1.5 px-3 py-1.5 text-white" style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); border: none; border-radius: 4px; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.25); font-weight: 600; font-size: 0.75rem; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 15px rgba(37, 211, 102, 0.4)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 10px rgba(37, 211, 102, 0.25)';" onmousedown="this.style.transform='scale(0.98)';">
                 <i class="ti tabler-brand-whatsapp fs-5"></i>
                 <span>WhatsApp</span>
               </a>`
            : `<button class="btn btn-sm d-inline-flex align-items-center justify-content-center gap-1.5 px-3 py-1.5 text-white disabled" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); border-radius: 4px; font-weight: 600; font-size: 0.75rem;" disabled>
                 <i class="ti tabler-brand-whatsapp fs-5 text-body-secondary"></i>
                 <span class="text-body-secondary">WhatsApp</span>
               </button>`;

          return `
            <tr class="align-middle">
              <td class="text-center font-monospace text-body-secondary" style="font-size: 0.8rem;">${startIdx + index + 1}</td>
              <td>
                <div class="d-flex align-items-center">
                  <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(siswa.nama_lengkap)}&background=EF5A5A&color=fff&size=30" class="rounded-circle me-2.5" width="30" height="30" alt="" loading="lazy">
                  <span class="fw-semibold text-white" style="font-size: 0.85rem;">${siswa.nama_lengkap}</span>
                </div>
              </td>
              <td>
                <span class="badge bg-label-primary px-2.5 py-1" style="font-size: 0.72rem; font-weight: 600; border-radius: 4px;">${siswa.kelas}</span>
              </td>
              <td class="text-body-secondary" style="font-size: 0.82rem;">${siswa.wali_kelas}</td>
              <td class="font-monospace text-body-secondary" style="font-size: 0.82rem;">${siswa.no_hp_ortu || '-'}</td>
              <td class="text-center py-2.5">${waButton}</td>
            </tr>
          `;
        }).join('');
      }

      function renderPagination(meta) {
        if (meta.last_page <= 1) {
          paginationContainer.innerHTML = `<span class="text-body-secondary small">Menampilkan ${meta.total} data siswa</span>`;
          return;
        }

        const from = (meta.current_page - 1) * meta.per_page + 1;
        const to = Math.min(meta.current_page * meta.per_page, meta.total);

        let paginationHtml = `<span class="text-body-secondary small">Menampilkan ${from}-${to} dari ${meta.total} data</span>`;
        paginationHtml += `<div class="btn-group gap-1" role="group" aria-label="Navigasi modal">`;

        // Prev Button
        paginationHtml += `
          <button type="button" class="btn btn-sm das-btn--ghost px-2.5 py-1 ${meta.current_page === 1 ? 'disabled' : ''}" 
                  ${meta.current_page === 1 ? 'disabled' : ''} 
                  onclick="changeModalPage(${meta.current_page - 1})">
            <i class="ti tabler-chevron-left fs-5"></i>
          </button>
        `;

        // Page Numbers (max 5 visible)
        let startPage = Math.max(1, meta.current_page - 2);
        let endPage = Math.min(meta.last_page, startPage + 4);
        if (endPage - startPage < 4) {
          startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
          paginationHtml += `
            <button type="button" class="btn btn-sm px-3 py-1 ${meta.current_page === i ? 'btn-primary fw-bold' : 'das-btn--ghost'}" 
                    style="${meta.current_page === i ? 'border-radius: 4px;' : ''}"
                    onclick="changeModalPage(${i})">
              ${i}
            </button>
          `;
        }

        // Next Button
        paginationHtml += `
          <button type="button" class="btn btn-sm das-btn--ghost px-2.5 py-1 ${meta.current_page === meta.last_page ? 'disabled' : ''}" 
                  ${meta.current_page === meta.last_page ? 'disabled' : ''} 
                  onclick="changeModalPage(${meta.current_page + 1})">
            <i class="ti tabler-chevron-right fs-5"></i>
          </button>
        `;

        paginationHtml += `</div>`;
        paginationContainer.innerHTML = paginationHtml;
      }

      function showError(message) {
        tbody.innerHTML = `
          <tr>
            <td colspan="6" class="text-center text-danger py-4">
              <i class="ti tabler-alert-circle fs-2 d-block mb-2"></i>
              ${message}
            </td>
          </tr>
        `;
        paginationContainer.innerHTML = '';
      }

      window.changeModalPage = function(page) {
        currentPage = page;
        fetchSiswaBelumAbsen(currentPage, searchQuery);
      };

      // Event listener: show.bs.modal
      if (modalBelumAbsen) {
        modalBelumAbsen.addEventListener('show.bs.modal', function () {
          currentPage = 1;
          searchQuery = '';
          if (searchInput) searchInput.value = '';
          fetchSiswaBelumAbsen(currentPage, searchQuery);
        });
      }

      // Debounced search input
      if (searchInput) {
        searchInput.addEventListener('input', function (e) {
          clearTimeout(searchTimeout);
          searchQuery = e.target.value;
          currentPage = 1;
          searchTimeout = setTimeout(() => {
            fetchSiswaBelumAbsen(currentPage, searchQuery);
          }, 400); // 400ms debounce
        });
      }
    });
  </script>
@endsection


