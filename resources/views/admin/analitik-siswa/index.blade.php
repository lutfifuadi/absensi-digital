@extends('layouts/layoutMaster')

@section('title', 'Grafik & Analitik Kehadiran Siswa')

@section('page-style')
<style>
  .analytics-kpi-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .analytics-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important;
  }
  .chart-card-loader {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(2px);
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
  }
</style>
@endsection

@section('content')
{{-- HEADER PAGE --}}
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
  <div>
    <h4 class="fw-bold mb-1 d-flex align-items-center">
      <i class="ti tabler-chart-dots me-2 text-primary fs-3"></i> Grafik & Analitik Kehadiran Siswa
    </h4>
    <p class="text-muted mb-0 small">Visualisasi komprehensif data kedisiplinan, tren kedatangan, dan perbandingan kehadiran siswa.</p>
  </div>
  <div class="d-flex gap-2">
    <button id="btn-refresh-data" class="btn btn-label-secondary d-flex align-items-center">
      <i class="ti tabler-refresh me-1"></i> Refresh Data
    </button>
    <button onclick="window.print()" class="btn btn-primary d-flex align-items-center">
      <i class="ti tabler-printer me-1"></i> Cetak Laporan
    </button>
  </div>
</div>

{{-- PANEL FILTER DINAMIS --}}
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <form id="filter-form" class="row g-3 align-items-end">
      <div class="col-12 col-md-3">
        <label class="form-label fw-medium"><i class="ti tabler-calendar me-1"></i> Rentang Waktu</label>
        <select id="filter-preset" class="form-select">
          <option value="30_days" selected>30 Hari Terakhir</option>
          <option value="7_days">7 Hari Terakhir</option>
          <option value="this_month">Bulan Ini</option>
          <option value="custom">Custom Tanggal</option>
        </select>
      </div>
      <div class="col-12 col-md-2 d-none" id="custom-date-container">
        <label class="form-label fw-medium">Tanggal Mulai & Selesai</label>
        <div class="input-group">
          <input type="date" id="filter-start-date" class="form-control" value="{{ \Carbon\Carbon::now()->subDays(29)->format('Y-m-d') }}">
          <input type="date" id="filter-end-date" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
        </div>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label fw-medium"><i class="ti tabler-school me-1"></i> Tingkat Kelas</label>
        <select id="filter-tingkat" class="form-select">
          <option value="all">Semua Tingkat</option>
          <option value="10">Kelas 10 (X)</option>
          <option value="11">Kelas 11 (XI)</option>
          <option value="12">Kelas 12 (XII)</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label fw-medium"><i class="ti tabler-door me-1"></i> Kelas</label>
        <select id="filter-kelas" class="form-select">
          <option value="all">Semua Kelas</option>
          @foreach($kelases as $kls)
            <option value="{{ $kls->id }}">{{ $kls->nama }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label fw-medium"><i class="ti tabler-books me-1"></i> Jurusan</label>
        <select id="filter-jurusan" class="form-select">
          <option value="all">Semua Jurusan</option>
          @foreach($jurusans as $jrs)
            <option value="{{ $jrs->id }}">{{ $jrs->nama }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100">
          <i class="ti tabler-filter me-1"></i> Terapkan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- KPI SUMMARY CARDS --}}
<div class="row g-4 mb-4">
  {{-- KPI 1: PERSENTASE KEHADIRAN --}}
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm analytics-kpi-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted fw-medium small">Tingkat Kehadiran</span>
          <div class="avatar avatar-sm bg-label-success rounded">
            <i class="ti tabler-circle-check fs-4"></i>
          </div>
        </div>
        <h3 class="fw-bold mb-1" id="kpi-persentase-kehadiran">0%</h3>
        <p class="mb-2 text-muted small"><span id="kpi-total-hadir" class="fw-semibold text-success">0</span> presensi dicatat</p>
        <div class="progress" style="height: 6px;">
          <div id="kpi-progress-kehadiran" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
        </div>
      </div>
    </div>
  </div>

  {{-- KPI 2: KETEPATAN WAKTU --}}
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm analytics-kpi-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted fw-medium small">Ketepatan Waktu</span>
          <div class="avatar avatar-sm bg-label-warning rounded">
            <i class="ti tabler-clock fs-4"></i>
          </div>
        </div>
        <h3 class="fw-bold mb-1" id="kpi-persentase-tepat-waktu">0%</h3>
        <p class="mb-2 text-muted small"><span id="kpi-total-terlambat" class="fw-semibold text-warning">0</span> terlambat</p>
        <div class="progress" style="height: 6px;">
          <div id="kpi-progress-tepat-waktu" class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
        </div>
      </div>
    </div>
  </div>

  {{-- KPI 3: IZIN & SAKIT --}}
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm analytics-kpi-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted fw-medium small">Izin & Sakit</span>
          <div class="avatar avatar-sm bg-label-info rounded">
            <i class="ti tabler-file-text fs-4"></i>
          </div>
        </div>
        <h3 class="fw-bold mb-1" id="kpi-count-izin-sakit">0</h3>
        <p class="mb-0 text-muted small">Surat keterangan terverifikasi</p>
      </div>
    </div>
  </div>

  {{-- KPI 4: ALPA & JAM PEAK --}}
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm analytics-kpi-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted fw-medium small">Alpa / Tanpa Ket.</span>
          <div class="avatar avatar-sm bg-label-danger rounded">
            <i class="ti tabler-alert-triangle fs-4"></i>
          </div>
        </div>
        <h3 class="fw-bold mb-1 text-danger" id="kpi-count-alpa">0</h3>
        <p class="mb-0 text-muted small">Jam Peak Scan: <strong id="kpi-peak-hour" class="text-dark">-</strong></p>
      </div>
    </div>
  </div>
</div>

{{-- SECTION GRAFIK ROW 1 --}}
<div class="row g-4 mb-4">
  {{-- CHART 1: TREN KEHADIRAN HARIAN --}}
  <div class="col-12 col-xl-8">
    <div class="card border-0 shadow-sm position-relative h-100">
      <div id="loader-chart-trend" class="chart-card-loader d-none">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
      </div>
      <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <div>
          <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-chart-line me-2 text-primary"></i> Tren Kehadiran Harian</h5>
          <span class="text-muted small">Fluktuasi status presensi siswa sepanjang periode terpilih</span>
        </div>
      </div>
      <div class="card-body pt-4">
        <div id="chart-trend-harian" style="min-height: 330px;"></div>
      </div>
    </div>
  </div>

  {{-- CHART 2: DISTRIBUSI STATUS KEHADIRAN --}}
  <div class="col-12 col-xl-4">
    <div class="card border-0 shadow-sm position-relative h-100">
      <div id="loader-chart-status" class="chart-card-loader d-none">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
      </div>
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-chart-donut me-2 text-primary"></i> Proporsi Status</h5>
        <span class="text-muted small">Perbandingan persentase status</span>
      </div>
      <div class="card-body pt-4 d-flex align-items-center justify-content-center">
        <div id="chart-status-donut" class="w-100" style="min-height: 330px;"></div>
      </div>
    </div>
  </div>
</div>

{{-- SECTION GRAFIK ROW 2 --}}
<div class="row g-4 mb-4">
  {{-- CHART 3: PERBANDINGAN KEHADIRAN ANTAK KELAS --}}
  <div class="col-12 col-xl-6">
    <div class="card border-0 shadow-sm position-relative h-100">
      <div id="loader-chart-kelas" class="chart-card-loader d-none">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
      </div>
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-chart-bar me-2 text-primary"></i> Perbandingan Kehadiran per Kelas</h5>
        <span class="text-muted small">Statistik status presensi untuk 10 kelas teratas</span>
      </div>
      <div class="card-body pt-4">
        <div id="chart-kelas-bar" style="min-height: 320px;"></div>
      </div>
    </div>
  </div>

  {{-- CHART 4: SEBARAN JAM KEDATANGAN (PEAK HOURS) --}}
  <div class="col-12 col-xl-6">
    <div class="card border-0 shadow-sm position-relative h-100">
      <div id="loader-chart-jam" class="chart-card-loader d-none">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
      </div>
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-clock-play me-2 text-primary"></i> Sebaran Waktu Kedatangan</h5>
        <span class="text-muted small">Distribusi jam siswa melakukan scan presensi</span>
      </div>
      <div class="card-body pt-4">
        <div id="chart-jam-column" style="min-height: 320px;"></div>
      </div>
    </div>
  </div>
</div>

{{-- SECTION GRAFIK ROW 3 & RANKING --}}
<div class="row g-4 mb-4">
  {{-- CHART 5: POLA HARI DALAM SEMINGGU (RADAR CHART) --}}
  <div class="col-12 col-xl-6">
    <div class="card border-0 shadow-sm position-relative h-100">
      <div id="loader-chart-radar" class="chart-card-loader d-none">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
      </div>
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-radar me-2 text-primary"></i> Pola Kedisiplinan per Hari (Senin - Sabtu)</h5>
        <span class="text-muted small">Analisis hari mana yang paling sering terjadi keterlambatan/alpa</span>
      </div>
      <div class="card-body pt-4 d-flex align-items-center justify-content-center">
        <div id="chart-radar-days" class="w-100" style="min-height: 320px;"></div>
      </div>
    </div>
  </div>

  {{-- TABEL RANKING SISWA SERING TERLAMBAT / ALPA --}}
  <div class="col-12 col-xl-6">
    <div class="card border-0 shadow-sm overflow-hidden h-100">
      <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <div>
          <h5 class="card-title mb-0 fw-bold text-danger"><i class="ti tabler-user-exclamation me-2"></i> Siswa Membutuhkan Perhatian BK / Wali Kelas</h5>
          <span class="text-muted small">Top 5 siswa dengan akumulasi keterlambatan & alpa tertinggi</span>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>SISWA</th>
              <th>KELAS</th>
              <th class="text-center">TERLAMBAT</th>
              <th class="text-center">ALPA</th>
              <th class="text-center">TOTAL</th>
            </tr>
          </thead>
          <tbody id="ranking-siswa-body">
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">Memuat data ranking...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Chart Instance Holders
  let chartTrend, chartStatus, chartKelas, chartJam, chartRadar;

  // Toggle Custom Date Inputs
  const presetSelect = document.getElementById('filter-preset');
  const customDateContainer = document.getElementById('custom-date-container');

  presetSelect.addEventListener('change', function () {
    if (this.value === 'custom') {
      customDateContainer.classList.remove('d-none');
    } else {
      customDateContainer.classList.add('d-none');
    }
  });

  // Calculate Dates based on Preset
  function getFilterDates() {
    const preset = presetSelect.value;
    const today = new Date();
    let startDate = new Date();
    let endDate = today;

    if (preset === '7_days') {
      startDate.setDate(today.getDate() - 6);
    } else if (preset === '30_days') {
      startDate.setDate(today.getDate() - 29);
    } else if (preset === 'this_month') {
      startDate = new Date(today.getFullYear(), today.getMonth(), 1);
    } else if (preset === 'custom') {
      const startVal = document.getElementById('filter-start-date').value;
      const endVal = document.getElementById('filter-end-date').value;
      if (startVal) startDate = new Date(startVal);
      if (endVal) endDate = new Date(endVal);
    }

    const formatDateStr = (d) => d.toISOString().split('T')[0];
    return {
      start_date: formatDateStr(startDate),
      end_date: formatDateStr(endDate)
    };
  }

  // Show/Hide Loaders
  function toggleLoaders(show) {
    document.querySelectorAll('.chart-card-loader').forEach(el => {
      if (show) el.classList.remove('d-none');
      else el.classList.add('d-none');
    });
  }

  // 1. Initialize ApexCharts Configurations
  function initCharts() {
    // Chart 1: Tren Harian
    const trendOptions = {
      series: [
        { name: 'Hadir Tepat Waktu', data: [] },
        { name: 'Terlambat', data: [] },
        { name: 'Izin / Sakit', data: [] },
        { name: 'Alpa', data: [] }
      ],
      chart: { type: 'area', height: 330, toolbar: { show: true }, zoom: { enabled: false } },
      colors: ['#28c76f', '#ff9f43', '#00cfdd', '#ea5455'],
      stroke: { curve: 'smooth', width: 2 },
      fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
      xaxis: { categories: [] },
      legend: { position: 'top', horizontalAlign: 'right' },
      dataLabels: { enabled: false },
      tooltip: { theme: 'light' }
    };
    chartTrend = new ApexCharts(document.querySelector("#chart-trend-harian"), trendOptions);
    chartTrend.render();

    // Chart 2: Status Donut
    const statusOptions = {
      series: [0, 0, 0, 0, 0],
      labels: ['Tepat Waktu', 'Terlambat', 'Izin', 'Sakit', 'Alpa'],
      chart: { type: 'donut', height: 330 },
      colors: ['#28c76f', '#ff9f43', '#00cfdd', '#7367f0', '#ea5455'],
      legend: { position: 'bottom' },
      dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' },
      plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total' } } } } }
    };
    chartStatus = new ApexCharts(document.querySelector("#chart-status-donut"), statusOptions);
    chartStatus.render();

    // Chart 3: Perbandingan Kelas (Stacked / Grouped Bar)
    const kelasOptions = {
      series: [
        { name: 'Tepat Waktu', data: [] },
        { name: 'Terlambat', data: [] },
        { name: 'Alpa', data: [] }
      ],
      chart: { type: 'bar', height: 320, toolbar: { show: false } },
      colors: ['#28c76f', '#ff9f43', '#ea5455'],
      plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } },
      dataLabels: { enabled: false },
      stroke: { show: true, width: 2, colors: ['transparent'] },
      xaxis: { categories: [] },
      legend: { position: 'top' },
      tooltip: { theme: 'light' }
    };
    chartKelas = new ApexCharts(document.querySelector("#chart-kelas-bar"), kelasOptions);
    chartKelas.render();

    // Chart 4: Sebaran Jam Kedatangan (Column)
    const jamOptions = {
      series: [{ name: 'Jumlah Scan', data: [] }],
      chart: { type: 'bar', height: 320, toolbar: { show: false } },
      colors: ['#7367f0'],
      plotOptions: { bar: { borderRadius: 4, distributed: true, columnWidth: '50%' } },
      colors: ['#28c76f', '#28c76f', '#7367f0', '#ff9f43', '#ea5455'],
      dataLabels: { enabled: true },
      xaxis: { categories: [] },
      legend: { show: false },
      tooltip: { theme: 'light' }
    };
    chartJam = new ApexCharts(document.querySelector("#chart-jam-column"), jamOptions);
    chartJam.render();

    // Chart 5: Pola Hari (Radar)
    const radarOptions = {
      series: [
        { name: 'Hadir', data: [] },
        { name: 'Terlambat', data: [] },
        { name: 'Alpa', data: [] }
      ],
      chart: { type: 'radar', height: 320, toolbar: { show: false } },
      colors: ['#28c76f', '#ff9f43', '#ea5455'],
      stroke: { width: 2 },
      fill: { opacity: 0.2 },
      markers: { size: 4 },
      xaxis: { categories: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] }
    };
    chartRadar = new ApexCharts(document.querySelector("#chart-radar-days"), radarOptions);
    chartRadar.render();
  }

  // 2. Fetch Data from Backend API
  function loadAnalyticsData() {
    toggleLoaders(true);

    const dates = getFilterDates();
    const params = new URLSearchParams({
      start_date: dates.start_date,
      end_date: dates.end_date,
      tingkat: document.getElementById('filter-tingkat').value,
      kelas_id: document.getElementById('filter-kelas').value,
      jurusan_id: document.getElementById('filter-jurusan').value,
    });

    fetch(`{{ route('admin.analitik-siswa.data') }}?${params.toString()}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => response.json())
    .then(res => {
      toggleLoaders(false);
      if (!res.success) return;

      // Update KPI
      document.getElementById('kpi-persentase-kehadiran').innerText = res.kpi.persentase_kehadiran + '%';
      document.getElementById('kpi-total-hadir').innerText = res.kpi.count_hadir;
      document.getElementById('kpi-progress-kehadiran').style.width = res.kpi.persentase_kehadiran + '%';

      document.getElementById('kpi-persentase-tepat-waktu').innerText = res.kpi.persentase_tepat_waktu + '%';
      document.getElementById('kpi-total-terlambat').innerText = res.kpi.count_terlambat;
      document.getElementById('kpi-progress-tepat-waktu').style.width = res.kpi.persentase_tepat_waktu + '%';

      document.getElementById('kpi-count-izin-sakit').innerText = res.kpi.count_izin_sakit;
      document.getElementById('kpi-count-alpa').innerText = res.kpi.count_alpa;
      document.getElementById('kpi-peak-hour').innerText = res.kpi.peak_hour;

      // Update Chart 1: Tren
      chartTrend.updateOptions({
        xaxis: { categories: res.chart_trend.labels }
      });
      chartTrend.updateSeries([
        { name: 'Hadir Tepat Waktu', data: res.chart_trend.hadir },
        { name: 'Terlambat', data: res.chart_trend.terlambat },
        { name: 'Izin / Sakit', data: res.chart_trend.izin_sakit },
        { name: 'Alpa', data: res.chart_trend.alpa }
      ]);

      // Update Chart 2: Status
      chartStatus.updateSeries(res.chart_status.series);

      // Update Chart 3: Kelas
      chartKelas.updateOptions({
        xaxis: { categories: res.chart_kelas.labels }
      });
      chartKelas.updateSeries([
        { name: 'Tepat Waktu', data: res.chart_kelas.hadir },
        { name: 'Terlambat', data: res.chart_kelas.terlambat },
        { name: 'Alpa', data: res.chart_kelas.alpa }
      ]);

      // Update Chart 4: Jam
      chartJam.updateOptions({
        xaxis: { categories: res.chart_jam.labels }
      });
      chartJam.updateSeries([
        { name: 'Jumlah Scan', data: res.chart_jam.series }
      ]);

      // Update Chart 5: Radar
      chartRadar.updateSeries([
        { name: 'Hadir', data: res.chart_radar.hadir },
        { name: 'Terlambat', data: res.chart_radar.terlambat },
        { name: 'Alpa', data: res.chart_radar.alpa }
      ]);

      // Update Ranking Table
      const rankingBody = document.getElementById('ranking-siswa-body');
      rankingBody.innerHTML = '';
      if (!res.ranking_siswa || res.ranking_siswa.length === 0) {
        rankingBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-success"><i class="ti tabler-circle-check me-1"></i> Tidak ada siswa dengan akumulasi alpa/terlambat tinggi.</td></tr>';
      } else {
        res.ranking_siswa.forEach(row => {
          rankingBody.innerHTML += `
            <tr>
              <td class="fw-semibold text-dark">${row.nama}</td>
              <td><span class="badge bg-label-secondary">${row.kelas}</span></td>
              <td class="text-center"><span class="badge bg-label-warning">${row.terlambat}</span></td>
              <td class="text-center"><span class="badge bg-label-danger">${row.alpa}</span></td>
              <td class="text-center"><strong class="text-danger">${row.total}</strong></td>
            </tr>
          `;
        });
      }
    })
    .catch(err => {
      toggleLoaders(false);
      console.error('Error fetching analytics:', err);
    });
  }

  // Event Listeners
  document.getElementById('filter-form').addEventListener('submit', function (e) {
    e.preventDefault();
    loadAnalyticsData();
  });

  document.getElementById('btn-refresh-data').addEventListener('click', function () {
    loadAnalyticsData();
  });

  // Init
  initCharts();
  loadAnalyticsData();
});
</script>
@endpush
