@extends('layouts/layoutMaster')

@section('title', 'Portal Orang Tua')

@section('page-style')
<style>
    @media (max-width: 576px) {
        .card-header .d-flex { flex-direction: column; align-items: flex-start !important; gap: 10px; }
        .card-header .text-md-end { text-align: left !important; }
        .badge { font-size: 0.75rem; }
    }
</style>
@endsection

@section('content')
  <div class="row mb-4">
    <div class="col-12">
      <div class="card bg-success text-white">
        <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
          <div>
            <h5 class="card-title mb-1 text-white">Selamat Datang, {{ $user->name }}!</h5>
            <p class="card-text mb-0 opacity-75">Portal Orang Tua — pantau kehadiran dan status izin anak secara real-time.</p>
          </div>
          <div class="text-md-end">
            <span class="badge bg-white text-success mb-2">Portal Wali Murid</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if($anakList->isEmpty())
    <div class="alert alert-warning">
      Data anak belum ditautkan ke akun Anda. Silakan hubungi admin sekolah.
    </div>
  @else
    <div class="row gy-4">
      @foreach($anakList as $dataAnak)
        @php
            $anak = $dataAnak['siswa'];
            $absensiHariIni = $dataAnak['absensi_hari_ini'];
            $stats = $dataAnak['stats'];
            $earlyWarning = $dataAnak['early_warning'];
        @endphp

        <div class="col-md-6 col-xl-4">
          <div class="card h-100 {{ $earlyWarning ? 'border-danger border-2' : '' }}">
            <div class="card-header border-bottom">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h5 class="mb-0 fw-bold">{{ $anak->nama_lengkap }}</h5>
                  <small class="text-muted">{{ $anak->nisn }} — {{ $anak->kelas->nama ?? 'Tidak Ada Kelas' }}</small>
                </div>
                <div>
                  @if($absensiHariIni)
                    <span class="badge w-100 bg-success"><i class="ti tabler-check"></i> Hadir</span>
                    <small class="d-block text-center text-muted mt-1" style="font-size: 0.70rem;">{{ $absensiHariIni->jam_masuk }}</small>
                  @else
                    <span class="badge w-100 bg-secondary"><i class="ti tabler-clock"></i> Belum Ada Data</span>
                  @endif
                </div>
              </div>
            </div>
            
            <div class="card-body pt-4">
              <h6 class="mb-3 text-muted">Statistik Bulan Ini</h6>
              <div class="row text-center mb-3">
                <div class="col-4">
                  <h4 class="text-success mb-0">{{ $stats['hadir'] }}</h4>
                  <small>Hadir</small>
                </div>
                <div class="col-4 border-start border-end">
                  <h4 class="text-warning mb-0">{{ $stats['izin_sakit'] }}</h4>
                  <small>Izin/Sakit</small>
                </div>
                <div class="col-4">
                  <h4 class="{{ $stats['alpha'] > 0 ? 'text-danger' : 'text-success' }} mb-0">{{ $stats['alpha'] }}</h4>
                  <small>Alpha</small>
                </div>
              </div>
              
              @if($earlyWarning)
                <div class="alert alert-danger mb-0 py-2 pb-0 pt-0 text-center">
                  <small><i class="ti tabler-alert-triangle"></i> Peringatan! Alpha bulan ini sudah melewati batas.</small>
                </div>
              @endif
              
              <div class="mt-3">
                  <div class="d-grid gap-2">
                    <a href="{{ route('ortu.anak.profil', $anak->id) }}" class="btn btn-sm btn-outline-primary">
                      <i class="ti tabler-user me-1"></i> Detail Profil
                    </a>
                    <a href="{{ route('ortu.anak.absensi', $anak->id) }}" class="btn btn-sm btn-outline-info">
                      <i class="ti tabler-calendar-stats me-1"></i> Riwayat Absensi
                    </a>
                  </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  <div class="row mt-4">
      <div class="col-12">
          <div class="card bg-label-info border-info border">
              <div class="card-body">
                  <div class="d-flex gap-3 align-items-center">
                      <i class="ti tabler-info-circle text-info fs-1"></i>
                      <div>
                          <h6 class="mb-1 text-info fw-bold">Informasi Notifikasi WhatsApp</h6>
                          <p class="mb-0 small text-info">Sistem segera akan diintegrasikan dengan Notifikasi WhatsApp. Anda akan menerima pesan otomatis setiap anak Anda melakukan *scan absensi* (masuk gedung sekolah).</p>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
@endsection
