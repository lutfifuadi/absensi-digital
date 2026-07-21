@extends('layouts/layoutMaster')

@section('title', 'Konfigurasi SP')

@section('page-style')
  <style>
    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
    }

    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--bs-info) !important;
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.35) !important;
    }

    .form-select option {
      background: #1a1a2e;
      color: #ccc;
    }
  </style>
@endsection

@section('content')
  {{-- HERO HEADER --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-settings text-warning"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Point Pelanggaran / Pengaturan SP
          </div>
          <h4 class="das-hero__title text-gradient-gold">Konfigurasi Surat Peringatan (SP)</h4>
          <p class="das-hero__subtitle">Tentukan batas akumulasi poin pelanggaran untuk penerbitan SP1, SP2, dan SP3.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGES --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5"></i>
      <div>
        <ul class="mb-0 ps-3">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row text-white">
    <div class="col-md-4">
      {{-- PANEL SELEKSI TAHUN AKADEMIK --}}
      <div class="das-panel mb-4">
        <div class="das-panel__header border-bottom py-3 px-4" style="border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="das-panel__title mb-0"><i class="ti tabler-calendar me-1 text-warning"></i> Pilih Tahun Ajaran</h6>
        </div>
        <div class="das-panel__body p-4">
          <form id="taForm" method="GET" action="{{ route('admin.pelanggaran-konfigurasi.index') }}">
            <div class="mb-3">
              <label for="tahun_akademik_id" class="form-label">Tahun Ajaran</label>
              <select name="tahun_akademik_id" id="tahun_akademik_id" class="form-select text-white" onchange="this.form.submit()">
                @foreach($tahunAkademiks as $ta)
                  <option value="{{ $ta->id }}" {{ $tahunAkademikId == $ta->id ? 'selected' : '' }}>
                    {{ $ta->nama }} - {{ ucfirst($ta->semester) }} 
                    @if($ta->is_aktif) (Aktif) @endif
                  </option>
                @endforeach
              </select>
            </div>
            <div class="small text-white-50">
              <i class="ti tabler-info-circle me-1"></i> Konfigurasi SP diatur per-Tahun Akademik. Pastikan Anda mengonfigurasi tahun ajaran yang sedang aktif.
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      {{-- PANEL FORM PENGATURAN SP --}}
      <div class="das-panel">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between" style="border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="das-panel__title mb-0"><i class="ti tabler-adjustments me-1 text-warning"></i> Pengaturan Batas SP</h6>
          @if($konfigurasi)
            <span class="badge bg-label-success">Sudah Dikonfigurasi</span>
          @else
            <span class="badge bg-label-danger">Belum Dikonfigurasi (Default Sistem)</span>
          @endif
        </div>
        <div class="das-panel__body p-4">
          <form action="{{ route('admin.pelanggaran-konfigurasi.save') }}" method="POST">
            @csrf
            <input type="hidden" name="tahun_akademik_id" value="{{ $tahunAkademikId }}">

            <div class="row mb-4">
              <div class="col-md-4">
                <div class="card bg-label-primary border-0 p-3 text-center">
                  <div class="h5 text-primary mb-1">Batas SP 1</div>
                  <input type="number" name="batas_sp1" class="form-control text-center fs-5 fw-bold bg-dark border-secondary text-white" min="1" max="100" value="{{ old('batas_sp1', $konfigurasi?->batas_sp1 ?? 25) }}" required>
                  <span class="text-white-50 extra-small mt-2">Akumulasi Poin Minimum</span>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card bg-label-warning border-0 p-3 text-center">
                  <div class="h5 text-warning mb-1">Batas SP 2</div>
                  <input type="number" name="batas_sp2" class="form-control text-center fs-5 fw-bold bg-dark border-secondary text-white" min="1" max="100" value="{{ old('batas_sp2', $konfigurasi?->batas_sp2 ?? 50) }}" required>
                  <span class="text-white-50 extra-small mt-2">Batas SP 2 > SP 1</span>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card bg-label-danger border-0 p-3 text-center">
                  <div class="h5 text-danger mb-1">Batas SP 3</div>
                  <input type="number" name="batas_sp3" class="form-control text-center fs-5 fw-bold bg-dark border-secondary text-white" min="1" max="100" value="{{ old('batas_sp3', $konfigurasi?->batas_sp3 ?? 75) }}" required>
                  <span class="text-white-50 extra-small mt-2">Batas SP 3 > SP 2</span>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="notif_wa_aktif" name="notif_wa_aktif" value="1" {{ old('notif_wa_aktif', $konfigurasi?->notif_wa_aktif ?? true) ? 'checked' : '' }}>
                <label class="form-check-input-label text-white" for="notif_wa_aktif">Kirim Notifikasi SP Otomatis via WA ke Wali Murid</label>
              </div>
              <div class="text-white-50 small ms-4 ps-2 mt-1">
                Jika diaktifkan, sistem akan otomatis mengirimkan pesan WhatsApp ke nomor handphone orang tua/wali murid saat akumulasi pelanggaran siswa menyentuh batas SP.
              </div>
            </div>

            @if($konfigurasi)
              <div class="mb-4 text-white-50 small bg-label-secondary p-3 rounded d-flex align-items-center gap-2">
                <i class="ti tabler-user"></i>
                <span>Terakhir diubah oleh: <b>{{ $konfigurasi->creator?->name ?? 'Sistem' }}</b> pada {{ $konfigurasi->updated_at->format('d M Y H:i') }}</span>
              </div>
            @endif

            <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Simpan Konfigurasi</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
