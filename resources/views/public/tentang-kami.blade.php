@extends('layouts/layoutFront')

@section('title', 'Tentang Kami — ' . ($info['nama_lembaga'] ?? 'Sistem Absensi'))

@section('content')
<style>
.pub-hero {
  background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
  padding: 6rem 0 4rem;
  position: relative;
  overflow: hidden;
}
.pub-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.pub-hero .container { position: relative; z-index: 1; }
.pub-section { padding: 4rem 0; }
.pub-section:nth-child(even) { background: #f8fafc; }
.info-card {
  background: #fff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 20px rgba(0,0,0,.08);
  height: 100%;
  transition: transform .2s, box-shadow .2s;
}
.info-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,.12); }
.info-card .icon-wrap {
  width: 56px; height: 56px;
  border-radius: 14px;
  background: linear-gradient(135deg, #696cff, #7b83ff);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1rem;
  font-size: 1.5rem; color: #fff;
}
</style>

{{-- Hero --}}
<section class="pub-hero text-white text-center">
  <div class="container">
    <div class="badge bg-primary bg-opacity-25 text-primary-emphasis px-3 py-2 mb-3 fs-6">
      <i class="ti tabler-school me-1"></i> Tentang Kami
    </div>
    <h1 class="fw-bold display-5 mb-3">{{ $info['nama_lembaga'] }}</h1>
    @if($info['nama_yayasan'])
      <p class="fs-5 text-white-50 mb-0">{{ $info['nama_yayasan'] }}</p>
    @endif
    @if($info['status_akreditasi'])
      <span class="badge bg-warning text-dark mt-2 px-3 py-2">
        <i class="ti tabler-award me-1"></i>{{ $info['status_akreditasi'] }}
      </span>
    @endif
  </div>
</section>

{{-- Profil Lembaga --}}
<section class="pub-section">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <h2 class="fw-bold mb-3">Profil Lembaga</h2>
        <p class="text-muted fs-5 mb-4">
          {{ $info['nama_lembaga'] }} adalah institusi pendidikan yang berkomitmen memberikan layanan pendidikan berkualitas 
          tinggi dengan memanfaatkan teknologi digital untuk meningkatkan efisiensi dan transparansi proses pembelajaran.
        </p>
        <ul class="list-unstyled">
          @if($info['alamat_lembaga'])
          <li class="d-flex gap-3 mb-3">
            <div style="width:40px;height:40px;background:#eef2ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="ti tabler-map-pin text-primary"></i>
            </div>
            <div>
              <div class="fw-semibold">Alamat</div>
              <div class="text-muted">{{ $info['alamat_lembaga'] }}</div>
            </div>
          </li>
          @endif
          @if($info['kontak_lembaga'])
          <li class="d-flex gap-3 mb-3">
            <div style="width:40px;height:40px;background:#eef2ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="ti tabler-phone text-primary"></i>
            </div>
            <div>
              <div class="fw-semibold">Telepon</div>
              <div class="text-muted">{{ $info['kontak_lembaga'] }}</div>
            </div>
          </li>
          @endif
          @if($info['email_lembaga'])
          <li class="d-flex gap-3 mb-3">
            <div style="width:40px;height:40px;background:#eef2ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="ti tabler-mail text-primary"></i>
            </div>
            <div>
              <div class="fw-semibold">Email</div>
              <div class="text-muted">{{ $info['email_lembaga'] }}</div>
            </div>
          </li>
          @endif
          @if($info['website_lembaga'])
          <li class="d-flex gap-3 mb-3">
            <div style="width:40px;height:40px;background:#eef2ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="ti tabler-world text-primary"></i>
            </div>
            <div>
              <div class="fw-semibold">Website</div>
              <div class="text-muted">{{ $info['website_lembaga'] }}</div>
            </div>
          </li>
          @endif
        </ul>
      </div>
      <div class="col-lg-6">
        <div class="row g-3">
          @php
          $features = [
            ['icon' => 'tabler-qrcode', 'title' => 'Absensi QR Code', 'desc' => 'Scan QR untuk absensi cepat dan akurat'],
            ['icon' => 'tabler-brand-whatsapp', 'title' => 'Notifikasi WA', 'desc' => 'Laporan absensi otomatis ke orang tua'],
            ['icon' => 'tabler-chart-bar', 'title' => 'Laporan Real-time', 'desc' => 'Data absensi tersedia secara real-time'],
            ['icon' => 'tabler-shield-check', 'title' => 'Data Aman', 'desc' => 'Keamanan data siswa terjamin'],
          ];
          @endphp
          @foreach($features as $f)
          <div class="col-6">
            <div class="info-card text-center">
              <div class="icon-wrap mx-auto">
                <i class="ti {{ $f['icon'] }}"></i>
              </div>
              <div class="fw-semibold mb-1">{{ $f['title'] }}</div>
              <div class="text-muted small">{{ $f['desc'] }}</div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Visi Misi --}}
<section class="pub-section">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Visi & Misi</h2>
      <p class="text-muted">Landasan kami dalam mengembangkan sistem presensi digital</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="info-card" style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;">
          <div style="width:56px;height:56px;background:rgba(255,255,255,.1);border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
            <i class="ti tabler-eye" style="font-size:1.5rem;"></i>
          </div>
          <h4 class="fw-bold mb-3">Visi</h4>
          <p class="mb-0 opacity-90">
            Mewujudkan sistem presensi digital yang cerdas, transparan, dan terintegrasi untuk mendukung ekosistem 
            pendidikan modern yang efektif dan akuntabel.
          </p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="info-card">
          <div class="icon-wrap">
            <i class="ti tabler-target"></i>
          </div>
          <h4 class="fw-bold mb-3">Misi</h4>
          <ul class="text-muted mb-0 ps-3">
            <li class="mb-2">Menyediakan sistem presensi yang mudah digunakan oleh semua pemangku kepentingan</li>
            <li class="mb-2">Meningkatkan akurasi dan efisiensi pencatatan kehadiran</li>
            <li class="mb-2">Membangun komunikasi transparan antara sekolah dan orang tua</li>
            <li>Mendukung digitalisasi administrasi pendidikan secara berkelanjutan</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- CTA --}}
<section class="pub-section" style="background:linear-gradient(135deg,#696cff,#5e60ce);">
  <div class="container text-center text-white py-3">
    <h3 class="fw-bold mb-2">Mulai Gunakan Sistem Presensi</h3>
    <p class="mb-4 opacity-90">Hubungi kami untuk informasi lebih lanjut atau langsung masuk ke dashboard</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="{{ route('public.bantuan') }}" class="btn btn-light btn-lg px-4 fw-semibold">
        <i class="ti tabler-help-circle me-2"></i>Butuh Bantuan?
      </a>
      <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4 fw-semibold">
        <i class="ti tabler-login me-2"></i>Masuk ke Sistem
      </a>
    </div>
  </div>
</section>

@endsection
