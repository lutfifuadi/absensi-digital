@extends('layouts/layoutMaster')

@section('title', 'Absensi Ekskul')

@section('page-style')
<style>
  :root {
    --das-primary: #7367f0;
    --das-primary-soft: rgba(115, 103, 240, 0.12);
    --das-success: #28c76f;
    --das-success-soft: rgba(40, 199, 111, 0.12);
    --das-info: #00cfe8;
    --das-info-soft: rgba(0, 207, 232, 0.12);
    --das-warning: #ff9f43;
    --das-warning-soft: rgba(255, 159, 67, 0.12);
    --das-danger: #ea5455;
    --das-surface: rgba(15, 23, 42, 0.4);
    --das-surface-hover: rgba(30, 41, 59, 0.6);
    --das-border: rgba(255, 255, 255, 0.06);
    --das-radius: 5px;
  }

  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #999 !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }
  .das-btn--success { background: transparent; border-color: var(--das-success); color: var(--das-success) !important; }
  .das-btn--success:hover { background: rgba(40,199,111,.08); }

  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }

  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #e0e0e0 !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; outline: none !important; box-shadow: none !important; color: white !important; }
  .das-form-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #888; margin-bottom: .5rem; display: block; }

  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }

  .qr-preview { width: 200px; height: 200px; margin: 0 auto; border-radius: 12px; overflow: hidden; background: white; }
  .qr-preview img { width: 100%; height: 100%; object-fit: contain; }

  .shortcut-card { background: rgba(255,255,255,.03); border: 1px solid var(--das-border); border-radius: var(--das-radius); padding: 1rem; text-align: center; transition: all .2s; cursor: pointer; text-decoration: none; display: block; }
  .shortcut-card:hover { background: rgba(255,255,255,.06); border-color: rgba(115,103,240,.3); transform: translateY(-2px); }
  .shortcut-card__icon { font-size: 2rem; margin-bottom: .5rem; }

  .scan-crosshair-admin {
    position: absolute; inset: 0; display: flex; align-items: center;
    justify-content: center; pointer-events: none; z-index: 5;
  }
  @keyframes scanLineAdmin {
    0% { top: 5%; }
    50% { top: 95%; }
    100% { top: 5%; }
  }

  /* ── QR Video Container ── */
  .qr-video-wrapper { position: relative; border-radius: 8px; overflow: hidden; background: #000; min-height: 180px; }
  .qr-video { width: 100%; height: auto; display: block; min-height: 180px; object-fit: cover; }
  .qr-canvas { display: none; }

  /* ── QR Crosshair ── */
  .qr-crosshair__box { width: 160px; height: 160px; position: relative; margin: auto; }

  .qr-corner { position: absolute; width: 24px; height: 24px; border-color: var(--das-success); border-style: solid; }
  .qr-corner--tl { top: 0; left: 0; border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
  .qr-corner--tr { top: 0; right: 0; border-width: 3px 3px 0 0; border-radius: 0 4px 0 0; }
  .qr-corner--bl { bottom: 0; left: 0; border-width: 0 0 3px 3px; border-radius: 0 0 0 4px; }
  .qr-corner--br { bottom: 0; right: 0; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }

  .qr-scan-line {
    position: absolute; left: 3px; right: 3px; height: 2px;
    background: linear-gradient(90deg, transparent, var(--das-success), transparent);
    box-shadow: 0 0 10px var(--das-success);
    animation: scanLineAdmin 2s ease-in-out infinite;
  }

  /* ── QR Camera Overlays ── */
  .qr-overlay { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; }
  .qr-overlay--idle { background: rgba(0,0,0,.3); }
  .qr-overlay--error { background: rgba(0,0,0,.7); padding: 1rem; gap: 8px; }
  .qr-overlay__icon { font-size: 2rem; opacity: .4; }
  .qr-overlay__text { font-size: .7rem; max-width: 200px; text-align: center; }
  .qr-overlay__error-icon { font-size: 1.5rem; }
  .qr-overlay__error-text { font-size: .7rem; }

  /* ── NIS Hint ── */
  .qr-nis-hint { font-size: .65rem; }

  /* ── Lookup Result Panel ── */
  .qr-result { background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.2); }
  .qr-result__avatar {
    width: 42px; height: 42px; border-radius: 10px !important;
    background: rgba(16, 185, 129, 0.15);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .qr-result__name { font-size: .85rem; }
  .qr-result__meta { font-size: .7rem; }
  .qr-result__badge { font-size: .6rem; }
  .qr-result__alert {
    font-size: .7rem; border-radius: 4px;
    background: rgba(234,84,85,0.1); border: 1px solid rgba(234,84,85,0.2); color: #ea5455;
  }
  .qr-result__warning { font-size: .7rem; color: var(--das-warning); text-align: center; }

  /* ── Scan Log ── */
  .qr-log { border-top: 1px solid var(--das-border); padding-top: 0.75rem; }
  .qr-log__header { font-size: .65rem; text-transform: uppercase; letter-spacing: .5px; font-weight: 700; }
  .qr-log__container { max-height: 120px; overflow-y: auto; }
  .qr-log__item { border-radius: 4px; background: rgba(255,255,255,0.03); }
  .qr-log__icon { font-size: .8rem; }
  .qr-log__message { font-size: .7rem; }
  .qr-log__time { font-size: .6rem; }
</style>
@endsection

@section('content')

  {{-- ═══════════ HERO HEADER ═══════════ --}}
  <div class="row mb-4 slide-in-up">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #0f2b1a 0%, #1a4a2e 40%, #28a745 100%); border-radius: 4px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                style="width:52px;height:52px;border-radius:12px !important;background:rgba(40,199,111,0.2);border:1px solid rgba(40,199,111,0.4);">
                <i class="ti tabler-clipboard-check text-success fs-3"></i>
              </div>
              <div>
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.ekskul.index') }}" class="text-white text-decoration-none">Ekstrakurikuler</a></li>
                    <li class="breadcrumb-item active text-white">Absensi</li>
                  </ol>
                </nav>
                <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                  Absensi: {{ $ekskul->nama }}
                </h4>
              </div>
            </div>
            <div>
              <a href="{{ route('admin.ekskul.anggota.index', $ekskul->id) }}" class="das-btn das-btn--ghost me-2">
                <i class="ti tabler-users"></i> Anggota
              </a>
              <a href="{{ route('admin.ekskul.index') }}" class="das-btn das-btn--ghost">
                <i class="ti tabler-arrow-left"></i> Kembali
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Flash Messages --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-lg slide-in-up"
      role="alert" style="border-radius:8px;background:rgba(0,0,0,.3);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.1)!important;">
      <i class="ti tabler-circle-check fs-4 text-success"></i>
      <div class="text-white small fw-medium">{{ session('success') }}</div>
      <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row g-3">
    {{-- ═══════════ SHORTCUT PICKER ═══════════ --}}
    <div class="col-lg-8">
      <div class="das-panel slide-in-up">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot"></span>
            Pilih Tanggal Absensi
          </div>
        </div>
        <div class="p-4">
          <form id="tanggalForm" class="row g-3 align-items-end">
            <div class="col-md-5">
              <label class="das-form-label" for="tanggal">
                <i class="ti tabler-calendar me-1 text-info"></i> Tanggal
              </label>
              <input type="date" name="tanggal" id="tanggal"
                class="form-control das-form-control"
                value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-4">
              <label class="das-form-label">&nbsp;</label>
              <button type="button" class="das-btn das-btn--primary w-100 justify-content-center" id="btnBukaAbsensi">
                <i class="ti tabler-search me-1"></i> Buka Absensi
              </button>
            </div>
          </form>

          <hr style="border-color:var(--das-border)!important;margin:1.5rem 0;">

          <div class="row g-3">
            {{-- Tombol Hari Ini --}}
            <div class="col-sm-6">
              <a href="{{ route('admin.ekskul.absensi.show', [$ekskul->id, date('Y-m-d')]) }}"
                class="shortcut-card">
                <div class="shortcut-card__icon text-info">
                  <i class="ti tabler-calendar-check"></i>
                </div>
                <div class="text-white fw-semibold small">Absensi Hari Ini</div>
                <div class="text-white-50 small" style="font-size:.7rem;">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</div>
              </a>
            </div>

            {{-- Tombol Rekap --}}
            <div class="col-sm-6">
              <a href="{{ route('admin.ekskul.absensi.rekap', $ekskul->id) }}"
                class="shortcut-card">
                <div class="shortcut-card__icon text-warning">
                  <i class="ti tabler-report-analytics"></i>
                </div>
                <div class="text-white fw-semibold small">Lihat Rekap</div>
                <div class="text-white-50 small" style="font-size:.7rem;">Statistik & rekap bulanan</div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ═══════════ QR GENERATOR ═══════════ --}}
    <div class="col-lg-4 d-flex flex-column gap-3">
      <div class="das-panel slide-in-up" x-data="qrGenerator({{ $ekskul->id }})">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot" style="background:var(--das-warning);box-shadow:0 0 6px var(--das-warning);"></span>
            QR Absensi Hari Ini
          </div>
        </div>
        <div class="p-4 text-center">
          {{-- Loading State --}}
          <div x-show="loading" class="py-4">
            <div class="spinner-border text-info mb-3" role="status" style="width:2.5rem;height:2.5rem;"></div>
            <p class="text-white-50 small">Menghasilkan QR Code...</p>
          </div>

          {{-- QR Preview --}}
          <div x-show="qrUrl && !loading">
            <div class="qr-preview mb-3" id="qrcodeContainer">
              <img :src="qrUrl" alt="QR Code" style="width:100%;height:100%;object-fit:contain;">
            </div>
            <div class="text-white-50 small mb-2" style="font-size:.7rem;">
              <i class="ti tabler-clock me-1"></i> Token: <code class="text-info" x-text="tokenShort" style="font-size:.65rem;"></code>
            </div>
            <button class="das-btn das-btn--ghost w-100 justify-content-center" @click="generate" style="font-size:.7rem;">
              <i class="ti tabler-refresh me-1"></i> Generate Ulang
            </button>
          </div>

          {{-- Initial State --}}
          <div x-show="!qrUrl && !loading && !error" class="py-3">
            <i class="ti tabler-qrcode text-muted" style="font-size:3rem;opacity:.3;"></i>
            <p class="text-white-50 small mt-2 mb-3">QR Code untuk mempermudah siswa melakukan absensi mandiri.</p>
            <button class="das-btn das-btn--primary w-100 justify-content-center" @click="generate">
              <i class="ti tabler-qrcode me-1"></i> Generate QR
            </button>
          </div>

          {{-- Error State --}}
          <div x-show="error && !loading" class="py-3">
            <i class="ti tabler-alert-triangle text-danger" style="font-size:2rem;"></i>
            <p class="text-danger small mt-2" x-text="error"></p>
            <button class="das-btn das-btn--ghost mt-2" @click="generate">
              <i class="ti tabler-refresh me-1"></i> Coba Lagi
            </button>
          </div>
        </div>
      </div>

      {{-- ═══════════ SCAN QR SISWA ═══════════ --}}
      <div class="das-panel slide-in-up" x-data="qrScanner({{ $ekskul->id }})">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot" style="background:var(--das-success);box-shadow:0 0 6px var(--das-success);"></span>
            Scan QR Siswa
          </div>
        </div>
        <div class="p-4">

          {{-- ··· NIS INPUT ··· --}}
          <div class="mb-3">
            <label class="das-form-label">
              <i class="ti tabler-id me-1 text-info"></i> NIS / NISN Siswa
            </label>
            <div class="d-flex gap-2">
              <input type="text" x-model="nis"
                class="form-control das-form-control flex-grow-1"
                placeholder="Masukkan NIS..."
                inputmode="numeric"
                autocomplete="off"
                maxlength="30"
                :disabled="scannerActive || loading">
              <button class="das-btn das-btn--primary flex-shrink-0" @click="lookupSiswa"
                :disabled="!nis.trim() || loading"
                x-show="!scannerActive">
                <i class="ti tabler-search"></i>
              </button>
            </div>
            <div class="text-white-50 small mt-1 qr-nis-hint">
              <i class="ti tabler-info-circle"></i> Masukkan NIS manual, atau scan QR siswa
            </div>
          </div>

          {{-- ··· CAMERA SECTION ··· --}}
          <div class="position-relative mb-3 qr-video-wrapper">
            {{-- Video Preview --}}
            <video x-ref="video" playsinline muted autoplay
              x-show="scannerActive"
              class="qr-video">
            </video>
            <canvas x-ref="canvas" class="qr-canvas"></canvas>

            {{-- Crosshair --}}
            <div x-show="scannerActive" class="scan-crosshair-admin">
              <div class="qr-crosshair__box">
                <div class="qr-corner qr-corner--tl"></div>
                <div class="qr-corner qr-corner--tr"></div>
                <div class="qr-corner qr-corner--bl"></div>
                <div class="qr-corner qr-corner--br"></div>
                <div class="qr-scan-line"></div>
              </div>
            </div>

            {{-- Idle / Initial State --}}
            <div x-show="!scannerActive && !lookupResult"
                 class="qr-overlay qr-overlay--idle">
              <i class="ti tabler-camera text-muted qr-overlay__icon"></i>
              <p class="text-white-50 small text-center mb-0 qr-overlay__text">Arahkan kamera ke QR Code siswa (kartu NIS / HP)</p>
            </div>

            {{-- Camera Error --}}
            <div x-show="cameraError" class="qr-overlay qr-overlay--error">
              <i class="ti tabler-alert-triangle text-danger qr-overlay__error-icon"></i>
              <p class="text-danger small text-center mb-0 qr-overlay__error-text" x-text="cameraError"></p>
            </div>
          </div>

          {{-- ··· CAMERA BUTTONS ··· --}}
          <div class="d-flex gap-2 mb-3">
            <button class="das-btn flex-grow-1 justify-content-center"
              :class="scannerActive ? 'das-btn--danger' : 'das-btn--ghost'"
              @click="scannerActive ? stopCamera() : startCamera()"
              :disabled="loading">
              <template x-if="!scannerActive">
                <><i class="ti tabler-camera-plus me-1"></i> Buka Kamera</>
              </template>
              <template x-if="scannerActive">
                <><i class="ti tabler-camera-off me-1"></i> Tutup Kamera</>
              </template>
            </button>
            <button class="das-btn das-btn--ghost" @click="switchCamera" x-show="scannerActive"
              :disabled="loading" title="Ganti Kamera">
              <i class="ti tabler-camera-rotate"></i>
            </button>
          </div>

          {{-- ··· LOADING ··· --}}
          <div x-show="loading" class="text-center py-2">
            <div class="spinner-border text-success spinner-border-sm me-2" role="status"></div>
            <span class="text-white-50 small" x-text="loadingText"></span>
          </div>

          {{-- ··· LOOKUP RESULT ··· --}}
          <div x-show="lookupResult" class="das-panel qr-result">
            <div class="p-3">
              <div class="d-flex align-items-center gap-3 mb-2">
                <div class="qr-result__avatar">
                  <i class="ti tabler-user-check text-success fs-5"></i>
                </div>
                <div class="min-w-0">
                  <div class="text-white fw-semibold small qr-result__name" x-text="lookupResult.siswa.nama_lengkap"></div>
                  <div class="text-white-50 small qr-result__meta">
                    <span x-text="lookupResult.siswa.nis"></span>
                    <template x-if="lookupResult.siswa.kelas">
                      <span> · <span x-text="lookupResult.siswa.kelas"></span></span>
                    </template>
                  </div>
                </div>
                <template x-if="!lookupResult.is_anggota">
                  <span class="badge bg-danger ms-auto qr-result__badge">Non-Anggota</span>
                </template>
              </div>

              <template x-if="!lookupResult.is_anggota">
                <div class="alert alert-danger py-1 px-2 mb-2 qr-result__alert">
                  <i class="ti tabler-alert-triangle me-1"></i> Siswa ini bukan anggota ekskul
                </div>
              </template>

              <div class="d-flex gap-2 mt-2">
                <button class="das-btn flex-grow-1 justify-content-center"
                  :class="confirmed ? 'das-btn--success' : 'das-btn--primary'"
                  @click="confirmAbsensi"
                  :disabled="loading || confirmed || !lookupResult.is_anggota">
                  <template x-if="!confirmed">
                    <><i class="ti tabler-check me-1"></i> Konfirmasi Absen</>
                  </template>
                  <template x-if="confirmed">
                    <><i class="ti tabler-circle-check me-1"></i> Tercatat Hadir</>
                  </template>
                </button>
                <button class="das-btn das-btn--ghost" @click="resetScan" :disabled="loading">
                  <i class="ti tabler-x"></i>
                </button>
              </div>

              {{-- Warning for already attended --}}
              <div x-show="alreadyAttended" class="mt-2 qr-result__warning">
                <i class="ti tabler-exclamation-circle me-1"></i> <span x-text="alreadyMessage"></span>
              </div>
            </div>
          </div>

          {{-- ··· SCAN LOG ··· --}}
          <div x-show="scanLog.length > 0" class="mt-3 qr-log">
            <div class="text-white-50 small mb-2 qr-log__header">
              <i class="ti tabler-clock me-1"></i> Riwayat Scan Hari Ini
            </div>
            <div class="d-flex flex-column gap-1 qr-log__container">
              <template x-for="(log, idx) in scanLog" :key="idx">
                <div class="d-flex align-items-center gap-2 px-2 py-1 qr-log__item">
                  <i class="ti qr-log__icon" :class="log.type === 'success' ? 'tabler-circle-check text-success' : (log.type === 'warning' ? 'tabler-exclamation-circle text-warning' : 'tabler-circle-x text-danger')"></i>
                  <span class="text-white small flex-grow-1 qr-log__message" x-text="log.message"></span>
                  <span class="text-white-50 qr-log__time" x-text="log.time"></span>
                </div>
              </template>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════ INFO EKSKUL ═══════════ --}}
  <div class="das-panel mt-4 slide-in-up">
    <div class="p-3">
      <div class="row g-3">
        <div class="col-md-4">
          <div class="d-flex align-items-center gap-2">
            <i class="ti tabler-category text-info"></i>
            <span class="text-white-50 small">Kategori:</span>
            <span class="text-white fw-semibold small text-capitalize">{{ $ekskul->kategori }}</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex align-items-center gap-2">
            <i class="ti tabler-users text-info"></i>
            <span class="text-white-50 small">Status:</span>
            <span class="das-chip {{ $ekskul->status ? 'das-chip--success' : 'das-chip--danger' }}" style="font-size:.6rem;padding:1px 8px;">
              {{ $ekskul->status ? 'Aktif' : 'Nonaktif' }}
            </span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex align-items-center gap-2">
            <i class="ti tabler-location text-info"></i>
            <span class="text-white-50 small">Jadwal:</span>
            @if($ekskul->jadwal->isNotEmpty())
              <span class="text-white small">{{ $ekskul->jadwal->first()->hari }}, {{ substr($ekskul->jadwal->first()->jam_mulai, 0, 5) }}</span>
            @else
              <span class="text-white-50 small">-</span>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Buka absensi dengan tanggal yang dipilih
    document.getElementById('btnBukaAbsensi').addEventListener('click', function() {
      var tanggal = document.getElementById('tanggal').value;
      if (!tanggal) {
        alert('Silakan pilih tanggal terlebih dahulu.');
        return;
      }
      window.location.href = '{{ route('admin.ekskul.absensi.show', [$ekskul->id, '__TANGGAL__']) }}'.replace('__TANGGAL__', tanggal);
    });
  });

  function qrGenerator(ekskulId) {
    return {
      qrUrl: null,
      tokenShort: '',
      loading: false,
      error: null,

      async generate() {
        this.loading = true;
        this.error = null;
        this.qrUrl = null;

        try {
          const response = await fetch('{{ route('admin.ekskul.generate-qr', $ekskul->id) }}', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              tanggal: '{{ date('Y-m-d') }}'
            })
          });

          const result = await response.json();

          if (result.success) {
            // Generate QR code secara lokal — encode URL lengkap agar konsisten dengan halaman scan
            this.qrUrl = result.qr_image || ('https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(result.data.url));
            this.tokenShort = result.data.token.substring(0, 16) + '...';
          } else {
            this.error = result.message || 'Gagal generate QR code.';
          }
        } catch (e) {
          this.error = 'Gagal terhubung ke server. Periksa koneksi.';
        } finally {
          this.loading = false;
        }
      }
    }
  }

  /**
   * ── QR Scanner Component ──────────────────────────────────────
   * Digunakan oleh admin/pembina untuk memindai QR Code siswa
   * (kartu NIS atau HP siswa) dan mencatat kehadiran.
   */
  function qrScanner(ekskulId) {
    return {
      // ── State ────────────────────────────────────────────────
      nis: '',
      scannerActive: false,
      loading: false,
      loadingText: 'Memproses...',
      cameraError: null,
      lookupResult: null,
      confirmed: false,
      alreadyAttended: false,
      alreadyMessage: '',
      scanLog: [],
      currentFacingMode: 'environment',

      // Internal (non-reaktif via closure)
      stream: null,
      animFrame: null,
      isProcessing: false,
      lastQR: '',
      lastQRTime: 0,
      DEBOUNCE: 3000,

      // ── Init ─────────────────────────────────────────────────
      init() {
        // Reset state
        this.stopCamera();
      },

      // ── Start Camera ─────────────────────────────────────────
      async startCamera() {
        this.cameraError = null;
        this.loading = true;
        this.loadingText = 'Mengakses kamera...';

        try {
          if (this.stream) {
            this.stream.getTracks().forEach(t => t.stop());
            this.stream = null;
          }

          const constraints = {
            video: {
              facingMode: { ideal: this.currentFacingMode },
              width: { ideal: 640 },
              height: { ideal: 480 }
            }
          };

          this.stream = await navigator.mediaDevices.getUserMedia(constraints);

          const video = this.$refs.video;
          video.srcObject = this.stream;
          await video.play();

          this.scannerActive = true;
          this.loading = false;

          // Mulai tick loop
          this.startTick();
        } catch (err) {
          this.loading = false;

          if (['NotAllowedError', 'PermissionDeniedError'].includes(err.name)) {
            this.cameraError = 'Izin kamera ditolak. Buka pengaturan browser untuk mengizinkan.';
          } else if (['NotFoundError', 'DevicesNotFoundError'].includes(err.name)) {
            this.cameraError = 'Kamera tidak ditemukan di perangkat ini.';
          } else if (err.name === 'NotReadableError') {
            this.cameraError = 'Kamera sedang digunakan aplikasi lain.';
          } else {
            this.cameraError = 'Gagal mengakses kamera: ' + (err.message || 'unknown error');
          }
        }
      },

      // ── Stop Camera ──────────────────────────────────────────
      stopCamera() {
        if (this.animFrame) {
          cancelAnimationFrame(this.animFrame);
          this.animFrame = null;
        }
        if (this.stream) {
          this.stream.getTracks().forEach(t => t.stop());
          this.stream = null;
        }
        this.scannerActive = false;
        this.isProcessing = false;
      },

      // ── Switch Camera ────────────────────────────────────────
      async switchCamera() {
        this.currentFacingMode = this.currentFacingMode === 'environment' ? 'user' : 'environment';
        await this.startCamera();
      },

      // ── Tick: baca frame dari video, deteksi QR ─────────────
      startTick() {
        const tick = () => {
          if (!this.stream || !this.scannerActive) {
            this.animFrame = null;
            return;
          }

          const video = this.$refs.video;
          const canvas = this.$refs.canvas;

          if (video.readyState >= video.HAVE_ENOUGH_DATA) {
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0);
            const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imgData.data, imgData.width, imgData.height, {
              inversionAttempts: 'attemptBoth'
            });

            if (code && !this.isProcessing && !this.lookupResult) {
              const now = Date.now();
              if (code.data !== this.lastQR || now - this.lastQRTime > this.DEBOUNCE) {
                this.lastQR = code.data;
                this.lastQRTime = now;
                this.handleScan(code.data);
              }
            }
          }

          this.animFrame = requestAnimationFrame(tick);
        };

        this.animFrame = requestAnimationFrame(tick);
      },

      // ── Handle QR Detection ──────────────────────────────────
      async handleScan(qrData) {
        // QR bisa berisi NIS langsung atau URL
        let nis = qrData.trim();

        // Jika QR berisi URL, coba ekstrak NIS dari parameter (fallback)
        // Tapi umumnya kita asumsikan QR siswa berisi NIS langsung
        // Support format URL: .../siswa/qr?nis=12345
        try {
          if (nis.startsWith('http')) {
            const url = new URL(nis);
            const params = new URLSearchParams(url.search);
            if (params.has('nis')) {
              nis = params.get('nis');
            }
          }
        } catch (_) {}

        // Hanya proses jika NIS terlihat valid (numeric atau alfanumerik pendek)
        if (!nis || nis.length < 3 || nis.length > 50) {
          return; // skip, lanjut scan
        }

        this.isProcessing = true;
        this.nis = nis;
        this.loading = true;
        this.loadingText = 'Memproses QR...';

        // Hentikan tick sementara
        if (this.animFrame) {
          cancelAnimationFrame(this.animFrame);
          this.animFrame = null;
        }

        await this.lookupSiswa();

        this.isProcessing = false;

        // Jika berhasil dapat result, stop kamera
        if (this.lookupResult) {
          this.stopCamera();
        } else {
          // Lanjutkan tick jika gagal
          this.startTick();
        }
      },

      // ── Lookup Siswa via API ─────────────────────────────────
      async lookupSiswa() {
        const nisValue = this.nis.trim();
        if (!nisValue) {
          this.addLog('error', 'NIS kosong');
          return;
        }

        this.loading = true;
        this.loadingText = 'Mencari siswa...';
        this.lookupResult = null;
        this.confirmed = false;
        this.alreadyAttended = false;

        try {
          const response = await fetch('{{ route('admin.ekskul.absensi.lookup-siswa', $ekskul->id) }}', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify({ nis: nisValue })
          });

          const result = await response.json();

          if (result.success) {
            this.lookupResult = result.data;
            this.addLog('success', 'Ditemukan: ' + result.data.siswa.nama_lengkap);
          } else {
            this.addLog('error', result.message || 'Siswa tidak ditemukan');
            // Haptic feedback
            if (navigator.vibrate) navigator.vibrate([50, 50, 50]);
          }
        } catch (e) {
          this.addLog('error', 'Gagal terhubung ke server');
          if (navigator.vibrate) navigator.vibrate([50, 50, 50]);
        } finally {
          this.loading = false;
        }
      },

      // ── Confirm Absensi via API ──────────────────────────────
      async confirmAbsensi() {
        if (!this.lookupResult || !this.lookupResult.is_anggota || this.confirmed) return;

        this.loading = true;
        this.loadingText = 'Menyimpan absensi...';

        try {
          const response = await fetch('{{ route('admin.ekskul.absensi.admin-scan', $ekskul->id) }}', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              siswa_id: this.lookupResult.siswa.id
            })
          });

          const result = await response.json();

          if (result.success) {
            this.confirmed = true;
            this.addLog('success', result.message || 'Absensi berhasil');
            if (navigator.vibrate) navigator.vibrate([100]);
          } else if (response.status === 409) {
            // Already attended
            this.alreadyAttended = true;
            this.alreadyMessage = result.message || 'Siswa sudah tercatat hadir';
            this.addLog('warning', result.message || 'Sudah hadir');
            if (navigator.vibrate) navigator.vibrate([50]);
          } else {
            this.addLog('error', result.message || 'Gagal mencatat absensi');
            if (navigator.vibrate) navigator.vibrate([50, 50, 50]);
          }
        } catch (e) {
          this.addLog('error', 'Gagal terhubung ke server');
          if (navigator.vibrate) navigator.vibrate([50, 50, 50]);
        } finally {
          this.loading = false;
        }
      },

      // ── Reset Scan ───────────────────────────────────────────
      resetScan() {
        this.lookupResult = null;
        this.confirmed = false;
        this.alreadyAttended = false;
        this.alreadyMessage = '';
        this.nis = '';
        this.lastQR = '';
      },

      // ── Add Log ──────────────────────────────────────────────
      addLog(type, message) {
        const time = new Date().toLocaleTimeString('id-ID', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
        });
        this.scanLog.unshift({ type, message, time });
        // Batasi maks 20 log
        if (this.scanLog.length > 20) {
          this.scanLog = this.scanLog.slice(0, 20);
        }
      },

      // ── Cleanup ──────────────────────────────────────────────
      destroy() {
        this.stopCamera();
      }
    }
  }
</script>
@endsection
