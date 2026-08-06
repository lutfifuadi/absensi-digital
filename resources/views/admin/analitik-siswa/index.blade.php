@extends('layouts/layoutMaster')

@section('title', 'Grafik & Analitik Kehadiran Siswa')

@section('page-style')
<style>
  /* ── Stat Cards ── */
  .analitik-kpi {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 5px;
    padding: 1.25rem 1.5rem;
    transition: transform .2s, box-shadow .2s;
    height: 100%;
  }
  .analitik-kpi:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 32px rgba(0,0,0,0.25);
  }
  .analitik-kpi__label   { font-size: .75rem; font-weight: 700; letter-spacing: .08em; color: rgba(255,255,255,.45); text-transform: uppercase; margin-bottom: .45rem; }
  .analitik-kpi__value   { font-size: 2rem; font-weight: 800; line-height: 1; color: #fff; }
  .analitik-kpi__sub     { font-size: .78rem; color: rgba(255,255,255,.45); margin-top: .35rem; }
  .analitik-kpi__icon    { width: 42px; height: 42px; border-radius: 5px; display:flex; align-items:center; justify-content:center; font-size:1.35rem; }
  .analitik-kpi__prog    { height: 5px; border-radius: 5px; background: rgba(255,255,255,.08); margin-top: .75rem; overflow:hidden; }
  .analitik-kpi__prog-bar{ height:100%; border-radius:5px; transition: width .6s ease; }

  /* ── Chart Cards ── */
  .analitik-chart-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 5px;
    overflow: hidden;
    height: 100%;
  }
  .analitik-chart-card__head {
    padding: .9rem 1.25rem;
    border-bottom: 1px solid rgba(255,255,255,.07);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
  }
  .analitik-chart-card__title {
    font-size: .875rem;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: .45rem;
    margin: 0;
  }
  .analitik-chart-card__sub { font-size: .72rem; color: rgba(255,255,255,.4); margin-top: 1px; }
  .analitik-chart-card__body { padding: 1.25rem; }

  /* ── Table ── */
  .analitik-table th {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: rgba(255,255,255,.4);
    border-bottom: 1px solid rgba(255,255,255,.07) !important;
    background: transparent !important;
    padding: .65rem 1rem;
  }
  .analitik-table td {
    color: rgba(255,255,255,.85);
    border-bottom: 1px solid rgba(255,255,255,.05) !important;
    padding: .7rem 1rem;
    vertical-align: middle;
  }
  .analitik-table tbody tr:last-child td { border-bottom: none !important; }
  .analitik-table tbody tr:hover td { background: rgba(255,255,255,.03); }

  /* ── Loader Overlay ── */
  .analitik-loader {
    position: absolute; inset: 0;
    background: rgba(15,23,42,.65);
    backdrop-filter: blur(3px);
    z-index: 10;
    display: flex; align-items: center; justify-content: center;
    border-radius: 5px;
  }

  /* ── Filter panel ── */
  .analitik-filter-label {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: rgba(255,255,255,.45);
    margin-bottom: .35rem;
  }
</style>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════ --}}
{{-- HERO HEADER                                     --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder">
          <i class="ti tabler-chart-dots"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Analitik & Visualisasi Data
        </div>
        <h4 class="das-hero__title text-gradient-gold">Grafik Kehadiran Siswa</h4>
        <p class="das-hero__subtitle">Visualisasi komprehensif tren kehadiran, keterlambatan, dan pola kedisiplinan siswa.</p>
      </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <button id="btn-refresh" class="das-btn das-btn--secondary">
        <i class="ti tabler-refresh me-1"></i> Refresh
      </button>
      <button onclick="window.print()" class="das-btn das-btn--info">
        <i class="ti tabler-printer me-1"></i> Cetak
      </button>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- PANEL FILTER                                    --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="das-panel mb-4">
  <div class="das-panel__head">
    <div class="das-panel__title">
      <span class="das-panel__icon-dot --primary"></span>
      Filter Data
    </div>
  </div>
  <div class="das-panel__body">
    <form id="analitik-filter-form" class="row gy-3 gx-2 align-items-end">

      <div class="col-12 col-sm-6 col-md-3">
        <div class="analitik-filter-label">Rentang Waktu</div>
        <select id="filter-preset" class="form-select form-select-sm"
          style="background:rgba(15,23,42,.6);color:#fff;border:1px solid rgba(255,255,255,.12);">
          <option value="30_days" selected>30 Hari Terakhir</option>
          <option value="7_days">7 Hari Terakhir</option>
          <option value="this_month">Bulan Ini</option>
          <option value="custom">Custom Tanggal</option>
        </select>
      </div>

      <div class="col-12 col-sm-6 col-md-3 d-none" id="custom-date-wrap">
        <div class="analitik-filter-label">Dari → Sampai</div>
        <div class="input-group input-group-sm">
          <input type="date" id="filter-start" class="form-control"
            style="background:rgba(15,23,42,.6);color:#fff;border:1px solid rgba(255,255,255,.12);"
            value="{{ \Carbon\Carbon::now()->subDays(29)->format('Y-m-d') }}">
          <span class="input-group-text" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.12);color:rgba(255,255,255,.5);">–</span>
          <input type="date" id="filter-end" class="form-control"
            style="background:rgba(15,23,42,.6);color:#fff;border:1px solid rgba(255,255,255,.12);"
            value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
        </div>
      </div>

      <div class="col-6 col-sm-4 col-md-2">
        <div class="analitik-filter-label">Tingkat</div>
        <select id="filter-tingkat" class="form-select form-select-sm"
          style="background:rgba(15,23,42,.6);color:#fff;border:1px solid rgba(255,255,255,.12);">
          <option value="all">Semua</option>
          <option value="10">Kelas X</option>
          <option value="11">Kelas XI</option>
          <option value="12">Kelas XII</option>
        </select>
      </div>

      <div class="col-6 col-sm-4 col-md-2">
        <div class="analitik-filter-label">Kelas</div>
        <select id="filter-kelas" class="form-select form-select-sm"
          style="background:rgba(15,23,42,.6);color:#fff;border:1px solid rgba(255,255,255,.12);">
          <option value="all">Semua Kelas</option>
          @foreach($kelases as $kls)
            <option value="{{ $kls->id }}">{{ $kls->nama }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-6 col-sm-4 col-md-2">
        <div class="analitik-filter-label">Jurusan</div>
        <select id="filter-jurusan" class="form-select form-select-sm"
          style="background:rgba(15,23,42,.6);color:#fff;border:1px solid rgba(255,255,255,.12);">
          <option value="all">Semua Jurusan</option>
          @foreach($jurusans as $jrs)
            <option value="{{ $jrs->id }}">{{ $jrs->nama }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-12 col-sm-12 col-md-2">
        <button type="submit" class="das-btn das-btn--info w-100">
          <i class="ti tabler-filter me-1"></i> Terapkan
        </button>
      </div>
    </form>

    <div id="periode-badge" class="mt-3 text-white-50 small">
      <i class="ti tabler-calendar me-1"></i> Memuat periode…
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- KPI SUMMARY CARDS                               --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
  {{-- Tingkat Kehadiran --}}
  <div class="col-6 col-lg-3">
    <div class="analitik-kpi">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="analitik-kpi__label">Tingkat Kehadiran</div>
        <div class="analitik-kpi__icon" style="background:rgba(40,199,111,.15);color:#28c76f;">
          <i class="ti tabler-circle-check"></i>
        </div>
      </div>
      <div class="analitik-kpi__value" id="kpi-pct-hadir">—</div>
      <div class="analitik-kpi__sub"><span id="kpi-count-hadir" class="text-success fw-bold">0</span> hadir dari <span id="kpi-total">0</span> presensi</div>
      <div class="analitik-kpi__prog"><div class="analitik-kpi__prog-bar bg-success" id="kpi-prog-hadir" style="width:0%"></div></div>
    </div>
  </div>

  {{-- Ketepatan Waktu --}}
  <div class="col-6 col-lg-3">
    <div class="analitik-kpi">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="analitik-kpi__label">Tepat Waktu</div>
        <div class="analitik-kpi__icon" style="background:rgba(255,159,67,.15);color:#ff9f43;">
          <i class="ti tabler-clock-check"></i>
        </div>
      </div>
      <div class="analitik-kpi__value" id="kpi-pct-tepat">—</div>
      <div class="analitik-kpi__sub"><span id="kpi-count-terlambat" class="text-warning fw-bold">0</span> terlambat</div>
      <div class="analitik-kpi__prog"><div class="analitik-kpi__prog-bar" id="kpi-prog-tepat" style="width:0%;background:#ff9f43;"></div></div>
    </div>
  </div>

  {{-- Izin & Sakit --}}
  <div class="col-6 col-lg-3">
    <div class="analitik-kpi">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="analitik-kpi__label">Izin & Sakit</div>
        <div class="analitik-kpi__icon" style="background:rgba(0,207,232,.15);color:#00cfe8;">
          <i class="ti tabler-file-text"></i>
        </div>
      </div>
      <div class="analitik-kpi__value" id="kpi-count-izin-sakit">—</div>
      <div class="analitik-kpi__sub">Surat keterangan masuk</div>
    </div>
  </div>

  {{-- Alpha --}}
  <div class="col-6 col-lg-3">
    <div class="analitik-kpi">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="analitik-kpi__label">Alpha / Tanpa Ket.</div>
        <div class="analitik-kpi__icon" style="background:rgba(234,84,85,.15);color:#ea5455;">
          <i class="ti tabler-alert-triangle"></i>
        </div>
      </div>
      <div class="analitik-kpi__value text-danger" id="kpi-count-alpha">—</div>
      <div class="analitik-kpi__sub">Peak scan: <strong class="text-white" id="kpi-peak">-</strong></div>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- CHART ROW 1 – Tren & Donut                      --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="row g-3 mb-3">
  {{-- Chart 1: Tren Harian --}}
  <div class="col-12 col-xl-8">
    <div class="analitik-chart-card position-relative">
      <div class="analitik-loader d-none" id="loader-trend"></div>
      <div class="analitik-chart-card__head">
        <div>
          <div class="analitik-chart-card__title">
            <i class="ti tabler-chart-line text-primary"></i> Tren Kehadiran Harian
          </div>
          <div class="analitik-chart-card__sub">Fluktuasi status presensi per hari pada periode terpilih</div>
        </div>
      </div>
      <div class="analitik-chart-card__body">
        <div id="chart-trend" style="min-height:320px;"></div>
      </div>
    </div>
  </div>

  {{-- Chart 2: Donut Proporsi --}}
  <div class="col-12 col-xl-4">
    <div class="analitik-chart-card position-relative">
      <div class="analitik-loader d-none" id="loader-donut"></div>
      <div class="analitik-chart-card__head">
        <div>
          <div class="analitik-chart-card__title">
            <i class="ti tabler-chart-donut text-primary"></i> Proporsi Status
          </div>
          <div class="analitik-chart-card__sub">Perbandingan seluruh status kehadiran</div>
        </div>
      </div>
      <div class="analitik-chart-card__body d-flex align-items-center justify-content-center">
        <div id="chart-donut" class="w-100" style="min-height:320px;"></div>
      </div>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- CHART ROW 2 – Bar Kelas & Peak Hour             --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="row g-3 mb-3">
  {{-- Chart 3: Perbandingan Kelas --}}
  <div class="col-12 col-xl-6">
    <div class="analitik-chart-card position-relative">
      <div class="analitik-loader d-none" id="loader-bar"></div>
      <div class="analitik-chart-card__head">
        <div>
          <div class="analitik-chart-card__title">
            <i class="ti tabler-chart-bar text-primary"></i> Perbandingan per Kelas
          </div>
          <div class="analitik-chart-card__sub">Top 10 kelas berdasarkan volume presensi</div>
        </div>
      </div>
      <div class="analitik-chart-card__body">
        <div id="chart-bar" style="min-height:320px;"></div>
      </div>
    </div>
  </div>

  {{-- Chart 4: Sebaran Jam --}}
  <div class="col-12 col-xl-6">
    <div class="analitik-chart-card position-relative">
      <div class="analitik-loader d-none" id="loader-jam"></div>
      <div class="analitik-chart-card__head">
        <div>
          <div class="analitik-chart-card__title">
            <i class="ti tabler-clock-play text-primary"></i> Sebaran Waktu Kedatangan
          </div>
          <div class="analitik-chart-card__sub">Distribusi jam siswa melakukan scan presensi</div>
        </div>
      </div>
      <div class="analitik-chart-card__body">
        <div id="chart-jam" style="min-height:320px;"></div>
      </div>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- CHART ROW 3 – Radar & Ranking                   --}}
{{-- ═══════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
  {{-- Chart 5: Radar Pola Hari --}}
  <div class="col-12 col-xl-5">
    <div class="analitik-chart-card position-relative">
      <div class="analitik-loader d-none" id="loader-radar"></div>
      <div class="analitik-chart-card__head">
        <div>
          <div class="analitik-chart-card__title">
            <i class="ti tabler-radar text-primary"></i> Pola Kedisiplinan per Hari
          </div>
          <div class="analitik-chart-card__sub">Senin – Sabtu: hari mana paling rawan alpa/terlambat?</div>
        </div>
      </div>
      <div class="analitik-chart-card__body d-flex align-items-center justify-content-center">
        <div id="chart-radar" class="w-100" style="min-height:320px;"></div>
      </div>
    </div>
  </div>

  {{-- Tabel Ranking Siswa Bermasalah --}}
  <div class="col-12 col-xl-7">
    <div class="analitik-chart-card position-relative">
      <div class="analitik-loader d-none" id="loader-ranking"></div>
      <div class="analitik-chart-card__head">
        <div>
          <div class="analitik-chart-card__title" style="color:#ea5455;">
            <i class="ti tabler-user-exclamation"></i> Siswa Perlu Perhatian BK / Wali Kelas
          </div>
          <div class="analitik-chart-card__sub">Top 5 siswa dengan akumulasi alpha & terlambat tertinggi</div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table analitik-table mb-0">
          <thead>
            <tr>
              <th>Siswa</th>
              <th>Kelas</th>
              <th class="text-center">Terlambat</th>
              <th class="text-center">Alpha</th>
              <th class="text-center">Total</th>
            </tr>
          </thead>
          <tbody id="ranking-body">
            <tr>
              <td colspan="5" class="text-center py-4 text-white-50">
                <div class="spinner-border spinner-border-sm me-2"></div> Memuat data…
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection

@push('page-script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>
<script>
(function () {
  'use strict';

  // ── Palette selaras Vuexy dark ──────────────────────────────────────────
  const C = {
    hadir:     '#28c76f',
    terlambat: '#ff9f43',
    izin_sakit:'#00cfe8',
    alpha:     '#ea5455',
    purple:    '#7367f0',
    grid:      'rgba(255,255,255,0.06)',
    text:      'rgba(255,255,255,0.55)',
    labelText: '#fff',
  };

  // ── Chart instances ──────────────────────────────────────────────────────
  let chartTrend, chartDonut, chartBar, chartJam, chartRadar;

  // ── Helpers ──────────────────────────────────────────────────────────────
  function showLoaders() {
    document.querySelectorAll('.analitik-loader').forEach(el => el.classList.remove('d-none'));
  }
  function hideLoaders() {
    document.querySelectorAll('.analitik-loader').forEach(el => el.classList.add('d-none'));
  }

  function fmt(n) {
    return Number(n).toLocaleString('id-ID');
  }

  // ── Preset → tanggal ────────────────────────────────────────────────────
  function getDates() {
    const preset = document.getElementById('filter-preset').value;
    const today  = new Date();
    const pad    = d => d.toISOString().split('T')[0];

    if (preset === '7_days') {
      const s = new Date(); s.setDate(today.getDate() - 6);
      return { start: pad(s), end: pad(today) };
    }
    if (preset === 'this_month') {
      const s = new Date(today.getFullYear(), today.getMonth(), 1);
      return { start: pad(s), end: pad(today) };
    }
    if (preset === 'custom') {
      return {
        start: document.getElementById('filter-start').value,
        end  : document.getElementById('filter-end').value,
      };
    }
    // default 30_days
    const s = new Date(); s.setDate(today.getDate() - 29);
    return { start: pad(s), end: pad(today) };
  }

  // ── Init Charts (sekali, render saat DOMContentLoaded) ──────────────────
  function initCharts() {
    const baseChart = {
      background: 'transparent',
      toolbar   : { show: false },
      fontFamily: 'inherit',
    };
    const baseTheme = { mode: 'dark' };

    // ── Tren Harian (Area) ──────────────────────────────────────────────
    chartTrend = new ApexCharts(document.querySelector('#chart-trend'), {
      chart  : { ...baseChart, type: 'area', height: 320, zoom: { enabled: false } },
      theme  : baseTheme,
      series : [
        { name: 'Hadir',       data: [] },
        { name: 'Terlambat',   data: [] },
        { name: 'Izin & Sakit',data: [] },
        { name: 'Alpha',       data: [] },
      ],
      colors : [C.hadir, C.terlambat, C.izin_sakit, C.alpha],
      stroke : { curve: 'smooth', width: 2 },
      fill   : { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.03 } },
      xaxis  : { categories: [], labels: { style: { colors: C.text } }, axisBorder: { show: false }, axisTicks: { show: false } },
      yaxis  : { labels: { style: { colors: C.text } } },
      grid   : { borderColor: C.grid },
      legend : { position: 'top', horizontalAlign: 'right', labels: { colors: C.text } },
      dataLabels: { enabled: false },
      tooltip: { theme: 'dark' },
    });
    chartTrend.render();

    // ── Donut ───────────────────────────────────────────────────────────
    chartDonut = new ApexCharts(document.querySelector('#chart-donut'), {
      chart  : { ...baseChart, type: 'donut', height: 320 },
      theme  : baseTheme,
      series : [0, 0, 0, 0, 0],
      labels : ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha'],
      colors : [C.hadir, C.terlambat, C.izin_sakit, C.purple, C.alpha],
      legend : { position: 'bottom', labels: { colors: C.text } },
      dataLabels: { enabled: true, formatter: v => v.toFixed(1) + '%', style: { fontSize: '11px' } },
      plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total', color: C.text } } } } },
      tooltip: { theme: 'dark' },
    });
    chartDonut.render();

    // ── Bar Kelas ────────────────────────────────────────────────────────
    chartBar = new ApexCharts(document.querySelector('#chart-bar'), {
      chart  : { ...baseChart, type: 'bar', height: 320 },
      theme  : baseTheme,
      series : [
        { name: 'Hadir',     data: [] },
        { name: 'Terlambat', data: [] },
        { name: 'Alpha',     data: [] },
      ],
      colors : [C.hadir, C.terlambat, C.alpha],
      plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
      dataLabels: { enabled: false },
      stroke    : { show: true, width: 2, colors: ['transparent'] },
      xaxis     : { categories: [], labels: { style: { colors: C.text }, rotate: -30 }, axisBorder: { show: false } },
      yaxis     : { labels: { style: { colors: C.text } } },
      grid      : { borderColor: C.grid },
      legend    : { position: 'top', labels: { colors: C.text } },
      tooltip   : { theme: 'dark' },
    });
    chartBar.render();

    // ── Jam Kedatangan (Column) ──────────────────────────────────────────
    chartJam = new ApexCharts(document.querySelector('#chart-jam'), {
      chart  : { ...baseChart, type: 'bar', height: 320 },
      theme  : baseTheme,
      series : [{ name: 'Jumlah Scan', data: [] }],
      colors : [C.hadir, C.hadir, C.purple, C.terlambat, C.alpha],
      plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '50%' } },
      dataLabels: { enabled: true, style: { colors: ['#fff'], fontSize: '11px' } },
      xaxis  : { categories: [], labels: { style: { colors: C.text }, rotate: -20 }, axisBorder: { show: false } },
      yaxis  : { labels: { style: { colors: C.text } } },
      grid   : { borderColor: C.grid },
      legend : { show: false },
      tooltip: { theme: 'dark' },
    });
    chartJam.render();

    // ── Radar ────────────────────────────────────────────────────────────
    chartRadar = new ApexCharts(document.querySelector('#chart-radar'), {
      chart  : { ...baseChart, type: 'radar', height: 320 },
      theme  : baseTheme,
      series : [
        { name: 'Hadir',     data: [] },
        { name: 'Terlambat', data: [] },
        { name: 'Alpha',     data: [] },
      ],
      colors : [C.hadir, C.terlambat, C.alpha],
      stroke : { width: 2 },
      fill   : { opacity: 0.2 },
      markers: { size: 4 },
      xaxis  : { categories: ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] },
      yaxis  : { show: false },
      legend : { position: 'top', labels: { colors: C.text } },
      tooltip: { theme: 'dark' },
    });
    chartRadar.render();
  }

  // ── Fetch & Update ───────────────────────────────────────────────────────
  function loadData() {
    showLoaders();

    const dates = getDates();
    const params = new URLSearchParams({
      start_date  : dates.start,
      end_date    : dates.end,
      tingkat     : document.getElementById('filter-tingkat').value,
      kelas_id    : document.getElementById('filter-kelas').value,
      jurusan_id  : document.getElementById('filter-jurusan').value,
    });

    fetch(`{{ route('admin.analitik-siswa.data') }}?${params}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => {
      if (!r.ok) return r.json().then(j => { throw new Error(j.message || 'HTTP ' + r.status); });
      return r.json();
    })
    .then(res => {
      if (!res.success) {
        const msg = res.message || 'Data gagal dimuat';
        console.error('[Analitik Siswa] Server error:', msg, res);
        document.getElementById('ranking-body').innerHTML =
          `<tr><td colspan="5" class="text-center py-4" style="color:#ea5455;">
            <i class="ti tabler-alert-triangle me-1"></i> Error: ${msg}
          </td></tr>`;
        hideLoaders();
        return;
      }

      // ── Periode Badge ──
      document.getElementById('periode-badge').innerHTML =
        `<i class="ti tabler-calendar me-1"></i> Periode: <strong class="text-white">${res.periode.start} – ${res.periode.end}</strong>`;

      // ── KPI ──
      const k = res.kpi;
      document.getElementById('kpi-pct-hadir').textContent    = k.persentase_kehadiran + '%';
      document.getElementById('kpi-count-hadir').textContent  = fmt(k.count_hadir);
      document.getElementById('kpi-total').textContent        = fmt(k.total_presensi);
      document.getElementById('kpi-prog-hadir').style.width   = k.persentase_kehadiran + '%';

      document.getElementById('kpi-pct-tepat').textContent       = k.persentase_tepat_waktu + '%';
      document.getElementById('kpi-count-terlambat').textContent = fmt(k.count_terlambat);
      document.getElementById('kpi-prog-tepat').style.width      = k.persentase_tepat_waktu + '%';

      document.getElementById('kpi-count-izin-sakit').textContent = fmt(k.count_izin_sakit);
      document.getElementById('kpi-count-alpha').textContent      = fmt(k.count_alpha);
      document.getElementById('kpi-peak').textContent             = k.peak_hour || '-';

      // ── Chart 1: Tren ──
      const t = res.chart_trend;
      chartTrend.updateOptions({ xaxis: { categories: t.labels } }, false, false);
      chartTrend.updateSeries([
        { name: 'Hadir',        data: t.hadir },
        { name: 'Terlambat',    data: t.terlambat },
        { name: 'Izin & Sakit', data: t.izin_sakit },
        { name: 'Alpha',        data: t.alpha },
      ], false);

      // ── Chart 2: Donut ──
      chartDonut.updateSeries(res.chart_status.series, false);

      // ── Chart 3: Bar Kelas ──
      const ck = res.chart_kelas;
      chartBar.updateOptions({ xaxis: { categories: ck.labels } }, false, false);
      chartBar.updateSeries([
        { name: 'Hadir',     data: ck.hadir },
        { name: 'Terlambat', data: ck.terlambat },
        { name: 'Alpha',     data: ck.alpha },
      ], false);

      // ── Chart 4: Jam ──
      const cj = res.chart_jam;
      chartJam.updateOptions({ xaxis: { categories: cj.labels } }, false, false);
      chartJam.updateSeries([{ name: 'Jumlah Scan', data: cj.series }], false);

      // ── Chart 5: Radar ──
      const cr = res.chart_radar;
      chartRadar.updateSeries([
        { name: 'Hadir',     data: cr.hadir },
        { name: 'Terlambat', data: cr.terlambat },
        { name: 'Alpha',     data: cr.alpha },
      ], false);

      // ── Ranking Tabel ──
      const tbody = document.getElementById('ranking-body');
      if (!res.ranking_siswa || res.ranking_siswa.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4" style="color:rgba(255,255,255,.4);">
          <i class="ti tabler-circle-check me-1 text-success"></i> Tidak ada siswa dengan akumulasi tinggi pada periode ini.
        </td></tr>`;
      } else {
        tbody.innerHTML = res.ranking_siswa.map((r, i) => `
          <tr>
            <td>
              <div class="fw-semibold" style="color:#fff;">${r.nama}</div>
              <div style="font-size:.72rem;color:rgba(255,255,255,.4);">${r.nis}</div>
            </td>
            <td><span class="badge" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);font-size:.72rem;">${r.kelas}</span></td>
            <td class="text-center"><span class="badge" style="background:rgba(255,159,67,.15);color:#ff9f43;">${r.terlambat}</span></td>
            <td class="text-center"><span class="badge" style="background:rgba(234,84,85,.15);color:#ea5455;">${r.alpha}</span></td>
            <td class="text-center"><strong style="color:#ea5455;">${r.total}</strong></td>
          </tr>
        `).join('');
      }

      hideLoaders();
    })
    .catch(err => {
      hideLoaders();
      console.error('[Analitik Siswa] Fetch Error:', err);
      document.getElementById('ranking-body').innerHTML =
        `<tr><td colspan="5" class="text-center py-4" style="color:#ea5455;">
          <i class="ti tabler-alert-triangle me-1"></i> Gagal memuat data: ${err.message}
        </td></tr>`;
    });
  }

  // ── Event Listeners ──────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    // toggle custom date
    document.getElementById('filter-preset').addEventListener('change', function () {
      document.getElementById('custom-date-wrap').classList.toggle('d-none', this.value !== 'custom');
    });

    document.getElementById('analitik-filter-form').addEventListener('submit', function (e) {
      e.preventDefault();
      loadData();
    });

    document.getElementById('btn-refresh').addEventListener('click', loadData);

    // Init
    initCharts();
    loadData();
  });
})();
</script>
@endpush
