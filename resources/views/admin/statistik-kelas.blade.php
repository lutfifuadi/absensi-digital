@extends('layouts/layoutMaster')

@section('title', 'Statistik & Perbandingan Kelas')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Dashboard /</span> Statistik Kelas
</h4>

<div class="card mb-4 border-0 shadow-sm">
  <div class="card-body">
    <form action="{{ route('admin.statistik-kelas') }}" method="GET" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label">Pilih Bulan</label>
        <select name="month" class="form-select">
          @for($m=1; $m<=12; $m++)
            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2025, $m, 1)->translatedFormat('F') }}</option>
          @endfor
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Tahun</label>
        <select name="year" class="form-select">
          <option value="2025" {{ $year == 2025 ? 'selected' : '' }}>2025</option>
          <option value="2026" {{ $year == 2026 ? 'selected' : '' }}>2026</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">
          <i class="ti tabler-filter me-1"></i> Filter
        </button>
      </div>
    </form>
  </div>
</div>

<div class="row g-4 mb-4">
  {{-- RANKING KELAS TABLE --}}
  <div class="col-xl-8">
    <div class="card border-0 shadow-sm overflow-hidden h-100">
      <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 d-flex align-items-center">
          <i class="ti tabler-award me-2 text-warning"></i> Ranking Kehadiran Kelas
        </h5>
        <span class="badge bg-label-secondary">Berdasarkan % Hadir</span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th width="70" class="text-center">RANK</th>
              <th>KELAS</th>
              <th class="text-center">TOTAL SISWA</th>
              <th class="text-center">% HADIR</th>
              <th width="200">VISUALISASI</th>
              <th class="text-center">STATUS</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rankingKelas as $index => $rk)
              <tr>
                <td class="text-center font-weight-bold">
                  {!! $index < 3 ? ['🥇','🥈','🥉'][$index] : ($index + 1) !!}
                </td>
                <td><div class="fw-bold">{{ $rk['nama'] }}</div></td>
                <td class="text-center">{{ $rk['total_siswa'] }}</td>
                <td class="text-center fw-bold">{{ $rk['percentage'] }}%</td>
                <td>
                  @php
                    $color = $rk['percentage'] >= 90 ? 'success' : ($rk['percentage'] >= 75 ? 'warning' : 'danger');
                  @endphp
                  <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $rk['percentage'] }}%" aria-valuenow="{{ $rk['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge bg-label-{{ $color }} rounded-pill">
                    <i class="ti tabler-point-filled me-1"></i> {{ $rk['percentage'] >= 90 ? 'Bagus' : ($rk['percentage'] >= 75 ? 'Cukup' : 'Kurang') }}
                  </span>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center py-4">Belum ada data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- HORIZONTAL BAR CHART --}}
  <div class="col-xl-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title mb-0 d-flex align-items-center">
          <i class="ti tabler-chart-bar me-2 text-primary"></i> Perbandingan Persentase
        </h5>
      </div>
      <div class="card-body d-flex align-items-center">
        <div id="chartKelasCompare" style="width: 100%;"></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  {{-- TOP 10 SISWA RAJIN --}}
  <div class="col-md-6">
    <div class="card border-0 shadow-sm overflow-hidden h-100">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title mb-0 d-flex align-items-center">
          <i class="ti tabler-crown me-2 text-warning"></i> 10 Siswa Paling Rajin
        </h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th width="50" class="text-center">NO</th>
              <th>NAMA SISWA</th>
              <th>KELAS</th>
              <th class="text-center">HADIR</th>
            </tr>
          </thead>
          <tbody>
            @forelse($topSiswa as $index => $ts)
              <tr>
                <td class="text-center text-muted">{{ $index + 1 }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($ts->siswa->nama_lengkap) }}&background=667eea&color=fff" class="rounded-circle" width="28">
                    <div class="fw-bold">{{ $ts->siswa->nama_lengkap }}</div>
                  </div>
                </td>
                <td><span class="badge bg-label-secondary small">{{ $ts->siswa->kelas->nama ?? '-' }}</span></td>
                <td class="text-center"><span class="badge bg-success">{{ $ts->total_hadir }} Hari</span></td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center py-4">Belum ada data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- EARLY WARNING ALPHA --}}
  <div class="col-md-6">
    <div class="card border-0 shadow-sm overflow-hidden h-100">
      <div class="card-header bg-danger bg-opacity-10 border-bottom border-danger border-opacity-25 py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 text-danger d-flex align-items-center">
          <i class="ti tabler-alert-triangle me-2"></i> Early Warning (Alpha &ge; 3)
        </h5>
        <span class="badge bg-danger">{{ count($warningSiswa) }} Siswa</span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th width="50" class="text-center">NO</th>
              <th>NAMA SISWA</th>
              <th>KELAS</th>
              <th class="text-center">JML ALPHA</th>
              <th class="text-center">LEVEL</th>
            </tr>
          </thead>
          <tbody>
            @forelse($warningSiswa as $index => $ws)
              <tr>
                <td class="text-center text-muted">{{ $index + 1 }}</td>
                <td>
                  <div class="fw-bold">{{ $ws->siswa->nama_lengkap }}</div>
                  <div class="small text-muted">{{ $ws->siswa->nis }}</div>
                </td>
                <td>{{ $ws->siswa->kelas->nama ?? '-' }}</td>
                <td class="text-center fw-bold text-danger">{{ $ws->total_alpha }} Hari</td>
                <td class="text-center">
                  @php
                    $level = $ws->total_alpha >= 7 ? ['red', 'Kritis'] : ($ws->total_alpha >= 5 ? ['orange', 'Peringatan'] : ['yellow', 'Waspada']);
                  @endphp
                  <span class="badge rounded-pill bg-{{ $level[0] }}">{{ $level[1] }}</span>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center py-5 text-muted italic">Tidak ada siswa dengan tingkat alpha mengkhawatirkan bulan ini.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-footer bg-light small italic">
        Siswa dalam daftar ini perlu segera mendapatkan pembinaan dari Wali Kelas atau Guru BK.
      </div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const options = {
      series: [{
        name: 'Persentase Hadir',
        data: @json(collect($rankingKelas)->pluck('percentage'))
      }],
      chart: { type: 'bar', height: 400, toolbar: { show: false } },
      plotOptions: {
        bar: {
          borderRadius: 4,
          horizontal: true,
          distributed: true,
        }
      },
      dataLabels: {
        enabled: true,
        formatter: (v) => v + '%',
        style: { fontSize: '12px', colors: ['#fff'] }
      },
      colors: @json(collect($rankingKelas)->map(fn($rk) => $rk['percentage'] >= 90 ? '#28c76f' : ($rk['percentage'] >= 75 ? '#ff9f43' : '#ea5455'))),
      xaxis: {
        categories: @json(collect($rankingKelas)->pluck('nama')),
        labels: { show: false },
        axisBorder: { show: false }
      },
      legend: { show: false },
      tooltip: { theme: 'dark', y: { formatter: (v) => v + '%' } }
    };

    const chart = new ApexCharts(document.querySelector("#chartKelasCompare"), options);
    chart.render();
  });
</script>
@endpush
