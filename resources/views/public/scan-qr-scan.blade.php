<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Scan QR Absensi — Guru Piket</title>
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}">
  <style>
    /* DESIGN SYSTEM TOKENS */
    :root {
      --das-primary: #7367f0;
      --das-success: #28c76f;
      --das-info: #00cfe8;
      --das-warning: #ff9f43;
      --das-danger: #ea5455;
      --das-dark-bg: #0f172a;
      --das-panel-bg: rgba(30, 41, 59, 0.7);
      --das-border-color: rgba(255, 255, 255, 0.08);
      --font-family: 'Quicksand', sans-serif;
    }

    /* GLOBAL RESET */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
      height: 100dvh;
      max-height: 100dvh;
      font-family: var(--font-family);
      background: var(--das-dark-bg);
      color: #e2e8f0;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    /* SCROLLBAR */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-thumb { background: var(--das-primary); border-radius: 4px; }

    /* ─── NAVBAR ───────────────────────────────────────────────── */
    .nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.8rem 1.5rem;
      background: rgba(15, 23, 42, 0.8);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--das-border-color);
      flex-shrink: 0;
      z-index: 100;
    }
    .nav-brand {
      display: flex;
      align-items: center;
      gap: 0.8rem;
    }
    .brand-icon {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, var(--das-primary), #6259e8);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      box-shadow: 0 0 15px rgba(115, 103, 240, 0.3);
    }
    .nav-brand h1 { font-size: 1.1rem; font-weight: 800; color: #fff; letter-spacing: -0.5px; margin: 0; }
    .nav-brand p  { font-size: 0.7rem; color: #94a3b8; margin: -2px 0 0 0; }

    .btn-logout {
      background: rgba(234, 84, 85, 0.1);
      border: 1px solid rgba(234, 84, 85, 0.3);
      color: var(--das-danger);
      border-radius: 8px;
      padding: 0.5rem 1rem;
      font-size: 0.8rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-logout:hover { background: var(--das-danger); color: white; border-color: var(--das-danger); }

    /* ─── LAYOUT WRAPPER ──────────────────────────────────────── */
    .layout {
      flex: 1;
      display: grid;
      grid-template-columns: 1fr;
      grid-template-rows: 1fr;
      min-height: 0;
    }

    @media (min-width: 992px) {
      .layout { grid-template-columns: 1fr 450px; }
    }

    /* ─── CAMERA AREA ─────────────────────────────────────────── */
    .camera-panel {
      position: relative;
      background: #000;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    #video { width: 100%; height: 100%; object-fit: cover; display: none; }

    .scan-crosshair {
      position: absolute;
      pointer-events: none;
      display: none;
      z-index: 5;
    }
    .scan-crosshair.active { display: block; }
    .scan-crosshair .frame { width: 280px; height: 280px; position: relative; }
    @media (min-width: 1200px) { .scan-crosshair .frame { width: 400px; height: 400px; } }
    
    .scan-crosshair .corner { position: absolute; width: 40px; height: 40px; border-color: var(--das-primary); border-style: solid; }
    .corner.tl { top: 0; left: 0; border-width: 4px 0 0 4px; border-radius: 4px 0 0 0; }
    .corner.tr { top: 0; right: 0; border-width: 4px 4px 0 0; border-radius: 0 4px 0 0; }
    .corner.bl { bottom: 0; left: 0; border-width: 0 0 4px 4px; border-radius: 0 0 0 4px; }
    .corner.br { bottom: 0; right: 0; border-width: 0 4px 4px 0; border-radius: 0 0 4px 0; }
    
    .scan-line {
      position: absolute;
      left: 3px; right: 3px;
      height: 3px;
      background: linear-gradient(90deg, transparent, var(--das-primary), transparent);
      box-shadow: 0 0 15px var(--das-primary);
      animation: scanLine 2.5s ease-in-out infinite;
    }
    @keyframes scanLine { 0% { top: 5%; } 50% { top: 95%; } 100% { top: 5%; } }

    /* Idle Screen Overlay */
    .idle-screen {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 1.5rem;
      background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
      z-index: 20;
      padding: 2rem;
    }
    .idle-icon-wrapper {
      width: 120px;
      height: 120px;
      background: rgba(115, 103, 240, 0.1);
      border: 1px solid rgba(115, 103, 240, 0.2);
      border-radius: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3.5rem;
      margin-bottom: 0.5rem;
      position: relative;
    }
    .idle-icon-wrapper::after {
      content: '';
      position: absolute;
      inset: -10px;
      border: 2px dashed rgba(115, 103, 240, 0.2);
      border-radius: 40px;
      animation: das_spin 10s linear infinite;
    }
    @keyframes das_spin { 100% { transform: rotate(360deg); } }
    
    .idle-screen p { color: #94a3b8; font-size: 0.9rem; text-align: center; max-width: 320px; line-height: 1.6; }
    
    .btn-start {
      background: var(--das-primary);
      color: white;
      border: none;
      padding: 1rem 2.5rem;
      border-radius: 12px;
      font-weight: 800;
      font-size: 1rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.8rem;
      box-shadow: 0 10px 25px rgba(115, 103, 240, 0.4);
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .btn-start:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 15px 35px rgba(115, 103, 240, 0.5); }
    .btn-start:active { transform: translateY(0) scale(0.98); }

    .btn-switch {
      position: absolute;
      top: 1.5rem;
      right: 1.5rem;
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(8px);
      border: 1px solid var(--das-border-color);
      color: white;
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: none;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      cursor: pointer;
      z-index: 40;
      transition: all 0.2s;
    }
    .btn-switch:hover { background: var(--das-primary); border-color: var(--das-primary); }
    .btn-switch.active { display: flex; }

    /* Result Toast */
    .result-toast {
      position: absolute;
      bottom: 2rem;
      left: 50%;
      transform: translateX(-50%) translateY(40px);
      width: 90%;
      max-width: 450px;
      background: rgba(15, 23, 42, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid var(--das-border-color);
      border-radius: 16px;
      padding: 1.2rem;
      display: flex;
      flex-direction: column;
      gap: 1rem;
      z-index: 30;
      opacity: 0;
      visibility: hidden;
      transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .result-toast.show { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
    
    .toast-inner { display: flex; align-items: start; gap: 1rem; }
    .toast-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
    .success .toast-icon { background: rgba(40, 199, 111, 0.1); color: var(--das-success); border: 1px solid rgba(40, 199, 111, 0.2); }
    .warning .toast-icon { background: rgba(255, 159, 67, 0.1); color: var(--das-warning); border: 1px solid rgba(255, 159, 67, 0.2); }
    .error .toast-icon { background: rgba(234, 84, 85, 0.1); color: var(--das-danger); border: 1px solid rgba(234, 84, 85, 0.2); }
    
    .toast-name { font-size: 1.1rem; font-weight: 800; color: white; display: block; }
    .toast-meta { font-size: 0.8rem; color: #94a3b8; }
    .toast-msg { font-size: 0.85rem; margin-top: 0.2rem; }
    
    .toast-bar { height: 4px; background: rgba(255,255,255,0.05); border-radius: 2px; overflow: hidden; }
    .toast-fill { height: 100%; transition: width linear; }

    /* ─── SIDEBAR ─────────────────────────────────────────────── */
    .sidebar {
      background: var(--das-panel-bg);
      border-left: 1px solid var(--das-border-color);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .sidebar-header { padding: 1.5rem; border-bottom: 1px solid var(--das-border-color); }
    .sidebar-title { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; }
    
    .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); padding: 1rem; gap: 1rem; border-bottom: 1px solid var(--das-border-color); }
    .stat-item { background: rgba(15, 23, 42, 0.4); border: 1px solid var(--das-border-color); padding: 1rem; border-radius: 12px; text-align: center; }
    .stat-num { font-size: 1.8rem; font-weight: 800; display: block; }
    .stat-label { font-size: 0.6rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-top: 5px; }

    .scan-log-header { padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--das-border-color); }
    .scan-log { flex: 1; overflow-y: auto; padding: 1rem 1.5rem; display: flex; flex-direction: column; gap: 0.8rem; }
    
    .log-item {
      padding: 1rem;
      background: rgba(15, 23, 42, 0.3);
      border: 1px solid var(--das-border-color);
      border-radius: 14px;
      display: flex;
      align-items: center;
      gap: 1rem;
      animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: none; } }
    
    .log-avatar { width: 40px; height: 40px; border-radius: 12px; background: var(--das-primary); display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; color: white; border: 1px solid rgba(255,255,255,0.1); }
    .log-avatar.late { background: var(--das-warning); }
    .log-avatar.dup { background: #334155; }
    
    .log-info { flex: 1; min-width: 0; }
    .log-name { font-size: 0.85rem; font-weight: 700; color: white; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .log-kelas { font-size: 0.75rem; color: #64748b; }
    .log-jam { font-size: 0.8rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 6px; background: rgba(255,255,255,0.05); }

    .log-empty { text-align: center; padding: 3rem; opacity: 0.4; }
    .log-empty i { font-size: 3.5rem; margin-bottom: 1rem; display: block; opacity: 0.2; }
    
    .btn-sound { background: rgba(255,255,255,0.05); border: 1px solid var(--das-border-color); color: #64748b; width: 34px; height: 34px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .btn-sound:hover { border-color: var(--das-primary); color: var(--das-primary); }

    @media (max-width: 991px) {
      .sidebar { border-left: none; border-top: 1px solid var(--das-border-color); max-height: 40%; }
      .nav { padding: 0.6rem 1rem; }
      .brand-icon { width: 34px; height: 34px; font-size: 1rem; }
      .nav-brand h1 { font-size: 0.9rem; }
      .nav-brand p { font-size: 0.6rem; }
    }
  </style>
</head>
<body>

<!-- ── NAVBAR ──────────────────────────────────────────────────── -->
<nav class="nav">
  <div class="nav-brand">
    <div class="brand-icon"><i class="ti tabler-camera"></i></div>
    <div>
      <h1>Scan QR Absensi</h1>
      <p>Pintu Gerbang & Guru Piket</p>
    </div>
  </div>
  <div class="nav-right" style="display:flex; align-items:center; gap:1.5rem;">
    <div id="nav-date" style="font-size:0.8rem; font-weight:700; color:#94a3b8; display:none;"></div>
    <form action="{{ route('public.scan-qr.logout') }}" method="POST" style="margin:0;">
      @csrf
      <button type="submit" class="btn-logout"><i class="ti tabler-logout me-1"></i> Keluar</button>
    </form>
  </div>
</nav>

<!-- ── LAYOUT ─────────────────────────────────────────────────── -->
<div class="layout">

  <!-- ── CAMERA PANEL ──────────────────────────────────────────── -->
  <div class="camera-panel">
    <video id="video" playsinline muted autoplay></video>
    <canvas id="canvas-hidden" style="display:none;"></canvas>

    <button class="btn-switch" id="switch-btn" title="Ganti Kamera">
      <i class="ti tabler-camera-rotate"></i>
    </button>

    <!-- Scan crosshair (shown when cam active) -->
    <div class="scan-crosshair" id="scan-crosshair">
      <div class="frame">
        <div class="corner tl"></div>
        <div class="corner tr"></div>
        <div class="corner bl"></div>
        <div class="corner br"></div>
        <div class="scan-line"></div>
      </div>
    </div>

    <!-- Idle screen -->
    <div class="idle-screen" id="idle-screen">
      <div class="idle-icon-wrapper">
        <i class="ti tabler-qrcode"></i>
      </div>
      <p>Akses kamera dibutuhkan untuk memulai pemindaian kartu siswa.</p>
      <div class="error-box" id="error-box" style="display:none; background:rgba(234,84,85,0.1); border:1px solid var(--das-danger); color:var(--das-danger); padding:1rem; border-radius:12px; font-size:0.85rem; max-width:300px; text-align:center;"></div>
      <button class="btn-start" id="start-btn">
        <i class="ti tabler-player-play"></i> Aktifkan Scanner
      </button>
    </div>

    <!-- Result toast (bottom of camera) -->
    <div class="result-toast" id="result-toast">
      <div class="toast-inner">
        <div class="toast-icon" id="toast-icon"><i class="ti tabler-check"></i></div>
        <div style="flex:1; min-width:0;">
          <div class="toast-name" id="toast-name">—</div>
          <div class="toast-meta" id="toast-meta">—</div>
          <div class="toast-msg"  id="toast-msg">—</div>
        </div>
      </div>
      <div class="toast-bar"><div class="toast-fill" id="toast-fill"></div></div>
    </div>
  </div>

  <!-- ── SIDEBAR ────────────────────────────────────────────────── -->
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-title">Rekap Scan Sesi Ini</div>
    </div>

    <div class="stats-row">
      <div class="stat-item">
        <div class="stat-num" id="stat-success" style="color:var(--das-success);">0</div>
        <div class="stat-label">Hadir</div>
      </div>
      <div class="stat-item">
        <div class="stat-num" id="stat-dup" style="color:var(--das-warning);">0</div>
        <div class="stat-label">Sudah</div>
      </div>
      <div class="stat-item">
        <div class="stat-num" id="stat-fail" style="color:var(--das-danger);">0</div>
        <div class="stat-label">Error</div>
      </div>
    </div>

    <div class="scan-log-header">
      <div class="scan-log-title">Log Kedatangan</div>
      <button class="btn-sound" id="sound-btn" onclick="toggleSound()" title="Toggle suara"><i class="ti tabler-volume"></i></button>
    </div>

    <div class="scan-log" id="scan-log">
      <div class="log-empty">
        <i class="ti tabler-history"></i>
        <p style="font-size:0.85rem;">Menunggu aktivitas scan...</p>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
  // ── Config ──
  const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
  const SCAN_URL  = "{{ route('public.scan-qr.process') }}";
  const LOGIN_URL = "{{ route('public.scan-qr.index') }}";
  const DISMISS   = 3000;
  const DEBOUNCE  = 3500;

  // ── Date ──
  const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  function updateNavDate() {
    const d = new Date();
    document.getElementById('nav-date').textContent =
      `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
  }
  updateNavDate();

  // ── Sound ──
  let soundEnabled = true;

  // Varian notifikasi dari pengaturan server
  const varianSuara = '{{ Illuminate\Support\Facades\DB::table("pengaturan")->where("key","varian_notifikasi_suara")->value("value") ?? "default" }}';

  // Preload Audio Files
  const soundBell     = new Audio('/assets/audio/bell.mp3');
  const soundThankYou = new Audio('/assets/audio/terima-kasih.mp3');

  function toggleSound() {
    soundEnabled = !soundEnabled;
    document.getElementById('sound-btn').textContent = soundEnabled ? '🔊' : '🔇';
  }

  function beep(type) {
    if (!soundEnabled) return;

    if (type === 'success') {
      switch (varianSuara) {
        case 'default':
          // Bel + Terima Kasih
          soundBell.pause(); soundBell.currentTime = 0;
          soundBell.play().then(() => {
            soundBell.onended = () => {
              setTimeout(() => {
                soundThankYou.pause(); soundThankYou.currentTime = 0;
                soundThankYou.play().catch(() => playLegacyBeep(type));
              }, 200);
            };
          }).catch(() => playLegacyBeep(type));
          return;
        case 'beep':
          playLegacyBeep('success'); return;
        case 'chime':
          playChime(); return;
        case 'soft':
          playSoftBell(); return;
        case 'digital':
          playDigitalBeep(); return;
        default:
          playLegacyBeep(type); return;
      }
    }
    playLegacyBeep(type);
  }

  function playChime() {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      const now = ctx.currentTime;
      [[880,0],[1108,0.15],[1318,0.30],[1760,0.45]].forEach(([f,t]) => {
        const osc = ctx.createOscillator(), g = ctx.createGain();
        osc.frequency.value = f; osc.type = 'sine';
        osc.connect(g); g.connect(ctx.destination);
        g.gain.setValueAtTime(0, now+t);
        g.gain.linearRampToValueAtTime(0.4, now+t+0.01);
        g.gain.exponentialRampToValueAtTime(0.001, now+t+0.6);
        osc.start(now+t); osc.stop(now+t+0.65);
      });
    } catch(_) {}
  }

  function playSoftBell() {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      const now = ctx.currentTime;
      [523.25, 659.25].forEach((f, i) => {
        const osc = ctx.createOscillator(), g = ctx.createGain();
        osc.frequency.value = f; osc.type = 'sine';
        osc.connect(g); g.connect(ctx.destination);
        const t = now + i * 0.25;
        g.gain.setValueAtTime(0, t);
        g.gain.linearRampToValueAtTime(0.25, t+0.01);
        g.gain.exponentialRampToValueAtTime(0.001, t+0.8);
        osc.start(t); osc.stop(t+0.85);
      });
    } catch(_) {}
  }

  function playDigitalBeep() {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      const now = ctx.currentTime;
      [0, 0.1, 0.2].forEach(t => {
        const osc = ctx.createOscillator(), g = ctx.createGain();
        osc.frequency.value = 1200; osc.type = 'square';
        osc.connect(g); g.connect(ctx.destination);
        g.gain.setValueAtTime(0.3, now+t);
        g.gain.linearRampToValueAtTime(0, now+t+0.08);
        osc.start(now+t); osc.stop(now+t+0.09);
      });
    } catch(_) {}
  }

  function playLegacyBeep(type) {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();

      function tone(freq, t0, dur, vol = 0.45, shape = 'bell') {
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
        tone(523.25, now, 0.55, 0.45, 'bell');
        tone(1046.5, now, 0.45, 0.12, 'bell');
        tone(659.25, now + 0.22, 0.65, 0.45, 'bell');
        tone(1318.5, now + 0.22, 0.55, 0.10, 'bell');
      } else if (type === 'error') {
        tone(330, now, 0.18, 0.40, 'square');
        tone(220, now + 0.22, 0.22, 0.40, 'square');
      } else {
        tone(440, now, 0.30, 0.35, 'bell');
      }
    } catch(_) {}
  }

  // ── Counters ──
  let cntSuccess = 0, cntDup = 0, cntFail = 0;
  function incrStat(type) {
    if (type === 'success') { cntSuccess++; document.getElementById('stat-success').textContent = cntSuccess; }
    else if (type === 'warning') { cntDup++; document.getElementById('stat-dup').textContent = cntDup; }
    else { cntFail++; document.getElementById('stat-fail').textContent = cntFail; }
  }

  // ── Scan log ──
  function addLog(type, siswa, msg) {
    const log = document.getElementById('scan-log');
    // Remove empty state
    const empty = log.querySelector('.log-empty');
    if (empty) empty.remove();

    const item = document.createElement('div');
    item.className = 'log-item';

    const initials = siswa?.nama ? siswa.nama.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase() : '?';
    const avatarClass = type === 'success' ? '' : (type === 'warning' ? 'dup' : 'dup');
    const jam = siswa?.jam ?? new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});

    item.innerHTML = `
      <div class="log-avatar ${avatarClass}">${initials}</div>
      <div class="log-info">
        <span class="log-name">${siswa?.nama ?? (type === 'error' ? 'Gagal Pindai' : 'Informasi')}</span>
        <span class="log-kelas">${siswa?.kelas ? 'Kelas ' + siswa.kelas : msg.substring(0, 38)}</span>
      </div>
      <div class="log-jam" style="color:var(--das-${type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')})">${jam}</div>
    `;
    log.insertBefore(item, log.firstChild);
    // Keep max 20 items for performance
    while (log.children.length > 20) log.removeChild(log.lastChild);
  }

  // ── Toast ──
  let toastTimer = null;
  function showToast(type, siswa, msg) {
    const toast = document.getElementById('result-toast');
    const fill  = document.getElementById('toast-fill');
    const iconEl = document.getElementById('toast-icon');

    const icons = {
      success: '<i class="ti tabler-circle-check"></i>',
      warning: '<i class="ti tabler-exclamation-circle"></i>',
      error: '<i class="ti tabler-circle-x"></i>'
    };

    iconEl.innerHTML = icons[type] || icons.error;
    document.getElementById('toast-name').textContent = siswa?.nama ?? (type === 'error' ? 'Sistem Error' : 'Perhatian');
    document.getElementById('toast-meta').textContent = siswa?.kelas ? `Kelas ${siswa.kelas} · ${siswa.jam}` : '';
    document.getElementById('toast-msg').textContent  = msg;

    toast.className = `result-toast ${type} show`;
    fill.style.transition = 'none';
    fill.style.width = '0%';
    fill.style.background = `var(--das-${type})`;

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

  // ── Camera vars ──
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
  const errorBox    = document.getElementById('error-box');

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
      let msg = 'Tidak dapat memulai kamera. ';
      if (['NotAllowedError','PermissionDeniedError'].includes(err.name)) msg += 'Izin kamera ditolak. Izinkan di pengaturan browser.';
      else if (['NotFoundError','DevicesNotFoundError'].includes(err.name)) msg += 'Kamera tidak ditemukan di perangkat ini.';
      else if (err.name === 'NotReadableError') msg += 'Kamera sedang dipakai aplikasi lain.';
      else msg += err.message;
      
      errorBox.textContent = msg;
      errorBox.style.display = 'block';
      return false;
    }
  }

  startBtn.addEventListener('click', async () => {
    startBtn.disabled = true;
    startBtn.innerHTML = '⏳ Memulai...';
    errorBox.style.display = 'none';
    
    const success = await startCamera(currentFacingMode);
    if (!success) {
      startBtn.disabled = false;
      startBtn.innerHTML = '📷 Coba Lagi';
    }
  });

  switchBtn.addEventListener('click', async () => {
    currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
    switchBtn.disabled = true;
    switchBtn.innerHTML = '<i class="ti tabler-refresh spin"></i>';
    
    await startCamera(currentFacingMode);
    
    switchBtn.disabled = false;
    switchBtn.innerHTML = '<i class="ti tabler-camera-rotate"></i>';
  });

  // ── Scan tick ──
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

  // ── Handle scan ──
  async function handleScan(qrCode) {
    isProcessing = true;
    if (animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }
    try {
      const resp = await fetch(SCAN_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ qr_code: qrCode }),
      });

      if (resp.redirected || resp.status === 419) {
        showToast('error', null, 'Sesi habis. Mengarahkan ke login...');
        setTimeout(() => window.location.href = LOGIN_URL, 2500);
        return;
      }

      const data = await resp.json();
      if (data.success) {
        beep('success');
        incrStat('success');
        addLog('success', data.siswa, data.message);
        showToast('success', data.siswa, data.message);
        if (navigator.vibrate) navigator.vibrate([80]);
      } else if (data.already) {
        beep('warning');
        incrStat('warning');
        addLog('warning', data.siswa, data.message);
        showToast('warning', data.siswa, data.message);
        if (navigator.vibrate) navigator.vibrate([80, 60, 80]);
      } else {
        beep('error');
        incrStat('error');
        addLog('error', null, data.message ?? 'QR tidak dikenal.');
        showToast('error', null, data.message ?? 'QR tidak dikenal.');
      }
    } catch(e) {
      beep('error');
      incrStat('error');
      showToast('error', null, 'Gagal terhubung ke server.');
    }
  }

  // Pause on hidden tab
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      if (animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }
    } else if (stream && !isProcessing) {
      animFrame = requestAnimationFrame(tick);
    }
  });
</script>
</body>
</html>
