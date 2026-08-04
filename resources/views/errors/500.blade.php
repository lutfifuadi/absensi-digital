@php
    $exceptionMessage = isset($exception) && !empty($exception->getMessage()) 
        ? $exception->getMessage() 
        : 'Terjadi kesalahan internal pada server kami saat memproses permintaan Anda.';
    $user = auth()->user();
    $isAdmin = $user && in_array($user->role, ['super_admin', 'admin_sekolah'], true);
    $showDetails = config('app.debug') || $isAdmin;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>500 - Server Error | Presensi Digital</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      border-radius: 6px;
    }

    html, body {
      width: 100vw;
      height: 100vh;
      height: 100dvh;
      max-width: 100vw;
      max-height: 100vh;
      max-height: 100dvh;
      margin: 0;
      padding: 0;
      overflow: hidden !important;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background-color: #0b0d14;
      color: #e4e6ef;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Ambient Background Mesh */
    .bg-mesh {
      position: fixed;
      width: 550px;
      height: 550px;
      border-radius: 50% !important;
      filter: blur(140px);
      pointer-events: none;
      z-index: 0;
      opacity: 0.3;
    }
    .bg-mesh-1 { top: -180px; left: -180px; background: #ea5455; }
    .bg-mesh-2 { bottom: -180px; right: -180px; background: #ff9f43; }
    .bg-mesh-3 { top: 40%; left: 60%; width: 400px; height: 400px; background: #7367f0; opacity: 0.15; }

    /* Card Container */
    .error-card {
      position: relative;
      z-index: 10;
      background: rgba(18, 21, 33, 0.95);
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 2.25rem 2rem;
      max-width: 560px;
      width: 92%;
      max-height: 96vh;
      max-height: 96dvh;
      overflow-y: auto;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 30px rgba(234, 84, 85, 0.15);
      backdrop-filter: blur(10px);
      animation: fadeIn 0.4s ease-out;
      scrollbar-width: thin;
      scrollbar-color: rgba(234, 84, 85, 0.4) transparent;
    }

    .error-card::-webkit-scrollbar {
      width: 4px;
    }
    .error-card::-webkit-scrollbar-thumb {
      background: rgba(234, 84, 85, 0.4);
      border-radius: 4px;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(12px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Status Pill */
    .status-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 5px 14px;
      background: rgba(234, 84, 85, 0.15);
      border: 1px solid rgba(234, 84, 85, 0.35);
      color: #ff6b6b;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      margin-bottom: 1.25rem;
      flex-shrink: 0;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50% !important;
      background-color: #ea5455;
      box-shadow: 0 0 10px #ea5455;
      animation: pulseDot 2s infinite;
    }

    @keyframes pulseDot {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.3); opacity: 0.6; }
    }

    /* Icon Box */
    .icon-box {
      width: 76px;
      height: 76px;
      margin: 0 auto 1rem auto;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, rgba(234, 84, 85, 0.2), rgba(255, 159, 67, 0.1));
      border: 1px solid rgba(234, 84, 85, 0.3);
      color: #ea5455;
      box-shadow: 0 8px 24px rgba(234, 84, 85, 0.2);
      flex-shrink: 0;
    }

    .icon-box svg {
      width: 38px;
      height: 38px;
      stroke-width: 1.75;
    }

    .error-code {
      font-size: 3.75rem;
      font-weight: 800;
      line-height: 1;
      background: linear-gradient(135deg, #ffffff 30%, #ea5455 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 0.4rem;
      letter-spacing: -1px;
      text-align: center;
      flex-shrink: 0;
    }

    .error-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: 0.6rem;
      text-align: center;
      flex-shrink: 0;
    }

    .error-desc {
      font-size: 0.88rem;
      color: #9296ad;
      line-height: 1.5;
      margin-bottom: 1.5rem;
      text-align: center;
      max-width: 440px;
      flex-shrink: 0;
    }

    /* Action Buttons */
    .btn-group-action {
      display: flex;
      gap: 10px;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      width: 100%;
      flex-shrink: 0;
    }

    .btn-action {
      padding: 9px 16px;
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s ease;
      border: none;
    }

    .btn-primary-action {
      background: linear-gradient(135deg, #ea5455, #e03e3e);
      color: #ffffff !important;
      box-shadow: 0 4px 14px rgba(234, 84, 85, 0.4);
    }

    .btn-primary-action:hover {
      background: linear-gradient(135deg, #d44343, #c53030);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(234, 84, 85, 0.5);
    }

    .btn-secondary-action {
      background: rgba(255, 255, 255, 0.08);
      color: #d0d2d6 !important;
      border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .btn-secondary-action:hover {
      background: rgba(255, 255, 255, 0.16);
      color: #ffffff !important;
      transform: translateY(-2px);
    }

    .btn-action svg {
      width: 16px;
      height: 16px;
    }

    /* Collapsible Debug Box */
    .debug-box {
      margin-top: 1.25rem;
      width: 100%;
      text-align: left;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      padding-top: 1rem;
      flex-shrink: 0;
    }

    .debug-toggle {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      color: #b0b4cf;
      padding: 7px 12px;
      font-size: 0.76rem;
      font-weight: 600;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      transition: background 0.2s;
    }

    .debug-toggle:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #ffffff;
    }

    .debug-content {
      display: none;
      margin-top: 6px;
      background: #07080e;
      border: 1px solid rgba(234, 84, 85, 0.3);
      padding: 10px 12px;
      font-family: 'Fira Code', 'Courier New', monospace;
      font-size: 0.74rem;
      color: #ff9f9f;
      max-height: 130px;
      overflow-y: auto;
      word-break: break-all;
      white-space: pre-wrap;
    }

    .copy-btn {
      background: rgba(255, 255, 255, 0.1);
      border: none;
      color: #d0d2d6;
      font-size: 0.7rem;
      padding: 4px 10px;
      cursor: pointer;
      margin-top: 6px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .copy-btn:hover {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
    }

    /* Responsive adjustments for low height or mobile screens */
    @media (max-height: 680px), (max-width: 576px) {
      .error-card {
        padding: 1.5rem 1.25rem;
      }
      .status-badge {
        margin-bottom: 0.75rem;
        padding: 3px 10px;
        font-size: 0.7rem;
      }
      .icon-box {
        width: 60px;
        height: 60px;
        margin-bottom: 0.6rem;
      }
      .icon-box svg {
        width: 28px;
        height: 28px;
      }
      .error-code {
        font-size: 3rem;
      }
      .error-title {
        font-size: 1.1rem;
        margin-bottom: 0.4rem;
      }
      .error-desc {
        font-size: 0.8rem;
        margin-bottom: 1rem;
      }
      .btn-action {
        padding: 8px 12px;
        font-size: 0.78rem;
      }
      .debug-box {
        margin-top: 0.85rem;
        padding-top: 0.75rem;
      }
    }
  </style>
</head>
<body>

  <div class="bg-mesh bg-mesh-1"></div>
  <div class="bg-mesh bg-mesh-2"></div>
  <div class="bg-mesh bg-mesh-3"></div>

  <div class="error-card">
    <div class="status-badge">
      <span class="status-dot"></span>
      <span>500 | Internal Server Error</span>
    </div>

    <div class="icon-box">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4" />
        <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
        <path d="M12 16h.01" />
      </svg>
    </div>

    <div class="error-code">500</div>

    <h1 class="error-title">
      Terjadi Kendala Internal Server
    </h1>

    <p class="error-desc">
      Maaf, sistem mengalami kesalahan saat memproses permintaan Anda. Tim teknis telah mencatat kejadian ini untuk segera ditangani.
    </p>

    <div class="btn-group-action">
      <button onclick="window.location.reload()" class="btn-action btn-primary-action">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
          <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
        </svg>
        Muat Ulang Halaman
      </button>

      <a href="javascript:history.back()" class="btn-action btn-secondary-action">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12" />
          <polyline points="12 19 5 12 12 5" />
        </svg>
        Kembali
      </a>

      <a href="{{ url('/') }}" class="btn-action btn-secondary-action">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
          <polyline points="9 22 9 12 15 12 15 22" />
        </svg>
        Beranda Utama
      </a>
    </div>

    @if ($showDetails)
      <div class="debug-box">
        <button class="debug-toggle" onclick="toggleDebug()">
          <span>⚙️ Detail Error (Khusus Developer / Admin)</span>
          <span id="toggleArrow">▼</span>
        </button>
        <div class="debug-content" id="debugContent">{{ $exceptionMessage }}</div>
        <button class="copy-btn" onclick="copyError()">📋 Salin Error Log</button>
      </div>
    @endif
  </div>

  <script>
    function toggleDebug() {
      const content = document.getElementById('debugContent');
      const arrow = document.getElementById('toggleArrow');
      if (content.style.display === 'block') {
        content.style.display = 'none';
        arrow.textContent = '▼';
      } else {
        content.style.display = 'block';
        arrow.textContent = '▲';
      }
    }

    function copyError() {
      const text = document.getElementById('debugContent').innerText;
      navigator.clipboard.writeText(text).then(() => {
        alert('Detail error berhasil disalin ke clipboard!');
      });
    }
  </script>

</body>
</html>
