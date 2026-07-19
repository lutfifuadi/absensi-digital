@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
  $configData = Helper::appClasses();
@endphp
@extends('layouts/commonMaster')

@php
  /* Display elements */
  $contentNavbar = $contentNavbar ?? true;
  $containerNav = $containerNav ?? 'container-xxl';
  $isNavbar = $isNavbar ?? true;
  $isMenu = $isMenu ?? true;
  $isFlex = $isFlex ?? false;
  $isFooter = $isFooter ?? true;
  $customizerHidden = $customizerHidden ?? '';

  /* HTML Classes */
  $navbarDetached = 'navbar-detached';
  $menuFixed = isset($configData['menuFixed']) ? $configData['menuFixed'] : '';
  if (isset($navbarType)) {
      $configData['navbarType'] = $navbarType;
  }
  $navbarType = isset($configData['navbarType']) ? $configData['navbarType'] : '';
  $footerFixed = isset($configData['footerFixed']) ? $configData['footerFixed'] : '';
  $menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '';

  /* Content classes */
  $container =
      isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
          ? 'container-xxl'
          : 'container-fluid';

@endphp

@section('layoutContent')
  <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
    <div class="layout-container">

      @if ($isMenu)
        @include('layouts/sections/menu/verticalMenu')
      @endif

      <!-- Layout page -->
      <div class="layout-page">

        {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
        <x-banner />

        <!-- BEGIN: Navbar-->
        @if ($isNavbar)
          @include('layouts/sections/navbar/navbar')
        @endif
        <!-- END: Navbar-->

        <!-- Content wrapper -->
        <div class="content-wrapper">

          <!-- Content -->
          @if ($isFlex)
            <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
            @else
              <div class="{{ $container }} flex-grow-1 container-p-y">
          @endif

          {{-- Global Update Notification --}}
          @if(auth()->check() && auth()->user()->role === 'super_admin')
            @php
              $updateVersion = \App\Models\Pengaturan::where('key', 'update_available_version')->value('value');
            @endphp
            @if($updateVersion)
              <div class="alert alert-warning alert-dismissible d-flex align-items-center mb-3 border-0 shadow-sm overflow-hidden py-1 px-3" role="alert" 
                   style="background: linear-gradient(135deg, #ff9f43 0%, #ff6b00 100%); position: relative; min-height: 42px;">
                {{-- Subtle pattern background --}}
                <div style="position: absolute; top: 0; right: 0; bottom: 0; left: 0; background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.1) 1px, transparent 0); background-size: 10px 10px; pointer-events: none;"></div>
                
                <span class="alert-icon me-2 bg-white bg-opacity-25 p-1 rounded-circle d-flex align-items-center justify-content-center shadow-sm pulse-update-animation" style="width: 26px; height: 26px;">
                  <i class="ti tabler-cloud-download text-white" style="font-size: 0.85rem;"></i>
                </span>
                
                <div class="d-flex flex-column flex-md-row align-items-md-center flex-grow-1">
                  <div class="text-white">
                    <span class="fw-bold d-block d-md-inline mb-0 mb-md-0" style="font-size: 0.85rem;">Versi Baru Tersedia ({{ $updateVersion }})!</span> 
                    <span class="opacity-75 ms-md-1" style="font-size: 0.75rem;">Perbarui sistem Anda untuk fitur terbaru.</span>
                  </div>
                  <div class="ms-md-auto mt-1 mt-md-0">
                    <a href="{{ route('admin.update.index') }}" class="btn btn-white btn-sm fw-bold px-2 py-0" style="background: #fff; color: #ff6b00; border-radius: 4px; border: none; box-shadow: 0 2px 4px 0 rgba(0,0,0,0.1); font-size: 0.7rem; line-height: 1.5;">
                      <i class="ti tabler-rocket me-1" style="font-size: 0.75rem;"></i> Perbarui
                    </a>
                  </div>
                </div>
                
                <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="alert" aria-label="Close" style="position: relative; top: 0; right: 0; padding: 0.5rem; transform: scale(0.6);"></button>
                
                <style>
                  @keyframes pulse-white-update {
                    0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
                    70% { box-shadow: 0 0 0 6px rgba(255, 255, 255, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
                  }
                  .pulse-update-animation {
                    animation: pulse-white-update 2s infinite;
                  }
                </style>
              </div>
            @endif
          @endif

          @yield('content')

        </div>
        <!-- / Content -->

        <!-- Footer -->
        @if ($isFooter)
          @include('layouts/sections/footer/footer')
        @endif
        <!-- / Footer -->
        <div class="content-backdrop fade"></div>
      </div>
      <!--/ Content wrapper -->
    </div>
    <!-- / Layout page -->

  @if ($isMenu)
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
  @endif
  <!-- Drag Target Area To SlideIn Menu On Small Screens -->
  <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->

  {{-- ── Impersonation Banner ──────────────────────────────────────────── --}}
  @if(session('impersonator_id') || session('impersonated_by'))
  <div id="impersonation-banner" style="
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      z-index: 99999;
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      border-top: 2px solid #ef4444;
      padding: 10px 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
      box-shadow: 0 -4px 20px rgba(239, 68, 68, 0.25);
      font-family: inherit;
  ">
      <span style="font-size: 13px; color: #991b1b;">
          <i class="ti tabler-user-check" style="color: #dc2626; margin-right: 5px;"></i>
          @if(session('impersonated_by'))
              Anda sedang login sebagai Siswa: <strong style="color: #991b1b;">{{ auth()->user()->name }}</strong>. Akun Admin Asli: <strong style="color: #991b1b;">{{ session('impersonator_name') }}</strong>
          @else
              Kamu sedang login sebagai user: <strong style="color: #991b1b;">{{ auth()->user()->name }}</strong>
              <span style="margin: 0 6px; color: #b91c1c;">·</span>
              <span class="badge" style="background:#f87171; color:#7f1d1d; font-size:11px; padding: 3px 8px; border-radius: 20px;">
                  {{ str_replace('_', ' ', ucfirst(auth()->user()->role)) }}
              </span>
          @endif
      </span>
      @if(session('impersonated_by'))
      <form action="{{ route('impersonate.leave') }}" method="POST" style="margin: 0;">
          @csrf
          <button type="submit"
             style="
                 background: #ef4444;
                 color: #fff;
                 border: none;
                 padding: 6px 18px;
                 border-radius: 6px;
                 font-size: 13px;
                 font-weight: 600;
                 text-decoration: none;
                 display: inline-flex;
                 align-items: center;
                 gap: 6px;
                 transition: background 0.2s;
                 cursor: pointer;
             "
             onmouseover="this.style.background='#dc2626'"
             onmouseout="this.style.background='#ef4444'">
              <i class="ti tabler-arrow-back-up"></i>
              Kembali ke Admin
          </button>
      </form>
      @else
      <a href="{{ route('admin.impersonate.revert') }}"
         style="
             background: #7367f0;
             color: #fff;
             border: none;
             padding: 6px 18px;
             border-radius: 6px;
             font-size: 13px;
             font-weight: 600;
             text-decoration: none;
             display: inline-flex;
             align-items: center;
             gap: 6px;
             transition: background 0.2s;
         "
         onmouseover="this.style.background='#5e50ee'"
         onmouseout="this.style.background='#7367f0'">
          <i class="ti tabler-arrow-back-up"></i>
          Kembali ke Admin
      </a>
      @endif
  </div>
  @endif
  {{-- ────────────────────────────────────────────────────────────────────── --}}

  {{-- ── Floating AI Chat Widget ─────────────────────────────────────────── --}}
  @auth
    @livewire('admin.floating-chat')
  @endauth
  {{-- ────────────────────────────────────────────────────────────────────── --}}

@endsection
