@php
    $exceptionMessage = isset($exception) && !empty($exception->getMessage()) 
        ? $exception->getMessage() 
        : 'Fitur ini sedang tidak aktif atau Anda tidak memiliki akses.';
    $isFeatureDisabled = str_contains(strtolower($exceptionMessage), 'fitur');
    $user = auth()->user();
    $isAdmin = $user && in_array($user->role, ['super_admin', 'admin_sekolah'], true);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>403 - {{ $isFeatureDisabled ? 'Fitur Tidak Aktif' : 'Akses Ditolak' }}</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      border-radius: 5px !important;
    }

    html, body {
      width: 100vw;
      height: 100vh;
      max-width: 100vw;
      max-height: 100vh;
      margin: 0;
      padding: 0;
      overflow: hidden !important;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background-color: #0b0d14;
      color: #e4e6ef;
      display: grid;
      place-items: center;
    }

    /* Ambient Background Mesh */
    .bg-mesh {
      position: fixed;
      width: 500px;
      height: 500px;
      border-radius: 50% !important;
      filter: blur(130px);
      pointer-events: none;
      z-index: 0;
      opacity: 0.35;
    }
    .bg-mesh-1 { top: -150px; left: -150px; background: #7367f0; }
    .bg-mesh-2 { bottom: -150px; right: -150px; background: #ff9f43; }

    /* Absolute Dead Center Card Container */
    .error-card {
      position: relative;
      z-index: 10;
      background: rgba(18, 21, 33, 0.95);
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 2.25rem 2.25rem;
      max-width: 540px;
      width: 92%;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.7);
    }

    /* Status Pill */
    .status-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 4px 12px;
      background: rgba(255, 159, 67, 0.15);
      border: 1px solid rgba(255, 159, 67, 0.3);
      color: #ff9f43;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 1.25rem;
    }

    .status-dot {
      width: 6px;
      height: 6px;
      border-radius: 50% !important;
      background-color: #ff9f43;
      box-shadow: 0 0 8px #ff9f43;
    }

    /* Icon Box */
    .icon-box {
      width: 72px;
      height: 72px;
      margin: 0 auto 1.25rem auto;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 159, 67, 0.12);
      border: 1px solid rgba(255, 159, 67, 0.25);
      color: #ff9f43;
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
      color: #ffffff;
      margin-bottom: 0.4rem;
      letter-spacing: -1px;
      text-align: center;
    }

    .error-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: 0.6rem;
      text-align: center;
    }

    .error-desc {
      font-size: 0.88rem;
      color: #9296ad;
      line-height: 1.5;
      margin-bottom: 1.5rem;
      text-align: center;
      max-width: 440px;
    }

    /* Admin Notice Box */
    .admin-notice {
      background: rgba(115, 103, 240, 0.1);
      border: 1px dashed rgba(115, 103, 240, 0.35);
      padding: 0.75rem 1rem;
      font-size: 0.8rem;
      color: #c4c7e0;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      text-align: center;
      width: 100%;
    }

    .admin-notice svg {
      width: 20px;
      height: 20px;
      color: #7367f0;
      flex-shrink: 0;
    }

    /* Action Buttons - Inline Row Alignment */
    .btn-group-action {
      display: flex;
      gap: 8px;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      width: 100%;
    }

    .btn-action {
      padding: 9px 16px;
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      cursor: pointer;
      white-space: nowrap;
      transition: background 0.2s ease, transform 0.15s ease;
    }

    .btn-primary-action {
      background: #7367f0;
      color: #ffffff !important;
      border: 1px solid #7367f0;
    }

    .btn-primary-action:hover {
      background: #5e50ee;
      transform: translateY(-1px);
    }

    .btn-secondary-action {
      background: rgba(255, 255, 255, 0.08);
      color: #d0d2d6 !important;
      border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .btn-secondary-action:hover {
      background: rgba(255, 255, 255, 0.15);
      color: #ffffff !important;
      transform: translateY(-1px);
    }

    .btn-admin-action {
      background: rgba(255, 159, 67, 0.15);
      color: #ff9f43 !important;
      border: 1px solid rgba(255, 159, 67, 0.35);
    }

    .btn-admin-action:hover {
      background: rgba(255, 159, 67, 0.25);
      color: #ffffff !important;
      transform: translateY(-1px);
    }

    .btn-action svg {
      width: 16px;
      height: 16px;
    }
  </style>
</head>
<body>

  <div class="bg-mesh bg-mesh-1"></div>
  <div class="bg-mesh bg-mesh-2"></div>

  <div class="error-card">
    <div class="status-badge">
      <span class="status-dot"></span>
      <span>{{ $isFeatureDisabled ? 'Modul Non-Aktif' : 'Akses Ditolak' }}</span>
    </div>

    <!-- Valid Vector SVG -->
    <div class="icon-box">
      @if ($isFeatureDisabled)
        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
          <path d="M8 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
          <path d="M2 12a6 6 0 0 1 6 -6h8a6 6 0 0 1 6 6v0a6 6 0 0 1 -6 6h-8a6 6 0 0 1 -6 -6z" />
        </svg>
      @else
        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
          <rect x="5" y="11" width="14" height="10" rx="2" />
          <circle cx="12" cy="16" r="1" />
          <path d="M8 11v-4a4 4 0 0 1 8 0v4" />
        </svg>
      @endif
    </div>

    <div class="error-code">403</div>

    <h1 class="error-title">
      {{ $isFeatureDisabled ? 'Fitur Ini Sedang Tidak Aktif' : 'Akses Halaman Dibatasi' }}
    </h1>

    <p class="error-desc">
      {{ $exceptionMessage }}
      <br>
      Modul ini disembunyikan atau dinonaktifkan sementara oleh administrator.
    </p>

    @if ($isAdmin)
      <div class="admin-notice">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="9" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <div>
          <strong>Info Administrator:</strong> Anda dapat mengaktifkan modul ini kembali di menu <strong>Pengaturan &gt; Aktivasi Fitur</strong>.
        </div>
      </div>
    @endif

    <div class="btn-group-action">
      <a href="javascript:history.back()" class="btn-action btn-secondary-action">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12" />
          <polyline points="12 19 5 12 12 5" />
        </svg>
        Kembali
      </a>

      <a href="{{ url('/') }}" class="btn-action btn-primary-action">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
          <polyline points="9 22 9 12 15 12 15 22" />
        </svg>
        Beranda Utama
      </a>

      @if ($isAdmin)
        <a href="{{ route('admin.pengaturan.index') }}#tab-aktivasi-fitur" class="btn-action btn-admin-action">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
            <path d="M2 12a6 6 0 0 1 6 -6h8a6 6 0 0 1 6 6v0a6 6 0 0 1 -6 6h-8a6 6 0 0 1 -6 -6z" />
          </svg>
          Pengaturan Fitur
        </a>
      @endif
    </div>
  </div>

</body>
</html>
