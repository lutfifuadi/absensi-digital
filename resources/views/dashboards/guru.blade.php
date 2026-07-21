@extends('layouts/layoutMaster')

@section('title', 'Dashboard Guru')

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
    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
      font-size: 1.5rem;
    }

    /* Kalender Styles (adapted from ortu) */
    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 6px;
      text-align: center;
    }
    .calendar-header-day {
      font-weight: 600;
      font-size: 0.75rem;
      color: #8b949e;
      text-transform: uppercase;
      padding-bottom: 8px;
    }
    .calendar-cell {
      aspect-ratio: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      border-radius: 4px;
      font-size: 0.85rem;
      font-weight: 600;
      position: relative;
      cursor: default;
      transition: all 0.15s ease;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .calendar-cell-empty {
      background: transparent !important;
      border: none !important;
    }
    .calendar-cell-today {
      border: 2px solid #7367f0 !important;
    }
    .calendar-cell-holiday {
      background: rgba(234, 84, 85, 0.1) !important;
      color: #ea5455 !important;
    }
    .calendar-cell-hadir {
      background: rgba(40, 199, 111, 0.15) !important;
      color: #28c76f !important;
    }
    .calendar-cell-terlambat {
      background: rgba(255, 159, 67, 0.15) !important;
      color: #ff9f43 !important;
    }
    .calendar-cell-izin {
      background: rgba(115, 103, 240, 0.15) !important;
      color: #7367f0 !important;
    }
    .calendar-cell-sakit {
      background: rgba(0, 207, 221, 0.15) !important;
      color: #00cfdd !important;
    }
    .calendar-cell-alpha {
      background: rgba(234, 84, 85, 0.15) !important;
      color: #ea5455 !important;
    }
    .calendar-legend-item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    .calendar-legend-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
    }

    /* Stat circle for rekap */
    .stat-circle {
      width: 50px;
      height: 50px;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 8px;
      font-weight: 700;
      font-size: 1.1rem;
      transition: transform 0.2s ease;
    }
    .das-panel:hover .stat-circle {
      transform: scale(1.1);
    }
    .stat-circle-success {
      background: rgba(40, 199, 111, 0.12);
      color: #28c76f;
    }
    .stat-circle-warning {
      background: rgba(255, 159, 67, 0.12);
      color: #ff9f43;
    }
    .stat-circle-info {
      background: rgba(0, 207, 221, 0.12);
      color: #00cfdd;
    }
    .stat-circle-danger {
      background: rgba(234, 84, 85, 0.12);
      color: #ea5455;
    }
    .stat-circle-primary {
      background: rgba(115, 103, 240, 0.12);
      color: #7367f0;
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
            <i class="ti tabler-presentation text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Panel Pendidik
          </div>
          <h4 class="das-hero__title text-gradient-gold">Selamat Datang, {{ $user->name }}</h4>
          <p class="das-hero__subtitle">Pantau riwayat kehadiran dan kelola absensi personal Anda.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="das-chip --primary p-2">
          <i class="ti tabler-calendar-stats me-1"></i> {{ now()->locale('id')->translatedFormat('l, d F Y') }}
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: STATS CARDS
  ═══════════════════════════════════════════════════════ --}}
  <div class="row gy-4 mb-5">
    <div class="col-6 col-md-3">
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
    <div class="col-6 col-md-3">
      <div class="das-panel h-100">
        <div class="das-panel__body py-4 text-center">
          <div class="avatar avatar-lg mx-auto mb-3">
            <span class="avatar-initial rounded bg-label-info shadow-sm">
              <i class="ti tabler-notebook fs-3"></i>
            </span>
          </div>
          <h4 class="mb-1 text-white fw-bold">{{ $total_absen_bulan_ini }}</h4>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Total Absen Bulan Ini</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
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
    <div class="col-6 col-md-3">
      <div class="das-panel h-100">
        <div class="das-panel__body py-4 text-center">
          <div class="avatar avatar-lg mx-auto mb-3">
            <span class="avatar-initial rounded bg-label-success shadow-sm">
              <i class="ti tabler-flame fs-3"></i>
            </span>
          </div>
          <h4 class="mb-1 text-white fw-bold">{{ $attendance_streak ?? 0 }} Hari</h4>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Attendance Streak</small>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: KALENDER & RIWAYAT KEHADIRAN
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-5">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-calendar-month text-primary"></i> Kalender & Riwayat Kehadiran
      </h6>
      <form action="{{ route('guru.dashboard') }}" method="GET" class="d-flex gap-2">
        <select name="month" class="form-select form-select-sm bg-dark text-white border-secondary">
          @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2000, $m, 1)->locale('id')->translatedFormat('F') }}</option>
          @endfor
        </select>
        <select name="year" class="form-select form-select-sm bg-dark text-white border-secondary">
          @for($y = now()->year; $y >= now()->year - 2; $y--)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
        <button type="submit" class="btn das-btn --primary btn-sm">Filter</button>
      </form>
    </div>
    <div class="das-panel__body p-4">
      {{-- Rekap Bulanan --}}
      <div class="row text-center mb-4 g-2">
        <div class="col-6 col-md">
          <div class="p-2 rounded" style="background: rgba(40, 199, 111, 0.08); border: 1px solid rgba(40, 199, 111, 0.15);">
            <div class="stat-circle stat-circle-success mx-auto">{{ $rekapBulanan['hadir'] }}</div>
            <small class="text-white-50">Hadir</small>
          </div>
        </div>
        <div class="col-6 col-md">
          <div class="p-2 rounded" style="background: rgba(255, 159, 67, 0.08); border: 1px solid rgba(255, 159, 67, 0.15);">
            <div class="stat-circle stat-circle-warning mx-auto">{{ $rekapBulanan['terlambat'] }}</div>
            <small class="text-white-50">Terlambat</small>
          </div>
        </div>
        <div class="col-6 col-md">
          <div class="p-2 rounded" style="background: rgba(0, 207, 221, 0.08); border: 1px solid rgba(0, 207, 221, 0.15);">
            <div class="stat-circle stat-circle-info mx-auto">{{ $rekapBulanan['sakit'] }}</div>
            <small class="text-white-50">Sakit</small>
          </div>
        </div>
        <div class="col-6 col-md">
          <div class="p-2 rounded" style="background: rgba(115, 103, 240, 0.08); border: 1px solid rgba(115, 103, 240, 0.15);">
            <div class="stat-circle stat-circle-primary mx-auto">{{ $rekapBulanan['izin'] }}</div>
            <small class="text-white-50">Izin</small>
          </div>
        </div>
        <div class="col-12 col-md">
          <div class="p-2 rounded" style="background: rgba(234, 84, 85, 0.08); border: 1px solid rgba(234, 84, 85, 0.15); {{ $rekapBulanan['alpha'] >= 3 ? 'border-danger' : '' }}">
            <div class="stat-circle stat-circle-danger mx-auto">{{ $rekapBulanan['alpha'] }}</div>
            <small class="text-white-50">Alpha</small>
          </div>
        </div>
      </div>

      {{-- Kalender Bulanan --}}
      @php
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
        $firstDayOfWeek = $startOfMonth->dayOfWeek;
        $offset = ($firstDayOfWeek + 6) % 7;
      @endphp

      <div class="calendar-grid mb-3">
        <div class="calendar-header-day">Sen</div>
        <div class="calendar-header-day">Sel</div>
        <div class="calendar-header-day">Rab</div>
        <div class="calendar-header-day">Kam</div>
        <div class="calendar-header-day">Jum</div>
        <div class="calendar-header-day">Sab</div>
        <div class="calendar-header-day">Min</div>

        @for($i = 0; $i < $offset; $i++)
          <div class="calendar-cell calendar-cell-empty"></div>
        @endfor

        @for($day = 1; $day <= $daysInMonth; $day++)
          @php
            $dateStr = \Carbon\Carbon::create($year, $month, $day)->toDateString();
            $abs = $rawAbsensiBulan->get($dateStr);
            $isToday = \Carbon\Carbon::today()->toDateString() == $dateStr;
            $isHoliday = isset($holidays[$dateStr]) || \Carbon\Carbon::create($year, $month, $day)->isSunday();

            $cellClass = '';
            $tooltip = '';

            if ($abs) {
              $cellClass = 'calendar-cell-' . $abs->status;
              $tooltip = strtoupper($abs->status) . ' (' . ($abs->jam_masuk ?? '-') . ')';
            } elseif ($isHoliday) {
              $cellClass = 'calendar-cell-holiday';
              $tooltip = $holidays[$dateStr] ?? 'Libur Akhir Pekan';
            }
          @endphp
          <div class="calendar-cell {{ $cellClass }} {{ $isToday ? 'calendar-cell-today' : '' }}"
               data-bs-toggle="tooltip"
               data-bs-placement="top"
               title="{{ $day }} {{ \Carbon\Carbon::create(2000, $month, 1)->locale('id')->translatedFormat('F') }}: {{ $tooltip ?: 'Tidak ada catatan' }}">
            <span>{{ $day }}</span>
          </div>
        @endfor
      </div>

      {{-- Legenda --}}
      <div class="d-flex flex-wrap justify-content-center gap-3 pt-2 border-top border-secondary">
        <div class="calendar-legend-item text-white-50">
          <div class="calendar-legend-dot" style="background: #28c76f;"></div> Hadir
        </div>
        <div class="calendar-legend-item text-white-50">
          <div class="calendar-legend-dot" style="background: #ff9f43;"></div> Terlambat
        </div>
        <div class="calendar-legend-item text-white-50">
          <div class="calendar-legend-dot" style="background: #00cfdd;"></div> Sakit
        </div>
        <div class="calendar-legend-item text-white-50">
          <div class="calendar-legend-dot" style="background: #7367f0;"></div> Izin
        </div>
        <div class="calendar-legend-item text-white-50">
          <div class="calendar-legend-dot" style="background: #ea5455;"></div> Alpa
        </div>
        <div class="calendar-legend-item text-white-50">
          <div class="calendar-legend-dot" style="background: #ea5455; opacity: 0.5;"></div> Libur
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 4: RIWAYAT ABSENSI TERBARU
  ═══════════════════════════════════════════════════════ --}}
  @php
    $riwayatGuru = \App\Models\AbsensiGuru::whereHas('guru', function($q) use ($user) {
      $q->where('user_id', $user->id);
    })
    ->orderBy('tanggal', 'desc')
    ->orderBy('jam_masuk', 'desc')
    ->limit(5)
    ->get();
  @endphp

  @if($riwayatGuru->isNotEmpty())
  <div class="das-panel mb-5">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-history text-info"></i> Riwayat Absensi Terbaru
      </h6>
      <a href="{{ route('guru.absensi.index') }}" class="btn das-btn --primary btn-sm">
        <i class="ti tabler-eye me-1"></i> Lihat Semua
      </a>
    </div>
    <div class="das-panel__body p-0">
      <div class="table-responsive">
        <table class="table table-dark table-borderless mb-0" style="background: transparent;">
          <thead>
            <tr>
              <th class="text-white-50 fw-bold" style="font-size:0.7rem; letter-spacing:0.5px;">TANGGAL</th>
              <th class="text-white-50 fw-bold" style="font-size:0.7rem; letter-spacing:0.5px;">JAM MASUK</th>
              <th class="text-white-50 fw-bold" style="font-size:0.7rem; letter-spacing:0.5px;">STATUS</th>
              <th class="text-white-50 fw-bold" style="font-size:0.7rem; letter-spacing:0.5px;">METODE</th>
            </tr>
          </thead>
          <tbody>
            @foreach($riwayatGuru as $r)
              @php
                $badgeClass = match($r->status) {
                  'hadir' => 'bg-label-success',
                  'terlambat' => 'bg-label-warning',
                  'sakit' => 'bg-label-info',
                  'izin' => 'bg-label-primary',
                  'alpha' => 'bg-label-danger',
                  default => 'bg-label-secondary'
                };
              @endphp
              <tr>
                <td class="text-white">{{ $r->tanggal->locale('id')->translatedFormat('d M Y') }}</td>
                <td class="text-white">{{ $r->jam_masuk ?? '-' }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ ucfirst($r->status) }}</span></td>
                <td class="text-white-50">{{ $r->metode ? strtoupper($r->metode) : '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════
       SECTION 5: AKSES CEPAT
  ═══════════════════════════════════════════════════════ --}}
  <div class="row gy-4">
    <div class="col-md-6">
      <div class="das-panel h-100">
        <div class="das-panel__body p-4 d-flex align-items-center justify-content-between">
          <div>
            <h6 class="text-white fw-bold mb-1">Absensi Saya</h6>
            <p class="text-white-50 small mb-0">Scan QR atau lihat riwayat absensi.</p>
          </div>
          <a href="{{ route('guru.absensi.scan') }}" class="btn das-btn --success">
            <i class="ti tabler-qrcode me-1"></i> Scan
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="das-panel h-100">
        <div class="das-panel__body p-4 d-flex align-items-center justify-content-between">
          <div>
            <h6 class="text-white fw-bold mb-1">Izin & Sakit</h6>
            <p class="text-white-50 small mb-0">Ajukan izin berhalangan hadir.</p>
          </div>
          <a href="{{ route('guru.izin-sakit.index') }}" class="btn das-btn --info">
            <i class="ti tabler-file-text me-1"></i> Kelola Izin
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection
