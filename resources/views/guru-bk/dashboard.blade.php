@extends('layouts/layoutMaster')

@section('title', 'Dashboard Guru BK')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/guru-bk.css') }}?v=1.0">
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

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
        <div class="das-hero__logo-placeholder" aria-hidden="true">
          <i class="ti tabler-user-heart"></i>
        </div>
        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="das-hero__pulse-dot" aria-hidden="true"></span>
            Bimbingan &amp; Konseling
          </div>
          <h1 class="das-hero__school">Dashboard Guru BK</h1>
          <p class="das-hero__welcome">
            Selamat datang, <strong>{{ auth()->user()->name }}</strong> 👋
            &nbsp;|&nbsp;
            <span class="text-muted" style="font-size:0.82rem;">
              {{ $tahunAkademik ? $tahunAkademik->nama . ' — ' . ($tahunAkademik->semester ?? '') : 'Tahun Akademik Aktif' }}
            </span>
          </p>
          {{-- Quick Action Buttons --}}
          <div class="d-flex flex-wrap gap-2 mt-3">
            <a href="{{ route('bk.pelanggaran.create') }}" class="das-btn das-btn--danger">
              <i class="ti tabler-plus"></i> Input Pelanggaran
            </a>
            <a href="{{ route('bk.sp.create') }}" class="das-btn das-btn--warning">
              <i class="ti tabler-file-certificate"></i> Terbitkan SP
            </a>
            <a href="{{ route('bk.rekap.index') }}" class="das-btn das-btn--ghost">
              <i class="ti tabler-chart-bar"></i> Lihat Rekap
            </a>
          </div>
        </div>
      </div>

      {{-- Live Clock --}}
      <div class="das-hero__clock" role="status" aria-live="off">
        <div class="das-hero__date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        <div class="das-hero__time">
          <span id="bk-live-clock">00:00:00</span>
          <span class="das-hero__live-badge"><span class="das-hero__pulse-dot" aria-hidden="true"></span>LIVE</span>
        </div>
        <div class="das-hero__tz">WAKTU INDONESIA BARAT (WIB)</div>
      </div>
    </div>
  </div>{{-- /das-hero --}}


  {{-- ═══════════════════════════════════════════
       SECTION A: STAT CARDS — 3 KPI Utama
  ═══════════════════════════════════════════ --}}
  <div class="row g-4 mb-4">
    {{-- Pelanggaran Bulan Ini --}}
    <div class="col-lg-4 col-sm-6">
      <a href="{{ route('bk.pelanggaran.index') }}" class="text-decoration-none">
        <div class="bk-stat-card bk-stat-card--danger">
          <div class="bk-stat-card__icon">
            <i class="ti tabler-alert-triangle"></i>
          </div>
          <div class="bk-stat-card__body">
            <div class="bk-stat-card__val">{{ number_format($summary['totalPelanggaranBulanIni'] ?? 0) }}</div>
            <div class="bk-stat-card__label">Pelanggaran Bulan Ini</div>
            <div class="bk-stat-card__sub">klik untuk lihat detail</div>
          </div>
        </div>
      </a>
    </div>

    {{-- Siswa Dalam Pemantauan --}}
    <div class="col-lg-4 col-sm-6">
      <a href="{{ route('bk.pelanggaran.index') }}" class="text-decoration-none">
        <div class="bk-stat-card bk-stat-card--warning">
          <div class="bk-stat-card__icon">
            <i class="ti tabler-users"></i>
          </div>
          <div class="bk-stat-card__body">
            <div class="bk-stat-card__val">{{ number_format($summary['totalSiswaBermasalah'] ?? 0) }}</div>
            <div class="bk-stat-card__label">Siswa Dalam Pemantauan</div>
            <div class="bk-stat-card__sub">tahun akademik aktif</div>
          </div>
        </div>
      </a>
    </div>

    {{-- SP Diterbitkan --}}
    <div class="col-lg-4 col-sm-6">
      <a href="{{ route('bk.sp.index') }}" class="text-decoration-none">
        <div class="bk-stat-card bk-stat-card--primary">
          <div class="bk-stat-card__icon">
            <i class="ti tabler-file-certificate"></i>
          </div>
          <div class="bk-stat-card__body">
            <div class="bk-stat-card__val">{{ number_format($summary['totalSpDiterbitkan'] ?? 0) }}</div>
            <div class="bk-stat-card__label">Surat Peringatan Diterbitkan</div>
            <div class="bk-stat-card__sub">klik untuk kelola SP</div>
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
  <div class="row g-4">

    {{-- ── COL KIRI ── --}}
    <div class="col-lg-7">

      {{-- Panel: Top Siswa Bermasalah --}}
      <div class="das-panel mb-4">
        <div class="das-panel__head">
          <h2 class="das-panel__title">
            <span class="das-panel__icon-dot das-panel__icon-dot--danger"></span>
            <i class="ti tabler-flame" style="color:var(--das-danger)"></i>
            Top Siswa Bermasalah (Poin Terbanyak)
          </h2>
          <a href="{{ route('bk.rekap.index') }}" class="das-btn das-btn--ghost" style="font-size:0.75rem; padding:0.4rem 0.75rem;">
            <i class="ti tabler-arrow-right"></i> Lihat Semua
          </a>
        </div>
        <div class="das-table-wrap">
          <table class="das-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Siswa</th>
                <th>Kelas</th>
                <th style="text-align:center">Pelanggaran</th>
                <th style="text-align:center">Total Poin</th>
                <th style="text-align:right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($topViolators as $rank => $siswa)
                <tr>
                  <td>
                    <span style="font: 700 0.85rem var(--das-font-mono); color: var(--das-text-dim);">
                      {{ str_pad($rank + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>
                  </td>
                  <td>
                    <div class="das-table__person">
                      <div style="width:32px;height:32px;border-radius:50%;background:var(--das-danger-soft);color:var(--das-danger);display:grid;place-items:center;font:700 0.72rem var(--das-font-body);flex-shrink:0;">
                        {{ strtoupper(substr($siswa->nama_lengkap, 0, 2)) }}
                      </div>
                      <div>
                        <div class="das-table__name" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $siswa->nama_lengkap }}</div>
                        <small style="color:var(--das-text-dim);font-size:0.72rem;">NIS: {{ $siswa->nis ?? '-' }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="das-chip das-chip--info">{{ $siswa->kelas->nama ?? '-' }}</span>
                  </td>
                  <td style="text-align:center;">
                    <span style="font:700 0.85rem var(--das-font-mono);color:var(--das-text);">{{ $siswa->total_pelanggaran }}×</span>
                  </td>
                  <td style="text-align:center;">
                    <span class="das-chip das-chip--danger">{{ $siswa->total_poin }} Poin</span>
                  </td>
                  <td style="text-align:right;">
                    <div class="d-flex gap-1 justify-content-end">
                      <a href="{{ route('bk.sp.create', ['siswa_id' => $siswa->id]) }}"
                         class="das-btn das-btn--warning" style="padding:0.35rem 0.6rem;" title="Terbitkan SP">
                        <i class="ti tabler-file-certificate"></i>
                      </a>
                      <a href="{{ route('bk.pelanggaran.index', ['siswa_id' => $siswa->id]) }}"
                         class="das-btn das-btn--ghost" style="padding:0.35rem 0.6rem;" title="Riwayat">
                        <i class="ti tabler-history"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="das-table__empty">
                    <i class="ti tabler-mood-happy d-block mb-1" style="font-size:2rem;color:var(--das-success);"></i>
                    Belum ada data pelanggaran tercatat.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>{{-- /panel top violators --}}

      {{-- Panel: Chart Tren Pelanggaran 6 Bulan --}}
      <div class="das-panel">
        <div class="das-panel__head">
          <h2 class="das-panel__title">
            <span class="das-panel__icon-dot das-panel__icon-dot--warning"></span>
            <i class="ti tabler-chart-line" style="color:var(--das-warning)"></i>
            Tren Pelanggaran (6 Bulan Terakhir)
          </h2>
        </div>
        <div class="das-panel__body">
          <div id="bkTrendChart" class="das-chart-mount"></div>
        </div>
      </div>{{-- /panel chart --}}

    </div>{{-- /col kiri --}}


    {{-- ── COL KANAN ── --}}
    <div class="col-lg-5">

      {{-- Panel: Quick Access --}}
      <div class="das-panel mb-4">
        <div class="das-panel__head">
          <h2 class="das-panel__title">
            <span class="das-panel__icon-dot das-panel__icon-dot--primary"></span>
            <i class="ti tabler-layout-grid" style="color:var(--das-primary)"></i>
            Menu Cepat
          </h2>
        </div>
        <div class="das-panel__body">
          <div class="bk-quick-grid">
            <a href="{{ route('bk.dashboard') }}" class="bk-quick-item bk-quick-item--primary">
              <i class="ti tabler-dashboard"></i>
              <span>Dashboard</span>
            </a>
            <a href="{{ route('bk.pelanggaran.index') }}" class="bk-quick-item bk-quick-item--danger">
              <i class="ti tabler-alert-triangle"></i>
              <span>Pelanggaran</span>
            </a>
            <a href="{{ route('bk.pelanggaran.create') }}" class="bk-quick-item bk-quick-item--danger">
              <i class="ti tabler-plus"></i>
              <span>Input Baru</span>
            </a>
            <a href="{{ route('bk.sp.index') }}" class="bk-quick-item bk-quick-item--warning">
              <i class="ti tabler-certificate"></i>
              <span>Kelola SP</span>
            </a>
            <a href="{{ route('bk.sp.create') }}" class="bk-quick-item bk-quick-item--warning">
              <i class="ti tabler-file-certificate"></i>
              <span>Terbitkan SP</span>
            </a>
            <a href="{{ route('bk.rekap.index') }}" class="bk-quick-item bk-quick-item--info">
              <i class="ti tabler-chart-pie"></i>
              <span>Rekap</span>
            </a>
          </div>
        </div>
      </div>{{-- /panel quick access --}}

      {{-- Panel: SP Terbaru Diterbitkan --}}
      <div class="das-panel mb-4">
        <div class="das-panel__head">
          <h2 class="das-panel__title">
            <span class="das-panel__icon-dot das-panel__icon-dot--warning"></span>
            <i class="ti tabler-certificate" style="color:var(--das-warning)"></i>
            SP Terbaru Diterbitkan
          </h2>
          <a href="{{ route('bk.sp.index') }}" class="das-btn das-btn--ghost" style="font-size:0.75rem; padding:0.4rem 0.75rem;">
            Kelola SP
          </a>
        </div>
        @forelse($spAktif as $sp)
          <div class="bk-list-item">
            <div class="bk-list-item__main">
              <div class="bk-list-item__name">
                <span class="das-chip das-chip--warning me-2">{{ $sp->level_sp }}</span>
                {{ $sp->siswa->nama_lengkap ?? '-' }}
              </div>
              <div class="bk-list-item__meta">
                Kelas: {{ $sp->siswa->kelas->nama ?? '-' }}
                &bull;
                {{ $sp->tanggal_sp ? \Carbon\Carbon::parse($sp->tanggal_sp)->translatedFormat('d M Y') : '-' }}
              </div>
            </div>
            <span class="das-chip das-chip--danger" style="white-space:nowrap;">{{ $sp->total_poin_saat_sp }} Poin</span>
          </div>
        @empty
          <div class="das-empty-state">
            <i class="ti tabler-file-off"></i>
            Belum ada SP diterbitkan.
          </div>
        @endforelse
      </div>{{-- /panel SP --}}

      {{-- Panel: Rekap Pelanggaran Per Kategori Bulan Ini --}}
      <div class="das-panel">
        <div class="das-panel__head">
          <h2 class="das-panel__title">
            <span class="das-panel__icon-dot das-panel__icon-dot--danger"></span>
            <i class="ti tabler-chart-pie" style="color:var(--das-danger)"></i>
            Kategori Pelanggaran (Bulan Ini)
          </h2>
        </div>
        @php $maxPoin = $rekapKategori->max('total_poin') ?: 1; @endphp
        @forelse($rekapKategori as $kat)
          <div style="padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--das-border);">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div style="font:600 0.85rem/1.3 var(--das-font-body);color:var(--das-text);">
                  {{ $kat->nama_kategori }}
                </div>
                <small style="color:var(--das-text-dim);font-size:0.74rem;">{{ $kat->total_pelanggaran }} kasus dicatat</small>
              </div>
              <span class="das-chip das-chip--primary">{{ $kat->total_poin }} Poin</span>
            </div>
            <div class="bk-cat-bar mt-2">
              <div class="bk-cat-bar__fill" style="width: {{ round(($kat->total_poin / $maxPoin) * 100) }}%"></div>
            </div>
          </div>
        @empty
          <div class="das-empty-state">
            <i class="ti tabler-chart-pie-off"></i>
            Belum ada data kategori bulan ini.
          </div>
        @endforelse
      </div>{{-- /panel kategori --}}

    </div>{{-- /col kanan --}}
  </div>{{-- /row --}}

</div>

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
      height: 220,
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
        style: { colors: '#5B6478', fontSize: '0.72rem', fontFamily: 'Inter' },
      },
      axisBorder: { show: false }, axisTicks: { show: false },
    },
    yaxis: {
      labels: { style: { colors: '#5B6478', fontSize: '0.72rem', fontFamily: 'Inter' } },
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
