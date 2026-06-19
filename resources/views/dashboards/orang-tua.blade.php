@extends('layouts/layoutMaster')

@section('title', 'Portal Orang Tua')

@section('page-style')
<style>
    /* Premium Cards & Gradients */
    .premium-card {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04), 0 2px 6px -1px rgba(0, 0, 0, 0.02) !important;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px -4px rgba(115, 103, 240, 0.12), 0 4px 12px -2px rgba(115, 103, 240, 0.04) !important;
    }
    
    /* Dark Mode Adjustments */
    [data-bs-theme="dark"] .premium-card {
        background: rgba(30, 41, 59, 0.45) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        box-shadow: 0 4px 24px 0 rgba(0, 0, 0, 0.3) !important;
    }
    [data-bs-theme="dark"] .premium-card:hover {
        background: rgba(30, 41, 59, 0.6) !important;
        box-shadow: 0 12px 30px -4px rgba(115, 103, 240, 0.2), 0 4px 14px -2px rgba(115, 103, 240, 0.08) !important;
    }

    /* Welcome Card Styling */
    .welcome-card {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #7367f0 0%, #4338ca 100%);
        border-radius: 16px;
        color: #ffffff;
    }
    .welcome-card::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
        top: -100px;
        right: -100px;
        border-radius: 50%;
        pointer-events: none;
    }

    /* Stat circle values */
    .stat-circle {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        font-weight: 700;
        font-size: 1.15rem;
        transition: transform 0.2s ease;
    }
    .premium-card:hover .stat-circle {
        transform: scale(1.1);
    }
    
    .stat-circle-success {
        background: rgba(40, 199, 111, 0.12);
        color: #28c76f;
    }
    .stat-circle-warning {
        background: rgba(255, 159, 67, 0.12);
        color: #ff9f43;
    }
    .stat-circle-danger {
        background: rgba(234, 84, 85, 0.12);
        color: #ea5455;
    }

    .badge-dynamic {
        padding: 6px 12px;
        border-radius: 30px;
        font-weight: 600;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
    }

    /* Pulse animation for waiting status */
    .pulse-amber {
        box-shadow: 0 0 0 0 rgba(255, 159, 67, 0.4);
        animation: pulse-amber 1.8s infinite;
    }
    @keyframes pulse-amber {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(255, 159, 67, 0.4);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 8px rgba(255, 159, 67, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(255, 159, 67, 0);
        }
    }

    /* Quick Actions panel */
    .quick-action-btn {
        border-radius: 12px;
        padding: 12px 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
    }
    .quick-action-btn:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
  <!-- Welcome Banner -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card welcome-card border-0 shadow-sm">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 position-relative" style="z-index: 1;">
          <div>
            <h4 class="card-title mb-1 text-white fw-bold">Halo, {{ $user->name }}! 👋</h4>
            <p class="card-text mb-0 opacity-85">Pantau kehadiran, status izin, dan perkembangan akademik putra-putri Anda secara real-time.</p>
          </div>
          <div class="text-md-end">
            <span class="badge bg-white text-primary px-3 py-2 fw-bold" style="border-radius: 30px; font-size: 0.8rem; box-shadow: 0 4px 10px rgba(0,0,0,0.08);">
              Wali Murid Dashboard
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions Panel -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card premium-card">
        <div class="card-body p-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar bg-label-primary p-2 rounded" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="ti tabler-apps text-primary fs-4"></i>
              </div>
              <div>
                <h6 class="mb-0 fw-bold">Akses Cepat Portal</h6>
                <small class="text-muted">Aksi cepat wali murid</small>
              </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <a href="{{ route('ortu.izin-sakit.create') }}" class="btn btn-primary quick-action-btn shadow-sm">
                <i class="ti tabler-file-text fs-5"></i> Ajukan Izin / Sakit Anak
              </a>
              <a href="{{ route('ortu.izin-sakit.index') }}" class="btn btn-outline-secondary quick-action-btn">
                <i class="ti tabler-history fs-5"></i> Riwayat Pengajuan Izin
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if($anakList->isEmpty())
    <div class="row">
      <div class="col-12">
        <div class="card premium-card border-warning border-2">
          <div class="card-body text-center py-5">
            <div class="avatar bg-label-warning p-3 rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
              <i class="ti tabler-alert-circle text-warning fs-1"></i>
            </div>
            <h5 class="fw-bold">Data Anak Belum Ditautkan</h5>
            <p class="text-muted max-w-md mx-auto">Kami belum menemukan data siswa yang terhubung dengan akun Anda. Silakan hubungi operator sekolah untuk melakukan sinkronisasi data wali murid.</p>
          </div>
        </div>
      </div>
    </div>
  @else
    <h5 class="mb-3 fw-bold d-flex align-items-center gap-2">
      <i class="ti tabler-users text-primary"></i> Data Kehadiran Siswa
    </h5>
    
    <div class="row gy-4">
      @foreach($anakList as $dataAnak)
        @php
            $anak = $dataAnak['siswa'];
            $absensiHariIni = $dataAnak['absensi_hari_ini'];
            $stats = $dataAnak['stats'];
            $earlyWarning = $dataAnak['early_warning'];
        @endphp

        <div class="col-md-6 col-xl-4">
          <div class="card h-100 premium-card {{ $earlyWarning ? 'border-danger border-2' : '' }}">
            <div class="card-header border-bottom pb-3">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                  <h5 class="mb-1 fw-bold text-truncate" style="max-width: 180px;" title="{{ $anak->nama_lengkap }}">
                    {{ $anak->nama_lengkap }}
                  </h5>
                  <span class="badge bg-label-primary font-monospace" style="font-size: 0.7rem;">NISN: {{ $anak->nisn }}</span>
                  <div class="text-muted small mt-1">
                    <i class="ti tabler-school me-1 fs-6"></i>{{ $anak->kelas->nama ?? 'Tidak Ada Kelas' }}
                  </div>
                </div>
                <div class="text-end">
                  @if($absensiHariIni)
                    @if(in_array($absensiHariIni->status, ['sakit', 'izin']))
                      <span class="badge-dynamic bg-label-warning">
                        <i class="ti tabler-file-text"></i> {{ ucfirst($absensiHariIni->status) }}
                      </span>
                    @elseif($absensiHariIni->status == 'terlambat')
                      <span class="badge-dynamic bg-label-danger">
                        <i class="ti tabler-clock-play"></i> Terlambat
                      </span>
                    @else
                      <span class="badge-dynamic bg-label-success">
                        <i class="ti tabler-check"></i> Hadir
                      </span>
                    @endif
                    <small class="d-block text-muted mt-1" style="font-size: 0.70rem; font-weight: 500;">
                      Masuk: <span class="fw-bold text-dark dark-text-light">{{ $absensiHariIni->jam_masuk }}</span>
                    </small>
                  @else
                    <span class="badge-dynamic bg-label-warning pulse-amber">
                      <i class="ti tabler-loader animate-spin"></i> Belum Absen
                    </span>
                    <small class="d-block text-muted mt-1" style="font-size: 0.65rem;">Menunggu kehadiran...</small>
                  @endif
                </div>
              </div>
            </div>
            
            <div class="card-body pt-4">
              <h6 class="mb-3 fw-bold text-muted" style="font-size: 0.8rem; letter-spacing: 0.5px; text-transform: uppercase;">
                Ringkasan Bulan Ini
              </h6>
              <div class="row text-center mb-4">
                <div class="col-4">
                  <div class="stat-circle stat-circle-success">
                    {{ $stats['hadir'] }}
                  </div>
                  <span class="text-muted small fw-medium">Hadir</span>
                </div>
                <div class="col-4 border-start border-end">
                  <div class="stat-circle stat-circle-warning">
                    {{ $stats['izin_sakit'] }}
                  </div>
                  <span class="text-muted small fw-medium">Izin/Sakit</span>
                </div>
                <div class="col-4">
                  <div class="stat-circle stat-circle-danger">
                    {{ $stats['alpha'] }}
                  </div>
                  <span class="text-muted small fw-medium">Alpha</span>
                </div>
              </div>
              
              @if($earlyWarning)
                <div class="alert alert-danger mb-4 py-2 px-3 d-flex align-items-center gap-2" style="border-radius: 10px;">
                  <i class="ti tabler-alert-triangle fs-4 text-danger animate-bounce"></i>
                  <span class="small text-danger fw-bold" style="font-size: 0.75rem;">
                    Peringatan: Jumlah Alpha bulan ini melebihi batas toleransi!
                  </span>
                </div>
              @endif
              
              <div class="d-flex flex-column gap-2 mt-2">
                <a href="{{ route('ortu.anak.profil', $anak->id) }}" class="btn btn-outline-primary btn-sm py-2" style="border-radius: 10px; font-weight: 600;">
                  <i class="ti tabler-user-circle me-1 fs-5"></i> Lihat Detail Profil
                </a>
                <a href="{{ route('ortu.anak.absensi', $anak->id) }}" class="btn btn-outline-info btn-sm py-2" style="border-radius: 10px; font-weight: 600;">
                  <i class="ti tabler-calendar-stats me-1 fs-5"></i> Riwayat Absensi Bulanan
                </a>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  <!-- WhatsApp Integration Banner -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card premium-card" style="background: linear-gradient(135deg, rgba(37, 211, 102, 0.05) 0%, rgba(18, 140, 126, 0.02) 100%); border-left: 5px solid #25d366 !important;">
        <div class="card-body p-4">
          <div class="d-flex gap-3 align-items-center flex-column flex-sm-row">
            <div class="avatar bg-label-success p-3 rounded" style="background: rgba(37, 211, 102, 0.15) !important; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
              <i class="ti tabler-brand-whatsapp text-success fs-1"></i>
            </div>
            <div>
              <h5 class="mb-1 text-success fw-bold">Integrasi Notifikasi WhatsApp</h5>
              <p class="mb-0 text-muted small" style="line-height: 1.5;">
                Segera aktif! Anda akan menerima pesan notifikasi instan langsung ke nomor WhatsApp Anda setiap kali putra-putri Anda melakukan pemindaian (scan) absensi masuk atau pulang sekolah secara real-time.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
