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
    $siteSettings = \Illuminate\Support\Facades\Cache::remember('site_settings_global', 3600, fn() =>
        \App\Models\Pengaturan::whereIn('key', [
            'nama_lembaga', 'nama_sekolah',
            'deskripsi_lembaga', 'deskripsi_sekolah', 'site_description',
            'logo_url', 'logo_sekolah', 'logo',
            'wa_og_preview_enabled', 'google_font_family',
        ])->pluck('value', 'key')
    );

    $siteName = $siteSettings['nama_lembaga']
      ?? $siteSettings['nama_sekolah']
      ?? config('app.name', 'Portal Presensi');

    $siteDesc = $siteSettings['deskripsi_lembaga']
      ?? $siteSettings['deskripsi_sekolah']
      ?? $siteSettings['site_description']
      ?? ('Sistem Informasi dan Portal Presensi Digital Resmi ' . $siteName);

    $siteLogo = $siteSettings['logo_url']
      ?? $siteSettings['logo_sekolah']
      ?? $siteSettings['logo']
      ?? asset('assets/img/favicon/favicon.ico');

    if (!empty($siteLogo) && !filter_var($siteLogo, FILTER_VALIDATE_URL)) {
        $siteLogo = asset($siteLogo);
    }

    $ogPreviewEnabled = $siteSettings['wa_og_preview_enabled'] ?? 'Ya';
    $pageTitle = trim($__env->yieldContent('title'));
    if ($pageTitle) {
        $fullTitle = \Illuminate\Support\Str::contains($pageTitle, $siteName) ? $pageTitle : ($pageTitle . ' | ' . $siteName);
    } else {
        $fullTitle = 'Portal Presensi ' . $siteName;
    }

    // Decode &amp; menjadi & otomatis untuk kebersihan SEO Title & Meta Tags
    $fullTitle = html_entity_decode($fullTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $siteName  = html_entity_decode($siteName, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $siteDesc  = html_entity_decode($siteDesc, ENT_QUOTES | ENT_HTML5, 'UTF-8');
  @endphp
  <title>{{ $fullTitle }}</title>
  <meta name="description" content="{{ $siteDesc }}" />
  <meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : 'presensi, portal presensi, absensi digital' }}" />

  @if(($ogPreviewEnabled ?? 'Ya') === 'Ya')
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
  @endif

  <meta name="robots" content="noindex, nofollow" />
  <!-- Preload critical font assets -->
  <link rel="preload" href="{{ asset('assets/fonts/ProductSans-Regular.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('assets/fonts/ProductSans-Medium.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('assets/fonts/ProductSans-Bold.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('assets/fonts/TrajanPro-Regular.woff2') }}" as="font" type="font/woff2" crossorigin>
  @php
    $fontFamily = $siteSettings['google_font_family'] ?? 'Product Sans';
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
    $faviconLogoSekolah = $siteSettings['logo_sekolah'] ?? null;
    $faviconSetting = null;
    if ($faviconLogoSekolah) {
      $faviconSetting = (filter_var($faviconLogoSekolah, FILTER_VALIDATE_URL) || str_starts_with($faviconLogoSekolah, 'http://') || str_starts_with($faviconLogoSekolah, 'https://'))
        ? $faviconLogoSekolah
        : asset('uploads/logo/' . $faviconLogoSekolah);
    } elseif (!empty($siteSettings['logo_url'])) {
      $faviconSetting = $siteSettings['logo_url'];
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
  <style id="das-theme-vars">
    /* Light Mode Variables */
    :root, [data-bs-theme="light"] {
      --das-primary: {{ $themeVars['theme_light_primary'] ?? $themeVars['theme_primary'] ?? '#7367f0' }};
      --das-primary-soft: {{ $themeVars['theme_light_primary_soft'] ?? $themeVars['theme_primary_soft'] ?? 'rgba(115, 103, 240, 0.12)' }};
      --das-secondary: {{ $themeVars['theme_light_secondary'] ?? $themeVars['theme_secondary'] ?? '#8592a3' }};
      --das-secondary-soft: {{ $themeVars['theme_light_secondary_soft'] ?? $themeVars['theme_secondary_soft'] ?? 'rgba(133, 146, 163, 0.12)' }};
      --das-success: {{ $themeVars['theme_light_success'] ?? $themeVars['theme_success'] ?? '#28c76f' }};
      --das-success-soft: {{ $themeVars['theme_light_success_soft'] ?? $themeVars['theme_success_soft'] ?? 'rgba(40, 199, 111, 0.12)' }};
      --das-info: {{ $themeVars['theme_light_info'] ?? $themeVars['theme_info'] ?? '#00cfe8' }};
      --das-info-soft: {{ $themeVars['theme_light_info_soft'] ?? $themeVars['theme_info_soft'] ?? 'rgba(0, 207, 232, 0.12)' }};
      --das-warning: {{ $themeVars['theme_light_warning'] ?? $themeVars['theme_warning'] ?? '#ff9f43' }};
      --das-warning-soft: {{ $themeVars['theme_light_warning_soft'] ?? $themeVars['theme_warning_soft'] ?? 'rgba(255, 159, 67, 0.12)' }};
      --das-danger: {{ $themeVars['theme_light_danger'] ?? $themeVars['theme_danger'] ?? '#ea5455' }};
      --das-danger-soft: {{ $themeVars['theme_light_danger_soft'] ?? $themeVars['theme_danger_soft'] ?? 'rgba(234, 84, 85, 0.12)' }};
      --das-text-main: {{ $themeVars['theme_light_text_main'] ?? '#0f172a' }};
      --das-surface: {{ $themeVars['theme_light_surface'] ?? '#ffffff' }};
      --das-border: {{ $themeVars['theme_light_border'] ?? 'rgba(226, 232, 240, 0.8)' }};
      --das-sidebar-bg: {{ !empty($themeVars['theme_light_sidebar_gradient']) && $themeVars['theme_light_sidebar_gradient'] !== 'none' ? $themeVars['theme_light_sidebar_gradient'] : ($themeVars['theme_light_sidebar_bg'] ?? '#ffffff') }};
    }

    /* Dark Mode Variables */
    [data-bs-theme="dark"] {
      --das-primary: {{ $themeVars['theme_dark_primary'] ?? $themeVars['theme_primary'] ?? '#7367f0' }};
      --das-primary-soft: {{ $themeVars['theme_dark_primary_soft'] ?? $themeVars['theme_primary_soft'] ?? 'rgba(115, 103, 240, 0.12)' }};
      --das-secondary: {{ $themeVars['theme_dark_secondary'] ?? $themeVars['theme_secondary'] ?? '#a8aaae' }};
      --das-secondary-soft: {{ $themeVars['theme_dark_secondary_soft'] ?? $themeVars['theme_secondary_soft'] ?? 'rgba(168, 170, 174, 0.12)' }};
      --das-success: {{ $themeVars['theme_dark_success'] ?? $themeVars['theme_success'] ?? '#28c76f' }};
      --das-success-soft: {{ $themeVars['theme_dark_success_soft'] ?? $themeVars['theme_success_soft'] ?? 'rgba(40, 199, 111, 0.12)' }};
      --das-info: {{ $themeVars['theme_dark_info'] ?? $themeVars['theme_info'] ?? '#00cfe8' }};
      --das-info-soft: {{ $themeVars['theme_dark_info_soft'] ?? $themeVars['theme_info_soft'] ?? 'rgba(0, 207, 232, 0.12)' }};
      --das-warning: {{ $themeVars['theme_dark_warning'] ?? $themeVars['theme_warning'] ?? '#ff9f43' }};
      --das-warning-soft: {{ $themeVars['theme_dark_warning_soft'] ?? $themeVars['theme_warning_soft'] ?? 'rgba(255, 159, 67, 0.12)' }};
      --das-danger: {{ $themeVars['theme_dark_danger'] ?? $themeVars['theme_danger'] ?? '#ea5455' }};
      --das-danger-soft: {{ $themeVars['theme_dark_danger_soft'] ?? $themeVars['theme_danger_soft'] ?? 'rgba(234, 84, 85, 0.12)' }};
      --das-text-main: {{ $themeVars['theme_dark_text_main'] ?? $themeVars['theme_text_main'] ?? '#cbd5e1' }};
      --das-surface: {{ $themeVars['theme_dark_surface'] ?? $themeVars['theme_surface'] ?? 'rgba(15, 23, 42, 0.45)' }};
      --das-border: {{ $themeVars['theme_dark_border'] ?? $themeVars['theme_border'] ?? 'rgba(255, 255, 255, 0.07)' }};
      --das-sidebar-bg: {{ !empty($themeVars['theme_dark_sidebar_gradient']) && $themeVars['theme_dark_sidebar_gradient'] !== 'none' ? $themeVars['theme_dark_sidebar_gradient'] : ($themeVars['theme_dark_sidebar_bg'] ?? 'rgba(15, 23, 42, 0.75)') }};
    }

    #layout-menu.menu-vertical, .menu-vertical {
      background: var(--das-sidebar-bg) !important;
    }
    .menu-inner-shadow {
      display: none !important;
    }
  </style>

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
