@extends('layouts/layoutMaster')

@section('title', 'Dashboard Guru BK')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <link rel="stylesheet" href="{{ asset('css/dashboards/guru-bk.css') }}?v=1.1">
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════
       HERO HEADER — BK Identity + Live Clock
  ═══════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__scanline" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner">
      {{-- Identity --}}
      <div class="das-hero__identity">
        <div class="das-hero__logo-placeholder" aria-hidden="true" style="background: rgba(239, 90, 90, 0.14); border-color: rgba(239, 90, 90, 0.3); color: #EF5A5A;">
          <i class="ti tabler-user-heart"></i>
        </div>
        <div class="das-hero__meta">
          <div class="das-hero__badge" style="color: #EF5A5A; background: rgba(239, 90, 90, 0.14); border-color: rgba(239, 90, 90, 0.25);">
            <span class="das-hero__pulse-dot" style="background:#EF5A5A;" aria-hidden="true"></span>
            Bimbingan &amp; Konseling (BK)
          </div>
          <h1 class="das-hero__school">Dashboard Guru BK</h1>
          <p class="das-hero__welcome">
            Selamat datang, <strong>{{ auth()->user()->name }}</strong> 👋
            &nbsp;|&nbsp;
            <span class="text-body-secondary" style="font-size:0.85rem;">
              {{ $tahunAkademik ? $tahunAkademik->nama . ' — ' . ($tahunAkademik->semester ?? '') : 'Tahun Akademik Aktif' }}
            </span>
          </p>
          {{-- Quick Action Buttons --}}
          <div class="d-flex flex-wrap gap-2 mt-3">
            <a href="{{ route('bk.pelanggaran.create') }}" class="das-btn das-btn--danger d-inline-flex align-items-center gap-1">
              <i class="ti tabler-plus"></i> Input Pelanggaran
            </a>
            <a href="{{ route('bk.sp.create') }}" class="das-btn das-btn--warning d-inline-flex align-items-center gap-1">
              <i class="ti tabler-file-certificate"></i> Terbitkan SP
            </a>
            <a href="{{ route('bk.rekap.index') }}" class="das-btn das-btn--info d-inline-flex align-items-center gap-1">
              <i class="ti tabler-report-analytics"></i> Rekap Pelanggaran Harian (Semua Kelas)
            </a>
          </div>
        </div>
      </div>

      {{-- Live Clock --}}
      <div class="das-hero__clock" role="status" aria-live="off">
        <div class="das-hero__date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        <div class="das-hero__time">
          <span id="bk-live-clock">00:00:00</span>
          <span class="das-hero__live-badge" style="color:#EF5A5A; background:rgba(239,90,90,0.14); border-color:rgba(239,90,90,0.3);"><span class="das-hero__pulse-dot" style="background:#EF5A5A;" aria-hidden="true"></span>LIVE</span>
        </div>
        <div class="das-hero__tz">WAKTU INDONESIA BARAT (WIB)</div>
      </div>
    </div>
  </div>{{-- /das-hero --}}


  {{-- ═══════════════════════════════════════════
       SECTION A: STAT CARDS — 3 KPI Utama (Card Gradient Admin System)
  ═══════════════════════════════════════════ --}}
  <div class="row g-4 mb-4">
    {{-- Card 1: Pelanggaran Bulan Ini --}}
    <div class="col-lg-4 col-sm-6">
      <a href="{{ route('bk.pelanggaran.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-danger h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-danger">
                  <i class="ti tabler-alert-triangle fs-4"></i>
                </span>
              </div>
              <h3 class="mb-0 fw-bold text-white">{{ number_format($summary['totalPelanggaranBulanIni'] ?? 0) }}</h3>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap fw-semibold">Pelanggaran Bulan Ini</p>
            <p class="mb-0">
              <span class="text-danger fw-medium me-2">Evaluasi Kejadian</span>
              <small class="text-body-secondary">klik untuk detail</small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 2: Siswa Dalam Pemantauan --}}
    <div class="col-lg-4 col-sm-6">
      <a href="{{ route('bk.pelanggaran.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-warning h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="ti tabler-users fs-4"></i>
                </span>
              </div>
              <h3 class="mb-0 fw-bold text-white">{{ number_format($summary['totalSiswaBermasalah'] ?? 0) }}</h3>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap fw-semibold">Siswa Dalam Pemantauan</p>
            <p class="mb-0">
              <span class="text-warning fw-medium me-2">Lintas Kelas</span>
              <small class="text-body-secondary">tahun aktif</small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 3: SP Diterbitkan --}}
    <div class="col-lg-4 col-sm-6">
      <a href="{{ route('bk.sp.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-primary h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ti tabler-file-certificate fs-4"></i>
                </span>
              </div>
              <h3 class="mb-0 fw-bold text-white">{{ number_format($summary['totalSpDiterbitkan'] ?? 0) }}</h3>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap fw-semibold">Surat Peringatan Diterbitkan</p>
            <p class="mb-0">
              <span class="text-primary fw-medium me-2">Intervensi Resmi</span>
              <small class="text-body-secondary">kelola SP</small>
            </p>
          </div>
        </div>
      </a>
    </div>
  </div>{{-- /stat cards --}}


  {{-- ═══════════════════════════════════════════
       SECTION B: MAIN CONTENT GRID
       Kiri (7): Top Violators + Tren Chart
       Kanan (5): Quick Access + SP Terbaru + Kategori
  ═══════════════════════════════════════════ --}}
  <div class="row g-4 mb-4">

    {{-- ── COL KIRI ── --}}
    <div class="col-lg-7">

      {{-- Card Panel: Top Siswa Bermasalah --}}
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 fw-bold d-flex align-items-center gap-2">
            <i class="ti tabler-flame text-danger fs-4"></i>
            Top Siswa Bermasalah (Poin Terbanyak)
          </h5>
          <a href="{{ route('bk.rekap.index') }}" class="btn btn-sm btn-label-secondary">
            Lihat Semua <i class="ti tabler-arrow-right ms-1"></i>
          </a>
        </div>
        <div class="table-responsive text-nowrap">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr class="table-light">
                <th style="width: 50px;">#</th>
                <th>Siswa</th>
                <th>Kelas</th>
                <th class="text-center">Pelanggaran</th>
                <th class="text-center">Total Poin</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($topViolators as $rank => $siswa)
                <tr>
                  <td class="fw-bold text-body-secondary">
                    {{ str_pad($rank + 1, 2, '0', STR_PAD_LEFT) }}
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-sm flex-shrink-0">
                        <span class="avatar-initial rounded-circle bg-label-danger fw-bold">
                          {{ strtoupper(substr($siswa->nama_lengkap, 0, 2)) }}
                        </span>
                      </div>
                      <div>
                        <h6 class="mb-0 fw-semibold text-body text-truncate" style="max-width: 200px;">{{ $siswa->nama_lengkap }}</h6>
                        <small class="text-body-secondary">NIS: {{ $siswa->nis ?? '-' }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-label-info fw-semibold">{{ $siswa->kelas->nama ?? '-' }}</span>
                  </td>
                  <td class="text-center fw-bold">
                    {{ $siswa->total_pelanggaran }}×
                  </td>
                  <td class="text-center">
                    <span class="badge bg-danger fs-6 fw-bold">{{ $siswa->total_poin }} Poin</span>
                  </td>
                  <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end">
                      <a href="{{ route('bk.sp.create', ['siswa_id' => $siswa->id]) }}"
                         class="btn btn-sm btn-label-warning btn-icon" title="Terbitkan SP">
                        <i class="ti tabler-file-certificate"></i>
                      </a>
                      <a href="{{ route('bk.pelanggaran.index', ['siswa_id' => $siswa->id]) }}"
                         class="btn btn-sm btn-label-info btn-icon" title="Riwayat">
                        <i class="ti tabler-history"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-5 text-body-secondary">
                    <i class="ti tabler-mood-happy d-block mb-2 text-success fs-1"></i>
                    Belum ada data pelanggaran tercatat.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>{{-- /card top violators --}}

      {{-- Card Panel: Chart Tren Pelanggaran 6 Bulan --}}
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 fw-bold d-flex align-items-center gap-2">
            <i class="ti tabler-chart-line text-warning fs-4"></i>
            Tren Pelanggaran (6 Bulan Terakhir)
          </h5>
        </div>
        <div class="card-body">
          <div id="bkTrendChart" style="min-height: 240px;"></div>
        </div>
      </div>{{-- /card chart --}}

    </div>{{-- /col kiri --}}


    {{-- ── COL KANAN ── --}}
    <div class="col-lg-5">

      {{-- Card Panel: Quick Access --}}
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title m-0 fw-bold d-flex align-items-center gap-2">
            <i class="ti tabler-layout-grid text-primary fs-4"></i>
            Menu Cepat BK
          </h5>
        </div>
        <div class="card-body">
          <div class="bk-quick-grid">
            <a href="{{ route('bk.dashboard') }}" class="bk-quick-item bk-quick-item--primary">
              <i class="ti tabler-dashboard fs-3 mb-1"></i>
              <span>Dashboard</span>
            </a>
            <a href="{{ route('bk.pelanggaran.index') }}" class="bk-quick-item bk-quick-item--danger">
              <i class="ti tabler-alert-triangle fs-3 mb-1"></i>
              <span>Pelanggaran</span>
            </a>
            <a href="{{ route('bk.pelanggaran.create') }}" class="bk-quick-item bk-quick-item--danger">
              <i class="ti tabler-plus fs-3 mb-1"></i>
              <span>Input Baru</span>
            </a>
            <a href="{{ route('bk.sp.index') }}" class="bk-quick-item bk-quick-item--warning">
              <i class="ti tabler-certificate fs-3 mb-1"></i>
              <span>Kelola SP</span>
            </a>
            <a href="{{ route('bk.sp.create') }}" class="bk-quick-item bk-quick-item--warning">
              <i class="ti tabler-file-certificate fs-3 mb-1"></i>
              <span>Terbitkan SP</span>
            </a>
            <a href="{{ route('bk.rekap.index') }}" class="bk-quick-item bk-quick-item--info">
              <i class="ti tabler-chart-pie fs-3 mb-1"></i>
              <span>Rekap Data</span>
            </a>
          </div>
        </div>
      </div>{{-- /card quick access --}}

      {{-- Card Panel: SP Terbaru Diterbitkan --}}
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 fw-bold d-flex align-items-center gap-2">
            <i class="ti tabler-certificate text-warning fs-4"></i>
            SP Terbaru Diterbitkan
          </h5>
          <a href="{{ route('bk.sp.index') }}" class="btn btn-sm btn-label-secondary">
            Kelola SP
          </a>
        </div>
        <ul class="list-group list-group-flush">
          @forelse($spAktif as $sp)
            <li class="list-group-item d-flex align-items-center justify-content-between py-3">
              <div>
                <div class="fw-bold text-body mb-1">
                  <span class="badge bg-warning text-dark me-2">{{ $sp->level_sp }}</span>
                  {{ $sp->siswa->nama_lengkap ?? '-' }}
                </div>
                <small class="text-body-secondary d-block">
                  Kelas: {{ $sp->siswa->kelas->nama ?? '-' }}
                  &bull;
                  {{ $sp->tanggal_sp ? \Carbon\Carbon::parse($sp->tanggal_sp)->translatedFormat('d M Y') : '-' }}
                </small>
              </div>
              <span class="badge bg-label-danger fw-bold fs-6">{{ $sp->total_poin_saat_sp }} Poin</span>
            </li>
          @empty
            <li class="list-group-item text-center py-4 text-body-secondary">
              <i class="ti tabler-file-off fs-3 d-block mb-1"></i>
              Belum ada SP diterbitkan.
            </li>
          @endforelse
        </ul>
      </div>{{-- /card SP --}}

      {{-- Card Panel: Rekap Pelanggaran Per Kategori --}}
      <div class="card">
        <div class="card-header">
          <h5 class="card-title m-0 fw-bold d-flex align-items-center gap-2">
            <i class="ti tabler-chart-pie text-danger fs-4"></i>
            Kategori Pelanggaran (Bulan Ini)
          </h5>
        </div>
        <div class="card-body">
          @php $maxPoin = $rekapKategori->max('total_poin') ?: 1; @endphp
          @forelse($rekapKategori as $kat)
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <div>
                  <span class="fw-bold text-body">{{ $kat->nama_kategori }}</span>
                  <small class="text-body-secondary d-block">{{ $kat->total_pelanggaran }} kasus dicatat</small>
                </div>
                <span class="badge bg-label-primary fw-bold">{{ $kat->total_poin }} Poin</span>
              </div>
              <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-danger" role="progressbar"
                     style="width: {{ round(($kat->total_poin / $maxPoin) * 100) }}%"
                     aria-valuenow="{{ $kat->total_poin }}" aria-valuemin="0" aria-valuemax="{{ $maxPoin }}"></div>
              </div>
            </div>
          @empty
            <div class="text-center py-4 text-body-secondary">
              <i class="ti tabler-chart-pie-off fs-3 d-block mb-1"></i>
              Belum ada data kategori bulan ini.
            </div>
          @endforelse
        </div>
      </div>{{-- /card kategori --}}

    </div>{{-- /col kanan --}}
  </div>{{-- /row --}}

{{-- Scroll to Top --}}
<button class="das-scroll-top" id="bkScrollTop" aria-label="Kembali ke atas">
  <i class="ti tabler-arrow-up"></i>
</button>

@endsection

@section('page-script')
<script>
/* ── Live Clock ── */
(function() {
  function updateClock() {
    const el = document.getElementById('bk-live-clock');
    if (el) el.textContent = new Date().toLocaleTimeString('id-ID', { hour12: false });
  }
  updateClock();
  setInterval(updateClock, 1000);
})();

/* ── Scroll to Top ── */
(function() {
  const btn = document.getElementById('bkScrollTop');
  if (!btn) return;
  window.addEventListener('scroll', () => {
    btn.classList.toggle('--visible', window.scrollY > 300);
  }, { passive: true });
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
})();

/* ── Tren Chart (ApexCharts) ── */
(function() {
  const chartMonths = @json($chartMonths ?? []);
  const chartData   = @json($chartData ?? []);
  const el = document.getElementById('bkTrendChart');
  if (!el || typeof ApexCharts === 'undefined') return;

  new ApexCharts(el, {
    chart: {
      type: 'area',
      height: 240,
      background: 'transparent',
      toolbar: { show: false },
      sparkline: { enabled: false },
      animations: { enabled: true, easing: 'easeinout', speed: 600 },
    },
    theme: { mode: 'dark' },
    series: [{ name: 'Pelanggaran', data: chartData }],
    xaxis: {
      categories: chartMonths,
      labels: {
        style: { colors: '#8B96AB', fontSize: '0.78rem', fontFamily: 'Inter' },
      },
      axisBorder: { show: false }, axisTicks: { show: false },
    },
    yaxis: {
      labels: { style: { colors: '#8B96AB', fontSize: '0.78rem', fontFamily: 'Inter' } },
      min: 0,
    },
    stroke: { curve: 'smooth', width: 2, colors: ['#EF5A5A'] },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02,
        stops: [0, 100], colorStops: [
          { offset: 0,   color: '#EF5A5A', opacity: 0.35 },
          { offset: 100, color: '#EF5A5A', opacity: 0.02 },
        ],
      },
    },
    markers: { size: 4, colors: ['#EF5A5A'], strokeWidth: 0, hover: { size: 6 } },
    grid: {
      borderColor: 'rgba(231,236,245,0.06)',
      strokeDashArray: 3,
      xaxis: { lines: { show: false } },
    },
    tooltip: {
      theme: 'dark',
      y: { formatter: val => val + ' kasus' },
    },
    dataLabels: { enabled: false },
  }).render();
})();
</script>
@endsection

