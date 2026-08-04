@extends('layouts/layoutMaster')

@section('title', 'Daftar Siswa Belum Absen')

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

    .st-belum { background: rgba(234, 84, 85, 0.15) !important; color: #ea5455 !important; border: 1px solid rgba(234, 84, 85, 0.3); }
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
            <i class="ti tabler-user-x text-danger"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Monitoring Presensi Harian
          </div>
          <h4 class="das-hero__title text-gradient-gold">Daftar Siswa Belum Absen</h4>
          <p class="das-hero__subtitle">Daftar nama siswa yang belum melakukan presensi kedatangan pada hari ini.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER PANEL --}}
  <div class="das-panel mb-4">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot --primary"></span>
        Filter Pencarian
      </div>
    </div>
    <div class="das-panel__body">
      <form method="GET" action="{{ url()->current() }}" class="row gy-3 gx-2 align-items-end">
        <div class="col-12 col-sm-6 col-md-3">
          <label class="form-label text-white-50 small fw-bold mb-1">TANGGAL</label>
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

  {{-- SUMMARY STAT --}}
  <div class="row mb-4">
    <div class="col-12 col-md-4">
      <div class="p-3 rounded glass-card border-danger d-flex align-items-center justify-content-between">
        <div>
          <small class="text-danger d-block fw-bold">TOTAL BELUM ABSEN</small>
          <span class="h3 text-danger fw-bold mb-0">{{ $siswaList->count() }} Siswa</span>
        </div>
        <div class="avatar avatar-md bg-label-danger rounded-circle p-2">
          <i class="ti tabler-user-x fs-3"></i>
        </div>
      </div>
    </div>
  </div>

  {{-- DATA TABLE --}}
  <div class="card glass-card">
    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center" style="background:transparent; border-color: rgba(255,255,255,0.08) !important;">
      <h6 class="card-title mb-0 text-white">
        <i class="ti tabler-user-x text-danger me-1"></i> Siswa Belum Absen — {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y') }}
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
              <th class="text-center">Jenis Kelamin</th>
              <th class="text-center">Status</th>
              <th class="text-center">Aksi / Kontak Ortu</th>
            </tr>
          </thead>
          <tbody>
            @forelse($siswaList as $siswa)
              @php
                $noHp = $siswa->no_hp_ortu ?: $siswa->no_hp_wali ?: $siswa->no_hp;
              @endphp
              <tr>
                <td class="text-center text-white-50">{{ $loop->iteration }}</td>
                <td><code class="text-info">{{ $siswa->nis }}</code></td>
                <td class="fw-semibold text-white">{{ $siswa->nama_lengkap }}</td>
                <td class="text-center">{{ $siswa->kelas?->nama ?? '-' }}</td>
                <td class="text-center">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                <td class="text-center">
                  <span class="badge st-belum px-2 py-1">
                    <i class="ti tabler-clock me-1"></i>Belum Absen
                  </span>
                </td>
                <td class="text-center">
                  @if($noHp)
                    @php
                      $cleanHp = preg_replace('/[^0-9]/', '', $noHp);
                      if (str_starts_with($cleanHp, '0')) {
                        $cleanHp = '62' . substr($cleanHp, 1);
                      }
                      $msg = rawurlencode("Assalamu'alaikum Wr. Wb. Informasi dari Wali Kelas {$siswa->kelas?->nama}: Siswa atas nama *{$siswa->nama_lengkap}* belum terpantau presensi pada hari ini (" . \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') . "). Mohon dikonfirmasi. Terima kasih.");
                    @endphp
                    <a href="https://wa.me/{{ $cleanHp }}?text={{ $msg }}" target="_blank" class="btn btn-xs btn-outline-success">
                      <i class="ti tabler-brand-whatsapp me-1"></i> WA Ortu
                    </a>
                  @else
                    <span class="text-white-50 extra-small">No HP Ortu -</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-white-50">
                  <i class="ti tabler-circle-check fs-2 text-success mb-2 d-block"></i>
                  Semua siswa pada kelas & tanggal ini sudah melakukan presensi.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection
