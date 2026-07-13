<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Live Absensi — {{ $namaSekolah }}</title>
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:        #080c14;
      --surface:   #0f1623;
      --border:    rgba(255,255,255,0.07);
      --primary:   #7367f0;
      --success:   #28c76f;
      --warning:   #ff9f43;
      --danger:    #ea5455;
      --info:      #00cfe8;
      --text:      #e2e8f0;
      --muted:     #64748b;
      --scanner-glow: rgba(115, 103, 240, 0.5);
    }

    html, body {
      height: 100dvh;
      max-height: 100dvh;
      font-family: 'Product Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }


    /* ─── SCROLLBAR ──────────────────────────────────────── */
    ::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 4px; }

    /* ─── HEADER ─────────────────────────────────────────── */
    .header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 0.75rem 1.5rem;
      background: linear-gradient(135deg, #1a1040 0%, #0f1623 100%);
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
      gap: 1rem;
    }
    .header-brand { display: flex; align-items: center; gap: 0.75rem; }
    .header-brand .logo-icon {
      width: 42px; height: 42px; border-radius: 4px;
      background: var(--primary); display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem; flex-shrink: 0;
      box-shadow: 0 0 18px rgba(115,103,240,.4);
    }
    .header-brand h1 { font-size: 1.1rem; font-weight: 800; color: #fff; }
    .header-brand p  { font-size: 0.72rem; color: var(--muted); margin-top: 1px; }

    .header-center { text-align: center; }
    #live-clock { font-size: 1.8rem; font-weight: 900; letter-spacing: 2px; color: #fff; font-variant-numeric: tabular-nums; }
    #live-date  { font-size: 0.72rem; color: var(--muted); }

    .header-right { display: flex; align-items: center; gap: 0.75rem; }
    .live-badge {
      display: flex; align-items: center; gap: 0.4rem;
      background: rgba(234,84,85,.15); border: 1px solid rgba(234,84,85,.4);
      border-radius: 5px; padding: 4px 12px; font-size: 0.72rem; font-weight: 700; color: var(--danger);
    }
    .live-dot { width: 7px; height: 7px; background: var(--danger); border-radius: 50%; animation: pulse 1.4s ease-in-out infinite; }
    @keyframes pulse { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:.4; transform:scale(1.4); } }

    .announce-bar {
      background: rgba(115,103,240,.12); border-bottom: 1px solid rgba(115,103,240,.2);
      padding: 6px 1.5rem; overflow: hidden; white-space: nowrap;
    }
    .announce-bar marquee { font-size: 0.8rem; color: rgba(255,255,255,.7); }

    /* ─── MAIN GRID ──────────────────────────────────────── */
    .main {
      display: grid;
      grid-template-columns: 460px 1fr 1fr;
      grid-template-rows: 1fr;
      gap: 0.75rem;
      padding: 0.75rem;
      flex: 1;
      min-height: 0;
      overflow: hidden;
    }
    /* Scanner selalu di kolom 1 (kiri), lepas dari urutan HTML */
    .scanner-col { grid-column: 1; grid-row: 1; }


    /* ─── PANELS ─────────────────────────────────────────── */
    .panel {
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 4px; display: flex; flex-direction: column; overflow: hidden;
    }
    .panel-header {
      padding: 0.9rem 1.1rem 0.7rem;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
    }
    .panel-title { font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; }
    .panel-body  { flex: 1; overflow-y: auto; padding: 0; }

    /* ─── STAT CHIPS ─────────────────────────────────────── */
    .stat-chips { display: flex; gap: 0.4rem; flex-wrap: wrap; padding: 0.6rem 1.1rem; border-bottom: 1px solid var(--border); flex-shrink: 0; }
    .scanner-area .stat-chips { border-bottom: none; justify-content: center; margin: 1rem 0; padding: 0; flex-wrap: nowrap; gap: 0.3rem; }
    .stat-chip {
      display: flex; align-items: center; gap: 0.35rem;
      background: rgba(255,255,255,.05); border: 1px solid var(--border);
      border-radius: 5px; padding: 3px 10px; font-size: 0.72rem; font-weight: 600;
    }
    .scanner-area .stat-chip {
      padding: 3px 6px;
      font-size: 0.68rem;
      flex-shrink: 0;
    }
    .stat-chip .dot { width: 6px; height: 6px; border-radius: 50%; }

    /* ─── LEADERBOARD TABLE ──────────────────────────────── */
    .lb-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .lb-table thead th {
      position: sticky; top: 0; z-index: 2;
      background: rgba(15,22,35,.97);
      padding: 0.5rem 0.7rem; font-size: 0.65rem; font-weight: 700; color: var(--muted);
      text-transform: uppercase; letter-spacing: .8px; text-align: left; border-bottom: 1px solid var(--border);
      overflow: hidden;
    }
    .lb-table tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
    .lb-table tbody tr:hover { background: rgba(255,255,255,.03); }
    .lb-table tbody td { padding: 0.55rem 0.7rem; font-size: 0.82rem; vertical-align: middle; overflow: hidden; }
    .lb-table tbody tr.top-3 { background: rgba(115,103,240,.06); }
    .lb-table tbody tr.late-row { background: rgba(255,159,67,.04); }

    .rank-cell { width: 40px; text-align: center; font-size: 1rem; }
    .name-cell { width: auto; }
    .name-cell .name { font-weight: 600; font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .name-cell .kelas-badge { font-size: 0.68rem; color: var(--muted); margin-top: 1px; }
    .jam-col { width: 85px; text-align: center; }
    .jam-cell { font-family: 'Courier New', monospace; font-weight: 700; font-size: 0.88rem; white-space: nowrap; text-align: center; display: block; }
    .jam-early { color: var(--success); }
    .jam-late  { color: var(--warning); }
    .status-col { width: 115px; }
    .status-badge {
      display: inline-block; border-radius: 5px; padding: 2px 9px;
      font-size: 0.66rem; font-weight: 700; white-space: nowrap;
    }
    .badge-hadir { background: rgba(40,199,111,.15); color: var(--success); }
    .badge-terlambat { background: rgba(255,159,67,.15); color: var(--warning); }
    .late-minutes { font-size: 0.66rem; color: var(--warning); display: block; margin-top: 1px; font-weight: 600; white-space: nowrap; }

    .empty-state {
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      padding: 3rem; color: var(--muted); gap: 0.5rem;
    }
    .empty-state .icon { font-size: 3rem; opacity: .4; }
    .empty-state p { font-size: 0.82rem; }

    /* ─── SCANNER PANEL ──────────────────────────────────── */
    .scanner-panel { display: flex; flex-direction: column; gap: 0; }

    .scanner-area {
      position: relative;
      background: linear-gradient(135deg, #0e1726 0%, #152238 100%);
      border-radius: 4px;
      overflow: hidden;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem 1.5rem;
      border: 1px dashed rgba(115, 103, 240, 0.25);
    }

    /* Counter Besar Futuristik */
    .counter-widget {
      text-align: center;
      margin-bottom: 2rem;
      position: relative;
    }
    .counter-widget .counter-title {
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 2px;
      margin-bottom: 0.5rem;
    }
    .counter-value {
      font-family: 'Courier New', Courier, monospace;
      font-size: 3rem;
      font-weight: 900;
      color: #fff;
      text-shadow: 0 0 10px rgba(115, 103, 240, 0.6), 0 0 20px rgba(115, 103, 240, 0.4);
      letter-spacing: -1px;
      line-height: 1;
      display: flex;
      align-items: baseline;
      justify-content: center;
      gap: 0.25rem;
    }
    .counter-value .current {
      color: #7367f0;
      font-size: 3.5rem;
    }
    .counter-value .slash {
      color: var(--muted);
      font-size: 2.2rem;
      opacity: 0.5;
    }
    .counter-value .total-cap {
      color: var(--muted);
      font-size: 2rem;
    }
    .counter-value .unit {
      font-size: 1rem;
      color: var(--muted);
      margin-left: 0.5rem;
      font-weight: 700;
    }

    /* Animasi RFID Melayang */
    .rfid-animation {
      position: relative;
      width: 140px;
      height: 140px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1.5rem;
    }
    .rfid-card {
      width: 90px;
      height: 60px;
      background: linear-gradient(135deg, #7367f0 0%, #a78bfa 100%);
      border-radius: 5px;
      box-shadow: 0 10px 25px rgba(115, 103, 240, 0.4), 0 0 15px rgba(115, 103, 240, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 2rem;
      animation: floatCard 4s ease-in-out infinite;
      position: relative;
      z-index: 2;
    }
    .rfid-card .wifi-icon {
      transform: rotate(90deg);
    }
    .rfid-scanner-base {
      position: absolute;
      bottom: 20px;
      width: 110px;
      height: 10px;
      background: #1e293b;
      border-radius: 99px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
      z-index: 1;
    }
    .rfid-scanner-base::after {
      content: '';
      position: absolute;
      top: -2px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 2px;
      background: var(--success);
      box-shadow: 0 0 8px var(--success);
      animation: basePulse 2s infinite;
    }
    .rfid-glow-ring {
      position: absolute;
      width: 120px;
      height: 120px;
      border: 2px solid rgba(115, 103, 240, 0.15);
      border-radius: 50%;
      animation: ripple 2.5s linear infinite;
    }
    .rfid-glow-ring:nth-child(2) {
      animation-delay: 1.25s;
    }

    @keyframes floatCard {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-12px) rotate(2deg); }
    }
    @keyframes basePulse {
      0%, 100% { opacity: 0.4; }
      50% { opacity: 1; }
    }
    @keyframes ripple {
      0% { transform: scale(0.6); opacity: 1; }
      100% { transform: scale(1.4); opacity: 0; }
    }

    /* ─── RESULT TOAST ───────────────────────────────────── */
    .result-toast {
      position: absolute; bottom: 0; left: 0; right: 0;
      padding: 0.8rem 1rem; transform: translateY(100%);
      transition: transform .35s cubic-bezier(.34,1.56,.64,1);
      z-index: 10;
    }
    .result-toast.show { transform: translateY(0); }
    .result-toast.success { background: rgba(40,199,111,.15); border-top: 2px solid var(--success); }
    .result-toast.warning { background: rgba(255,159,67,.15); border-top: 2px solid var(--warning); }
    .result-toast.error   { background: rgba(234,84,85,.15);  border-top: 2px solid var(--danger); }
    .result-toast-inner { display: flex; align-items: flex-start; gap: 0.75rem; }
    .result-icon { font-size: 1.6rem; flex-shrink: 0; }
    .result-name { font-weight: 800; font-size: 0.95rem; }
    .result-sub  { font-size: 0.75rem; color: var(--muted); margin-top: 2px; }
    .result-msg  { font-size: 0.75rem; margin-top: 4px; opacity: .7; }
    .result-bar  { height: 3px; background: rgba(255,255,255,.1); border-radius: 99px; margin-top: 0.5rem; overflow: hidden; }
    .result-bar-fill { height: 100%; background: var(--success); width: 0; }

    /* ─── SCAN LOG ───────────────────────────────────────── */
    .scan-info { padding: 0.7rem 1rem; border-top: 1px solid var(--border); flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; }
    .scan-count-wrap { font-size: 0.78rem; color: var(--muted); }
    .scan-count-wrap span { font-size: 1.2rem; font-weight: 800; color: var(--primary); }

    .scan-log-title { padding: 0.6rem 1rem; font-size: 0.7rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; border-bottom: 1px solid var(--border); flex-shrink: 0; }
    .scan-log { flex: 1; overflow-y: auto; }
    .scan-log-item {
      display: flex; align-items: center; gap: 0.6rem;
      padding: 0.55rem 1rem; border-bottom: 1px solid var(--border);
      animation: slideIn .3s ease;
    }
    @keyframes slideIn { from { opacity: 0; transform: translateX(16px); } to { opacity: 1; transform: none; } }
    .scan-log-item .log-avatar {
      width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
      background: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;
    }
    .scan-log-item .log-name  { font-weight: 600; font-size: 0.8rem; }
    .scan-log-item .log-kelas { font-size: 0.65rem; color: var(--muted); }
    .scan-log-item .log-jam   { font-family: monospace; font-size: 0.78rem; font-weight: 700; color: var(--success); margin-left: auto; white-space: nowrap; }

    /* ═══════════════════════════════════════════════════════
       RESPONSIVE BREAKPOINTS
       ═══════════════════════════════════════════════════════
       Desktop PC   : ≥1280px  → 3 kolom, full-height, no scroll
       Laptop       : 1024–1279px → 3 kolom, scanner lebih kecil
       Tablet Land  : 768–1023px  → 2 kolom atas + scanner bawah full-width
       Tablet Port  : 600–767px   → 1 kolom, scroll, panel tinggi auto
       Smartphone   : <600px      → 1 kolom compact, header ringkas
       ═══════════════════════════════════════════════════════ */

    /* ─── LAPTOP (1024–1279) ──────────────────────────────── */
    @media (max-width: 1279px) {
      .main { grid-template-columns: 1fr 1fr 300px; gap: 0.6rem; padding: 0.6rem; }
      .scan-frame { width: 160px; height: 160px; }
      .name-cell .name { max-width: 120px; }
    }

    /* ─── TABLET LANDSCAPE (768–1023) ────────────────────── */
    @media (max-width: 1023px) {
      html, body {
        overflow-y: auto; height: auto; max-height: none;
        display: block;
      }
      .header { padding: 0.6rem 1rem; }
      #live-clock { font-size: 1.5rem; }

      .main {
        grid-template-columns: 1fr 1fr;
        grid-template-rows: auto auto;
        flex: none; min-height: auto;
        gap: 0.6rem; padding: 0.6rem;
      }
      .scanner-col { grid-column: 1 / -1; }
      .panel { border-radius: 4px; }
      .panel-body { max-height: 340px; }
      .scanner-area { aspect-ratio: 16/9; }
      .scan-frame { width: 180px; height: 180px; }
    }

    /* ─── TABLET PORTRAIT (600–767) ──────────────────────── */
    @media (max-width: 767px) {
      .header {
        flex-wrap: wrap; justify-content: center; gap: 0.4rem;
        padding: 0.6rem 0.8rem; text-align: center;
      }
      .header-brand { order: 1; width: 100%; justify-content: center; }
      .header-center { order: 2; }
      .header-right  { order: 3; }
      .header-brand .logo-icon { width: 36px; height: 36px; font-size: 1.1rem; border-radius: 4px; }
      .header-brand h1 { font-size: 0.95rem; }
      .header-brand p  { font-size: 0.62rem; }
      #live-clock { font-size: 1.3rem; letter-spacing: 1px; }
      #live-date  { font-size: 0.62rem; }

      .main {
        grid-template-columns: 1fr;
        gap: 0.5rem; padding: 0.5rem;
      }
      .scanner-col { grid-column: auto; }
      .panel { border-radius: 4px; }
      .panel-header { padding: 0.7rem 0.9rem 0.55rem; }
      .panel-title  { font-size: 0.8rem; }
      .panel-body   { max-height: 300px; }

      .stat-chips { padding: 0.4rem 0.8rem; gap: 0.3rem; }
      .stat-chip  { font-size: 0.65rem; padding: 2px 8px; }

      .lb-table tbody td { padding: 0.4rem 0.6rem; font-size: 0.78rem; }
      .lb-table thead th { padding: 0.4rem 0.6rem; font-size: 0.6rem; }
      .name-cell .name { max-width: 110px; font-size: 0.78rem; }
      .jam-cell { font-size: 0.78rem; }
      .rank-cell { width: 28px; font-size: 0.85rem; }

      .scanner-area { aspect-ratio: 4/3; }
      .scan-frame { width: 160px; height: 160px; }

      .scan-info { padding: 0.5rem 0.8rem; }
      .scan-log-item { padding: 0.45rem 0.8rem; }
      .scan-log-item .log-avatar { width: 26px; height: 26px; font-size: 0.65rem; }

      .announce-bar { padding: 5px 0.8rem; }
      .announce-bar marquee { font-size: 0.72rem; }

      .live-badge { padding: 3px 10px; font-size: 0.65rem; }
    }

    /* ─── SMARTPHONE (<600px) ────────────────────────────── */
    @media (max-width: 599px) {
      .header {
        padding: 0.5rem 0.6rem; gap: 0.3rem;
      }
      .header-brand .logo-icon { width: 32px; height: 32px; font-size: 1rem; border-radius: 4px; box-shadow: 0 0 10px rgba(115,103,240,.3); }
      .header-brand h1 { font-size: 0.85rem; }
      .header-brand p  { display: none; } /* Sembunyikan subtitle di HP */
      #live-clock { font-size: 1.15rem; letter-spacing: 0.5px; }
      #live-date  { font-size: 0.58rem; }
      .header-right > div:last-child { display: none; } /* Sembunyikan teks "Refresh otomatis..." */

      .main { gap: 0.4rem; padding: 0.4rem; }
      .panel { border-radius: 4px; }
      .panel-header { padding: 0.55rem 0.7rem 0.45rem; }
      .panel-title  { font-size: 0.75rem; gap: 0.35rem; }
      .panel-body   { max-height: 260px; }

      .stat-chips { padding: 0.35rem 0.7rem; gap: 0.25rem; }
      .stat-chip  { font-size: 0.6rem; padding: 2px 6px; }
      .stat-chip .dot { width: 5px; height: 5px; }

      .lb-table tbody td { padding: 0.35rem 0.5rem; font-size: 0.72rem; }
      .lb-table thead th { padding: 0.35rem 0.5rem; font-size: 0.55rem; letter-spacing: 0.5px; }
      .name-cell .name { max-width: 90px; font-size: 0.72rem; }
      .name-cell .kelas-badge { font-size: 0.58rem; }
      .jam-cell { font-size: 0.72rem; }
      .rank-cell { width: 24px; font-size: 0.8rem; }
      .status-badge { padding: 1px 6px; font-size: 0.58rem; }
      .late-minutes { font-size: 0.58rem; }

      .scanner-area { aspect-ratio: 1/1; } /* Lebih compact: kotak */
      .scan-frame { width: 140px; height: 140px; }
      .scan-frame::before, .scan-frame::after,
      .scan-frame .corner-br, .scan-frame .corner-bl { width: 22px; height: 22px; }
      .scanner-idle .idle-icon { font-size: 2rem; }
      .scanner-idle p { font-size: 0.7rem; max-width: 160px; }
      #start-cam-btn { font-size: 0.78rem; padding: 0.5rem 1.2rem; border-radius: 4px; }

      .result-toast { padding: 0.6rem 0.8rem; }
      .result-icon { font-size: 1.2rem; }
      .result-name { font-size: 0.82rem; }
      .result-sub  { font-size: 0.65rem; }
      .result-msg  { font-size: 0.65rem; }

      .scan-info { padding: 0.4rem 0.7rem; }
      .scan-count-wrap { font-size: 0.68rem; }
      .scan-count-wrap span { font-size: 1rem; }
      .scan-log-title { padding: 0.4rem 0.7rem; font-size: 0.62rem; }
      .scan-log-item { padding: 0.35rem 0.7rem; gap: 0.4rem; }
      .scan-log-item .log-avatar { width: 24px; height: 24px; font-size: 0.6rem; }
      .scan-log-item .log-name  { font-size: 0.7rem; }
      .scan-log-item .log-kelas { font-size: 0.58rem; }
      .scan-log-item .log-jam   { font-size: 0.68rem; }

      .announce-bar { padding: 4px 0.6rem; }
      .announce-bar marquee { font-size: 0.65rem; }

      .empty-state { padding: 1.5rem; }
      .empty-state .icon { font-size: 2rem; }
      .empty-state p { font-size: 0.72rem; }

      /* Scanner status bar compact */
      #scanner-status-bar { padding: .4rem .7rem; }
      #scanner-status-bar span[id="hw-status-text"] { font-size: .62rem; }
    }

    /* ─── VERY SMALL PHONES (<400px) ─────────────────────── */
    @media (max-width: 399px) {
      .header-brand { gap: 0.5rem; }
      .header-brand .logo-icon { width: 28px; height: 28px; font-size: 0.85rem; border-radius: 4px; }
      .header-brand h1 { font-size: 0.78rem; }
      #live-clock { font-size: 1rem; }
      .live-badge { font-size: 0.58rem; padding: 2px 8px; }

      .panel-title { font-size: 0.7rem; }
      .stat-chip { font-size: 0.55rem; }
      .lb-table tbody td { font-size: 0.65rem; padding: 0.3rem 0.4rem; }
      .lb-table thead th { font-size: 0.5rem; padding: 0.3rem 0.4rem; }
      .name-cell .name { max-width: 70px; font-size: 0.65rem; }
      .jam-cell { font-size: 0.65rem; }
      .rank-cell { width: 20px; font-size: 0.72rem; }
    }
  </style>
</head>
<body>

<!-- ══ HEADER ══════════════════════════════════════════════════════ -->
<header class="header">
  <div class="header-brand">
    <div class="logo-icon">🏫</div>
    <div>
      <h1>{{ $namaSekolah }}</h1>
      <p>Papan Absensi Live · Akses Publik</p>
    </div>
  </div>

  <div class="header-center">
    <div id="live-clock">--:--:--</div>
    <div id="live-date">Memuat...</div>
  </div>

  <div class="header-right">
    <div class="live-badge">
      <span class="live-dot"></span> LIVE
    </div>
    <div style="font-size:.72rem; color:var(--muted);">
      Sinkronisasi <strong style="color:#fff;" id="sync-status">Real-time</strong>
    </div>
  </div>
</header>

@if($announcement)
<div class="announce-bar">
  <marquee scrollamount="4">📢 &nbsp; {{ $announcement }}</marquee>
</div>
@endif

<!-- ══ MAIN GRID ════════════════════════════════════════════════════ -->
<div class="main">

  <!-- ── PANEL 1: 10 PALING AWAL ─────────────────────────────────── -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">
        🏆 <span>10 Siswa Hadir Paling Awal</span>
      </div>
      <div style="font-size:.7rem; color:var(--muted);">{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</div>
    </div>

    <div class="panel-body">
      <table class="lb-table" id="table-awal">
        <thead><tr>
          <th class="rank-cell">#</th>
          <th class="name-cell">Nama Siswa</th>
          <th class="jam-col">Jam</th>
          <th class="status-col">Status</th>
        </tr></thead>
        <tbody id="tbody-awal">
          @forelse($leaderboardAwal as $i => $abs)
            @php
              $jamMasukSetting = \Carbon\Carbon::createFromTimeString($jamMasukCfg ?? '07:00');
              $jamSiswa   = \Carbon\Carbon::createFromTimeString($abs->jam);
              $selisih    = (int) $jamMasukSetting->diffInMinutes($jamSiswa, false);
              $isLate     = $selisih > $toleransi;
            @endphp
            <tr class="{{ $i < 3 ? 'top-3' : '' }} {{ $isLate ? 'late-row' : '' }}">
              <td class="rank-cell">{{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i+1)) }}</td>
              <td class="name-cell">
                <div class="name">{{ $abs->nama }}</div>
                <div class="kelas-badge">{{ $abs->kelas }}</div>
              </td>
              <td class="jam-cell {{ $isLate ? 'jam-late' : 'jam-early' }}">{{ $abs->jam }}</td>
              <td>
                @if($isLate)
                  <span class="status-badge badge-terlambat">⏰ Terlambat</span>
                  <span class="late-minutes">+{{ $selisih }} menit</span>
                @else
                  <span class="status-badge badge-hadir">✅ Hadir</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="4"><div class="empty-state"><span class="icon">🌅</span><p>Belum ada siswa yang hadir hari ini</p></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── PANEL 2: RIWAYAT SCAN TERBARU ────────────────────────── -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">
        🕐 <span>Riwayat Scan Terbaru</span>
      </div>
      <div style="font-size:.7rem; color: var(--muted);">Urutan scan dari yang paling baru</div>
    </div>

    <div class="panel-body">
      <table class="lb-table">
        <thead><tr>
          <th class="rank-cell">#</th>
          <th class="name-cell">Nama Siswa</th>
          <th class="jam-col">Jam</th>
          <th class="status-col">Status</th>
        </tr></thead>
        <tbody id="tbody-akhir">
          @forelse($leaderboardTerbaru as $i => $abs)
            @php
              $jamMasukSetting = \Carbon\Carbon::createFromTimeString($jamMasukCfg ?? '07:00');
              $jamSiswa   = \Carbon\Carbon::createFromTimeString($abs->jam);
              $selisih    = (int) $jamMasukSetting->diffInMinutes($jamSiswa, false);
              $isLate     = $selisih > $toleransi;
            @endphp
            <tr class="{{ $isLate ? 'late-row' : '' }}">
              <td class="rank-cell" style="color:var(--muted);">{{ $i+1 }}</td>
              <td class="name-cell">
                <div class="name">{{ $abs->nama }}</div>
                <div class="kelas-badge">{{ $abs->kelas }}</div>
              </td>
              <td class="jam-cell {{ $isLate ? 'jam-late' : '' }}">{{ $abs->jam }}</td>
              <td>
                @if($isLate)
                  <span class="status-badge badge-terlambat">⏰ Terlambat</span>
                  @if($selisih > 0)<span class="late-minutes">+{{ $selisih }} menit</span>@endif
                @else
                  <span class="status-badge badge-hadir">✅ Hadir</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="4"><div class="empty-state"><span class="icon">🌙</span><p>Belum ada data scan terbaru hari ini</p></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── PANEL 3: PUSAT KONTROL & COUNTER SCANNER ────────────────── -->
  <div class="panel scanner-col" style="position:relative; overflow:hidden;">
    <div class="panel-header">
      <div class="panel-title">🔌 <span>Pusat Kontrol &amp; Counter Scanner</span></div>
      <div style="display:flex;align-items:center;gap:.5rem;">
        <span id="hw-indicator" title="Status alat scanner fisik" style="font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:5px;background:rgba(40,199,111,.15);color:var(--success);border:1px solid rgba(40,199,111,.4);">🔌 Scanner Piket: AKTIF & Siap</span>
      </div>
    </div>

    <!-- Scanner Area (Hardware Only) -->
    <div class="scanner-area">
      <!-- Widget Counter Besar Futuristik -->
      <div class="counter-widget">
        <div class="counter-title">Total Siswa Hadir Hari Ini</div>
        <div class="counter-value">
          <span class="current" id="s-hadir-large">{{ $stats['hadir'] }}</span>
          <span class="slash">/</span>
          <span class="total-cap" id="large-total-kapasitas">{{ $totalKapasitasSiswa }}</span>
          <span class="unit">Siswa</span>
        </div>
      </div>

      <div class="stat-chips" id="stat-chips">
        <div class="stat-chip"><span class="dot" style="background:var(--success)"></span> Hadir: <strong id="s-hadir">{{ $stats['hadir'] }}</strong></div>
        <div class="stat-chip"><span class="dot" style="background:var(--info)"></span> Sakit: <strong id="s-sakit">{{ $stats['sakit'] }}</strong></div>
        <div class="stat-chip"><span class="dot" style="background:var(--warning)"></span> Izin: <strong id="s-izin">{{ $stats['izin'] }}</strong></div>
        <div class="stat-chip"><span class="dot" style="background:var(--danger)"></span> Alpha: <strong id="s-alpha">{{ $stats['alpha'] }}</strong></div>
        <div class="stat-chip"><span class="dot" style="background:#a78bfa"></span> Terlambat: <strong id="s-terlambat">{{ $stats['terlambat'] }}</strong></div>
      </div>

      <!-- RFID Animation -->
      <div class="rfid-animation">
        <div class="rfid-glow-ring"></div>
        <div class="rfid-glow-ring"></div>
        <div class="rfid-card">
          <span class="wifi-icon">📶</span>
        </div>
        <div class="rfid-scanner-base"></div>
      </div>

      <div style="text-align:center; color: var(--muted); font-size: 0.85rem; max-width: 280px; line-height: 1.4;">
        <p style="color: #fff; font-weight: 700; margin-bottom: 0.25rem;">SIAP SCANNING</p>
        <p>Silakan tap kartu RFID atau scan QR-Code siswa pada scanner</p>
      </div>

      <!-- Result toast -->
      <div class="result-toast" id="result-toast">
        <div class="result-toast-inner">
          <div class="result-icon" id="result-icon">✅</div>
          <div style="flex:1;min-width:0;">
            <div class="result-name" id="result-name">—</div>
            <div class="result-sub" id="result-sub">—</div>
            <div class="result-msg" id="result-msg">—</div>
          </div>
        </div>
        <div class="result-bar"><div class="result-bar-fill" id="result-bar-fill"></div></div>
      </div>
    </div>

    <!-- Input off-screen: tidak terlihat, selalu fokus, menangkap ketikan scanner fisik -->
    <input
      id="hw-scanner-input"
      type="text"
      autocomplete="off"
      spellcheck="false"
      tabindex="-1"
      aria-hidden="true"
      style="position:fixed;top:-9999px;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;"
    >

    <div class="scan-info" style="border-top:1px solid var(--border);">
      <div class="scan-count-wrap">Scan Sesi Ini:&nbsp;<span id="scan-count">0</span></div>
      <div style="display:flex;gap:.5rem;align-items:center;">
        <button id="toggle-sound-btn" title="Toggle suara" style="background:none;border:1px solid var(--border);border-radius:5px;padding:4px 8px;color:var(--muted);cursor:pointer;font-size:1rem;" onclick="toggleSound()">🔊</button>
      </div>
    </div>

  </div>

</div><!-- /main -->

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
// ─── DEVICE ID HANDSHAKE ──────────────────────────────────────────────────
(function() {
    const cookieName = 'device_uuid';
    function getCookie(name) {
        let value = "; " + document.cookie;
        let parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }
    if (!getCookie(cookieName)) {
        const uuid = 'DEV-' + Math.random().toString(36).substr(2, 9).toUpperCase() + '-' + Date.now().toString(36).toUpperCase();
        document.cookie = cookieName + "=" + uuid + "; path=/; max-age=" + (60*60*24*365*10);
        window.location.reload();
    }
})();

// ─── CONFIG ───────────────────────────────────────────────────────────────
const SCAN_URL       = '{{ route("public.live-board.scan") }}';
const LEADERBOARD_URL= '{{ route("public.live-board.leaderboard") }}';
const CSRF           = document.querySelector('meta[name="csrf-token"]').content;
const JAM_MASUK_CFG  = '{{ $jamMasukCfg }}';
const TOLERANSI_MENIT= {{ $toleransi }};
const REFRESH_MS     = 3000; // leaderboard auto-refresh (Real-time speed)
const DISMISS_MS     = 3800;  // toast auto-hide
const DEBOUNCE_MS    = 3000;  // anti-duplicate scan

// ─── CLOCK ────────────────────────────────────────────────────────────────
const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
function updateClock() {
  const now = new Date();
  const h = String(now.getHours()).padStart(2,'0');
  const m = String(now.getMinutes()).padStart(2,'0');
  const s = String(now.getSeconds()).padStart(2,'0');
  document.getElementById('live-clock').textContent = `${h}:${m}:${s}`;
  document.getElementById('live-date').textContent =
    `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
}
updateClock(); setInterval(updateClock, 1000);

// ─── SOUND ────────────────────────────────────────────────────────────────
let soundEnabled = true;
function toggleSound() {
  soundEnabled = !soundEnabled;
  document.getElementById('toggle-sound-btn').textContent = soundEnabled ? '🔊' : '🔇';
}

/**
 * Bel sukses  → nada bell ding dua-nada naik (DO–MI)
 * Bel gagal   → dua nada turun pendek (buzz-buzz)
 * Bel warning → satu nada tengah pendek
 */
function beep(type = 'success') {
  if (!soundEnabled) return;
  try {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;
    const ctx = new AudioCtx();

    function playTone(freq, startTime, duration, gainPeak = 0.5, curve = 'bell') {
      const osc  = ctx.createOscillator();
      const gain = ctx.createGain();
      // Bell = sine + harmonics via distortion-like shaping
      osc.type = 'sine';
      osc.frequency.setValueAtTime(freq, startTime);
      osc.connect(gain);
      gain.connect(ctx.destination);

      gain.gain.setValueAtTime(0, startTime);
      if (curve === 'bell') {
        // Fast attack, exponential decay = bell-like
        gain.gain.linearRampToValueAtTime(gainPeak, startTime + 0.005);
        gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
      } else {
        // Square-ish: short flat then cut
        gain.gain.linearRampToValueAtTime(gainPeak, startTime + 0.01);
        gain.gain.linearRampToValueAtTime(gainPeak * 0.8, startTime + duration - 0.02);
        gain.gain.linearRampToValueAtTime(0.001, startTime + duration);
      }
      osc.start(startTime);
      osc.stop(startTime + duration + 0.01);
    }

    const now = ctx.currentTime;
    if (type === 'success') {
      // Bel sukses: dua ding naik (Bell C5 → E5), harmonik ringan
      playTone(523.25, now,        0.55, 0.45, 'bell');  // C5
      playTone(1046.5, now,        0.45, 0.12, 'bell');  // C6 harmonic
      playTone(659.25, now + 0.22, 0.65, 0.45, 'bell');  // E5
      playTone(1318.5, now + 0.22, 0.55, 0.10, 'bell');  // E6 harmonic
    } else if (type === 'error') {
      // Bel gagal: dua nada turun pendek (buzz)
      playTone(330, now,        0.18, 0.40, 'square');
      playTone(220, now + 0.22, 0.22, 0.40, 'square');
    } else {
      // Warning: single mid tone
      playTone(440, now, 0.30, 0.35, 'bell');
    }
  } catch(_) {}
}

// ─── SCANNER VARS ─────────────────────────────────────────────────────────
let isProcessing = false, lastQR = '', lastQRTime = 0, scanCount = 0;

// ─── HANDLE SCAN → SERVER ─────────────────────────────────────────────────
async function handleScan(qrCode) {
  isProcessing = true;
  try {
    const resp = await fetch(SCAN_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify({ qr_code: qrCode }),
    });
    const data = await resp.json();
    if (data.success) {
      scanCount++;
      document.getElementById('scan-count').textContent = scanCount;
      showToast('success', '✅', data.siswa, data.message);
      beep('success');
      refreshLeaderboard();
    } else if (data.already) {
      showToast('warning', '⚠️', data.siswa, data.message);
      beep('error');
    } else {
      showToast('error', '❌', null, data.message ?? 'QR tidak dikenal.');
      beep('error');
    }
  } catch(e) {
    showToast('error', '❌', null, 'Gagal terhubung ke server. Coba lagi.');
    beep('error');
  }
}

// ─── TOAST ────────────────────────────────────────────────────────────────
let toastTimer = null;
function showToast(type, icon, siswa, msg) {
  const toast  = document.getElementById('result-toast');
  const barFill= document.getElementById('result-bar-fill');
  document.getElementById('result-icon').textContent = icon;
  document.getElementById('result-name').textContent = siswa?.nama ?? (type === 'error' ? 'Error' : 'Info');
  document.getElementById('result-sub').textContent  = siswa?.kelas ? `Kelas ${siswa.kelas} · ${siswa.jam}` : '';
  document.getElementById('result-msg').textContent  = msg;

  toast.className = `result-toast ${type} show`;
  barFill.style.transition = 'none'; barFill.style.width = '0%';
  barFill.style.background = type === 'success' ? 'var(--success)' : type === 'warning' ? 'var(--warning)' : 'var(--danger)';

  if (toastTimer) clearTimeout(toastTimer);
  requestAnimationFrame(() => {
    barFill.style.transition = `width ${DISMISS_MS}ms linear`;
    barFill.style.width = '100%';
  });
  toastTimer = setTimeout(() => {
    toast.classList.remove('show');
    isProcessing = false;
  }, DISMISS_MS);
}


// ─── LEADERBOARD AUTO-REFRESH ─────────────────────────────────────────────
function renderRows(rows, colClass) {
  const jamMasuk = JAM_MASUK_CFG.split(':');
  return rows.map((r, i) => {
    const [h, m] = (r.jam || '00:00').split(':').map(Number);
    const [bh, bm] = [parseInt(jamMasuk[0]), parseInt(jamMasuk[1])];
    const diff = (h * 60 + m) - (bh * 60 + bm);
    const isLate = diff > TOLERANSI_MENIT;
    const rank = r.rank || (i + 1);
    const medal = colClass === 'awal' && rank === 1 ? '🥇' : colClass === 'awal' && rank === 2 ? '🥈' : colClass === 'awal' && rank === 3 ? '🥉' : rank;
    const badge = isLate
      ? `<span class="status-badge badge-terlambat">⏰ Terlambat</span><span class="late-minutes">+${diff} mnt</span>`
      : `<span class="status-badge badge-hadir">✅ Hadir</span>`;
    return `<tr class="${colClass==='awal'&&rank<=3?'top-3':''} ${isLate?'late-row':''}">
      <td class="rank-cell">${medal}</td>
      <td class="name-cell"><div class="name">${r.nama}</div><div class="kelas-badge">${r.kelas}</div></td>
      <td><span class="jam-cell ${isLate?'jam-late':'jam-early'}">${r.jam}</span></td>
      <td class="status-col">${badge}</td>
    </tr>`;
  }).join('') || (colClass === 'awal' 
    ? `<tr><td colspan="4"><div class="empty-state"><span class="icon">🌅</span><p>Belum ada siswa yang hadir hari ini</p></div></td></tr>`
    : `<tr><td colspan="4"><div class="empty-state"><span class="icon">🌙</span><p>Belum ada data scan terbaru hari ini</p></div></td></tr>`);
}

async function refreshLeaderboard() {
  try {
    const resp = await fetch(LEADERBOARD_URL, { headers: { 'Accept': 'application/json' } });
    const data = await resp.json();
    document.getElementById('tbody-awal').innerHTML  = renderRows(data.awal, 'awal');
    document.getElementById('tbody-akhir').innerHTML = renderRows(data.terbaru, 'terbaru');
    if (data.stats) {
      document.getElementById('s-hadir').textContent     = data.stats.hadir     ?? 0;
      document.getElementById('s-hadir-large').textContent = data.stats.hadir   ?? 0;
      document.getElementById('s-sakit').textContent     = data.stats.sakit     ?? 0;
      document.getElementById('s-izin').textContent      = data.stats.izin      ?? 0;
      document.getElementById('s-alpha').textContent     = data.stats.alpha     ?? 0;
      document.getElementById('s-terlambat').textContent = data.stats.terlambat ?? 0;
    }
  } catch(_) {}
}

// Auto-refresh leaderboard every 3s (Real-time speed)
setInterval(refreshLeaderboard, REFRESH_MS);
</script>

<style>
  @keyframes hwPulse {
    0%   { box-shadow: 0 0 0 0 rgba(40,199,111,.5); }
    70%  { box-shadow: 0 0 0 7px rgba(40,199,111,0); }
    100% { box-shadow: 0 0 0 0 rgba(40,199,111,0); }
  }
  @keyframes hwPulseWarn {
    0%   { box-shadow: 0 0 0 0 rgba(255,159,67,.5); }
    70%  { box-shadow: 0 0 0 7px rgba(255,159,67,0); }
    100% { box-shadow: 0 0 0 0 rgba(255,159,67,0); }
  }
</style>
<script>
// ════════════════════════════════════════════════════════════════════════════
// HARDWARE QR SCANNER — PLUG & PLAY (PIKET ROOM)
// ════════════════════════════════════════════════════════════════════════════
// Alat scanner fisik USB/Bluetooth bekerja sebagai HID keyboard:
//   • Mengetik kode QR sangat cepat lalu menekan Enter
//   • Tidak perlu driver khusus — langsung bisa dipakai
//
// CARA KERJA:
//   1. Input off-screen (tidak terlihat) selalu terfokus & di-guard oleh timer
//   2. Saat scanner ketik karakter cepat → buffer terkumpul → proses saat Enter
//   3. Jika Enter tidak muncul, auto-commit setelah 200ms tidak ada karakter baru
//   4. Setiap 300ms sistem memastikan input tetap fokus (guard loop)

(function initPiketScanner() {
  const CHAR_INTERVAL_MAX = 100; // ms maks antar karakter scanner (scanner < 100ms, manusia > 200ms)
  const COMMIT_TIMEOUT_MS = 200; // commit otomatis jika tidak ada Enter setelah 200ms
  const REFOCUS_INTERVAL  = 300; // cek & refocus setiap 300ms
  const MIN_CODE_LENGTH   = 4;   // panjang minimum kode valid

  const hwInput    = document.getElementById('hw-scanner-input');
  const statusBar  = document.getElementById('scanner-status-bar');
  const statusText = document.getElementById('hw-status-text');
  const statusPulse= document.getElementById('hw-pulse');
  const hwIndicator= document.getElementById('hw-indicator');

  let buffer    = '';
  let lastCharAt= 0;
  let commitTmr = null;
  let guardTmr  = null;
  let scanCount2= 0; // local counter for this session

  // ── Status display ────────────────────────────────────────────────────────
  function setStatus(type) {
    const cfg = {
      ready:    { text: '🔌 Scanner Piket: AKTIF & Siap',      color: 'var(--success)', pulse: 'hwPulse',     barBg: '' },
      scanning: { text: '🔌 Scanner Piket: Memproses…',         color: '#a78bfa',        pulse: 'hwPulse',     barBg: 'rgba(115,103,240,.08)' },
      lost:     { text: '⚠️ Scanner: Fokus Hilang — Mengembalikan…', color: 'var(--warning)', pulse: 'hwPulseWarn', barBg: '' },
    };
    const s = cfg[type] ?? cfg.ready;
    if (statusText)  { statusText.textContent = s.text; statusText.style.color = s.color; }
    if (statusPulse) { statusPulse.style.background = s.color; statusPulse.style.animation = `${s.pulse} 2s infinite`; }
    if (statusBar && s.barBg !== '') statusBar.style.background = s.barBg;
    if (hwIndicator) { hwIndicator.textContent = type === 'scanning' ? '🔌 HW: Scanning…' : '🔌 HW: Piket Aktif'; hwIndicator.style.color = s.color; }
  }

  // ── Commit: proses kode yang terkumpul di buffer ──────────────────────────
  async function commitScan() {
    const code = buffer.trim();
    buffer = '';
    hwInput.value = '';
    if (commitTmr) { clearTimeout(commitTmr); commitTmr = null; }
    if (code.length < MIN_CODE_LENGTH || isProcessing) return;

    setStatus('scanning');
    await handleScan(code);
    setStatus('ready');
    ensureFocus();
  }

  // ── Input handler: terima karakter dari scanner ──────────────────────────
  hwInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      // Commit langsung saat Enter diterima
      buffer = hwInput.value.trim() || buffer;
      hwInput.value = '';
      commitScan();
      return;
    }
  });

  hwInput.addEventListener('input', function() {
    const now   = Date.now();
    const delta = now - lastCharAt;
    lastCharAt  = now;

    const val = hwInput.value;

    if (delta < CHAR_INTERVAL_MAX) {
      // Karakter datang cepat = alat scanner
      buffer = val;
      if (commitTmr) clearTimeout(commitTmr);
      commitTmr = setTimeout(commitScan, COMMIT_TIMEOUT_MS);
    } else {
      // Karakter lambat (manusia mengetik) — tetap tampung, tapi reset buffer
      buffer = val;
    }
  });

  // ── Guard: pastikan input SELALU fokus ────────────────────────────────────
  function ensureFocus() {
    if (document.activeElement !== hwInput && !document.hidden) {
      const prev = document.activeElement;
      hwInput.focus({ preventScroll: true });
      // Jika ada elemen lain yang butuh fokus (modal, dll), jangan rebut
      if (document.activeElement === hwInput) {
        setStatus('ready');
      } else {
        setStatus('lost');
      }
    }
  }

  // Jalankan guard setiap 300ms
  guardTmr = setInterval(ensureFocus, REFOCUS_INTERVAL);

  // Fokus awal
  setTimeout(ensureFocus, 400);

  // Kembalikan fokus saat tab aktif kembali
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) setTimeout(ensureFocus, 200);
  });

  // Klik di luar button → kembalikan fokus ke scanner input
  document.addEventListener('mouseup', function(e) {
    if (e.target.closest('button') || e.target.tagName === 'INPUT') return;
    setTimeout(ensureFocus, 50);
  });

  // Deteksi jika input kehilangan fokus
  hwInput.addEventListener('blur', () => {
    // Beri waktu 100ms sebelum menandai lost (mungkin fokus pindah ke elemen sah)
    setTimeout(() => {
      if (document.activeElement !== hwInput) setStatus('lost');
    }, 100);
  });
  hwInput.addEventListener('focus', () => setStatus('ready'));

  // Status awal
  setStatus('ready');
  console.log('[Piket Scanner] Sistem siap. Colokkan alat USB/Bluetooth QR scanner — langsung bisa scan.');
})();
</script>
</body>
</html>
