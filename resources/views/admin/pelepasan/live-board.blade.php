<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIVE BOARD — PELEPASAN KELAS XII 2026</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    
    <style>
        :root {
            --primary: #7367f0;
            --primary-glow: rgba(115, 103, 240, 0.4);
            --success: #28c76f;
            --success-glow: rgba(40, 199, 111, 0.4);
            --danger: #ea5455;
            --border: rgba(255, 255, 255, 0.08);
            --bg-glass: rgba(15, 23, 42, 0.6);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 0% 0%, #1e1b4b 0%, #0f172a 50%, #311042 100%);
            color: #f1f5f9;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Ambient Glow Objects */
        .glow-1 {
            position: absolute;
            top: -10%;
            left: 20%;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(115,103,240,0.15) 0%, transparent 70%);
            filter: blur(50px);
            z-index: 0;
            pointer-events: none;
        }
        .glow-2 {
            position: absolute;
            bottom: 5%;
            right: 15%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(147,51,234,0.12) 0%, transparent 70%);
            filter: blur(50px);
            z-index: 0;
            pointer-events: none;
        }

        /* HEADER */
        header {
            position: relative;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 3rem;
            border-bottom: 1px solid var(--border);
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(12px);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary) 0%, #a55eea 100%);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .logo-text h1 {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            background: linear-gradient(to right, #fff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text p {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .counter-container {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .counter-pill {
            background: var(--bg-glass);
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 0.5rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .counter-val {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success);
            text-shadow: 0 0 10px var(--success-glow);
        }

        .counter-label {
            font-size: 0.75rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }

        /* MAIN CONTENT */
        main {
            flex: 1;
            display: flex;
            padding: 2rem 3rem;
            gap: 2rem;
            z-index: 10;
            position: relative;
        }

        .board-left {
            flex: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .board-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--bg-glass);
            border: 1px solid var(--border);
            border-radius: 5px;
            backdrop-filter: blur(12px);
            padding: 1.5rem;
            max-height: calc(100vh - 160px);
        }

        /* SCREEN DISPLAY CARD */
        .welcome-display {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            animation: pulseGlow 4s infinite alternate;
        }

        .welcome-display i {
            font-size: 5rem;
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        .welcome-display h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 0.5rem;
        }

        .welcome-display p {
            font-size: 0.9rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* CONGRATS CARD (ACTIVE ON SCAN) */
        .congrats-card {
            width: 100%;
            max-width: 680px;
            background: rgba(15, 23, 42, 0.7);
            border: 2px solid var(--primary);
            border-radius: 5px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 0 50px var(--primary-glow);
            backdrop-filter: blur(20px);
            display: none;
            transform: scale(0.9);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .congrats-card.active {
            display: block;
            transform: scale(1);
            opacity: 1;
        }

        .avatar-frame {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, #a55eea 100%);
            padding: 4px;
            margin: 0 auto 1.5rem;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #0f172a;
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 3rem;
        }

        .badge-lulus {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--success-glow);
            border: 1px solid var(--success);
            color: #52e597;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.4rem 1.2rem;
            border-radius: 5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 1rem;
        }

        .grad-title {
            font-size: 1.25rem;
            color: #a5a2f7;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .student-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            line-height: 1.2;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        .student-class {
            font-size: 1.25rem;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .divider {
            width: 100px;
            height: 2px;
            background: linear-gradient(to right, transparent, var(--primary), transparent);
            margin: 0 auto 1.5rem;
        }

        .congrats-footer {
            font-size: 0.85rem;
            color: #64748b;
            font-style: italic;
        }

        /* RECENT LIST PANEL */
        .recent-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .recent-list {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .recent-item {
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 0.85rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.25s;
            animation: slideInRight 0.4s ease-out;
        }

        .recent-item:hover {
            background: rgba(255,255,255,0.04);
            border-color: rgba(255,255,255,0.12);
        }

        .recent-name {
            font-weight: 700;
            color: white;
            font-size: 0.88rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }

        .recent-class {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        .recent-time {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            color: #a5a2f7;
            text-align: right;
        }

        .recent-wa {
            font-size: 0.65rem;
            color: #28c76f;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 2px;
        }

        /* HIDDEN SCAN INPUT */
        .hidden-scanner-input {
            position: fixed;
            left: -9999px;
            top: 0;
            width: 1px;
            height: 1px;
            opacity: 0;
            z-index: 1;
            pointer-events: none;
        }

        /* SCANNER OFFLINE OVERLAY */
        #scannerOfflineOverlay {
            display: none;
            position: fixed;
            bottom: 5rem;
            left: 3rem;
            z-index: 200;
            background: rgba(234, 84, 85, 0.15);
            border: 1px solid rgba(234, 84, 85, 0.4);
            border-radius: 5px;
            padding: 0.6rem 1.2rem;
            color: #ea5455;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            animation: pulseGlow 1s infinite alternate;
        }

        /* SCAN ERROR NOTIFICATION */
        #scanErrorMsg {
            display: none;
            position: fixed;
            top: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 500;
            background: rgba(234, 84, 85, 0.15);
            border: 1px solid rgba(234, 84, 85, 0.5);
            border-radius: 5px;
            padding: 0.7rem 1.5rem;
            color: #ea5455;
            font-weight: 600;
            font-size: 0.88rem;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        /* SCAN INDICATOR */
        .scan-indicator {
            position: fixed;
            bottom: 2rem;
            left: 3rem;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-glass);
            border: 1px solid var(--border);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.75rem;
            color: #94a3b8;
            cursor: pointer;
        }

        /* MANUAL SCAN INPUT PANEL (FLOATING) */
        .manual-scan-panel {
            position: fixed;
            bottom: 2rem;
            right: 3rem;
            z-index: 100;
            background: var(--bg-glass);
            border: 1px solid var(--border);
            border-radius: 5px;
            backdrop-filter: blur(12px);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            transition: all 0.3s ease;
            min-width: 220px;
        }

        .manual-scan-panel:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .manual-scan-panel input {
            flex: 1;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 4px;
            padding: 0.45rem 0.7rem;
            color: #f1f5f9;
            font-family: 'Outfit', sans-serif;
            font-size: 0.8rem;
            outline: none;
            transition: border-color 0.2s;
            min-width: 0;
            width: 130px;
        }

        .manual-scan-panel input::placeholder {
            color: #475569;
            font-size: 0.75rem;
        }

        .manual-scan-panel input:focus {
            border-color: var(--primary);
        }

        .btn-scan-manual {
            background: linear-gradient(135deg, var(--primary) 0%, #a55eea 100%);
            border: none;
            border-radius: 4px;
            padding: 0.45rem 0.85rem;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-scan-manual:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px var(--primary-glow);
        }

        .btn-scan-manual i {
            font-size: 0.9rem;
        }

        .manual-scan-divider {
            color: #475569;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 0.2rem;
            user-select: none;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 8px var(--success);
            animation: pulseGlow 1.5s infinite alternate;
        }

        /* ANIMATIONS */
        @keyframes pulseGlow {
            from { opacity: 0.6; }
            to { opacity: 1; }
        }

        @keyframes slideInRight {
            from { transform: translateX(20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="glow-1"></div>
    <div class="glow-2"></div>

    <!-- Hidden Input for QR Code Reader -->
    <input type="text" id="scannerInput" class="hidden-scanner-input" autocomplete="off" tabindex="0">

    <!-- Scanner Offline Overlay -->
    <div id="scannerOfflineOverlay" onclick="reactivateScanner()">
        <i class="ti tabler-wifi-off" style="margin-right:6px;"></i> Scanner tidak aktif — Klik di sini untuk mengaktifkan
    </div>

    <!-- Scan Error Message -->
    <div id="scanErrorMsg"></div>

    <!-- HEADER -->
    <header>
        <div class="logo-area">
            <div class="logo-icon">
                <i class="ti tabler-school"></i>
            </div>
            <div class="logo-text">
                <h1>PELEPASAN KELAS XII</h1>
                <p>{{ $kegiatan->tahunAkademik->nama ?? '2025/2026' }} — MAN 1 KOTA BANDUNG</p>
            </div>
        </div>
        
        <div class="counter-container">
            <div class="counter-pill">
                <div class="counter-val" id="countHadir">{{ $totalHadir }}</div>
                <div class="counter-label">Wisudawan<br>Hadir</div>
            </div>
            <div class="counter-pill">
                <div class="counter-val" style="color:#64748b; text-shadow:none;">{{ $totalSiswa }}</div>
                <div class="counter-label">Total<br>Siswa XII</div>
            </div>
        </div>
    </header>

    <!-- MAIN BOARD -->
    <main>
        <!-- Welcome & Congrats Display -->
        <div class="board-left">
            <div class="welcome-display" id="welcomePanel">
                <i class="ti tabler-qrcode"></i>
                <h2>SILAKAN PINDAI KARTU ANDA</h2>
                <p>Arahkan QR Code Kartu Kelulusan ke Scanner</p>
            </div>

            <div class="congrats-card" id="congratsPanel">
                <div class="avatar-frame">
                    <img id="studentAvatar" class="avatar-img" src="" style="display:none;">
                    <div id="studentInitials" class="avatar-img">🎓</div>
                </div>
                <div class="badge-lulus">
                    <i class="ti tabler-award"></i> Lulusan 2026
                </div>
                <div class="grad-title">Selamat &amp; Sukses Atas Kelulusan</div>
                <div class="student-name" id="studentName">AMALIA NURHASANAH</div>
                <div class="student-class" id="studentClass">XII-F.1</div>
                <div class="divider"></div>
                <div class="congrats-footer">"Semoga sukses menempuh pendidikan yang lebih tinggi dan meraih cita-cita!"</div>
            </div>
        </div>

        <!-- Recent Attendance Log -->
        <div class="board-right">
            <div class="recent-title">
                <i class="ti tabler-clock"></i> Kehadiran Terbaru
            </div>
            <div class="recent-list" id="recentList">
                @forelse($recentLogs as $log)
                    <div class="recent-item">
                        <div>
                            <div class="recent-name">{{ $log->siswa->nama_lengkap }}</div>
                            <div class="recent-class">{{ $log->siswa->kelas->nama }}</div>
                        </div>
                        <div>
                            <div class="recent-time">{{ \Carbon\Carbon::parse($log->jam_absen)->format('H:i') }}</div>
                            <div class="recent-wa"><i class="ti tabler-brand-whatsapp"></i> Terkirim</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted small" id="emptyRecentText">Belum ada aktivitas scan masuk.</div>
                @endforelse
            </div>
        </div>
    </main>

    <!-- Manual Scan Input Panel -->
    <div class="manual-scan-panel" id="manualScanPanel">
        <span class="manual-scan-divider">Scan</span>
        <input type="text" id="manualScanInput" 
               placeholder="NISN / NIS / QR Code..." 
               autocomplete="off" inputmode="numeric">
        <button class="btn-scan-manual" id="manualScanBtn">
            <i class="ti tabler-send"></i> Kirim
        </button>
    </div>

    <!-- Scan Indicator & Trigger Focus -->
    <div class="scan-indicator" id="focusIndicator">
        <span class="status-dot"></span>
        <span id="focusText">Scanner Terhubung</span>
    </div>

    <!-- Web Audio API Success Chime Script -->
    <script>
        // Synthesise a success sound locally
        function playChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                
                const ctx = new AudioContext();
                
                // Play first note (E5)
                let osc1 = ctx.createOscillator();
                let gain1 = ctx.createGain();
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.type = 'sine';
                osc1.frequency.value = 659.25; // E5
                gain1.gain.setValueAtTime(0.1, ctx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                osc1.start();
                osc1.stop(ctx.currentTime + 0.3);
                
                // Play second note (A5) slightly delayed
                let osc2 = ctx.createOscillator();
                let gain2 = ctx.createGain();
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.type = 'sine';
                osc2.frequency.value = 880.00; // A5
                gain2.gain.setValueAtTime(0.1, ctx.currentTime + 0.15);
                gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.55);
                osc2.start(ctx.currentTime + 0.15);
                osc2.stop(ctx.currentTime + 0.55);
            } catch (e) {
                console.error("Audio failure: ", e);
            }
        }

        // Focus management
        const scannerInput = document.getElementById('scannerInput');
        const focusIndicator = document.getElementById('focusIndicator');
        const focusText = document.getElementById('focusText');
        const statusDot = focusIndicator.querySelector('.status-dot');
        const offlineOverlay = document.getElementById('scannerOfflineOverlay');
        const scanErrorMsg  = document.getElementById('scanErrorMsg');
        let scannerOfflineTimer;

        function setOnline() {
            focusText.textContent = "Scanner Terhubung";
            statusDot.style.background = "#28c76f";
            statusDot.style.boxShadow = "0 0 8px #28c76f";
            offlineOverlay.style.display = 'none';
            clearTimeout(scannerOfflineTimer);
        }

        function setOffline() {
            focusText.textContent = "Klik untuk Mengaktifkan Scanner";
            statusDot.style.background = "#ea5455";
            statusDot.style.boxShadow = "0 0 8px #ea5455";
            // Tunda tampilkan overlay agar tidak berkedip terus
            scannerOfflineTimer = setTimeout(() => {
                offlineOverlay.style.display = 'block';
            }, 800);
        }

        function keepFocus() {
            scannerInput.focus();
        }

        function reactivateScanner() {
            keepFocus();
        }
        window.reactivateScanner = reactivateScanner;

        // Cek apakah user sedang mengetik di input manual
        function isManualInputActive() {
            const active = document.activeElement;
            return active && (active.id === 'manualScanInput' || active.closest('.manual-scan-panel'));
        }

        // Force focus on click anywhere on body (kecuali overlay & manual input)
        document.addEventListener('click', function(e) {
            if (e.target.id !== 'scannerOfflineOverlay' && !isManualInputActive()) {
                keepFocus();
            }
        });

        scannerInput.addEventListener('focus', setOnline);
        scannerInput.addEventListener('blur', () => {
            setOffline();
            // Coba refokus setelah 300ms (hanya jika manual input tidak aktif)
            setTimeout(() => {
                if (!isManualInputActive()) keepFocus();
            }, 300);
        });

        // Backup: interval re-focus setiap 2 detik (kecuali manual input aktif)
        setInterval(() => {
            if (!isManualInputActive()) keepFocus();
        }, 2000);

        // Initialize focus
        keepFocus();

        // ─── Manual Scan Input ──────────────────────────────────────────
        const manualScanInput = document.getElementById('manualScanInput');
        const manualScanBtn = document.getElementById('manualScanBtn');
        const manualScanPanel = document.getElementById('manualScanPanel');

        function submitManualScan() {
            const val = manualScanInput.value.trim();
            if (!val) return;
            manualScanInput.value = '';
            // Kirim scan via fetch (sama seperti scanner fisik)
            fetch("{{ route('admin.pelepasan.scan.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ qr_code: val })
            })
            .then(async response => {
                const data = await response.json();
                if (response.ok) {
                    playChime();
                    displayCongrats(data);
                } else {
                    scanErrorMsg.textContent = data.message || 'Kartu tidak dikenal';
                    scanErrorMsg.style.display = 'block';
                    setTimeout(() => { scanErrorMsg.style.display = 'none'; }, 3000);
                }
            })
            .catch(error => console.error("Error: ", error));
        }

        manualScanBtn.addEventListener('click', submitManualScan);
        manualScanInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') submitManualScan();
        });

        // Jangan ganggu fokus scanner fisik saat mengetik di input manual
        manualScanInput.addEventListener('focus', function() {
            this.closest('.manual-scan-panel').style.borderColor = 'var(--primary)';
        });
        manualScanInput.addEventListener('blur', function() {
            this.closest('.manual-scan-panel').style.borderColor = '';
        });

        // Absensi Action Handler
        const welcomePanel = document.getElementById('welcomePanel');
        const congratsPanel = document.getElementById('congratsPanel');
        const studentName = document.getElementById('studentName');
        const studentClass = document.getElementById('studentClass');
        const studentAvatar = document.getElementById('studentAvatar');
        const studentInitials = document.getElementById('studentInitials');
        const countHadir = document.getElementById('countHadir');
        const recentList = document.getElementById('recentList');
        const emptyRecentText = document.getElementById('emptyRecentText');
        
        let hideTimeout;

        scannerInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const qrCode = scannerInput.value.trim();
                scannerInput.value = ''; // Clear input
                
                if (qrCode === '') return;

                // Send request to server
                fetch("{{ route('admin.pelepasan.scan.store') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ qr_code: qrCode })
                })
                .then(async response => {
                    const data = await response.json();
                    if (response.ok) {
                        playChime();
                        displayCongrats(data);
                    } else {
                        // Tampilkan error secara visual (bukan alert)
                        scanErrorMsg.textContent = data.message || 'Kartu tidak dikenal';
                        scanErrorMsg.style.display = 'block';
                        setTimeout(() => {
                            scanErrorMsg.style.display = 'none';
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error("Error: ", error);
                });
            }
        });

        function displayCongrats(data) {
            // Cancel pending hide timeout
            clearTimeout(hideTimeout);

            // Set student data
            studentName.textContent = data.siswa_nama;
            studentClass.textContent = data.siswa_kelas;

            if (data.foto) {
                studentAvatar.src = data.foto;
                studentAvatar.style.display = 'block';
                studentInitials.style.display = 'none';
            } else {
                studentAvatar.style.display = 'none';
                studentInitials.style.display = 'flex';
                studentInitials.textContent = '🎓';
            }

            // Swap panels
            welcomePanel.style.display = 'none';
            congratsPanel.style.display = 'block';
            
            // Trigger animation repaint
            congratsPanel.classList.remove('active');
            void congratsPanel.offsetWidth; // Force reflow
            congratsPanel.classList.add('active');

            // Update Counter
            countHadir.textContent = data.total_hadir;

            // Update Recent List
            if (data.is_new) {
                if (emptyRecentText) {
                    emptyRecentText.remove();
                }

                // Create new item HTML
                const item = document.createElement('div');
                item.className = 'recent-item';
                
                // Format current time
                const now = new Date();
                const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                
                const waText = data.wa_status === 'sent' 
                    ? '<span class="recent-wa"><i class="ti tabler-brand-whatsapp"></i> Terkirim</span>'
                    : (data.wa_status === 'skip' ? '<span class="recent-wa text-muted"><i class="ti tabler-ban"></i> Skip</span>' : '<span class="recent-wa text-warning"><i class="ti tabler-alert-triangle"></i> Gagal</span>');

                item.innerHTML = `
                    <div>
                        <div class="recent-name">${data.siswa_nama}</div>
                        <div class="recent-class">${data.siswa_kelas}</div>
                    </div>
                    <div>
                        <div class="recent-time">${timeStr}</div>
                        ${waText}
                    </div>
                `;

                // Add to list top
                recentList.insertBefore(item, recentList.firstChild);

                // Limit list to 8 items
                if (recentList.children.length > 8) {
                    recentList.removeChild(recentList.lastChild);
                }
            }

            // Set timeout to hide congrats panel after 6 seconds
            hideTimeout = setTimeout(() => {
                congratsPanel.classList.remove('active');
                setTimeout(() => {
                    congratsPanel.style.display = 'none';
                    welcomePanel.style.display = 'flex';
                }, 400); // Wait for transition to complete
            }, 6000);
        }

        // ─── Auto-Polling Real-time Data ────────────────────────────────────
        const REALTIME_URL_LB = "{{ route('admin.pelepasan.realtime') }}";
        let prevTotalHadirLB = parseInt(countHadir.textContent) || 0;

        function pollLiveBoard() {
            fetch(REALTIME_URL_LB, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update counter
                    countHadir.textContent = data.total_hadir;

                    // Jika ada siswa baru hadir, update recent list
                    if (data.total_hadir > prevTotalHadirLB) {
                        // Hapus semua recent items kecuali pesan kosong
                        const items = recentList.querySelectorAll('.recent-item');
                        items.forEach(item => item.remove());
                        if (emptyRecentText) emptyRecentText.remove();

                        // Render ulang recent logs dari server
                        data.recent_logs.forEach(log => {
                            const item = document.createElement('div');
                            item.className = 'recent-item';
                            item.innerHTML = `
                                <div>
                                    <div class="recent-name">${log.siswa_nama}</div>
                                    <div class="recent-class">${log.siswa_kelas}</div>
                                </div>
                                <div>
                                    <div class="recent-time">${log.waktu.substring(0, 5)}</div>
                                </div>
                            `;
                            recentList.appendChild(item);
                        });
                    }
                    prevTotalHadirLB = data.total_hadir;
                }
            })
            .catch(err => console.error('Polling error:', err));
        }

        // Start polling every 3 seconds
        setInterval(pollLiveBoard, 3000);
        // Initial poll after 1 second
        setTimeout(pollLiveBoard, 1000);
    </script>
</body>
</html>
