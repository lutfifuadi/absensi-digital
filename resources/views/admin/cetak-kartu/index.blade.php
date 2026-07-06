@extends('layouts/layoutMaster')

@section('title', 'Cetak Kartu Identitas')

@section('page-style')
<style>
/* ════════════════════════════════════════════════════════════
   STEP WIZARD
════════════════════════════════════════════════════════════ */
.step-wizard {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0;
  margin-bottom: 2rem;
}

.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
  position: relative;
}

.step-num {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.9rem;
  border: 2px solid rgba(255,255,255,0.12);
  background: rgba(255,255,255,0.05);
  color: rgba(255,255,255,0.3);
  transition: all 0.35s ease;
}

.step-label {
  font-size: 0.72rem;
  font-weight: 600;
  color: rgba(255,255,255,0.3);
  text-align: center;
  white-space: nowrap;
  transition: all 0.35s ease;
}

.step-item.active .step-num {
  background: var(--das-primary);
  border-color: var(--das-primary);
  color: #fff;
  box-shadow: 0 0 16px rgba(115,103,240,0.5);
}

.step-item.active .step-label {
  color: #c8c4f8;
}

.step-item.done .step-num {
  background: rgba(40,199,111,0.2);
  border-color: var(--das-success);
  color: var(--das-success);
}

.step-item.done .step-label {
  color: var(--das-success);
}

.step-connector {
  flex: 1;
  height: 2px;
  min-width: 48px;
  max-width: 96px;
  background: rgba(255,255,255,0.08);
  margin: 0 0.5rem;
  margin-bottom: 1.4rem;
  border-radius: 2px;
  transition: background 0.35s ease;
}

.step-connector.done {
  background: var(--das-success);
  opacity: 0.5;
}

/* ════════════════════════════════════════════════════════════
   ENTITY CARDS (Step 1)
════════════════════════════════════════════════════════════ */
.entitas-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: center;
}

.entitas-card {
  flex: 1;
  min-width: 130px;
  max-width: 200px;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.6rem;
  padding: 1.5rem 1rem 1.2rem;
  border: 1.5px solid rgba(255,255,255,0.1);
  border-radius: 14px;
  background: rgba(255,255,255,0.03);
  cursor: pointer;
  transition: all 0.25s ease;
  text-align: center;
}

.entitas-card:hover {
  border-color: rgba(255,255,255,0.22);
  background: rgba(255,255,255,0.06);
  transform: translateY(-3px);
}

.entitas-card input[type="radio"] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.entitas-card__icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  transition: transform 0.25s ease;
}

.entitas-card__title {
  font-weight: 700;
  font-size: 0.92rem;
  color: rgba(255,255,255,0.85);
  transition: color 0.2s ease;
}

.entitas-card__sub {
  font-size: 0.72rem;
  color: rgba(255,255,255,0.35);
  transition: color 0.2s ease;
}

.entitas-card__check {
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 1.05rem;
  opacity: 0;
  transition: opacity 0.2s ease, transform 0.2s ease;
  transform: scale(0.6);
}

/* Siswa selected */
.entitas-card.--siswa:has(input:checked) {
  border-color: var(--das-info);
  background: rgba(0,207,232,0.1);
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0,207,232,0.2);
}

.entitas-card.--siswa:has(input:checked) .entitas-card__check {
  opacity: 1;
  transform: scale(1);
  color: var(--das-info);
}

.entitas-card.--siswa:has(input:checked) .entitas-card__title { color: #fff; }
.entitas-card.--siswa:has(input:checked) .entitas-card__sub { color: rgba(0,207,232,0.75); }

/* Guru selected */
.entitas-card.--guru:has(input:checked) {
  border-color: var(--das-warning);
  background: rgba(255,159,67,0.1);
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(255,159,67,0.2);
}

.entitas-card.--guru:has(input:checked) .entitas-card__check {
  opacity: 1;
  transform: scale(1);
  color: var(--das-warning);
}

.entitas-card.--guru:has(input:checked) .entitas-card__title { color: #fff; }
.entitas-card.--guru:has(input:checked) .entitas-card__sub { color: rgba(255,159,67,0.75); }

/* Staff selected */
.entitas-card.--staff:has(input:checked) {
  border-color: var(--das-success);
  background: rgba(40,199,111,0.1);
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(40,199,111,0.2);
}

.entitas-card.--staff:has(input:checked) .entitas-card__check {
  opacity: 1;
  transform: scale(1);
  color: var(--das-success);
}

.entitas-card.--staff:has(input:checked) .entitas-card__title { color: #fff; }
.entitas-card.--staff:has(input:checked) .entitas-card__sub { color: rgba(40,199,111,0.75); }

/* ════════════════════════════════════════════════════════════
   STEP SECTION PANEL (slide-down)
════════════════════════════════════════════════════════════ */
.step-section {
  overflow: hidden;
  max-height: 0;
  opacity: 0;
  transition: max-height 0.45s cubic-bezier(0.4,0,0.2,1),
              opacity 0.35s ease,
              margin-top 0.35s ease;
  margin-top: 0;
}

.step-section.is-open {
  max-height: 800px;
  opacity: 1;
  margin-top: 1.75rem;
}

/* ════════════════════════════════════════════════════════════
   OPSI CETAK CARDS (Step 2)
════════════════════════════════════════════════════════════ */
.opsi-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.opsi-card {
  flex: 1;
  min-width: 110px;
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.7rem;
  padding: 0.9rem 1.1rem;
  border: 1.5px solid rgba(255,255,255,0.1);
  border-radius: 10px;
  background: rgba(255,255,255,0.03);
  cursor: pointer;
  transition: all 0.22s ease;
}

.opsi-card:hover {
  border-color: rgba(255,255,255,0.2);
  background: rgba(255,255,255,0.06);
}

.opsi-card input[type="radio"] {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.opsi-card__icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  background: rgba(255,255,255,0.06);
  color: rgba(255,255,255,0.45);
  flex-shrink: 0;
  transition: all 0.22s ease;
}

.opsi-card__text {
  font-weight: 600;
  font-size: 0.83rem;
  color: rgba(255,255,255,0.6);
  transition: color 0.22s ease;
}

.opsi-card:has(input:checked) {
  border-color: var(--das-primary);
  background: rgba(115,103,240,0.12);
}

.opsi-card:has(input:checked) .opsi-card__icon {
  background: rgba(115,103,240,0.2);
  color: #c8c4f8;
}

.opsi-card:has(input:checked) .opsi-card__text {
  color: #fff;
}

/* hidden by JS */
.opsi-card.d-none { display: none !important; }

/* ════════════════════════════════════════════════════════════
   FILTER SECTIONS (slide-down)
════════════════════════════════════════════════════════════ */
.filter-section {
  overflow: hidden;
  max-height: 0;
  opacity: 0;
  transition: max-height 0.38s cubic-bezier(0.4,0,0.2,1),
              opacity 0.3s ease,
              margin-top 0.3s ease;
  margin-top: 0;
}

.filter-section.is-open {
  max-height: 500px;
  opacity: 1;
  margin-top: 1.25rem;
}

/* ════════════════════════════════════════════════════════════
   INDIVIDU SEARCH (Chip + List)
════════════════════════════════════════════════════════════ */
.individu-search-results {
  max-height: 230px;
  overflow-y: auto;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px;
  margin-top: 0.5rem;
  background: rgba(10,15,30,0.6);
}

.individu-search-results:empty {
  display: none;
}

.search-result-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.55rem 0.8rem;
  cursor: pointer;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  transition: background 0.15s ease;
  font-size: 0.82rem;
  color: rgba(255,255,255,0.7);
}

.search-result-item:last-child { border-bottom: none; }

.search-result-item:hover {
  background: rgba(115,103,240,0.15);
  color: #fff;
}

.search-result-item .sri-name { font-weight: 600; flex: 1; }
.search-result-item .sri-nip  { font-size: 0.7rem; color: rgba(255,255,255,0.35); }
.search-result-item .sri-icon { color: rgba(255,255,255,0.2); font-size: 0.85rem; }

.search-empty-msg {
  text-align: center;
  padding: 1rem;
  font-size: 0.8rem;
  color: rgba(255,255,255,0.3);
}

/* Selected chip */
.selected-chip-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.5rem;
  min-height: 0;
}

.selected-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.3rem 0.75rem;
  border-radius: 20px;
  background: rgba(115,103,240,0.2);
  border: 1px solid rgba(115,103,240,0.4);
  color: #c8c4f8;
  font-size: 0.78rem;
  font-weight: 600;
}

.selected-chip .chip-remove {
  cursor: pointer;
  opacity: 0.55;
  font-size: 0.7rem;
  transition: opacity 0.15s;
  line-height: 1;
}

.selected-chip .chip-remove:hover { opacity: 1; }

/* ════════════════════════════════════════════════════════════
   STEP 3 — TEMPLATE & DOWNLOAD
════════════════════════════════════════════════════════════ */
.preview-bar {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1.1rem;
  background: rgba(115,103,240,0.08);
  border: 1px solid rgba(115,103,240,0.2);
  border-radius: 10px;
  margin-bottom: 1.25rem;
}

.preview-bar__icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: rgba(115,103,240,0.18);
  color: #c8c4f8;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}

.preview-bar__text {
  font-size: 0.83rem;
  color: rgba(255,255,255,0.75);
  line-height: 1.45;
}

.preview-bar__text strong {
  color: #fff;
}

.no-template-msg {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.85rem 1rem;
  border: 1px dashed rgba(255,159,67,0.35);
  border-radius: 8px;
  background: rgba(255,159,67,0.06);
  font-size: 0.82rem;
  color: rgba(255,159,67,0.85);
  margin-top: 0.75rem;
}

/* Tombol Download */
.btn-download-big {
  width: 100%;
  padding: 0.85rem 1.5rem;
  font-size: 1rem;
  font-weight: 700;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  background: linear-gradient(135deg, var(--das-primary) 0%, rgba(115,103,240,0.7) 100%);
  border: none;
  color: #fff;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 4px 18px rgba(115,103,240,0.35);
}

.btn-download-big:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 28px rgba(115,103,240,0.45);
  color: #fff;
}

.btn-download-big:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
}

/* Divider label */
.step-divider {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.step-divider__label {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: rgba(255,255,255,0.3);
  white-space: nowrap;
}

.step-divider__line {
  flex: 1;
  height: 1px;
  background: rgba(255,255,255,0.07);
}

/* Scrollbar untuk search results */
.individu-search-results::-webkit-scrollbar { width: 4px; }
.individu-search-results::-webkit-scrollbar-track { background: transparent; }
.individu-search-results::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.12);
  border-radius: 4px;
}

/* ════════════════════════════════════════════════════════════
   RESPONSIVE
════════════════════════════════════════════════════════════ */
@media (max-width: 576px) {
  .step-connector { min-width: 28px; }
  .entitas-card { min-width: 100px; padding: 1.1rem 0.75rem; }
  .entitas-card__icon { width: 42px; height: 42px; font-size: 1.3rem; }
  .step-wizard { gap: 0; }
  .opsi-card { min-width: 100%; }
}
</style>
@endsection

@section('content')

@php
  $jumlahSiswa  = \App\Models\Siswa::where('status', 'aktif')->count();
  $jumlahGuru   = $guruList->count();
  $jumlahStaff  = $staffList->count();

  // Pisahkan template per tipe
  $templatesSiswa = $templates->filter(fn($t) => strtolower($t->type ?? '') === 'siswa');
  $templatesGuru  = $templates->filter(fn($t) => strtolower($t->type ?? '') === 'guru');
  $templatesStaff = $templates->filter(fn($t) => in_array(strtolower($t->type ?? ''), ['staff', 'staff_tu']));
  // Jika tidak ada tipe, tampilkan semua di setiap dropdown
  $noTypeTemplates = $templates->filter(fn($t) => empty($t->type));
@endphp

{{-- ═══════════════════════════════════════════════════════
     HERO HEADER
═══════════════════════════════════════════════════════ --}}
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder">
          <i class="ti tabler-id text-info"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>

      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Cetak Kartu
        </div>
        <h4 class="das-hero__title text-gradient-gold">Cetak Kartu Identitas</h4>
        <p class="das-hero__subtitle">Cetak kartu identitas untuk Siswa, Guru, atau Staff TU dalam tiga langkah mudah.</p>
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

@if (session('error'))
  <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
    <i class="ti tabler-alert-circle fs-5"></i>
    <span>{{ session('error') }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger alert-dismissible mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
    <div class="d-flex align-items-center gap-2">
      <i class="ti tabler-alert-triangle fs-5"></i>
      <ul class="mb-0 ps-3 small">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  </div>
@endif

{{-- ═══════════════════════════════════════════════════════
     FORM CARD
═══════════════════════════════════════════════════════ --}}
<div class="set-panel mb-4">
  <div class="set-panel__head">
    <div class="set-panel__title-wrap">
      <div class="set-panel__icon --primary"><i class="ti tabler-printer"></i></div>
      <div>
        <div class="set-panel__title">Wizard Cetak Kartu</div>
        <div class="set-panel__sub">Ikuti 3 langkah berikut untuk mencetak kartu identitas.</div>
      </div>
    </div>
  </div>

  <div class="set-panel__body">

    {{-- ── STEP INDICATOR ── --}}
    <div class="step-wizard" id="stepWizard">
      <div class="step-item active" id="stepItem1">
        <div class="step-num">1</div>
        <div class="step-label">Pilih Entitas</div>
      </div>
      <div class="step-connector" id="stepConn1"></div>
      <div class="step-item" id="stepItem2">
        <div class="step-num">2</div>
        <div class="step-label">Opsi Cetak</div>
      </div>
      <div class="step-connector" id="stepConn2"></div>
      <div class="step-item" id="stepItem3">
        <div class="step-num">3</div>
        <div class="step-label">Template & Cetak</div>
      </div>
    </div>

    <form method="POST" action="{{ route('admin.cetak-kartu.download') }}" id="formCetakKartu" novalidate>
      @csrf

      {{-- ════════════════════════════════════════════════
           STEP 1: PILIH ENTITAS
      ════════════════════════════════════════════════ --}}
      <div class="step-divider">
        <div class="step-divider__label">
          <i class="ti tabler-square-number-1 me-1" style="font-size:1rem;vertical-align:middle;color:#7367f0;"></i>
          Langkah 1 — Pilih Entitas
        </div>
        <div class="step-divider__line"></div>
      </div>

      <div class="entitas-row" id="tipeGroup">

        {{-- SISWA --}}
        <label class="entitas-card --siswa" for="tipe_siswa">
          <input type="radio" name="tipe" id="tipe_siswa" value="siswa" autocomplete="off"
            {{ old('tipe', 'siswa') === 'siswa' ? 'checked' : '' }}>
          <div class="entitas-card__icon" style="background:rgba(0,207,232,0.15);color:#00cfe8;">
            <i class="ti tabler-users-group"></i>
          </div>
          <div class="entitas-card__title">Siswa</div>
          <div class="entitas-card__sub">{{ number_format($jumlahSiswa) }} siswa aktif</div>
          <div class="entitas-card__check"><i class="ti tabler-circle-check"></i></div>
        </label>

        {{-- GURU --}}
        <label class="entitas-card --guru" for="tipe_guru">
          <input type="radio" name="tipe" id="tipe_guru" value="guru" autocomplete="off"
            {{ old('tipe') === 'guru' ? 'checked' : '' }}>
          <div class="entitas-card__icon" style="background:rgba(255,159,67,0.15);color:#ff9f43;">
            <i class="ti tabler-chalkboard"></i>
          </div>
          <div class="entitas-card__title">Guru</div>
          <div class="entitas-card__sub">{{ number_format($jumlahGuru) }} guru aktif</div>
          <div class="entitas-card__check"><i class="ti tabler-circle-check"></i></div>
        </label>

        {{-- STAFF TU --}}
        <label class="entitas-card --staff" for="tipe_staff">
          <input type="radio" name="tipe" id="tipe_staff" value="staff" autocomplete="off"
            {{ old('tipe') === 'staff' ? 'checked' : '' }}>
          <div class="entitas-card__icon" style="background:rgba(40,199,111,0.15);color:#28c76f;">
            <i class="ti tabler-building"></i>
          </div>
          <div class="entitas-card__title">Staff TU</div>
          <div class="entitas-card__sub">{{ number_format($jumlahStaff) }} staff aktif</div>
          <div class="entitas-card__check"><i class="ti tabler-circle-check"></i></div>
        </label>

      </div>{{-- /entitas-row --}}

      {{-- ════════════════════════════════════════════════
           STEP 2: OPSI CETAK + FILTER
      ════════════════════════════════════════════════ --}}
      <div class="step-section" id="step2Section">

        <div class="step-divider">
          <div class="step-divider__label">
            <i class="ti tabler-square-number-2 me-1" style="font-size:1rem;vertical-align:middle;color:#7367f0;"></i>
            Langkah 2 — Opsi Cetak
          </div>
          <div class="step-divider__line"></div>
        </div>

        <div class="opsi-row" id="opsiCetakGroup">

          {{-- Semua --}}
          <label class="opsi-card" for="opsi_semua" id="opsiLabelSemua">
            <input type="radio" name="opsi_cetak" id="opsi_semua" value="semua" autocomplete="off"
              {{ old('opsi_cetak', 'semua') === 'semua' ? 'checked' : '' }}>
            <div class="opsi-card__icon"><i class="ti tabler-id-badge-2"></i></div>
            <div class="opsi-card__text">Semua</div>
          </label>

          {{-- Per Kelas (hanya siswa) --}}
          <label class="opsi-card" for="opsi_kelas" id="opsiLabelKelas">
            <input type="radio" name="opsi_cetak" id="opsi_kelas" value="kelas" autocomplete="off"
              {{ old('opsi_cetak') === 'kelas' ? 'checked' : '' }}>
            <div class="opsi-card__icon"><i class="ti tabler-door"></i></div>
            <div class="opsi-card__text">Per Kelas</div>
          </label>

          {{-- Individu --}}
          <label class="opsi-card" for="opsi_individu" id="opsiLabelIndividu">
            <input type="radio" name="opsi_cetak" id="opsi_individu" value="individu" autocomplete="off"
              {{ old('opsi_cetak') === 'individu' ? 'checked' : '' }}>
            <div class="opsi-card__icon"><i class="ti tabler-user"></i></div>
            <div class="opsi-card__text">Individu</div>
          </label>

        </div>{{-- /opsi-row --}}

        {{-- FILTER KELAS --}}
        <div class="filter-section" id="filterKelasSection">
          <label class="set-label" for="kelas_id">Pilih Kelas <span class="text-danger">*</span></label>
          <div class="set-input-group">
            <span class="set-input-prefix"><i class="ti tabler-door"></i></span>
            <select class="set-input" name="kelas_id" id="kelas_id">
              <option value="">-- Pilih Kelas --</option>
              @foreach ($kelasOptions as $k)
                <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                  {{ $k->nama }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="set-field-hint --info mt-1">
            <i class="ti tabler-info-circle"></i> Pilih kelas untuk mencetak kartu seluruh siswa di kelas tersebut.
          </div>
        </div>

        {{-- FILTER INDIVIDU --}}
        <div class="filter-section" id="filterIndividuSection">
          <label class="set-label">Cari & Pilih Individu <span class="text-danger">*</span></label>

          {{-- Hidden input yang akan terisi nilai terpilih --}}
          <input type="hidden" name="entitas_id" id="entitas_id_hidden" value="{{ old('entitas_id') }}">

          {{-- Search box --}}
          <div class="set-input-group">
            <span class="set-input-prefix"><i class="ti tabler-search"></i></span>
            <input type="text" class="set-input" id="searchIndividu"
              placeholder="Ketik nama atau NIS/NIP untuk mencari..."
              autocomplete="off">
          </div>

          {{-- Selected chip display --}}
          <div class="selected-chip-wrap" id="selectedChipWrap"></div>

          {{-- Search results list --}}
          <div class="individu-search-results" id="searchResultsList"></div>

          <div class="set-field-hint --info mt-1">
            <i class="ti tabler-info-circle"></i>
            <span id="individuHintText">Ketik minimal 1 karakter untuk menampilkan hasil.</span>
          </div>
        </div>

      </div>{{-- /step2Section --}}

      {{-- ════════════════════════════════════════════════
           STEP 3: TEMPLATE & DOWNLOAD
      ════════════════════════════════════════════════ --}}
      <div class="step-section" id="step3Section">

        <div class="step-divider">
          <div class="step-divider__label">
            <i class="ti tabler-square-number-3 me-1" style="font-size:1rem;vertical-align:middle;color:#7367f0;"></i>
            Langkah 3 — Template & Download
          </div>
          <div class="step-divider__line"></div>
        </div>

        {{-- Preview Bar --}}
        <div class="preview-bar" id="previewBar">
          <div class="preview-bar__icon"><i class="ti tabler-clipboard-list"></i></div>
          <div class="preview-bar__text" id="previewBarText">
            Pilih entitas dan opsi cetak terlebih dahulu.
          </div>
        </div>

        {{-- Pilih Template --}}
        <label class="set-label" for="template_id">Pilih Template Kartu <span class="text-danger">*</span></label>
        <div class="set-input-group">
          <span class="set-input-prefix"><i class="ti tabler-template"></i></span>
          <select class="set-input" name="template_id" id="template_id" required>
            <option value="">-- Pilih Template --</option>
            {{-- Semua template ditampilkan, filter via JS berdasarkan tipe --}}
            @foreach ($templates as $t)
              <option value="{{ $t->id }}"
                data-type="{{ strtolower($t->type ?? 'all') }}"
                {{ old('template_id') == $t->id ? 'selected' : '' }}>
                {{ $t->name }}{{ $t->is_active ? '' : ' (nonaktif)' }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Pesan jika tidak ada template --}}
        <div class="no-template-msg d-none" id="noTemplateMsg">
          <i class="ti tabler-alert-triangle" style="font-size:1.1rem;"></i>
          <span>
            Belum ada template untuk tipe ini.
            <a href="{{ route('admin.id-card-templates.create') }}" class="text-warning fw-bold ms-1">
              Buat Template Baru →
            </a>
          </span>
        </div>

        <div class="set-field-hint --info mt-1 mb-4">
          <i class="ti tabler-info-circle"></i> Template menentukan tata letak dan gaya kartu identitas yang akan dicetak.
        </div>

        {{-- Tombol Download --}}
        <button type="submit" class="btn-download-big" id="btnDownload">
          <i class="ti tabler-printer" style="font-size:1.2rem;"></i>
          <span id="btnDownloadText">Download PDF Kartu</span>
        </button>

        {{-- Tombol Reset --}}
        <div class="text-center mt-2">
          <button type="button" class="btn das-btn --secondary px-3 py-1" id="btnReset" style="font-size:0.8rem;">
            <i class="ti tabler-refresh me-1"></i> Mulai Ulang
          </button>
        </div>

      </div>{{-- /step3Section --}}

    </form>
  </div>
</div>

@endsection

@section('page-script')
@php
  $siswaList = \App\Models\Siswa::where('status', 'aktif')
      ->orderBy('nama_lengkap')
      ->get(['id', 'nama_lengkap', 'nisn', 'nis']);
  $totalSiswaAktif = $siswaList->count();

  // Siapkan data kelas dengan jumlah siswa (opsional, jika ada relasi)
  $kelasWithCount = $kelasOptions->map(function($k) {
      return ['id' => $k->id, 'nama' => $k->nama];
  });
@endphp
<script>
// ════════════════════════════════════════════════════════════
// DATA dari backend
// ════════════════════════════════════════════════════════════
const DATA_SISWA = @json($siswaList);
const DATA_GURU  = @json($guruList);
const DATA_STAFF = @json($staffList);
const DATA_KELAS = @json($kelasWithCount);
const TOTAL_SISWA = {{ $totalSiswaAktif }};
const TOTAL_GURU  = {{ $jumlahGuru }};
const TOTAL_STAFF = {{ $jumlahStaff }};

// ════════════════════════════════════════════════════════════
// STATE
// ════════════════════════════════════════════════════════════
let selectedIndividu = null; // { id, name, nip }

// ════════════════════════════════════════════════════════════
// STEP INDICATOR
// ════════════════════════════════════════════════════════════
function updateStepIndicator(completedSteps) {
  // completedSteps: number (1, 2, 3)
  ['1','2','3'].forEach(n => {
    const item = document.getElementById('stepItem' + n);
    if (!item) return;
    item.classList.remove('active', 'done');
  });
  ['1','2'].forEach(n => {
    const conn = document.getElementById('stepConn' + n);
    if (conn) conn.classList.remove('done');
  });

  if (completedSteps >= 1) {
    document.getElementById('stepItem1')?.classList.add('done');
    document.getElementById('stepConn1')?.classList.add('done');
  } else {
    document.getElementById('stepItem1')?.classList.add('active');
  }

  if (completedSteps >= 2) {
    document.getElementById('stepItem2')?.classList.add('done');
    document.getElementById('stepConn2')?.classList.add('done');
  } else if (completedSteps === 1) {
    document.getElementById('stepItem2')?.classList.add('active');
  }

  if (completedSteps >= 3) {
    document.getElementById('stepItem3')?.classList.add('done');
  } else if (completedSteps === 2) {
    document.getElementById('stepItem3')?.classList.add('active');
  }
}

// ════════════════════════════════════════════════════════════
// SLIDE DOWN / UP helpers
// ════════════════════════════════════════════════════════════
function openSection(el) {
  if (!el) return;
  el.classList.add('is-open');
}

function closeSection(el) {
  if (!el) return;
  el.classList.remove('is-open');
}

// ════════════════════════════════════════════════════════════
// TIPE ENTITAS selected
// ════════════════════════════════════════════════════════════
function onTipeChange() {
  const tipe = getCheckedVal('tipe');
  if (!tipe) return;

  // Tampilkan step 2
  openSection(document.getElementById('step2Section'));

  // Per Kelas hanya untuk siswa
  const opsiKelasLabel = document.getElementById('opsiLabelKelas');
  if (tipe === 'siswa') {
    if (opsiKelasLabel) opsiKelasLabel.classList.remove('d-none');
  } else {
    if (opsiKelasLabel) opsiKelasLabel.classList.add('d-none');
    // Jika opsi kelas terpilih, pindahkan ke semua
    const opsiKelasInput = document.getElementById('opsi_kelas');
    if (opsiKelasInput && opsiKelasInput.checked) {
      const opsiSemua = document.getElementById('opsi_semua');
      if (opsiSemua) opsiSemua.checked = true;
    }
  }

  // Filter template berdasarkan tipe
  filterTemplateOptions(tipe);

  // Reset individu selection
  clearSelectedIndividu();

  // Update search placeholder
  updateSearchPlaceholder(tipe);

  // Update step 2 state jika sudah ada opsi terpilih
  onOpsiChange();

  updateStepIndicator(1);
}

// ════════════════════════════════════════════════════════════
// OPSI CETAK changed
// ════════════════════════════════════════════════════════════
function onOpsiChange() {
  const tipe = getCheckedVal('tipe');
  const opsi = getCheckedVal('opsi_cetak');

  if (!opsi || !tipe) return;

  const filterKelas    = document.getElementById('filterKelasSection');
  const filterIndividu = document.getElementById('filterIndividuSection');

  // Reset
  closeSection(filterKelas);
  closeSection(filterIndividu);

  if (opsi === 'kelas' && tipe === 'siswa') {
    openSection(filterKelas);
  } else if (opsi === 'individu') {
    openSection(filterIndividu);
    // Trigger search jika sudah ada value
    const searchEl = document.getElementById('searchIndividu');
    if (searchEl && searchEl.value.trim().length > 0) {
      renderSearchResults(searchEl.value.trim());
    }
  }

  // Tampilkan step 3
  openSection(document.getElementById('step3Section'));
  updateStepIndicator(2);

  updatePreviewBar();
}

// ════════════════════════════════════════════════════════════
// FILTER TEMPLATE OPTIONS
// ════════════════════════════════════════════════════════════
function filterTemplateOptions(tipe) {
  const select = document.getElementById('template_id');
  if (!select) return;

  const noMsg = document.getElementById('noTemplateMsg');
  let visibleCount = 0;

  Array.from(select.options).forEach(opt => {
    if (!opt.value) return; // skip placeholder
    const optType = opt.dataset.type || 'all';
    // Tampilkan jika type cocok, atau type = 'all' / kosong
    const show = optType === tipe || optType === 'all' || optType === '';
    opt.style.display = show ? '' : 'none';
    if (show) visibleCount++;
  });

  // Reset pilihan jika option yang dipilih tersembunyi
  const selected = select.options[select.selectedIndex];
  if (selected && selected.value && selected.style.display === 'none') {
    select.value = '';
  }

  if (noMsg) {
    if (visibleCount === 0) {
      noMsg.classList.remove('d-none');
    } else {
      noMsg.classList.add('d-none');
    }
  }

  updatePreviewBar();
}

// ════════════════════════════════════════════════════════════
// SEARCH INDIVIDU
// ════════════════════════════════════════════════════════════
function getActiveDataset() {
  const tipe = getCheckedVal('tipe');
  if (tipe === 'guru')  return DATA_GURU;
  if (tipe === 'staff') return DATA_STAFF;
  return DATA_SISWA;
}

function getItemLabel(item) {
  return item.nama_lengkap || item.name || '-';
}

function getItemSub(item) {
  return item.nip || item.nisn || item.nis || '';
}

function renderSearchResults(term) {
  const list = document.getElementById('searchResultsList');
  if (!list) return;

  if (!term || term.length < 1) {
    list.innerHTML = '';
    return;
  }

  const dataset = getActiveDataset();
  const lc = term.toLowerCase();
  const filtered = dataset.filter(item => {
    const nm  = getItemLabel(item).toLowerCase();
    const sub = getItemSub(item).toLowerCase();
    return nm.includes(lc) || sub.includes(lc);
  }).slice(0, 40); // max 40 results

  if (filtered.length === 0) {
    list.innerHTML = `<div class="search-empty-msg"><i class="ti tabler-search-off" style="font-size:1.4rem;display:block;margin:0 auto 0.35rem;"></i>Tidak ada hasil untuk "<strong>${escHtml(term)}</strong>"</div>`;
    return;
  }

  list.innerHTML = filtered.map(item => {
    const name = getItemLabel(item);
    const sub  = getItemSub(item);
    return `<div class="search-result-item" data-id="${item.id}" data-name="${escHtml(name)}" data-sub="${escHtml(sub)}" onclick="selectIndividu(this)">
      <span class="sri-icon"><i class="ti tabler-user-circle"></i></span>
      <span class="sri-name">${escHtml(name)}</span>
      ${sub ? `<span class="sri-nip">${escHtml(sub)}</span>` : ''}
    </div>`;
  }).join('');
}

function selectIndividu(el) {
  const id   = el.dataset.id;
  const name = el.dataset.name;
  const sub  = el.dataset.sub;

  selectedIndividu = { id, name, sub };

  // Set hidden input
  const hidden = document.getElementById('entitas_id_hidden');
  if (hidden) hidden.value = id;

  // Render chip
  const chipWrap = document.getElementById('selectedChipWrap');
  if (chipWrap) {
    chipWrap.innerHTML = `<div class="selected-chip">
      <i class="ti tabler-user" style="font-size:0.8rem;"></i>
      <span>${escHtml(name)}${sub ? ' — ' + escHtml(sub) : ''}</span>
      <span class="chip-remove" onclick="clearSelectedIndividu()" title="Hapus pilihan">✕</span>
    </div>`;
  }

  // Clear search & results
  const searchEl = document.getElementById('searchIndividu');
  if (searchEl) searchEl.value = '';
  const list = document.getElementById('searchResultsList');
  if (list) list.innerHTML = '';

  updatePreviewBar();
}

function clearSelectedIndividu() {
  selectedIndividu = null;
  const hidden = document.getElementById('entitas_id_hidden');
  if (hidden) hidden.value = '';
  const chipWrap = document.getElementById('selectedChipWrap');
  if (chipWrap) chipWrap.innerHTML = '';
  updatePreviewBar();
}

function updateSearchPlaceholder(tipe) {
  const el = document.getElementById('searchIndividu');
  if (!el) return;
  if (tipe === 'guru')  el.placeholder = 'Ketik nama atau NIP guru...';
  else if (tipe === 'staff') el.placeholder = 'Ketik nama atau NIP staff TU...';
  else el.placeholder = 'Ketik nama atau NISN/NIS siswa...';

  const hint = document.getElementById('individuHintText');
  if (hint) {
    if (tipe === 'guru')  hint.textContent = 'Ketik nama atau NIP untuk menyaring daftar guru.';
    else if (tipe === 'staff') hint.textContent = 'Ketik nama atau NIP untuk menyaring daftar staff TU.';
    else hint.textContent = 'Ketik nama atau NISN/NIS untuk menyaring daftar siswa.';
  }
}

// ════════════════════════════════════════════════════════════
// PREVIEW BAR
// ════════════════════════════════════════════════════════════
function updatePreviewBar() {
  const barText = document.getElementById('previewBarText');
  if (!barText) return;

  const tipe = getCheckedVal('tipe');
  const opsi = getCheckedVal('opsi_cetak');

  const tipeLabel = { siswa: 'Siswa', guru: 'Guru', staff: 'Staff TU' };
  const opsiLabel = { semua: 'Semua', kelas: 'Per Kelas', individu: 'Individu' };

  const templateSelect = document.getElementById('template_id');
  const templateName = templateSelect && templateSelect.value
    ? (templateSelect.options[templateSelect.selectedIndex]?.text || '—')
    : '<em style="opacity:0.5">belum dipilih</em>';

  if (!tipe || !opsi) {
    barText.innerHTML = 'Pilih entitas dan opsi cetak terlebih dahulu.';
    return;
  }

  let countStr = '';
  let detailStr = '';

  if (opsi === 'semua') {
    if (tipe === 'siswa')  countStr = `<strong>${TOTAL_SISWA}</strong>`;
    else if (tipe === 'guru')  countStr = `<strong>${TOTAL_GURU}</strong>`;
    else countStr = `<strong>${TOTAL_STAFF}</strong>`;
    detailStr = `Semua`;
  } else if (opsi === 'kelas') {
    const kelasEl = document.getElementById('kelas_id');
    if (kelasEl && kelasEl.value) {
      const kelasName = kelasEl.options[kelasEl.selectedIndex]?.text || '—';
      countStr = `<strong>?</strong>`;
      detailStr = `Kelas <strong>${escHtml(kelasName)}</strong>`;
    } else {
      countStr = `<strong>?</strong>`;
      detailStr = `Kelas <em style="opacity:0.5">belum dipilih</em>`;
    }
  } else if (opsi === 'individu') {
    if (selectedIndividu) {
      countStr = `<strong>1</strong>`;
      detailStr = `<strong>${escHtml(selectedIndividu.name)}</strong>`;
    } else {
      countStr = `<strong>1</strong>`;
      detailStr = `Individu <em style="opacity:0.5">belum dipilih</em>`;
    }
  }

  barText.innerHTML = `📋 Akan mencetak ${countStr} kartu <strong>${tipeLabel[tipe]}</strong> · ${detailStr} · Template: ${templateName}`;
}

// ════════════════════════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════════════════════════
function getCheckedVal(name) {
  const el = document.querySelector(`input[name="${name}"]:checked`);
  return el ? el.value : null;
}

function escHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#39;');
}

// ════════════════════════════════════════════════════════════
// INIT & EVENT LISTENERS
// ════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {

  // Radio: tipe
  document.querySelectorAll('input[name="tipe"]').forEach(el => {
    el.addEventListener('change', onTipeChange);
  });

  // Radio: opsi_cetak
  document.querySelectorAll('input[name="opsi_cetak"]').forEach(el => {
    el.addEventListener('change', onOpsiChange);
  });

  // Kelas select
  const kelasEl = document.getElementById('kelas_id');
  if (kelasEl) kelasEl.addEventListener('change', updatePreviewBar);

  // Template select
  const templateEl = document.getElementById('template_id');
  if (templateEl) templateEl.addEventListener('change', updatePreviewBar);

  // Search individu
  const searchEl = document.getElementById('searchIndividu');
  if (searchEl) {
    searchEl.addEventListener('input', function() {
      renderSearchResults(this.value.trim());
    });
    // Close list on outside click
    document.addEventListener('click', function(e) {
      if (!e.target.closest('#filterIndividuSection')) {
        const list = document.getElementById('searchResultsList');
        if (list) list.innerHTML = '';
      }
    });
  }

  // ── Form validation & submit ──
  const form = document.getElementById('formCetakKartu');
  if (form) {
    form.addEventListener('submit', function(e) {
      const tipe = getCheckedVal('tipe');
      const opsi = getCheckedVal('opsi_cetak');
      const template = document.getElementById('template_id');
      const errors = [];

      if (!tipe) errors.push('Pilih tipe entitas terlebih dahulu.');
      if (!opsi) errors.push('Pilih opsi cetak terlebih dahulu.');
      if (!template || !template.value) errors.push('Pilih template kartu.');

      if (opsi === 'kelas') {
        const kelas = document.getElementById('kelas_id');
        if (!kelas || !kelas.value) errors.push('Pilih kelas terlebih dahulu.');
      }

      if (opsi === 'individu') {
        const hidden = document.getElementById('entitas_id_hidden');
        if (!hidden || !hidden.value) errors.push('Pilih individu terlebih dahulu (cari dan klik nama).');
      }

      if (errors.length > 0) {
        e.preventDefault();
        // Gunakan alert sederhana atau bisa diganti dengan toast
        alert('Mohon lengkapi form:\n• ' + errors.join('\n• '));
        return false;
      }

      // Loading state
      const btn = document.getElementById('btnDownload');
      const btnText = document.getElementById('btnDownloadText');
      if (btn) {
        btn.disabled = true;
        if (btnText) btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses...';
        else btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses...';
      }
    });
  }

  // ── Reset button ──
  const resetBtn = document.getElementById('btnReset');
  if (resetBtn) {
    resetBtn.addEventListener('click', function() {
      // Reset radio ke default siswa + semua
      const tipeSiswa = document.getElementById('tipe_siswa');
      const opsiSemua = document.getElementById('opsi_semua');
      if (tipeSiswa) tipeSiswa.checked = true;
      if (opsiSemua) opsiSemua.checked = true;

      // Reset selects
      const kelasEl = document.getElementById('kelas_id');
      if (kelasEl) kelasEl.value = '';
      const templateEl = document.getElementById('template_id');
      if (templateEl) templateEl.value = '';

      // Reset individu
      clearSelectedIndividu();
      const searchEl = document.getElementById('searchIndividu');
      if (searchEl) searchEl.value = '';
      const list = document.getElementById('searchResultsList');
      if (list) list.innerHTML = '';

      // Close sections
      closeSection(document.getElementById('step2Section'));
      closeSection(document.getElementById('step3Section'));
      closeSection(document.getElementById('filterKelasSection'));
      closeSection(document.getElementById('filterIndividuSection'));

      // Reset step indicator
      updateStepIndicator(0);

      // Re-show Per Kelas option
      const opsiKelasLabel = document.getElementById('opsiLabelKelas');
      if (opsiKelasLabel) opsiKelasLabel.classList.remove('d-none');

      // Reset button
      const btn = document.getElementById('btnDownload');
      const btnText = document.getElementById('btnDownloadText');
      if (btn) btn.disabled = false;
      if (btnText) btnText.textContent = 'Download PDF Kartu';
    });
  }

  // ── Inisialisasi dari old() data (jika ada validasi gagal) ──
  const oldTipe = @json(old('tipe', ''));
  const oldOpsi = @json(old('opsi_cetak', ''));

  if (oldTipe) {
    // Tipe sudah ter-checked dari Blade, trigger handler manual
    onTipeChange();
    if (oldOpsi) {
      onOpsiChange();
    }
  }

  updateStepIndicator(oldTipe ? (oldOpsi ? 2 : 1) : 0);
  updatePreviewBar();

}); // end DOMContentLoaded
</script>
@endsection
