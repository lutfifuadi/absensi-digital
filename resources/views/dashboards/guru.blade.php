@extends('layouts/layoutMaster')

@section('title', 'Dashboard Saya — Guru Portal')

@section('page-style')
<style>
  .das-hero {
    position: relative;
    padding: 1.75rem 2rem;
    background: linear-gradient(135deg, rgba(15,23,42,0.95), rgba(30,41,59,0.85));
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    overflow: hidden;
  }
  .kpi-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;
    padding: 1.25rem;
    height: 100%;
    transition: all 0.2s ease;
  }
  .kpi-card:hover {
    transform: translateY(-2px);
    background: rgba(255,255,255,0.05);
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
  }
  .quick-action-btn {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;
    padding: 1.15rem 1rem;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    text-decoration: none !important;
    transition: all 0.2s ease;
  }
  .quick-action-btn:hover {
    background: rgba(115,103,240,0.12);
    border-color: rgba(115,103,240,0.3);
    transform: translateY(-2px);
    color: #fff;
  }
  .quick-action-icon {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
  }
</style>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════════════
     SECTION 1: HERO HEADER — Identitas Guru YBS + Status Presensi
═══════════════════════════════════════════════════════ --}}
<div class="das-hero mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="avatar avatar-xl flex-shrink-0">
        <span class="avatar-initial rounded-circle bg-warning text-dark fw-bold fs-3 shadow-sm">
          {{ strtoupper(substr($guruSelf ? $guruSelf->nama_lengkap : $user->name, 0, 2)) }}
        </span>
      </div>
      <div>
        <div class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-30 mb-1" style="font-size:0.7rem;">
          <i class="ti tabler-badge-check me-1 fs-tiny"></i> PORTAL GURU PRIVAT
        </div>
        <h3 class="text-white fw-bold mb-1" style="font-size:1.35rem;">Selamat datang kembali, {{ $guruSelf ? $guruSelf->nama_lengkap : $user->name }} 👋</h3>
        <p class="text-white-50 small mb-0">
          NIP: <strong>{{ $guruSelf ? ($guruSelf->nip ?? '-') : '-' }}</strong> &bull; 
          Status: <span class="badge bg-label-warning py-0 px-2 fs-tiny">{{ strtoupper(str_replace('_', ' ', $guruSelf->tipe_kepegawaian ?? 'FULL TIME')) }}</span>
        </p>
      </div>
    </div>

    {{-- Status Presensi Masuk / Pulang Guru Hari Ini --}}
    <div class="bg-black bg-opacity-30 p-3 rounded border border-white border-opacity-10 d-flex align-items-center gap-3">
      <div class="text-end">
        <div class="text-white-50 fs-tiny uppercase fw-semibold">Status Presensi Saya Hari Ini</div>
        @if($selfAbsensiHariIni)
          @if($selfAbsensiHariIni->status === 'hadir')
            <div class="fw-bold text-success fs-6"><i class="ti tabler-circle-check me-1"></i> HADIR ({{ substr($selfAbsensiHariIni->jam_masuk, 0, 5) }})</div>
          @elseif($selfAbsensiHariIni->status === 'terlambat')
            <div class="fw-bold text-warning fs-6"><i class="ti tabler-clock-check me-1"></i> TERLAMBAT ({{ substr($selfAbsensiHariIni->jam_masuk, 0, 5) }})</div>
          @elseif($selfAbsensiHariIni->status === 'izin')
            <div class="fw-bold text-info fs-6"><i class="ti tabler-file-text me-1"></i> IZIN</div>
          @elseif($selfAbsensiHariIni->status === 'sakit')
            <div class="fw-bold text-secondary fs-6"><i class="ti tabler-stethoscope me-1"></i> SAKIT</div>
          @else
            <div class="fw-bold text-danger fs-6"><i class="ti tabler-alert-triangle me-1"></i> ALPHA</div>
          @endif
        @else
          <div class="fw-bold text-warning fs-6"><i class="ti tabler-alert-circle me-1"></i> Belum Presensi Masuk</div>
        @endif
      </div>
      
      <a href="{{ route('public.scan-qr.index') }}" class="btn btn-sm btn-warning fw-bold px-3 py-2 shadow-sm d-flex align-items-center gap-1" style="border-radius: 8px;">
        <i class="ti tabler-qr fs-5"></i> Scan QR
      </a>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     SECTION 2: 4 KPI CARDS — Statistik Kehadiran Saya Bulan Ini
═══════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
  {{-- Card 1: Hadir Tepat Waktu Saya --}}
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="text-white-50 uppercase fw-bold fs-tiny">Hadir Tepat Waktu</div>
        <div class="avatar avatar-sm">
          <span class="avatar-initial rounded bg-success bg-opacity-15 text-success">
            <i class="ti tabler-circle-check"></i>
          </span>
        </div>
      </div>
      <div class="fs-2 fw-extrabold text-success mb-1">{{ number_format($selfStats['count_hadir']) }}</div>
      <div class="text-white-50 fs-tiny">Tingkat Kehadiran: <strong class="text-white">{{ $selfStats['persentase_kehadiran'] }}%</strong></div>
    </div>
  </div>

  {{-- Card 2: Terlambat Saya --}}
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="text-white-50 uppercase fw-bold fs-tiny">Terlambat</div>
        <div class="avatar avatar-sm">
          <span class="avatar-initial rounded bg-warning bg-opacity-15 text-warning">
            <i class="ti tabler-clock-check"></i>
          </span>
        </div>
      </div>
      <div class="fs-2 fw-extrabold text-warning mb-1">{{ number_format($selfStats['count_terlambat']) }}</div>
      <div class="text-white-50 fs-tiny">Presensi masuk terlambat</div>
    </div>
  </div>

  {{-- Card 3: Izin & Sakit Saya --}}
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="text-white-50 uppercase fw-bold fs-tiny">Izin & Sakit</div>
        <div class="avatar avatar-sm">
          <span class="avatar-initial rounded bg-info bg-opacity-15 text-info">
            <i class="ti tabler-stethoscope"></i>
          </span>
        </div>
      </div>
      <div class="fs-2 fw-extrabold text-info mb-1">{{ number_format($selfStats['count_izin_sakit']) }}</div>
      <div class="text-white-50 fs-tiny">Pengajuan keterangan resmi</div>
    </div>
  </div>

  {{-- Card 4: Alpha / Tanpa Ket. --}}
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="text-white-50 uppercase fw-bold fs-tiny">Tanpa Keterangan</div>
        <div class="avatar avatar-sm">
          <span class="avatar-initial rounded bg-danger bg-opacity-15 text-danger">
            <i class="ti tabler-alert-triangle"></i>
          </span>
        </div>
      </div>
      <div class="fs-2 fw-extrabold text-danger mb-1">{{ number_format($selfStats['count_alpha']) }}</div>
      <div class="text-white-50 fs-tiny">Hari tanpa presensi</div>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     SECTION 3: JADWAL MENGAJAR SAYA HARI INI & AKSI CEPAT
═══════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
  {{-- Jadwal Mengajar Hari Ini --}}
  <div class="col-12 col-xl-8">
    <div class="card bg-dark border border-secondary border-opacity-20 shadow-sm h-100">
      <div class="card-header bg-transparent border-bottom border-secondary border-opacity-20 py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title text-white mb-0 d-flex align-items-center gap-2" style="font-size:0.95rem;">
          <i class="ti tabler-calendar-event text-warning"></i> Jadwal Mengajar Saya Hari Ini ({{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }})
        </h5>
        <span class="badge bg-primary bg-opacity-20 text-primary fs-tiny">{{ $selfJadwalHariIni->count() }} Kelas</span>
      </div>

      <div class="card-body p-0">
        @if($selfJadwalHariIni->isNotEmpty())
          <div class="table-responsive">
            <table class="table align-middle text-white mb-0" style="font-size:0.85rem;">
              <thead>
                <tr class="text-white-50 border-bottom border-secondary border-opacity-20">
                  <th class="ps-4">Jam Pelajaran</th>
                  <th>Kelas</th>
                  <th>Mata Pelajaran</th>
                  <th class="text-end pe-4">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @foreach($selfJadwalHariIni as $j)
                  <tr class="border-bottom border-secondary border-opacity-10">
                    <td class="ps-4">
                      <span class="badge bg-secondary bg-opacity-20 text-warning border border-warning border-opacity-30">
                        <i class="ti tabler-clock me-1"></i> {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                      </span>
                    </td>
                    <td>
                      <strong class="text-white">{{ $j->kelas ? $j->kelas->nama : '-' }}</strong>
                    </td>
                    <td>
                      <span class="text-white-50">{{ $j->mata_pelajaran ?? '-' }}</span>
                    </td>
                    <td class="text-end pe-4">
                      <a href="{{ route('guru.absensi-per-jam') }}" class="btn btn-xs btn-primary d-inline-flex align-items-center gap-1 shadow-xs">
                        <i class="ti tabler-edit fs-tiny"></i> Absen Kelas
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-5 text-white-50">
            <div class="mb-2"><i class="ti tabler-calendar-off fs-1 opacity-25"></i></div>
            <h6>Tidak ada jadwal mengajar pada hari {{ now()->locale('id')->isoFormat('dddd') }}.</h6>
            <p class="small text-white-50 mb-0">Nikmati waktu Anda atau periksa rekap presensi harian Anda.</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Quick Action Links --}}
  <div class="col-12 col-xl-4">
    <div class="card bg-dark border border-secondary border-opacity-20 shadow-sm h-100">
      <div class="card-header bg-transparent border-bottom border-secondary border-opacity-20 py-3">
        <h5 class="card-title text-white mb-0 d-flex align-items-center gap-2" style="font-size:0.95rem;">
          <i class="ti tabler-bolt text-warning"></i> Aksi Cepat Portal Guru
        </h5>
      </div>
      
      <div class="card-body d-flex flex-column gap-2 p-3 justify-content-center">
        <a href="{{ route('guru.absensi-per-jam') }}" class="quick-action-btn">
          <div class="quick-action-icon bg-primary bg-opacity-20 text-primary border border-primary border-opacity-30">
            <i class="ti tabler-clipboard-check"></i>
          </div>
          <div>
            <div class="fw-bold text-white fs-6">Absensi Kelas & Mapel</div>
            <div class="text-white-50 fs-tiny">Input kehadiran siswa per jam mengajar</div>
          </div>
        </a>

        <a href="{{ route('guru.absensi-cepat') }}" class="quick-action-btn">
          <div class="quick-action-icon bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30">
            <i class="ti tabler-bolt"></i>
          </div>
          <div>
            <div class="fw-bold text-white fs-6">Absensi Cepat Siswa</div>
            <div class="text-white-50 fs-tiny">Mode cepat pencatatan absensi massal</div>
          </div>
        </a>

        <a href="{{ route('guru.rekap-absensi-saya') }}" class="quick-action-btn">
          <div class="quick-action-icon bg-success bg-opacity-20 text-success border border-success border-opacity-30">
            <i class="ti tabler-user-check"></i>
          </div>
          <div>
            <div class="fw-bold text-white fs-6">Rekap Absensi Saya</div>
            <div class="text-white-50 fs-tiny">Lihat riwayat presensi harian pribadi</div>
          </div>
        </a>

        <a href="{{ route('guru.izin-sakit.index') }}" class="quick-action-btn">
          <div class="quick-action-icon bg-info bg-opacity-20 text-info border border-info border-opacity-30">
            <i class="ti tabler-stethoscope"></i>
          </div>
          <div>
            <div class="fw-bold text-white fs-6">Pengajuan Izin / Sakit</div>
            <div class="text-white-50 fs-tiny">Ajukan surat izin atau sakit resmi</div>
          </div>
        </a>
      </div>
    </div>
  </div>
</div>

@endsection
