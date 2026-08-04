@extends('layouts/layoutMaster')

@section('title', 'Laporan Rekap Presensi Harian')

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
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

    .st-hadir { background: rgba(40, 199, 111, 0.15) !important; color: #28c76f !important; border: 1px solid rgba(40, 199, 111, 0.3); }
    .st-sakit { background: rgba(0, 207, 232, 0.15) !important; color: #00cfe8 !important; border: 1px solid rgba(0, 207, 232, 0.3); }
    .st-izin { background: rgba(255, 159, 67, 0.15) !important; color: #ff9f43 !important; border: 1px solid rgba(255, 159, 67, 0.3); }
    .st-alpha { background: rgba(234, 84, 85, 0.15) !important; color: #ea5455 !important; border: 1px solid rgba(234, 84, 85, 0.3); }
    .st-terlambat { background: rgba(168, 85, 247, 0.15) !important; color: #c084fc !important; border: 1px solid rgba(168, 85, 247, 0.3); }
    .st-belum { background: rgba(148, 163, 184, 0.15) !important; color: #94a3b8 !important; border: 1px solid rgba(148, 163, 184, 0.3); }
  </style>
@endsection

@section('content')

  {{-- HERO HEADER --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-calendar-time text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Laporan Harian Kehadiran
          </div>
          <h4 class="das-hero__title text-gradient-gold">Rekap Presensi Harian</h4>
          <p class="das-hero__subtitle">Pemantauan dan rincian jam presensi harian siswa secara real-time.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER PANEL --}}
  <div class="das-panel mb-4">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot --primary"></span>
        Filter Laporan Harian
      </div>
    </div>
    <div class="das-panel__body">
      <form method="GET" action="{{ url()->current() }}" class="row gy-3 gx-2 align-items-end">
        <div class="col-12 col-sm-6 col-md-3">
          <label class="form-label text-white-50 small fw-bold mb-1">
            TANGGAL
          </label>
          <input type="date" class="form-control form-control-sm" name="tanggal" value="{{ $tanggal }}" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);">
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <label class="form-label text-white-50 small fw-bold mb-1">
            KELAS
            @if(!empty($isWaliKelasLocked))
              <span class="badge ms-1" style="background: #ff9f43 !important; color: #0f172a !important; font-size: 0.7rem; font-weight: 800 !important; padding: 2px 7px; border-radius: 4px;">
                <i class="ti tabler-lock me-1" style="color: #0f172a !important;"></i>Kelas Saya
              </span>
            @endif
          </label>
          <select class="form-select form-select-sm" name="kelas_id" style="background: rgba(15, 23, 42, 0.6) !important; color: #ffffff !important; border: 1px solid {{ !empty($isWaliKelasLocked) ? 'rgba(255,159,67,0.5)' : 'rgba(255,255,255,0.1)' }} !important; opacity: 1 !important;" @if(!empty($isWaliKelasLocked)) disabled @endif>
            @if(empty($isWaliKelasLocked))
              <option value="">Semua Kelas</option>
            @endif
            @foreach ($kelasOptions as $k)
              <option value="{{ $k->id }}" @selected($kelasId == $k->id)>{{ $k->nama }}</option>
            @endforeach
          </select>
          @if(!empty($isWaliKelasLocked))
            <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
          @endif
        </div>

        <div class="col-12 col-sm-6 col-md-4">
          <label class="form-label text-white-50 small fw-bold mb-1">CARI SISWA</label>
          <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Nama / NIS Siswa..." style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);">
        </div>

        <div class="col-12 col-sm-6 col-md-2">
          <button type="submit" class="das-btn das-btn--info w-100">
            <i class="ti tabler-search me-1"></i> Terapkan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- SUMMARY STATS --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
      <div class="p-3 rounded text-center glass-card">
        <small class="text-white-50 d-block mb-1">TOTAL SISWA</small>
        <span class="h4 text-white fw-bold mb-0">{{ $summary['total'] }}</span>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="p-3 rounded text-center glass-card border-success">
        <small class="text-success d-block mb-1">HADIR</small>
        <span class="h4 text-success fw-bold mb-0">{{ $summary['hadir'] }}</span>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="p-3 rounded text-center glass-card border-warning">
        <small class="text-warning d-block mb-1">IZIN</small>
        <span class="h4 text-warning fw-bold mb-0">{{ $summary['izin'] }}</span>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="p-3 rounded text-center glass-card border-info">
        <small class="text-info d-block mb-1">SAKIT</small>
        <span class="h4 text-info fw-bold mb-0">{{ $summary['sakit'] }}</span>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="p-3 rounded text-center glass-card border-danger">
        <small class="text-danger d-block mb-1">ALPHA</small>
        <span class="h4 text-danger fw-bold mb-0">{{ $summary['alpha'] }}</span>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="p-3 rounded text-center glass-card">
        <small class="text-secondary d-block mb-1">TERLAMBAT</small>
        <span class="h4 text-purple fw-bold mb-0" style="color: #c084fc;">{{ $summary['terlambat'] }}</span>
      </div>
    </div>
  </div>

  {{-- DATA TABLE --}}
  <div class="card glass-card">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center" style="background:transparent; border-color: rgba(255,255,255,0.08) !important;">
      <h6 class="card-title mb-0 text-white">
        <i class="ti tabler-list text-info me-1"></i> Data Presensi Tanggal {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y') }}
        @if($kelas) <span class="badge bg-label-info ms-2">Kelas {{ $kelas->nama }}</span> @endif
      </h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px; color:inherit;">
          <thead style="background:rgba(255,255,255,0.02);">
            <tr>
              <th class="text-center" style="width:40px;">#</th>
              <th>NIS</th>
              <th>Nama Siswa</th>
              <th class="text-center">Kelas</th>
              <th class="text-center">Status</th>
              <th class="text-center">Jam Masuk</th>
              <th class="text-center">Jam Pulang</th>
              <th>Keterangan / Metode</th>
            </tr>
          </thead>
          <tbody>
            @forelse($siswaList as $siswa)
              @php
                $absen = $siswa->absensi->first();
                $st = $absen ? strtolower($absen->status) : 'belum';
              @endphp
              <tr>
                <td class="text-center text-white-50">{{ $loop->iteration }}</td>
                <td><code class="text-info">{{ $siswa->nis }}</code></td>
                <td class="fw-semibold text-white">{{ $siswa->nama_lengkap }}</td>
                <td class="text-center">{{ $siswa->kelas?->nama ?? '-' }}</td>
                <td class="text-center">
                  <span class="badge st-{{ $st }} px-2 py-1">
                    {{ $absen ? ucfirst($absen->status) : 'Belum Absen' }}
                  </span>
                </td>
                <td class="text-center fw-bold text-success">
                  {{ $absen && $absen->jam_masuk ? \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i:s') : '-' }}
                </td>
                <td class="text-center fw-bold text-info">
                  {{ $absen && $absen->jam_pulang ? \Carbon\Carbon::parse($absen->jam_pulang)->format('H:i:s') : '-' }}
                </td>
                <td class="text-white-50">
                  @if($absen)
                    {{ ucfirst($absen->metode ?? 'manual') }} @if($absen->keterangan) ({{ $absen->keterangan }}) @endif
                  @else
                    -
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-4 text-white-50">
                  Tidak ada data siswa ditemukan.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection
