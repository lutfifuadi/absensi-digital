<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Akses Ditolak — Perangkat Tidak Dikenal</title>
    <meta name="description" content="Perangkat Anda belum terdaftar dalam sistem absensi. Hubungi admin untuk aktivasi." />
    <meta name="robots" content="noindex, nofollow" />
    @php
      $faviconSetting = \App\Models\Pengaturan::where('key', 'logo_url')->value('value');
      if (!$faviconSetting) {
        $faviconSetting = \App\Models\Pengaturan::where('key', 'logo_sekolah')->value('value');
        if ($faviconSetting) {
          $faviconSetting = asset('storage/' . $faviconSetting);
        }
      }
    @endphp
    @if($faviconSetting)
      <link rel="icon" type="image/png" href="{{ $faviconSetting }}" />
    @else
      <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet" />

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Quicksand', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a0e1a;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── Animated Background ──────────────────────────────── */
        .bg-animated {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .bg-animated::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(239, 68, 68, 0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(124, 58, 237, 0.10) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 50% 50%, rgba(15, 23, 42, 0.95) 0%, #0a0e1a 100%);
            animation: bgPulse 8s ease-in-out infinite alternate;
        }

        @keyframes bgPulse {
            0%   { opacity: 0.8; }
            100% { opacity: 1; }
        }

        .grid-overlay {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(ellipse 70% 70% at center, black 30%, transparent 75%);
        }

        /* ── Floating Orbs ───────────────────────────────────── */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: orbFloat 12s ease-in-out infinite;
            pointer-events: none;
        }

        .orb-1 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.15), transparent 70%);
            top: -10%; left: -5%;
            animation-duration: 14s;
        }

        .orb-2 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.12), transparent 70%);
            bottom: -5%; right: -5%;
            animation-duration: 10s;
            animation-delay: -4s;
        }

        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(20px, -20px) scale(1.05); }
            66%       { transform: translate(-15px, 15px) scale(0.97); }
        }

        /* ── Main Card ───────────────────────────────────────── */
        .landing-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            /* Smartphone default */
            max-width: 480px;
            padding: 1rem;
        }

        /* Tablet (≥768px) */
        @media (min-width: 768px) {
            .landing-wrapper { max-width: 640px; padding: 1.5rem; }
        }

        /* Laptop / Desktop (≥1024px) */
        @media (min-width: 1024px) {
            .landing-wrapper { max-width: 820px; padding: 2rem; }
        }

        /* Large Desktop (≥1440px) */
        @media (min-width: 1440px) {
            .landing-wrapper { max-width: 960px; padding: 2rem; }
        }

        .landing-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            padding: 2.5rem 2.5rem 2rem;
            box-shadow:
                0 0 0 1px rgba(239, 68, 68, 0.05),
                0 25px 50px rgba(0, 0, 0, 0.5),
                0 0 80px rgba(239, 68, 68, 0.06);
            animation: cardEntrance 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        /* Tablet Layout (≥768px) - 2 Columns */
        @media (min-width: 768px) {
            .landing-card {
                display: grid;
                grid-template-columns: 1fr 1px 1fr;
                column-gap: 2rem;
                align-items: stretch;
            }
            .left-column {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding-right: 0.5rem;
            }
            .left-column .subtitle {
                margin-bottom: 0;
            }
            .right-column {
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding-left: 0.5rem;
            }
            .divider {
                width: 1px;
                height: 100%;
                background: linear-gradient(180deg, transparent, rgba(255,255,255,0.07), transparent);
                margin: 0;
            }
            .card-footer {
                grid-column: 1 / -1;
                margin-top: 1.5rem;
            }
        }

        /* Desktop Layout (≥1024px) */
        @media (min-width: 1024px) {
            .landing-card {
                padding: 3rem 3.5rem 2.5rem;
                column-gap: 3rem;
            }
            .left-column {
                padding-right: 1rem;
            }
            .right-column {
                padding-left: 1rem;
            }
            .card-footer {
                margin-top: 2rem;
            }
        }

        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(30px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── Shield Icon ─────────────────────────────────────── */
        .shield-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .shield-ring {
            position: relative;
            width: 90px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .shield-ring::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid rgba(239, 68, 68, 0.25);
            animation: ringPulse 2.5s ease-in-out infinite;
        }

        .shield-ring::after {
            content: '';
            position: absolute;
            inset: -10px;
            border-radius: 50%;
            border: 1px solid rgba(239, 68, 68, 0.10);
            animation: ringPulse 2.5s ease-in-out infinite 0.8s;
        }

        @keyframes ringPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(1.08); }
        }

        .shield-icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.20), rgba(239, 68, 68, 0.08));
            border: 1.5px solid rgba(239, 68, 68, 0.30);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .shield-icon-wrap svg {
            width: 34px;
            height: 34px;
            color: #ef4444;
            filter: drop-shadow(0 0 10px rgba(239, 68, 68, 0.5));
        }

        /* ── Typography ──────────────────────────────────────── */
        .title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 800;
            color: #f1f5f9;
            letter-spacing: -0.02em;
            line-height: 1.25;
            margin-bottom: 0.6rem;
        }

        .subtitle {
            text-align: center;
            font-size: 0.875rem;
            color: #94a3b8;
            line-height: 1.6;
            max-width: 380px;
            margin: 0 auto 1.75rem;
        }

        /* ── Divider ─────────────────────────────────────────── */
        .divider {
            height: 1px;
            width: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.07), transparent);
            margin: 1.75rem 0;
        }

        /* ── Device ID Box ───────────────────────────────────── */
        .device-id-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
            text-align: center;
            margin-bottom: 0.6rem;
        }

        .device-id-box {
            background: rgba(239, 68, 68, 0.06);
            border: 1.5px dashed rgba(239, 68, 68, 0.25);
            border-radius: 4px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.65rem;
            transition: border-color 0.3s, background 0.3s;
        }

        .device-id-box:hover {
            border-color: rgba(239, 68, 68, 0.45);
            background: rgba(239, 68, 68, 0.09);
        }

        .device-id-text {
            flex: 1;
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: 0.9rem;
            font-weight: 500;
            color: #f87171;
            letter-spacing: 0.06em;
            word-break: break-all;
            text-align: center;
            min-height: 1.3em;
            line-height: 1.5;
        }

        /* ── Copy Button ─────────────────────────────────────── */
        .btn-copy {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem 1.25rem;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: none;
            border-radius: 4px;
            color: #fff;
            font-family: 'Quicksand', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: 0.01em;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.35);
            margin-bottom: 1.75rem;
        }

        .btn-copy:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.50);
        }

        .btn-copy:active {
            transform: translateY(0) scale(0.98);
        }

        .btn-copy svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            transition: all 0.3s;
        }

        .btn-copy.copied {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.35);
        }

        .btn-copy.copied:hover {
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.50);
        }

        /* ── Info Hint ───────────────────────────────────────── */
        .info-hint {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            background: rgba(251, 191, 36, 0.06);
            border: 1px solid rgba(251, 191, 36, 0.15);
            border-radius: 4px;
            padding: 0.9rem 1.1rem;
            margin-bottom: 1.75rem;
        }

        .info-hint svg {
            width: 18px;
            height: 18px;
            color: #fbbf24;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .info-hint p {
            font-size: 0.8rem;
            color: #cbd5e1;
            line-height: 1.55;
        }

        .info-hint p strong {
            color: #fbbf24;
            font-weight: 600;
        }

        /* ── Action Buttons ──────────────────────────────────── */
        .action-row {
            display: flex;
            gap: 0.75rem;
        }

        .btn-secondary {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.65rem 1rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 4px;
            color: #94a3b8;
            font-family: 'Quicksand', sans-serif;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .btn-secondary svg {
            width: 15px;
            height: 15px;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.09);
            border-color: rgba(255,255,255,0.18);
            color: #e2e8f0;
            transform: translateY(-1px);
        }

        /* ── Footer ──────────────────────────────────────────── */
        .card-footer {
            text-align: center;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        .card-footer p {
            font-size: 0.75rem;
            color: #475569;
        }

        .card-footer a {
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .card-footer a:hover {
            color: #94a3b8;
        }

        /* ── Toast Notification ──────────────────────────────── */
        .toast {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(0);
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 4px;
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #22c55e;
            font-size: 0.85rem;
            font-weight: 500;
            z-index: 9999;
            /* Hidden by default — opacity + pointer-events (NO transform shift) */
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.35s ease, transform 0.35s ease;
            white-space: nowrap;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }

        .toast.show {
            opacity: 1;
            pointer-events: auto;
        }

        .toast svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* ── Loading State ───────────────────────────────────── */
        .id-loading {
            display: inline-flex;
            gap: 4px;
            align-items: center;
        }

        .id-loading span {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #ef4444;
            animation: dotBounce 1.2s ease-in-out infinite;
        }

        .id-loading span:nth-child(2) { animation-delay: 0.2s; }
        .id-loading span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes dotBounce {
            0%, 100% { transform: translateY(0); opacity: 0.5; }
            50%       { transform: translateY(-5px); opacity: 1; }
        }

        /* ── Responsive (small phone) ────────────────────────── */
        @media (max-width: 400px) {
            .landing-card { padding: 1.5rem 1rem 1rem; }
            .title { font-size: 1.15rem; }
            .device-id-text { font-size: 0.75rem; }
        }
    </style>
</head>
<body>

<!-- ── Animated Background ───────────────────────────────── -->
<div class="bg-animated">
    <div class="grid-overlay"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
</div>

<!-- ── Landing Card ──────────────────────────────────────── -->
<div class="landing-wrapper">
    <div class="landing-card">

        <div class="left-column">
        <!-- Shield Icon -->
        <div class="shield-container">
            <div class="shield-ring">
                <div class="shield-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Title -->
        <h1 class="title">Perangkat Tidak Dikenali</h1>
        <p class="subtitle">
            Demi keamanan sistem presensi digital, hanya perangkat yang telah diverifikasi Admin yang dapat mengakses halaman ini.
        </p>
        </div>

        <div class="divider"></div>

        <div class="right-column">
        <!-- Device ID -->
        <p class="device-id-label">ID Perangkat Anda</p>
        <div class="device-id-box" id="deviceIdBox">
            <div class="device-id-text" id="deviceIdDisplay">
                <span class="id-loading">
                    <span></span><span></span><span></span>
                </span>
            </div>
        </div>

        <!-- Copy Button -->
        <button class="btn-copy" id="btnCopy" onclick="copyDeviceId()">
            <svg id="copyIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
            </svg>
            <span id="btnCopyText">Salin ID Perangkat</span>
        </button>

        <!-- Info Hint -->
        <div class="info-hint">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <p>
                Salin ID di atas, lalu kirimkan ke <strong>Admin Sekolah / Operator</strong> untuk proses aktivasi perangkat ini.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="action-row">
            <a href="{{ url('/') }}" class="btn-secondary" id="btnHome">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Beranda
            </a>
            <button onclick="window.location.reload()" class="btn-secondary" id="btnRefresh">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Coba Lagi
            </button>
        </div>
        </div>

        <!-- Footer -->
        <div class="card-footer">
            <p>Sistem Presensi Digital &mdash; <a href="{{ url('/') }}">Kembali ke Beranda</a></p>
        </div>

    </div>
</div>

<!-- Toast Notification -->
<div class="toast" id="toastCopied">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    ID Perangkat berhasil disalin!
</div>

<script>
(function () {
    const cookieName   = 'device_uuid';
    const displayEl    = document.getElementById('deviceIdDisplay');
    const btnCopy      = document.getElementById('btnCopy');
    const btnCopyText  = document.getElementById('btnCopyText');
    const copyIcon     = document.getElementById('copyIcon');
    const toast        = document.getElementById('toastCopied');
    let   currentDeviceId = '';

    // ── Cookie helpers ────────────────────────────────────────
    function getCookie(name) {
        const value = '; ' + document.cookie;
        const parts = value.split('; ' + name + '=');
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    function setCookie(name, value, days) {
        const maxAge = days * 24 * 60 * 60;
        document.cookie = name + '=' + value + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
    }

    function generateUUID() {
        const hex  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        const ts   = Date.now().toString(36).toUpperCase();
        const rand = Array.from({length: 8}, () => hex[Math.floor(Math.random() * hex.length)]).join('');
        return 'DEV-' + rand + '-' + ts;
    }

    // ── Init Device ID ────────────────────────────────────────
    function initDeviceId() {
        let id = getCookie(cookieName);

        if (!id) {
            id = generateUUID();
            setCookie(cookieName, id, 365 * 10); // 10 tahun
            // Delay sebentar untuk menampilkan animasi loading, lalu reload
            setTimeout(() => window.location.reload(), 600);
            return;
        }

        currentDeviceId = id;
        displayEl.textContent = id;
    }

    // ── Copy to Clipboard ─────────────────────────────────────
    window.copyDeviceId = function () {
        if (!currentDeviceId) return;

        const checkIcon = `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>`;

        const originalIcon = copyIcon.outerHTML;

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(currentDeviceId)
                .then(() => showCopiedState(checkIcon, originalIcon))
                .catch(() => fallbackCopy(checkIcon, originalIcon));
        } else {
            fallbackCopy(checkIcon, originalIcon);
        }
    };

    function fallbackCopy(checkIcon, originalIcon) {
        const el = document.createElement('textarea');
        el.value = currentDeviceId;
        el.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0';
        document.body.appendChild(el);
        el.select();
        try {
            document.execCommand('copy');
            showCopiedState(checkIcon, originalIcon);
        } catch (e) {
            console.warn('Copy gagal:', e);
        }
        document.body.removeChild(el);
    }

    function showCopiedState(checkIcon, originalIcon) {
        // Button state
        btnCopy.classList.add('copied');
        btnCopy.querySelector('svg').outerHTML = checkIcon;
        btnCopyText.textContent = 'ID Berhasil Disalin!';

        // Toast
        toast.classList.add('show');

        setTimeout(() => {
            btnCopy.classList.remove('copied');
            btnCopy.innerHTML = originalIcon + '<span id="btnCopyText">Salin ID Perangkat</span>';

            // Re-attach after innerHTML reset
            document.getElementById('btnCopyText');

            toast.classList.remove('show');
        }, 2500);
    }

    // ── Run ───────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', initDeviceId);
})();
</script>

</body>
</html>
