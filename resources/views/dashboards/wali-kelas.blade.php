@extends('layouts/layoutMaster')

@section('title', 'Dashboard Wali Kelas')

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
      background: rgba(255, 255, 255, 0.06) !important;
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

    .action-btn {
      border-radius: 8px;
      padding: 10px 16px;
      font-weight: 600;
      transition: all 0.2s ease;
    }

    @media (max-width: 576px) {
      .stat-icon { width: 36px; height: 36px; font-size: 1.1rem; margin-bottom: 0.5rem; }
      h4 { font-size: 1.3rem !important; }
      .card-body { padding: 1.5rem 0.75rem !important; }
      .hero-header h4 { font-size: 1.1rem; }
      .badge { font-size: 0.7rem; }
    }
  </style>
@endsection

@section('content')
  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #360033 0%, #0b8793 100%); border-radius: 12px;">
        <div class="card-body p-4 p-md-5">
          <div class="row align-items-center">
            <div class="col-md-8 text-white">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded d-flex align-items-center justify-content-center shadow-lg"
                  style="width:64px;height:64px;border-radius:16px !important;background:rgba(255,255,255,0.2);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.3);">
                  <i class="ti tabler-users text-white fs-2"></i>
                </div>
                <div>
                  <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Portal Wali Kelas</h4>
                  <p class="mb-0 text-white opacity-90 fw-medium fs-5">{{ $user->name }}</p>
                </div>
              </div>
              <p class="mb-0 text-white opacity-75">Kelola kelas bimbingan Anda, periksa kehadiran harian siswa, dan bantu tugas piket jika diperlukan.</p>
            </div>
            <div class="col-md-4 text-md-end mt-4 mt-md-0">
               <div class="badge bg-white bg-opacity-10 p-2 px-3 border border-white border-opacity-20 text-white shadow-sm">
                  <i class="ti tabler-door me-1"></i> Kelas: {{ $has_class ? $kelas_nama : 'Belum Ada' }}
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- STATS SECTION --}}
  <div class="row gy-4 mb-4">
    <div class="col-6 col-md-3">
      <div class="card glass-card text-center h-100 border-0 shadow-sm">
        <div class="card-body py-4">
          <div class="stat-icon mx-auto bg-label-info shadow-sm">
            <i class="ti tabler-users"></i>
          </div>
          <h4 class="mb-1 text-white fw-bold">{{ $has_class ? $total_siswa : 0 }}</h4>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Total Siswa Kelas</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card glass-card text-center h-100 border-0 shadow-sm">
        <div class="card-body py-4">
          <div class="stat-icon mx-auto bg-label-success shadow-sm">
            <i class="ti tabler-calendar-check"></i>
          </div>
          <h4 class="mb-1 text-white fw-bold">{{ $has_class ? $hadir_hari_ini : 0 }}</h4>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Hadir Hari Ini</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card glass-card text-center h-100 border-0 shadow-sm">
          <div class="card-body py-4">
            <div class="stat-icon mx-auto bg-label-danger shadow-sm">
              <i class="ti tabler-user-x"></i>
            </div>
            <h4 class="mb-1 text-white fw-bold">{{ $has_class ? $tidak_hadir : 0 }}</h4>
            <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Tidak Hadir</small>
          </div>
        </div>
      </div>
    <div class="col-6 col-md-3">
      <div class="card glass-card text-center h-100 border-0 shadow-sm">
        <div class="card-body py-4">
          <div class="stat-icon mx-auto bg-label-warning shadow-sm">
            <i class="ti tabler-clock"></i>
          </div>
          <h4 class="mb-1 text-white fw-bold">{{ $has_class ? $pending_izin_kelas : 0 }}</h4>
          <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Izin Pending</small>
        </div>
      </div>
    </div>
  </div>

  {{-- ACTIONS SECTION --}}
  <h6 class="text-white-50 small fw-bold text-uppercase mb-3 ps-1" style="letter-spacing:1.5px; opacity:0.6;">Tugas & Piket</h6>
  <div class="row gy-4">
    <div class="col-md-4">
      <div class="card glass-card h-100">
        <div class="card-body d-flex flex-column p-4">
            <div class="stat-icon bg-label-primary mb-3"><i class="ti tabler-qrcode"></i></div>
            <h6 class="text-white fw-bold">Scanner Piket</h6>
            <p class="text-white-50 small flex-grow-1">Buka scanner untuk merekam kehadiran siswa di gerbang.</p>
            <a href="{{ route('public.scan-qr.index') }}" target="_blank" class="btn btn-primary btn-sm">Buka Scanner</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card glass-card h-100">
        <div class="card-body d-flex flex-column p-4">
            <div class="stat-icon bg-label-info mb-3"><i class="ti tabler-calendar-event"></i></div>
            <h6 class="text-white fw-bold">Kegiatan Khusus</h6>
            <p class="text-white-50 small flex-grow-1">Scan kehadiran untuk kegiatan ekskul/ujian.</p>
            <a href="{{ route('admin.absensi-kegiatan.scan') }}" class="btn btn-info btn-sm">Mulai Scan</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
        <div class="card glass-card h-100">
          <div class="card-body d-flex flex-column p-4">
              <div class="stat-icon bg-label-success mb-3"><i class="ti tabler-users"></i></div>
              <h6 class="text-white fw-bold">Monitoring Kelas</h6>
              <p class="text-white-50 small flex-grow-1">Lihat detail absensi dan rekap siswa kelas Anda.</p>
              <a href="{{ route('wali-kelas.absensi-siswa.index') }}" class="btn btn-success btn-sm">Buka Data Kelas</a>
          </div>
        </div>
      </div>
  </div>
@endsection

