@extends('layouts/layoutMaster')

@section('title', 'Piket Dashboard')

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
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__scanline" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner">
      {{-- Identitas --}}
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-user-shield text-info" aria-hidden="true"></i>
          </div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="das-hero__pulse-dot" aria-hidden="true"></span>
            Sistem Administrasi / Guru Piket
          </div>
          <h1 class="das-hero__school text-gradient-gold">Pusat Portal Guru Piket</h1>
          <p class="das-hero__welcome">Selamat datang, <strong>{{ $user->name }}</strong>. Berikut adalah ringkasan absensi sekolah hari ini.</p>
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
  </div>

  {{-- STATS GRID --}}
  <div class="row gy-4 mb-4">
    <div class="col-6 col-md-4 col-lg">
      <div class="card card-grad-primary h-100 text-center">
        <div class="card-body">
          <div class="avatar avatar-md mx-auto mb-2">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="ti tabler-users fs-4"></i>
            </span>
          </div>
          <h3 class="mb-0 fw-bold text-white">{{ $totalSiswa }}</h3>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Siswa</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
      <div class="card card-grad-success h-100 text-center">
        <div class="card-body">
          <div class="avatar avatar-md mx-auto mb-2">
            <span class="avatar-initial rounded bg-label-success">
              <i class="ti tabler-user-check fs-4"></i>
            </span>
          </div>
          <h3 class="mb-0 fw-bold text-white">{{ $hadirCount }}</h3>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Hadir</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
      <div class="card card-grad-warning h-100 text-center">
        <div class="card-body">
          <div class="avatar avatar-md mx-auto mb-2">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="ti tabler-clock fs-4"></i>
            </span>
          </div>
          <h3 class="mb-0 fw-bold text-white">{{ $terlambatCount }}</h3>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Terlambat</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
      <div class="card card-grad-info h-100 text-center">
        <div class="card-body">
          <div class="avatar avatar-md mx-auto mb-2">
            <span class="avatar-initial rounded bg-label-info">
              <i class="ti tabler-stethoscope fs-4"></i>
            </span>
          </div>
          <h3 class="mb-0 fw-bold text-white">{{ $sakitCount }}</h3>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Sakit</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
      <div class="card card-grad-info h-100 text-center">
        <div class="card-body">
          <div class="avatar avatar-md mx-auto mb-2">
            <span class="avatar-initial rounded bg-label-info">
              <i class="ti tabler-file-text fs-4"></i>
            </span>
          </div>
          <h3 class="mb-0 fw-bold text-white">{{ $izinCount }}</h3>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Izin</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
      <div class="card card-grad-danger h-100 text-center">
        <div class="card-body">
          <div class="avatar avatar-md mx-auto mb-2">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="ti tabler-alert-circle fs-4"></i>
            </span>
          </div>
          <h3 class="mb-0 fw-bold text-white">{{ $alphaCount }}</h3>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Alpha</small>
        </div>
      </div>
    </div>
  </div>

  {{-- QUICK ACTIONS & LOG --}}
  <div class="row">
    <div class="col-md-6 mb-4">
      <h6 class="text-white-50 small fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">Aksi Cepat Piket</h6>
      <div class="row gy-3">
        <div class="col-6">
          <div class="card card-grad-primary h-100">
            <div class="card-body text-center">
              <div class="avatar avatar-md bg-label-primary mx-auto mb-2">
                <span class="avatar-initial rounded"><i class="ti tabler-qrcode fs-3"></i></span>
              </div>
              <h5 class="fw-bold text-white mb-1" style="font-size: 0.95rem;">Scan QR Absensi</h5>
              <p class="small text-white-50 mb-2" style="font-size: 0.75rem;">Mulai scan QR siswa.</p>
              <a href="{{ route('public.scan-qr.index') }}" target="_blank" class="btn das-btn --primary w-100">Buka Scanner</a>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="card card-grad-info h-100">
            <div class="card-body text-center">
              <div class="avatar avatar-md bg-label-info mx-auto mb-2">
                <span class="avatar-initial rounded"><i class="ti tabler-clipboard-list fs-3"></i></span>
              </div>
              <h5 class="fw-bold text-white mb-1" style="font-size: 0.95rem;">Absensi Cepat</h5>
              <p class="small text-white-50 mb-2" style="font-size: 0.75rem;">Absensi manual per kelas.</p>
              <a href="{{ route('admin.absensi-cepat') }}" class="btn das-btn --info w-100">Input Absen</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 mb-4">
      <h6 class="text-white-50 small fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">Aktivitas Terakhir</h6>
      <div class="das-panel h-100">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <i class="ti tabler-activity text-info fs-4"></i> Log Aktivitas Terbaru
          </div>
        </div>
        <div class="das-panel__body">
          @if($recentLogs->isEmpty())
            <div class="text-center py-4 text-white-50">
              <i class="ti tabler-info-circle fs-2 mb-2 d-block text-muted"></i>
              Belum ada aktivitas scan hari ini.
            </div>
          @else
            <ul class="timeline mb-0">
              @foreach($recentLogs as $log)
                <li class="timeline-item timeline-item-transparent border-left-dashed">
                  <span class="timeline-point timeline-point-primary"></span>
                  <div class="timeline-event">
                    <div class="timeline-header mb-1">
                      <h6 class="mb-0 fw-bold text-white">{{ $log->description }}</h6>
                      <small class="text-white-50">{{ $log->created_at->diffForHumans() }}</small>
                    </div>
                  </div>
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
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
