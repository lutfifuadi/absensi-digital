@extends('layouts/layoutMaster')

@section('title', 'Rekap Harian Absensi')

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      transition: transform 0.2s ease;
    }

    .glass-card:hover {
      transform: translateY(-2px);
    }

    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
    }

    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--bs-info) !important;
    }

    .rekap-row-hover {
      transition: background 0.15s ease;
    }

    .rekap-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }
  </style>
@endsection

@section('content')

  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 12px;">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-7">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                  style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
                  <i class="ti tabler-calendar-stats text-info fs-3"></i>
                </div>
                <div>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                      <li class="breadcrumb-item"><span class="text-white opacity-50">Laporan</span></li>
                      <li class="breadcrumb-item active text-white">Rekap Harian</li>
                    </ol>
                  </nav>
                  <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Rekap Harian</h4>
                  <p class="mb-0 text-white opacity-60 small">Audit riwayat absensi harian untuk seluruh entitas.
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
               <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white">
                  <i class="ti tabler-calendar me-1"></i> {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Filter Card --}}
  <div class="card glass-card mb-4">
    <div class="card-body">
      <form method="GET" class="row gy-3 gx-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label text-white-50 small fw-bold">Pilih Tanggal</label>
          <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}">
        </div>
        <div class="col-md-4">
          <label class="form-label text-white-50 small fw-bold">Filter Kelas</label>
          <select name="kelas_id" class="form-select">
            <option value="">Semua Kelas</option>
            @foreach ($kelasOptions as $k)
              <option value="{{ $k->id }}" @selected($kelasId == $k->id)>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-info w-100 fw-bold">
            <i class="ti tabler-search me-1"></i> Tampilkan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Summary Row --}}
  <div class="row gy-3 mb-4">
    @foreach ([['label' => 'Siswa Hadir', 'val' => $summaryHarian['siswa_hadir'], 'color' => 'success', 'icon' => 'tabler-circle-check'], ['label' => 'Siswa Sakit', 'val' => $summaryHarian['siswa_sakit'], 'color' => 'info', 'icon' => 'tabler-stethoscope'], ['label' => 'Siswa Izin', 'val' => $summaryHarian['siswa_izin'], 'color' => 'warning', 'icon' => 'tabler-file-description'], ['label' => 'Siswa Alpha', 'val' => $summaryHarian['siswa_alpha'], 'color' => 'danger', 'icon' => 'tabler-x'], ['label' => 'Guru Hadir', 'val' => $summaryHarian['guru_hadir'], 'color' => 'primary', 'icon' => 'tabler-chalkboard-teacher'], ['label' => 'Staff Hadir', 'val' => $summaryHarian['staff_hadir'], 'color' => 'secondary', 'icon' => 'tabler-briefcase']] as $stat)
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card text-center h-100 glass-card">
          <div class="card-body py-3">
            <div class="avatar avatar-sm mx-auto mb-2">
               <span class="avatar-initial rounded bg-label-{{ $stat['color'] }}">
                  <i class="ti {{ $stat['icon'] }}"></i>
               </span>
            </div>
            <h4 class="mb-0 text-white fw-bold">{{ $stat['val'] }}</h4>
            <small class="text-white-50 opacity-75" style="font-size:0.7rem;">{{ $stat['label'] }}</small>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Absensi Siswa --}}
  <div class="card glass-card mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between" style="background:transparent; border-color: rgba(255,255,255,0.08) !important;">
      <h6 class="card-title mb-0 text-white d-flex align-items-center gap-2">
        <i class="ti tabler-users text-info"></i> Absensi Siswa
      </h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="color:inherit;">
          <thead style="background:rgba(255,255,255,0.02); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; opacity:0.7;">
            <tr>
              <th class="ps-4 py-3" width="60">#</th>
              <th class="py-3">Nama Siswa</th>
              <th class="py-3">Kelas</th>
              <th class="py-3">Jam Masuk</th>
              <th class="py-3 text-center">Status</th>
              <th class="py-3 text-end pe-4">Metode</th>
            </tr>
          </thead>
          <tbody>
            @forelse($absensiSiswa as $row)
              <tr class="rekap-row-hover">
                <td class="ps-4 text-white-50 small">{{ $loop->iteration }}</td>
                <td><span class="fw-bold text-white">{{ $row->siswa->nama_lengkap ?? '-' }}</span></td>
                <td><span class="badge bg-label-secondary">{{ $row->kelas->nama ?? '-' }}</span></td>
                <td><code class="text-info">{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</code></td>
                <td class="text-center">
                  @php
                    $scolor = match ($row->status) {
                        'hadir' => 'success',
                        'sakit' => 'info',
                        'izin' => 'warning',
                        'alpha' => 'danger',
                        'terlambat' => 'primary',
                        default => 'secondary',
                    };
                  @endphp
                  <span class="badge bg-label-{{ $scolor }} px-2 py-1 rounded-pill small">{{ ucfirst($row->status) }}</span>
                </td>
                <td class="text-end pe-4"><span class="small text-white-50">{{ ucfirst($row->metode) }}</span></td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-5">
                   <div class="opacity-50">
                      <i class="ti tabler-users-minus fs-1 d-block mb-2"></i>
                      <span class="small">Tidak ada data absensi siswa hari ini.</span>
                   </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="row">
    {{-- Absensi Guru --}}
    <div class="col-md-6 mb-4">
      <div class="card glass-card h-100">
        <div class="card-header border-bottom py-3" style="background:transparent; border-color: rgba(255,255,255,0.08) !important;">
          <h6 class="card-title mb-0 text-white d-flex align-items-center gap-2">
            <i class="ti tabler-chalkboard-teacher text-info"></i> Absensi Guru
          </h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color:inherit;">
              <thead style="background:rgba(255,255,255,0.02); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; opacity:0.7;">
                <tr>
                  <th class="ps-3 py-3" width="40">#</th>
                  <th class="py-3">Nama Guru</th>
                  <th class="py-3 text-center">Jam</th>
                  <th class="py-3 text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($absensiGuru as $row)
                  <tr class="rekap-row-hover">
                    <td class="ps-3 text-white-50 small">{{ $loop->iteration }}</td>
                    <td><div class="fw-semibold text-white">{{ $row->guru->nama_lengkap ?? '-' }}</div></td>
                    <td class="text-center"><code class="text-info">{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</code></td>
                    <td class="text-center">
                      <span class="badge bg-label-{{ match ($row->status) {
                          'hadir' => 'success',
                          'sakit' => 'info',
                          'izin' => 'warning',
                          'alpha' => 'danger',
                          default => 'secondary',
                      } }} px-2 py-1 small">{{ ucfirst($row->status) }}</span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center py-5 text-white-50 opacity-50 small">Tidak ada data.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- Absensi Staff --}}
    <div class="col-md-6 mb-4">
      <div class="card glass-card h-100">
        <div class="card-header border-bottom py-3" style="background:transparent; border-color: rgba(255,255,255,0.08) !important;">
          <h6 class="card-title mb-0 text-white d-flex align-items-center gap-2">
            <i class="ti tabler-briefcase text-info"></i> Absensi Staff TU
          </h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color:inherit;">
              <thead style="background:rgba(255,255,255,0.02); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; opacity:0.7;">
                <tr>
                  <th class="ps-3 py-3" width="40">#</th>
                  <th class="py-3">Nama Staff</th>
                  <th class="py-3 text-center">Jam</th>
                  <th class="py-3 text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($absensiStaff as $row)
                  <tr class="rekap-row-hover">
                    <td class="ps-3 text-white-50 small">{{ $loop->iteration }}</td>
                    <td><div class="fw-semibold text-white">{{ $row->staff->nama_lengkap ?? '-' }}</div></td>
                    <td class="text-center"><code class="text-info">{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</code></td>
                    <td class="text-center">
                      <span class="badge bg-label-{{ match ($row->status) {
                          'hadir' => 'success',
                          'sakit' => 'info',
                          'izin' => 'warning',
                          'alpha' => 'danger',
                          default => 'secondary',
                      } }} px-2 py-1 small">{{ ucfirst($row->status) }}</span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center py-5 text-white-50 opacity-50 small">Tidak ada data.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

