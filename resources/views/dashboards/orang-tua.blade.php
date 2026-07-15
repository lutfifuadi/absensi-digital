@extends('layouts/layoutMaster')

@section('title', 'Portal Orang Tua')

@section('page-style')
<style>
    /* Premium Cards & Gradients */
    .premium-card {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
        border-radius: 5px !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04), 0 2px 6px -1px rgba(0, 0, 0, 0.02) !important;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px -4px rgba(115, 103, 240, 0.12), 0 4px 12px -2px rgba(115, 103, 240, 0.04) !important;
    }
    
    /* Dark Mode Adjustments */
    [data-bs-theme="dark"] .premium-card {
        background: rgba(30, 41, 59, 0.45) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        box-shadow: 0 4px 24px 0 rgba(0, 0, 0, 0.3) !important;
    }
    [data-bs-theme="dark"] .premium-card:hover {
        background: rgba(30, 41, 59, 0.6) !important;
        box-shadow: 0 12px 30px -4px rgba(115, 103, 240, 0.2), 0 4px 14px -2px rgba(115, 103, 240, 0.08) !important;
    }

    /* Welcome Card Styling */
    .welcome-card {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #7367f0 0%, #4338ca 100%);
        border-radius: 5px;
        color: #ffffff;
    }
    .welcome-card::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
        top: -100px;
        right: -100px;
        border-radius: 50%;
        pointer-events: none;
    }

    /* Stat circle values */
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
    .premium-card:hover .stat-circle {
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

    .badge-dynamic {
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: 600;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
    }

    /* Pulse animation for waiting status */
    .pulse-amber {
        box-shadow: 0 0 0 0 rgba(255, 159, 67, 0.4);
        animation: pulse-amber 1.8s infinite;
    }
    @keyframes pulse-amber {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(255, 159, 67, 0.4);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 8px rgba(255, 159, 67, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(255, 159, 67, 0);
        }
    }

    /* Quick Actions panel */
    .quick-action-btn {
        border-radius: 5px;
        padding: 12px 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
    }
    .quick-action-btn:hover {
        transform: translateY(-2px);
    }

    /* Kalender Minimalis */
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
        background: #f8f9fa;
        border: 1px solid #f1f2f4;
    }
    [data-bs-theme="dark"] .calendar-cell {
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

    /* Profile Switcher style */
    .profile-switch-btn {
        transition: all 0.2s ease;
        border: 2px solid transparent;
        padding: 4px;
        border-radius: 5px;
    }
    .profile-switch-btn.active {
        border-color: #7367f0;
        transform: scale(1.08);
    }
    .profile-switch-btn:hover {
        transform: scale(1.08);
    }
</style>
@endsection

@section('content')
  <!-- Welcome Banner -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card welcome-card border-0 shadow-sm">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 position-relative" style="z-index: 1;">
          <div>
            <h4 class="card-title mb-1 text-white fw-bold">Halo, {{ $user->name }}! 👋</h4>
            <p class="card-text mb-0 opacity-85">Pantau kehadiran, status izin, dan perkembangan akademik putra-putri Anda secara real-time.</p>
          </div>
          <div class="text-md-end">
            <span class="badge bg-white text-primary px-3 py-2 fw-bold" style="border-radius: 4px; font-size: 0.8rem; box-shadow: 0 4px 10px rgba(0,0,0,0.08);">
              Wali Murid Dashboard
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if($anakList->isEmpty())
    <div class="row">
      <div class="col-12">
        <div class="card premium-card border-warning border-2">
          <div class="card-body text-center py-5">
            <div class="avatar bg-label-warning p-3 rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
              <i class="ti tabler-alert-circle text-warning fs-1"></i>
            </div>
            <h5 class="fw-bold">Data Anak Belum Ditautkan</h5>
            <p class="text-muted max-w-md mx-auto">Kami belum menemukan data siswa yang terhubung dengan akun Anda. Silakan hubungi operator sekolah untuk melakukan sinkronisasi data wali murid.</p>
          </div>
        </div>
      </div>
    </div>
  @else
    
    <!-- Profile Switcher & Selector (Jika Multi-Anak) -->
    @if($anakList->count() > 1)
      <div class="card premium-card mb-4" style="background: linear-gradient(135deg, rgba(0, 207, 221, 0.05) 0%, rgba(115, 103, 240, 0.02) 100%); border-left: 4px solid #00cfdd !important;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
              <i class="ti tabler-arrows-left-right text-primary fs-4"></i>
              <div>
                <h6 class="mb-0 fw-bold">Pilih Anak Aktif</h6>
                <small class="text-muted">Ganti profil untuk melihat rekap kehadiran</small>
            </div>
            <div class="d-flex align-items-center gap-3">
              @foreach($anakList as $child)
                @php
                  $childAvatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($child->nama_lengkap) . '&size=100&background=7367f0&color=fff';
                  if ($child->foto) {
                      if (strlen($child->foto) > 30) {
                          $childAvatarUrl = 'https://drive.google.com/thumbnail?id=' . $child->foto . '&sz=w200&_t=' . time();
                      } else {
                          $childAvatarUrl = asset('storage/' . $child->foto);
                      }
                  }
                @endphp
                <form action="{{ route('ortu.switch-anak') }}" method="POST" class="d-inline">
                  @csrf
                  <input type="hidden" name="siswa_id" value="{{ $child->id }}">
                  <button type="submit" class="btn p-0 profile-switch-btn {{ $activeAnak->id == $child->id ? 'active' : '' }}" title="{{ $child->nama_lengkap }}">
                    <img src="{{ $childAvatarUrl }}" alt="{{ $child->nama_lengkap }}" style="border-radius: 5px; width: 48px; height: 48px; object-fit: cover;">
                  </button>
                </form>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    @endif

    <div class="row g-4">
      <!-- Info & Ringkasan Kehadiran Hari Ini -->
      <div class="col-md-6 col-xl-4">
        @php
          $cardBg = 'background: #ffffff; border: 1px solid rgba(0, 0, 0, 0.05) !important;';
          $statusBorderColor = '#7367f0';
          if ($absensiHariIni) {
              $statusBorderColor = match($absensiHariIni->status) {
                  'hadir' => '#28c76f',
                  'terlambat' => '#ff9f43',
                  'sakit' => '#00cfdd',
                  'izin' => '#7367f0',
                  'alpha' => '#ea5455',
                  default => '#7367f0'
              };
          } else {
              $statusBorderColor = '#ff9f43';
          }
          
          $cardBg = "background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.98) 100%); border-left: 4px solid {$statusBorderColor} !important;";
          
          $statusBoxStyle = match($absensiHariIni ? $absensiHariIni->status : 'belum_absen') {
              'hadir' => 'background: linear-gradient(135deg, rgba(40, 199, 111, 0.12) 0%, rgba(40, 199, 111, 0.03) 100%); border: 1px solid rgba(40, 199, 111, 0.2) !important;',
              'terlambat' => 'background: linear-gradient(135deg, rgba(255, 159, 67, 0.12) 0%, rgba(255, 159, 67, 0.03) 100%); border: 1px solid rgba(255, 159, 67, 0.2) !important;',
              'sakit' => 'background: linear-gradient(135deg, rgba(0, 207, 221, 0.12) 0%, rgba(0, 207, 221, 0.03) 100%); border: 1px solid rgba(0, 207, 221, 0.2) !important;',
              'izin' => 'background: linear-gradient(135deg, rgba(115, 103, 240, 0.12) 0%, rgba(115, 103, 240, 0.03) 100%); border: 1px solid rgba(115, 103, 240, 0.2) !important;',
              'alpha' => 'background: linear-gradient(135deg, rgba(234, 84, 85, 0.12) 0%, rgba(234, 84, 85, 0.03) 100%); border: 1px solid rgba(234, 84, 85, 0.2) !important;',
              default => 'background: linear-gradient(135deg, rgba(255, 159, 67, 0.12) 0%, rgba(255, 159, 67, 0.03) 100%); border: 1px solid rgba(255, 159, 67, 0.2) !important;'
          };
        @endphp
        <div class="card h-100 premium-card" style="{{ $cardBg }}">
          <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
            <div class="d-flex align-items-center gap-2">
              @php
                $activeAvatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($activeAnak->nama_lengkap) . '&size=100&background=7367f0&color=fff';
                if ($activeAnak->foto) {
                    if (strlen($activeAnak->foto) > 30) {
                        $activeAvatarUrl = 'https://drive.google.com/thumbnail?id=' . $activeAnak->foto . '&sz=w200&_t=' . time();
                    } else {
                        $activeAvatarUrl = asset('storage/' . $activeAnak->foto);
                    }
                }
              @endphp
              <img src="{{ $activeAvatarUrl }}" alt="{{ $activeAnak->nama_lengkap }}" style="border-radius: 5px; width: 42px; height: 42px; object-fit: cover;">
              <div>
                <h5 class="mb-0 fw-bold text-truncate" style="max-width: 160px;" title="{{ $activeAnak->nama_lengkap }}">
                  {{ $activeAnak->nama_lengkap }}
                </h5>
                <span class="badge bg-label-primary font-monospace" style="font-size: 0.65rem;">NISN: {{ $activeAnak->nisn }}</span>
              </div>
            </div>
            <div>
              <span class="badge bg-label-secondary font-semibold" style="font-size: 0.75rem;">
                Kelas {{ $activeAnak->kelas->nama ?? 'Tidak Ada Kelas' }}
              </span>
            </div>
          </div>
          
          <div class="card-body pt-4">
            <h6 class="mb-3 fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: 0.5px; text-transform: uppercase;">
              Status Presensi Hari Ini
            </h6>
            
            <div class="text-center py-4 mb-4 position-relative overflow-hidden" style="border-radius: 5px !important; {{ $statusBoxStyle }}">
              @if($absensiHariIni)
                @php
                  $statusLabel = match($absensiHariIni->status) {
                      'hadir' => 'Hadir',
                      'terlambat' => 'Terlambat',
                      'sakit' => 'Sakit',
                      'izin' => 'Izin',
                      'alpha' => 'Alpa',
                      default => 'Belum Absen'
                  };
                  $statusColor = match($absensiHariIni->status) {
                      'hadir' => 'text-success',
                      'terlambat' => 'text-warning',
                      'sakit' => 'text-info',
                      'izin' => 'text-primary',
                      'alpha' => 'text-danger',
                      default => 'text-muted'
                  };
                  $statusBgSolid = match($absensiHariIni->status) {
                      'hadir' => 'background: #28c76f; color: #ffffff;',
                      'terlambat' => 'background: #ff9f43; color: #ffffff;',
                      'sakit' => 'background: #00cfdd; color: #ffffff;',
                      'izin' => 'background: #7367f0; color: #ffffff;',
                      'alpha' => 'background: #ea5455; color: #ffffff;',
                      default => 'background: #a8aaae; color: #ffffff;'
                  };
                @endphp
                <div class="d-inline-block p-3 mb-2 shadow-sm" style="border-radius: 5px !important; {{ $statusBgSolid }}">
                  <i class="ti {{ $absensiHariIni->status == 'hadir' || $absensiHariIni->status == 'terlambat' ? 'tabler-check' : 'tabler-clock' }} fs-2"></i>
                </div>
                <h3 class="fw-bold {{ $statusColor }} mb-1">{{ $statusLabel }}</h3>
                <p class="text-muted small mb-0">Tercatat Jam: <span class="fw-bold text-dark dark-text-light">{{ $absensiHariIni->jam_masuk ?? '-' }}</span></p>
                @if($absensiHariIni->metode)
                  <span class="badge bg-label-dark mt-2" style="border-radius: 4px; font-size: 0.65rem;">Scan: {{ strtoupper($absensiHariIni->metode) }}</span>
                @endif
              @else
                <div class="d-inline-block p-3 bg-warning text-white pulse-amber mb-2" style="border-radius: 5px !important;">
                  <i class="ti tabler-loader fs-2 animate-spin"></i>
                </div>
                <h3 class="fw-bold text-warning mb-1">Belum Absen</h3>
                <p class="text-muted small mb-0">Menunggu kehadiran siswa di sekolah...</p>
              @endif
            </div>

            <div class="d-flex flex-column gap-2">
              <a href="{{ route('ortu.izin-sakit.create') }}" class="btn btn-primary py-2 w-full" style="border-radius: 5px; font-weight: 600;">
                <i class="ti tabler-file-text me-1 fs-5"></i> Ajukan Izin / Sakit
              </a>
              <a href="{{ route('ortu.anak.profil', $activeAnak->id) }}" class="btn btn-outline-secondary py-2 w-full" style="border-radius: 5px; font-weight: 600;">
                <i class="ti tabler-user-circle me-1 fs-5"></i> Lihat Detail Profil Anak
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Ringkasan Performa Bulanan & Kalender Widget -->
      <div class="col-md-6 col-xl-8">
        <div class="card h-100 premium-card" style="background: linear-gradient(135deg, rgba(115, 103, 240, 0.03) 0%, rgba(30, 41, 59, 0.01) 100%); border-top: 4px solid #7367f0 !important;">
          <div class="card-header border-bottom pb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <h5 class="mb-0 fw-bold">Kalender & Riwayat Kehadiran</h5>
                <small class="text-muted">Performa bulanan anak aktif</small>
              </div>
              <form action="{{ route('ortu.dashboard') }}" method="GET" class="d-flex gap-2">
                <select name="month" class="form-select form-select-sm">
                  @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2000, $m, 1)->locale('id')->translatedFormat('F') }}</option>
                  @endfor
                </select>
                <select name="year" class="form-select form-select-sm">
                  @for($y=now()->year; $y>=now()->year-2; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                  @endfor
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
              </form>
            </div>
          </div>

          <div class="card-body pt-4">
            <!-- Counter Bulanan -->
            <div class="row text-center mb-4 g-2">
              <div class="col-6 col-md-2">
                <div class="p-2 border rounded bg-light dark-bg-dark">
                  <h4 class="fw-bold text-success mb-1">{{ $rekapBulanan['hadir'] }}</h4>
                  <small class="text-muted">Hadir</small>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="p-2 border rounded bg-light dark-bg-dark">
                  <h4 class="fw-bold text-warning mb-1">{{ $rekapBulanan['terlambat'] }}</h4>
                  <small class="text-muted">Terlambat</small>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="p-2 border rounded bg-light dark-bg-dark">
                  <h4 class="fw-bold text-info mb-1">{{ $rekapBulanan['sakit'] }}</h4>
                  <small class="text-muted">Sakit</small>
                </div>
              </div>
              <div class="col-6 col-md-2">
                <div class="p-2 border rounded bg-light dark-bg-dark">
                  <h4 class="fw-bold text-primary mb-1">{{ $rekapBulanan['izin'] }}</h4>
                  <small class="text-muted">Izin</small>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-2 border rounded bg-light dark-bg-dark {{ $rekapBulanan['alpha'] >= 3 ? 'border-danger bg-label-danger' : '' }}">
                  <h4 class="fw-bold text-danger mb-1">{{ $rekapBulanan['alpha'] }}</h4>
                  <small class="text-muted">Tanpa Keterangan (Alpha)</small>
                </div>
              </div>
            </div>

            <!-- Kalender Bulanan -->
            @php
              $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
              $daysInMonth = $startOfMonth->daysInMonth;
              $firstDayOfWeek = $startOfMonth->dayOfWeek; // 0=Sunday, 1=Monday
              // Adjust so Monday is first (0=Mon, 1=Tue, ..., 6=Sun)
              $offset = ($firstDayOfWeek + 6) % 7;
            @endphp

            <div class="calendar-grid mb-3">
              <!-- Header Hari -->
              <div class="calendar-header-day">Sen</div>
              <div class="calendar-header-day">Sel</div>
              <div class="calendar-header-day">Rab</div>
              <div class="calendar-header-day">Kam</div>
              <div class="calendar-header-day">Jum</div>
              <div class="calendar-header-day">Sab</div>
              <div class="calendar-header-day">Min</div>

              <!-- Offset Cells -->
              @for($i = 0; $i < $offset; $i++)
                <div class="calendar-cell calendar-cell-empty"></div>
              @endfor

              <!-- Day Cells -->
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
                  } elseif (\Carbon\Carbon::create($year, $month, $day)->isPast()) {
                      // default alpha if past and no record and not holiday (simplified)
                      // $cellClass = 'calendar-cell-alpha';
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

            <!-- Legenda Kalender -->
            <div class="d-flex flex-wrap justify-content-center gap-3 pt-2 border-top">
              <div class="calendar-legend-item">
                <div class="calendar-legend-dot bg-success"></div> Hadir
              </div>
              <div class="calendar-legend-item">
                <div class="calendar-legend-dot bg-warning"></div> Terlambat
              </div>
              <div class="calendar-legend-item">
                <div class="calendar-legend-dot bg-info"></div> Sakit
              </div>
              <div class="calendar-legend-item">
                <div class="calendar-legend-dot bg-primary"></div> Izin
              </div>
              <div class="calendar-legend-item">
                <div class="calendar-legend-dot bg-danger"></div> Alpa
              </div>
              <div class="calendar-legend-item">
                <div class="calendar-legend-dot bg-danger opacity-50"></div> Libur
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- WhatsApp Integration Banner -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card premium-card" style="background: linear-gradient(135deg, rgba(37, 211, 102, 0.05) 0%, rgba(18, 140, 126, 0.02) 100%); border-left: 5px solid #25d366 !important;">
        <div class="card-body p-4">
          <div class="d-flex gap-3 align-items-center flex-column flex-sm-row">
            <div class="avatar bg-label-success p-3 rounded" style="background: rgba(37, 211, 102, 0.15) !important; border-radius: 5px !important; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
              <i class="ti tabler-brand-whatsapp text-success fs-1"></i>
            </div>
            <div>
              <h5 class="mb-1 text-success fw-bold">Integrasi Notifikasi WhatsApp</h5>
              <p class="mb-0 text-muted small" style="line-height: 1.5;">
                Segera aktif! Anda akan menerima pesan notifikasi instan langsung ke nomor WhatsApp Anda setiap kali putra-putri Anda melakukan pemindaian (scan) absensi masuk atau pulang sekolah secara real-time.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
