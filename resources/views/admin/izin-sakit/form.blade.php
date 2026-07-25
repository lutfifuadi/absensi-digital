@extends('layouts/layoutMaster')

@section('title', isset($izinSakit) ? 'Ubah Pengajuan Izin/Sakit' : 'Tambah Pengajuan Izin/Sakit')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-style')
<style>
  /* Modern Select2 Dark Override */
  .select2-container {
    width: 100% !important;
    max-width: 100% !important;
  }
  .select2-container--default .select2-selection--single {
    background-color: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #fff !important;
    height: 42px !important;
    border-radius: 8px !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #fff !important;
    line-height: 40px !important;
    padding-left: 14px !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
  }
  .select2-dropdown {
    background-color: #1e1e30 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #fff !important;
    z-index: 1060 !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    border-radius: 8px !important;
  }
  .select2-container--default .select2-results__option[aria-selected=true] {
    background-color: rgba(115, 103, 240, 0.25) !important;
    color: #fff !important;
  }
  .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #7367f0 !important;
    color: #fff !important;
  }

  /* Form Container Glassmorphism */
  .das-form-card {
    background: rgba(30, 30, 48, 0.6);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
  }
  .das-form-card .card-header {
    background: rgba(255, 255, 255, 0.02);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }

  /* Identity Banner Card */
  .identity-banner {
    background: linear-gradient(135deg, rgba(115, 103, 240, 0.12) 0%, rgba(0, 207, 232, 0.08) 100%);
    border: 1px solid rgba(115, 103, 240, 0.25);
    border-radius: 10px;
    padding: 1.2rem 1.5rem;
  }
  .identity-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(115, 103, 240, 0.2);
    border: 2px solid rgba(115, 103, 240, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    color: #7367f0;
  }

  /* Interactive Radio Cards for Jenis Izin */
  .jenis-card-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }
  .jenis-card {
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.03);
    border-radius: 10px;
    padding: 1rem 1.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .jenis-card:hover {
    border-color: rgba(115, 103, 240, 0.4);
    background: rgba(115, 103, 240, 0.06);
    transform: translateY(-2px);
  }
  .jenis-card.active {
    border-color: #7367f0 !important;
    background: rgba(115, 103, 240, 0.15) !important;
    box-shadow: 0 4px 15px rgba(115, 103, 240, 0.2);
  }
  .jenis-card.--sakit.active {
    border-color: #ea5455 !important;
    background: rgba(234, 84, 85, 0.15) !important;
    box-shadow: 0 4px 15px rgba(234, 84, 85, 0.2);
  }
  .jenis-card__icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
  }
  .jenis-card.--sakit .jenis-card__icon {
    background: rgba(234, 84, 85, 0.15);
    color: #ea5455;
  }
  .jenis-card.--izin .jenis-card__icon {
    background: rgba(115, 103, 240, 0.15);
    color: #7367f0;
  }

  /* Duration Banner */
  .duration-badge {
    background: rgba(0, 207, 232, 0.1);
    border: 1px solid rgba(0, 207, 232, 0.25);
    color: #00cfe8;
    border-radius: 6px;
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  /* Quota Info Card */
  .quota-card {
    background: rgba(30, 30, 48, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    backdrop-filter: blur(12px);
    overflow: hidden;
    margin-bottom: 1.5rem;
  }
  .quota-card__head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }
  .quota-card__head-icon {
    width: 32px; height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 207, 232, 0.15);
    color: #00cfe8;
    font-size: 1rem;
    flex-shrink: 0;
  }
  .quota-card__title {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: rgba(255,255,255,0.7);
    margin: 0;
  }
  .quota-card__body {
    padding: 1rem 1.25rem;
  }
  .quota-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.75rem;
  }
  .quota-item {
    background: rgba(15, 23, 42, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.2s;
  }
  .quota-item__name {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: rgba(255,255,255,0.5);
    margin-bottom: 4px;
  }
  .quota-item__bar {
    height: 5px;
    background: rgba(255,255,255,0.08);
    border-radius: 3px;
    margin: 6px 0;
    overflow: hidden;
  }
  .quota-item__bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.6s ease;
  }
  .quota-item__stats {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
  }
  .quota-item__remaining {
    font-weight: 700;
  }
  .quota-item__remaining.--safe { color: #28c76f; }
  .quota-item__remaining.--low { color: #ff9f43; }
  .quota-item__remaining.--empty { color: #ea5455; }
  .quota-item__used {
    color: rgba(255,255,255,0.4);
  }
  .quota-message {
    padding: 0.65rem 1rem;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    margin-top: 0.75rem;
  }
  .quota-message.--success {
    background: rgba(40, 199, 111, 0.12);
    color: #28c76f;
    border: 1px solid rgba(40, 199, 111, 0.25);
  }
  .quota-message.--warning {
    background: rgba(255, 159, 67, 0.12);
    color: #ff9f43;
    border: 1px solid rgba(255, 159, 67, 0.25);
  }
  .quota-message.--danger {
    background: rgba(234, 84, 85, 0.12);
    color: #ea5455;
    border: 1px solid rgba(234, 84, 85, 0.25);
  }
  .quota-loading {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.8rem;
    color: rgba(255,255,255,0.5);
    padding: 0.5rem 0;
  }
  .quota-loading .spinner {
    width: 18px; height: 18px;
    border: 2px solid rgba(255,255,255,0.1);
    border-top-color: #00cfe8;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  .quota-card-hidden { display: none; }
</style>
@endsection

@section('content')

@php
  $indexRoute = route('admin.izin-sakit.index');
  if (auth()->check()) {
      if (auth()->user()->isRole(\App\Models\User::ROLE_SISWA)) {
          $indexRoute = route('siswa.izin-sakit.index');
      } elseif (auth()->user()->isRole(\App\Models\User::ROLE_GURU)) {
          $indexRoute = route('guru.izin-sakit.index');
      }
  }
@endphp

  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1e1e30 0%, #16213e 50%, #0f3460 100%); border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm"
              style="width:52px;height:52px;background:rgba(0,207,232,0.15);border:1px solid rgba(0,207,232,0.3);flex-shrink:0;">
              <i class="ti {{ isset($izinSakit) ? 'tabler-pencil' : 'tabler-file-plus' }} text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.75rem;opacity:0.7;">
                  @auth
                    @if(auth()->user()->isRole(\App\Models\User::ROLE_SISWA))
                      <li class="breadcrumb-item"><a href="{{ route('siswa.dashboard') }}" class="text-white text-decoration-none">Portal Siswa</a></li>
                      <li class="breadcrumb-item"><a href="{{ $indexRoute }}" class="text-white text-decoration-none">Izin & Sakit</a></li>
                    @elseif(auth()->user()->isRole(\App\Models\User::ROLE_GURU))
                      <li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}" class="text-white text-decoration-none">Portal Guru</a></li>
                      <li class="breadcrumb-item"><a href="{{ $indexRoute }}" class="text-white text-decoration-none">Izin & Sakit</a></li>
                    @else
                      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white text-decoration-none">Admin</a></li>
                      <li class="breadcrumb-item"><a href="{{ $indexRoute }}" class="text-white text-decoration-none">Izin & Sakit</a></li>
                    @endif
                  @endauth
                  <li class="breadcrumb-item active text-info fw-semibold">{{ isset($izinSakit) ? 'Ubah Pengajuan' : 'Formulir Baru' }}</li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                {{ isset($izinSakit) ? 'Ubah Pengajuan Izin / Sakit' : 'Pengajuan Izin / Sakit' }}
              </h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- QUOTA INFO CARD WIDGET --}}
  <div class="row mb-4">
    <div class="col-12">
      <div id="quotaCard" class="quota-card quota-card-hidden mb-0">
        <div class="quota-card__head">
          <div class="quota-card__head-icon">
            <i class="ti tabler-chart-bar"></i>
          </div>
          <h6 class="quota-card__title">Informasi Kuota Perizinan</h6>
          <span id="quotaPeriod" class="ms-auto text-white-50" style="font-size:0.68rem; font-weight:600;"></span>
        </div>
        <div class="quota-card__body">
          {{-- Loading --}}
          <div id="quotaLoading" class="quota-loading" style="display:none;">
            <div class="spinner"></div>
            <span>Memeriksa sisa kuota Anda...</span>
          </div>

          {{-- Error --}}
          <div id="quotaError" class="quota-message --danger" style="display:none;"></div>

          {{-- Grid --}}
          <div id="quotaGridContainer" style="display:none;">
            <div id="quotaGrid" class="quota-grid"></div>
            <div id="quotaMessage" class="quota-message" style="display:none;"></div>
          </div>

          {{-- No limits --}}
          <div id="quotaNoLimits" class="quota-message --success" style="display:none;">
            <i class="ti tabler-circle-check me-1"></i> Tidak ada batasan kuota perizinan untuk akun Anda.
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">

      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
          style="border-radius:10px; background: rgba(234, 84, 85, 0.15); color: #ea5455; border: 1px solid rgba(234,84,85,0.3);">
          <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
          <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="card das-form-card shadow-lg border-0 mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="ti tabler-notes text-info fs-5"></i>
            <h6 class="card-title mb-0 fw-bold text-white">Formulir Pengajuan Surat</h6>
          </div>
          <span class="badge bg-label-info px-3 py-1" style="font-size:0.75rem; border-radius: 20px;">
            <i class="ti tabler-shield-check me-1"></i> Form Resmi
          </span>
        </div>
        <div class="card-body p-4">
          <form
            action="{{ isset($izinSakit) ? route('admin.izin-sakit.update', $izinSakit) : route('admin.izin-sakit.store') }}"
            method="POST" enctype="multipart/form-data" id="formIzinSakit">
            @csrf
            @if (isset($izinSakit))
              @method('PUT')
            @endif

            {{-- SECTION 1: IDENTITAS PENGAJU --}}
            @if(!empty($isSelf))
              <input type="hidden" name="tipe" id="tipePengaju" value="{{ $selfType }}">
              <input type="hidden" name="reference_id" id="referenceId" value="{{ $selfReferenceId }}" data-user-id="{{ auth()->id() }}">

              <div class="identity-banner mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="identity-avatar">
                      @if(!empty($selfModel?->foto))
                        <img src="{{ Storage::url($selfModel->foto) }}" alt="Foto" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                      @else
                        {{ strtoupper(substr($selfName, 0, 1)) }}
                      @endif
                    </div>
                    <div>
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <h6 class="mb-0 text-white fw-bold fs-6">{{ $selfName }}</h6>
                        <span class="badge bg-info text-dark font-mono" style="font-size:0.68rem; font-weight:700;">
                          {{ strtoupper($selfType) }} YBS
                        </span>
                      </div>
                      <div class="text-white-50 small d-flex align-items-center gap-3">
                        @if($selfType === 'siswa')
                          <span><i class="ti tabler-id me-1 text-info"></i> NIS: <strong>{{ $selfModel?->nis ?? '-' }}</strong></span>
                          <span><i class="ti tabler-school me-1 text-info"></i> Kelas: <strong>{{ $selfModel?->kelas?->nama_kelas ?? 'Umum' }}</strong></span>
                        @else
                          <span><i class="ti tabler-id me-1 text-info"></i> NIP: <strong>{{ $selfModel?->nip ?? '-' }}</strong></span>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="badge bg-label-success px-3 py-2" style="font-size:0.75rem; border-radius: 20px;">
                    <i class="ti tabler-circle-check me-1"></i> Data Terverifikasi
                  </div>
                </div>
              </div>
            @else
              {{-- ADMIN / WALI KELAS MODE (SELECT DROPDOWN) --}}
              <div class="row g-4 mb-4 pb-3 border-bottom" style="border-color: rgba(255,255,255,0.08) !important;">
                <div class="col-md-4">
                  <label class="form-label fw-semibold small text-white-50" for="tipePengaju">
                    <i class="ti tabler-user-cog me-1 text-info"></i> Tipe Pengaju <span class="text-danger">*</span>
                  </label>
                  <select name="tipe" class="form-select @error('tipe') is-invalid @enderror" id="tipePengaju" required>
                    <option value="">-- Pilih Tipe --</option>
                    @foreach (['siswa', 'guru', 'staff'] as $t)
                      <option value="{{ $t }}" @selected(old('tipe', $izinSakit->tipe ?? '') === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                  </select>
                  @error('tipe')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-8">
                  <label class="form-label fw-semibold small text-white-50" for="referenceId">
                    <i class="ti tabler-user me-1 text-info"></i> Nama Pengaju <span class="text-danger">*</span>
                  </label>
                  <select name="reference_id" class="form-select select2 @error('reference_id') is-invalid @enderror" id="referenceId" data-placeholder="-- Pilih Nama --" required>
                    <option value="">-- Pilih Nama --</option>
                    <optgroup label="Siswa">
                      @foreach ($siswaOptions as $s)
                        <option value="{{ $s->id }}" data-tipe="siswa" data-user-id="{{ $s->user?->id ?? '' }}" @selected(old('reference_id', $izinSakit->reference_id ?? '') == $s->id && old('tipe', $izinSakit->tipe ?? '') === 'siswa')>
                          {{ $s->nama_lengkap }}
                        </option>
                      @endforeach
                    </optgroup>
                    <optgroup label="Guru">
                      @foreach ($guruOptions as $g)
                        <option value="{{ $g->id }}" data-tipe="guru" data-user-id="{{ $g->user?->id ?? '' }}" @selected(old('reference_id', $izinSakit->reference_id ?? '') == $g->id && old('tipe', $izinSakit->tipe ?? '') === 'guru')>
                          {{ $g->nama_lengkap }}
                        </option>
                      @endforeach
                    </optgroup>
                    <optgroup label="Staff TU">
                      @foreach ($staffOptions as $st)
                        <option value="{{ $st->id }}" data-tipe="staff" data-user-id="{{ $st->user?->id ?? '' }}" @selected(old('reference_id', $izinSakit->reference_id ?? '') == $st->id && old('tipe', $izinSakit->tipe ?? '') === 'staff')>
                          {{ $st->nama_lengkap }}
                        </option>
                      @endforeach
                    </optgroup>
                  </select>
                  @error('reference_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            @endif

            {{-- SECTION 2: DETAIL PENGAJUAN --}}
            <div class="row g-4">
              {{-- JENIS PENGAJUAN (PILL CARDS) --}}
              <div class="col-12">
                <label class="form-label fw-semibold small text-white-50 mb-2">
                  <i class="ti tabler-clipboard-text me-1 text-info"></i> Jenis Pengajuan <span class="text-danger">*</span>
                </label>

                <!-- Hidden select synced with pills for backend validation -->
                <select name="jenis" class="d-none @error('jenis') is-invalid @enderror" id="jenisIzin" required>
                  <option value="">-- Pilih Jenis --</option>
                  <option value="sakit" @selected(old('jenis', $izinSakit->jenis ?? '') === 'sakit')>Sakit</option>
                  <option value="izin" @selected(old('jenis', $izinSakit->jenis ?? '') === 'izin')>Izin</option>
                </select>

                <div class="jenis-card-group">
                  <div class="jenis-card --sakit {{ old('jenis', $izinSakit->jenis ?? '') === 'sakit' ? 'active' : '' }}" onclick="selectJenis('sakit')">
                    <div class="jenis-card__icon">
                      <i class="ti tabler-stethoscope"></i>
                    </div>
                    <div>
                      <h6 class="mb-0 text-white fw-bold" style="font-size:0.95rem;">Sakit</h6>
                      <small class="text-white-50">Pengajuan karena kondisi kesehatan / sakit</small>
                    </div>
                    <i class="ti tabler-circle-check-filled ms-auto text-danger fs-5 check-icon" style="{{ old('jenis', $izinSakit->jenis ?? '') === 'sakit' ? '' : 'display:none;' }}"></i>
                  </div>

                  <div class="jenis-card --izin {{ old('jenis', $izinSakit->jenis ?? '') === 'izin' ? 'active' : '' }}" onclick="selectJenis('izin')">
                    <div class="jenis-card__icon">
                      <i class="ti tabler-file-text"></i>
                    </div>
                    <div>
                      <h6 class="mb-0 text-white fw-bold" style="font-size:0.95rem;">Izin</h6>
                      <small class="text-white-50">Pengajuan untuk keperluan lain / urusan keluarga</small>
                    </div>
                    <i class="ti tabler-circle-check-filled ms-auto text-primary fs-5 check-icon" style="{{ old('jenis', $izinSakit->jenis ?? '') === 'izin' ? '' : 'display:none;' }}"></i>
                  </div>
                </div>
                @error('jenis')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              {{-- TANGGAL MULAI & SELESAI --}}
              <div class="col-md-5">
                <label class="form-label fw-semibold small text-white-50" for="tanggalMulai">
                  <i class="ti tabler-calendar me-1 text-info"></i> Tanggal Mulai <span class="text-danger">*</span>
                </label>
                <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                  id="tanggalMulai"
                  value="{{ old('tanggal_mulai', isset($izinSakit) ? $izinSakit->tanggal_mulai->format('Y-m-d') : '') }}"
                  style="background: rgba(255,255,255,0.05); color: #fff; border-color: rgba(255,255,255,0.12); height: 42px; border-radius: 8px;"
                  required>
                @error('tanggal_mulai')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-5">
                <label class="form-label fw-semibold small text-white-50" for="tanggalSelesai">
                  <i class="ti tabler-calendar-due me-1 text-info"></i> Tanggal Selesai <span class="text-danger">*</span>
                </label>
                <input type="date" name="tanggal_selesai"
                  class="form-control @error('tanggal_selesai') is-invalid @enderror"
                  id="tanggalSelesai"
                  value="{{ old('tanggal_selesai', isset($izinSakit) ? $izinSakit->tanggal_selesai->format('Y-m-d') : '') }}"
                  style="background: rgba(255,255,255,0.05); color: #fff; border-color: rgba(255,255,255,0.12); height: 42px; border-radius: 8px;"
                  required>
                @error('tanggal_selesai')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-2 d-flex align-items-end">
                <div id="durationBadge" class="duration-badge w-100 justify-content-center py-2" style="display:none; height:42px;">
                  <i class="ti tabler-clock"></i> <span id="durationText">0 Hari</span>
                </div>
              </div>

              @if (isset($izinSakit) && auth()->user()->isRole(\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_ADMIN_SEKOLAH))
                <div class="col-md-12">
                  <label class="form-label fw-semibold small text-white-50" for="statusIzin">
                    <i class="ti tabler-circle-check me-1 text-info"></i> Status Persetujuan Admin
                  </label>
                  <select name="status" class="form-select" id="statusIzin" style="background: rgba(255,255,255,0.05); color: #fff; border-color: rgba(255,255,255,0.12); height: 42px; border-radius: 8px;">
                    @foreach (['pending', 'disetujui', 'ditolak'] as $s)
                      <option value="{{ $s }}" @selected(old('status', $izinSakit->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                  </select>
                </div>
              @endif

              {{-- KETERANGAN --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small text-white-50" for="keterangan">
                  <i class="ti tabler-note me-1 text-info"></i> Keterangan & Alasan Pengajuan
                </label>
                <textarea id="keterangan" name="keterangan" class="form-control" rows="3"
                  placeholder="Tuliskan keterangan detail mengenai izin/sakit Anda..."
                  style="background: rgba(255,255,255,0.05); color: #fff; border-color: rgba(255,255,255,0.12); border-radius: 8px;">{{ old('keterangan', $izinSakit->keterangan ?? '') }}</textarea>
              </div>

              {{-- LAMPIRAN FILE --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small text-white-50" for="lampiran">
                  <i class="ti tabler-paperclip me-1 text-info"></i> Upload Lampiran / Surat Keterangan Dokter
                </label>
                
                @if (isset($izinSakit) && $izinSakit->lampiran)
                  <div class="mb-2 p-2 rounded d-flex align-items-center gap-2" style="background: rgba(0,207,232,0.1); border: 1px dashed rgba(0,207,232,0.3);">
                    <i class="ti tabler-file-check text-info fs-5"></i>
                    <span class="small text-white me-auto">Lampiran tersimpan saat ini</span>
                    <a href="{{ Storage::url($izinSakit->lampiran) }}" target="_blank" class="btn btn-xs btn-info">
                      <i class="ti tabler-eye me-1"></i> Lihat File
                    </a>
                  </div>
                @endif

                <input type="file" name="lampiran" id="lampiran" class="form-control @error('lampiran') is-invalid @enderror"
                  accept=".jpg,.jpeg,.png,.pdf"
                  style="background: rgba(255,255,255,0.05); color: #fff; border-color: rgba(255,255,255,0.12); border-radius: 8px;">
                <div class="d-flex align-items-center justify-content-between mt-1 fs-xs text-white-50">
                  <span>Format: <strong>JPG, PNG, PDF</strong></span>
                  <span>Ukuran Maksimum: <strong>100 KB</strong></span>
                </div>
                @error('lampiran')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            {{-- FOOTER ACTIONS --}}
            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-3 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ $indexRoute }}" class="btn btn-label-secondary px-4 style-btn">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm" id="btnSubmit">
                <i class="ti tabler-send me-1"></i>
                {{ isset($izinSakit) ? 'Perbarui Pengajuan' : 'Kirim Pengajuan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script type="module">
    $(function() {
      const $refSelect = $('#referenceId');
      if ($refSelect.length && !$refSelect.is(':hidden')) {
        $refSelect.wrap('<div class="position-relative w-100"></div>').select2({
          width: '100%',
          placeholder: $refSelect.data('placeholder') || '-- Pilih Nama --',
          allowClear: true,
          dropdownParent: $refSelect.parent()
        });

        const tipeSelect = document.getElementById('tipePengaju');

        // Synchronize Select2 change event with quota checker
        $refSelect.on('change', function () {
          var selectedOpt = this.options[this.selectedIndex];
          if (selectedOpt && selectedOpt.value) {
            var optTipe = selectedOpt.getAttribute('data-tipe');
            if (optTipe && tipeSelect && !tipeSelect.value) {
              tipeSelect.value = optTipe;
            }
          }
          if (typeof window.scheduleCheck === 'function') {
            window.scheduleCheck();
          }
        });

        if (tipeSelect) {
          tipeSelect.addEventListener('change', function () {
            if ($refSelect.val()) {
              var selectedOpt = $refSelect[0].options[$refSelect[0].selectedIndex];
              if (selectedOpt && selectedOpt.getAttribute('data-tipe') !== this.value) {
                $refSelect.val('').trigger('change.select2');
              }
            }
          });
        }
      }
    });
  </script>

  <script>
  /**
   * Helper function for interactive Jenis pill selection
   */
  function selectJenis(value) {
    const jenisSelect = document.getElementById('jenisIzin');
    if (jenisSelect) {
      jenisSelect.value = value;
    }

    document.querySelectorAll('.jenis-card').forEach(card => {
      card.classList.remove('active');
      const checkIcon = card.querySelector('.check-icon');
      if (checkIcon) checkIcon.style.display = 'none';
    });

    const activeCard = document.querySelector('.jenis-card.--' + value);
    if (activeCard) {
      activeCard.classList.add('active');
      const checkIcon = activeCard.querySelector('.check-icon');
      if (checkIcon) checkIcon.style.display = 'block';
    }

    if (typeof window.scheduleCheck === 'function') {
      window.scheduleCheck();
    }
  }

  /**
   * Quota Checker for Izin/Sakit Form
   */
  var scheduleCheck;

  document.addEventListener('DOMContentLoaded', function () {
    const quotaCard      = document.getElementById('quotaCard');
    const quotaLoading   = document.getElementById('quotaLoading');
    const quotaError     = document.getElementById('quotaError');
    const quotaGrid      = document.getElementById('quotaGrid');
    const quotaGridContainer = document.getElementById('quotaGridContainer');
    const quotaNoLimits  = document.getElementById('quotaNoLimits');
    const quotaMessage   = document.getElementById('quotaMessage');
    const quotaPeriod    = document.getElementById('quotaPeriod');
    const btnSubmit      = document.getElementById('btnSubmit');

    const tipeSelect     = document.getElementById('tipePengaju');
    const refSelect      = document.getElementById('referenceId');
    const jenisSelect    = document.getElementById('jenisIzin');
    const tanggalMulai   = document.getElementById('tanggalMulai');
    const tanggalSelesai = document.getElementById('tanggalSelesai');
    const durationBadge  = document.getElementById('durationBadge');
    const durationText   = document.getElementById('durationText');

    // ─── State ────────────────────────────────────────────
    var currentUserId = @json(auth()->id());
    var isSelfMode = @json(!empty($isSelf));
    var checkTimeout = null;

    // Calculate Duration in Days
    function updateDuration() {
      if (tanggalMulai && tanggalSelesai && tanggalMulai.value && tanggalSelesai.value) {
        var start = new Date(tanggalMulai.value);
        var end = new Date(tanggalSelesai.value);
        if (end >= start) {
          var diffTime = Math.abs(end - start);
          var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
          durationText.textContent = diffDays + ' Hari';
          durationBadge.style.display = 'inline-flex';
        } else {
          durationBadge.style.display = 'none';
        }
      } else {
        durationBadge.style.display = 'none';
      }
    }

    if (tanggalMulai) tanggalMulai.addEventListener('change', function() { updateDuration(); scheduleCheck(); });
    if (tanggalSelesai) tanggalSelesai.addEventListener('change', function() { updateDuration(); scheduleCheck(); });
    updateDuration();

    // ─── Schedule check with debounce ─────────────────────
    scheduleCheck = function() {
      if (checkTimeout) clearTimeout(checkTimeout);
      checkTimeout = setTimeout(doCheck, 500);
    };
    window.scheduleCheck = scheduleCheck;

    // Trigger initial check if self mode
    if (isSelfMode) {
      scheduleCheck();
    }

  // ─── Main check function ──────────────────────────────
  function doCheck() {
    var userId = currentUserId;

    if (!isSelfMode) {
      var tipe = tipeSelect ? tipeSelect.value : '';
      var refId = refSelect ? refSelect.value : '';

      if (!tipe || !refId) {
        quotaCard.classList.add('quota-card-hidden');
        return;
      }

      var selectedOption = refSelect.options ? refSelect.options[refSelect.selectedIndex] : null;
      var resolvedUserId = selectedOption ? selectedOption.getAttribute('data-user-id') : '';

      if (!resolvedUserId) {
        quotaCard.classList.remove('quota-card-hidden');
        quotaLoading.style.display = 'none';
        quotaError.style.display = 'block';
        quotaError.textContent = 'Referensi terpilih belum memiliki akun pengguna. Kuota tidak dapat diperiksa.';
        quotaGridContainer.style.display = 'none';
        quotaNoLimits.style.display = 'none';
        return;
      }

      userId = resolvedUserId;
    }

    var jenis = jenisSelect ? jenisSelect.value : '';
    var startDate = tanggalMulai ? tanggalMulai.value : '';
    var endDate = tanggalSelesai ? tanggalSelesai.value : '';

    if (!userId || !jenis || !startDate || !endDate) {
      return;
    }

    var leaveType = jenis === 'sakit' ? 'sick' : 'permission';

    // Show loading
    quotaCard.classList.remove('quota-card-hidden');
    quotaLoading.style.display = 'flex';
    quotaError.style.display = 'none';
    quotaGridContainer.style.display = 'none';
    quotaNoLimits.style.display = 'none';

    var url = '{{ route("admin.izin-sakit.check-quota") }}' +
      '?user_id=' + encodeURIComponent(userId) +
      '&leave_type=' + encodeURIComponent(leaveType) +
      '&start_date=' + encodeURIComponent(startDate) +
      '&end_date=' + encodeURIComponent(endDate) +
      '&_=' + Date.now();

    fetch(url, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(function (res) {
        if (!res.ok) {
          return res.json().then(function (err) {
            throw new Error(err.message || 'Server error (' + res.status + ')');
          }).catch(function () {
            throw new Error('Server error (' + res.status + ')');
          });
        }
        return res.json();
      })
      .then(function (data) {
        quotaLoading.style.display = 'none';

        if (!data.success) {
          quotaError.style.display = 'block';
          quotaError.textContent = 'Gagal memeriksa kuota perizinan.';
          return;
        }

        var balances = data.balances || [];

        if (balances.length === 0) {
          quotaNoLimits.style.display = 'block';
          return;
        }

        // Show grid
        quotaGridContainer.style.display = 'block';
        quotaGrid.innerHTML = '';

        // Set period info
        var periodText = balances[0].period_code ? 'Periode Aktif: ' + balances[0].period_code : '';
        quotaPeriod.textContent = periodText;

        // Render each balance item
        balances.forEach(function (item) {
          var total = item.max_days + item.extra_days;
          var used = item.used_days;
          var remaining = item.remaining;
          var pct = total > 0 ? Math.min(100, (used / total) * 100) : 0;

          var barColor = pct >= 100 ? '#ea5455' : (pct >= 75 ? '#ff9f43' : '#28c76f');
          var remClass = remaining <= 0 ? '--empty' : (remaining <= 3 ? '--low' : '--safe');

          var div = document.createElement('div');
          div.className = 'quota-item';
          div.innerHTML =
            '<div class="quota-item__name">' + escapeHtml(item.name) + '</div>' +
            '<div class="quota-item__bar"><div class="quota-item__bar-fill" style="width:' + pct + '%;background:' + barColor + ';"></div></div>' +
            '<div class="quota-item__stats">' +
              '<span class="quota-item__remaining ' + remClass + '">Sisa: ' + remaining + ' / ' + total + ' Hari</span>' +
              '<span class="quota-item__used">Terpakai ' + used + '</span>' +
            '</div>';
          quotaGrid.appendChild(div);
        });

        // Show message
        if (data.allowed) {
          quotaMessage.className = 'quota-message --success';
          quotaMessage.innerHTML = '<i class="ti tabler-circle-check me-1"></i> Kuota mencukupi untuk pengajuan ini.';
          quotaMessage.style.display = 'block';
          if (btnSubmit) btnSubmit.disabled = false;
        } else if (data.action_type === 'warning') {
          quotaMessage.className = 'quota-message --warning';
          quotaMessage.innerHTML = '<i class="ti tabler-alert-triangle me-1"></i> <strong>Perhatian:</strong> Kuota perizinan Anda menipis atau habis. Pengajuan tetap dapat dikirim, namun perlu persetujuan admin.';
          quotaMessage.style.display = 'block';
          if (btnSubmit) btnSubmit.disabled = false;
        } else if (data.action_type === 'block') {
          quotaMessage.className = 'quota-message --danger';
          quotaMessage.innerHTML = '<i class="ti tabler-ban me-1"></i> <strong>Kuota Habis:</strong> Kuota perizinan Anda sudah habis. Silakan hubungi pihak sekolah/admin.';
          quotaMessage.style.display = 'block';
          if (btnSubmit) btnSubmit.disabled = true;
        }
      })
      .catch(function (err) {
        quotaLoading.style.display = 'none';
        quotaError.style.display = 'block';
        quotaError.textContent = 'Gagal memeriksa kuota: ' + err.message;
      });
  }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
});
</script>
@endsection