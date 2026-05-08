@extends('layouts/layoutMaster')

@section('title', 'Pengaturan API Sumber Data')

@section('content')

<div class="set-hero mb-5">
  <div class="set-hero__bg"></div>
  <div class="set-hero__glass"></div>
  <div class="set-hero__grid"></div>
  <div class="set-hero__inner">
    <div class="set-hero__identity">
      <div class="set-hero__icon-wrap">
        <i class="ti tabler-api"></i>
        <div class="set-hero__icon-glow"></div>
      </div>
      <div>
        <div class="set-hero__badge">
          <span class="pulse-dot"></span>
          Admin API
        </div>
        <h4 class="set-hero__title text-gradient-gold">Pengaturan API Sumber Data</h4>
        <p class="set-hero__sub">Konfigurasi URL dan token API untuk pengambilan data dari aplikasi eksternal.</p>
      </div>
    </div>
    <div class="set-hero__breadcrumb glass-card">
      <span class="text-muted small"><i class="ti tabler-home me-1"></i>Dashboard</span>
      <i class="ti tabler-chevron-right text-muted mx-1" style="font-size:0.7rem;"></i>
      <span class="small text-white fw-semibold">Pengaturan API</span>
    </div>
  </div>
</div>

@if (session('success'))
  <div class="set-toast mb-4" id="successToast">
    <div class="set-toast__icon"><i class="ti tabler-circle-check"></i></div>
    <div class="set-toast__msg">{{ session('success') }}</div>
    <button type="button" class="set-toast__close" onclick="document.getElementById('successToast').style.display='none'"><i class="ti tabler-x"></i></button>
  </div>
@endif

@if (session('sync_success'))
  <div class="set-toast set-toast--success mb-4" id="syncSuccessToast">
    <div class="set-toast__icon"><i class="ti tabler-refresh-dot"></i></div>
    <div class="set-toast__msg">{{ session('sync_success') }}</div>
    <button type="button" class="set-toast__close" onclick="document.getElementById('syncSuccessToast').style.display='none'"><i class="ti tabler-x"></i></button>
  </div>
@endif

@if (session('sync_error'))
  <div class="set-toast set-toast--danger mb-4" id="syncErrorToast">
    <div class="set-toast__icon"><i class="ti tabler-alert-triangle"></i></div>
    <div class="set-toast__msg">{{ session('sync_error') }}</div>
    <button type="button" class="set-toast__close" onclick="document.getElementById('syncErrorToast').style.display='none'"><i class="ti tabler-x"></i></button>
  </div>
@endif

<form action="{{ route('admin.pengaturan.api-source.update') }}" method="POST">
  @csrf
  <div class="set-layout">

    {{-- ─────────────────────────────
         SIDEBAR NAV
    ───────────────────────────── --}}
    <aside class="set-sidebar">
      <div class="set-nav">
        <div class="set-nav__label">Menu Navigasi</div>
        <nav id="set-nav-tabs">
          <a href="{{ route('admin.pengaturan.index') }}" class="set-nav__item">
            <div class="set-nav__item-icon">
              <i class="ti tabler-settings-2"></i>
            </div>
            <span>Pengaturan Umum</span>
            <i class="ti tabler-chevron-right set-nav__item-arrow"></i>
          </a>
          <button type="button" class="set-nav__item active">
            <div class="set-nav__item-icon">
              <i class="ti tabler-api"></i>
            </div>
            <span>Pengaturan API</span>
            <i class="ti tabler-chevron-right set-nav__item-arrow"></i>
          </button>
        </nav>

        {{-- Save Button (Sidebar) --}}
        <div class="set-nav__save-wrap">
          <button type="submit" class="set-save-btn w-100">
            <i class="ti tabler-device-floppy"></i>
            <span>Simpan Pengaturan</span>
          </button>
        </div>
      </div>
    </aside>

    <main class="set-content">
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

      <div class="set-footer-save d-lg-none">
        <button type="submit" class="set-save-btn">
          <i class="ti tabler-device-floppy"></i>
          <span>Simpan Pengaturan API</span>
        </button>
      </div>
    </main>
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
/* CSS di bawah ini disalin dari index.blade.php agar konsisten */
:root {
  --das-primary: #7367f0;
  --das-primary-soft: rgba(115,103,240,0.12);
  --das-warning: #ff9f43;
  --das-warning-soft: rgba(255,159,67,0.12);
  --das-danger: #ea5455;
  --das-danger-soft: rgba(234,84,85,0.12);
  --das-success: #28c76f;
  --das-success-soft: rgba(40,199,111,0.12);
  --das-info: #00cfe8;
  --das-info-soft: rgba(0,207,232,0.12);
  --das-surface: #161d31;
  --das-surface-hover: #283046;
  --das-border: rgba(255,255,255,0.1);
  --das-radius: 10px;
  --das-radius-sm: 6px;
}

.font-monospace { font-family: 'Courier New', monospace !important; }

/* HERO HEADER */
.set-hero { position: relative; border-radius: var(--das-radius); overflow: hidden; }
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
  background-image:
    linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
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
  animation: heroIconSpin 20s linear infinite;
}
@keyframes heroIconSpin {
  0%,100% { box-shadow: 0 0 15px rgba(115,103,240,0.2); }
  50%      { box-shadow: 0 0 30px rgba(115,103,240,0.5); }
}
.set-hero__title { font-size: 1.5rem; font-weight: 800; margin: 0 0 4px; }
.text-gradient-gold {
  background: linear-gradient(to right, #fff, #ffd700);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.set-hero__sub { margin: 0; font-size: 0.8rem; color: rgba(255,255,255,0.5); max-width: 500px; }
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
.set-hero__breadcrumb {
  border-radius: var(--das-radius-sm);
  padding: 0.6rem 1rem;
  display: flex; align-items: center;
  background: rgba(0,0,0,0.2) !important;
}

/* LAYOUT & SIDEBAR */
.set-layout { display: grid; grid-template-columns: 240px 1fr; gap: 1.25rem; align-items: start; }
.set-sidebar { position: sticky; top: 88px; }
.set-nav {
  background: var(--das-surface);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius);
  padding: 1rem;
  backdrop-filter: blur(8px);
}
.set-nav__label {
  font-size: 0.58rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 1.2px;
  color: #475569; margin-bottom: 0.65rem;
  padding: 0 0.5rem;
}
.set-nav__item {
  width: 100%;
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.7rem 0.85rem;
  border: 1px solid transparent; border-radius: var(--das-radius-sm);
  background: transparent; cursor: pointer;
  font-size: 0.8rem; font-weight: 600; color: #64748b;
  transition: all 0.2s ease; text-align: left; margin-bottom: 3px;
  position: relative; text-decoration: none;
}
.set-nav__item:hover { color: #e2e8f0; background: var(--das-surface-hover); }
.set-nav__item.active {
  background: var(--das-primary-soft);
  border-color: rgba(115,103,240,0.35);
  color: #fff;
}
.set-nav__item-icon {
  width: 30px; height: 30px; border-radius: 5px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; flex-shrink: 0;
  background: rgba(255,255,255,0.04);
}
.set-nav__item.active .set-nav__item-icon { background: var(--das-primary-soft); color: var(--das-primary); }
.set-nav__item-arrow { margin-left: auto; font-size: 0.75rem; color: #334155; }
.set-nav__save-wrap { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--das-border); }

/* PANELS & FORMS */
.set-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
.set-panel__head { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--das-border); background: linear-gradient(90deg, rgba(115,103,240,0.06) 0%, transparent 60%); }
.set-panel__title-wrap { display: flex; align-items: center; gap: 1rem; }
.set-panel__icon { width: 44px; height: 44px; border-radius: var(--das-radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
.set-panel__icon.--primary { background: var(--das-primary-soft); color: var(--das-primary); }
.set-panel__title { font-size: 1rem; font-weight: 700; color: #e2e8f0; margin: 0 0 2px; }
.set-panel__sub { font-size: 0.72rem; color: #64748b; margin: 0; }
.set-panel__body { padding: 1.5rem; }

.set-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.15rem; }
.set-field--full { grid-column: 1 / -1; }
.set-label { display: block; font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; margin-bottom: 0.45rem; }
.set-input-group {
  display: flex; align-items: center;
  background: rgba(15,23,42,0.5);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius-sm);
  overflow: hidden;
  transition: all 0.2s;
}
.set-input-group:focus-within { border-color: var(--das-primary); box-shadow: 0 0 0 3px rgba(115,103,240,0.12); }
.set-input-prefix { padding: 0 0.75rem; font-size: 1rem; color: #475569; }
.set-input { flex: 1; padding: 0.6rem 0.5rem 0.6rem 0; background: transparent; border: none; color: #e2e8f0; font-size: 0.85rem; outline: none; }
select.set-input { padding-right: 0.5rem; }

/* SAVE BUTTON */
.set-save-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
  background: var(--das-primary); border: none; border-radius: var(--das-radius-sm);
  color: white; font-size: 0.82rem; font-weight: 700; padding: 0.7rem 1.25rem; cursor: pointer; transition: all 0.2s;
}
.set-save-btn:hover { background: #6259e8; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(115,103,240,0.3); }

/* TOAST */
.set-toast { display: flex; align-items: center; gap: 0.75rem; background: rgba(40,199,111,0.12); border: 1px solid rgba(40,199,111,0.25); border-radius: var(--das-radius-sm); padding: 0.85rem 1.1rem; }
.set-toast--success .set-toast__icon { color: var(--das-success); font-size: 1.2rem; }
.set-toast--danger { background: rgba(234,84,85,0.12); border-color: rgba(234,84,85,0.25); }
.set-toast--danger .set-toast__icon { color: var(--das-danger); font-size: 1.2rem; }
.set-toast__icon { color: var(--das-success); font-size: 1.2rem; }
.set-toast__msg { flex: 1; font-size: 0.85rem; color: #d1fae5; }
.set-toast--danger .set-toast__msg { color: #fecaca; }
.set-toast__close { background: transparent; border: none; color: #888; cursor: pointer; }

/* SYNC MODE CARDS */
.sync-mode-card {
  flex: 1; display: flex; align-items: flex-start; gap: 0.75rem;
  padding: 0.85rem 1rem; border-radius: var(--das-radius-sm);
  border: 1.5px solid var(--das-border);
  background: rgba(15,23,42,0.4);
  cursor: pointer; transition: all 0.2s;
}
.sync-mode-card:hover { border-color: rgba(115,103,240,0.4); background: rgba(115,103,240,0.06); }
.sync-mode-card.active { border-color: rgba(115,103,240,0.6); background: rgba(115,103,240,0.1); }
.sync-mode-card__icon { font-size: 1.4rem; color: #64748b; flex-shrink: 0; margin-top: 2px; }
.sync-mode-card.active .sync-mode-card__icon { color: var(--das-primary); }
.sync-mode-card__title { font-size: 0.82rem; font-weight: 700; color: #94a3b8; }
.sync-mode-card.active .sync-mode-card__title { color: #e2e8f0; }
.sync-mode-card__sub { font-size: 0.7rem; color: #475569; margin-top: 2px; }

/* SYNC NOW PANEL */
.sync-now-panel {
  background: var(--das-surface);
  border: 1px solid rgba(255,159,67,0.25);
  border-radius: var(--das-radius);
  overflow: hidden;
}
.sync-now-panel__inner {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1.25rem 1.5rem; gap: 1.5rem; flex-wrap: wrap;
  background: linear-gradient(90deg, rgba(255,159,67,0.06) 0%, transparent 60%);
}
.sync-now-panel__info { display: flex; align-items: center; gap: 1rem; }
.sync-now-panel__icon {
  width: 44px; height: 44px; border-radius: var(--das-radius-sm);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem; flex-shrink: 0;
  background: rgba(255,159,67,0.15); color: var(--das-warning);
}
.sync-now-panel__title { font-size: 0.95rem; font-weight: 700; color: #e2e8f0; margin-bottom: 2px; }
.sync-now-panel__sub { font-size: 0.72rem; color: #64748b; max-width: 480px; }
.sync-now-btn {
  display: inline-flex; align-items: center; gap: 0.5rem;
  background: var(--das-warning); border: none;
  border-radius: var(--das-radius-sm);
  color: #1a1a1a; font-size: 0.82rem; font-weight: 700;
  padding: 0.7rem 1.4rem; cursor: pointer; transition: all 0.2s;
  white-space: nowrap;
}
.sync-now-btn:hover { background: #f0920a; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,159,67,0.35); }

.sync-confirm-modal {
  position: fixed;
  inset: 0;
  display: grid;
  place-items: center;
  padding: 1.5rem;
  z-index: 1100;
  pointer-events: none;
}

.sync-confirm-modal[hidden] {
  display: none;
}

.sync-confirm-modal__backdrop {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.8);
  backdrop-filter: blur(4px);
}

.sync-confirm-modal__content {
  position: relative;
  z-index: 1;
  width: min(520px, 100%);
  background: var(--das-surface);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius);
  padding: 1.5rem;
  box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
}

.sync-confirm-modal__header { display: grid; gap: 0.8rem; }
.sync-confirm-modal__icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #f6c90e;
  background: rgba(255, 159, 67, 0.15);
  font-size: 1.35rem;
}
.sync-confirm-modal__header h2 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: #e2e8f0;
}
.sync-confirm-modal__header p {
  margin: 0;
  color: #94a3b8;
  font-size: 0.88rem;
  line-height: 1.6;
}
.sync-confirm-modal__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.4rem;
  flex-wrap: wrap;
}
.sync-confirm-modal__cancel-btn,
.sync-confirm-modal__confirm-btn {
  min-width: 120px;
  border-radius: var(--das-radius-sm);
  border: none;
  padding: 0.75rem 1rem;
  font-weight: 700;
  cursor: pointer;
}
.sync-confirm-modal__cancel-btn {
  background: rgba(255,255,255,0.08);
  color: #cbd5e1;
}
.sync-confirm-modal__cancel-btn:hover { background: rgba(255,255,255,0.14); }
.sync-confirm-modal__confirm-btn {
  background: var(--das-warning);
  color: #1a1a1a;
}
.sync-confirm-modal__confirm-btn:hover { background: #f0920a; }

/* SUCCESS MODAL SPECIFIC */
.sync-confirm-modal__content--success {
  border-color: rgba(40, 199, 111, 0.3);
  background: linear-gradient(180deg, var(--das-surface) 0%, rgba(40, 199, 111, 0.05) 100%);
}
.sync-success-icon-wrap {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}
.sync-success-icon-ring {
  position: absolute;
  width: 70px;
  height: 70px;
  border-radius: 50%;
  border: 2px solid rgba(40, 199, 111, 0.2);
  animation: ringPulse 2s infinite;
}
@keyframes ringPulse {
  0% { transform: scale(1); opacity: 1; }
  100% { transform: scale(1.5); opacity: 0; }
}
.sync-success-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--das-success);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  box-shadow: 0 0 20px rgba(40, 199, 111, 0.4);
}
.text-gradient-success {
  background: linear-gradient(to right, #fff, #28c76f);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.sync-confirm-modal__confirm-btn--success {
  background: var(--das-success);
  color: #fff;
  width: 100%;
}
.sync-confirm-modal__confirm-btn--success:hover {
  background: #23ad60;
  box-shadow: 0 8px 20px rgba(40, 199, 111, 0.3);
}

@media (max-width: 991px) {
  .set-layout { grid-template-columns: 1fr; }
  .set-sidebar { position: static; margin-bottom: 1rem; }
  .set-nav { display: flex; overflow-x: auto; padding: 0.65rem; }
  .set-nav__label, .set-nav__save-wrap { display: none; }
  .set-nav__item { flex-direction: column; text-align: center; font-size: 0.65rem; min-width: 120px; }
  .sync-mode-card { flex-direction: column; }
  .sync-now-panel__inner { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 767px) {
  .set-form-grid { grid-template-columns: 1fr; }
  .d-flex.gap-3 { flex-direction: column; }
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
  // Check if toast container exists, if not create one or use existing
  // We can use the existing toast structures if they are present in the DOM
  // Or create a simple one. Let's try to find an existing one first.
  
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
    // Fallback alert if toast not found in DOM
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

<style>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin {
  animation: spin 1s linear infinite;
  display: inline-block;
}
</style>

@endsection
