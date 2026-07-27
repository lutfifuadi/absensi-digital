@extends('layouts/layoutMaster')

@section('title', $isEdit ? 'Edit Template ID Card' : 'Buat Template ID Card')

@php
    $bgUrlFront = '';
    if (!empty($template->background_path)) {
        if (str_starts_with($template->background_path, 'http://') || str_starts_with($template->background_path, 'https://')) {
            $bgUrlFront = $template->background_path;
        } elseif (strlen($template->background_path) > 30 && !str_contains($template->background_path, '/')) {
            $bgUrlFront = 'https://drive.google.com/thumbnail?id=' . $template->background_path . '&sz=w800&_t=' . time();
        } elseif (file_exists(storage_path('app/public/' . $template->background_path))) {
            $bgData = @file_get_contents(storage_path('app/public/' . $template->background_path));
            if ($bgData !== false) {
                $ext = pathinfo($template->background_path, PATHINFO_EXTENSION) ?: 'png';
                $bgUrlFront = 'data:image/' . $ext . ';base64,' . base64_encode($bgData);
            } else {
                $bgUrlFront = asset('storage/' . $template->background_path);
            }
        } else {
            $bgUrlFront = asset('storage/' . $template->background_path);
        }
    }

    $bgUrlBack = '';
    if (!empty($template->background_path_back)) {
        if (str_starts_with($template->background_path_back, 'http://') || str_starts_with($template->background_path_back, 'https://')) {
            $bgUrlBack = $template->background_path_back;
        } elseif (strlen($template->background_path_back) > 30 && !str_contains($template->background_path_back, '/')) {
            $bgUrlBack = 'https://drive.google.com/thumbnail?id=' . $template->background_path_back . '&sz=w800&_t=' . time();
        } elseif (file_exists(storage_path('app/public/' . $template->background_path_back))) {
            $bgDataBack = @file_get_contents(storage_path('app/public/' . $template->background_path_back));
            if ($bgDataBack !== false) {
                $extBack = pathinfo($template->background_path_back, PATHINFO_EXTENSION) ?: 'png';
                $bgUrlBack = 'data:image/' . $extBack . ';base64,' . base64_encode($bgDataBack);
            } else {
                $bgUrlBack = asset('storage/' . $template->background_path_back);
            }
        } else {
            $bgUrlBack = asset('storage/' . $template->background_path_back);
        }
    }
@endphp

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

    /* Tab Custom Styling */
    .side-tab-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.6);
        padding: 0.65rem 1rem;
        border-radius: 8px;
        transition: all 0.25s ease;
    }
    .side-tab-btn:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.1);
    }
    .side-tab-btn.active {
        background: #7367f0 !important;
        border-color: #7367f0 !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(115, 103, 240, 0.4);
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
    }
</style>
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
                    {{-- Global Options --}}
                    <div class="mb-3 mt-2">
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
                        <label class="form-label text-white-50 small d-flex justify-content-between align-items-center">
                            <span>Border Radius (Rounded Corner)</span>
                            <span class="badge bg-label-primary rounded-pill" id="borderRadiusValue" style="font-size:0.7rem;">5px</span>
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" id="borderRadiusSlider" class="form-range flex-grow-1" min="0" max="5" step="1" value="5" style="height:6px;">
                            <span class="text-white-50 small" style="min-width:24px;text-align:right;" id="borderRadiusLabel">5</span>
                        </div>
                        <small class="text-white-50 d-block mt-1" style="font-size:0.65rem;">Atur kelengkungan sudut kartu untuk KEDUA sisi (0 = kotak, 5 = rounded)</small>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.08) !important;" class="my-4">

                    {{-- DUAL SIDE TABS --}}
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn side-tab-btn flex-fill active" id="btnTabFront">
                            <i class="ti tabler-credit-card me-1"></i> Depan (Front)
                        </button>
                        <button type="button" class="btn side-tab-btn flex-fill d-flex align-items-center justify-content-center gap-1" id="btnTabBack">
                            <i class="ti tabler-credit-card-refund me-1"></i> Belakang (Back)
                            <span class="badge bg-success rounded-pill d-none" id="backActiveBadge" style="font-size:0.6rem;">• Aktif</span>
                        </button>
                    </div>

                    {{-- FRONT SIDE BACKGROUND CONTROLS --}}
                    <div id="sideControlsFront">
                        <div class="mb-3 p-3 rounded" style="background: rgba(15, 23, 42, 0.3); border: 1px solid rgba(255,255,255,0.06);">
                            <label class="form-label text-white fw-semibold small mb-2"><i class="ti tabler-photo me-1 text-primary"></i>Background Sisi Depan (Front)</label>
                            <input type="file" name="background" class="form-control" id="bgInput" accept="image/*">
                            
                            <div class="text-center my-2 text-white-50 small">— ATAU —</div>
                            
                            <label class="form-label text-white-50 small">Link Gambar Eksternal (URL)</label>
                            <input type="url" name="background_url" class="form-control" id="bgUrlInput" 
                                   value="{{ old('background_url', (isset($template) && str_starts_with($template->background_path ?? '', 'http')) ? $template->background_path : '') }}" 
                                   placeholder="https://example.com/background-front.png">
                            
                            @if($template->background_path && !str_starts_with($template->background_path, 'http'))
                                <small class="text-white-50 d-block mt-1">Current: {{ basename($template->background_path) }}</small>
                            @endif
                        </div>
                    </div>

                    {{-- BACK SIDE BACKGROUND CONTROLS --}}
                    <div id="sideControlsBack" class="d-none">
                        <div class="mb-3 p-3 rounded" style="background: rgba(15, 23, 42, 0.3); border: 1px solid rgba(255,255,255,0.06);">
                            <label class="form-label text-white fw-semibold small mb-2"><i class="ti tabler-photo me-1 text-info"></i>Background Sisi Belakang (Back)</label>
                            <input type="file" name="background_back" class="form-control" id="bgBackInput" accept="image/*">
                            
                            <div class="text-center my-2 text-white-50 small">— ATAU —</div>
                            
                            <label class="form-label text-white-50 small">Link Gambar Eksternal (URL)</label>
                            <input type="url" name="background_back_url" class="form-control" id="bgBackUrlInput" 
                                   value="{{ old('background_back_url', (isset($template) && str_starts_with($template->background_path_back ?? '', 'http')) ? $template->background_path_back : '') }}" 
                                   placeholder="https://example.com/background-back.png">
                            
                            @if($template->background_path_back && !str_starts_with($template->background_path_back, 'http'))
                                <small class="text-white-50 d-block mt-1">Current: {{ basename($template->background_path_back) }}</small>
                            @endif
                        </div>
                    </div>

                    <input type="hidden" name="config" id="configInput">

                    <div class="mb-4">
                        <label class="form-label text-white-50 small mb-2 d-flex justify-content-between align-items-center">
                            <span>Palet Elemen Nonaktif (<span id="paletteSideText">Depan</span>)</span>
                            <span class="badge bg-label-primary rounded-pill" style="font-size:0.65rem;">Drag & Drop</span>
                        </label>
                        <div id="element-palette" class="d-flex flex-wrap gap-2 p-3 rounded" style="background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.1); min-height: 50px;">
                            <!-- Badge elemen draggable akan di-render dinamis via JS -->
                        </div>
                        <div class="mt-2 d-flex gap-2">
                            <button type="button" id="addCustomTextBtn" class="btn btn-sm btn-outline-info flex-fill" style="border-style: dashed;">
                                <i class="ti tabler-plus me-1"></i> Tambah Teks
                            </button>
                            <button type="button" id="addDividerBtn" class="btn btn-sm btn-outline-warning flex-fill" style="border-style: dashed;">
                                <i class="ti tabler-plus me-1"></i> Tambah Garis
                            </button>
                        </div>
                    </div>

                    <div class="accordion" id="elementAccordion">
                        @php
                            $orderedStandard = ['photo', 'qr', 'barcode', 'name', 'id_number', 'nis', 'nisn', 'nip', 'class', 'gender', 'ttl', 'masa_berlaku', 'logo_lembaga', 'logo_dinas', 'nama_lembaga', 'alamat_lembaga', 'tempat_tanggal_terbit', 'ttd_kepala_sekolah', 'cap_lembaga', 'nama_kepala_sekolah', 'nip_kepala_sekolah'];
                            
                            $elementsSource = $template->config['front']['elements'] ?? ($template->config['elements'] ?? []);
                            $customTextKeys = [];
                            foreach (array_keys($elementsSource) as $ek) {
                                if (str_starts_with($ek, 'custom_text_')) {
                                    $customTextKeys[] = $ek;
                                }
                            }
                            sort($customTextKeys);
                            
                            $dividerKeys = [];
                            foreach (array_keys($elementsSource) as $ek) {
                                if (str_starts_with($ek, 'divider_')) {
                                    $dividerKeys[] = $ek;
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
                                    @elseif($el === 'barcode')
                                        Barcode 1D
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
                                        @if(in_array($el, ['photo', 'qr', 'barcode', 'logo_lembaga', 'logo_dinas', 'ttd_kepala_sekolah', 'cap_lembaga']))
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
                        <h5 class="card-title mb-0 text-white" id="previewTitle">Live Preview Sisi Depan (Front)</h5>
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
                            <div id="id-card-canvas" style="background-image: url('{{ $bgUrlFront }}')">
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
    let rawConfig = @json($template->config);
    
    // Normalize dual side config structure
    let config = {
        canvas: rawConfig.canvas ?? { width: 153, height: 243, border_radius: 5 },
        front: {
            elements: (rawConfig.front && rawConfig.front.elements) ? rawConfig.front.elements : (rawConfig.elements ?? {})
        },
        back: {
            elements: (rawConfig.back && rawConfig.back.elements) ? rawConfig.back.elements : {}
        }
    };

    let activeSide = 'front'; // 'front' or 'back'
    let selectedElementKey = null;

    let bgUrlFront = @json($bgUrlFront);
    let bgUrlBack  = @json($bgUrlBack);

    const canvas = document.getElementById('id-card-canvas');
    const container = document.getElementById('id-card-preview-container');
    const configInput = document.getElementById('configInput');
    const palette = document.getElementById('element-palette');

    const btnTabFront = document.getElementById('btnTabFront');
    const btnTabBack  = document.getElementById('btnTabBack');
    const sideControlsFront = document.getElementById('sideControlsFront');
    const sideControlsBack  = document.getElementById('sideControlsBack');
    const previewTitle = document.getElementById('previewTitle');
    const paletteSideText = document.getElementById('paletteSideText');
    const backActiveBadge = document.getElementById('backActiveBadge');

    const bgInput = document.getElementById('bgInput');
    const bgUrlInput = document.getElementById('bgUrlInput');
    const bgBackInput = document.getElementById('bgBackInput');
    const bgBackUrlInput = document.getElementById('bgBackUrlInput');

    // Live background updates for Front
    if (bgInput) {
        bgInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    bgUrlFront = evt.target.result;
                    if (activeSide === 'front') {
                        canvas.style.backgroundImage = `url('${bgUrlFront}')`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    if (bgUrlInput) {
        bgUrlInput.addEventListener('input', function() {
            if (this.value) {
                bgUrlFront = this.value;
                if (activeSide === 'front') {
                    canvas.style.backgroundImage = `url('${bgUrlFront}')`;
                }
            }
        });
    }

    // Live background updates for Back
    if (bgBackInput) {
        bgBackInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    bgUrlBack = evt.target.result;
                    if (activeSide === 'back') {
                        canvas.style.backgroundImage = `url('${bgUrlBack}')`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    if (bgBackUrlInput) {
        bgBackUrlInput.addEventListener('input', function() {
            if (this.value) {
                bgUrlBack = this.value;
                if (activeSide === 'back') {
                    canvas.style.backgroundImage = `url('${bgUrlBack}')`;
                }
            }
        });
    }

    // Tab Switching Handlers
    btnTabFront.addEventListener('click', function() {
        activeSide = 'front';
        btnTabFront.classList.add('active');
        btnTabBack.classList.remove('active');
        sideControlsFront.classList.remove('d-none');
        sideControlsBack.classList.add('d-none');
        previewTitle.innerText = 'Live Preview Sisi Depan (Front)';
        paletteSideText.innerText = 'Depan';
        canvas.style.backgroundImage = bgUrlFront ? `url('${bgUrlFront}')` : 'none';
        selectedElementKey = null;
        renderElements();
    });

    btnTabBack.addEventListener('click', function() {
        activeSide = 'back';
        btnTabBack.classList.add('active');
        btnTabFront.classList.remove('active');
        sideControlsBack.classList.remove('d-none');
        sideControlsFront.classList.add('d-none');
        previewTitle.innerText = 'Live Preview Sisi Belakang (Back)';
        paletteSideText.innerText = 'Belakang';
        canvas.style.backgroundImage = bgUrlBack ? `url('${bgUrlBack}')` : 'none';
        selectedElementKey = null;
        renderElements();
    });

    function updateBackActiveBadge() {
        const hasActive = Object.values(config.back.elements).some(e => e && e.show);
        if (hasActive) {
            backActiveBadge.classList.remove('d-none');
        } else {
            backActiveBadge.classList.add('d-none');
        }
    }

    function getFriendlyName(key) {
        const names = {
            'photo': 'Foto',
            'qr': 'QR Code',
            'barcode': 'Barcode 1D',
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
        const matchText = key.match(/^custom_text_(\d+)$/);
        if (matchText) return 'Teks Kustom ' + matchText[1];
        const matchDivider = key.match(/^divider_(\d+)$/);
        if (matchDivider) return 'Garis Pembatas ' + matchDivider[1];
        return key.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

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

        const currentElements = config[activeSide].elements;

        Object.keys(currentElements).forEach(key => {
            const el = currentElements[key];
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
            const isImageEl = ['photo', 'qr', 'barcode', 'logo_lembaga', 'logo_dinas', 'ttd_kepala_sekolah', 'cap_lembaga'].includes(key);
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
                    const defaultLogo = (lembaga && (lembaga.logo_base64 || lembaga.logo_url)) ? (lembaga.logo_base64 || lembaga.logo_url) : null;
                    if (sample && sample.photo) {
                        div.innerHTML = `<img src="${sample.photo}" style="width:100%; height:100%; object-fit:cover;">`;
                    } else if (defaultLogo) {
                        div.innerHTML = `<img src="${defaultLogo}" style="width:100%; height:100%; object-fit:contain; background:#fff; padding:2px;">`;
                    } else {
                        div.innerHTML = '<i class="ti tabler-school"></i> LOGO FOTO';
                    }
                } else if (key === 'qr') {
                    let qrData = sample ? (sample.nisn || sample.nip || 'ABSENSI') : 'ABSENSI_PREVIEW';
                    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(qrData)}`;
                    div.innerHTML = `<img src="${qrUrl}" style="width:100%; height:100%; object-fit:contain; background:#fff; padding:2px;">`;
                } else if (key === 'barcode') {
                    const barcodeSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 100 30" preserveAspectRatio="none"><rect width="100%" height="100%" fill="#ffffff"/><path d="M5 5h3v20H5zm5 0h2v20h-2zm4 0h1v20h-1zm3 0h4v20h-4zm6 0h2v20h-2zm4 0h1v20h-1zm3 0h3v20h-3zm5 0h2v20h-2zm4 0h4v20h-4zm6 0h1v20h-1zm3 0h3v20h-3zm5 0h2v20h-2zm4 0h1v20h-1zm3 0h4v20h-4zm6 0h2v20h-2zm4 0h2v20h-2zm5 0h3v20h-5z" fill="#000000"/></svg>`;
                    div.innerHTML = `<div style="width:100%; height:100%; background:#fff; padding:2px; display:flex; align-items:center; justify-content:center;">${barcodeSvg}</div>`;
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
                div.style.fontWeight = el.bold ? 'bold' : 'normal';
                div.style.fontStyle = el.italic ? 'italic' : 'normal';
                div.style.textTransform = el.transform || 'none';
                
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
        updateBackActiveBadge();
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

        const currentElements = config[activeSide].elements;

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
            return currentElements[key] ? (currentElements[key].content || 'Teks Kustom') : 'Teks Kustom';
        }
        return key.toUpperCase();
    }

    function updateControlInputs() {
        const currentElements = config[activeSide].elements;
        document.querySelectorAll('.config-sync').forEach(input => {
            const el = input.dataset.el;
            const prop = input.dataset.prop;
            if(currentElements[el]) {
                const val = currentElements[el][prop];
                if(input.type === 'checkbox') {
                    input.checked = !!val;
                } else {
                    input.value = (val === undefined || val === null) ? '' : val;
                }
            }
        });
    }

    // ── Shared drag state (single listener, no accumulation) ──────────────────
    let activeDragKey = null;
    let dragLastX = 0, dragLastY = 0;

    document.addEventListener('mousemove', e => {
        if (!activeDragKey) return;
        const currentElements = config[activeSide].elements;
        if (!currentElements[activeDragKey]) return;

        const zoomSelect = document.getElementById('zoomSelect');
        const zoom = zoomSelect ? parseFloat(zoomSelect.value) : 1.0;

        const dx = (e.clientX - dragLastX) / zoom;
        const dy = (e.clientY - dragLastY) / zoom;

        let nx = currentElements[activeDragKey].x + dx;
        let ny = currentElements[activeDragKey].y + dy;

        const dragEl = document.getElementById('el-' + activeDragKey);
        const elW = dragEl ? dragEl.offsetWidth : 0;
        const elH = dragEl ? dragEl.offsetHeight : 0;

        nx = Math.max(0, Math.min(nx, config.canvas.width - elW));
        ny = Math.max(0, Math.min(ny, config.canvas.height - elH));

        const isImageEl = ['photo', 'qr', 'logo_lembaga', 'logo_dinas', 'ttd_kepala_sekolah', 'cap_lembaga'].includes(activeDragKey);
        const isDividerEl = activeDragKey.startsWith('divider_');
        if (!isImageEl && !isDividerEl && currentElements[activeDragKey].align === 'center') {
            nx = 0;
        }

        currentElements[activeDragKey].x = Math.round(nx);
        currentElements[activeDragKey].y = Math.round(ny);

        if (dragEl) {
            dragEl.style.left = currentElements[activeDragKey].x + 'px';
            dragEl.style.top  = currentElements[activeDragKey].y + 'px';
        }

        dragLastX = e.clientX;
        dragLastY = e.clientY;

        updateControlInputs();
        configInput.value = JSON.stringify(config);
    });

    document.addEventListener('mouseup', () => {
        activeDragKey = null;
    });

    // Drag Logic – only registers mousedown per element
    function makeDraggable(el, key) {
        el.addEventListener('mousedown', e => {
            if (e.button !== 0) return; // left click only
            // Select element
            document.querySelectorAll('.draggable-element').forEach(item => {
                item.classList.remove('selected-element');
            });
            selectedElementKey = key;
            el.classList.add('selected-element');

            // Start drag
            activeDragKey = key;
            dragLastX = e.clientX;
            dragLastY = e.clientY;

            e.preventDefault();
            e.stopPropagation();
        });
    }

    // Palette Drag and Drop to Canvas
    const handlePaletteDrop = e => {
        e.preventDefault();
        const key = e.dataTransfer.getData('text/plain');
        const currentElements = config[activeSide].elements;
        if(key && currentElements[key]) {
            const rect = canvas.getBoundingClientRect();
            const zoomSelect = document.getElementById('zoomSelect');
            const zoom = zoomSelect ? parseFloat(zoomSelect.value) : 1.0;

            let dropX = (e.clientX - rect.left) / zoom;
            let dropY = (e.clientY - rect.top) / zoom;

            const isImageEl = ['photo', 'qr', 'barcode', 'logo_lembaga', 'logo_dinas', 'ttd_kepala_sekolah', 'cap_lembaga'].includes(key);
            const isDividerEl = key.startsWith('divider_');
            const elW = isImageEl ? (currentElements[key].w || 40) : (isDividerEl ? (currentElements[key].w || 100) : 60);
            const elH = isImageEl ? (currentElements[key].h || 40) : (isDividerEl ? (currentElements[key].h || 2) : 12);

            dropX = Math.max(0, Math.min(dropX, config.canvas.width - elW));
            dropY = Math.max(0, Math.min(dropY, config.canvas.height - elH));

            if (!isImageEl && !isDividerEl && currentElements[key].align === 'center') {
                dropX = 0;
            }

            currentElements[key].x = Math.round(dropX);
            currentElements[key].y = Math.round(dropY);
            currentElements[key].show = true;

            selectedElementKey = key;
            renderElements();
            showNudgeToast(key, currentElements[key].x, currentElements[key].y);
        }
    };

    container.addEventListener('dragover', e => e.preventDefault());
    container.addEventListener('drop', handlePaletteDrop);

    // Orientation Switch Helper
    function clampElementsToCanvas() {
        ['front', 'back'].forEach(side => {
            if (!config[side] || !config[side].elements) return;
            Object.keys(config[side].elements).forEach(key => {
                const el = config[side].elements[key];
                if (!el) return;
                const isImageEl = ['photo', 'qr', 'barcode', 'logo_lembaga', 'logo_dinas', 'ttd_kepala_sekolah', 'cap_lembaga'].includes(key);
                const isDividerEl = key.startsWith('divider_');
                const elW = isImageEl ? (el.w || 40) : (isDividerEl ? (el.w || 100) : 60);
                const elH = isImageEl ? (el.h || 40) : (isDividerEl ? (el.h || 2) : 12);

                if (el.y + elH > config.canvas.height) {
                    el.y = Math.max(0, config.canvas.height - elH);
                }
                if (el.x + elW > config.canvas.width) {
                    el.x = Math.max(0, config.canvas.width - elW);
                }
                if (!isImageEl && !isDividerEl && el.align === 'center') {
                    el.x = 0;
                }
            });
        });
    }

    // Orientation Buttons
    const btnPortrait = document.getElementById('btnPortrait');
    const btnLandscape = document.getElementById('btnLandscape');
    if (btnPortrait && btnLandscape) {
        btnPortrait.addEventListener('click', function() {
            btnPortrait.classList.add('active', 'btn-outline-primary');
            btnPortrait.classList.remove('btn-outline-secondary');
            btnLandscape.classList.remove('active', 'btn-outline-primary');
            btnLandscape.classList.add('btn-outline-secondary');

            config.canvas.width = 153;
            config.canvas.height = 243;
            clampElementsToCanvas();
            updateCanvasSize();
            renderElements();
        });

        btnLandscape.addEventListener('click', function() {
            btnLandscape.classList.add('active', 'btn-outline-primary');
            btnLandscape.classList.remove('btn-outline-secondary');
            btnPortrait.classList.remove('active', 'btn-outline-primary');
            btnPortrait.classList.add('btn-outline-secondary');

            config.canvas.width = 243;
            config.canvas.height = 153;
            clampElementsToCanvas();
            updateCanvasSize();
            renderElements();
        });
    }

    // Context Menu – use position:fixed so viewport coords are correct
    const contextMenu = document.getElementById('element-context-menu');
    let contextElementKey = null;

    // Right-click on canvas or any element inside it
    canvas.addEventListener('contextmenu', e => {
        e.preventDefault();
        e.stopPropagation();
        const target = e.target.closest('.draggable-element');
        if (!target) {
            contextMenu.style.display = 'none';
            return;
        }
        contextElementKey = target.id.replace('el-', '');

        // Select the element visually
        document.querySelectorAll('.draggable-element').forEach(item => item.classList.remove('selected-element'));
        target.classList.add('selected-element');
        selectedElementKey = contextElementKey;

        // Position menu at cursor using fixed coordinates
        const menuW = 200;
        const menuH = 180;
        let left = e.clientX;
        let top  = e.clientY;
        if (left + menuW > window.innerWidth)  left = e.clientX - menuW;
        if (top  + menuH > window.innerHeight) top  = e.clientY - menuH;

        contextMenu.style.position = 'fixed';
        contextMenu.style.left = left + 'px';
        contextMenu.style.top  = top  + 'px';
        contextMenu.style.display = 'block';
    });

    // Close context menu on any click or right-click outside
    document.addEventListener('click', e => {
        if (!contextMenu.contains(e.target)) {
            contextMenu.style.display = 'none';
        }
    });

    document.addEventListener('contextmenu', e => {
        // If context menu is open and right-click is not on canvas, close it
        if (contextMenu.style.display === 'block' && !canvas.contains(e.target)) {
            contextMenu.style.display = 'none';
        }
    });

    document.getElementById('btn-edit-element').addEventListener('click', () => {
        if (contextElementKey) {
            const collapseEl = document.getElementById('collapse' + contextElementKey);
            if (collapseEl) {
                const accordionItem = collapseEl.closest('.accordion-item');
                if (accordionItem) {
                    accordionItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                const bsCollapse = new bootstrap.Collapse(collapseEl, { toggle: true });
            }
        }
        contextMenu.style.display = 'none';
    });

    document.getElementById('btn-duplicate-element').addEventListener('click', () => {
        if (contextElementKey) {
            duplicateElement(contextElementKey);
        }
        contextMenu.style.display = 'none';
    });

    document.getElementById('btn-delete-element').addEventListener('click', () => {
        if (contextElementKey) {
            config[activeSide].elements[contextElementKey].show = false;
            renderElements();
        }
        contextMenu.style.display = 'none';
    });

    document.getElementById('btn-front-element').addEventListener('click', () => {
        if (contextElementKey) {
            config[activeSide].elements[contextElementKey].z_index = (config[activeSide].elements[contextElementKey].z_index || 1) + 10;
            renderElements();
        }
        contextMenu.style.display = 'none';
    });

    document.getElementById('btn-back-element').addEventListener('click', () => {
        if (contextElementKey) {
            config[activeSide].elements[contextElementKey].z_index = Math.max(1, (config[activeSide].elements[contextElementKey].z_index || 1) - 10);
            renderElements();
        }
        contextMenu.style.display = 'none';
    });

    // ── Helper Pembuatan Item Accordion Dinamis & Binding Event ────────────────
    function bindAccordionInputEvents(parentEl) {
        parentEl.querySelectorAll('.config-sync').forEach(input => {
            input.addEventListener('input', e => {
                const el = e.target.dataset.el;
                const prop = e.target.dataset.prop;
                const currentElements = config[activeSide].elements;
                if(currentElements[el]) {
                    if(e.target.type === 'checkbox') {
                        currentElements[el][prop] = e.target.checked;
                    } else if(e.target.type === 'number') {
                        currentElements[el][prop] = e.target.value === '' ? 0 : parseFloat(e.target.value);
                    } else {
                        currentElements[el][prop] = e.target.value;
                    }
                    renderElements();
                }
            });
        });
    }

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

    // ── Fungsi Duplikasi Elemen ─────────────────────────────────────────────────
    function duplicateElement(key) {
        const currentElements = config[activeSide].elements;
        const sourceEl = currentElements[key];
        if (!sourceEl) return;

        const isDivider = key.startsWith('divider_');
        let maxNum = 0;

        if (isDivider) {
            Object.keys(currentElements).forEach(k => {
                const m = k.match(/^divider_(\d+)$/);
                if (m) maxNum = Math.max(maxNum, parseInt(m[1], 10));
            });
            const newNum = maxNum + 1;
            const newKey = 'divider_' + newNum;

            currentElements[newKey] = {
                x: Math.min(config.canvas.width - 20, (sourceEl.x || 10) + 5),
                y: Math.min(config.canvas.height - 10, (sourceEl.y || 10) + 10),
                w: sourceEl.w || 100,
                h: sourceEl.h || 2,
                color: sourceEl.color || '#cccccc',
                z_index: (sourceEl.z_index || 1) + 1,
                show: true
            };

            const accordion = document.getElementById('elementAccordion');
            const newItem = createDividerAccordionItem(newKey, newNum);
            accordion.appendChild(newItem);
            bindAccordionInputEvents(newItem);

            selectedElementKey = newKey;
            renderElements();
            showNudgeToast(newKey, currentElements[newKey].x, currentElements[newKey].y);
        } else {
            Object.keys(currentElements).forEach(k => {
                const m = k.match(/^custom_text_(\d+)$/);
                if (m) maxNum = Math.max(maxNum, parseInt(m[1], 10));
            });
            const newNum = maxNum + 1;
            const newKey = 'custom_text_' + newNum;

            const contentText = sourceEl.content || getLabelFor(key);

            currentElements[newKey] = {
                x: sourceEl.align === 'center' ? 0 : Math.min(config.canvas.width - 20, (sourceEl.x || 10) + 5),
                y: Math.min(config.canvas.height - 15, (sourceEl.y || 10) + 10),
                size: sourceEl.size || 8,
                color: sourceEl.color || '#000000',
                show: true,
                align: sourceEl.align || 'center',
                content: contentText,
                bold: sourceEl.bold || false,
                italic: sourceEl.italic || false,
                transform: sourceEl.transform || 'none',
                z_index: (sourceEl.z_index || 1) + 1
            };

            const accordion = document.getElementById('elementAccordion');
            const newItem = createCustomTextAccordionItem(newKey, newNum);
            accordion.appendChild(newItem);
            bindAccordionInputEvents(newItem);

            selectedElementKey = newKey;
            renderElements();
            showNudgeToast(newKey, currentElements[newKey].x, currentElements[newKey].y);
        }
    }

    // Tombol Tambah Teks & Tambah Garis di area Palet
    const addCustomTextBtn = document.getElementById('addCustomTextBtn');
    if (addCustomTextBtn) {
        addCustomTextBtn.addEventListener('click', () => {
            let maxNum = 0;
            const currentElements = config[activeSide].elements;
            Object.keys(currentElements).forEach(k => {
                const m = k.match(/^custom_text_(\d+)$/);
                if (m) maxNum = Math.max(maxNum, parseInt(m[1], 10));
            });
            const newNum = maxNum + 1;
            const newKey = 'custom_text_' + newNum;

            currentElements[newKey] = {
                x: 0,
                y: Math.min(config.canvas.height - 20, 140 + (newNum - 1) * 12),
                size: 8,
                color: '#000000',
                show: true,
                align: 'center',
                content: 'Teks Kustom Baru',
                bold: false,
                italic: false,
                transform: 'none',
                z_index: 10
            };

            const accordion = document.getElementById('elementAccordion');
            const newItem = createCustomTextAccordionItem(newKey, newNum);
            accordion.appendChild(newItem);
            bindAccordionInputEvents(newItem);

            selectedElementKey = newKey;
            renderElements();
            showNudgeToast(newKey, currentElements[newKey].x, currentElements[newKey].y);
        });
    }

    const addDividerBtn = document.getElementById('addDividerBtn');
    if (addDividerBtn) {
        addDividerBtn.addEventListener('click', () => {
            let maxNum = 0;
            const currentElements = config[activeSide].elements;
            Object.keys(currentElements).forEach(k => {
                const m = k.match(/^divider_(\d+)$/);
                if (m) maxNum = Math.max(maxNum, parseInt(m[1], 10));
            });
            const newNum = maxNum + 1;
            const newKey = 'divider_' + newNum;

            currentElements[newKey] = {
                x: 10,
                y: Math.min(config.canvas.height - 10, 100 + (newNum - 1) * 15),
                w: Math.min(133, config.canvas.width - 20),
                h: 2,
                color: '#cccccc',
                show: true,
                z_index: 9
            };

            const accordion = document.getElementById('elementAccordion');
            const newItem = createDividerAccordionItem(newKey, newNum);
            accordion.appendChild(newItem);
            bindAccordionInputEvents(newItem);

            selectedElementKey = newKey;
            renderElements();
            showNudgeToast(newKey, currentElements[newKey].x, currentElements[newKey].y);
        });
    }

    // Delegasi Event Hapus Elemen pada Accordion
    const accordionEl = document.getElementById('elementAccordion');
    if (accordionEl) {
        accordionEl.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-remove-custom-text');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                const elKey = btn.dataset.el;
                if (confirm('Hapus elemen ini?')) {
                    delete config[activeSide].elements[elKey];
                    const item = btn.closest('.accordion-item');
                    if (item) item.remove();
                    selectedElementKey = null;
                    renderElements();
                }
            }
        });
    }

    // ── Keyboard Arrow Key Movement ────────────────────────────────────────────
    // Arrow keys move selected element 1px; Shift+Arrow moves 10px (fast nudge)
    document.addEventListener('keydown', e => {
        if (!selectedElementKey) return;

        // Don't intercept when focus is on an input/textarea/select
        const tag = document.activeElement ? document.activeElement.tagName : '';
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return;

        const arrowKeys = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'];
        if (!arrowKeys.includes(e.key)) return;

        e.preventDefault(); // prevent page scroll

        const step = e.shiftKey ? 10 : 1;
        const currentElements = config[activeSide].elements;
        const elConfig = currentElements[selectedElementKey];
        if (!elConfig) return;

        const domEl = document.getElementById('el-' + selectedElementKey);
        const elW = domEl ? domEl.offsetWidth  : 0;
        const elH = domEl ? domEl.offsetHeight : 0;

        if (e.key === 'ArrowLeft')  elConfig.x = Math.max(0, elConfig.x - step);
        if (e.key === 'ArrowRight') elConfig.x = Math.min(config.canvas.width  - elW, elConfig.x + step);
        if (e.key === 'ArrowUp')    elConfig.y = Math.max(0, elConfig.y - step);
        if (e.key === 'ArrowDown')  elConfig.y = Math.min(config.canvas.height - elH, elConfig.y + step);

        // Center-aligned text elements are locked to X = 0
        const isImageEl = ['photo', 'qr', 'logo_lembaga', 'logo_dinas', 'ttd_kepala_sekolah', 'cap_lembaga'].includes(selectedElementKey);
        const isDividerEl = selectedElementKey.startsWith('divider_');
        if (!isImageEl && !isDividerEl && elConfig.align === 'center') {
            elConfig.x = 0;
        }

        // Update DOM directly for smooth feel (no full re-render)
        if (domEl) {
            domEl.style.left = elConfig.x + 'px';
            domEl.style.top  = elConfig.y + 'px';
        }

        updateControlInputs();
        configInput.value = JSON.stringify(config);

        // Show live coordinate toast
        showNudgeToast(selectedElementKey, elConfig.x, elConfig.y);
    });

    // Small floating toast showing coordinates during keyboard nudge
    let nudgeToastTimer = null;
    function showNudgeToast(key, x, y) {
        let toast = document.getElementById('nudge-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'nudge-toast';
            toast.style.cssText = `
                position: fixed; bottom: 24px; right: 24px; z-index: 99999;
                background: rgba(15,23,42,0.92); color: #fff;
                padding: 8px 14px; border-radius: 8px; font-size: 0.75rem;
                border: 1px solid rgba(115,103,240,0.4);
                backdrop-filter: blur(8px);
                pointer-events: none;
                box-shadow: 0 4px 20px rgba(0,0,0,0.4);
                transition: opacity 0.2s ease;
            `;
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<span style="color:#7367f0;font-weight:600;">${getFriendlyName(key)}</span>&nbsp;&nbsp;X: <b>${x}</b> &nbsp; Y: <b>${y}</b>&nbsp;&nbsp;<span style="color:rgba(255,255,255,0.4);font-size:0.65rem;">[Shift + ↑↓←→ = ×10]</span>`;
        toast.style.opacity = '1';

        clearTimeout(nudgeToastTimer);
        nudgeToastTimer = setTimeout(() => {
            toast.style.opacity = '0';
        }, 1500);
    }

    // Show keyboard hint when an element is selected via mouse
    canvas.addEventListener('mousedown', e => {
        const target = e.target.closest('.draggable-element');
        if (target && e.button === 0) {
            const key = target.id.replace('el-', '');
            const el = config[activeSide].elements[key];
            if (el) setTimeout(() => showNudgeToast(key, el.x, el.y), 100);
        }
    });

    // Zoom Selector
    const zoomSelect = document.getElementById('zoomSelect');
    if (zoomSelect) {
        // Set initial zoom value on load
        container.style.setProperty('--zoom-factor', zoomSelect.value);
        zoomSelect.addEventListener('change', function() {
            container.style.setProperty('--zoom-factor', this.value);
        });
    }

    // Border Radius Slider
    const brSlider = document.getElementById('borderRadiusSlider');
    const brLabel = document.getElementById('borderRadiusLabel');
    const brValue = document.getElementById('borderRadiusValue');
    if (brSlider) {
        brSlider.value = config.canvas.border_radius ?? 5;
        if (brLabel) brLabel.innerText = brSlider.value;
        if (brValue) brValue.innerText = brSlider.value + 'px';

        brSlider.addEventListener('input', function() {
            const val = parseInt(this.value, 10);
            config.canvas.border_radius = val;
            if (brLabel) brLabel.innerText = val;
            if (brValue) brValue.innerText = val + 'px';
            updateCanvasSize();
            configInput.value = JSON.stringify(config);
        });
    }

    // Form Submit Handler
    const templateForm = document.getElementById('templateForm');
    if (templateForm) {
        templateForm.addEventListener('submit', function() {
            configInput.value = JSON.stringify(config);
        });
    }

    // Initial render
    bindAccordionInputEvents(document);
    updateCanvasSize();
    renderElements();
});
</script>
@endpush
