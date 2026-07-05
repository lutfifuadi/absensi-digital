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

{{-- Warning jika credentials rusak akibat perubahan APP_KEY --}}
@if($setting->id && empty($setting->credentials_json))
  <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
    <i class="ti tabler-alert-triangle fs-5 flex-shrink-0"></i>
    <span>
      <strong>Peringatan Keamanan:</strong> Kunci aplikasi (APP_KEY) sistem telah berubah. File <strong>Service Account JSON</strong> yang tersimpan sebelumnya tidak dapat didekripsi. Silakan unggah kembali file JSON Anda pada kolom konfigurasi di bawah untuk mengaktifkan kembali fitur sinkronisasi Google Sheets.
    </span>
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
          <button type="submit" form="gsUpdateForm" class="set-save-btn w-100">
            <i class="ti tabler-device-floppy"></i>
            <span>Simpan Pengaturan</span>
          </button>
        </div>
      </div>
    </aside>

    <main class="set-content">
      <div class="gs-grid-container">
        
        <!-- KOLOM KIRI (UTAMA): FORM KONFIGURASI -->
        <div class="gs-grid-left">
          <form action="{{ route('admin.pengaturan.google-sheets.update') }}" method="POST" id="gsUpdateForm">
            @csrf
            
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
                {{-- Action Bar: Download Template & Buat Google Sheet --}}
                <div class="d-flex flex-wrap gap-2 mb-4 pb-3" style="border-bottom: 1px solid var(--das-border);">
                  <a href="{{ route('admin.pengaturan.google-sheets.template.download') }}"
                     class="gs-action-btn gs-action-btn--info">
                    <i class="ti tabler-download"></i>
                    <span>Download Template Excel</span>
                  </a>
                  <button type="button"
                          class="gs-action-btn gs-action-btn--success"
                          id="gsCreateSheetBtn"
                          onclick="createGsSheetTemplate()">
                    <i class="ti tabler-file-plus"></i>
                    <span>Buat Google Sheet Template</span>
                  </button>
                </div>

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
          </form>
        </div>
        
        <!-- KOLOM KANAN (KONTROL): STATUS & AKSI CEPAT -->
        <div class="gs-grid-right">
          
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
                    <span class="text-white small" id="sync-last-time">{{ $setting->last_sync_at ? \Carbon\Carbon::parse($setting->last_sync_at)->format('d M Y H:i:s') : '-' }}</span>
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
                    <span class="badge {{ $badgeClass }}" id="sync-status-badge">{{ $setting->status_badge_text }}</span>
                  </div>
                </div>
                {{-- Selalu render div pesan, tapi set d-none jika pesan kosong --}}
                <div class="set-field set-field--full {{ !$setting->last_sync_message ? 'd-none' : '' }}" id="sync-message-container">
                  <label class="set-label">Pesan</label>
                  <p class="text-muted small mt-1 mb-0" id="sync-message-text">{{ $setting->last_sync_message ?? '' }}</p>
                </div>
              </div>
            </div>
          </div>
          @endif

          <!-- Panel 3: Aksi Cepat (Quick Actions) -->
          @if($setting->id)
          <div class="set-panel mb-4">
            <div class="set-panel__head">
              <div class="set-panel__title-wrap">
                <div class="set-panel__icon --primary"><i class="ti tabler-bolt"></i></div>
                <div>
                  <div class="set-panel__title">Aksi Cepat</div>
                  <div class="set-panel__sub">Jalankan aksi sinkronisasi dan manajemen antrian.</div>
                </div>
              </div>
            </div>
            <div class="set-panel__body">
              <div class="gs-action-list">
                
                <!-- Aksi 1: Sinkron Sekarang -->
                <div class="gs-action-item">
                  <div class="gs-action-item__header">
                    <div class="gs-action-item__icon --warning"><i class="ti tabler-refresh"></i></div>
                    <div>
                      <div class="gs-action-item__title">Sinkronisasi Sekarang</div>
                      <div class="gs-action-item__sub">Tarik data siswa terbaru ke antrian.</div>
                    </div>
                  </div>
                  <form action="{{ route('admin.pengaturan.google-sheets.sync-now') }}" method="POST" id="gsSyncNowForm" class="m-0">
                    @csrf
                    <button type="button" class="gs-btn-compact gs-btn-compact--warning" id="gsOpenSyncConfirmButton" onclick="openGsSyncConfirmModal()">
                      <i class="ti tabler-refresh"></i>
                      <span>Sinkron Sekarang</span>
                    </button>
                  </form>
                </div>

                <!-- Aksi 2: Proses Antrian -->
                <div class="gs-action-item">
                  <div class="gs-action-item__header">
                    <div class="gs-action-item__icon --primary"><i class="ti tabler-player-play"></i></div>
                    <div>
                      <div class="gs-action-item__title">Proses Antrian</div>
                      <div class="gs-action-item__sub">Proses job tertunda di lokal.</div>
                    </div>
                  </div>
                  <button type="button" class="gs-btn-compact gs-btn-compact--primary" id="gsProcessQueueBtn" onclick="processGsQueue()">
                    <i class="ti tabler-player-play"></i>
                    <span>Proses Antrian</span>
                  </button>
                </div>

                <!-- Aksi 3: Reset Antrian -->
                <div class="gs-action-item">
                  <div class="gs-action-item__header">
                    <div class="gs-action-item__icon --danger"><i class="ti tabler-trash-x"></i></div>
                    <div>
                      <div class="gs-action-item__title">Reset Antrian</div>
                      <div class="gs-action-item__sub">Batalkan sinkronisasi stuck.</div>
                    </div>
                  </div>
                  <button type="button" class="gs-btn-compact gs-btn-compact--danger" id="gsResetQueueBtn" onclick="confirmGsResetQueue()">
                    <i class="ti tabler-trash-x"></i>
                    <span>Reset Antrian</span>
                  </button>
                </div>

                <!-- Aksi 4: Test Koneksi -->
                <div class="gs-action-item">
                  <div class="gs-action-item__header">
                    <div class="gs-action-item__icon --info"><i class="ti tabler-plug-connected"></i></div>
                    <div>
                      <div class="gs-action-item__title">Test Koneksi API</div>
                      <div class="gs-action-item__sub">Uji credential ke Google API.</div>
                    </div>
                  </div>
                  <button type="button" class="gs-btn-compact gs-btn-compact--info" id="gsTestBtn" onclick="openGsTestModal()">
                    <i class="ti tabler-plug-connected"></i>
                    <span>Test Koneksi</span>
                  </button>
                </div>

              </div>
            </div>
          </div>
          @endif
          
        </div>
      </div>

      <!-- BAGIAN BAWAH: TROUBLESHOOTING & PREVIEW MAPPING (FULL WIDTH) -->
      <div class="gs-grid-bottom mt-4">
        {{-- ─────────────────────────────
             PANEL 3: Panduan Pemecahan Masalah (Dinamis)
        ───────────────────────────── --}}
        @if($setting->id)
        <div class="set-panel mb-4 {{ !in_array($setting->last_sync_status, ['failed', 'completed_with_errors']) ? 'd-none' : '' }}" id="sync-troubleshoot-panel" style="border-color: rgba(234, 84, 85, 0.25);">
          <div class="set-panel__head" style="background: linear-gradient(90deg, rgba(234, 84, 85, 0.05) 0%, transparent 60%);">
            <div class="set-panel__title-wrap">
              <div class="set-panel__icon" style="background: rgba(234, 84, 85, 0.12); color: var(--das-danger);"><i class="ti tabler-help-circle"></i></div>
              <div>
                <div class="set-panel__title">Panduan Pemecahan Masalah</div>
                <div class="set-panel__sub">Ikuti langkah berikut untuk memperbaiki kesalahan sinkronisasi yang terjadi.</div>
              </div>
            </div>
          </div>
          <div class="set-panel__body">
            <div id="sync-troubleshoot-steps" style="display:flex;flex-direction:column;gap:0.75rem;font-size:0.8rem;color:#94a3b8;">
              {{-- Diisi secara dinamis oleh JavaScript, atau render server-side jika error --}}
              @if(in_array($setting->last_sync_status, ['failed', 'completed_with_errors']))
                @php
                  $msg = $setting->last_sync_message ?? '';
                  $stepHtml = '';
                  if (str_contains($msg, 'Credentials JSON') || str_contains($msg, 'MAC') || str_contains($msg, 'decrypt') || str_contains($msg, 'decrpyt')) {
                    $stepHtml = '
                      <div class="d-flex align-items-start gap-2 p-2 rounded" style="background: rgba(234, 84, 85, 0.04); border: 1px solid rgba(234, 84, 85, 0.08);">
                        <i class="ti tabler-circle-number-1 text-danger mt-1"></i>
                        <div>
                          <b class="text-white">Credentials Lama Rusak (APP_KEY Berubah)</b>
                          <p class="mb-0 mt-1 small">Langkah perbaikan: Silakan upload/tempel kembali isi file <b>Service Account JSON</b> Anda yang baru pada kolom konfigurasi di bawah, lalu klik <b>Simpan Pengaturan</b>.</p>
                        </div>
                      </div>';
                  } elseif (str_contains($msg, 'Mapping kolom')) {
                    $stepHtml = '
                      <div class="d-flex align-items-start gap-2 p-2 rounded" style="background: rgba(234, 84, 85, 0.04); border: 1px solid rgba(234, 84, 85, 0.08);">
                        <i class="ti tabler-circle-number-1 text-danger mt-1"></i>
                        <div>
                          <b class="text-white">Konfigurasi Mapping Kolom Kosong</b>
                          <p class="mb-0 mt-1 small">Langkah perbaikan: Gulir ke bagian <b>Mapping Kolom (JSON)</b> di bawah, masukkan format pemetaan kolom database Anda ke kolom Google Sheet (gunakan template contoh di bawah input), lalu klik <b>Simpan</b>.</p>
                        </div>
                      </div>';
                  } elseif (str_contains($msg, 'Gagal mengambil data') || str_contains($msg, 'permission') || str_contains($msg, 'access') || str_contains($msg, 'Forbidden')) {
                    $stepHtml = '
                      <div class="d-flex align-items-start gap-2 p-2 rounded" style="background: rgba(234, 84, 85, 0.04); border: 1px solid rgba(234, 84, 85, 0.08);">
                        <i class="ti tabler-circle-number-1 text-danger mt-1"></i>
                        <div>
                          <b class="text-white">Akses ke Spreadsheet Ditolak / Dilarang</b>
                          <p class="mb-0 mt-1 small">Langkah perbaikan: Buka Google Sheets Anda ➔ Klik tombol <b>Bagikan (Share)</b> di pojok kanan atas ➔ Undang email <b>Service Account</b> Anda (misal yang berakhiran <i>@gserviceaccount.com</i>) sebagai <b>Viewer</b> atau <b>Editor</b>, lalu klik kirim.</p>
                        </div>
                      </div>
                      <div class="d-flex align-items-start gap-2 p-2 rounded mt-2" style="background: rgba(234, 84, 85, 0.04); border: 1px solid rgba(234, 84, 85, 0.08);">
                        <i class="ti tabler-circle-number-2 text-danger mt-1"></i>
                        <div>
                          <b class="text-white">ID Spreadsheet atau Range Salah</b>
                          <p class="mb-0 mt-1 small">Pastikan nilai pada kolom <b>ID Spreadsheet</b> dan <b>Range Sheet</b> di bawah sudah benar sesuai URL dan nama Sheet Anda.</p>
                        </div>
                      </div>';
                  } elseif (str_contains($msg, 'tidak dikenal')) {
                    $stepHtml = '
                      <div class="d-flex align-items-start gap-2 p-2 rounded" style="background: rgba(255, 159, 67, 0.04); border: 1px solid rgba(255, 159, 67, 0.08);">
                        <i class="ti tabler-circle-number-1 text-warning mt-1"></i>
                        <div>
                          <b class="text-white">Ada Header Kolom yang Tidak Dikenal</b>
                          <p class="mb-0 mt-1 small">Kolom berikut tidak dikenal: <span id="troubleshoot-unrecognized-list" class="text-warning fw-bold"></span>. Silakan sesuaikan header Google Sheet dengan template standar yang bisa diunduh di atas.</p>
                        </div>
                      </div>';
                  } else {
                    $stepHtml = '
                      <div class="d-flex align-items-start gap-2 p-2 rounded" style="background: rgba(255, 159, 67, 0.04); border: 1px solid rgba(255, 159, 67, 0.08);">
                        <i class="ti tabler-info-circle text-warning mt-1"></i>
                        <div>
                          <b class="text-white">Kesalahan Umum</b>
                          <p class="mb-0 mt-1 small">Langkah perbaikan: Pastikan koneksi internet server stabil, file credentials Service Account JSON valid, ID Spreadsheet benar, dan Anda sudah melakukan <b>Test Koneksi</b> terlebih dahulu sebelum sinkronisasi.</p>
                        </div>
                      </div>';
                  }
                  echo $stepHtml;
                @endphp
              @endif
            </div>
          </div>
        </div>
        @endif

        {{-- ─────────────────────────────
             PANEL 4: Preview Mapping Kolom
        ───────────────────────────── --}}
        @if($setting->id)
        <div class="set-panel mb-4">
          <div class="set-panel__head">
            <div class="set-panel__title-wrap">
              <div class="set-panel__icon --primary"><i class="ti tabler-columns"></i></div>
              <div>
                <div class="set-panel__title">Preview Mapping Kolom</div>
                <div class="set-panel__sub">Deteksi otomatis header Google Sheet dan mapping ke kolom database.</div>
              </div>
            </div>
          </div>
          <div class="set-panel__body">
            <p class="text-muted small mb-3" style="color:#94a3b8;font-size:0.78rem;line-height:1.5;">
              Klik tombol di bawah untuk mendeteksi header dari Google Sheet dan melihat preview mapping kolom.
            </p>
            <div class="d-flex flex-wrap gap-2 mb-3">
              <button type="button" class="gs-action-btn gs-action-btn--primary" id="gsDetectMappingBtn" onclick="detectGsMapping()">
                <i class="ti tabler-refresh"></i>
                <span>Deteksi Mapping</span>
              </button>
              <span id="gsDetectMappingSpinner" style="display:none;align-self:center;">
                <i class="ti tabler-loader-2 animate-spin" style="font-size:1.1rem;color:var(--das-primary);"></i>
                <span class="small text-muted ms-1">Mendeteksi header...</span>
              </span>
            </div>

            {{-- Wrapper hasil preview (diisi JS) --}}
            <div id="gsMappingResult" style="display:none;">
              {{-- Tabel Preview --}}
              <div style="overflow-x:auto;border:1px solid var(--das-border);border-radius:var(--das-radius-sm);">
                <table class="gs-preview-table" id="gsMappingTable">
                  <thead>
                    <tr>
                      <th style="width:60px;text-align:center;">No</th>
                      <th>Header Sheet</th>
                      <th>Mapping ke Database</th>
                      <th style="width:130px;text-align:center;">Status</th>
                    </tr>
                  </thead>
                  <tbody id="gsMappingTableBody"></tbody>
                </table>
              </div>

              {{-- Warning unrecognized --}}
              <div id="gsUnrecognizedWarning" style="display:none;margin-top:0.75rem;padding:0.65rem 1rem;background:rgba(255,159,67,0.08);border:1px solid rgba(255,159,67,0.2);border-radius:var(--das-radius-sm);font-size:0.78rem;color:#fcd34d;">
                <i class="ti tabler-alert-triangle me-1"></i>
                <span id="gsUnrecognizedWarningText"></span>
              </div>

              {{-- Ringkasan --}}
              <div id="gsMappingSummary" style="display:none;margin-top:0.75rem;flex-wrap:wrap;gap:1rem;font-size:0.78rem;">
                <span><span class="text-muted">Total Header:</span> <strong class="text-white" id="gsTotalHeaders">0</strong></span>
                <span><span class="text-muted">Terdeteksi:</span> <strong class="text-white" id="gsMatchedCount">0</strong></span>
                <span><span class="text-muted">Tidak Dikenal:</span> <strong class="text-white" id="gsUnrecognizedCount">0</strong></span>
              </div>
            </div>

            {{-- Error state --}}
            <div id="gsMappingError" style="display:none;padding:1rem;background:rgba(234,84,85,0.06);border:1px solid rgba(234,84,85,0.15);border-radius:var(--das-radius-sm);font-size:0.82rem;color:#fca5a5;">
              <i class="ti tabler-alert-circle me-1"></i>
              <span id="gsMappingErrorText"></span>
            </div>
          </div>
        </div>
        @endif
      </div>

      <div class="set-footer-save d-lg-none">
        <button type="submit" form="gsUpdateForm" class="set-save-btn">
          <i class="ti tabler-device-floppy"></i>
          <span>Simpan Pengaturan</span>
        </button>
      </div>
    </main>
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

/* ── Action Buttons (Download Template, Buat Sheet, Deteksi Mapping) ── */
.gs-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.6rem 1.15rem;
  border: none;
  border-radius: var(--das-radius-sm);
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  white-space: nowrap;
}
.gs-action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.25);
  color: #fff;
}
.gs-action-btn--primary {
  background: var(--das-primary);
  color: #fff;
}
.gs-action-btn--primary:hover {
  background: #6259e8;
  box-shadow: 0 6px 16px rgba(115,103,240,0.35);
}
.gs-action-btn--success {
  background: var(--das-success);
  color: #fff;
}
.gs-action-btn--success:hover {
  background: #23ad60;
  box-shadow: 0 6px 16px rgba(40,199,111,0.35);
}
.gs-action-btn--info {
  background: var(--das-info);
  color: #fff;
}
.gs-action-btn--info:hover {
  background: #00b8d4;
  box-shadow: 0 6px 16px rgba(0,207,232,0.35);
}

/* ── Layout Grid 2 Kolom ── */
@media (min-width: 992px) {
  .gs-grid-container {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 1.25rem;
    align-items: start;
  }
}

.gs-grid-left {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.gs-grid-right {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

/* ── Panel Aksi Cepat (Quick Actions) ── */
.gs-action-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.gs-action-item {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  padding: 0.85rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius-sm);
  transition: all 0.2s ease;
}

.gs-action-item:hover {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.15);
}

.gs-action-item__header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.gs-action-item__icon {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}

.gs-action-item__icon.--warning { background: var(--das-warning-soft); color: var(--das-warning); }
.gs-action-item__icon.--info { background: var(--das-info-soft); color: var(--das-info); }
.gs-action-item__icon.--danger { background: var(--das-danger-soft); color: var(--das-danger); }
.gs-action-item__icon.--primary { background: var(--das-primary-soft); color: var(--das-primary); }

.gs-action-item__title {
  font-size: 0.82rem;
  font-weight: 700;
  color: #e2e8f0;
}

.gs-action-item__sub {
  font-size: 0.68rem;
  color: #64748b;
  margin-top: 1px;
}

.gs-btn-compact {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  border: none;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.55rem 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.gs-btn-compact--warning { background: var(--das-warning); color: #1a1a1a; }
.gs-btn-compact--warning:hover { background: #f0920a; transform: translateY(-1px); }

.gs-btn-compact--info { background: var(--das-info); color: #1a1a1a; }
.gs-btn-compact--info:hover { background: #00b8d4; transform: translateY(-1px); }

.gs-btn-compact--danger { background: var(--das-danger); color: #fff; }
.gs-btn-compact--danger:hover { background: #d32f2f; transform: translateY(-1px); }

.gs-btn-compact--primary { background: var(--das-primary); color: #fff; }
.gs-btn-compact--primary:hover { background: #5a4ee3; transform: translateY(-1px); }

/* ── Preview Mapping Table ── */
.gs-preview-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8rem;
}
.gs-preview-table thead th {
  background: rgba(255,255,255,0.04);
  color: #64748b;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.62rem;
  letter-spacing: 0.8px;
  padding: 0.65rem 1rem;
  border-bottom: 1px solid var(--das-border);
  text-align: left;
}
.gs-preview-table tbody td {
  padding: 0.55rem 1rem;
  border-bottom: 1px solid var(--das-border);
  color: #cbd5e1;
  vertical-align: middle;
}
.gs-preview-table tbody tr:last-child td {
  border-bottom: none;
}
.gs-preview-table tbody tr:hover {
  background: rgba(255,255,255,0.02);
}
.gs-preview-table tbody tr td:first-child {
  text-align: center;
  color: #64748b;
}
.gs-preview-table .gs-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.2rem 0.55rem;
  border-radius: 20px;
  font-size: 0.68rem;
  font-weight: 600;
}
.gs-preview-table .gs-status-badge--matched {
  background: rgba(40,199,111,0.12);
  color: #4ade80;
}
.gs-preview-table .gs-status-badge--unrecognized {
  background: rgba(255,159,67,0.12);
  color: #fcd34d;
}
.gs-mapped-col {
  font-family: 'Courier New', monospace;
  color: #e2e8f0;
}
.gs-unmapped-col {
  color: #64748b;
  font-style: italic;
}

/* ── SweetAlert2 Dark Theme Premium ── */
.das-swal-popup {
  width: 420px !important;
  padding: 2rem 1.5rem !important;
  background: rgba(22, 29, 49, 0.96) !important;
  backdrop-filter: blur(16px) !important;
  border: 1px solid rgba(255, 255, 255, 0.06) !important;
  border-radius: 16px !important;
  box-shadow: 
    0 0 0 1px rgba(255, 255, 255, 0.03),
    0 8px 32px rgba(0, 0, 0, 0.4),
    0 2px 8px rgba(0, 0, 0, 0.2) !important;
}

.das-swal-icon-success {
  position: relative;
  width: 64px !important;
  height: 64px !important;
  margin: 0 auto 1rem !important;
  border-color: #28c76f !important;
  animation: das-glow-pulse 2s ease-in-out infinite;
}

.das-swal-icon-success::before {
  content: '';
  position: absolute;
  inset: -8px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(40, 199, 111, 0.15) 0%, transparent 70%);
  animation: das-glow-pulse-ring 2s ease-in-out infinite;
}

@keyframes das-glow-pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.3); }
  50% { box-shadow: 0 0 0 12px rgba(40, 199, 111, 0); }
}

@keyframes das-glow-pulse-ring {
  0%, 100% { transform: scale(1); opacity: 0.6; }
  50% { transform: scale(1.15); opacity: 0; }
}

.das-swal-icon-success .swal2-success-ring {
  border-color: #28c76f !important;
}

.das-swal-icon-success [class^='swal2-success-line'] {
  background-color: #28c76f !important;
}

.das-swal-title {
  color: #fff !important;
  font-size: 1.35rem !important;
  font-weight: 700 !important;
  padding: 0 !important;
  margin-bottom: 0.5rem !important;
}

.das-swal-html {
  color: rgba(255, 255, 255, 0.65) !important;
  font-size: 0.85rem !important;
  line-height: 1.6 !important;
  max-width: 320px !important;
  margin: 0 auto !important;
}

.das-swal-actions {
  display: flex !important;
  flex-direction: column !important;
  gap: 0.625rem !important;
  margin-top: 1.25rem !important;
  width: 100% !important;
}

.das-swal-confirm-btn {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 0.5rem !important;
  width: 100% !important;
  padding: 0.7rem 1.25rem !important;
  background: linear-gradient(135deg, #28c76f, #1f9d57) !important;
  color: #fff !important;
  font-size: 0.85rem !important;
  font-weight: 600 !important;
  border: none !important;
  border-radius: 10px !important;
  cursor: pointer !important;
  transition: all 0.2s ease !important;
  box-shadow: 0 4px 16px rgba(40, 199, 111, 0.25) !important;
  text-decoration: none !important;
}

.das-swal-confirm-btn:hover {
  transform: translateY(-1px) !important;
  box-shadow: 0 6px 24px rgba(40, 199, 111, 0.35) !important;
  background: linear-gradient(135deg, #3ddb84, #28c76f) !important;
}

.das-swal-danger-btn {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 0.5rem !important;
  width: 100% !important;
  padding: 0.7rem 1.25rem !important;
  background: linear-gradient(135deg, #ea5455, #c53738) !important;
  color: #fff !important;
  font-size: 0.85rem !important;
  font-weight: 600 !important;
  border: none !important;
  border-radius: 10px !important;
  cursor: pointer !important;
  transition: all 0.2s ease !important;
  box-shadow: 0 4px 16px rgba(234, 84, 85, 0.25) !important;
  text-decoration: none !important;
}

.das-swal-danger-btn:hover {
  transform: translateY(-1px) !important;
  box-shadow: 0 6px 24px rgba(234, 84, 85, 0.35) !important;
  background: linear-gradient(135deg, #ff6b6b, #ea5455) !important;
}

.das-swal-cancel-btn {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 0.5rem !important;
  width: 100% !important;
  padding: 0.65rem 1.25rem !important;
  background: rgba(255, 255, 255, 0.04) !important;
  color: rgba(255, 255, 255, 0.6) !important;
  font-size: 0.82rem !important;
  font-weight: 500 !important;
  border: 1px solid rgba(255, 255, 255, 0.08) !important;
  border-radius: 10px !important;
  cursor: pointer !important;
  transition: all 0.2s ease !important;
  text-decoration: none !important;
}

.das-swal-cancel-btn:hover {
  background: rgba(255, 255, 255, 0.08) !important;
  color: #fff !important;
  border-color: rgba(255, 255, 255, 0.15) !important;
}

/* Error popup variants */
.das-swal-popup-error {
  border-color: rgba(234, 84, 85, 0.2) !important;
}

.das-swal-icon-error {
  border-color: #ea5455 !important;
}

.das-swal-icon-error .swal2-x-mark-line-left,
.das-swal-icon-error .swal2-x-mark-line-right {
  background-color: #ea5455 !important;
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
        startGsSyncPolling(); // <--- PANGGIL POLLING DI SINI
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

/**
 * Proses antrian queue sinkronisasi via AJAX.
 */
async function processGsQueue() {
  const btn = document.getElementById('gsProcessQueueBtn');
  const originalHtml = btn.innerHTML;
  
  btn.disabled = true;
  btn.innerHTML = '<i class="ti tabler-loader-2 animate-spin"></i> <span>Memproses...</span>';
  
  try {
    const response = await fetch('{{ route("admin.pengaturan.google-sheets.process-queue") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: JSON.stringify({})
    });

    const result = await response.json();

    if (result.success) {
      // Refresh status sinkronisasi
      await refreshGsSyncStatus();
      
      Swal.fire({
        icon: 'success',
        title: 'Antrian Diproses!',
        html: 'Job sinkronisasi berhasil diproses. <br> Status: <strong>' + (result.last_sync_status || '-') + '</strong>',
        confirmButtonText: '<i class="ti tabler-check"></i> OK',
        buttonsStyling: false,
        customClass: {
          popup: 'das-swal-popup',
          title: 'das-swal-title',
          htmlContainer: 'das-swal-html',
          confirmButton: 'das-swal-cancel-btn',
          icon: 'das-swal-icon-success'
        }
      });
    } else {
      showGsDynamicToast('danger', result.message || 'Gagal memproses antrian.');
    }
  } catch (error) {
    console.error('Process queue error:', error);
    showGsDynamicToast('danger', 'Gagal menghubungi server. Silakan coba lagi.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHtml;
  }
}

/**
 * Refresh panel status sinkronisasi via AJAX.
 */
async function refreshGsSyncStatus() {
  try {
    const response = await fetch('{{ route("admin.pengaturan.google-sheets.index") }}', {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });
    const result = await response.json();
    
    if (result.last_sync_at) {
      document.getElementById('sync-last-time').textContent = result.last_sync_at;
    }
    if (result.status_badge_text) {
      const badge = document.getElementById('sync-status-badge');
      badge.textContent = result.status_badge_text;
      // Update badge class - you may need to map this properly
      badge.className = 'badge ' + (result.last_sync_status === 'success' ? 'badge-success' : 
                                     result.last_sync_status === 'in_progress' ? 'badge-warning' : 
                                     result.last_sync_status === 'failed' ? 'badge-danger' : 'badge-secondary');
    }
    if (result.last_sync_message) {
      const msgContainer = document.getElementById('sync-message-container');
      msgContainer.classList.remove('d-none');
      document.getElementById('sync-message-text').textContent = result.last_sync_message;
    }
  } catch (e) {
    console.warn('Failed to refresh sync status:', e);
  }
}

/**
 * Konfirmasi reset antrian via SweetAlert2.
 */
function confirmGsResetQueue() {
  Swal.fire({
    icon: 'warning',
    title: 'Reset Antrian?',
    html: '<div style="text-align:center">Apakah Anda yakin ingin membatalkan sinkronisasi aktif dan menghapus semua antrian?</div>',
    showCancelButton: true,
    confirmButtonText: '<i class="ti tabler-trash"></i> Ya, Reset',
    cancelButtonText: 'Batal',
    buttonsStyling: false,
    customClass: {
      popup: 'das-swal-popup das-swal-popup-error',
      title: 'das-swal-title',
      htmlContainer: 'das-swal-html',
      actions: 'das-swal-actions',
      confirmButton: 'das-swal-danger-btn',
      cancelButton: 'das-swal-cancel-btn'
    }
  }).then((result) => {
    if (result.isConfirmed) {
      submitGsResetQueue();
    }
  });
}

/**
 * Submit reset antrian via AJAX.
 */
async function submitGsResetQueue() {
  const btn = document.getElementById('gsResetQueueBtn');
  const originalHtml = btn.innerHTML;
  
  btn.disabled = true;
  btn.innerHTML = '<i class="ti tabler-loader-2 animate-spin"></i> <span>Mereset...</span>';
  
  try {
    const response = await fetch('{{ route("admin.pengaturan.google-sheets.reset-antrian") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: JSON.stringify({})
    });

    const result = await response.json();

    if (result.success) {
      // Refresh status sinkronisasi
      await refreshGsSyncStatus();
      
      Swal.fire({
        icon: 'success',
        title: 'Antrian Di-reset!',
        html: 'Antrian sinkronisasi berhasil dikosongkan dan status di-reset ke idle.',
        confirmButtonText: '<i class="ti tabler-check"></i> OK',
        buttonsStyling: false,
        customClass: {
          popup: 'das-swal-popup',
          title: 'das-swal-title',
          htmlContainer: 'das-swal-html',
          confirmButton: 'das-swal-cancel-btn',
          icon: 'das-swal-icon-success'
        }
      });
    } else {
      showGsDynamicToast('danger', result.message || 'Gagal mereset antrian.');
    }
  } catch (error) {
    console.error('Reset queue error:', error);
    showGsDynamicToast('danger', 'Gagal menghubungi server. Silakan coba lagi.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHtml;
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

function getTroubleshootStepsHtml(message) {
  if (!message) return '';
  
  if (message.includes('Credentials JSON') || message.includes('MAC') || message.includes('decrypt') || message.includes('decrpyt')) {
    return `
      <div class="d-flex align-items-start gap-2 p-2 rounded" style="background: rgba(234, 84, 85, 0.04); border: 1px solid rgba(234, 84, 85, 0.08);">
        <i class="ti tabler-circle-number-1 text-danger mt-1"></i>
        <div>
          <b class="text-white">Credentials Lama Rusak (APP_KEY Berubah)</b>
          <p class="mb-0 mt-1 small">Langkah perbaikan: Silakan upload/tempel kembali isi file <b>Service Account JSON</b> Anda yang baru pada kolom konfigurasi di bawah, lalu klik <b>Simpan Pengaturan</b>.</p>
        </div>
      </div>
    `;
  }
  
  if (message.includes('Mapping kolom')) {
    return `
      <div class="d-flex align-items-start gap-2 p-2 rounded" style="background: rgba(234, 84, 85, 0.04); border: 1px solid rgba(234, 84, 85, 0.08);">
        <i class="ti tabler-circle-number-1 text-danger mt-1"></i>
        <div>
          <b class="text-white">Konfigurasi Mapping Kolom Kosong</b>
          <p class="mb-0 mt-1 small">Langkah perbaikan: Gulir ke bagian <b>Mapping Kolom (JSON)</b> di bawah, masukkan format pemetaan kolom database Anda ke kolom Google Sheet (gunakan template contoh di bawah input), lalu klik <b>Simpan</b>.</p>
        </div>
      </div>
    `;
  }
  
  if (message.includes('Gagal mengambil data') || message.includes('permission') || message.includes('access') || message.includes('Forbidden')) {
    return `
      <div class="d-flex align-items-start gap-2 p-2 rounded" style="background: rgba(234, 84, 85, 0.04); border: 1px solid rgba(234, 84, 85, 0.08);">
        <i class="ti tabler-circle-number-1 text-danger mt-1"></i>
        <div>
          <b class="text-white">Akses ke Spreadsheet Ditolak / Dilarang</b>
          <p class="mb-0 mt-1 small">Langkah perbaikan: Buka Google Sheets Anda ➔ Klik tombol <b>Bagikan (Share)</b> di pojok kanan atas ➔ Undang email <b>Service Account</b> Anda (misal yang berakhiran <i>@gserviceaccount.com</i>) sebagai <b>Viewer</b> atau <b>Editor</b>, lalu klik kirim.</p>
        </div>
      </div>
      <div class="d-flex align-items-start gap-2 p-2 rounded mt-2" style="background: rgba(234, 84, 85, 0.04); border: 1px solid rgba(234, 84, 85, 0.08);">
        <i class="ti tabler-circle-number-2 text-danger mt-1"></i>
        <div>
          <b class="text-white">ID Spreadsheet atau Range Salah</b>
          <p class="mb-0 mt-1 small">Pastikan nilai pada kolom <b>ID Spreadsheet</b> dan <b>Range Sheet</b> di bawah sudah benar sesuai URL dan nama Sheet Anda.</p>
        </div>
      </div>
    `;
  }
  
  if (message.includes('tidak dikenal')) {
    return `
      <div class="d-flex align-items-start gap-2 p-2 rounded" style="background: rgba(255, 159, 67, 0.04); border: 1px solid rgba(255, 159, 67, 0.08);">
        <i class="ti tabler-circle-number-1 text-warning mt-1"></i>
        <div>
          <b class="text-white">Ada Header Kolom yang Tidak Dikenal</b>
          <p class="mb-0 mt-1 small">Kolom berikut tidak dikenal: <span id="troubleshoot-unrecognized-list-dynamic" class="text-warning fw-bold"></span>. Silakan sesuaikan header Google Sheet dengan template standar yang bisa diunduh di atas.</p>
        </div>
      </div>
    `;
  }

  // Fallback
  return `
    <div class="d-flex align-items-start gap-2 p-2 rounded" style="background: rgba(255, 159, 67, 0.04); border: 1px solid rgba(255, 159, 67, 0.08);">
      <i class="ti tabler-info-circle text-warning mt-1"></i>
      <div>
        <b class="text-white">Kesalahan Umum</b>
        <p class="mb-0 mt-1 small">Langkah perbaikan: Pastikan koneksi internet server stabil, file credentials Service Account JSON valid, ID Spreadsheet benar, dan Anda sudah melakukan <b>Test Koneksi</b> terlebih dahulu sebelum sinkronisasi.</p>
      </div>
    </div>
  `;
}

/* ── SweetAlert2 (di-load via Vite di akhir halaman) ── */

/**
 * Buat Google Sheet Template via AJAX.
 * Memanggil route admin.pengaturan.google-sheets.template.create
 */
async function createGsSheetTemplate() {
  const btn = document.getElementById('gsCreateSheetBtn');
  const originalHtml = btn.innerHTML;

  // Loading state
  btn.disabled = true;
  btn.innerHTML = '<i class="ti tabler-loader-2 animate-spin"></i> <span>Membuat...</span>';

  try {
    const response = await fetch('{{ route("admin.pengaturan.google-sheets.template.create") }}', {
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
      // Isi otomatis spreadsheet_id
      const inputEl = document.getElementById('spreadsheet_id');
      if (inputEl && result.spreadsheet_id) {
        inputEl.value = result.spreadsheet_id;
      }

      // SweetAlert2 sukses — premium theme
      Swal.fire({
        icon: 'success',
        title: 'Template Berhasil Dibuat!',
        html: '<div style="text-align:center">Google Sheet template telah berhasil dibuat dengan 11 kolom otomatis.</div>',
        confirmButtonText: '<i class="ti tabler-check"></i> Selesai',
        showCancelButton: true,
        cancelButtonText: '<i class="ti tabler-external-link"></i> Buka Google Sheet',
        cancelButtonColor: '#28c76f',
        reverseButtons: false,
        buttonsStyling: false,
        customClass: {
          popup: 'das-swal-popup',
          title: 'das-swal-title',
          htmlContainer: 'das-swal-html',
          actions: 'das-swal-actions',
          confirmButton: 'das-swal-cancel-btn',
          cancelButton: 'das-swal-confirm-btn',
          icon: 'das-swal-icon-success'
        },
        didOpen: () => {
          // Link buka google sheet di cancel button
          const cancelBtn = document.querySelector('.das-swal-confirm-btn');
          if (cancelBtn && result.url) {
            cancelBtn.addEventListener('click', () => {
              window.open(result.url, '_blank');
            });
          }
        }
      }).then((resultSwal) => {
        if (resultSwal.dismiss === Swal.DismissReason.cancel && result.url) {
          window.open(result.url, '_blank');
        }
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Gagal Membuat Template',
        text: result.message || 'Terjadi kesalahan saat membuat template Google Sheet.',
        confirmButtonText: '<i class="ti tabler-x"></i> Tutup',
        buttonsStyling: false,
        customClass: {
          popup: 'das-swal-popup das-swal-popup-error',
          title: 'das-swal-title',
          htmlContainer: 'das-swal-html',
          confirmButton: 'das-swal-cancel-btn',
          icon: 'das-swal-icon-error'
        }
      });
    }
  } catch (error) {
    console.error('Create sheet error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Gagal Menghubungi Server',
      text: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
      confirmButtonText: '<i class="ti tabler-x"></i> Tutup',
      buttonsStyling: false,
      customClass: {
        popup: 'das-swal-popup das-swal-popup-error',
        title: 'das-swal-title',
        htmlContainer: 'das-swal-html',
        confirmButton: 'das-swal-cancel-btn',
        icon: 'das-swal-icon-error'
      }
    });
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHtml;
  }
}

/**
 * Deteksi mapping kolom via AJAX.
 * Memanggil route admin.pengaturan.google-sheets.preview-mapping
 */
async function detectGsMapping() {
  const btn = document.getElementById('gsDetectMappingBtn');
  const spinner = document.getElementById('gsDetectMappingSpinner');
  const resultDiv = document.getElementById('gsMappingResult');
  const errorDiv = document.getElementById('gsMappingError');
  const tableBody = document.getElementById('gsMappingTableBody');
  const warningDiv = document.getElementById('gsUnrecognizedWarning');
  const warningText = document.getElementById('gsUnrecognizedWarningText');

  // Reset
  resultDiv.style.display = 'none';
  errorDiv.style.display = 'none';
  warningDiv.style.display = 'none';
  btn.disabled = true;
  spinner.style.display = 'inline-flex';

  try {
    const response = await fetch('{{ route("admin.pengaturan.google-sheets.preview-mapping") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify({})
    });

    const json = await response.json();

    if (!json.success) {
      errorDiv.style.display = 'flex';
      document.getElementById('gsMappingErrorText').textContent = json.message || 'Gagal mendapatkan preview mapping.';
      return;
    }

    const data = json.data;

    // Validasi data
    if (!data.preview || data.preview.length === 0) {
      errorDiv.style.display = 'flex';
      document.getElementById('gsMappingErrorText').textContent = data.error || 'Tidak ada header yang terdeteksi dari sheet.';
      return;
    }

    // Render tabel
    let html = '';
    data.preview.forEach((item, index) => {
      const no = index + 1;
      const isMatched = item.status === 'matched';

      html += '<tr>' +
        '<td>' + no + '</td>' +
        '<td>' + escapeHtml(item.header) + '</td>' +
        '<td>' + (isMatched
          ? '<span class="gs-mapped-col">' + escapeHtml(item.mapped_to) + '</span>'
          : '<span class="gs-unmapped-col">—</span>') +
        '</td>' +
        '<td style="text-align:center;">' +
        (isMatched
          ? '<span class="gs-status-badge gs-status-badge--matched"><i class="ti tabler-circle-check"></i> Terdeteksi</span>'
          : '<span class="gs-status-badge gs-status-badge--unrecognized"><i class="ti tabler-alert-triangle"></i> Tidak dikenal</span>') +
        '</td>' +
        '</tr>';
    });

    tableBody.innerHTML = html;

    // Update ringkasan
    document.getElementById('gsTotalHeaders').textContent = data.total_headers || data.preview.length;
    document.getElementById('gsMatchedCount').textContent = data.matched || 0;
    const unrecognizedCount = (data.unrecognized && data.unrecognized.length) || 0;
    document.getElementById('gsUnrecognizedCount').textContent = unrecognizedCount;

    // Warning untuk unrecognized
    if (unrecognizedCount > 0) {
      warningText.textContent = unrecognizedCount + ' kolom tidak dikenal. Sinkronisasi tetap berjalan untuk kolom yang dikenal.';
      warningDiv.style.display = 'flex';
    } else {
      warningDiv.style.display = 'none';
    }

    // Tampilkan hasil
    resultDiv.style.display = 'block';
    // Tampilkan ringkasan
    document.getElementById('gsMappingSummary').style.display = 'flex';

  } catch (error) {
    console.error('Preview mapping error:', error);
    errorDiv.style.display = 'flex';
    document.getElementById('gsMappingErrorText').textContent = 'Gagal menghubungi server. Silakan coba lagi.';
  } finally {
    btn.disabled = false;
    spinner.style.display = 'none';
  }
}

/**
 * Escape HTML untuk mencegah XSS.
 */
function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.appendChild(document.createTextNode(text));
  return div.innerHTML;
}

let gsSyncPollingInterval = null;

function startGsSyncPolling() {
  if (gsSyncPollingInterval) return; // cegah dobel interval

  const lastTimeEl = document.getElementById('sync-last-time');
  const badgeEl = document.getElementById('sync-status-badge');
  const msgContainer = document.getElementById('sync-message-container');
  const msgEl = document.getElementById('sync-message-text');

  // Pastikan elemen ada di DOM sebelum polling
  if (!badgeEl) return;

  gsSyncPollingInterval = setInterval(async () => {
    try {
      const response = await fetch('{{ route('admin.pengaturan.google-sheets.index') }}', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      const data = await response.json();

      // Update Terakhir Sinkron
      if (lastTimeEl) lastTimeEl.textContent = data.last_sync_at;

      // Update Status Badge
      if (badgeEl) {
        badgeEl.textContent = data.status_badge_text;
        
        // Reset classes
        badgeEl.className = 'badge';
        
        // Set new class
        const status = data.last_sync_status;
        if (status === 'success') {
          badgeEl.classList.add('bg-label-success');
        } else if (status === 'completed_with_errors') {
          badgeEl.classList.add('bg-label-warning');
        } else if (status === 'failed') {
          badgeEl.classList.add('bg-label-danger');
        } else if (status === 'in_progress') {
          badgeEl.classList.add('bg-label-warning');
        } else {
          badgeEl.classList.add('bg-label-secondary');
        }
      }

      // Update Pesan
      if (msgEl) {
        if (data.last_sync_message) {
          msgEl.textContent = data.last_sync_message;
          if (msgContainer) msgContainer.classList.remove('d-none');
        } else {
          if (msgContainer) msgContainer.classList.add('d-none');
        }
      }

      // Update Panel Troubleshooting (TAMBAHAN LOGIC BARU)
      const tsPanel = document.getElementById('sync-troubleshoot-panel');
      const tsStepsEl = document.getElementById('sync-troubleshoot-steps');
      if (tsPanel && tsStepsEl) {
        if (data.last_sync_status === 'failed' || data.last_sync_status === 'completed_with_errors') {
          tsStepsEl.innerHTML = getTroubleshootStepsHtml(data.last_sync_message);
          tsPanel.classList.remove('d-none');

          // Isi daftar unrecognized headers jika ada
          if (data.unrecognized_headers && data.unrecognized_headers.length > 0) {
            const listEl = document.getElementById('troubleshoot-unrecognized-list');
            const listElDynamic = document.getElementById('troubleshoot-unrecognized-list-dynamic');
            const listText = data.unrecognized_headers.join(', ');
            if (listEl) listEl.textContent = listText;
            if (listElDynamic) listElDynamic.textContent = listText;
          }
        } else {
          tsPanel.classList.add('d-none');
        }
      }

      // Hentikan polling jika sudah selesai/gagal
      if (data.last_sync_status !== 'in_progress') {
        clearInterval(gsSyncPollingInterval);
        gsSyncPollingInterval = null;
      }
    } catch (error) {
      console.error('Polling error:', error);
    }
  }, 2500);
}

// Panggil polling saat halaman di-load jika statusnya sedang berjalan
document.addEventListener('DOMContentLoaded', () => {
  const currentStatus = '{{ $setting->last_sync_status ?? "" }}';
  if (currentStatus === 'in_progress') {
    startGsSyncPolling();
  }
});
</script>

{{-- SweetAlert2 --}}
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])

@endsection
