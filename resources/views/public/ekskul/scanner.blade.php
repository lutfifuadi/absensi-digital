<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Scanner Presensi Ekskul — Pembina</title>
  
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
  <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

  <style>
    :root {
      --das-primary: #10b981;
      --das-primary-glow: rgba(16, 185, 129, 0.35);
      --das-success: #28c76f;
      --das-warning: #ff9f43;
      --das-danger: #ea5455;
      --das-dark-bg: #0f172a;
      --das-panel-bg: rgba(30, 41, 59, 0.75);
      --das-border: rgba(255, 255, 255, 0.08);
      --font-family: 'Product Sans', sans-serif;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
      height: 100dvh; max-height: 100dvh; width: 100vw; max-width: 100vw;
      font-family: var(--font-family);
      background: var(--das-dark-bg);
      color: #e2e8f0;
      overflow: hidden;
      display: flex; flex-direction: column;
    }

    /* ── NAVBAR ────────────────────────────────────────────── */
    .navbar {
      display: flex; align-items: center; justify-content: space-between;
      padding: 0.6rem 1rem;
      background: rgba(15, 23, 42, 0.9);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--das-border);
      flex-shrink: 0; z-index: 100;
    }
    .nav-brand { display: flex; align-items: center; gap: 0.6rem; }
    .brand-icon {
      width: 34px; height: 34px;
      background: linear-gradient(135deg, var(--das-primary), #059669);
      border-radius: 5px;
      display: flex; align-items: center; justify-content: center;
      color: white;
      box-shadow: 0 0 12px var(--das-primary-glow);
    }
    .brand-icon svg { width: 20px; height: 20px; }

    .nav-brand h1 { font-size: 0.95rem; font-weight: 800; color: #fff; margin: 0; letter-spacing: -0.3px; }
    .nav-brand p { font-size: 0.65rem; color: #94a3b8; margin: -2px 0 0 0; }
    .btn-logout {
      background: rgba(234, 84, 85, 0.12);
      border: 1px solid rgba(234, 84, 85, 0.25);
      color: var(--das-danger);
      padding: 0.35rem 0.75rem; border-radius: 5px;
      font-size: 0.75rem; font-weight: 700; cursor: pointer;
      display: flex; align-items: center; gap: 0.35rem;
      transition: all 0.2s;
    }
    .btn-logout:hover { background: var(--das-danger); color: white; }
    .btn-logout svg { width: 15px; height: 15px; }

    /* ── LAYOUT MAIN ───────────────────────────────────────── */
    .main-container {
      flex: 1; display: grid; grid-template-columns: 1fr;
      min-height: 0; overflow: hidden;
    }
    @media (min-width: 992px) {
      .main-container { grid-template-columns: 1fr 400px; }
    }

    /* ── LEFT PANEL (SCANNER & SELECTOR) ───────────────────── */
    .scanner-panel {
      display: flex; flex-direction: column; padding: 1rem;
      gap: 0.85rem; overflow-y: auto; background: #000; position: relative;
    }

    /* Controls Card */
    .controls-card {
      background: var(--das-panel-bg);
      backdrop-filter: blur(10px);
      border: 1px solid var(--das-border);
      border-radius: 5px;
      padding: 0.85rem 1rem;
      display: flex; flex-direction: column; gap: 0.75rem;
      flex-shrink: 0; z-index: 10;
    }
    .form-row { display: grid; grid-template-columns: 1fr; gap: 0.65rem; }
    @media (min-width: 640px) {
      .form-row { grid-template-columns: 1fr 1fr; }
    }
    .form-group label {
      display: block; font-size: 0.65rem; font-weight: 700;
      color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.35rem;
    }
    .select-input, .text-input {
      width: 100%;
      background: rgba(15, 23, 42, 0.8);
      border: 1px solid var(--das-border);
      border-radius: 5px; padding: 0.55rem 0.75rem;
      color: white; font-size: 0.85rem; font-family: var(--font-family);
      outline: none; transition: border-color 0.2s;
    }
    .select-input:focus, .text-input:focus {
      border-color: var(--das-primary);
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .select-input option { background: #0f172a; color: white; }

    /* Camera Viewport Wrapper */
    .camera-wrapper {
      flex: 1; min-height: 250px; position: relative;
      background: #050811; border-radius: 5px;
      border: 1px solid var(--das-border);
      overflow: hidden; display: flex; align-items: center; justify-content: center;
    }
    #reader { width: 100%; height: 100%; }
    #reader video { width: 100% !important; height: 100% !important; object-fit: cover !important; }

    /* Manual NIS Bar */
    .manual-bar {
      display: flex; gap: 0.5rem; flex-shrink: 0; margin-top: auto;
    }
    .btn-manual {
      background: var(--das-primary); color: white; border: none;
      padding: 0.55rem 1.1rem; border-radius: 5px;
      font-weight: 700; font-size: 0.82rem; cursor: pointer;
      display: flex; align-items: center; gap: 0.35rem;
      white-space: nowrap; transition: all 0.2s;
    }
    .btn-manual:hover { background: #059669; }
    .btn-manual svg { width: 16px; height: 16px; }

    /* ── RIGHT PANEL (LOG & RECENT) ────────────────────────── */
    .log-panel {
      background: var(--das-panel-bg);
      border-left: 1px solid var(--das-border);
      display: flex; flex-direction: column; overflow: hidden;
    }
    @media (max-width: 991px) {
      .log-panel { border-left: none; border-top: 1px solid var(--das-border); min-height: 220px; }
    }
    .log-header {
      padding: 0.75rem 1rem; border-bottom: 1px solid var(--das-border);
      display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
    }
    .log-header h3 { font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; }
    .badge-count {
      background: rgba(16, 185, 129, 0.15); color: var(--das-primary);
      padding: 0.15rem 0.5rem; border-radius: 5px; font-size: 0.7rem; font-weight: 800;
    }

    .log-list {
      flex: 1; overflow-y: auto; padding: 0.75rem 1rem;
      display: flex; flex-direction: column; gap: 0.55rem;
    }
    .log-item {
      background: rgba(15, 23, 42, 0.5);
      border: 1px solid var(--das-border);
      border-radius: 5px; padding: 0.65rem 0.85rem;
      display: flex; align-items: center; gap: 0.65rem;
      animation: fadeIn 0.3s ease-out;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

    .log-avatar {
      width: 36px; height: 36px; border-radius: 5px;
      background: var(--das-primary-glow); color: var(--das-primary);
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; font-size: 0.8rem; flex-shrink: 0;
      border: 1px solid rgba(16, 185, 129, 0.25);
    }
    .log-avatar.warning { background: rgba(255, 159, 67, 0.15); color: var(--das-warning); border-color: rgba(255, 159, 67, 0.25); }
    .log-avatar.danger  { background: rgba(234, 84, 85, 0.15); color: var(--das-danger); border-color: rgba(234, 84, 85, 0.25); }

    .log-info { flex: 1; min-width: 0; }
    .log-name { font-size: 0.82rem; font-weight: 700; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .log-sub  { font-size: 0.7rem; color: #64748b; margin-top: 1px; }
    .log-time { font-size: 0.72rem; font-weight: 700; color: #94a3b8; }

    .empty-state {
      text-align: center; padding: 2.5rem 1rem; opacity: 0.4;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .empty-state svg { width: 44px; height: 44px; margin-bottom: 0.4rem; opacity: 0.6; }

    /* Toast Notification Overlay */
    .toast-overlay {
      position: fixed; bottom: 1.25rem; left: 50%; transform: translateX(-50%) translateY(40px);
      width: 90%; max-width: 420px;
      background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px);
      border: 1px solid var(--das-border); border-radius: 5px;
      padding: 0.85rem 1.1rem; display: flex; align-items: center; gap: 0.85rem;
      z-index: 1000; opacity: 0; visibility: hidden;
      transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
    }
    .toast-overlay.show { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
    .toast-icon-wrapper {
      width: 42px; height: 42px; border-radius: 5px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .toast-icon-wrapper svg { width: 22px; height: 22px; }
    .toast-overlay.success .toast-icon-wrapper { background: rgba(40, 199, 111, 0.15); color: var(--das-success); border: 1px solid rgba(40, 199, 111, 0.3); }
    .toast-overlay.warning .toast-icon-wrapper { background: rgba(255, 159, 67, 0.15); color: var(--das-warning); border: 1px solid rgba(255, 159, 67, 0.3); }
    .toast-overlay.danger  .toast-icon-wrapper { background: rgba(234, 84, 85, 0.15); color: var(--das-danger); border: 1px solid rgba(234, 84, 85, 0.3); }
    
    .toast-body { flex: 1; min-width: 0; }
    .toast-title { font-size: 0.9rem; font-weight: 800; color: white; }
    .toast-msg   { font-size: 0.75rem; color: #cbd5e1; margin-top: 2px; }
  </style>
</head>
<body>

  <!-- Navbar -->
  <header class="navbar">
    <div class="nav-brand">
      <div class="brand-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"></path><path d="M9 12h6"></path><path d="M12 9v6"></path></svg>
      </div>
      <div>
        <h1>Scan Presensi Ekskul</h1>
        <p>Mode Pembina (Tanpa Login Full)</p>
      </div>
    </div>

    <form action="{{ route('public.ekskul.scan.logout') }}" method="POST">
      @csrf
      <button type="submit" class="btn-logout">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"></path><path d="M9 12h12l-3 -3"></path><path d="M18 15l3 -3"></path></svg>
        <span>Keluar</span>
      </button>
    </form>
  </header>

  <!-- Main Container -->
  <main class="main-container">

    <!-- Scanner & Selector Left Panel -->
    <section class="scanner-panel">

      <!-- Form Controls -->
      <div class="controls-card">
        <div class="form-row">
          <div class="form-group">
            <label for="ekskul_id">Pilih Kegiatan Ekskul <span style="color:var(--das-danger)">*</span></label>
            <select id="ekskul_id" class="select-input">
              <option value="">-- Pilih Ekskul Hari Ini --</option>
              @foreach($ekskuls as $e)
                <option value="{{ $e->id }}">{{ $e->nama }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label for="pembina_id">Pembina Penanggung Jawab</label>
            <select id="pembina_id" class="select-input">
              <option value="">-- Opsional (Pilih Guru) --</option>
              @foreach($gurus as $g)
                <option value="{{ $g->id }}">{{ $g->nama_lengkap }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <!-- Camera Viewport Wrapper -->
      <div class="camera-wrapper">
        <div id="reader"></div>
      </div>

      <!-- Manual NIS Bar -->
      <div class="manual-bar">
        <input type="text" id="manual_nis" class="text-input" placeholder="Masukkan NIS / NISN / Kode Siswa manual..." autocomplete="off">
        <button type="button" id="btn_manual_submit" class="btn-manual">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5l10 -10"></path></svg>
          <span>Absen</span>
        </button>
      </div>

    </section>

    <!-- Log Right Panel -->
    <aside class="log-panel">
      <div class="log-header">
        <h3>Siswa Berhasil Di-Scan Hari Ini</h3>
        <span class="badge-count" id="scan_count">0 Siswa</span>
      </div>

      <div class="log-list" id="log_list">
        <div class="empty-state" id="log_empty">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path><path d="M7 17l0 .01"></path><path d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path><path d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path><path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path></svg>
          <p>Belum ada siswa yang di-scan pada sesi ini.</p>
        </div>
      </div>
    </aside>

  </main>

  <!-- Toast Notification Overlay -->
  <div class="toast-overlay" id="toast_overlay">
    <div class="toast-icon-wrapper" id="toast_icon_wrapper">
      <span id="toast_icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5l10 -10"></path></svg>
      </span>
    </div>
    <div class="toast-body">
      <div class="toast-title" id="toast_title">Absensi Berhasil</div>
      <div class="toast-msg" id="toast_msg">Ahmad Fathan tercatat hadir.</div>
    </div>
  </div>

  <script>
    const PROCESS_URL = "{{ route('public.ekskul.scan.process') }}";
    const CSRF = "{{ csrf_token() }}";

    let html5QrCode = null;
    let isProcessing = false;
    let scannedCount = 0;

    const svgCheck = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5l10 -10"></path></svg>`;
    const svgWarning = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"></path><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"></path><path d="M12 16h.01"></path></svg>`;
    const svgError = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6l-12 12"></path><path d="M6 6l12 12"></path></svg>`;

    // Web Audio Synthesizer Beep Sound
    function playBeep(type = 'success') {
      try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();

        osc.connect(gain);
        gain.connect(audioCtx.destination);

        if (type === 'success') {
          osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
          osc.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.12);
          gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
          osc.start();
          osc.stop(audioCtx.currentTime + 0.15);
        } else if (type === 'warning') {
          osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
          gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
          osc.start();
          osc.stop(audioCtx.currentTime + 0.25);
        } else {
          osc.frequency.setValueAtTime(220, audioCtx.currentTime); // A3
          osc.frequency.exponentialRampToValueAtTime(150, audioCtx.currentTime + 0.3);
          gain.gain.setValueAtTime(0.4, audioCtx.currentTime);
          osc.start();
          osc.stop(audioCtx.currentTime + 0.3);
        }
      } catch (e) {}
    }

    // Show Toast Overlay
    let toastTimeout = null;
    function showToast(type, title, msg) {
      const toast = document.getElementById('toast_overlay');
      const icon = document.getElementById('toast_icon');
      const titleEl = document.getElementById('toast_title');
      const msgEl = document.getElementById('toast_msg');

      toast.className = `toast-overlay ${type} show`;
      titleEl.innerText = title;
      msgEl.innerText = msg;

      if (type === 'success') icon.innerHTML = svgCheck;
      else if (type === 'warning') icon.innerHTML = svgWarning;
      else icon.innerHTML = svgError;

      if (toastTimeout) clearTimeout(toastTimeout);
      toastTimeout = setTimeout(() => {
        toast.classList.remove('show');
      }, 3500);
    }

    // Add Item to Log List
    function addLog(type, name, subText, time) {
      const logEmpty = document.getElementById('log_empty');
      if (logEmpty) logEmpty.style.display = 'none';

      const logList = document.getElementById('log_list');
      const item = document.createElement('div');
      item.className = 'log-item';

      const avatarClass = type === 'success' ? '' : (type === 'warning' ? 'warning' : 'danger');
      const initial = (name || '?').charAt(0).toUpperCase();

      item.innerHTML = `
        <div class="log-avatar ${avatarClass}">${initial}</div>
        <div class="log-info">
          <div class="log-name">${name}</div>
          <div class="log-sub">${subText}</div>
        </div>
        <div class="log-time">${time}</div>
      `;

      logList.insertBefore(item, logList.firstChild);

      if (type === 'success') {
        scannedCount++;
        document.getElementById('scan_count').innerText = `${scannedCount} Siswa`;
      }
    }

    // Process Scan Request
    async function processCode(code) {
      const ekskulId = document.getElementById('ekskul_id').value;
      const pembinaId = document.getElementById('pembina_id').value;

      if (!ekskulId) {
        playBeep('error');
        showToast('danger', 'Ekskul Belum Dipilih', 'Silakan pilih kegiatan ekskul terlebih dahulu di panel atas.');
        isProcessing = false;
        return;
      }

      if (!code) return;

      isProcessing = true;

      try {
        const response = await fetch(PROCESS_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            ekskul_id: ekskulId,
            pembina_id: pembinaId,
            qr_code: code
          })
        });

        const data = await response.json();

        if (response.ok && data.success) {
          playBeep('success');
          showToast('success', data.data.siswa.nama, `${data.data.siswa.kelas} · Hadir di ${data.data.ekskul}`);
          addLog('success', data.data.siswa.nama, `${data.data.siswa.kelas} (${data.data.siswa.nis})`, data.data.jam);
        } else if (response.status === 409) {
          playBeep('warning');
          showToast('warning', data.data?.siswa?.nama || 'Sudah Hadir', data.message);
          addLog('warning', data.data?.siswa?.nama || 'Sudah Hadir', `${data.data?.siswa?.kelas || '-'} · ${data.message}`, new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}));
        } else {
          playBeep('error');
          showToast('danger', 'Scan Gagal', data.message || 'Kode QR tidak dikenali.');
          addLog('danger', 'Gagal', data.message, new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}));
        }
      } catch (err) {
        playBeep('error');
        showToast('danger', 'Koneksi Error', 'Terjadi kesalahan jaringan.');
      } finally {
        setTimeout(() => {
          isProcessing = false;
        }, 1500); // Cool down 1.5 detik per scan
      }
    }

    // Initialize Camera Scanner
    window.addEventListener('DOMContentLoaded', () => {
      html5QrCode = new Html5Qrcode("reader");
      
      const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
      };

      html5QrCode.start(
        { facingMode: "environment" },
        config,
        (decodedText) => {
          if (!isProcessing) {
            processCode(decodedText);
          }
        },
        () => {}
      ).catch(err => {
        console.error("Camera access failed:", err);
      });

      // Manual Submit Handler
      const manualInput = document.getElementById('manual_nis');
      const manualBtn = document.getElementById('btn_manual_submit');

      function handleManualSubmit() {
        const val = manualInput.value.trim();
        if (val && !isProcessing) {
          processCode(val);
          manualInput.value = '';
        }
      }

      manualBtn.addEventListener('click', handleManualSubmit);
      manualInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          handleManualSubmit();
        }
      });
    });
  </script>
</body>
</html>
