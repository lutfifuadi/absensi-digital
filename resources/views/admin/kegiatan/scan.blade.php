@extends('layouts/layoutMaster')

@section('title', 'Scanner Kegiatan Khusus')

@section('page-style')
<style>
    .hero-master {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        border-radius: 4px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 12px !important;
        background: rgba(115, 103, 240, 0.3);
        border: 1px solid rgba(115, 103, 240, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .glass-dark {
        background: rgba(15, 23, 42, 0.8) !important;
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 12px;
    }
    #reader {
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
        border-radius: 16px;
        overflow: hidden;
        border: 3px solid rgba(115, 103, 240, 0.4);
        box-shadow: 0 0 30px rgba(115, 103, 240, 0.15);
    }
    #reader video {
        border-radius: 16px;
    }
    .log-container {
        max-height: 400px;
        overflow-y: auto;
    }
    .log-item {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        margin-bottom: 8px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .scanner-active {
        border-color: #7367f0 !important;
        box-shadow: 0 0 25px rgba(115, 103, 240, 0.3) !important;
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
            <i class="ti tabler-camera-bolt text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            System / Absensi Kegiatan
          </div>
          <h4 class="das-hero__title text-gradient-gold">Scanner Kegiatan</h4>
          <p class="das-hero__subtitle">Pindai QR Code siswa untuk mencatat kehadiran kegiatan secara real-time.</p>
        </div>
      </div>

      <div class="das-hero__actions" style="min-width: 320px;">
        <label class="form-label text-white-50 small fw-bold mb-1 opacity-75">PILIH KEGIATAN AKTIF</label>
        <select id="kegiatan_id" class="form-select border-0 shadow-sm" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2) !important; color: white; font-weight: 600; border-radius: 8px;">
          <option value="" style="background: #1e1b4b;">-- Pilih Kegiatan --</option>
          @foreach($kegiatans as $k)
            <option value="{{ $k->id }}" style="background: #1e1b4b;">{{ $k->nama_kegiatan }} ({{ $k->waktu_mulai }}-{{ $k->waktu_selesai }})</option>
          @endforeach
        </select>
        <div id="kegiatan-error" class="badge bg-danger mt-2 d-none w-100 py-2">Harap pilih kegiatan terlebih dahulu!</div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-7">
      <div class="das-panel h-100">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
          style="border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
            <i class="ti tabler-camera text-info"></i> Kamera Scanner
          </h6>
          <div class="d-flex align-items-center gap-2">
            <button id="switch-cam-btn" class="das-chip --secondary d-none border-0 cursor-pointer" title="Ganti Kamera">
              <i class="ti tabler-camera-rotate"></i>
            </button>
            <span class="das-chip --primary">Live Mode</span>
          </div>
        </div>
        <div class="das-panel__body p-4 text-center">
          <div id="reader" class="mb-3 d-none rounded border border-info border-opacity-25 overflow-hidden shadow"></div>
          
          <div id="scanner-placeholder" class="py-5">
            <div class="avatar avatar-xl bg-label-info mx-auto mb-4" style="width: 80px; height: 80px; border-radius: 16px !important;">
              <span class="avatar-initial rounded-3 bg-opacity-10"><i class="ti tabler-qrcode fs-1"></i></span>
            </div>
            <h5 class="text-white fw-bold">Pilih Kegiatan</h5>
            <p class="text-white-50 small">Sistem membutuhkan target kegiatan sebelum mengaktifkan pemindaian.</p>
          </div>

          <div class="d-flex justify-content-center gap-2 mt-2">
            <div class="das-chip --warning small"><i class="ti tabler-bulb me-1"></i> PASTIKAN CAHAYA CUKUP</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="das-panel h-100">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
          style="border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
            <i class="ti tabler-history text-info"></i> Log Scan Rekap
          </h6>
          <span class="das-chip --info" id="log-count">0</span>
        </div>
        <div class="das-panel__body p-3 log-container" id="attendance-log">
          <div class="text-center py-5 text-white-50 opacity-25 empty-msg bg-light bg-opacity-10 rounded">
            <div class="mb-2"><i class="ti tabler-database-off fs-2"></i></div>
            <div class="small">Belum ada aktivitas scan pada sesi ini</div>
          </div>
        </div>
      </div>
    </div>
  </div>

{{-- Audio for feedback --}}
<audio id="success-sound" src="{{ asset('assets/audio/success.mp3') }}" preload="auto"></audio>
<audio id="error-sound" src="{{ asset('assets/audio/error.mp3') }}" preload="auto"></audio>

@endsection

@section('page-vendor')
<script src="https://unpkg.com/html5-qrcode"></script>
@endsection

@section('page-script')
<script>
    let html5QrCode;
    const readerDiv = document.getElementById('reader');
    const placeholder = document.getElementById('scanner-placeholder');
    const errorMsg = document.getElementById('kegiatan-error');
    const switchBtn = document.getElementById('switch-cam-btn');
    
    let isProcessing = false;
    let currentFacingMode = "environment";

    kegiatanSelect.addEventListener('change', function() {
        if (this.value) {
            startScanner();
            errorMsg.classList.add('d-none');
        } else {
            stopScanner();
        }
    });

    function startScanner() {
        readerDiv.classList.remove('d-none');
        placeholder.classList.add('d-none');
        switchBtn.classList.remove('d-none');
        
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        const config = { fps: 15, qrbox: { width: 280, height: 280 } };
        
        html5QrCode.start({ facingMode: currentFacingMode }, config, onScanSuccess)
            .catch(err => {
                console.error("Gagal start scanner:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Gagal',
                    text: 'Kamera tidak dapat diakses atau diblokir.',
                    background: '#1a1b25',
                    color: '#fff'
                });
            });
        
        readerDiv.classList.add('scanner-active');
    }

    switchBtn.addEventListener('click', function() {
        currentFacingMode = currentFacingMode === "environment" ? "user" : "environment";
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                startScanner();
            });
        }
    });

    function stopScanner() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                readerDiv.classList.add('d-none');
                placeholder.classList.remove('d-none');
                readerDiv.classList.remove('scanner-active');
            });
        }
    }

    function onScanSuccess(decodedText) {
        if (isProcessing) return;
        
        const activityId = kegiatanSelect.value;
        if (!activityId) {
            errorMsg.classList.remove('d-none');
            return;
        }

        isProcessing = true;
        
        // Visual feedback
        readerDiv.style.borderColor = '#ffab00';

        fetch("{{ route('admin.absensi-kegiatan.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                qr_code: decodedText,
                kegiatan_id: activityId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                try { document.getElementById('success-sound').play(); } catch(e){}
                addToLog(data.siswa_nama, data.waktu, 'success');
                
                Swal.fire({
                    icon: 'success',
                    title: 'BERHASIL',
                    html: `<span class="text-success fw-bold">${data.siswa_nama}</span><br><small>${data.message}</small>`,
                    timer: 1800,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    background: '#1a1b25'
                });
            } else {
                try { document.getElementById('error-sound').play(); } catch(e){}
                Swal.fire({
                    icon: 'warning',
                    title: 'PERINGATAN',
                    text: data.message,
                    timer: 2500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    background: '#450a0a'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({ icon: 'error', title: 'Network Error', text: 'Koneksi ke server terputus.', background: '#1a1b25'});
        })
        .finally(() => {
            setTimeout(() => {
                isProcessing = false;
                readerDiv.style.borderColor = 'rgba(115, 103, 240, 0.4)';
            }, 1000);
        });
    }

    function addToLog(nama, waktu, type) {
        const log = document.getElementById('attendance-log');
        const emptyMsg = log.querySelector('.empty-msg');
        if (emptyMsg) emptyMsg.remove();

        const countLabel = document.getElementById('log-count');
        countLabel.innerText = parseInt(countLabel.innerText) + 1;

        const div = document.createElement('div');
        div.className = 'p-3 mb-2 rounded border border-white border-opacity-10 d-flex justify-content-between align-items-center animate__animated animate__fadeInLeft';
        div.style.background = 'rgba(255,255,255,0.03)';
        div.innerHTML = `
            <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-sm border border-success border-opacity-25 rounded p-1">
                    <span class="avatar-initial rounded bg-label-success"><i class="ti tabler-user-check"></i></span>
                </div>
                <div>
                    <div class="fw-bold text-white mb-0" style="font-size: 0.85rem;">${nama}</div>
                    <div class="text-white-50" style="font-size: 0.65rem;">Pukul ${waktu}</div>
                </div>
            </div>
            <div class="das-chip --success small" style="font-size: 0.6rem;">HADIR</div>
        `;
        log.prepend(div);
    }
</script>
@endsection
