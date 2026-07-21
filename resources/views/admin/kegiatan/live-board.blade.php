@php
  $namaSekolah = \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Sistem Presensi';
  $liveFont = \App\Models\Pengaturan::where('key', 'live_board_font_family')->value('value') ?? 'Product Sans';
  $liveCounterFont = \App\Models\Pengaturan::where('key', 'live_board_counter_font_family')->value('value') ?? 'Courier New';
  $liveCounterColor = \App\Models\Pengaturan::where('key', 'live_board_counter_color')->value('value') ?? '#7367f0';
  $browserFonts = ['Courier New', 'Courier', 'Arial', 'Helvetica', 'Times New Roman', 'Times', 'Georgia', 'Verdana', 'Trebuchet MS', 'Impact', 'Comic Sans MS', 'Palatino', 'Bookman Old Style', 'monospace', 'serif', 'sans-serif'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Live Board Absensi — {{ $kegiatan->nama_kegiatan }}</title>
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  @if($liveFont !== 'Product Sans' || (!in_array($liveCounterFont, $browserFonts) && $liveCounterFont !== 'Product Sans'))
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if($liveFont !== 'Product Sans')
      <link href="https://fonts.googleapis.com/css2?family={{ urlencode($liveFont) }}:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @endif
    @if(!in_array($liveCounterFont, $browserFonts) && $liveCounterFont !== 'Product Sans')
      <link href="https://fonts.googleapis.com/css2?family={{ urlencode($liveCounterFont) }}:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @endif
  @endif
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

    ::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 4px; }

    /* HEADER */
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

    /* MAIN GRID */
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
    .scanner-col { grid-column: 1; grid-row: 1; }

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

    /* LEADERBOARD TABLE */
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

    .rank-cell { width: 40px; text-align: center; font-size: 1rem; }
    .name-cell { width: auto; }
    .name-cell .name { font-weight: 600; font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .name-cell .kelas-badge { font-size: 0.68rem; color: var(--muted); margin-top: 1px; }
    .jam-col { width: 85px; text-align: center; }
    .jam-cell { font-family: 'Courier New', monospace; font-weight: 700; font-size: 0.88rem; white-space: nowrap; text-align: center; display: block; }
    .jam-early { color: var(--success); }
    .status-col { width: 115px; }
    .status-badge {
      display: inline-block; border-radius: 5px; padding: 2px 9px;
      font-size: 0.66rem; font-weight: 700; white-space: nowrap;
    }
    .badge-hadir { background: rgba(40,199,111,.15); color: var(--success); }

    .empty-state {
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      padding: 3rem; color: var(--muted); gap: 0.5rem;
    }
    .empty-state .icon { font-size: 3rem; opacity: .4; }
    .empty-state p { font-size: 0.82rem; }

    /* SCANNER PANEL */
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

    /* RESULT TOAST */
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

    /* SCAN LOG */
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
</head>
<body>

<header class="header">
  <div class="header-brand">
    <div class="logo-icon">📅</div>
    <div>
      <h1>{{ $kegiatan->nama_kegiatan }}</h1>
      <p>Live Board & Scanner Absensi Kegiatan</p>
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

<div class="main">
  <!-- PUSAT KONTROL & SCANNER -->
  <div class="panel scanner-col" style="position:relative; overflow:hidden;">
    <div class="panel-header">
      <div class="panel-title">🔌 <span>Pusat Kontrol &amp; Counter Scanner</span></div>
      <div style="display:flex;align-items:center;gap:.5rem;">
        <span id="hw-indicator" title="Status alat scanner fisik" style="font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:5px;background:rgba(40,199,111,.15);color:var(--success);border:1px solid rgba(40,199,111,.4);">🔌 Scanner: AKTIF & Siap</span>
      </div>
    </div>

    <div class="scanner-area">
      <div class="counter-widget">
        <div class="counter-title">Total Hadir Hari Ini</div>
        <div class="counter-value">
          <span class="current" id="s-hadir-large">{{ $totalHadir }}</span>
          <span class="slash">/</span>
          <span class="total-cap" id="large-total-kapasitas">{{ $totalTarget }}</span>
          <span class="unit">Siswa</span>
        </div>
      </div>

      <div class="stat-chips" id="stat-chips">
        <div class="stat-chip"><span class="dot" style="background:var(--success)"></span> Hadir: <strong id="s-hadir">{{ $totalHadir }}</strong></div>
        <div class="stat-chip"><span class="dot" style="background:var(--danger)"></span> Alpha/Belum: <strong id="s-alpha">{{ $totalAlpha }}</strong></div>
      </div>

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

  <!-- TABLE 10 HADIR PALING AWAL -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">🏆 <span>10 Hadir Tercepat</span></div>
      <div style="font-size:.7rem; color:var(--muted);">{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</div>
    </div>
    <div class="panel-body">
      <table class="lb-table">
        <thead>
          <tr>
            <th class="rank-cell">#</th>
            <th class="name-cell">Nama Siswa</th>
            <th class="jam-col">Jam</th>
            <th class="status-col">Status</th>
          </tr>
        </thead>
        <tbody id="tbody-awal">
          @forelse($logs->reverse()->take(10)->values() as $i => $log)
            <tr class="{{ $i < 3 ? 'top-3' : '' }}">
              <td class="rank-cell">{{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i+1)) }}</td>
              <td class="name-cell">
                <div class="name">{{ $log->siswa->nama_lengkap }}</div>
                <div class="kelas-badge">{{ $log->siswa->kelas?->nama ?? '-' }}</div>
              </td>
              <td class="jam-cell jam-early">{{ \Carbon\Carbon::parse($log->jam_absen)->format('H:i:s') }}</td>
              <td><span class="status-badge badge-hadir">✅ Hadir</span></td>
            </tr>
          @empty
            <tr><td colspan="4"><div class="empty-state"><span class="icon">🌅</span><p>Belum ada siswa yang hadir hari ini</p></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- TABLE RIWAYAT SCAN TERBARU -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">🕐 <span>Riwayat Scan Terbaru</span></div>
      <div style="font-size:.7rem; color: var(--muted);">Urutan scan dari yang paling baru</div>
    </div>
    <div class="panel-body">
      <table class="lb-table">
        <thead>
          <tr>
            <th class="rank-cell">#</th>
            <th class="name-cell">Nama Siswa</th>
            <th class="jam-col">Jam</th>
            <th class="status-col">Status</th>
          </tr>
        </thead>
        <tbody id="tbody-terbaru">
          @forelse($logs as $i => $log)
            <tr>
              <td class="rank-cell" style="color:var(--muted);">{{ $i+1 }}</td>
              <td class="name-cell">
                <div class="name">{{ $log->siswa->nama_lengkap }}</div>
                <div class="kelas-badge">{{ $log->siswa->kelas?->nama ?? '-' }}</div>
              </td>
              <td class="jam-cell">{{ \Carbon\Carbon::parse($log->jam_absen)->format('H:i:s') }}</td>
              <td><span class="status-badge badge-hadir">✅ Hadir</span></td>
            </tr>
          @empty
            <tr><td colspan="4"><div class="empty-state"><span class="icon">🌙</span><p>Belum ada data scan terbaru hari ini</p></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const SCAN_URL = '{{ route("admin.absensi-kegiatan.live-board.scan", $kegiatan->id) }}';
const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
const DISMISS_MS = 1500;

// CLOCK
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

// SOUND
let soundEnabled = true;
function toggleSound() {
  soundEnabled = !soundEnabled;
  document.getElementById('toggle-sound-btn').textContent = soundEnabled ? '🔊' : '🔇';
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
    if (ctx.state === 'suspended') {
      ctx.resume();
    }

    function playTone(freq, startTime, duration, gainPeak = 0.5, curve = 'bell') {
      const osc  = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(freq, startTime);
      osc.connect(gain);
      gain.connect(ctx.destination);

      gain.gain.setValueAtTime(0, startTime);
      if (curve === 'bell') {
        gain.gain.linearRampToValueAtTime(gainPeak, startTime + 0.005);
        gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
      } else {
        gain.gain.linearRampToValueAtTime(gainPeak, startTime + 0.01);
        gain.gain.linearRampToValueAtTime(gainPeak * 0.8, startTime + duration - 0.02);
        gain.gain.linearRampToValueAtTime(0.001, startTime + duration);
      }
      osc.start(startTime);
      osc.stop(startTime + duration + 0.01);
    }

    const now = ctx.currentTime;
    if (type === 'success') {
      playTone(523.25, now,        0.55, 0.45, 'bell');
      playTone(1046.5, now,        0.45, 0.12, 'bell');
      playTone(659.25, now + 0.22, 0.65, 0.45, 'bell');
      playTone(1318.5, now + 0.22, 0.55, 0.10, 'bell');
    } else {
      playTone(330, now,        0.18, 0.40, 'square');
      playTone(220, now + 0.22, 0.22, 0.40, 'square');
    }
  } catch(_) {}
}

// TOAST & LOGS
let toastTimer = null;
let scanCount = 0;

function showToast(type, icon, title, subtitle, msg) {
  const toast  = document.getElementById('result-toast');
  const barFill= document.getElementById('result-bar-fill');
  document.getElementById('result-icon').textContent = icon;
  document.getElementById('result-name').textContent = title;
  document.getElementById('result-sub').textContent  = subtitle;
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

function updateStats(stats) {
  document.getElementById('s-hadir-large').textContent = stats.totalHadir;
  document.getElementById('s-hadir').textContent = stats.totalHadir;
  document.getElementById('s-alpha').textContent = stats.totalAlpha;
  document.getElementById('large-total-kapasitas').textContent = stats.totalTarget;
}

function prependToLogs(siswa, waktu) {
  const tbodyTerbaru = document.getElementById('tbody-terbaru');
  const tbodyAwal = document.getElementById('tbody-awal');
  
  // Remove empty states if present
  const emptyTerbaru = tbodyTerbaru.querySelector('.empty-state');
  if (emptyTerbaru) tbodyTerbaru.innerHTML = '';
  const emptyAwal = tbodyAwal.querySelector('.empty-state');
  if (emptyAwal) tbodyAwal.innerHTML = '';

  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td class="rank-cell" style="color:var(--muted);">-</td>
    <td class="name-cell">
      <div class="name">${siswa.nama}</div>
      <div class="kelas-badge">${siswa.kelas}</div>
    </td>
    <td class="jam-cell">${waktu}</td>
    <td><span class="status-badge badge-hadir">✅ Hadir</span></td>
  `;
  tbodyTerbaru.insertBefore(tr, tbodyTerbaru.firstChild);

  // Re-index tbodyTerbaru numbers
  const rows = tbodyTerbaru.querySelectorAll('tr');
  rows.forEach((row, index) => {
    const cell = row.querySelector('.rank-cell');
    if (cell) cell.textContent = index + 1;
  });

  // For tbodyAwal, we can just reload page or dynamically push. Let's do a simple clone or insert.
  // To keep it simple, we prepend to Awal too if it has space or isn't 10 yet.
  const trAwal = document.createElement('tr');
  trAwal.innerHTML = `
    <td class="rank-cell">-</td>
    <td class="name-cell">
      <div class="name">${siswa.nama}</div>
      <div class="kelas-badge">${siswa.kelas}</div>
    </td>
    <td class="jam-cell jam-early">${waktu}</td>
    <td><span class="status-badge badge-hadir">✅ Hadir</span></td>
  `;
  tbodyAwal.appendChild(trAwal);
  const rowsAwal = tbodyAwal.querySelectorAll('tr');
  rowsAwal.forEach((row, index) => {
    const cell = row.querySelector('.rank-cell');
    if (cell) {
      cell.textContent = index === 0 ? '🥇' : (index === 1 ? '🥈' : (index === 2 ? '🥉' : index + 1));
    }
    if (index < 3) {
      row.classList.add('top-3');
    } else {
      row.classList.remove('top-3');
    }
  });
}

async function handleScan(qrCode) {
  try {
    const resp = await fetch(SCAN_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify({ qr_code: qrCode }),
    });
    const data = await resp.json();
    if (resp.status === 200 && data.success) {
      scanCount++;
      document.getElementById('scan-count').textContent = scanCount;
      showToast('success', '✅', data.siswa_nama, `Kelas ${data.siswa_kelas}`, data.message);
      beep('success');
      updateStats(data.stats);
      prependToLogs({ nama: data.siswa_nama, kelas: data.siswa_kelas }, data.waktu);
    } else {
      showToast('error', '❌', 'Gagal', '', data.message ?? 'QR tidak dikenal.');
      beep('error');
    }
  } catch(e) {
    showToast('error', '❌', 'Koneksi Error', '', 'Gagal terhubung ke server. Coba lagi.');
    beep('error');
  }
}

// HARDWARE SCANNER INPUT FOCUS GUARD
(function initScanner() {
  const hwInput = document.getElementById('hw-scanner-input');
  let buffer = '';

  function ensureFocus() {
    if (document.activeElement !== hwInput && !document.hidden) {
      hwInput.focus({ preventScroll: true });
    }
  }

  setInterval(ensureFocus, 300);
  setTimeout(ensureFocus, 400);

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) setTimeout(ensureFocus, 200);
  });

  document.addEventListener('mouseup', function(e) {
    if (e.target.closest('button') || e.target.tagName === 'INPUT') return;
    setTimeout(ensureFocus, 50);
  });

  hwInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const code = hwInput.value.trim();
      hwInput.value = '';
      if (code.length >= 3) {
        handleScan(code);
      }
    }
  });
})();
</script>
</body>
</html>
