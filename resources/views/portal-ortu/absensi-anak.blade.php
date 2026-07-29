@extends('layouts/layoutMaster')

@section('title', 'Riwayat Absensi - ' . $anak->nama_lengkap)

@section('page-style')
<style>
  body, .layout-page, .content-wrapper {
    background: #0a0e1a !important;
  }

  /* ── STAT CARDS ─────────────────────────────────────── */
  .abs-stat-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
    gap: 0.9rem;
    margin-bottom: 1.25rem;
  }
  @media (max-width: 768px) {
    .abs-stat-row { grid-template-columns: 1fr 1fr; gap: 0.65rem; }
    .abs-stat-card--avg { grid-column: 1 / -1; }
  }

  .abs-stat-card {
    background: linear-gradient(135deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 1rem 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
    overflow: hidden;
  }
  .abs-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.35);
  }
  .abs-stat-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    opacity: 0;
    transition: opacity 0.2s;
    background: radial-gradient(ellipse at 50% 0%, rgba(255,255,255,0.04), transparent 70%);
  }
  .abs-stat-card:hover::before { opacity: 1; }

  /* Avg card — spans full width on mobile, special gold glow */
  .abs-stat-card--avg {
    background: linear-gradient(135deg, rgba(255,215,0,0.10), rgba(255,180,0,0.04));
    border-color: rgba(255,215,0,0.22);
  }
  .abs-stat-card--avg:hover { box-shadow: 0 8px 28px rgba(255,215,0,0.15); }

  .abs-stat-card__icon {
    width: 44px; height: 44px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
  }
  .abs-stat-card__body { flex: 1; min-width: 0; }
  .abs-stat-card__val {
    font-size: 1.5rem; font-weight: 800; line-height: 1;
    color: #fff; letter-spacing: -0.5px;
  }
  .abs-stat-card--avg .abs-stat-card__val {
    font-size: 1.8rem; color: #ffd700;
  }
  .abs-stat-card__label {
    font-size: 0.72rem; color: rgba(255,255,255,0.4);
    font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
    margin-top: 0.2rem;
  }

  /* progress bar */
  .abs-progress {
    height: 4px; border-radius: 99px;
    background: rgba(255,255,255,0.07);
    margin-top: 0.8rem; overflow: hidden;
  }
  .abs-progress__fill {
    height: 100%; border-radius: 99px;
    transition: width 0.8s cubic-bezier(.4,0,.2,1);
  }

  /* ── FILTER TABS ──────────────────────────────────────── */
  .abs-filter-bar {
    display: flex; align-items: center;
    justify-content: space-between; flex-wrap: wrap;
    gap: 0.75rem; margin-bottom: 1rem;
  }
  .abs-tabs {
    display: flex; gap: 0.35rem;
    background: rgba(255,255,255,0.04);
    border-radius: 10px; padding: 4px;
  }
  .abs-tab {
    padding: 0.38rem 0.85rem;
    border-radius: 7px; border: none; cursor: pointer;
    font-size: 0.78rem; font-weight: 600;
    color: rgba(255,255,255,0.5);
    background: transparent;
    transition: all 0.2s;
  }
  .abs-tab.active {
    background: rgba(255,215,0,0.15);
    color: #ffd700;
    box-shadow: 0 0 0 1px rgba(255,215,0,0.25);
  }
  .abs-tab:hover:not(.active) { color: rgba(255,255,255,0.75); }

  .abs-filter-controls {
    display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;
  }
  .abs-filter-controls .form-select {
    height: 34px; min-width: 115px; font-size: 0.8rem;
    background-color: rgba(255,255,255,0.05);
    color: #fff; border-color: rgba(255,255,255,0.12);
    border-radius: 8px;
  }

  /* ── TABLE ──────────────────────────────────────────── */
  .abs-table-wrap {
    border-radius: 14px; overflow: hidden;
    border: 1px solid rgba(255,255,255,0.07);
  }
  .das-table { width: 100%; border-collapse: collapse; }
  .das-table thead tr { background: rgba(255,255,255,0.04); }
  .das-table thead th {
    padding: 0.75rem 1rem;
    font-size: 0.68rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.7px;
    color: rgba(255,255,255,0.35); border-bottom: 1px solid rgba(255,255,255,0.07);
    white-space: nowrap;
  }
  .das-table tbody tr {
    border-bottom: 1px solid rgba(255,255,255,0.05);
    transition: background 0.15s;
  }
  .das-table tbody tr:last-child { border-bottom: none; }
  .das-table tbody tr:hover { background: rgba(255,255,255,0.03); }
  .das-table tbody td { padding: 0.8rem 1rem; vertical-align: middle; }

  /* jam masuk highlight */
  .abs-jam {
    font-family: 'JetBrains Mono', 'Courier New', monospace;
    font-weight: 600; font-size: 0.88rem;
    color: #fff;
  }
  .abs-jam--early { color: #5bde8a; } /* lebih awal */
  .abs-jam--normal { color: #fff; }
  .abs-jam--late { color: #f5a524; }

  /* ── Loading & Empty ─────────────────────────────────── */
  .abs-center-state {
    padding: 3.5rem 1rem; text-align: center;
  }
  .abs-center-state__icon {
    font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.35;
  }
  .abs-center-state__text {
    font-size: 0.85rem; color: rgba(255,255,255,0.3); font-weight: 500;
  }
  .abs-spinner {
    width: 36px; height: 36px; border-radius: 50%;
    border: 3px solid rgba(255,215,0,0.12);
    border-top-color: #ffd700;
    animation: absSpin 0.75s linear infinite;
    margin: 0 auto 0.75rem;
  }
  @keyframes absSpin { to { transform: rotate(360deg); } }

  @media (max-width: 576px) {
    .das-table thead th,
    .das-table tbody td { padding: 0.6rem 0.65rem; font-size: 0.75rem; }
    .abs-stat-card__val { font-size: 1.25rem; }
    .abs-stat-card--avg .abs-stat-card__val { font-size: 1.5rem; }
  }
</style>
@endsection

@section('content')
@php
    $filter   = $filter   ?? request()->query('filter', 'monthly');
    $semester = $semester ?? request()->query('semester', now()->month <= 6 ? 'ganjil' : 'genap');
    $month    = $month    ?? request()->query('month', now()->month);
    $year     = $year     ?? request()->query('year', now()->year);
@endphp

{{-- HERO -------------------------------------------------------------------- --}}
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>
  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder" style="width:64px;height:64px;border-radius:5px;display:flex;align-items:center;justify-content:center;background:rgba(255,215,0,0.1);border:2px solid rgba(255,215,0,0.25);">
          <i class="ti tabler-calendar-stats" style="font-size:1.6rem;color:#ffd700;"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge"><span class="pulse-dot"></span> Portal Orang Tua</div>
        <h4 class="das-hero__title text-gradient-gold">Riwayat Absensi Anak</h4>
        <p class="das-hero__subtitle">Pantau kehadiran & rata-rata jam masuk <strong>{{ $anak->nama_lengkap }}</strong> secara real-time.</p>
      </div>
    </div>
  </div>
</div>

{{-- STAT CARDS -------------------------------------------------------------- --}}
<div class="abs-stat-row" id="statCards">
  {{-- Avg jam masuk --}}
  <div class="abs-stat-card abs-stat-card--avg">
    <div class="abs-stat-card__icon" style="background:rgba(255,215,0,0.12);color:#ffd700;">
      <i class="ti tabler-clock-hour-4"></i>
    </div>
    <div class="abs-stat-card__body">
      <div class="abs-stat-card__val" id="statAvgJam">—:——</div>
      <div class="abs-stat-card__label">Rata-rata Jam Masuk</div>
    </div>
  </div>

  {{-- Hadir --}}
  <div class="abs-stat-card">
    <div class="abs-stat-card__icon" style="background:rgba(91,222,138,0.12);color:#5bde8a;">
      <i class="ti tabler-circle-check"></i>
    </div>
    <div class="abs-stat-card__body">
      <div class="abs-stat-card__val" id="statHadir">—</div>
      <div class="abs-stat-card__label">Hadir</div>
      <div class="abs-progress"><div class="abs-progress__fill" id="statHadirBar" style="width:0%;background:#5bde8a;"></div></div>
    </div>
  </div>

  {{-- Terlambat --}}
  <div class="abs-stat-card">
    <div class="abs-stat-card__icon" style="background:rgba(245,165,36,0.12);color:#f5a524;">
      <i class="ti tabler-clock-exclamation"></i>
    </div>
    <div class="abs-stat-card__body">
      <div class="abs-stat-card__val" id="statTerlambat">—</div>
      <div class="abs-stat-card__label">Terlambat</div>
      <div class="abs-progress"><div class="abs-progress__fill" id="statTerlambatBar" style="width:0%;background:#f5a524;"></div></div>
    </div>
  </div>

  {{-- Alpha --}}
  <div class="abs-stat-card">
    <div class="abs-stat-card__icon" style="background:rgba(234,84,85,0.12);color:#ea5455;">
      <i class="ti tabler-x-circle"></i>
    </div>
    <div class="abs-stat-card__body">
      <div class="abs-stat-card__val" id="statAlpha">—</div>
      <div class="abs-stat-card__label">Alpha</div>
      <div class="abs-progress"><div class="abs-progress__fill" id="statAlphaBar" style="width:0%;background:#ea5455;"></div></div>
    </div>
  </div>

  {{-- Izin/Sakit --}}
  <div class="abs-stat-card">
    <div class="abs-stat-card__icon" style="background:rgba(0,186,255,0.10);color:#00bfff;">
      <i class="ti tabler-file-text"></i>
    </div>
    <div class="abs-stat-card__body">
      <div class="abs-stat-card__val" id="statIzinSakit">—</div>
      <div class="abs-stat-card__label">Izin / Sakit</div>
      <div class="abs-progress"><div class="abs-progress__fill" id="statIzinBar" style="width:0%;background:#00bfff;"></div></div>
    </div>
  </div>
</div>

{{-- PANEL ------------------------------------------------------------------- --}}
<div class="das-panel">
  {{-- Filter bar --}}
  <div class="das-panel__head" style="background:transparent;border-bottom:1px solid rgba(255,255,255,0.07);padding-bottom:0.75rem;">
    <div class="abs-filter-bar">
      <div class="abs-tabs" role="group" aria-label="Filter Periode Absensi">
        <button class="abs-tab" data-filter="weekly"   onclick="switchFilter('weekly')">Mingguan</button>
        <button class="abs-tab" data-filter="monthly"  onclick="switchFilter('monthly')">Bulanan</button>
        <button class="abs-tab" data-filter="semester" onclick="switchFilter('semester')">Semester</button>
        <button class="abs-tab" data-filter="yearly"   onclick="switchFilter('yearly')">Tahunan</button>
      </div>

      <div class="abs-filter-controls">
        <span id="filterWeeklyLabel" class="text-white-50 small fst-italic" style="display:none;">
          <i class="ti tabler-calendar-week me-1"></i> Minggu ini
        </span>
        <select id="monthSelect" class="form-select" style="display:none;" onchange="loadAbsensi()">
          @for($m=1; $m<=12; $m++)
            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2000, $m, 1)->locale('id')->translatedFormat('F') }}</option>
          @endfor
        </select>
        <select id="semesterSelect" class="form-select" style="display:none;" onchange="loadAbsensi()">
          <option value="ganjil" {{ $semester === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
          <option value="genap"  {{ $semester === 'genap'  ? 'selected' : '' }}>Genap</option>
        </select>
        <select id="yearSelect" class="form-select" style="display:none;" onchange="loadAbsensi()">
          @for($y=now()->year; $y>=now()->year-2; $y--)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="abs-table-wrap">
    <table class="das-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th><i class="ti tabler-clock-hour-4 me-1" style="opacity:.6;"></i>Jam Masuk</th>
          <th>Jam Pulang</th>
          <th class="text-center" style="width:120px;">Status</th>
          <th>Metode</th>
        </tr>
      </thead>
      <tbody id="absensiBody">
        <tr>
          <td colspan="5">
            <div class="abs-center-state">
              <div class="abs-spinner"></div>
              <div class="abs-center-state__text">Memuat data absensi...</div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection

@section('page-script')
<script>
let currentFilter = '{{ $filter }}';

document.addEventListener('DOMContentLoaded', function() {
  switchFilter(currentFilter);
});

function switchFilter(filter) {
  currentFilter = filter;

  // Update tab active states
  document.querySelectorAll('.abs-tab').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('data-filter') === filter);
  });

  // Toggle filter controls
  document.getElementById('filterWeeklyLabel').style.display = filter === 'weekly'   ? 'inline-flex' : 'none';
  document.getElementById('monthSelect').style.display        = filter === 'monthly'  ? 'block' : 'none';
  document.getElementById('semesterSelect').style.display     = filter === 'semester' ? 'block' : 'none';
  document.getElementById('yearSelect').style.display         = (filter !== 'weekly') ? 'block' : 'none';

  showLoadingTable();
  loadAbsensi();
}

function showLoadingTable() {
  document.getElementById('absensiBody').innerHTML = `
    <tr>
      <td colspan="5">
        <div class="abs-center-state">
          <div class="abs-spinner"></div>
          <div class="abs-center-state__text">Memuat data absensi...</div>
        </div>
      </td>
    </tr>`;
}

function getStatusBadgeClass(status) {
  const map = {
    hadir: 'bg-label-success', terlambat: 'bg-label-warning',
    sakit: 'bg-label-info', izin: 'bg-label-primary', alpha: 'bg-label-danger'
  };
  return map[status] || 'bg-label-secondary';
}

function getJamClass(jamStr) {
  if (!jamStr || jamStr === '-') return '';
  const [h, m] = jamStr.split(':').map(Number);
  const mins = h * 60 + m;
  if (mins <= 6 * 60 + 15) return 'abs-jam--early';   // ≤ 06:15 hijau
  if (mins <= 7 * 60)       return 'abs-jam--normal';   // ≤ 07:00 normal
  return 'abs-jam--late';                                // > 07:00 oranye
}

function updateStats(stats) {
  if (!stats) return;
  const total = stats.total || 0;

  // Avg jam masuk
  document.getElementById('statAvgJam').textContent = stats.avg_jam_masuk || '—:——';

  // Per-status
  document.getElementById('statHadir').textContent     = stats.hadir ?? '0';
  document.getElementById('statTerlambat').textContent = stats.terlambat ?? '0';
  document.getElementById('statAlpha').textContent     = stats.alpha ?? '0';
  document.getElementById('statIzinSakit').textContent = ((stats.izin ?? 0) + (stats.sakit ?? 0));

  // Progress bars
  const pct = (n) => total > 0 ? Math.round((n / total) * 100) : 0;
  document.getElementById('statHadirBar').style.width     = pct(stats.hadir)    + '%';
  document.getElementById('statTerlambatBar').style.width = pct(stats.terlambat)+ '%';
  document.getElementById('statAlphaBar').style.width     = pct(stats.alpha)    + '%';
  document.getElementById('statIzinBar').style.width      = pct((stats.izin ?? 0) + (stats.sakit ?? 0)) + '%';
}

async function loadAbsensi() {
  const tbody = document.getElementById('absensiBody');
  const params = new URLSearchParams();
  params.set('filter', currentFilter);

  if (currentFilter === 'monthly') {
    params.set('month', document.getElementById('monthSelect').value);
    params.set('year',  document.getElementById('yearSelect').value);
  } else if (currentFilter === 'semester') {
    params.set('semester', document.getElementById('semesterSelect').value);
    params.set('year',     document.getElementById('yearSelect').value);
  } else if (currentFilter === 'yearly') {
    params.set('year', document.getElementById('yearSelect').value);
  }

  try {
    const response = await fetch('{{ route('ortu.anak.absensi.data', $anak->id) }}?' + params.toString());
    const result   = await response.json();

    // Update stat cards
    updateStats(result.stats);

    const data = Array.isArray(result) ? result : (result.absensi || result.data || []);

    if (data.length === 0) {
      tbody.innerHTML = `
        <tr><td colspan="5">
          <div class="abs-center-state">
            <div class="abs-center-state__icon"><i class="ti tabler-calendar-off"></i></div>
            <div class="abs-center-state__text">Tidak ada data absensi untuk periode ini.</div>
          </div>
        </td></tr>`;
      return;
    }

    tbody.innerHTML = data.map(row => {
      const tanggal   = row.tanggal   || '-';
      const jamMasuk  = row.jam_masuk || '-';
      const jamPulang = row.jam_pulang|| '-';
      const statusText   = row.status_text  || row.status || '-';
      const statusBadge  = row.status_badge || getStatusBadgeClass(row.status);
      const metodeIcon   = row.metode_icon  || row.metode || '-';
      const jamClass     = getJamClass(jamMasuk);

      return `
        <tr>
          <td class="fw-semibold text-white">${tanggal}</td>
          <td><span class="abs-jam ${jamClass}">${jamMasuk}</span></td>
          <td><span class="abs-jam" style="color:rgba(255,255,255,.5);">${jamPulang}</span></td>
          <td class="text-center">
            <span class="badge ${statusBadge} px-2 py-1 text-uppercase" style="font-size:.72rem;letter-spacing:.3px;">${statusText}</span>
          </td>
          <td><small class="text-muted">${metodeIcon}</small></td>
        </tr>`;
    }).join('');

  } catch (e) {
    console.error('Gagal memuat absensi:', e);
    tbody.innerHTML = `
      <tr><td colspan="5">
        <div class="abs-center-state">
          <div class="abs-center-state__icon" style="color:rgba(234,84,85,0.5);"><i class="ti tabler-cloud-off"></i></div>
          <div class="abs-center-state__text" style="color:rgba(234,84,85,0.6);">Gagal memuat data. Coba refresh halaman.</div>
        </div>
      </td></tr>`;
  }
}
</script>
@endsection