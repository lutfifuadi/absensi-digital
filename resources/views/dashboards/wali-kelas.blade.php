@extends('layouts/layoutMaster')

@section('title', 'Dashboard Wali Kelas')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       HERO HEADER — identitas wali kelas + jam live
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__scanline" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner">
      {{-- Identitas --}}
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-users" aria-hidden="true"></i>
          </div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="das-hero__pulse-dot" aria-hidden="true"></span>
            Portal Wali Kelas
          </div>
          <h1 class="das-hero__school">Kelas {{ $has_class ? $kelas_nama : '—' }}</h1>
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

  @if ($has_class)
    {{-- ═══════════════════════════════════════════════════════
         STATS ROW — 4 Card Statistik Dinamis
    ═══════════════════════════════════════════════════════ --}}
    <div class="row g-6 mb-6">
      {{-- Card 1: Total Siswa --}}
      <div class="col-lg-3 col-sm-6">
        <a href="{{ route('wali-kelas.siswa.index') }}" class="text-decoration-none stats-card-link">
          <div class="card card-grad-primary h-100">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <div class="avatar me-4">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="ti tabler-users fs-4"></i>
                  </span>
                </div>
                <h4 class="mb-0 fw-semibold">{{ $total_siswa }}</h4>
              </div>
              <p class="mb-1 text-body-secondary text-nowrap">Total Siswa</p>
              <p class="mb-0">
                <span class="text-primary fw-medium me-2">Kelas {{ $kelas_nama }}</span>
                <small class="text-body-secondary">terdaftar</small>
              </p>
            </div>
          </div>
        </a>
      </div>

      {{-- Card 2: Hadir Hari Ini --}}
      <div class="col-lg-3 col-sm-6">
        <a href="{{ route('wali-kelas.absensi-siswa.index') }}" class="text-decoration-none stats-card-link">
          <div class="card card-grad-success h-100">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <div class="avatar me-4">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="ti tabler-calendar-check fs-4"></i>
                  </span>
                </div>
                <h4 class="mb-0 fw-semibold">{{ $hadir_hari_ini }}</h4>
              </div>
              <p class="mb-1 text-body-secondary text-nowrap">Hadir Hari Ini</p>
              <p class="mb-0">
                <span class="text-success fw-medium me-2">Hadir & Terlambat</span>
                <small class="text-body-secondary">siswa</small>
              </p>
            </div>
          </div>
        </a>
      </div>

      {{-- Card 3: Tidak Hadir --}}
      <div class="col-lg-3 col-sm-6">
        <a href="{{ route('wali-kelas.absensi-siswa.index') }}" class="text-decoration-none stats-card-link">
          <div class="card card-grad-danger h-100">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <div class="avatar me-4">
                  <span class="avatar-initial rounded bg-label-danger">
                    <i class="ti tabler-user-x fs-4"></i>
                  </span>
                </div>
                <h4 class="mb-0 fw-semibold">{{ $tidak_hadir }}</h4>
              </div>
              <p class="mb-1 text-body-secondary text-nowrap">Tidak Hadir</p>
              <p class="mb-0">
                <span class="text-danger fw-medium me-2">Sakit / Izin / Alpha</span>
                <small class="text-body-secondary">hari ini</small>
              </p>
            </div>
          </div>
        </a>
      </div>

      {{-- Card 4: Izin Pending --}}
      <div class="col-lg-3 col-sm-6">
        <a href="{{ route('wali-kelas.absensi-siswa.index') }}" class="text-decoration-none stats-card-link">
          <div class="card card-grad-warning h-100">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <div class="avatar me-4">
                  <span class="avatar-initial rounded bg-label-warning">
                    <i class="ti tabler-clock fs-4"></i>
                  </span>
                </div>
                <h4 class="mb-0 fw-semibold">{{ $pending_izin_kelas }}</h4>
              </div>
              <p class="mb-1 text-body-secondary text-nowrap">Izin Pending</p>
              <p class="mb-0">
                <span class="text-warning fw-medium me-2">Perlu Tindakan</span>
                <small class="text-body-secondary">butuh konfirmasi</small>
              </p>
            </div>
          </div>
        </a>
      </div>
    </div>{{-- /row g-6 mb-6 (Stats Row) --}}

    {{-- ═══════════════════════════════════════════════════════
         QUICK MENU — Tugas & Piket ala Super Admin
    ═══════════════════════════════════════════════════════ --}}
    <div class="row g-6">
      {{-- Card 1: Scanner Piket --}}
      <div class="col-md-4">
        <div class="card card-grad-primary h-100">
          <div class="card-body d-flex flex-column p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ti tabler-qrcode fs-4"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-0">Scanner Piket</h6>
                <small class="text-body-secondary">Rekam kehadiran siswa</small>
              </div>
            </div>
            <p class="text-body-secondary small flex-grow-1">Buka scanner untuk merekam kehadiran siswa di gerbang.</p>
            <a href="{{ route('public.scan-qr.index') }}" target="_blank" class="btn btn-primary">Buka Scanner</a>
          </div>
        </div>
      </div>

      {{-- Card 2: Kegiatan Khusus --}}
      <div class="col-md-4">
        <div class="card card-grad-info h-100">
          <div class="card-body d-flex flex-column p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="ti tabler-calendar-event fs-4"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-0">Kegiatan Khusus</h6>
                <small class="text-body-secondary">Ekskul & ujian</small>
              </div>
            </div>
            <p class="text-body-secondary small flex-grow-1">Scan kehadiran untuk kegiatan ekskul/ujian.</p>
            <a href="{{ route('admin.absensi-kegiatan.scan') }}" class="btn btn-info">Mulai Scan</a>
          </div>
        </div>
      </div>

      {{-- Card 3: Monitoring Kelas --}}
      <div class="col-md-4">
        <div class="card card-grad-success h-100">
          <div class="card-body d-flex flex-column p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="ti tabler-users fs-4"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-0">Monitoring Kelas</h6>
                <small class="text-body-secondary">Data absensi & rekap</small>
              </div>
            </div>
            <p class="text-body-secondary small flex-grow-1">Lihat detail absensi dan rekap siswa kelas Anda.</p>
            <a href="{{ route('wali-kelas.absensi-siswa.index') }}" class="btn btn-success">Buka Data Kelas</a>
          </div>
        </div>
      </div>
    </div>{{-- /row g-6 --}}

  @else
    {{-- ═══════════════════════════════════════════════════════
         EMPTY STATE — Belum punya kelas
    ═══════════════════════════════════════════════════════ --}}
    <div class="row g-6">
      <div class="col-12">
        <div class="card card-grad-warning">
          <div class="card-body text-center py-5">
            <div class="avatar mb-3">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti tabler-door fs-2"></i>
              </span>
            </div>
            <h5 class="mb-2">Belum Ada Kelas Tersedia</h5>
            <p class="text-body-secondary mb-1">Akun Anda belum terdaftar sebagai wali kelas.</p>
            <p class="text-body-secondary mb-0">Silakan hubungi admin sekolah untuk menetapkan kelas bimbingan Anda.</p>
          </div>
        </div>
      </div>
    </div>
  @endif

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
@endsection
