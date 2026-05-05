@extends('layouts.layoutMaster')

@section('title', 'Pengaturan PWA')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Pengaturan /</span> PWA (Progressive Web App)
  </h4>

  <div class="row">
    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header border-bottom mb-3"><i class="ti tabler-device-mobile me-2"></i> Konfigurasi PWA</h5>
        
        <div class="card-body">
          <form action="{{ route('admin.pwa.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="name" class="form-label">Nama Aplikasi (name)</label>
                  <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $manifest['name'] ?? '') }}" required>
                  <div class="form-text">Nama lengkap aplikasi yang akan ditampilkan pada layar splash screen.</div>
                  @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                  <label for="short_name" class="form-label">Nama Pendek (short_name)</label>
                  <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name" value="{{ old('short_name', $manifest['short_name'] ?? '') }}" required>
                  <div class="form-text">Nama yang ditampilkan di bawah icon aplikasi pada home screen.</div>
                  @error('short_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                  <label for="description" class="form-label">Deskripsi</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description', $manifest['description'] ?? '') }}</textarea>
                  @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="theme_color" class="form-label">Theme Color</label>
                    <input type="color" class="form-control form-control-color w-100 @error('theme_color') is-invalid @enderror" id="theme_color" name="theme_color" value="{{ old('theme_color', $manifest['theme_color'] ?? '#0f3460') }}" required>
                    <div class="form-text">Warna tema utama aplikasi (seperti warna address bar browser).</div>
                    @error('theme_color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="background_color" class="form-label">Background Color</label>
                    <input type="color" class="form-control form-control-color w-100 @error('background_color') is-invalid @enderror" id="background_color" name="background_color" value="{{ old('background_color', $manifest['background_color'] ?? '#16213e') }}" required>
                    <div class="form-text">Warna latar belakang untuk splash screen.</div>
                    @error('background_color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="alert alert-info d-flex align-items-center" role="alert">
                  <i class="ti tabler-info-circle me-2"></i>
                  <div>
                    Untuk hasil terbaik, pastikan icon PWA memiliki bentuk persegi dengan background transparan atau solid.
                  </div>
                </div>

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

                <div class="mb-4">
                  <label for="icon_192" class="form-label fw-bold">Icon (192x192 px)</label>
                  <div class="d-flex align-items-start gap-3">
                    @if($icon192)
                      <div class="bg-dark p-2 rounded text-center" style="width: 100px; height: 100px;">
                        <img src="{{ url($icon192) }}" alt="Icon 192" class="img-fluid" style="max-height: 100%">
                      </div>
                    @else
                      <div class="bg-secondary p-2 rounded text-center d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="ti tabler-photo-off text-white opacity-50 fs-2"></i>
                      </div>
                    @endif
                    <div class="flex-grow-1">
                      <input class="form-control mb-2 @error('icon_192') is-invalid @enderror" type="file" id="icon_192" name="icon_192" accept="image/png">
                      <input type="url" class="form-control @error('icon_192_url') is-invalid @enderror" id="icon_192_url" name="icon_192_url" placeholder="Atau masukkan URL Icon 192x192...">
                      <div class="form-text">Upload file PNG (Maks 2MB) ATAU gunakan URL eksternal.</div>
                      @error('icon_192') <div class="invalid-feedback">{{ $message }}</div> @enderror
                      @error('icon_192_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                  </div>
                </div>

                <div class="mb-4">
                  <label for="icon_512" class="form-label fw-bold">Icon (512x512 px) - Splash Screen</label>
                  <div class="d-flex align-items-start gap-3">
                    @if($icon512)
                      <div class="bg-dark p-2 rounded text-center" style="width: 100px; height: 100px;">
                        <img src="{{ url($icon512) }}" alt="Icon 512" class="img-fluid" style="max-height: 100%">
                      </div>
                    @else
                      <div class="bg-secondary p-2 rounded text-center d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="ti tabler-photo-off text-white opacity-50 fs-2"></i>
                      </div>
                    @endif
                    <div class="flex-grow-1">
                      <input class="form-control mb-2 @error('icon_512') is-invalid @enderror" type="file" id="icon_512" name="icon_512" accept="image/png">
                      <input type="url" class="form-control @error('icon_512_url') is-invalid @enderror" id="icon_512_url" name="icon_512_url" placeholder="Atau masukkan URL Icon 512x512...">
                      <div class="form-text">Upload file PNG (Maks 4MB) ATAU gunakan URL eksternal. Disarankan beresolusi tinggi.</div>
                      @error('icon_512') <div class="invalid-feedback">{{ $message }}</div> @enderror
                      @error('icon_512_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <div class="mt-4 pt-3 border-top">
              <button type="submit" class="btn btn-primary me-2"><i class="ti tabler-device-floppy me-1"></i> Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
      
      <div class="card bg-label-warning">
         <div class="card-body">
             <h6 class="text-warning mb-2"><i class="ti tabler-alert-triangle me-1"></i> Catatan Penting Tentang PWA</h6>
             <ul class="text-warning mb-0 fs-6 ps-3">
                 <li>Perubahan nama, warna, atau icon mungkin tidak langsung terlihat di perangkat user yang sudah menginstall PWA.</li>
                 <li>Browser secara cerdas mencache file <code>manifest.json</code>. User mungkin perlu melakukan "Force Reload" atau menghapus cache aplikasi untuk melihat pembaruan.</li>
                 <li>Di perangkat Android, untuk melihat perubahan icon, user biasanya harus menghapus (uninstall) aplikasi PWA terlebih dahulu lalu menginstallnya kembali ("Add to Home Screen").</li>
             </ul>
         </div>
      </div>
    </div>
  </div>
</div>
@endsection
