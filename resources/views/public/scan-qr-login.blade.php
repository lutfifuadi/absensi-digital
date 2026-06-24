<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scan QR Absensi — Guru Piket</title>
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --bg: #080c14;
      --surface: #0f1623;
      --surface2: #111827;
      --border: rgba(255, 255, 255, 0.07);
      --primary: #7367f0;
      --success: #28c76f;
      --danger: #ea5455;
      --text: #e2e8f0;
      --muted: #64748b;
    }

    body {
      background: var(--bg);
      font-family: 'Product Sans', sans-serif;
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* ── LAYOUT (Matching Login UI) ─────────────────────── */
    .authentication-wrapper {
      width: 100%;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: radial-gradient(circle at top right, rgba(115, 103, 240, 0.18), transparent 40%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
      padding: 1.5rem;
      position: relative;
    }

    /* Top-left Logo Strip */
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

    .auth-form-wrapper {
      width: 100%;
      max-width: 420px;
      padding: 2.5rem 2rem;
      background: rgba(15, 22, 35, 0.92);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 4px;
      box-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
    }

    .brand-icon {
      width: 64px;
      height: 64px;
      background: linear-gradient(135deg, var(--primary), #a78bfa);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      font-size: 1.75rem;
      box-shadow: 0 0 30px rgba(115, 103, 240, 0.4);
    }

    .form-heading {
      text-align: center;
      margin-bottom: 2rem;
    }

    .form-heading h4 {
      color: #fff !important;
      font-weight: 800;
      font-size: 1.5rem;
      margin-bottom: 0.35rem;
    }

    .form-heading p {
      color: var(--muted);
      font-size: 0.9rem;
      margin: 0;
    }

    .date-pill {
      display: inline-block;
      background: rgba(115, 103, 240, 0.1);
      border: 1px solid rgba(115, 103, 240, 0.2);
      color: #a78bfa;
      border-radius: 99px;
      padding: 4px 14px;
      font-size: 0.75rem;
      font-weight: 600;
      margin: 0.5rem auto 0;
    }

    .form-label {
      display: block;
      color: var(--text);
      font-weight: 600;
      font-size: 0.85rem;
      margin-bottom: 0.45rem;
    }

    .form-control {
      width: 100%;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: 0.75rem 1rem;
      font-size: 1rem;
      font-family: inherit;
      color: var(--text);
      transition: border-color 0.2s, box-shadow 0.2s;
      outline: none;
    }

    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.15);
    }

    .btn-signin {
      width: 100%;
      padding: 0.85rem;
      background: var(--primary);
      border: none;
      border-radius: 4px;
      font-family: inherit;
      font-size: 1rem;
      font-weight: 700;
      color: #fff;
      cursor: pointer;
      box-shadow: 0 4px 20px rgba(115, 103, 240, 0.4);
      transition: all 0.2s ease;
      margin-top: 1rem;
    }

    .btn-signin:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 26px rgba(115, 103, 240, 0.55);
      background: #6357d9;
    }

    .alert {
      padding: 0.75rem 1rem;
      border-radius: 4px;
      font-size: 0.85rem;
      margin-bottom: 1.5rem;
      text-align: center;
    }

    .alert-danger {
      background: rgba(234, 84, 85, 0.1);
      border: 1px solid rgba(234, 84, 85, 0.2);
      color: #ff7b7b;
    }

    .alert-success {
      background: rgba(40, 199, 111, 0.1);
      border: 1px solid rgba(40, 199, 111, 0.2);
      color: #28c76f;
    }

    .footer-note {
      text-align: center;
      font-size: 0.75rem;
      color: var(--muted);
      margin-top: 1.5rem;
    }
  </style>
</head>

<body>
  <div class="authentication-wrapper">
    <!-- Top-left Logo (optional, adding to match logic) -->
    <a href="{{ url('/') }}" class="auth-cover-brand">
      <span class="app-brand-logo">@include('_partials.macros', ['width' => 22, 'height' => 22])</span>
      <span class="app-brand-text">{{ config('variables.templateName') }}</span>
    </a>

    <div class="auth-form-wrapper">
      <div class="brand-icon">📷</div>
      
      <div class="form-heading">
        <h4>Scan QR Absensi</h4>
        <p>Guru Piket — Akses Khusus</p>
        <div class="date-pill">{{ now()->isoFormat('dddd, D MMMM Y') }}</div>
      </div>

      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if ($errors->any())
        @foreach ($errors->all() as $err)
          <div class="alert alert-danger">{{ $err }}</div>
        @endforeach
      @endif

      <form action="{{ route('public.scan-qr.auth') }}" method="POST">
        @csrf
        <div class="mb-4">
          <label for="password" class="form-label">Password Scan QR</label>
          <input id="password" type="password" name="password"
            class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
            placeholder="Masukkan password..." autofocus autocomplete="current-password">
          @error('password')
            <div class="invalid-feedback" style="color:var(--danger); font-size:0.75rem; margin-top:0.4rem;">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" class="btn-signin">🚀 Buka Halaman Scan</button>
      </form>

      <p class="footer-note">Password diatur oleh admin sekolah.</p>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const cookieName = 'device_uuid';

      function getCookie(name) {
        let value = "; " + document.cookie;
        let parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
      }

      if (!getCookie(cookieName)) {
        const uuid = 'DEV-' + Math.random().toString(36).substr(2, 9).toUpperCase() + '-' + Date.now().toString(36).toUpperCase();
        document.cookie = cookieName + "=" + uuid + "; path=/; max-age=" + (60 * 60 * 24 * 365 * 10);
        window.location.reload();
      }
    });
  </script>
</body>
</html>
