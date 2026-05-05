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
  @if(session('impersonator_id'))
  <div id="impersonation-banner" style="
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      z-index: 9999;
      background: linear-gradient(135deg, #fef3cd 0%, #fde68a 100%);
      border-top: 2px solid #f59e0b;
      padding: 10px 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
      box-shadow: 0 -4px 20px rgba(245, 158, 11, 0.25);
      font-family: inherit;
  ">
      <span style="font-size: 13px; color: #92400e;">
          <i class="ti tabler-user-check" style="color: #d97706; margin-right: 5px;"></i>
          Kamu sedang login sebagai user:
          <strong style="color: #92400e;">{{ auth()->user()->name }}</strong>
          <span style="margin: 0 6px; color: #b45309;">·</span>
          <span class="badge" style="background:#fbbf24; color:#78350f; font-size:11px; padding: 3px 8px; border-radius: 20px;">
              {{ str_replace('_', ' ', ucfirst(auth()->user()->role)) }}
          </span>
      </span>
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
  </div>
  @endif
  {{-- ────────────────────────────────────────────────────────────────────── --}}

@endsection
