@extends('layouts/layoutMaster')

@section('title', 'Dashboard Utama — ' . ($pengaturanArr['nama_sekolah'] ?? 'Sistem Absensi'))

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER — identitas sekolah + jam live
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero das-hero--with-stats mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      {{-- Identitas --}}
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          @if (isset($pengaturanArr['logo_sekolah']))
            <img src="{{ asset('storage/' . $pengaturanArr['logo_sekolah']) }}" alt="Logo" class="das-hero__logo">
          @else
            <div class="das-hero__logo-placeholder">
              <i class="ti tabler-school"></i>
            </div>
          @endif
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Sistem Administrasi Sekolah
          </div>
          <h4 class="das-hero__school text-gradient-gold">{{ $pengaturanArr['nama_sekolah'] ?? $pengaturanArr['nama_lembaga'] ?? 'Sistem Absensi' }}</h4>
          <p class="das-hero__welcome">Selamat datang kembali, <strong>{{ $user->name }}</strong> 👋</p>
        </div>
      </div>

      {{-- Clock --}}
      <div class="das-hero__clock glass-card">
        <div class="das-hero__date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        <div class="das-hero__time">
          <span id="live-clock">00:00:00</span>
          <div class="das-hero__status-indicator">
            <span class="das-hero__live-badge">LIVE</span>
          </div>
        </div>
        <div class="das-hero__tz">WAKTU INDONESIA BARAT (WIB)</div>
      </div>
    </div>

    {{-- ── CORE STATS (mengambang di bawah hero) ── --}}
    <div class="das-stats-row">
      @php
        $coreStats = [
            [
                'label' => 'Total Siswa',
                'val' => $totalSiswa,
                'icon' => 'tabler-users',
                'color' => 'primary',
                'link' => route('admin.siswa.index'),
                'desc' => 'Tercatat aktif',
            ],
            [
                'label' => 'Total Guru',
                'val' => $totalGuru,
                'icon' => 'tabler-chalkboard-teacher',
                'color' => 'success',
                'link' => route('admin.guru.index'),
                'desc' => 'Pendidik',
            ],
            [
                'label' => 'Staff TU',
                'val' => $totalStaff,
                'icon' => 'tabler-user-check',
                'color' => 'info',
                'link' => route('admin.staff-tata-usaha.index'),
                'desc' => 'Administrasi',
            ],
            [
                'label' => 'Total Kelas',
                'val' => $totalKelas,
                'icon' => 'tabler-door',
                'color' => 'warning',
                'link' => route('admin.kelas.index'),
                'desc' => 'Rombel',
            ],
        ];
      @endphp
      @foreach ($coreStats as $item)
        <a href="{{ $item['link'] }}"
          class="das-stat-card das-stat-card--{{ $item['color'] }} bounce-in text-decoration-none">
          <div class="das-stat-card__icon">
            <i class="ti {{ $item['icon'] }}"></i>
          </div>
          <div class="das-stat-card__body">
            <div class="das-stat-card__val counter-value" data-target="{{ $item['val'] }}">0</div>
            <div class="das-stat-card__label">{{ $item['label'] }}</div>
          </div>
          <div class="das-stat-card__side-info">
            <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
          </div>
        </a>
      @endforeach
    </div>
  </div>{{-- /das-hero --}}



  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: MAIN GRID — kiri (monitoring) | kanan (tools)
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-main-grid">

    {{-- ─────────────────────────────
         KOLOM KIRI
    ───────────────────────────── --}}
    <div class="das-col-left">

      {{-- TREN 7 HARI --}}
      <div class="das-panel mb-4">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot --primary"></span>
            Tren Kehadiran 7 Hari Terakhir
          </div>
          <a href="{{ route('admin.laporan.index') }}" class="das-btn das-btn--ghost">
            Detail <i class="ti tabler-arrow-right"></i>
          </a>
        </div>
        <div class="das-panel__body">
          <div id="chartKehadiranMingguan" style="min-height:300px;"></div>
        </div>
      </div>

      {{-- RINGKASAN HARI INI --}}
      <div class="das-panel mb-4">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot --success"></span>
            Ringkasan Absensi Hari Ini
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="das-chip --info">{{ $hadirCount + $terlambatCount }}/{{ $totalSiswa }} Hadir</span>
            <span class="das-chip --primary">{{ now()->translatedFormat('d F Y') }}</span>
          </div>
        </div>
        <div class="das-panel__body">
          <div class="das-today-grid">
            {{-- Donut --}}
            <div class="das-today-donut">
              @if ($totalAbsensiHariIni > 0 || $totalSiswa > 0)
                <div id="chartDonutStatus" style="min-height:240px;"></div>
              @else
                <div class="das-empty-state">
                  <i class="ti tabler-chart-pie"></i>
                  <span>Belum ada data</span>
                </div>
              @endif
            </div>

            {{-- Status Grid --}}
            <div class="das-status-grid">
              @php
                $statuses = [
                    [
                        'label' => 'Hadir',
                        'val' => $hadirCount,
                        'color' => 'success',
                        'icon' => 'tabler-circle-check',
                        'desc' => 'Tepat waktu',
                    ],
                    [
                        'label' => 'Sakit',
                        'val' => $sakitCount,
                        'color' => 'info',
                        'icon' => 'tabler-heart-rate-monitor',
                        'desc' => 'Izin medis',
                    ],
                    [
                        'label' => 'Izin',
                        'val' => $izinCount,
                        'color' => 'warning',
                        'icon' => 'tabler-clipboard-text',
                        'desc' => 'Izin terdata',
                    ],
                    [
                        'label' => 'Alpha',
                        'val' => $alphaCount,
                        'color' => 'danger',
                        'icon' => 'tabler-ban',
                        'desc' => 'Tanpa kabar',
                    ],
                    [
                        'label' => 'Terlambat',
                        'val' => $terlambatCount,
                        'color' => 'secondary',
                        'icon' => 'tabler-clock-exclamation',
                        'desc' => 'Lewat batas',
                    ],
                    [
                        'label' => 'Belum Absen',
                        'val' => $belumAbsen,
                        'color' => 'dark',
                        'icon' => 'tabler-user-question',
                        'desc' => 'Standby',
                    ],
                ];
              @endphp
              @foreach ($statuses as $st)
                <div class="das-status-item das-status-item--{{ $st['color'] }}">
                  <div class="das-status-item__icon">
                    <i class="ti {{ $st['icon'] }}"></i>
                  </div>
                  <div class="das-status-item__info">
                    <div class="das-status-item__label">
                      {{ $st['label'] }}
                      @if ($st['label'] == 'Belum Absen' && $st['val'] > 0)
                        <i class="ti tabler-info-circle text-muted ms-1" style="cursor:help" data-bs-toggle="modal"
                          data-bs-target="#modalBelumAbsen"></i>
                      @endif
                    </div>
                    <div class="das-status-progress">
                      <div class="das-status-progress__bar"
                        style="width: {{ $totalSiswa > 0 ? ($st['val'] / $totalSiswa) * 100 : 0 }}%"></div>
                    </div>
                  </div>
                  <div class="das-status-item__val">{{ $st['val'] }}</div>
                </div>
              @endforeach
            </div>

          </div>
        </div>
      </div>

      {{-- SISWA PALING AWAL --}}
      <div class="das-panel">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot --warning"></span>
            10 Siswa Paling Awal Hadir
          </div>
          <button class="das-icon-btn" onclick="refreshDashboardData()" title="Refresh">
            <i class="ti tabler-refresh"></i>
          </button>
        </div>
        <div class="table-responsive">
          <table class="das-table" id="table-earliest">
            <thead>
              <tr>
                <th class="text-center" width="60">RANK</th>
                <th>NAMA SISWA</th>
                <th>KELAS</th>
                <th class="text-center">JAM MASUK</th>
                <th class="text-center">STATUS</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($palingAwal as $index => $abs)
                @php $rankIcons = [0=>'🥇',1=>'🥈',2=>'🥉']; @endphp
                <tr class="{{ $index < 3 ? 'das-table__row--highlight' : '' }}">
                  <td class="text-center fs-5">{!! $rankIcons[$index] ?? $index + 1 !!}</td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <img
                        src="https://ui-avatars.com/api/?name={{ urlencode($abs->siswa->nama_lengkap) }}&background=7367f0&color=fff"
                        class="das-avatar" width="30">
                      <span class="fw-semibold">{{ $abs->siswa->nama_lengkap }}</span>
                    </div>
                  </td>
                  <td><span class="das-chip --info">{{ $abs->siswa->kelas->nama ?? '-' }}</span></td>
                  <td class="text-center font-monospace fw-bold">{{ $abs->jam_masuk }}</td>
                  <td class="text-center"><span class="das-chip --success">Hadir</span></td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="das-table__empty">Belum ada siswa yang hadir hari ini.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>{{-- /das-col-left --}}


    {{-- ─────────────────────────────
         KOLOM KANAN
    ───────────────────────────── --}}
    <div class="das-col-right">

      {{-- QR SCANNER --}}
      <div class="das-scanner-card mb-4">
        <div class="das-scanner-card__head">
          <div>
            <h5 class="das-scanner-card__title"><i class="ti tabler-qrcode"></i> Scanner Presensi</h5>
            <p class="das-scanner-card__sub">Arahkan kamera ke QR kartu siswa/guru.</p>
          </div>
          <div class="das-scanner-card__pulse"></div>
        </div>

        {{-- Video Area --}}
        <div id="scanner-container" class="das-scanner-viewport">
          {{-- Placeholder --}}
          <div id="cam-placeholder" class="das-scanner-placeholder">
            <div class="das-scanner-placeholder__icon">
              <i class="ti tabler-camera"></i>
            </div>
            <p>Kamera Belum Aktif</p>
            <button class="das-btn das-btn--primary" id="btn-start-scanner">
              <i class="ti tabler-player-play"></i> Mulai Scanner
            </button>
          </div>

          {{-- Active cam --}}
          <div id="cam-active" class="d-none w-100 h-100">
            <div id="reader-wrapper" class="w-100 h-100 position-relative">
              <div class="das-crosshair">
                <div class="das-crosshair__corner das-crosshair__corner--tl"></div>
                <div class="das-crosshair__corner das-crosshair__corner--tr"></div>
                <div class="das-crosshair__corner das-crosshair__corner--bl"></div>
                <div class="das-crosshair__corner das-crosshair__corner--br"></div>
                <div class="das-crosshair__scan-line"></div>
              </div>
              <div id="reader" class="w-100 h-100"></div>
              <div class="das-scanner-cam-toolbar">
                <button class="das-btn das-btn--ghost-sm" id="btn-switch-cam">
                  <i class="ti tabler-refresh"></i> Putar Kamera
                </button>
              </div>
            </div>
          </div>

          {{-- Feedback Overlay --}}
          <div id="scan-feedback" class="das-scanner-feedback d-none">
            <div class="das-scanner-feedback__inner scale-in">
              <div id="feedback-icon" class="das-scanner-feedback__icon">✅</div>
              <div id="feedback-status" class="das-scanner-feedback__status">BERHASIL</div>
              <div id="feedback-name" class="das-scanner-feedback__name">NAMA SISWA</div>
              <div id="feedback-kelas" class="das-scanner-feedback__kelas">KELAS</div>
              <div id="feedback-time" class="das-scanner-feedback__time font-monospace">--:--:--</div>
            </div>
          </div>
        </div>

        {{-- Sound toggle --}}
        <div class="das-scanner-sound">
          <span>Efek Suara Scan</span>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="toggleSound" checked>
          </div>
        </div>

        {{-- Mini Log --}}
        <div class="das-mini-log">
          <div class="das-mini-log__head">
            <i class="ti tabler-history"></i> Aktivitas Terakhir
          </div>
          <div class="das-mini-log__list list-scan-log">
            @forelse ($palingAkhir as $abs)
              <div class="das-mini-log__item">
                <img
                  src="https://ui-avatars.com/api/?name={{ urlencode($abs->siswa->nama_lengkap) }}&background=7367f0&color=fff"
                  class="das-avatar" width="32">
                <div class="das-mini-log__info">
                  <div class="das-mini-log__name">{{ $abs->siswa->nama_lengkap }}</div>
                  <div class="das-mini-log__meta">{{ $abs->siswa->kelas->nama }} · {{ $abs->jam_masuk }}</div>
                </div>
                <span class="das-chip --success das-chip--xs">Presensi</span>
              </div>
            @empty
              <p class="das-mini-log__empty">Sistem stand-by.</p>
            @endforelse
          </div>
        </div>
      </div>{{-- /das-scanner-card --}}


      {{-- QUICK ACCESS --}}
      <div class="das-panel mb-4">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot --primary"></span>
            Akses Cepat
          </div>
        </div>
        <div class="das-panel__body">
          <div class="das-quick-grid">
            @php
              $quickLinks = [
                  [
                      'icon' => 'tabler-database',
                      'title' => 'Master',
                      'route' => route('admin.master-data'),
                      'color' => 'primary',
                  ],
                  [
                      'icon' => 'tabler-school',
                      'title' => 'Absensi',
                      'route' => route('admin.absensi-siswa.index'),
                      'color' => 'success',
                  ],
                  [
                      'icon' => 'tabler-report-analytics',
                      'title' => 'Laporan',
                      'route' => route('admin.laporan.index'),
                      'color' => 'warning',
                  ],
                  [
                      'icon' => 'tabler-file-heart',
                      'title' => 'Izin',
                      'route' => route('admin.izin-sakit.index'),
                      'color' => 'danger',
                  ],
                  [
                      'icon' => 'tabler-users',
                      'title' => 'Users',
                      'route' => route('admin.users.index'),
                      'color' => 'dark',
                  ],
                  [
                      'icon' => 'tabler-settings',
                      'title' => 'Settings',
                      'route' => route('admin.pengaturan.index'),
                      'color' => 'info',
                  ],
                  [
                      'icon' => 'tabler-cloud-download',
                      'title' => 'Update',
                      'route' => route('admin.update.index'),
                      'color' => 'primary',
                  ],
              ];
            @endphp
            @foreach ($quickLinks as $link)
              <a href="{{ $link['route'] }}"
                class="das-quick-item das-quick-item--{{ $link['color'] }} text-decoration-none">
                <i class="ti {{ $link['icon'] }}"></i>
                <span>{{ $link['title'] }}</span>
              </a>
            @endforeach
          </div>
        </div>
      </div>

      {{-- ALERT UPDATE SYSTEM --}}
      @php
          $updateInfo = app(\App\Services\UpdateService::class)->getCachedUpdateInfo();
      @endphp
      @if ($updateInfo)
        <div class="das-alert-card das-alert-card--info mb-4 bounce-in">
          <div class="das-alert-card__icon pulse-info">
            <i class="ti tabler-cloud-download"></i>
          </div>
          <div class="das-alert-card__body">
            <div class="das-alert-card__title">Update Tersedia: v{{ $updateInfo['latest_version'] }}</div>
            <div class="das-alert-card__count">Klik untuk melihat catatan rilis</div>
          </div>
          <a href="{{ route('admin.update.index') }}" class="das-btn das-btn--info">Update</a>
        </div>
      @endif

      {{-- ALERT IZIN PENDING --}}
      @if ($totalIzinPending > 0)
        <div class="das-alert-card mb-4">
          <div class="das-alert-card__icon pulse-danger">
            <i class="ti tabler-alert-triangle"></i>
          </div>
          <div class="das-alert-card__body">
            <div class="das-alert-card__title">Persetujuan Izin Pending</div>
            <div class="das-alert-card__count">{{ $totalIzinPending }} pengajuan menunggu</div>
          </div>
          <a href="{{ route('admin.izin-sakit.index') }}" class="das-btn das-btn--danger">Proses</a>
        </div>
      @endif

      {{-- GURU & STAFF HADIR --}}
      <div class="das-attendance-mini">
        <div class="das-attendance-mini__item">
          <div class="das-attendance-mini__icon --success">
            <i class="ti tabler-chalkboard-teacher"></i>
          </div>
          <div>
            <div class="das-attendance-mini__val">{{ $absensiGuruHariIni }}</div>
            <div class="das-attendance-mini__label">Guru Hadir</div>
          </div>
        </div>
        <div class="das-attendance-mini__divider"></div>
        <div class="das-attendance-mini__item">
          <div class="das-attendance-mini__icon --info">
            <i class="ti tabler-user-check"></i>
          </div>
          <div>
            <div class="das-attendance-mini__val">{{ $absensiStaffHariIni }}</div>
            <div class="das-attendance-mini__label">Staff Hadir</div>
          </div>
        </div>
      </div>

    </div>{{-- /das-col-right --}}
  </div>{{-- /das-main-grid --}}


  {{-- ═══════════════════════════════════════════════════════
       MODAL: BELUM ABSEN
  ═══════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalBelumAbsen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal">
        <div class="das-modal__head">
          <h5 class="das-modal__title"><i class="ti tabler-user-question me-2"></i>Siswa Belum Absen</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="das-modal__body">
          <div class="das-modal__stat">
            <div class="das-modal__stat-val">{{ $belumAbsen }}</div>
            <div class="das-modal__stat-label">Total Siswa Belum Absen Hari Ini</div>
            <div class="das-modal__stat-warn"><i class="ti tabler-alert-circle"></i> Segera lakukan follow up.</div>
          </div>
          <div class="das-modal__note">Fitur rincian daftar siswa di modal ini akan segera hadir pada update berikutnya.
          </div>
        </div>
        <div class="das-modal__foot">
          <button type="button" class="das-btn das-btn--ghost w-100" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

@endsection


@section('page-style')
  <style>
    /* ═══════════════════════════════════════════════════════
     CSS VARIABLES
  ═══════════════════════════════════════════════════════ */
    :root {
      --das-primary: #7367f0;
      --das-primary-soft: rgba(115, 103, 240, 0.12);
      --das-success: #28c76f;
      --das-success-soft: rgba(40, 199, 111, 0.12);
      --das-info: #00cfe8;
      --das-info-soft: rgba(0, 207, 232, 0.12);
      --das-warning: #ff9f43;
      --das-warning-soft: rgba(255, 159, 67, 0.12);
      --das-danger: #ea5455;
      --das-danger-soft: rgba(234, 84, 85, 0.12);
      --das-dark: #4b4b4b;
      --das-secondary: #a8aaae;

      --das-surface: rgba(15, 23, 42, 0.4);
      --das-surface-hover: rgba(30, 41, 59, 0.6);
      --das-border: rgba(255, 255, 255, 0.06);
      --das-border-hover: rgba(255, 255, 255, 0.12);
      --das-radius: 5px;
      --das-radius-sm: 5px;
      --das-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      --das-shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* ═══════════════════════════════════════════════════════
     HERO HEADER
  ═══════════════════════════════════════════════════════ */
    /* ═══════════════════════════════════════════════════════
     GLOBAL AESTHETICS
     ═══════════════════════════════════════════════════════ */
    .glass-card {
      background: rgba(255, 255, 255, 0.03) !important;
      backdrop-filter: blur(12px) saturate(180%);
      -webkit-backdrop-filter: blur(12px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    .text-gradient-gold {
      background: linear-gradient(to right, #fff, #ffd700);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .das-hero {
      position: relative;
      border-radius: var(--das-radius);
      overflow: visible;
      margin-top: 1.5rem;
      /* Added margin top */
      margin-bottom: 8rem;
      /* Aggressive margin to clear content */
    }

    .das-hero__bg {
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%);
      border-radius: var(--das-radius);
      z-index: 0;
    }

    .das-hero__glass {
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top right, rgba(115, 103, 240, 0.15), transparent 40%);
      z-index: 1;
      border-radius: var(--das-radius);
    }

    /* Subtle animated grid overlay */
    .das-hero__grid-lines {
      position: absolute;
      inset: 0;
      border-radius: var(--das-radius);
      background-image:
        linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
      background-size: 40px 40px;
      z-index: 1;
    }

    .das-hero__inner {
      position: relative;
      z-index: 2;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 3.5rem 2.5rem 5rem;
      /* Increased top padding from 2.25rem to 3.5rem */
      gap: 1.5rem;
      flex-wrap: wrap;
    }

    /* Identity */
    .das-hero__identity {
      display: flex;
      align-items: center;
      gap: 1.25rem;
    }

    .das-hero__logo-wrapper {
      position: relative;
    }

    .das-hero__logo {
      width: 72px;
      height: 72px;
      object-fit: cover;
      border-radius: 5px;
      border: 2px solid rgba(255, 255, 255, 0.25);
      background: white;
      padding: 5px;
      position: relative;
      z-index: 2;
    }

    .das-hero__logo-glow {
      position: absolute;
      inset: -5px;
      background: var(--das-primary);
      filter: blur(15px);
      opacity: 0.3;
      z-index: 1;
      border-radius: 50%;
    }

    .das-hero__logo-placeholder {
      width: 72px;
      height: 72px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid rgba(255, 255, 255, 0.15);
      font-size: 2rem;
      color: white;
    }

    .das-hero__badge {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      background: rgba(115, 103, 240, 0.2);
      border: 1px solid rgba(115, 103, 240, 0.3);
      color: #a5a2f7;
      padding: 3px 10px;
      border-radius: 20px;
      margin-bottom: 6px;
    }

    .pulse-dot {
      width: 6px;
      height: 6px;
      background: #a5a2f7;
      border-radius: 50%;
      animation: pulseGlow 1.5s infinite;
    }

    @keyframes pulseGlow {
      50% {
        transform: scale(1.2);
        opacity: 1;
      }

      100% {
        transform: scale(0.8);
        opacity: 0.5;
      }
    }

    .das-hero__school {
      font-size: 1.5rem;
      font-weight: 800;
      color: white;
      margin: 0 0 4px;
    }

    .das-hero__welcome {
      margin: 0;
      font-size: 0.88rem;
      color: rgba(255, 255, 255, 0.6);
    }

    .das-hero__welcome strong {
      color: white;
      font-weight: 600;
    }

    /* Clock */
    .das-hero__clock {
      text-align: right;
      background: rgba(0, 0, 0, 0.25);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: var(--das-radius);
      padding: 1rem 1.5rem;
      min-width: 210px;
    }

    .das-hero__date {
      font-size: 0.65rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: rgba(255, 255, 255, 0.55);
      margin-bottom: 4px;
    }

    .das-hero__time {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 8px;
    }

    #live-clock {
      font-size: 1.75rem;
      font-weight: 700;
      font-family: monospace;
      color: white;
      letter-spacing: 2px;
    }

    .das-hero__live-badge {
      background: var(--das-danger);
      color: white;
      font-size: 0.5rem;
      font-weight: 800;
      letter-spacing: 1px;
      padding: 3px 6px;
      border-radius: 4px;
      animation: pulse 2s ease infinite;
    }

    .das-hero__tz {
      font-size: 0.55rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: rgba(255, 255, 255, 0.4);
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      margin-top: 6px;
      padding-top: 6px;
    }

    /* ── Floating stats row ── */
    .das-stats-row {
      position: absolute;
      bottom: -45px;
      left: 1.25rem;
      right: 1.25rem;
      z-index: 10;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
    }

    .das-stat-card {
      display: flex;
      align-items: center;
      gap: 1rem;
      background: rgba(30, 41, 59, 0.7);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border-radius: var(--das-radius-sm);
      padding: 1.1rem 1.25rem;
      border: 1px solid rgba(255, 255, 255, 0.08);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }

    .das-stat-card:hover {
      transform: translateY(-5px);
      background: rgba(30, 41, 59, 0.9);
      border-color: rgba(255, 255, 255, 0.2);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
    }

    .das-stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      transition: opacity 0.3s;
      opacity: 0.6;
    }

    .das-stat-card--primary::before {
      background: var(--das-primary);
    }

    .das-stat-card--success::before {
      background: var(--das-success);
    }

    .das-stat-card--info::before {
      background: var(--das-info);
    }

    .das-stat-card--warning::before {
      background: var(--das-warning);
    }

    .das-stat-card__icon {
      width: 46px;
      height: 46px;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      flex-shrink: 0;
      transition: transform 0.3s;
    }

    .das-stat-card:hover .das-stat-card__icon {
      transform: scale(1.1);
    }

    .das-stat-card--primary .das-stat-card__icon {
      background: rgba(115, 103, 240, 0.15);
      color: var(--das-primary);
    }

    .das-stat-card--success .das-stat-card__icon {
      background: rgba(40, 199, 111, 0.15);
      color: var(--das-success);
    }

    .das-stat-card--info .das-stat-card__icon {
      background: rgba(0, 207, 232, 0.15);
      color: var(--das-info);
    }

    .das-stat-card--warning .das-stat-card__icon {
      background: rgba(255, 159, 67, 0.15);
      color: var(--das-warning);
    }

    .das-stat-card__body {
      flex: 1;
    }

    .das-stat-card__val {
      font-size: 1.6rem;
      font-weight: 800;
      line-height: 1.1;
      color: #fff;
      letter-spacing: -0.5px;
    }

    .das-stat-card__label {
      font-size: 0.68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #94a3b8;
      margin-top: 3px;
    }

    .das-stat-card__arrow {
      margin-left: auto;
      font-size: 1rem;
      color: #475569;
      transition: all 0.3s;
    }

    .das-stat-card:hover .das-stat-card__arrow {
      color: #fff;
      transform: translateX(3px);
    }

    /* ═══════════════════════════════════════════════════════
     MAIN GRID
  ═══════════════════════════════════════════════════════ */
    .das-main-grid {
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: 1.25rem;
      align-items: start;
      margin-top: 2rem;
      padding-top: 2rem;
    }

    /* ═══════════════════════════════════════════════════════
     PANEL (generic card)
  ═══════════════════════════════════════════════════════ */
    .das-panel {
      background: var(--das-surface);
      border: 1px solid var(--das-border);
      border-radius: var(--das-radius);
      overflow: hidden;
      backdrop-filter: blur(6px);
    }

    .das-panel__head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.9rem 1.25rem;
      border-bottom: 1px solid var(--das-border);
    }

    .das-panel__title {
      font-size: 0.82rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      display: flex;
      align-items: center;
      gap: 8px;
      color: #ccc;
    }

    .das-panel__icon-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      display: inline-block;
      flex-shrink: 0;
    }

    .das-panel__icon-dot.--primary {
      background: var(--das-primary);
      box-shadow: 0 0 6px var(--das-primary);
    }

    .das-panel__icon-dot.--success {
      background: var(--das-success);
      box-shadow: 0 0 6px var(--das-success);
    }

    .das-panel__icon-dot.--warning {
      background: var(--das-warning);
      box-shadow: 0 0 6px var(--das-warning);
    }

    .das-panel__body {
      padding: 1rem 1.25rem;
    }

    /* ═══════════════════════════════════════════════════════
     TODAY SECTION
  ═══════════════════════════════════════════════════════ */
    .das-today-grid {
      display: grid;
      grid-template-columns: 220px 1fr;
      gap: 1.5rem;
      align-items: center;
    }

    .das-today-donut {
      min-height: 240px;
    }

    .das-status-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.6rem;
    }

    .das-status-item {
      display: flex;
      align-items: center;
      gap: 0.85rem;
      padding: 0.75rem 1rem;
      border-radius: var(--das-radius-sm);
      background: rgba(30, 41, 59, 0.4);
      border: 1px solid var(--das-border);
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .das-status-item:hover {
      border-color: var(--das-primary);
      background: rgba(30, 41, 59, 0.7);
      transform: translateX(5px);
    }

    .das-status-item__icon {
      width: 36px;
      height: 36px;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .das-status-item--success .das-status-item__icon {
      background: rgba(40, 199, 111, 0.1);
      color: var(--das-success);
    }

    .das-status-item--info .das-status-item__icon {
      background: rgba(0, 207, 232, 0.1);
      color: var(--das-info);
    }

    .das-status-item--warning .das-status-item__icon {
      background: rgba(255, 159, 67, 0.1);
      color: var(--das-warning);
    }

    .das-status-item--danger .das-status-item__icon {
      background: rgba(234, 84, 85, 0.1);
      color: var(--das-danger);
    }

    .das-status-item--secondary .das-status-item__icon {
      background: rgba(168, 170, 174, 0.1);
      color: var(--das-secondary);
    }

    .das-status-item--dark .das-status-item__icon {
      background: rgba(255, 255, 255, 0.05);
      color: #64748b;
    }

    .das-status-item__info {
      flex: 1;
      min-width: 0;
    }

    .das-status-item__label {
      font-size: 0.75rem;
      font-weight: 700;
      color: #e2e8f0;
      line-height: 1.2;
      margin-bottom: 4px;
    }

    .das-status-progress {
      height: 4px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 5px;
      overflow: hidden;
    }

    .das-status-progress__bar {
      height: 100%;
      border-radius: 5px;
      transition: width 1s ease-out;
    }

    .das-status-item--success .das-status-progress__bar {
      background: var(--das-success);
    }

    .das-status-item--info .das-status-progress__bar {
      background: var(--das-info);
    }

    .das-status-item--warning .das-status-progress__bar {
      background: var(--das-warning);
    }

    .das-status-item--danger .das-status-progress__bar {
      background: var(--das-danger);
    }

    .das-status-item--secondary .das-status-progress__bar {
      background: var(--das-secondary);
    }

    .das-status-item--dark .das-status-progress__bar {
      background: #475569;
    }

    .das-status-item__val {
      font-size: 1.25rem;
      font-weight: 800;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 4px;
    }


    /* ═══════════════════════════════════════════════════════
     TABLE
  ═══════════════════════════════════════════════════════ */
    .das-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.82rem;
    }

    .das-table thead th {
      font-size: 0.62rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #666;
      padding: 0.6rem 1rem;
      border-bottom: 1px solid var(--das-border);
      background: transparent;
    }

    .das-table tbody td {
      padding: 0.65rem 1rem;
      border-bottom: 1px solid var(--das-border);
      color: #ccc;
      vertical-align: middle;
    }

    .das-table tbody tr:last-child td {
      border-bottom: none;
    }

    .das-table tbody tr {
      transition: background 0.15s;
    }

    .das-table tbody tr:hover td {
      background: var(--das-surface-hover);
    }

    .das-table__row--highlight td {
      background: rgba(255, 159, 67, 0.05);
    }

    .das-table__empty {
      text-align: center;
      padding: 2rem;
      color: #555;
    }

    /* ═══════════════════════════════════════════════════════
     SCANNER CARD
  ═══════════════════════════════════════════════════════ */
    .das-scanner-card {
      background: var(--das-surface);
      border: 1px solid var(--das-border);
      border-radius: var(--das-radius);
      overflow: hidden;
      backdrop-filter: blur(6px);
    }

    .das-scanner-card__head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      padding: 1rem 1.25rem 0.75rem;
      border-bottom: 1px solid var(--das-border);
      background: linear-gradient(90deg, rgba(115, 103, 240, 0.15) 0%, transparent 100%);
    }

    .das-scanner-card__title {
      font-size: 0.9rem;
      font-weight: 700;
      color: #e0e0e0;
      margin: 0 0 3px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .das-scanner-card__title .ti {
      color: var(--das-primary);
    }

    .das-scanner-card__sub {
      margin: 0;
      font-size: 0.72rem;
      color: #666;
    }

    .das-scanner-card__pulse {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--das-success);
      box-shadow: 0 0 8px var(--das-success);
      animation: pulse 2s ease infinite;
      flex-shrink: 0;
      margin-top: 4px;
    }

    /* Viewport */
    .das-scanner-viewport {
      position: relative;
      aspect-ratio: 4/3;
      background: #0a0a14;
      overflow: hidden;
    }

    .das-scanner-placeholder {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      text-align: center;
      padding: 1.5rem;
    }

    .das-scanner-placeholder__icon {
      width: 54px;
      height: 54px;
      border-radius: 5px;
      background: var(--das-primary-soft);
      color: var(--das-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin-bottom: 4px;
    }

    .das-scanner-placeholder p {
      color: #555;
      font-size: 0.8rem;
      margin: 0 0 8px;
    }

    /* Crosshair */
    .das-crosshair {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 190px;
      height: 150px;
      z-index: 10;
    }

    .das-crosshair__corner {
      position: absolute;
      width: 20px;
      height: 20px;
      border-color: var(--das-primary);
      border-style: solid;
    }

    .das-crosshair__corner--tl {
      top: 0;
      left: 0;
      border-width: 2px 0 0 2px;
      border-radius: 2px 0 0 0;
    }

    .das-crosshair__corner--tr {
      top: 0;
      right: 0;
      border-width: 2px 2px 0 0;
      border-radius: 0 2px 0 0;
    }

    .das-crosshair__corner--bl {
      bottom: 0;
      left: 0;
      border-width: 0 0 2px 2px;
      border-radius: 0 0 0 2px;
    }

    .das-crosshair__corner--br {
      bottom: 0;
      right: 0;
      border-width: 0 2px 2px 0;
      border-radius: 0 0 2px 0;
    }

    .das-crosshair__scan-line {
      position: absolute;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--das-primary), transparent);
      box-shadow: 0 0 10px var(--das-primary);
      top: 0;
      animation: scanLine 2.5s ease-in-out infinite;
    }

    .das-scanner-cam-toolbar {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 0.5rem;
      background: rgba(0, 0, 0, 0.4);
      display: flex;
      justify-content: center;
      z-index: 10;
    }

    /* Feedback overlay */
    .das-scanner-feedback {
      position: absolute;
      inset: 0;
      z-index: 20;
      background: rgba(0, 0, 0, 0.88);
      backdrop-filter: blur(6px);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .das-scanner-feedback__inner {
      text-align: center;
    }

    .das-scanner-feedback__icon {
      font-size: 2.5rem;
      margin-bottom: 8px;
    }

    .das-scanner-feedback__status {
      font-size: 0.7rem;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--das-success);
      margin-bottom: 8px;
    }

    .das-scanner-feedback__name {
      font-size: 1rem;
      font-weight: 700;
      color: white;
      margin-bottom: 2px;
    }

    .das-scanner-feedback__kelas {
      font-size: 0.75rem;
      color: #888;
      margin-bottom: 8px;
    }

    .das-scanner-feedback__time {
      font-size: 1.4rem;
      font-weight: 700;
      color: white;
      letter-spacing: 2px;
    }

    .das-scanner-sound {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.65rem 1.1rem;
      border-top: 1px solid var(--das-border);
      font-size: 0.75rem;
      color: #888;
    }

    /* Mini log */
    .das-mini-log {
      padding: 0.75rem 1.1rem 1rem;
    }

    .das-mini-log__head {
      font-size: 0.62rem;
      font-weight: 700;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      color: #666;
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 0.65rem;
    }

    .das-mini-log__list {
      max-height: 210px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
    }

    .das-mini-log__list::-webkit-scrollbar {
      width: 4px;
    }

    .das-mini-log__list::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 5px;
    }

    .das-mini-log__item {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.45rem 0;
      border-bottom: 1px solid var(--das-border);
    }

    .das-mini-log__item:last-child {
      border-bottom: none;
    }

    .das-mini-log__info {
      flex: 1;
      min-width: 0;
    }

    .das-mini-log__name {
      font-size: 0.78rem;
      font-weight: 600;
      color: #ddd;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .das-mini-log__meta {
      font-size: 0.62rem;
      color: #666;
    }

    .das-mini-log__empty {
      text-align: center;
      color: #555;
      font-size: 0.78rem;
      padding: 0.75rem 0;
      margin: 0;
    }

    /* ═══════════════════════════════════════════════════════
     QUICK LINKS
  ═══════════════════════════════════════════════════════ */
    .das-quick-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.6rem;
    }

    .das-quick-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 5px;
      padding: 0.85rem 0.5rem;
      border-radius: var(--das-radius-sm);
      border: 1px solid var(--das-border);
      background: var(--das-surface);
      transition: all 0.2s ease;
      cursor: pointer;
      font-size: 0.62rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #aaa;
    }

    .das-quick-item .ti {
      font-size: 1.25rem;
    }

    .das-quick-item:hover {
      border-color: var(--das-border-hover);
      transform: translateY(-2px);
      color: white;
    }

    .das-quick-item--primary .ti {
      color: var(--das-primary);
    }

    .das-quick-item--success .ti {
      color: var(--das-success);
    }

    .das-quick-item--warning .ti {
      color: var(--das-warning);
    }

    .das-quick-item--danger .ti {
      color: var(--das-danger);
    }

    .das-quick-item--info .ti {
      color: var(--das-info);
    }

    .das-quick-item--dark .ti {
      color: #aaa;
    }

    .das-quick-item--primary:hover {
      background: var(--das-primary-soft);
      border-color: var(--das-primary);
    }

    .das-quick-item--success:hover {
      background: var(--das-success-soft);
      border-color: var(--das-success);
    }

    .das-quick-item--warning:hover {
      background: var(--das-warning-soft);
      border-color: var(--das-warning);
    }

    .das-quick-item--danger:hover {
      background: var(--das-danger-soft);
      border-color: var(--das-danger);
    }

    .das-quick-item--info:hover {
      background: var(--das-info-soft);
      border-color: var(--das-info);
    }

    .das-quick-item--dark:hover {
      background: rgba(75, 75, 75, 0.15);
    }

    /* ═══════════════════════════════════════════════════════
     ALERT CARD
  ═══════════════════════════════════════════════════════ */
    .das-alert-card {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.85rem 1rem;
      border-radius: var(--das-radius-sm);
      background: var(--das-danger-soft);
      border: 1px solid rgba(234, 84, 85, 0.25);
    }

    .das-alert-card__icon {
      font-size: 1.25rem;
      color: var(--das-danger);
      flex-shrink: 0;
    }

    .das-alert-card__body {
      flex: 1;
    }

    .das-alert-card__title {
      font-size: 0.8rem;
      font-weight: 700;
      color: #ddd;
    }

    .das-alert-card__count {
      font-size: 0.7rem;
      color: #aaa;
    }

    .das-alert-card--info {
      background: var(--das-info-soft);
      border-color: rgba(0, 207, 232, 0.25);
    }

    .das-alert-card--info .das-alert-card__icon {
      color: var(--das-info);
    }

    /* ═══════════════════════════════════════════════════════
     ATTENDANCE MINI (Guru & Staff)
  ═══════════════════════════════════════════════════════ */
    .das-attendance-mini {
      display: flex;
      align-items: center;
      background: var(--das-surface);
      border: 1px solid var(--das-border);
      border-radius: var(--das-radius-sm);
      overflow: hidden;
    }

    .das-attendance-mini__item {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.9rem 1rem;
    }

    .das-attendance-mini__divider {
      width: 1px;
      height: 50px;
      background: var(--das-border);
    }

    .das-attendance-mini__icon {
      width: 40px;
      height: 40px;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .das-attendance-mini__icon.--success {
      background: var(--das-success-soft);
      color: var(--das-success);
    }

    .das-attendance-mini__icon.--info {
      background: var(--das-info-soft);
      color: var(--das-info);
    }

    .das-attendance-mini__val {
      font-size: 1.4rem;
      font-weight: 800;
      color: #fff;
      line-height: 1;
    }

    .das-attendance-mini__label {
      font-size: 0.62rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #777;
      margin-top: 2px;
    }

    /* ═══════════════════════════════════════════════════════
     CHIPS / BADGES
  ═══════════════════════════════════════════════════════ */
    .das-chip {
      display: inline-flex;
      align-items: center;
      font-size: 0.65rem;
      font-weight: 700;
      padding: 2px 9px;
      border-radius: 20px;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }

    .das-chip.--primary {
      background: var(--das-primary-soft);
      color: var(--das-primary);
    }

    .das-chip.--success {
      background: var(--das-success-soft);
      color: var(--das-success);
    }

    .das-chip.--info {
      background: var(--das-info-soft);
      color: var(--das-info);
    }

    .das-chip.--warning {
      background: var(--das-warning-soft);
      color: var(--das-warning);
    }

    .das-chip.--danger {
      background: var(--das-danger-soft);
      color: var(--das-danger);
    }

    .das-chip--xs {
      font-size: 0.58rem;
      padding: 1px 6px;
    }

    /* ═══════════════════════════════════════════════════════
     BUTTONS
  ═══════════════════════════════════════════════════════ */
    .das-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.45rem 0.85rem;
      border-radius: 5px;
      border: 1px solid transparent;
      cursor: pointer;
      transition: all 0.18s ease;
      text-decoration: none;
      white-space: nowrap;
    }

    .das-btn--ghost {
      background: transparent;
      border-color: var(--das-border);
      color: #999;
    }

    .das-btn--ghost:hover {
      background: var(--das-surface-hover);
      color: white;
      border-color: var(--das-border-hover);
    }

    .das-btn--ghost-sm {
      background: rgba(255, 255, 255, 0.07);
      border-color: rgba(255, 255, 255, 0.12);
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.7rem;
    }

    .das-btn--ghost-sm:hover {
      background: rgba(255, 255, 255, 0.13);
      color: white;
    }

    .das-btn--primary {
      background: var(--das-primary);
      color: white;
      border-color: var(--das-primary);
    }

    .das-btn--primary:hover {
      background: #6259e8;
      color: white;
    }

    .das-btn--danger {
      background: var(--das-danger);
      color: white;
      border-color: var(--das-danger);
    }

    .das-btn--info {
      background: var(--das-info);
      color: white;
      border-color: var(--das-info);
    }

    .das-btn--info:hover {
      background: #00b8d4;
      color: white;
    }
    /* =============================================================================
     RESPONSIVE
     ============================================================================= */
    @media (max-width: 1199px) {
      .das-main-grid {
        grid-template-columns: 1fr;
      }

      .das-col-right {
        order: -1;
      }
    }

    @media (max-width: 767px) {
      .das-stats-row {
        grid-template-columns: 1fr 1fr;
        gap: 0.6rem;
        bottom: -90px;
      }

      .das-hero {
        margin-bottom: 12rem;
      }

      .das-hero__inner {
        padding: 2rem 1.25rem 3rem;
        flex-direction: column;
        align-items: flex-start;
      }

      .das-hero__clock {
        width: 100%;
        text-align: left;
        min-width: unset;
      }

      .das-hero__time {
        justify-content: flex-start;
      }

      .das-today-grid {
        grid-template-columns: 1fr;
      }

      .das-status-grid {
        grid-template-columns: 1fr 1fr;
      }

      .das-stat-card {
        padding: 0.9rem 1rem;
        gap: 0.75rem;
      }

      .das-stat-card__val {
        font-size: 1.35rem;
      }
    }

    @media (max-width: 575px) {
      .das-stats-row {
        position: absolute;
        bottom: -105px;
        left: 0.75rem;
        right: 0.75rem;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
      }

      .das-hero {
        margin-bottom: 14rem;
      }

      .das-stat-card {
        padding: 0.8rem 0.75rem;
        gap: 0.6rem;
        flex-wrap: nowrap;
      }

      .das-stat-card__icon {
        width: 38px;
        height: 38px;
        font-size: 1.1rem;
        flex-shrink: 0;
      }

      .das-stat-card__val {
        font-size: 1.2rem;
        font-weight: 800;
      }

      .das-stat-card__label {
        font-size: 0.6rem;
        letter-spacing: 0.3px;
      }

      .das-stat-card__side-info {
        display: none;
      }

      .das-hero__inner {
        padding: 1.5rem 1rem 2.5rem;
      }

      .das-hero__school {
        font-size: 1.2rem;
      }

      #live-clock {
        font-size: 1.4rem;
      }

      .das-hero__logo,
      .das-hero__logo-placeholder {
        width: 56px;
        height: 56px;
      }

      .das-hero__logo-placeholder {
        font-size: 1.5rem;
      }
    }

    @media (max-width: 400px) {
      .das-stats-row {
        bottom: -115px;
        left: 0.5rem;
        right: 0.5rem;
        gap: 0.4rem;
      }

      .das-hero {
        margin-bottom: 15rem;
      }

      .das-stat-card {
        padding: 0.65rem 0.6rem;
        gap: 0.45rem;
      }

      .das-stat-card__icon {
        width: 32px;
        height: 32px;
        font-size: 0.95rem;
      }

      .das-stat-card__val {
        font-size: 1.05rem;
      }

      .das-stat-card__label {
        font-size: 0.55rem;
      }
    }

  /* ═══════════════════════════════════════════════════════
     EMPTY STATE
   ═══════════════════════════════════════════════════════ */
    .das-empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 200px;
      gap: 0.5rem;
      color: #555;
    }

    .das-empty-state .ti {
      font-size: 2rem;
    }

    .das-empty-state span {
      font-size: 0.78rem;
    }

    /* ═══════════════════════════════════════════════════════
     MODAL
  ═══════════════════════════════════════════════════════ */
    .das-modal {
      background: #1a1a2e;
      border: 1px solid var(--das-border);
      border-radius: var(--das-radius);
      overflow: hidden;
    }

    .das-modal__head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 1.25rem;
      background: var(--das-primary-soft);
      border-bottom: 1px solid var(--das-border);
    }

    .das-modal__title {
      font-size: 0.9rem;
      font-weight: 700;
      color: #ddd;
      margin: 0;
    }

    .das-modal__body {
      padding: 0;
    }

    .das-modal__stat {
      padding: 1.5rem;
      text-align: center;
      border-bottom: 1px solid var(--das-border);
    }

    .das-modal__stat-val {
      font-size: 2.5rem;
      font-weight: 800;
      color: white;
    }

    .das-modal__stat-label {
      font-size: 0.78rem;
      color: #777;
      margin-bottom: 8px;
    }

    .das-modal__stat-warn {
      font-size: 0.72rem;
      color: var(--das-danger);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
    }

    .das-modal__note {
      padding: 1rem 1.25rem;
      font-size: 0.78rem;
      color: #555;
      text-align: center;
      font-style: italic;
    }

    .das-modal__foot {
      padding: 0.75rem 1.25rem;
      border-top: 1px solid var(--das-border);
    }

    /* ═══════════════════════════════════════════════════════
     ANIMATIONS
  ═══════════════════════════════════════════════════════ */
    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0.5;
      }
    }

    @keyframes scanLine {
      0% {
        top: 0%;
      }

      50% {
        top: 100%;
      }

      100% {
        top: 0%;
      }
    }

    @keyframes bounceIn {
      0% {
        transform: scale(0.9);
        opacity: 0;
      }

      60% {
        transform: scale(1.02);
        opacity: 1;
      }

      100% {
        transform: scale(1);
      }
    }

    .bounce-in {
      animation: bounceIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes slideInUp {
      from {
        transform: translateY(20px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .slide-in-up {
      animation: slideInUp 0.5s ease-out;
    }


    /* ═══════════════════════════════════════════════════════
     RESPONSIVE
  ═══════════════════════════════════════════════════════ */
    @media (max-width: 1199px) {
      .das-main-grid {
        grid-template-columns: 1fr;
      }

      .das-col-right {
        order: -1;
      }
    }

    @media (max-width: 767px) {
      .das-stats-row {
        grid-template-columns: 1fr 1fr;
      }

      .das-hero {
        margin-bottom: 14rem;
      }

      .das-hero__inner {
        padding: 2.5rem 1.25rem 3.5rem;
        flex-direction: column;
        align-items: flex-start;
      }

      .das-hero__clock {
        width: 100%;
        text-align: left;
      }

      .das-hero__time {
        justify-content: flex-start;
      }

      .das-today-grid {
        grid-template-columns: 1fr;
      }

      .das-status-grid {
        grid-template-columns: 1fr 1fr;
      }
    }

    @media (max-width: 480px) {
      .das-stats-row {
        grid-template-columns: 1fr 1fr;
        left: 0.5rem;
        right: 0.5rem;
      }

      .das-hero {
        margin-bottom: 22rem;
      }
    }
  </style>
@endsection


@section('page-script')
  <script>
    /* ── LIVE CLOCK ── */
    (function() {
      function updateClock() {
        const el = document.getElementById('live-clock');
        if (el) el.textContent = new Date().toLocaleTimeString('id-ID', {
          hour12: false
        });
      }
      updateClock();
      setInterval(updateClock, 1000);
    })();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {

      /* ── COUNTER ANIMATION ── */
      document.querySelectorAll('.counter-value').forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const speed = 200;
        const inc = target / speed;
        const tick = () => {
          const current = +counter.innerText;
          if (current < target) {
            counter.innerText = Math.ceil(current + inc);
            setTimeout(tick, 1);
          } else {
            counter.innerText = target;
          }
        };
        tick();
      });

      /* ── APEX: DONUT ── */
      @php
        $series = [$hadirCount, $sakitCount, $izinCount, $alphaCount, $terlambatCount];
        $labels = ['Hadir', 'Sakit', 'Izin', 'Alpha', 'Terlambat'];
      @endphp
      const chartDonut = new ApexCharts(document.querySelector('#chartDonutStatus'), {
        chart: {
          type: 'donut',
          height: 240,
          background: 'transparent'
        },
        theme: {
          mode: 'dark'
        },
        series: @json($series),
        labels: @json($labels),
        colors: ['#28c76f', '#00cfe8', '#ff9f43', '#ea5455', '#a8aaae'],
        legend: {
          show: false
        },
        dataLabels: {
          enabled: false
        },
        plotOptions: {
          pie: {
            donut: {
              size: '78%',
              labels: {
                show: true,
                total: {
                  show: true,
                  label: 'Total',
                  formatter: () => '{{ $totalAbsensiHariIni }}'
                }
              }
            }
          }
        },
        tooltip: {
          y: {
            formatter: v => v + ' Siswa'
          }
        }
      });
      chartDonut.render();

      /* ── APEX: BAR WEEKLY ── */
      const chartWeekly = new ApexCharts(document.querySelector('#chartKehadiranMingguan'), {
        series: [{
            name: 'Hadir',
            data: @json($chartHadir)
          },
          {
            name: 'Sakit',
            data: @json($chartSakit)
          },
          {
            name: 'Izin',
            data: @json($chartIzin)
          },
          {
            name: 'Alpha',
            data: @json($chartAlpha)
          }
        ],
        chart: {
          type: 'area',
          height: 300,
          background: 'transparent',
          toolbar: {
            show: false
          },
          sparkline: {
            enabled: false
          },
          animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800
          }
        },
        theme: {
          mode: 'dark'
        },
        stroke: {
          curve: 'smooth',
          width: 2.5
        },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [0, 90, 100]
          }
        },
        dataLabels: {
          enabled: false
        },
        colors: ['#28c76f', '#00cfe8', '#ff9f43', '#ea5455'],
        xaxis: {
          categories: @json($chartDays),
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          },
          labels: {
            style: {
              colors: '#64748b',
              fontSize: '11px'
            }
          }
        },
        yaxis: {
          labels: {
            style: {
              colors: '#64748b'
            }
          }
        },
        grid: {
          borderColor: 'rgba(255,255,255,0.04)',
          strokeDashArray: 4
        },
        legend: {
          position: 'top',
          horizontalAlign: 'right',
          labels: {
            colors: '#94a3b8'
          }
        },
        tooltip: {
          theme: 'dark',
          y: {
            formatter: v => v + ' Siswa'
          }
        }
      });
      chartWeekly.render();


      /* ═══════════════════════════════════════════════
         QR SCANNER LOGIC
      ═══════════════════════════════════════════════ */
      let stream = null;
      let scannerActive = false;
      let isProcessing = false;
      let lastScanData = '';
      let lastScanTime = 0;
      const DEBOUNCE = 4000;

      const btnStart = document.getElementById('btn-start-scanner');
      const plhCam = document.getElementById('cam-placeholder');
      const actCam = document.getElementById('cam-active');
      const feedbackEl = document.getElementById('scan-feedback');

      const video = document.createElement('video');
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d', {
        willReadFrequently: true
      });

      video.setAttribute('playsinline', 'true');
      video.setAttribute('muted', 'true');
      video.style.cssText = 'width:100%;height:100%;object-fit:cover;';
      document.getElementById('reader').appendChild(video);

      async function startScanner() {
        try {
          stream = await navigator.mediaDevices.getUserMedia({
            video: {
              facingMode: {
                ideal: 'environment'
              },
              width: 640,
              height: 480
            }
          });
          video.srcObject = stream;
          await video.play();
          plhCam.classList.add('d-none');
          actCam.classList.remove('d-none');
          scannerActive = true;
          requestAnimationFrame(tick);
        } catch (err) {
          console.error('Camera error:', err);
          alert('Gagal mengakses kamera. Pastikan izin kamera telah diberikan.');
        }
      }

      function tick() {
        if (!scannerActive) return;
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;
          ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
          const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
          const code = jsQR(imageData.data, imageData.width, imageData.height);
          if (code && !isProcessing) {
            const now = Date.now();
            if (code.data !== lastScanData || now - lastScanTime > DEBOUNCE) {
              processScan(code.data);
              lastScanData = code.data;
              lastScanTime = now;
            }
          }
        }
        requestAnimationFrame(tick);
      }

      async function processScan(qrContent) {
        isProcessing = true;
        if (document.getElementById('toggleSound').checked) {
          try {
            const ac = new AudioContext();
            const osc = ac.createOscillator();
            const g = ac.createGain();
            osc.connect(g);
            g.connect(ac.destination);
            g.gain.value = 0.1;
            osc.frequency.value = 800;
            osc.type = 'sine';
            osc.start();
            setTimeout(() => osc.stop(), 100);
          } catch (_) {}
        }
        try {
          const res = await fetch("{{ route('admin.dashboard.scan-qr') }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              qr_code: qrContent
            })
          });
          const result = await res.json();
          showFeedback(result);
          if (result.success) refreshDashboardData();
        } catch (e) {
          console.error('Scan error:', e);
        } finally {
          setTimeout(() => {
            isProcessing = false;
          }, 1000);
        }
      }

      function showFeedback(result) {
        const icon = document.getElementById('feedback-icon');
        const status = document.getElementById('feedback-status');
        const name = document.getElementById('feedback-name');
        const kelas = document.getElementById('feedback-kelas');
        const time = document.getElementById('feedback-time');

        if (result.success) {
          icon.textContent = '✅';
          status.textContent = 'BERHASIL';
          status.style.color = '#28c76f';
        } else if (result.already) {
          icon.textContent = '⚠️';
          status.textContent = 'SUDAH ABSEN';
          status.style.color = '#ff9f43';
        } else {
          icon.textContent = '❌';
          status.textContent = 'GAGAL';
          status.style.color = '#ea5455';
          name.textContent = 'QR Code Tidak Dikenal';
          kelas.textContent = '-';
          time.textContent = '--:--:--';
        }

        if (result.siswa) {
          name.textContent = result.siswa.nama;
          kelas.textContent = 'Kelas ' + result.siswa.kelas;
          time.textContent = result.siswa.jam;
        }

        feedbackEl.classList.remove('d-none');
        feedbackEl.classList.add('d-flex');
        setTimeout(() => {
          feedbackEl.classList.add('d-none');
          feedbackEl.classList.remove('d-flex');
        }, 3000);
      }

      /* ── REFRESH DASHBOARD ── */
      window.refreshDashboardData = async function() {
        try {
          const resp = await fetch("{{ route('admin.dashboard.refresh-stats') }}");
          const data = await resp.json();

          // Counters
          document.querySelectorAll('.counter-value').forEach(el => {
            const lbl = el.nextElementSibling?.innerText?.trim() || '';
            if (lbl.includes('SISWA')) el.innerText = data.totalSiswa;
            if (lbl.includes('GURU')) el.innerText = data.totalGuru;
            if (lbl.includes('STAFF')) el.innerText = data.totalStaff;
            if (lbl.includes('KELAS')) el.innerText = data.totalKelas;
          });

          // Charts
          chartDonut.updateSeries([data.hadirCount, data.sakitCount, data.izinCount, data.alphaCount, data
            .terlambatCount
          ]);
          chartWeekly.updateSeries([{
              name: 'Hadir',
              data: data.chartHadir
            },
            {
              name: 'Sakit',
              data: data.chartSakit
            },
            {
              name: 'Izin',
              data: data.chartIzin
            },
            {
              name: 'Alpha',
              data: data.chartAlpha
            }
          ]);

          // Tables
          updateTable('table-earliest', data.palingAwal, true);
          updateMiniLog(data.palingAkhir);
        } catch (e) {
          console.error('Refresh error:', e);
        }
      };

      function updateTable(id, list, isEarliest) {
        const tbody = document.querySelector('#' + id + ' tbody');
        if (!list || list.length === 0) {
          tbody.innerHTML = '<tr><td colspan="5" class="das-table__empty">Belum ada data.</td></tr>';
          return;
        }
        const icons = ['🥇', '🥈', '🥉'];
        tbody.innerHTML = list.map((abs, i) => `
        <tr class="${isEarliest && i < 3 ? 'das-table__row--highlight' : ''}">
          <td class="text-center ${isEarliest ? 'fs-5' : 'text-muted'}">${isEarliest ? (icons[i] ?? i+1) : i+1}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(abs.siswa.nama_lengkap)}&background=7367f0&color=fff"
                   class="das-avatar" width="30">
              <span class="fw-semibold">${abs.siswa.nama_lengkap}</span>
            </div>
          </td>
          <td><span class="das-chip --info">${abs.siswa.kelas?.nama || '-'}</span></td>
          <td class="text-center font-monospace fw-bold">${abs.jam_masuk}</td>
          <td class="text-center"><span class="das-chip --success">Hadir</span></td>
        </tr>`).join('');
      }

      function updateMiniLog(list) {
        const cont = document.querySelector('.das-mini-log__list');
        if (!list || list.length === 0) return;
        cont.innerHTML = list.map(abs => `
        <div class="das-mini-log__item">
          <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(abs.siswa.nama_lengkap)}&background=7367f0&color=fff"
               class="das-avatar" width="32">
          <div class="das-mini-log__info">
            <div class="das-mini-log__name">${abs.siswa.nama_lengkap}</div>
            <div class="das-mini-log__meta">${abs.siswa.kelas?.nama || '-'} · ${abs.jam_masuk}</div>
          </div>
          <span class="das-chip --success das-chip--xs">Presensi</span>
        </div>`).join('');
      }

      /* ── EVENT LISTENERS ── */
      btnStart.addEventListener('click', startScanner);

      document.getElementById('btn-switch-cam').addEventListener('click', async () => {
        if (stream) stream.getTracks().forEach(t => t.stop());
        startScanner();
      });

    }); // end DOMContentLoaded
  </script>
@endsection