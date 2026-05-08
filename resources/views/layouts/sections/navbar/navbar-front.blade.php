@php
use Illuminate\Support\Facades\Route;
$currentRouteName = Route::currentRouteName();
$namaSekolah = \App\Models\Pengaturan::where('key', 'nama_lembaga')->value('value')
  ?? \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value')
  ?? 'Sistem Absensi';
@endphp

<style>
  .navbar-front-custom {
    background: rgba(15, 23, 42, 0.8) !important;
    backdrop-filter: blur(15px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 0.75rem 0;
  }
  
  .navbar-front-custom .navbar-brand {
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff !important;
    letter-spacing: -0.5px;
  }

  .navbar-front-custom .nav-link {
    color: rgba(255, 255, 255, 0.7) !important;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.3s ease;
  }

  .navbar-front-custom .nav-link:hover, 
  .navbar-front-custom .nav-link.active {
    color: #fff !important;
  }

  /* Fixed Mobile Menu Sidebar Style */
  @media (max-width: 991.98px) {
    .navbar-collapse {
      background: #0f172a;
      position: fixed;
      top: 0;
      left: 0;
      width: 280px;
      height: 100vh;
      display: block !important;
      transform: translateX(-100%);
      transition: transform 0.3s cubic-bezier(0.7, 0, 0.3, 1);
      padding: 2rem;
      z-index: 1050;
      border-right: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 20px 0 50px rgba(0,0,0,0.5);
    }
    .navbar-collapse.show {
      transform: translateX(0);
    }
    .navbar-toggler[aria-expanded="true"] {
       color: #fff !important;
    }
    .mobile-menu-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(5px);
      z-index: 1040;
    }
    .navbar-collapse.show + .mobile-menu-overlay {
      display: block;
    }
    .close-mobile-menu {
      position: absolute;
      top: 1.5rem;
      right: 1.5rem;
      color: #fff;
      font-size: 1.5rem;
      cursor: pointer;
    }
  }
</style>

<nav class="navbar navbar-expand-lg navbar-front-custom sticky-top">
  <div class="container text-white">
    <a class="navbar-brand d-flex align-items-center" href="/">
      <div class="avatar avatar-sm me-2 bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
        <i class="ti tabler-school text-white fs-5"></i>
      </div>
      <span>{{ $namaSekolah }}</span>
    </a>
    
    <button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <i class="ti tabler-menu-2 fs-2 text-white"></i>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <div class="d-lg-none close-mobile-menu" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <i class="ti tabler-x"></i>
      </div>
      
      <div class="d-lg-none mb-4 pt-2">
         <span class="fw-bold text-primary fs-4">{{ $namaSekolah }}</span>
         <hr class="border-secondary opacity-25">
      </div>

      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link px-3 {{ $currentRouteName === 'pages-home' ? 'active' : '' }}" href="/">Beranda</a></li>
        <li class="nav-item"><a class="nav-link px-3" href="/#fitur">Fitur</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle px-3" href="#" role="button" data-bs-toggle="dropdown">Informasi</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item {{ $currentRouteName === 'public.tentang-kami' ? 'active' : '' }}" href="{{ route('public.tentang-kami') }}"><i class="ti tabler-school me-2"></i>Tentang Kami</a></li>
            <li><a class="dropdown-item {{ $currentRouteName === 'public.panduan-pengguna' ? 'active' : '' }}" href="{{ route('public.panduan-pengguna') }}"><i class="ti tabler-book me-2"></i>Panduan Pengguna</a></li>
            <li><a class="dropdown-item {{ $currentRouteName === 'public.kebijakan-privasi' ? 'active' : '' }}" href="{{ route('public.kebijakan-privasi') }}"><i class="ti tabler-shield-check me-2"></i>Kebijakan Privasi</a></li>
            <li><a class="dropdown-item {{ $currentRouteName === 'public.bantuan' ? 'active' : '' }}" href="{{ route('public.bantuan') }}"><i class="ti tabler-help-circle me-2"></i>Bantuan</a></li>
          </ul>
        </li>
      </ul>

      <div class="d-flex flex-column flex-lg-row gap-3">
        <a href="{{ route('public.live-board') }}" class="btn btn-sm btn-outline-danger rounded-pill fw-bold px-3 d-flex align-items-center justify-content-center gap-2" target="_blank">
          <span style="width:8px;height:8px;background:#ea5455;border-radius:50%;display:inline-block;box-shadow:0 0 10px #ea5455;"></span> Live Board
        </a>
        <a href="{{ route('public.scan-qr.index') }}" class="btn btn-sm btn-outline-primary rounded-pill border-2 fw-bold px-4">
          <i class="ti tabler-qrcode me-2"></i> Scan QR
        </a>
        @if(auth()->check())
          <div class="dropdown">
            <button class="btn btn-sm btn-primary rounded-pill fw-bold px-4 shadow-lg dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown">
              <i class="ti tabler-user-check me-2"></i> {{ explode(' ', auth()->user()->name)[0] }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.1) !important;">
              <li><a class="dropdown-item text-white" href="{{ route('dashboard') }}"><i class="ti tabler-layout-dashboard me-2"></i> Dashboard</a></li>
              <li><hr class="dropdown-divider border-secondary opacity-20"></li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="dropdown-item text-danger"><i class="ti tabler-logout me-2"></i> Keluar</button>
                </form>
              </li>
            </ul>
          </div>
        @else
          @php
            $ijinkanRegister = \App\Models\Pengaturan::where('key', 'ijinkan_pembuatan_akun_mandiri')->value('value') === 'Ya';
          @endphp
          @if (\Illuminate\Support\Facades\Route::has('register') && $ijinkanRegister)
            <a href="{{ route('register') }}" class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-4 me-lg-2">
              <i class="ti tabler-user-plus me-1"></i> Daftar
            </a>
          @endif
          <a href="{{ route('login') }}" class="btn btn-sm btn-primary rounded-pill fw-bold px-4 shadow-lg">
            <i class="ti tabler-login me-2"></i> Masuk
          </a>
        @endif
      </div>
    </div>
    <div class="mobile-menu-overlay d-lg-none" data-bs-toggle="collapse" data-bs-target="#navbarContent"></div>
  </div>
</nav>
