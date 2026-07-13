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
        border-radius: 9px; /* Rounded ISO CR80 corners */
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
    .element-photo { background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; }
    .element-qr { background: #ffffff; border: 1px solid #000000; color: #000000; }
    .element-logo_lembaga, .element-ttd_kepala_sekolah, .element-cap_lembaga { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; font-size: 10px; }
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

<form action="{{ $isEdit ? route('admin.id-card-templates.update', $template->id) : route('admin.id-card-templates.store') }}" method="POST" enctype="multipart/form-data" id="templateForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

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
                        @if($template->background_path)
                            <small class="text-white-50 d-block mt-1">Current: {{ basename($template->background_path) }}</small>
                        @endif
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label text-white small" for="isActive">Jadikan Template Aktif</label>
                    </div>

                    <input type="hidden" name="config" id="configInput">

                    <hr style="border-color: rgba(255,255,255,0.08) !important;">
                    
                    <div class="accordion" id="elementAccordion">
                        <!-- Navigation for elements -->
                        @foreach(['photo', 'qr', 'name', 'nis', 'nisn', 'nip', 'class', 'gender', 'ttl', 'masa_berlaku', 'logo_lembaga', 'nama_lembaga', 'alamat_lembaga', 'tempat_tanggal_terbit', 'ttd_kepala_sekolah', 'cap_lembaga', 'nama_kepala_sekolah', 'nip_kepala_sekolah', 'custom_text_1', 'custom_text_2', 'custom_text_3', 'divider_1', 'divider_2'] as $el)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $el }}">
                                    @if($el === 'photo')
                                        Foto
                                    @elseif($el === 'qr')
                                        QR Code
                                    @elseif($el === 'name')
                                        Nama Lengkap
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
                                    @elseif($el === 'custom_text_1')
                                        Teks Kustom 1
                                    @elseif($el === 'custom_text_2')
                                        Teks Kustom 2
                                    @elseif($el === 'custom_text_3')
                                        Teks Kustom 3
                                    @elseif($el === 'divider_1')
                                        Garis Pembatas 1
                                    @elseif($el === 'divider_2')
                                        Garis Pembatas 2
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $el)) }}
                                    @endif
                                </button>
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
                                        @if(in_array($el, ['photo', 'qr', 'logo_lembaga', 'ttd_kepala_sekolah', 'cap_lembaga']))
                                        <div class="col-6">
                                            <label class="small">Lebar (W)</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="w">
                                        </div>
                                        <div class="col-6">
                                            <label class="small">Tinggi (H)</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="h">
                                        </div>
                                        @elseif(in_array($el, ['divider_1', 'divider_2']))
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
                                        @if(in_array($el, ['custom_text_1', 'custom_text_2', 'custom_text_3']))
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
        <div class="col-xl-8 col-lg-7 col-md-12">
            <div class="card" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08) !important;">
                <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center" style="border-color:rgba(255,255,255,0.08) !important; background:transparent;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti tabler-eye text-primary"></i>
                        <h5 class="card-title mb-0 text-white">Live Preview (Skala 1:1)</h5>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary text-white" id="btnPortrait">Portrait</button>
                        <button type="button" class="btn btn-outline-primary active" id="btnLandscape">Landscape</button>
                    </div>
                </div>
                <div class="card-body py-5 overflow-auto" style="background: #f1f5f9;">
                    <div id="id-card-preview-container">
                        @php
                          $bgUrl = '';
                          if ($template->background_path) {
                              if (strlen($template->background_path) > 30) {
                                  $bgUrl = 'https://drive.google.com/thumbnail?id=' . $template->background_path . '&sz=w800&_t=' . time();
                              } else {
                                  $bgUrl = Storage::url($template->background_path);
                              }
                          }
                        @endphp
                        <div id="id-card-canvas" style="background-image: url('{{ $bgUrl }}')">
                            <!-- Elements will be rendered here via JS -->
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <p class="text-secondary small mb-0"><i class="ti tabler-info-circle me-1 text-primary"></i> Geser elemen di preview untuk memindahkan posisi secara instan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const samples = @json($samples ?? []);
const lembaga = @json($lembaga ?? []);

document.addEventListener('DOMContentLoaded', function() {
    // Initial Config from PHP
    let config = @json($template->config);
    const canvas = document.getElementById('id-card-canvas');
    const container = document.getElementById('id-card-preview-container');
    const configInput = document.getElementById('configInput');
    const bgInput = document.getElementById('bgInput');

    // Update Dimensions
    function updateCanvasSize() {
        container.style.width = config.canvas.width + 'px';
        container.style.height = config.canvas.height + 'px';
    }

    function renderElements() {
        canvas.innerHTML = '';
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
            if (!el.show) return;

            const div = document.createElement('div');
            const isImageEl = ['photo', 'qr', 'logo_lembaga', 'ttd_kepala_sekolah', 'cap_lembaga'].includes(key);
            const isDividerEl = ['divider_1', 'divider_2'].includes(key);
            
            if (isDividerEl) {
                div.className = 'draggable-element element-divider';
            } else {
                div.className = 'draggable-element element-' + (isImageEl ? key : 'text');
            }
            
            div.id = 'el-' + key;
            div.style.left = el.x + 'px';
            div.style.top = el.y + 'px';

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
                } else if (key === 'ttd_kepala_sekolah') {
                    if (lembaga && lembaga.ttd_base64) {
                        div.innerHTML = `<img src="${lembaga.ttd_base64}" style="width:100%; height:100%; object-fit:contain;">`;
                    } else {
                        div.innerHTML = '<i class="ti tabler-writing-sign"></i> TTD KEPSEK';
                    }
                } else if (key === 'cap_lembaga') {
                    if (lembaga && lembaga.cap_base64) {
                        div.innerHTML = `<img src="${lembaga.cap_base64}" style="width:100%; height:100%; object-fit:contain;">`;
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
        if(key === 'nis') return sample ? 'NIS: ' + sample.nis : 'NIS: -';
        if(key === 'nisn') return sample ? 'NISN: ' + sample.nisn : 'NISN: -';
        if(key === 'nip') return sample ? 'NIP: ' + sample.nip : 'NIP: -';
        if(key === 'id_number') return sample ? 'NISN: ' + sample.nisn : 'NISN: -';
        if(key === 'class') return sample ? 'KELAS/JABATAN: ' + sample.class : 'KELAS/JABATAN: -';
        if(key === 'gender') return sample ? sample.gender : '-';
        if(key === 'ttl') return sample ? sample.ttl : '-';
        if(key === 'masa_berlaku') return sample ? sample.masa_berlaku : 'Selama menjadi anggota aktif';
        if(key === 'nama_lembaga') return lembaga.nama_sekolah || 'NAMA SEKOLAH';
        if(key === 'alamat_lembaga') return lembaga.alamat_lembaga || 'Alamat Sekolah';
        if(key === 'tempat_tanggal_terbit') return (lembaga.kota_penerbitan || 'Kota') + ', ' + new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'});
        if(key === 'nama_kepala_sekolah') return lembaga.nama_kepala_lembaga || 'Nama Kepala Sekolah';
        if(key === 'nip_kepala_sekolah') return lembaga.nip_kepala_lembaga ? 'NIP. ' + lembaga.nip_kepala_lembaga : 'NIP. -';
        if(['custom_text_1', 'custom_text_2', 'custom_text_3'].includes(key)) {
            return config.elements[key].content || 'Teks Kustom';
        }
        return key.toUpperCase();
    }

    function updateControlInputs() {
        document.querySelectorAll('.config-sync').forEach(input => {
            const el = input.dataset.el;
            const prop = input.dataset.prop;
            if(config.elements[el]) {
                const val = config.elements[el][prop];
                if(input.type === 'checkbox') input.checked = val;
                else input.value = val;
            }
        });
    }

    // Drag Logic
    function makeDraggable(el, key) {
        let isDragging = false;
        let startX, startY;

        el.addEventListener('mousedown', e => {
            isDragging = true;
            startX = e.clientX - el.offsetLeft;
            startY = e.clientY - el.offsetTop;
            e.preventDefault();
        });

        document.addEventListener('mousemove', e => {
            if (!isDragging) return;
            let nx = e.clientX - startX;
            let ny = e.clientY - startY;

            // Bounds check
            nx = Math.max(0, Math.min(nx, config.canvas.width - el.offsetWidth));
            ny = Math.max(0, Math.min(ny, config.canvas.height - el.offsetHeight));

            // Snap to grid if name/id/class is center aligned
            const isImageEl = ['photo', 'qr', 'logo_lembaga', 'ttd_kepala_sekolah', 'cap_lembaga'].includes(key);
            const isDividerEl = ['divider_1', 'divider_2'].includes(key);
            if(!isImageEl && !isDividerEl && config.elements[key].align === 'center') {
                nx = 0;
            }

            el.style.left = nx + 'px';
            el.style.top = ny + 'px';

            config.elements[key].x = nx;
            config.elements[key].y = ny;
            
            updateControlInputs();
            configInput.value = JSON.stringify(config);
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
        });
    }

    // Event Listeners for Controls
    document.querySelectorAll('.config-sync').forEach(input => {
        input.addEventListener('input', e => {
            const el = input.dataset.el;
            const prop = input.dataset.prop;
            let val = input.type === 'checkbox' ? input.checked : input.value;
            if(input.type === 'number') val = parseInt(val);
            
            config.elements[el][prop] = val;
            renderElements();
        });
    });

    document.getElementById('btnPortrait').addEventListener('click', () => {
        config.canvas = { width: 153, height: 243 };
        document.getElementById('btnPortrait').classList.add('active', 'btn-outline-primary');
        document.getElementById('btnLandscape').classList.remove('active', 'btn-outline-primary');
        updateCanvasSize();
        renderElements();
    });

    document.getElementById('btnLandscape').addEventListener('click', () => {
        config.canvas = { width: 243, height: 153 };
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

    document.getElementById('cardType').addEventListener('change', () => {
        renderElements();
    });

    // Initial Load
    updateCanvasSize();
    renderElements();
});
</script>
@endpush
