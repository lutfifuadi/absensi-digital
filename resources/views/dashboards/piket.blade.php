@extends('layouts/layoutMaster')

@section('title', 'Piket Dashboard')

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .glass-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2) !important;
    }
    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
      font-size: 1.5rem;
    }

    @media (max-width: 576px) {
      .stat-icon { width: 36px; height: 36px; font-size: 1.1rem; margin-bottom: 0.5rem; }
      h3 { font-size: 1.5rem !important; }
      .card-body { padding: 1.25rem 0.75rem !important; }
      .hero-header h4 { font-size: 1.1rem; }
      .action-avatar { width: 36px; height: 36px; }
      .btn { padding: 0.5rem 1rem; font-size: 0.8rem; }
    }
  </style>
@endsection

@section('content')
  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #2f80ed 0%, #00d2ff 100%); border-radius: 12px;">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1 text-white fw-bold">Pusat Portal Guru Piket</h4>
                <p class="mb-0 text-white opacity-75">Selamat datang, {{ $user->name }}. Berikut adalah ringkasan absensi sekolah hari ini.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="badge bg-white bg-opacity-20 p-2 px-3 border border-white border-opacity-10 text-white">
                  <i class="ti tabler-calendar me-1"></i> {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- STATS GRID --}}
  <div class="row gy-4 mb-4">
    <div class="col-md-2 col-4">
      <div class="card glass-card h-100 border-0 shadow-sm text-center">
        <div class="card-body">
          <div class="stat-icon mx-auto bg-label-primary">
            <i class="ti tabler-users"></i>
          </div>
          <h3 class="mb-0 fw-bold">{{ $totalSiswa }}</h3>
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Siswa</small>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-4">
      <div class="card glass-card h-100 border-0 shadow-sm text-center">
        <div class="card-body">
          <div class="stat-icon mx-auto bg-label-success">
            <i class="ti tabler-user-check"></i>
          </div>
          <h3 class="mb-0 fw-bold">{{ $hadirCount }}</h3>
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Hadir</small>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-4">
      <div class="card glass-card h-100 border-0 shadow-sm text-center">
        <div class="card-body">
          <div class="stat-icon mx-auto bg-label-warning">
            <i class="ti tabler-clock"></i>
          </div>
          <h3 class="mb-0 fw-bold">{{ $terlambatCount }}</h3>
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Terlambat</small>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-4">
      <div class="card glass-card h-100 border-0 shadow-sm text-center">
        <div class="card-body">
          <div class="stat-icon mx-auto bg-label-info">
            <i class="ti tabler-stethoscope"></i>
          </div>
          <h3 class="mb-0 fw-bold">{{ $sakitCount }}</h3>
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Sakit</small>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-4">
      <div class="card glass-card h-100 border-0 shadow-sm text-center">
        <div class="card-body">
          <div class="stat-icon mx-auto bg-label-info">
            <i class="ti tabler-file-text"></i>
          </div>
          <h3 class="mb-0 fw-bold">{{ $izinCount }}</h3>
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Izin</small>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-4">
      <div class="card glass-card h-100 border-0 shadow-sm text-center">
        <div class="card-body">
          <div class="stat-icon mx-auto bg-label-danger">
            <i class="ti tabler-alert-circle"></i>
          </div>
          <h3 class="mb-0 fw-bold">{{ $alphaCount }}</h3>
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Alpha</small>
        </div>
      </div>
    </div>
  </div>

  {{-- QUICK ACTIONS & LOG --}}
  <div class="row">
    <div class="col-md-6 mb-4">
      <h6 class="text-white-50 small fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">Aksi Cepat Piket</h6>
      <div class="row gy-3">
        <div class="col-6">
          <div class="card glass-card h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-md bg-label-primary mx-auto mb-2">
                    <span class="avatar-initial rounded"><i class="ti tabler-qrcode fs-3"></i></span>
                </div>
                <h5 class="fw-bold mb-1" style="font-size: 0.95rem;">Scan QR Absensi</h5>
                <p class="small text-muted mb-2" style="font-size: 0.75rem;">Mulai scan QR siswa.</p>
                <a href="{{ route('public.scan-qr.index') }}" target="_blank" class="btn btn-sm btn-primary w-100">Buka Scanner</a>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="card glass-card h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-md bg-label-info mx-auto mb-2">
                    <span class="avatar-initial rounded"><i class="ti tabler-clipboard-list fs-3"></i></span>
                </div>
                <h5 class="fw-bold mb-1" style="font-size: 0.95rem;">Absensi Cepat</h5>
                <p class="small text-muted mb-2" style="font-size: 0.75rem;">Absensi manual per kelas.</p>
                <a href="{{ route('admin.absensi-cepat') }}" class="btn btn-sm btn-info w-100">Input Absen</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 mb-4">
      <h6 class="text-white-50 small fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">Aktivitas Terakhir</h6>
      <div class="card glass-card">
        <div class="card-body">
          @if($recentLogs->isEmpty())
            <div class="text-center py-4 text-muted">
              <i class="ti tabler-info-circle fs-2 mb-2 d-block"></i>
              Belum ada aktivitas scan hari ini.
            </div>
          @else
            <ul class="timeline mb-0">
              @foreach($recentLogs as $log)
                <li class="timeline-item timeline-item-transparent border-left-dashed">
                  <span class="timeline-point timeline-point-primary"></span>
                  <div class="timeline-event">
                    <div class="timeline-header mb-1">
                      <h6 class="mb-0 fw-bold text-white">{{ $log->description }}</h6>
                      <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                    </div>
                  </div>
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection