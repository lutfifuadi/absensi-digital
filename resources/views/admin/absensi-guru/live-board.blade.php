<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Live Board Absensi Guru — {{ $namaSekolah }}</title>
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  @php
    $liveFont = $liveFont ?? \App\Models\Pengaturan::where('key', 'live_board_font_family')->value('value') ?? 'Product Sans';
    $liveCounterFont = $liveCounterFont ?? \App\Models\Pengaturan::where('key', 'live_board_counter_font_family')->value('value') ?? 'Courier New';
    $liveCounterColor = $liveCounterColor ?? \App\Models\Pengaturan::where('key', 'live_board_counter_color')->value('value') ?? '#7367f0';
    $browserFonts = ['Courier New', 'Courier', 'Arial', 'Helvetica', 'Times New Roman', 'Times', 'Georgia', 'Verdana', 'Trebuchet MS', 'Impact', 'Comic Sans MS', 'Palatino', 'Bookman Old Style', 'monospace', 'serif', 'sans-serif'];
  @endphp
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  @if($liveFont !== 'Product Sans' || (!in_array($liveCounterFont, $browserFonts) && $liveCounterFont !== 'Product Sans'))
    @if($liveFont !== 'Product Sans' && !in_array($liveFont, $browserFonts))
      <link href="https://fonts.googleapis.com/css2?family={{ urlencode($liveFont) }}:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600;1,700&display=swap" rel="stylesheet">
    @endif
    @if(!in_array($liveCounterFont, $browserFonts) && $liveCounterFont !== 'Product Sans' && $liveCounterFont !== $liveFont)
      <link href="https://fonts.googleapis.com/css2?family={{ urlencode($liveCounterFont) }}:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600;1,700&display=swap" rel="stylesheet">
    @endif
  @else
    <link href="https://fonts.googleapis.com/css2?family=Outfit:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:ital,wght@0,600;0,700;0,800;1,600;1,700&display=swap" rel="stylesheet">
  @endif
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:        #080c14;
      --surface:   #0f1623;
      --surface-card: #141c2e;
      --border:    rgba(255,255,255,0.07);
      --border-subtle: rgba(255,255,255,0.04);
      --primary:   #7367f0;
      --success:   #28c76f;
      --warning:   #ff9f43;
      --danger:    #ea5455;
      --info:      #00cfe8;
      --purple:    #a78bfa;
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
    ::-webkit-scrollbar { width: 4px; height: 4px; }
    ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 4px; }

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
      font-size: 1.4rem; flex-shrink: 0; overflow: hidden;
      box-shadow: 0 0 18px rgba(115,103,240,.4);
    }
    .header-brand .logo-icon img { width: 100%; height: 100%; object-fit: contain; }
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

    .btn-control-head {
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border);
      border-radius: 5px;
      color: var(--muted);
      padding: 5px 10px;
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-control-head:hover { color: #fff; background: rgba(255,255,255,0.1); }
    .btn-control-head.active { color: var(--success); border-color: rgba(40,199,111,0.4); }

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
    .scanner-col { grid-column: 1; grid-row: 1 / span 2; }

    /* ─── PANELS ─────────────────────────────────────────── */
    .panel {
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 4px; display: flex; flex-direction: column; overflow: hidden;
    }
    .panel-header {
      padding: 0.8rem 1.1rem 0.65rem;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
    }
    .panel-title { font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; }
    .panel-body  { flex: 1; overflow-y: auto; padding: 0; }

    /* ─── STAT CHIPS ─────────────────────────────────────── */
    .stat-chips { display: flex; gap: 0.4rem; flex-wrap: wrap; padding: 0.6rem 1.1rem; border-bottom: 1px solid var(--border); flex-shrink: 0; }
    .scanner-area .stat-chips { border-bottom: none; justify-content: center; margin: 0.8rem 0; padding: 0; flex-wrap: nowrap; gap: 0.3rem; }
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
      padding: 3rem; color: var(--muted); gap: 0.5rem; text-align: center;
    }
    .empty-state .icon { font-size: 2.5rem; opacity: .4; }
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
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 2px;
      margin-bottom: 0.4rem;
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
      width: 130px;
      height: 130px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1.25rem;
    }
    .rfid-card {
      width: 85px;
      height: 55px;
      background: linear-gradient(135deg, #7367f0 0%, #a78bfa 100%);
      border-radius: 5px;
      box-shadow: 0 10px 25px rgba(115, 103, 240, 0.4), 0 0 15px rgba(115, 103, 240, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 1.8rem;
      animation: floatCard 4s ease-in-out infinite;
      position: relative;
      z-index: 2;
    }
    .rfid-card .wifi-icon {
      transform: rotate(90deg);
    }
    .rfid-scanner-base {
      position: absolute;
      bottom: 15px;
      width: 105px;
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
      width: 75px;
      height: 2px;
      background: var(--success);
      box-shadow: 0 0 8px var(--success);
      animation: basePulse 2s infinite;
    }
    .rfid-glow-ring {
      position: absolute;
      width: 115px;
      height: 115px;
      border: 2px solid rgba(115, 103, 240, 0.15);
      border-radius: 50%;
      animation: ripple 2.5s linear infinite;
    }
    .rfid-glow-ring:nth-child(2) {
      animation-delay: 1.25s;
    }

    @keyframes floatCard {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-10px) rotate(2deg); }
    }
    @keyframes basePulse {
      0%, 100% { opacity: 0.4; }
      50% { opacity: 1; }
    }
    @keyframes ripple {
      0% { transform: scale(0.6); opacity: 1; }
      100% { transform: scale(1.4); opacity: 0; }
    }

    /* ─── CHRONOS LUXURY SPORT WATCH (sama dengan Live Board Siswa) ─── */
    .chronos-watch-container {
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      z-index: 2;
      transform: scale(0.78);
      margin: -18px 0;
      filter: drop-shadow(0 22px 48px rgba(0,0,0,0.95));
    }
    .chronos-strap-lug {
      width: 80px; height: 16px;
      background: linear-gradient(180deg, #252832 0%, #181a24 50%, #252832 100%);
      border-radius: 4px;
      border: 1px solid rgba(255,255,255,0.1);
      box-shadow: inset 0 1px 2px rgba(255,255,255,0.12), 0 2px 6px rgba(0,0,0,0.8);
      position: relative; z-index: 0;
    }
    .chronos-strap-lug.top-lug { border-radius: 4px 4px 0 0; }
    .chronos-strap-lug.bot-lug { border-radius: 0 0 4px 4px; }
    .chronos-body { position: relative; display: flex; align-items: center; z-index: 1; }
    .chronos-side-buttons {
      position: absolute; right: -18px; top: 50%;
      transform: translateY(-50%);
      display: flex; flex-direction: column; align-items: flex-start; gap: 6px; z-index: 10;
    }
    .side-btn { display: flex; align-items: center; }
    .side-btn .btn-grip {
      height: 18px; border-radius: 0 4px 4px 0;
      background: repeating-linear-gradient(180deg, #4a4f62 0px, #4a4f62 2px, #14161f 2px, #14161f 4px);
      border: 1px solid rgba(255,255,255,0.2);
      box-shadow: inset 0 1px 2px rgba(255,255,255,0.4), 3px 0 8px rgba(0,0,0,0.85);
    }
    .side-btn.top-btn { transform: rotate(-15deg) translateX(2px); }
    .side-btn.top-btn .btn-grip { width: 12px; }
    .side-btn.mid-crown .btn-grip { width: 16px; height: 24px; background: repeating-linear-gradient(180deg, #64697d 0px, #64697d 2px, #10121a 2px, #10121a 4px); box-shadow: inset 0 2px 3px rgba(255,255,255,0.45), 4px 0 10px rgba(0,0,0,0.9); }
    .side-btn.bot-btn { transform: rotate(15deg) translateX(2px); }
    .side-btn.bot-btn .btn-grip { width: 12px; }
    .chronos-bezel-outer {
      position: relative; width: 224px; height: 224px;
      clip-path: polygon(10% 0%, 90% 0%, 100% 10%, 100% 90%, 90% 100%, 10% 100%, 0% 90%, 0% 10%);
      background:
        linear-gradient(135deg, rgba(255,255,255,0.45) 0%, rgba(255,255,255,0.08) 25%, transparent 45%),
        linear-gradient(145deg, #6c738c 0%, #2a2e3d 25%, #11131c 55%, #3d4257 85%, #7a829c 100%);
      display: flex; align-items: center; justify-content: center;
      filter: drop-shadow(0 14px 32px rgba(0,0,0,0.95));
    }
    .chronos-bezel-outer::before {
      content: ""; position: absolute; inset: 4px;
      clip-path: polygon(10% 0%, 90% 0%, 100% 10%, 100% 90%, 90% 100%, 10% 100%, 0% 90%, 0% 10%);
      background:
        linear-gradient(135deg, rgba(255,255,255,0.18) 0%, transparent 40%),
        linear-gradient(145deg, #181b26 0%, #3a3f52 35%, #12141d 65%, #2a2e3e 100%);
      pointer-events: none; z-index: 1;
    }
    .chronos-bezel-outer::after {
      content: ""; position: absolute; inset: 10px;
      clip-path: polygon(10% 0%, 90% 0%, 100% 10%, 100% 90%, 90% 100%, 10% 100%, 0% 90%, 0% 10%);
      background: linear-gradient(135deg, #07090e 0%, #1a1d29 50%, #050609 100%);
      box-shadow: inset 0 3px 8px rgba(0,0,0,0.98), inset 0 -1px 2px rgba(255,255,255,0.25);
      pointer-events: none; z-index: 2;
    }
    .outer-screw {
      position: absolute; width: 9px; height: 9px; border-radius: 50%;
      background: radial-gradient(circle at 35% 35%, #ffffff 0%, #d1d5db 40%, #374151 100%);
      box-shadow: inset 0 0 2px rgba(0,0,0,0.9), 0 1px 3px rgba(0,0,0,0.8); z-index: 5;
    }
    .outer-screw::after { content: ""; position: absolute; top: 50%; left: 50%; width: 4px; height: 1.2px; background: #111827; transform: translate(-50%, -50%); }
    .outer-screw.sc-1 { top: 14px; left: 50%; transform: translateX(-50%); }
    .outer-screw.sc-2 { top: 22px; right: 22px; }
    .outer-screw.sc-3 { right: 14px; top: 50%; transform: translateY(-50%); }
    .outer-screw.sc-4 { bottom: 22px; right: 22px; }
    .outer-screw.sc-5 { bottom: 14px; left: 50%; transform: translateX(-50%); }
    .outer-screw.sc-6 { bottom: 22px; left: 22px; }
    .outer-screw.sc-7 { left: 14px; top: 50%; transform: translateY(-50%); }
    .outer-screw.sc-8 { top: 22px; left: 22px; }
    .chronos-bezel-ring {
      position: relative; width: 192px; height: 192px; border-radius: 50%; z-index: 3;
      background:
        radial-gradient(circle at 28% 28%, rgba(255,255,255,0.18) 0%, transparent 55%),
        conic-gradient(from 45deg, #151722 0deg, #404455 60deg, #0f1118 120deg, #404455 180deg, #151722 240deg, #404455 300deg, #151722 360deg);
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 0 0 3px #080b14, 0 12px 30px rgba(0,0,0,0.95), inset 0 2px 4px rgba(255,255,255,0.35), inset 0 -2px 6px rgba(0,0,0,0.95);
    }
    .chronos-dial-face {
      position: relative; width: 168px; height: 168px; border-radius: 50%; overflow: hidden;
      background-color: #070a0f;
      background-image:
        repeating-linear-gradient(45deg, rgba(255,255,255,0.055) 0px, rgba(255,255,255,0.055) 1px, transparent 1px, transparent 8px),
        repeating-linear-gradient(-45deg, rgba(255,255,255,0.055) 0px, rgba(255,255,255,0.055) 1px, transparent 1px, transparent 8px),
        radial-gradient(circle at 50% 50%, rgba(20, 30, 50, 0.4) 0%, rgba(2, 4, 8, 1) 100%);
      background-size: 8px 8px, 8px 8px, 100% 100%;
      box-shadow: inset 0 0 25px rgba(0,0,0,0.99);
    }
    .dial-inner-ring { position: absolute; inset: 6px; border-radius: 50%; border: 1.5px solid rgba(0,255,160,0.3); pointer-events: none; z-index: 2; }
    .lumi-tick { position: absolute; left: 50%; top: 10px; width: 2.5px; height: 8px; margin-left: -1.25px; background: #00ffaa; border-radius: 1px; box-shadow: 0 0 6px #00ffaa, 0 0 12px rgba(0,255,170,0.6); transform-origin: 50% 74px; z-index: 3; pointer-events: none; }
    .lumi-tick.major { width: 3.5px; height: 12px; margin-left: -1.75px; top: 8px; box-shadow: 0 0 10px #00ffaa, 0 0 20px rgba(0,255,170,0.7); }
    .chronos-logo-area { position: absolute; top: 32%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none; z-index: 4; }
    .brand-emblem { font-size: 12px; font-weight: 900; color: #00f2fe; text-shadow: 0 0 8px #00f2fe; line-height: 1; margin-bottom: 1px; }
    .brand-name { font-size: 8.5px; font-weight: 900; color: #ffffff; letter-spacing: 2px; text-shadow: 0 1px 3px rgba(0,0,0,0.9); line-height: 1; }
    .brand-auto { font-size: 5px; color: rgba(255,255,255,0.6); letter-spacing: 0.8px; margin-top: 2px; font-weight: 700; }
    .chronos-sub {
      position: absolute; border-radius: 50%;
      background: radial-gradient(circle, #0a0c14 0%, #030508 100%);
      z-index: 5; display: flex; align-items: center; justify-content: center; overflow: visible;
    }
    .chronos-sub.sub-left { width: 46px; height: 46px; top: 50%; left: 12px; transform: translateY(-50%); border: 1.8px solid #ff7e29; box-shadow: inset 0 0 8px rgba(0,0,0,0.98), 0 0 12px rgba(255,126,41,0.5); }
    .chronos-sub.sub-right { width: 46px; height: 46px; top: 50%; right: 12px; transform: translateY(-50%); border: 1.8px solid #00b4fc; box-shadow: inset 0 0 8px rgba(0,0,0,0.98), 0 0 12px rgba(0,180,252,0.5); }
    .sub-tick { position: absolute; left: 50%; top: 3px; width: 1px; height: 4px; margin-left: -0.5px; background: rgba(255,255,255,0.5); transform-origin: 50% 20px; }
    .sub-tick.major { width: 1.5px; height: 6px; top: 2px; transform-origin: 50% 21px; }
    .chronos-sub.sub-left .sub-tick { background: rgba(255,126,41,0.7); }
    .chronos-sub.sub-left .sub-tick.major { background: #ff7e29; box-shadow: 0 0 4px rgba(255,126,41,0.8); }
    .chronos-sub.sub-right .sub-tick { background: rgba(0,180,252,0.7); }
    .chronos-sub.sub-right .sub-tick.major { background: #00b4fc; box-shadow: 0 0 4px rgba(0,180,252,0.8); }
    .sub-hand { position: absolute; left: 50%; top: 50%; width: 1.5px; height: 16px; margin-left: -0.75px; margin-top: -16px; transform-origin: 50% 100%; border-radius: 1px; z-index: 6; }
    .chronos-sub.sub-left .sub-hand { background: #ff7e29; box-shadow: 0 0 6px #ff7e29; }
    .chronos-sub.sub-right .sub-hand { background: #00b4fc; box-shadow: 0 0 6px #00b4fc; }
    .sub-center-dot { position: absolute; width: 4px; height: 4px; border-radius: 50%; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 7; }
    .chronos-sub.sub-left .sub-center-dot { background: #ff7e29; box-shadow: 0 0 4px #ff7e29; }
    .chronos-sub.sub-right .sub-center-dot { background: #00b4fc; box-shadow: 0 0 4px #00b4fc; }
    .chronos-lcd {
      position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%);
      width: 112px; background: #00050a;
      border: 1.5px solid rgba(0,200,255,0.65); border-radius: 5px;
      box-shadow: inset 0 2px 8px rgba(0,0,0,0.98), inset 0 -1px 3px rgba(0,200,255,0.15), 0 0 14px rgba(0,180,255,0.4), 0 2px 8px rgba(0,0,0,0.9);
      z-index: 5; overflow: hidden;
    }
    .chronos-lcd::before { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,200,255,0.06) 0%, transparent 60%); pointer-events: none; }
    .lcd-inner { display: flex; align-items: center; justify-content: center; gap: 4px; padding: 4px 6px; font-family: 'Courier New', monospace; font-weight: 900; }
    .lcd-day, .lcd-time { font-size: 9px; color: #00e8ff; text-shadow: 0 0 8px rgba(0,232,255,0.95), 0 0 16px rgba(0,232,255,0.5); letter-spacing: 0.5px; }
    .lcd-sep { font-size: 9px; color: rgba(0,200,255,0.5); }
    .chronos-dial-face .hand { position: absolute; left: 50%; top: 50%; transform-origin: 50% 100%; filter: drop-shadow(2px 5px 8px rgba(0,0,0,0.92)); transition: transform 0.3s cubic-bezier(0.4, 1.4, 0.4, 1); }
    .chronos-dial-face .hour-hand { width: 7px; height: 42px; margin-left: -3.5px; margin-top: -42px; border-radius: 3px 3px 2px 2px; z-index: 6; background: linear-gradient(180deg, #00e8ff 0%, #0090b8 60%, #00c8e0 100%); box-shadow: 0 0 12px rgba(0,232,255,0.95), 0 0 25px rgba(0,180,255,0.6), inset 0 1px 2px rgba(255,255,255,0.6); clip-path: polygon(20% 0%, 80% 0%, 100% 100%, 0% 100%); }
    .chronos-dial-face .minute-hand { width: 5px; height: 60px; margin-left: -2.5px; margin-top: -60px; border-radius: 2px 2px 1px 1px; z-index: 7; background: linear-gradient(180deg, #ff9520 0%, #e86000 60%, #ff8200 100%); box-shadow: 0 0 12px rgba(255,140,0,0.95), 0 0 25px rgba(255,100,0,0.6), inset 0 1px 2px rgba(255,255,255,0.5); clip-path: polygon(25% 0%, 75% 0%, 100% 100%, 0% 100%); }
    .chronos-dial-face .second-hand { width: 1.8px; height: 70px; margin-left: -0.9px; margin-top: -56px; transform-origin: 50% 56px; z-index: 8; background: linear-gradient(180deg, #ff3300 0%, #ff6600 50%, rgba(255,100,0,0.4) 100%); box-shadow: 0 0 8px rgba(255,60,0,0.95); border-radius: 1px; transition: none !important; }
    .chronos-dial-face .second-hand::after { content: ""; position: absolute; bottom: 0; left: -2px; width: 6px; height: 14px; background: #ff3300; box-shadow: 0 0 8px rgba(255,50,0,0.8); border-radius: 0 0 3px 3px; }
    .chronos-cap { position: absolute; left: 50%; top: 50%; width: 12px; height: 12px; transform: translate(-50%, -50%); border-radius: 50%; background: radial-gradient(circle at 35% 35%, #ffffff 0%, #c0c8d8 30%, #475569 70%, #0f172a 100%); box-shadow: 0 0 0 2.5px rgba(0,0,0,0.95), 0 0 10px rgba(0,232,255,0.85); z-index: 9; }
    .glass-glare { position: absolute; inset: 0; border-radius: 50%; background: linear-gradient(135deg, rgba(255,255,255,0.22) 0%, rgba(255,255,255,0.06) 30%, transparent 55%, rgba(0,180,255,0.06) 100%); pointer-events: none; z-index: 11; }
    .bezel-tick { position: absolute; left: 50%; top: 5px; width: 2px; height: 7px; margin-left: -1px; background: rgba(255,255,255,0.35); border-radius: 1px; transform-origin: 50% 91px; pointer-events: none; }
    .bezel-tick.major { width: 3px; height: 12px; background: rgba(255,255,255,0.65); }

    /* ─── RESULT TOAST ────────────────────────────────────── */
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
      background: rgba(40,199,111,.88);
      border-top: 2px solid var(--success);
      backdrop-filter: blur(10px);
    }
    .result-toast.warning {
      background: rgba(255,159,67,.88);
      border-top: 2px solid var(--warning);
      backdrop-filter: blur(10px);
    }
    .result-toast.error {
      background: rgba(234,84,85,.88);
      border-top: 2px solid var(--danger);
      backdrop-filter: blur(10px);
    }
    .result-toast-inner { display: flex; align-items: flex-start; gap: 0.75rem; }
    .result-icon { font-size: 1.6rem; flex-shrink: 0; }
    .result-name { font-weight: 800; font-size: 0.95rem; color: #fff; }
    .result-sub  { font-size: 0.75rem; color: rgba(255,255,255,0.85); margin-top: 2px; }
    .result-msg  { font-size: 0.75rem; margin-top: 4px; color: rgba(255,255,255,0.9); }
    .result-bar  { height: 3px; background: rgba(255,255,255,.2); border-radius: 99px; margin-top: 0.5rem; overflow: hidden; }
    .result-bar-fill { height: 100%; background: #fff; width: 0; }

    /* ─── SCAN LOG / GURU GRID ───────────────────────────── */
    .scan-info { padding: 0.65rem 1rem; border-top: 1px solid var(--border); flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; }
    .scan-count-wrap { font-size: 0.78rem; color: var(--muted); }
    .scan-count-wrap span { font-size: 1.2rem; font-weight: 800; color: var(--primary); }

    .filter-tabs { display: flex; gap: 0.3rem; }
    .filter-btn {
      background: transparent; border: 1px solid var(--border); border-radius: 4px;
      color: var(--muted); padding: 3px 8px; font-size: 0.68rem; font-weight: 700; cursor: pointer; transition: all .2s;
    }
    .filter-btn:hover { color: #fff; background: rgba(255,255,255,0.06); }
    .filter-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }

    .guru-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 0.5rem;
      padding: 0.75rem;
    }
    .guru-card {
      background: var(--surface-card);
      border: 1px solid var(--border-subtle);
      border-radius: 4px;
      padding: 0.55rem 0.7rem;
      display: flex;
      align-items: center;
      gap: 0.65rem;
      transition: all 0.2s;
    }
    .guru-card:hover { border-color: rgba(255,255,255,0.15); background: #182238; }
    .guru-card.is-belum { opacity: 0.75; }
    .guru-card-avatar {
      width: 38px; height: 38px; border-radius: 50%; object-fit: cover;
      border: 1.5px solid var(--border); flex-shrink: 0;
    }
    .guru-card-info { flex: 1; overflow: hidden; }
    .guru-card-name { font-weight: 700; font-size: 0.8rem; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .guru-card-sub  { font-size: 0.62rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px; }
    .guru-card-meta { display: flex; align-items: center; gap: 0.35rem; margin-top: 3px; }

    .pill { display: inline-flex; align-items: center; gap: 2px; border-radius: 99px; padding: 1px 7px; font-size: 0.6rem; font-weight: 700; white-space: nowrap; }
    .pill-hadir     { background: rgba(40,199,111,0.14); color: var(--success); border: 1px solid rgba(40,199,111,0.25); }
    .pill-terlambat { background: rgba(255,159,67,0.14); color: var(--warning); border: 1px solid rgba(255,159,67,0.25); }
    .pill-izin      { background: rgba(0,207,232,0.14);  color: var(--info);    border: 1px solid rgba(0,207,232,0.25); }
    .pill-pulang    { background: rgba(167,139,250,0.14);color: var(--purple);  border: 1px solid rgba(167,139,250,0.25); }
    .pill-belum     { background: rgba(100,116,139,0.12);color: var(--muted);   border: 1px solid rgba(100,116,139,0.2); }
    .pill-sesuai    { background: rgba(0,207,232,0.14);  color: var(--info);    border: 1px solid rgba(0,207,232,0.25); }
    .pill-tidak-hadir  { background: rgba(255,159,67,0.14); color: var(--warning); border: 1px solid rgba(255,159,67,0.25); }
    .pill-belum-dimonitor { background: rgba(100,116,139,0.12); color: var(--muted); border: 1px solid rgba(100,116,139,0.2); }
    .pill-part-time { background: rgba(115,103,240,0.14); color: #a78bfa;       border: 1px solid rgba(115,103,240,0.3); }

    /* Strip ringkasan slot part time (Panel Daftar Presensi Guru) */
    .stat-chips-pt { display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0.45rem 0.8rem; border-top: 1px solid var(--border); align-items: center; }
    .stat-chips-pt .label-pt { font-size: 0.66rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; color: var(--muted); }
    .stat-chip-pt {
      display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.66rem; font-weight: 700;
      padding: 2px 9px; border-radius: 99px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-subtle); color: var(--text);
    }
    .stat-chip-pt b { color: #fff; }
    .stat-chip-pt .dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

    /* ─── RESPONSIVE BREAKPOINTS ─────────────────────────── */
    @media (max-width: 1279px) {
      .main { grid-template-columns: 1fr 1fr 300px; gap: 0.6rem; padding: 0.6rem; }
    }
    @media (max-width: 1023px) {
      html, body { overflow-y: auto; height: auto; max-height: none; display: block; }
      .main { grid-template-columns: 1fr 1fr; grid-template-rows: auto auto; gap: 0.6rem; padding: 0.6rem; }
      .scanner-col { grid-column: 1 / -1; }
      .panel-body { max-height: 340px; }
    }
    @media (max-width: 767px) {
      .header { flex-wrap: wrap; justify-content: center; gap: 0.4rem; padding: 0.6rem 0.8rem; text-align: center; }
      .header-brand { order: 1; width: 100%; justify-content: center; }
      .header-center { order: 2; }
      .header-right  { order: 3; }
      .main { grid-template-columns: 1fr; gap: 0.5rem; padding: 0.5rem; }
      .scanner-col { grid-column: auto; }
      .panel-body   { max-height: 300px; }
    }
    @media (max-width: 599px) {
      .header { padding: 0.5rem 0.6rem; gap: 0.3rem; }
      .header-brand p { display: none; }
      #live-clock { font-size: 1.15rem; }
      .main { gap: 0.4rem; padding: 0.4rem; }
      .panel-body { max-height: 260px; }
    }
  </style>
</head>
<body>

<!-- ══ HEADER ══════════════════════════════════════════════════════ -->
<header class="header">
  <div class="header-brand">
    <div class="logo-icon">
      @if(!empty($logoSekolah))
        <img src="{{ $logoSekolah }}" alt="Logo">
      @else
        👨‍🏫
      @endif
    </div>
    <div>
      <h1>{{ $namaSekolah }}</h1>
      <p>Papan Absensi Live · Khusus Guru</p>
    </div>
  </div>

  <div class="header-center" style="text-align: center;">
    <div id="session-status-badge" style="display: flex; align-items: center; justify-content: center; gap: 0.4rem; margin-bottom: 4px;">
      <span id="session-pill" style="font-size: 0.68rem; font-weight: 800; padding: 2px 9px; border-radius: 99px; background: rgba(115, 103, 240, 0.15); color: #a78bfa; border: 1px solid rgba(115, 103, 240, 0.4); letter-spacing: 0.5px;">☀️ SESI MASUK GURU</span>
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
    <div class="segmented-control">
      <button class="{{ $mode === 'otomatis' ? 'active' : '' }}" onclick="switchMode('otomatis')">⏰ Otomatis</button>
      <button class="{{ $mode === 'masuk' ? 'active' : '' }}" onclick="switchMode('masuk')">☀️ Masuk</button>
      <button class="{{ $mode === 'pulang' ? 'active' : '' }}" onclick="switchMode('pulang')">🌙 Pulang</button>
    </div>
    <div class="live-badge">
      <span class="live-dot"></span> LIVE
    </div>
    <button class="btn-control-head active" id="toggle-sound-btn" title="Toggle Suara & TTS" onclick="toggleSound()">🔊</button>
    <button class="btn-control-head" title="Layar Penuh" onclick="toggleFullscreen()">🖥️</button>
  </div>
</header>

<!-- ══ MAIN GRID ════════════════════════════════════════════════════ -->
<div class="main">

  <!-- ── PANEL 1: 10 GURU PALING AWAL ────────────────────────────── -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">
        @if($mode === 'pulang')
          🏆 <span>10 Guru Pulang Paling Awal</span>
        @else
          🏆 <span>10 Guru Hadir Paling Awal</span>
        @endif
      </div>
      <div style="font-size:.7rem; color:var(--muted);">{{ $hariIndo }}, {{ $tanggalIndo }}</div>
    </div>

    <div class="panel-body">
      <table class="lb-table" id="table-awal">
        <thead><tr>
          <th class="rank-cell">#</th>
          <th class="name-cell">Nama / Jabatan</th>
          <th class="jam-col">Jam</th>
          <th class="status-col">Status</th>
        </tr></thead>
        <tbody id="tbody-awal">
          @forelse($leaderboardAwal as $i => $abs)
            @php
              $jamMasukSetting = \Carbon\Carbon::createFromTimeString($jamMasukCfg ?? '07:00');
              $jamGuru    = \Carbon\Carbon::createFromTimeString($abs['jam']);
              $selisih    = (int) $jamMasukSetting->diffInMinutes($jamGuru, false);
              $isLate     = $selisih > $toleransi;
            @endphp
            <tr class="{{ $i < 3 ? 'top-3' : '' }} {{ ($mode !== 'pulang' && $isLate) ? 'late-row' : '' }}">
              <td class="rank-cell">{{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i+1)) }}</td>
              <td class="name-cell">
                <div class="name">{{ $abs['nama'] }}</div>
                <div class="kelas-badge">{{ $abs['jabatan'] }}</div>
              </td>
              <td class="jam-cell {{ ($mode !== 'pulang' && $isLate) ? 'jam-late' : 'jam-early' }}">{{ \Carbon\Carbon::parse($abs['jam'])->format('H:i:s') }}</td>
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
            <tr><td colspan="4"><div class="empty-state"><span class="icon">🌅</span><p>Belum ada data hadir guru hari ini</p></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── PANEL 2: RIWAYAT SCAN & DAFTAR STATUS GURU ─────────────── -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">
        🕐 <span>Status & Riwayat Scan Guru</span>
      </div>
      <div style="font-size:.7rem; color:var(--muted);">Urutan scan &amp; daftar status guru</div>
    </div>

    <div class="panel-body">
      <!-- Section Riwayat Scan Terbaru -->
      <div style="padding:0.5rem 0.8rem; background:rgba(255,255,255,0.02); border-bottom:1px solid var(--border); font-size:0.72rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px;">
        Riwayat Scan Terbaru Hari Ini
      </div>
      <table class="lb-table">
        <thead><tr>
          <th class="rank-cell">#</th>
          <th class="name-cell">Nama / Jabatan</th>
          <th class="jam-col">Jam</th>
          <th class="status-col">Status</th>
        </tr></thead>
        <tbody id="tbody-akhir">
          @forelse($leaderboardTerbaru as $i => $abs)
            @php
              $jamMasukSetting = \Carbon\Carbon::createFromTimeString($jamMasukCfg ?? '07:00');
              $jamGuru    = \Carbon\Carbon::createFromTimeString($abs['jam']);
              $selisih    = (int) $jamMasukSetting->diffInMinutes($jamGuru, false);
              $isLate     = $selisih > $toleransi;
            @endphp
            <tr class="{{ ($mode !== 'pulang' && $isLate) ? 'late-row' : '' }}">
              <td class="rank-cell" style="color:var(--muted);">{{ $i+1 }}</td>
              <td class="name-cell">
                <div class="name">{{ $abs['nama'] }}</div>
                <div class="kelas-badge">{{ $abs['jabatan'] }}</div>
              </td>
              <td class="jam-cell {{ ($mode !== 'pulang' && $isLate) ? 'jam-late' : '' }}">{{ \Carbon\Carbon::parse($abs['jam'])->format('H:i:s') }}</td>
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
            <tr><td colspan="4"><div class="empty-state"><span class="icon">🌙</span><p>Belum ada data scan guru terbaru hari ini</p></div></td></tr>
          @endforelse
        </tbody>
      </table>

      <!-- Section Grid Status Seluruh Guru -->
      <div style="padding:0.6rem 0.8rem 0.3rem; border-top:1px solid var(--border); font-size:0.72rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px; display:flex; justify-content:space-between; align-items:center;">
        <span>Daftar Presensi Guru</span>
        <span id="guruTotalSub" style="font-size:0.68rem; text-transform:none;">({{ count($guruList) }} Guru)</span>
      </div>

      <!-- Ringkasan Slot Part Time (PRD-007) -->
      <div class="stat-chips-pt">
        <span class="label-pt"><i class="ti tabler-briefcase-off" style="font-size:0.8rem;"></i> Part Time</span>
        <span class="stat-chip-pt"><span class="dot" style="background:var(--primary);"></span> Total: <b id="s-part-time">{{ $stats['part_time'] ?? 0 }}</b></span>
        <span class="stat-chip-pt"><span class="dot" style="background:var(--info);"></span> Sesuai Jadwal: <b id="s-pt-sesuai">{{ $stats['part_time_sesuai_jadwal'] ?? 0 }}</b></span>
        <span class="stat-chip-pt"><span class="dot" style="background:var(--muted);"></span> Belum Dimonitor: <b id="s-pt-belum-monitor">{{ $stats['part_time_belum_dimonitor'] ?? 0 }}</b></span>
        <span class="stat-chip-pt"><span class="dot" style="background:var(--warning);"></span> Tidak Hadir: <b id="s-pt-tidak-hadir">{{ $stats['part_time_tidak_hadir'] ?? 0 }}</b></span>
        <span class="stat-chip-pt"><span class="dot" style="background:var(--purple);"></span> Tanpa Slot: <b id="s-pt-tanpa-slot">{{ $stats['part_time_tanpa_slot'] ?? 0 }}</b></span>
      </div>

      <div class="guru-grid" id="guruGrid">
        <!-- Rendered via JS -->
      </div>
    </div>
  </div>

  <!-- ── PANEL 3: PUSAT KONTROL & COUNTER SCANNER GURU ────────────── -->
  <div class="panel scanner-col" style="position:relative; overflow:hidden;">
    <div class="panel-header">
      <div class="panel-title">🔌 <span>Pusat Kontrol &amp; Counter Scanner</span></div>
      <div style="display:flex;align-items:center;gap:.5rem;">
        <span id="hw-indicator" title="Status alat scanner fisik" style="font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:5px;background:rgba(40,199,111,.15);color:var(--success);border:1px solid rgba(40,199,111,.4);">🔌 Scanner Guru: AKTIF & Siap</span>
      </div>
    </div>

    <!-- Scanner Area -->
    <div class="scanner-area">
      <!-- Watermark Logo Sekolah (Ukuran Extra Besar) -->
      <div class="watermark-logo" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; opacity: 0.08; z-index: 0; width: 85%; height: 85%; max-width: 460px; max-height: 460px; display: flex; align-items: center; justify-content: center;">
        @if(!empty($logoSekolah))
          <img src="{{ $logoSekolah }}" alt="Watermark Logo Sekolah" style="width: 100%; height: 100%; object-fit: contain; filter: brightness(130%);">
        @else
          <span style="font-size: 240px; line-height: 1; opacity: 0.8;">👨‍🏫</span>
        @endif
      </div>

      <!-- Widget Counter Besar Futuristik (KHUSUS GURU) -->
      <div class="counter-widget">
        @if($mode === 'pulang')
          <div class="counter-title">Total Guru Pulang Hari Ini</div>
        @else
          <div class="counter-title">Total Kehadiran Guru Hari Ini</div>
        @endif
        <div class="counter-value">
          <span class="current" id="s-hadir-large">{{ $stats['hadir'] }}</span>
          <span class="slash">/</span>
          <span class="total-cap" id="large-total-kapasitas">{{ $totalKapasitasGuru }}</span>
          <span class="unit">Guru</span>
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

      <!-- INDONESIA Luxury Sport Watch (sama dengan Live Board Siswa) -->
      <div class="chronos-watch-container">

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
              <div id="gbBezelTicks"></div>

              <!-- CARBON FIBER DIAL FACE -->
              <div class="chronos-dial-face" id="gbDial">
                <div class="dial-inner-ring"></div>
                <div id="gbLumiBars"></div>

                <div class="chronos-logo-area">
                  <div class="brand-emblem">◈</div>
                  <div class="brand-name">INDONESIA</div>
                  <div class="brand-auto">{{ $zoneAbbr ?? 'WIB' }} · {{ $utcOffset ?? 'UTC+7' }}</div>
                </div>

                <!-- LEFT SUB-DIAL (9 o'clock — Orange) -->
                <div class="chronos-sub sub-left">
                  <div id="gbSubLeftTicks"></div>
                  <div class="sub-hand" id="gbSubLeftHand"></div>
                  <div class="sub-center-dot"></div>
                </div>

                <!-- RIGHT SUB-DIAL (3 o'clock — Blue) -->
                <div class="chronos-sub sub-right">
                  <div id="gbSubRightTicks"></div>
                  <div class="sub-hand" id="gbSubRightHand"></div>
                  <div class="sub-center-dot"></div>
                </div>

                <!-- BOTTOM LCD DISPLAY -->
                <div class="chronos-lcd">
                  <div class="lcd-inner">
                    <span class="lcd-day" id="gbLcdDay">SEN 01</span>
                    <span class="lcd-sep">|</span>
                    <span class="lcd-time" id="gbLcdTime">00:00:00</span>
                  </div>
                </div>

                <div class="hand hour-hand" id="gbHourHand"></div>
                <div class="hand minute-hand" id="gbMinuteHand"></div>
                <div class="hand second-hand" id="gbSecondHand"></div>
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
          SIAP SCANNING GURU
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

    <!-- Input off-screen untuk ketikan alat scanner fisik -->
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

  <!-- ── BOTTOM RUNNING TEXT (Spans Columns 2 & 3) ──────────────── -->
  @php
    $runningText = $announcement ?? ('✨ Selamat Datang di Live Presensi Guru & Karyawan ' . $namaSekolah . ' — Budayakan Disiplin & Tepat Waktu! ✨');
  @endphp
  <div class="bottom-running-bar">
    <div class="announce-icon">📢</div>
    <marquee scrollamount="5" onmouseover="this.stop()" onmouseout="this.start()">{{ $runningText }}</marquee>
  </div>

</div><!-- /main -->

<script>
// ─── CONFIG ───────────────────────────────────────────────────────────────
const SCAN_URL       = '{{ route("public.live-board-guru.scan") }}';
const LEADERBOARD_URL= '{{ route("public.live-board-guru.data") }}';
const CSRF           = document.querySelector('meta[name="csrf-token"]').content;
const JAM_MASUK_CFG  = '{{ $jamMasukCfg }}';
const TOLERANSI_MENIT= {{ $toleransi }};
const REFRESH_MS     = 3000;
const DISMISS_MS     = 800;
const CURRENT_MODE   = '{{ $mode }}';
const APP_TIMEZONE   = '{{ $ianaTimezone ?? "Asia/Jakarta" }}';

let guruListGlobal = @json($guruList);
let currentFilter  = 'all';

function switchMode(mode) {
  window.location.search = '?mode=' + mode;
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(() => {});
  } else {
    document.exitFullscreen && document.exitFullscreen();
  }
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
    pillEl.textContent = '🌙 SESI PULANG GURU';
    pillEl.style.background = 'rgba(0, 207, 232, 0.15)';
    pillEl.style.color = '#00cfe8';
    pillEl.style.borderColor = 'rgba(0, 207, 232, 0.4)';
    cdEl.style.color = '#00cfe8';
    cdEl.textContent = '🌙 Presensi Pulang Aktif';
    return;
  }

  pillEl.textContent = '☀️ SESI MASUK GURU';
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

// ─── INDONESIA LUXURY SPORT WATCH — Live Board Guru ────────────────────────
const _gbTz = (typeof APP_TIMEZONE !== 'undefined' && APP_TIMEZONE) ? APP_TIMEZONE : 'Asia/Jakarta';
const _gbTzFormatter = new Intl.DateTimeFormat('en-US', {
  timeZone: _gbTz,
  hour: 'numeric', minute: 'numeric', second: 'numeric',
  hour12: false
});
const _gbDayFormatter = new Intl.DateTimeFormat('id-ID', {
  timeZone: _gbTz,
  weekday: 'short', day: '2-digit'
});

// Elemen jarum & LCD (diisi setelah DOM siap)
let _gbHourHand, _gbMinuteHand, _gbSecondHand, _gbSubLeftHand, _gbSubRightHand, _gbLcdDay, _gbLcdTime;
const _gbPrevDeg = { hour: null, minute: null };

function _gbSetRot(el, deg, key) {
  if (!el) return;
  const prev = _gbPrevDeg[key];
  if (prev !== null && deg < prev - 180) {
    el.style.transition = 'none';
    el.style.transform = `rotate(${deg}deg)`;
    void el.offsetWidth;
    el.style.transition = '';
  } else {
    el.style.transform = `rotate(${deg}deg)`;
  }
  _gbPrevDeg[key] = deg;
}

function updateGbWatch() {
  const now = new Date();
  const timeParts = _gbTzFormatter.formatToParts(now);
  const h = +timeParts.find(p => p.type === 'hour').value;
  const m = +timeParts.find(p => p.type === 'minute').value;
  const s = +timeParts.find(p => p.type === 'second').value;

  _gbSetRot(_gbHourHand,   (h % 12) * 30 + m * 0.5, 'hour');
  _gbSetRot(_gbMinuteHand, m * 6 + s * 0.1,          'minute');

  // Jarum detik: snap langsung (tanpa transisi) agar berdetak
  if (_gbSecondHand) {
    _gbSecondHand.style.transition = 'none';
    _gbSecondHand.style.transformOrigin = '50% 56px';
    _gbSecondHand.style.transform = `rotate(${s * 6}deg)`;
  }

  if (_gbSubLeftHand)  _gbSubLeftHand.style.transform  = `rotate(${(m / 60) * 360}deg)`;
  if (_gbSubRightHand) _gbSubRightHand.style.transform = `rotate(${(h / 12) * 360}deg)`;

  if (_gbLcdDay) {
    const dayParts = _gbDayFormatter.formatToParts(now);
    const weekday = dayParts.find(p => p.type === 'weekday').value.toUpperCase();
    const day     = dayParts.find(p => p.type === 'day').value;
    _gbLcdDay.textContent = `${weekday} ${day}`;
  }
  if (_gbLcdTime) {
    _gbLcdTime.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  }
}

(function initGbWatch() {
  // 1. LumiBrite tick marks (60 with 12 major)
  const lumiEl = document.getElementById('gbLumiBars');
  if (lumiEl && lumiEl.children.length === 0) {
    for (let i = 0; i < 60; i++) {
      const tick = document.createElement('div');
      tick.className = 'lumi-tick' + (i % 5 === 0 ? ' major' : '');
      tick.style.transform = `rotate(${i * 6}deg)`;
      lumiEl.appendChild(tick);
    }
  }

  // 2. Bezel ticks (60)
  const bezelEl = document.getElementById('gbBezelTicks');
  if (bezelEl && bezelEl.children.length === 0) {
    for (let i = 0; i < 60; i++) {
      const tick = document.createElement('div');
      tick.className = 'bezel-tick' + (i % 5 === 0 ? ' major' : '');
      tick.style.transform = `rotate(${i * 6}deg)`;
      bezelEl.appendChild(tick);
    }
  }

  // 3. Sub-dial ticks
  function buildSubTicks(id, count) {
    const el = document.getElementById(id);
    if (!el || el.children.length > 0) return;
    for (let i = 0; i < count; i++) {
      const tick = document.createElement('div');
      tick.className = 'sub-tick' + (i % (count / 4) === 0 ? ' major' : '');
      tick.style.transform = `rotate(${(i / count) * 360}deg)`;
      el.appendChild(tick);
    }
  }
  buildSubTicks('gbSubLeftTicks', 20);
  buildSubTicks('gbSubRightTicks', 20);

  // 4. Simpan referensi elemen ke variabel luar
  _gbHourHand    = document.getElementById('gbHourHand');
  _gbMinuteHand  = document.getElementById('gbMinuteHand');
  _gbSecondHand  = document.getElementById('gbSecondHand');
  _gbSubLeftHand  = document.getElementById('gbSubLeftHand');
  _gbSubRightHand = document.getElementById('gbSubRightHand');
  _gbLcdDay  = document.getElementById('gbLcdDay');
  _gbLcdTime = document.getElementById('gbLcdTime');

  // 5. Jalankan pertama kali
  updateGbWatch();
})();

// ─── SOUND & TTS ──────────────────────────────────────────────────────────
let soundEnabled = true;
function toggleSound() {
  soundEnabled = !soundEnabled;
  document.getElementById('toggle-sound-btn').textContent = soundEnabled ? '🔊' : '🔇';
  document.getElementById('toggle-sound-btn').classList.toggle('active', soundEnabled);
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
  } catch (_) {}
}

// ─── Jalankan jam setiap detik (setelah playTickSound sudah didefinisikan) ───
setInterval(function() {
  updateGbWatch();
  playTickSound();
}, 1000);

function speak(text) {
  if (!soundEnabled || !('speechSynthesis' in window)) return;
  try {
    window.speechSynthesis.cancel();
    const u = new SpeechSynthesisUtterance(text);
    u.lang = 'id-ID';
    u.rate = 1.0;
    window.speechSynthesis.speak(u);
  } catch (e) {}
}

function beep(type = 'success') {
  if (!soundEnabled) return;
  try {
    if (!window._audioCtx) {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      window._audioCtx = new AudioCtx();
    }
    const ctx = window._audioCtx;
    if (ctx.state === 'suspended') ctx.resume();

    function playTone(freq, startTime, duration, gainPeak = 0.45, typeWave = 'sine') {
      const osc  = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = typeWave;
      osc.frequency.setValueAtTime(freq, startTime);
      osc.connect(gain);
      gain.connect(ctx.destination);
      gain.gain.setValueAtTime(0, startTime);
      gain.gain.linearRampToValueAtTime(gainPeak, startTime + 0.005);
      gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
      osc.start(startTime);
      osc.stop(startTime + duration + 0.01);
    }

    const now = ctx.currentTime;
    if (type === 'success') {
      playTone(523.25, now,        0.55, 0.45);
      playTone(659.25, now + 0.22, 0.65, 0.45);
    } else if (type === 'error') {
      playTone(330, now,        0.18, 0.40, 'square');
      playTone(220, now + 0.22, 0.22, 0.40, 'square');
    } else {
      playTone(440, now, 0.30, 0.35);
    }
  } catch(_) {}
}

// ─── GURU GRID RENDERER ───────────────────────────────────────────────────
function setFilter(f) {
  currentFilter = f;
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.toggle('active', b.dataset.f === f));
  renderGuruGrid();
}

function renderPill(status) {
  const map = {
    hadir:       ['pill pill-hadir', '✅ Hadir'],
    terlambat:   ['pill pill-terlambat', '⏰ Terlambat'],
    izin:        ['pill pill-izin', '📋 Izin'],
    sakit:       ['pill pill-izin', '🤒 Sakit'],
    dinas:       ['pill pill-izin', '🏛️ Dinas'],
    cuti:        ['pill pill-izin', '🌴 Cuti'],
    alpha:       ['pill pill-belum', '🕐 Belum Absen'],
    belum_absen: ['pill pill-belum', '🕐 Belum Absen'],
    sesuai_jadwal:    ['pill pill-sesuai', '📋 Sesuai Jadwal'],
    tidak_hadir:      ['pill pill-tidak-hadir', '⚠️ Tidak Hadir'],
    belum_dimonitor:  ['pill pill-belum-dimonitor', '🕐 Belum Dimonitor'],
    part_time:        ['pill pill-part-time', '🕐 Part Time'],
  };
  const [cls, label] = map[status] || ['pill pill-belum', '🕐 ' + status];
  return `<span class="${cls}">${label}</span>`;
}

function renderGuruGrid() {
  const grid = document.getElementById('guruGrid');
  if (!grid) return;

  let list = guruListGlobal || [];
  if (currentFilter === 'hadir')          list = list.filter(g => g.status === 'hadir');
  else if (currentFilter === 'terlambat') list = list.filter(g => g.status === 'terlambat');
  else if (currentFilter === 'izin')      list = list.filter(g => ['izin','sakit','dinas','cuti'].includes(g.status));
  else if (currentFilter === 'belum')     list = list.filter(g => ['belum_absen','alpha','belum_dimonitor','part_time','tidak_hadir'].includes(g.status));

  const subEl = document.getElementById('guruTotalSub');
  if (subEl) subEl.textContent = `(${list.length} Guru)`;

  if (list.length === 0) {
    grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1; padding:1.5rem;"><p>Tidak ada data guru untuk filter ini.</p></div>`;
    return;
  }

  grid.innerHTML = list.map(g => {
    const isPartTime = g.tipe_kepegawaian === 'part_time';

    let jamText = '';
    if (g.jam_masuk) jamText = `Masuk ${g.jam_masuk}`;
    if (g.jam_pulang) jamText += ` &bull; Pulang ${g.jam_pulang}`;
    if (!jamText) {
      jamText = ['sesuai_jadwal', 'belum_dimonitor', 'tidak_hadir', 'part_time'].includes(g.status) ? 'Slot mengajar' : 'Belum scan';
    }

    const isBelum = ['belum_absen', 'alpha', 'belum_dimonitor', 'part_time', 'tidak_hadir'].includes(g.status);

    return `<div class="guru-card ${isBelum ? 'is-belum' : ''}">
      <img class="guru-card-avatar" src="${g.foto}" alt="${g.nama}" onerror="this.src='{{ asset('assets/img/avatars/1.png') }}'">
      <div class="guru-card-info">
        <div class="guru-card-name">${g.nama}</div>
        <div class="guru-card-sub">${g.jabatan}${isPartTime ? ' · Part Time' : ''}</div>
        <div class="guru-card-meta">
          ${isPartTime ? '<span class="pill pill-part-time">Part Time</span>' : ''}
          ${renderPill(g.status)}
          <span style="font-size:0.6rem; color:var(--muted);">${jamText}</span>
        </div>
      </div>
    </div>`;
  }).join('');
}
renderGuruGrid();

// ─── SCANNER VARS ─────────────────────────────────────────────────────────
let lastQR = '', lastQRTime = 0, scanCount = 0;

// ─── HANDLE SCAN → SERVER ─────────────────────────────────────────────────
async function handleScan(qrCode) {
  const now = Date.now();
  if (qrCode === lastQR && (now - lastQRTime) < 3000) {
    showToast('warning', '⚠️', null, 'QR / NIP yang sama baru saja di-scan. Silakan tunggu 3 detik.');
    return;
  }

  lastQR = qrCode;
  lastQRTime = now;

  try {
    const resp = await fetch(SCAN_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify({ qr_code: qrCode, mode: CURRENT_MODE }),
    });
    const data = await resp.json();
    if (data.success) {
      scanCount++;
      document.getElementById('scan-count').textContent = scanCount;
      showToast('success', '✅', data.guru, data.message);
      beep('success');
      if (data.guru?.nama) {
        const greeting = CURRENT_MODE === 'pulang' ? 'Selamat jalan' : 'Selamat datang';
        speak(`${greeting}, Bapak atau Ibu ${data.guru.nama}`);
      }
      refreshLeaderboard();
    } else if (data.already) {
      showToast('warning', '⚠️', data.guru, data.message);
      beep('error');
    } else {
      showToast('error', '❌', null, data.message ?? 'QR Code / NIP Guru tidak dikenal.');
      beep('error');
    }
  } catch(e) {
    showToast('error', '❌', null, 'Gagal terhubung ke server. Coba lagi.');
    beep('error');
  }
}

// ─── TOAST ────────────────────────────────────────────────────────────────
let toastTimer = null;
function showToast(type, icon, guru, msg) {
  const toast  = document.getElementById('result-toast');
  const barFill= document.getElementById('result-bar-fill');
  document.getElementById('result-icon').textContent = icon;
  document.getElementById('result-name').textContent = guru?.nama ?? (type === 'error' ? 'Error' : 'Info');
  document.getElementById('result-sub').textContent  = guru?.jabatan ? `${guru.jabatan} · ${guru.jam}` : '';
  document.getElementById('result-msg').textContent  = msg;

  toast.className = `result-toast ${type} show`;
  barFill.style.transition = 'none'; barFill.style.width = '0%';

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
    if (parts.length === 2) jamVal += ':00';

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
      <td class="name-cell"><div class="name">${r.nama}</div><div class="kelas-badge">${r.jabatan}</div></td>
      <td><span class="jam-cell ${isLateRow?'jam-late':'jam-early'}">${jamVal}</span></td>
      <td class="status-col">${badge}</td>
    </tr>`;
  }).join('') || (colClass === 'awal' 
    ? `<tr><td colspan="4"><div class="empty-state"><span class="icon">🌅</span><p>Belum ada guru yang hadir hari ini</p></div></td></tr>`
    : `<tr><td colspan="4"><div class="empty-state"><span class="icon">🌙</span><p>Belum ada data scan terbaru hari ini</p></div></td></tr>`);
}

let oldAwalStr = '';
let oldTerbaruStr = '';
let oldGuruListStr = '';

async function refreshLeaderboard() {
  try {
    const url = LEADERBOARD_URL + '?mode=' + CURRENT_MODE;
    const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const res = await resp.json();
    const data = res.data ?? res;
    
    if (data.leaderboardAwal || res.awal) {
      const awalData = res.awal ?? data.leaderboardAwal;
      const newAwalStr = JSON.stringify(awalData);
      if (newAwalStr !== oldAwalStr) {
        document.getElementById('tbody-awal').innerHTML = renderRows(awalData, 'awal');
        oldAwalStr = newAwalStr;
      }
    }
    
    if (data.leaderboardTerbaru || res.terbaru) {
      const terbaruData = res.terbaru ?? data.leaderboardTerbaru;
      const newTerbaruStr = JSON.stringify(terbaruData);
      if (newTerbaruStr !== oldTerbaruStr) {
        document.getElementById('tbody-akhir').innerHTML = renderRows(terbaruData, 'terbaru');
        oldTerbaruStr = newTerbaruStr;
      }
    }

    if (data.stats) {
      const statsMap = {
        's-hadir': data.stats.hadir ?? 0,
        's-hadir-large': CURRENT_MODE === 'pulang' ? (data.stats.pulang ?? 0) : (data.stats.hadir ?? 0),
        's-sakit': data.stats.sakit ?? 0,
        's-izin': data.stats.izin_sakit ?? 0,
        's-alpha': data.stats.belum_absen ?? 0,
        's-terlambat': data.stats.terlambat ?? 0,
        's-remaining': data.stats.remaining ?? 0,
        's-part-time': data.stats.part_time ?? 0,
        's-pt-sesuai': data.stats.part_time_sesuai_jadwal ?? 0,
        's-pt-belum-monitor': data.stats.part_time_belum_dimonitor ?? 0,
        's-pt-tidak-hadir': data.stats.part_time_tidak_hadir ?? 0,
        's-pt-tanpa-slot': data.stats.part_time_tanpa_slot ?? 0
      };
      for (const [id, val] of Object.entries(statsMap)) {
        const el = document.getElementById(id);
        if (el && el.textContent !== String(val)) {
          el.textContent = val;
        }
      }
    }

    if (data.guruList) {
      const newGuruStr = JSON.stringify(data.guruList);
      if (newGuruStr !== oldGuruListStr) {
        guruListGlobal = data.guruList;
        renderGuruGrid();
        oldGuruListStr = newGuruStr;
      }
    }
  } catch(_) {}
}

setInterval(refreshLeaderboard, REFRESH_MS);

// ════════════════════════════════════════════════════════════════════════════
// HARDWARE QR & RFID SCANNER (GURU)
// ════════════════════════════════════════════════════════════════════════════
(function initPiketScanner() {
  const CHAR_INTERVAL_MAX = 100;
  const COMMIT_TIMEOUT_MS = 200;
  const REFOCUS_INTERVAL  = 300;
  const MIN_CODE_LENGTH   = 3;

  const hwInput    = document.getElementById('hw-scanner-input');
  const hwIndicator= document.getElementById('hw-indicator');

  let buffer    = '';
  let lastCharAt= 0;
  let commitTmr = null;
  let guardTmr  = null;

  function setStatus(type) {
    const cfg = {
      ready:    { text: '🔌 Scanner Guru: AKTIF & Siap', color: 'var(--success)' },
      scanning: { text: '🔌 Scanner Guru: Memproses…', color: '#a78bfa' },
      lost:     { text: '⚠️ Scanner: Refokus…',        color: 'var(--warning)' },
    };
    const s = cfg[type] ?? cfg.ready;
    if (hwIndicator) { hwIndicator.textContent = s.text; hwIndicator.style.color = s.color; }
  }

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

  async function commitScan() {
    const code = buffer.trim();
    buffer = '';
    hwInput.value = '';
    if (commitTmr) { clearTimeout(commitTmr); commitTmr = null; }
    if (code.length < MIN_CODE_LENGTH) return;

    scanQueue.push(code);
    processQueue();
  }

  hwInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      buffer = hwInput.value.trim() || buffer;
      hwInput.value = '';
      commitScan();
    }
  });

  hwInput.addEventListener('input', function() {
    const now   = Date.now();
    const delta = now - lastCharAt;
    lastCharAt  = now;
    const val   = hwInput.value;

    if (delta < CHAR_INTERVAL_MAX) {
      buffer = val;
      if (commitTmr) clearTimeout(commitTmr);
      commitTmr = setTimeout(commitScan, COMMIT_TIMEOUT_MS);
    } else {
      buffer = val;
    }
  });

  function ensureFocus() {
    if (document.activeElement !== hwInput && !document.hidden) {
      hwInput.focus({ preventScroll: true });
      if (document.activeElement === hwInput) {
        if (!isProcessingQueue) setStatus('ready');
      } else {
        setStatus('lost');
      }
    }
  }

  guardTmr = setInterval(ensureFocus, REFOCUS_INTERVAL);
  setTimeout(ensureFocus, 400);

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) setTimeout(ensureFocus, 200);
  });

  document.addEventListener('mouseup', function(e) {
    if (e.target.closest('button') || e.target.tagName === 'INPUT') return;
    setTimeout(ensureFocus, 50);
  });

  hwInput.addEventListener('blur', () => {
    setTimeout(() => {
      if (document.activeElement !== hwInput) setStatus('lost');
    }, 100);
  });
  hwInput.addEventListener('focus', () => {
    if (!isProcessingQueue) setStatus('ready');
  });

  setStatus('ready');
})();
</script>
</body>
</html>