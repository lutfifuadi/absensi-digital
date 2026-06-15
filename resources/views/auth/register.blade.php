@php
  use Illuminate\Support\Facades\Route;
  $configData = Helper::appClasses();
  $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Register')

@section('page-style')
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
    }

    :root {
      --bg: #080c14;
      --surface: #0f1623;
      --surface2: #111827;
      --border: rgba(255, 255, 255, 0.07);
      --primary: #7367f0;
      --success: #28c76f;
      --text: #e2e8f0;
      --muted: #64748b;
    }

    body,
    .authentication-wrapper {
      background: var(--bg) !important;
      font-family: 'Product Sans', sans-serif !important;
      color: var(--text) !important;
    }

    /* ── LAYOUT ─────────────────────────────────────────── */
    .authentication-wrapper.authentication-cover {
      min-height: 100vh;
      display: flex;
      align-items: stretch;
    }

    .authentication-inner {
      width: 100%;
      min-height: 100vh;
    }

    .auth-right-panel {
      background: radial-gradient(circle at top right, rgba(115, 103, 240, 0.18), transparent 40%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1.5rem;
      width: 100%;
      min-height: 100vh;
    }

    .auth-form-wrapper {
      width: clamp(320px, 40%, 420px);
      max-width: 420px;
      min-width: 320px;
      padding: 2rem;
      background: rgba(15, 22, 35, 0.92);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 4px;
      box-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
    }

    .form-heading {
      margin-bottom: 2rem;
    }

    .form-heading h4 {
      color: #fff !important;
      font-weight: 800 !important;
      font-size: 1.75rem !important;
      margin-bottom: 0.35rem !important;
    }

    .form-heading p {
      color: var(--muted) !important;
      font-size: 0.95rem !important;
      margin: 0 !important;
    }

    .form-label {
      color: var(--text) !important;
      font-weight: 600 !important;
      font-size: 0.85rem !important;
      margin-bottom: 0.45rem !important;
    }

    .form-control {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid var(--border) !important;
      color: var(--text) !important;
      border-radius: 4px !important;
      padding: 0.65rem 0.9rem !important;
      font-size: 0.9rem !important;
      transition: border-color 0.2s, box-shadow 0.2s !important;
    }

    .form-control:focus {
      background: rgba(255, 255, 255, 0.06) !important;
      border-color: var(--primary) !important;
      box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.15) !important;
      outline: none !important;
    }

    .form-control::placeholder {
      color: var(--muted) !important;
    }

    .input-group.input-group-merge {
      display: flex !important;
      width: 100% !important;
      align-items: stretch !important;
    }

    .input-group-merge .form-control {
      flex: 1 1 auto !important;
      width: 1% !important;
      min-width: 0 !important;
      border-right: none !important;
      border-radius: 4px 0 0 4px !important;
      height: 44px !important;
      margin-bottom: 0 !important;
    }

    .input-group-merge .input-group-text {
      width: 44px !important;
      min-width: 44px !important;
      padding: 0 !important;
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid var(--border) !important;
      border-left: none !important;
      border-radius: 0 4px 4px 0 !important;
      color: var(--muted) !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      height: 44px !important;
    }

    .input-group-merge .input-group-text .icon-base {
      font-size: 1rem !important;
    }

    .form-check-input {
      background-color: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid var(--border) !important;
    }

    .form-check-input:checked {
      background-color: var(--primary) !important;
      border-color: var(--primary) !important;
    }

    .form-check-label {
      color: var(--muted) !important;
      font-size: 0.85rem !important;
    }

    .forgot-link {
      color: var(--primary) !important;
      font-size: 0.85rem !important;
      font-weight: 600 !important;
      text-decoration: none !important;
    }

    .forgot-link:hover {
      color: #a99bf5 !important;
    }

    .btn-signin {
      background: var(--primary) !important;
      border: none !important;
      border-radius: 4px !important;
      padding: 0.8rem !important;
      font-weight: 700 !important;
      font-size: 0.95rem !important;
      color: #fff !important;
      letter-spacing: 0.3px !important;
      box-shadow: 0 4px 20px rgba(115, 103, 240, 0.4) !important;
      transition: all 0.2s ease !important;
      width: 100% !important;
    }

    .btn-signin:hover {
      transform: translateY(-1px) !important;
      box-shadow: 0 6px 26px rgba(115, 103, 240, 0.55) !important;
      background: #6357d9 !important;
    }

    .alert-success {
      background: rgba(40, 199, 111, 0.1) !important;
      border: 1px solid rgba(40, 199, 111, 0.2) !important;
      color: #28c76f !important;
      border-radius: 4px !important;
      font-size: 0.88rem !important;
    }

    .invalid-feedback span {
      font-size: 0.8rem;
    }

    /* Top logo strip */
    .auth-cover-brand {
      position: absolute;
      top: 1.25rem;
      left: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.6rem;
      text-decoration: none !important;
      z-index: 10;
    }

    .auth-cover-brand .app-brand-logo {
      width: 36px;
      height: 36px;
      background: var(--primary) !important;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 0 16px rgba(115, 103, 240, 0.45);
    }

    .auth-cover-brand .app-brand-text {
      color: #fff !important;
      font-weight: 800;
      font-size: 1rem;
    }
  </style>
@endsection

@php
  $logoSrc = null;
  $logoUrl = \App\Models\Pengaturan::where('key', 'logo_url')->value('value');
  if ($logoUrl) {
    $logoSrc = $logoUrl;
  } else {
    $logoLocal = \App\Models\Pengaturan::where('key', 'logo_sekolah')->value('value');
    if ($logoLocal) {
      $logoSrc = asset('storage/' . $logoLocal);
    }
  }
@endphp

@section('content')
  <div class="authentication-wrapper authentication-cover" style="position:relative;">
    <!-- Top-left Logo -->
    <a href="{{ url('/') }}" class="auth-cover-brand">
      @if($logoSrc)
        <img src="{{ $logoSrc }}" alt="Logo" style="height:22px;object-fit:contain;">
      @else
        <span class="app-brand-logo">@include('_partials.macros', ['width' => 22, 'height' => 22])</span>
      @endif
      <span class="app-brand-text">{{ config('variables.templateName') }}</span>
    </a>

    <div class="authentication-inner row m-0" style="min-height:100vh;">

      <div class="col-12 auth-right-panel">
        <div class="auth-form-wrapper">
          <div class="form-heading">
            <h4>Daftar Akun Baru 🚀</h4>
            <p>Mulai perjalanan Anda bersama kami</p>
          </div>

          <form id="formAuthentication" action="{{ route('register') }}" method="POST">
            @csrf
            <div class="mb-4">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username"
                placeholder="johndoe" autofocus value="{{ old('username') }}" />
              @error('username')
                <span class="invalid-feedback" role="alert"><span class="fw-medium">{{ $message }}</span></span>
              @enderror
            </div>

            <div class="mb-4">
              <label for="name" class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                placeholder="Nama Lengkap" value="{{ old('name') }}" />
              @error('name')
                <span class="invalid-feedback" role="alert"><span class="fw-medium">{{ $message }}</span></span>
              @enderror
            </div>

            <div class="mb-4">
              <label for="email" class="form-label">Email</label>
              <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                placeholder="contoh@sekolah.sch.id" value="{{ old('email') }}" />
              @error('email')
                <span class="invalid-feedback" role="alert"><span class="fw-medium">{{ $message }}</span></span>
              @enderror
            </div>

            <div class="mb-4 form-password-toggle">
              <label class="form-label" for="password">Password</label>
              <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror"
                  name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password" />
                <span class="input-group-text cursor-pointer">
                  <i class="icon-base ti tabler-eye-off"></i>
                </span>
              </div>
              @error('password')
                <span class="invalid-feedback" role="alert"><span class="fw-medium">{{ $message }}</span></span>
              @enderror
            </div>

            <div class="mb-4 form-password-toggle">
              <label class="form-label" for="password-confirm">Konfirmasi Password</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password-confirm" class="form-control" name="password_confirmation"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password" />
                <span class="input-group-text cursor-pointer">
                  <i class="icon-base ti tabler-eye-off"></i>
                </span>
              </div>
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
              <div class="mb-4">
                <div class="form-check @error('terms') is-invalid @enderror">
                  <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms"
                    name="terms" />
                  <label class="form-check-label" for="terms">
                    Saya setuju dengan
                    <a href="{{ route('policy.show') }}" target="_blank" class="forgot-link">kebijakan privasi</a> &
                    <a href="{{ route('terms.show') }}" target="_blank" class="forgot-link">ketentuan</a>
                  </label>
                </div>
                @error('terms')
                  <span class="invalid-feedback" role="alert"><span class="fw-medium">{{ $message }}</span></span>
                @enderror
              </div>
            @endif

            <button class="btn btn-signin" type="submit">Daftar</button>
          </form>

          <p class="text-center mt-4">
            <span class="text-muted">Sudah punya akun?</span>
            @if (Route::has('login'))
              <a href="{{ route('login') }}" class="forgot-link">
                Masuk sekarang
              </a>
            @endif
          </p>
        </div>
      </div>

    </div>
  </div>
@endsection