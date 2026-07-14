@extends('layouts/layoutMaster')

@section('title', $isEdit ? 'Edit Template ID Card' : 'Buat Template ID Card')

@section('vendor-style')
<style>
    #id-card-preview-container {
        position: relative;
        background-color: #ffffff;
        border: 2px dashed #cbd5e1;
        margin: 0 auto;
        overflow: hidden;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        border-radius: var(--border-radius, 5px);
        transform: scale(var(--zoom-factor, 2));
        transform-origin: center center;
        transition: transform 0.2s ease-in-out;
    }
    #id-card-canvas {
        position: relative;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
    }
    .draggable-element {
        position: absolute;
        border: 1px dashed rgba(115, 103, 240, 0.2);
        cursor: move;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 4px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .draggable-element:hover {
        border-color: #7367f0;
        background: rgba(115, 103, 240, 0.25);
        box-shadow: 0 0 8px rgba(115, 103, 240, 0.6);
    }
    .draggable-element.selected-element {
        border: 2px solid #7367f0 !important;
        box-shadow: 0 0 10px rgba(115, 103, 240, 0.8) !important;
        background: rgba(115, 103, 240, 0.15);
    }
    .element-photo { background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; }
    .element-qr { background: #ffffff; border: 1px solid #000000; color: #000000; }
    .element-logo_lembaga, .element-logo_dinas, .element-ttd_kepala_sekolah, .element-cap_lembaga { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; font-size: 10px; }
    .element-text { white-space: nowrap; font-weight: bold; }

    /* Custom premium dark theme form fields */
    .card-body .form-control,
    .card-body .form-select {
        background: rgba(15, 23, 42, 0.4) !important;
        color: white !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
    }
    .card-body .form-control::placeholder {
        color: rgba(255, 255, 255, 0.4) !important;
    }
    .card-body .form-control:focus,
    .card-body .form-select:focus {
        border-color: rgba(115, 103, 240, 0.6) !important;
        box-shadow: 0 0 0 0.2rem rgba(115, 103, 240, 0.25) !important;
        background: rgba(15, 23, 42, 0.6) !important;
    }
    .card-body .form-control-color {
        padding: 0.3rem 0.5rem !important;
    }
    .form-check-input {
        background-color: rgba(15, 23, 42, 0.4) !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
    }
    .form-check-input:checked {
        background-color: #7367f0 !important;
        border-color: #7367f0 !important;
    }

    /* Accordion Custom Styling */
    #elementAccordion .accordion-item {
        background: transparent !important;
        border-color: rgba(255,255,255,0.08) !important;
    }
    #elementAccordion .accordion-button.collapsed {
        background: transparent !important;
        color: #fff !important;
    }
    #elementAccordion .accordion-button:not(.collapsed) {
        background: rgba(115, 103, 240, 0.1) !important;
        color: #7367f0 !important;
        box-shadow: none !important;
    }
    #elementAccordion .accordion-button::after {
        filter: invert(1);
    }
    #elementAccordion .accordion-body {
        background: transparent !important;
    }
    #elementAccordion .accordion-body label {
        color: rgba(255, 255, 255, 0.6) !important;
    }

    /* Sticky Preview */
    .sticky-preview-wrapper {
        position: relative;
    }
    .sticky-preview-wrapper .sticky-preview-inner {
        position: sticky;
        top: 90px;
        align-self: flex-start;
    }
    @media (max-width: 991.98px) {
        .sticky-preview-wrapper .sticky-preview-inner {
            position: static;
        }
    }
    .btn-remove-custom-text {
        position: absolute;
        right: 40px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
        padding: 4px 8px;
        z-index: 5;
        font-size: 18px;
        line-height: 1;
        border-radius: 4px;
        transition: background 0.15s;
    }
    .btn-remove-custom-text:hover {
        background: rgba(239, 68, 68, 0.15);
    }</style>
@endsection

@section('content')
{{-- HERO HEADER --}}
<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 text-white overflow-hidden shadow-lg"
      style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 4px;">
      <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
            style="width:52px;height:52px;border-radius:12px !important;background:rgba(115,103,240,0.2);border:1px solid rgba(115,103,240,0.4);">
            <i class="ti {{ $isEdit ? 'tabler-pencil' : 'tabler-plus' }} text-primary fs-3"></i>
          </div>
          <div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                <li class="breadcrumb-item"><a href="{{ route('admin.id-card-templates.index') }}" class="text-white text-decoration-none">Template ID Card</a></li>
                <li class="breadcrumb-item active text-white">{{ $isEdit ? 'Ubah' : 'Buat' }}</li>
              </ol>
            </nav>
            <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
              {{ $isEdit ? 'Ubah Template ID Card' : 'Buat Template Baru' }}
            </h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<form action="{{ $isEdit ? route('admin.id-card-templates.update', $template->id) : route('admin.id-card-templates.store') }}" method="POST" enctype="multipart/form-data" id="templateForm" novalidate>
    @csrf
    @if($isEdit) @method('PUT') @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171;">
            <div class="d-flex align-items-center mb-2">
                <i class="ti tabler-ban me-2" style="font-size: 1.25rem;"></i>
                <strong class="text-white">Terjadi kesalahan validasi!</strong>
            </div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Sidebar Controls -->
        <div class="col-xl-4 col-lg-5 col-md-12">
            <div class="card mb-4" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="card-header border-bottom py-3 d-flex align-items-center gap-2" style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
                    <i class="ti tabler-settings text-primary"></i>
                    <h5 class="card-title mb-0 text-white">Konfigurasi Template</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 mt-3">
                        <label class="form-label text-white-50 small">Nama Template</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" placeholder="Contoh: Kartu Siswa Biru" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50 small">Tipe Kartu</label>
                        <select name="type" class="form-select" required id="cardType">
                            <option value="siswa" {{ old('type', $template->type) == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ old('type', $template->type) == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="staff" {{ old('type', $template->type) == 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="pelepasan" {{ old('type', $template->type) == 'pelepasan' ? 'selected' : '' }}>Pelepasan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50 small">Background Kartu (PNG/JPG)</label>
                        <input type="file" name="background" class="form-control" id="bgInput" accept="image/*">
                        
                        <div class="text-center my-2 text-white-50 small">— ATAU —</div>
                        
                        <label class="form-label text-white-50 small">Link Gambar Eksternal (URL)</label>
                        <input type="url" name="background_url" class="form-control" id="bgUrlInput" 
                               value="{{ old('background_url', (isset($template) && str_starts_with($template->background_path ?? '', 'http')) ? $template->background_path : '') }}" 
                               placeholder="https://example.com/background.png">
                        
                        @if($template->background_path && !str_starts_with($template->background_path, 'http'))
                            <small class="text-white-50 d-block mt-1">Current: {{ basename($template->background_path) }}</small>
                        @endif
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label text-white small" for="isActive">Jadikan Template Aktif</label>
                    </div>

                    <input type="hidden" name="config" id="configInput">

                    <div class="mb-4">
                        <label class="form-label text-white-50 small d-flex justify-content-between align-items-center">
                            <span>Border Radius (Rounded Corner)</span>
                            <span class="badge bg-label-primary rounded-pill" id="borderRadiusValue" style="font-size:0.7rem;">5px</span>
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" id="borderRadiusSlider" class="form-range flex-grow-1" min="0" max="5" step="1" value="5" style="height:6px;">
                            <span class="text-white-50 small" style="min-width:24px;text-align:right;" id="borderRadiusLabel">5</span>
                        </div>
                        <small class="text-white-50 d-block mt-1" style="font-size:0.65rem;">Atur tingkat kelengkungan sudut kartu (0 = kotak, 5 = maksimal rounded)</small>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.08) !important;">
                    
                    <div class="mb-4">
                        <label class="form-label text-white-50 small mb-2 d-flex justify-content-between align-items-center">
                            <span>Palet Elemen (Tarik ke Kartu)</span>
                            <span class="badge bg-label-primary rounded-pill" style="font-size:0.65rem;">Drag & Drop</span>
                        </label>
                        <div id="element-palette" class="d-flex flex-wrap gap-2 p-3 rounded" style="background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.1); min-height: 50px;">
                            <!-- Badge elemen draggable akan di-render dinamis via JS -->
                        </div>
                        <div class="mt-2">
                            <button type="button" id="addCustomTextBtn" class="btn btn-sm btn-outline-info w-100" style="border-style: dashed;">
                                <i class="ti tabler-plus me-1"></i> Tambah Teks Kustom
                            </button>
                        </div>
                    </div>

                    <div class="accordion" id="elementAccordion">
                        @php
                            $orderedStandard = ['photo', 'qr', 'name', 'id_number', 'nis', 'nisn', 'nip', 'class', 'gender', 'ttl', 'masa_berlaku', 'logo_lembaga', 'logo_dinas', 'nama_lembaga', 'alamat_lembaga', 'tempat_tanggal_terbit', 'ttd_kepala_sekolah', 'cap_lembaga', 'nama_kepala_sekolah', 'nip_kepala_sekolah'];
                            $customTextKeys = [];
                            if (isset($template->config['elements'])) {
                                foreach (array_keys($template->config['elements']) as $ek) {
                                    if (str_starts_with($ek, 'custom_text_')) {
                                        $customTextKeys[] = $ek;
                                    }
                                }
                            }
                            sort($customTextKeys);
                            $dividerKeys = [];
                            if (isset($template->config['elements'])) {
                                foreach (array_keys($template->config['elements']) as $ek) {
                                    if (str_starts_with($ek, 'divider_')) {
                                        $dividerKeys[] = $ek;
                                    }
                                }
                            }
                            if (empty($dividerKeys)) {
                                $dividerKeys = ['divider_1', 'divider_2'];
                            }
                            sort($dividerKeys);
                            $allEls = array_merge($orderedStandard, $customTextKeys, $dividerKeys);
                        @endphp
                        @foreach($allEls as $el)
                        <div class="accordion-item">
                            <h2 class="accordion-header" style="position: relative;">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $el }}">
                                    @if($el === 'photo')
                                        Foto
                                    @elseif($el === 'qr')
                                        QR Code
                                    @elseif($el === 'name')
                                        Nama Lengkap
                                    @elseif($el === 'id_number')
                                        ID Number (NIS/NIP)
                                    @elseif($el === 'nis')
                                        NIS (Siswa)
                                    @elseif($el === 'nisn')
                                        NISN (Siswa)
                                    @elseif($el === 'nip')
                                        NIP (Guru/Staff)
                                    @elseif($el === 'class')
                                        Kelas / Jabatan
                                    @elseif($el === 'gender')
                                        Jenis Kelamin
                                    @elseif($el === 'ttl')
                                        Tempat Tanggal Lahir
                                    @elseif($el === 'masa_berlaku')
                                        Masa Berlaku
                                    @elseif($el === 'logo_lembaga')
                                        Logo Lembaga
                                    @elseif($el === 'logo_dinas')
                                        Logo Dinas
                                    @elseif($el === 'nama_lembaga')
                                        Nama Lembaga
                                    @elseif($el === 'alamat_lembaga')
                                        Alamat Lembaga
                                    @elseif($el === 'tempat_tanggal_terbit')
                                        Tempat Tanggal Terbit
                                    @elseif($el === 'ttd_kepala_sekolah')
                                        TTD Kepala Sekolah
                                    @elseif($el === 'cap_lembaga')
                                        Cap Lembaga / Stempel
                                    @elseif($el === 'nama_kepala_sekolah')
                                        Nama Kepala Sekolah
                                    @elseif($el === 'nip_kepala_sekolah')
                                        NIP Kepala Sekolah
                                    @elseif(str_starts_with($el, 'custom_text_'))
                                        Teks Kustom {{ str_replace('custom_text_', '', $el) }}
                                    @elseif(str_starts_with($el, 'divider_'))
                                        Garis Pembatas {{ str_replace('divider_', '', $el) }}
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $el)) }}
                                    @endif
                                </button>
                                @if(str_starts_with($el, 'custom_text_') || str_starts_with($el, 'divider_'))
                                <button type="button" class="btn-remove-custom-text" data-el="{{ $el }}" title="Hapus elemen">
                                    <i class="ti tabler-x"></i>
                                </button>
                                @endif
                            </h2>
                            <div id="collapse{{ $el }}" class="accordion-collapse collapse" data-bs-parent="#elementAccordion">
                                <div class="accordion-body p-2">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="small">Posisi X</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="x">
                                        </div>
                                        <div class="col-6">
                                            <label class="small">Posisi Y</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="y">
                                        </div>
                                        <div class="col-12 mt-1">
                                            <label class="small">Z-Index</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="z_index" min="1" default="1">
                                        </div>
                                        @if(in_array($el, ['photo', 'qr', 'logo_lembaga', 'logo_dinas', 'ttd_kepala_sekolah', 'cap_lembaga']))
                                        <div class="col-6">
                                            <label class="small">Lebar (W)</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="w">
                                        </div>
                                        <div class="col-6">
                                            <label class="small">Tinggi (H)</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="h">
                                        </div>
                                        @elseif(str_starts_with($el, 'divider_'))
                                        <div class="col-6">
                                            <label class="small">Lebar (W)</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="w">
                                        </div>
                                        <div class="col-6">
                                            <label class="small">Tinggi/Tebal (H)</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="h">
                                        </div>
                                        <div class="col-12">
                                            <label class="small">Warna</label>
                                            <input type="color" class="form-control form-control-sm form-control-color w-100 config-sync" data-el="{{ $el }}" data-prop="color">
                                        </div>
                                        @else
                                        @if(str_starts_with($el, 'custom_text_'))
                                        <div class="col-12">
                                            <label class="small">Teks Konten</label>
                                            <input type="text" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="content">
                                        </div>
                                        @endif
                                        <div class="col-6">
                                            <label class="small">Ukuran Font</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="size">
                                        </div>
                                        <div class="col-6">
                                            <label class="small">Warna</label>
                                            <input type="color" class="form-control form-control-sm form-control-color w-100 config-sync" data-el="{{ $el }}" data-prop="color">
                                        </div>
                                        <div class="col-12">
                                             <label class="small">Align</label>
                                             <select class="form-select form-select-sm config-sync" data-el="{{ $el }}" data-prop="align">
                                                 <option value="left">Left</option>
                                                 <option value="center">Center</option>
                                                 <option value="right">Right</option>
                                             </select>
                                         </div>
                                         <div class="col-12 mt-2">
                                             <label class="small">Kapitalisasi (Case)</label>
                                             <select class="form-select form-select-sm config-sync" data-el="{{ $el }}" data-prop="transform">
                                                 <option value="none">Default (Asli)</option>
                                                 <option value="uppercase">UPPERCASE</option>
                                                 <option value="lowercase">lowercase</option>
                                                 <option value="capitalize">Capitalize Each Word</option>
                                             </select>
                                         </div>
                                         <div class="col-6 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input config-sync" type="checkbox" data-el="{{ $el }}" data-prop="bold">
                                                <label class="form-check-label text-white-50 small">Bold</label>
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input config-sync" type="checkbox" data-el="{{ $el }}" data-prop="italic">
                                                <label class="form-check-label text-white-50 small">Italic</label>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-12 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input config-sync" type="checkbox" data-el="{{ $el }}" data-prop="show" checked>
                                                <label class="form-check-label text-white-50 small">Tampilkan Elemen</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer border-top py-3" style="border-color:rgba(255,255,255,0.08) !important; background:transparent;">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti tabler-device-floppy me-1"></i> Simpan Template
                    </button>
                    <a href="{{ route('admin.id-card-templates.index') }}" class="btn btn-label-secondary w-100 mt-2">Batal</a>
                </div>
            </div>
        </div>

        <!-- Designer Preview -->
        <div class="col-xl-8 col-lg-7 col-md-12 sticky-preview-wrapper">
            <div class="sticky-preview-inner">
            <div class="card" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center" style="border-color:rgba(255,255,255,0.08) !important; background:transparent;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti tabler-eye text-primary"></i>
                        <h5 class="card-title mb-0 text-white">Live Preview (Skala 1:1)</h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-white-50 small mb-0">Zoom:</label>
                            <select class="form-select form-select-sm" id="zoomSelect" style="width: auto; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 0.2rem 0.5rem;">
                                <option value="1">100% (Skala 1:1)</option>
                                <option value="1.5">150%</option>
                                <option value="2" selected>200% (Rekomendasi)</option>
                                <option value="2.5">250%</option>
                                <option value="3">300%</option>
                            </select>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary text-white" id="btnPortrait">Portrait</button>
                            <button type="button" class="btn btn-outline-primary active" id="btnLandscape">Landscape</button>
                        </div>
                    </div>
                </div>
                <div class="card-body py-5 overflow-auto" style="background: #f1f5f9;">
                    <div class="d-flex align-items-center justify-content-center p-3" style="min-height: 520px; overflow: auto; width: 100%;">
                        <div id="id-card-preview-container">
                            @php
                              $bgUrl = '';
                              if ($template->background_path) {
                                  if (str_starts_with($template->background_path, 'http://') || str_starts_with($template->background_path, 'https://')) {
                                      $bgUrl = $template->background_path;
                                  } elseif (strlen($template->background_path) > 30 && !str_contains($template->background_path, '/')) {
                                      $bgUrl = 'https://drive.google.com/thumbnail?id=' . $template->background_path . '&sz=w800&_t=' . time();
                                  } else {
                                      $bgUrl = asset('storage/' . $template->background_path);
                                  }
                              }
                            @endphp
                            <div id="id-card-canvas" style="background-image: url('{{ $bgUrl }}')">
                                <!-- Elements will be rendered here via JS -->
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <p class="text-secondary small mb-0"><i class="ti tabler-info-circle me-1 text-primary"></i> Geser elemen di preview untuk memindahkan posisi secara instan.</p>
                    </div>
                </div>
            </div>
            </div> <!-- sticky-preview-inner -->
        </div>
    </div>
</form>

<div id="element-context-menu" class="dropdown-menu shadow py-1" style="display: none; position: absolute; z-index: 9999; background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 6px;">
    <button type="button" class="dropdown-item text-white py-2 d-flex align-items-center gap-2" id="btn-edit-element" style="font-size: 0.8rem; font-weight: 600;">
        <i class="ti tabler-pencil text-primary" style="font-size: 1rem;"></i> Edit Elemen
    </button>
    <button type="button" class="dropdown-item text-white py-2 d-flex align-items-center gap-2" id="btn-duplicate-element" style="font-size: 0.8rem; font-weight: 600;">
        <i class="ti tabler-copy text-info" style="font-size: 1rem;"></i> Duplikat Elemen
    </button>
    <button type="button" class="dropdown-item text-white py-2 d-flex align-items-center gap-2" id="btn-front-element" style="font-size: 0.8rem; font-weight: 600;">
        <i class="ti tabler-chevron-up text-success" style="font-size: 1rem;"></i> Bawa ke Paling Depan
    </button>
    <button type="button" class="dropdown-item text-white py-2 d-flex align-items-center gap-2" id="btn-back-element" style="font-size: 0.8rem; font-weight: 600;">
        <i class="ti tabler-chevron-down text-warning" style="font-size: 1rem;"></i> Kirim ke Paling Belakang
    </button>
    <div class="dropdown-divider my-1" style="border-color: rgba(255,255,255,0.08);"></div>
    <button type="button" class="dropdown-item text-danger py-2 d-flex align-items-center gap-2" id="btn-delete-element" style="font-size: 0.8rem; font-weight: 600;">
        <i class="ti tabler-trash" style="font-size: 1rem;"></i> Hapus Elemen
    </button>
</div>
@endsection

@push('scripts')
<script>
const samples = @json($samples ?? []);
const lembaga = @json($lembaga ?? []);

document.addEventListener('DOMContentLoaded', function() {
    // Initial Config from PHP
    let config = @json($template->config);
    let selectedElementKey = null; // Menyimpan elemen terpilih untuk navigasi keyboard
    const canvas = document.getElementById('id-card-canvas');
    const container = document.getElementById('id-card-preview-container');
    const configInput = document.getElementById('configInput');
    const bgInput = document.getElementById('bgInput');
    const palette = document.getElementById('element-palette');

    function getFriendlyName(key) {
        const names = {
            'photo': 'Foto',
            'qr': 'QR Code',
            'name': 'Nama Lengkap',
            'nis': 'NIS (Siswa)',
            'nisn': 'NISN (Siswa)',
            'nip': 'NIP (Guru/Staff)',
            'class': 'Kelas / Jabatan',
            'gender': 'Jenis Kelamin',
            'ttl': 'Tempat Tanggal Lahir',
            'masa_berlaku': 'Masa Berlaku',
            'logo_lembaga': 'Logo Lembaga',
            'logo_dinas': 'Logo Dinas',
            'nama_lembaga': 'Nama Lembaga',
            'alamat_lembaga': 'Alamat Lembaga',
            'tempat_tanggal_terbit': 'Tempat Tanggal Terbit',
            'ttd_kepala_sekolah': 'TTD Kepala Sekolah',
            'cap_lembaga': 'Cap Lembaga / Stempel',
            'nama_kepala_sekolah': 'Nama Kepala Sekolah',
            'nip_kepala_sekolah': 'NIP Kepala Sekolah'
        };
        if (names[key]) return names[key];
        // Handle dynamic custom_text_N
        const matchText = key.match(/^custom_text_(\d+)$/);
        if (matchText) {
            return 'Teks Kustom ' + matchText[1];
        }
        // Handle dynamic divider_N
        const matchDivider = key.match(/^divider_(\d+)$/);
        if (matchDivider) {
            return 'Garis Pembatas ' + matchDivider[1];
        }
        return key.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    // Update Dimensions & Border Radius
    function updateCanvasSize() {
        container.style.width = config.canvas.width + 'px';
        container.style.height = config.canvas.height + 'px';
        const br = config.canvas.border_radius ?? 5;
        container.style.setProperty('--border-radius', br + 'px');
        container.style.borderRadius = br + 'px';
    }

    function renderElements() {
        canvas.innerHTML = '';
        palette.innerHTML = '';
        const cardType = document.getElementById('cardType').value;
        let sample = null;
        if (cardType === 'siswa' || cardType === 'pelepasan') {
            sample = samples.siswa;
        } else if (cardType === 'guru') {
            sample = samples.guru;
        } else if (cardType === 'staff') {
            sample = samples.staff;
        }

        Object.keys(config.elements).forEach(key => {
            const el = config.elements[key];
            if (!el.show) {
                const badge = document.createElement('div');
                badge.className = 'badge bg-label-secondary cursor-move p-2 border border-dashed border-secondary';
                badge.draggable = true;
                badge.innerText = getFriendlyName(key);
                badge.dataset.el = key;
                badge.addEventListener('dragstart', e => {
                    e.dataTransfer.setData('text/plain', key);
                });
                palette.appendChild(badge);
                return;
            }

            const div = document.createElement('div');
            const isImageEl = ['photo', 'qr', 'logo_lembaga', 'logo_dinas', 'ttd_kepala_sekolah', 'cap_lembaga'].includes(key);
            const isDividerEl = key.startsWith('divider_');
            
            if (isDividerEl) {
                div.className = 'draggable-element element-divider';
            } else {
                div.className = 'draggable-element element-' + (isImageEl ? key : 'text');
            }
            
            div.id = 'el-' + key;
            div.style.left = el.x + 'px';
            div.style.top = el.y + 'px';
            div.style.zIndex = el.z_index ?? 1;

            if (selectedElementKey === key) {
                div.classList.add('selected-element');
            }

            if (isDividerEl) {
                div.style.width = el.w + 'px';
                div.style.height = el.h + 'px';
                div.style.backgroundColor = el.color;
            } else if (isImageEl) {
                div.style.width = el.w + 'px';
                div.style.height = el.h + 'px';
                if (key === 'photo') {
                    if (sample && sample.photo) {
                        div.innerHTML = `<img src="${sample.photo}" style="width:100%; height:100%; object-fit:cover;">`;
                    } else {
                        div.innerHTML = '<i class="ti tabler-user"></i> FOTO';
                    }
                } else if (key === 'qr') {
                    // Determine the QR data from sample (nisn for students, nip for guru/staff, fallback to 'ABSENSI')
                    let qrData = 'ABSENSI_PREVIEW';
                    if (sample) {
                        qrData = sample.nisn || sample.nip || 'ABSENSI';
                    }
                    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(qrData)}`;
                    div.innerHTML = `<img src="${qrUrl}" style="width:100%; height:100%; object-fit:contain; background:#fff; padding:2px;">`;
                } else if (key === 'logo_lembaga') {
                    if (lembaga && lembaga.logo_base64) {
                        div.innerHTML = `<img src="${lembaga.logo_base64}" style="width:100%; height:100%; object-fit:contain;">`;
                    } else {
                        div.innerHTML = '<i class="ti tabler-school"></i> LOGO';
                    }
                } else if (key === 'logo_dinas') {
                    if (lembaga && lembaga.logo_dinas_base64) {
                        div.innerHTML = `<img src="${lembaga.logo_dinas_base64}" style="width:100%; height:100%; object-fit:contain;">`;
                    } else {
                        div.innerHTML = '<i class="ti tabler-building"></i> LOGO DINAS';
                    }
                } else if (key === 'ttd_kepala_sekolah') {
                    if (lembaga && lembaga.ttd_base64) {
                        div.innerHTML = `<img src="${lembaga.ttd_base64}" style="width:100%; height:100%; object-fit:contain;">`;
                    } else if (lembaga && lembaga.ttd_url) {
                        div.innerHTML = `<img src="${lembaga.ttd_url}" style="width:100%; height:100%; object-fit:contain;">`;
                    } else {
                        div.innerHTML = '<i class="ti tabler-writing-sign"></i> TTD KEPSEK';
                    }
                } else if (key === 'cap_lembaga') {
                    if (lembaga && lembaga.cap_base64) {
                        div.innerHTML = `<img src="${lembaga.cap_base64}" style="width:100%; height:100%; object-fit:contain;">`;
                    } else if (lembaga && lembaga.cap_url) {
                        div.innerHTML = `<img src="${lembaga.cap_url}" style="width:100%; height:100%; object-fit:contain;">`;
                    } else {
                        div.innerHTML = '<i class="ti tabler-stamp"></i> STEMPEL';
                    }
                }
            } else {
                div.style.fontSize = el.size + 'px';
                div.style.color = el.color;
                div.style.textAlign = el.align;
                div.innerText = getLabelFor(key);
                
                // Formatting Bold & Italic
                div.style.fontWeight = el.bold ? 'bold' : 'normal';
                div.style.fontStyle = el.italic ? 'italic' : 'normal';
                
                // Formatting transform
                div.style.textTransform = el.transform || 'none';
                
                // Adjust width for center/right align
                if(el.align === 'center') {
                    div.style.width = config.canvas.width + 'px';
                    div.style.left = '0';
                }
            }

            canvas.appendChild(div);
            makeDraggable(div, key);
        });
        
        configInput.value = JSON.stringify(config);
        updateControlInputs();
    }

    function getLabelFor(key) {
        const cardType = document.getElementById('cardType').value;
        let sample = null;
        if (cardType === 'siswa' || cardType === 'pelepasan') {
            sample = samples.siswa;
        } else if (cardType === 'guru') {
            sample = samples.guru;
        } else if (cardType === 'staff') {
            sample = samples.staff;
        }

        if(key === 'name') return sample ? sample.name : 'NAMA LENGKAP';
        if(key === 'nis') return sample ? sample.nis : '-';
        if(key === 'nisn') return sample ? sample.nisn : '-';
        if(key === 'nip') return sample ? sample.nip : '-';
        if(key === 'id_number') return sample ? 'NISN: ' + sample.nisn : 'NISN: -';
        if(key === 'class') return sample ? sample.class : '-';
        if(key === 'gender') return sample ? sample.gender : '-';
        if(key === 'ttl') return sample ? sample.ttl : '-';
        if(key === 'masa_berlaku') return sample ? sample.masa_berlaku : 'Selama menjadi anggota aktif';
        if(key === 'nama_lembaga') return lembaga.nama_sekolah || 'NAMA SEKOLAH';
        if(key === 'alamat_lembaga') return lembaga.alamat_lembaga || 'Alamat Sekolah';
        if(key === 'tempat_tanggal_terbit') return (lembaga.kota_penerbitan || 'Kota') + ', ' + new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'});
        if(key === 'nama_kepala_sekolah') return lembaga.nama_kepala_lembaga || 'Nama Kepala Sekolah';
        if(key === 'nip_kepala_sekolah') return lembaga.nip_kepala_lembaga ? 'NIP. ' + lembaga.nip_kepala_lembaga : 'NIP. -';
        if(key.startsWith('custom_text_')) {
            return config.elements[key].content || 'Teks Kustom';
        }
        return key.toUpperCase();
    }

    // Fungsi untuk menambah custom text baru
    function addCustomText() {
        // Cari nomor custom text terbesar
        let maxNum = 0;
        Object.keys(config.elements).forEach(k => {
            const m = k.match(/^custom_text_(\d+)$/);
            if (m) maxNum = Math.max(maxNum, parseInt(m[1], 10));
        });
        const newNum = maxNum + 1;
        const newKey = 'custom_text_' + newNum;

        // Default config untuk custom text baru
        const defaultY = 140 + (newNum - 1) * 10;
        config.elements[newKey] = {
            x: 10,
            y: defaultY,
            size: 8,
            color: '#000000',
            show: true,
            align: 'center',
            content: 'Teks Kustom Baru',
            bold: false,
            italic: false,
            transform: 'none'
        };

        // Buat accordion item baru dan sisipkan sebelum divider_1
        const accordion = document.getElementById('elementAccordion');
        const dividerItem = document.querySelector('[data-bs-target="#collapsedivider_1"]');
        const newItem = createCustomTextAccordionItem(newKey, newNum);
        if (dividerItem) {
            accordion.insertBefore(newItem, dividerItem.closest('.accordion-item'));
        } else {
            accordion.appendChild(newItem);
        }

        // Re-render preview & palette
        renderElements();

        // Buka accordion item baru
        setTimeout(() => {
            const collapseEl = document.getElementById('collapse' + newKey);
            if (collapseEl) {
                const bsCollapse = new bootstrap.Collapse(collapseEl, { toggle: true });
            }
        }, 100);
    }

    // Fungsi untuk menghapus custom text
    function removeCustomText(key) {
        if (!confirm('Hapus teks kustom ini?')) return;
        delete config.elements[key];
        // Hapus accordion item dari DOM
        const collapseEl = document.getElementById('collapse' + key);
        if (collapseEl) {
            const accordionItem = collapseEl.closest('.accordion-item');
            if (accordionItem) accordionItem.remove();
        }
        renderElements();
    }

    // Helper untuk membuat HTML accordion item custom text
    function createCustomTextAccordionItem(key, num) {
        const div = document.createElement('div');
        div.className = 'accordion-item';
        div.innerHTML = `
            <h2 class="accordion-header" style="position: relative;">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${key}">
                    Teks Kustom ${num}
                </button>
                <button type="button" class="btn-remove-custom-text" data-el="${key}" title="Hapus teks kustom">
                    <i class="ti tabler-x"></i>
                </button>
            </h2>
            <div id="collapse${key}" class="accordion-collapse collapse" data-bs-parent="#elementAccordion">
                <div class="accordion-body p-2">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="small">Posisi X</label>
                            <input type="number" class="form-control form-control-sm config-sync" data-el="${key}" data-prop="x">
                        </div>
                        <div class="col-6">
                            <label class="small">Posisi Y</label>
                            <input type="number" class="form-control form-control-sm config-sync" data-el="${key}" data-prop="y">
                        </div>
                        <div class="col-12 mt-1">
                            <label class="small">Z-Index</label>
                            <input type="number" class="form-control form-control-sm config-sync" data-el="${key}" data-prop="z_index" min="1" default="1">
                        </div>
                        <div class="col-12">
                            <label class="small">Teks Konten</label>
                            <input type="text" class="form-control form-control-sm config-sync" data-el="${key}" data-prop="content">
                        </div>
                        <div class="col-6">
                            <label class="small">Ukuran Font</label>
                            <input type="number" class="form-control form-control-sm config-sync" data-el="${key}" data-prop="size">
                        </div>
                        <div class="col-6">
                            <label class="small">Warna</label>
                            <input type="color" class="form-control form-control-sm form-control-color w-100 config-sync" data-el="${key}" data-prop="color">
                        </div>
                        <div class="col-12">
                            <label class="small">Align</label>
                            <select class="form-select form-select-sm config-sync" data-el="${key}" data-prop="align">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                        <div class="col-12 mt-2">
                            <label class="small">Kapitalisasi (Case)</label>
                            <select class="form-select form-select-sm config-sync" data-el="${key}" data-prop="transform">
                                <option value="none">Default (Asli)</option>
                                <option value="uppercase">UPPERCASE</option>
                                <option value="lowercase">lowercase</option>
                                <option value="capitalize">Capitalize Each Word</option>
                            </select>
                        </div>
                        <div class="col-6 mt-2">
                            <div class="form-check">
                                <input class="form-check-input config-sync" type="checkbox" data-el="${key}" data-prop="bold">
                                <label class="form-check-label text-white-50 small">Bold</label>
                            </div>
                        </div>
                        <div class="col-6 mt-2">
                            <div class="form-check">
                                <input class="form-check-input config-sync" type="checkbox" data-el="${key}" data-prop="italic">
                                <label class="form-check-label text-white-50 small">Italic</label>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="form-check">
                                <input class="form-check-input config-sync" type="checkbox" data-el="${key}" data-prop="show" checked>
                                <label class="form-check-label text-white-50 small">Tampilkan Elemen</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        return div;
    }

    function createDividerAccordionItem(key, num) {
        const div = document.createElement('div');
        div.className = 'accordion-item';
        div.innerHTML = `
            <h2 class="accordion-header" style="position: relative;">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${key}">
                    Garis Pembatas ${num}
                </button>
                <button type="button" class="btn-remove-custom-text" data-el="${key}" title="Hapus garis pembatas">
                    <i class="ti tabler-x"></i>
                </button>
            </h2>
            <div id="collapse${key}" class="accordion-collapse collapse" data-bs-parent="#elementAccordion">
                <div class="accordion-body p-2">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="small">Posisi X</label>
                            <input type="number" class="form-control form-control-sm config-sync" data-el="${key}" data-prop="x">
                        </div>
                        <div class="col-6">
                            <label class="small">Posisi Y</label>
                            <input type="number" class="form-control form-control-sm config-sync" data-el="${key}" data-prop="y">
                        </div>
                        <div class="col-12 mt-1">
                            <label class="small">Z-Index</label>
                            <input type="number" class="form-control form-control-sm config-sync" data-el="${key}" data-prop="z_index" min="1" default="1">
                        </div>
                        <div class="col-6">
                            <label class="small">Lebar (W)</label>
                            <input type="number" class="form-control form-control-sm config-sync" data-el="${key}" data-prop="w">
                        </div>
                        <div class="col-6">
                            <label class="small">Tinggi/Tebal (H)</label>
                            <input type="number" class="form-control form-control-sm config-sync" data-el="${key}" data-prop="h">
                        </div>
                        <div class="col-12">
                            <label class="small">Warna</label>
                            <input type="color" class="form-control form-control-sm form-control-color w-100 config-sync" data-el="${key}" data-prop="color">
                        </div>
                        <div class="col-12 mt-2">
                            <div class="form-check">
                                <input class="form-check-input config-sync" type="checkbox" data-el="${key}" data-prop="show" checked>
                                <label class="form-check-label text-white-50 small">Tampilkan Elemen</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        return div;
    }

    function updateControlInputs() {
        document.querySelectorAll('.config-sync').forEach(input => {
            const el = input.dataset.el;
            const prop = input.dataset.prop;
            if(config.elements[el]) {
                const val = config.elements[el][prop];
                if(input.type === 'checkbox') {
                    input.checked = !!val;
                } else {
                    input.value = (val === undefined || val === null) ? '' : val;
                }
            }
        });
    }

    // Drag Logic
    function makeDraggable(el, key) {
        let isDragging = false;
        let lastX, lastY;

        el.addEventListener('mousedown', e => {
            if (e.button === 0) { // Klik kiri saja
                // Hapus style select lama
                document.querySelectorAll('.draggable-element').forEach(item => {
                    item.classList.remove('selected-element');
                });
                selectedElementKey = key;
                el.classList.add('selected-element');
            }
            isDragging = true;
            lastX = e.clientX;
            lastY = e.clientY;
            e.preventDefault();
        });

        document.addEventListener('mousemove', e => {
            if (!isDragging) return;
            const zoomSelect = document.getElementById('zoomSelect');
            const zoom = zoomSelect ? parseFloat(zoomSelect.value) : 1.0;

            const dx = (e.clientX - lastX) / zoom;
            const dy = (e.clientY - lastY) / zoom;

            let nx = config.elements[key].x + dx;
            let ny = config.elements[key].y + dy;

            // Bounds check
            nx = Math.max(0, Math.min(nx, config.canvas.width - el.offsetWidth));
            ny = Math.max(0, Math.min(ny, config.canvas.height - el.offsetHeight));

            // Snap to grid if name/id/class is center aligned
            const isImageEl = ['photo', 'qr', 'logo_lembaga', 'logo_dinas', 'ttd_kepala_sekolah', 'cap_lembaga'].includes(key);
            const isDividerEl = key.startsWith('divider_');
            if(!isImageEl && !isDividerEl && config.elements[key].align === 'center') {
                nx = 0;
            }

            el.style.left = nx + 'px';
            el.style.top = ny + 'px';

            config.elements[key].x = nx;
            config.elements[key].y = ny;

            lastX = e.clientX;
            lastY = e.clientY;

            updateControlInputs();
            configInput.value = JSON.stringify(config);
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
        });
    }

    // Dragover & Drop Events pada Canvas
    canvas.addEventListener('dragover', e => {
        e.preventDefault();
        canvas.style.border = '2px dashed #7367f0';
    });

    canvas.addEventListener('dragleave', e => {
        e.preventDefault();
        canvas.style.border = '';
    });

    canvas.addEventListener('drop', e => {
        e.preventDefault();
        canvas.style.border = '';
        const key = e.dataTransfer.getData('text/plain');
        if (!key || !config.elements[key]) return;

        const rect = canvas.getBoundingClientRect();
        const zoom = zoomSelect ? parseFloat(zoomSelect.value) : 1.0;

        let rx = (e.clientX - rect.left) / zoom;
        let ry = (e.clientY - rect.top) / zoom;

        // Default dimensions offset center
        let w = config.elements[key].w || 70;
        let h = config.elements[key].h || 15;

        let nx = rx - (w / 2);
        let ny = ry - (h / 2);

        // Bounds check
        nx = Math.max(0, Math.min(nx, config.canvas.width - w));
        ny = Math.max(0, Math.min(ny, config.canvas.height - h));

        config.elements[key].x = Math.round(nx);
        config.elements[key].y = Math.round(ny);
        config.elements[key].show = true;

        renderElements();
    });

    // Event Listeners for Controls using Event Delegation (bind to #elementAccordion for custom element input sync)
    document.getElementById('elementAccordion').addEventListener('input', e => {
        const input = e.target.closest('.config-sync');
        if (!input) return;
        
        const el = input.dataset.el;
        const prop = input.dataset.prop;
        let val = input.type === 'checkbox' ? input.checked : input.value;
        if(input.type === 'number') val = parseInt(val);
        
        config.elements[el][prop] = val;
        renderElements();
        
        // Also update hidden config input
        configInput.value = JSON.stringify(config);
    });

    document.getElementById('btnPortrait').addEventListener('click', () => {
        const currentBr = config.canvas.border_radius ?? 5;
        config.canvas = { width: 153, height: 243, border_radius: currentBr };
        document.getElementById('btnPortrait').classList.add('active', 'btn-outline-primary');
        document.getElementById('btnLandscape').classList.remove('active', 'btn-outline-primary');
        updateCanvasSize();
        renderElements();
    });

    document.getElementById('btnLandscape').addEventListener('click', () => {
        const currentBr = config.canvas.border_radius ?? 5;
        config.canvas = { width: 243, height: 153, border_radius: currentBr };
        document.getElementById('btnLandscape').classList.add('active', 'btn-outline-primary');
        document.getElementById('btnPortrait').classList.remove('active', 'btn-outline-primary');
        updateCanvasSize();
        renderElements();
    });

    bgInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                canvas.style.backgroundImage = 'url(' + e.target.result + ')';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    const bgUrlInput = document.getElementById('bgUrlInput');
    if (bgUrlInput) {
        bgUrlInput.addEventListener('input', function() {
            const val = this.value.trim();
            if (val) {
                canvas.style.backgroundImage = 'url(' + val + ')';
            } else {
                // Jika kosong, kembalikan ke file input
                const fileInput = document.getElementById('bgInput');
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        canvas.style.backgroundImage = 'url(' + e.target.result + ')';
                    }
                    reader.readAsDataURL(fileInput.files[0]);
                } else {
                    canvas.style.backgroundImage = '';
                }
            }
        });
    }

    document.getElementById('cardType').addEventListener('change', () => {
        renderElements();
    });

    // Tombol + Tambah Teks Kustom
    document.getElementById('addCustomTextBtn').addEventListener('click', addCustomText);

    // Event delegation untuk tombol × hapus custom text
    document.getElementById('elementAccordion').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-remove-custom-text');
        if (btn) {
            e.stopPropagation();
            const key = btn.dataset.el;
            if (key) removeCustomText(key);
        }
    });

    // Event listener keydown pada document untuk menggerakkan elemen terpilih
    document.addEventListener('keydown', e => {
        if (!selectedElementKey || !config.elements[selectedElementKey]) return;
        
        // Lewati jika user sedang mengetik di input field atau select
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
            return;
        }

        const arrowKeys = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'];
        if (!arrowKeys.includes(e.key)) return;

        e.preventDefault();

        const step = e.shiftKey ? 10 : 1; // 10px jika Shift ditekan, 1px jika biasa
        const elConfig = config.elements[selectedElementKey];
        const domEl = document.getElementById('el-' + selectedElementKey);

        if (!domEl) return;

        let nx = elConfig.x;
        let ny = elConfig.y;

        if (e.key === 'ArrowUp') {
            ny = Math.max(0, ny - step);
        } else if (e.key === 'ArrowDown') {
            ny = Math.min(config.canvas.height - domEl.offsetHeight, ny + step);
        } else if (e.key === 'ArrowLeft') {
            nx = Math.max(0, nx - step);
        } else if (e.key === 'ArrowRight') {
            nx = Math.min(config.canvas.width - domEl.offsetWidth, nx + step);
        }

        // Align center check (horizontal block jika align center untuk text)
        const isImageEl = ['photo', 'qr', 'logo_lembaga', 'logo_dinas', 'ttd_kepala_sekolah', 'cap_lembaga'].includes(selectedElementKey);
        const isDividerEl = selectedElementKey.startsWith('divider_');
        if (!isImageEl && !isDividerEl && elConfig.align === 'center') {
            nx = 0;
        }

        nx = Math.round(nx);
        ny = Math.round(ny);

        elConfig.x = nx;
        elConfig.y = ny;
        domEl.style.left = nx + 'px';
        domEl.style.top = ny + 'px';

        updateControlInputs();
        configInput.value = JSON.stringify(config);
    });

    // Zoom Event Listener
    const zoomSelect = document.getElementById('zoomSelect');
    function updateZoom() {
        if(zoomSelect && container) {
            container.style.setProperty('--zoom-factor', zoomSelect.value);
        }
    }
    if (zoomSelect) {
        zoomSelect.addEventListener('change', updateZoom);
        updateZoom(); // Set default 200% pada initial load
    }

    // Border Radius Slider
    const borderSlider = document.getElementById('borderRadiusSlider');
    const borderLabel = document.getElementById('borderRadiusLabel');
    const borderValueBadge = document.getElementById('borderRadiusValue');
    
    function updateBorderRadius(val) {
        const br = isNaN(parseInt(val)) ? 5 : parseInt(val);
        config.canvas.border_radius = br;
        container.style.setProperty('--border-radius', br + 'px');
        container.style.borderRadius = br + 'px';
        borderLabel.textContent = br;
        borderValueBadge.textContent = br + 'px';
        configInput.value = JSON.stringify(config);
    }
    
    if (borderSlider) {
        // Set initial value from config
        const initBr = config.canvas.border_radius ?? 5;
        borderSlider.value = initBr;
        borderLabel.textContent = initBr;
        borderValueBadge.textContent = initBr + 'px';
        
        borderSlider.addEventListener('input', function() {
            updateBorderRadius(this.value);
        });
    }

    // Klik di area canvas (bukan pada draggable-element) untuk deselect elemen
    document.addEventListener('click', e => {
        if (!e.target.closest('.draggable-element') && !e.target.closest('#elementAccordion') && !e.target.closest('#element-palette') && !e.target.closest('#addCustomTextBtn') && !e.target.closest('#element-context-menu')) {
            selectedElementKey = null;
            document.querySelectorAll('.draggable-element').forEach(item => {
                item.classList.remove('selected-element');
            });
        }
    });

    // Context Menu Logic
    const contextMenu = document.getElementById('element-context-menu');
    const deleteBtn = document.getElementById('btn-delete-element');
    const editBtn = document.getElementById('btn-edit-element');
    const duplicateBtn = document.getElementById('btn-duplicate-element');
    const frontBtn = document.getElementById('btn-front-element');
    const backBtn = document.getElementById('btn-back-element');
    let activeElementKey = null;

    canvas.addEventListener('contextmenu', e => {
        const targetEl = e.target.closest('.draggable-element');
        if (targetEl) {
            e.preventDefault();
            activeElementKey = targetEl.id.replace('el-', '');
            
            // Pilih juga elemen tersebut (agar sinkron dengan klik kiri)
            selectedElementKey = activeElementKey;
            document.querySelectorAll('.draggable-element').forEach(item => {
                item.classList.remove('selected-element');
            });
            targetEl.classList.add('selected-element');

            // Tampilkan tombol duplikat hanya jika diawali custom_text_ atau divider_
            if (activeElementKey.startsWith('custom_text_') || activeElementKey.startsWith('divider_')) {
                duplicateBtn.style.display = 'flex';
            } else {
                duplicateBtn.style.display = 'none';
            }

            contextMenu.style.left = e.pageX + 'px';
            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.display = 'block';
        }
    });

    document.addEventListener('click', e => {
        if (contextMenu.style.display === 'block') {
            contextMenu.style.display = 'none';
        }
    });

    editBtn.addEventListener('click', e => {
        if (activeElementKey) {
            const collapseEl = document.getElementById('collapse' + activeElementKey);
            if (collapseEl) {
                const accordionButton = document.querySelector(`[data-bs-target="#collapse${activeElementKey}"]`);
                if (accordionButton && accordionButton.classList.contains('collapsed')) {
                    accordionButton.click();
                }

                setTimeout(() => {
                    const accordionItem = collapseEl.closest('.accordion-item');
                    if (accordionItem) {
                        // Scroll to the accordion item
                        accordionItem.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        // Highlight effect
                        accordionItem.style.transition = 'background-color 0.3s ease';
                        accordionItem.style.backgroundColor = 'rgba(115, 103, 240, 0.2)';
                        setTimeout(() => {
                            accordionItem.style.backgroundColor = 'transparent';
                        }, 1000);

                        // Focus on the first configuration input
                        const firstInput = accordionItem.querySelector('input, select');
                        if (firstInput) {
                            firstInput.focus();
                        }
                    }
                }, 250);
            }
        }
        contextMenu.style.display = 'none';
    });

    frontBtn.addEventListener('click', e => {
        if (activeElementKey && config.elements[activeElementKey]) {
            // Cari z-index tertinggi dari semua elemen
            let maxZ = 1;
            Object.keys(config.elements).forEach(k => {
                const z = parseInt(config.elements[k].z_index || 1);
                if (z > maxZ) maxZ = z;
            });
            config.elements[activeElementKey].z_index = maxZ + 1;
            renderElements();
        }
        contextMenu.style.display = 'none';
    });

    backBtn.addEventListener('click', e => {
        if (activeElementKey && config.elements[activeElementKey]) {
            // Cari z-index terendah dari semua elemen
            let minZ = 1;
            Object.keys(config.elements).forEach(k => {
                const z = parseInt(config.elements[k].z_index || 1);
                if (z < minZ) minZ = z;
            });
            
            // Set ke minZ - 1 jika minZ > 1, jika tidak geser elemen lain naik dan set ini ke 1
            if (minZ > 1) {
                config.elements[activeElementKey].z_index = minZ - 1;
            } else {
                // Semua elemen lain dinaikkan z-indexnya
                Object.keys(config.elements).forEach(k => {
                    if (k !== activeElementKey) {
                        config.elements[k].z_index = parseInt(config.elements[k].z_index || 1) + 1;
                    }
                });
                config.elements[activeElementKey].z_index = 1;
            }
            renderElements();
        }
        contextMenu.style.display = 'none';
    });

    deleteBtn.addEventListener('click', e => {
        if (activeElementKey && config.elements[activeElementKey]) {
            config.elements[activeElementKey].show = false;
            renderElements();
        }
        contextMenu.style.display = 'none';
    });

    duplicateBtn.addEventListener('click', e => {
        if (activeElementKey && config.elements[activeElementKey]) {
            let newKey = '';
            if (activeElementKey.startsWith('custom_text_')) {
                // Cari nomor custom text terbesar yang ada
                let maxNum = 0;
                Object.keys(config.elements).forEach(k => {
                    const m = k.match(/^custom_text_(\d+)$/);
                    if (m) maxNum = Math.max(maxNum, parseInt(m[1], 10));
                });
                newKey = 'custom_text_' + (maxNum + 1);
            } else if (activeElementKey.startsWith('divider_')) {
                // Cari nomor divider terbesar yang ada
                let maxNum = 0;
                Object.keys(config.elements).forEach(k => {
                    const m = k.match(/^divider_(\d+)$/);
                    if (m) maxNum = Math.max(maxNum, parseInt(m[1], 10));
                });
                newKey = 'divider_' + (maxNum + 1);
            }

            if (newKey) {
                // Clone config dari elemen aktif
                const sourceEl = config.elements[activeElementKey];
                config.elements[newKey] = JSON.parse(JSON.stringify(sourceEl));

                // Geser posisinya sedikit (misal x + 10, y + 10) agar tidak tumpang tindih persis
                config.elements[newKey].x = Math.min(config.canvas.width - 20, config.elements[newKey].x + 10);
                config.elements[newKey].y = Math.min(config.canvas.height - 20, config.elements[newKey].y + 10);
                config.elements[newKey].show = true;

                // Jika tipe custom_text, tambahkan string ' (Salin)' di kontennya
                if (newKey.startsWith('custom_text_')) {
                    config.elements[newKey].content = (sourceEl.content || 'Teks Kustom') + ' (Salin)';
                }

                // --- SISIPKAN ACCORDION ITEM BARU KE DOM ---
                const accordion = document.getElementById('elementAccordion');
                if (newKey.startsWith('custom_text_')) {
                    const newNum = parseInt(newKey.replace('custom_text_', ''), 10);
                    const newItem = createCustomTextAccordionItem(newKey, newNum);
                    
                    // Sisipkan sebelum divider_1 agar rapi
                    const dividerItem = document.querySelector('[data-bs-target="#collapsedivider_1"]');
                    if (dividerItem) {
                        accordion.insertBefore(newItem, dividerItem.closest('.accordion-item'));
                    } else {
                        accordion.appendChild(newItem);
                    }
                } else if (newKey.startsWith('divider_')) {
                    const newNum = parseInt(newKey.replace('divider_', ''), 10);
                    const newItem = createDividerAccordionItem(newKey, newNum);
                    accordion.appendChild(newItem);
                }

                // Render ulang elements agar sidebar accordion & canvas terupdate
                renderElements();

                // PENTING: Update input values agar form accordion baru terisi data config duplikatnya
                updateControlInputs();

                // Bonus UX: Expand accordion baru & scroll ke posisinya
                setTimeout(() => {
                    const accordionButton = document.querySelector(`[data-bs-target="#collapse${newKey}"]`);
                    if (accordionButton) {
                        if (accordionButton.classList.contains('collapsed')) {
                            accordionButton.click();
                        }
                        setTimeout(() => {
                            const collapseEl = document.getElementById('collapse' + newKey);
                            const accordionItem = collapseEl ? collapseEl.closest('.accordion-item') : null;
                            if (accordionItem) {
                                accordionItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                accordionItem.style.transition = 'background-color 0.3s ease';
                                accordionItem.style.backgroundColor = 'rgba(115, 103, 240, 0.2)';
                                setTimeout(() => {
                                    accordionItem.style.backgroundColor = 'transparent';
                                }, 1000);
                            }
                        }, 250);
                    }
                }, 100);
            }
        }
        contextMenu.style.display = 'none';
    });

    // Initial Load
    updateCanvasSize();
    renderElements();

    const templateForm = document.getElementById('templateForm');
    if (templateForm) {
        templateForm.addEventListener('submit', function(e) {
            // Pastikan config diserialkan ke input hidden sebelum submit
            configInput.value = JSON.stringify(config);
        });
    }
});
</script>
@endpush
