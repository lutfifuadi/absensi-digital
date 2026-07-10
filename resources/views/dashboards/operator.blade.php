@extends('layouts/layoutMaster')

@section('title', 'Operator Dashboard')

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
        style="background: linear-gradient(135deg, #7367f0 0%, #ce9ffc 100%); border-radius: 12px;">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1 text-white fw-bold">Pusat Data Operator</h4>
                <p class="mb-0 text-white opacity-75">Selamat datang, {{ $user->name }}. Berikut adalah ringkasan data operasional sekolah hari ini.</p>
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
    <div class="col-md-3 col-6">
      <div class="card glass-card h-100 border-0 shadow-sm text-center">
        <div class="card-body">
          <div class="stat-icon mx-auto bg-label-primary">
            <i class="ti tabler-users"></i>
          </div>
          <h3 class="mb-0 fw-bold">{{ $totalSiswa }}</h3>
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Siswa</small>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card glass-card h-100 border-0 shadow-sm text-center">
        <div class="card-body">
          <div class="stat-icon mx-auto bg-label-success">
            <i class="ti tabler-user-check"></i>
          </div>
          <h3 class="mb-0 fw-bold">{{ $totalAbsensiHariIni }}</h3>
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Absensi Masuk</small>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card glass-card h-100 border-0 shadow-sm text-center">
        <div class="card-body">
          <div class="stat-icon mx-auto bg-label-warning">
            <i class="ti tabler-stethoscope"></i>
          </div>
          <h3 class="mb-0 fw-bold">{{ $totalIzinPending }}</h3>
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Izin Pending</small>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card glass-card h-100 border-0 shadow-sm text-center">
        <div class="card-body">
          <div class="stat-icon mx-auto bg-label-info">
            <i class="ti tabler-calendar-event"></i>
          </div>
          <h3 class="mb-0 fw-bold">{{ $kegiatanAktif }}</h3>
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Kegiatan Hari Ini</small>
        </div>
      </div>
    </div>
  </div>

  {{-- QUICK ACTIONS --}}
  <h6 class="text-white-50 small fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">Aksi Cepat Operator</h6>
  <div class="row gy-4">
    <div class="col-md-3">
      <div class="card glass-card h-100">
        <div class="card-body p-4 text-center">
            <div class="avatar avatar-lg bg-label-primary mx-auto mb-3">
                <span class="avatar-initial rounded"><i class="ti tabler-database fs-2"></i></span>
            </div>
            <h5 class="fw-bold">Master Data</h5>
            <p class="small text-muted">Input siswa, guru, dan rombel.</p>
            <a href="{{ route('admin.master-data') }}" class="btn btn-primary w-100">Buka Master Data</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card glass-card h-100">
        <div class="card-body p-4 text-center">
            <div class="avatar avatar-lg bg-label-info mx-auto mb-3">
                <span class="avatar-initial rounded"><i class="ti tabler-qrcode fs-2"></i></span>
            </div>
            <h5 class="fw-bold">Scan Kegiatan</h5>
            <p class="small text-muted">Mulai scan kegiatan khusus.</p>
            <a href="{{ route('admin.absensi-kegiatan.scan') }}" class="btn btn-info w-100">Mulai Scanner</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
        <div class="card glass-card h-100">
          <div class="card-body p-4 text-center">
              <div class="avatar avatar-lg bg-label-warning mx-auto mb-3">
                  <span class="avatar-initial rounded"><i class="ti tabler-id-badge-2 fs-2"></i></span>
              </div>
              <h5 class="fw-bold">ID Card Card</h5>
              <p class="small text-muted">Desain dan cetak kartu pelajar.</p>
              <a href="{{ route('admin.id-card-templates.index') }}" class="btn btn-warning w-100">Designer</a>
          </div>
        </div>
      </div>
    <div class="col-md-3">
      <div class="card glass-card h-100" style="background: linear-gradient(135deg, rgba(40,199,111,0.1) 0%, rgba(40,199,111,0.02) 100%) !important;">
        <div class="card-body p-4 text-center">
            <div class="avatar avatar-lg bg-label-success mx-auto mb-3">
                <span class="avatar-initial rounded"><i class="ti tabler-brand-whatsapp fs-2"></i></span>
            </div>
            <h5 class="fw-bold">Weekly Digest</h5>
            <p class="small text-muted">Kirim laporan WA Kepala Sekolah.</p>
            <button onclick="sendWeeklyDigest(this)" class="btn btn-success w-100 fw-bold"><i class="ti tabler-send me-1"></i> Kirim</button>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card glass-card h-100">
        <div class="card-body p-4 text-center">
            <div class="avatar avatar-lg bg-label-danger mx-auto mb-3">
                <span class="avatar-initial rounded"><i class="ti tabler-scan fs-2"></i></span>
            </div>
            <h5 class="fw-bold">Scan Kegiatan Publik</h5>
            <p class="small text-muted">Scan tanpa lock device.</p>
            <a href="{{ route('public.kegiatan.index') }}" target="_blank" class="btn btn-danger w-100">Scan Publik</a>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function sendWeeklyDigest(btn) {
    btn.disabled = true;
    let originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';
    
    fetch('{{ route('admin.weekly-digest.send') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                confirmButtonColor: '#28c76f'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message,
                confirmButtonColor: '#ea5455'
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: 'Gagal menghubungi server.',
            confirmButtonColor: '#ea5455'
        });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
@endpush
