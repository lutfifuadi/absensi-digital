<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Live Absensi — {{ $namaSekolah }}</title>
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  @vite(['resources/css/das-theme.css'])
  @php
    $browserFonts = ['Courier New', 'Courier', 'Arial', 'Helvetica', 'Times New Roman', 'Times', 'Georgia', 'Verdana', 'Trebuchet MS', 'Impact', 'Comic Sans MS', 'Palatino', 'Bookman Old Style', 'monospace', 'serif', 'sans-serif'];
  @endphp
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:ital,wght@0,600;0,700;0,800;1,600;1,700&display=swap" rel="stylesheet">
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
      font-family: '{{ $liveFont }}', 'Product Sans', sans-serif !important;
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
      width: 42px; height: 42px; border-radius: 6px;
      background: rgba(115,103,240,0.15); border: 1px solid rgba(115,103,240,0.3); display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem; flex-shrink: 0;
      box-shadow: 0 0 18px rgba(115,103,240,.4);
      overflow: hidden;
    }
    .header-brand .logo-icon img {
      width: 100%; height: 100%; object-fit: contain; padding: 2px;
    }
    .header-brand h1 { font-size: 1.1rem; font-weight: 800; color: #fff; }
    .header-brand p  { font-size: 0.72rem; color: var(--muted); margin-top: 1px; }

    .header-center { text-align: center; }
    #live-clock { font-size: 1.8rem; font-weight: 900; letter-spacing: 2px; color: #fff; font-variant-numeric: tabular-nums; }
    #live-date  { font-size: 0.72rem; color: var(--muted); }

    .header-right { display: flex; align-items: center; gap: 0.75rem; }
    .segmented-control {
      display: inline-flex;
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 2px;
      gap: 2px;
    }
    .segmented-control button {
      background: transparent;
      border: none;
      color: var(--muted);
      padding: 5px 12px;
      font-size: 0.75rem;
      font-weight: 700;
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .segmented-control button:hover {
      color: #fff;
    }
    .segmented-control button.active {
      background: var(--primary);
      color: #fff;
      box-shadow: 0 2px 8px rgba(115,103,240,0.4);
    }
    .live-badge {
      display: flex; align-items: center; gap: 0.4rem;
      background: rgba(234,84,85,.15); border: 1px solid rgba(234,84,85,.4);
      border-radius: 5px; padding: 4px 12px; font-size: 0.72rem; font-weight: 700; color: var(--danger);
    }
    .live-dot { width: 7px; height: 7px; background: var(--danger); border-radius: 50%; animation: pulse 1.4s ease-in-out infinite; }
    @keyframes pulse { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:.4; transform:scale(1.4); } }

    .bottom-running-bar {
      grid-column: 2 / -1;
      grid-row: 2;
      background: linear-gradient(90deg, rgba(15, 22, 35, 0.95) 0%, rgba(115, 103, 240, 0.15) 50%, rgba(15, 22, 35, 0.95) 100%);
      border: 1px solid rgba(115, 103, 240, 0.3);
      border-radius: 4px;
      padding: 6px 1.2rem;
      display: flex;
      align-items: center;
      gap: 0.6rem;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }
    .bottom-running-bar .announce-icon {
      font-size: 0.9rem;
      color: #ff9f43;
      flex-shrink: 0;
      animation: pulse 1.5s ease-in-out infinite;
    }
    .bottom-running-bar marquee { font-size: 0.85rem; font-weight: 600; color: #f1f5f9; letter-spacing: 0.5px; }

    /* ─── MAIN GRID ──────────────────────────────────────── */
    .main {
      display: grid;
      grid-template-columns: 460px 1fr 1fr;
      grid-template-rows: 1fr auto;
      gap: 0.75rem;
      padding: 0.75rem;
      flex: 1;
      min-height: 0;
      overflow: hidden;
    }
    /* Scanner selalu di kolom 1 (kiri), span 2 baris */
    .scanner-col { grid-column: 1; grid-row: 1 / span 2; }


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
      flex: 1;
      min-height: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-evenly;
      padding: 1.5rem 1.25rem;
      border: 1px dashed rgba(115, 103, 240, 0.25);
    }

    /* Counter Besar Futuristik */
    .counter-widget {
      text-align: center;
      margin: 0.25rem 0 0.5rem;
      position: relative;
      z-index: 2;
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
      font-family: '{{ $liveCounterFont }}', 'Courier New', Courier, monospace;
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
      color: {{ $liveCounterColor }} !important;
      text-shadow: 0 0 10px {{ $liveCounterColor }}99, 0 0 20px {{ $liveCounterColor }}66 !important;
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

    /* ─── CYBER ANALOG CLOCK INSTRUMENT ───────────────────── */
    .analog-clock-wrapper {
      position: relative;
      margin: 0.4rem 0 0.6rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      z-index: 2;
    }
    .analog-case {
      width: 155px;
      height: 155px;
      border-radius: 12px;
      padding: 6px;
      background: conic-gradient(from 210deg, #1e1b4b 0deg, #7367f0 55deg, #00cfe8 120deg, #7367f0 190deg, #1e1b4b 260deg, #00cfe8 330deg, #1e1b4b 360deg);
      box-shadow: 0 10px 30px rgba(0,0,0,0.6), inset 0 0 0 1px rgba(255,255,255,0.2), 0 0 20px rgba(115,103,240,0.3);
      position: relative;
    }
    .analog-case .rivet {
      position: absolute;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: radial-gradient(circle at 35% 30%, #00cfe8, #7367f0 75%);
      box-shadow: 0 0 4px rgba(0,207,232,0.8);
    }
    .analog-case .rivet.n { top: 6px; left: 6px; }
    .analog-case .rivet.s { bottom: 6px; right: 6px; }
    .analog-case .rivet.e { top: 6px; right: 6px; }
    .analog-case .rivet.w { bottom: 6px; left: 6px; }

    .analog-face {
      position: relative;
      width: 100%;
      height: 100%;
      border-radius: 8px;
      overflow: hidden;
      background: radial-gradient(circle at 50% 40%, rgba(21, 34, 56, 0.88) 0%, rgba(14, 23, 38, 0.94) 80%);
      backdrop-filter: blur(8px);
      box-shadow: inset 0 0 15px rgba(0,0,0,0.8);
    }
    .analog-face::before {
      content: "";
      position: absolute; inset: 0;
      background: repeating-conic-gradient(from 0deg, rgba(255,255,255,0.03) 0deg 0.6deg, transparent 0.6deg 5deg);
      mix-blend-mode: screen;
    }
    .analog-face::after {
      content: "";
      position: absolute;
      top: -25%; left: -35%;
      width: 85%; height: 65%;
      background: linear-gradient(135deg, rgba(255,255,255,0.12), rgba(255,255,255,0) 65%);
      transform: rotate(-18deg);
      pointer-events: none;
    }

    .analog-face .tick {
      position: absolute;
      left: 50%; top: 3%;
      width: 2px; height: 7px;
      background: rgba(255,255,255,0.3);
      transform-origin: 50% 65px;
      border-radius: 1px;
    }
    .analog-face .tick.major {
      width: 3px; height: 10px;
      background: #00cfe8;
      transform-origin: 50% 65px;
      box-shadow: 0 0 6px rgba(0,207,232,0.6);
    }

    .analog-face .dial-label {
      position: absolute;
      top: 32%; left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      color: var(--muted);
      pointer-events: none;
    }
    .analog-face .dial-label .city {
      font-size: 9px;
      font-weight: 800;
      color: #7367f0;
      letter-spacing: 2px;
    }
    .analog-face .dial-label .zone {
      font-size: 7px;
      color: var(--muted);
      letter-spacing: 1px;
    }

    .analog-face .hand {
      position: absolute;
      left: 50%; top: 50%;
      transform-origin: 50% 100%;
      filter: drop-shadow(0 3px 6px rgba(0,0,0,0.9));
      transition: transform 0.25s cubic-bezier(0.4, 1.4, 0.4, 1);
    }
    .analog-face .hour-hand {
      width: 8px; height: 36px;
      margin-left: -4px; margin-top: -36px;
      background: linear-gradient(180deg, #ffd700 0%, #ff9f43 100%);
      box-shadow: 0 0 10px rgba(255, 184, 0, 0.85);
      clip-path: polygon(50% 0%, 75% 18%, 60% 100%, 40% 100%, 25% 18%);
      border-radius: 4px;
      z-index: 3;
    }
    .analog-face .minute-hand {
      width: 5px; height: 50px;
      margin-left: -2.5px; margin-top: -50px;
      background: linear-gradient(180deg, #ffd700 0%, #ff9f43 100%);
      box-shadow: 0 0 10px rgba(255, 184, 0, 0.85);
      clip-path: polygon(50% 0%, 70% 12%, 58% 100%, 42% 100%, 30% 12%);
      border-radius: 3px;
      z-index: 4;
    }
    .analog-face .second-hand {
      width: 2px; height: 56px;
      margin-left: -1px; margin-top: -56px;
      background: #ea5455;
      box-shadow: 0 0 10px #ea5455;
      border-radius: 2px;
      z-index: 5;
    }
    .analog-face .second-hand::after {
      content: "";
      position: absolute;
      left: 50%; bottom: -12px;
      width: 6px; height: 12px;
      background: #ea5455;
      transform: translateX(-50%);
      border-radius: 2px;
    }

    .analog-cap {
      position: absolute;
      left: 50%; top: 50%;
      width: 12px; height: 12px;
      transform: translate(-50%, -50%);
      border-radius: 50%;
      background: radial-gradient(circle at 35% 30%, #00cfe8, #7367f0 100%);
      box-shadow: 0 0 0 2px rgba(0,0,0,0.6), 0 0 8px rgba(0,207,232,0.8);
      z-index: 6;
    }

    .analog-plaque {
      display: flex;
      align-items: center;
      margin-top: 8px;
      border-radius: 6px;
      overflow: hidden;
      border: 1px solid rgba(115,103,240,0.4);
      box-shadow: 0 4px 12px rgba(0,0,0,0.4);
      background: #090e17;
    }
    .analog-plaque .plaque-time {
      color: #00cfe8;
      font-family: 'JetBrains Mono', monospace;
      font-weight: 700;
      font-size: 0.85rem;
      letter-spacing: 1px;
      padding: 3px 10px;
      text-shadow: 0 0 8px rgba(0,207,232,0.4);
    }
    .analog-plaque .plaque-chip {
      background: linear-gradient(135deg, #7367f0, #a78bfa);
      color: #fff;
      font-size: 0.65rem;
      font-weight: 800;
      padding: 4px 8px;
      letter-spacing: 1px;
    }

    /* ─── RESULT TOAST — Translucent per-type + Fade ──────────── */
    .result-toast {
      position: relative;
      width: 100%;
      margin-top: 1rem;
      padding: 0.8rem 1rem;
      border-radius: 8px;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.2s ease, visibility 0.2s ease, transform 0.2s ease;
      transform: translateY(-6px);
      z-index: 10;
    }
    .result-toast.show { opacity: 1; visibility: visible; transform: translateY(0); }
    .result-toast.success {
      background: rgba(40,199,111,.85);
      border-top: 2px solid var(--success);
      backdrop-filter: blur(10px);
    }
    .result-toast.warning {
      background: rgba(255,159,67,.85);
      border-top: 2px solid var(--warning);
      backdrop-filter: blur(10px);
    }
    .result-toast.error {
      background: rgba(234,84,85,.85);
      border-top: 2px solid var(--danger);
      backdrop-filter: blur(10px);
    }
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

    /* ─── HOLIDAY BANNER ──────────────────────────────────── */
    .holiday-banner {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 3rem 2rem;
      background: linear-gradient(135deg, rgba(255, 159, 67, 0.08) 0%, rgba(234, 84, 85, 0.08) 100%);
      border: 1px dashed rgba(255, 159, 67, 0.3);
      border-radius: 4px;
      text-align: center;
      flex: 1;
      min-height: 0;
      overflow: hidden;
      position: relative;
    }
    .holiday-banner::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse at 50% 40%, rgba(255, 159, 67, 0.06) 0%, transparent 70%);
      pointer-events: none;
    }
    .holiday-banner .holiday-icon {
      font-size: 4.5rem;
      margin-bottom: 1rem;
      opacity: 0.7;
      filter: drop-shadow(0 0 20px rgba(255, 159, 67, 0.3));
      animation: holidayPulse 3s ease-in-out infinite;
    }
    .holiday-banner .holiday-title {
      font-size: 1.6rem;
      font-weight: 900;
      color: var(--warning);
      margin-bottom: 0.5rem;
      letter-spacing: 2px;
      text-shadow: 0 0 20px rgba(255, 159, 67, 0.3);
    }
    .holiday-banner .holiday-reason {
      font-size: 1rem;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 0.35rem;
    }
    .holiday-banner .holiday-msg {
      font-size: 0.85rem;
      color: var(--muted);
      font-weight: 500;
    }
    @keyframes holidayPulse {
      0%, 100% { transform: scale(1); opacity: 0.7; }
      50% { transform: scale(1.05); opacity: 0.9; }
    }

    /* ─── RESPONSIVE HOLIDAY BANNER ───────────────────────── */
    @media (max-width: 767px) {
      .holiday-banner { padding: 2rem 1.5rem; }
      .holiday-banner .holiday-icon { font-size: 3.5rem; }
      .holiday-banner .holiday-title { font-size: 1.2rem; letter-spacing: 1px; }
      .holiday-banner .holiday-reason { font-size: 0.88rem; }
      .holiday-banner .holiday-msg { font-size: 0.78rem; }
    }
    @media (max-width: 599px) {
      .holiday-banner { padding: 1.5rem 1rem; }
      .holiday-banner .holiday-icon { font-size: 2.8rem; margin-bottom: 0.75rem; }
      .holiday-banner .holiday-title { font-size: 1rem; }
      .holiday-banner .holiday-reason { font-size: 0.82rem; }
      .holiday-banner .holiday-msg { font-size: 0.72rem; }
    }
  </style>
</head>
<body>

<!-- ══ HEADER ══════════════════════════════════════════════════════ -->
<header class="header">
  <div class="header-brand">
    <div class="logo-icon">
      @if(!empty($logoSekolah))
        <img src="{{ $logoSekolah }}" alt="Logo {{ $namaSekolah }}">
      @else
        🏫
      @endif
    </div>
    <div>
      <h1>{{ $namaSekolah }}</h1>
      <p>Papan Absensi Live · Akses Publik</p>
    </div>
  </div>

  <div class="header-center" style="text-align: center;">
    <div id="session-status-badge" style="display: flex; align-items: center; justify-content: center; gap: 0.4rem; margin-bottom: 4px;">
      <span id="session-pill" style="font-size: 0.68rem; font-weight: 800; padding: 2px 9px; border-radius: 99px; background: rgba(115, 103, 240, 0.15); color: #a78bfa; border: 1px solid rgba(115, 103, 240, 0.4); letter-spacing: 0.5px;">☀️ SESI MASUK</span>
      <span id="live-date" style="font-size: 0.72rem; color: var(--muted); font-weight: 600;">Memuat...</span>
    </div>

    <!-- 3 Timeline Badges + Live Countdown Inline -->
    <div class="timeline-badges" style="display: flex; align-items: center; justify-content: center; gap: 0.35rem; font-size: 0.68rem; font-weight: 700; flex-wrap: wrap;">
      <div class="tl-chip" title="Pintu scanner mulai aktif menerima absen" style="background: rgba(0, 207, 232, 0.1); border: 1px solid rgba(0, 207, 232, 0.3); color: #00cfe8; padding: 1px 7px; border-radius: 5px; display: flex; align-items: center; gap: 3px;">
        <span>🔓 Mulai:</span>
        <strong style="color: #fff; font-family: 'JetBrains Mono', monospace;">{{ $jamMulaiAbsensi ?? '06:00' }}</strong>
      </div>
      <span style="color: var(--muted); opacity: 0.4; font-size: 0.6rem;">➔</span>
      <div class="tl-chip" title="Jam bel masuk (Batas tepat waktu)" style="background: rgba(115, 103, 240, 0.15); border: 1px solid rgba(115, 103, 240, 0.4); color: #a78bfa; padding: 1px 7px; border-radius: 5px; display: flex; align-items: center; gap: 3px;">
        <span>⏰ Tepat Waktu:</span>
        <strong style="color: #fff; font-family: 'JetBrains Mono', monospace;">{{ $jamMasukCfg }}</strong>
      </div>
      <span style="color: var(--muted); opacity: 0.4; font-size: 0.6rem;">➔</span>
      <div class="tl-chip" title="Batas akhir toleransi keterlambatan" style="background: rgba(255, 159, 67, 0.15); border: 1px solid rgba(255, 159, 67, 0.4); color: var(--warning); padding: 1px 7px; border-radius: 5px; display: flex; align-items: center; gap: 3px;">
        <span>⚠️ Batas:</span>
        <strong style="color: #fff; font-family: 'JetBrains Mono', monospace;" id="tl-batas-time">--:--</strong>
      </div>
      <span style="color: var(--muted); opacity: 0.3; margin: 0 2px;">|</span>
      <div id="session-countdown-wrap" style="font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center;">
        <span id="session-countdown" style="color: var(--success); font-family: 'JetBrains Mono', monospace;">⏱️ Sisa Waktu: --:--</span>
      </div>
    </div>
  </div>

  <div class="header-right">
    @if(!$isHariLibur)
    <div class="segmented-control">
      <button class="{{ $mode === 'otomatis' ? 'active' : '' }}" onclick="switchMode('otomatis')">⏰ Otomatis</button>
      <button class="{{ $mode === 'masuk' ? 'active' : '' }}" onclick="switchMode('masuk')">☀️ Masuk</button>
      <button class="{{ $mode === 'pulang' ? 'active' : '' }}" onclick="switchMode('pulang')">🌙 Pulang</button>
    </div>
    @else
    <div class="segmented-control" style="opacity: 0.4; pointer-events: none;">
      <button class="active" disabled>🗓️ Hari Libur</button>
    </div>
    @endif
    <div class="live-badge">
      <span class="live-dot"></span> LIVE
    </div>
    <div style="font-size:.72rem; color:var(--muted);">
      Sinkronisasi <strong style="color:#fff;" id="sync-status">Real-time</strong>
    </div>
  </div>
</header>

<!-- ══ MAIN GRID ════════════════════════════════════════════════════ -->
<div class="main">

  <!-- ── PANEL 1: 10 PALING AWAL ─────────────────────────────────── -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">
        @if($mode === 'pulang')
          🏆 <span>10 Pulang Paling Awal</span>
        @else
          🏆 <span>10 Hadir Paling Awal</span>
        @endif
      </div>
      <div style="font-size:.7rem; color:var(--muted);">{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</div>
    </div>

    <div class="panel-body">
      <table class="lb-table" id="table-awal">
        <thead><tr>
          <th class="rank-cell">#</th>
          <th class="name-cell">Nama / Identitas</th>
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
            <tr class="{{ $i < 3 ? 'top-3' : '' }} {{ ($mode !== 'pulang' && $isLate) ? 'late-row' : '' }}">
              <td class="rank-cell">{{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i+1)) }}</td>
              <td class="name-cell">
                <div class="name">{{ $abs->nama }}</div>
                <div class="kelas-badge">{{ $abs->kelas }}</div>
              </td>
              <td class="jam-cell {{ ($mode !== 'pulang' && $isLate) ? 'jam-late' : 'jam-early' }}">{{ \Carbon\Carbon::parse($abs->jam)->format('H:i:s') }}</td>
              <td>
                @if($mode === 'pulang')
                  <span class="status-badge badge-hadir">✅ Pulang</span>
                @elseif($isLate)
                  <span class="status-badge badge-terlambat">⏰ Terlambat</span>
                  <span class="late-minutes">+{{ $selisih }} menit</span>
                @else
                  <span class="status-badge badge-hadir">✅ Hadir</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="4"><div class="empty-state"><span class="icon">🌅</span><p>Belum ada data hadir hari ini</p></div></td></tr>
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
          <th class="name-cell">Nama / Identitas</th>
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
            <tr class="{{ ($mode !== 'pulang' && $isLate) ? 'late-row' : '' }}">
              <td class="rank-cell" style="color:var(--muted);">{{ $i+1 }}</td>
              <td class="name-cell">
                <div class="name">{{ $abs->nama }}</div>
                <div class="kelas-badge">{{ $abs->kelas }}</div>
              </td>
              <td class="jam-cell {{ ($mode !== 'pulang' && $isLate) ? 'jam-late' : '' }}">{{ \Carbon\Carbon::parse($abs->jam)->format('H:i:s') }}</td>
              <td>
                @if($mode === 'pulang')
                  <span class="status-badge badge-hadir">✅ Pulang</span>
                @elseif($isLate)
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
  @if(!$isHariLibur)
  <div class="panel scanner-col" style="position:relative; overflow:hidden;">
    <div class="panel-header">
      <div class="panel-title">🔌 <span>Pusat Kontrol &amp; Counter Scanner</span></div>
      <div style="display:flex;align-items:center;gap:.5rem;">
        <span id="hw-indicator" title="Status alat scanner fisik" style="font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:5px;background:rgba(40,199,111,.15);color:var(--success);border:1px solid rgba(40,199,111,.4);">🔌 Scanner Piket: AKTIF & Siap</span>
      </div>
    </div>

    <!-- Scanner Area (Hardware Only) -->
    <div class="scanner-area">
      <!-- Watermark Logo Sekolah (Presisi Centered & Middle — 105% Bounds) -->
      <div class="watermark-logo" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; opacity: 0.10; z-index: 0; padding: 0; overflow: hidden;">
        @if(!empty($logoSekolah))
          <img src="{{ $logoSekolah }}" alt="Watermark Logo Sekolah" style="width: 105%; height: 105%; max-width: none; max-height: none; object-fit: contain; filter: brightness(125%) drop-shadow(0 0 20px rgba(115,103,240,0.25));">
        @else
          <span style="font-size: 335px; line-height: 1; opacity: 0.8;">🏫</span>
        @endif
      </div>

      <!-- Widget Counter Besar Futuristik -->
      <div class="counter-widget">
        @if($mode === 'pulang')
          <div class="counter-title">Total Civitas Pulang Hari Ini</div>
        @else
          <div class="counter-title">Total Kehadiran Hari Ini</div>
        @endif
        <div class="counter-value">
          <span class="current" id="s-hadir-large">{{ $stats['hadir'] }}</span>
          <span class="slash">/</span>
          <span class="total-cap" id="large-total-kapasitas">{{ $totalKapasitasSiswa }}</span>
          <span class="unit">Orang</span>
        </div>
      </div>

      <!-- Separate Breakdown per Role (Siswa, Guru, Staff TU) -->
      <div class="role-stat-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; width: 100%; margin-bottom: 0.75rem; padding: 0 0.25rem; position: relative; z-index: 2;">
        <div class="role-stat-card" style="background: rgba(115, 103, 240, 0.08); border: 1px solid rgba(115, 103, 240, 0.25); border-radius: 8px; padding: 0.5rem 0.4rem; text-align: center; backdrop-filter: blur(4px);">
          <div style="font-size: 0.68rem; color: #a78bfa; font-weight: 800; text-transform: uppercase; margin-bottom: 3px; letter-spacing: 0.5px;">🎓 Siswa</div>
          <div style="font-size: 1.1rem; font-weight: 900; color: #fff; font-family: 'Courier New', monospace; letter-spacing: -0.5px;">
            <span id="s-siswa-hadir" style="color: #7367f0;">{{ $stats['siswa_hadir'] ?? 0 }}</span><span style="font-size: 0.75rem; color: var(--muted); opacity: 0.8;">/</span><span id="s-siswa-total" style="font-size: 0.8rem; color: var(--muted);">{{ $stats['siswa_total'] ?? 0 }}</span>
          </div>
        </div>

        <div class="role-stat-card" style="background: rgba(40, 199, 111, 0.08); border: 1px solid rgba(40, 199, 111, 0.25); border-radius: 8px; padding: 0.5rem 0.4rem; text-align: center; backdrop-filter: blur(4px);">
          <div style="font-size: 0.68rem; color: #28c76f; font-weight: 800; text-transform: uppercase; margin-bottom: 3px; letter-spacing: 0.5px;">👨‍🏫 Guru</div>
          <div style="font-size: 1.1rem; font-weight: 900; color: #fff; font-family: 'Courier New', monospace; letter-spacing: -0.5px;">
            <span id="s-guru-hadir" style="color: #28c76f;">{{ $stats['guru_hadir'] ?? 0 }}</span><span style="font-size: 0.75rem; color: var(--muted); opacity: 0.8;">/</span><span id="s-guru-total" style="font-size: 0.8rem; color: var(--muted);">{{ $stats['guru_total'] ?? 0 }}</span>
          </div>
        </div>
        <div class="role-stat-card" style="background: rgba(0, 207, 232, 0.08); border: 1px solid rgba(0, 207, 232, 0.25); border-radius: 8px; padding: 0.5rem 0.4rem; text-align: center; backdrop-filter: blur(4px);">
          <div style="font-size: 0.68rem; color: #00cfe8; font-weight: 800; text-transform: uppercase; margin-bottom: 3px; letter-spacing: 0.5px;">💼 Staff TU</div>
          <div style="font-size: 1.1rem; font-weight: 900; color: #fff; font-family: 'Courier New', monospace; letter-spacing: -0.5px;">
            <span id="s-staff-hadir" style="color: #00cfe8;">{{ $stats['staff_hadir'] ?? 0 }}</span><span style="font-size: 0.75rem; color: var(--muted); opacity: 0.8;">/</span><span id="s-staff-total" style="font-size: 0.8rem; color: var(--muted);">{{ $stats['staff_total'] ?? 0 }}</span>
          </div>
        </div>
      </div>

      <!-- Tahun Pelajaran & Slogan Header (Mencolok) -->
      <div class="tp-slogan-wrapper" style="text-align: center; margin-top: 0.3rem; margin-bottom: 0.5rem; position: relative; z-index: 2;">
        <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 4px 14px; border-radius: 5px; background: linear-gradient(135deg, rgba(115, 103, 240, 0.25) 0%, rgba(167, 139, 250, 0.15) 100%); border: 1px solid rgba(167, 139, 250, 0.5); box-shadow: 0 0 12px rgba(115, 103, 240, 0.35); margin-bottom: 5px;">
          <span style="font-size: 0.88rem; font-weight: 900; color: #ffffff; text-shadow: 0 0 10px rgba(167, 139, 250, 0.8); letter-spacing: 0.8px;">🎓 TAHUN AJARAN {{ $tahunAktif->nama ?? '2025/2026' }} @if(!empty($tahunAktif->semester))({{ $tahunAktif->semester }})@endif</span>
        </div>
        @if(!empty($sloganSekolah))
          <div style="font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif; font-size: 0.78rem; color: #e2d9f3; font-style: italic; font-weight: 700; letter-spacing: 0.5px; text-shadow: 0 0 10px rgba(167, 139, 250, 0.4); margin-top: 1px;">
            “{{ $sloganSekolah }}”
          </div>
        @endif
      </div>

      <!-- INDONESIA Luxury Sport Watch (same as Admin Dashboard) -->
      <div class="chronos-watch-container" style="transform: scale(0.78); margin: -18px 0;">

        <!-- TOP STRAP LUG -->
        <div class="chronos-strap-lug top-lug"></div>

        <!-- MAIN WATCH BODY -->
        <div class="chronos-body">

          <!-- SIDE CROWN & PUSHERS -->
          <div class="chronos-side-buttons">
            <div class="side-btn top-btn"><div class="btn-grip"></div></div>
            <div class="side-btn mid-crown"><div class="btn-grip"></div></div>
            <div class="side-btn bot-btn"><div class="btn-grip"></div></div>
          </div>

          <!-- OCTAGONAL BEZEL WITH CORNER SCREWS -->
          <div class="chronos-bezel-outer">
            <div class="outer-screw sc-1"></div>
            <div class="outer-screw sc-2"></div>
            <div class="outer-screw sc-3"></div>
            <div class="outer-screw sc-4"></div>
            <div class="outer-screw sc-5"></div>
            <div class="outer-screw sc-6"></div>
            <div class="outer-screw sc-7"></div>
            <div class="outer-screw sc-8"></div>

            <!-- CIRCULAR BEZEL RING -->
            <div class="chronos-bezel-ring">
              <div id="lbBezelTicks"></div>

              <!-- CARBON FIBER DIAL FACE -->
              <div class="chronos-dial-face" id="lbDial">
                <div class="dial-inner-ring"></div>
                <div id="lbLumiBars"></div>

                <div class="chronos-logo-area">
                  <div class="brand-emblem">◈</div>
                  <div class="brand-name">INDONESIA</div>
                  <div class="brand-auto">{{ $zoneAbbr ?? 'WIB' }} · {{ $utcOffset ?? 'UTC+7' }}</div>
                </div>

                <!-- LEFT SUB-DIAL (9 o'clock — Orange) -->
                <div class="chronos-sub sub-left">
                  <div id="lbSubLeftTicks"></div>
                  <div class="sub-hand" id="lbSubLeftHand"></div>
                  <div class="sub-center-dot"></div>
                </div>

                <!-- RIGHT SUB-DIAL (3 o'clock — Blue) -->
                <div class="chronos-sub sub-right">
                  <div id="lbSubRightTicks"></div>
                  <div class="sub-hand" id="lbSubRightHand"></div>
                  <div class="sub-center-dot"></div>
                </div>

                <!-- BOTTOM LCD DISPLAY -->
                <div class="chronos-lcd">
                  <div class="lcd-inner">
                    <span class="lcd-day" id="lbLcdDay">RAB 02</span>
                    <span class="lcd-sep">|</span>
                    <span class="lcd-time" id="lbLcdTime">00:00:00</span>
                  </div>
                </div>

                <div class="hand hour-hand" id="lbHourHand"></div>
                <div class="hand minute-hand" id="lbMinuteHand"></div>
                <div class="hand second-hand" id="lbSecondHand"></div>
                <div class="chronos-cap"></div>
                <div class="glass-glare"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- BOTTOM STRAP LUG -->
        <div class="chronos-strap-lug bot-lug"></div>
      </div>

      <div style="text-align:center; color: var(--muted); font-size: 0.85rem; max-width: 280px; line-height: 1.4; position: relative; z-index: 2; margin-top: 1.25rem;">
        <p style="color: #fff; font-weight: 700; margin-bottom: 0.25rem; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
          <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--success); box-shadow: 0 0 8px var(--success); animation: pulse 1.4s ease-in-out infinite;"></span>
          SIAP SCANNING
        </p>
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



  </div>
  @else
  <!-- ── HOLIDAY BANNER ─────────────────────────────────────────── -->
  <div class="panel scanner-col" style="position:relative; overflow:hidden;">
    <div class="panel-header">
      <div class="panel-title">🗓️ <span>Status Hari Ini</span></div>
      <div style="font-size:.7rem; color:var(--muted);">{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</div>
    </div>
    <div class="holiday-banner">
      <div style="position: relative; z-index: 1;">
        <div class="holiday-icon">🗓️</div>
        <h2 class="holiday-title">HARI INI LIBUR</h2>
        <p class="holiday-reason">{{ $liburReason }}</p>
        <p class="holiday-msg">Absensi tidak berlaku hari ini</p>
      </div>
    </div>
  </div>
  @endif

  <!-- ── BOTTOM RUNNING TEXT (Spans Columns 2 & 3) ──────────────── -->
  @php
    $runningText = $announcement ?? ('✨ Selamat Datang di Live Presensi ' . $namaSekolah . ' — Budayakan Disiplin & Tepat Waktu Demi Masa Depan Gemilang! ✨');
  @endphp
  <div class="bottom-running-bar">
    <div class="announce-icon">📢</div>
    <marquee scrollamount="5" onmouseover="this.stop()" onmouseout="this.start()">{{ $runningText }}</marquee>
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
const DISMISS_MS     = 800;  // toast auto-hide
const DEBOUNCE_MS    = 3000;  // anti-duplicate scan
const CURRENT_MODE   = '{{ $mode }}';
const APP_TIMEZONE   = '{{ $ianaTimezone ?? "Asia/Jakarta" }}';
const IS_HARI_LIBUR  = {{ $isHariLibur ? 'true' : 'false' }};

function switchMode(mode) {
  window.location.search = '?mode=' + mode;
}

// ─── SESSION COUNTDOWN & HEADER WIDGET ──────────────────────────────────────
const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

function updateSessionCountdown() {
  const dateEl = document.getElementById('live-date');
  const pillEl = document.getElementById('session-pill');
  const cdEl   = document.getElementById('session-countdown');
  
  const now = new Date();
  if (dateEl) {
    dateEl.textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
  }

  if (!cdEl || !pillEl) return;

  const mode = CURRENT_MODE;
  
  const [targetH, targetM] = JAM_MASUK_CFG.split(':').map(Number);
  const targetDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), targetH, targetM, 0);
  const toleransiDate = new Date(targetDate.getTime() + TOLERANSI_MENIT * 60 * 1000);

  const batasEl = document.getElementById('tl-batas-time');
  if (batasEl) {
    const bH = String(toleransiDate.getHours()).padStart(2, '0');
    const bM = String(toleransiDate.getMinutes()).padStart(2, '0');
    batasEl.textContent = `${bH}:${bM}`;
  }

  let isPulang = (mode === 'pulang');
  if (mode === 'otomatis') {
    isPulang = (now.getHours() >= 12);
  }

  if (isPulang) {
    pillEl.textContent = '🌙 SESI PULANG';
    pillEl.style.background = 'rgba(0, 207, 232, 0.15)';
    pillEl.style.color = '#00cfe8';
    pillEl.style.borderColor = 'rgba(0, 207, 232, 0.4)';
    cdEl.style.color = '#00cfe8';
    cdEl.textContent = '🌙 Presensi Pulang Aktif';
    return;
  }

  pillEl.textContent = '☀️ SESI MASUK';
  pillEl.style.background = 'rgba(115, 103, 240, 0.15)';
  pillEl.style.color = '#a78bfa';
  pillEl.style.borderColor = 'rgba(115, 103, 240, 0.4)';

  const diffMs = targetDate.getTime() - now.getTime();

  if (diffMs > 0) {
    const mins = Math.floor(diffMs / 60000);
    const secs = Math.floor((diffMs % 60000) / 1000);
    cdEl.style.color = 'var(--success)';
    cdEl.textContent = `⏱️ Sisa Waktu: ${String(mins).padStart(2,'0')}m ${String(secs).padStart(2,'0')}s`;
  } else if (now.getTime() <= toleransiDate.getTime()) {
    const tolDiffMs = toleransiDate.getTime() - now.getTime();
    const mins = Math.floor(tolDiffMs / 60000);
    const secs = Math.floor((tolDiffMs % 60000) / 1000);
    cdEl.style.color = 'var(--warning)';
    cdEl.textContent = `⏳ Toleransi: ${String(mins).padStart(2,'0')}m ${String(secs).padStart(2,'0')}s`;
  } else {
    cdEl.style.color = '#ea5455';
    cdEl.textContent = '⏰ Status: Sesi Terlambat';
  }
}
updateSessionCountdown();
setInterval(updateSessionCountdown, 1000);

// ─── SOUND ────────────────────────────────────────────────────────────────
let soundEnabled = true;

// ─── INDONESIA LUXURY SPORT WATCH — Live Board ─────────────────────────────
const _lbTz = (typeof APP_TIMEZONE !== 'undefined' && APP_TIMEZONE) ? APP_TIMEZONE : 'Asia/Jakarta';
const _lbTzFormatter = new Intl.DateTimeFormat('en-US', {
  timeZone: _lbTz,
  hour: 'numeric', minute: 'numeric', second: 'numeric',
  hour12: false
});
const _lbDayFormatter = new Intl.DateTimeFormat('id-ID', {
  timeZone: _lbTz,
  weekday: 'short', day: '2-digit'
});

(function initLbWatch() {
  // 1. LumiBrite tick marks (60 with 12 major)
  const lumiEl = document.getElementById('lbLumiBars');
  if (lumiEl && lumiEl.children.length === 0) {
    for (let i = 0; i < 60; i++) {
      const tick = document.createElement('div');
      tick.className = 'lumi-tick' + (i % 5 === 0 ? ' major' : '');
      tick.style.transform = `rotate(${i * 6}deg)`;
      lumiEl.appendChild(tick);
    }
  }

  // 2. Bezel ticks (60)
  const bezelEl = document.getElementById('lbBezelTicks');
  if (bezelEl && bezelEl.children.length === 0) {
    for (let i = 0; i < 60; i++) {
      const tick = document.createElement('div');
      tick.className = 'bezel-tick' + (i % 5 === 0 ? ' major' : '');
      tick.style.transform = `rotate(${i * 6}deg)`;
      bezelEl.appendChild(tick);
    }
  }

  // 3. Sub-dial ticks
  function buildSubTicks(id, count, color) {
    const el = document.getElementById(id);
    if (!el || el.children.length > 0) return;
    for (let i = 0; i < count; i++) {
      const tick = document.createElement('div');
      tick.className = 'sub-tick' + (i % (count / 4) === 0 ? ' major' : '');
      tick.style.transform = `rotate(${(i / count) * 360}deg)`;
      el.appendChild(tick);
    }
  }
  buildSubTicks('lbSubLeftTicks', 20);
  buildSubTicks('lbSubRightTicks', 20);

  // 4. Hand & LCD elements
  const hourHand    = document.getElementById('lbHourHand');
  const minuteHand  = document.getElementById('lbMinuteHand');
  const secondHand  = document.getElementById('lbSecondHand');
  const subLeftHand  = document.getElementById('lbSubLeftHand');
  const subRightHand = document.getElementById('lbSubRightHand');
  const lcdDay  = document.getElementById('lbLcdDay');
  const lcdTime = document.getElementById('lbLcdTime');

  const DAYS_ID = ['MIN','SEN','SEL','RAB','KAM','JUM','SAB'];
  const prevDeg = { hour: null, minute: null, second: null };

  function setRot(el, deg, key) {
    if (!el) return;
    const prev = prevDeg[key];
    if (prev !== null && deg < prev - 180) {
      el.style.transition = 'none';
      el.style.transform = `rotate(${deg}deg)`;
      void el.offsetWidth;
      el.style.transition = '';
    } else {
      el.style.transform = `rotate(${deg}deg)`;
    }
    prevDeg[key] = deg;
  }

  function updateLbWatch() {
    const now = new Date();
    const timeParts = _lbTzFormatter.formatToParts(now);
    const h = +timeParts.find(p => p.type === 'hour').value;
    const m = +timeParts.find(p => p.type === 'minute').value;
    const s = +timeParts.find(p => p.type === 'second').value;

    setRot(hourHand,   (h % 12) * 30 + m * 0.5, 'hour');
    setRot(minuteHand, m * 6 + s * 0.1,          'minute');
    setRot(secondHand, s * 6,                     'second');
    secondHand && (secondHand.style.transformOrigin = '50% 56px');

    if (subLeftHand)  subLeftHand.style.transform  = `rotate(${(m / 60) * 360}deg)`;
    if (subRightHand) subRightHand.style.transform = `rotate(${(h / 12) * 360}deg)`;

    if (lcdDay) {
      const dayParts = _lbDayFormatter.formatToParts(now);
      const weekday = dayParts.find(p => p.type === 'weekday').value.toUpperCase();
      const day     = dayParts.find(p => p.type === 'day').value;
      lcdDay.textContent = `${weekday} ${day}`;
    }
    if (lcdTime) {
      lcdTime.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    }

    // Play tick sound for analog clock
    playTickSound();
  }

  updateLbWatch();
  setInterval(updateLbWatch, 1000);
})();

function toggleSound() {
  soundEnabled = !soundEnabled;
  const btn = document.getElementById('toggle-sound-btn');
  if (btn) btn.textContent = soundEnabled ? '🔊' : '🔇';
}

function playTickSound() {
  if (!soundEnabled) return;
  try {
    if (!window._audioCtx) {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      window._audioCtx = new AudioCtx();
    }
    const ctx = window._audioCtx;
    if (ctx.state === 'suspended') {
      ctx.resume();
    }
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    const now = ctx.currentTime;
    osc.type = 'sine';
    osc.frequency.setValueAtTime(1200, now);
    osc.frequency.exponentialRampToValueAtTime(400, now + 0.015);
    gain.gain.setValueAtTime(0.04, now);
    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.015);
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start(now);
    osc.stop(now + 0.015);
    osc.onended = () => { osc.disconnect(); gain.disconnect(); };
  } catch (_) {}
}

/**
 * Bel sukses  → nada bell ding dua-nada naik (DO–MI)
 * Bel gagal   → dua nada turun pendek (buzz-buzz)
 * Bel warning → satu nada tengah pendek
 */
function beep(type = 'success') {
  if (!soundEnabled) return;
  try {
    if (!window._audioCtx) {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      window._audioCtx = new AudioCtx();
    }
    const ctx = window._audioCtx;
    if (ctx.state === 'suspended') {
      ctx.resume();
    }

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
      osc.onended = () => { osc.disconnect(); gain.disconnect(); };
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
let lastQR = '', lastQRTime = 0, scanCount = 0;

// ─── HANDLE SCAN → SERVER ─────────────────────────────────────────────────
async function handleScan(qrCode) {
  // Implementasi cooldown untuk QR yang sama
  const now = Date.now();
  if (qrCode === lastQR && (now - lastQRTime) < 3000) {
    // Tampilkan toast warning
    showToast('warning', '⚠️', null, 'QR yang sama baru saja di-scan. Silakan tunggu 3 detik.');
    return;
  }

  lastQR = qrCode;
  lastQRTime = now;

  try {
    const resp = await fetch(SCAN_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify({ qr_code: qrCode, mode: CURRENT_MODE, client_timestamp: new Date().toISOString() }),
    });
    const data = await resp.json();
    if (data.success) {
      scanCount++;
      const scanCountEl = document.getElementById('scan-count');
      if (scanCountEl) scanCountEl.textContent = scanCount;
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
  }, DISMISS_MS);
}


// ─── LEADERBOARD AUTO-REFRESH ─────────────────────────────────────────────
function renderRows(rows, colClass) {
  const jamMasuk = JAM_MASUK_CFG.split(':');
  return rows.map((r, i) => {
    let jamVal = r.jam || '00:00:00';
    const parts = jamVal.split(':');
    if (parts.length === 2) {
      jamVal += ':00';
    }
    const [h, m] = jamVal.split(':').map(Number);
    const [bh, bm] = [parseInt(jamMasuk[0]), parseInt(jamMasuk[1])];
    const diff = (h * 60 + m) - (bh * 60 + bm);
    const isLate = diff > TOLERANSI_MENIT;
    const rank = r.rank || (i + 1);
    const medal = colClass === 'awal' && rank === 1 ? '🥇' : colClass === 'awal' && rank === 2 ? '🥈' : colClass === 'awal' && rank === 3 ? '🥉' : rank;
    const badge = CURRENT_MODE === 'pulang'
      ? `<span class="status-badge badge-hadir">✅ Pulang</span>`
      : (isLate
        ? `<span class="status-badge badge-terlambat">⏰ Terlambat</span><span class="late-minutes">+${diff} mnt</span>`
        : `<span class="status-badge badge-hadir">✅ Hadir</span>`);
    const isLateRow = CURRENT_MODE !== 'pulang' && isLate;
    return `<tr class="${colClass==='awal'&&rank<=3?'top-3':''} ${isLateRow?'late-row':''}">
      <td class="rank-cell">${medal}</td>
      <td class="name-cell"><div class="name">${r.nama}</div><div class="kelas-badge">${r.kelas}</div></td>
      <td><span class="jam-cell ${isLateRow?'jam-late':'jam-early'}">${jamVal}</span></td>
      <td class="status-col">${badge}</td>
    </tr>`;
  }).join('') || (colClass === 'awal' 
    ? `<tr><td colspan="4"><div class="empty-state"><span class="icon">🌅</span><p>Belum ada siswa yang hadir hari ini</p></div></td></tr>`
    : `<tr><td colspan="4"><div class="empty-state"><span class="icon">🌙</span><p>Belum ada data scan terbaru hari ini</p></div></td></tr>`);
}

let oldAwalStr = '';
let oldTerbaruStr = '';

async function refreshLeaderboard() {
  try {
    const url = LEADERBOARD_URL + '?mode=' + CURRENT_MODE;
    const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await resp.json();
    
    const newAwalStr = JSON.stringify(data.awal);
    if (newAwalStr !== oldAwalStr) {
      document.getElementById('tbody-awal').innerHTML = renderRows(data.awal, 'awal');
      oldAwalStr = newAwalStr;
    }
    
    const newTerbaruStr = JSON.stringify(data.terbaru);
    if (newTerbaruStr !== oldTerbaruStr) {
      document.getElementById('tbody-akhir').innerHTML = renderRows(data.terbaru, 'terbaru');
      oldTerbaruStr = newTerbaruStr;
    }

    if (data.stats) {
      const statsMap = {
        's-hadir': data.stats.hadir ?? 0,
        's-hadir-large': data.stats.hadir ?? 0,
        's-sakit': data.stats.sakit ?? 0,
        's-izin': data.stats.izin ?? 0,
        's-alpha': data.stats.alpha ?? 0,
        's-terlambat': data.stats.terlambat ?? 0,
        's-remaining': data.stats.remaining ?? 0,
        's-siswa-hadir': data.stats.siswa_hadir ?? 0,
        's-siswa-total': data.stats.siswa_total ?? 0,
        's-guru-hadir': data.stats.guru_hadir ?? 0,
        's-guru-total': data.stats.guru_total ?? 0,
        's-staff-hadir': data.stats.staff_hadir ?? 0,
        's-staff-total': data.stats.staff_total ?? 0
      };
      for (const [id, val] of Object.entries(statsMap)) {
        const el = document.getElementById(id);
        if (el && el.textContent !== String(val)) {
          el.textContent = val;
        }
      }
    }
  } catch(_) {}
}

// Auto-refresh leaderboard — jitter agar tidak thundering herd
(function scheduleRefresh() {
  setTimeout(function() {
    refreshLeaderboard();
    scheduleRefresh();
  }, REFRESH_MS + Math.floor(Math.random() * 1500));
})();
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
  // Skip scanner initialization during holiday
  if (IS_HARI_LIBUR) return;

  const CHAR_INTERVAL_MAX = 100; // ms maks antar karakter scanner (scanner < 100ms, manusia > 200ms)
  const COMMIT_TIMEOUT_MS = 200; // commit otomatis jika tidak ada Enter setelah 200ms
  const REFOCUS_INTERVAL  = 500; // cek & refocus setiap 500ms
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

  // Queue state inside the IIFE
  let scanQueue = [];
  let isProcessingQueue = false;

  async function processQueue() {
    if (isProcessingQueue) return;
    isProcessingQueue = true;

    while (scanQueue.length > 0) {
      const code = scanQueue.shift();
      setStatus('scanning');
      await handleScan(code);
    }

    if (document.activeElement === hwInput) {
      setStatus('ready');
    } else {
      setStatus('lost');
    }
    isProcessingQueue = false;
    ensureFocus();
  }

  // ── Commit: proses kode yang terkumpul di buffer ──────────────────────────
  async function commitScan() {
    const code = buffer.trim();
    buffer = '';
    hwInput.value = '';
    if (commitTmr) { clearTimeout(commitTmr); commitTmr = null; }
    if (code.length < MIN_CODE_LENGTH) return;

    scanQueue.push(code);
    processQueue();
  }

  // Input handler: terima karakter dari scanner
  hwInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
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
        if (!isProcessingQueue) setStatus('ready');
      } else {
        setStatus('lost');
      }
    }
  }

  // Jalankan guard setiap 500ms
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
  hwInput.addEventListener('focus', () => {
    if (!isProcessingQueue) setStatus('ready');
  });

  // Status awal
  setStatus('ready');
  console.log('[Piket Scanner] Sistem siap. Colokkan alat USB/Bluetooth QR scanner — langsung bisa scan.');
})();
</script>
</body>
</html>
