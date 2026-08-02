<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <title>Scanner Izin Pulang Cepat — Pos Satpam / Gatekeeper</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- html5-qrcode -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <style>
        :root {
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.4);
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.4);
            --danger: #ef4444;
            --danger-glow: rgba(239, 68, 68, 0.4);
            --warning: #f59e0b;
            --bg-main: #090d16;
            --bg-card: #111827;
            --bg-input: #1f2937;
            --border: rgba(255, 255, 255, 0.1);
            --text-main: #f9fafb;
            --text-sub: #9ca3af;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            background-color: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            min-height: 100dvh;
            width: 100vw;
            overflow-x: hidden;
        }

        /* ── NAVBAR ─────────────────────────────────────── */
        .header-bar {
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            box-shadow: 0 0 16px var(--primary-glow);
            color: #fff;
        }

        .header-title {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.3px;
            line-height: 1.2;
        }

        .header-subtitle {
            font-size: 0.75rem;
            color: var(--text-sub);
            font-weight: 500;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-header {
            background: var(--bg-input);
            border: 1px solid var(--border);
            color: var(--text-main);
            padding: 0.5rem 0.85rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-header:hover {
            background: #374151;
            color: #fff;
        }

        /* ── LAYOUT ────────────────────────────────────── */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.25rem;
        }

        .grid-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        @media (min-width: 1024px) {
            .grid-layout {
                grid-template-columns: 420px 1fr;
            }
        }

        /* ── SCANNER PANEL ─────────────────────────────── */
        .panel {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .panel-title {
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #f3f4f6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .scanner-box {
            position: relative;
            width: 100%;
            background: #000;
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid var(--border);
            aspect-ratio: 4 / 3;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #reader {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
        }

        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
        }

        #reader img, #reader__dashboard {
            display: none !important;
        }

        .scan-overlay-frame {
            position: absolute;
            inset: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .scan-target {
            width: 60%;
            height: 60%;
            border: 2px dashed rgba(99, 102, 241, 0.6);
            border-radius: 12px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.45);
            animation: pulse-border 2s infinite;
        }

        @keyframes pulse-border {
            0%, 100% { border-color: rgba(99, 102, 241, 0.8); }
            50% { border-color: rgba(16, 185, 129, 0.8); }
        }

        /* ── INPUT MANUAL BAR ──────────────────────────── */
        .manual-input-box {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .input-group {
            display: flex;
            gap: 0.5rem;
        }

        .input-text {
            flex: 1;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.85rem 1rem;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            outline: none;
            transition: border 0.2s;
        }

        .input-text:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .btn-submit {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            border: none;
            color: #fff;
            padding: 0.85rem 1.25rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            transition: all 0.2s;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .cam-controls {
            display: flex;
            gap: 0.5rem;
        }

        .btn-cam {
            flex: 1;
            background: var(--bg-input);
            border: 1px solid var(--border);
            color: var(--text-main);
            padding: 0.6rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .btn-cam:hover {
            background: #374151;
        }

        /* ── RESULT / VERIFICATION CARD ───────────────── */
        .result-panel {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-h: 500px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 420px;
            text-align: center;
            color: var(--text-sub);
            gap: 1rem;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px dashed var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #6b7280;
        }

        /* Status Badge Giant */
        .status-badge-giant {
            width: 100%;
            padding: 1.25rem 1rem;
            border-radius: 16px;
            text-align: center;
            font-weight: 900;
            font-size: 1.25rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            margin-bottom: 1.25rem;
        }

        .status-badge-giant.valid {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff;
            box-shadow: 0 0 25px var(--success-glow);
        }

        .status-badge-giant.invalid {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: #ffffff;
            box-shadow: 0 0 25px var(--danger-glow);
        }

        .status-badge-sub {
            font-size: 0.85rem;
            font-weight: 500;
            opacity: 0.9;
            text-transform: none;
        }

        /* Profile Card Body */
        .profile-header-card {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            padding: 1rem 1.25rem;
            border-radius: 16px;
            margin-bottom: 1.25rem;
        }

        .avatar-img {
            width: 72px;
            height: 72px;
            border-radius: 14px;
            object-fit: cover;
            border: 2px solid var(--border);
            flex-shrink: 0;
            background: #1f2937;
        }

        .profile-info {
            flex: 1;
            min-width: 0;
        }

        .profile-name {
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 0.25rem;
            word-break: break-word;
        }

        .profile-badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.35rem;
        }

        .chip-badge {
            background: var(--bg-input);
            border: 1px solid var(--border);
            padding: 0.25rem 0.65rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #d1d5db;
        }

        /* Detail List */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.85rem;
            margin-bottom: 1.5rem;
        }

        .detail-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.85rem 1rem;
        }

        .detail-label {
            font-size: 0.75rem;
            color: var(--text-sub);
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 700;
            color: #f3f4f6;
            word-break: break-word;
        }

        .detail-value.highlight {
            color: #38bdf8;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.15rem;
        }

        /* Tombol Eksekusi Menarik */
        .btn-gate-checkout {
            width: 100%;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border: none;
            color: #ffffff;
            padding: 1.25rem;
            border-radius: 16px;
            font-size: 1.25rem;
            font-weight: 900;
            letter-spacing: 0.5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            box-shadow: 0 8px 25px var(--success-glow);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
        }

        .btn-gate-checkout:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 12px 30px var(--success-glow);
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
        }

        .btn-gate-checkout:active {
            transform: translateY(0) scale(0.99);
        }

        .btn-gate-disabled {
            width: 100%;
            background: #374151;
            border: 1px solid var(--border);
            color: #9ca3af;
            padding: 1.1rem;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: not-allowed;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>

    <!-- NAVBAR HEADER -->
    <header class="header-bar">
        <div class="header-brand">
            <div class="header-icon">
                <i class="ti ti-shield-check"></i>
            </div>
            <div>
                <div class="header-title">POS SATPAM / GATEKEEPER</div>
                <div class="header-subtitle">Verifikasi Izin Pulang Cepat Digital</div>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('dashboard') }}" class="btn-header">
                <i class="ti ti-dashboard"></i>
                <span class="d-none d-sm-inline">Dashboard</span>
            </a>
        </div>
    </header>

    <!-- MAIN CONTAINER -->
    <main class="container">
        <div class="grid-layout">

            <!-- LEFT PANEL: SCANNER & SEARCH -->
            <section class="panel">
                <div class="panel-title">
                    <i class="ti ti-scan text-indigo-400"></i> Kamera Barcode / QR Scanner
                </div>

                <!-- Camera Container -->
                <div class="scanner-box">
                    <div id="reader"></div>
                    <div class="scan-overlay-frame">
                        <div class="scan-target"></div>
                    </div>
                </div>

                <!-- Controls Camera -->
                <div class="cam-controls">
                    <button type="button" class="btn-cam" id="btn-toggle-cam">
                        <i class="ti ti-camera"></i> <span id="cam-status-text">Stop Kamera</span>
                    </button>
                    <button type="button" class="btn-cam" id="btn-switch-cam">
                        <i class="ti ti-camera-rotate"></i> Putar Kamera
                    </button>
                </div>

                <hr style="border-color: var(--border); margin: 0.25rem 0;">

                <!-- Manual Input Bar -->
                <div class="manual-input-box">
                    <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-sub);">
                        MANUAL INPUT (KODE IZIN / NIS / NIP / QR STRING)
                    </label>
                    <form id="form-verify" class="input-group">
                        <input type="text" id="input-query" class="input-text" placeholder="Scan barcode / Ketik Kode IPC-..." autofocus autocomplete="off">
                        <button type="submit" class="btn-submit" id="btn-search">
                            <i class="ti ti-search"></i> CARI
                        </button>
                    </form>
                </div>
            </section>

            <!-- RIGHT PANEL: VERIFICATION RESULT CARD -->
            <section class="result-panel" id="result-panel">
                <!-- DEFAULT EMPTY STATE -->
                <div class="empty-state" id="empty-state">
                    <div class="empty-icon">
                        <i class="ti ti-qrcode"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1.2rem; color: #e5e7eb; margin-bottom: 0.4rem;">Siap Memindai</h3>
                        <p style="font-size: 0.9rem; max-width: 320px;">Arahkan QR Code kartu/surat izin ke kamera pos satpam atau ketik kode izin pada kotak pencarian.</p>
                    </div>
                </div>

                <!-- CARD CONTENT (Hidden initially) -->
                <div id="card-content" style="display: none; height: 100%; flex-direction: column; justify-content: space-between;">
                    <div>
                        <!-- STATUS BADGE GIANT -->
                        <div class="status-badge-giant" id="status-badge-giant">
                            <span id="badge-title-text">IZIN VALID - SILAKAN KELUAR</span>
                            <span class="status-badge-sub" id="badge-sub-text">Status disetujui untuk hari ini</span>
                        </div>

                        <!-- PROFILE CARD -->
                        <div class="profile-header-card">
                            <img src="" id="user-photo" class="avatar-img" alt="Foto Profil" onerror="this.src='{{ asset('assets/img/avatars/1.png') }}'">
                            <div class="profile-info">
                                <div class="profile-name" id="user-name">-</div>
                                <div class="profile-badge-row">
                                    <span class="chip-badge" id="user-kategori">-</span>
                                    <span class="chip-badge" id="user-identitas">-</span>
                                    <span class="chip-badge" id="user-subtext">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- DETAILS GRID -->
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label"><i class="ti ti-barcode"></i> KODE IZIN</div>
                                <div class="detail-value highlight" id="val-kode">-</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="ti ti-clock"></i> RENCANA KELUAR</div>
                                <div class="detail-value" id="val-jam-rencana">-</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="ti ti-category"></i> JENIS ALASAN</div>
                                <div class="detail-value" id="val-jenis-alasan">-</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="ti ti-user-check"></i> DISETUJUI OLEH</div>
                                <div class="detail-value" id="val-approver">-</div>
                            </div>
                            <div class="detail-item" style="grid-column: 1 / -1;">
                                <div class="detail-label"><i class="ti ti-notes"></i> KETERANGAN ALASAN</div>
                                <div class="detail-value" id="val-alasan" style="font-weight: 500; font-size: 0.95rem;">-</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="ti ti-user-share"></i> NAMA PENJEMPUT</div>
                                <div class="detail-value" id="val-penjemput">-</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="ti ti-phone"></i> NO HP PENJEMPUT</div>
                                <div class="detail-value" id="val-hp-penjemput">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION BUTTON AREA -->
                    <div id="action-area" style="margin-top: 1rem;">
                        <button type="button" class="btn-gate-checkout" id="btn-confirm-checkout">
                            <i class="ti ti-door-exit" style="font-size: 1.6rem;"></i>
                            <span>BUKA GERBANG & CONFIRM KELUAR</span>
                        </button>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- SCRIPTS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Elements
            const formVerify = document.getElementById('form-verify');
            const inputQuery = document.getElementById('input-query');
            const emptyState = document.getElementById('empty-state');
            const cardContent = document.getElementById('card-content');

            // Card Fields
            const statusBadge = document.getElementById('status-badge-giant');
            const badgeTitle = document.getElementById('badge-title-text');
            const badgeSub = document.getElementById('badge-sub-text');
            const userPhoto = document.getElementById('user-photo');
            const userName = document.getElementById('user-name');
            const userKategori = document.getElementById('user-kategori');
            const userIdentitas = document.getElementById('user-identitas');
            const userSubtext = document.getElementById('user-subtext');
            const valKode = document.getElementById('val-kode');
            const valJamRencana = document.getElementById('val-jam-rencana');
            const valJenisAlasan = document.getElementById('val-jenis-alasan');
            const valApprover = document.getElementById('val-approver');
            const valAlasan = document.getElementById('val-alasan');
            const valPenjemput = document.getElementById('val-penjemput');
            const valHpPenjemput = document.getElementById('val-hp-penjemput');
            const actionArea = document.getElementById('action-area');

            let currentIzinData = null;
            let html5QrCode = null;
            let isScanning = false;

            // Initialize Camera Scanner
            function startScanner() {
                html5QrCode = new Html5Qrcode("reader");
                const config = { fps: 10, qrbox: { width: 220, height: 220 } };

                html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    isScanning = true;
                    document.getElementById('cam-status-text').innerText = "Stop Kamera";
                }).catch(err => {
                    console.warn("Kamera tidak dapat diakses: ", err);
                    document.getElementById('cam-status-text').innerText = "Mulai Kamera";
                    isScanning = false;
                });
            }

            function stopScanner() {
                if (html5QrCode && isScanning) {
                    html5QrCode.stop().then(() => {
                        isScanning = false;
                        document.getElementById('cam-status-text').innerText = "Mulai Kamera";
                    });
                }
            }

            document.getElementById('btn-toggle-cam').addEventListener('click', function () {
                if (isScanning) {
                    stopScanner();
                } else {
                    startScanner();
                }
            });

            startScanner();

            function onScanSuccess(decodedText) {
                if (inputQuery.value === decodedText) return;
                inputQuery.value = decodedText;
                performVerification(decodedText);
            }

            function onScanFailure(error) {
                // Ignore silent scan loop frame failures
            }

            // Form Manual Submit
            formVerify.addEventListener('submit', function (e) {
                e.preventDefault();
                const q = inputQuery.value.trim();
                if (!q) return;
                performVerification(q);
            });

            // Perform AJAX Verification
            function performVerification(queryStr) {
                Swal.fire({
                    title: 'Memeriksa Data...',
                    text: 'Mohon tunggu sejenak',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch("{{ route('satpam.gatekeeper.verify') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ query: queryStr })
                })
                .then(res => res.json())
                .then(res => {
                    Swal.close();
                    if (!res.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Tidak Ditemukan',
                            text: res.message || 'Data izin pulang cepat tidak ditemukan.',
                            confirmButtonColor: '#ef4444'
                        });
                        return;
                    }

                    renderIzinCard(res);
                })
                .catch(err => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Gagal terhubung ke server.',
                        confirmButtonColor: '#ef4444'
                    });
                });
            }

            // Render Result Card UI
            function renderIzinCard(res) {
                const data = res.data;
                currentIzinData = data;

                emptyState.style.display = 'none';
                cardContent.style.display = 'flex';

                // Status Badge
                if (res.is_valid) {
                    statusBadge.className = 'status-badge-giant valid';
                } else {
                    statusBadge.className = 'status-badge-giant invalid';
                }
                badgeTitle.innerText = res.status_label;
                badgeSub.innerText = res.status_message;

                // Profile
                userPhoto.src = data.foto_url;
                userName.innerText = data.nama;
                userKategori.innerText = data.kategori;
                userIdentitas.innerText = data.identitas;
                userSubtext.innerText = data.sub_text;

                // Details
                valKode.innerText = data.kode_izin;
                valJamRencana.innerText = data.jam_rencana_keluar + ' WIB';
                valJenisAlasan.innerText = data.jenis_alasan;
                valApprover.innerText = data.disetujui_oleh;
                valAlasan.innerText = data.alasan;
                valPenjemput.innerText = data.nama_penjemput || '-';
                valHpPenjemput.innerText = data.no_hp_penjemput || '-';

                // Action Area Button
                if (res.is_valid) {
                    actionArea.innerHTML = `
                        <button type="button" class="btn-gate-checkout" id="btn-confirm-checkout">
                            <i class="ti ti-door-exit" style="font-size: 1.6rem;"></i>
                            <span>BUKA GERBANG & CONFIRM KELUAR</span>
                        </button>
                    `;
                    document.getElementById('btn-confirm-checkout').addEventListener('click', executeCheckout);
                } else {
                    let msg = 'TIDAK DAPAT DIKELUARKAN';
                    if (data.status === 'completed') {
                        msg = 'SUDAH KELUAR (' + (data.diverifikasi_satpam_pada || '-') + ')';
                    }
                    actionArea.innerHTML = `
                        <div class="btn-gate-disabled">
                            <i class="ti ti-lock"></i>
                            <span>${msg}</span>
                        </div>
                    `;
                }
            }

            // Execute Checkout Action
            function executeCheckout() {
                if (!currentIzinData) return;

                Swal.fire({
                    title: 'Konfirmasi Buka Gerbang?',
                    text: `Apakah Anda yakin menyetujui kepulangan untuk ${currentIzinData.nama} (${currentIzinData.kode_izin})?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'YA, BUKA GERBANG & CONFIRM',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses Checkout...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        const url = "{{ route('satpam.gatekeeper.checkout', ':id') }}".replace(':id', currentIzinData.id);

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(res => {
                            Swal.close();
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'BERHASIL!',
                                    text: res.message,
                                    confirmButtonColor: '#10b981'
                                }).then(() => {
                                    // Reset or re-verify to update status UI
                                    performVerification(currentIzinData.kode_izin);
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message || 'Terjadi kesalahan.',
                                    confirmButtonColor: '#ef4444'
                                });
                            }
                        })
                        .catch(err => {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal memproses ke server.',
                                confirmButtonColor: '#ef4444'
                            });
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>
