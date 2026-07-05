@php
    use App\Support\QrCodeGenerator;
    
    // Get the stats
    $statsRaw = $siswa->absensi()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->toArray();
    $stats = [
        'hadir' => $statsRaw['hadir'] ?? 0,
        'sakit' => $statsRaw['sakit'] ?? 0,
        'izin' => $statsRaw['izin'] ?? 0,
        'alpha' => $statsRaw['alpha'] ?? 0,
        'terlambat' => $statsRaw['terlambat'] ?? 0,
        'total' => array_sum($statsRaw) ?: 1, // avoid div zero
    ];

    // Riwayat absensi paginated
    $absensi = $siswa->absensi()->orderByDesc('tanggal')->paginate(15);

    // Riwayat izin/sakit
    $izinSakit = $siswa->izinSakit()->orderByDesc('created_at')->get();

    // Riwayat kenaikan kelas alumni
    $riwayatAlumni = $siswa->riwayatKenaikanKelas->where('status_akhir', 'alumni')->first();
    $kelasTerakhir = $riwayatAlumni->kelasAsal->nama ?? '-';
    $tahunLulusNama = $riwayatAlumni->tahunAkademikAsal->nama ?? '-';
    $tahunLulusSemester = isset($riwayatAlumni->tahunAkademikAsal->semester) ? ucfirst($riwayatAlumni->tahunAkademikAsal->semester) : '';

    // Qr Code
    if (!$siswa->qr_code) {
        $siswa->qr_code = $siswa->nisn ?: 'ALUMNI-' . $siswa->id;
    }
    $qrImage = QrCodeGenerator::renderDataUri($siswa->qr_code, 150);
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Profil Alumni — ' . $siswa->nama_lengkap)

@section('content')
<div class="das-hero das-hero--with-stats mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <img class="das-hero__logo" src="https://ui-avatars.com/api/?name={{ urlencode($siswa->nama_lengkap) }}&size=120&background=7367f0&color=fff" alt="Avatar">
        <div class="das-hero__logo-glow"></div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Alumni / Detail Profil
        </div>
        <h4 class="das-hero__title text-gradient-gold">{{ $siswa->nama_lengkap }}</h4>
        <p class="das-hero__subtitle">{{ $kelasTerakhir }} • Lulus TA {{ $tahunLulusNama }} {{ $tahunLulusSemester }}</p>
      </div>
    </div>

    <div class="das-hero__actions d-flex gap-2">
      <a href="{{ route('admin.alumni.index') }}" class="das-btn das-btn--primary">
        <i class="ti tabler-arrow-left"></i> Kembali ke Daftar Alumni
      </a>
    </div>
  </div>

  <div class="das-stats-row">
    <div class="das-stat-card das-stat-card--success">
      <div class="das-stat-card__icon"><i class="ti tabler-checkbox"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $stats['hadir'] }}</div>
        <div class="das-stat-card__label">Hadir</div>
      </div>
    </div>
    <div class="das-stat-card das-stat-card--warning">
      <div class="das-stat-card__icon"><i class="ti tabler-clock-pause"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $stats['terlambat'] }}</div>
        <div class="das-stat-card__label">Terlambat</div>
      </div>
    </div>
    <div class="das-stat-card das-stat-card--info">
      <div class="das-stat-card__icon"><i class="ti tabler-file-description"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $stats['izin'] + $stats['sakit'] }}</div>
        <div class="das-stat-card__label">Izin/Sakit</div>
      </div>
    </div>
    <div class="das-stat-card das-stat-card--danger">
      <div class="das-stat-card__icon"><i class="ti tabler-x"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $stats['alpha'] }}</div>
        <div class="das-stat-card__label">Alpha</div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-4 col-lg-5">
    <div class="das-panel mb-4">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot --primary"></span>
          Informasi Personal
        </div>
      </div>
      <div class="das-panel__body">
        <ul class="list-unstyled mb-0">
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">NISN</span>
            <span class="text-white fw-bold">{{ $siswa->nisn ?? '-' }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Jenis Kelamin</span>
            <span class="text-white fw-bold">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Kelas Terakhir</span>
            <span class="text-white fw-bold">{{ $kelasTerakhir }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Tahun Lulus</span>
            <span class="text-white fw-bold">{{ $tahunLulusNama }} {{ $tahunLulusSemester }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">No HP</span>
            <span class="text-white fw-bold">{{ $siswa->no_hp ?? '-' }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">No HP Ortu</span>
            <span class="text-white fw-bold">{{ $siswa->no_hp_ortu ?? '-' }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Alamat</span>
            <span class="text-white fw-bold text-end" style="max-width: 60%;">{{ $siswa->alamat ?? '-' }}</span>
          </li>
          <li class="d-flex justify-content-between">
            <span class="text-muted small">Status</span>
            <span class="das-chip --warning">Alumni</span>
          </li>
        </ul>
        
        <div class="mt-4 pt-3 border-top border-secondary border-opacity-10 text-center">
            <img src="{{ $qrImage }}" alt="QR Code" width="120" class="img-fluid rounded bg-white p-2 mb-2">
            <div class="text-muted font-monospace small">{{ $siswa->qr_code }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-8 col-lg-7">
    <div class="das-panel mb-4">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot --info"></span>
          Analisis Kehadiran (Historis)
        </div>
      </div>
      <div class="das-panel__body">
        <div class="row align-items-center">
          <div class="col-md-6 border-end border-secondary border-opacity-10">
            <div id="chartSiswaDonut"></div>
          </div>
          <div class="col-md-6 ps-md-4">
             <div class="mb-4">
               <h3 class="mb-0 text-white fw-800">{{ round(($stats['hadir']+$stats['terlambat'])/$stats['total']*100, 1) }}%</h3>
               <small class="text-muted text-uppercase letter-spacing-1">Tingkat Kehadiran Total</small>
             </div>
             <div class="d-flex flex-column gap-2">
               <div class="d-flex justify-content-between small">
                 <span class="text-muted">Total Hari Efektif</span>
                 <span class="text-white fw-bold">{{ $stats['total'] }} Hari</span>
               </div>
               <div class="d-flex justify-content-between small">
                 <span class="text-muted">Total Terlambat</span>
                 <span class="text-warning fw-bold">{{ $stats['terlambat'] }} Kali</span>
               </div>
               <div class="d-flex justify-content-between small">
                 <span class="text-muted">Rata-rata per Bulan</span>
                 <span class="text-info fw-bold">{{ round($stats['total'] / 12, 1) }} Hari</span>
               </div>
             </div>
          </div>
        </div>
      </div>
    </div>

    <div class="das-panel mb-4">
       <div class="nav-align-top">
          <ul class="nav nav-tabs das-panel__head border-0" role="tablist">
            <li class="nav-item"><button type="button" class="nav-link active py-3 bg-transparent border-0 text-white" role="tab" data-bs-toggle="tab" data-bs-target="#tab-history"><i class="ti tabler-history me-1"></i> Riwayat Absen</button></li>
            <li class="nav-item"><button type="button" class="nav-link py-3 bg-transparent border-0 text-white" role="tab" data-bs-toggle="tab" data-bs-target="#tab-izin"><i class="ti tabler-file-description me-1"></i> Izin & Sakit</button></li>
          </ul>
          <div class="tab-content bg-transparent p-0 border-0">
            <div class="tab-pane fade show active" id="tab-history" role="tabpanel">
              <div class="table-responsive">
                <table class="das-table">
                  <thead>
                    <tr>
                      <th>TANGGAL</th>
                      <th>MASUK</th>
                      <th>PULANG</th>
                      <th>STATUS</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($absensi as $abs)
                       <tr>
                          <td>{{ \Carbon\Carbon::parse($abs->tanggal)->translatedFormat('d M Y') }}</td>
                          <td class="font-monospace fw-bold text-info">{{ $abs->jam_masuk ?? '--:--' }}</td>
                          <td class="font-monospace text-muted">{{ $abs->jam_pulang ?? '--:--' }}</td>
                          <td>
                             @php $color = ['hadir'=>'success', 'sakit'=>'info', 'izin'=>'warning', 'alpha'=>'danger', 'terlambat'=>'warning'][$abs->status] ?? 'secondary'; @endphp
                             <span class="das-chip --{{ $color }}">{{ $abs->status }}</span>
                          </td>
                       </tr>
                    @empty
                       <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada data riwayat absensi.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
              @if ($absensi->hasPages())
                <div class="p-3">
                  {{ $absensi->links('vendor.pagination.users') }}
                </div>
              @endif
            </div>
            
            <div class="tab-pane fade" id="tab-izin" role="tabpanel">
               <div class="table-responsive">
                <table class="das-table">
                  <thead>
                    <tr>
                      <th>PERIODE</th>
                      <th>JENIS</th>
                      <th>STATUS</th>
                      <th>KET</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($izinSakit as $is)
                       <tr>
                          <td>
                             <div class="fw-bold">{{ \Carbon\Carbon::parse($is->tanggal_mulai)->translatedFormat('d/m/y') }}</div>
                             <div class="small text-muted">s/d {{ \Carbon\Carbon::parse($is->tanggal_selesai)->translatedFormat('d/m/y') }}</div>
                          </td>
                          <td><span class="das-chip --{{ $is->jenis == 'sakit' ? 'info' : 'warning' }}">{{ $is->jenis }}</span></td>
                          <td><span class="badge bg-label-{{ $is->status == 'disetujui' ? 'success' : ($is->status == 'pending' ? 'warning' : 'danger') }}">{{ $is->status }}</span></td>
                          <td class="small">{{ Str::limit($is->keterangan, 30) }}</td>
                       </tr>
                    @empty
                       <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada data izin/sakit.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
       </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Donut Chart
    const donutOptions = {
      series: [
        parseInt('{{ $stats["hadir"] }}') || 0,
        parseInt('{{ $stats["sakit"] }}') || 0,
        parseInt('{{ $stats["izin"] }}') || 0,
        parseInt('{{ $stats["alpha"] }}') || 0,
        parseInt('{{ $stats["terlambat"] }}') || 0
      ],
      labels: ['Hadir', 'Sakit', 'Izin', 'Alpha', 'Terlambat'],
      chart: { type: 'donut', height: 280 },
      colors: ['#28c76f', '#00cfe8', '#ff9f43', '#ea5455', '#a8aaae'],
      legend: { position: 'bottom', labels: { colors: '#94a3b8' } },
      dataLabels: { enabled: false },
      stroke: { width: 0 },
      plotOptions: { pie: { donut: { size: '75%', labels: { show: true, name: { color: '#94a3b8' }, value: { color: '#fff', fontSize: '20px', fontWeight: 800 } } } } }
    };
    new ApexCharts(document.querySelector("#chartSiswaDonut"), donutOptions).render();
  });
</script>
@endsection
