@extends('layouts/layoutMaster')

@section('title', 'Dashboard Analitik & Alert')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Dashboard /</span> Analitik & Alert
</h4>

<div class="row mb-4">
  <div class="col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-lg me-3 bg-label-success">
            <i class="ti tabler-users"></i>
          </div>
          <div>
            <small class="text-muted">Total Kelas</small>
            <h4 class="mb-0 fw-bold" id="totalKelas">-</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-lg me-3 bg-label-primary">
            <i class="ti tabler-checkbox"></i>
          </div>
          <div>
            <small class="text-muted">Rata-rata Kehadiran</small>
            <h4 class="mb-0 fw-bold" id="avgKehadiran">-</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-lg me-3 bg-label-warning">
            <i class="ti tabler-clock"></i>
          </div>
          <div>
            <small class="text-muted">Rata-rata Terlambat</small>
            <h4 class="mb-0 fw-bold" id="avgTerlambat">-</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-lg me-3 bg-label-danger">
            <i class="ti tabler-alert-triangle"></i>
          </div>
          <div>
            <small class="text-muted">Kelas Bermasalah</small>
            <h4 class="mb-0 fw-bold" id="kelasBermasalah">-</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row mb-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header py-3">
        <h5 class="mb-0"><i class="ti tabler-calendar-stats me-2"></i>Filter Tanggal</h5>
      </div>
      <div class="card-body">
        <input type="date" id="filterDate" class="form-control" value="{{ now()->toDateString() }}">
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header py-3">
        <h5 class="mb-0"><i class="ti tabler-school me-2"></i>Filter Kelas</h5>
      </div>
      <div class="card-body">
        <select id="filterKelas" class="form-select">
          <option value="">Semua Kelas</option>
        </select>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header d-flex justify-content-between align-items-center py-3">
    <h5 class="mb-0"><i class="ti tabler-chart-bar me-2"></i>Data Absensi per Kelas</h5>
    <button class="btn btn-primary btn-sm" onclick="loadAnalytics()">
      <i class="ti tabler-refresh me-1"></i> Refresh
    </button>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="analyticsTable">
      <thead class="table-light">
        <tr>
          <th>Kelas</th>
          <th class="text-center">Total</th>
          <th class="text-center text-success">Hadir</th>
          <th class="text-center text-warning">Terlambat</th>
          <th class="text-center text-info">Sakit</th>
          <th class="text-center text-primary">Izin</th>
          <th class="text-center text-danger">Alpha</th>
          <th class="text-center">% Kehadiran</th>
          <th class="text-center">Status</th>
        </tr>
      </thead>
      <tbody id="analyticsBody">
        <tr>
          <td colspan="9" class="text-center py-4 text-muted">
            <i class="ti tabler-loader-2 me-2"></i>Memuat data...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header py-3">
    <h5 class="mb-0"><i class="ti tabler-chart-line me-2"></i>Tren Mingguan</h5>
  </div>
  <div class="card-body">
    <canvas id="weeklyChart" height="100"></canvas>
  </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let weeklyChart = null;

document.addEventListener('DOMContentLoaded', function() {
  loadKelas();
  loadAnalytics();
  
  document.getElementById('filterDate').addEventListener('change', loadAnalytics);
  document.getElementById('filterKelas').addEventListener('change', loadAnalytics);
});

async function loadKelas() {
  try {
    const response = await fetch('/api/v1/kelas');
    const result = await response.json();
    
    if (result.data) {
      const select = document.getElementById('filterKelas');
      const totalKelas = document.getElementById('totalKelas');
      totalKelas.textContent = result.data.length;
      
      result.data.forEach(kelas => {
        const option = document.createElement('option');
        option.value = kelas.id;
        option.textContent = kelas.nama;
        select.appendChild(option);
      });
    }
  } catch (e) {
    console.error('Error loading kelas:', e);
  }
}

async function loadAnalytics() {
  const date = document.getElementById('filterDate').value;
  const kelasId = document.getElementById('filterKelas').value;
  
  try {
    const response = await fetch(`/api/v1/innovation/analytics?date=${date}&kelas_id=${kelasId || ''}`);
    const result = await response.json();
    
    const tbody = document.getElementById('analyticsBody');
    const data = result.data || [];
    
    if (data.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center py-4 text-muted">
            <i class="ti tabler-info-circle me-2"></i>Belum ada data untuk tanggal ini.
          </td>
        </tr>
      `;
      updateStats(0, 0, 0, 0);
      return;
    }
    
    let totalKehadiran = 0;
    let totalTerlambat = 0;
    let kelasBermasalah = 0;
    
    tbody.innerHTML = data.map(item => {
      totalKehadiran += parseFloat(item.persentase_kehadiran || 0);
      totalTerlambat += parseFloat(item.persentase_keterlambatan || 0);
      if (item.alert_triggered) kelasBermasalah++;
      
      const percentage = parseFloat(item.persentase_kehadiran || 0);
      let badgeClass = 'bg-success';
      let badgeText = 'Normal';
      
      if (percentage < 75) {
        badgeClass = 'bg-danger';
        badgeText = 'Bermasalah';
      } else if (percentage < 90) {
        badgeClass = 'bg-warning';
        badgeText = 'Waspada';
      }
      
      return `
        <tr>
          <td class="fw-medium">${item.kelas?.nama || '-'}</td>
          <td class="text-center">${item.total_students}</td>
          <td class="text-center text-success">${item.hadir_tepat_waktu}</td>
          <td class="text-center text-warning">${item.terlambat}</td>
          <td class="text-center text-info">${item.sakit}</td>
          <td class="text-center text-primary">${item.izin}</td>
          <td class="text-center text-danger">${item.alpha}</td>
          <td class="text-center">
            <span class="badge ${badgeClass}">${percentage.toFixed(1)}%</span>
          </td>
          <td class="text-center">
            ${item.alert_triggered ? '<span class="badge bg-danger"><i class="ti tabler-alert-triangle me-1"></i>Alert</span>' : '-'}
          </td>
        </tr>
      `;
    }).join('');
    
    const avgKehadiran = data.length > 0 ? (totalKehadiran / data.length) : 0;
    const avgTerlambat = data.length > 0 ? (totalTerlambat / data.length) : 0;
    
    updateStats(avgKehadiran, avgTerlambat, kelasBermasalah, data.length);
    updateWeeklyChart(data);
    
  } catch (e) {
    console.error('Error loading analytics:', e);
  }
}

function updateStats(kehadiran, terlambat, bermasalah, total) {
  document.getElementById('avgKehadiran').textContent = kehadiran.toFixed(1) + '%';
  document.getElementById('avgTerlambat').textContent = terlambat.toFixed(1) + '%';
  document.getElementById('kelasBermasalah').textContent = bermasalah;
}

function updateWeeklyChart(data) {
  const ctx = document.getElementById('weeklyChart').getContext('2d');
  
  if (weeklyChart) {
    weeklyChart.destroy();
  }
  
  const labels = data.map(d => d.kelas?.nama || 'Unknown');
  const kehadiran = data.map(d => parseFloat(d.persentase_kehadiran || 0));
  
  weeklyChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: '% Kehadiran',
        data: kehadiran,
        backgroundColor: '#26a69a',
        borderRadius: 4
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          max: 100
        }
      }
    }
  });
}
</script>
@endsection