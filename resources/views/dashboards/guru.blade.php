@extends('layouts/layoutMaster')

@section('title', 'Dashboard Pemantauan & Analitik Guru — ' . ($pengaturanArr['nama_sekolah'] ?? 'Sistem Absensi'))

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .glass-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25) !important;
      background: rgba(255, 255, 255, 0.06) !important;
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
       SECTION 1: HERO HEADER — Analitik & Monitoring Guru
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          @if (isset($pengaturanArr['logo_sekolah']))
            <img src="{{ asset('uploads/logo/' . $pengaturanArr['logo_sekolah']) }}" alt="Logo Sekolah" class="das-hero__logo">
          @else
            <div class="das-hero__logo-placeholder">
              <i class="ti tabler-chart-line text-info" aria-hidden="true"></i>
            </div>
          @endif
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="das-hero__pulse-dot" aria-hidden="true"></span>
            Dashboard Pemantauan & Analitik Kehadiran Guru
          </div>
          <h1 class="das-hero__school">Analitik Kehadiran & Kinerja Seluruh Guru</h1>
          <p class="das-hero__welcome">
            Pemantauan statistik real-time, tren bulanan, & leaderboard kedisiplinan guru {{ $pengaturanArr['nama_sekolah'] ?? 'sekolah' }}
          </p>
        </div>
      </div>

      {{-- Clock --}}
      <div class="das-hero__clock" role="status">
        <div class="das-hero__date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        <div class="das-hero__time text-gradient-info">
          <span id="live-clock">00:00:00</span>
          <span class="das-hero__live-badge"><span class="das-hero__pulse-dot" aria-hidden="true"></span>LIVE</span>
        </div>
        <div class="das-hero__tz">WAKTU INDONESIA BARAT (WIB)</div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: 4 STAT CARDS KPI AGREGAT SELURUH GURU
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-4 mb-4">
    {{-- Card 1: Tingkat Kehadiran Guru Hari Ini --}}
    <div class="col-lg-3 col-sm-6">
      <div class="card card-grad-success h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti tabler-percentage fs-4"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-0 fw-bold text-white">{{ $persentaseHadirToday }}%</h4>
              <small class="text-white-50">Tingkat Kehadiran Guru</small>
            </div>
          </div>
          <div class="pt-2 border-top border-white-10 d-flex justify-content-between text-white-50 extra-small">
            <span>Guru Hadir: <strong class="text-white">{{ $guruHadirToday + $guruTerlambatToday }}</strong></span>
            <span>Total Guru: <strong class="text-white">{{ $totalGuru }}</strong></span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 2: Guru Terlambat Hari Ini --}}
    <div class="col-lg-3 col-sm-6">
      <div class="card card-grad-warning h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti tabler-clock-exclamation fs-4"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-0 fw-bold text-white">{{ $guruTerlambatToday }} Guru</h4>
              <small class="text-white-50">Guru Terlambat Hari Ini</small>
            </div>
          </div>
          <div class="pt-2 border-top border-white-10 d-flex justify-content-between text-white-50 extra-small">
            <span>Evaluasi: <strong class="text-warning">Butuh Tindakan</strong></span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 3: Guru Izin & Sakit --}}
    <div class="col-lg-3 col-sm-6">
      <div class="card card-grad-info h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-info">
                <i class="ti tabler-stethoscope fs-4"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-0 fw-bold text-white">{{ $guruIzinToday }} Guru</h4>
              <small class="text-white-50">Guru Izin & Sakit Hari Ini</small>
            </div>
          </div>
          <div class="pt-2 border-top border-white-10 d-flex justify-content-between text-white-50 extra-small">
            <span>Tercatat Resmi: <strong class="text-info">{{ $guruIzinToday }} Guru</strong></span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 4: Best Streak Guru --}}
    <div class="col-lg-3 col-sm-6">
      <div class="card card-grad-primary h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti tabler-trophy fs-4"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-0 fw-bold text-white">{{ $bestStreakGuru ? $bestStreakGuru->streak : 0 }} Hari</h4>
              <small class="text-white-50">Top Streak Kehadiran Guru</small>
            </div>
          </div>
          <div class="pt-2 border-top border-white-10 text-truncate text-white-50 extra-small">
            <span>Pendidik: <strong class="text-white">{{ $bestStreakGuru->user->name ?? 'Belum ada data' }}</strong></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: VISUALISASI GRAFIK INTERAKTIF (CHART.JS)
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-4 mb-4">
    {{-- Left: Grafik Tren Kehadiran Bulanan --}}
    <div class="col-lg-8">
      <div class="das-panel h-100">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
          <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2 text-white">
            <i class="ti tabler-chart-line text-info fs-4"></i> Grafik Tren Kehadiran Seluruh Guru
          </h6>
          <form action="{{ route('guru.dashboard') }}" method="GET" class="d-flex gap-2">
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
            <button type="submit" class="btn das-btn --info btn-sm">Filter</button>
          </form>
        </div>
        <div class="das-panel__body p-4">
          <canvas id="chartTrenGuru" style="max-height: 320px; width: 100%;"></canvas>
        </div>
      </div>
    </div>

    {{-- Right: Doughnut Chart Status Presensi Guru --}}
    <div class="col-lg-4">
      <div class="das-panel h-100">
        <div class="das-panel__header border-bottom py-3 px-4">
          <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2 text-white">
            <i class="ti tabler-chart-pie text-warning fs-4"></i> Distribusi Status Presensi
          </h6>
        </div>
        <div class="das-panel__body p-4 d-flex flex-column align-items-center justify-content-center">
          <canvas id="chartStatusGuru" style="max-height: 250px; max-width: 250px;"></canvas>
          
          <div class="w-100 mt-4 pt-3 border-top border-white-10 d-flex justify-content-around text-center extra-small">
            <div>
              <div class="text-success fw-bold h6 mb-0">{{ $rekapBulananGuru['hadir'] }}</div>
              <span class="text-white-50">Hadir</span>
            </div>
            <div>
              <div class="text-warning fw-bold h6 mb-0">{{ $rekapBulananGuru['terlambat'] }}</div>
              <span class="text-white-50">Terlambat</span>
            </div>
            <div>
              <div class="text-info fw-bold h6 mb-0">{{ $rekapBulananGuru['sakit'] + $rekapBulananGuru['izin'] }}</div>
              <span class="text-white-50">Izin/Sakit</span>
            </div>
            <div>
              <div class="text-danger fw-bold h6 mb-0">{{ $rekapBulananGuru['alpha'] }}</div>
              <span class="text-white-50">Alpha</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 4: MONITORING REAL-TIME PRESENSI & LEADERBOARD GURU
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-4 mb-4">
    {{-- Tabel Monitoring Presensi Guru Hari Ini --}}
    <div class="col-lg-8">
      <div class="das-panel h-100">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
          <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2 text-white">
            <i class="ti tabler-users-group text-primary fs-4"></i> Monitoring Presensi Guru Hari Ini
          </h6>
          <a href="{{ route('admin.absensi-guru.index') }}" class="btn das-btn --primary btn-sm">
            Lihat Rekap Selengkapnya
          </a>
        </div>
        <div class="das-panel__body p-0">
          <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
              <thead>
                <tr style="background: rgba(255,255,255,0.03); font-size:0.75rem; text-transform:uppercase;">
                  <th class="ps-4">Nama Guru</th>
                  <th>Jam Masuk</th>
                  <th>Jam Pulang</th>
                  <th>Metode Presensi</th>
                  <th class="pe-4 text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($guruTodayList as $g)
                  @php
                    $absToday = $g->absensiGuru->first();
                  @endphp
                  <tr>
                    <td class="ps-4">
                      <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                          <span class="avatar-initial rounded-circle bg-label-info">
                            {{ strtoupper(substr($g->user->name ?? 'G', 0, 2)) }}
                          </span>
                        </div>
                        <div>
                          <h6 class="mb-0 text-white text-truncate" style="max-width:200px;">{{ $g->user->name ?? '-' }}</h6>
                          <small class="text-white-50">{{ $g->jabatan ?? 'Guru Pengajar' }}</small>
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
                        <i class="ti tabler-qrcode me-1"></i> {{ ucfirst($absToday->metode ?? 'QR/System') }}
                      </span>
                    </td>
                    <td class="pe-4 text-center">
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
                        <span class="badge bg-label-secondary text-white-50 px-3 py-1">Belum Presensi</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center py-4 text-white-50">Belum ada data guru terdaftar.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- Leaderboard Kedisiplinan Guru --}}
    <div class="col-lg-4">
      <div class="das-panel h-100">
        <div class="das-panel__header border-bottom py-3 px-4">
          <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2 text-white">
            <i class="ti tabler-trophy text-warning fs-4"></i> Leaderboard Streak Guru
          </h6>
        </div>
        <div class="das-panel__body p-4">
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
              <div class="d-flex align-items-center justify-content-between p-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                <div class="d-flex align-items-center gap-3">
                  <div class="leaderboard-rank {{ $bgRank }}">
                    #{{ $index + 1 }}
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold text-white text-truncate" style="max-width: 150px;">{{ $g->user->name ?? '-' }}</h6>
                    <small class="text-white-50">{{ $g->jabatan ?? 'Guru' }}</small>
                  </div>
                </div>
                <div class="text-end">
                  <span class="badge bg-label-primary px-3 py-2 fw-bold">
                    <i class="ti tabler-flame me-1 text-warning"></i> {{ $g->streak }} Hari
                  </span>
                </div>
              </div>
            @empty
              <p class="text-center text-white-50 mb-0">Belum ada data streak.</p>
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
