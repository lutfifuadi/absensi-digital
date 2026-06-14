@extends('layouts/layoutMaster')

@section('title', $isEdit ? 'Edit Template ID Card' : 'Buat Template ID Card')

@section('vendor-style')
<style>
    #id-card-preview-container {
        position: relative;
        background-color: #f8f9fa;
        border: 2px dashed #dee2e6;
        margin: 0 auto;
        overflow: hidden;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
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
        border: 1px dashed transparent;
        cursor: move;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .draggable-element:hover {
        border-color: #7367f0;
        background: rgba(115, 103, 240, 0.1);
    }
    .element-photo { background: #eee; border: 1px solid #ccc; }
    .element-qr { background: #fff; border: 1px solid #000; }
    .element-text { white-space: nowrap; font-weight: bold; }
</style>
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Smart Card /</span> {{ $isEdit ? 'Edit' : 'Buat' }} Template
</h4>

<form action="{{ $isEdit ? route('admin.id-card-templates.update', $template->id) : route('admin.id-card-templates.store') }}" method="POST" enctype="multipart/form-data" id="templateForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <!-- Sidebar Controls -->
        <div class="col-xl-4 col-lg-5 col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Konfigurasi Template</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Template</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" placeholder="Contoh: Kartu Siswa Biru" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Kartu</label>
                        <select name="type" class="form-select" required id="cardType">
                            <option value="siswa" {{ old('type', $template->type) == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ old('type', $template->type) == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="staff" {{ old('type', $template->type) == 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="pelepasan" {{ old('type', $template->type) == 'pelepasan' ? 'selected' : '' }}>Pelepasan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Background Kartu (PNG/JPG)</label>
                        <input type="file" name="background" class="form-control" id="bgInput" accept="image/*">
                        @if($template->background_path)
                            <small class="text-muted">Current: {{ basename($template->background_path) }}</small>
                        @endif
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Jadikan Template Aktif</label>
                    </div>

                    <input type="hidden" name="config" id="configInput">

                    <hr>
                    
                    <div class="accordion" id="elementAccordion">
                        <!-- Navigation for elements -->
                        @foreach(['photo', 'qr', 'name', 'id_number', 'class'] as $el)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $el }}">
                                    {{ ucfirst(str_replace('_', ' ', $el)) }}
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
                                        @if(in_array($el, ['photo', 'qr']))
                                        <div class="col-6">
                                            <label class="small">Lebar (W)</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="w">
                                        </div>
                                        <div class="col-6">
                                            <label class="small">Tinggi (H)</label>
                                            <input type="number" class="form-control form-control-sm config-sync" data-el="{{ $el }}" data-prop="h">
                                        </div>
                                        @else
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
                                        @endif
                                        <div class="col-12 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input config-sync" type="checkbox" data-el="{{ $el }}" data-prop="show" checked>
                                                <label class="form-check-label small">Tampilkan Elemen</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100">Simpan Template</button>
                    <a href="{{ route('admin.id-card-templates.index') }}" class="btn btn-label-secondary w-100 mt-2">Batal</a>
                </div>
            </div>
        </div>

        <!-- Designer Preview -->
        <div class="col-xl-8 col-lg-7 col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Live Preview (Skala 1:1)</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" id="btnPortrait">Portrait</button>
                        <button type="button" class="btn btn-outline-primary active" id="btnLandscape">Landscape</button>
                    </div>
                </div>
                <div class="card-body py-5 overflow-auto bg-lighter">
                    <div id="id-card-preview-container">
                        <div id="id-card-canvas" style="background-image: url('{{ $template->background_path ? Storage::url($template->background_path) : '' }}')">
                            <!-- Elements will be rendered here via JS -->
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <p class="text-muted small"><i class="ti tabler-info-circle me-1"></i> Geser elemen di preview untuk memindahkan posisi secara instan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
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
        Object.keys(config.elements).forEach(key => {
            const el = config.elements[key];
            if (!el.show) return;

            const div = document.createElement('div');
            div.className = 'draggable-element element-' + (['photo', 'qr'].includes(key) ? key : 'text');
            div.id = 'el-' + key;
            div.style.left = el.x + 'px';
            div.style.top = el.y + 'px';

            if (['photo', 'qr'].includes(key)) {
                div.style.width = el.w + 'px';
                div.style.height = el.h + 'px';
                div.innerHTML = key === 'photo' ? '<i class="ti tabler-user"></i> FOTO' : '<i class="ti tabler-qrcode"></i> QR';
            } else {
                div.style.fontSize = el.size + 'px';
                div.style.color = el.color;
                div.style.textAlign = el.align;
                div.innerText = getLabelFor(key);
                
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
        if(key === 'name') return 'NAMA LENGKAP SISWA';
        if(key === 'id_number') return 'NISN: 0012345678';
        if(key === 'class') return 'KELAS: XII IPA 1';
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
            if(!['photo', 'qr'].includes(key) && config.elements[key].align === 'center') {
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
        config.canvas = { width: 350, height: 500 };
        document.getElementById('btnPortrait').classList.add('active', 'btn-outline-primary');
        document.getElementById('btnLandscape').classList.remove('active', 'btn-outline-primary');
        updateCanvasSize();
        renderElements();
    });

    document.getElementById('btnLandscape').addEventListener('click', () => {
        config.canvas = { width: 500, height: 350 };
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

    // Initial Load
    updateCanvasSize();
    renderElements();
});
</script>
@endpush
