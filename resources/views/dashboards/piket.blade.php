@extends('layouts/layoutMaster')

@section('title', 'Portal Guru Piket')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <style>
    .form-control, .form-select, .btn {
      border-radius: 6px !important;
    }
    .health-progress-bar {
      height: 6px;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.12);
      overflow: hidden;
    }
    .health-progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
      border-radius: 10px;
      transition: width 0.6s ease;
    }
    .late-student-table th {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: rgba(255, 255, 255, 0.5);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .late-student-table td {
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      padding: 0.75rem 0.5rem;
      vertical-align: middle;
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
          <p class="das-hero__welcome mb-2">Selamat datang, <strong>{{ $user->name }}</strong>. Monitoring presensi harian seluruh sekolah.</p>

          {{-- Health Meter Kehadiran --}}
          <div class="d-flex align-items-center gap-3 mt-2" style="max-width: 320px;">
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-white-50" style="font-size: 0.75rem;">Kehadiran Sekolah Hari Ini</small>
                <span class="fw-bold text-success" style="font-size: 0.8rem;">{{ $tingkatKehadiran }}%</span>
              </div>
              <div class="health-progress-bar">
                <div class="health-progress-fill" style="width: {{ min(100, $tingkatKehadiran) }}%;"></div>
              </div>
            </div>
          </div>
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

  {{-- AKSI CEPAT PIKET (ACTION HUB - 4 CARDS) --}}
  <div class="mb-4">
    <h6 class="text-white-50 small fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">Aksi Utama Guru Piket</h6>
    <div class="row g-3">
      {{-- Card 1: Scanner Gerbang --}}
      <div class="col-6 col-md-3">
        <div class="card card-grad-primary h-100">
          <div class="card-body text-center p-3 d-flex flex-column justify-content-between">
            <div>
              <div class="avatar avatar-md bg-label-primary mx-auto mb-2">
                <span class="avatar-initial rounded"><i class="ti tabler-qrcode fs-3"></i></span>
              </div>
              <h5 class="fw-bold text-white mb-1" style="font-size: 0.9rem;">Scan QR Gerbang</h5>
              <p class="small text-white-50 mb-3" style="font-size: 0.72rem;">Mulai rekam kehadiran gerbang.</p>
            </div>
            <a href="{{ route('public.scan-qr.index') }}" target="_blank" class="das-btn das-btn--primary w-100">Buka Scanner</a>
          </div>
        </div>
      </div>

      {{-- Card 2: Absensi Cepat --}}
      <div class="col-6 col-md-3">
        <div class="card card-grad-info h-100">
          <div class="card-body text-center p-3 d-flex flex-column justify-content-between">
            <div>
              <div class="avatar avatar-md bg-label-info mx-auto mb-2">
                <span class="avatar-initial rounded"><i class="ti tabler-bolt fs-3"></i></span>
              </div>
              <h5 class="fw-bold text-white mb-1" style="font-size: 0.9rem;">Absensi Cepat</h5>
              <p class="small text-white-50 mb-3" style="font-size: 0.72rem;">Input manual per kelas.</p>
            </div>
            <a href="{{ route('piket.absensi-cepat') }}" class="das-btn das-btn--info w-100">Input Absen</a>
          </div>
        </div>
      </div>

      {{-- Card 3: Rekap Pelanggaran --}}
      <div class="col-6 col-md-3">
        <div class="card card-grad-danger h-100">
          <div class="card-body text-center p-3 d-flex flex-column justify-content-between">
            <div>
              <div class="avatar avatar-md bg-label-danger mx-auto mb-2">
                <span class="avatar-initial rounded"><i class="ti tabler-alert-triangle fs-3"></i></span>
              </div>
              <h5 class="fw-bold text-white mb-1" style="font-size: 0.9rem;">Rekap Pelanggaran</h5>
              <p class="small text-white-50 mb-3" style="font-size: 0.72rem;">Laporan harian semua kelas.</p>
            </div>
            <a href="{{ route('piket.rekap-pelanggaran') }}" class="das-btn das-btn--danger w-100">Cek Pelanggaran</a>
          </div>
        </div>
      </div>

      {{-- Card 4: Rekap Absensi Siswa --}}
      <div class="col-6 col-md-3">
        <div class="card card-grad-warning h-100">
          <div class="card-body text-center p-3 d-flex flex-column justify-content-between">
            <div>
              <div class="avatar avatar-md bg-label-warning mx-auto mb-2">
                <span class="avatar-initial rounded"><i class="ti tabler-report-analytics fs-3"></i></span>
              </div>
              <h5 class="fw-bold text-white mb-1" style="font-size: 0.9rem;">Rekap Absensi</h5>
              <p class="small text-white-50 mb-3" style="font-size: 0.72rem;">Rekapitulasi presensi harian.</p>
            </div>
            <a href="{{ route('piket.laporan.index') }}" class="das-btn das-btn--warning w-100">Buka Rekap</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- DUAL COLUMN REAL-TIME WIDGETS --}}
  <div class="row">
    {{-- Widget 1: Tabel Siswa Terlambat Hari Ini --}}
    <div class="col-lg-7 mb-4">
      <div class="das-panel h-100">
        <div class="das-panel__head d-flex justify-content-between align-items-center">
          <div class="das-panel__title">
            <i class="ti tabler-clock-exclamation text-warning fs-4 me-1"></i> Siswa Terlambat Hari Ini
          </div>
          <span class="badge bg-label-warning px-2 py-1" style="font-size: 0.75rem;">{{ count($siswaTerlambatHariIni) }} Terdaftar</span>
        </div>
        <div class="das-panel__body p-0">
          @if($siswaTerlambatHariIni->isEmpty())
            <div class="text-center py-5 text-white-50">
              <i class="ti tabler-circle-check fs-1 text-success mb-2 d-block opacity-75"></i>
              <span class="fw-medium">Tidak ada siswa yang terlambat hari ini!</span>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-borderless late-student-table mb-0 align-middle">
                <thead>
                  <tr>
                    <th class="ps-4">Siswa</th>
                    <th>Kelas</th>
                    <th>Jam Kedatangan</th>
                    <th class="pe-4 text-end">Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($siswaTerlambatHariIni as $absen)
                    <tr>
                      <td class="ps-4 fw-semibold text-white">
                        {{ $absen->siswa->nama_lengkap ?? '-' }}
                      </td>
                      <td>
                        <span class="badge bg-label-secondary" style="font-size: 0.7rem;">
                          {{ $absen->siswa->kelas->nama ?? '-' }}
                        </span>
                      </td>
                      <td class="font-monospace text-warning fw-medium">
                        {{ $absen->jam_masuk ? \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') : '-' }} WIB
                      </td>
                      <td class="pe-4 text-end">
                        <span class="badge bg-label-warning">Terlambat</span>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Widget 2: Log Aktivitas Terbaru --}}
    <div class="col-lg-5 mb-4">
      <div class="das-panel h-100">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <i class="ti tabler-activity text-info fs-4 me-1"></i> Log Aktivitas Scan Terbaru
          </div>
        </div>
        <div class="das-panel__body">
          @if($recentLogs->isEmpty())
            <div class="text-center py-5 text-white-50">
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
                      <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">{{ $log->description }}</h6>
                      <small class="text-white-50" style="font-size: 0.72rem;">{{ $log->created_at->diffForHumans() }}</small>
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
