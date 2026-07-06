<div class="set-tab" id="tab-api-source">
  <form action="{{ route('admin.pengaturan.api-source.update') }}" method="POST" id="formApiSource">
    @csrf
    <div class="set-panel mb-4">
      <div class="set-panel__head">
        <div class="set-panel__title-wrap">
          <div class="set-panel__icon --primary"><i class="ti tabler-server"></i></div>
          <div>
            <div class="set-panel__title">Konfigurasi Pengambilan Data</div>
            <div class="set-panel__sub">Atur API sumber data eksternal yang akan digunakan untuk sync user dan siswa.</div>
          </div>
        </div>
      </div>
      <div class="set-panel__body">
        <div class="set-form-grid">
          {{-- Mode Sinkronisasi --}}
          <div class="set-field set-field--full">
            <label class="set-label">Mode Sinkronisasi</label>
            <div class="d-flex gap-3 mt-1">
              <label class="sync-mode-card {{ old('master_db_sync_mode', $settings['master_db_sync_mode'] ?? 'otomatis') === 'otomatis' ? 'active' : '' }}" id="card-otomatis">
                <input type="radio" name="master_db_sync_mode" value="otomatis" class="d-none" onchange="toggleSyncMode(this)"
                  {{ old('master_db_sync_mode', $settings['master_db_sync_mode'] ?? 'otomatis') === 'otomatis' ? 'checked' : '' }}>
                <div class="sync-mode-card__icon"><i class="ti tabler-clock-play"></i></div>
                <div>
                  <div class="sync-mode-card__title">Otomatis (Terjadwal)</div>
                  <div class="sync-mode-card__sub">Sinkronisasi berjalan otomatis sesuai jadwal yang ditentukan.</div>
                </div>
              </label>
              <label class="sync-mode-card {{ old('master_db_sync_mode', $settings['master_db_sync_mode'] ?? '') === 'manual' ? 'active' : '' }}" id="card-manual">
                <input type="radio" name="master_db_sync_mode" value="manual" class="d-none" onchange="toggleSyncMode(this)"
                  {{ old('master_db_sync_mode', $settings['master_db_sync_mode'] ?? '') === 'manual' ? 'checked' : '' }}>
                <div class="sync-mode-card__icon"><i class="ti tabler-hand-click"></i></div>
                <div>
                  <div class="sync-mode-card__title">Manual</div>
                  <div class="sync-mode-card__sub">Sinkronisasi hanya berjalan jika dipicu secara manual oleh admin.</div>
                </div>
              </label>
            </div>
            @error('master_db_sync_mode')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          {{-- Aktifkan Sinkronisasi --}}
          <div class="set-field">
            <label class="set-label" for="master_db_sync_enabled">Aktifkan Sinkronisasi</label>
            <div class="set-input-group">
              <span class="set-input-prefix"><i class="ti tabler-switch-horizontal"></i></span>
              <select class="set-input {{ $errors->has('master_db_sync_enabled') ? 'is-invalid' : '' }}"
                id="master_db_sync_enabled"
                name="master_db_sync_enabled"
                aria-describedby="master_db_sync_enabled_help">
                <option value="Ya" {{ old('master_db_sync_enabled', $settings['master_db_sync_enabled'] ?? 'Ya') === 'Ya' ? 'selected' : '' }}>Ya</option>
                <option value="Tidak" {{ old('master_db_sync_enabled', $settings['master_db_sync_enabled'] ?? '') === 'Tidak' ? 'selected' : '' }}>Tidak</option>
              </select>
            </div>
            <div id="master_db_sync_enabled_help" class="text-muted small mt-1">Pilih apakah sinkronisasi aktif atau nonaktif.</div>
            @error('master_db_sync_enabled')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          {{-- Waktu sinkronisasi — hanya tampil saat mode otomatis --}}
          <div class="set-field" id="field-sync-time" style="{{ old('master_db_sync_mode', $settings['master_db_sync_mode'] ?? 'otomatis') === 'manual' ? 'display:none;' : '' }}">
            <label class="set-label" for="master_db_sync_time">Waktu Sinkronisasi Otomatis</label>
            <div class="set-input-group">
              <span class="set-input-prefix"><i class="ti tabler-clock"></i></span>
              <input type="time" class="set-input {{ $errors->has('master_db_sync_time') ? 'is-invalid' : '' }}"
                id="master_db_sync_time"
                name="master_db_sync_time"
                value="{{ old('master_db_sync_time', $settings['master_db_sync_time'] ?? '03:00') }}"
                aria-describedby="master_db_sync_time_help">
            </div>
            <div id="master_db_sync_time_help" class="text-muted small mt-1">Waktu harian untuk proses sinkronisasi otomatis.</div>
            @error('master_db_sync_time')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="set-field set-field--full">
            <label class="set-label" for="master_db_api_url">API URL Sumber Data</label>
            <div class="set-input-group">
              <span class="set-input-prefix"><i class="ti tabler-world"></i></span>
              <input type="url" class="set-input font-monospace {{ $errors->has('master_db_api_url') ? 'is-invalid' : '' }}"
                id="master_db_api_url"
                name="master_db_api_url"
                value="{{ old('master_db_api_url', $settings['master_db_api_url'] ?? '') }}"
                placeholder="https://api.aplikasi-lain.com/v1"
                aria-describedby="master_db_api_url_help">
            </div>
            <div id="master_db_api_url_help" class="text-muted small mt-1">Masukkan full URL endpoint API yang akan digunakan untuk sinkronisasi.</div>
            @error('master_db_api_url')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="set-field set-field--full">
            <label class="set-label" for="master_db_api_key">API Key / Token</label>
            <div class="set-input-group">
              <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
              <input type="password" class="set-input font-monospace {{ $errors->has('master_db_api_key') ? 'is-invalid' : '' }}"
                id="master_db_api_key"
                name="master_db_api_key"
                value=""
                placeholder="Masukkan API Key untuk aplikasi sumber"
                autocomplete="new-password"
                aria-describedby="master_db_api_key_help">
            </div>
            <div id="master_db_api_key_help" class="text-muted small mt-1">Kosongkan jika Anda tidak ingin mengubah kunci API yang sudah tersimpan.</div>
            @error('master_db_api_key')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>
  </form>

  {{-- TOMBOL SINKRON SEKARANG --}}
  <div class="sync-now-panel mt-4">
    <div class="sync-now-panel__inner">
      <div class="sync-now-panel__info">
        <div class="sync-now-panel__icon"><i class="ti tabler-refresh"></i></div>
        <div>
          <div class="sync-now-panel__title">Sinkronisasi Sekarang</div>
          <div class="sync-now-panel__sub">Jalankan sinkronisasi data master dari API eksternal secara langsung tanpa menunggu jadwal otomatis.</div>
        </div>
      </div>
      <form action="{{ route('admin.pengaturan.api-source.sync-now') }}" method="POST" id="syncNowForm">
        @csrf
        <button type="button" class="sync-now-btn" id="openSyncConfirmButton" onclick="openSyncConfirmModal()">
          <i class="ti tabler-refresh"></i>
          <span>Sinkron Sekarang</span>
        </button>
      </form>
    </div>
  </div>

  <div id="syncConfirmModal" class="sync-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="syncConfirmTitle" aria-describedby="syncConfirmDescription" hidden>
    <div class="sync-confirm-modal__backdrop" onclick="closeSyncConfirmModal()" tabindex="-1"></div>
    <div class="sync-confirm-modal__content" role="document">
      <div class="sync-confirm-modal__header">
        <div class="sync-confirm-modal__icon"><i class="ti tabler-alert-circle"></i></div>
        <div>
          <h2 id="syncConfirmTitle">Konfirmasi Sinkronisasi</h2>
          <p id="syncConfirmDescription">Apakah Anda yakin ingin menjalankan sinkronisasi data master sekarang? Proses ini dapat memakan beberapa saat dan akan menarik data dari API eksternal.</p>
        </div>
      </div>
      <div class="sync-confirm-modal__actions">
        <button type="button" class="sync-confirm-modal__cancel-btn" onclick="closeSyncConfirmModal()">Batal</button>
        <button type="button" class="sync-confirm-modal__confirm-btn" id="syncConfirmButton" onclick="submitSyncNow()">
          <span id="syncBtnText">Konfirmasi</span>
          <span id="syncBtnSpinner" style="display:none;"><i class="ti tabler-loader-2 animate-spin"></i></span>
        </button>
      </div>
    </div>
  </div>

  {{-- MODAL SUKSES SINKRONISASI --}}
  <div id="syncSuccessModal" class="sync-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="syncSuccessTitle" aria-describedby="syncSuccessDescription" hidden>
    <div class="sync-confirm-modal__backdrop" onclick="closeSyncSuccessModal()" tabindex="-1"></div>
    <div class="sync-confirm-modal__content sync-confirm-modal__content--success" role="document">
      <div class="sync-confirm-modal__header text-center">
        <div class="sync-success-icon-wrap mb-3">
          <div class="sync-success-icon-ring"></div>
          <div class="sync-success-icon"><i class="ti tabler-circle-check"></i></div>
        </div>
        <h2 id="syncSuccessTitle" class="text-gradient-success">Berhasil Dijadwalkan!</h2>
        <p id="syncSuccessDescription" class="mt-2">Sinkronisasi data master telah dijadwalkan dan akan diproses di latar belakang. Anda dapat menutup halaman ini atau melanjutkan aktifitas lainnya.</p>
      </div>
      <div class="sync-confirm-modal__actions justify-content-center">
        <button type="button" class="sync-confirm-modal__confirm-btn sync-confirm-modal__confirm-btn--success" onclick="closeSyncSuccessModal()">
          Selesai
        </button>
      </div>
    </div>
  </div>

  <style>
  .sync-mode-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1.5px solid var(--das-border);
    border-radius: var(--das-radius-sm);
    cursor: pointer;
    flex: 1;
    transition: all 0.2s ease;
  }
  .sync-mode-card:hover {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.15);
  }
  .sync-mode-card.active {
    background: var(--das-primary-soft);
    border-color: var(--das-primary);
  }
  .sync-mode-card__icon {
    font-size: 1.5rem;
    color: #475569;
    transition: color 0.2s;
  }
  .sync-mode-card.active .sync-mode-card__icon {
    color: var(--das-primary);
  }
  .sync-mode-card__title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #e2e8f0;
  }
  .sync-mode-card__sub {
    font-size: 0.7rem;
    color: #64748b;
    margin-top: 2px;
  }
  </style>

  <script>
  function toggleSyncMode(radio) {
    const isOtomatis = radio.value === 'otomatis';
    const fieldTime = document.getElementById('field-sync-time');
    if (fieldTime) {
      fieldTime.style.display = isOtomatis ? '' : 'none';
    }
    document.getElementById('card-otomatis').classList.toggle('active', isOtomatis);
    document.getElementById('card-manual').classList.toggle('active', !isOtomatis);
  }

  function openSyncConfirmModal() {
    const modal = document.getElementById('syncConfirmModal');
    const confirmButton = document.getElementById('syncConfirmButton');
    if (!modal || !confirmButton) {
      return true;
    }
    modal.hidden = false;
    modal.style.pointerEvents = 'auto';
    setTimeout(() => confirmButton.focus(), 50);
  }

  function closeSyncConfirmModal() {
    const modal = document.getElementById('syncConfirmModal');
    const openButton = document.getElementById('openSyncConfirmButton');
    if (!modal) {
      return;
    }
    modal.hidden = true;
    modal.style.pointerEvents = 'none';
    if (openButton) {
      openButton.focus();
    }
  }

  function openSyncSuccessModal() {
    const modal = document.getElementById('syncSuccessModal');
    if (modal) {
      modal.hidden = false;
      modal.style.pointerEvents = 'auto';
    }
  }

  function closeSyncSuccessModal() {
    const modal = document.getElementById('syncSuccessModal');
    if (modal) {
      modal.hidden = true;
      modal.style.pointerEvents = 'none';
    }
  }

  async function submitSyncNow() {
    const form = document.getElementById('syncNowForm');
    const btn = document.getElementById('syncConfirmButton');
    const btnText = document.getElementById('syncBtnText');
    const btnSpinner = document.getElementById('syncBtnSpinner');
    const url = form.action;

    // Loading state
    btn.disabled = true;
    btnText.style.display = 'none';
    btnSpinner.style.display = 'inline-block';

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: JSON.stringify({})
      });

      const result = await response.json();

      if (result.success) {
        closeSyncConfirmModal();
        setTimeout(() => {
          openSyncSuccessModal();
        }, 300);
      } else {
        showDynamicToast('danger', result.message || 'Terjadi kesalahan saat sinkronisasi.');
      }
    } catch (error) {
      console.error('Sync error:', error);
      showDynamicToast('danger', 'Gagal menghubungi server. Silakan coba lagi.');
    } finally {
      // Reset state
      btn.disabled = false;
      btnText.style.display = 'inline-block';
      btnSpinner.style.display = 'none';
    }
  }

  function showDynamicToast(type, message) {
    const toastId = type === 'success' ? 'syncSuccessToast' : 'syncErrorToast';
    let toast = document.getElementById(toastId);
    
    if (toast) {
      const msgEl = toast.querySelector('.set-toast__msg');
      if (msgEl) msgEl.textContent = message;
      toast.style.display = 'flex';
      
      // Auto hide after 5s
      setTimeout(() => {
        toast.style.display = 'none';
      }, 5000);
    } else {
      alert(message);
    }
  }

  window.addEventListener('keydown', function (event) {
    const confirmModal = document.getElementById('syncConfirmModal');
    const successModal = document.getElementById('syncSuccessModal');
    
    if (event.key === 'Escape') {
      if (confirmModal && !confirmModal.hidden) {
        closeSyncConfirmModal();
      }
      if (successModal && !successModal.hidden) {
        closeSyncSuccessModal();
      }
    }
  });
  </script>
</div>
