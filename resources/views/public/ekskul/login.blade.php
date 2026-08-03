<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Akses Pembina — Scan Presensi Ekskul</title>
  
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
  
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #080c14;
      --surface: #0f1623;
      --border: rgba(255, 255, 255, 0.08);
      --primary: #10b981;
      --primary-soft: rgba(16, 185, 129, 0.15);
      --danger: #ea5455;
      --text: #e2e8f0;
      --muted: #64748b;
      --font-family: 'Product Sans', sans-serif;
    }
    html, body {
      background: var(--bg);
      font-family: var(--font-family);
      color: var(--text);
      height: 100vh;
      height: 100dvh;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .auth-wrapper {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: radial-gradient(circle at center, rgba(16, 185, 129, 0.12) 0%, transparent 60%),
                  radial-gradient(circle at top right, rgba(0, 207, 232, 0.08) 0%, transparent 40%);
      padding: 1.25rem;
      position: relative;
    }
    .auth-card {
      width: 100%;
      max-width: 380px;
      padding: 2rem 1.75rem;
      background: rgba(15, 22, 35, 0.95);
      backdrop-filter: blur(16px);
      border: 1px solid var(--border);
      border-radius: 5px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
      text-align: center;
    }
    .icon-box {
      width: 56px;
      height: 56px;
      margin: 0 auto 1.25rem auto;
      background: linear-gradient(135deg, var(--primary), #059669);
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
    }
    .icon-box svg { width: 28px; height: 28px; }

    h2 { font-size: 1.2rem; font-weight: 800; color: white; margin-bottom: 0.35rem; letter-spacing: -0.3px; }
    p.sub { font-size: 0.8rem; color: var(--muted); margin-bottom: 1.5rem; line-height: 1.4; }
    .form-group { margin-bottom: 1.25rem; text-align: left; }
    label { display: block; font-size: 0.68rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.5rem; }
    
    .input-box {
      position: relative;
      display: flex;
      align-items: center;
    }
    .input-box .input-icon-left {
      position: absolute; left: 0.85rem;
      color: #64748b; display: flex; align-items: center; justify-content: center;
      pointer-events: none;
    }
    .input-box .input-icon-left svg { width: 18px; height: 18px; }

    input[type="password"], input[type="text"] {
      width: 100%;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--border);
      border-radius: 5px;
      padding: 0.65rem 2.6rem 0.65rem 2.6rem;
      color: white;
      font-size: 0.95rem;
      font-family: var(--font-family);
      outline: none;
      transition: all 0.2s;
    }
    input[type="password"]:focus, input[type="text"]:focus {
      border-color: var(--primary);
      background: rgba(255, 255, 255, 0.07);
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .btn-eye-toggle {
      position: absolute; right: 0.6rem;
      background: transparent; border: none;
      color: #94a3b8; cursor: pointer;
      padding: 0.3rem 0.4rem; border-radius: 4px;
      display: flex; align-items: center; justify-content: center;
      transition: color 0.2s;
    }
    .btn-eye-toggle:hover { color: white; }
    .btn-eye-toggle svg { width: 18px; height: 18px; }
    
    .btn-submit {
      width: 100%;
      background: linear-gradient(135deg, var(--primary), #059669);
      color: white;
      border: none;
      padding: 0.65rem 1.25rem;
      border-radius: 5px;
      font-size: 0.88rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
      transition: all 0.2s;
    }
    .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4); }
    .btn-submit:active { transform: translateY(0); }
    .btn-submit svg { width: 18px; height: 18px; }

    .alert-error {
      background: rgba(234, 84, 85, 0.12);
      border: 1px solid rgba(234, 84, 85, 0.3);
      color: var(--danger);
      padding: 0.6rem 0.85rem;
      border-radius: 5px;
      font-size: 0.78rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      text-align: left;
    }
    .alert-error svg { width: 18px; height: 18px; flex-shrink: 0; }
  </style>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="icon-box">
      <!-- Camera Icon SVG -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2"></path><path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path></svg>
    </div>

    <h2>Scan Presensi Pembina</h2>
    <p class="sub">Masukkan Passcode PIN sekolah untuk membuka scanner kamera presensi ekskul.</p>

    @if ($errors->any())
      <div class="alert-error">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    <form action="{{ route('public.ekskul.scan.auth') }}" method="POST">
      @csrf
      <div class="form-group">
        <label for="password">Passcode PIN Scanner</label>
        <div class="input-box">
          <span class="input-icon-left">
            <!-- Lock Icon SVG -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z"></path><path d="M8 11v-4a4 4 0 1 1 8 0v4"></path></svg>
          </span>
          <input type="password" name="password" id="password" placeholder="••••••••" required autofocus autocomplete="current-password">
          <button type="button" class="btn-eye-toggle" id="togglePassword" title="Lihat PIN">
            <span id="eyeIcon">
              <!-- Eye Open SVG -->
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path></svg>
            </span>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-submit">
        <span>Buka Scanner Kamera</span>
        <!-- Arrow Right SVG -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0"></path><path d="M13 18l6 -6"></path><path d="M13 6l6 6"></path></svg>
      </button>
    </form>
  </div>
</div>

<script>
  const togglePasswordBtn = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  const eyeIcon = document.getElementById('eyeIcon');

  const eyeOpenSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path></svg>`;
  const eyeOffSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.585 10.587a2 2 0 0 0 2.828 2.83"></path><path d="M9.363 5.365a9.466 9.466 0 0 1 2.637 -.365c3.6 0 6.6 2 9 6c-.774 1.29 -1.656 2.34 -2.643 3.15m-2.433 1.581c-1.206 .444 -2.482 .669 -3.924 .669c-3.6 0 -6.6 -2 -9 -6c.77 -.128 1.554 -.48 2.374 -1.057"></path><path d="M3 3l18 18"></path></svg>`;

  togglePasswordBtn.addEventListener('click', () => {
    const isPassword = passwordInput.getAttribute('type') === 'password';
    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
    eyeIcon.innerHTML = isPassword ? eyeOffSvg : eyeOpenSvg;
  });
</script>
</body>
</html>
