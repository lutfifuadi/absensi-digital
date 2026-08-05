@extends('layouts/layoutMaster')

@section('title', 'Kelola Hari Libur')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/animate-css/animate.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection

@section('page-style')
<style>
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  .spinner {
    animation: spin 0.8s linear infinite;
    display: inline-block;
  }

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
    --das-danger-soft: rgba(234, 84, 85, 0.12);
    --das-surface: rgba(15, 23, 42, 0.4);
    --das-surface-hover: rgba(30, 41, 59, 0.6);
    --das-border: rgba(255, 255, 255, 0.06);
    --das-border-hover: rgba(255, 255, 255, 0.12);
    --das-radius: 5px;
  }

  /* HERO */
  .das-hero { position: relative; border-radius: var(--das-radius); overflow: hidden; margin-bottom: 2rem; }
  .das-hero__bg { position: absolute; inset: 0; background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%); z-index: 0; }
  .das-hero__glass { position: absolute; inset: 0; background: radial-gradient(circle at top right, rgba(115,103,240,.15), transparent 40%); z-index: 1; }
  .das-hero__grid-lines { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 40px 40px; z-index: 1; }
  .das-hero__inner { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; padding: 2.5rem; gap: 1.5rem; flex-wrap: wrap; }
  .das-hero__identity { display: flex; align-items: center; gap: 1.25rem; }
  .das-hero__icon { width: 64px; height: 64px; background: rgba(115,103,240,.2); border: 1px solid rgba(115,103,240,.3); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: #a5a2f7; }
  .das-hero__title { font-size: 1.5rem; font-weight: 800; color: white; margin: 0 0 4px; }
  .das-hero__welcome { margin: 0; font-size: .88rem; color: rgba(255,255,255,.6); }

  /* BUTTONS */
  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #aaa !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }
  .das-btn--sm { padding: .35rem .7rem; font-size: .72rem; }

  /* PANEL */
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); flex-wrap: wrap; gap: .75rem; }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
  .das-panel__body { padding: 1.5rem; }

  /* CHIP */
  .das-chip { display: inline-flex; align-items: center; font-size: .65rem; font-weight: 700; padding: 2px 10px; border-radius: 5px; text-transform: uppercase; letter-spacing: .5px; }
  .das-chip--danger  { background: var(--das-danger-soft);  color: var(--das-danger); }
  .das-chip--info    { background: var(--das-info-soft);    color: var(--das-info); }
  .das-chip--primary { background: var(--das-primary-soft); color: var(--das-primary); }
  .das-chip--success { background: var(--das-success-soft); color: var(--das-success); }

  /* TABLE */
  .das-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
  .das-table thead th { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #555; padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); background: rgba(255,255,255,.02); }
  .das-table tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); color: #ccc; vertical-align: middle; transition: background .15s ease; }
  .das-table tbody tr:hover td { background: var(--das-surface-hover); }
  .das-table tbody tr:last-child td { border-bottom: none; }

  /* ACTION BUTTONS */
  .das-table-btn { width: 30px; height: 30px; border-radius: 5px; border: 1px solid var(--das-border); background: transparent; color: #666; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; cursor: pointer; }
  .das-table-btn:hover { background: var(--das-surface-hover); color: white; transform: translateY(-2px); }
  .das-table-btn--danger:hover { color: var(--das-danger); border-color: var(--das-danger); }

  /* FORM ELEMENTS */
  .das-form-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #777; margin-bottom: .5rem; display: block; }
  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #ddd !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control::placeholder { color: rgba(255,255,255,.2) !important; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; box-shadow: none !important; outline: none !important; color: white !important; }
  .das-form-text { font-size: .73rem; color: #555; margin-top: .35rem; }

  /* SELECT DARK */
  .das-select { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; color: #ddd !important; border-radius: var(--das-radius) !important; }
  .das-select:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; box-shadow: none !important; }
  .das-select option { background: #1a1a2e; color: #ccc; }

  /* SEGMENTED SWITCH */
  .segmented-switch { display: flex; background: rgba(255,255,255,.04); border: 1px solid var(--das-border); border-radius: 5px; padding: 3px; gap: 4px; }
  .segmented-switch input[type="radio"] { display: none; }
  .segmented-switch label { flex: 1; text-align: center; font-size: .75rem; font-weight: 600; padding: 6px 10px; border-radius: 4px; cursor: pointer; color: #888; transition: all .2s ease; margin: 0; }
  .segmented-switch input[type="radio"]:checked + label { background: var(--das-primary); color: #fff; box-shadow: 0 2px 6px rgba(115,103,240,.4); }

  /* CAKUPAN CARD GRID */
  .cakupan-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
  .cakupan-card { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; padding: 10px 6px; border-radius: 5px; border: 1px solid var(--das-border); background: rgba(255, 255, 255, 0.03); cursor: pointer; transition: all 0.2s ease; text-align: center; margin: 0; }
  .cakupan-card:hover { border-color: rgba(115, 103, 240, 0.4); background: rgba(115, 103, 240, 0.08); transform: translateY(-2px); }
  .cakupan-card input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
  .cakupan-card__icon { font-size: 1.25rem; color: #888; transition: all 0.2s ease; }
  .cakupan-card__title { font-size: 0.72rem; font-weight: 700; color: #ccc; line-height: 1.2; }
  .cakupan-card:has(input[type="radio"]:checked) { border-color: var(--das-primary); background: rgba(115, 103, 240, 0.18); box-shadow: 0 4px 14px rgba(115, 103, 240, 0.3); transform: translateY(-2px); }
  .cakupan-card:has(input[type="radio"]:checked) .cakupan-card__icon { color: #a5a2f7; transform: scale(1.1); }
  /* TINGKAT CHECKBOX GROUP */
  .tingkat-checkbox-group { display: flex; gap: 8px; }
  .tingkat-chip { flex: 1; position: relative; display: flex; align-items: center; justify-content: center; padding: 8px 10px; border-radius: 5px; border: 1px solid var(--das-border); background: rgba(255, 255, 255, 0.03); cursor: pointer; transition: all 0.2s ease; margin: 0; text-align: center; }
  .tingkat-chip input[type="checkbox"] { position: absolute; opacity: 0; width: 0; height: 0; }
  .tingkat-chip__text { font-size: 0.78rem; font-weight: 700; color: #aaa; transition: all 0.2s ease; }
  .tingkat-chip:hover { border-color: rgba(115, 103, 240, 0.4); background: rgba(115, 103, 240, 0.08); }
  .tingkat-chip:has(input[type="checkbox"]:checked) { border-color: var(--das-primary); background: rgba(115, 103, 240, 0.18); box-shadow: 0 2px 8px rgba(115, 103, 240, 0.3); }
  .tingkat-chip:has(input[type="checkbox"]:checked) .tingkat-chip__text { color: #fff; }

  /* LIVE SEARCH & MULTI CHIPS FOR KELAS */
  .set-input-group { position: relative; display: flex; align-items: center; width: 100%; margin-top: 0.25rem; }
  .set-input-prefix { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.4); z-index: 5; pointer-events: none; font-size: 1rem; }
  .set-input-group:focus-within .set-input-prefix { color: var(--das-primary); }
  .set-input-search { width: 100% !important; background: rgba(255, 255, 255, 0.04) !important; border: 1px solid var(--das-border) !important; color: #fff !important; font-size: 0.85rem !important; border-radius: 5px !important; padding: 0.6rem 0.9rem 0.6rem 2.4rem !important; transition: all 0.2s; }
  .set-input-search:focus { background: rgba(255, 255, 255, 0.07) !important; border-color: rgba(115,103,240,.6) !important; outline: none; }
  .set-input-search::placeholder { color: rgba(255,255,255,.3) !important; }

  .kelas-search-results { max-height: 200px; overflow-y: auto; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 5px; margin-top: 0.5rem; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); box-shadow: 0 8px 20px rgba(0,0,0,0.4); }
  .kelas-search-results:empty { display: none; }
  .kelas-result-item { display: flex; align-items: center; justify-content: space-between; padding: 0.55rem 0.85rem; cursor: pointer; border-bottom: 1px solid rgba(255, 255, 255, 0.04); transition: all 0.15s ease; font-size: 0.8rem; color: #ccc; }
  .kelas-result-item:hover { background: rgba(115, 103, 240, 0.12); color: #fff; }
  .kelas-result-item.is-selected { background: rgba(40, 199, 111, 0.12); border-left: 3px solid var(--das-success); color: #fff; }

  .selected-chip-wrap { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.6rem; min-height: 0; }
  .selected-chip { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.65rem; border-radius: 4px; background: rgba(115, 103, 240, 0.15); border: 1px solid rgba(115, 103, 240, 0.3); color: #c8c4f8; font-size: 0.75rem; font-weight: 600; }
  .selected-chip .chip-remove { cursor: pointer; opacity: 0.7; font-size: 0.75rem; padding: 1px 4px; border-radius: 3px; background: rgba(255, 255, 255, 0.1); transition: all 0.15s; }
  .selected-chip .chip-remove:hover { opacity: 1; color: #ff4d4f; background: rgba(255, 77, 79, 0.2); }

  .quick-action-btn-group { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 0.5rem; }
  .quick-btn { font-size: 0.68rem; padding: 2px 8px; border-radius: 4px; border: 1px solid var(--das-border); background: rgba(255,255,255,0.03); color: #aaa; cursor: pointer; transition: all 0.15s; }
  .quick-btn:hover { background: rgba(115, 103, 240, 0.15); color: #fff; border-color: rgba(115, 103, 240, 0.3); }

  /* MODAL */
  .das-modal { background: #1a1a2e !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; overflow: hidden; }
  .das-modal-head { border-bottom: 1px solid var(--das-border); background: rgba(234,84,85,.05); padding: 1.25rem; }
  .das-modal-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
  .das-modal-body { padding: 1.5rem; }

  /* INFO BOX */
  .das-info-box { background: rgba(0,207,232,.05); border: 1px solid rgba(0,207,232,.12); border-radius: var(--das-radius); padding: .9rem 1rem; }

  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }
</style>
@endsection

@section('content')

  {{-- ── HERO HEADER ────────────────────────────────── --}}
  <div class="das-hero slide-in-up">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>
    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__icon">
          <i class="ti tabler-calendar-plus"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.6;">
              <li class="breadcrumb-item text-white">Monitoring &amp; Kalender</li>
              <li class="breadcrumb-item active text-white opacity-100">Kelola Hari Libur</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">Kelola Hari Libur</h4>
          <p class="das-hero__welcome">Konfigurasi hari libur sekolah dengan dukungan rentang tanggal &amp; multi-select kelas.</p>
        </div>
      </div>
      <div>
        <a href="{{ route('admin.kalender-absensi') }}" class="das-btn das-btn--ghost">
          <i class="ti tabler-calendar-stats me-1"></i> Lihat Kalender
        </a>
      </div>
    </div>
  </div>

  {{-- ── FLASH MESSAGES ──────────────────────────────── --}}
  @foreach (['success', 'error'] as $msg)
    @if (session($msg))
      <div class="alert alert-{{ $msg === 'success' ? 'success' : 'danger' }} alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 slide-in-up"
           role="alert" style="border-radius:5px;background:rgba(0,0,0,.3);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.08)!important;">
        <i class="ti tabler-{{ $msg === 'success' ? 'circle-check' : 'alert-circle' }} fs-5 text-{{ $msg === 'success' ? 'success' : 'danger' }}"></i>
        <div class="text-white small fw-medium">{{ session($msg) }}</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach

  <div class="row g-4 slide-in-up">

    {{-- ── PANEL FORM TAMBAH ────────────────────────── --}}
    <div class="col-md-5">
      <div class="das-panel h-100">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot" style="background:var(--das-primary);box-shadow:0 0 6px var(--das-primary);"></span>
            Tambah Libur Sekolah
          </div>
        </div>
        <div class="das-panel__body">
          <form action="{{ route('admin.holidays.store') }}" method="POST" id="formHoliday">
            @csrf

            {{-- OPSI TIPE TANGGAL (SINGLE vs RANGE) --}}
            <div class="mb-3">
              <label class="das-form-label">Mode Tanggal <span class="text-danger">*</span></label>
              <div class="segmented-switch mb-2">
                <input type="radio" name="tipe_tanggal" id="tipe_single" value="single" @checked(old('tipe_tanggal', 'single') === 'single')>
                <label for="tipe_single"><i class="ti tabler-calendar-event me-1"></i> 1 Hari</label>

                <input type="radio" name="tipe_tanggal" id="tipe_range" value="range" @checked(old('tipe_tanggal') === 'range')>
                <label for="tipe_range"><i class="ti tabler-calendar-time me-1"></i> Rentang Tanggal</label>
              </div>
            </div>

            {{-- FIELD TANGGAL SINGLE --}}
            <div class="mb-3" id="div-tanggal-single">
              <label class="das-form-label">Tanggal <span class="text-danger">*</span></label>
              <input type="date" name="tanggal" id="tanggal"
                     class="form-control das-form-control @error('tanggal') is-invalid @enderror"
                     value="{{ old('tanggal') }}">
              @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- FIELD TANGGAL RANGE --}}
            <div class="row g-2 mb-3" id="div-tanggal-range" style="display: none;">
              <div class="col-6">
                <label class="das-form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                       class="form-control das-form-control @error('tanggal_mulai') is-invalid @enderror"
                       value="{{ old('tanggal_mulai') }}">
                @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-6">
                <label class="das-form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                       class="form-control das-form-control @error('tanggal_selesai') is-invalid @enderror"
                       value="{{ old('tanggal_selesai') }}">
                @error('tanggal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="mb-3">
              <label class="das-form-label">Nama Libur <span class="text-danger">*</span></label>
              <input type="text" name="nama"
                     class="form-control das-form-control @error('nama') is-invalid @enderror"
                     placeholder="Contoh: Libur Semester Ganjil"
                     value="{{ old('nama') }}" required>
              @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
              <div class="das-form-text">Nama ini akan tampil di Kalender Absensi.</div>
            </div>

            <div class="mb-3">
              <label class="das-form-label">Cakupan Libur <span class="text-danger">*</span></label>
              <div class="cakupan-grid">
                <label class="cakupan-card" for="cakupan_global" title="Berlaku untuk seluruh Siswa & Guru">
                  <input type="radio" name="cakupan" id="cakupan_global" value="global" @checked(old('cakupan', 'global') === 'global')>
                  <div class="cakupan-card__icon"><i class="ti tabler-world"></i></div>
                  <div class="cakupan-card__title">Semua</div>
                </label>

                <label class="cakupan-card" for="cakupan_tingkat" title="Berlaku untuk seluruh kelas dalam 1 Tingkat">
                  <input type="radio" name="cakupan" id="cakupan_tingkat" value="tingkat" @checked(old('cakupan') === 'tingkat')>
                  <div class="cakupan-card__icon"><i class="ti tabler-school"></i></div>
                  <div class="cakupan-card__title">Per Tingkat</div>
                </label>

                <label class="cakupan-card" for="cakupan_kelas" title="Pilih kelas tertentu (Multi-Select)">
                  <input type="radio" name="cakupan" id="cakupan_kelas" value="kelas" @checked(old('cakupan') === 'kelas')>
                  <div class="cakupan-card__icon"><i class="ti tabler-door"></i></div>
                  <div class="cakupan-card__title">Per Kelas</div>
                </label>
              </div>
              @error('cakupan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3" id="div-tingkat" style="display: none;">
              <label class="das-form-label">Tingkat Kelas (Multi-Select) <span class="text-danger">*</span></label>
              <div class="tingkat-checkbox-group">
                <label class="tingkat-chip" for="tingkat_x">
                  <input type="checkbox" name="tingkat[]" id="tingkat_x" value="X" @checked(is_array(old('tingkat')) ? in_array('X', old('tingkat')) : old('tingkat') === 'X')>
                  <span class="tingkat-chip__text">Tingkat X</span>
                </label>

                <label class="tingkat-chip" for="tingkat_xi">
                  <input type="checkbox" name="tingkat[]" id="tingkat_xi" value="XI" @checked(is_array(old('tingkat')) ? in_array('XI', old('tingkat')) : old('tingkat') === 'XI')>
                  <span class="tingkat-chip__text">Tingkat XI</span>
                </label>

                <label class="tingkat-chip" for="tingkat_xii">
                  <input type="checkbox" name="tingkat[]" id="tingkat_xii" value="XII" @checked(is_array(old('tingkat')) ? in_array('XII', old('tingkat')) : old('tingkat') === 'XII')>
                  <span class="tingkat-chip__text">Tingkat XII</span>
                </label>
              </div>
              @error('tingkat') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            {{-- INTERAKTIF MULTI-SELECT KELAS (LIVE SEARCH & CHIPS) --}}
            <div class="mb-4" id="div-kelas" style="display: none;">
              <label class="das-form-label">Cari &amp; Pilih Kelas <span class="text-danger">*</span></label>
              
              {{-- Container untuk hidden inputs kelas_ids[] --}}
              <div id="hiddenKelasInputsContainer"></div>

              {{-- Input Search --}}
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-search"></i></span>
                <input type="text" class="set-input-search" id="searchKelas"
                       placeholder="Ketik nama kelas (misal: X-A, XII IPA)..." autocomplete="off">
              </div>

              {{-- Tombol Cepat (Quick Select) --}}
              <div class="quick-action-btn-group">
                <button type="button" class="quick-btn" id="btnSelectAllKelas">+ Semua Kelas</button>
                <button type="button" class="quick-btn" id="btnSelectTingkatX">+ Tingkat X</button>
                <button type="button" class="quick-btn" id="btnSelectTingkatXI">+ Tingkat XI</button>
                <button type="button" class="quick-btn" id="btnSelectTingkatXII">+ Tingkat XII</button>
                <button type="button" class="quick-btn text-danger ms-auto" id="btnClearKelas">Hapus Semua</button>
              </div>

              {{-- Display Chip Badges --}}
              <div class="selected-chip-wrap" id="selectedKelasChipWrap"></div>

              {{-- Dropdown Hasil Pencarian --}}
              <div class="kelas-search-results" id="kelasSearchResultsList"></div>

              @error('kelas_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <input type="hidden" name="jenis" value="school">

            <button type="submit" class="das-btn das-btn--primary w-100">
              <i class="ti tabler-device-floppy me-1"></i> Simpan Hari Libur
            </button>
          </form>

          <div class="das-info-box mt-4">
            <div class="small text-info d-flex gap-2">
              <i class="ti tabler-info-circle flex-shrink-0 mt-1"></i>
              <span>Gunakan fitur <strong>Rentang Tanggal</strong> &amp; <strong>Multi-Select Kelas</strong> untuk menghemat waktu penginputan libur semester.</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── PANEL DAFTAR LIBUR ────────────────────────── --}}
    <div class="col-md-7">
      <div class="das-panel">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot" style="background:var(--das-info);box-shadow:0 0 6px var(--das-info);"></span>
            Daftar Hari Libur
          </div>
          <div class="d-flex align-items-center gap-2">
            @if($holidays->isNotEmpty())
              <span class="das-chip das-chip--primary">{{ $holidays->count() }} Libur</span>
            @endif
            
            {{-- Form Sinkronisasi Libur Nasional --}}
            <form action="{{ route('admin.holidays.sync') }}" method="POST" onsubmit="this.querySelector('button').disabled = true; this.querySelector('.ti').className = 'ti tabler-loader spinner';">
              @csrf
              <input type="hidden" name="year" value="{{ $year }}">
              <button type="submit" class="das-btn das-btn--ghost">
                <i class="ti tabler-refresh me-1"></i> Sync Libur Nasional
              </button>
            </form>

            <form method="GET" class="d-flex align-items-center gap-2">
              <label class="das-form-label mb-0" style="font-size:.7rem;">Tahun:</label>
              <select name="year" class="form-select form-select-sm das-select" style="width:90px;" onchange="this.form.submit()">
                @foreach (range(now()->year - 1, now()->year + 2) as $y)
                  <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endforeach
              </select>
            </form>
          </div>
        </div>

        <div class="table-responsive">
          <table class="das-table">
            <thead>
              <tr>
                <th width="40">#</th>
                <th>Tanggal</th>
                <th>Nama Hari Libur</th>
                <th class="text-center">Jenis</th>
                <th class="text-end px-4">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($holidays as $holiday)
              <tr>
                <td class="text-muted font-monospace small text-center">{{ $loop->iteration }}</td>
                <td>
                  <div class="fw-bold text-white" style="font-size:.83rem;">
                    {{ \Carbon\Carbon::parse($holiday->tanggal)->translatedFormat('d F Y') }}
                  </div>
                  <div class="text-muted" style="font-size:.7rem;">
                    {{ \Carbon\Carbon::parse($holiday->tanggal)->translatedFormat('l') }}
                  </div>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div style="width:6px;height:6px;border-radius:50%;background:{{ $holiday->jenis === 'national' ? 'var(--das-danger)' : 'var(--das-info)' }};flex-shrink:0;"></div>
                    <span style="font-size:.83rem;" class="fw-medium">{{ $holiday->nama }}</span>
                    
                    @if($holiday->kelas_id)
                      <span class="badge bg-label-info ms-1" style="font-size: .65rem;">Kelas: {{ $holiday->kelas->nama ?? '-' }}</span>
                    @elseif($holiday->tingkat)
                      <span class="badge bg-label-warning ms-1" style="font-size: .65rem;">Tingkat: {{ $holiday->tingkat }}</span>
                    @endif

                    @if($holiday->batch_id)
                      <span class="badge bg-label-secondary border border-secondary border-opacity-25" style="font-size:.6rem;" title="Di-input dalam satu kelompok/batch">
                        <i class="ti tabler-layers-intersect me-1"></i>Batch
                      </span>
                    @endif
                  </div>
                </td>
                <td class="text-center">
                  @if($holiday->jenis === 'national')
                    <span class="das-chip das-chip--danger">
                      <i class="ti tabler-flag me-1" style="font-size:.65rem;"></i>Nasional
                    </span>
                  @else
                    <span class="das-chip das-chip--info">
                      <i class="ti tabler-school me-1" style="font-size:.65rem;"></i>Sekolah
                    </span>
                  @endif
                </td>
                <td class="text-end px-4">
                  @if($holiday->jenis === 'school')
                    <button type="button" class="das-table-btn das-table-btn--danger"
                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                            data-id="{{ $holiday->id }}"
                            data-name="{{ $holiday->nama }}"
                            data-batch="{{ $holiday->batch_id ?? '' }}"
                            data-url="{{ route('admin.holidays.destroy', $holiday->id) }}"
                            title="Hapus Libur">
                      <i class="ti tabler-trash" style="font-size:.9rem;"></i>
                    </button>
                  @else
                    <span class="text-muted small">—</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="py-5 text-center">
                  <div class="d-flex flex-column align-items-center gap-2 opacity-25">
                    <i class="ti tabler-calendar-off" style="font-size:3rem;"></i>
                    <span class="small font-monospace">Tidak ada data hari libur pada tahun {{ $year }}</span>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($holidays->isNotEmpty())
        <div class="px-4 py-3 border-top d-flex gap-3" style="border-color:var(--das-border)!important;">
          <span class="small text-muted">Total: <strong class="text-white">{{ $holidays->count() }}</strong> libur</span>
          <span class="small text-muted">·</span>
          <span class="das-chip das-chip--danger" style="font-size:.6rem;">{{ $holidays->where('jenis','national')->count() }} Nasional</span>
          <span class="das-chip das-chip--info" style="font-size:.6rem;">{{ $holidays->where('jenis','school')->count() }} Sekolah</span>
        </div>
        @endif
      </div>
    </div>

  </div>

  {{-- ── MODAL HAPUS ─────────────────────────────────── --}}
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title">
            <i class="ti tabler-alert-triangle me-2 text-danger"></i>Hapus Hari Libur
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form id="delForm" method="POST">
          @csrf @method('DELETE')
          <div class="das-modal-body text-center">
            <div class="mb-4">
              <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                   style="width:70px;height:70px;background:rgba(234,84,85,.1);border:1px solid rgba(234,84,85,.2);">
                <i class="ti tabler-trash-x text-danger fs-1"></i>
              </div>
              <h4 class="text-white mb-2">Hapus "<span id="delName" class="text-danger"></span>"?</h4>
              
              {{-- OPSI BATCH DELETE (Hanya tampil jika ber-batch_id) --}}
              <div id="batchDeleteOptions" class="text-start mt-3 p-3 rounded d-none" style="background:rgba(255,255,255,0.03);border:1px solid var(--das-border);">
                <div class="small fw-bold text-white mb-2"><i class="ti tabler-layers-intersect text-warning me-1"></i>Pilihan Penghapusan Group:</div>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="radio" name="delete_batch" id="del_single_only" value="0" checked>
                  <label class="form-check-input-label text-white small" for="del_single_only">
                    Hapus <strong>hanya 1 tanggal/entri ini</strong> saja
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="delete_batch" id="del_batch_all" value="1">
                  <label class="form-check-input-label text-warning small" for="del_batch_all">
                    Hapus <strong>seluruh kelompok hari libur</strong> ini sekaligus (Batch)
                  </label>
                </div>
              </div>

              <p class="text-muted small mt-3 mb-0">Tindakan ini tidak dapat dibatalkan. Hari libur ini akan dihapus dari sistem.</p>
            </div>
            <div class="d-flex gap-2 justify-content-center">
              <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="das-btn das-btn--primary px-4"
                      style="background-color:var(--das-danger);border-color:var(--das-danger);">
                <i class="ti tabler-trash me-1"></i> Ya, Hapus
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
<script>
const DATA_KELAS = @json($kelas);

document.addEventListener('DOMContentLoaded', function () {
  // Modal Delete Listener
  const delModal = document.getElementById('deleteModal');
  if (delModal) {
    delModal.addEventListener('show.bs.modal', function (e) {
      const btn = e.relatedTarget;
      const batchId = btn.getAttribute('data-batch');
      delModal.querySelector('#delName').textContent = btn.getAttribute('data-name');
      delModal.querySelector('#delForm').action      = btn.getAttribute('data-url');

      const batchOptions = delModal.querySelector('#batchDeleteOptions');
      const delSingleRadio = delModal.querySelector('#del_single_only');
      if (batchId && batchOptions) {
        batchOptions.classList.remove('d-none');
        if (delSingleRadio) delSingleRadio.checked = true;
      } else if (batchOptions) {
        batchOptions.classList.add('d-none');
      }
    });
  }

  // Tipe Tanggal Toggle (Single vs Range)
  const tipeSingle = document.getElementById('tipe_single');
  const tipeRange  = document.getElementById('tipe_range');
  const divSingle  = document.getElementById('div-tanggal-single');
  const divRange   = document.getElementById('div-tanggal-range');
  const inputTanggal = document.getElementById('tanggal');
  const inputMulai   = document.getElementById('tanggal_mulai');
  const inputSelesai = document.getElementById('tanggal_selesai');

  function toggleTipeTanggal() {
    if (tipeRange.checked) {
      divSingle.style.display = 'none';
      divRange.style.display  = 'flex';
      inputTanggal.removeAttribute('required');
      inputMulai.setAttribute('required', 'required');
      inputSelesai.setAttribute('required', 'required');
    } else {
      divSingle.style.display = 'block';
      divRange.style.display  = 'none';
      inputTanggal.setAttribute('required', 'required');
      inputMulai.removeAttribute('required');
      inputSelesai.removeAttribute('required');
    }
  }

  document.querySelectorAll('input[name="tipe_tanggal"]').forEach(el => {
    el.addEventListener('change', toggleTipeTanggal);
  });
  toggleTipeTanggal();

  // Cakupan Toggle (Radio Cards)
  const divTingkat = document.getElementById('div-tingkat');
  const divKelas   = document.getElementById('div-kelas');

  function toggleCakupan() {
    const checked = document.querySelector('input[name="cakupan"]:checked');
    const val = checked ? checked.value : 'global';
    if (val === 'tingkat') {
      divTingkat.style.display = 'block';
      divKelas.style.display   = 'none';
    } else if (val === 'kelas') {
      divKelas.style.display   = 'block';
      divTingkat.style.display = 'none';
    } else {
      divTingkat.style.display = 'none';
      divKelas.style.display   = 'none';
    }
  }

  document.querySelectorAll('input[name="cakupan"]').forEach(el => {
    el.addEventListener('change', toggleCakupan);
  });
  toggleCakupan();

  // ════════════════════════════════════════════════════════════
  // MULTI-SELECT KELAS LOGIC (LIVE SEARCH & CHIPS)
  // ════════════════════════════════════════════════════════════
  let selectedKelasIds = [];

  const searchInput = document.getElementById('searchKelas');
  const resultsList = document.getElementById('kelasSearchResultsList');
  const chipsWrap   = document.getElementById('selectedKelasChipWrap');
  const hiddenContainer = document.getElementById('hiddenKelasInputsContainer');

  function renderKelasSearchResults(term) {
    if (!resultsList) return;
    const lc = (term || '').toLowerCase().trim();

    if (!lc) {
      resultsList.innerHTML = '';
      return;
    }

    const filtered = DATA_KELAS.filter(k => {
      const nm = (k.nama || '').toLowerCase();
      const tk = (k.tingkat || '').toLowerCase();
      return nm.includes(lc) || tk.includes(lc);
    });

    if (filtered.length === 0) {
      resultsList.innerHTML = `<div class="p-3 text-center text-muted small"><i class="ti tabler-search-off me-1"></i>Kelas "${escHtml(term)}" tidak ditemukan</div>`;
      return;
    }

    resultsList.innerHTML = filtered.map(k => {
      const isSelected = selectedKelasIds.includes(Number(k.id));
      return `
        <div class="kelas-result-item ${isSelected ? 'is-selected' : ''}" data-id="${k.id}" onclick="toggleSelectKelas(${k.id})">
          <span>
            <strong class="text-white">${escHtml(k.nama)}</strong>
            <span class="text-muted ms-1" style="font-size:0.7rem;">(Tingkat ${escHtml(k.tingkat || '-')})</span>
          </span>
          <span class="badge ${isSelected ? 'bg-success' : 'bg-label-primary'}" style="font-size:0.65rem;">
            ${isSelected ? '<i class="ti tabler-check me-1"></i>Terpilih' : '+ Pilih'}
          </span>
        </div>
      `;
    }).join('');
  }

  window.toggleSelectKelas = function(id) {
    const numId = Number(id);
    const idx = selectedKelasIds.indexOf(numId);
    if (idx !== -1) {
      selectedKelasIds.splice(idx, 1);
    } else {
      selectedKelasIds.push(numId);
    }
    updateSelectedKelasUI();
    if (searchInput && searchInput.value.trim().length > 0) {
      renderKelasSearchResults(searchInput.value.trim());
    }
  };

  window.removeKelasChip = function(id) {
    selectedKelasIds = selectedKelasIds.filter(kId => kId !== Number(id));
    updateSelectedKelasUI();
    if (searchInput && searchInput.value.trim().length > 0) {
      renderKelasSearchResults(searchInput.value.trim());
    }
  };

  function updateSelectedKelasUI() {
    // Render Hidden Inputs
    if (hiddenContainer) {
      hiddenContainer.innerHTML = selectedKelasIds.map(id => `
        <input type="hidden" name="kelas_ids[]" value="${id}">
      `).join('');
    }

    // Render Chips
    if (chipsWrap) {
      if (selectedKelasIds.length === 0) {
        chipsWrap.innerHTML = '<span class="text-muted small italic" style="font-size:0.75rem;">Belum ada kelas yang dipilih. Ketik nama kelas di atas.</span>';
      } else {
        chipsWrap.innerHTML = selectedKelasIds.map(id => {
          const kObj = DATA_KELAS.find(k => Number(k.id) === Number(id));
          const name = kObj ? kObj.nama : `Kelas #${id}`;
          return `
            <div class="selected-chip">
              <span>${escHtml(name)}</span>
              <span class="chip-remove" onclick="removeKelasChip(${id})" title="Hapus kelas">✕</span>
            </div>
          `;
        }).join('');
      }
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      renderKelasSearchResults(this.value);
    });
  }

  // Quick Action Buttons
  document.getElementById('btnSelectAllKelas')?.addEventListener('click', function() {
    selectedKelasIds = DATA_KELAS.map(k => Number(k.id));
    updateSelectedKelasUI();
    if (searchInput && searchInput.value.trim().length > 0) renderKelasSearchResults(searchInput.value);
  });

  document.getElementById('btnSelectTingkatX')?.addEventListener('click', function() {
    const ids = DATA_KELAS.filter(k => (k.tingkat || '').toUpperCase() === 'X').map(k => Number(k.id));
    selectedKelasIds = Array.from(new Set([...selectedKelasIds, ...ids]));
    updateSelectedKelasUI();
    if (searchInput && searchInput.value.trim().length > 0) renderKelasSearchResults(searchInput.value);
  });

  document.getElementById('btnSelectTingkatXI')?.addEventListener('click', function() {
    const ids = DATA_KELAS.filter(k => (k.tingkat || '').toUpperCase() === 'XI').map(k => Number(k.id));
    selectedKelasIds = Array.from(new Set([...selectedKelasIds, ...ids]));
    updateSelectedKelasUI();
    if (searchInput && searchInput.value.trim().length > 0) renderKelasSearchResults(searchInput.value);
  });

  document.getElementById('btnSelectTingkatXII')?.addEventListener('click', function() {
    const ids = DATA_KELAS.filter(k => (k.tingkat || '').toUpperCase() === 'XII').map(k => Number(k.id));
    selectedKelasIds = Array.from(new Set([...selectedKelasIds, ...ids]));
    updateSelectedKelasUI();
    if (searchInput && searchInput.value.trim().length > 0) renderKelasSearchResults(searchInput.value);
  });

  document.getElementById('btnClearKelas')?.addEventListener('click', function() {
    selectedKelasIds = [];
    updateSelectedKelasUI();
    if (searchInput) searchInput.value = '';
    if (resultsList) resultsList.innerHTML = '';
  });

  function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  }

  // Form Submission Loading State
  const formHoliday = document.getElementById('formHoliday');
  if (formHoliday) {
    formHoliday.addEventListener('submit', function () {
      const btn = formHoliday.querySelector('button[type="submit"]');
      if (btn) {
        btn.innerHTML = '<i class="ti tabler-loader spinner me-1"></i> Menyimpan data...';
        btn.style.opacity = '0.85';
        setTimeout(function() {
          btn.disabled = true;
        }, 10);
      }
    });
  }

  // SweetAlert2 Session Flash Triggers
  @if (session('success'))
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: @json(session('success')),
        customClass: {
          confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false,
        timer: 3500,
        timerProgressBar: true
      });
    }
  @endif

  @if (session('error'))
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: @json(session('error')),
        customClass: {
          confirmButton: 'btn btn-danger'
        },
        buttonsStyling: false
      });
    }
  @endif

  // Initialize UI state
  updateSelectedKelasUI();
});
</script>
@endsection