<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}" />

@vite(['resources/assets/vendor/fonts/iconify/iconify.css'])

@if ($configData['hasCustomizer'])
  @vite(['resources/assets/vendor/libs/pickr/pickr-themes.scss'])
@endif

<!-- Vendor Styles -->
@yield('vendor-style')
@vite(['resources/assets/vendor/libs/node-waves/node-waves.scss'])

<!-- Core CSS -->
@vite(['resources/assets/vendor/scss/core.scss', 'resources/assets/css/demo.css', 'resources/assets/vendor/scss/pages/front-page.scss'])

<!-- Page Styles -->
@yield('page-style')
