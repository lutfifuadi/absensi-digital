@extends('layouts/layoutMaster')

@section('title', 'Dashboard Admin Sekolah')

@push('page-css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  /* ── CSS Variables ── */
  :root {
    --accent-blue:    #3b82f6;
    --accent-cyan:    #06b6d4;
    --accent-green:   #10b981;
    --accent-orange:  #f59e0b;
    --accent-red:     #ef4444;
    --glass-bg:       rgba(255,255,255,0.04);
    --glass-border:   rgba(255,255,255,0.08);
    --glass-hover:    rgba(255,255,255,0.07);
    --text-primary:   #f1f5f9;
    --text-muted:     rgba(241,245,249,0.45);
    --radius-card:    16px;
    --radius-sm:      10px;
    --shadow-card:    0 4px 24px rgba(0,0,0,0.25);
    --shadow-hover:   0 12px 32px rgba(0,0,0,0.35);
    --font-body:      'Plus Jakarta Sans', sans-serif;
  }

  /* ── Base ── */
  body, .card, .card-body { font-family: var(--font-body) !important; }

  /* ── Layout Container ── */
  .dash-wrapper {
    padding: clamp(12px, 3vw, 28px);
    max-width: 1400px;
    margin: 0 auto;
  }

  /* ── Glass Card ── */
  .glass-card {
    background: var(--glass-bg) !important;
    border: 1px solid var(--glass-border) !important;
    border-radius: var(--radius-card) !important;
    box-shadow: var(--shadow-card) !important;
    transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
    overflow: hidden;
  }
  .glass-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover) !important;
    background: var(--glass-hover) !important;
  }
  .glass-card .card-header {
    background: transparent !important;
    border-bottom: 1px solid var(--glass-border) !important;
  }

  /* ── Hero Banner ── */
  .hero-banner {
    background: linear-gradient(135deg, #1d4ed8 0%, #0891b2 55%, #0e7490 100%);
    border-radius: var(--radius-card);
    padding: clamp(20px, 4vw, 40px) clamp(20px, 4vw, 40px);
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(14,116,144,0.4);
  }
  .hero-banner::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 240px; height: 240px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
    pointer-events: none;
  }
  .hero-banner::after {
    content: '';
    position: absolute;
    bottom: -80px; left: 30%;
    width: 320px; height: 320px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
    pointer-events: none;
  }
  .hero-icon-wrap {
    width: clamp(48px, 8vw, 72px);
    height: clamp(48px, 8vw, 72px);
    border-radius: 18px;
    background: rgba(255,255,255,0.18);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.28);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
  }
  .hero-icon-wrap i { font-size: clamp(1.4rem, 3vw, 2rem); color: #fff; }
  .hero-title {
    font-size: clamp(1.05rem, 2.5vw, 1.4rem);
    font-weight: 800;
    letter-spacing: -0.5px;
    color: #fff;
    margin: 0;
  }
  .hero-subtitle {
    font-size: clamp(0.9rem, 2vw, 1.1rem);
    color: rgba(255,255,255,0.85);
    font-weight: 600;
    margin: 2px 0 0;
  }
  .hero-desc {
    font-size: clamp(0.75rem, 1.5vw, 0.875rem);
    color: rgba(255,255,255,0.65);
    margin: 0;
    max-width: 480px;
    line-height: 1.55;
  }
  .hero-date-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(0,0,0,0.22);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 50px;
    padding: 6px 14px;
    font-size: 0.75rem;
    font-weight: 600;
    color: rgba(255,255,255,0.9);
    backdrop-filter: blur(8px);
    white-space: nowrap;
  }

  /* ── Stat Cards ── */
  .stat-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: clamp(10px, 2vw, 18px);
  }
  @media (min-width: 768px) {
    .stat-grid { grid-template-columns: repeat(4, 1fr); }
  }
  .stat-card {
    padding: clamp(16px, 2.5vw, 28px) clamp(14px, 2vw, 20px);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
  }
  .stat-pill {
    width: clamp(40px, 6vw, 52px);
    height: clamp(40px, 6vw, 52px);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: clamp(1.1rem, 2vw, 1.35rem);
    flex-shrink: 0;
  }
  .stat-value {
    font-size: clamp(1.6rem, 4vw, 2.2rem);
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
    letter-spacing: -1px;
  }
  .stat-label {
    font-size: clamp(0.58rem, 1.2vw, 0.65rem);
    text-transform: uppercase;
    letter-spacing: 1.2px;
    font-weight: 700;
    color: var(--text-muted);
  }

  /* ── Chart Section ── */
  .chart-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: clamp(10px, 2vw, 18px);
  }
  @media (min-width: 768px) {
    .chart-grid { grid-template-columns: 340px 1fr; }
  }
  @media (min-width: 1200px) {
    .chart-grid { grid-template-columns: 360px 1fr; }
  }
  .chart-card-header {
    padding: clamp(14px, 2vw, 20px) clamp(16px, 2.5vw, 24px);
  }
  .chart-card-body {
    padding: clamp(10px, 2vw, 16px) clamp(16px, 2.5vw, 24px) clamp(16px, 2.5vw, 24px);
  }
  .chart-title {
    font-size: clamp(0.8rem, 1.5vw, 0.9rem);
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex; align-items: center; gap: 8px;
  }
  .chart-sub {
    font-size: 0.72rem;
    color: var(--text-muted);
    margin: 4px 0 0;
  }
  .donut-empty {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: clamp(30px, 6vw, 60px) 20px;
    gap: 10px;
    color: var(--text-muted);
    font-size: 0.82rem;
  }

  /* ── Quick Action Cards ── */
  .section-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 1.8px;
    font-weight: 700;
    color: var(--text-muted);
    margin-bottom: clamp(10px, 2vw, 16px);
    padding-left: 2px;
  }
  .action-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: clamp(10px, 2vw, 18px);
  }
  @media (min-width: 992px) {
    .action-grid { grid-template-columns: repeat(4, 1fr); }
  }
  .action-card-body {
    padding: clamp(16px, 2.5vw, 24px);
    display: flex;
    flex-direction: column;
    height: 100%;
  }
  .action-avatar {
    width: clamp(40px, 5vw, 48px);
    height: clamp(40px, 5vw, 48px);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .action-avatar i { font-size: clamp(1.1rem, 2vw, 1.35rem); }
  .action-title {
    font-size: clamp(0.82rem, 1.5vw, 0.95rem);
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
  }
  .action-desc {
    font-size: clamp(0.72rem, 1.2vw, 0.8rem);
    color: var(--text-muted);
    flex-grow: 1;
    margin: 0;
    line-height: 1.5;
  }
  .action-btn {
    border-radius: 10px !important;
    padding: clamp(8px, 1.5vw, 10px) 16px !important;
    font-size: clamp(0.75rem, 1.2vw, 0.82rem) !important;
    font-weight: 700 !important;
    margin-top: auto;
    transition: all 0.2s ease !important;
    display: flex; align-items: center; justify-content: center; gap: 4px;
  }

  /* ── Mobile Specific Tweaks ── */
  @media (max-width: 576px) {
    .stat-card { padding: 12px 10px; }
    .stat-value { font-size: 1.5rem; }
    .stat-pill { width: 38px; height: 38px; border-radius: 10px; font-size: 1rem; }
    .hero-banner { padding: 20px; }
    .action-card-body { padding: 16px; gap: 8px !important; }
    .action-avatar { width: 36px; height: 36px; border-radius: 8px; }
    .action-title { font-size: 0.85rem; }
  }

  /* ── Stagger Animations ── */
  @keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .anim { animation: fadeSlideUp 0.45s ease both; }
  .anim-d1 { animation-delay: 0.05s; }
  .anim-d2 { animation-delay: 0.10s; }
  .anim-d3 { animation-delay: 0.15s; }
  .anim-d4 { animation-delay: 0.20s; }
  .anim-d5 { animation-delay: 0.25s; }
  .anim-d6 { animation-delay: 0.30s; }

  /* ── Apex Dark Tooltip ── */
  .apexcharts-tooltip {
    background: #1e2235 !important;
    border-color: var(--glass-border) !important;
    color: #fff !important;
    border-radius: 10px !important;
    box-shadow: 0 8px 24px rgba(0,0,0,0.3) !important;
  }
  .apexcharts-tooltip-title {
    background: rgba(255,255,255,0.06) !important;
    border-bottom-color: var(--glass-border) !important;
  }

  /* ── Divider spacing ── */
  .section-gap { margin-bottom: clamp(16px, 3vw, 28px); }
</style>
@endpush

@section('content')
@php
  $izinPending    = $totalIzinPending;
  $absensiHariIni = $totalAbsensiHariIni;

  $statCards = [
    ['icon'=>'tabler-users',       'color'=>'primary', 'value'=>$totalSiswa,   'label'=>'Total Siswa'],
    ['icon'=>'tabler-id-badge-2',  'color'=>'info',    'value'=>$totalGuru,    'label'=>'Total Guru'],
    ['icon'=>'tabler-door',        'color'=>'success', 'value'=>$totalKelas,   'label'=>'Total Kelas'],
    ['icon'=>'tabler-clock-pause', 'color'=>'danger',  'value'=>$izinPending,  'label'=>'Izin Menunggu'],
  ];

  $actionCards = [
    ['icon'=>'tabler-database',         'color'=>'primary', 'title'=>'Master Data',   'desc'=>'Tahun akademik, kelas, & profil.',  'route'=>route('admin.master-data')],
    ['icon'=>'tabler-calendar-check',   'color'=>'success', 'title'=>'Input Absensi', 'desc'=>'Input harian per kelas.',           'route'=>route('admin.absensi-siswa.index')],
    ['icon'=>'tabler-report-analytics', 'color'=>'info',    'title'=>'Laporan',        'desc'=>'Rekap bulanan & export.',           'route'=>route('admin.laporan.index')],
    ['icon'=>'tabler-file-heart',       'color'=>'danger',  'title'=>'Izin & Sakit',   'desc'=>'Tinjau pengajuan izin.',            'route'=>route('admin.izin-sakit.index')],
  ];
@endphp

<div class="dash-wrapper">

  {{-- ══ HERO BANNER ══ --}}
  <div class="hero-banner section-gap anim anim-d1">
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="hero-icon-wrap">
          <i class="ti tabler-school"></i>
        </div>
        <div>
          <h4 class="hero-title">Dashboard Admin Sekolah</h4>
          <p class="hero-subtitle">{{ $user->name }}</p>
        </div>
      </div>
      <div class="hero-date-badge">
        <i class="ti tabler-calendar-time" style="font-size:0.85rem;"></i>
        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
      </div>
    </div>
    <p class="hero-desc mt-3">
      Fokus pada operasional, monitoring kehadiran siswa secara real-time, dan manajemen laporan akademik sekolah.
    </p>
  </div>

  {{-- ══ STATS ══ --}}
  <div class="stat-grid section-gap">
    @foreach($statCards as $i => $s)
    <div class="glass-card anim anim-d{{ $i + 2 }}">
      <div class="stat-card">
        <div class="stat-pill bg-label-{{ $s['color'] }}">
          <i class="ti {{ $s['icon'] }}"></i>
        </div>
        <div class="stat-value">{{ $s['value'] }}</div>
        <div class="stat-label">{{ $s['label'] }}</div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- ══ CHARTS ══ --}}
  <div class="chart-grid section-gap">

    {{-- Donut --}}
    <div class="glass-card anim anim-d2 d-flex flex-column">
      <div class="chart-card-header">
        <p class="chart-title">
          <i class="ti tabler-chart-pie" style="color:var(--accent-green);"></i>
          Absensi Hari Ini
        </p>
        <p class="chart-sub">{{ $absensiHariIni }} dari {{ $totalSiswa }} siswa tercatat</p>
      </div>
      <div class="chart-card-body flex-grow-1 d-flex align-items-center justify-content-center">
        @if($absensiHariIni > 0)
          <div id="chartDonut" style="width:100%;"></div>
        @else
          <div class="donut-empty w-100">
            <i class="ti tabler-chart-donut-off" style="font-size:2.5rem;opacity:0.2;"></i>
            <span>Belum ada data absensi hari ini.</span>
          </div>
        @endif
      </div>
    </div>

    {{-- Bar Chart --}}
    <div class="glass-card anim anim-d3 d-flex flex-column">
      <div class="chart-card-header d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
        <div>
          <p class="chart-title">
            <i class="ti tabler-chart-bar" style="color:var(--accent-cyan);"></i>
            Tren Kehadiran 7 Hari Terakhir
          </p>
        </div>
        <a href="{{ route('admin.laporan.index') }}" class="btn btn-xs btn-label-info fw-bold flex-shrink-0">
          <i class="ti tabler-external-link me-1"></i> Rincian
        </a>
      </div>
      <div class="chart-card-body flex-grow-1">
        <div id="chartKehadiran" style="width:100%;"></div>
      </div>
    </div>

  </div>

  {{-- ══ QUICK ACTIONS ══ --}}
  <p class="section-label anim anim-d4">⚡ Akses Cepat Monitoring</p>
  <div class="action-grid anim anim-d5">
    @foreach($actionCards as $card)
    <div class="glass-card">
      <div class="action-card-body gap-3">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="action-avatar bg-label-{{ $card['color'] }}">
            <i class="ti {{ $card['icon'] }}"></i>
          </div>
          <h6 class="action-title">{{ $card['title'] }}</h6>
        </div>
        <p class="action-desc mb-3">{{ $card['desc'] }}</p>
        <a href="{{ $card['route'] }}" class="btn btn-label-{{ $card['color'] }} action-btn w-100">
          Buka Modul <i class="ti tabler-chevron-right" style="font-size:0.8rem;"></i>
        </a>
      </div>
    </div>
    @endforeach
  </div>

</div>
@endsection

@push('page-js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
/* ── Shared palette ── */
const C = {
  hadir:  '#10b981',
  sakit:  '#06b6d4',
  izin:   '#f59e0b',
  alpha:  '#ef4444',
  text:   '#e2e8f0',
  grid:   'rgba(255,255,255,0.07)',
};

/* ── Donut ── */
@if($absensiHariIni > 0)
@php $donutSeries = [$hadirCount, $sakitCount, $izinCount, $alphaCount]; @endphp
new ApexCharts(document.querySelector('#chartDonut'), {
  chart:  { type: 'donut', height: 270, background: 'transparent', sparkline:{enabled:false} },
  series: @json($donutSeries),
  labels: ['Hadir', 'Sakit', 'Izin', 'Alpha'],
  colors: [C.hadir, C.sakit, C.izin, C.alpha],
  theme:  { mode: 'dark' },
  stroke: { show: false },
  legend: {
    position: 'bottom',
    fontSize:  '12px',
    fontFamily: 'Plus Jakarta Sans, sans-serif',
    fontWeight: 600,
    labels:   { colors: C.text },
    markers:  { width: 8, height: 8, radius: 4 },
    itemMargin: { horizontal: 8 }
  },
  dataLabels: { enabled: false },
  plotOptions: {
    pie: {
      donut: {
        size: '72%',
        labels: {
          show:  true,
          total: {
            show:       true,
            label:      'Hadir',
            color:      C.text,
            fontFamily: 'Plus Jakarta Sans, sans-serif',
            fontWeight: 800,
            fontSize:   '15px',
            formatter:  () => '{{ $absensiHariIni }}'
          },
          value: {
            color:      C.text,
            fontFamily: 'Plus Jakarta Sans, sans-serif',
            fontWeight: 700,
            fontSize:   '22px',
          }
        }
      }
    }
  },
  tooltip: { theme: 'dark', style:{ fontFamily:'Plus Jakarta Sans, sans-serif' } },
}).render();
@endif

/* ── Bar Chart ── */
const barHeight = window.innerWidth < 576 ? 220 : 268;

new ApexCharts(document.querySelector('#chartKehadiran'), {
  chart: {
    type:       'bar',
    height:     barHeight,
    toolbar:    { show: false },
    fontFamily: 'Plus Jakarta Sans, sans-serif',
    background: 'transparent',
    animations: { enabled: true, speed: 600 }
  },
  theme: { mode: 'dark' },
  plotOptions: {
    bar: { borderRadius: 5, columnWidth: '52%', borderRadiusApplication: 'end' }
  },
  dataLabels: { enabled: false },
  series: [
    { name: 'Hadir', data: @json($chartHadir) },
    { name: 'Sakit', data: @json($chartSakit) },
    { name: 'Izin',  data: @json($chartIzin)  },
    { name: 'Alpha', data: @json($chartAlpha) }
  ],
  xaxis: {
    categories: @json($chartDays),
    axisBorder: { show: false },
    axisTicks:  { show: false },
    labels: {
      style: { colors: Array(7).fill(C.text), fontSize: '11px', fontWeight: 600 },
      rotate: -20,
      rotateAlways: false,
    }
  },
  yaxis: {
    labels: {
      style: { colors: C.text, fontSize: '11px' },
      formatter: v => v % 1 === 0 ? v : ''
    }
  },
  colors: [C.hadir, C.sakit, C.izin, C.alpha],
  grid: {
    borderColor: C.grid,
    xaxis: { lines: { show: false } },
    padding: { left: 0, right: 0 }
  },
  legend: {
    position:        'top',
    horizontalAlign: 'left',
    fontSize:        '12px',
    fontWeight:      600,
    labels:          { colors: C.text },
    markers:         { width: 8, height: 8, radius: 4 },
    itemMargin:      { horizontal: 8 }
  },
  tooltip: {
    theme: 'dark',
    style: { fontFamily: 'Plus Jakarta Sans, sans-serif' },
    y:     { formatter: val => val + ' siswa' }
  }
}).render();
</script>
@endpush