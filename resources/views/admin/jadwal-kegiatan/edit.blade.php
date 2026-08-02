@extends('layouts/layoutMaster')

@section('title', 'Edit Jadwal Kegiatan Berulang')

@section('page-style')
<style>
  /* Cegah overflow horizontal */
  html, body, .layout-wrapper, .layout-container, .layout-page, .content-wrapper, .das-panel__body {
    overflow-x: hidden !important;
    max-width: 100vw !important;
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

  /* ICON BUTTON */
  .das-icon-btn { width: 36px; height: 36px; border-radius: 5px; border: 1px solid var(--das-border); background: transparent; color: #888; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; text-decoration: none; cursor: pointer; position: relative; }
  .das-icon-btn:hover { background: var(--das-surface-hover); color: white; transform: translateY(-2px); }
  .das-icon-btn--secondary { border-color: var(--das-border); color: #999; }
  .das-icon-btn--secondary:hover { background: var(--das-surface-hover); color: white; border-color: var(--das-border-hover); }

  /* BUTTONS */
  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #999 !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }

  /* PANEL */
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); margin-bottom: 1.5rem; }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }
  .das-panel__body { padding: 1.5rem; }

  /* FORM ELEMENTS */
  .das-form-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #888; margin-bottom: .5rem; display: block; }
  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #e0e0e0 !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; outline: none !important; box-shadow: none !important; color: white !important; }
  .das-form-control option { background: #1a1a2e; color: #ccc; }
  textarea.das-form-control { resize: vertical; }

  .das-form-control[readonly], .das-form-control[disabled] { opacity: .7; cursor: default; }

  /* ALERT */
  .das-alert { display: flex; align-items: flex-start; gap: 10px; padding: .85rem 1.1rem; border-radius: var(--das-radius); font-size: .82rem; border: 1px solid transparent; }
  .das-alert--danger { background: var(--das-danger-soft); border-color: rgba(234,84,85,.25); color: #f7a7a8; }
  .das-alert__icon { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }
  .das-alert__list { margin: 0; padding-left: 1.2rem; }

  /* CHIP */
  .das-chip { display: inline-flex; align-items: center; font-size: .65rem; font-weight: 700; padding: 2px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; max-width: 100%; word-break: break-word; }
  .das-chip--primary { background: var(--das-primary-soft); color: var(--das-primary); }
  .das-chip--info { background: var(--das-info-soft); color: var(--das-info); }
  .das-chip--warning { background: var(--das-warning-soft); color: var(--das-warning); }

  /* ANIMATION */
  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }

  /* TOOLTIP */
  .das-tooltip { position: relative; }
  .das-tooltip:hover::after { content: attr(data-tip); position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%); background: #1a1a2e; color: #ccc; font-size: .65rem; font-weight: 600; padding: 4px 10px; border-radius: 4px; border: 1px solid var(--das-border); white-space: nowrap; z-index: 10; }

  /* ════════════════════════════════════════════════════════════
     TARGET SISWA — CUSTOM MULTI-SELECT SEARCH
     ════════════════════════════════════════════════════════════ */
  #targetSiswaSection .set-input-group {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    margin-top: .5rem;
    margin-bottom: .5rem;
  }
  #targetSiswaSection .set-input-prefix {
    position: absolute;
    left: 1.15rem;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, .35);
    transition: color .25s ease;
    z-index: 5;
    pointer-events: none;
    font-size: 1.1rem;
    line-height: 1;
  }
  #targetSiswaSection .set-input-group:focus-within .set-input-prefix {
    color: var(--das-primary);
  }
  #targetSiswaSection .set-input {
    width: 100% !important;
    max-width: 100% !important;
    background: rgba(255, 255, 255, .04) !important;
    border: 1px solid var(--das-border) !important;
    border-radius: var(--das-radius) !important;
    color: #e0e0e0 !important;
    font-family: inherit;
    font-size: .85rem !important;
    transition: all .25s ease-in-out;
    padding: .75rem 1.25rem .75rem 3rem;
    margin: 0 !important;
  }
  #targetSiswaSection .set-input::placeholder {
    color: rgba(255, 255, 255, .32);
  }
  #targetSiswaSection .set-input:focus {
    background: rgba(255, 255, 255, .07) !important;
    border-color: rgba(115, 103, 240, .5) !important;
    box-shadow: 0 0 0 2px rgba(115, 103, 240, .2) !important;
    color: #fff !important;
    outline: none !important;
  }

  #targetSiswaSection .individu-search-results {
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid var(--das-border);
    border-radius: var(--das-radius);
    margin-top: .65rem;
    width: 100%;
    background: rgba(15, 23, 42, .88) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, .3);
    z-index: 20;
  }
  #targetSiswaSection .individu-search-results:empty { display: none; }
  #targetSiswaSection .individu-search-results::-webkit-scrollbar { width: 4px; }
  #targetSiswaSection .individu-search-results::-webkit-scrollbar-track { background: transparent; }
  #targetSiswaSection .individu-search-results::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, .12);
    border-radius: 4px;
  }

  #targetSiswaSection .search-result-item {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .65rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid rgba(255, 255, 255, .04);
    border-left: 3px solid transparent;
    transition: all .2s ease-in-out;
    font-size: .83rem;
    color: rgba(255, 255, 255, .7);
  }
  #targetSiswaSection .search-result-item:last-child { border-bottom: none; }
  #targetSiswaSection .search-result-item:hover {
    background: var(--das-primary-soft);
    border-left-color: var(--das-primary);
    color: #fff;
  }
  #targetSiswaSection .search-result-item.is-selected {
    background: rgba(40, 199, 111, .06);
    border-left-color: var(--das-success);
    color: rgba(255, 255, 255, .55);
  }
  #targetSiswaSection .search-result-item.is-selected:hover {
    background: rgba(40, 199, 111, .1);
    color: rgba(255, 255, 255, .7);
  }
  #targetSiswaSection .search-result-item .sri-name {
    font-weight: 600;
    flex: 1;
    color: #fff;
  }
  #targetSiswaSection .search-result-item.is-selected .sri-name { color: rgba(255, 255, 255, .6); }
  #targetSiswaSection .search-result-item .sri-nip {
    font-size: .73rem;
    color: rgba(255, 255, 255, .45);
    font-family: monospace;
    background: rgba(255, 255, 255, .05);
    padding: 1px 6px;
    border-radius: 4px;
    white-space: nowrap;
  }
  #targetSiswaSection .search-result-item .sri-check {
    color: var(--das-success);
    font-size: .95rem;
    flex-shrink: 0;
  }

  #targetSiswaSection .search-empty-msg {
    text-align: center;
    padding: 1.5rem;
    font-size: .8rem;
    color: rgba(255, 255, 255, .35);
  }

  #targetSiswaSection .avatar-initials-mini {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7367f0, #00cfe8);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .7rem;
    color: #fff;
    font-weight: bold;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(115, 103, 240, .3);
  }

  #targetSiswaSection .selected-chip-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    margin-top: .75rem;
    min-height: 0;
  }
  #targetSiswaSection .selected-chip {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    padding: .45rem .85rem;
    border-radius: 8px;
    background: var(--das-primary-soft);
    border: 1px solid rgba(115, 103, 240, .25);
    color: #c8c4f8;
    font-size: .8rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(115, 103, 240, .1);
  }
  #targetSiswaSection .selected-chip .chip-remove {
    cursor: pointer;
    opacity: .6;
    font-size: .8rem;
    transition: all .2s ease-in-out;
    line-height: 1;
    color: rgba(255, 255, 255, .6);
    padding: 2px 6px;
    border-radius: 4px;
    background: rgba(255, 255, 255, .05);
  }
  #targetSiswaSection .selected-chip .chip-remove:hover {
    opacity: 1;
    color: var(--das-danger);
    background: rgba(234, 84, 85, .15);
  }
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
          <i class="ti tabler-calendar-repeat"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.6;">
              <li class="breadcrumb-item text-white opacity-60">Modul Khusus</li>
              <li class="breadcrumb-item text-white opacity-60">Jadwal Kegiatan</li>
              <li class="breadcrumb-item active text-white opacity-100">Edit</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">Edit Jadwal Kegiatan Berulang</h4>
          <p class="das-hero__welcome">Perbarui parameter jadwal berulang. Perubahan hanya berlaku untuk sesi mendatang.</p>
        </div>
      </div>
      <div class="das-hero__actions" style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.jadwal-kegiatan.index') }}"
           class="das-icon-btn das-icon-btn--secondary das-tooltip"
           data-tip="Kembali"
           data-bs-toggle="tooltip"
           title="Kembali ke daftar jadwal kegiatan">
          <i class="ti tabler-arrow-left"></i>
        </a>
      </div>
    </div>
  </div>

  {{-- ── ERROR VALIDATION ────────────────────────────── --}}
  @if ($errors->any())
    <div class="das-alert das-alert--danger slide-in-up mb-4">
      <i class="ti tabler-alert-triangle das-alert__icon"></i>
      <div>
        <strong class="d-block mb-1">Terdapat kesalahan input:</strong>
        <ul class="das-alert__list">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  <form action="{{ route('admin.jadwal-kegiatan.update', $jadwalKegiatan->id) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- ── PANEL 1: INFORMASI UTAMA & POLA JADWAL ──────────────── --}}
    <div class="das-panel slide-in-up">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot" style="background:var(--das-primary);box-shadow:0 0 6px var(--das-primary);"></span>
          1. Informasi Utama & Pola Jadwal
        </div>
        <span class="das-chip das-chip--info">ID: {{ $jadwalKegiatan->id }}</span>
      </div>
      <div class="das-panel__body">
        <div class="row g-4">
          {{-- Nama Kegiatan --}}
          <div class="col-md-6">
            <label class="das-form-label">Nama Kegiatan <span style="color:var(--das-danger);">*</span></label>
            <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $jadwalKegiatan->nama_kegiatan) }}"
                   class="form-control das-form-control" placeholder="Contoh: Sholat Dhuha Rutin" required>
          </div>

          {{-- Jenis Kegiatan --}}
          <div class="col-md-6">
            <label class="das-form-label">Jenis Kegiatan <span style="color:var(--das-danger);">*</span></label>
            <select name="jenis" class="form-select das-form-control" required>
              @foreach(['Seminar', 'Ekstrakurikuler', 'Lomba', 'Acara Internal', 'Lainnya'] as $j)
                <option value="{{ $j }}" {{ old('jenis', $jadwalKegiatan->jenis) == $j ? 'selected' : '' }}>{{ $j }}</option>
              @endforeach
            </select>
          </div>

          {{-- Tipe Pola Jadwal (Readonly) --}}
          <div class="col-12">
            <label class="das-form-label">Tipe Pola Jadwal</label>
            <div class="d-flex align-items-center gap-2 p-2.5 rounded" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border);">
              <span class="das-chip das-chip--warning me-1">
                <i class="ti tabler-lock me-1"></i> {{ str_replace('_', ' ', strtoupper($jadwalKegiatan->tipe_jadwal)) }}
              </span>
              <small class="text-white-50" style="font-size:.72rem;">
                Tipe pola jadwal dikunci dan tidak dapat diubah setelah dibuat untuk menjaga konsistensi sesi.
              </small>
            </div>
          </div>

          {{-- POLA HARI MINGGUAN --}}
          @if($jadwalKegiatan->tipe_jadwal !== 'tanggal_kalender')
            <div class="col-12">
              <label class="das-form-label">Hari Pelaksanaan <span style="color:var(--das-danger);">*</span></label>
              <div class="p-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius);">
                <div class="d-flex flex-wrap gap-3">
                  @php
                    $hariOptions = [
                      'senin'  => 'Senin',
                      'selasa' => 'Selasa',
                      'rabu'   => 'Rabu',
                      'kamis'  => 'Kamis',
                      'jumat'  => 'Jumat',
                      'sabtu'  => 'Sabtu',
                      'ahad'   => 'Ahad'
                    ];
                    $existingHari = is_array($jadwalKegiatan->hari) ? $jadwalKegiatan->hari : [];
                    $oldHari = old('hari', $existingHari);
                  @endphp
                  @foreach($hariOptions as $key => $label)
                    @php
                      $isChecked = in_array($key, $oldHari) || ($key === 'ahad' && in_array('minggu', $oldHari));
                    @endphp
                    <div class="form-check form-check-inline mb-0">
                      <input class="form-check-input" type="checkbox" name="hari[]" value="{{ $key }}" id="hari_{{ $key }}"
                             style="box-shadow:none !important;" {{ $isChecked ? 'checked' : '' }}>
                      <label class="form-check-label text-white small fw-medium" for="hari_{{ $key }}" style="cursor:pointer;">
                        {{ $label }}
                      </label>
                    </div>
                  @endforeach
                </div>
              </div>
              <small class="text-muted mt-1 d-block" style="font-size:.7rem;">
                <i class="ti tabler-info-circle"></i> Centang hari-hari pelaksanaan kegiatan berulang.
              </small>
            </div>
          @else
            {{-- POLA TANGGAL KALENDER --}}
            <div class="col-12">
              <label class="das-form-label">Tanggal Kalender Per Bulan <span style="color:var(--das-danger);">*</span></label>
              @php
                $existingDates = is_array($jadwalKegiatan->tanggal_kalender) ? implode(', ', $jadwalKegiatan->tanggal_kalender) : '';
              @endphp
              <input type="text" name="tanggal_kalender_input" class="form-control das-form-control"
                     value="{{ old('tanggal_kalender_input', $existingDates) }}" placeholder="Contoh: 5, 20">
              <small class="text-muted mt-1 d-block" style="font-size:.7rem;">
                <i class="ti tabler-info-circle"></i> Masukkan tanggal 1–31 dipisahkan dengan koma (contoh: <code>1, 15, 30</code>).
              </small>
            </div>
          @endif

          {{-- Waktu Mulai --}}
          <div class="col-md-6">
            <label class="das-form-label">Waktu Mulai</label>
            <input type="time" name="waktu_mulai" class="form-control das-form-control"
                   value="{{ old('waktu_mulai', $jadwalKegiatan->waktu_mulai ? substr($jadwalKegiatan->waktu_mulai,0,5) : '') }}">
          </div>

          {{-- Waktu Selesai --}}
          <div class="col-md-6">
            <label class="das-form-label">Waktu Selesai</label>
            <input type="time" name="waktu_selesai" class="form-control das-form-control"
                   value="{{ old('waktu_selesai', $jadwalKegiatan->waktu_selesai ? substr($jadwalKegiatan->waktu_selesai,0,5) : '') }}">
          </div>

          {{-- Lokasi Pelaksanaan --}}
          <div class="col-12">
            <label class="das-form-label">Lokasi Pelaksanaan</label>
            <input type="text" name="lokasi" class="form-control das-form-control"
                   value="{{ old('lokasi', $jadwalKegiatan->lokasi) }}" placeholder="Nama Ruangan/Lapangan">
          </div>

          {{-- Keterangan / Catatan --}}
          <div class="col-12">
            <label class="das-form-label">Deskripsi / Keterangan</label>
            <textarea name="keterangan" class="form-control das-form-control" rows="2"
                      placeholder="Tuliskan deskripsi atau catatan jadwal">{{ old('keterangan', $jadwalKegiatan->keterangan) }}</textarea>
          </div>
        </div>
      </div>
    </div>

    {{-- ── PANEL 2: INTEGRASI EKSTRAKURIKULER & MASA BERLAKU ─────── --}}
    <div class="das-panel slide-in-up">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot" style="background:var(--das-info);box-shadow:0 0 6px var(--das-info);"></span>
          2. Integrasi Ekskul & Masa Berlaku
        </div>
      </div>
      <div class="das-panel__body">
        <div class="row g-4">
          {{-- Tautkan ke Ekstrakurikuler --}}
          <div class="col-md-6">
            <label class="das-form-label">Tautkan ke Ekstrakurikuler <small class="text-muted" style="font-size:.65rem;text-transform:none;">(Opsional)</small></label>
            <select name="ekskul_id" class="form-select das-form-control">
              <option value="">-- Tidak Terikat Ekskul (Mandiri) --</option>
              @foreach($ekskuls as $e)
                <option value="{{ $e->id }}" {{ old('ekskul_id', $jadwalKegiatan->ekskul_id) == $e->id ? 'selected' : '' }}>{{ $e->nama }}</option>
              @endforeach
            </select>
          </div>

          {{-- Tautkan ke Jadwal Ekskul Existing --}}
          <div class="col-md-6">
            <label class="das-form-label">Tautkan ke Jadwal Ekskul Existing <small class="text-muted" style="font-size:.65rem;text-transform:none;">(Opsional)</small></label>
            <select name="ekskul_jadwal_id" class="form-select das-form-control">
              <option value="">-- Buat Waktu / Hari Terpisah --</option>
              @foreach($ekskuls as $e)
                @foreach($e->jadwal as $ej)
                  <option value="{{ $ej->id }}" {{ old('ekskul_jadwal_id', $jadwalKegiatan->ekskul_jadwal_id) == $ej->id ? 'selected' : '' }}>
                    {{ $e->nama }} — {{ $ej->hari === 'minggu' ? 'Ahad' : ucfirst($ej->hari) }} ({{ substr($ej->jam_mulai,0,5) }} - {{ substr($ej->jam_selesai,0,5) }}) @ {{ $ej->lokasi }}
                  </option>
                @endforeach
              @endforeach
            </select>
          </div>

          {{-- Tanggal Mulai Berlaku --}}
          <div class="col-md-6">
            <label class="das-form-label">Tanggal Mulai Berlaku <span style="color:var(--das-danger);">*</span></label>
            <input type="date" name="tanggal_mulai" class="form-control das-form-control"
                   value="{{ old('tanggal_mulai', $jadwalKegiatan->tanggal_mulai?->format('Y-m-d')) }}" required>
          </div>

          {{-- Tanggal Selesai Berlaku --}}
          <div class="col-md-6">
            <label class="das-form-label">Tanggal Selesai Berlaku <small class="text-muted" style="font-size:.65rem;text-transform:none;">(Opsional)</small></label>
            <input type="date" name="tanggal_selesai" class="form-control das-form-control"
                   value="{{ old('tanggal_selesai', $jadwalKegiatan->tanggal_selesai?->format('Y-m-d')) }}">
          </div>

          {{-- QR Code Prefix --}}
          <div class="col-md-6">
            <label class="das-form-label">QR Code Prefix <small class="text-muted" style="font-size:.65rem;text-transform:none;">(Opsional)</small></label>
            <input type="text" name="qr_code_prefix" class="form-control das-form-control text-uppercase"
                   value="{{ old('qr_code_prefix', $jadwalKegiatan->qr_code_prefix) }}" placeholder="Contoh: DHR">
          </div>

          {{-- Tahun Akademik --}}
          <div class="col-md-6">
            <label class="das-form-label">Tahun Akademik</label>
            <select name="tahun_akademik_id" class="form-select das-form-control">
              <option value="">-- Berlaku Semua Tahun Akademik --</option>
              @foreach($tahunAkademiks as $ta)
                <option value="{{ $ta->id }}" {{ old('tahun_akademik_id', $jadwalKegiatan->tahun_akademik_id) == $ta->id ? 'selected' : '' }}>
                  {{ $ta->nama }} {{ $ta->is_aktif ? '(Aktif)' : '' }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Switches --}}
          <div class="col-md-6">
            <div class="p-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius); height:100%;">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="is_wajib" value="1" id="is_wajib"
                       style="width:40px;height:20px;cursor:pointer;"
                       {{ old('is_wajib', $jadwalKegiatan->is_wajib) ? 'checked' : '' }}>
                <label class="form-check-label text-white small fw-semibold ms-2" for="is_wajib" style="cursor:pointer;font-size:.82rem;">
                  <i class="ti tabler-alert-triangle text-warning me-1"></i> Wajib Hadir
                </label>
                <small class="text-muted d-block mt-1 ms-2" style="font-size:.7rem;">
                  <i class="ti tabler-info-circle"></i> Jika diaktifkan, peserta diwajibkan hadir pada sesi rutin ini.
                </small>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="p-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius); height:100%;">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="is_aktif" value="1" id="is_aktif"
                       style="width:40px;height:20px;cursor:pointer;"
                       {{ old('is_aktif', $jadwalKegiatan->is_aktif) ? 'checked' : '' }}>
                <label class="form-check-label text-white small fw-semibold ms-2" for="is_aktif" style="cursor:pointer;font-size:.82rem;">
                  <i class="ti tabler-circle-check text-success me-1"></i> Status Jadwal Aktif
                </label>
                <small class="text-muted d-block mt-1 ms-2" style="font-size:.7rem;">
                  <i class="ti tabler-info-circle"></i> Matikan switch ini untuk menonaktifkan pembuatan sesi otomatis sementara.
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── PANEL 3: TARGET PESERTA & SEGMENTASI ────────────────── --}}
    <div class="das-panel slide-in-up">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot" style="background:var(--das-warning);box-shadow:0 0 6px var(--das-warning);"></span>
          3. Target Peserta & Segmentasi Absensi (Opsional)
        </div>
      </div>
      <div class="das-panel__body">
        <div class="row g-4">
          {{-- Target Jenis Kelamin --}}
          <div class="col-md-5">
            <label class="das-form-label">Target Jenis Kelamin <small class="text-muted" style="font-size:.65rem;text-transform:none;">(Opsional)</small></label>
            <select name="target_gender" class="form-select das-form-control">
              <option value="" {{ old('target_gender', $jadwalKegiatan->target_gender) === null || old('target_gender', $jadwalKegiatan->target_gender) === '' ? 'selected' : '' }}>Semua Jenis Kelamin (Laki-laki & Perempuan)</option>
              <option value="L" {{ old('target_gender', $jadwalKegiatan->target_gender) === 'L' ? 'selected' : '' }}>Laki-laki Saja</option>
              <option value="P" {{ old('target_gender', $jadwalKegiatan->target_gender) === 'P' ? 'selected' : '' }}>Perempuan Saja</option>
            </select>
            <small class="text-muted mt-1 d-block" style="font-size:.7rem;">
              <i class="ti tabler-info-circle"></i> Filter absensi berdasarkan gender siswa.
            </small>
          </div>

          {{-- Target Tingkat --}}
          <div class="col-md-7">
            <label class="das-form-label">Target Peserta (Berdasarkan Tingkat)</label>
            <div class="p-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius); min-height:42px;">
              <div class="d-flex flex-wrap gap-3">
                @php $oldTingkat = old('target_tingkat', $jadwalKegiatan->target_tingkat ?? []); @endphp
                @foreach($tingkat as $t)
                  <div class="form-check mb-0">
                    <input class="form-check-input checkbox-tingkat" type="checkbox" name="target_tingkat[]" value="{{ $t }}" id="tingkat_{{ $t }}"
                           data-tingkat="{{ $t }}"
                           {{ (is_array($oldTingkat) && in_array($t, $oldTingkat)) ? 'checked' : '' }}>
                    <label class="form-check-label text-white small fw-medium" for="tingkat_{{ $t }}">
                      Tingkat {{ $t }}
                    </label>
                  </div>
                @endforeach
              </div>
            </div>
            <small class="text-muted mt-1 d-block" style="font-size:.7rem;">
              <i class="ti tabler-info-circle"></i> Centang tingkat untuk menargetkan seluruh kelas pada tingkat tersebut.
            </small>
          </div>

          {{-- Target Jurusan --}}
          <div class="col-12">
            <label class="das-form-label">Target Jurusan <small class="text-muted" style="font-size:.65rem;text-transform:none;">(Opsional)</small></label>
            <div class="p-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius);">
              <div class="row g-2">
                @php $oldJurusan = old('target_jurusan', $jadwalKegiatan->target_jurusan ?? []); @endphp
                @forelse($jurusanList as $jurusan)
                  <div class="col-md-3 col-6">
                    <div class="form-check mb-0">
                      <input class="form-check-input jurusan-checkbox" type="checkbox" name="target_jurusan[]" value="{{ $jurusan }}" id="jurusan_{{ \Illuminate\Support\Str::slug($jurusan) }}"
                             {{ (is_array($oldJurusan) && in_array($jurusan, $oldJurusan)) ? 'checked' : '' }}>
                      <label class="form-check-label text-white small fw-medium" for="jurusan_{{ \Illuminate\Support\Str::slug($jurusan) }}">
                        {{ $jurusan }}
                      </label>
                    </div>
                  </div>
                @empty
                  <div class="col-12">
                    <p class="text-muted small mb-0" style="font-size:.75rem;">
                      <i class="ti tabler-info-circle me-1"></i>Tidak ada data jurusan tersedia.
                    </p>
                  </div>
                @endforelse
              </div>
            </div>
          </div>

          {{-- Target Kelas Spesifik --}}
          <div class="col-12">
            <label class="das-form-label">Target Peserta (Kelas Spesifik)</label>
            <div class="p-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius);">
              <div class="row g-2">
                @php $oldPeserta = old('target_peserta', $jadwalKegiatan->target_peserta ?? []); @endphp
                @foreach($kelas as $k)
                  <div class="col-md-3 col-6 checkbox-kelas-wrapper" data-tingkat="{{ $k->tingkat }}" data-jurusan="{{ $k->jurusan?->nama ?? '' }}">
                    <div class="form-check mb-0">
                      <input class="form-check-input checkbox-kelas" type="checkbox" name="target_peserta[]" value="{{ $k->id }}" id="kelas_{{ $k->id }}"
                             {{ (is_array($oldPeserta) && in_array($k->id, $oldPeserta)) ? 'checked' : '' }}>
                      <label class="form-check-label text-white small" for="kelas_{{ $k->id }}">
                        {{ $k->nama }}
                      </label>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
            <small class="text-muted mt-1 d-block" style="font-size: .7rem;">
              <i class="ti tabler-info-circle"></i> Gunakan pilihan kelas spesifik jika jadwal hanya ditujukan untuk kelas tertentu.
            </small>
          </div>

          {{-- Target Siswa Individu - Custom Multi-Select Search --}}
          <div class="col-12">
            <label class="das-form-label">Target Siswa Spesifik (Per Individu) <small class="text-muted" style="font-size:.65rem;text-transform:none;">(Opsional)</small></label>
            <div id="targetSiswaSection">
              <select name="target_siswa[]" multiple hidden id="select_target_siswa" tabindex="-1" aria-hidden="true">
                @php $oldSiswa = old('target_siswa', $jadwalKegiatan->target_siswa ?? []); @endphp
                @foreach($siswaList as $s)
                  <option value="{{ $s->id }}" {{ (is_array($oldSiswa) && in_array($s->id, $oldSiswa)) ? 'selected' : '' }}>
                    {{ $s->nama_lengkap }} — ({{ $s->kelas ? $s->kelas->nama : '-' }} / NIS: {{ $s->nis ?? '-' }})
                  </option>
                @endforeach
              </select>

              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-search"></i></span>
                <input type="text" class="set-input" id="searchTargetSiswa"
                       placeholder="Ketik nama, NIS atau NISN siswa..." autocomplete="off"
                       aria-label="Cari siswa untuk target individu">
              </div>

              <div class="selected-chip-wrap" id="selectedSiswaChipWrap" aria-live="polite"></div>
              <div class="individu-search-results" id="siswaSearchResultsList" role="listbox" aria-label="Hasil pencarian siswa"></div>

              <small class="text-muted mt-1 d-block" style="font-size: .7rem;">
                <i class="ti tabler-info-circle"></i> Cari nama siswa, NIS atau NISN untuk menambahkan target individu jadwal kegiatan ini.
              </small>
            </div>
          </div>
        </div>

        {{-- Submit Button Actions --}}
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top:1px solid var(--das-border);">
          <a href="{{ route('admin.jadwal-kegiatan.index') }}" class="das-btn das-btn--ghost">
            <i class="ti tabler-x"></i> Batal
          </a>
          <button type="submit" class="das-btn das-btn--primary px-4">
            <i class="ti tabler-device-floppy"></i> Perbarui Jadwal Berulang
          </button>
        </div>
      </div>
    </div>
  </form>

@endsection

@section('page-script')
  @php
    $siswaData = $siswaList->map(fn($s) => [
        'id'           => $s->id,
        'nama_lengkap' => $s->nama_lengkap,
        'nis'          => $s->nis,
        'nisn'         => $s->nisn,
        'kelas'        => $s->kelas ? $s->kelas->nama : '-',
    ])->values();
  @endphp
  <script>
    // ════════════════════════════════════════════════════════════
    // TARGET SISWA — CUSTOM MULTI-SELECT SEARCH
    // ════════════════════════════════════════════════════════════
    const DATA_SISWA_JADWAL = @json($siswaData);

    document.addEventListener('DOMContentLoaded', function () {
      const sectionEl = document.getElementById('targetSiswaSection');
      const selectEl  = document.getElementById('select_target_siswa');
      const searchEl  = document.getElementById('searchTargetSiswa');
      const chipWrap  = document.getElementById('selectedSiswaChipWrap');
      const listEl    = document.getElementById('siswaSearchResultsList');
      if (!sectionEl || !selectEl || !searchEl || !chipWrap || !listEl) return;

      let selectedIds = [];

      function escHtml(str) {
        if (!str) return '';
        return String(str)
          .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
      }

      function buildSub(item) {
        const kelas = item.kelas || '-';
        const nis   = item.nis || '-';
        return `(${kelas} / NIS: ${nis})`;
      }

      function buildLabel(item) {
        return `${item.nama_lengkap} — ${buildSub(item)}`;
      }

      function searchable(item) {
        return `${item.nama_lengkap} ${item.nis || ''} ${item.nisn || ''} ${item.kelas || ''}`.toLowerCase();
      }

      function findSiswa(id) {
        return DATA_SISWA_JADWAL.find(d => String(d.id) === String(id)) || null;
      }

      function syncSelect() {
        Array.from(selectEl.options).forEach(opt => {
          opt.selected = selectedIds.some(id => String(id) === opt.value);
        });
      }

      function renderChips() {
        if (selectedIds.length === 0) { chipWrap.innerHTML = ''; return; }
        chipWrap.innerHTML = selectedIds.map(id => {
          const item = findSiswa(id);
          if (!item) return '';
          return `<div class="selected-chip">
            <div class="avatar-initials-mini">${escHtml(item.nama_lengkap.charAt(0).toUpperCase())}</div>
            <span>${escHtml(buildLabel(item))}</span>
            <span class="chip-remove" data-id="${item.id}" title="Hapus ${escHtml(item.nama_lengkap)}" aria-label="Hapus ${escHtml(item.nama_lengkap)}" role="button">✕</span>
          </div>`;
        }).join('');
      }

      function addSiswa(id) {
        if (selectedIds.some(i => String(i) === String(id))) return;
        selectedIds.push(id);
        renderChips();
        syncSelect();
        searchEl.value = '';
        listEl.innerHTML = '';
      }

      function removeSiswa(id) {
        selectedIds = selectedIds.filter(i => String(i) !== String(id));
        renderChips();
        syncSelect();
      }

      function renderSearchResults(term) {
        if (!term) { listEl.innerHTML = ''; return; }
        const lc = term.toLowerCase();
        const filtered = DATA_SISWA_JADWAL
          .filter(item => searchable(item).includes(lc))
          .slice(0, 40);

        if (filtered.length === 0) {
          listEl.innerHTML = `<div class="search-empty-msg"><i class="ti tabler-search-off" style="font-size:1.4rem;display:block;margin:0 auto 0.35rem;"></i>Tidak ada hasil untuk "<strong>${escHtml(term)}</strong>"</div>`;
          return;
        }

        listEl.innerHTML = filtered.map(item => {
          const isSelected = selectedIds.some(i => String(i) === String(item.id));
          return `<div class="search-result-item${isSelected ? ' is-selected' : ''}" data-id="${item.id}" role="option" aria-selected="${isSelected}">
            <div class="avatar-initials-mini">${escHtml(item.nama_lengkap.charAt(0).toUpperCase())}</div>
            <span class="sri-name">${escHtml(item.nama_lengkap)}</span>
            <span class="sri-nip">${escHtml(buildSub(item))}</span>
            ${isSelected ? '<span class="sri-check"><i class="ti tabler-check"></i></span>' : ''}
          </div>`;
        }).join('');
      }

      selectedIds = Array.from(selectEl.options).filter(o => o.selected).map(o => o.value);
      renderChips();
      syncSelect();

      searchEl.addEventListener('input', function () {
        renderSearchResults(this.value.trim());
      });

      listEl.addEventListener('click', function (e) {
        const item = e.target.closest('.search-result-item');
        if (item && item.dataset.id) addSiswa(item.dataset.id);
      });

      chipWrap.addEventListener('click', function (e) {
        const rm = e.target.closest('.chip-remove');
        if (rm && rm.dataset.id) removeSiswa(rm.dataset.id);
      });

      document.addEventListener('click', function (e) {
        if (!e.target.closest('#targetSiswaSection')) listEl.innerHTML = '';
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') listEl.innerHTML = '';
      });
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltips.map(el => new bootstrap.Tooltip(el));

      const tingkatCheckboxes = document.querySelectorAll('.checkbox-tingkat');
      const kelasWrappers = document.querySelectorAll('.checkbox-kelas-wrapper');

      function updateKelasVisibility() {
        const selectedTingkats = Array.from(tingkatCheckboxes)
          .filter(cb => cb.checked)
          .map(cb => cb.dataset.tingkat);

        kelasWrappers.forEach(wrapper => {
          const tingkat = wrapper.dataset.tingkat;
          const checkbox = wrapper.querySelector('.checkbox-kelas');
          
          if (selectedTingkats.includes(tingkat)) {
            wrapper.style.opacity = '0.5';
            wrapper.style.pointerEvents = 'none';
            checkbox.checked = false;
          } else {
            wrapper.style.opacity = '1';
            wrapper.style.pointerEvents = 'auto';
          }
        });
      }

      tingkatCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateKelasVisibility);
      });

      updateKelasVisibility();

      // Jurusan Checkboxes
      const jurusanCheckboxes = document.querySelectorAll('.jurusan-checkbox');

      function updateKelasByJurusan() {
        const selectedJurusan = Array.from(jurusanCheckboxes)
          .filter(cb => cb.checked)
          .map(cb => cb.value);

        kelasWrappers.forEach(wrapper => {
          const jurusan = wrapper.dataset.jurusan;
          if (!jurusan) return;

          const checkbox = wrapper.querySelector('.checkbox-kelas');
          const isDisabled = wrapper.style.pointerEvents === 'none';

          if (selectedJurusan.includes(jurusan) && !isDisabled) {
            checkbox.checked = true;
          }
        });
      }

      function updateJurusanFromKelas() {
        jurusanCheckboxes.forEach(jcb => {
          const jurusan = jcb.value;
          const relatedWrappers = Array.from(kelasWrappers)
            .filter(w => w.dataset.jurusan === jurusan && w.style.pointerEvents !== 'none');

          if (relatedWrappers.length > 0) {
            const allChecked = relatedWrappers.every(w => w.querySelector('.checkbox-kelas').checked);
            jcb.checked = allChecked;
          }
        });
      }

      jurusanCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
          updateKelasByJurusan();
          updateJurusanFromKelas();
        });
      });

      document.querySelectorAll('.checkbox-kelas').forEach(cb => {
        cb.addEventListener('change', updateJurusanFromKelas);
      });

      tingkatCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateJurusanFromKelas);
      });

      updateKelasByJurusan();
      updateJurusanFromKelas();
    });
  </script>
@endsection
