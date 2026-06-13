<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <title>Scan QR — Pelepasan Kelas XII</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

    <!-- html5-qrcode -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <style>
        :root {
            --primary: #7367f0;
            --primary-glow: rgba(115, 103, 240, 0.35);
            --success: #28c76f;
            --success-glow: rgba(40, 199, 111, 0.35);
            --warning: #ff9f43;
            --danger: #ea5455;
            --bg: #0f172a;
            --bg2: #1e293b;
            --border: rgba(255,255,255,0.08);
            --glass: rgba(15, 23, 42, 0.8);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: #f1f5f9;
            min-height: 100dvh;
            overflow-x: hidden;
        }

        /* ── HEADER ─────────────────────────────────────── */
        .mob-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 0.9rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .mob-header__left {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .mob-header__icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary) 0%, #a55eea 100%);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            box-shadow: 0 0 14px var(--primary-glow);
        }

        .mob-header__title {
            font-size: 0.9rem;
            font-weight: 700;
        }

        .mob-header__sub {
            font-size: 0.68rem;
            color: #64748b;
            margin-top: 1px;
        }

        /* ── COUNTER BAR ─────────────────────────────────── */
        .counter-bar {
            display: flex;
            gap: 0.75rem;
            padding: 0.9rem 1.25rem;
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
        }

        .counter-pill {
            flex: 1;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 0.6rem 0.75rem;
            text-align: center;
        }

        .counter-pill__val {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success);
            text-shadow: 0 0 10px var(--success-glow);
            line-height: 1;
        }

        .counter-pill__val.neutral {
            color: #64748b;
            text-shadow: none;
        }

        .counter-pill__label {
            font-size: 0.65rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }

        /* ── CAMERA BOX ──────────────────────────────────── */
        .camera-wrapper {
            padding: 1.25rem;
            position: relative;
        }

        .camera-container {
            width: 100%;
            border-radius: 5px;
            overflow: hidden;
            background: #000;
            position: relative;
            aspect-ratio: 1 / 1;
            max-width: 400px;
            margin: 0 auto;
        }

        #reader {
            width: 100% !important;
            border: none !important;
        }

        /* Override html5-qrcode default styles */
        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            border-radius: 16px;
        }

        #reader img { display: none !important; }
        #reader__dashboard { display: none !important; }

        /* Corner scan frame overlay */
        .scan-frame {
            position: absolute;
            inset: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .scan-frame__box {
            width: 55%;
            aspect-ratio: 1;
            position: relative;
        }

        .scan-frame__box::before,
        .scan-frame__box::after,
        .scan-frame__corner-br,
        .scan-frame__corner-bl {
            content: '';
            position: absolute;
            width: 28px;
            height: 28px;
            border-color: var(--primary);
            border-style: solid;
        }

        .scan-frame__box::before {
            top: 0; left: 0;
            border-width: 3px 0 0 3px;
            border-radius: 4px 0 0 0;
        }

        .scan-frame__box::after {
            top: 0; right: 0;
            border-width: 3px 3px 0 0;
            border-radius: 0 4px 0 0;
        }

        .scan-frame__corner-br {
            bottom: 0; right: 0;
            border-width: 0 3px 3px 0;
            border-radius: 0 0 4px 0;
        }

        .scan-frame__corner-bl {
            bottom: 0; left: 0;
            border-width: 0 0 3px 3px;
            border-radius: 0 0 0 4px;
        }

        /* Scanning line animation */
        .scan-line {
            position: absolute;
            left: 8%;
            right: 8%;
            height: 2px;
            background: linear-gradient(to right, transparent, var(--primary), transparent);
            box-shadow: 0 0 8px var(--primary);
            top: 50%;
            animation: scanLine 2s ease-in-out infinite;
        }

        @keyframes scanLine {
            0%   { top: 10%; opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { top: 90%; opacity: 0; }
        }

        /* ── START / STOP BUTTON ─────────────────────────── */
        .camera-controls {
            padding: 0 1.25rem 1.25rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .btn-scanner {
            width: 100%;
            padding: 0.85rem;
            border-radius: 5px;
            border: none;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-scanner.start {
            background: linear-gradient(135deg, var(--primary) 0%, #a55eea 100%);
            color: #fff;
            box-shadow: 0 4px 20px var(--primary-glow);
        }

        .btn-scanner.start:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 24px var(--primary-glow);
        }

        .btn-scanner.stop {
            background: rgba(234, 84, 85, 0.1);
            border: 1px solid rgba(234, 84, 85, 0.3);
            color: var(--danger);
        }

        .btn-scanner.stop:hover {
            background: rgba(234, 84, 85, 0.18);
        }

        /* ── MANUAL INPUT ────────────────────────────────── */
        .manual-section {
            padding: 0 1.25rem 1.25rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .manual-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
            font-size: 0.72rem;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .manual-divider::before,
        .manual-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .manual-row {
            display: flex;
            gap: 0.5rem;
        }

        .manual-input {
            flex: 1;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 0.7rem 0.9rem;
            color: #f1f5f9;
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .manual-input:focus {
            border-color: var(--primary);
        }

        .manual-input::placeholder {
            color: #475569;
        }

        .btn-manual-submit {
            background: var(--primary);
            border: none;
            border-radius: 5px;
            padding: 0 1rem;
            color: #fff;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-manual-submit:hover {
            background: #8b7cf8;
        }

        /* ── RECENT LOG ──────────────────────────────────── */
        .recent-section {
            padding: 0 1.25rem 1.5rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .recent-header {
            font-size: 0.72rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 0.7rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .recent-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .recent-item {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 0.7rem 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(8px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }

        .recent-item__avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, #a55eea 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .recent-item__info {
            flex: 1;
            min-width: 0;
        }

        .recent-item__name {
            font-weight: 700;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .recent-item__meta {
            font-size: 0.72rem;
            color: #64748b;
            margin-top: 1px;
        }

        .recent-item__time {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.75rem;
            color: #a5a2f7;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-hadir {
            font-size: 0.65rem;
            background: rgba(40, 199, 111, 0.15);
            border: 1px solid rgba(40, 199, 111, 0.3);
            color: var(--success);
            padding: 2px 7px;
            border-radius: 3px;
            font-weight: 700;
        }

        .badge-duplikat {
            font-size: 0.65rem;
            background: rgba(255, 159, 67, 0.15);
            border: 1px solid rgba(255, 159, 67, 0.3);
            color: var(--warning);
            padding: 2px 7px;
            border-radius: 3px;
            font-weight: 700;
        }

        /* ── TOAST ───────────────────────────────────────── */
        #toastContainer {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            width: 90%;
            max-width: 380px;
            pointer-events: none;
        }

        .toast-msg {
            width: 100%;
            background: rgba(15, 23, 42, 0.97);
            backdrop-filter: blur(16px);
            border-radius: 5px;
            padding: 0.9rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.08);
            animation: toastIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        .toast-msg.success { border-left: 3px solid var(--success); }
        .toast-msg.warning { border-left: 3px solid var(--warning); }
        .toast-msg.danger  { border-left: 3px solid var(--danger); }

        .toast-icon { font-size: 1.5rem; flex-shrink: 0; }
        .toast-name { font-weight: 700; font-size: 0.9rem; }
        .toast-sub  { font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-top: 2px; }

        @keyframes toastIn {
            from { transform: translateY(-12px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }

        @keyframes toastOut {
            from { transform: translateY(0); opacity: 1; }
            to   { transform: translateY(-12px); opacity: 0; }
        }

        /* ── CAMERA PLACEHOLDER ──────────────────────────── */
        .camera-placeholder {
            width: 100%;
            aspect-ratio: 1;
            max-width: 400px;
            margin: 0 auto;
            border-radius: 5px;
            background: rgba(255,255,255,0.03);
            border: 1px dashed var(--border);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            color: #475569;
        }

        .camera-placeholder i {
            font-size: 3.5rem;
            color: #334155;
        }

        .camera-placeholder p {
            font-size: 0.85rem;
            text-align: center;
        }

        /* ── SCAN SUCCESS OVERLAY ────────────────────────── */
        #successOverlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(8px);
            z-index: 999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 2rem;
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(40, 199, 111, 0.15);
            border: 2px solid var(--success);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--success);
            box-shadow: 0 0 30px var(--success-glow);
            animation: popIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes popIn {
            from { transform: scale(0.5); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }

        .success-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .success-class {
            font-size: 1rem;
            color: #94a3b8;
        }

        .success-badge {
            background: rgba(40, 199, 111, 0.12);
            border: 1px solid rgba(40, 199, 111, 0.3);
            color: var(--success);
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.35rem 1rem;
            border-radius: 5px;
        }

        .success-time {
            font-size: 0.85rem;
            color: #64748b;
        }

        /* ── CAMERA STATUS ───────────────────────────────── */
        .camera-status {
            text-align: center;
            padding: 0.6rem;
            font-size: 0.78rem;
            color: #475569;
            max-width: 400px;
            margin: 0 auto;
        }

        .status-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 6px var(--success);
            animation: blink 1.5s infinite;
            margin-right: 5px;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
    </style>
</head>
<body>

    <!-- TOAST CONTAINER -->
    <div id="toastContainer"></div>

    <!-- SUCCESS OVERLAY (full-screen flash) -->
    <div id="successOverlay">
        <div class="success-icon"><i class="ti tabler-check"></i></div>
        <div class="success-badge">✅ Kehadiran Tercatat</div>
        <div class="success-name" id="overlayName">—</div>
        <div class="success-class" id="overlayClass">—</div>
        <div class="success-time" id="overlayTime">—</div>
    </div>

    <!-- HEADER -->
    <div class="mob-header">
        <div class="mob-header__left">
            <div class="mob-header__icon">
                <i class="ti tabler-qrcode"></i>
            </div>
            <div>
                <div class="mob-header__title">Scan QR Pelepasan</div>
                <div class="mob-header__sub">{{ $kegiatan->tahunAkademik->nama ?? '2025/2026' }} — MAN 1 Kota Bandung</div>
            </div>
        </div>
        <a href="{{ route('admin.pelepasan.index') }}" style="color:#64748b; text-decoration:none; font-size:1.3rem;">
            <i class="ti tabler-arrow-left"></i>
        </a>
    </div>

    <!-- COUNTER BAR -->
    <div class="counter-bar">
        <div class="counter-pill">
            <div class="counter-pill__val" id="ctrHadir">{{ $totalHadir }}</div>
            <div class="counter-pill__label">Hadir</div>
        </div>
        <div class="counter-pill">
            <div class="counter-pill__val" id="ctrBelum" style="color:#64748b;text-shadow:none;">{{ $totalSiswa - $totalHadir }}</div>
            <div class="counter-pill__label">Belum Hadir</div>
        </div>
        <div class="counter-pill">
            <div class="counter-pill__val neutral" id="ctrTotal">{{ $totalSiswa }}</div>
            <div class="counter-pill__label">Total Siswa</div>
        </div>
    </div>

    <!-- CAMERA SECTION -->
    <div class="camera-wrapper">
        <!-- placeholder when camera is off -->
        <div class="camera-placeholder" id="cameraPlaceholder">
            <i class="ti tabler-camera-off"></i>
            <p>Kamera belum aktif<br><span style="font-size:0.78rem;">Tekan tombol di bawah untuk mulai scan</span></p>
        </div>

        <!-- camera viewport -->
        <div class="camera-container" id="cameraContainer" style="display:none;">
            <div id="reader"></div>
            <div class="scan-frame">
                <div class="scan-frame__box">
                    <span class="scan-frame__corner-br"></span>
                    <span class="scan-frame__corner-bl"></span>
                </div>
                <div class="scan-line"></div>
            </div>
        </div>

        <div class="camera-status" id="cameraStatus" style="display:none;">
            <span class="status-dot"></span> Kamera aktif — arahkan ke QR code siswa
        </div>
    </div>

    <!-- START / STOP BUTTON -->
    <div class="camera-controls">
        <button id="toggleCameraBtn" class="btn-scanner start">
            <i class="ti tabler-camera"></i> Mulai Kamera
        </button>
    </div>

    <!-- MANUAL INPUT -->
    <div class="manual-section">
        <div class="manual-divider">atau input NISN manual</div>
        <div class="manual-row">
            <input type="text" id="manualInput" class="manual-input"
                   placeholder="Ketik NISN / NIS / QR Code..."
                   inputmode="numeric" autocomplete="off">
            <button id="manualSubmitBtn" class="btn-manual-submit">
                <i class="ti tabler-send"></i>
            </button>
        </div>
    </div>

    <!-- RECENT LOG -->
    <div class="recent-section">
        <div class="recent-header">
            <i class="ti tabler-history"></i> Scan Terbaru Sesi Ini
        </div>
        <div class="recent-list" id="recentList">
            <div style="text-align:center; color:#334155; font-size:0.8rem; padding:1.5rem 0;" id="recentEmpty">
                Belum ada scan dalam sesi ini.
            </div>
        </div>
    </div>

    <script>
        const SCAN_URL  = "{{ route('admin.pelepasan.scan.store') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";

        let html5QrCode = null;
        let isCameraOn  = false;
        let lastScanned = '';
        let lastScannedTime = 0;
        const DEBOUNCE_MS = 3000; // prevent duplicate scan within 3s

        // ── DOM Refs ────────────────────────────────────────────
        const toggleBtn        = document.getElementById('toggleCameraBtn');
        const cameraPlaceholder= document.getElementById('cameraPlaceholder');
        const cameraContainer  = document.getElementById('cameraContainer');
        const cameraStatus     = document.getElementById('cameraStatus');
        const manualInput      = document.getElementById('manualInput');
        const manualSubmitBtn  = document.getElementById('manualSubmitBtn');
        const recentList       = document.getElementById('recentList');
        const recentEmpty      = document.getElementById('recentEmpty');
        const successOverlay   = document.getElementById('successOverlay');
        const ctrHadir         = document.getElementById('ctrHadir');
        const ctrBelum         = document.getElementById('ctrBelum');
        const ctrTotal         = document.getElementById('ctrTotal');

        // ── Camera Toggle ────────────────────────────────────────
        toggleBtn.addEventListener('click', () => {
            if (isCameraOn) {
                stopCamera();
            } else {
                startCamera();
            }
        });

        function startCamera() {
            cameraPlaceholder.style.display = 'none';
            cameraContainer.style.display   = 'block';
            cameraStatus.style.display      = 'block';

            toggleBtn.className = 'btn-scanner stop';
            toggleBtn.innerHTML = '<i class="ti tabler-camera-off"></i> Matikan Kamera';

            html5QrCode = new Html5Qrcode("reader");

            Html5Qrcode.getCameras().then(cameras => {
                if (!cameras || cameras.length === 0) {
                    showToast('danger', '📵', 'Kamera tidak ditemukan', 'Pastikan izin kamera sudah diberikan');
                    stopCamera();
                    return;
                }

                // Prefer back camera
                const backCam = cameras.find(c => c.label.toLowerCase().includes('back') || c.label.toLowerCase().includes('belakang') || c.label.toLowerCase().includes('rear'));
                const camId = backCam ? backCam.id : cameras[cameras.length - 1].id;

                html5QrCode.start(
                    camId,
                    {
                        fps: 15,
                        qrbox: { width: 240, height: 240 },
                        aspectRatio: 1.0,
                        disableFlip: false,
                    },
                    (decodedText) => {
                        const now = Date.now();
                        // Debounce same code
                        if (decodedText === lastScanned && (now - lastScannedTime) < DEBOUNCE_MS) return;
                        lastScanned = decodedText;
                        lastScannedTime = now;

                        doScan(decodedText);
                    },
                    () => {} // ignore scan failure callbacks
                ).catch(err => {
                    console.error(err);
                    showToast('danger', '❌', 'Gagal akses kamera', err.message || 'Coba izinkan akses kamera di browser');
                    stopCamera();
                });

                isCameraOn = true;

            }).catch(() => {
                showToast('danger', '❌', 'Tidak bisa membuka kamera', 'Pastikan izin kamera diberikan di browser');
                stopCamera();
            });
        }

        function stopCamera() {
            isCameraOn = false;
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                    html5QrCode = null;
                }).catch(() => {});
            }
            cameraPlaceholder.style.display = 'flex';
            cameraContainer.style.display   = 'none';
            cameraStatus.style.display      = 'none';
            toggleBtn.className = 'btn-scanner start';
            toggleBtn.innerHTML = '<i class="ti tabler-camera"></i> Mulai Kamera';
        }

        // ── Notifikasi Suara ────────────────────────────────────
        function playChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();
                let osc1 = ctx.createOscillator();
                let gain1 = ctx.createGain();
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.type = 'sine';
                osc1.frequency.value = 659.25;
                gain1.gain.setValueAtTime(0.1, ctx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                osc1.start();
                osc1.stop(ctx.currentTime + 0.3);
                let osc2 = ctx.createOscillator();
                let gain2 = ctx.createGain();
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.type = 'sine';
                osc2.frequency.value = 880.00;
                gain2.gain.setValueAtTime(0.1, ctx.currentTime + 0.15);
                gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.55);
                osc2.start(ctx.currentTime + 0.15);
                osc2.stop(ctx.currentTime + 0.55);
            } catch (e) { console.error("Audio failure: ", e); }
        }

        // ── Scan Logic ───────────────────────────────────────────
        function doScan(qrCode) {
            fetch(SCAN_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ qr_code: qrCode })
            })
            .then(async res => {
                const data = await res.json();

                if (res.ok) {
                    if (data.is_new) {
                        playChime();
                        showSuccessOverlay(data);
                        showToast('success', '✅', data.siswa_nama, `${data.siswa_kelas} · ${data.waktu}`);
                        updateCounters(data.total_hadir, parseInt(ctrTotal.textContent));
                    } else {
                        showToast('warning', '⚠️', data.siswa_nama, 'Sudah scan sebelumnya');
                    }
                    addToRecentList(data, data.is_new);
                } else {
                    showToast('danger', '❌', 'Scan Gagal', data.message || 'Kartu tidak dikenal');
                }
            })
            .catch(() => {
                showToast('danger', '❌', 'Error', 'Gagal menghubungi server');
            });
        }

        // ── Manual Input ─────────────────────────────────────────
        manualSubmitBtn.addEventListener('click', submitManual);
        manualInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') submitManual();
        });

        function submitManual() {
            const val = manualInput.value.trim();
            if (!val) return;
            manualInput.value = '';
            doScan(val);
        }

        // ── Success Overlay ───────────────────────────────────────
        let overlayTimeout;

        function showSuccessOverlay(data) {
            document.getElementById('overlayName').textContent  = data.siswa_nama;
            document.getElementById('overlayClass').textContent = data.siswa_kelas;
            document.getElementById('overlayTime').textContent  = '🕐 ' + data.waktu + ' WIB';

            successOverlay.style.display = 'flex';
            clearTimeout(overlayTimeout);
            overlayTimeout = setTimeout(() => {
                successOverlay.style.display = 'none';
            }, 2500);
        }

        successOverlay.addEventListener('click', () => {
            successOverlay.style.display = 'none';
        });

        // ── Recent List ───────────────────────────────────────────
        function addToRecentList(data, isNew) {
            if (recentEmpty) recentEmpty.remove();

            const now  = new Date();
            const time = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;
            const initials = data.siswa_nama.split(' ').slice(0,2).map(w => w[0]).join('').toUpperCase();
            const badge = isNew
                ? `<span class="badge-hadir">Hadir</span>`
                : `<span class="badge-duplikat">Sudah Scan</span>`;

            const item = document.createElement('div');
            item.className = 'recent-item';
            item.innerHTML = `
                <div class="recent-item__avatar">${initials}</div>
                <div class="recent-item__info">
                    <div class="recent-item__name">${data.siswa_nama}</div>
                    <div class="recent-item__meta">${data.siswa_kelas} &nbsp;${badge}</div>
                </div>
                <div class="recent-item__time">${time}</div>
            `;
            recentList.insertBefore(item, recentList.firstChild);

            // Keep only 10 entries
            while (recentList.children.length > 10) {
                recentList.removeChild(recentList.lastChild);
            }
        }

        // ── Counters ─────────────────────────────────────────────
        function updateCounters(hadir, total) {
            ctrHadir.textContent = hadir;
            ctrBelum.textContent = Math.max(0, total - hadir);
        }

        // ── Toast ─────────────────────────────────────────────────
        function showToast(type, icon, name, sub) {
            const c = document.getElementById('toastContainer');
            const t = document.createElement('div');
            t.className = `toast-msg ${type}`;
            t.innerHTML = `
                <div class="toast-icon">${icon}</div>
                <div>
                    <div class="toast-name">${name}</div>
                    <div class="toast-sub">${sub}</div>
                </div>
            `;
            c.appendChild(t);
            setTimeout(() => {
                t.style.animation = 'toastOut 0.3s ease forwards';
                setTimeout(() => t.remove(), 300);
            }, 3500);
        }

        // ── Auto-start camera if user has already granted permission ──
        navigator.permissions && navigator.permissions.query({ name: 'camera' }).then(p => {
            if (p.state === 'granted') {
                setTimeout(startCamera, 600);
            }
        }).catch(() => {});
    </script>
</body>
</html>
