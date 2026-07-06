<div class="set-tab" id="tab-google-sheets-siswa">
  <div class="gs-grid-container">
    
    <!-- KOLOM KIRI (UTAMA): FORM KONFIGURASI -->
    <div class="gs-grid-left">
      <form action="{{ route('admin.pengaturan.google-sheets.update') }}" method="POST" id="gsUpdateFormSiswa">
        @csrf
        
        {{-- ─────────────────────────────
             PANEL 1: Konfigurasi Google Sheets Siswa
        ───────────────────────────── --}}
        <div class="set-panel mb-4">
          <div class="set-panel__head">
            <div class="set-panel__title-wrap">
              <div class="set-panel__icon --primary"><i class="ti tabler-file-spreadsheet"></i></div>
              <div>
                <div class="set-panel__title">Konfigurasi Google Sheets (Siswa)</div>
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
                      id="gsCreateSheetBtnSiswa"
                      onclick="createGsSheetTemplateSiswa()">
                <i class="ti tabler-file-plus"></i>
                <span>Buat Google Sheet Template</span>
              </button>
            </div>

            <div class="set-form-grid">

              <div class="set-field set-field--full">
                <label class="set-label" for="spreadsheet_id_siswa">ID Spreadsheet</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-brand-google"></i></span>
                  <input type="text" class="set-input font-monospace {{ $errors->has('spreadsheet_id') ? 'is-invalid' : '' }}"
                    id="spreadsheet_id_siswa"
                    name="spreadsheet_id"
                    value="{{ old('spreadsheet_id', $setting->spreadsheet_id ?? '') }}"
                    placeholder="12YfG6AYYKHm5TJSoqXBz9Xfk1MUTElXNAFi8ad7CGc"
                    aria-describedby="spreadsheet_id_siswa_help">
                </div>
                <div id="spreadsheet_id_siswa_help" class="text-muted small mt-1">ID spreadsheet dari URL Google Sheets (contoh: 12YfG6AYYKHm5TJSoqXBz9Xfk1MUTElXNAFi8ad7CGc)</div>
                @error('spreadsheet_id')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="set-field set-field--full">
                <label class="set-label" for="sheet_range_siswa">Range Sheet</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-table"></i></span>
                  <input type="text" class="set-input {{ $errors->has('sheet_range') ? 'is-invalid' : '' }}"
                    id="sheet_range_siswa"
                    name="sheet_range"
                    value="{{ old('sheet_range', $setting->sheet_range ?? 'siswa!A:Z') }}"
                    aria-describedby="sheet_range_siswa_help">
                </div>
                <div id="sheet_range_siswa_help" class="text-muted small mt-1">Range data di sheet (contoh: siswa!A:Z)</div>
                @error('sheet_range')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="set-field set-field--full">
                <label class="set-label" for="credentials_json_siswa">Service Account JSON</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
                  <textarea class="set-input font-monospace {{ $errors->has('credentials_json') ? 'is-invalid' : '' }}"
                    id="credentials_json_siswa"
                    name="credentials_json"
                    rows="8"
                    aria-describedby="credentials_json_siswa_help"
                    placeholder="{{ $setting->id ? 'Kosongkan jika tidak ingin mengubah credentials yang sudah tersimpan' : '' }}">{{ old('credentials_json', $setting->id ? '' : '') }}</textarea>
                </div>
                <div id="credentials_json_siswa_help" class="text-muted small mt-1">Upload file JSON Service Account dari Google Cloud Console. Kosongkan jika tidak ingin mengubah.</div>
                @error('credentials_json')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="set-field set-field--full">
                <label class="set-label" for="column_mapping_siswa">Mapping Kolom (JSON)</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-columns"></i></span>
                  <textarea class="set-input font-monospace {{ $errors->has('column_mapping') ? 'is-invalid' : '' }}"
                    id="column_mapping_siswa"
                    name="column_mapping"
                    rows="4"
                    aria-describedby="column_mapping_siswa_help">{{ old('column_mapping', json_encode($setting->column_mapping ?? [], JSON_PRETTY_PRINT)) }}</textarea>
                </div>
                <div id="column_mapping_siswa_help" class="text-muted small mt-1">Mapping kolom Google Sheets ke field database. Format: {"nis":"NIS","nama_lengkap":"Nama Lengkap","nisn":"NISN","jenis_kelamin":"Jenis Kelamin","tempat_lahir":"Tempat Lahir","tanggal_lahir":"Tanggal Lahir","alamat":"Alamat","no_hp":"No HP"}</div>
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
                <span class="text-white small" id="sync-last-time-siswa">{{ $setting->last_sync_at ? \Carbon\Carbon::parse($setting->last_sync_at)->format('d M Y H:i:s') : '-' }}</span>
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
                <span class="badge {{ $badgeClass }}" id="sync-status-badge-siswa">{{ $setting->status_badge_text }}</span>
              </div>
            </div>
            {{-- Selalu render div pesan, tapi set d-none jika pesan kosong --}}
            <div class="set-field set-field--full {{ !$setting->last_sync_message ? 'd-none' : '' }}" id="sync-message-container-siswa">
              <label class="set-label">Pesan</label>
              <p class="text-muted small mt-1 mb-0" id="sync-message-text-siswa">{{ $setting->last_sync_message ?? '' }}</p>
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
              <form action="{{ route('admin.pengaturan.google-sheets.sync-now') }}" method="POST" id="gsSyncNowFormSiswa" class="m-0">
                @csrf
                <button type="button" class="gs-btn-compact gs-btn-compact--warning" id="gsOpenSyncConfirmButtonSiswa" onclick="openGsSyncConfirmModalSiswa()">
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
              <button type="button" class="gs-btn-compact gs-btn-compact--primary" id="gsProcessQueueBtnSiswa" onclick="processGsQueueSiswa()">
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
              <button type="button" class="gs-btn-compact gs-btn-compact--danger" id="gsResetQueueBtnSiswa" onclick="confirmGsResetQueueSiswa()">
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
              <button type="button" class="gs-btn-compact gs-btn-compact--info" id="gsTestBtnSiswa" onclick="openGsTestModalSiswa()">
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
    <div class="set-panel mb-4 {{ !in_array($setting->last_sync_status, ['failed', 'completed_with_errors']) ? 'd-none' : '' }}" id="sync-troubleshoot-panel-siswa" style="border-color: rgba(234, 84, 85, 0.25);">
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
        <div id="sync-troubleshoot-steps-siswa" style="display:flex;flex-direction:column;gap:0.75rem;font-size:0.8rem;color:#94a3b8;">
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
                      <p class="mb-0 mt-1 small">Kolom berikut tidak dikenal: <span id="troubleshoot-unrecognized-list-siswa" class="text-warning fw-bold"></span>. Silakan sesuaikan header Google Sheet dengan template standar yang bisa diunduh di atas.</p>
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
            <div class="set-panel__title">Preview Mapping Kolom (Siswa)</div>
            <div class="set-panel__sub">Deteksi otomatis header Google Sheet dan mapping ke kolom database.</div>
          </div>
        </div>
      </div>
      <div class="set-panel__body">
        <p class="text-muted small mb-3" style="color:#94a3b8;font-size:0.78rem;line-height:1.5;">
          Klik tombol di bawah untuk mendeteksi header dari Google Sheet dan melihat preview mapping kolom.
        </p>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <button type="button" class="gs-action-btn gs-action-btn--primary" id="gsDetectMappingBtnSiswa" onclick="detectGsMappingSiswa()">
            <i class="ti tabler-refresh"></i>
            <span>Deteksi Mapping</span>
          </button>
          <span id="gsDetectMappingSpinnerSiswa" style="display:none;align-self:center;">
            <i class="ti tabler-loader-2 animate-spin" style="font-size:1.1rem;color:var(--das-primary);"></i>
            <span class="small text-muted ms-1">Mendeteksi header...</span>
          </span>
        </div>

        {{-- Wrapper hasil preview (diisi JS) --}}
        <div id="gsMappingResultSiswa" style="display:none;">
          {{-- Tabel Preview --}}
          <div style="overflow-x:auto;border:1px solid var(--das-border);border-radius:var(--das-radius-sm);">
            <table class="gs-preview-table" id="gsMappingTableSiswa">
              <thead>
                <tr>
                  <th style="width:60px;text-align:center;">No</th>
                  <th>Header Sheet</th>
                  <th>Mapping ke Database</th>
                  <th style="width:130px;text-align:center;">Status</th>
                </tr>
              </thead>
              <tbody id="gsMappingTableBodySiswa"></tbody>
            </table>
          </div>

          {{-- Warning unrecognized --}}
          <div id="gsUnrecognizedWarningSiswa" style="display:none;margin-top:0.75rem;padding:0.65rem 1rem;background:rgba(255,159,67,0.08);border:1px solid rgba(255,159,67,0.2);border-radius:var(--das-radius-sm);font-size:0.78rem;color:#fcd34d;">
            <i class="ti tabler-alert-triangle me-1"></i>
            <span id="gsUnrecognizedWarningTextSiswa"></span>
          </div>

          {{-- Ringkasan --}}
          <div id="gsMappingSummarySiswa" style="display:none;margin-top:0.75rem;flex-wrap:wrap;gap:1rem;font-size:0.78rem;">
            <span><span class="text-muted">Total Header:</span> <strong class="text-white" id="gsTotalHeadersSiswa">0</strong></span>
            <span><span class="text-muted">Terdeteksi:</span> <strong class="text-white" id="gsMatchedCountSiswa">0</strong></span>
            <span><span class="text-muted">Tidak Dikenal:</span> <strong class="text-white" id="gsUnrecognizedCountSiswa">0</strong></span>
          </div>
        </div>

        {{-- Error state --}}
        <div id="gsMappingErrorSiswa" style="display:none;padding:1rem;background:rgba(234,84,85,0.06);border:1px solid rgba(234,84,85,0.15);border-radius:var(--das-radius-sm);font-size:0.82rem;color:#fca5a5;">
          <i class="ti tabler-alert-circle me-1"></i>
          <span id="gsMappingErrorTextSiswa"></span>
        </div>
      </div>
    </div>
    @endif
  </div>

  {{-- ── MODAL KONFIRMASI SINKRON ── --}}
  <div id="gsSyncConfirmModalSiswa" class="sync-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="gsSyncConfirmTitleSiswa" aria-describedby="gsSyncConfirmDescriptionSiswa" hidden>
    <div class="sync-confirm-modal__backdrop" onclick="closeGsSyncConfirmModalSiswa()" tabindex="-1"></div>
    <div class="sync-confirm-modal__content" role="document">
      <div class="sync-confirm-modal__header">
        <div class="sync-confirm-modal__icon"><i class="ti tabler-alert-circle"></i></div>
        <div>
          <h2 id="gsSyncConfirmTitleSiswa">Konfirmasi Sinkronisasi</h2>
          <p id="gsSyncConfirmDescriptionSiswa">Apakah Anda yakin ingin menjalankan sinkronisasi data siswa dari Google Sheets sekarang? Proses ini dapat memakan beberapa saat dan akan menarik data dari spreadsheet.</p>
        </div>
      </div>
      <div class="sync-confirm-modal__actions">
        <button type="button" class="sync-confirm-modal__cancel-btn" onclick="closeGsSyncConfirmModalSiswa()">Batal</button>
        <button type="button" class="sync-confirm-modal__confirm-btn" id="gsSyncConfirmButtonSiswa" onclick="submitGsSyncNowSiswa()">
          <span id="gsSyncBtnTextSiswa">Konfirmasi</span>
          <span id="gsSyncBtnSpinnerSiswa" style="display:none;"><i class="ti tabler-loader-2 animate-spin"></i></span>
        </button>
      </div>
    </div>
  </div>

  {{-- ── MODAL SUKSES SINKRON ── --}}
  <div id="gsSyncSuccessModalSiswa" class="sync-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="gsSyncSuccessTitleSiswa" aria-describedby="gsSyncSuccessDescriptionSiswa" hidden>
    <div class="sync-confirm-modal__backdrop" onclick="closeGsSyncSuccessModalSiswa()" tabindex="-1"></div>
    <div class="sync-confirm-modal__content sync-confirm-modal__content--success" role="document">
      <div class="sync-confirm-modal__header text-center">
        <div class="sync-success-icon-wrap mb-3">
          <div class="sync-success-icon-ring"></div>
          <div class="sync-success-icon"><i class="ti tabler-circle-check"></i></div>
        </div>
        <h2 id="gsSyncSuccessTitleSiswa" class="text-gradient-success">Berhasil Dijadwalkan!</h2>
        <p id="gsSyncSuccessDescriptionSiswa" class="mt-2">Sinkronisasi Google Sheets telah dijadwalkan dan akan diproses di latar belakang secara bertahap (±50 baris per tahap). Anda dapat menutup halaman ini atau melanjutkan aktivitas lainnya. Status sinkronisasi dapat dicek kembali di halaman ini.</p>
      </div>
      <div class="sync-confirm-modal__actions justify-content-center">
        <button type="button" class="sync-confirm-modal__confirm-btn sync-confirm-modal__confirm-btn--success" onclick="closeGsSyncSuccessModalSiswa()">
          Selesai
        </button>
      </div>
    </div>
  </div>

  {{-- ── MODAL TEST KONEKSI ── --}}
  <div id="gsTestModalSiswa" class="sync-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="gsTestTitleSiswa" aria-describedby="gsTestDescriptionSiswa" hidden>
    <div class="sync-confirm-modal__backdrop" onclick="closeGsTestModalSiswa()" tabindex="-1"></div>
    <div class="sync-confirm-modal__content" role="document" id="gsTestModalContentSiswa">
      <div class="sync-confirm-modal__header text-center">
        <div class="mb-3" id="gsTestIconWrapSiswa">
          <div id="gsTestLoadingSiswa" style="display:none;">
            <div style="width:56px;height:56px;margin:0 auto;border-radius:50%;background:rgba(0,207,232,0.12);display:flex;align-items:center;justify-content:center;color:var(--das-info);font-size:2rem;">
              <i class="ti tabler-loader-2 animate-spin"></i>
            </div>
          </div>
          <div id="gsTestIconSuccessSiswa" style="display:none;">
            <div class="sync-success-icon-wrap">
              <div class="sync-success-icon-ring"></div>
              <div class="sync-success-icon" style="background:var(--das-success);"><i class="ti tabler-circle-check"></i></div>
            </div>
          </div>
          <div id="gsTestIconErrorSiswa" style="display:none;">
            <div style="width:56px;height:56px;margin:0 auto;border-radius:50%;background:rgba(234,84,85,0.12);display:flex;align-items:center;justify-content:center;color:var(--das-danger);font-size:2rem;">
              <i class="ti tabler-alert-circle"></i>
            </div>
          </div>
        </div>
        <h2 id="gsTestTitleSiswa" style="font-size:1.05rem;font-weight:700;color:#e2e8f0;margin:0;">Test Koneksi</h2>
        <p id="gsTestDescriptionSiswa" class="mt-2" style="color:#94a3b8;font-size:0.88rem;line-height:1.6;margin:0;">Menguji koneksi ke Google Sheets...</p>
      </div>
      <div class="sync-confirm-modal__actions justify-content-center" id="gsTestActionsSiswa" style="display:none;">
        <button type="button" class="sync-confirm-modal__cancel-btn" onclick="closeGsTestModalSiswa()">Tutup</button>
      </div>
    </div>
  </div>

  <style>
  /* Google Sheets Layout Styles */
  .gs-grid-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    align-items: start;
  }
  @media (min-width: 992px) {
    .gs-grid-container {
      grid-template-columns: 1fr 360px;
    }
  }
  .gs-grid-left, .gs-grid-right {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
  }
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
  .gs-action-btn--primary { background: var(--das-primary); color: #fff; }
  .gs-action-btn--primary:hover { background: #6259e8; box-shadow: 0 6px 16px rgba(115,103,240,0.35); }
  .gs-action-btn--success { background: var(--das-success); color: #fff; }
  .gs-action-btn--success:hover { background: #23ad60; box-shadow: 0 6px 16px rgba(40,199,111,0.35); }
  .gs-action-btn--info { background: var(--das-info); color: #fff; }
  .gs-action-btn--info:hover { background: #00b8d4; box-shadow: 0 6px 16px rgba(0,207,232,0.35); }
  
  .gs-action-list { display: flex; flex-direction: column; gap: 0.75rem; }
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
  .gs-action-item__header { display: flex; align-items: center; gap: 0.75rem; }
  .gs-action-item__icon {
    width: 32px; height: 32px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
  }
  .gs-action-item__icon.--warning { background: var(--das-warning-soft); color: var(--das-warning); }
  .gs-action-item__icon.--info { background: var(--das-info-soft); color: var(--das-info); }
  .gs-action-item__icon.--danger { background: var(--das-danger-soft); color: var(--das-danger); }
  .gs-action-item__icon.--primary { background: var(--das-primary-soft); color: var(--das-primary); }
  .gs-action-item__title { font-size: 0.82rem; font-weight: 700; color: #e2e8f0; }
  .gs-action-item__sub { font-size: 0.68rem; color: #64748b; margin-top: 1px; }
  .gs-btn-compact {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
    width: 100%; border: none; border-radius: 6px;
    font-size: 0.75rem; font-weight: 700; padding: 0.55rem 1rem;
    cursor: pointer; transition: all 0.2s ease;
  }
  .gs-btn-compact--warning { background: var(--das-warning); color: #1a1a1a; }
  .gs-btn-compact--warning:hover { background: #f0920a; transform: translateY(-1px); }
  .gs-btn-compact--info { background: var(--das-info); color: #1a1a1a; }
  .gs-btn-compact--info:hover { background: #00b8d4; transform: translateY(-1px); }
  .gs-btn-compact--danger { background: var(--das-danger); color: #fff; }
  .gs-btn-compact--danger:hover { background: #d32f2f; transform: translateY(-1px); }
  .gs-btn-compact--primary { background: var(--das-primary); color: #fff; }
  .gs-btn-compact--primary:hover { background: #5a4ee3; transform: translateY(-1px); }

  .gs-preview-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
  .gs-preview-table thead th {
    background: rgba(255,255,255,0.04); color: #64748b; font-weight: 700;
    text-transform: uppercase; font-size: 0.62rem; letter-spacing: 0.8px;
    padding: 0.65rem 1rem; border-bottom: 1px solid var(--das-border); text-align: left;
  }
  .gs-preview-table tbody td { padding: 0.55rem 1rem; border-bottom: 1px solid var(--das-border); color: #cbd5e1; vertical-align: middle; }
  .gs-preview-table tbody tr:last-child td { border-bottom: none; }
  .gs-preview-table tbody tr:hover { background: rgba(255,255,255,0.02); }
  .gs-preview-table tbody tr td:first-child { text-align: center; color: #64748b; }
  .gs-preview-table .gs-status-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.2rem 0.55rem; border-radius: 20px; font-size: 0.68rem; font-weight: 600; }
  .gs-preview-table .gs-status-badge--matched { background: rgba(40,199,111,0.12); color: #4ade80; }
  .gs-preview-table .gs-status-badge--unrecognized { background: rgba(255,159,67,0.12); color: #fcd34d; }
  .gs-mapped-col { font-family: 'Courier New', monospace; color: #e2e8f0; }
  .gs-unmapped-col { color: #64748b; font-style: italic; }
  </style>

  <script>
  function openGsSyncConfirmModalSiswa() {
    const modal = document.getElementById('gsSyncConfirmModalSiswa');
    const confirmButton = document.getElementById('gsSyncConfirmButtonSiswa');
    if (!modal || !confirmButton) return true;
    modal.hidden = false;
    modal.style.pointerEvents = 'auto';
    setTimeout(() => confirmButton.focus(), 50);
  }

  function closeGsSyncConfirmModalSiswa() {
    const modal = document.getElementById('gsSyncConfirmModalSiswa');
    const openButton = document.getElementById('gsOpenSyncConfirmButtonSiswa');
    if (!modal) return;
    modal.hidden = true;
    modal.style.pointerEvents = 'none';
    if (openButton) openButton.focus();
  }

  function openGsSyncSuccessModalSiswa() {
    const modal = document.getElementById('gsSyncSuccessModalSiswa');
    if (modal) {
      modal.hidden = false;
      modal.style.pointerEvents = 'auto';
    }
  }

  function closeGsSyncSuccessModalSiswa() {
    const modal = document.getElementById('gsSyncSuccessModalSiswa');
    if (modal) {
      modal.hidden = true;
      modal.style.pointerEvents = 'none';
    }
  }

  async function submitGsSyncNowSiswa() {
    const form = document.getElementById('gsSyncNowFormSiswa');
    const btn = document.getElementById('gsSyncConfirmButtonSiswa');
    const btnText = document.getElementById('gsSyncBtnTextSiswa');
    const btnSpinner = document.getElementById('gsSyncBtnSpinnerSiswa');
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
        closeGsSyncConfirmModalSiswa();
        setTimeout(() => {
          openGsSyncSuccessModalSiswa();
          startGsSyncPollingSiswa();
        }, 300);
      } else {
        showGsDynamicToastSiswa('danger', result.message || 'Terjadi kesalahan saat sinkronisasi.');
      }
    } catch (error) {
      console.error('Sync error:', error);
      showGsDynamicToastSiswa('danger', 'Gagal menghubungi server. Silakan coba lagi.');
    } finally {
      btn.disabled = false;
      btnText.style.display = 'inline-block';
      btnSpinner.style.display = 'none';
    }
  }

  function showGsDynamicToastSiswa(type, message) {
    // Dynamic toast helper for Siswa
    const toastId = type === 'success' ? 'gsSyncSuccessToastSiswa' : 'gsSyncErrorToastSiswa';
    let toast = document.getElementById(toastId);
    if (!toast) {
      toast = document.getElementById(type === 'success' ? 'gsSyncSuccessToast' : 'gsSyncErrorToast');
    }
    if (toast) {
      const msgEl = toast.querySelector('.set-toast__msg');
      if (msgEl) msgEl.textContent = message;
      toast.style.display = 'flex';
      setTimeout(() => { toast.style.display = 'none'; }, 5000);
    } else {
      alert(message);
    }
  }

  async function processGsQueueSiswa() {
    const btn = document.getElementById('gsProcessQueueBtnSiswa');
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
        await refreshGsSyncStatusSiswa();
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
        showGsDynamicToastSiswa('danger', result.message || 'Gagal memproses antrian.');
      }
    } catch (error) {
      console.error('Process queue error:', error);
      showGsDynamicToastSiswa('danger', 'Gagal menghubungi server. Silakan coba lagi.');
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  }

  async function refreshGsSyncStatusSiswa() {
    try {
      const response = await fetch('{{ route("admin.pengaturan.google-sheets.index") }}', {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      const result = await response.json();
      
      if (result.last_sync_at) {
        document.getElementById('sync-last-time-siswa').textContent = result.last_sync_at;
      }
      if (result.status_badge_text) {
        const badge = document.getElementById('sync-status-badge-siswa');
        badge.textContent = result.status_badge_text;
        badge.className = 'badge ' + (result.last_sync_status === 'success' ? 'bg-label-success' : 
                                       result.last_sync_status === 'in_progress' ? 'bg-label-warning' : 
                                       result.last_sync_status === 'failed' ? 'bg-label-danger' : 'bg-label-secondary');
      }
      if (result.last_sync_message) {
        const msgContainer = document.getElementById('sync-message-container-siswa');
        if (msgContainer) {
          msgContainer.classList.remove('d-none');
          document.getElementById('sync-message-text-siswa').textContent = result.last_sync_message;
        }
      }
    } catch (e) {
      console.warn('Failed to refresh sync status:', e);
    }
  }

  function confirmGsResetQueueSiswa() {
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
        submitGsResetQueueSiswa();
      }
    });
  }

  async function submitGsResetQueueSiswa() {
    const btn = document.getElementById('gsResetQueueBtnSiswa');
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
        await refreshGsSyncStatusSiswa();
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
        showGsDynamicToastSiswa('danger', result.message || 'Gagal mereset antrian.');
      }
    } catch (error) {
      console.error('Reset queue error:', error);
      showGsDynamicToastSiswa('danger', 'Gagal menghubungi server. Silakan coba lagi.');
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  }

  function openGsTestModalSiswa() {
    const modal = document.getElementById('gsTestModalSiswa');
    if (!modal) return;

    document.getElementById('gsTestLoadingSiswa').style.display = 'block';
    document.getElementById('gsTestIconSuccessSiswa').style.display = 'none';
    document.getElementById('gsTestIconErrorSiswa').style.display = 'none';
    document.getElementById('gsTestActionsSiswa').style.display = 'none';
    document.getElementById('gsTestTitleSiswa').textContent = 'Test Koneksi';
    document.getElementById('gsTestDescriptionSiswa').textContent = 'Menguji koneksi ke Google Sheets...';
    document.getElementById('gsTestModalContentSiswa').className = 'sync-confirm-modal__content';

    modal.hidden = false;
    modal.style.pointerEvents = 'auto';

    submitGsTestSiswa();
  }

  function closeGsTestModalSiswa() {
    const modal = document.getElementById('gsTestModalSiswa');
    if (modal) {
      modal.hidden = true;
      modal.style.pointerEvents = 'none';
    }
  }

  async function submitGsTestSiswa() {
    const spreadsheetId = document.getElementById('spreadsheet_id_siswa').value;
    const credentialsJson = document.getElementById('credentials_json_siswa').value;
    const sheetRange = document.getElementById('sheet_range_siswa').value;
    const hasSaved = {{ $setting->id ? 'true' : 'false' }};

    if (!spreadsheetId) {
      document.getElementById('gsTestLoadingSiswa').style.display = 'none';
      document.getElementById('gsTestIconErrorSiswa').style.display = 'block';
      document.getElementById('gsTestTitleSiswa').textContent = 'Validasi Gagal';
      document.getElementById('gsTestDescriptionSiswa').textContent = 'Harap isi ID Spreadsheet terlebih dahulu.';
      document.getElementById('gsTestActionsSiswa').style.display = 'flex';
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
          sheet_range: sheetRange || 'siswa!A1:Z1'
        })
      });

      const result = await response.json();
      document.getElementById('gsTestLoadingSiswa').style.display = 'none';

      if (result.success) {
        document.getElementById('gsTestIconSuccessSiswa').style.display = 'block';
        document.getElementById('gsTestTitleSiswa').textContent = 'Koneksi Berhasil!';
        document.getElementById('gsTestDescriptionSiswa').textContent = result.message || 'Koneksi ke Google Sheets berhasil.';
        document.getElementById('gsTestModalContentSiswa').className = 'sync-confirm-modal__content sync-confirm-modal__content--success';
      } else {
        document.getElementById('gsTestIconErrorSiswa').style.display = 'block';
        document.getElementById('gsTestTitleSiswa').textContent = 'Koneksi Gagal';
        document.getElementById('gsTestDescriptionSiswa').textContent = result.message || 'Tidak dapat terhubung ke Google Sheets.';
      }
    } catch (error) {
      document.getElementById('gsTestLoadingSiswa').style.display = 'none';
      document.getElementById('gsTestIconErrorSiswa').style.display = 'block';
      document.getElementById('gsTestTitleSiswa').textContent = 'Koneksi Gagal';
      document.getElementById('gsTestDescriptionSiswa').textContent = 'Gagal menghubungi server.';
      console.error('Test connection error:', error);
    }

    document.getElementById('gsTestActionsSiswa').style.display = 'flex';
  }

  async function createGsSheetTemplateSiswa() {
    const btn = document.getElementById('gsCreateSheetBtnSiswa');
    const originalHtml = btn.innerHTML;
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
        const inputEl = document.getElementById('spreadsheet_id_siswa');
        if (inputEl && result.spreadsheet_id) {
          inputEl.value = result.spreadsheet_id;
        }

        Swal.fire({
          icon: 'success',
          title: 'Template Berhasil Dibuat!',
          html: '<div style="text-align:center">Google Sheet template telah berhasil dibuat dengan kolom otomatis.</div>',
          confirmButtonText: '<i class="ti tabler-check"></i> Selesai',
          showCancelButton: true,
          cancelButtonText: '<i class="ti tabler-external-link"></i> Buka Google Sheet',
          cancelButtonColor: '#28c76f',
          buttonsStyling: false,
          customClass: {
            popup: 'das-swal-popup',
            title: 'das-swal-title',
            htmlContainer: 'das-swal-html',
            actions: 'das-swal-actions',
            confirmButton: 'das-swal-cancel-btn',
            cancelButton: 'das-swal-confirm-btn',
            icon: 'das-swal-icon-success'
          }
        }).then((resultSwal) => {
          if (resultSwal.dismiss === Swal.DismissReason.cancel && result.url) {
            window.open(result.url, '_blank');
          }
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: result.message || 'Gagal membuat template.'
        });
      }
    } catch (error) {
      console.error('Create template error:', error);
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  }

  async function detectGsMappingSiswa() {
    const btn = document.getElementById('gsDetectMappingBtnSiswa');
    const spinner = document.getElementById('gsDetectMappingSpinnerSiswa');
    const resultDiv = document.getElementById('gsMappingResultSiswa');
    const errorDiv = document.getElementById('gsMappingErrorSiswa');
    const tableBody = document.getElementById('gsMappingTableBodySiswa');
    const warningDiv = document.getElementById('gsUnrecognizedWarningSiswa');
    const warningText = document.getElementById('gsUnrecognizedWarningTextSiswa');

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
        document.getElementById('gsMappingErrorTextSiswa').textContent = json.message || 'Gagal preview mapping.';
        return;
      }

      const data = json.data;
      let html = '';
      data.preview.forEach((item, index) => {
        const no = index + 1;
        const isMatched = item.status === 'matched';
        html += `<tr>
          <td>${no}</td>
          <td>${escapeHtml(item.header)}</td>
          <td>${isMatched ? '<span class="gs-mapped-col">' + escapeHtml(item.mapped_to) + '</span>' : '<span class="gs-unmapped-col">—</span>'}</td>
          <td style="text-align:center;">
            ${isMatched ? '<span class="gs-status-badge gs-status-badge--matched"><i class="ti tabler-circle-check"></i> Terdeteksi</span>' : '<span class="gs-status-badge gs-status-badge--unrecognized"><i class="ti tabler-alert-triangle"></i> Tidak dikenal</span>'}
          </td>
        </tr>`;
      });

      tableBody.innerHTML = html;
      document.getElementById('gsTotalHeadersSiswa').textContent = data.total_headers || data.preview.length;
      document.getElementById('gsMatchedCountSiswa').textContent = data.matched || 0;
      const unrecognizedCount = (data.unrecognized && data.unrecognized.length) || 0;
      document.getElementById('gsUnrecognizedCountSiswa').textContent = unrecognizedCount;

      if (unrecognizedCount > 0) {
        warningText.textContent = unrecognizedCount + ' kolom tidak dikenal. Sinkronisasi tetap berjalan untuk kolom yang dikenal.';
        warningDiv.style.display = 'flex';
      }

      resultDiv.style.display = 'block';
      document.getElementById('gsMappingSummarySiswa').style.display = 'flex';
    } catch (error) {
      console.error(error);
      errorDiv.style.display = 'flex';
      document.getElementById('gsMappingErrorTextSiswa').textContent = 'Terjadi kesalahan.';
    } finally {
      btn.disabled = false;
      spinner.style.display = 'none';
    }
  }

  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
  }

  let gsSyncPollingIntervalSiswa = null;

  function startGsSyncPollingSiswa() {
    if (gsSyncPollingIntervalSiswa) return;
    const lastTimeEl = document.getElementById('sync-last-time-siswa');
    const badgeEl = document.getElementById('sync-status-badge-siswa');
    const msgContainer = document.getElementById('sync-message-container-siswa');
    const msgEl = document.getElementById('sync-message-text-siswa');

    if (!badgeEl) return;

    gsSyncPollingIntervalSiswa = setInterval(async () => {
      try {
        const response = await fetch('{{ route('admin.pengaturan.google-sheets.index') }}', {
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();

        if (lastTimeEl) lastTimeEl.textContent = data.last_sync_at;
        if (badgeEl) {
          badgeEl.textContent = data.status_badge_text;
          badgeEl.className = 'badge';
          const status = data.last_sync_status;
          if (status === 'success') badgeEl.classList.add('bg-label-success');
          else if (status === 'in_progress') badgeEl.classList.add('bg-label-warning');
          else if (status === 'failed') badgeEl.classList.add('bg-label-danger');
          else badgeEl.classList.add('bg-label-secondary');
        }

        if (msgEl && data.last_sync_message) {
          msgEl.textContent = data.last_sync_message;
          if (msgContainer) msgContainer.classList.remove('d-none');
        }

        if (data.last_sync_status !== 'in_progress') {
          clearInterval(gsSyncPollingIntervalSiswa);
          gsSyncPollingIntervalSiswa = null;
        }
      } catch (e) {
        console.error(e);
      }
    }, 2500);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const currentStatus = '{{ $setting->last_sync_status ?? "" }}';
    if (currentStatus === 'in_progress') {
      startGsSyncPollingSiswa();
    }
  });

  window.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeGsSyncConfirmModalSiswa();
      closeGsSyncSuccessModalSiswa();
      closeGsTestModalSiswa();
    }
  });
  </script>
</div>
