<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Scan QR Absensi Ekskul — Siswa</title>

  {{-- PWA Meta Tags --}}
  <meta name="application-name" content="Absensi Ekskul">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Absensi Ekskul">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#059669">
  <link rel="manifest" href="/manifest.json">

  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}">

  <style>
    /* ── DESIGN SYSTEM TOKENS ────────────────────────────────── */
    :root {
      --das-primary: #10b981;
      --das-primary-soft: rgba(16, 185, 129, 0.15);
      --das-success: #28c76f;
      --das-info: #00cfe8;
      --das-warning: #ff9f43;
      --das-danger: #ea5455;
      --das-dark-bg: #0f172a;
      --das-panel-bg: rgba(30, 41, 59, 0.7);
      --das-border-color: rgba(255, 255, 255, 0.08);
      --font-family: 'Product Sans', sans-serif;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
      height: 100dvh; max-height: 100dvh; width: 100vw; max-width: 100vw;
      font-family: var(--font-family);
      background: var(--das-dark-bg);
      color: #e2e8f0;
      overflow: hidden;
      display: flex; flex-direction: column;
    }

    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-thumb { background: var(--das-primary); border-radius: 4px; }

    /* ── NAVBAR ──────────────────────────────────────────────── */
    .nav {
      display: flex; align-items: center; justify-content: space-between;
      padding: 0.8rem 1.5rem;
      background: rgba(15, 23, 42, 0.85);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--das-border-color);
      flex-shrink: 0; z-index: 100;
    }
    .nav-brand { display: flex; align-items: center; gap: 0.8rem; }
    .brand-icon {
      width: 40px; height: 40px;
      background: linear-gradient(135deg, var(--das-primary), #059669);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.2rem;
      box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
    }
    .nav-brand h1 { font-size: 1.1rem; font-weight: 800; color: #fff; letter-spacing: -0.5px; margin: 0; }
    .nav-brand p  { font-size: 0.7rem; color: #94a3b8; margin: -2px 0 0 0; }
    .nav-right { display: flex; align-items: center; gap: 1rem; }
    .nav-date { font-size: 0.8rem; font-weight: 700; color: #94a3b8; }

    /* ── LAYOUT ──────────────────────────────────────────────── */
    .layout {
      flex: 1; display: grid; grid-template-columns: 1fr; grid-template-rows: 1fr;
      min-height: 0; overflow: hidden;
    }
    @media (min-width: 992px) {
      .layout { grid-template-columns: 1fr 420px; }
    }

    /* ── CAMERA PANEL ────────────────────────────────────────── */
    .camera-panel {
      position: relative; background: #000;
      display: flex; align-items: center; justify-content: center;
      overflow: hidden; min-height: 0;
    }
    @media (max-width: 991px) {
      .camera-panel { height: 55dvh; flex-shrink: 0; }
    }
    @media (max-width: 480px) {
      .camera-panel { height: 48dvh; }
    }

    #video { width: 100%; height: 100%; object-fit: cover; display: none; }

    /* Scan Crosshair */
    .scan-crosshair {
      position: absolute; pointer-events: none; display: none; z-index: 5;
    }
    .scan-crosshair.active { display: block; }
    .scan-crosshair .frame { width: 260px; height: 260px; position: relative; }
    @media (min-width: 1200px) { .scan-crosshair .frame { width: 360px; height: 360px; } }
    @media (max-width: 480px) { .scan-crosshair .frame { width: 200px; height: 200px; } }

    .scan-crosshair .corner {
      position: absolute; width: 36px; height: 36px;
      border-color: var(--das-primary); border-style: solid;
    }
    .corner.tl { top: 0; left: 0; border-width: 4px 0 0 4px; border-radius: 6px 0 0 0; }
    .corner.tr { top: 0; right: 0; border-width: 4px 4px 0 0; border-radius: 0 6px 0 0; }
    .corner.bl { bottom: 0; left: 0; border-width: 0 0 4px 4px; border-radius: 0 0 0 6px; }
    .corner.br { bottom: 0; right: 0; border-width: 0 4px 4px 0; border-radius: 0 0 6px 0; }

    .scan-line {
      position: absolute; left: 3px; right: 3px; height: 3px;
      background: linear-gradient(90deg, transparent, var(--das-primary), transparent);
      box-shadow: 0 0 15px var(--das-primary);
      animation: scanLine 2.5s ease-in-out infinite;
    }
    @keyframes scanLine { 0% { top: 5%; } 50% { top: 95%; } 100% { top: 5%; } }

    /* Idle Screen */
    .idle-screen {
      position: absolute; inset: 0;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center; gap: 1.5rem;
      background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
      z-index: 20; padding: 2rem;
    }
    .idle-icon-wrapper {
      width: 110px; height: 110px;
      background: var(--das-primary-soft);
      border: 1px solid rgba(16, 185, 129, 0.25);
      border-radius: 24px;
      display: flex; align-items: center; justify-content: center;
      font-size: 3.2rem; margin-bottom: 0.5rem; position: relative;
    }
    .idle-icon-wrapper::after {
      content: ''; position: absolute; inset: -10px;
      border: 2px dashed rgba(16, 185, 129, 0.2);
      border-radius: 32px;
      animation: das_spin 10s linear infinite;
    }
    @keyframes das_spin { 100% { transform: rotate(360deg); } }

    .idle-screen p { color: #94a3b8; font-size: 0.9rem; text-align: center; max-width: 300px; line-height: 1.6; }

    .btn-start {
      background: var(--das-primary); color: white; border: none;
      padding: 0.9rem 2.2rem; border-radius: 12px;
      font-weight: 800; font-size: 1rem; cursor: pointer;
      display: flex; align-items: center; gap: 0.8rem;
      box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .btn-start:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 15px 35px rgba(16, 185, 129, 0.5); }
    .btn-start:active { transform: translateY(0) scale(0.98); }
    .btn-start:disabled { opacity: 0.5; transform: none; cursor: not-allowed; }

    .btn-switch {
      position: absolute; top: 1.2rem; right: 1.2rem;
      background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px);
      border: 1px solid var(--das-border-color); color: white;
      width: 44px; height: 44px; border-radius: 12px;
      display: none; align-items: center; justify-content: center;
      font-size: 1.2rem; cursor: pointer; z-index: 40;
      transition: all 0.2s;
    }
    .btn-switch:hover { background: var(--das-primary); border-color: var(--das-primary); }
    .btn-switch.active { display: flex; }

    /* Error Box */
    .error-box {
      display: none;
      background: rgba(234,84,85,0.1); border: 1px solid var(--das-danger);
      color: var(--das-danger); padding: 0.8rem 1rem; border-radius: 10px;
      font-size: 0.8rem; max-width: 300px; text-align: center; line-height: 1.5;
      margin-top: -0.5rem;
    }

    /* ── RESULT TOAST ────────────────────────────────────────── */
    .result-toast {
      position: absolute; bottom: 1.5rem; left: 50%;
      transform: translateX(-50%) translateY(40px);
      width: 90%; max-width: 420px;
      background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(10px);
      border: 1px solid var(--das-border-color); border-radius: 14px;
      padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;
      z-index: 30; opacity: 0; visibility: hidden;
      transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .result-toast.show { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }

    .toast-inner { display: flex; align-items: start; gap: 1rem; }
    .toast-icon {
      width: 48px; height: 48px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem; flex-shrink: 0;
    }
    .success .toast-icon { background: rgba(40,199,111,0.1); color: var(--das-success); border: 1px solid rgba(40,199,111,0.2); }
    .warning .toast-icon { background: rgba(255,159,67,0.1); color: var(--das-warning); border: 1px solid rgba(255,159,67,0.2); }
    .error .toast-icon   { background: rgba(234,84,85,0.1); color: var(--das-danger); border: 1px solid rgba(234,84,85,0.2); }

    .toast-name { font-size: 1.05rem; font-weight: 800; color: white; display: block; }
    .toast-meta { font-size: 0.78rem; color: #94a3b8; }
    .toast-msg  { font-size: 0.85rem; margin-top: 0.15rem; }

    .toast-bar { height: 4px; background: rgba(255,255,255,0.05); border-radius: 2px; overflow: hidden; }
    .toast-fill { height: 100%; transition: width linear; }

    /* ── SIDEBAR ─────────────────────────────────────────────── */
    .sidebar {
      background: var(--das-panel-bg);
      border-left: 1px solid var(--das-border-color);
      display: flex; flex-direction: column; overflow: hidden;
    }
    @media (max-width: 991px) {
      .sidebar { border-left: none; border-top: 1px solid var(--das-border-color); flex: 1; min-height: 0; }
    }

    .sidebar-header {
      padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--das-border-color);
      flex-shrink: 0;
    }
    .sidebar-title {
      font-size: 0.72rem; font-weight: 700; color: #64748b;
      text-transform: uppercase; letter-spacing: 1.5px;
    }

    /* NIS Input Section */
    .nis-section {
      padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--das-border-color);
      flex-shrink: 0;
    }
    .nis-label {
      font-size: 0.7rem; font-weight: 700; color: #64748b;
      text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.6rem;
    }
    .nis-input-group {
      display: flex; gap: 0.5rem;
    }
    .nis-input {
      flex: 1;
      background: rgba(255,255,255,0.04);
      border: 1px solid var(--das-border-color);
      border-radius: 10px; padding: 0.7rem 1rem;
      color: white; font-size: 1rem; font-family: var(--font-family);
      outline: none; transition: all 0.2s;
    }
    .nis-input:focus {
      border-color: var(--das-primary);
      background: rgba(255,255,255,0.07);
      box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
    }
    .nis-input::placeholder { color: #475569; }
    .nis-input.error { border-color: var(--das-danger); }

    .nis-hint {
      font-size: 0.7rem; color: #64748b; margin-top: 0.5rem;
      display: flex; align-items: center; gap: 0.3rem;
    }

    /* Info Section */
    .info-section {
      padding: 1rem 1.5rem; border-bottom: 1px solid var(--das-border-color);
      flex-shrink: 0;
    }
    .info-item {
      display: flex; align-items: center; gap: 0.6rem;
      padding: 0.4rem 0; font-size: 0.82rem;
    }
    .info-item i { color: var(--das-primary); font-size: 1rem; width: 22px; text-align: center; }
    .info-item span { color: #cbd5e1; }
    .info-item strong { color: white; }

    /* Scan Log */
    .scan-log-header {
      padding: 0.8rem 1.5rem; display: flex; align-items: center;
      justify-content: space-between; border-bottom: 1px solid var(--das-border-color);
      flex-shrink: 0;
    }
    .scan-log-title { font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; }
    .scan-log {
      flex: 1; overflow-y: auto; padding: 1rem 1.5rem;
      display: flex; flex-direction: column; gap: 0.6rem; min-height: 0;
    }

    .log-item {
      padding: 0.8rem 1rem;
      background: rgba(15, 23, 42, 0.3);
      border: 1px solid var(--das-border-color); border-radius: 12px;
      display: flex; align-items: center; gap: 0.8rem;
      animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }

    .log-avatar {
      width: 38px; height: 38px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; flex-shrink: 0; color: white; font-size: 0.75rem;
      border: 1px solid rgba(255,255,255,0.1);
    }
    .log-avatar.success { background: var(--das-success); }
    .log-avatar.warning { background: var(--das-warning); }
    .log-avatar.error   { background: var(--das-danger); }

    .log-info { flex: 1; min-width: 0; }
    .log-name {
      font-size: 0.8rem; font-weight: 700; color: white; display: block;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .log-desc { font-size: 0.72rem; color: #64748b; }
    .log-jam {
      font-size: 0.75rem; font-weight: 800; padding: 0.15rem 0.5rem;
      border-radius: 6px; background: rgba(255,255,255,0.05); white-space: nowrap;
    }

    .log-empty {
      text-align: center; padding: 2.5rem 1rem; opacity: 0.35;
    }
    .log-empty i { font-size: 2.5rem; margin-bottom: 0.8rem; display: block; opacity: 0.15; }
    .log-empty p { font-size: 0.8rem; }

    /* ── RESPONSIVE ──────────────────────────────────────────── */
    @media (max-width: 991px) {
      .nav { padding: 0.6rem 1rem; }
      .brand-icon { width: 34px; height: 34px; font-size: 1rem; }
      .nav-brand h1 { font-size: 0.9rem; }
      .nav-brand p { font-size: 0.6rem; }
      .nav-date { display: none; }
      .sidebar-header { padding: 0.8rem 1rem; }
      .nis-section { padding: 0.8rem 1rem; }
      .nis-input { font-size: 0.9rem; padding: 0.6rem 0.8rem; }
      .info-section { padding: 0.6rem 1rem; }
      .scan-log-header { padding: 0.6rem 1rem; }
      .scan-log { padding: 0.6rem 1rem; }
      .result-toast { bottom: 1rem; }
    }

    @media (max-width: 480px) {
      .camera-panel { height: 44dvh; }
      .nav-brand h1 { font-size: 0.8rem; }
      .nav-brand p { display: none; }
      .idle-screen { padding: 1.5rem; }
      .idle-icon-wrapper { width: 90px; height: 90px; font-size: 2.5rem; }
      .result-toast { width: 94%; padding: 0.8rem; }
      .btn-start { padding: 0.7rem 1.8rem; font-size: 0.9rem; }
    }
  </style>
</head>
<body>

<!-- ── NAVBAR ──────────────────────────────────────────────────── -->
<nav class="nav">
  <div class="nav-brand">
    <div class="brand-icon">
      <i class="ti tabler-qrcode"></i>
    </div>
    <div>
      <h1>Scan QR Absensi Ekskul</h1>
      <p>Silakan scan QR dari pembina</p>
    </div>
  </div>
  <div class="nav-right">
    <div class="nav-date" id="nav-date"></div>
  </div>
</nav>

<!-- ── LAYOUT ─────────────────────────────────────────────────── -->
<div class="layout">

  <!-- ── CAMERA PANEL ────────────────────────────────────────── -->
  <div class="camera-panel">
    <video id="video" playsinline muted autoplay></video>
    <canvas id="canvas-hidden" style="display:none;"></canvas>

    <button class="btn-switch" id="switch-btn" title="Ganti Kamera">
      <i class="ti tabler-camera-rotate"></i>
    </button>

    <div class="scan-crosshair" id="scan-crosshair">
      <div class="frame">
        <div class="corner tl"></div>
        <div class="corner tr"></div>
        <div class="corner bl"></div>
        <div class="corner br"></div>
        <div class="scan-line"></div>
      </div>
    </div>

    <div class="idle-screen" id="idle-screen">
      <div class="idle-icon-wrapper">
        <i class="ti tabler-qrcode"></i>
      </div>
      <p>Arahkan kamera ke QR code yang ditampilkan oleh pembina ekskul.</p>
      <div class="error-box" id="camera-error"></div>
      <button class="btn-start" id="start-btn">
        <i class="ti tabler-player-play"></i> Aktifkan Kamera
      </button>
    </div>

    <div class="result-toast" id="result-toast">
      <div class="toast-inner">
        <div class="toast-icon" id="toast-icon"><i class="ti tabler-check"></i></div>
        <div style="flex:1; min-width:0;">
          <div class="toast-name" id="toast-name">—</div>
          <div class="toast-meta" id="toast-meta">—</div>
          <div class="toast-msg" id="toast-msg">—</div>
        </div>
      </div>
      <div class="toast-bar"><div class="toast-fill" id="toast-fill"></div></div>
    </div>
  </div>

  <!-- ── SIDEBAR ──────────────────────────────────────────────── -->
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-title">Data Diri Siswa</div>
    </div>

    {{-- NIS Input --}}
    <div class="nis-section">
      <div class="nis-label">Nomor Induk Siswa (NIS/NISN)</div>
      <div class="nis-input-group">
        <input type="text"
               id="nis-input"
               class="nis-input"
               placeholder="Masukkan NIS kamu..."
               inputmode="numeric"
               autocomplete="off"
               maxlength="30">
      </div>
      <div class="nis-hint">
        <i class="ti tabler-info-circle" style="font-size:0.75rem;"></i>
        Isi NIS terlebih dahulu sebelum scan QR
      </div>
    </div>

    {{-- Info Setelah Scan --}}
    <div class="info-section" id="info-section" style="display:none;">
      <div class="info-item">
        <i class="ti tabler-school"></i>
        <span>Ekskul: <strong id="info-ekskul">—</strong></span>
      </div>
      <div class="info-item">
        <i class="ti tabler-calendar"></i>
        <span>Tanggal: <strong id="info-tanggal">—</strong></span>
      </div>
      <div class="info-item">
        <i class="ti tabler-user"></i>
        <span>Siswa: <strong id="info-siswa">—</strong></span>
      </div>
    </div>

    {{-- Scan Log --}}
    <div class="scan-log-header">
      <div class="scan-log-title">Riwayat Scan</div>
    </div>

    <div class="scan-log" id="scan-log">
      <div class="log-empty">
        <i class="ti tabler-qrcode"></i>
        <p>Scan QR dari pembina untuk mencatat kehadiran</p>
      </div>
    </div>
  </div>

</div>

{{-- jsQR Library --}}
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<script>
  // ── Config ──────────────────────────────────────────────────
  const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
  const SCAN_URL  = "{{ url('/api/ekskul/absensi/scan') }}";
  const DISMISS   = 3500;
  const DEBOUNCE  = 3500;

  // ── Date Display ─────────────────────────────────────────────
  const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  function updateNavDate() {
    const d = new Date();
    document.getElementById('nav-date').textContent =
      `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
  }
  updateNavDate();
  setInterval(updateNavDate, 60000);

  // ── Sound ────────────────────────────────────────────────────
  let soundEnabled = true;

  function beep(type) {
    if (!soundEnabled) return;
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();

      function tone(freq, t0, dur, vol = 0.4, shape = 'bell') {
        const osc = ctx.createOscillator();
        const g   = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, t0);
        osc.connect(g); g.connect(ctx.destination);
        g.gain.setValueAtTime(0, t0);
        if (shape === 'bell') {
          g.gain.linearRampToValueAtTime(vol, t0 + 0.005);
          g.gain.exponentialRampToValueAtTime(0.001, t0 + dur);
        } else {
          g.gain.linearRampToValueAtTime(vol, t0 + 0.01);
          g.gain.linearRampToValueAtTime(vol * 0.8, t0 + dur - 0.02);
          g.gain.linearRampToValueAtTime(0.001, t0 + dur);
        }
        osc.start(t0); osc.stop(t0 + dur + 0.01);
      }

      const now = ctx.currentTime;
      if (type === 'success') {
        tone(523.25, now, 0.55, 0.4, 'bell');
        tone(659.25, now + 0.18, 0.6, 0.4, 'bell');
        tone(783.99, now + 0.35, 0.5, 0.35, 'bell');
      } else if (type === 'warning') {
        tone(440, now, 0.25, 0.35, 'bell');
        tone(330, now + 0.3, 0.3, 0.35, 'bell');
      } else {
        tone(330, now, 0.18, 0.35, 'square');
        tone(220, now + 0.22, 0.22, 0.35, 'square');
      }
    } catch(_) {}
  }

  // ── Scan Log ─────────────────────────────────────────────────
  function addLog(type, title, desc) {
    const log = document.getElementById('scan-log');
    const empty = log.querySelector('.log-empty');
    if (empty) empty.remove();

    const item = document.createElement('div');
    item.className = 'log-item';

    const initials = title ? title.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase() : '?';
    const avatarClass = type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'error');
    const jam = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});

    const colorMap = { success: 'var(--das-success)', warning: 'var(--das-warning)', error: 'var(--das-danger)' };

    item.innerHTML = `
      <div class="log-avatar ${avatarClass}">${initials}</div>
      <div class="log-info">
        <span class="log-name">${title || 'Info'}</span>
        <span class="log-desc">${desc || ''}</span>
      </div>
      <div class="log-jam" style="color:${colorMap[type] || '#94a3b8'}">${jam}</div>
    `;
    log.insertBefore(item, log.firstChild);
    while (log.children.length > 25) log.removeChild(log.lastChild);
  }

  // ── Toast ────────────────────────────────────────────────────
  let toastTimer = null;
  function showToast(type, title, meta, msg) {
    const toast  = document.getElementById('result-toast');
    const fill   = document.getElementById('toast-fill');
    const iconEl = document.getElementById('toast-icon');

    const icons = {
      success: '<i class="ti tabler-circle-check"></i>',
      warning: '<i class="ti tabler-exclamation-circle"></i>',
      error:   '<i class="ti tabler-circle-x"></i>'
    };

    iconEl.innerHTML = icons[type] || icons.error;
    document.getElementById('toast-name').textContent = title || '—';
    document.getElementById('toast-meta').textContent = meta || '';
    document.getElementById('toast-msg').textContent  = msg || '';

    toast.className = `result-toast ${type} show`;
    fill.style.transition = 'none';
    fill.style.width = '0%';
    fill.style.background = `var(--das-${type === 'warning' ? 'warning' : (type === 'error' ? 'danger' : 'success')})`;

    if (toastTimer) clearTimeout(toastTimer);
    requestAnimationFrame(() => {
      fill.style.transition = `width ${DISMISS}ms linear`;
      fill.style.width = '100%';
    });
    toastTimer = setTimeout(() => {
      toast.classList.remove('show');
      isProcessing = false;
      if (stream && !animFrame) animFrame = requestAnimationFrame(tick);
    }, DISMISS);
  }

  // ── Info Section Updater ─────────────────────────────────────
  function updateInfoSection(data) {
    const section = document.getElementById('info-section');
    if (data && data.ekskul) {
      section.style.display = 'block';
      document.getElementById('info-ekskul').textContent = data.ekskul;
      document.getElementById('info-tanggal').textContent = data.tanggal || '—';
      document.getElementById('info-siswa').textContent = data.siswa?.nama || '—';
    }
  }

  // ── Camera ───────────────────────────────────────────────────
  let stream = null, animFrame = null, isProcessing = false;
  let lastQR = '', lastQRTime = 0;
  let currentFacingMode = 'environment';

  const video       = document.getElementById('video');
  const canvas      = document.getElementById('canvas-hidden');
  const ctx         = canvas.getContext('2d', { willReadFrequently: true });
  const idleScreen  = document.getElementById('idle-screen');
  const startBtn    = document.getElementById('start-btn');
  const switchBtn   = document.getElementById('switch-btn');
  const crosshair   = document.getElementById('scan-crosshair');
  const nisInput    = document.getElementById('nis-input');

  async function startCamera(facingMode = 'environment') {
    if (stream) {
      stream.getTracks().forEach(t => t.stop());
    }

    try {
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: { ideal: facingMode }, width: { ideal: 1280 }, height: { ideal: 720 } }
        });
      } catch(_) {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
      }

      video.srcObject = stream;
      await video.play();

      idleScreen.style.display = 'none';
      video.style.display = 'block';
      crosshair.classList.add('active');
      switchBtn.classList.add('active');

      if (!animFrame) animFrame = requestAnimationFrame(tick);
      return true;
    } catch(err) {
      let msg = 'Tidak dapat mengakses kamera. ';
      if (['NotAllowedError','PermissionDeniedError'].includes(err.name))
        msg += 'Izin kamera ditolak. Buka pengaturan browser untuk mengizinkan akses kamera.';
      else if (['NotFoundError','DevicesNotFoundError'].includes(err.name))
        msg += 'Kamera tidak ditemukan di perangkat ini.';
      else if (err.name === 'NotReadableError')
        msg += 'Kamera sedang digunakan aplikasi lain.';
      else msg += err.message;

      document.getElementById('camera-error').textContent = msg;
      document.getElementById('camera-error').style.display = 'block';
      return false;
    }
  }

  startBtn.addEventListener('click', async () => {
    startBtn.disabled = true;
    startBtn.innerHTML = '<i class="ti tabler-loader-2" style="animation:das_spin 1s linear infinite;"></i> Memulai...';
    document.getElementById('camera-error').style.display = 'none';

    const ok = await startCamera(currentFacingMode);
    if (!ok) {
      startBtn.disabled = false;
      startBtn.innerHTML = '<i class="ti tabler-refresh"></i> Coba Lagi';
    }
  });

  switchBtn.addEventListener('click', async () => {
    currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
    switchBtn.disabled = true;
    switchBtn.innerHTML = '<i class="ti tabler-loader-2" style="animation:das_spin 1s linear infinite;"></i>';

    await startCamera(currentFacingMode);

    switchBtn.disabled = false;
    switchBtn.innerHTML = '<i class="ti tabler-camera-rotate"></i>';
  });

  // ── Scan Tick ────────────────────────────────────────────────
  function tick() {
    if (!stream) return;
    if (video.readyState >= video.HAVE_ENOUGH_DATA) {
      canvas.width  = video.videoWidth;
      canvas.height = video.videoHeight;
      ctx.drawImage(video, 0, 0);
      const img  = ctx.getImageData(0, 0, canvas.width, canvas.height);
      const code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'attemptBoth' });
      if (code && !isProcessing) {
        const now = Date.now();
        if (code.data !== lastQR || now - lastQRTime > DEBOUNCE) {
          lastQR = code.data; lastQRTime = now;
          handleScan(code.data);
        }
      }
    }
    animFrame = requestAnimationFrame(tick);
  }

  // ── Handle Scan ──────────────────────────────────────────────
  async function handleScan(qrData) {
    // Validasi NIS sudah diisi
    const nis = nisInput.value.trim();
    if (!nis) {
      showToast('error', 'NIS Belum Diisi', '', 'Silakan masukkan NIS/NISN kamu terlebih dahulu di panel samping.');
      addLog('error', 'NIS Kosong', 'Silakan isi NIS terlebih dahulu');
      isProcessing = false;
      if (stream && !animFrame) animFrame = requestAnimationFrame(tick);
      return;
    }

    // Ekstrak token dari QR data
    // QR bisa berisi URL lengkap atau token langsung
    let token = qrData.trim();
    const urlMatch = token.match(/\/api\/ekskul\/absensi\/scan\/(.+)$/);
    if (urlMatch) {
      token = urlMatch[1];
    }

    isProcessing = true;
    if (animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }

    try {
      const resp = await fetch(`${SCAN_URL}/${encodeURIComponent(token)}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ nis: nis }),
      });

      const data = await resp.json();

      if (resp.ok && data.success) {
        beep('success');
        addLog('success', data.data?.siswa?.nama || 'Berhasil', data.message);
        showToast('success',
          data.data?.siswa?.nama || 'Berhasil',
          `${data.data?.ekskul || ''} · ${data.data?.tanggal || ''}`,
          data.message || 'Absensi berhasil dicatat.'
        );
        updateInfoSection({
          ekskul: data.data?.ekskul,
          tanggal: data.data?.tanggal,
          siswa: data.data?.siswa,
        });
        if (navigator.vibrate) navigator.vibrate([100]);
      } else if (resp.status === 409) {
        // Already attended
        beep('warning');
        addLog('warning', data.data?.siswa?.nama || 'Sudah Hadir', data.message);
        showToast('warning',
          data.data?.siswa?.nama || 'Sudah Hadir',
          data.data?.ekskul || '',
          data.message || 'Kamu sudah tercatat hadir.'
        );
        updateInfoSection({
          ekskul: data.data?.ekskul,
          tanggal: data.data?.tanggal,
          siswa: data.data?.siswa,
        });
      } else {
        beep('error');
        addLog('error', 'Gagal', data.message || 'QR tidak valid.');
        showToast('error', 'Gagal', '', data.message || 'QR Code tidak valid atau sudah kadaluarsa.');
      }
    } catch(e) {
      beep('error');
      addLog('error', 'Error', 'Gagal terhubung ke server.');
      showToast('error', 'Koneksi Gagal', '', 'Tidak dapat terhubung ke server. Periksa koneksi internet kamu.');
    }
  }

  // ── NIS Input: Enter key triggers camera ─────────────────────
  nisInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      if (!stream) {
        startBtn.click();
      }
    }
  });

  // ── Pause scanning when tab is hidden ────────────────────────
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      if (animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }
    } else if (stream && !isProcessing) {
      animFrame = requestAnimationFrame(tick);
    }
  });

  // ── Register Service Worker (PWA) ────────────────────────────
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js')
        .then(reg => console.log('SW registered:', reg.scope))
        .catch(err => console.log('SW registration failed:', err));
    });
  }
</script>

</body>
</html>
