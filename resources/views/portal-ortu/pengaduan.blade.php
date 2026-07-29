@extends('layouts/layoutMaster')

@section('title', 'Layanan Pengaduan Transparan')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
<style>
  body, .layout-page, .content-wrapper { background: #0a0e1a !important; }

  /* ── HERO ─────────────────────────────────────────── */
  .pgd-hero {
    background: linear-gradient(135deg, #1a1040 0%, #0f0a2e 60%, #0a0e1a 100%);
    border: 1px solid rgba(115,103,240,0.2);
    border-radius: 5px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.25rem;
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap;
    position: relative; overflow: hidden;
  }
  .pgd-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 80% 50%, rgba(115,103,240,0.12), transparent 65%);
    pointer-events: none;
  }
  .pgd-hero__icon {
    width: 52px; height: 52px; border-radius: 5px; flex-shrink: 0;
    background: rgba(115,103,240,0.15);
    border: 1px solid rgba(115,103,240,0.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: #a29bfe;
  }
  .pgd-hero__badge {
    font-size: 0.68rem; font-weight: 700; letter-spacing: 1px;
    text-transform: uppercase; color: #a29bfe;
    display: flex; align-items: center; gap: 0.4rem;
    margin-bottom: 0.3rem;
  }
  .pgd-hero__badge .pulse-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #a29bfe; flex-shrink: 0;
    animation: pgdPulse 1.8s ease-in-out infinite;
  }
  @keyframes pgdPulse {
    0%,100% { opacity:1; transform: scale(1); }
    50% { opacity:0.4; transform: scale(0.75); }
  }
  .pgd-hero__title {
    font-size: 1.2rem; font-weight: 800; color: #fff;
    letter-spacing: -0.3px; margin: 0;
  }
  .pgd-hero__sub {
    font-size: 0.8rem; color: rgba(255,255,255,0.45); margin: 0.15rem 0 0;
  }
  .pgd-btn-new {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.5rem 1.25rem; border-radius: 5px; border: none; cursor: pointer;
    font-size: 0.82rem; font-weight: 700;
    background: linear-gradient(135deg, #7367f0 0%, #5e50ee 100%);
    color: #fff; white-space: nowrap;
    box-shadow: 0 4px 14px rgba(115,103,240,0.35);
    transition: all 0.2s;
  }
  .pgd-btn-new:hover {
    background: linear-gradient(135deg, #6254e8 0%, #4d3edc 100%);
    box-shadow: 0 6px 18px rgba(115,103,240,0.45);
    transform: translateY(-1px);
    color: #fff;
  }

  /* ── LAYOUT SPLIT ─────────────────────────────────── */
  .pgd-layout {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 1.1rem;
    align-items: start;
  }
  @media (max-width: 900px) {
    .pgd-layout { grid-template-columns: 1fr; }
  }

  /* ── LIST PANEL (kiri) ───────────────────────────── */
  .pgd-panel {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 5px;
    overflow: hidden;
  }
  .pgd-panel__head {
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    display: flex; align-items: center; gap: 0.5rem;
  }
  .pgd-panel__head-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #7367f0; flex-shrink: 0;
  }
  .pgd-panel__head-title {
    font-size: 0.82rem; font-weight: 700;
    color: rgba(255,255,255,0.75); letter-spacing: 0.3px;
  }
  .pgd-panel__body { padding: 0.65rem; }

  /* ── ITEM PENGADUAN ─────────────────────────────── */
  .pgd-item {
    display: block; text-decoration: none;
    padding: 0.85rem 1rem;
    border-radius: 5px;
    border: 1px solid transparent;
    margin-bottom: 0.4rem;
    transition: all 0.18s;
    background: rgba(255,255,255,0.02);
  }
  .pgd-item:last-child { margin-bottom: 0; }
  .pgd-item:hover {
    background: rgba(115,103,240,0.07);
    border-color: rgba(115,103,240,0.2);
    text-decoration: none;
  }
  .pgd-item.active {
    background: rgba(115,103,240,0.13);
    border-color: rgba(115,103,240,0.35);
  }
  .pgd-item__top {
    display: flex; align-items: center;
    justify-content: space-between; gap: 0.5rem;
    margin-bottom: 0.3rem;
  }
  .pgd-item__kode {
    font-size: 0.7rem; font-weight: 700; font-family: monospace;
    color: rgba(255,255,255,0.35); letter-spacing: 0.5px;
  }
  .pgd-item.active .pgd-item__kode { color: rgba(162,155,254,0.7); }
  .pgd-item__cat {
    font-size: 0.82rem; font-weight: 600; color: rgba(255,255,255,0.8);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 100%;
  }
  .pgd-item.active .pgd-item__cat { color: #fff; }
  .pgd-item__date {
    font-size: 0.7rem; color: rgba(255,255,255,0.3); margin-top: 0.15rem;
  }
  .pgd-item.active .pgd-item__date { color: rgba(162,155,254,0.55); }

  /* ── BADGE STATUS ───────────────────────────────── */
  .pgd-badge {
    font-size: 0.63rem; font-weight: 800; letter-spacing: 0.5px;
    text-transform: uppercase; padding: 0.2rem 0.55rem;
    border-radius: 3px; flex-shrink: 0; white-space: nowrap;
  }
  .pgd-badge--baru      { background: rgba(255,215,0,0.15); color: #ffd700; border: 1px solid rgba(255,215,0,0.25); }
  .pgd-badge--diproses  { background: rgba(0,207,232,0.12); color: #00cfe8; border: 1px solid rgba(0,207,232,0.25); }
  .pgd-badge--selesai   { background: rgba(40,199,111,0.12); color: #28c76f; border: 1px solid rgba(40,199,111,0.25); }
  .pgd-badge--ditolak   { background: rgba(234,84,85,0.12); color: #ea5455; border: 1px solid rgba(234,84,85,0.25); }
  .pgd-badge--default   { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.5); border: 1px solid rgba(255,255,255,0.1); }

  /* ── DETAIL PANEL (kanan) ───────────────────────── */
  .pgd-detail {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 5px;
    overflow: hidden;
  }
  .pgd-detail__head {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 0.75rem;
  }
  .pgd-detail__kode {
    font-size: 0.7rem; font-weight: 700; font-family: monospace;
    color: rgba(255,255,255,0.3); letter-spacing: 0.5px; margin-bottom: 0.2rem;
  }
  .pgd-detail__title {
    font-size: 1rem; font-weight: 700; color: #fff; margin: 0;
    line-height: 1.3;
  }
  .pgd-detail__body { padding: 1.25rem; }

  /* Deskripsi box */
  .pgd-desc-box {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 5px;
    padding: 1rem 1.1rem;
    margin-bottom: 1.25rem;
  }
  .pgd-desc-box__label {
    font-size: 0.7rem; font-weight: 700; letter-spacing: 0.7px;
    text-transform: uppercase; color: rgba(255,255,255,0.35);
    margin-bottom: 0.5rem;
    display: flex; align-items: center; gap: 0.4rem;
  }
  .pgd-desc-box__text {
    font-size: 0.875rem; color: rgba(255,255,255,0.7);
    line-height: 1.65; white-space: pre-line; margin: 0;
  }
  .pgd-catatan-box {
    margin-top: 0.85rem; padding-top: 0.85rem;
    border-top: 1px solid rgba(255,255,255,0.07);
  }
  .pgd-catatan-box__label {
    font-size: 0.7rem; font-weight: 700; letter-spacing: 0.7px;
    text-transform: uppercase; color: rgba(255,185,0,0.6);
    margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.4rem;
  }
  .pgd-catatan-box__text {
    font-size: 0.875rem; color: rgba(255,185,0,0.85);
    line-height: 1.6; white-space: pre-line; margin: 0;
  }

  /* Timeline */
  .pgd-timeline-label {
    font-size: 0.7rem; font-weight: 700; letter-spacing: 0.7px;
    text-transform: uppercase; color: rgba(255,255,255,0.35);
    display: flex; align-items: center; gap: 0.4rem;
    margin-bottom: 1rem;
  }
  .pgd-timeline { list-style: none; padding: 0; margin: 0; position: relative; }
  .pgd-timeline::before {
    content: ''; position: absolute;
    left: 11px; top: 0; bottom: 0; width: 1px;
    background: rgba(255,255,255,0.07);
  }
  .pgd-tl-item {
    display: flex; gap: 1rem;
    padding-bottom: 1.25rem; position: relative;
  }
  .pgd-tl-item:last-child { padding-bottom: 0; }
  .pgd-tl-dot {
    width: 23px; height: 23px; border-radius: 50%;
    flex-shrink: 0; display: flex; align-items: center; justify-content: center;
    font-size: 0.65rem; position: relative; z-index: 1;
    margin-top: 1px;
  }
  .pgd-tl-dot--baru      { background: rgba(255,215,0,0.18); border: 2px solid #ffd700; color: #ffd700; }
  .pgd-tl-dot--diproses  { background: rgba(0,207,232,0.15); border: 2px solid #00cfe8; color: #00cfe8; }
  .pgd-tl-dot--selesai   { background: rgba(40,199,111,0.15); border: 2px solid #28c76f; color: #28c76f; }
  .pgd-tl-dot--ditolak   { background: rgba(234,84,85,0.15); border: 2px solid #ea5455; color: #ea5455; }
  .pgd-tl-dot--default   { background: rgba(255,255,255,0.06); border: 2px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.4); }
  .pgd-tl-content { flex: 1; min-width: 0; }
  .pgd-tl-header { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.25rem; flex-wrap: wrap; }
  .pgd-tl-status {
    font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;
  }
  .pgd-tl-status--baru     { color: #ffd700; }
  .pgd-tl-status--diproses { color: #00cfe8; }
  .pgd-tl-status--selesai  { color: #28c76f; }
  .pgd-tl-status--ditolak  { color: #ea5455; }
  .pgd-tl-status--default  { color: rgba(255,255,255,0.4); }
  .pgd-tl-time {
    font-size: 0.68rem; color: rgba(255,255,255,0.3);
  }
  .pgd-tl-note {
    font-size: 0.8rem; color: rgba(255,255,255,0.5);
    line-height: 1.5; margin: 0;
  }
  .pgd-tl-oleh {
    display: inline-block; font-size: 0.66rem; font-weight: 600;
    color: rgba(255,255,255,0.3); margin-top: 0.3rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 0.1rem 0.45rem; border-radius: 3px;
  }

  /* Empty state */
  .pgd-empty {
    padding: 3rem 1rem; text-align: center;
  }
  .pgd-empty__icon {
    width: 64px; height: 64px; border-radius: 5px; margin: 0 auto 1rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; opacity: 0.3;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
  }
  .pgd-empty__title { font-size: 0.95rem; font-weight: 700; color: rgba(255,255,255,0.55); margin-bottom: 0.35rem; }
  .pgd-empty__sub   { font-size: 0.78rem; color: rgba(255,255,255,0.25); max-width: 260px; margin: 0 auto; }

  /* ── MODAL ──────────────────────────────────────── */
  .modal-content {
    background: #111827 !important;
    border: 1px solid rgba(115,103,240,0.2) !important;
    border-radius: 5px !important;
  }
  .modal-header {
    padding: 1.1rem 1.5rem !important;
    background: rgba(115,103,240,0.06) !important;
    border-bottom: 1px solid rgba(255,255,255,0.07) !important;
  }
  .modal-title { font-size: 1rem !important; font-weight: 700 !important; color: #fff !important; }
  .modal-body { padding: 1.5rem !important; }
  .modal-footer {
    padding: 1rem 1.5rem !important;
    background: rgba(0,0,0,0.15) !important;
    border-top: 1px solid rgba(255,255,255,0.07) !important;
    gap: 0.6rem;
  }
  .modal-content label.form-label {
    font-size: 0.78rem !important; font-weight: 700 !important;
    color: rgba(255,255,255,0.55) !important; letter-spacing: 0.4px;
    text-transform: uppercase; margin-bottom: 0.45rem !important;
    display: inline-flex !important; align-items: center !important; gap: 0.35rem !important;
  }
  .modal-content label.form-label i { color: #7367f0 !important; font-size: 0.9rem !important; }
  .modal-content .form-control,
  .modal-content .form-select {
    background: rgba(255,255,255,0.04) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    color: #fff !important; border-radius: 4px !important;
    font-size: 0.875rem !important;
    transition: border-color 0.18s, box-shadow 0.18s;
  }
  .modal-content .form-control::placeholder { color: rgba(255,255,255,0.2) !important; }
  .modal-content .form-control:focus,
  .modal-content .form-select:focus {
    border-color: #7367f0 !important;
    box-shadow: 0 0 0 3px rgba(115,103,240,0.18) !important;
    outline: none;
  }
  .modal-content .form-control[readonly] {
    background: rgba(255,255,255,0.02) !important;
    color: rgba(255,255,255,0.35) !important;
    border-color: rgba(255,255,255,0.06) !important;
  }

  /* Select2 in modal */
  .select2-container { width: 100% !important; }
  .select2-container--default .select2-selection--single {
    background: rgba(255,255,255,0.04) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 4px !important; height: 40px !important;
    color: #fff !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #fff !important; line-height: 38px !important; padding-left: 12px !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__placeholder { color: rgba(255,255,255,0.25) !important; }
  .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px !important; }
  .select2-dropdown {
    background: #1a2035 !important;
    border: 1px solid rgba(115,103,240,0.2) !important;
    border-radius: 4px !important; z-index: 1060 !important;
  }
  .select2-container--default .select2-search--dropdown .select2-search__field {
    background: rgba(255,255,255,0.04) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    color: #fff !important; border-radius: 3px !important;
  }
  .select2-container--default .select2-results__option { color: rgba(255,255,255,0.65) !important; }
  .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: rgba(115,103,240,0.2) !important; color: #fff !important;
  }
  .select2-container--default .select2-results__option[aria-selected=true] {
    background: rgba(115,103,240,0.1) !important; color: #a29bfe !important;
  }
  .select2-container--default .select2-results__group { color: rgba(255,255,255,0.35) !important; font-weight: 700; font-size: 0.7rem; letter-spacing: 0.5px; }

  /* Modal buttons */
  .pgd-modal-cancel {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.5rem 1.15rem; border-radius: 4px; cursor: pointer;
    font-size: 0.82rem; font-weight: 600;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    color: rgba(255,255,255,0.55);
    transition: all 0.2s;
  }
  .pgd-modal-cancel:hover {
    background: rgba(255,255,255,0.09); color: #fff;
    border-color: rgba(255,255,255,0.22);
  }
  .pgd-modal-submit {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.5rem 1.4rem; border-radius: 4px; cursor: pointer; border: none;
    font-size: 0.82rem; font-weight: 700; color: #fff;
    background: linear-gradient(135deg, #7367f0 0%, #5e50ee 100%);
    box-shadow: 0 4px 12px rgba(115,103,240,0.3);
    transition: all 0.2s;
  }
  .pgd-modal-submit span {
    display: inline-flex; align-items: center; gap: 0.35rem;
  }
  .pgd-modal-submit span:has(.spinner-border),
  .pgd-modal-submit #submitLoading {
    display: none;
  }
  .pgd-modal-submit:hover:not(:disabled) {
    background: linear-gradient(135deg, #6254e8 0%, #4d3edc 100%);
    box-shadow: 0 6px 16px rgba(115,103,240,0.45); transform: translateY(-1px);
  }
  .pgd-modal-submit:disabled { opacity: 0.6; cursor: not-allowed; }

  /* WA info banner */
  .pgd-wa-note {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.55rem 0.85rem; border-radius: 4px; margin-top: 0.6rem;
    background: rgba(37,211,102,0.08);
    border: 1px solid rgba(37,211,102,0.2);
    border-left: 3px solid #25d366;
  }
  .pgd-wa-note i { color: #25d366; flex-shrink: 0; font-size: 1.05rem; }
  .pgd-wa-note span { font-size: 0.75rem; color: rgba(37,211,102,0.9); }
  .pgd-wa-note strong { color: #25d366; }

  /* Count badge */
  #deskripsiCountWrapper { transition: color 0.2s; font-size: 0.75rem; }
</style>
@endsection

@section('content')

{{-- HERO --}}
<div class="pgd-hero">
  <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
    <div class="pgd-hero__icon">
      <i class="ti tabler-message-dots"></i>
    </div>
    <div>
      <div class="pgd-hero__badge">
        <span class="pulse-dot"></span> Portal Orang Tua
      </div>
      <h4 class="pgd-hero__title">Portal Layanan Pengaduan</h4>
      <p class="pgd-hero__sub">Sampaikan kendala atau keluhan terkait data presensi secara transparan.</p>
    </div>
  </div>
  <button type="button" class="pgd-btn-new" data-bs-toggle="modal" data-bs-target="#modalCreatePengaduan">
    <i class="ti tabler-plus"></i> Buat Pengaduan
  </button>
</div>

{{-- SPLIT LAYOUT --}}
<div class="pgd-layout">

  {{-- ── KIRI: Daftar Pengaduan ─────────────────────── --}}
  <div class="pgd-panel">
    <div class="pgd-panel__head">
      <span class="pgd-panel__head-dot"></span>
      <span class="pgd-panel__head-title">Riwayat Pengaduan Saya</span>
    </div>
    <div class="pgd-panel__body">
      @forelse($pengaduanList as $p)
        @php
          $isActive = $activePengaduan && $activePengaduan->id === $p->id;
          $badgeClass = match($p->status) {
            'baru'      => 'pgd-badge--baru',
            'diproses'  => 'pgd-badge--diproses',
            'selesai'   => 'pgd-badge--selesai',
            'ditolak'   => 'pgd-badge--ditolak',
            default     => 'pgd-badge--default',
          };
        @endphp
        <a href="{{ route('ortu.pengaduan', ['id' => $p->id]) }}"
           class="pgd-item {{ $isActive ? 'active' : '' }}">
          <div class="pgd-item__top">
            <span class="pgd-item__kode">#{{ $p->kode_unik }}</span>
            <span class="pgd-badge {{ $badgeClass }}">{{ strtoupper($p->status_label) }}</span>
          </div>
          <div class="pgd-item__cat">{{ \Illuminate\Support\Str::limit($p->kategori, 42) }}</div>
          <div class="pgd-item__date">
            <i class="ti tabler-clock me-1" style="font-size:0.65rem;"></i>
            {{ $p->created_at->translatedFormat('d F Y, H:i') }} WIB
          </div>
        </a>
      @empty
        <div class="pgd-empty">
          <div class="pgd-empty__icon"><i class="ti tabler-message-off text-white"></i></div>
          <div class="pgd-empty__title">Belum Ada Pengaduan</div>
          <div class="pgd-empty__sub">Buat pengaduan baru jika ada kendala terkait data presensi.</div>
        </div>
      @endforelse
    </div>
  </div>

  {{-- ── KANAN: Detail Pengaduan ────────────────────── --}}
  @if($activePengaduan)
    @php
      $detailBadgeClass = match($activePengaduan->status) {
        'baru'      => 'pgd-badge--baru',
        'diproses'  => 'pgd-badge--diproses',
        'selesai'   => 'pgd-badge--selesai',
        'ditolak'   => 'pgd-badge--ditolak',
        default     => 'pgd-badge--default',
      };
    @endphp
    <div class="pgd-detail">
      <div class="pgd-detail__head">
        <div style="min-width:0;">
          <div class="pgd-detail__kode">#{{ $activePengaduan->kode_unik }}</div>
          <h5 class="pgd-detail__title">{{ $activePengaduan->kategori }}</h5>
        </div>
        <span class="pgd-badge {{ $detailBadgeClass }}">{{ strtoupper($activePengaduan->status_label) }}</span>
      </div>
      <div class="pgd-detail__body">

        {{-- Deskripsi --}}
        <div class="pgd-desc-box">
          <div class="pgd-desc-box__label">
            <i class="ti tabler-file-text"></i> Deskripsi Pengaduan
          </div>
          <p class="pgd-desc-box__text">{{ $activePengaduan->deskripsi }}</p>

          @if($activePengaduan->catatan_admin)
            <div class="pgd-catatan-box">
              <div class="pgd-catatan-box__label">
                <i class="ti tabler-alert-circle"></i> Catatan Admin
              </div>
              <p class="pgd-catatan-box__text">{{ $activePengaduan->catatan_admin }}</p>
            </div>
          @endif
        </div>

        {{-- Timeline --}}
        <div class="pgd-timeline-label">
          <i class="ti tabler-history"></i> Riwayat Status
        </div>
        <ul class="pgd-timeline">
          @forelse($activeLogs as $log)
            @php
              $tlColor = match($log->status_ke) {
                'baru'      => 'baru',
                'diproses'  => 'diproses',
                'selesai'   => 'selesai',
                'ditolak'   => 'ditolak',
                default     => 'default',
              };
              $tlIcon = match($log->status_ke) {
                'baru'      => 'tabler-star',
                'diproses'  => 'tabler-loader',
                'selesai'   => 'tabler-circle-check',
                'ditolak'   => 'tabler-x',
                default     => 'tabler-dots',
              };
              $statusLabel = match($log->status_ke) {
                'baru'      => 'Baru Masuk',
                'diproses'  => 'Sedang Diproses',
                'selesai'   => 'Selesai',
                'ditolak'   => 'Ditolak',
                default     => ucfirst($log->status_ke),
              };
            @endphp
            <li class="pgd-tl-item">
              <div class="pgd-tl-dot pgd-tl-dot--{{ $tlColor }}">
                <i class="ti {{ $tlIcon }}"></i>
              </div>
              <div class="pgd-tl-content">
                <div class="pgd-tl-header">
                  <span class="pgd-tl-status pgd-tl-status--{{ $tlColor }}">{{ $statusLabel }}</span>
                  <span class="pgd-tl-time">{{ $log->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                </div>
                <p class="pgd-tl-note">{{ $log->catatan ?? 'Status pengaduan diperbarui.' }}</p>
                <span class="pgd-tl-oleh">oleh: {{ ucfirst($log->diubah_oleh) }}</span>
              </div>
            </li>
          @empty
            <li style="font-size:0.8rem;color:rgba(255,255,255,0.25);padding-left:2rem;">
              Belum ada riwayat perubahan status.
            </li>
          @endforelse
        </ul>

      </div>
    </div>
  @else
    <div class="pgd-detail">
      <div class="pgd-empty" style="padding: 4rem 1rem;">
        <div class="pgd-empty__icon" style="margin-bottom:1.25rem;background:rgba(115,103,240,0.08);border-color:rgba(115,103,240,0.2);">
          <i class="ti tabler-message-dots" style="color:rgba(115,103,240,0.5);"></i>
        </div>
        <div class="pgd-empty__title">Detail Pengaduan</div>
        <div class="pgd-empty__sub">Pilih salah satu pengaduan di sebelah kiri untuk melihat detail dan riwayat tindak lanjut.</div>
      </div>
    </div>
  @endif

</div>

{{-- ── MODAL BUAT PENGADUAN ──────────────────────────────────────────── --}}
<div class="modal fade" id="modalCreatePengaduan" tabindex="-1"
     aria-labelledby="modalCreatePengaduanLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="modalCreatePengaduanLabel">
          <i class="ti tabler-flag text-danger"></i> Buat Pengaduan Baru
        </h5>
        <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <form id="pengaduanForm" novalidate>
          @csrf
          <input type="hidden" name="status_pelapor" value="orang_tua">

          <div class="row g-3 mb-3">
            {{-- Nama Pelapor --}}
            <div class="col-md-6">
              <label for="nama_lengkap" class="form-label">
                <i class="ti tabler-user"></i> Nama Pelapor <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                     value="{{ auth()->user()->name }}" required readonly>
              <div class="invalid-feedback" id="nama_lengkap-error">Nama pelapor wajib terisi</div>
            </div>

            {{-- Nomor WhatsApp --}}
            <div class="col-md-6">
              <label for="nomor_wa" class="form-label">
                <i class="ti tabler-brand-whatsapp"></i> Nomor WhatsApp <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <input type="tel" class="form-control" id="nomor_wa" name="nomor_wa"
                       placeholder="08xxxxxxxxxx" required
                       value="{{ auth()->user()->no_hp ?? '' }}"
                       autocomplete="tel" minlength="10" maxlength="16"
                       style="border-radius:4px 0 0 4px !important;">
                <span class="input-group-text" id="waStatusIndicator"
                      style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-left:none;border-radius:0 4px 4px 0;color:rgba(255,255,255,0.3);">
                  <i class="ti tabler-brand-whatsapp" id="waStatusIcon"></i>
                </span>
              </div>
              <div class="invalid-feedback d-block" id="nomor_wa-error" style="display:none !important;"></div>
              <div class="pgd-wa-note">
                <i class="ti tabler-brand-whatsapp"></i>
                <span>Nomor WA untuk menerima <strong>kode tracking</strong> pengaduan</span>
              </div>
            </div>
          </div>

          {{-- Kategori --}}
          <div class="mb-3">
            <label for="kategori" class="form-label">
              <i class="ti tabler-category"></i> Kategori <span class="text-danger">*</span>
            </label>
            <select class="form-select select2" id="kategori" name="kategori" required
                    data-placeholder="— Pilih Kategori Pengaduan —">
              <option value="">— Pilih Kategori Pengaduan —</option>
              <optgroup label="Ketidakvalidan Status Presensi">
                <option value="Kehadiran Tercatat Alpa (Padahal Hadir)">Kehadiran Tercatat Alpa (Padahal Hadir)</option>
                <option value="Status Izin/Sakit Belum Diperbarui">Status Izin/Sakit Belum Diperbarui</option>
                <option value="Jam Presensi / Terlambat Tidak Sesuai">Jam Presensi / Terlambat Tidak Sesuai</option>
                <option value="Perbedaan Data Rekapitulasi Bulanan">Perbedaan Data Rekapitulasi Bulanan</option>
              </optgroup>
              <optgroup label="Kendala Teknis & Aplikasi">
                <option value="Gagal Scan QR / Sensor RFID">Gagal Scan QR / Sensor RFID</option>
                <option value="Masalah Aplikasi / GPS (Presensi Mandiri)">Masalah Aplikasi / GPS (Presensi Mandiri)</option>
                <option value="Notifikasi Presensi Tidak Masuk / Salah">Notifikasi Presensi Tidak Masuk / Salah</option>
              </optgroup>
              <optgroup label="Kesalahan Data Profil">
                <option value="Biodata Profil Salah (Nama/NIS/Kelas)">Biodata Profil Salah (Nama/NIS/Kelas)</option>
              </optgroup>
              <option value="Lainnya">Lainnya</option>
            </select>
            <div class="invalid-feedback" id="kategori-error">Silakan pilih kategori pengaduan</div>
          </div>

          {{-- Deskripsi --}}
          <div class="mb-1">
            <label for="deskripsi" class="form-label">
              <i class="ti tabler-file-description"></i> Deskripsi Pengaduan <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="deskripsi" name="deskripsi"
                      placeholder="Jelaskan secara mendetail pengaduan yang ingin dilaporkan..." required
                      minlength="10" maxlength="2000" style="min-height:120px;resize:vertical;"></textarea>
            <div class="d-flex justify-content-between mt-1">
              <div class="invalid-feedback d-inline" id="deskripsi-error">Deskripsi minimal 10 karakter</div>
              <small id="deskripsiCountWrapper" class="ms-auto" style="color:rgba(255,255,255,0.3);">
                <span id="deskripsiCount">0</span>/2000
              </small>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer d-flex justify-content-end">
        <button type="button" class="pgd-modal-cancel" data-bs-dismiss="modal">
          <i class="ti tabler-x fs-6"></i> Batal
        </button>
        <button type="submit" form="pengaduanForm" class="pgd-modal-submit" id="submitBtn">
          <span id="submitText">
            <i class="ti tabler-send fs-6"></i> Kirim Pengaduan
          </span>
          <span id="submitLoading" style="display:none !important;">
            <span class="spinner-border spinner-border-sm" role="status"></span> Mengirim...
          </span>
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form          = document.getElementById('pengaduanForm');
  const submitBtn     = document.getElementById('submitBtn');
  const submitText    = document.getElementById('submitText');
  const submitLoading = document.getElementById('submitLoading');
  const deskripsi     = document.getElementById('deskripsi');
  const deskripsiCount= document.getElementById('deskripsiCount');

  const modalEl = document.getElementById('modalCreatePengaduan');
  let modalInstance = null;
  if (typeof bootstrap !== 'undefined') {
    modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  }

  // ── Select2 ──────────────────────────────────────────
  function initSelect2() {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
      const $k = jQuery('#kategori');
      if ($k.length) {
        $k.select2({ placeholder: $k.data('placeholder'), dropdownParent: jQuery('#modalCreatePengaduan') });
        $k.on('change', function() { validateField(this); });
      }
    } else { setTimeout(initSelect2, 50); }
  }
  document.readyState === 'complete' ? initSelect2() : window.addEventListener('load', initSelect2);

  function getKategoriValue() {
    return (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined')
      ? jQuery('#kategori').val()
      : document.getElementById('kategori').value;
  }
  function resetKategori() {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
      jQuery('#kategori').val('').trigger('change.select2');
    } else { document.getElementById('kategori').value = ''; }
  }

  // ── Char counter ──────────────────────────────────────
  if (deskripsi) {
    deskripsi.addEventListener('input', function() {
      const len = Math.min(this.value.length, 2000);
      if (this.value.length > 2000) this.value = this.value.substring(0, 2000);
      deskripsiCount.textContent = len;
      const wrapper = document.getElementById('deskripsiCountWrapper');
      if (wrapper) wrapper.style.color = len > 1950 ? '#ea5455' : len > 1800 ? '#ff9f43' : 'rgba(255,255,255,0.3)';
    });
  }

  // ── WA Validation ────────────────────────────────────
  function isValidWA(n) { return /^(08|628)[0-9]{8,14}$/.test(n.replace(/\D/g,'')); }

  const waInput   = document.getElementById('nomor_wa');
  const waInd     = document.getElementById('waStatusIndicator');
  const waIcon    = document.getElementById('waStatusIcon');
  const waError   = document.getElementById('nomor_wa-error');
  let   waAbort   = null;

  function setWaStatus(status, msg='') {
    if (!waIcon || !waInd || !waInput || !waError) return;
    const states = {
      default:  { icon:'ti tabler-brand-whatsapp',       border:'rgba(255,255,255,0.1)', cls:'' },
      loading:  { icon:'spinner-border spinner-border-sm text-primary', border:'rgba(255,255,255,0.1)', cls:'' },
      valid:    { icon:'ti tabler-circle-check-filled text-success', border:'#28c76f', cls:'is-valid' },
      invalid:  { icon:'ti tabler-circle-x-filled text-danger', border:'#ea5455', cls:'is-invalid' },
    };
    const s = states[status] || states.default;
    waIcon.className = s.icon;
    waInd.style.borderColor = s.border;
    waInput.style.borderColor = s.border;
    waInput.classList.remove('is-valid','is-invalid');
    if (s.cls) waInput.classList.add(s.cls);
    waError.textContent = msg;
    waError.style.setProperty('display', status==='invalid' ? 'block' : 'none', 'important');
  }

  async function checkWaApi(number) {
    if (waAbort) waAbort.abort();
    waAbort = new AbortController();
    setWaStatus('loading'); submitBtn.disabled = true;
    try {
      const r = await fetch('/api/pengaduan/cek-wa?nomor_wa='+encodeURIComponent(number), {
        headers:{'Accept':'application/json'}, signal: waAbort.signal
      });
      const d = await r.json();
      if (r.ok && d.valid === true) { setWaStatus('valid'); }
      else { setWaStatus('invalid', d.message || 'Nomor tidak terdaftar atau tidak aktif.'); }
    } catch(e) {
      if (e.name === 'AbortError') return;
      setWaStatus('invalid','Gagal memeriksa nomor WhatsApp.');
    } finally { submitBtn.disabled = false; }
  }

  function handleWaValidation() {
    if (!waInput) return;
    const val = waInput.value.trim();
    if (!val) { setWaStatus('default'); return; }
    if (!isValidWA(val)) { setWaStatus('invalid','Format nomor harus diawali 08 atau 628 (10–16 digit)'); return; }
    checkWaApi(val);
  }

  if (waInput) {
    waInput.addEventListener('change', handleWaValidation);
    waInput.addEventListener('blur', handleWaValidation);
  }

  // ── Per-field validation ──────────────────────────────
  function validateField(input) {
    let valid = true;
    if (input.hasAttribute('required') && !input.value.trim()) valid = false;
    if (input.id === 'kategori' && getKategoriValue() === '') valid = false;
    const minLen = input.getAttribute('minlength');
    if (valid && minLen && input.value.trim().length < parseInt(minLen)) valid = false;
    if (input.id === 'nomor_wa') {
      const v = input.value.trim();
      if (!v || !isValidWA(v) || input.classList.contains('is-invalid')) valid = false;
    }
    if (input.id !== 'nomor_wa') input.classList.toggle('is-invalid', !valid);
    return valid;
  }

  form.querySelectorAll('[required], #nomor_wa').forEach(inp => {
    if (inp.id !== 'nomor_wa') {
      inp.addEventListener('blur', function() { validateField(this); });
      inp.addEventListener('input', function() { if (this.classList.contains('is-invalid')) validateField(this); });
    } else {
      inp.addEventListener('input', function() {
        if (this.classList.contains('is-invalid') && isValidWA(this.value.trim())) {
          this.classList.remove('is-invalid');
          waError.style.setProperty('display','none','important');
        }
      });
    }
  });

  // ── Submit ────────────────────────────────────────────
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    let allValid = true;
    form.querySelectorAll('[required], #nomor_wa').forEach(inp => { if (!validateField(inp)) allValid = false; });
    if (!allValid) {
      const first = form.querySelector('.is-invalid');
      if (first) { first.focus(); first.scrollIntoView({behavior:'smooth',block:'center'}); }
      return;
    }

    submitBtn.disabled = true;
    submitText.style.setProperty('display', 'none', 'important');
    submitLoading.style.setProperty('display', 'inline-flex', 'important');

    const data = {
      nama_lengkap: form.nama_lengkap.value.trim(),
      status_pelapor: 'orang_tua',
      kategori: getKategoriValue(),
      deskripsi: form.deskripsi.value.trim(),
      nomor_wa: form.nomor_wa.value.trim(),
    };

    try {
      const res = await fetch('/api/pengaduan', {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json',
                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
        body: JSON.stringify(data),
      });
      const result = await res.json();

      if (!res.ok) {
        if (result.errors) {
          const firstKey = Object.keys(result.errors)[0];
          const el = document.getElementById(firstKey);
          if (el) {
            el.classList.add('is-invalid');
            const errEl = document.getElementById(firstKey+'-error');
            if (errEl) errEl.textContent = result.errors[firstKey][0];
            el.focus(); el.scrollIntoView({behavior:'smooth',block:'center'});
          }
        }
        throw new Error(result.message || 'Terjadi kesalahan');
      }

      if (modalInstance) modalInstance.hide();

      const kode = result.kode_unik || (result.data && result.data.kode_unik) || '—';
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon:'success', title:'Pengaduan Terkirim!',
          html:`Pengaduan terdaftar dengan kode:<br><strong class="fs-4 text-success">${kode}</strong><br><br><small class="text-muted">Kode tracking dikirim ke WhatsApp Anda.</small>`,
          confirmButtonColor:'#7367f0', confirmButtonText:'OK',
          background:'#111827', color:'#fff'
        }).then(() => window.location.reload());
      } else {
        alert('Berhasil! Kode Tracking: ' + kode);
        window.location.reload();
      }

      form.reset(); resetKategori();
      form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      if (deskripsiCount) deskripsiCount.textContent = '0';

    } catch(err) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({icon:'error',title:'Gagal Mengirim',text:err.message||'Terjadi kesalahan. Coba lagi.',confirmButtonColor:'#7367f0',background:'#111827',color:'#fff'});
      } else { alert('Gagal: '+(err.message||'Error')); }
    } finally {
      submitBtn.disabled = false;
      submitText.style.setProperty('display', 'inline-flex', 'important');
      submitLoading.style.setProperty('display', 'none', 'important');
    }
  });

  if (waInput && waInput.value.trim()) handleWaValidation();
});
</script>
@endsection
