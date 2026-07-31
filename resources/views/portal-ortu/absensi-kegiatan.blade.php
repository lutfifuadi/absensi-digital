@extends('layouts/layoutMaster')

@section('title', 'Absensi Kegiatan — ' . $anak->nama_lengkap)

@section('content')

{{-- ── HERO HEADER ────────────────────────────────── --}}
<div class="card bg-primary text-white mb-4 overflow-hidden position-relative" style="border-radius: 12px; background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%) !important;">
  <div class="card-body p-4 position-relative" style="z-index: 2;">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="avatar avatar-lg bg-white bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
          <i class="ti tabler-calendar-event text-white fs-2"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1 mb-1" style="font-size:0.75rem;">
              <li class="breadcrumb-item"><a href="{{ route('ortu.dashboard') }}" class="text-white opacity-75">Dashboard</a></li>
              <li class="breadcrumb-item active text-white fw-semibold">Absensi Kegiatan</li>
            </ol>
          </nav>
          <h4 class="text-white mb-0 fw-bold">Absensi Kegiatan Sekolah</h4>
          <p class="text-white opacity-75 small mb-0 mt-1">
            <i class="ti tabler-user me-1"></i>{{ $anak->nama_lengkap }} ({{ $anak->kelas->nama ?? 'Tanpa Kelas' }})
          </p>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('ortu.dashboard') }}" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
          <i class="ti tabler-arrow-left"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</div>

{{-- ── STATS CARDS ────────────────────────────────── --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="avatar avatar-md bg-label-primary rounded p-2">
          <i class="ti tabler-calendar-event fs-3"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
          <small class="text-muted">Total Kegiatan</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="avatar avatar-md bg-label-success rounded p-2">
          <i class="ti tabler-circle-check fs-3"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-bold text-success">{{ $stats['hadir'] }}</h5>
          <small class="text-muted">Hadir</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="avatar avatar-md bg-label-info rounded p-2">
          <i class="ti tabler-file-text fs-3"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-bold text-info">{{ $stats['izin'] + $stats['sakit'] }}</h5>
          <small class="text-muted">Izin / Sakit</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="avatar avatar-md bg-label-warning rounded p-2">
          <i class="ti tabler-clock fs-3"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-bold text-warning">{{ $stats['belum'] }}</h5>
          <small class="text-muted">Belum Presensi</small>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── DAFTAR KEGIATAN ────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
      <i class="ti tabler-list-check text-primary fs-4"></i> Jadwal & Riwayat Kegiatan Anak
    </h5>
    <span class="badge bg-label-primary rounded-pill">{{ $kegiatans->count() }} Kegiatan Diikuti</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Nama Kegiatan</th>
          <th>Jenis</th>
          <th>Waktu & Lokasi</th>
          <th>Sifat</th>
          <th>Status Presensi</th>
          <th>Jam Presensi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($kegiatans as $index => $kegiatan)
          @php
            $absen = $kegiatan->absensiKegiatan->first();
          @endphp
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>
              <div class="fw-bold text-dark">{{ $kegiatan->nama_kegiatan }}</div>
              @if($kegiatan->keterangan)
                <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ $kegiatan->keterangan }}</small>
              @endif
            </td>
            <td>
              <span class="badge bg-label-secondary text-uppercase" style="font-size:0.7rem;">
                {{ $kegiatan->jenis }}
              </span>
            </td>
            <td>
              <div>
                <i class="ti tabler-calendar text-muted me-1"></i>
                @if($kegiatan->tanggal_pelaksanaan)
                  {{ \Carbon\Carbon::parse($kegiatan->tanggal_pelaksanaan)->locale('id')->translatedFormat('d M Y') }}
                  @if($kegiatan->tanggal_selesai && $kegiatan->tanggal_selesai != $kegiatan->tanggal_pelaksanaan)
                    - {{ \Carbon\Carbon::parse($kegiatan->tanggal_selesai)->locale('id')->translatedFormat('d M Y') }}
                  @endif
                @else
                  <span class="text-muted">Kegiatan Fleksibel</span>
                @endif
              </div>
              <small class="text-muted d-block">
                @if($kegiatan->waktu_mulai)
                  <i class="ti tabler-clock me-1"></i>{{ \Carbon\Carbon::parse($kegiatan->waktu_mulai)->format('H:i') }} - {{ $kegiatan->waktu_selesai ? \Carbon\Carbon::parse($kegiatan->waktu_selesai)->format('H:i') : 'Selesai' }}
                @else
                  <i class="ti tabler-clock-off me-1"></i>Seharian penuh
                @endif
                @if($kegiatan->lokasi)
                  <span class="ms-2"><i class="ti tabler-map-pin me-1"></i>{{ $kegiatan->lokasi }}</span>
                @endif
              </small>
            </td>
            <td>
              @if($kegiatan->is_wajib)
                <span class="badge bg-label-danger"><i class="ti tabler-alert-circle me-1"></i>Wajib</span>
              @else
                <span class="badge bg-label-info">Opsional</span>
              @endif
            </td>
            <td>
              @if(!$absen)
                <span class="badge bg-label-warning"><i class="ti tabler-clock me-1"></i>Belum Absen</span>
              @else
                @switch($absen->status)
                  @case('hadir')
                    <span class="badge bg-label-success"><i class="ti tabler-circle-check me-1"></i>Hadir</span>
                    @break
                  @case('izin')
                    <span class="badge bg-label-info"><i class="ti tabler-file-text me-1"></i>Izin</span>
                    @break
                  @case('sakit')
                    <span class="badge bg-label-primary"><i class="ti tabler-stethoscope me-1"></i>Sakit</span>
                    @break
                  @case('alpha')
                    <span class="badge bg-label-danger"><i class="ti tabler-circle-x me-1"></i>Alpha</span>
                    @break
                  @default
                    <span class="badge bg-label-secondary">{{ ucfirst($absen->status) }}</span>
                @endswitch
              @endif
            </td>
            <td>
              @if($absen && $absen->jam_absen)
                <span class="fw-semibold">{{ \Carbon\Carbon::parse($absen->jam_absen)->format('H:i:s') }}</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-5">
              <div class="text-muted">
                <i class="ti tabler-calendar-off fs-1 d-block mb-2 text-secondary"></i>
                <p class="mb-0 fw-semibold">Tidak ada kegiatan khusus yang diikuti anak Anda saat ini.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
