@extends('layouts/layoutFront')

@section('title', $namaSekolah)

@section('content')
<style>
  :root {
    --p-primary: #6366f1;
    --p-secondary: #a855f7;
    --p-dark: #0f172a;
    --p-glass: rgba(255, 255, 255, 0.03);
    --p-glass-border: rgba(255, 255, 255, 0.08);
  }

  body {
    background-color: var(--p-dark);
    color: #e2e8f0;
    overflow-x: hidden;
  }

  /* ── Navbar ── */
  .p-nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    padding: 20px 0;
    transition: all 0.3s ease;
    background: transparent;
  }
  .p-nav.scrolled {
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(12px);
    padding: 12px 0;
    border-bottom: 1px solid var(--p-glass-border);
  }
  .p-nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .p-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: #fff;
    font-weight: 800;
    font-size: 1.25rem;
    letter-spacing: -0.02em;
  }
  .p-logo-img {
    width: 40px; height: 40px;
    object-fit: contain;
  }
  .p-logo-mark {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, var(--p-primary), var(--p-secondary));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
  }

  /* ── Hero ── */
  .p-hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    padding: 120px 0 80px;
    background-image: 
      radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
      radial-gradient(circle at 90% 80%, rgba(168, 85, 247, 0.1) 0%, transparent 40%);
  }
  .p-hero-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    width: 100%;
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 60px;
    align-items: center;
  }
  .p-hero-text h1 {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 850;
    line-height: 1.1;
    margin-bottom: 20px;
    background: linear-gradient(to right, #fff, #94a3b8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .p-hero-text p {
    font-size: 1.125rem;
    color: #94a3b8;
    max-width: 540px;
    margin-bottom: 40px;
    line-height: 1.6;
  }
  .p-hero-btns {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
  }
  .p-btn {
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9375rem;
  }
  .p-btn-primary {
    background: linear-gradient(135deg, var(--p-primary), var(--p-secondary));
    color: #fff;
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
  }
  .p-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(99, 102, 241, 0.5);
  }
  .p-btn-outline {
    background: var(--p-glass);
    border: 1px solid var(--p-glass-border);
    color: #fff;
    backdrop-filter: blur(8px);
  }
  .p-btn-outline:hover {
    background: rgba(255,255,255,0.08);
    transform: translateY(-2px);
  }

  /* ── Hero Card ── */
  .p-hero-visual {
    position: relative;
  }
  .p-card-float {
    background: rgba(30, 41, 59, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid var(--p-glass-border);
    border-radius: 24px;
    padding: 32px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    position: relative;
    z-index: 10;
  }
  .p-status-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  .p-status-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px;
    background: var(--p-glass);
    border-radius: 16px;
    border: 1px solid transparent;
    transition: all 0.2s;
  }
  .p-status-item:hover {
    border-color: var(--p-glass-border);
    background: rgba(255,255,255,0.06);
  }
  .p-status-avatar {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6, #06b6d4);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700;
    color: #fff;
  }
  .p-status-info h5 { margin: 0; font-size: 0.9375rem; font-weight: 600; color: #fff; }
  .p-status-info p { margin: 0; font-size: 0.75rem; color: #94a3b8; }
  .p-status-badge {
    margin-left: auto;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 0.6875rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .bg-hadir { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
  .bg-terlambat { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }

  /* ── Stats ── */
  .p-stats {
    padding: 60px 0;
    background: rgba(15, 23, 42, 0.5);
  }
  .p-stats-grid {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 32px;
  }
  .p-stat-card {
    text-align: center;
  }
  .p-stat-num {
    font-size: 2.5rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
  }
  .p-stat-label {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
  }

  /* ── Features ── */
  .p-section {
    padding: 100px 0;
  }
  .p-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
  }
  .p-section-head {
    text-align: center;
    margin-bottom: 60px;
  }
  .p-section-head h2 {
    font-size: 2.25rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 16px;
  }
  .p-section-head p {
    color: #94a3b8;
    max-width: 600px;
    margin: 0 auto;
  }
  .p-feat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
  }
  .p-feat-card {
    background: var(--p-glass);
    border: 1px solid var(--p-glass-border);
    padding: 40px;
    border-radius: 24px;
    transition: all 0.3s ease;
  }
  .p-feat-card:hover {
    transform: translateY(-8px);
    background: rgba(255,255,255,0.06);
    border-color: rgba(99, 102, 241, 0.3);
  }
  .p-feat-icon {
    width: 56px; height: 56px;
    background: rgba(99, 102, 241, 0.1);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    color: var(--p-primary);
    font-size: 1.5rem;
    margin-bottom: 24px;
  }
  .p-feat-card h3 { color: #fff; margin-bottom: 12px; font-size: 1.25rem; font-weight: 700; }
  .p-feat-card p { color: #94a3b8; line-height: 1.6; margin: 0; }

  /* ── Responsive ── */
  @media (max-width: 991px) {
    .p-hero-content { grid-template-columns: 1fr; text-align: center; }
    .p-hero-text p { margin-left: auto; margin-right: auto; }
    .p-hero-btns { justify-content: center; }
    .p-hero-visual { display: none; }
  }

  @media (max-width: 480px) {
    .p-hero-text h1 { font-size: 2.25rem; }
    .p-btn { width: 100%; justify-content: center; }
  }
</style>

<nav class="p-nav" id="mainNav">
  <div class="p-nav-container">
    <a href="#" class="p-logo">
      @if($logoSekolah)
        <img src="{{ asset('storage/' . $logoSekolah) }}" class="p-logo-img" alt="Logo">
      @else
        <div class="p-logo-mark">
          <i class="ti tabler-school text-white"></i>
        </div>
      @endif
      <span>{{ $namaSekolah }}</span>
    </a>
    <div class="d-none d-md-flex gap-4">
      @auth
        <a href="{{ route('dashboard') }}" class="p-btn p-btn-primary py-2 px-4">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="p-btn p-btn-primary py-2 px-4">Masuk</a>
      @endauth
    </div>
  </div>
</nav>

<section class="p-hero">
  <div class="p-hero-content">
    <div class="p-hero-text" data-aos="fade-right">
      <h1>Portal Digital Presensi {{ $namaSekolah }}</h1>
      <p>
        {{ \App\Models\Pengaturan::where('key', 'slogan_lembaga')->value('value') ?? 'Modernisasi ekosistem sekolah melalui teknologi presensi berbasis QR Code yang cerdas, cepat, dan transparan.' }}
      </p>
      <div class="p-hero-btns">
        @auth
          <a href="{{ route('dashboard') }}" class="p-btn p-btn-primary">
            <i class="ti tabler-layout-dashboard"></i> Buka Dashboard
          </a>
        @else
          <a href="{{ route('login') }}" class="p-btn p-btn-primary">
            Mulai Sekarang <i class="ti tabler-arrow-right"></i>
          </a>
          <a href="{{ route('public.live-board') }}" class="p-btn p-btn-outline">
            <i class="ti tabler-device-tv"></i> Live Board
          </a>
        @endauth
      </div>
    </div>

    <div class="p-hero-visual" data-aos="fade-left">
      <div class="p-card-float">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="m-0 text-white font-weight-700" style="font-size:1.1rem;">Live Presensi</h4>
          <span class="badge bg-label-success rounded-pill">Real-time</span>
        </div>
        <div class="p-status-list">
          <div class="p-status-item">
            <div class="p-status-avatar" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">AR</div>
            <div class="p-status-info">
              <h5>Ahmad Ridwan</h5>
              <p>Siswa &middot; 07:15 WIB</p>
            </div>
            <div class="p-status-badge bg-hadir">Hadir</div>
          </div>
          <div class="p-status-item">
            <div class="p-status-avatar" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">SF</div>
            <div class="p-status-info">
              <h5>Siti Fatimah</h5>
              <p>Guru &middot; 07:22 WIB</p>
            </div>
            <div class="p-status-badge bg-terlambat">Terlambat</div>
          </div>
          <div class="p-status-item">
            <div class="p-status-avatar" style="background: linear-gradient(135deg, #10b981, #3b82f6);">MN</div>
            <div class="p-status-info">
              <h5>M. Naufal</h5>
              <p>Staff &middot; 07:30 WIB</p>
            </div>
            <div class="p-status-badge bg-hadir">Hadir</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="p-stats">
  <div class="p-stats-grid">
    <div class="p-stat-card" data-aos="fade-up">
      <div class="p-stat-num">{{ max($siswaCount, 0) }}</div>
      <div class="p-stat-label">Siswa</div>
    </div>
    <div class="p-stat-card" data-aos="fade-up" data-aos-delay="100">
      <div class="p-stat-num">{{ max($guruCount, 0) }}</div>
      <div class="p-stat-label">Guru</div>
    </div>
    <div class="p-stat-card" data-aos="fade-up" data-aos-delay="200">
      <div class="p-stat-num">{{ max($staffCount, 0) }}</div>
      <div class="p-stat-label">Staff</div>
    </div>
    <div class="p-stat-card" data-aos="fade-up" data-aos-delay="300">
      <div class="p-stat-num">99.9%</div>
      <div class="p-stat-label">Uptime</div>
    </div>
  </div>
</section>

<section class="p-section">
  <div class="p-container">
    <div class="p-section-head" data-aos="fade-up">
      <h2>Ekosistem Digital Sekolah</h2>
      <p>Satu platform terintegrasi untuk memudahkan manajemen kehadiran dan meningkatkan kedisiplinan seluruh warga sekolah.</p>
    </div>
    <div class="p-feat-grid">
      <div class="p-feat-card" data-aos="fade-up">
        <div class="p-feat-icon"><i class="ti tabler-qrcode"></i></div>
        <h3>Smart QR Attendance</h3>
        <p>Presensi super cepat menggunakan QR Code unik untuk setiap siswa dan guru, terintegrasi dengan geofencing.</p>
      </div>
      <div class="p-feat-card" data-aos="fade-up" data-aos-delay="100">
        <div class="p-feat-icon"><i class="ti tabler-device-desktop-analytics"></i></div>
        <h3>Analitik Real-time</h3>
        <p>Pantau data kehadiran secara langsung melalui dashboard interaktif dan live board di lobi sekolah.</p>
      </div>
      <div class="p-feat-card" data-aos="fade-up" data-aos-delay="200">
        <div class="p-feat-icon"><i class="ti tabler-file-report"></i></div>
        <h3>Laporan Otomatis</h3>
        <p>Rekapitulasi absensi harian, mingguan, dan bulanan yang dapat diunduh kapan saja dalam format PDF & Excel.</p>
      </div>
    </div>
  </div>
</section>

<footer class="py-5" style="border-top: 1px solid var(--p-glass-border);">
  <div class="p-container text-center">
    <p class="mb-0 text-muted" style="font-size: 0.875rem;">
      &copy; {{ date('Y') }} {{ $namaSekolah }}. Dikembangkan dengan ❤️ untuk pendidikan Indonesia.
    </p>
  </div>
</footer>

<script>
  window.addEventListener('scroll', function() {
    const nav = document.getElementById('mainNav');
    if (window.scrollY > 50) {
      nav.classList.add('scrolled');
    } else {
      nav.classList.remove('scrolled');
    }
  });
</script>

{{-- AOS --}}
<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof AOS !== 'undefined') {
      AOS.init({
        duration: 800,
        once: true,
        easing: 'ease-out-cubic'
      });
    }
  });
</script>
@endsection
