<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scan Absensi Kegiatan — Akses Publik</title>
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
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
      --primary: #10b981;
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

    /* ── LAYOUT ─────────────────────────────────────────── */
    .authentication-wrapper {
      width: 100%;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.18), transparent 40%),
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
      box-shadow: 0 0 16px rgba(16, 185, 129, 0.45);
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
      background: linear-gradient(135deg, var(--primary), #34d399);
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      font-size: 1.75rem;
      box-shadow: 0 0 30px rgba(16, 185, 129, 0.4);
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
      background: rgba(16, 185, 129, 0.1);
      border: 1px solid rgba(16, 185, 129, 0.2);
      color: #34d399;
      border-radius: 5px;
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
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
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
      box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
      transition: all 0.2s ease;
      margin-top: 1rem;
    }

    .btn-signin:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 26px rgba(16, 185, 129, 0.55);
      background: #0ea472;
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

    /* ── Mobile: form lebih lega ──────────────────────── */
    @media (max-width: 480px) {
      .authentication-wrapper {
        padding: 1rem;
      }

      .auth-form-wrapper {
        padding: 2rem 1.25rem;
      }
    }

    @media (max-width: 380px) {
      .auth-form-wrapper {
        padding: 1.5rem 1rem;
      }
    }
  </style>
</head>

<body>
  <div class="authentication-wrapper">
    <!-- Top-left Logo -->
    <a href="{{ url('/') }}" class="auth-cover-brand">
      <span class="app-brand-logo">@include('_partials.macros', ['width' => 22, 'height' => 22])</span>
      <span class="app-brand-text">{{ config('variables.templateName') }}</span>
    </a>

    <div class="auth-form-wrapper">
      <div class="brand-icon">📅</div>

      <div class="form-heading">
        <h4>Scan Absensi Kegiatan</h4>
        <p>Absensi Kegiatan — Akses Publik</p>
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

      <form action="{{ route('public.kegiatan.auth') }}" method="POST">
        @csrf
        <div class="mb-4" style="margin-bottom: 1.5rem;">
          <label for="password" class="form-label">Password Scan</label>
          <div class="password-input-wrapper" style="position: relative; display: flex; align-items: center;">
            <input id="password" type="password" name="password"
              class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
              placeholder="Masukkan password scan..." autofocus autocomplete="current-password"
              style="padding-right: 2.75rem; width: 100%;">
            <button type="button" id="togglePasswordBtn" 
              style="position: absolute; right: 12px; background: none; border: none; color: var(--muted); cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px; outline: none; transition: color 0.2s;" 
              title="Lihat password">
              <!-- Eye SVG -->
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
              </svg>
              <!-- Eye Off SVG -->
              <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none; display: none;">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M3 3l18 18" />
                <path d="M19 19a12 12 0 0 1 -18 -7a12 12 0 0 1 18 0" />
                <path d="M14 14a2 2 0 1 1 -4 -4" />
              </svg>
            </button>
          </div>
          @error('password')
            <div class="invalid-feedback" style="color:var(--danger); font-size:0.75rem; margin-top:0.4rem;">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" class="btn-signin">📅 Buka Halaman Scan</button>
      </form>

      <p class="footer-note">Password diatur oleh admin sekolah.</p>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const togglePasswordBtn = document.getElementById('togglePasswordBtn');
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      const eyeOffIcon = document.getElementById('eyeOffIcon');

      togglePasswordBtn.addEventListener('click', function() {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        
        if (isPassword) {
          eyeIcon.style.display = 'none';
          eyeOffIcon.style.display = 'block';
          togglePasswordBtn.setAttribute('title', 'Sembunyikan password');
        } else {
          eyeIcon.style.display = 'block';
          eyeOffIcon.style.display = 'none';
          togglePasswordBtn.setAttribute('title', 'Lihat password');
        }
      });

      // Hover effect
      togglePasswordBtn.addEventListener('mouseenter', () => {
        togglePasswordBtn.style.color = '#fff';
      });
      togglePasswordBtn.addEventListener('mouseleave', () => {
        togglePasswordBtn.style.color = 'var(--muted)';
      });
    });
  </script>
</body>
</html>
