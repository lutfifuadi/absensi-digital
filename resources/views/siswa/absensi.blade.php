@extends('layouts/layoutMaster')

@section('title', 'Riwayat Absensi')

@section('page-style')
<style>
  body, .layout-page, .content-wrapper {
    background: #0a0e1a !important;
  }

  /* ── Loading Spinner ── */
  .lb-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 0;
    gap: 1rem;
  }
  .lb-spinner__ring {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.04);
    border-top-color: #ffd700;
    animation: lbSpin 0.8s linear infinite;
  }
  @keyframes lbSpin { to { transform: rotate(360deg); } }
  .lb-spinner__text {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.35);
    letter-spacing: 0.5px;
    font-weight: 500;
  }

  /* ── Empty state ── */
  .lb-empty {
    text-align: center;
    padding: 4rem 1rem;
    color: rgba(255,255,255,0.25);
  }
  .lb-empty__icon {
    font-size: 3rem;
    margin-bottom: 0.75rem;
    opacity: 0.5;
  }
  .lb-empty__text {
    font-size: 0.85rem;
    font-weight: 500;
  }

  @media (max-width: 576px) {
    .das-table { font-size: 0.72rem; }
    .das-table thead th,
    .das-table tbody td { padding: 0.5rem 0.6rem; }
  }
</style>
@endsection

@section('content')
@php
    $filter = $filter ?? request()->query('filter', 'monthly');
    $semester = $semester ?? request()->query('semester', now()->month <= 6 ? 'ganjil' : 'genap');
    $month = $month ?? request()->query('month', now()->month);
    $year = $year ?? request()->query('year', now()->year);
@endphp

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
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Portal Siswa
        </div>
        <h4 class="das-hero__title text-gradient-gold">Riwayat Absensi</h4>
        <p class="das-hero__subtitle">Pantau seluruh catatan kehadiran dan tingkat presensi Anda secara real-time.</p>
      </div>
    </div>
  </div>
</div>

<div class="das-panel">
  <div class="das-panel__head d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div class="das-panel__title mb-0">
      <span class="das-panel__icon-dot --warning"></span>
      Daftar Kehadiran
    </div>
    
    <div class="d-flex align-items-center gap-3 flex-wrap">
      {{-- Filter Periode Tabs --}}
      <div class="btn-group btn-group-sm" role="group" aria-label="Filter Periode Absensi">
        <button type="button" class="btn btn-outline-warning lb-filter-btn" data-filter="weekly" onclick="switchFilter('weekly')">
          <i class="ti tabler-calendar-week me-1"></i> Mingguan
        </button>
        <button type="button" class="btn btn-outline-warning lb-filter-btn" data-filter="monthly" onclick="switchFilter('monthly')">
          <i class="ti tabler-calendar-month me-1"></i> Bulanan
        </button>
        <button type="button" class="btn btn-outline-warning lb-filter-btn" data-filter="semester" onclick="switchFilter('semester')">
          <i class="ti tabler-calendar me-1"></i> Semester
        </button>
        <button type="button" class="btn btn-outline-warning lb-filter-btn" data-filter="yearly" onclick="switchFilter('yearly')">
          <i class="ti tabler-calendar me-1"></i> Tahunan
        </button>
      </div>
    </div>
  </div>

  {{-- Row 2: Filter Controls --}}
  <div class="das-panel__body pt-0 pb-3 d-flex justify-content-end border-bottom border-secondary border-opacity-10" style="background: transparent;">
    <div class="d-flex align-items-center gap-2 justify-content-end flex-wrap">
      {{-- Weekly info --}}
      <span id="filterWeeklyLabel" class="text-white-50 small fst-italic d-flex align-items-center gap-1" style="display: none;">
          <i class="ti tabler-calendar-week"></i> Menampilkan data absensi minggu ini.
      </span>

      {{-- Month select --}}
      <select id="monthSelect" class="form-select form-select-sm" style="height:35px;min-width:125px;display:none;background-color:rgba(255,255,255,0.05);color:white;border-color:rgba(255,255,255,0.15);" onchange="loadAbsensi()">
          @for($m=1; $m<=12; $m++)
              <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2000, $m, 1)->locale('id')->translatedFormat('F') }}</option>
          @endfor
      </select>

      {{-- Semester select --}}
      <select id="semesterSelect" class="form-select form-select-sm" style="height:35px;min-width:125px;display:none;background-color:rgba(255,255,255,0.05);color:white;border-color:rgba(255,255,255,0.15);" onchange="loadAbsensi()">
          <option value="ganjil" {{ $semester === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
          <option value="genap" {{ $semester === 'genap' ? 'selected' : '' }}>Genap</option>
      </select>

      {{-- Year select --}}
      <select id="yearSelect" class="form-select form-select-sm" style="height:35px;min-width:90px;display:none;background-color:rgba(255,255,255,0.05);color:white;border-color:rgba(255,255,255,0.15);" onchange="loadAbsensi()">
          @for($y=now()->year; $y>=now()->year-2; $y--)
              <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
      </select>
    </div>
  </div>

  <div class="table-responsive">
    <table class="das-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th class="text-center" style="width: 120px;">Status</th>
                <th>Metode</th>
            </tr>
        </thead>
        <tbody id="absensiBody">
            <tr id="loadingRow">
                <td colspan="5" class="text-center py-5">
                    <div class="lb-spinner">
                        <div class="lb-spinner__ring"></div>
                        <div class="lb-spinner__text">Memuat data absensi...</div>
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
    // Set initial visibility for filter controls
    switchFilter(currentFilter);
});

function switchFilter(filter) {
    currentFilter = filter;

    // Update button active states
    document.querySelectorAll('[data-filter]').forEach(btn => {
        if (btn.getAttribute('data-filter') === filter) {
            btn.classList.remove('btn-outline-warning');
            btn.classList.add('btn-warning');
        } else {
            btn.classList.remove('btn-warning');
            btn.classList.add('btn-outline-warning');
        }
    });

    // Toggle filter controls visibility
    document.getElementById('filterWeeklyLabel').style.display = filter === 'weekly' ? 'inline-flex' : 'none';
    document.getElementById('monthSelect').style.display = filter === 'monthly' ? 'block' : 'none';
    document.getElementById('semesterSelect').style.display = filter === 'semester' ? 'block' : 'none';
    document.getElementById('yearSelect').style.display = (filter === 'monthly' || filter === 'semester' || filter === 'yearly') ? 'block' : 'none';

    // Show loading spinner
    const tbody = document.getElementById('absensiBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center py-5">
                <div class="lb-spinner">
                    <div class="lb-spinner__ring"></div>
                    <div class="lb-spinner__text">Memuat data absensi...</div>
                </div>
            </td>
        </tr>
    `;

    loadAbsensi();
}

function getBadgeClass(status) {
    const map = {
        hadir: 'bg-label-success',
        terlambat: 'bg-label-warning',
        sakit: 'bg-label-info',
        izin: 'bg-label-primary',
        alpha: 'bg-label-danger'
    };
    return map[status] || 'bg-label-secondary';
}

function getMetodeIcon(metode) {
    const map = {
        mandiri: '<i class="ti tabler-gps me-1"></i> GPS Mandiri',
        qr: '<i class="ti tabler-qrcode me-1"></i> Scan QR',
        manual: '<i class="ti tabler-edit me-1"></i> Manual'
    };
    return map[metode] || '<i class="ti tabler-help-circle me-1"></i> ' + (metode ? metode.charAt(0).toUpperCase() + metode.slice(1) : '\u2014');
}

function getEmptyMessage(filter) {
    const map = {
        weekly: 'Tidak ada data absensi minggu ini.',
        semester: 'Tidak ada data absensi semester ini.',
        yearly: 'Tidak ada data absensi tahun ini.'
    };
    return map[filter] || 'Tidak ada data absensi bulan ini.';
}

async function loadAbsensi() {
    const tbody = document.getElementById('absensiBody');

    // Build query parameters
    const params = new URLSearchParams();
    params.set('filter', currentFilter);

    if (currentFilter === 'monthly') {
        params.set('month', document.getElementById('monthSelect').value);
        params.set('year', document.getElementById('yearSelect').value);
    } else if (currentFilter === 'semester') {
        params.set('semester', document.getElementById('semesterSelect').value);
        params.set('year', document.getElementById('yearSelect').value);
    } else if (currentFilter === 'yearly') {
        params.set('year', document.getElementById('yearSelect').value);
    }

    try {
        const response = await fetch('{{ route('siswa.absensi.data') }}?' + params.toString());
        const result = await response.json();

        // Handle both { absensi: [...] } and plain [...] response formats
        const data = Array.isArray(result) ? result : (result.absensi || result.data || []);

        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="lb-empty">
                            <div class="lb-empty__icon"><i class="ti tabler-calendar-off"></i></div>
                            <div class="lb-empty__text">${getEmptyMessage(currentFilter)}</div>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.map(row => {
            const tanggal = row.tanggal || '-';
            const jamMasuk = row.jam_masuk || '-';
            const jamPulang = row.jam_pulang || '-';
            const statusText = row.status_text || row.status || '-';
            const statusBadge = row.status_badge || getBadgeClass(row.status);
            const metodeIcon = row.metode_icon || getMetodeIcon(row.metode);

            return `
                <tr>
                    <td class="fw-semibold text-white py-3">${tanggal}</td>
                    <td class="text-white">${jamMasuk}</td>
                    <td class="text-white">${jamPulang}</td>
                    <td class="text-center">
                        <span class="badge ${statusBadge} px-2.5 py-1 text-uppercase" style="font-size: 0.75rem;">${statusText}</span>
                    </td>
                    <td>
                        <span class="small">${metodeIcon}</span>
                    </td>
                </tr>
            `;
        }).join('');

    } catch (e) {
        console.error('Gagal memuat absensi:', e);
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="lb-empty">
                        <div class="lb-empty__icon" style="color:rgba(234,84,85,0.6);"><i class="ti tabler-cloud-off"></i></div>
                        <div class="lb-empty__text" style="color:rgba(234,84,85,0.6);">Gagal memuat data. Coba refresh halaman.</div>
                    </div>
                </td>
            </tr>
        `;
    }
}
</script>
@endsection