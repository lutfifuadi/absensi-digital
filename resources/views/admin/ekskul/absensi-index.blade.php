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
    <div class="col-lg-4">
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
            // Gunakan QR API eksternal untuk generate gambar
            this.qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(result.data.token);
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
</script>
@endsection
