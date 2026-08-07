@extends('layouts/layoutMaster')

@section('title', 'Kelola Jam Absensi')

@section('page-style')
  <style>
    /* ═══════════════════════════════════════════════════════════════
         ROW HOVER & ACTION BUTTONS
    ═══════════════════════════════════════════════════════════════ */
    .kelas-row-hover {
      transition: background 0.15s ease;
    }

    .kelas-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .action-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 5px 12px;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 500;
      text-decoration: none;
      border: none;
      cursor: pointer;
      transition: opacity 0.15s ease, transform 0.15s ease;
      color: #fff !important;
    }

    .action-btn:hover {
      opacity: 0.85;
      transform: translateY(-1px);
    }

    /* ═══════════════════════════════════════════════════════════════
         MODAL JADWAL KELAS
    ═══════════════════════════════════════════════════════════════ */
    #modalJadwalKelas .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #1e1e2d;
      border-radius: 5px;
      overflow: hidden;
    }

    #modalJadwalKelas .modal-header {
      background: linear-gradient(135deg, #ff9f43 0%, #e65c00 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1.25rem 1.5rem;
    }

    #modalJadwalKelas .modal-body {
      padding: 1.5rem;
      max-height: 70vh;
      overflow-y: auto;
    }

    #modalJadwalKelas .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1rem 1.5rem;
      background: rgba(255, 255, 255, 0.02);
    }

    .modal-icon-header {
      width: 44px;
      height: 44px;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    /* ═══════════════════════════════════════════════════════════════
         MODAL BULK APPLY
    ═══════════════════════════════════════════════════════════════ */
    #modalBulkApply .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #1e1e2d;
      border-radius: 5px;
      overflow: hidden;
    }

    #modalBulkApply .modal-header {
      background: linear-gradient(135deg, #ff9f43 0%, #e65c00 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1.25rem 1.5rem;
    }

    #modalBulkApply .modal-body {
      padding: 1.5rem;
    }

    #modalBulkApply .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1rem 1.5rem;
      background: rgba(255, 255, 255, 0.02);
    }

    #modalBulkApply .form-control,
    #modalBulkApply .form-select {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: inherit;
      border-radius: 5px;
      transition: border-color 0.2s ease, background 0.2s ease;
    }

    #modalBulkApply .form-control:focus,
    #modalBulkApply .form-select:focus {
      background: rgba(255, 255, 255, 0.09);
      border-color: rgba(40, 199, 111, 0.6);
      box-shadow: 0 0 0 3px rgba(40, 199, 111, 0.12);
    }

    #modalBulkApply .form-select option {
      background: #1e1e2d;
      color: #cdd2e0;
    }

    /* ═══════════════════════════════════════════════════════════════
         JADWAL HARI CARD (Grid 7 Hari)
    ═══════════════════════════════════════════════════════════════ */
    .jadwal-hari-card {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 5px;
      overflow: hidden;
      transition: opacity 0.2s ease, border-color 0.2s ease;
    }

    .jadwal-hari-card.is-libur {
      opacity: 0.5;
    }

    .jadwal-hari-card.is-libur .jadwal-hari-body {
      pointer-events: none;
    }

    .jadwal-hari-header {
      background: linear-gradient(135deg, #ff9f43 0%, #e65c00 100%);
      padding: 0.75rem 1rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .jadwal-hari-header h6 {
      margin: 0;
      font-size: 0.85rem;
      font-weight: 700;
      color: #fff;
    }

    .jadwal-hari-body {
      padding: 1rem;
    }

    .jadwal-field {
      margin-bottom: 0.75rem;
    }

    .jadwal-field:last-child {
      margin-bottom: 0;
    }

    .jadwal-field label {
      font-size: 0.7rem;
      font-weight: 600;
      margin-bottom: 0.25rem;
      display: block;
      color: rgba(255, 255, 255, 0.6);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .jadwal-field input[type="time"] {
      width: 100%;
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 5px;
      color: inherit;
      padding: 0.5rem;
      font-size: 0.85rem;
      transition: border-color 0.2s ease, background 0.2s ease;
    }

    .jadwal-field input[type="time"]:focus {
      outline: none;
      background: rgba(255, 255, 255, 0.09);
      border-color: rgba(255, 159, 67, 0.6);
      box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.12);
    }

    .jadwal-field input[type="time"]:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    .jadwal-field-hint {
      font-size: 0.65rem;
      color: rgba(255, 255, 255, 0.4);
      margin-top: 0.25rem;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .jadwal-field-hint i {
      font-size: 0.7rem;
    }

    .jadwal-field-hint.--info {
      color: #ff9f43;
    }

    .jadwal-copy-btn {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 4px 10px;
      border-radius: 5px;
      font-size: 0.7rem;
      font-weight: 600;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.04);
      color: rgba(255, 255, 255, 0.6);
      cursor: pointer;
      transition: all 0.15s ease;
    }

    .jadwal-copy-btn:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      border-color: rgba(255, 255, 255, 0.2);
    }

    /* ═══════════════════════════════════════════════════════════════
         TOGGLE SWITCH
    ═══════════════════════════════════════════════════════════════ */
    .jadwal-toggle {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
    }

    .jadwal-toggle input {
      display: none;
    }

    .jadwal-toggle-slider {
      position: relative;
      width: 36px;
      height: 20px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 20px;
      transition: background 0.2s ease;
    }

    .jadwal-toggle-slider::after {
      content: '';
      position: absolute;
      top: 2px;
      left: 2px;
      width: 16px;
      height: 16px;
      background: #fff;
      border-radius: 50%;
      transition: transform 0.2s ease;
    }

    .jadwal-toggle input:checked + .jadwal-toggle-slider {
      background: #ff9f43;
    }

    .jadwal-toggle input:checked + .jadwal-toggle-slider::after {
      transform: translateX(16px);
    }

    .jadwal-toggle-label {
      font-size: 0.7rem;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.6);
    }

    /* ═══════════════════════════════════════════════════════════════
         PAGINATION
    ═══════════════════════════════════════════════════════════════ */
    .das-page-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 32px;
      height: 32px;
      padding: 0 8px;
      font-size: 0.78rem;
      font-weight: 600;
      border-radius: 5px;
      border: 1px solid rgba(255,255,255,0.08);
      background: transparent;
      color: #888;
      text-decoration: none;
      transition: all 0.18s ease;
      cursor: pointer;
      line-height: 1;
      font-family: inherit;
    }

    .das-page-btn:hover {
      background: rgba(255,255,255,0.08);
      color: #fff;
      border-color: rgba(255,255,255,0.12);
    }

    .das-page-active {
      background: #7367f0 !important;
      color: #fff !important;
      border-color: #7367f0 !important;
    }

    .das-page-dots {
      border-color: transparent;
      background: transparent;
      color: #555;
      pointer-events: none;
    }

    .page-item.disabled .das-page-btn {
      opacity: 0.35;
      pointer-events: none;
    }

    /* ═══════════════════════════════════════════════════════════════
         SEARCH & FILTER INPUTS
    ═══════════════════════════════════════════════════════════════ */
    #searchInput::placeholder { color: rgba(255,255,255,0.4); }
    #searchInput:focus {
      outline: none;
      box-shadow: none;
      background: rgba(255,255,255,0.08) !important;
      border-color: rgba(115,103,240,0.5) !important;
    }

    #perPageSelect option { background: #1a1a2e; color: #ccc; }
    #perPageSelect:focus { outline: none; box-shadow: none; }

    /* ═══════════════════════════════════════════════════════════════
         SEGMENTED TAB FILTERS
    ═══════════════════════════════════════════════════════════════ */
    .tingkat-tab-btn {
      color: rgba(255, 255, 255, 0.6) !important;
      border: none;
      box-shadow: none !important;
      font-size: 0.8rem;
      font-weight: 600;
      padding: 0.35rem 1.1rem;
      border-radius: var(--das-radius, 5px);
      transition: all 0.2s ease-in-out;
      background: transparent;
    }

    .tingkat-tab-btn:hover {
      color: #fff !important;
      background: rgba(255, 255, 255, 0.05);
    }

    .tingkat-tab-btn.active {
      background: var(--das-primary) !important;
      color: #fff !important;
      box-shadow: 0 4px 12px rgba(115, 103, 240, 0.3) !important;
    }

    /* ═══════════════════════════════════════════════════════════════
         LOADING STATE
    ═══════════════════════════════════════════════════════════════ */
    .jadwal-loading {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem;
      gap: 1rem;
    }

    .jadwal-loading-spinner {
      width: 40px;
      height: 40px;
      border: 3px solid rgba(255, 255, 255, 0.1);
      border-top-color: var(--das-info);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    .jadwal-loading-text {
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.5);
    }

    /* ═══════════════════════════════════════════════════════════════
         RESPONSIVE ADJUSTMENTS
    ═══════════════════════════════════════════════════════════════ */
    @media (max-width: 767px) {
      .jadwal-hari-card {
        margin-bottom: 1rem;
      }

      #modalJadwalKelas .modal-body {
        padding: 1rem;
        max-height: 60vh;
      }

      .jadwal-hari-header {
        padding: 0.6rem 0.75rem;
      }

      .jadwal-hari-body {
        padding: 0.75rem;
      }
    }

    @media (max-width: 575px) {
      .das-hero__actions {
        width: 100%;
      }

      .das-hero__actions .btn {
        width: 100%;
        justify-content: center;
      }
    }

    /* Orange Theme & 5px Border-Radius overrides */
    #modalJadwalKelas .btn, #modalBulkApply .btn {
      border-radius: 5px !important;
    }
    #modalJadwalKelas .btn-info, #modalBulkApply .btn-info {
      background: linear-gradient(135deg, #ff9f43 0%, #e65c00 100%) !important;
      border: none !important;
      color: #fff !important;
      box-shadow: 0 4px 12px rgba(255, 159, 67, 0.2) !important;
    }
    #modalJadwalKelas .btn-info:hover, #modalBulkApply .btn-info:hover {
      opacity: 0.9 !important;
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
            <i class="ti tabler-clock-edit text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Kelola Jam Absensi
          </div>
          <h4 class="das-hero__title text-gradient-gold">Kelola Jam Absensi</h4>
          <p class="das-hero__subtitle">Atur jam masuk dan pulang setiap kelas per hari.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <button type="button" class="btn das-btn --secondary" data-bs-toggle="modal" data-bs-target="#modalBulkApply">
          <i class="ti tabler-copy me-1"></i> Copy dari Kelas Lain
        </button>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGES --}}
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

  {{-- ═══════════════════════════════════════════════════════════════
       JAM ABSENSI GURU PENGAJAR & STAFF TATA USAHA (SEPARATE)
  ═══════════════════════════════════════════════════════════════ --}}
  <div class="row g-4 mb-4">
    {{-- PANEL 1: GURU PENGAJAR --}}
    <div class="col-12 col-xl-6">
      <div class="das-panel h-100 mb-0">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
          style="border-color:rgba(255,255,255,0.08) !important;">
          <div class="d-flex align-items-center gap-2">
            <i class="ti tabler-school text-warning fs-5"></i>
            <h6 class="das-panel__title mb-0 fw-bold">Jam Absensi Guru Pengajar</h6>
          </div>
          <span class="badge bg-warning-subtle text-warning" style="font-size: 0.65rem;">Khusus Guru</span>
        </div>
        <div class="das-panel__body py-3 px-4">
          <form id="formJamGuru" class="row g-3 align-items-end">
            @csrf
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Jam Mulai Scan</label>
              <input type="time" class="form-control" name="jam_mulai_absensi_guru"
                value="{{ $guruSettings['jam_mulai_absensi_guru'] ?? '06:00' }}">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Jam Masuk</label>
              <input type="time" class="form-control" name="jam_masuk_guru"
                value="{{ $guruSettings['jam_masuk_guru'] ?? '07:00' }}">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Batas Jam Masuk</label>
              <input type="time" class="form-control" name="jam_batas_masuk_guru"
                value="{{ $guruSettings['jam_batas_masuk_guru'] ?? '08:00' }}">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Toleransi (Menit)</label>
              <input type="number" class="form-control" name="toleransi_guru"
                value="{{ $guruSettings['toleransi_guru'] ?? '15' }}" min="0" max="60">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Jam Mulai Pulang</label>
              <input type="time" class="form-control" name="jam_mulai_pulang_guru"
                value="{{ $guruSettings['jam_mulai_pulang_guru'] ?? '14:00' }}">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Jam Pulang</label>
              <input type="time" class="form-control" name="jam_pulang_guru"
                value="{{ $guruSettings['jam_pulang_guru'] ?? '15:00' }}">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Batas Akhir Pulang</label>
              <input type="time" class="form-control" name="jam_akhir_pulang_guru"
                value="{{ $guruSettings['jam_akhir_pulang_guru'] ?? '17:00' }}">
            </div>
            <div class="col-6 col-md-3">
              <button type="submit" class="btn das-btn --warning w-100" style="border-radius: 4px;">
                <i class="ti tabler-device-floppy me-1"></i> Simpan Guru
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- PANEL 2: STAFF TATA USAHA --}}
    <div class="col-12 col-xl-6">
      <div class="das-panel h-100 mb-0">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
          style="border-color:rgba(255,255,255,0.08) !important;">
          <div class="d-flex align-items-center gap-2">
            <i class="ti tabler-id text-info fs-5"></i>
            <h6 class="das-panel__title mb-0 fw-bold">Jam Absensi Staff Tata Usaha / Tendik</h6>
          </div>
          <span class="badge bg-info-subtle text-info" style="font-size: 0.65rem;">Khusus Staff</span>
        </div>
        <div class="das-panel__body py-3 px-4">
          <form id="formJamStaff" class="row g-3 align-items-end">
            @csrf
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Jam Mulai Scan</label>
              <input type="time" class="form-control" name="jam_mulai_absensi_staff"
                value="{{ $staffSettings['jam_mulai_absensi_staff'] ?? '06:00' }}">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Jam Masuk</label>
              <input type="time" class="form-control" name="jam_masuk_staff"
                value="{{ $staffSettings['jam_masuk_staff'] ?? '07:30' }}">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Batas Jam Masuk</label>
              <input type="time" class="form-control" name="jam_batas_masuk_staff"
                value="{{ $staffSettings['jam_batas_masuk_staff'] ?? '08:30' }}">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Toleransi (Menit)</label>
              <input type="number" class="form-control" name="toleransi_staff"
                value="{{ $staffSettings['toleransi_staff'] ?? '15' }}" min="0" max="60">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Jam Mulai Pulang</label>
              <input type="time" class="form-control" name="jam_mulai_pulang_staff"
                value="{{ $staffSettings['jam_mulai_pulang_staff'] ?? '15:00' }}">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Jam Pulang</label>
              <input type="time" class="form-control" name="jam_pulang_staff"
                value="{{ $staffSettings['jam_pulang_staff'] ?? '16:00' }}">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label text-white-50 small fw-bold">Batas Akhir Pulang</label>
              <input type="time" class="form-control" name="jam_akhir_pulang_staff"
                value="{{ $staffSettings['jam_akhir_pulang_staff'] ?? '18:00' }}">
            </div>
            <div class="col-6 col-md-3">
              <button type="submit" class="btn das-btn --info w-100" style="border-radius: 4px;">
                <i class="ti tabler-device-floppy me-1"></i> Simpan Staff
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- TABLE CARD --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-none d-lg-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Kelas
      </h6>

      <!-- Filter Segmented Tab (Desktop & Tablet) -->
      <div class="d-none d-md-flex align-items-center">
        <div class="tingkat-filter-pill p-1" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--das-radius, 5px); display: flex; gap: 4px; backdrop-filter: blur(8px);">
          <button type="button" class="btn tingkat-tab-btn {{ $tingkat === null || $tingkat === '' ? 'active' : '' }}" data-tingkat="">
            Semua
          </button>
          @foreach ($tingkatOptions as $opt)
            <button type="button" class="btn tingkat-tab-btn {{ $tingkat === $opt ? 'active' : '' }}" data-tingkat="{{ $opt }}">
              Tingkat {{ $opt }}
            </button>
          @endforeach
        </div>
      </div>

      <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-3 flex-grow-1 flex-md-grow-0 w-100 w-md-auto">
        <div class="position-relative flex-grow-1 flex-md-grow-0" style="max-width:300px;">
          <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size:0.85rem; pointer-events:none;"></i>
          <input type="text" id="searchInput" class="form-control border-0 text-white" placeholder="Cari nama atau jurusan..." style="background: rgba(255,255,255,0.05); height:38px; padding-left:2.2rem; font-size:0.85rem;">
        </div>

        <!-- Filter Dropdown (Mobile fallback) -->
        <select id="tingkatSelect" class="form-select border-0 text-white w-auto d-md-none" style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="" {{ $tingkat === null || $tingkat === '' ? 'selected' : '' }}>Semua Tingkat</option>
          @foreach ($tingkatOptions as $opt)
            <option value="{{ $opt }}" {{ $tingkat == $opt ? 'selected' : '' }}>Tingkat {{ $opt }}</option>
          @endforeach
        </select>

        <select id="perPageSelect" class="form-select border-0 text-white w-auto" style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>

        <span class="das-chip --info d-none d-sm-inline-flex" id="totalKelasChip">{{ $kelas->total() }} Kelas</span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="kelasTableContainer">
        @include('admin.jadwal-absensi.table')
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════════════
       MODAL: KELOLA JADWAL ABSENSI (Grid 7 Hari)
  ═══════════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalJadwalKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width:900px;">
      <div class="modal-content shadow-lg">

        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header"
              style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);">
              <i class="ti tabler-clock-edit fs-5" style="color:#ffffff !important;"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Kelola Jadwal Absensi</h5>
              <small id="modalJadwalKelasName" class="text-white-50">—</small>
            </div>
          </div>
        </div>

        <div class="modal-body">
          <input type="hidden" id="jadwal_kelas_id">

          {{-- Loading State --}}
          <div id="jadwalLoading" class="jadwal-loading">
            <div class="jadwal-loading-spinner"></div>
            <span class="jadwal-loading-text">Memuat jadwal...</span>
          </div>

          {{-- Grid 7 Hari (diisi via JS) --}}
          <div class="row g-3" id="jadwalGrid" style="display:none;"></div>
        </div>

        <div class="modal-footer gap-2">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Batal
          </button>
          <button type="button" class="btn btn-info fw-semibold px-4" onclick="simpanSemuaJadwal()" id="btnSimpanJadwal">
            <i class="ti tabler-device-floppy me-1"></i> Simpan Semua
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════════════
       MODAL: BULK APPLY (Copy dari Kelas Lain)
  ═══════════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalBulkApply" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:540px;">
      <div class="modal-content shadow-lg">

        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header"
              style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);">
              <i class="ti tabler-copy fs-5" style="color:#ffffff !important;"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Copy Jadwal dari Kelas Lain</h5>
              <small class="text-white-50">Salin jadwal dari kelas sumber ke kelas tujuan.</small>
            </div>
          </div>
        </div>

        <div class="modal-body">
          {{-- Kelas Sumber --}}
          <div class="mb-3">
            <label class="form-label fw-semibold small" for="bulkSourceKelas">
              <i class="ti tabler-source-code me-1 text-warning"></i> Kelas Sumber
            </label>
            <select id="bulkSourceKelas" class="form-select">
              <option value="">Pilih kelas sumber</option>
              @foreach($allKelas as $item)
                <option value="{{ $item->id }}">{{ $item->nama }} — {{ $item->tingkat }}</option>
              @endforeach
            </select>
          </div>

          {{-- Kelas Tujuan --}}
          <div class="mb-3">
            <label class="form-label fw-semibold small" for="bulkTargetKelas">
              <i class="ti tabler-target me-1 text-warning"></i> Kelas Tujuan
            </label>
            <select id="bulkTargetKelas" class="form-select" multiple style="height:150px;">
              @foreach($allKelas as $item)
                <option value="{{ $item->id }}">{{ $item->nama }} — {{ $item->tingkat }}</option>
              @endforeach
            </select>
            <small class="text-white-50">Tahan Ctrl/Cmd untuk memilih beberapa kelas.</small>
          </div>

          {{-- Error Container --}}
          <div id="bulkErrorContainer" style="display:none;">
            <div class="alert alert-danger d-flex align-items-center gap-2 border-0 mb-0" style="border-radius:8px;">
              <i class="ti tabler-alert-circle text-danger fs-5"></i>
              <span id="bulkErrorText" class="small"></span>
            </div>
          </div>
        </div>

        <div class="modal-footer gap-2">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Batal
          </button>
          <button type="button" class="btn btn-info fw-semibold px-4" onclick="executeBulkApply()" id="btnBulkApply">
            <i class="ti tabler-copy me-1"></i> Terapkan
          </button>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    // ═══════════════════════════════════════════════════════════════
    // CONSTANTS
    // ═══════════════════════════════════════════════════════════════
    const HARI_LABELS = {
      senin: 'Senin',
      selasa: 'Selasa',
      rabu: 'Rabu',
      kamis: 'Kamis',
      jumat: 'Jumat',
      sabtu: 'Sabtu',
      minggu: 'Minggu'
    };

    const HARI_ICONS = {
      senin: 'tabler-calendar',
      selasa: 'tabler-calendar',
      rabu: 'tabler-calendar',
      kamis: 'tabler-calendar',
      jumat: 'tabler-calendar',
      sabtu: 'tabler-calendar-off',
      minggu: 'tabler-calendar-off'
    };

    const HARI_COLORS = {
      senin: 'info',
      selasa: 'primary',
      rabu: 'success',
      kamis: 'warning',
      jumat: 'danger',
      sabtu: 'secondary',
      minggu: 'secondary'
    };

    const GLOBAL_JADWAL = @json($globalJadwal ?? \App\Helpers\JadwalAbsensiHelper::getJadwalForKelas(0));

    // ═══════════════════════════════════════════════════════════════
    // SEARCH & PAGINATION AJAX
    // ═══════════════════════════════════════════════════════════════
    (function() {
      const container = document.getElementById('kelasTableContainer');
      const searchInput = document.getElementById('searchInput');
      const tingkatSelect = document.getElementById('tingkatSelect');
      const perPageSelect = document.getElementById('perPageSelect');
      const tabButtons = document.querySelectorAll('.tingkat-tab-btn');
      let selectedTingkat = '{{ $tingkat ?? "" }}';
      let searchTimeout;

      function fetchData(page = 1) {
        const search = encodeURIComponent(searchInput.value || '');
        const perPage = perPageSelect.value || 10;
        const tingkat = encodeURIComponent(selectedTingkat);
        const url = `{{ route('admin.jadwal-absensi.index') }}?page=${page}&search=${search}&per_page=${perPage}&tingkat=${tingkat}`;

        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(res => res.text())
          .then(html => {
            container.innerHTML = html;
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';

            // Sync total count badge dynamically
            const tableWrapper = container.querySelector('.table-responsive');
            if (tableWrapper && tableWrapper.dataset.total !== undefined) {
              const totalChip = document.getElementById('totalKelasChip');
              if (totalChip) {
                totalChip.textContent = `${tableWrapper.dataset.total} Kelas`;
              }
            }
          })
          .catch(err => {
            console.error('Fetch error:', err);
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
          });
      }

      // Sync active state of UI components (tabs vs mobile select)
      function syncTingkatUI(val) {
        selectedTingkat = val;
        tingkatSelect.value = val;
        
        tabButtons.forEach(btn => {
          if ((btn.dataset.tingkat || '') === val) {
            btn.classList.add('active');
          } else {
            btn.classList.remove('active');
          }
        });
      }

      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchData(1), 450);
      });

      // Desktop/Tablet Tab Buttons Click handler
      tabButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const targetVal = this.dataset.tingkat || '';
          syncTingkatUI(targetVal);
          fetchData(1);
        });
      });

      // Mobile Select Dropdown Change handler
      tingkatSelect.addEventListener('change', function() {
        syncTingkatUI(this.value);
        fetchData(1);
      });

      perPageSelect.addEventListener('change', function() {
        fetchData(1);
      });

      container.addEventListener('click', function(e) {
        const link = e.target.closest('a.das-page-btn');
        if (link) {
          e.preventDefault();
          const page = link.dataset.page || new URL(link.href).searchParams.get('page') || 1;
          fetchData(page);
        }
      });
    })();
    // ─── End Search & Pagination ─────────────────────────────────────────────

    // ═══════════════════════════════════════════════════════════════
    // MODAL: KELOLA JADWAL ABSENSI
    // ═══════════════════════════════════════════════════════════════
    let currentJadwalData = {};

    function openJadwalModal(kelasId, kelasNama) {
      // Set hidden input and modal title
      document.getElementById('jadwal_kelas_id').value = kelasId;
      document.getElementById('modalJadwalKelasName').textContent = kelasNama;

      // Show loading, hide grid
      document.getElementById('jadwalLoading').style.display = 'flex';
      document.getElementById('jadwalGrid').style.display = 'none';

      // Open modal
      const modal = new bootstrap.Modal(document.getElementById('modalJadwalKelas'));
      modal.show();

      // Fetch jadwal data
      fetch(`{{ url('admin/jadwal-absensi') }}/${kelasId}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          currentJadwalData = data.data.jadwal;
          renderJadwalGrid(data.data.jadwal);

          // Hide loading, show grid
          document.getElementById('jadwalLoading').style.display = 'none';
          document.getElementById('jadwalGrid').style.display = 'flex';
        } else {
          showJadwalError('Gagal memuat jadwal: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(err => {
        console.error('Fetch jadwal error:', err);
        showJadwalError('Terjadi kesalahan saat memuat jadwal.');
      });
    }

    function renderJadwalGrid(jadwal) {
      const grid = document.getElementById('jadwalGrid');
      grid.innerHTML = '';

      const hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

      hariList.forEach(hari => {
        const data = jadwal[hari] || {};
        const isLibur = data.is_libur || false;
        const color = HARI_COLORS[hari];

        const card = document.createElement('div');
        card.className = 'col-md-6 col-lg-4';
        card.innerHTML = `
          <div class="jadwal-hari-card ${isLibur ? 'is-libur' : ''}" id="card-${hari}" data-hari="${hari}">
            <div class="jadwal-hari-header">
              <h6>
                <i class="ti ${HARI_ICONS[hari]} me-1" style="font-size:0.85rem; color:#ffffff !important;"></i>
                ${HARI_LABELS[hari]}
              </h6>
              <label class="jadwal-toggle">
                <input type="checkbox" class="libur-toggle" data-hari="${hari}" 
                  ${isLibur ? 'checked' : ''} 
                  onchange="toggleLibur('${hari}', this.checked)">
                <span class="jadwal-toggle-slider"></span>
                <span class="jadwal-toggle-label">Libur</span>
              </label>
            </div>
            <div class="jadwal-hari-body">
              <div class="jadwal-field">
                <label>Jam Mulai Absensi</label>
                <input type="time" class="jam-input" data-hari="${hari}" data-field="jam_mulai_absensi" 
                  value="${data.jam_mulai_absensi || ''}" ${isLibur ? 'disabled' : ''}>
                <div class="jadwal-field-hint --info">
                  <i class="ti tabler-info-circle"></i>
                  Kosongkan untuk menggunakan global (${GLOBAL_JADWAL.jam_mulai_absensi || '06:00'})
                </div>
              </div>
              <div class="jadwal-field">
                <label>Jam Masuk</label>
                <input type="time" class="jam-input" data-hari="${hari}" data-field="jam_masuk" 
                  value="${data.jam_masuk || ''}" ${isLibur ? 'disabled' : ''}>
                <div class="jadwal-field-hint --info">
                  <i class="ti tabler-info-circle"></i>
                  Kosongkan untuk menggunakan global (${GLOBAL_JADWAL.jam_masuk || '07:00'})
                </div>
              </div>
              <div class="jadwal-field">
                <label>Batas Jam Absensi Masuk</label>
                <input type="time" class="jam-input" data-hari="${hari}" data-field="batas_jam_masuk" 
                  value="${data.batas_jam_masuk || ''}" ${isLibur ? 'disabled' : ''}>
                <div class="jadwal-field-hint --info">
                  <i class="ti tabler-info-circle"></i>
                  Kosongkan untuk menggunakan global (${GLOBAL_JADWAL.batas_jam_masuk || '09:00'})
                </div>
              </div>
              <div class="jadwal-field">
                <label>Jam Pulang</label>
                <input type="time" class="jam-input" data-hari="${hari}" data-field="jam_pulang" 
                  value="${data.jam_pulang || ''}" ${isLibur ? 'disabled' : ''}>
                <div class="jadwal-field-hint --info">
                  <i class="ti tabler-info-circle"></i>
                  Kosongkan untuk menggunakan global (${GLOBAL_JADWAL.jam_pulang || '15:00'})
                </div>
              </div>
              <div class="jadwal-field">
                <label>Jam Akhir Pulang</label>
                <input type="time" class="jam-input" data-hari="${hari}" data-field="jam_akhir_pulang" 
                  value="${data.jam_akhir_pulang || ''}" ${isLibur ? 'disabled' : ''}>
                <div class="jadwal-field-hint --info">
                  <i class="ti tabler-info-circle"></i>
                  Kosongkan untuk menggunakan global (${GLOBAL_JADWAL.jam_akhir_pulang || '17:00'})
                </div>
              </div>
              <div class="mt-3 pt-2" style="border-top: 1px solid rgba(255,255,255,0.08);">
                <button type="button" class="jadwal-copy-btn" onclick="copyJadwalHari('${hari}')">
                  <i class="ti tabler-copy"></i> Copy ke Semua Hari
                </button>
              </div>
            </div>
          </div>
        `;

        grid.appendChild(card);
      });
    }

    function toggleLibur(hari, isLibur) {
      const card = document.getElementById(`card-${hari}`);
      const inputs = card.querySelectorAll('.jam-input');

      if (isLibur) {
        card.classList.add('is-libur');
        inputs.forEach(input => input.disabled = true);
      } else {
        card.classList.remove('is-libur');
        inputs.forEach(input => input.disabled = false);
      }
    }

    function copyJadwalHari(sourceHari) {
      const sourceData = collectHariData(sourceHari);

      const hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

      hariList.forEach(hari => {
        if (hari === sourceHari) return;

        // Set values
        const inputs = document.querySelectorAll(`#card-${hari} .jam-input`);
        inputs.forEach(input => {
          const field = input.dataset.field;
          input.value = sourceData[field] || '';
        });

        // Set libur toggle
        const liburToggle = document.querySelector(`#card-${hari} .libur-toggle`);
        liburToggle.checked = sourceData.is_libur;
        toggleLibur(hari, sourceData.is_libur);
      });

      // Show success feedback
      showToast(`Jadwal ${HARI_LABELS[sourceHari]} berhasil disalin ke semua hari lainnya.`, 'success');
    }

    function collectHariData(hari) {
      const inputs = document.querySelectorAll(`#card-${hari} .jam-input`);
      const liburToggle = document.querySelector(`#card-${hari} .libur-toggle`);

      const data = {
        hari: hari,
        is_libur: liburToggle.checked,
        jam_mulai_absensi: null,
        jam_masuk: null,
        batas_jam_masuk: null,
        jam_pulang: null,
        jam_akhir_pulang: null
      };

      inputs.forEach(input => {
        const field = input.dataset.field;
        data[field] = input.value || null;
      });

      return data;
    }

    function simpanSemuaJadwal() {
      const kelasId = document.getElementById('jadwal_kelas_id').value;
      const btn = document.getElementById('btnSimpanJadwal');
      const originalText = btn.innerHTML;

      // Collect all data
      const jadwal = [];
      const hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

      hariList.forEach(hari => {
        jadwal.push(collectHariData(hari));
      });

      // Validate time sequences
      for (const item of jadwal) {
        if (!item.is_libur) {
          if (item.jam_mulai_absensi && item.jam_masuk && item.jam_mulai_absensi > item.jam_masuk) {
            showToast(`Error: Jam Mulai Absensi ${HARI_LABELS[item.hari]} harus lebih awal dari Jam Masuk.`, 'error');
            return;
          }
          if (item.batas_jam_masuk && item.jam_masuk && item.batas_jam_masuk < item.jam_masuk) {
            showToast(`Error: Batas Jam Absensi Masuk ${HARI_LABELS[item.hari]} harus lebih lambat atau sama dengan Jam Masuk.`, 'error');
            return;
          }
          if (item.batas_jam_masuk && item.jam_mulai_absensi && item.batas_jam_masuk < item.jam_mulai_absensi) {
            showToast(`Error: Batas Jam Absensi Masuk ${HARI_LABELS[item.hari]} harus lebih lambat atau sama dengan Jam Mulai Absensi.`, 'error');
            return;
          }
          if (item.jam_pulang && item.jam_akhir_pulang && item.jam_pulang > item.jam_akhir_pulang) {
            showToast(`Error: Jam Pulang ${HARI_LABELS[item.hari]} harus lebih awal dari Jam Akhir Pulang.`, 'error');
            return;
          }
        }
      }

      // Disable button and show loading
      btn.disabled = true;
      btn.innerHTML = '<i class="ti tabler-loader ti-spin me-1"></i> Menyimpan...';

      // POST data
      fetch('{{ route("admin.jadwal-absensi.store-all") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          kelas_id: kelasId,
          jadwal: jadwal
        })
      })
      .then(res => res.json())
      .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (data.success) {
          showToast(data.message || 'Jadwal berhasil disimpan.', 'success');
          
          // Close modal
          bootstrap.Modal.getInstance(document.getElementById('modalJadwalKelas')).hide();

          // Reload table
          setTimeout(() => {
            document.getElementById('searchInput').dispatchEvent(new Event('input'));
          }, 300);
        } else {
          showToast(data.message || 'Gagal menyimpan jadwal.', 'error');
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        console.error('Save jadwal error:', err);
        showToast('Terjadi kesalahan saat menyimpan jadwal.', 'error');
      });
    }

    function showJadwalError(msg) {
      document.getElementById('jadwalLoading').innerHTML = `
        <div class="text-center">
          <i class="ti tabler-alert-circle text-danger" style="font-size:2.5rem;"></i>
          <p class="text-white-50 mt-2 mb-0">${msg}</p>
        </div>
      `;
    }

    // ═══════════════════════════════════════════════════════════════
    // MODAL: BULK APPLY
    // ═══════════════════════════════════════════════════════════════
    function executeBulkApply() {
      const sourceId = document.getElementById('bulkSourceKelas').value;
      const targetSelect = document.getElementById('bulkTargetKelas');
      const targetIds = Array.from(targetSelect.selectedOptions).map(opt => opt.value);
      const errorContainer = document.getElementById('bulkErrorContainer');
      const errorText = document.getElementById('bulkErrorText');
      const btn = document.getElementById('btnBulkApply');
      const originalText = btn.innerHTML;

      // Hide previous errors
      errorContainer.style.display = 'none';

      // Validation
      if (!sourceId) {
        errorText.textContent = 'Pilih kelas sumber terlebih dahulu.';
        errorContainer.style.display = 'block';
        return;
      }

      if (targetIds.length === 0) {
        errorText.textContent = 'Pilih minimal 1 kelas tujuan.';
        errorContainer.style.display = 'block';
        return;
      }

      if (targetIds.includes(sourceId)) {
        errorText.textContent = 'Kelas sumber dan tujuan tidak boleh sama.';
        errorContainer.style.display = 'block';
        return;
      }

      // Confirm
      if (!confirm(`Yakin ingin menyalin jadwal dari kelas sumber ke ${targetIds.length} kelas tujuan?`)) {
        return;
      }

      // Disable button and show loading
      btn.disabled = true;
      btn.innerHTML = '<i class="ti tabler-loader ti-spin me-1"></i> Memproses...';

      // POST data
      fetch('{{ route("admin.jadwal-absensi.bulk-apply") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          source_kelas_id: sourceId,
          target_kelas_ids: targetIds
        })
      })
      .then(res => res.json())
      .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (data.success) {
          showToast(data.message || 'Jadwal berhasil disalin.', 'success');
          
          // Close modal
          bootstrap.Modal.getInstance(document.getElementById('modalBulkApply')).hide();

          // Reload table
          setTimeout(() => {
            document.getElementById('searchInput').dispatchEvent(new Event('input'));
          }, 300);
        } else {
          errorText.textContent = data.message || 'Gagal menyalin jadwal.';
          errorContainer.style.display = 'block';
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        console.error('Bulk apply error:', err);
        errorText.textContent = 'Terjadi kesalahan saat memproses.';
        errorContainer.style.display = 'block';
      });
    }

    // Reset bulk apply modal saat ditutup
    document.getElementById('modalBulkApply').addEventListener('hidden.bs.modal', function() {
      document.getElementById('bulkSourceKelas').value = '';
      document.getElementById('bulkTargetKelas').selectedIndex = -1;
      document.getElementById('bulkErrorContainer').style.display = 'none';
    });

    // ═══════════════════════════════════════════════════════════════
    // TOAST NOTIFICATION
    // ═══════════════════════════════════════════════════════════════
    function showToast(message, type = 'success') {
      // Create toast container if not exists
      let toastContainer = document.getElementById('toastContainer');
      if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
      }

      const toastId = 'toast-' + Date.now();
      const bgColor = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-info');
      const icon = type === 'success' ? 'tabler-circle-check' : (type === 'error' ? 'tabler-alert-circle' : 'tabler-info-circle');

      const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgColor} border-0" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              <i class="ti ${icon} me-2"></i> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      `;

      toastContainer.insertAdjacentHTML('beforeend', toastHtml);

      const toastElement = document.getElementById(toastId);
      const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
      toast.show();

      // Remove toast element after hidden
      toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
      });
    }

    // ═══════════════════════════════════════════════════════════════
    // RESET MODAL SAAT DITUTUP
    // ═══════════════════════════════════════════════════════════════
    document.getElementById('modalJadwalKelas').addEventListener('hidden.bs.modal', function() {
      document.getElementById('jadwal_kelas_id').value = '';
      document.getElementById('jadwalGrid').innerHTML = '';
      document.getElementById('jadwalGrid').style.display = 'none';
      document.getElementById('jadwalLoading').style.display = 'flex';
      document.getElementById('jadwalLoading').innerHTML = `
        <div class="jadwal-loading-spinner"></div>
        <span class="jadwal-loading-text">Memuat jadwal...</span>
      `;
      currentJadwalData = {};
    });

    // ═══════════════════════════════════════════════════════════════
    // FORM JAM GURU: AJAX SUBMIT
    // ═══════════════════════════════════════════════════════════════
    document.getElementById('formJamGuru').addEventListener('submit', async function(e) {
      e.preventDefault();
      const form = this;
      const btn = form.querySelector('button[type="submit"]');
      const origHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="ti tabler-loader me-1"></i> Menyimpan...';

      try {
        const fd = new FormData(form);
        const res = await fetch('{{ route("admin.jadwal-absensi.save-guru-settings") }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': fd.get('_token'),
            'Accept': 'application/json',
          },
          body: fd,
        });
        const json = await res.json();
        if (json.success) {
          showToast(json.message, 'success');
        } else {
          showToast(json.message || 'Gagal menyimpan.', 'error');
        }
      } catch (err) {
        showToast('Terjadi kesalahan jaringan.', 'error');
        console.error(err);
      } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
      }
    });

    // ═══════════════════════════════════════════════════════════════
    // FORM JAM STAFF: AJAX SUBMIT
    // ═══════════════════════════════════════════════════════════════
    document.getElementById('formJamStaff').addEventListener('submit', async function(e) {
      e.preventDefault();
      const form = this;
      const btn = form.querySelector('button[type="submit"]');
      const origHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="ti tabler-loader me-1"></i> Menyimpan...';

      try {
        const fd = new FormData(form);
        const res = await fetch('{{ route("admin.jadwal-absensi.save-staff-settings") }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': fd.get('_token'),
            'Accept': 'application/json',
          },
          body: fd,
        });
        const json = await res.json();
        if (json.success) {
          showToast(json.message, 'success');
        } else {
          showToast(json.message || 'Gagal menyimpan.', 'error');
        }
      } catch (err) {
        showToast('Terjadi kesalahan jaringan.', 'error');
        console.error(err);
      } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
      }
    });
  </script>
@endsection
