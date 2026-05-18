@extends('layouts/layoutMaster')

@section('title', 'Pengaturan Google Sheets')

@section('content')

<div class="set-hero mb-5">
  <div class="set-hero__bg"></div>
  <div class="set-hero__glass"></div>
  <div class="set-hero__grid"></div>
  <div class="set-hero__inner">
    <div class="set-hero__identity">
      <div class="set-hero__icon-wrap">
        <i class="ti tabler-file-spreadsheet"></i>
        <div class="set-hero__icon-glow"></div>
      </div>
      <div>
        <div class="set-hero__badge">
          <span class="pulse-dot"></span>
          Integrasi Data
        </div>
        <h4 class="set-hero__title text-gradient-gold">Pengaturan Google Sheets</h4>
        <p class="set-hero__sub">Konfigurasi koneksi Google Sheets untuk sinkronisasi data siswa dan pengguna.</p>
      </div>
    </div>
    <div class="set-hero__breadcrumb glass-card">
      <span class="text-muted small"><i class="ti tabler-home me-1"></i>Dashboard</span>
      <i class="ti tabler-chevron-right text-muted mx-1" style="font-size:0.7rem;"></i>
      <span class="small text-white fw-semibold">Google Sheets</span>
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
  <div class="set-toast set-toast--success mb-4" id="gsSyncSuccessToast">
    <div class="set-toast__icon"><i class="ti tabler-refresh-dot"></i></div>
    <div class="set-toast__msg">{{ session('sync_success') }}</div>
    <button type="button" class="set-toast__close" onclick="document.getElementById('gsSyncSuccessToast').style.display='none'"><i class="ti tabler-x"></i></button>
  </div>
@endif

@if (session('sync_error'))
  <div class="set-toast set-toast--danger mb-4" id="gsSyncErrorToast">
    <div class="set-toast__icon"><i class="ti tabler-alert-triangle"></i></div>
    <div class="set-toast__msg">{{ session('sync_error') }}</div>
    <button type="button" class="set-toast__close" onclick="document.getElementById('gsSyncErrorToast').style.display='none'"><i class="ti tabler-x"></i></button>
  </div>
@endif

{{-- ─────────────────────────────
     PETUNJUK PENGGUNAAN
───────────────────────────── --}}
<div class="set-panel mb-4">
  <div class="set-panel__head">
    <div class="set-panel__title-wrap">
      <div class="set-panel__icon --info"><i class="ti tabler-books"></i></div>
      <div>
        <div class="set-panel__title">Petunjuk Penggunaan</div>
        <div class="set-panel__sub">Ikuti langkah-langkah berikut untuk menghubungkan Google Sheets dengan sistem.</div>
      </div>
    </div>
  </div>
  <div class="set-panel__body">
    <div style="display:flex;flex-direction:column;gap:1rem;">
      <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 1rem;background:rgba(115,103,240,0.06);border:1px solid rgba(115,103,240,0.15);border-radius:6px;">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--das-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:800;flex-shrink:0;">1</div>
        <div>
          <div style="font-size:0.82rem;font-weight:700;color:#e2e8f0;margin-bottom:2px;">Buat Service Account di Google Cloud Console</div>
          <div style="font-size:0.72rem;color:#94a3b8;line-height:1.5;">
            Buka <a href="https://console.cloud.google.com" target="_blank" style="color:var(--das-info);">Google Cloud Console</a>, buat project baru, lalu buka <strong>IAM & Admin &gt; Service Accounts</strong>. Klik <strong>Buat Service Account</strong>, beri nama, lalu klik <strong>Selesai</strong>.
          </div>
        </div>
      </div>
      <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 1rem;background:rgba(115,103,240,0.06);border:1px solid rgba(115,103,240,0.15);border-radius:6px;">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--das-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:800;flex-shrink:0;">2</div>
        <div>
          <div style="font-size:0.82rem;font-weight:700;color:#e2e8f0;margin-bottom:2px;">Generate & Download Kunci JSON</div>
          <div style="font-size:0.72rem;color:#94a3b8;line-height:1.5;">
            Klik Service Account yang baru dibuat, buka tab <strong>Keys</strong>, klik <strong>Add Key &gt; Create New Key</strong>, pilih format <strong>JSON</strong>, lalu klik <strong>Create</strong>. File akan terunduh secara otomatis — simpan baik-baik.
          </div>
        </div>
      </div>
      <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 1rem;background:rgba(115,103,240,0.06);border:1px solid rgba(115,103,240,0.15);border-radius:6px;">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--das-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:800;flex-shrink:0;">3</div>
        <div>
          <div style="font-size:0.82rem;font-weight:700;color:#e2e8f0;margin-bottom:2px;">Bagikan Spreadsheet ke Service Account</div>
          <div style="font-size:0.72rem;color:#94a3b8;line-height:1.5;">
            Buka Google Sheets Anda, klik tombol <strong>Bagikan</strong> di pojok kanan atas. Masukkan alamat email Service Account (ada di file JSON, field <code>client_email</code>). Pilih akses <strong>Editor</strong> (atau minimal <strong>Viewer</strong> jika hanya baca). Klik <strong>Kirim</strong>.
          </div>
        </div>
      </div>
      <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 1rem;background:rgba(115,103,240,0.06);border:1px solid rgba(115,103,240,0.15);border-radius:6px;">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--das-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:800;flex-shrink:0;">4</div>
        <div>
          <div style="font-size:0.82rem;font-weight:700;color:#e2e8f0;margin-bottom:2px;">Isi Konfigurasi di Bawah & Simpan</div>
          <div style="font-size:0.72rem;color:#94a3b8;line-height:1.5;">
            Masukkan <strong>ID Spreadsheet</strong> (ambil dari URL: <code>https://docs.google.com/spreadsheets/d/</code><strong>ID</strong><code>/edit</code>), atur <strong>Range Sheet</strong>, tempel isi file JSON ke kolom <strong>Service Account JSON</strong>, lalu atur <strong>Mapping Kolom</strong> sesuai header sheet Anda. Klik <strong>Simpan</strong>.
          </div>
        </div>
      </div>
      <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 1rem;background:rgba(40,199,111,0.06);border:1px solid rgba(40,199,111,0.15);border-radius:6px;">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--das-success);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:800;flex-shrink:0;">5</div>
        <div>
          <div style="font-size:0.82rem;font-weight:700;color:#e2e8f0;margin-bottom:2px;">Test Koneksi & Sinkronisasi</div>
          <div style="font-size:0.72rem;color:#94a3b8;line-height:1.5;">
            Gunakan tombol <strong>Test Koneksi</strong> untuk memastikan semuanya berfungsi. Jika berhasil, klik <strong>Sinkron Sekarang</strong> untuk menarik data siswa dari Google Sheets. Proses sinkron berjalan di latar belakang — status bisa dicek di panel <strong>Status Sinkronisasi</strong> setelah halaman di-reload.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<form action="{{ route('admin.pengaturan.google-sheets.update') }}" method="POST">
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
          <a href="{{ route('admin.pengaturan.api-source.index') }}" class="set-nav__item">
            <div class="set-nav__item-icon">
              <i class="ti tabler-api"></i>
            </div>
            <span>Pengaturan API</span>
            <i class="ti tabler-chevron-right set-nav__item-arrow"></i>
          </a>
          <button type="button" class="set-nav__item active">
            <div class="set-nav__item-icon">
              <i class="ti tabler-file-spreadsheet"></i>
            </div>
            <span>Google Sheets</span>
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

      {{-- ─────────────────────────────
           PANEL 1: Konfigurasi Google Sheets
      ───────────────────────────── --}}
      <div class="set-panel mb-4">
        <div class="set-panel__head">
          <div class="set-panel__title-wrap">
            <div class="set-panel__icon --primary"><i class="ti tabler-file-spreadsheet"></i></div>
            <div>
              <div class="set-panel__title">Konfigurasi Google Sheets</div>
              <div class="set-panel__sub">Atur koneksi spreadsheet Google Sheets untuk sinkronisasi data siswa dan pengguna.</div>
            </div>
          </div>
        </div>
        <div class="set-panel__body">
          <div class="set-form-grid">

            <div class="set-field set-field--full">
              <label class="set-label" for="spreadsheet_id">ID Spreadsheet</label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-brand-google"></i></span>
                <input type="text" class="set-input font-monospace {{ $errors->has('spreadsheet_id') ? 'is-invalid' : '' }}"
                  id="spreadsheet_id"
                  name="spreadsheet_id"
                  value="{{ old('spreadsheet_id', $setting->spreadsheet_id ?? '') }}"
                  placeholder="1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms"
                  aria-describedby="spreadsheet_id_help">
              </div>
              <div id="spreadsheet_id_help" class="text-muted small mt-1">ID spreadsheet dari URL Google Sheets (contoh: 1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms)</div>
              @error('spreadsheet_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <div class="set-field set-field--full">
              <label class="set-label" for="sheet_range">Range Sheet</label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-table"></i></span>
                <input type="text" class="set-input {{ $errors->has('sheet_range') ? 'is-invalid' : '' }}"
                  id="sheet_range"
                  name="sheet_range"
                  value="{{ old('sheet_range', $setting->sheet_range ?? 'Sheet1!A:Z') }}"
                  aria-describedby="sheet_range_help">
              </div>
              <div id="sheet_range_help" class="text-muted small mt-1">Range data di sheet (contoh: Sheet1!A:Z)</div>
              @error('sheet_range')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <div class="set-field set-field--full">
              <label class="set-label" for="credentials_json">Service Account JSON</label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
                <textarea class="set-input font-monospace {{ $errors->has('credentials_json') ? 'is-invalid' : '' }}"
                  id="credentials_json"
                  name="credentials_json"
                  rows="8"
                  aria-describedby="credentials_json_help"
                  placeholder="{{ $setting->id ? 'Kosongkan jika tidak ingin mengubah credentials yang sudah tersimpan' : '' }}">{{ old('credentials_json', $setting->id ? '' : '') }}</textarea>
              </div>
              <div id="credentials_json_help" class="text-muted small mt-1">Upload file JSON Service Account dari Google Cloud Console. Kosongkan jika tidak ingin mengubah.</div>
              @error('credentials_json')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <div class="set-field set-field--full">
              <label class="set-label" for="column_mapping">Mapping Kolom (JSON)</label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-columns"></i></span>
                <textarea class="set-input font-monospace {{ $errors->has('column_mapping') ? 'is-invalid' : '' }}"
                  id="column_mapping"
                  name="column_mapping"
                  rows="4"
                  aria-describedby="column_mapping_help">{{ old('column_mapping', json_encode($setting->column_mapping ?? [], JSON_PRETTY_PRINT)) }}</textarea>
              </div>
              <div id="column_mapping_help" class="text-muted small mt-1">Mapping kolom Google Sheets ke field database. Format: {"nis":"NIS","nama_lengkap":"Nama Lengkap","nisn":"NISN","jenis_kelamin":"Jenis Kelamin","tempat_lahir":"Tempat Lahir","tanggal_lahir":"Tanggal Lahir","alamat":"Alamat","no_hp":"No HP"}</div>
              @error('column_mapping')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

          </div>
        </div>
      </div>

      {{-- ─────────────────────────────
           PANEL 2: Status Sinkronisasi
      ───────────────────────────── --}}
      @if($setting->id)
      <div class="set-panel mb-4">
        <div class="set-panel__head">
          <div class="set-panel__title-wrap">
            <div class="set-panel__icon --info"><i class="ti tabler-refresh"></i></div>
            <div>
              <div class="set-panel__title">Status Sinkronisasi</div>
              <div class="set-panel__sub">Informasi terakhir proses sinkronisasi data dari Google Sheets.</div>
            </div>
          </div>
        </div>
        <div class="set-panel__body">
          <div class="set-form-grid">
            <div class="set-field">
              <label class="set-label">Terakhir Sinkron</label>
              <div class="d-flex align-items-center gap-2 mt-1">
                <i class="ti tabler-calendar-time text-muted"></i>
                <span class="text-white small">{{ $setting->last_sync_at ? \Carbon\Carbon::parse($setting->last_sync_at)->format('d M Y H:i:s') : '-' }}</span>
              </div>
            </div>
            <div class="set-field">
              <label class="set-label">Status</label>
              <div class="mt-1">
                @php
                  $badgeClass = match($setting->last_sync_status) {
                    'success' => 'bg-label-success',
                    'completed_with_errors' => 'bg-label-warning',
                    'failed' => 'bg-label-danger',
                    'in_progress' => 'bg-label-warning',
                    default => 'bg-label-secondary',
                  };
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $setting->status_badge_text }}</span>
              </div>
            </div>
            @if($setting->last_sync_message)
            <div class="set-field set-field--full">
              <label class="set-label">Pesan</label>
              <p class="text-muted small mt-1 mb-0">{{ $setting->last_sync_message }}</p>
            </div>
            @endif
          </div>
        </div>
      </div>
      @endif

      <div class="set-footer-save d-lg-none">
        <button type="submit" class="set-save-btn">
          <i class="ti tabler-device-floppy"></i>
          <span>Simpan Pengaturan</span>
        </button>
      </div>
    </main>
  </div>
</form>

{{-- ─────────────────────────────
     SYNC NOW PANEL
───────────────────────────── --}}
<div class="sync-now-panel mt-4">
  <div class="sync-now-panel__inner">
    <div class="sync-now-panel__info">
      <div class="sync-now-panel__icon"><i class="ti tabler-refresh"></i></div>
      <div>
        <div class="sync-now-panel__title">Sinkronisasi Sekarang</div>
          <div class="sync-now-panel__sub">Jalankan sinkronisasi data dari Google Sheets. Proses berjalan di latar belakang dan akan tetap berjalan meskipun halaman ditutup. Data besar akan diproses secara bertahap.</div>
      </div>
    </div>
    <form action="{{ route('admin.pengaturan.google-sheets.sync-now') }}" method="POST" id="gsSyncNowForm">
      @csrf
      <button type="button" class="sync-now-btn" id="gsOpenSyncConfirmButton" onclick="openGsSyncConfirmModal()">
        <i class="ti tabler-refresh"></i>
        <span>Sinkron Sekarang</span>
      </button>
    </form>
  </div>
</div>

{{-- ─────────────────────────────
     TEST CONNECTION PANEL
───────────────────────────── --}}
<div class="sync-now-panel mt-4" style="border-color: rgba(0, 207, 232, 0.25);">
  <div class="sync-now-panel__inner" style="background: linear-gradient(90deg, rgba(0, 207, 232, 0.06) 0%, transparent 60%);">
    <div class="sync-now-panel__info">
      <div class="sync-now-panel__icon" style="background: rgba(0, 207, 232, 0.15); color: var(--das-info);"><i class="ti tabler-plug-connected"></i></div>
      <div>
        <div class="sync-now-panel__title">Test Koneksi</div>
        <div class="sync-now-panel__sub">Uji koneksi ke Google Sheets untuk memastikan konfigurasi sudah benar sebelum menyimpan.</div>
      </div>
    </div>
    <button type="button" class="sync-now-btn" style="background: var(--das-info); color: #fff;" id="gsTestBtn" onclick="openGsTestModal()">
      <i class="ti tabler-plug-connected"></i>
      <span>Test Koneksi</span>
    </button>
  </div>
</div>

{{-- ── MODAL KONFIRMASI SINKRON ── --}}
<div id="gsSyncConfirmModal" class="sync-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="gsSyncConfirmTitle" aria-describedby="gsSyncConfirmDescription" hidden>
  <div class="sync-confirm-modal__backdrop" onclick="closeGsSyncConfirmModal()" tabindex="-1"></div>
  <div class="sync-confirm-modal__content" role="document">
    <div class="sync-confirm-modal__header">
      <div class="sync-confirm-modal__icon"><i class="ti tabler-alert-circle"></i></div>
      <div>
        <h2 id="gsSyncConfirmTitle">Konfirmasi Sinkronisasi</h2>
        <p id="gsSyncConfirmDescription">Apakah Anda yakin ingin menjalankan sinkronisasi data dari Google Sheets sekarang? Proses ini dapat memakan beberapa saat dan akan menarik data dari spreadsheet.</p>
      </div>
    </div>
    <div class="sync-confirm-modal__actions">
      <button type="button" class="sync-confirm-modal__cancel-btn" onclick="closeGsSyncConfirmModal()">Batal</button>
      <button type="button" class="sync-confirm-modal__confirm-btn" id="gsSyncConfirmButton" onclick="submitGsSyncNow()">
        <span id="gsSyncBtnText">Konfirmasi</span>
        <span id="gsSyncBtnSpinner" style="display:none;"><i class="ti tabler-loader-2 animate-spin"></i></span>
      </button>
    </div>
  </div>
</div>

{{-- ── MODAL SUKSES SINKRON ── --}}
<div id="gsSyncSuccessModal" class="sync-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="gsSyncSuccessTitle" aria-describedby="gsSyncSuccessDescription" hidden>
  <div class="sync-confirm-modal__backdrop" onclick="closeGsSyncSuccessModal()" tabindex="-1"></div>
  <div class="sync-confirm-modal__content sync-confirm-modal__content--success" role="document">
    <div class="sync-confirm-modal__header text-center">
      <div class="sync-success-icon-wrap mb-3">
        <div class="sync-success-icon-ring"></div>
        <div class="sync-success-icon"><i class="ti tabler-circle-check"></i></div>
      </div>
      <h2 id="gsSyncSuccessTitle" class="text-gradient-success">Berhasil Dijadwalkan!</h2>
        <p id="gsSyncSuccessDescription" class="mt-2">Sinkronisasi Google Sheets telah dijadwalkan dan akan diproses di latar belakang secara bertahap (±50 baris per tahap). Anda dapat menutup halaman ini atau melanjutkan aktivitas lainnya. Status sinkronisasi dapat dicek kembali di halaman ini.</p>
    </div>
    <div class="sync-confirm-modal__actions justify-content-center">
      <button type="button" class="sync-confirm-modal__confirm-btn sync-confirm-modal__confirm-btn--success" onclick="closeGsSyncSuccessModal()">
        Selesai
      </button>
    </div>
  </div>
</div>

{{-- ── MODAL TEST KONEKSI ── --}}
<div id="gsTestModal" class="sync-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="gsTestTitle" aria-describedby="gsTestDescription" hidden>
  <div class="sync-confirm-modal__backdrop" onclick="closeGsTestModal()" tabindex="-1"></div>
  <div class="sync-confirm-modal__content" role="document" id="gsTestModalContent">
    <div class="sync-confirm-modal__header text-center">
      <div class="mb-3" id="gsTestIconWrap">
        <div id="gsTestLoading" style="display:none;">
          <div style="width:56px;height:56px;margin:0 auto;border-radius:50%;background:rgba(0,207,232,0.12);display:flex;align-items:center;justify-content:center;color:var(--das-info);font-size:2rem;">
            <i class="ti tabler-loader-2 animate-spin"></i>
          </div>
        </div>
        <div id="gsTestIconSuccess" style="display:none;">
          <div class="sync-success-icon-wrap">
            <div class="sync-success-icon-ring"></div>
            <div class="sync-success-icon" style="background:var(--das-success);"><i class="ti tabler-circle-check"></i></div>
          </div>
        </div>
        <div id="gsTestIconError" style="display:none;">
          <div style="width:56px;height:56px;margin:0 auto;border-radius:50%;background:rgba(234,84,85,0.12);display:flex;align-items:center;justify-content:center;color:var(--das-danger);font-size:2rem;">
            <i class="ti tabler-alert-circle"></i>
          </div>
        </div>
      </div>
      <h2 id="gsTestTitle" style="font-size:1.05rem;font-weight:700;color:#e2e8f0;margin:0;">Test Koneksi</h2>
      <p id="gsTestDescription" class="mt-2" style="color:#94a3b8;font-size:0.88rem;line-height:1.6;margin:0;">Menguji koneksi ke Google Sheets...</p>
    </div>
    <div class="sync-confirm-modal__actions justify-content-center" id="gsTestActions" style="display:none;">
      <button type="button" class="sync-confirm-modal__cancel-btn" onclick="closeGsTestModal()">Tutup</button>
    </div>
  </div>
</div>

<style>
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

.set-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
.set-panel__head { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--das-border); background: linear-gradient(90deg, rgba(115,103,240,0.06) 0%, transparent 60%); }
.set-panel__title-wrap { display: flex; align-items: center; gap: 1rem; }
.set-panel__icon { width: 44px; height: 44px; border-radius: var(--das-radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
.set-panel__icon.--primary { background: var(--das-primary-soft); color: var(--das-primary); }
.set-panel__icon.--info { background: var(--das-info-soft); color: var(--das-info); }
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
.set-input-prefix { padding: 0 0.75rem; font-size: 1rem; color: #475569; flex-shrink: 0; }
.set-input { flex: 1; padding: 0.6rem 0.5rem 0.6rem 0; background: transparent; border: none; color: #e2e8f0; font-size: 0.85rem; outline: none; }
textarea.set-input { resize: vertical; padding: 0.6rem 0.5rem; }

.set-save-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
  background: var(--das-primary); border: none; border-radius: var(--das-radius-sm);
  color: white; font-size: 0.82rem; font-weight: 700; padding: 0.7rem 1.25rem; cursor: pointer; transition: all 0.2s;
}
.set-save-btn:hover { background: #6259e8; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(115,103,240,0.3); }

.set-toast { display: flex; align-items: center; gap: 0.75rem; background: rgba(40,199,111,0.12); border: 1px solid rgba(40,199,111,0.25); border-radius: var(--das-radius-sm); padding: 0.85rem 1.1rem; }
.set-toast--success .set-toast__icon { color: var(--das-success); font-size: 1.2rem; }
.set-toast--danger { background: rgba(234,84,85,0.12); border-color: rgba(234,84,85,0.25); }
.set-toast--danger .set-toast__icon { color: var(--das-danger); font-size: 1.2rem; }
.set-toast__icon { color: var(--das-success); font-size: 1.2rem; }
.set-toast__msg { flex: 1; font-size: 0.85rem; color: #d1fae5; }
.set-toast--danger .set-toast__msg { color: #fecaca; }
.set-toast__close { background: transparent; border: none; color: #888; cursor: pointer; }

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
.sync-confirm-modal[hidden] { display: none; }
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
  width: 44px; height: 44px; border-radius: 12px;
  display: inline-flex; align-items: center; justify-content: center;
  color: #f6c90e;
  background: rgba(255, 159, 67, 0.15);
  font-size: 1.35rem;
}
.sync-confirm-modal__header h2 {
  margin: 0; font-size: 1.05rem; font-weight: 700; color: #e2e8f0;
}
.sync-confirm-modal__header p {
  margin: 0; color: #94a3b8; font-size: 0.88rem; line-height: 1.6;
}
.sync-confirm-modal__actions {
  display: flex; justify-content: flex-end; gap: 0.75rem;
  margin-top: 1.4rem; flex-wrap: wrap;
}
.sync-confirm-modal__cancel-btn,
.sync-confirm-modal__confirm-btn {
  min-width: 120px; border-radius: var(--das-radius-sm);
  border: none; padding: 0.75rem 1rem; font-weight: 700; cursor: pointer;
}
.sync-confirm-modal__cancel-btn { background: rgba(255,255,255,0.08); color: #cbd5e1; }
.sync-confirm-modal__cancel-btn:hover { background: rgba(255,255,255,0.14); }
.sync-confirm-modal__confirm-btn { background: var(--das-warning); color: #1a1a1a; }
.sync-confirm-modal__confirm-btn:hover { background: #f0920a; }

.sync-confirm-modal__content--success {
  border-color: rgba(40, 199, 111, 0.3);
  background: linear-gradient(180deg, var(--das-surface) 0%, rgba(40, 199, 111, 0.05) 100%);
}
.sync-success-icon-wrap {
  position: relative; display: flex; align-items: center; justify-content: center; margin: 0 auto;
}
.sync-success-icon-ring {
  position: absolute; width: 70px; height: 70px;
  border-radius: 50%; border: 2px solid rgba(40, 199, 111, 0.2);
  animation: ringPulse 2s infinite;
}
@keyframes ringPulse {
  0% { transform: scale(1); opacity: 1; }
  100% { transform: scale(1.5); opacity: 0; }
}
.sync-success-icon {
  width: 56px; height: 56px; border-radius: 50%;
  background: var(--das-success); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem; box-shadow: 0 0 20px rgba(40, 199, 111, 0.4);
}
.text-gradient-success {
  background: linear-gradient(to right, #fff, #28c76f);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.sync-confirm-modal__confirm-btn--success {
  background: var(--das-success); color: #fff; width: 100%;
}
.sync-confirm-modal__confirm-btn--success:hover {
  background: #23ad60; box-shadow: 0 8px 20px rgba(40, 199, 111, 0.3);
}

@media (max-width: 991px) {
  .set-layout { grid-template-columns: 1fr; }
  .set-sidebar { position: static; margin-bottom: 1rem; }
  .set-nav { display: flex; overflow-x: auto; padding: 0.65rem; }
  .set-nav__label, .set-nav__save-wrap { display: none; }
  .set-nav__item { flex-direction: column; text-align: center; font-size: 0.65rem; min-width: 120px; }
  .sync-now-panel__inner { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 767px) {
  .set-form-grid { grid-template-columns: 1fr; }
}
</style>

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

<script>
function openGsSyncConfirmModal() {
  const modal = document.getElementById('gsSyncConfirmModal');
  const confirmButton = document.getElementById('gsSyncConfirmButton');
  if (!modal || !confirmButton) {
    return true;
  }
  modal.hidden = false;
  modal.style.pointerEvents = 'auto';
  setTimeout(() => confirmButton.focus(), 50);
}

function closeGsSyncConfirmModal() {
  const modal = document.getElementById('gsSyncConfirmModal');
  const openButton = document.getElementById('gsOpenSyncConfirmButton');
  if (!modal) return;
  modal.hidden = true;
  modal.style.pointerEvents = 'none';
  if (openButton) openButton.focus();
}

function openGsSyncSuccessModal() {
  const modal = document.getElementById('gsSyncSuccessModal');
  if (modal) {
    modal.hidden = false;
    modal.style.pointerEvents = 'auto';
  }
}

function closeGsSyncSuccessModal() {
  const modal = document.getElementById('gsSyncSuccessModal');
  if (modal) {
    modal.hidden = true;
    modal.style.pointerEvents = 'none';
  }
}

async function submitGsSyncNow() {
  const form = document.getElementById('gsSyncNowForm');
  const btn = document.getElementById('gsSyncConfirmButton');
  const btnText = document.getElementById('gsSyncBtnText');
  const btnSpinner = document.getElementById('gsSyncBtnSpinner');
  const url = form.action;

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
      closeGsSyncConfirmModal();
      setTimeout(() => {
        openGsSyncSuccessModal();
      }, 300);
    } else {
      showGsDynamicToast('danger', result.message || 'Terjadi kesalahan saat sinkronisasi.');
    }
  } catch (error) {
    console.error('Sync error:', error);
    showGsDynamicToast('danger', 'Gagal menghubungi server. Silakan coba lagi.');
  } finally {
    btn.disabled = false;
    btnText.style.display = 'inline-block';
    btnSpinner.style.display = 'none';
  }
}

function showGsDynamicToast(type, message) {
  const toastId = type === 'success' ? 'gsSyncSuccessToast' : 'gsSyncErrorToast';
  let toast = document.getElementById(toastId);

  if (toast) {
    const msgEl = toast.querySelector('.set-toast__msg');
    if (msgEl) msgEl.textContent = message;
    toast.style.display = 'flex';
    setTimeout(() => {
      toast.style.display = 'none';
    }, 5000);
  } else {
    alert(message);
  }
}

function openGsTestModal() {
  const modal = document.getElementById('gsTestModal');
  if (!modal) return;

  document.getElementById('gsTestLoading').style.display = 'block';
  document.getElementById('gsTestIconSuccess').style.display = 'none';
  document.getElementById('gsTestIconError').style.display = 'none';
  document.getElementById('gsTestActions').style.display = 'none';
  document.getElementById('gsTestTitle').textContent = 'Test Koneksi';
  document.getElementById('gsTestDescription').textContent = 'Menguji koneksi ke Google Sheets...';
  document.getElementById('gsTestModalContent').className = 'sync-confirm-modal__content';

  modal.hidden = false;
  modal.style.pointerEvents = 'auto';

  submitGsTest();
}

function closeGsTestModal() {
  const modal = document.getElementById('gsTestModal');
  if (modal) {
    modal.hidden = true;
    modal.style.pointerEvents = 'none';
  }
}

async function submitGsTest() {
  const spreadsheetId = document.getElementById('spreadsheet_id').value;
  const credentialsJson = document.getElementById('credentials_json').value;
  const sheetRange = document.getElementById('sheet_range').value;
  const hasSaved = {{ $setting->id ? 'true' : 'false' }};

  if (!spreadsheetId) {
    document.getElementById('gsTestLoading').style.display = 'none';
    document.getElementById('gsTestIconError').style.display = 'block';
    document.getElementById('gsTestTitle').textContent = 'Validasi Gagal';
    document.getElementById('gsTestDescription').textContent = 'Harap isi ID Spreadsheet terlebih dahulu.';
    document.getElementById('gsTestActions').style.display = 'flex';
    document.getElementById('gsTestModalContent').className = 'sync-confirm-modal__content';
    return;
  }

  if (!credentialsJson && !hasSaved) {
    document.getElementById('gsTestLoading').style.display = 'none';
    document.getElementById('gsTestIconError').style.display = 'block';
    document.getElementById('gsTestTitle').textContent = 'Validasi Gagal';
    document.getElementById('gsTestDescription').textContent = 'Harap isi Service Account JSON terlebih dahulu.';
    document.getElementById('gsTestActions').style.display = 'flex';
    document.getElementById('gsTestModalContent').className = 'sync-confirm-modal__content';
    return;
  }

  try {
    const response = await fetch('{{ route('admin.pengaturan.google-sheets.test') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        spreadsheet_id: spreadsheetId,
        credentials_json: credentialsJson,
        sheet_range: sheetRange || 'Sheet1!A1:Z1'
      })
    });

    const result = await response.json();

    document.getElementById('gsTestLoading').style.display = 'none';

    if (result.success) {
      document.getElementById('gsTestIconSuccess').style.display = 'block';
      document.getElementById('gsTestTitle').textContent = 'Koneksi Berhasil!';
      document.getElementById('gsTestDescription').textContent = result.message || 'Koneksi ke Google Sheets berhasil. Spreadsheet dapat diakses.';
      document.getElementById('gsTestModalContent').className = 'sync-confirm-modal__content sync-confirm-modal__content--success';
    } else {
      document.getElementById('gsTestIconError').style.display = 'block';
      document.getElementById('gsTestTitle').textContent = 'Koneksi Gagal';
      document.getElementById('gsTestDescription').textContent = result.message || 'Tidak dapat terhubung ke Google Sheets. Periksa kembali konfigurasi Anda.';
    }
  } catch (error) {
    document.getElementById('gsTestLoading').style.display = 'none';
    document.getElementById('gsTestIconError').style.display = 'block';
    document.getElementById('gsTestTitle').textContent = 'Koneksi Gagal';
    document.getElementById('gsTestDescription').textContent = 'Gagal menghubungi server. Silakan coba lagi.';
    console.error('Test connection error:', error);
  }

  document.getElementById('gsTestActions').style.display = 'flex';
}

window.addEventListener('keydown', function (event) {
  const confirmModal = document.getElementById('gsSyncConfirmModal');
  const successModal = document.getElementById('gsSyncSuccessModal');
  const testModal = document.getElementById('gsTestModal');

  if (event.key === 'Escape') {
    if (confirmModal && !confirmModal.hidden) closeGsSyncConfirmModal();
    if (successModal && !successModal.hidden) closeGsSyncSuccessModal();
    if (testModal && !testModal.hidden) closeGsTestModal();
  }
});
</script>

@endsection
