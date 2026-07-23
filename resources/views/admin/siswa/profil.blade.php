@extends('layouts/layoutMaster')

@section('title', 'Profil Siswa — ' . $siswa->nama_lengkap)

@section('content')

{{-- ═══════════════════════════════════════════════════════
     SECTION 1: HERO HEADER — Identitas Siswa + Live Clock
═══════════════════════════════════════════════════════ --}}
<div class="das-hero mb-6">
  <div class="das-hero__bg" aria-hidden="true"></div>
  <div class="das-hero__scanline" aria-hidden="true"></div>
  <div class="das-hero__grid-lines" aria-hidden="true"></div>

  <div class="das-hero__inner flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
    {{-- Identitas Siswa --}}
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        @php
          $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($siswa->nama_lengkap) . '&size=120&background=7367f0&color=fff';
          if ($siswa->foto) {
              if (strlen($siswa->foto) > 30) {
                  $avatarUrl = 'https://drive.google.com/thumbnail?id=' . $siswa->foto . '&sz=w200&_t=' . time();
              } else {
                  $avatarUrl = asset('storage/' . $siswa->foto);
              }
          }
        @endphp
        <img class="das-hero__logo" src="{{ $avatarUrl }}" alt="Foto {{ $siswa->nama_lengkap }}" style="object-fit: cover;">
        <div class="das-hero__logo-glow"></div>
      </div>

      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="das-hero__pulse-dot" aria-hidden="true"></span>
          NIS / NISN: {{ $siswa->nis ?: $siswa->nisn }}
        </div>
        <h3 class="das-hero__school text-gradient-gold mb-1" style="font-size: 1.35rem; line-height: 1.3;">{{ $siswa->nama_lengkap }}</h3>
        <p class="das-hero__welcome mb-0">
          <i class="ti tabler-school me-1"></i>{{ $siswa->kelas->nama ?? 'Tanpa Kelas' }} 
          <span class="opacity-50 mx-1">•</span> 
          <i class="ti tabler-calendar-event me-1"></i>TA {{ $siswa->tahunAkademik->nama ?? '-' }}
        </p>
      </div>
    </div>

    {{-- Quick Hero Actions --}}
    <div class="das-hero__actions d-flex gap-2 flex-wrap ms-md-auto">
      <a href="{{ route('admin.siswa.generate-qr', $siswa->id) }}" class="btn btn-info btn-sm d-flex align-items-center gap-1 shadow-sm">
        <i class="ti tabler-qrcode"></i> Cetak Kartu
      </a>
      <a href="{{ route('admin.siswa.edit', $siswa->id) }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
        <i class="ti tabler-edit"></i> Edit Profil
      </a>
    </div>
  </div>
</div>{{-- /das-hero --}}


{{-- ═══════════════════════════════════════════════════════
     SECTION 2: STATS ROW — 4 Card Statistik Dynamic
═══════════════════════════════════════════════════════ --}}
<div class="row g-6 mb-6">
  {{-- Card 1: Hadir --}}
  <div class="col-lg-3 col-sm-6">
    <div class="card card-grad-success h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-success">
              <i class="ti tabler-circle-check fs-4"></i>
            </span>
          </div>
          <h4 class="mb-0 fw-semibold">{{ $stats['hadir'] }}</h4>
        </div>
        <p class="mb-1 text-body-secondary text-nowrap">Hadir Tepat Waktu</p>
        <p class="mb-0">
          <span class="text-success fw-medium me-2">{{ round(($stats['hadir'] / $stats['total']) * 100, 1) }}%</span>
          <small class="text-body-secondary">dari total hari</small>
        </p>
      </div>
    </div>
  </div>

  {{-- Card 2: Terlambat --}}
  <div class="col-lg-3 col-sm-6">
    <div class="card card-grad-warning h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="ti tabler-clock-exclamation fs-4"></i>
            </span>
          </div>
          <h4 class="mb-0 fw-semibold">{{ $stats['terlambat'] }}</h4>
        </div>
        <p class="mb-1 text-body-secondary text-nowrap">Siswa Terlambat</p>
        <p class="mb-0">
          <span class="text-warning fw-medium me-2">Evaluasi Waktu</span>
          <small class="text-body-secondary">catatan terlambat</small>
        </p>
      </div>
    </div>
  </div>

  {{-- Card 3: Izin & Sakit --}}
  <div class="col-lg-3 col-sm-6">
    <div class="card card-grad-info h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-info">
              <i class="ti tabler-clipboard-check fs-4"></i>
            </span>
          </div>
          <h4 class="mb-0 fw-semibold">{{ $stats['izin'] + $stats['sakit'] }}</h4>
        </div>
        <p class="mb-1 text-body-secondary text-nowrap">Izin & Sakit</p>
        <p class="mb-0">
          <span class="text-info fw-medium me-2">Keterangan Resmi</span>
          <small class="text-body-secondary">surat izin/sakit</small>
        </p>
      </div>
    </div>
  </div>

  {{-- Card 4: Alpha --}}
  <div class="col-lg-3 col-sm-6">
    <div class="card card-grad-danger h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="ti tabler-user-x fs-4"></i>
            </span>
          </div>
          <h4 class="mb-0 fw-semibold">{{ $stats['alpha'] }}</h4>
        </div>
        <p class="mb-1 text-body-secondary text-nowrap">Alpha / Tanpa Ket.</p>
        <p class="mb-0">
          <span class="text-danger fw-medium me-2">Tindakan Disiplin</span>
          <small class="text-body-secondary">butuh konfirmasi</small>
        </p>
      </div>
    </div>
  </div>
</div>{{-- /row g-6 mb-6 (Stats Row) --}}


{{-- ═══════════════════════════════════════════════════════
     SECTION 3: CONTENT GRID — Personal Info vs Analytics & Tabs
═══════════════════════════════════════════════════════ --}}
<div class="row g-6">
  {{-- Kiri: Informasi Personal & Dual QR Code --}}
  <div class="col-xl-4 col-lg-5">
    <div class="card card-grad-primary mb-6">
      <div class="card-header pb-2 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="ti tabler-user fs-4"></i>
            </span>
          </div>
          <div>
            <h5 class="card-title mb-0">Informasi Personal</h5>
            <small class="text-body-secondary">Detail data diri siswa</small>
          </div>
        </div>
        <span class="badge bg-label-success">Aktif</span>
      </div>
      <div class="card-body pt-2">
        <ul class="list-group list-group-flush border-top border-bottom my-3">
          <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 border-bottom">
            <span class="text-body-secondary small"><i class="ti tabler-id me-1"></i> NISN</span>
            <span class="fw-bold font-monospace">{{ $siswa->nisn }}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 border-bottom">
            <span class="text-body-secondary small"><i class="ti tabler-badge me-1"></i> NIS</span>
            <span class="fw-bold font-monospace">{{ $siswa->nis ?: '-' }}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 border-bottom">
            <span class="text-body-secondary small"><i class="ti tabler-gender-male-female me-1"></i> Jenis Kelamin</span>
            <span class="fw-semibold">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 border-bottom">
            <span class="text-body-secondary small"><i class="ti tabler-map-pin me-1"></i> Tempat, Tgl Lahir</span>
            <span class="fw-semibold text-end">{{ $siswa->tempat_lahir ?? '-' }}, {{ $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d M Y') : '-' }}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 border-bottom">
            <span class="text-body-secondary small"><i class="ti tabler-chalkboard-teacher me-1"></i> Wali Kelas</span>
            <span class="fw-semibold text-primary">{{ $siswa->kelas->waliKelas->nama_lengkap ?? '-' }}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
            <span class="text-body-secondary small"><i class="ti tabler-phone me-1"></i> Kontak Ortu</span>
            <span class="fw-semibold font-monospace">{{ $siswa->no_hp_ortu }}</span>
          </li>
        </ul>
        
        {{-- Dual QR & Barcode Section --}}
        <div class="mt-4 pt-3 border-top border-secondary border-opacity-10">
          <h6 class="text-body-secondary small mb-3 text-center text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Dual QR & Barcode Presensi</h6>
          <div class="d-flex flex-column gap-3 w-100">
            <!-- QR Code Block -->
            <div class="w-100 p-3 bg-white rounded shadow-sm text-center border">
              <div class="d-flex justify-content-center mb-2">
                <img src="{{ $qrImage }}" alt="QR Code" class="img-fluid" style="max-width: 150px; width: 100%; height: auto;">
              </div>
              <div class="text-muted font-monospace" style="font-size: 0.75rem;">QR Code UUID</div>
            </div>

            <!-- Barcode Block -->
            <div class="w-100 p-3 bg-white rounded shadow-sm text-center border">
              <div class="barcode-svg-container w-100 mb-2" style="height: 60px;">
                {!! App\Support\BarcodeGenerator::renderSvg($siswa->nis ?: $siswa->nisn ?: 'SISWA'.$siswa->id) !!}
              </div>
              <div class="text-muted font-monospace" style="font-size: 0.75rem;">Barcode: {{ App\Support\BarcodeGenerator::getFormattedData($siswa->nis ?: $siswa->nisn ?: 'SISWA'.$siswa->id) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Card Aksi Akademik --}}
    @php $userRole = auth()->user()?->role; @endphp
    @if(in_array($userRole, ['super_admin', 'admin_sekolah', 'operator']))
    <div class="card card-grad-gold mb-6">
      <div class="card-header pb-2">
        <div class="d-flex align-items-center gap-2">
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="ti tabler-subtask fs-4"></i>
            </span>
          </div>
          <div>
            <h5 class="card-title mb-0">Aksi Akademik</h5>
            <small class="text-body-secondary">Mutasi & Kenaikan Kelas</small>
          </div>
        </div>
      </div>
      <div class="card-body pt-3 d-grid gap-2">
        <button class="btn btn-warning text-white w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPindahKelas">
          <i class="ti tabler-arrows-exchange"></i> Pindah Kelas
        </button>
        <button class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNaikKelas">
          <i class="ti tabler-trending-up"></i> Naik Kelas
        </button>
      </div>
    </div>
    @endif
  </div>

  {{-- Kanan: Analisis Kehadiran & Riwayat Tab --}}
  <div class="col-xl-8 col-lg-7">
    {{-- Card Analisis Kehadiran --}}
    <div class="card card-grad-primary mb-6">
      <div class="card-header pb-2">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="ti tabler-chart-pie fs-4"></i>
              </span>
            </div>
            <div>
              <h5 class="card-title mb-0">Analisis Kehadiran</h5>
              <small class="text-body-secondary">Persentase & Akumulasi Presensi</small>
            </div>
          </div>
          <span class="badge bg-label-info p-2">Akumulasi TA</span>
        </div>
      </div>
      <div class="card-body pt-3">
        <div class="row align-items-center">
          <div class="col-md-6 border-end border-secondary border-opacity-10 mb-4 mb-md-0">
            <div id="chartSiswaDonut"></div>
          </div>
          <div class="col-md-6 ps-md-4">
            <div class="mb-4">
              <h2 class="mb-0 text-primary fw-bold">{{ round(($stats['hadir']+$stats['terlambat'])/$stats['total']*100, 1) }}%</h2>
              <small class="text-body-secondary text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Tingkat Kehadiran Total</small>
            </div>
            <div class="d-flex flex-column gap-2">
              <div class="d-flex justify-content-between align-items-center p-2 rounded bg-label-secondary bg-opacity-10">
                <span class="text-body-secondary small"><i class="ti tabler-calendar-event me-1"></i> Total Hari Efektif</span>
                <span class="fw-bold">{{ $stats['total'] }} Hari</span>
              </div>
              <div class="d-flex justify-content-between align-items-center p-2 rounded bg-label-warning bg-opacity-10">
                <span class="text-body-secondary small"><i class="ti tabler-clock-exclamation me-1"></i> Total Terlambat</span>
                <span class="text-warning fw-bold">{{ $stats['terlambat'] }} Kali</span>
              </div>
              <div class="d-flex justify-content-between align-items-center p-2 rounded bg-label-info bg-opacity-10">
                <span class="text-body-secondary small"><i class="ti tabler-chart-bar me-1"></i> Rata-rata per Bulan</span>
                <span class="text-info fw-bold">{{ round($stats['total'] / 12, 1) }} Hari</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Card Tabs: Riwayat Absen & Izin/Sakit --}}
    <div class="card shadow-sm mb-6">
      <div class="card-header p-0">
        <ul class="nav nav-tabs nav-fill card-header-tabs m-0" role="tablist">
          <li class="nav-item">
            <button type="button" class="nav-link active py-3 fw-semibold" role="tab" data-bs-toggle="tab" data-bs-target="#tab-history">
              <i class="ti tabler-history me-1 fs-5"></i> Riwayat Absen
            </button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link py-3 fw-semibold" role="tab" data-bs-toggle="tab" data-bs-target="#tab-izin">
              <i class="ti tabler-file-description me-1 fs-5"></i> Izin & Sakit
            </button>
          </li>
        </ul>
      </div>
      <div class="card-body p-0">
        <div class="tab-content p-0 border-0">
          {{-- Tab 1: Riwayat Absen --}}
          <div class="tab-pane fade show active" id="tab-history" role="tabpanel">
            <div class="table-responsive text-nowrap">
              <table class="table table-hover mb-0">
                <thead class="table-light">
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
                      <td class="fw-medium">{{ \Carbon\Carbon::parse($abs->tanggal)->translatedFormat('d M Y') }}</td>
                      <td class="font-monospace fw-bold text-info">{{ $abs->jam_masuk ?? '--:--' }}</td>
                      <td class="font-monospace text-body-secondary">{{ $abs->jam_pulang ?? '--:--' }}</td>
                      <td>
                        @php 
                          $badgeClass = [
                            'hadir' => 'bg-label-success',
                            'sakit' => 'bg-label-info',
                            'izin' => 'bg-label-warning',
                            'alpha' => 'bg-label-danger',
                            'terlambat' => 'bg-label-warning'
                          ][$abs->status] ?? 'bg-label-secondary'; 
                        @endphp
                        <span class="badge {{ $badgeClass }} text-uppercase">{{ $abs->status }}</span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center py-5 text-body-secondary">Belum ada data presensi.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            <div class="p-3 border-top">
              {{ $absensi->links('vendor.pagination.users') }}
            </div>
          </div>
          
          {{-- Tab 2: Izin & Sakit --}}
          <div class="tab-pane fade" id="tab-izin" role="tabpanel">
            <div class="table-responsive text-nowrap">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>PERIODE</th>
                    <th>JENIS</th>
                    <th>STATUS</th>
                    <th>KETERANGAN</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($izinSakit as $is)
                    <tr>
                      <td>
                        <div class="fw-bold">{{ \Carbon\Carbon::parse($is->tanggal_mulai)->translatedFormat('d/m/Y') }}</div>
                        <small class="text-body-secondary">s/d {{ \Carbon\Carbon::parse($is->tanggal_selesai)->translatedFormat('d/m/Y') }}</small>
                      </td>
                      <td>
                        <span class="badge bg-label-{{ $is->jenis == 'sakit' ? 'info' : 'warning' }} text-uppercase">{{ $is->jenis }}</span>
                      </td>
                      <td>
                        <span class="badge bg-label-{{ $is->status == 'disetujui' ? 'success' : ($is->status == 'pending' ? 'warning' : 'danger') }} text-uppercase">{{ $is->status }}</span>
                      </td>
                      <td class="small">{{ \Illuminate\Support\Str::limit($is->keterangan, 40) }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="text-center py-5 text-body-secondary">Belum ada data izin/sakit.</td>
                    </tr>
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

{{-- MODALS AKSI --}}
@include('admin.siswa.modals.pindah-naik')

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // 1. Donut Chart ApexCharts
    const donutOptions = {
      series: [{{ $stats['hadir'] }}, {{ $stats['sakit'] }}, {{ $stats['izin'] }}, {{ $stats['alpha'] }}, {{ $stats['terlambat'] }}],
      labels: ['Hadir', 'Sakit', 'Izin', 'Alpha', 'Terlambat'],
      chart: { type: 'donut', height: 280 },
      colors: ['#28c76f', '#00cfe8', '#ff9f43', '#ea5455', '#a8aaae'],
      legend: { position: 'bottom', labels: { colors: '#94a3b8' } },
      dataLabels: { enabled: false },
      stroke: { width: 0 },
      plotOptions: { 
        pie: { 
          donut: { 
            size: '75%', 
            labels: { 
              show: true, 
              name: { color: '#94a3b8' }, 
              value: { color: '#7367f0', fontSize: '20px', fontWeight: 800 } 
            } 
          } 
        } 
      }
    };
    new ApexCharts(document.querySelector("#chartSiswaDonut"), donutOptions).render();

    // 3. Dropdown Kelas dinamis (Naik Kelas)
    const allKelas = {!! json_encode($kelasOptions->map(function($k) { 
        return ['id' => $k->id, 'nama' => $k->nama, 'jurusan' => $k->jurusan?->nama, 'tahun_akademik_id' => $k->tahun_akademik_id]; 
    })->toArray()) !!};

    const selectTA    = document.getElementById('naik_tahun_akademik_id');
    const selectKelas = document.getElementById('naik_kelas_id');

    if (selectTA) {
      selectTA.addEventListener('change', function () {
        const taId = parseInt(this.value);
        selectKelas.innerHTML = '<option value="">— Pilih Kelas Tujuan —</option>';
        if (taId) {
          const filtered = allKelas.filter(k => k.tahun_akademik_id === taId);
          if (filtered.length > 0) {
            filtered.forEach(k => {
              const opt = document.createElement('option');
              opt.value = k.id;
              opt.textContent = k.nama + (k.jurusan ? ' (' + k.jurusan + ')' : '');
              selectKelas.appendChild(opt);
            });
            selectKelas.disabled = false;
          } else {
            selectKelas.disabled = true;
          }
        }
      });
    }
  });
</script>
@endsection

@push('styles')
<style>
    .barcode-svg-container svg {
        width: 100% !important;
        height: 100% !important;
    }
</style>
@endpush
