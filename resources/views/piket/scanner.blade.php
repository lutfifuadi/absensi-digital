@extends('layouts/layoutMaster')

@section('title', 'Scanner QR Absensi — Guru Piket')

@section('page-style')
  <style>
    /* DESIGN SYSTEM TOKENS & OVERRIDES FOR THEMED INSIDE LAYOUT */
    :root {
      --das-primary: #7367f0;
      --das-success: #28c76f;
      --das-info: #00cfe8;
      --das-warning: #ff9f43;
      --das-danger: #ea5455;
      --das-radius: 5px;
    }

    .scanner-container {
      display: grid;
      grid-template-columns: 1fr;
      min-height: 550px;
      background: #1e1e2f;
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    @media (min-width: 992px) {
      .scanner-container {
        grid-template-columns: 1fr 400px;
      }
    }

    /* CAMERA AREA */
    .camera-panel {
      position: relative;
      background: #000;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      min-height: 350px;
    }

    #video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: none;
    }

    .scan-crosshair {
      position: absolute;
      pointer-events: none;
      display: none;
      z-index: 5;
    }
    .scan-crosshair.active { display: block; }
    .scan-crosshair .frame { width: 220px; height: 220px; position: relative; }
    
    .scan-crosshair .corner { position: absolute; width: 30px; height: 30px; border-color: var(--das-primary); border-style: solid; }
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
      gap: 1rem;
      background: radial-gradient(circle at center, #2b2b40 0%, #14141f 100%);
      z-index: 70;
      padding: 2rem;
    }
    .idle-icon-wrapper {
      width: 64px;
      height: 64px;
      background: rgba(115, 103, 240, 0.1);
      border: 1px solid rgba(115, 103, 240, 0.2);
      border-radius: var(--das-radius);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
    }
    .idle-screen p { color: #a6a6c0; font-size: 0.9rem; text-align: center; max-width: 280px; }
    
    .btn-start {
      background: var(--das-primary);
      color: white;
      border: none;
      padding: 0.65rem 1.3rem;
      border-radius: var(--das-radius);
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 8px 20px rgba(115, 103, 240, 0.3);
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.2s;
    }
    .btn-start:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(115, 103, 240, 0.4); }

    .btn-switch {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: rgba(30, 30, 47, 0.7);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,0.08);
      color: white;
      width: 38px;
      height: 38px;
      border-radius: var(--das-radius);
      display: none;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 40;
    }
    .btn-switch.active { display: flex; }

    /* Result Toast Inside Camera */
    .result-toast {
      position: absolute;
      bottom: 1rem;
      left: 50%;
      transform: translateX(-50%) translateY(20px);
      width: 90%;
      background: rgba(30, 30, 47, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: var(--das-radius);
      padding: 1rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      z-index: 30;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .result-toast.show { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
    .toast-inner { display: flex; align-items: center; gap: 0.8rem; }
    .toast-icon { width: 42px; height: 42px; border-radius: var(--das-radius); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .success .toast-icon { background: rgba(40, 199, 111, 0.1); color: var(--das-success); border: 1px solid rgba(40, 199, 111, 0.2); }
    .warning .toast-icon { background: rgba(255, 159, 67, 0.1); color: var(--das-warning); border: 1px solid rgba(255, 159, 67, 0.2); }
    .error .toast-icon { background: rgba(234, 84, 85, 0.1); color: var(--das-danger); border: 1px solid rgba(234, 84, 85, 0.2); }
    
    .toast-name { font-size: 0.95rem; font-weight: 700; color: white; display: block; }
    .toast-meta { font-size: 0.75rem; color: #a6a6c0; }
    .toast-msg { font-size: 0.8rem; margin-top: 0.1rem; }
    .toast-bar { height: 3px; background: rgba(255,255,255,0.05); border-radius: 1.5px; overflow: hidden; }
    .toast-fill { height: 100%; transition: width linear; }

    /* SIDEBAR PANEL */
    .sidebar-panel {
      background: #151521;
      border-left: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      flex-direction: column;
      min-height: 200px;
    }
    .panel-header {
      padding: 1rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .panel-title { font-size: 0.75rem; font-weight: 700; color: #8c8db0; text-transform: uppercase; letter-spacing: 1px; }
    
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); padding: 1rem; gap: 0.75rem; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
    .stat-box { background: rgba(30, 30, 47, 0.4); border: 1px solid rgba(255, 255, 255, 0.06); padding: 0.75rem; border-radius: var(--das-radius); text-align: center; }
    .stat-val { font-size: 1.5rem; font-weight: 800; display: block; }
    .stat-lbl { font-size: 0.6rem; color: #8c8db0; font-weight: 700; text-transform: uppercase; margin-top: 3px; }

    .log-section { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .log-header { padding: 0.75rem 1rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.04); }
    .log-list { flex: 1; overflow-y: auto; padding: 0.75rem 1rem; display: flex; flex-direction: column; gap: 0.60rem; max-height: 320px; }

    .log-row {
      padding: 0.75rem;
      background: rgba(30, 30, 47, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.04);
      border-radius: var(--das-radius);
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .log-avatar { width: 34px; height: 34px; border-radius: var(--das-radius); background: var(--das-primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; flex-shrink: 0; color: white; }
    .log-avatar.late { background: var(--das-warning); }
    .log-avatar.guru { background: #7367f0 !important; }
    
    .log-details { flex: 1; min-width: 0; }
    .log-name-text { font-size: 0.8rem; font-weight: 700; color: white; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .log-sub-text { font-size: 0.7rem; color: #8c8db0; }
    .log-time-badge { font-size: 0.75rem; font-weight: 700; padding: 0.15rem 0.4rem; border-radius: var(--das-radius); background: rgba(255,255,255,0.05); }

    .guru-tag { display: inline-block; background: rgba(115, 103, 240, 0.15); color: #a89aff; font-size: 0.5rem; font-weight: 800; padding: 1px 4px; border-radius: 3px; letter-spacing: 0.5px; margin-left: 2px; }

    .btn-action-sound { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); color: #8c8db0; width: 32px; height: 32px; border-radius: var(--das-radius); cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .btn-action-sound:hover { border-color: var(--das-primary); color: var(--das-primary); }

    .empty-state { text-align: center; padding: 2rem; opacity: 0.5; }
    .empty-state i { font-size: 2.5rem; margin-bottom: 0.5rem; display: block; }
  </style>
@endsection

@section('content')
  <div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
      <h4 class="py-3 mb-0 fw-bold"><span class="text-muted fw-light">Piket /</span> Scanner QR Absensi</h4>
      <div>
        <button class="btn btn-primary" onclick="openManualInputModal()" title="Input manual NIS atau NIP siswa/guru">
          <i class="ti tabler-keyboard me-1"></i> Input Manual
        </button>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="scanner-container">
        
        <!-- CAMERA MODULE -->
        <div class="camera-panel">
          <video id="video" playsinline muted autoplay></video>
          <canvas id="canvas-hidden" style="display:none;"></canvas>

          <button class="btn-switch" id="switch-btn" title="Ganti Kamera">
            <i class="ti tabler-camera-rotate"></i>
          </button>

          <!-- Scanning Target Frame -->
          <div class="scan-crosshair" id="scan-crosshair">
            <div class="frame">
              <div class="corner tl"></div>
              <div class="corner tr"></div>
              <div class="corner bl"></div>
              <div class="corner br"></div>
              <div class="scan-line"></div>
            </div>
          </div>

          <!-- Overlaid Stats & Clock -->
          <div id="scan-overlay-bar" style="position:absolute;top:0;left:0;right:0;z-index:55;display:flex;align-items:center;justify-content:between;padding:0.7rem 1rem;background:linear-gradient(180deg,rgba(0,0,0,0.8) 0%,transparent 100%);pointer-events:none;opacity:0;transition:opacity 0.3s;width:100%;">
            <div style="display:flex;gap:0.75rem;font-size:0.7rem;font-weight:700;">
              <span style="color:var(--das-success);">Hadir: <b id="ov-hadir">0</b></span>
              <span style="color:var(--das-warning);">Terlambat: <b id="ov-terlambat">0</b></span>
            </div>
            <div style="font-size:0.8rem;font-weight:800;color:white;font-family:monospace;margin-left:auto;" id="ov-clock">--:--:--</div>
          </div>

          <!-- Active/Inactive Idle Screen Screen -->
          <div class="idle-screen" id="idle-screen">
            <div class="idle-icon-wrapper text-primary">
              <i class="ti tabler-qrcode"></i>
            </div>
            <h5 class="text-white fw-bold mb-1">Scanner QR Internal</h5>
            <p>Scanner ini digunakan untuk mencatat kehadiran siswa dan guru secara langsung dari pos piket.</p>
            <div class="alert alert-danger p-2 text-center" id="error-box" style="display:none; font-size:0.8rem; max-width:320px;"></div>
            <button class="btn-start" id="start-btn">
              <i class="ti tabler-player-play"></i> Aktifkan Scanner
            </button>
          </div>

          <!-- Scanner Flash Element -->
          <div id="flash-overlay" style="position:absolute;inset:0;pointer-events:none;z-index:45;transition:opacity 0.1s ease;opacity:0;"></div>

          <!-- Floating Result Alert Inside Scanner -->
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

        <!-- SIDEBAR PANEL -->
        <div class="sidebar-panel">
          <div class="panel-header">
            <span class="panel-title">Statistik Pemindaian</span>
            <button class="btn-action-sound" id="sound-btn" onclick="toggleSoundEffect()" title="Toggle suara feedback">
              <i class="ti tabler-volume"></i>
            </button>
          </div>

          <div class="stats-grid">
            <div class="stat-box">
              <span class="stat-val text-success" id="stat-hadir-count">0</span>
              <span class="stat-lbl">Hadir</span>
            </div>
            <div class="stat-box">
              <span class="stat-val text-warning" id="stat-telat-count">0</span>
              <span class="stat-lbl">Terlambat</span>
            </div>
            <div class="stat-box">
              <span class="stat-val text-primary" id="stat-total-count">0</span>
              <span class="stat-lbl">Total Scan</span>
            </div>
          </div>

          <div class="log-section">
            <div class="log-header">
              <span class="panel-title" style="font-size:0.7rem;">Log Pemindaian Hari Ini</span>
            </div>
            <div class="log-list" id="scan-log-container">
              <div class="empty-state">
                <i class="ti tabler-history text-muted opacity-50"></i>
                <p class="small text-muted mb-0">Belum ada scan masuk hari ini.</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- MODAL INPUT MANUAL -->
  <div class="modal fade" id="modalManualAbsen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
      <div class="modal-content" style="background:#1e1e2d; border:1px solid rgba(255,255,255,0.1); border-radius:5px;">
        <div class="modal-header border-bottom border-light border-opacity-10 py-3">
          <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2">
            <i class="ti tabler-keyboard text-primary"></i> Input Absensi Manual Piket
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4 d-flex flex-column gap-3">
          <div>
            <label class="form-label text-white-50 small">Cari Siswa / Guru</label>
            <input type="text" id="manualQueryInput" class="form-control bg-dark border-light border-opacity-10 text-white" 
              placeholder="Masukkan NIS, NIP atau Nama...">
          </div>
          
          <div id="manualSearchLoader" style="display:none;" class="text-center py-2">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="small text-muted ms-1">Mencari...</span>
          </div>

          <div id="manualListResults" class="list-group" style="max-height:180px; overflow-y:auto; display:none; gap:5px;"></div>

          <div id="manualTargetConfirm" style="display:none; background:rgba(40,199,111,0.06); border:1px solid rgba(40,199,111,0.15); border-radius:5px; padding:1rem;">
            <p class="text-white-50 small mb-1">Konfirmasi Identitas:</p>
            <p class="fw-bold text-white mb-0" id="confirmTargetName">—</p>
            <p class="text-white-50 small mb-3" id="confirmTargetMeta">—</p>
            <button class="btn btn-success w-100 fw-bold" onclick="submitManualPiketAbsen()">
              <i class="ti tabler-check me-1"></i> Konfirmasi Absen
            </button>
          </div>

          <div id="manualNotFoundAlert" style="display:none;" class="alert alert-warning py-2 text-center small mb-0">
            Siswa atau guru tidak ditemukan.
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
  <script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const PROCESS_URL = "{{ route('piket.scanner.process') }}";
    const STATS_URL = "{{ route('piket.scanner.stats') }}";
    const SEARCH_URL = "{{ route('public.scan-qr.search') }}"; // Pakai endpoint search publik yang sudah ada

    // Clock Overlay Update
    function updateClock() {
      const d = new Date();
      const timeStr = String(d.getHours()).padStart(2,'0') + ':' + 
                      String(d.getMinutes()).padStart(2,'0') + ':' + 
                      String(d.getSeconds()).padStart(2,'0');
      const clock = document.getElementById('ov-clock');
      if (clock) clock.textContent = timeStr;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Sound & Notification Varian Settings
    let soundEnabled = true;
    const soundBell = new Audio('/assets/audio/bell.mp3');
    const soundThankYou = new Audio('/assets/audio/terima-kasih.mp3');

    function toggleSoundEffect() {
      soundEnabled = !soundEnabled;
      document.getElementById('sound-btn').innerHTML = soundEnabled ? '<i class="ti tabler-volume"></i>' : '<i class="ti tabler-volume-3"></i>';
    }

    function playBeepSound(type) {
      if (!soundEnabled) return;
      if (type === 'success') {
        soundBell.pause(); soundBell.currentTime = 0;
        soundBell.play().then(() => {
          soundBell.onended = () => {
            setTimeout(() => {
              soundThankYou.pause(); soundThankYou.currentTime = 0;
              soundThankYou.play().catch(() => playSynthesizedBeep(type));
            }, 150);
          };
        }).catch(() => playSynthesizedBeep(type));
      } else {
        playSynthesizedBeep(type);
      }
    }

    function playSynthesizedBeep(type) {
      try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const now = ctx.currentTime;
        
        const osc = ctx.createOscillator();
        const g = ctx.createGain();
        osc.connect(g);
        g.connect(ctx.destination);

        if (type === 'success') {
          osc.frequency.setValueAtTime(523.25, now); // C5
          g.gain.setValueAtTime(0, now);
          g.gain.linearRampToValueAtTime(0.3, now + 0.01);
          g.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
          osc.start(now);
          osc.stop(now + 0.35);
        } else if (type === 'warning') {
          osc.type = 'triangle';
          osc.frequency.setValueAtTime(440, now); // A4
          g.gain.setValueAtTime(0, now);
          g.gain.linearRampToValueAtTime(0.3, now + 0.01);
          g.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
          osc.start(now);
          osc.stop(now + 0.45);
        } else {
          osc.type = 'square';
          osc.frequency.setValueAtTime(220, now); // A3
          g.gain.setValueAtTime(0, now);
          g.gain.linearRampToValueAtTime(0.35, now + 0.01);
          g.gain.linearRampToValueAtTime(0.001, now + 0.25);
          osc.start(now);
          osc.stop(now + 0.3);
        }
      } catch(e) {}
    }

    // Flash Overlay Effect
    function flashScreen(type) {
      const el = document.getElementById('flash-overlay');
      if (!el) return;
      el.style.background = type === 'success' 
        ? 'rgba(255,255,255,0.25)' 
        : type === 'warning' 
          ? 'rgba(255,159,67,0.15)' 
          : 'rgba(234,84,85,0.2)';
      el.style.opacity = '1';
      setTimeout(() => { el.style.opacity = '0'; }, 100);
    }

    // Sync Stats & Recent Logs from Database
    async function syncScannerData() {
      try {
        const resp = await fetch(STATS_URL);
        const data = await resp.json();

        // Update counts
        const totalHadir = data.stats.siswa_hadir + data.stats.guru_hadir;
        const totalTerlambat = data.stats.siswa_terlambat + data.stats.guru_terlambat;
        const totalScan = data.stats.siswa_total + data.stats.guru_total;

        document.getElementById('stat-hadir-count').textContent = totalHadir;
        document.getElementById('stat-telat-count').textContent = totalTerlambat;
        document.getElementById('stat-total-count').textContent = totalScan;

        document.getElementById('ov-hadir').textContent = totalHadir;
        document.getElementById('ov-terlambat').textContent = totalTerlambat;

        renderLogRows(data.recent_logs);
      } catch(e) {
        console.error('Stats sync error:', e);
      }
    }

    function renderLogRows(logs) {
      const container = document.getElementById('scan-log-container');
      if (!container) return;

      if (!logs || logs.length === 0) {
        container.innerHTML = `
          <div class="empty-state">
            <i class="ti tabler-history text-muted opacity-50"></i>
            <p class="small text-muted mb-0">Belum ada scan masuk hari ini.</p>
          </div>
        `;
        return;
      }

      container.innerHTML = logs.map(log => {
        const initials = log.tipe === 'guru' ? '👤' : log.nama.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();
        const avatarClass = log.tipe === 'guru' ? 'guru' : (log.status === 'terlambat' ? 'late' : '');
        const guruBadge = log.tipe === 'guru' ? '<span class="guru-tag">GURU</span>' : '';
        const statusText = log.status === 'terlambat' ? 'Terlambat' : 'Hadir Tepat Waktu';
        const color = log.status === 'terlambat' ? 'var(--das-warning)' : 'var(--das-success)';

        return `
          <div class="log-row">
            <div class="log-avatar ${avatarClass}">${initials}</div>
            <div class="log-details">
              <span class="log-name-text">${log.nama} ${guruBadge}</span>
              <span class="log-sub-text">${log.tipe === 'guru' ? 'Tenaga Pendidik' : 'Kelas ' + log.kelas} · ${statusText}</span>
            </div>
            <div class="log-time-badge" style="color:${color}">${log.jam}</div>
          </div>
        `;
      }).join('');
    }

    // Camera Scanning Logic
    let stream = null, animFrame = null, isProcessing = false;
    let lastQR = '', lastQRTime = 0;
    let currentFacingMode = 'environment';

    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas-hidden');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    const idleScreen = document.getElementById('idle-screen');
    const startBtn = document.getElementById('start-btn');
    const switchBtn = document.getElementById('switch-btn');
    const crosshair = document.getElementById('scan-crosshair');
    const errorBox = document.getElementById('error-box');

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
        document.getElementById('scan-overlay-bar').style.opacity = '1';
        
        if (!animFrame) animFrame = requestAnimationFrame(tick);
        return true;
      } catch(err) {
        let msg = 'Kamera tidak dapat dimulai. ';
        if (['NotAllowedError','PermissionDeniedError'].includes(err.name)) {
          msg += 'Izin kamera ditolak browser.';
        } else if (['NotFoundError','DevicesNotFoundError'].includes(err.name)) {
          msg += 'Kamera tidak ditemukan.';
        } else {
          msg += err.message;
        }
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
        startBtn.innerHTML = '<i class="ti tabler-player-play"></i> Aktifkan Scanner';
      }
    });

    switchBtn.addEventListener('click', async () => {
      currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
      switchBtn.disabled = true;
      await startCamera(currentFacingMode);
      switchBtn.disabled = false;
    });

    function tick() {
      if (!stream) return;
      if (video.readyState >= video.HAVE_ENOUGH_DATA) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);
        const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'attemptBoth' });
        if (code && !isProcessing) {
          const now = Date.now();
          if (code.data !== lastQR || now - lastQRTime > 3000) {
            lastQR = code.data; lastQRTime = now;
            handlePiketScan(code.data);
          }
        }
      }
      animFrame = requestAnimationFrame(tick);
    }

    // Show floating toast inside camera layout
    let toastTimer = null;
    function showFloatingToast(type, name, meta, message) {
      const toast = document.getElementById('result-toast');
      const fill = document.getElementById('toast-fill');
      const iconEl = document.getElementById('toast-icon');

      const icons = {
        success: '<i class="ti tabler-circle-check"></i>',
        warning: '<i class="ti tabler-exclamation-circle"></i>',
        error: '<i class="ti tabler-circle-x"></i>'
      };

      iconEl.innerHTML = icons[type] || icons.error;
      document.getElementById('toast-name').textContent = name;
      document.getElementById('toast-meta').textContent = meta;
      document.getElementById('toast-msg').textContent = message;

      toast.className = `result-toast ${type} show`;
      fill.style.transition = 'none';
      fill.style.width = '0%';
      fill.style.background = `var(--das-${type})`;

      if (toastTimer) clearTimeout(toastTimer);
      requestAnimationFrame(() => {
        fill.style.transition = 'width 2500ms linear';
        fill.style.width = '100%';
      });

      toastTimer = setTimeout(() => {
        toast.classList.remove('show');
        isProcessing = false;
        if (stream && !animFrame) animFrame = requestAnimationFrame(tick);
      }, 2500);
    }

    async function handlePiketScan(qrCode) {
      isProcessing = true;
      if (animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }
      
      try {
        const resp = await fetch(PROCESS_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
          body: JSON.stringify({ qr_code: qrCode }),
        });

        const data = await resp.json();
        if (data.success) {
          flashScreen('success');
          playBeepSound('success');
          const isGuru = data.siswa.kelas === 'GURU';
          const meta = isGuru ? `Tenaga Pendidik · ${data.siswa.jam}` : `Kelas ${data.siswa.kelas} · ${data.siswa.jam}`;
          showFloatingToast('success', data.siswa.nama, meta, data.message);
          syncScannerData();
        } else if (data.already) {
          flashScreen('warning');
          playBeepSound('warning');
          const isGuru = data.siswa?.kelas === 'GURU';
          const meta = data.siswa ? (isGuru ? `Tenaga Pendidik · ${data.siswa.jam}` : `Kelas ${data.siswa.kelas} · ${data.siswa.jam}`) : '';
          showFloatingToast('warning', data.siswa?.nama ?? 'Sudah Scan', meta, data.message);
        } else {
          flashScreen('error');
          playBeepSound('error');
          showFloatingToast('error', 'Gagal', 'Sistem Absensi', data.message || 'QR Code tidak dikenal.');
        }
      } catch(e) {
        flashScreen('error');
        playBeepSound('error');
        showFloatingToast('error', 'Koneksi Error', 'Sistem Absensi', 'Gagal memproses pemindaian.');
      }
    }

    // Visibility Listener to save resources
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        if (animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }
      } else if (stream && !isProcessing) {
        animFrame = requestAnimationFrame(tick);
      }
    });

    // ── Input Manual Piket ──
    let selectedManualTarget = null;
    let searchTimeout = null;

    function openManualInputModal() {
      selectedManualTarget = null;
      document.getElementById('manualQueryInput').value = '';
      document.getElementById('manualListResults').style.display = 'none';
      document.getElementById('manualTargetConfirm').style.display = 'none';
      document.getElementById('manualNotFoundAlert').style.display = 'none';
      const myModal = new bootstrap.Modal(document.getElementById('modalManualAbsen'));
      myModal.show();
      setTimeout(() => document.getElementById('manualQueryInput').focus(), 500);
    }

    document.getElementById('manualQueryInput').addEventListener('input', function() {
      clearTimeout(searchTimeout);
      const q = this.value.trim();
      if (q.length < 2) {
        document.getElementById('manualListResults').style.display = 'none';
        return;
      }
      searchTimeout = setTimeout(() => searchManualTarget(q), 300);
    });

    async function searchManualTarget(q) {
      document.getElementById('manualSearchLoader').style.display = 'block';
      document.getElementById('manualNotFoundAlert').style.display = 'none';
      
      try {
        const resp = await fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`);
        const data = await resp.json();
        const container = document.getElementById('manualListResults');
        
        document.getElementById('manualSearchLoader').style.display = 'none';

        if (!data.results || data.results.length === 0) {
          container.style.display = 'none';
          document.getElementById('manualNotFoundAlert').style.display = 'block';
          document.getElementById('manualTargetConfirm').style.display = 'none';
          return;
        }
        
        container.style.display = 'block';
        container.innerHTML = data.results.map(r => `
          <button type="button" class="list-group-item list-group-item-action text-white border-light border-opacity-10" 
            style="background:#28283d; font-size:0.85rem;"
            onclick="selectManualTarget('${r.nis}', '${r.nama.replace(/'/g, "\\'")}', '${r.kelas}', '${r.tipe}')">
            <div class="d-flex align-items-center gap-2">
              <span>${r.tipe === 'guru' ? '👤' : '🎓'}</span>
              <div>
                <div class="fw-bold">${r.nama}</div>
                <div class="text-white-50" style="font-size:0.7rem;">${r.tipe === 'guru' ? 'GURU' : r.kelas} · ${r.nis}</div>
              </div>
            </div>
          </button>
        `).join('');
      } catch(e) {
        document.getElementById('manualSearchLoader').style.display = 'none';
        console.error('Manual search error:', e);
      }
    }

    function selectManualTarget(nis, nama, kelas, tipe) {
      selectedManualTarget = { nis, nama, kelas, tipe };
      document.getElementById('manualListResults').style.display = 'none';
      document.getElementById('manualTargetConfirm').style.display = 'block';
      document.getElementById('confirmTargetName').textContent = `${nama} ${tipe === 'guru' ? '👤' : '🎓'}`;
      document.getElementById('confirmTargetMeta').textContent = `${tipe === 'guru' ? 'Tenaga Pendidik' : 'Kelas ' + kelas} · NIS/NIP: ${nis}`;
    }

    async function submitManualPiketAbsen() {
      if (!selectedManualTarget) return;
      
      const btn = document.querySelector('#manualTargetConfirm button');
      btn.disabled = true;
      btn.innerHTML = '⏳ Memproses...';
      
      try {
        const resp = await fetch(PROCESS_URL, {
          method: 'POST',
          headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json'},
          body: JSON.stringify({ qr_code: selectedManualTarget.nis }),
        });
        const data = await resp.json();
        
        bootstrap.Modal.getInstance(document.getElementById('modalManualAbsen')).hide();
        
        if (data.success) {
          flashScreen('success');
          playBeepSound('success');
          const isGuru = data.siswa.kelas === 'GURU';
          const meta = isGuru ? `Tenaga Pendidik · ${data.siswa.jam}` : `Kelas ${data.siswa.kelas} · ${data.siswa.jam}`;
          showFloatingToast('success', data.siswa.nama, meta, data.message);
          syncScannerData();
        } else if (data.already) {
          flashScreen('warning');
          playBeepSound('warning');
          const isGuru = data.siswa?.kelas === 'GURU';
          const meta = data.siswa ? (isGuru ? `Tenaga Pendidik · ${data.siswa.jam}` : `Kelas ${data.siswa.kelas} · ${data.siswa.jam}`) : '';
          showFloatingToast('warning', data.siswa?.nama ?? 'Sudah Scan', meta, data.message);
        } else {
          flashScreen('error');
          playBeepSound('error');
          showFloatingToast('error', 'Gagal', 'Sistem Absensi', data.message || 'Gagal memproses absensi.');
        }
      } catch(e) {
        flashScreen('error');
        playBeepSound('error');
        showFloatingToast('error', 'Koneksi Error', 'Sistem Absensi', 'Gagal memproses absensi.');
      }
      
      btn.disabled = false;
      btn.innerHTML = '<i class="ti tabler-check me-1"></i> Konfirmasi Absen';
    }

    // Initial Load & Refresh
    syncScannerData();
    setInterval(syncScannerData, 8000);
  </script>
@endsection
