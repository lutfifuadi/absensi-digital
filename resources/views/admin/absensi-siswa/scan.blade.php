@extends('layouts/layoutMaster')

@section('title', 'Scan QR Absensi Siswa')

@section('page-style')
  <style>
    .scan-alert {
      transition: all .2s ease;
    }

    .scan-card {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    .scan-button-group .btn {
      min-width: 150px;
    }
  </style>
@endsection

@section('content')
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 4px;">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-7">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                  style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
                  <i class="ti tabler-qrcode text-info fs-3"></i>
                </div>
                <div>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                      <li class="breadcrumb-item">
                        <a href="{{ route('admin.absensi-siswa.index') }}"
                          class="text-white text-decoration-none">Absensi</a>
                      </li>
                      <li class="breadcrumb-item active text-white">Scan QR</li>
                    </ol>
                  </nav>
                  <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Scan QR Absensi Siswa</h4>
                  <p class="mb-0 text-white opacity-60 small">Arahkan kamera ke QR siswa dan catat kehadiran secara
                    otomatis.</p>
                </div>
              </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
              <div class="d-flex gap-2 justify-content-md-end flex-wrap scan-button-group">
                <a href="{{ route('admin.absensi-siswa.index') }}" class="btn btn-label-secondary fw-semibold">
                  <i class="ti tabler-arrow-left me-1"></i> Kembali
                </a>
                <button id="start-cam-btn" type="button" class="btn btn-primary fw-semibold">
                  <i class="ti tabler-camera me-1"></i> Aktifkan Kamera
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5"></i>
      <span>{{ session('error') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card border-0 shadow-sm scan-card">
    <div class="card-body">
      <div id="scan-alert" class="alert scan-alert d-none" role="alert"></div>
      <div id="reader" class="mb-3" style="width: 100%; min-height: 320px; max-width: 600px; margin: auto;"></div>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="card border-0 bg-label-secondary text-white p-3">
            <div class="small opacity-75">Status</div>
            <div id="scan-status" class="fs-5 fw-semibold">Siap memindai QR code...</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 bg-label-secondary text-white p-3">
            <div class="small opacity-75">Tanggal</div>
            <div class="fs-5 fw-semibold">{{ now()->format('d M Y') }}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 bg-label-secondary text-white p-3">
            <div class="small opacity-75">Mode</div>
            <div class="fs-5 fw-semibold">Kamera</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <form id="scanForm" action="{{ route('admin.absensi-siswa.scan.store') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="qr_code" id="qr_code" />
    <input type="hidden" name="tanggal" value="{{ now()->toDateString() }}" />
    <input type="hidden" name="status" value="hadir" />
  </form>
@endsection

@section('page-script')
  <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
  <script>
    const statusEl = document.getElementById('scan-status');
    const alertEl = document.getElementById('scan-alert');
    const qrField = document.getElementById('qr_code');
    const scanForm = document.getElementById('scanForm');
    const readerEl = document.getElementById('reader');

    // ── DOM: video + canvas ──────────────────────────────────────
    readerEl.style.display = 'none';

    const video = document.createElement('video');
    video.setAttribute('playsinline', 'true');
    video.setAttribute('muted', 'true');
    video.setAttribute('autoplay', 'true');
    video.style.cssText = 'width:100%;max-height:320px;object-fit:cover;display:block;border-radius:8px;';
    readerEl.appendChild(video);

    const canvas = document.createElement('canvas');
    canvas.style.display = 'none';
    readerEl.appendChild(canvas);
    const ctx = canvas.getContext('2d', {
      willReadFrequently: true
    });

    // ── State ─────────────────────────────────────────────────────
    let stream = null;
    let animFrame = null;
    let submitted = false;
    const DEBOUNCE = 3000;
    let lastQR = '',
      lastQRTime = 0;

    // ── Helpers ───────────────────────────────────────────────────
    function showMessage(message, type = 'info') {
      alertEl.className = `alert alert-${type}`;
      alertEl.textContent = message;
      alertEl.classList.remove('d-none');
    }

    function showError(msg) {
      readerEl.style.display = 'none';
      const btn = document.getElementById('start-cam-btn');
      btn.disabled = false;
      btn.innerHTML = '<i class="ti tabler-camera me-1"></i> Coba Lagi';
      showMessage(msg, 'danger');
      statusEl.textContent = 'Kamera tidak tersedia';
      if (stream) {
        stream.getTracks().forEach(t => t.stop());
        stream = null;
      }
      if (animFrame) {
        cancelAnimationFrame(animFrame);
        animFrame = null;
      }
    }

    // ── Scan loop ─────────────────────────────────────────────────
    function tick() {
      if (!stream) return;
      if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);
        const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(img.data, img.width, img.height, {
          inversionAttempts: 'attemptBoth'
        });
        if (code && !submitted) {
          const now = Date.now();
          if (code.data !== lastQR || now - lastQRTime > DEBOUNCE) {
            lastQR = code.data;
            lastQRTime = now;
            submitted = true;
            statusEl.textContent = 'QR terdeteksi, memproses absensi...';
            cancelAnimationFrame(animFrame);
            stream.getTracks().forEach(t => t.stop());
            qrField.value = code.data;
            scanForm.submit();
          }
        }
      }
      animFrame = requestAnimationFrame(tick);
    }

    // ── Aktifkan kamera ───────────────────────────────────────────
    document.getElementById('start-cam-btn').addEventListener('click', async function() {
      this.disabled = true;
      this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memulai...';
      alertEl.classList.add('d-none');

      try {
        try {
          stream = await navigator.mediaDevices.getUserMedia({
            video: {
              facingMode: {
                ideal: 'environment'
              },
              width: {
                ideal: 1280
              },
              height: {
                ideal: 720
              }
            }
          });
        } catch (_) {
          stream = await navigator.mediaDevices.getUserMedia({
            video: true
          });
        }

        video.srcObject = stream;
        await video.play();

        const btn = document.getElementById('start-cam-btn');
        btn.innerHTML = '<i class="ti tabler-camera me-1"></i> Kamera Aktif';
        readerEl.style.display = 'block';
        statusEl.textContent = 'Mendeteksi QR code...';
        animFrame = requestAnimationFrame(tick);

      } catch (err) {
        let msg = 'Tidak dapat memulai kamera. ';
        if (['NotAllowedError', 'PermissionDeniedError'].includes(err.name)) {
          msg += 'Izin kamera ditolak. Buka pengaturan browser dan izinkan akses kamera.';
        } else if (['NotFoundError', 'DevicesNotFoundError'].includes(err.name)) {
          msg += 'Kamera tidak ditemukan di perangkat ini.';
        } else if (err.name === 'NotReadableError') {
          msg += 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi lain dan coba lagi.';
        } else {
          msg += err.message;
        }
        showError(msg);
      }
    });

    // ── Pause saat tab tersembunyi ────────────────────────────────
    document.addEventListener('visibilitychange', function() {
      if (document.hidden) {
        if (animFrame) {
          cancelAnimationFrame(animFrame);
          animFrame = null;
        }
      } else if (stream && !submitted) {
        animFrame = requestAnimationFrame(tick);
      }
    });
  </script>
@endsection
