@php
  $driveSetting = \App\Models\GoogleDriveSetting::firstOrNew();
  $hasClientCredentials = !empty($driveSetting->google_client_id) && !empty($driveSetting->google_client_secret);
@endphp

<div class="set-tab" id="tab-google-drive">
  <div class="set-panel">
    <div class="set-panel__head">
      <div class="set-panel__title-wrap">
        <div class="set-panel__icon --primary"><i class="ti tabler-brand-google-drive"></i></div>
        <div>
          <div class="set-panel__title">Google Drive (Foto Absen)</div>
          <div class="set-panel__sub">Konfigurasi penyimpanan foto presensi mandiri secara otomatis ke Google Drive.</div>
        </div>
      </div>
    </div>
    
    <div class="set-panel__body">
      {{-- 1. CONTAINER LOADING --}}
      <div id="gd-loading" class="py-5 text-center text-muted">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mb-0">Memeriksa status koneksi Google Drive...</p>
      </div>

      {{-- 2. CONTAINER CONNECTED --}}
      <div id="gd-connected" style="display: none;">
        {{-- Card Status Connected --}}
        <div class="mb-4 p-4 rounded-3 border position-relative overflow-hidden" 
             style="background: rgba(40, 199, 111, 0.05); border-color: rgba(40, 199, 111, 0.15) !important; backdrop-filter: blur(10px);">
          <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center bg-success text-white rounded-3 animate__animated animate__pulse animate__infinite" 
                 style="width: 48px; height: 48px; background: rgba(40, 199, 111, 0.2) !important; color: #28c76f !important;">
              <i class="ti tabler-cloud-check fs-2"></i>
            </div>
            <div>
              <h5 class="mb-1 text-white">Google Drive Terhubung</h5>
              <p class="mb-0 text-muted small" id="gd-user-email">Email: -</p>
            </div>
          </div>
        </div>

        {{-- Info Storage Quota --}}
        <div class="mb-4 p-4 rounded-3 border" style="background: rgba(255, 255, 255, 0.02); border-color: rgba(255, 255, 255, 0.08) !important;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted small">Kapasitas Penyimpanan</span>
            <span class="text-white fw-bold small" id="gd-storage-text">0 GB / 0 GB (0%)</span>
          </div>
          <div class="progress" style="height: 10px; background: rgba(255, 255, 255, 0.05); border-radius: 5px;">
            <div id="gd-storage-progress" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; border-radius: 5px;"></div>
          </div>
        </div>

        {{-- Form Root Folder ID (POST) --}}
        <form action="{{ route('admin.google.update-settings') }}" method="POST" class="mb-4" id="formGoogleDriveConnected">
          @csrf
          <div class="set-field set-field--full">
            <label class="set-label">Folder ID Google Drive</label>
            <div class="set-input-group">
              <span class="set-input-prefix"><i class="ti tabler-folder"></i></span>
              <input type="text" class="set-input font-monospace" name="google_root_folder_id" id="gd-connected-folder-id"
                value="{{ old('google_root_folder_id', $driveSetting->google_root_folder_id) }}"
                placeholder="Contoh: 1a2b3c4d5e6f7g8h9i0j...">
            </div>
            <div class="set-field-hint --info">
              <i class="ti tabler-info-circle"></i> ID Folder tujuan upload dari URL Google Drive.
            </div>
          </div>
        </form>

        {{-- Form Revoke Connection --}}
        <form action="{{ route('admin.google.revoke') }}" method="POST" id="formGoogleRevoke">
          @csrf
          <div class="p-4 rounded-3 border d-flex flex-wrap justify-content-between align-items-center gap-3" 
               style="background: rgba(234, 84, 85, 0.05); border-color: rgba(234, 84, 85, 0.15) !important;">
            <div>
              <h6 class="mb-1 text-white">Putuskan Hubungan Google Drive</h6>
              <p class="mb-0 text-muted small">Mencabut akses aplikasi ini dari akun Google Drive Anda.</p>
            </div>
            <button type="submit" class="btn btn-danger btn-md px-4">
              <i class="ti tabler-unlink me-1"></i> Putuskan Koneksi
            </button>
          </div>
        </form>
      </div>

      {{-- 3. CONTAINER DISCONNECTED --}}
      <div id="gd-disconnected" style="display: none;">
        {{-- Card Status Disconnected --}}
        <div class="mb-4 p-4 rounded-3 border" 
             style="background: rgba(234, 84, 85, 0.05); border-color: rgba(234, 84, 85, 0.15) !important;">
          <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center bg-danger text-white rounded-3" 
                 style="width: 48px; height: 48px; background: rgba(234, 84, 85, 0.2) !important; color: #ea5455 !important;">
              <i class="ti tabler-cloud-off fs-2"></i>
            </div>
            <div>
              <h5 class="mb-1 text-white">Belum Terhubung</h5>
              <p class="mb-0 text-muted small">Anda perlu mengatur kredensial dan menghubungkan akun Google Drive.</p>
            </div>
          </div>
        </div>

        {{-- Form Save Configuration --}}
        <form action="{{ route('admin.google.update-settings') }}" method="POST" id="formGoogleDrive">
          @csrf
          
          <div class="set-form-grid mb-4">
            <div class="set-field set-field--full mb-3">
              <label class="set-label">Google Client ID</label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-id"></i></span>
                <input type="text" class="set-input font-monospace" name="google_client_id"
                  value="{{ old('google_client_id', $driveSetting->google_client_id) }}"
                  placeholder="Masukkan Google Client ID">
              </div>
            </div>

            <div class="set-field set-field--full mb-3">
              <label class="set-label">Google Client Secret</label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-lock"></i></span>
                <input type="password" class="set-input font-monospace" name="google_client_secret"
                  value="{{ $driveSetting->google_client_secret ? '********' : '' }}"
                  placeholder="Masukkan Google Client Secret">
              </div>
            </div>

            <div class="set-field set-field--full mb-3">
              <label class="set-label">Google Redirect URI</label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-link"></i></span>
                <input type="text" class="set-input font-monospace text-muted" readonly
                  value="{{ route('admin.google.callback') }}">
              </div>
              <div class="set-field-hint --info">
                <i class="ti tabler-info-circle"></i> Salin URL redirect ini dan tempel di bagian <b>Authorized redirect URIs</b> pada Google Cloud Console.
              </div>
            </div>

            <div class="set-field set-field--full mb-3">
              <label class="set-label">Root Folder ID</label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-folder"></i></span>
                <input type="text" class="set-input font-monospace" name="google_root_folder_id"
                  value="{{ old('google_root_folder_id', $driveSetting->google_root_folder_id) }}"
                  placeholder="Contoh: 1a2b3c4d5e6f7g8h9i0j...">
              </div>
              <div class="set-field-hint --info">
                <i class="ti tabler-info-circle"></i> ID Folder tujuan upload dari URL Google Drive.
              </div>
            </div>
          </div>
        </form>

        {{-- Oauth Action Section --}}
        <div class="p-4 rounded-3 border d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4" 
             style="background: rgba(115, 103, 240, 0.05); border-color: rgba(115, 103, 240, 0.15) !important;">
          <div class="flex-grow-1">
            <h6 class="mb-1 text-white">Hubungkan Google Drive</h6>
            <p class="mb-0 text-muted small">Otorisasikan aplikasi ini untuk mengakses penyimpanan Google Drive.</p>
          </div>
          <div>
            @if($hasClientCredentials)
              <a href="{{ route('admin.google.redirect') }}" class="btn btn-primary px-4">
                <i class="ti tabler-brand-google-drive me-1"></i> Hubungkan ke Google Drive
              </a>
            @else
              <button class="btn btn-secondary px-4" disabled title="Harap simpan Client ID dan Client Secret terlebih dahulu.">
                <i class="ti tabler-brand-google-drive me-1"></i> Hubungkan ke Google Drive
              </button>
              <span class="d-block text-danger small mt-1 text-end">Simpan Client ID & Secret dulu</span>
            @endif
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    checkGoogleDriveStatus();
  });

  function checkGoogleDriveStatus() {
    const loadingEl = document.getElementById('gd-loading');
    const connectedEl = document.getElementById('gd-connected');
    const disconnectedEl = document.getElementById('gd-disconnected');

    if (!loadingEl || !connectedEl || !disconnectedEl) return;

    fetch("{{ route('admin.google.status') }}")
      .then(response => {
        if (!response.ok) {
          throw new Error('Gagal memeriksa status koneksi Google Drive.');
        }
        return response.json();
      })
      .then(data => {
        loadingEl.style.setProperty('display', 'none', 'important');
        if (data.connected) {
          disconnectedEl.style.setProperty('display', 'none', 'important');
          connectedEl.style.setProperty('display', 'block', 'important');
          
          // Set email
          const emailEl = document.getElementById('gd-user-email');
          if (emailEl) {
            emailEl.textContent = `Email Terhubung: ${data.email}`;
          }

          // Set folder ID
          const folderInputEl = document.getElementById('gd-connected-folder-id');
          if (folderInputEl && data.root_folder_id) {
            folderInputEl.value = data.root_folder_id;
          }

          // Set storage info
          const storageTextEl = document.getElementById('gd-storage-text');
          const storageProgressEl = document.getElementById('gd-storage-progress');
          if (data.storage) {
            const usage = data.storage.usage_gb;
            const limit = data.storage.is_unlimited ? 'Unlimited' : `${data.storage.limit_gb} GB`;
            const percent = data.storage.used_percent;

            if (storageTextEl) {
              storageTextEl.textContent = `${usage} GB / ${limit} (${percent}%)`;
            }

            if (storageProgressEl) {
              storageProgressEl.style.width = `${percent}%`;
              storageProgressEl.setAttribute('aria-valuenow', percent);
            }
          }
        } else {
          connectedEl.style.setProperty('display', 'none', 'important');
          disconnectedEl.style.setProperty('display', 'block', 'important');
        }
      })
      .catch(error => {
        console.error('Error checking Google Drive status:', error);
        loadingEl.style.setProperty('display', 'none', 'important');
        disconnectedEl.style.setProperty('display', 'block', 'important');
        
        // Tampilkan pesan error anggun
        const statusCard = disconnectedEl.querySelector('.bg-danger');
        if (statusCard) {
          const p = statusCard.parentElement.querySelector('p');
          if (p) {
            p.textContent = `Terjadi kesalahan saat menghubungi API Google Drive: ${error.message}`;
          }
        }
      });
  }
</script>