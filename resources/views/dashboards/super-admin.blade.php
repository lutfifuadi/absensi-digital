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
            <img src="{{ asset('uploads/logo/' . $pengaturanArr['logo_sekolah']) }}" alt="Logo" class="das-hero__logo">
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

      {{-- RINGKASAN HARI INI --}}
      <div class="das-panel mb-4" data-skeleton>
        <div class="das-skeleton" style="height: 380px; border-radius: var(--das-radius);"></div>
        <div class="das-panel__content">
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
      </div>

      {{-- TREN 7 HARI --}}
      <div class="das-panel mb-4" data-skeleton>
        <div class="das-skeleton" style="height: 340px; border-radius: var(--das-radius);"></div>
        <div class="das-panel__content">
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
      </div>

      {{-- SISWA PALING AWAL --}}
      <div class="das-panel" data-skeleton>
        <div class="das-skeleton" style="height: 400px; border-radius: var(--das-radius);"></div>
        <div class="das-panel__content">
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
      </div>

    </div>{{-- /das-col-left --}}


    {{-- ─────────────────────────────
         KOLOM KANAN
    ───────────────────────────── --}}
    <div class="das-col-right">

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
      <div class="das-attendance-mini mb-4">
        <div class="das-attendance-mini__item">
          <div class="das-attendance-mini__head">
            <div class="das-attendance-mini__icon --success">
              <i class="ti tabler-chalkboard-teacher"></i>
            </div>
            <div>
              <div class="das-attendance-mini__val">{{ $absensiGuruHariIni }} <span class="das-attendance-mini__total">/ {{ $totalGuru }}</span></div>
              <div class="das-attendance-mini__label">Guru Hadir</div>
            </div>
          </div>
          <div class="das-attendance-mini__progress">
            <div class="das-attendance-mini__progress-bar --success" style="width: {{ $totalGuru > 0 ? ($absensiGuruHariIni / $totalGuru) * 100 : 0 }}%"></div>
          </div>
        </div>
        
        <div class="das-attendance-mini__divider"></div>
        
        <div class="das-attendance-mini__item">
          <div class="das-attendance-mini__head">
            <div class="das-attendance-mini__icon --info">
              <i class="ti tabler-user-check"></i>
            </div>
            <div>
              <div class="das-attendance-mini__val">{{ $absensiStaffHariIni }} <span class="das-attendance-mini__total">/ {{ $totalStaff }}</span></div>
              <div class="das-attendance-mini__label">Staff TU Hadir</div>
            </div>
          </div>
          <div class="das-attendance-mini__progress">
            <div class="das-attendance-mini__progress-bar --info" style="width: {{ $totalStaff > 0 ? ($absensiStaffHariIni / $totalStaff) * 100 : 0 }}%"></div>
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
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=2.0">
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

  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {

      /* ── SKELETON LOADING ── */
      setTimeout(() => {
        document.querySelectorAll('[data-skeleton]').forEach(el => {
          el.classList.add('--loaded');
        });
      }, 350);

      /* ── COUNTER ANIMATION (requestAnimationFrame) ── */
      document.querySelectorAll('.counter-value').forEach(counter => {
        const target = +counter.getAttribute('data-target');
        if (!target || target === 0) { counter.innerText = target || 0; return; }
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

      /* ── REFRESH DASHBOARD ── */
      window.refreshDashboardData = async function() {
        try {
          const resp = await fetch("{{ route('admin.dashboard.refresh-stats') }}");
          const data = await resp.json();

          // Counters — re-animate
          document.querySelectorAll('.counter-value').forEach(el => {
            const target = parseInt(el.getAttribute('data-target'));
            animateCounter(el, target, 600);
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

          // Table
          updateTable('table-earliest', data.palingAwal, true);
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

      /* ── SCROLL TO TOP ── */
      const scrollBtn = document.createElement('button');
      scrollBtn.innerHTML = '<i class="ti tabler-arrow-up"></i>';
      scrollBtn.className = 'das-scroll-top';
      scrollBtn.setAttribute('aria-label', 'Scroll to top');
      document.body.appendChild(scrollBtn);

      window.addEventListener('scroll', () => {
        scrollBtn.classList.toggle('--visible', window.scrollY > 400);
      });

      scrollBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });

    }); // end DOMContentLoaded
  </script>
@endsection