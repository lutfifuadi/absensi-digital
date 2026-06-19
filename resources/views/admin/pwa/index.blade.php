@extends('layouts/layoutMaster')

@section('title', 'Pengaturan PWA')

@section('page-style')
<style>
:root {
  --das-primary:      #7367f0;
  --das-primary-soft: rgba(115,103,240,0.12);
  --das-success:      #28c76f;
  --das-success-soft: rgba(40,199,111,0.12);
  --das-info:         #00cfe8;
  --das-info-soft:    rgba(0,207,232,0.12);
  --das-warning:      #ff9f43;
  --das-warning-soft: rgba(255,159,67,0.12);
  --das-danger:       #ea5455;
  --das-danger-soft:  rgba(234,84,85,0.12);
  --das-secondary:    #a8aaae;
  --das-surface:       rgba(15, 23, 42, 0.45);
  --das-border:        rgba(255,255,255,0.07);
  --das-radius:        5px;
  --das-radius-sm:     5px;
}
.text-gradient-gold {
  background: linear-gradient(to right, #fff, #ffd700);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.glass-card {
  background: rgba(255,255,255,0.03) !important;
  backdrop-filter: blur(12px) saturate(180%);
  border: 1px solid var(--das-border) !important;
}

.set-hero {
  position: relative;
  border-radius: var(--das-radius);
  overflow: hidden;
}
.set-hero__bg {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, #1e1b4b 0%, #312d89 45%, #4338ca 100%);
  z-index: 0;
}
.set-hero__glass {
  position: absolute; inset: 0;
  background: radial-gradient(circle at top right, rgba(115,103,240,0.18), transparent 45%);
  z-index: 1;
}
.set-hero__grid {
  position: absolute; inset: 0; z-index: 1;
  background-image: linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
  background-size: 40px 40px;
}
.set-hero__inner {
  position: relative; z-index: 2;
  display: flex; align-items: center;
  justify-content: space-between;
  padding: 2rem 2.5rem;
  gap: 1.5rem; flex-wrap: wrap;
}
.set-hero__identity { display: flex; align-items: center; gap: 1.25rem; }
.set-hero__icon-wrap {
  position: relative;
  width: 64px; height: 64px; border-radius: 5px;
  background: rgba(115,103,240,0.2);
  border: 1.5px solid rgba(115,103,240,0.4);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.75rem; color: #a5a2f7; flex-shrink: 0;
}
.set-hero__icon-glow {
  position: absolute; inset: -8px;
  background: var(--das-primary);
  filter: blur(18px); opacity: 0.2;
  border-radius: 50%; z-index: -1;
}
.set-hero__badge {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 0.62rem; font-weight: 700;
  letter-spacing: 1.2px; text-transform: uppercase;
  background: rgba(115,103,240,0.18);
  border: 1px solid rgba(115,103,240,0.3);
  color: #a5a2f7;
  padding: 3px 10px; border-radius: 20px; margin-bottom: 6px;
}
.pulse-dot {
  width: 6px; height: 6px; background: #a5a2f7; border-radius: 50%;
  animation: pulseGlow 1.5s infinite;
}
@keyframes pulseGlow {
  50% { transform: scale(1.3); opacity: 1; }
  100% { transform: scale(0.8); opacity: 0.5; }
}
.set-hero__title {
  font-size: 1.5rem; font-weight: 800;
  margin: 0 0 4px;
}
.set-hero__sub {
  margin: 0; font-size: 0.8rem;
  color: rgba(255,255,255,0.5);
  max-width: 500px;
}
.set-hero__breadcrumb {
  border-radius: var(--das-radius-sm);
  padding: 0.6rem 1rem;
  display: flex; align-items: center;
  background: rgba(0,0,0,0.2) !important;
}

.set-panel {
  background: var(--das-surface);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius);
  overflow: hidden;
  backdrop-filter: blur(6px);
  margin-bottom: 1.25rem;
}
.set-panel__head {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--das-border);
  background: linear-gradient(90deg, rgba(115,103,240,0.06) 0%, transparent 60%);
}
.set-panel__title-wrap {
  display: flex; align-items: center; gap: 1rem;
}
.set-panel__icon {
  width: 44px; height: 44px; border-radius: var(--das-radius);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.25rem; flex-shrink: 0;
}
.set-panel__icon.--primary   { background: var(--das-primary-soft); color: var(--das-primary); }
.set-panel__icon.--info      { background: var(--das-info-soft);    color: var(--das-info);    }
.set-panel__title  { font-size: 1rem; font-weight: 700; color: #e2e8f0; margin: 0 0 2px; }
.set-panel__sub    { font-size: 0.72rem; color: #64748b; margin: 0; }
.set-panel__body   { padding: 1.5rem; }

.set-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.15rem;
}
.set-field--full { grid-column: 1 / -1; }

.set-label {
  display: block;
  font-size: 0.62rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.8px;
  color: #64748b; margin-bottom: 0.45rem;
}
.set-input-group {
  display: flex; align-items: center;
  background: rgba(15,23,42,0.5);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius-sm);
  overflow: hidden;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.set-input-group:focus-within {
  border-color: var(--das-primary);
  box-shadow: 0 0 0 3px rgba(115,103,240,0.12);
}
.set-input {
  flex: 1; padding: 0.6rem 0.75rem;
  background: transparent; border: none;
  color: #e2e8f0; font-size: 0.85rem;
  outline: none; min-width: 0;
}
.set-input::placeholder { color: #334155; }

.set-field-hint {
  display: flex; align-items: center; gap: 4px;
  font-size: 0.7rem; font-weight: 600; margin-top: 6px;
  color: #475569;
}
.set-field-hint.--info    { color: var(--das-info); }

.color-input-wrapper {
  display: flex; align-items: center;
  background: rgba(15,23,42,0.5);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius-sm);
  overflow: hidden;
}
.color-input-wrapper input[type="color"] {
  width: 40px; height: 36px;
  padding: 0; border: none;
  background: transparent;
  cursor: pointer;
}
.color-input-wrapper .color-value {
  padding: 0 0.75rem;
  font-size: 0.8rem;
  color: #94a3b8;
  font-family: monospace;
}

.icon-preview {
  width: 80px; height: 80px;
  background: rgba(15,23,42,0.8);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius-sm);
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
.icon-preview img {
  width: 100%; height: 100%;
  object-fit: contain;
}
.icon-preview .no-icon {
  color: #475569;
  font-size: 1.5rem;
}

.file-upload-wrapper {
  display: flex; gap: 1rem; align-items: flex-start;
}
.file-upload-wrapper .upload-area {
  flex: 1;
}
.file-upload-wrapper .upload-area input[type="file"] {
  font-size: 0.85rem;
}
.file-upload-wrapper .upload-area input[type="url"] {
  margin-top: 0.5rem;
}

.alert-panel {
  background: var(--das-surface);
  border: 1px solid rgba(255,159,67,0.25);
  border-radius: var(--das-radius);
  padding: 1.25rem 1.5rem;
  backdrop-filter: blur(6px);
}
.alert-panel__title {
  display: flex; align-items: center; gap: 0.5rem;
  font-size: 0.9rem; font-weight: 700;
  color: #fbbf24; margin-bottom: 0.75rem;
}
.alert-panel__list {
  margin: 0; padding-left: 1.25rem;
  font-size: 0.8rem;
  color: rgba(255,255,255,0.7);
}
.alert-panel__list li { margin-bottom: 0.4rem; }
.alert-panel__list code {
  background: rgba(255,255,255,0.1);
  padding: 1px 5px; border-radius: 3px;
  font-size: 0.75rem;
  color: #fbbf24;
}

.set-save-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
  background: var(--das-primary);
  border: none; border-radius: var(--das-radius-sm);
  color: white; font-size: 0.82rem; font-weight: 700;
  padding: 0.7rem 1.25rem; cursor: pointer;
  transition: all 0.2s ease;
  letter-spacing: 0.3px;
}
.set-save-btn:hover {
  background: #6259e8;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(115,103,240,0.3);
}
.set-save-btn i { font-size: 1.05rem; }

.set-toast {
  display: flex; align-items: center; gap: 0.75rem;
  background: rgba(40,199,111,0.12);
  border: 1px solid rgba(40,199,111,0.25);
  border-radius: var(--das-radius-sm);
  padding: 0.85rem 1.1rem;
}
.set-toast__icon { color: var(--das-success); font-size: 1.2rem; flex-shrink: 0; }
.set-toast__msg  { flex: 1; font-size: 0.85rem; color: #d1fae5; }
.set-toast__close {
  background: transparent; border: none; color: #888; cursor: pointer;
  padding: 0; font-size: 0.9rem; transition: color 0.15s;
}
.set-toast__close:hover { color: white; }
.set-toast.--danger {
  background: rgba(234,84,85,0.12);
  border-color: rgba(234,84,85,0.25);
}
.set-toast.--danger .set-toast__icon {
  color: var(--das-danger);
}
.set-toast.--danger .set-toast__msg {
  color: #fecaca;
}

@media (max-width: 767px) {
  .set-form-grid { grid-template-columns: 1fr; }
  .set-field--full { grid-column: 1; }
  .set-hero__inner { flex-direction: column; align-items: flex-start; }
  .file-upload-wrapper { flex-direction: column; }
  .file-upload-wrapper .icon-preview { margin: 0 auto; }
}
</style>
@endsection

@section('content')

@if (session('success'))
  <div class="set-toast mb-4" id="successToast">
    <div class="set-toast__icon"><i class="ti tabler-circle-check"></i></div>
    <div class="set-toast__msg">{{ session('success') }}</div>
    <button type="button" class="set-toast__close" onclick="document.getElementById('successToast').style.display='none'"><i class="ti tabler-x"></i></button>
  </div>
@endif

@if (session('error'))
  <div class="set-toast --danger mb-4" id="errorToast">
    <div class="set-toast__icon"><i class="ti tabler-alert-circle"></i></div>
    <div class="set-toast__msg">{{ session('error') }}</div>
    <button type="button" class="set-toast__close" onclick="document.getElementById('errorToast').style.display='none'"><i class="ti tabler-x"></i></button>
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger mb-4">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

{{-- Hero Header --}}
<div class="set-hero mb-4">
  <div class="set-hero__bg"></div>
  <div class="set-hero__glass"></div>
  <div class="set-hero__grid"></div>
  <div class="set-hero__inner">
    <div class="set-hero__identity">
      <div class="set-hero__icon-wrap">
        <i class="ti tabler-device-mobile"></i>
        <div class="set-hero__icon-glow" style="background:rgba(0,207,232,.35);"></div>
      </div>
      <div>
        <div class="set-hero__badge">
          <span class="pulse-dot" style="background:#00cfe8;"></span>
          Progressive Web App
        </div>
        <h4 class="set-hero__title text-gradient-gold">Pengaturan PWA</h4>
        <p class="set-hero__sub">Konfigurasi tampilan dan perilaku aplikasi web saat diinstall di perangkat user.</p>
      </div>
    </div>
  </div>
</div>

<div class="d-flex gap-2 mb-4 align-items-center">
  <a href="{{ route('admin.pengaturan.index') }}" class="btn btn-sm btn-outline-secondary">
    <i class="ti tabler-arrow-left me-1"></i>Kembali ke Pengaturan
  </a>
</div>

<form action="{{ route('admin.pwa.update') }}" method="POST" enctype="multipart/form-data">
  @csrf

  <div class="set-panel mb-4">
    <div class="set-panel__head">
      <div class="set-panel__title-wrap">
        <div class="set-panel__icon --primary"><i class="ti tabler-info-circle"></i></div>
        <div>
          <div class="set-panel__title">Informasi Aplikasi</div>
          <div class="set-panel__sub">Data dasar aplikasi yang ditampilkan pada PWA.</div>
        </div>
      </div>
    </div>
    <div class="set-panel__body">
      <div class="set-form-grid">
        <div class="set-field">
          <label class="set-label">Nama Aplikasi (name)</label>
          <div class="set-input-group">
            <input type="text" class="set-input" id="name" name="name" value="{{ old('name', $manifest['name'] ?? '') }}" required placeholder="Absensi Digital Sekolah">
          </div>
          <div class="set-field-hint">Nama lengkap yang ditampilkan pada splash screen.</div>
          @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="set-field">
          <label class="set-label">Nama Pendek (short_name)</label>
          <div class="set-input-group">
            <input type="text" class="set-input" id="short_name" name="short_name" value="{{ old('short_name', $manifest['short_name'] ?? '') }}" required placeholder="Absensi">
          </div>
          <div class="set-field-hint">Nama di bawah icon home screen.</div>
          @error('short_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="set-field set-field--full">
          <label class="set-label">Deskripsi</label>
          <div class="set-input-group">
            <input type="text" class="set-input" id="description" name="description" value="{{ old('description', $manifest['description'] ?? '') }}" placeholder="Aplikasi absensi digital untuk sekolah">
          </div>
          @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>
  </div>

  <div class="set-panel mb-4">
    <div class="set-panel__head">
      <div class="set-panel__title-wrap">
        <div class="set-panel__icon --info"><i class="ti tabler-palette"></i></div>
        <div>
          <div class="set-panel__title">Warna Tema</div>
          <div class="set-panel__sub">Pengaturan warna untuk address bar dan splash screen.</div>
        </div>
      </div>
    </div>
    <div class="set-panel__body">
      <div class="set-form-grid">
        <div class="set-field">
          <label class="set-label">Theme Color</label>
          <div class="color-input-wrapper">
            <input type="color" id="theme_color" name="theme_color" value="{{ old('theme_color', $manifest['theme_color'] ?? '#0f3460') }}">
            <span class="color-value" id="theme_color_value">{{ old('theme_color', $manifest['theme_color'] ?? '#0f3460') }}</span>
          </div>
          <div class="set-field-hint">Warna address bar browser.</div>
          @error('theme_color')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="set-field">
          <label class="set-label">Background Color</label>
          <div class="color-input-wrapper">
            <input type="color" id="background_color" name="background_color" value="{{ old('background_color', $manifest['background_color'] ?? '#16213e') }}">
            <span class="color-value" id="background_color_value">{{ old('background_color', $manifest['background_color'] ?? '#16213e') }}</span>
          </div>
          <div class="set-field-hint">Warna latar splash screen.</div>
          @error('background_color')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>
  </div>

  <div class="set-panel mb-4">
    <div class="set-panel__head">
      <div class="set-panel__title-wrap">
        <div class="set-panel__icon --primary"><i class="ti tabler-photo"></i></div>
        <div>
          <div class="set-panel__title">Icon PWA</div>
          <div class="set-panel__sub">Upload icon untuk tampilan di home screen dan splash screen.</div>
        </div>
      </div>
    </div>
    <div class="set-panel__body">
      @php
        $icon192 = null;
        $icon512 = null;
        if(isset($manifest['icons']) && is_array($manifest['icons'])){
          foreach($manifest['icons'] as $icon){
             if(isset($icon['sizes']) && $icon['sizes'] == '192x192') $icon192 = $icon['src'];
             if(isset($icon['sizes']) && $icon['sizes'] == '512x512') $icon512 = $icon['src'];
          }
        }
      @endphp

      <div class="set-form-grid">
        <div class="set-field">
          <label class="set-label">Icon 192x192 px</label>
          <div class="file-upload-wrapper">
            <div class="icon-preview">
              @if($icon192)
                <img src="{{ url($icon192) }}" alt="Icon 192">
              @else
                <i class="ti tabler-photo-off no-icon"></i>
              @endif
            </div>
            <div class="upload-area">
              <input class="form-control" type="file" id="icon_192" name="icon_192" accept="image/png">
              <input type="url" class="form-control mt-2" id="icon_192_url" name="icon_192_url" placeholder="Atau masukkan URL..." value="{{ old('icon_192_url', (filter_var($icon192, FILTER_VALIDATE_URL) ? $icon192 : '')) }}">
              <div class="set-field-hint">Upload PNG (Maks 2MB) atau gunakan URL.</div>
              @error('icon_192')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              @error('icon_192_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        <div class="set-field">
          <label class="set-label">Icon 512x512 px</label>
          <div class="file-upload-wrapper">
            <div class="icon-preview">
              @if($icon512)
                <img src="{{ url($icon512) }}" alt="Icon 512">
              @else
                <i class="ti tabler-photo-off no-icon"></i>
              @endif
            </div>
            <div class="upload-area">
              <input class="form-control" type="file" id="icon_512" name="icon_512" accept="image/png">
              <input type="url" class="form-control mt-2" id="icon_512_url" name="icon_512_url" placeholder="Atau masukkan URL..." value="{{ old('icon_512_url', (filter_var($icon512, FILTER_VALIDATE_URL) ? $icon512 : '')) }}">
              <div class="set-field-hint">Upload PNG (Maks 4MB) - Disarankan resolusi tinggi.</div>
              @error('icon_512')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              @error('icon_512_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="alert-panel mb-4">
    <div class="alert-panel__title">
      <i class="ti tabler-alert-triangle"></i>
      Catatan Penting
    </div>
    <ul class="alert-panel__list">
      <li>Perubahan nama, warna, atau icon mungkin tidak langsung terlihat di perangkat user yang sudah install PWA.</li>
      <li>Browser mencache file <code>manifest.json</code>. User perlu Force Reload atau hapus cache untuk melihat pembaruan.</li>
      <li>Di Android, untuk melihat perubahan icon, user harus uninstall PWA terlebih dahulu lalu install ulang.</li>
    </ul>
  </div>

  <div class="d-flex justify-content-end gap-2 mb-5">
    <a href="{{ route('admin.pengaturan.index') }}" class="btn btn-outline-secondary">
      <i class="ti tabler-arrow-left me-1"></i>Batal
    </a>
    <button type="submit" class="set-save-btn">
      <i class="ti tabler-device-floppy me-1"></i>Simpan Perubahan
    </button>
  </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const themeColorInput = document.getElementById('theme_color');
  const themeColorValue = document.getElementById('theme_color_value');
  const bgColorInput = document.getElementById('background_color');
  const bgColorValue = document.getElementById('background_color_value');

  if(themeColorInput && themeColorValue) {
    themeColorInput.addEventListener('input', function() {
      themeColorValue.textContent = this.value;
    });
  }

  if(bgColorInput && bgColorValue) {
    bgColorInput.addEventListener('input', function() {
      bgColorValue.textContent = this.value;
    });
  }
});
</script>
@endpush