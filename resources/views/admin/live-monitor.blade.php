<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Live Attendance Monitor — {{ $pengaturanArr['nama_sekolah'] ?? $pengaturanArr['nama_lembaga'] ?? 'Sistem Absensi' }}</title>
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
    ::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 4px; }
    
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
      border-radius: 99px; padding: 4px 12px; font-size: 0.72rem; font-weight: 700; color: var(--danger);
    }
    .live-dot { width: 7px; height: 7px; background: var(--danger); border-radius: 50%; animation: pulse 1.4s ease-in-out infinite; }
    @keyframes pulse { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:.4; transform:scale(1.4); } }

    .announce-bar {
      background: rgba(115,103,240,.12); border-bottom: 1px solid rgba(115,103,240,.2);
      padding: 6px 1.5rem; overflow: hidden; white-space: nowrap;
    }
    .announce-bar marquee { font-size: 0.8rem; color: rgba(255,255,255,.7); }

    .main {
      display: grid;
      grid-template-columns: 360px 1fr 1fr;
      grid-template-rows: 1fr;
      gap: 0.75rem;
      padding: 0.75rem;
      flex: 1;
      min-height: 0;
      overflow: hidden;
    }

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
    .panel-body  { flex: 1; overflow-y: auto; padding: 0; display: flex; flex-direction: column; }

    .stat-chips { display: flex; gap: 0.4rem; flex-wrap: wrap; padding: 0.6rem 1.1rem; border-bottom: 1px solid var(--border); flex-shrink: 0; }
    .stat-chip {
      display: flex; align-items: center; gap: 0.35rem;
      background: rgba(255,255,255,.05); border: 1px solid var(--border);
      border-radius: 99px; padding: 3px 10px; font-size: 0.72rem; font-weight: 600;
    }
    .stat-chip .dot { width: 6px; height: 6px; border-radius: 50%; }

    .lb-table { width: 100%; border-collapse: collapse; }
    .lb-table thead th {
      position: sticky; top: 0; z-index: 2;
      background: rgba(15,22,35,.97);
      padding: 0.5rem 0.8rem; font-size: 0.65rem; font-weight: 700; color: var(--muted);
      text-transform: uppercase; letter-spacing: .8px; text-align: left; border-bottom: 1px solid var(--border);
    }
    .lb-table tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
    .lb-table tbody tr:hover { background: rgba(255,255,255,.03); }
    .lb-table tbody td { padding: 0.6rem 0.8rem; font-size: 0.83rem; vertical-align: middle; }
    .lb-table tbody tr.top-3 { background: rgba(115,103,240,.06); }
    .lb-table tbody tr.late-row { background: rgba(255,159,67,.04); }

    .rank-cell { width: 45px; text-align: center; }
    .name-cell .name { font-weight: 600; font-size: 0.88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
    .name-cell .kelas-badge { font-size: 0.7rem; color: var(--muted); margin-top: 1px; }
    .jam-cell { font-family: 'Courier New', monospace; font-weight: 700; font-size: 0.95rem; white-space: nowrap; width: 90px; text-align: center; }
    .jam-early { color: var(--success); }
    .jam-late  { color: var(--warning); }
    .status-badge {
      display: inline-block; border-radius: 99px; padding: 2px 10px;
      font-size: 0.68rem; font-weight: 700; white-space: nowrap;
    }
    .badge-hadir { background: rgba(40,199,111,.15); color: var(--success); }
    .badge-terlambat { background: rgba(255,159,67,.15); color: var(--warning); }
    .late-minutes { font-size: 0.68rem; color: var(--warning); display: block; margin-top: 1px; font-weight: 600; }

    .empty-state {
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      padding: 3rem; color: var(--muted); gap: 0.5rem; flex: 1;
    }
    .empty-state .icon { font-size: 3rem; opacity: .4; }
    .empty-state p { font-size: 0.82rem; }

    .progress-bar-container { background: rgba(255,255,255,0.05); height: 16px; border-radius: 99px; overflow: hidden; margin-top: 0.5rem; }
    .progress-bar-fill { height: 100%; transition: width 1s ease, background-color 1s ease; border-radius: 99px; }
    .animate-stripes {
      background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
      background-size: 1rem 1rem;
      animation: progress-bar-stripes 1s linear infinite;
    }
    @keyframes progress-bar-stripes { from { background-position: 1rem 0; } to { background-position: 0 0; } }

    .apexcharts-canvas { margin: 0 auto; }
  </style>
</head>
<body>

<header class="header">
  <div class="header-brand">
    @if (isset($pengaturanArr['logo_sekolah']))
      <img src="{{ asset('storage/' . $pengaturanArr['logo_sekolah']) }}" alt="Logo" class="logo-icon" style="object-fit:cover; background:#fff; padding:2px;">
    @else
      <div class="logo-icon">🏫</div>
    @endif
    <div>
      <h1>{{ $pengaturanArr['nama_sekolah'] ?? $pengaturanArr['nama_lembaga'] ?? 'Sistem Absensi' }}</h1>
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

<div class="announce-bar">
  <marquee scrollamount="4">📢 &nbsp; Selamat datang di {{ $pengaturanArr['nama_sekolah'] ?? $pengaturanArr['nama_lembaga'] ?? 'Sistem Absensi' }}. Silahkan tunjukkan kartu identitas QR Anda ke kamera scanner untuk mencatat kehadiran. Jam masuk dimulai pukul {{ $pengaturanArr['jam_masuk'] ?? '07:00' }}. Waktu keterlambatan maksimal {{ $pengaturanArr['toleransi_terlambat'] ?? '15' }} menit.</marquee>
</div>

<div class="main">

  <!-- ── PANEL 1: STATS ─────────────────────────────────── -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">
        📊 <span>Statistik Kehadiran</span>
      </div>
      <div style="font-size:.7rem; color:var(--muted);">Hari Ini</div>
    </div>
    <div class="panel-body" style="padding: 1rem; position: relative;">
      
      <!-- Chart Area -->
      <div style="flex: 1; display:flex; align-items:center; justify-content:center; position:relative;">
        <div id="chartDonutLive" style="width: 100%; min-height:250px;"></div>
        <div style="position: absolute; text-align: center; pointer-events: none;">
          <div id="stat-total-hadir" style="font-size: 2.5rem; font-weight: 800; color: #fff; line-height: 1;">{{ $totalAbsensiHariIni }}</div>
          <div style="font-size: 0.75rem; color: var(--muted); font-weight: 600;">TOTAL HADIR</div>
        </div>
      </div>

      <!-- Stat Values -->
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); text-align: center; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 1rem 0; margin-top: 1rem; gap: 0.5rem;">
        <div>
          <div id="stat-hadir-count" style="font-size: 1.5rem; font-weight: 700; color: var(--success);">{{ $hadirCount }}</div>
          <div style="font-size: 0.75rem; color: var(--muted);">Hadir</div>
        </div>
        <div>
          <div id="stat-izin-sakit" style="font-size: 1.5rem; font-weight: 700; color: var(--info);">{{ $sakitCount + $izinCount }}</div>
          <div style="font-size: 0.75rem; color: var(--muted);">Izin/Sakit</div>
        </div>
        <div>
          <div id="stat-alpha-count" style="font-size: 1.5rem; font-weight: 700; color: var(--danger);">{{ $alphaCount }}</div>
          <div style="font-size: 0.75rem; color: var(--muted);">Alpha</div>
        </div>
      </div>

      <!-- Progress Area -->
      <div style="margin-top: 1.5rem;">
        @php 
          $pct = $totalSiswa > 0 ? ($totalAbsensiHariIni / $totalSiswa) * 100 : 0;
          $barColor = $pct > 85 ? 'var(--success)' : ($pct > 70 ? 'var(--warning)' : 'var(--danger)');
        @endphp
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.5rem;">
          <h4 style="font-size: 0.8rem; font-weight: 600; color: #fff;">KEHADIRAN</h4>
          <div id="stat-pct-text" style="font-size: 1rem; font-weight: 700; color: {{ $barColor }};">{{ number_format($pct, 1) }}%</div>
        </div>
        <div class="progress-bar-container">
          <div id="stat-pct-bar" class="progress-bar-fill animate-stripes" style="width: {{ $pct }}%; background-color: {{ $barColor }};"></div>
        </div>
        <div id="stat-keterangan" style="text-align: center; margin-top: 0.8rem; font-size: 0.75rem; color: var(--muted);">
           {{ $totalAbsensiHariIni }} dari {{ $totalSiswa }} siswa mengabsen
        </div>
      </div>

    </div>
  </div>

  <!-- ── PANEL 2: 10 PALING AWAL ─────────────────────────────────── -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">
        🏆 <span>10 Siswa Paling Awal</span>
      </div>
    </div>
    <div class="panel-body">
      <table class="lb-table">
        <thead><tr>
          <th class="rank-cell">#</th>
          <th>Nama Siswa</th>
          <th style="width: 90px; text-align: center;">Jam</th>
        </tr></thead>
        <tbody id="tbody-awal">
          @forelse($palingAwal as $i => $abs)
            <tr class="{{ $i < 3 ? 'top-3' : '' }}">
              <td class="rank-cell">{{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i+1)) }}</td>
              <td class="name-cell">
                <div class="name">{{ $abs->siswa->nama_lengkap }}</div>
                <div class="kelas-badge">{{ $abs->siswa->kelas->nama ?? '-' }}</div>
              </td>
              <td class="jam-cell jam-early">{{ $abs->jam_masuk }}</td>
            </tr>
          @empty
            <tr><td colspan="3"><div class="empty-state"><span class="icon">🌅</span><p>Belum ada siswa yang hadir hari ini</p></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── PANEL 3: 10 PALING AKHIR ────────────────────────────────── -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">
        🕐 <span>10 Siswa Paling Akhir</span>
      </div>
      <div style="font-size:.7rem; color: var(--warning);">Urut dari terlambat</div>
    </div>
    <div class="panel-body">
      <table class="lb-table">
        <thead><tr>
          <th class="rank-cell">#</th>
          <th>Nama Siswa</th>
          <th style="width: 90px; text-align: center;">Jam</th>
          <th style="width: 120px;">Status</th>
        </tr></thead>
        <tbody id="tbody-akhir">
          @forelse($palingAkhir as $i => $abs)
            @php
              $jamMasukSetting = \Carbon\Carbon::createFromTimeString($pengaturanArr['jam_masuk'] ?? '07:00');
              $jamSiswa   = \Carbon\Carbon::createFromTimeString($abs->jam_masuk);
              $toleransi  = (int)($pengaturanArr['toleransi_terlambat'] ?? 15);
              $selisih    = (int) $jamMasukSetting->diffInMinutes($jamSiswa, false);
              $isLate     = $selisih > $toleransi;
            @endphp
            <tr class="{{ $isLate ? 'late-row' : '' }}">
              <td class="rank-cell" style="color:var(--muted);">{{ $i+1 }}</td>
              <td class="name-cell">
                <div class="name">{{ $abs->siswa->nama_lengkap }}</div>
                <div class="kelas-badge">{{ $abs->siswa->kelas->nama ?? '-' }}</div>
              </td>
              <td class="jam-cell {{ $isLate ? 'jam-late' : '' }}">{{ $abs->jam_masuk }}</td>
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
            <tr><td colspan="4"><div class="empty-state"><span class="icon">🌙</span><p>Belum ada data akhir hari ini</p></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  // ── Const Config ──
  const API_URL = "{{ route('admin.dashboard.refresh-stats') }}";
  const JAM_MASUK = "{{ $pengaturanArr['jam_masuk'] ?? '07:00' }}";
  const TOLERANSI = parseInt("{{ $pengaturanArr['toleransi_terlambat'] ?? 15 }}");
  
  // ── Parse Time Helper ──
  function timeToMinutes(timeStr) {
    if(!timeStr) return 0;
    const parts = timeStr.split(':');
    return parseInt(parts[0]) * 60 + parseInt(parts[1]);
  }
  const jamMasukMins = timeToMinutes(JAM_MASUK);

  // ── Clock ──
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

  // ── Global Chart ──
  let donutChart = null;

  document.addEventListener('DOMContentLoaded', function() {
    const options = {
      series: [{{ $hadirCount }}, {{ $sakitCount }}, {{ $izinCount }}, {{ $alphaCount }}, {{ $terlambatCount }}],
      labels: ['Hadir', 'Sakit', 'Izin', 'Alpha', 'Terlambat'],
      chart: { type: 'donut', height: 260, parentHeightOffset: 0, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
      colors: ['#28c76f', '#00cfe8', '#ff9f43', '#ea5455', '#a78bfa'],
      stroke: { width: 0 },
      legend: { show: false },
      plotOptions: { pie: { donut: { size: '75%', labels: { show: false } } } },
      dataLabels: { enabled: false },
      tooltip: { theme: 'dark' }
    };
    donutChart = new ApexCharts(document.querySelector("#chartDonutLive"), options);
    donutChart.render();

    // ── AJAX REAL-TIME UPDATES ──
    setInterval(fetchLiveStats, 5000); // Poll every 5 seconds
  });

  async function fetchLiveStats() {
    try {
      const resp = await fetch(API_URL, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      if (!resp.ok) return;
      const data = await resp.json();
      
      updateStats(data);
      updateAwalList(data.palingAwal);
      updateAkhirList(data.palingAkhir);

    } catch (e) {
      console.warn('Real-time sync failed:', e);
    }
  }

  function updateStats(data) {
    // Basic DOM Counters
    document.getElementById('stat-total-hadir').textContent = data.totalAbsensiHariIni;
    document.getElementById('stat-hadir-count').textContent = data.hadirCount;
    document.getElementById('stat-izin-sakit').textContent = data.sakitCount + data.izinCount;
    document.getElementById('stat-alpha-count').textContent = data.alphaCount;
    
    // Progress
    const totalSiswa = data.totalSiswa || 1;
    let pct = (data.totalAbsensiHariIni / totalSiswa) * 100;
    pct = isNaN(pct) ? 0 : pct;
    
    let barColor = pct > 85 ? 'var(--success)' : (pct > 70 ? 'var(--warning)' : 'var(--danger)');
    
    const pctText = document.getElementById('stat-pct-text');
    const pctBar = document.getElementById('stat-pct-bar');
    pctText.textContent = pct.toFixed(1) + '%';
    pctText.style.color = barColor;
    pctBar.style.width = pct + '%';
    pctBar.style.backgroundColor = barColor;
    
    document.getElementById('stat-keterangan').textContent = 
      `${data.totalAbsensiHariIni} dari ${totalSiswa} siswa mengabsen`;

    // Update Chart
    if (donutChart) {
      donutChart.updateSeries([
        data.hadirCount,
        data.sakitCount,
        data.izinCount,
        data.alphaCount,
        data.terlambatCount
      ]);
    }
  }

  function updateAwalList(list) {
    const tbody = document.getElementById('tbody-awal');
    if (!list || list.length === 0) {
      tbody.innerHTML = `<tr><td colspan="3"><div class="empty-state"><span class="icon">🌅</span><p>Belum ada siswa yang hadir hari ini</p></div></td></tr>`;
      return;
    }
    
    let html = '';
    list.forEach((item, index) => {
      let rank = index + 1;
      if(index === 0) rank = '🥇';
      if(index === 1) rank = '🥈';
      if(index === 2) rank = '🥉';
      
      const trClass = index < 3 ? 'top-3' : '';
      const nama = item.siswa ? item.siswa.nama_lengkap : '-';
      const kelas = (item.siswa && item.siswa.kelas) ? item.siswa.kelas.nama : '-';
      
      html += `
        <tr class="${trClass}">
          <td class="rank-cell">${rank}</td>
          <td class="name-cell">
            <div class="name">${nama}</div>
            <div class="kelas-badge">${kelas}</div>
          </td>
          <td class="jam-cell jam-early">${item.jam_masuk}</td>
        </tr>
      `;
    });
    tbody.innerHTML = html;
  }

  function updateAkhirList(list) {
    const tbody = document.getElementById('tbody-akhir');
    if (!list || list.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4"><div class="empty-state"><span class="icon">🌙</span><p>Belum ada data akhir hari ini</p></div></td></tr>`;
      return;
    }
    
    let html = '';
    list.forEach((item, index) => {
      const nama = item.siswa ? item.siswa.nama_lengkap : '-';
      const kelas = (item.siswa && item.siswa.kelas) ? item.siswa.kelas.nama : '-';
      
      const jamAbsenMins = timeToMinutes(item.jam_masuk);
      const selisih = jamAbsenMins - jamMasukMins;
      const isLate = selisih > TOLERANSI;
      
      const trClass = isLate ? 'late-row' : '';
      let statusHtml = '';
      
      if(isLate) {
        statusHtml = `<span class="status-badge badge-terlambat">⏰ Terlambat</span>`;
        if (selisih > 0) statusHtml += `<span class="late-minutes">+${selisih} menit</span>`;
      } else {
        statusHtml = `<span class="status-badge badge-hadir">✅ Hadir</span>`;
      }
      
      html += `
        <tr class="${trClass}">
          <td class="rank-cell" style="color:var(--muted);">${index + 1}</td>
          <td class="name-cell">
            <div class="name">${nama}</div>
            <div class="kelas-badge">${kelas}</div>
          </td>
          <td class="jam-cell ${isLate ? 'jam-late' : ''}">${item.jam_masuk}</td>
          <td>${statusHtml}</td>
        </tr>
      `;
    });
    tbody.innerHTML = html;
  }
</script>
</body>
</html>
