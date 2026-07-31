@extends('layouts/layoutMaster')

@section('title', 'Dashboard Pemantauan & Analitik Guru — ' . ($pengaturanArr['nama_sekolah'] ?? 'Sistem Absensi'))

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.03) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      border-radius: 12px;
      backdrop-filter: blur(10px);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .glass-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25) !important;
      background: rgba(255, 255, 255, 0.05) !important;
    }
    .das-hero__time {
      font-family: monospace;
      font-weight: 700;
      font-size: 1.6rem;
      letter-spacing: 1px;
    }
    .leaderboard-rank {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.9rem;
    }
  </style>
@endsection

@section('content')
  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER — Identitas & Live Clock (Matching Dashboard Utama)
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-6">
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__scanline" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          @if (isset($pengaturanArr['logo_sekolah']))
            <img src="{{ asset('uploads/logo/' . $pengaturanArr['logo_sekolah']) }}" alt="Logo {{ $pengaturanArr['nama_sekolah'] ?? 'sekolah' }}" class="das-hero__logo">
          @else
            <div class="das-hero__logo-placeholder">
              <i class="ti tabler-presentation" aria-hidden="true"></i>
            </div>
          @endif
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="das-hero__pulse-dot" aria-hidden="true"></span>
            Sistem Administrasi Sekolah — Panel Guru
          </div>
          <h1 class="das-hero__school">Dashboard Analitik & Pemantauan Guru</h1>
          <p class="das-hero__welcome">Visualisasi real-time tren presensi, kedisiplinan, & statistik {{ $pengaturanArr['nama_sekolah'] ?? 'sekolah' }} <span aria-hidden="true">📊</span></p>
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

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1B: STATS ROW — 4 Card Statistik (Matching Dashboard Utama)
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    {{-- Card 1: Tingkat Kehadiran Guru --}}
    <div class="col-lg-3 col-sm-6">
      <a href="{{ route('admin.absensi-guru.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-success h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="ti tabler-percentage fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $persentaseHadirToday }}%</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Kehadiran Guru Hari Ini</p>
            <p class="mb-0">
              <span class="text-success fw-medium me-2">{{ $guruHadirToday + $guruTerlambatToday }} Guru</span>
              <small class="text-body-secondary">dari {{ $totalGuru }} guru</small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 2: Guru Terlambat --}}
    <div class="col-lg-3 col-sm-6">
      <a href="{{ route('admin.absensi-guru.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-warning h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="ti tabler-clock-exclamation fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $guruTerlambatToday }}</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Guru Terlambat</p>
            <p class="mb-0">
              <span class="text-warning fw-medium me-2">Evaluasi Kehadiran</span>
              <small class="text-body-secondary">hari ini</small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 3: Guru Izin & Sakit --}}
    <div class="col-lg-3 col-sm-6">
      <a href="{{ route('admin.absensi-guru.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-info h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="ti tabler-stethoscope fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $guruIzinToday }}</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Guru Izin & Sakit</p>
            <p class="mb-0">
              <span class="text-info fw-medium me-2">Keterangan Resmi</span>
              <small class="text-body-secondary">tercatat</small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 4: Best Streak Guru --}}
    <div class="col-lg-3 col-sm-6">
      <a href="{{ route('admin.absensi-guru.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-primary h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ti tabler-trophy fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $bestStreakGuru ? $bestStreakGuru->streak : 0 }} Hari</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Top Streak Disiplin</p>
            <p class="mb-0 text-truncate">
              <span class="text-primary fw-medium me-2">{{ $bestStreakGuru->user->name ?? 'Belum ada data' }}</span>
            </p>
          </div>
        </div>
      </a>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: GRAFIK TREN & DISTRIBUSI PRESENSI GURU
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    {{-- Line Chart: Tren Bulanan Kehadiran Guru --}}
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm glass-card h-100">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2 border-bottom border-white-10 pb-3">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="ti tabler-chart-line fs-4"></i>
                </span>
              </div>
              <div>
                <h5 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
                  Grafik Tren Kehadiran Seluruh Guru
                  <span class="badge bg-label-info font-normal" style="font-size:0.7rem;">Analitik Bulanan</span>
                </h5>
                <small class="text-body-secondary">Visualisasi tren harian status presensi guru (Hadir, Terlambat, Izin, Sakit, Alpha)</small>
              </div>
            </div>

            <form action="{{ route('admin.dashboard.guru') }}" method="GET" class="d-flex gap-2">
              <select name="month" class="form-select form-select-sm bg-dark text-white border-secondary">
                @for($m = 1; $m <= 12; $m++)
                  <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create(2000, $m, 1)->locale('id')->translatedFormat('F') }}
                  </option>
                @endfor
              </select>
              <select name="year" class="form-select form-select-sm bg-dark text-white border-secondary">
                @for($y = now()->year; $y >= now()->year - 2; $y--)
                  <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
              </select>
              <button type="submit" class="btn btn-sm btn-label-info font-semibold">
                <i class="ti tabler-filter me-1"></i> Filter
              </button>
            </form>
          </div>

          <div style="height: 320px; position: relative;">
            <canvas id="chartTrenGuru"></canvas>
          </div>
        </div>
      </div>
    </div>

    {{-- Doughnut Chart: Distribusi Status Presensi Guru --}}
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm glass-card h-100">
        <div class="card-body p-4 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex align-items-center gap-3 mb-3 border-bottom border-white-10 pb-3">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="ti tabler-chart-pie fs-4"></i>
                </span>
              </div>
              <div>
                <h5 class="mb-0 fw-bold text-white">Distribusi Status Presensi</h5>
                <small class="text-body-secondary">Proporsi kehadiran guru bulan ini</small>
              </div>
            </div>

            <div class="d-flex justify-content-center py-2" style="height: 230px; position: relative;">
              <canvas id="chartStatusGuru"></canvas>
            </div>
          </div>

          <div class="pt-3 border-top border-white-10 d-flex justify-content-around text-center">
            <div>
              <div class="text-success fw-bold h6 mb-0">{{ $rekapBulananGuru['hadir'] }}</div>
              <small class="text-body-secondary">Hadir</small>
            </div>
            <div>
              <div class="text-warning fw-bold h6 mb-0">{{ $rekapBulananGuru['terlambat'] }}</div>
              <small class="text-body-secondary">Terlambat</small>
            </div>
            <div>
              <div class="text-info fw-bold h6 mb-0">{{ $rekapBulananGuru['sakit'] + $rekapBulananGuru['izin'] }}</div>
              <small class="text-body-secondary">Izin/Sakit</small>
            </div>
            <div>
              <div class="text-danger fw-bold h6 mb-0">{{ $rekapBulananGuru['alpha'] }}</div>
              <small class="text-body-secondary">Alpha</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: MONITORING TABEL REAL-TIME & LEADERBOARD GURU
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    {{-- Monitoring Presensi Guru Hari Ini --}}
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm glass-card h-100">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2 border-bottom border-white-10 pb-3">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ti tabler-list-check fs-4"></i>
                </span>
              </div>
              <div>
                <h5 class="mb-0 fw-bold text-white">Monitoring Presensi Guru Hari Ini</h5>
                <small class="text-body-secondary">Status jam masuk, jam pulang, dan metode presensi real-time</small>
              </div>
            </div>
            <a href="{{ route('admin.absensi-guru.index') }}" class="btn btn-sm btn-label-primary d-inline-flex align-items-center gap-1 font-semibold">
              <i class="ti tabler-arrow-right"></i> Lihat Rekap Lengkap
            </a>
          </div>

          <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
              <thead>
                <tr style="background: rgba(255,255,255,0.03); font-size:0.75rem; text-transform:uppercase;">
                  <th class="ps-3">Nama Guru</th>
                  <th>Jam Masuk</th>
                  <th>Jam Pulang</th>
                  <th>Metode</th>
                  <th class="pe-3 text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($guruTodayList as $g)
                  @php
                    $absToday = $g->absensiGuru->first();
                  @endphp
                  <tr>
                    <td class="ps-3">
                      <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                          <span class="avatar-initial rounded-circle bg-label-info">
                            {{ strtoupper(substr($g->user->name ?? 'G', 0, 2)) }}
                          </span>
                        </div>
                        <div>
                          <h6 class="mb-0 text-white text-truncate" style="max-width:180px;">{{ $g->user->name ?? '-' }}</h6>
                          <small class="text-body-secondary">{{ $g->jabatan ?? 'Guru Pengajar' }}</small>
                        </div>
                      </div>
                    </td>
                    <td class="text-white fw-medium">
                      {{ $absToday->jam_masuk ?? '-' }}
                    </td>
                    <td class="text-white fw-medium">
                      {{ $absToday->jam_pulang ?? '-' }}
                    </td>
                    <td>
                      <span class="badge bg-label-secondary">
                        <i class="ti tabler-qrcode me-1"></i> {{ ucfirst($absToday->metode ?? 'System/QR') }}
                      </span>
                    </td>
                    <td class="pe-3 text-center">
                      @if($absToday)
                        @if($absToday->status === 'hadir')
                          <span class="badge bg-label-success px-3 py-1">Hadir Tepat Waktu</span>
                        @elseif($absToday->status === 'terlambat')
                          <span class="badge bg-label-warning px-3 py-1">Terlambat</span>
                        @elseif(in_array($absToday->status, ['sakit', 'izin']))
                          <span class="badge bg-label-info px-3 py-1">{{ ucfirst($absToday->status) }}</span>
                        @else
                          <span class="badge bg-label-danger px-3 py-1">Alpha</span>
                        @endif
                      @else
                        <span class="badge bg-label-secondary text-body-secondary px-3 py-1">Belum Presensi</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center py-4 text-body-secondary">Belum ada data guru terdaftar.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- Leaderboard Streak Guru --}}
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm glass-card h-100">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-3 mb-4 border-bottom border-white-10 pb-3">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti tabler-flame fs-4"></i>
              </span>
            </div>
            <div>
              <h5 class="mb-0 fw-bold text-white">Leaderboard Kedisiplinan</h5>
              <small class="text-body-secondary">Top 5 guru paling tepat waktu</small>
            </div>
          </div>

          <div class="d-flex flex-column gap-3">
            @forelse($streakList as $index => $g)
              @php
                $bgRank = match($index) {
                  0 => 'bg-warning text-dark',
                  1 => 'bg-secondary text-white',
                  2 => 'bg-info text-white',
                  default => 'bg-dark text-white-50 border border-secondary'
                };
              @endphp
              <div class="d-flex align-items-center justify-content-between p-3 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07);">
                <div class="d-flex align-items-center gap-3">
                  <div class="leaderboard-rank {{ $bgRank }}">
                    #{{ $index + 1 }}
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold text-white text-truncate" style="max-width: 140px;">{{ $g->user->name ?? '-' }}</h6>
                    <small class="text-body-secondary">{{ $g->jabatan ?? 'Guru' }}</small>
                  </div>
                </div>
                <span class="badge bg-label-primary px-3 py-2 fw-bold">
                  <i class="ti tabler-flame me-1 text-warning"></i> {{ $g->streak }} Hari
                </span>
              </div>
            @empty
              <p class="text-center text-body-secondary mb-0">Belum ada data streak.</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // 1. Live Clock
    function updateClock() {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      const el = document.getElementById('live-clock');
      if (el) el.textContent = `${hours}:${minutes}:${seconds}`;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // 2. Chart Tren Kehadiran Guru (Line Chart)
    const ctxTren = document.getElementById('chartTrenGuru');
    if (ctxTren) {
      new Chart(ctxTren, {
        type: 'line',
        data: {
          labels: {!! json_encode($chartLabels) !!},
          datasets: [
            {
              label: 'Hadir Tepat Waktu',
              data: {!! json_encode($chartHadir) !!},
              borderColor: '#28c76f',
              backgroundColor: 'rgba(40, 199, 111, 0.1)',
              tension: 0.3,
              fill: true
            },
            {
              label: 'Terlambat',
              data: {!! json_encode($chartTerlambat) !!},
              borderColor: '#ff9f43',
              backgroundColor: 'rgba(255, 159, 67, 0.1)',
              tension: 0.3,
              fill: true
            },
            {
              label: 'Izin / Sakit',
              data: {!! json_encode($chartIzin) !!},
              borderColor: '#00cfdd',
              backgroundColor: 'rgba(0, 207, 221, 0.1)',
              tension: 0.3,
              fill: true
            },
            {
              label: 'Alpha',
              data: {!! json_encode($chartAlpha) !!},
              borderColor: '#ea5455',
              backgroundColor: 'rgba(234, 84, 85, 0.1)',
              tension: 0.3,
              fill: true
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              labels: { color: '#a1acb8' }
            }
          },
          scales: {
            x: {
              ticks: { color: '#8b949e' },
              grid: { color: 'rgba(255,255,255,0.05)' }
            },
            y: {
              ticks: { color: '#8b949e', stepSize: 1 },
              grid: { color: 'rgba(255,255,255,0.05)' }
            }
          }
        }
      });
    }

    // 3. Chart Status Presensi Guru (Doughnut Chart)
    const ctxStatus = document.getElementById('chartStatusGuru');
    if (ctxStatus) {
      new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
          labels: ['Hadir Tepat Waktu', 'Terlambat', 'Izin/Sakit', 'Alpha'],
          datasets: [{
            data: [
              {{ $rekapBulananGuru['hadir'] }},
              {{ $rekapBulananGuru['terlambat'] }},
              {{ $rekapBulananGuru['sakit'] + $rekapBulananGuru['izin'] }},
              {{ $rekapBulananGuru['alpha'] }}
            ],
            backgroundColor: ['#28c76f', '#ff9f43', '#00cfdd', '#ea5455'],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          cutout: '75%'
        }
      });
    }
  });
</script>
@endsection
