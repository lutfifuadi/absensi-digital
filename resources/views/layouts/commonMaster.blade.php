<!DOCTYPE html>
@php
  use Illuminate\Support\Str;
  use App\Helpers\Helpers;

  $menuFixed =
      $configData['layout'] === 'vertical'
          ? $menuFixed ?? ''
          : ($configData['layout'] === 'front'
              ? ''
              : $configData['headerType']);
  $navbarType =
      $configData['layout'] === 'vertical'
          ? $configData['navbarType']
          : ($configData['layout'] === 'front'
              ? 'layout-navbar-fixed'
              : '');
  $isFront = ($isFront ?? '') == true ? 'Front' : '';
  $contentLayout = isset($container) ? ($container === 'container-xxl' ? 'layout-compact' : 'layout-wide') : '';

  // Get skin name from configData - only applies to admin layouts
  $isAdminLayout = !\Illuminate\Support\Str::contains($configData['layout'] ?? '', 'front');
  $skinName = $isAdminLayout ? $configData['skinName'] ?? 'default' : 'default';

  // Get semiDark value from configData - only applies to admin layouts
  $semiDarkEnabled = $isAdminLayout && filter_var($configData['semiDark'] ?? false, FILTER_VALIDATE_BOOLEAN);

  // Generate primary color CSS if color is set
  $primaryColorCSS = '';
  if (isset($configData['color']) && $configData['color']) {
      $primaryColorCSS = Helpers::generatePrimaryColorCSS($configData['color']);
  }

@endphp

<html lang="{{ session()->get('locale') ?? app()->getLocale() }}"
  class="{{ $navbarType ?? '' }} {{ $contentLayout ?? '' }} {{ $menuFixed ?? '' }} {{ $menuCollapsed ?? '' }} {{ $footerFixed ?? '' }} {{ $customizerHidden ?? '' }}"
  dir="{{ $configData['textDirection'] }}" data-skin="{{ $skinName }}" data-assets-path="{{ asset('/assets') . '/' }}"
  data-base-url="{{ url('/') }}" data-framework="laravel" data-template="{{ $configData['layout'] }}-menu-template"
  data-bs-theme="{{ $configData['theme'] }}" @if ($isAdminLayout && $semiDarkEnabled) data-semidark-menu="true" @endif>

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  @php
    $siteName = \App\Models\Pengaturan::where('key', 'nama_lembaga')->value('value')
      ?? \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value')
      ?? config('app.name', 'Portal Presensi');

    $siteDesc = \App\Models\Pengaturan::where('key', 'deskripsi_lembaga')->value('value')
      ?? \App\Models\Pengaturan::where('key', 'deskripsi_sekolah')->value('value')
      ?? \App\Models\Pengaturan::where('key', 'site_description')->value('value')
      ?? ('Sistem Informasi dan Portal Presensi Digital Resmi ' . $siteName);

    $siteLogo = \App\Models\Pengaturan::where('key', 'logo_url')->value('value')
      ?? \App\Models\Pengaturan::where('key', 'logo_sekolah')->value('value')
      ?? \App\Models\Pengaturan::where('key', 'logo')->value('value')
      ?? asset('assets/img/favicon/favicon.ico');

    if (!empty($siteLogo) && !filter_var($siteLogo, FILTER_VALIDATE_URL)) {
        $siteLogo = asset($siteLogo);
    }

    $pageTitle = trim($__env->yieldContent('title'));
    if ($pageTitle) {
        $fullTitle = \Illuminate\Support\Str::contains($pageTitle, $siteName) ? $pageTitle : ($pageTitle . ' | ' . $siteName);
    } else {
        $fullTitle = 'Portal Presensi ' . $siteName;
    }
  @endphp
  <title>{{ $fullTitle }}</title>
  <meta name="description" content="{{ $siteDesc }}" />
  <meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : 'presensi, portal presensi, absensi digital' }}" />

  <!-- Open Graph Meta Tags (WhatsApp, Facebook, Social Share) -->
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="{{ $siteName }}" />
  <meta property="og:title" content="{{ $fullTitle }}" />
  <meta property="og:description" content="{{ $siteDesc }}" />
  <meta property="og:image" content="{{ $siteLogo }}" />
  <meta property="og:url" content="{{ url()->current() }}" />

  <!-- Twitter Card Meta Tags -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="{{ $fullTitle }}" />
  <meta name="twitter:description" content="{{ $siteDesc }}" />
  <meta name="twitter:image" content="{{ $siteLogo }}" />

  <meta name="robots" content="noindex, nofollow" />
  <!-- Preload critical font assets -->
  <link rel="preload" href="{{ asset('assets/fonts/ProductSans-Regular.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('assets/fonts/ProductSans-Medium.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('assets/fonts/ProductSans-Bold.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('assets/fonts/TrajanPro-Regular.woff2') }}" as="font" type="font/woff2" crossorigin>
  @php
    $fontFamily = \App\Models\Pengaturan::where('key', 'google_font_family')->value('value') ?? 'Product Sans';
  @endphp
  @if($fontFamily !== 'Product Sans')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ urlencode($fontFamily) }}:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  @endif
  <style>
    body {
      font-family: '{{ $fontFamily }}', 'Product Sans', sans-serif !important;
    }
  </style>
  <!-- laravel CRUD token -->
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <!-- Canonical SEO -->
  <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
  <!-- Favicon -->
  @php
    $faviconSetting = \App\Models\Pengaturan::where('key', 'logo_url')->value('value');
    if (!$faviconSetting) {
      $faviconSetting = \App\Models\Pengaturan::where('key', 'logo_sekolah')->value('value');
      if ($faviconSetting) {
        $faviconSetting = asset('uploads/logo/' . $faviconSetting);
      }
    }
  @endphp
  @if($faviconSetting)
    <link rel="icon" type="image/png" href="{{ $faviconSetting }}" />
  @else
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
  @endif

  <!-- PWA Manifest -->
  <link rel="manifest" href="{{ asset('manifest.json') }}?v={{ file_exists(public_path('manifest.json')) ? filemtime(public_path('manifest.json')) : '1' }}">
  <meta name="theme-color" content="#0f3460">
  <link rel="apple-touch-icon" href="{{ asset('assets/img/icons/icon-192x192.png') }}">

  <!-- Include Styles -->
  <!-- $isFront is used to append the front layout styles only on the front layout otherwise the variable will be blank -->
  @include('layouts/sections/styles' . $isFront)

  @if (
      $primaryColorCSS &&
          (config('custom.custom.primaryColor') ||
              isset($_COOKIE['admin-primaryColor']) ||
              isset($_COOKIE['front-primaryColor'])))
    <!-- Primary Color Style -->
    <style id="primary-color-style">
      {!! $primaryColorCSS !!}
    </style>
  @endif

  @php
    $themeVars = \Illuminate\Support\Facades\Cache::rememberForever('das_theme_vars', function () {
        return \App\Models\Pengaturan::where('group', 'theme')->pluck('value', 'key')->toArray();
    });
  @endphp
  @if(!empty($themeVars))
    <style id="das-theme-vars">
      :root {
        @isset($themeVars['theme_primary']) --das-primary: {{ $themeVars['theme_primary'] }}; @endisset
        @isset($themeVars['theme_primary_soft']) --das-primary-soft: {{ $themeVars['theme_primary_soft'] }}; @endisset
        @isset($themeVars['theme_secondary']) --das-secondary: {{ $themeVars['theme_secondary'] }}; @endisset
        @isset($themeVars['theme_secondary_soft']) --das-secondary-soft: {{ $themeVars['theme_secondary_soft'] }}; @endisset
        @isset($themeVars['theme_success']) --das-success: {{ $themeVars['theme_success'] }}; @endisset
        @isset($themeVars['theme_success_soft']) --das-success-soft: {{ $themeVars['theme_success_soft'] }}; @endisset
        @isset($themeVars['theme_info']) --das-info: {{ $themeVars['theme_info'] }}; @endisset
        @isset($themeVars['theme_info_soft']) --das-info-soft: {{ $themeVars['theme_info_soft'] }}; @endisset
        @isset($themeVars['theme_warning']) --das-warning: {{ $themeVars['theme_warning'] }}; @endisset
        @isset($themeVars['theme_warning_soft']) --das-warning-soft: {{ $themeVars['theme_warning_soft'] }}; @endisset
        @isset($themeVars['theme_danger']) --das-danger: {{ $themeVars['theme_danger'] }}; @endisset
        @isset($themeVars['theme_danger_soft']) --das-danger-soft: {{ $themeVars['theme_danger_soft'] }}; @endisset
        @isset($themeVars['theme_text_main']) --das-text-main: {{ $themeVars['theme_text_main'] }}; @endisset
        @isset($themeVars['theme_surface']) --das-surface: {{ $themeVars['theme_surface'] }}; @endisset
        @isset($themeVars['theme_border']) --das-border: {{ $themeVars['theme_border'] }}; @endisset
      }
    </style>
  @endif

  <!-- Include Scripts for customizer, helper, analytics, config -->
  <!-- $isFront is used to append the front layout scriptsIncludes only on the front layout otherwise the variable will be blank -->
  @include('layouts/sections/scriptsIncludes' . $isFront)
</head>

<body>
  <!-- Layout Content -->
  @yield('layoutContent')
  <!--/ Layout Content -->

  {{-- remove while creating package --}}
  {{-- remove while creating package end --}}

  <!-- Include Scripts -->
  <!-- $isFront is used to append the front layout scripts only on the front layout otherwise the variable will be blank -->
  @include('layouts/sections/scripts' . $isFront)

  <!-- PWA Service Worker Registration -->
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').then(function(registration) {
          console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }, function(err) {
          console.log('ServiceWorker registration failed: ', err);
        });
      });
    }
  </script>
</body>

</html>
