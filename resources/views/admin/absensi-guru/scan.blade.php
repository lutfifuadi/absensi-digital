@extends('layouts/layoutMaster')

@section('title', 'Scan QR Absensi Guru')

@section('page-style')
  <style>
    .scan-alert {
      transition: all .2s ease;
    }

    .scan-card {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    .scan-action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      transition: all 0.2s ease;
      border: none;
      background: rgba(255, 255, 255, 0.05);
      color: inherit;
    }

    .scan-action-btn:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.1);
    }
  </style>
@endsection

@section('content')
  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-qrcode text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.absensi-guru.index') }}" class="text-white text-decoration-none">Absensi</a> / Scan
          </div>
          <h4 class="das-hero__title text-gradient-gold">Scan QR Absensi Guru</h4>
          <p class="das-hero__subtitle">Halaman khusus untuk presensi Guru & Tenaga Pendidik menggunakan QR Code.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.absensi-guru.index') }}" class="btn das-btn --secondary">
          <i class="ti tabler-arrow-left me-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-xl-7">
      <div class="das-panel h-100">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
          style="border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
            <i class="ti tabler-camera text-info"></i> Kamera Scanner
          </h6>
          <span id="scan-status" class="das-chip --primary">Standby</span>
        </div>
        <div class="das-panel__body">
          <div id="reader-wrapper" class="position-relative bg-dark rounded overflow-hidden shadow-inner" style="min-height: 350px; border: 1px solid rgba(255,255,255,0.05);">
            <div id="start-cam-overlay"
              class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark"
              style="z-index: 10; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;">
              <div class="d-flex gap-2">
                <button id="start-cam-btn" class="btn das-btn --primary btn-lg px-5 shadow">
                  <i class="ti ti-camera me-1"></i> Aktifkan Kamera
                </button>
                <button id="switch-cam-btn" class="btn das-btn --secondary btn-lg d-none">
                  <i class="ti ti-camera-rotate"></i>
                </button>
              </div>
              <p class="text-white-50 mt-3 smaller">Gunakan kamera depan atau belakang</p>
            </div>
            <div id="reader" style="width: 100%;"></div>
            <div class="scan-overlay-frame"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-5">
      <div class="das-panel h-100">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
          style="border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
            <i class="ti tabler-list text-info"></i> Aktivitas Terkini
          </h6>
        </div>
        <div class="das-panel__body p-3">
          <div id="recent-logs" class="d-flex flex-column gap-3">
            <div class="text-center py-5 text-muted empty-state bg-light bg-opacity-10 rounded">
              <i class="ti tabler-scan fs-1 d-block mb-3 opacity-25"></i>
              <p class="small opacity-50 mb-0">Belum ada aktivitas scan.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="scan-feedback" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;"></div>
@endsection

@section('page-script')
  <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
  <script>
    /**
     * AUDIO NOTIFICATION SYSTEM
     */
    const audioCtx = new(window.AudioContext || window.webkitAudioContext)();

    function playBeep(type) {
      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      osc.connect(gain);
      gain.connect(audioCtx.destination);

      if (type === 'success') {
        osc.type = 'sine';
        osc.frequency.setValueAtTime(800, audioCtx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.3);

        // Second bell hit
        setTimeout(() => {
          const osc2 = audioCtx.createOscillator();
          const gain2 = audioCtx.createGain();
          osc2.connect(gain2);
          gain2.connect(audioCtx.destination);
          osc2.frequency.setValueAtTime(1000, audioCtx.currentTime);
          gain2.gain.setValueAtTime(0.1, audioCtx.currentTime);
          gain2.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);
          osc2.start();
          osc2.stop(audioCtx.currentTime + 0.4);
        }, 100);
      } else if (type === 'warning') {
        osc.type = 'square';
        osc.frequency.setValueAtTime(400, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.05, audioCtx.currentTime);
        gain.gain.linearRampToValueAtTime(0, audioCtx.currentTime + 0.2);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.2);
      } else { // error
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(200, audioCtx.currentTime);
        osc.frequency.linearRampToValueAtTime(100, audioCtx.currentTime + 0.3);
        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
        gain.gain.linearRampToValueAtTime(0, audioCtx.currentTime + 0.4);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.4);
      }
    }

    /**
     * SCANNER LOGIC
     */
    const statusEl = document.getElementById('scan-status');
    const logsEl = document.getElementById('recent-logs');
    const startBtn = document.getElementById('start-cam-btn');
    const overlay = document.getElementById('start-cam-overlay');
    const readerEl = document.getElementById('reader');

    const video = document.createElement('video');
    video.setAttribute('playsinline', 'true');
    video.style.cssText = 'width:100%; height:350px; object-fit:cover; display:block;';
    readerEl.appendChild(video);

    const canvas = document.createElement('canvas');
    canvas.style.display = 'none';
    const ctx = canvas.getContext('2d', {
      willReadFrequently: true
    });

    let stream = null;
    let animFrame = null;
    let isProcessing = false;
    let lastCode = '';
    let lastTime = 0;
    let currentFacingMode = 'environment';

    async function startCamera(facingMode = 'environment') {
      if (stream) {
        stream.getTracks().forEach(t => t.stop());
      }
      try {
        overlay.classList.add('d-none');
        stream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: facingMode
          }
        });
        video.srcObject = stream;
        await video.play();
        statusEl.textContent = 'Scanning...';
        statusEl.className = 'das-chip --primary';
        document.getElementById('switch-cam-btn').classList.remove('d-none');
        
        if (!animFrame) animFrame = requestAnimationFrame(scanLoop);

        // Warm up audio
        audioCtx.resume();
        return true;
      } catch (err) {
        alert('Gagal mengakses kamera: ' + err.message);
        overlay.classList.remove('d-none');
        return false;
      }
    }

    function addLog(name, message, type) {
      const time = new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
      });
      const chipColor = type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger');
      const icon = type === 'success' ? 'check' : (type === 'warning' ? 'exclamation-circle' : 'x');

      const html = `
            <div class="p-3 rounded border border-white border-opacity-10 d-flex align-items-center gap-3 animate__animated animate__fadeInDown" 
                 style="background:rgba(255,255,255,0.03); transform-origin: top;">
                <div class="avatar avatar-md border border-${chipColor} border-opacity-25 rounded p-1">
                    <span class="avatar-initial rounded bg-label-${chipColor}"><i class="ti tabler-${icon}"></i></span>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <h6 class="mb-0 text-white text-truncate">${name}</h6>
                    <small class="text-white-50 d-block text-truncate">${message}</small>
                </div>
                <div class="text-end ps-2">
                    <div class="das-chip --${chipColor} small" style="font-size: 0.6rem;">${time}</div>
                </div>
            </div>
        `;

      const emptyState = logsEl.querySelector('.empty-state');
      if (emptyState) emptyState.remove();

      logsEl.insertAdjacentHTML('afterbegin', html);

      // Remove older logs if too many
      if (logsEl.children.length > 5) {
        logsEl.lastElementChild.remove();
      }
    }

    async function processQR(code) {
      if (isProcessing) return;

      // Debounce same card 5 seconds
      const now = Date.now();
      if (code === lastCode && (now - lastTime) < 5000) return;

      isProcessing = true;
      lastCode = code;
      lastTime = now;

      statusEl.textContent = 'Memproses...';
      statusEl.className = 'badge bg-label-info';

      try {
        const response = await fetch("{{ route('admin.absensi-guru.scan.ajax') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            qr_code: code
          })
        });

        const result = await response.json();

        if (response.ok) {
          playBeep('success');
          addLog(result.data.nama, result.message, 'success');
          statusEl.textContent = 'Berhasil!';
          statusEl.className = 'das-chip --success';
        } else {
          if (response.status === 422) {
            playBeep('warning');
            addLog('Warning', result.message, 'warning');
            statusEl.textContent = 'Sudah Absen';
            statusEl.className = 'das-chip --warning';
          } else {
            playBeep('error');
            addLog('Error', result.message, 'error');
            statusEl.textContent = 'Gagal';
            statusEl.className = 'das-chip --danger';
          }
        }
      } catch (err) {
        playBeep('error');
        addLog('Sistem', 'Terjadi kesalahan koneksi.', 'error');
      } finally {
        setTimeout(() => {
          isProcessing = false;
          statusEl.textContent = 'Scanning...';
          statusEl.className = 'das-chip --primary';
        }, 1000);
      }
    }

    function scanLoop() {
      if (!stream) return;
      if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);
        const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imgData.data, imgData.width, imgData.height, {
          inversionAttempts: 'attemptBoth'
        });

        if (code && code.data && !isProcessing) {
          processQR(code.data);
        }
      }
      animFrame = requestAnimationFrame(scanLoop);
    }

    startBtn.addEventListener('click', async () => {
      await startCamera(currentFacingMode);
    });

    document.getElementById('switch-cam-btn').addEventListener('click', async function() {
      currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
      const btn = this;
      const icon = btn.querySelector('i');
      icon.className = 'ti tabler-refresh spin';
      btn.disabled = true;

      await startCamera(currentFacingMode);

      icon.className = 'ti ti-camera-rotate';
      btn.disabled = false;
    });
  </script>
@endsection
