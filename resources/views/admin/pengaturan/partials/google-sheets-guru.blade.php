<div class="set-tab" id="tab-google-sheets-guru">
  <div class="gs-grid-container">
    
    <!-- KOLOM KIRI (UTAMA): FORM KONFIGURASI -->
    <div class="gs-grid-left">
      <form action="{{ route('admin.pengaturan.google-sheets-guru.update') }}" method="POST" id="gsUpdateFormGuru">
        @csrf
        
        {{-- ─────────────────────────────
             PANEL 1: Konfigurasi Google Sheets Guru
        ───────────────────────────── --}}
        <div class="set-panel mb-4">
          <div class="set-panel__head">
            <div class="set-panel__title-wrap">
              <div class="set-panel__icon --primary"><i class="ti tabler-file-spreadsheet"></i></div>
              <div>
                <div class="set-panel__title">Konfigurasi Google Sheets (Guru)</div>
                <div class="set-panel__sub">Atur koneksi spreadsheet Google Sheets untuk sinkronisasi data guru dan pengguna.</div>
              </div>
            </div>
          </div>
          <div class="set-panel__body">
            {{-- Action Bar: Download Template & Buat Google Sheet --}}
            <div class="d-flex flex-wrap gap-2 mb-4 pb-3" style="border-bottom: 1px solid var(--das-border);">
              <a href="{{ route('admin.pengaturan.google-sheets-guru.template.download') }}"
                 class="gs-action-btn gs-action-btn--info">
                <i class="ti tabler-download"></i>
                <span>Download Template Excel</span>
              </a>
              <button type="button"
                      class="gs-action-btn gs-action-btn--success"
                      id="gsCreateSheetBtnGuru"
                      onclick="createGsSheetTemplateGuru()">
                <i class="ti tabler-file-plus"></i>
                <span>Buat Google Sheet Template</span>
              </button>
            </div>

            <div class="set-form-grid">

              <div class="set-field set-field--full">
                <label class="set-label" for="spreadsheet_id_guru">ID Spreadsheet</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-brand-google"></i></span>
                  <input type="text" class="set-input font-monospace {{ $errors->has('spreadsheet_id') ? 'is-invalid' : '' }}"
                    id="spreadsheet_id_guru"
                    name="spreadsheet_id"
                    value="{{ old('spreadsheet_id', $guruSetting->spreadsheet_id ?? '') }}"
                    placeholder="1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms"
                    aria-describedby="spreadsheet_id_guru_help">
                </div>
                <div id="spreadsheet_id_guru_help" class="text-muted small mt-1">ID spreadsheet dari URL Google Sheets (contoh: 1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms)</div>
                @error('spreadsheet_id')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="set-field set-field--full">
                <label class="set-label" for="sheet_range_guru">Range Sheet</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-table"></i></span>
                  <input type="text" class="set-input {{ $errors->has('sheet_range') ? 'is-invalid' : '' }}"
                    id="sheet_range_guru"
                    name="sheet_range"
                    value="{{ old('sheet_range', $guruSetting->sheet_range ?? 'Sheet1!A:Z') }}"
                    aria-describedby="sheet_range_guru_help">
                </div>
                <div id="sheet_range_guru_help" class="text-muted small mt-1">Range data di sheet (contoh: Sheet1!A:Z)</div>
                @error('sheet_range')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="set-field set-field--full">
                <label class="set-label" for="credentials_json_guru">Service Account JSON</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
                  <textarea class="set-input font-monospace {{ $errors->has('credentials_json') ? 'is-invalid' : '' }}"
                    id="credentials_json_guru"
                    name="credentials_json"
                    rows="8"
                    aria-describedby="credentials_json_guru_help"
                    placeholder="{{ $guruSetting->id ? 'Kosongkan jika tidak ingin mengubah credentials yang sudah tersimpan' : '' }}">{{ old('credentials_json', $guruSetting->id ? '' : '') }}</textarea>
                </div>
                <div id="credentials_json_guru_help" class="text-muted small mt-1">Upload file JSON Service Account dari Google Cloud Console. Kosongkan jika tidak ingin mengubah.</div>
                @error('credentials_json')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="set-field set-field--full">
                <label class="set-label" for="column_mapping_guru">Mapping Kolom (JSON)</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-columns"></i></span>
                  <textarea class="set-input font-monospace {{ $errors->has('column_mapping') ? 'is-invalid' : '' }}"
                    id="column_mapping_guru"
                    name="column_mapping"
                    rows="4"
                    aria-describedby="column_mapping_guru_help">{{ old('column_mapping', json_encode($guruSetting->column_mapping ?? [], JSON_PRETTY_PRINT)) }}</textarea>
                </div>
                <div id="column_mapping_guru_help" class="text-muted small mt-1">Mapping kolom Google Sheets ke field database. Format: {"nip":"NIP","nama_lengkap":"Nama Lengkap","jenis_kelamin":"Jenis Kelamin","mata_pelajaran":"Mata Pelajaran","jabatan":"Jabatan","no_hp":"No HP"}</div>
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
      @if($guruSetting->id)
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
                <span class="text-white small" id="sync-last-time-guru">{{ $guruSetting->last_sync_at ? \Carbon\Carbon::parse($guruSetting->last_sync_at)->format('d M Y H:i:s') : '-' }}</span>
              </div>
            </div>
            <div class="set-field">
              <label class="set-label">Status</label>
              <div class="mt-1">
                @php
                  $badgeClass = match($guruSetting->last_sync_status) {
                    'success' => 'bg-label-success',
                    'completed_with_errors' => 'bg-label-warning',
                    'failed' => 'bg-label-danger',
                    'in_progress' => 'bg-label-warning',
                    default => 'bg-label-secondary',
                  };
                @endphp
                <span class="badge {{ $badgeClass }}" id="sync-status-badge-guru">{{ $guruSetting->status_badge_text }}</span>
              </div>
            </div>
            {{-- Selalu render div pesan, tapi set d-none jika pesan kosong --}}
            <div class="set-field set-field--full {{ !$guruSetting->last_sync_message ? 'd-none' : '' }}" id="sync-message-container-guru">
              <label class="set-label">Pesan</label>
              <p class="text-muted small mt-1 mb-0" id="sync-message-text-guru">{{ $guruSetting->last_sync_message ?? '' }}</p>
            </div>
          </div>
        </div>
      </div>
      @endif

      <!-- Panel 3: Aksi Cepat (Quick Actions) -->
      @if($guruSetting->id)
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
                  <div class="gs-action-item__sub">Tarik data guru terbaru ke antrian.</div>
                </div>
              </div>
              <form action="{{ route('admin.pengaturan.google-sheets-guru.sync-now') }}" method="POST" id="gsSyncNowFormGuru" class="m-0">
                @csrf
                <button type="button" class="gs-btn-compact gs-btn-compact--warning" id="gsOpenSyncConfirmButtonGuru" onclick="openGsSyncConfirmModalGuru()">
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
              <button type="button" class="gs-btn-compact gs-btn-compact--primary" id="gsProcessQueueBtnGuru" onclick="processGsQueueGuru()">
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
              <button type="button" class="gs-btn-compact gs-btn-compact--danger" id="gsResetQueueBtnGuru" onclick="confirmGsResetQueueGuru()">
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
              <button type="button" class="gs-btn-compact gs-btn-compact--info" id="gsTestBtnGuru" onclick="openGsTestModalGuru()">
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
    @if($guruSetting->id)
    <div class="set-panel mb-4 {{ !in_array($guruSetting->last_sync_status, ['failed', 'completed_with_errors']) ? 'd-none' : '' }}" id="sync-troubleshoot-panel-guru" style="border-color: rgba(234, 84, 85, 0.25);">
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
        <div id="sync-troubleshoot-steps-guru" style="display:flex;flex-direction:column;gap:0.75rem;font-size:0.8rem;color:#94a3b8;">
          @if(in_array($guruSetting->last_sync_status, ['failed', 'completed_with_errors']))
            @php
              $msg = $guruSetting->last_sync_message ?? '';
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
                      <p class="mb-0 mt-1 small">Kolom berikut tidak dikenal: <span id="troubleshoot-unrecognized-list-guru" class="text-warning fw-bold"></span>. Silakan sesuaikan header Google Sheet dengan template standar yang bisa diunduh di atas.</p>
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
    @if($guruSetting->id)
    <div class="set-panel mb-4">
      <div class="set-panel__head">
        <div class="set-panel__title-wrap">
          <div class="set-panel__icon --primary"><i class="ti tabler-columns"></i></div>
          <div>
            <div class="set-panel__title">Preview Mapping Kolom (Guru)</div>
            <div class="set-panel__sub">Deteksi otomatis header Google Sheet dan mapping ke kolom database.</div>
          </div>
        </div>
      </div>
      <div class="set-panel__body">
        <p class="text-muted small mb-3" style="color:#94a3b8;font-size:0.78rem;line-height:1.5;">
          Klik tombol di bawah untuk mendeteksi header dari Google Sheet dan melihat preview mapping kolom.
        </p>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <button type="button" class="gs-action-btn gs-action-btn--primary" id="gsDetectMappingBtnGuru" onclick="detectGsMappingGuru()">
            <i class="ti tabler-refresh"></i>
            <span>Deteksi Mapping</span>
          </button>
          <span id="gsDetectMappingSpinnerGuru" style="display:none;align-self:center;">
            <i class="ti tabler-loader-2 animate-spin" style="font-size:1.1rem;color:var(--das-primary);"></i>
            <span class="small text-muted ms-1">Mendeteksi header...</span>
          </span>
        </div>

        {{-- Wrapper hasil preview (diisi JS) --}}
        <div id="gsMappingResultGuru" style="display:none;">
          {{-- Tabel Preview --}}
          <div style="overflow-x:auto;border:1px solid var(--das-border);border-radius:var(--das-radius-sm);">
            <table class="gs-preview-table" id="gsMappingTableGuru">
              <thead>
                <tr>
                  <th style="width:60px;text-align:center;">No</th>
                  <th>Header Sheet</th>
                  <th>Mapping ke Database</th>
                  <th style="width:130px;text-align:center;">Status</th>
                </tr>
              </thead>
              <tbody id="gsMappingTableBodyGuru"></tbody>
            </table>
          </div>

          {{-- Warning unrecognized --}}
          <div id="gsUnrecognizedWarningGuru" style="display:none;margin-top:0.75rem;padding:0.65rem 1rem;background:rgba(255,159,67,0.08);border:1px solid rgba(255,159,67,0.2);border-radius:var(--das-radius-sm);font-size:0.78rem;color:#fcd34d;">
            <i class="ti tabler-alert-triangle me-1"></i>
            <span id="gsUnrecognizedWarningTextGuru"></span>
          </div>

          {{-- Ringkasan --}}
          <div id="gsMappingSummaryGuru" style="display:none;margin-top:0.75rem;flex-wrap:wrap;gap:1rem;font-size:0.78rem;">
            <span><span class="text-muted">Total Header:</span> <strong class="text-white" id="gsTotalHeadersGuru">0</strong></span>
            <span><span class="text-muted">Terdeteksi:</span> <strong class="text-white" id="gsMatchedCountGuru">0</strong></span>
            <span><span class="text-muted">Tidak Dikenal:</span> <strong class="text-white" id="gsUnrecognizedCountGuru">0</strong></span>
          </div>
        </div>

        {{-- Error state --}}
        <div id="gsMappingErrorGuru" style="display:none;padding:1rem;background:rgba(234,84,85,0.06);border:1px solid rgba(234,84,85,0.15);border-radius:var(--das-radius-sm);font-size:0.82rem;color:#fca5a5;">
          <i class="ti tabler-alert-circle me-1"></i>
          <span id="gsMappingErrorTextGuru"></span>
        </div>
      </div>
    </div>
    @endif
  </div>

  {{-- ── MODAL KONFIRMASI SINKRON ── --}}
  <div id="gsSyncConfirmModalGuru" class="sync-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="gsSyncConfirmTitleGuru" aria-describedby="gsSyncConfirmDescriptionGuru" hidden>
    <div class="sync-confirm-modal__backdrop" onclick="closeGsSyncConfirmModalGuru()" tabindex="-1"></div>
    <div class="sync-confirm-modal__content" role="document">
      <div class="sync-confirm-modal__header">
        <div class="sync-confirm-modal__icon"><i class="ti tabler-alert-circle"></i></div>
        <div>
          <h2 id="gsSyncConfirmTitleGuru">Konfirmasi Sinkronisasi</h2>
          <p id="gsSyncConfirmDescriptionGuru">Apakah Anda yakin ingin menjalankan sinkronisasi data guru dari Google Sheets sekarang? Proses ini dapat memakan beberapa saat dan akan menarik data dari spreadsheet.</p>
        </div>
      </div>
      <div class="sync-confirm-modal__actions">
        <button type="button" class="sync-confirm-modal__cancel-btn" onclick="closeGsSyncConfirmModalGuru()">Batal</button>
        <button type="button" class="sync-confirm-modal__confirm-btn" id="gsSyncConfirmButtonGuru" onclick="submitGsSyncNowGuru()">
          <span id="gsSyncBtnTextGuru">Konfirmasi</span>
          <span id="gsSyncBtnSpinnerGuru" style="display:none;"><i class="ti tabler-loader-2 animate-spin"></i></span>
        </button>
      </div>
    </div>
  </div>

  {{-- ── MODAL SUKSES SINKRON ── --}}
  <div id="gsSyncSuccessModalGuru" class="sync-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="gsSyncSuccessTitleGuru" aria-describedby="gsSyncSuccessDescriptionGuru" hidden>
    <div class="sync-confirm-modal__backdrop" onclick="closeGsSyncSuccessModalGuru()" tabindex="-1"></div>
    <div class="sync-confirm-modal__content sync-confirm-modal__content--success" role="document">
      <div class="sync-confirm-modal__header text-center">
        <div class="sync-success-icon-wrap mb-3">
          <div class="sync-success-icon-ring"></div>
          <div class="sync-success-icon"><i class="ti tabler-circle-check"></i></div>
        </div>
        <h2 id="gsSyncSuccessTitleGuru" class="text-gradient-success">Berhasil Dijadwalkan!</h2>
        <p id="gsSyncSuccessDescriptionGuru" class="mt-2">Sinkronisasi Google Sheets telah dijadwalkan dan akan diproses di latar belakang secara bertahap (±50 baris per tahap). Anda dapat menutup halaman ini atau melanjutkan aktivitas lainnya. Status sinkronisasi dapat dicek kembali di halaman ini.</p>
      </div>
      <div class="sync-confirm-modal__actions justify-content-center">
        <button type="button" class="sync-confirm-modal__confirm-btn sync-confirm-modal__confirm-btn--success" onclick="closeGsSyncSuccessModalGuru()">
          Selesai
        </button>
      </div>
    </div>
  </div>

  {{-- ── MODAL TEST KONEKSI ── --}}
  <div id="gsTestModalGuru" class="sync-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="gsTestTitleGuru" aria-describedby="gsTestDescriptionGuru" hidden>
    <div class="sync-confirm-modal__backdrop" onclick="closeGsTestModalGuru()" tabindex="-1"></div>
    <div class="sync-confirm-modal__content" role="document" id="gsTestModalContentGuru">
      <div class="sync-confirm-modal__header text-center">
        <div class="mb-3" id="gsTestIconWrapGuru">
          <div id="gsTestLoadingGuru" style="display:none;">
            <div style="width:56px;height:56px;margin:0 auto;border-radius:50%;background:rgba(0,207,232,0.12);display:flex;align-items:center;justify-content:center;color:var(--das-info);font-size:2rem;">
              <i class="ti tabler-loader-2 animate-spin"></i>
            </div>
          </div>
          <div id="gsTestIconSuccessGuru" style="display:none;">
            <div class="sync-success-icon-wrap">
              <div class="sync-success-icon-ring"></div>
              <div class="sync-success-icon" style="background:var(--das-success);"><i class="ti tabler-circle-check"></i></div>
            </div>
          </div>
          <div id="gsTestIconErrorGuru" style="display:none;">
            <div style="width:56px;height:56px;margin:0 auto;border-radius:50%;background:rgba(234,84,85,0.12);display:flex;align-items:center;justify-content:center;color:var(--das-danger);font-size:2rem;">
              <i class="ti tabler-alert-circle"></i>
            </div>
          </div>
        </div>
        <h2 id="gsTestTitleGuru" style="font-size:1.05rem;font-weight:700;color:#e2e8f0;margin:0;">Test Koneksi</h2>
        <p id="gsTestDescriptionGuru" class="mt-2" style="color:#94a3b8;font-size:0.88rem;line-height:1.6;margin:0;">Menguji koneksi ke Google Sheets...</p>
      </div>
      <div class="sync-confirm-modal__actions justify-content-center" id="gsTestActionsGuru" style="display:none;">
        <button type="button" class="sync-confirm-modal__cancel-btn" onclick="closeGsTestModalGuru()">Tutup</button>
      </div>
    </div>
  </div>

  <script>
  function openGsSyncConfirmModalGuru() {
    const modal = document.getElementById('gsSyncConfirmModalGuru');
    const confirmButton = document.getElementById('gsSyncConfirmButtonGuru');
    if (!modal || !confirmButton) return true;
    modal.hidden = false;
    modal.style.pointerEvents = 'auto';
    setTimeout(() => confirmButton.focus(), 50);
  }

  function closeGsSyncConfirmModalGuru() {
    const modal = document.getElementById('gsSyncConfirmModalGuru');
    const openButton = document.getElementById('gsOpenSyncConfirmButtonGuru');
    if (!modal) return;
    modal.hidden = true;
    modal.style.pointerEvents = 'none';
    if (openButton) openButton.focus();
  }

  function openGsSyncSuccessModalGuru() {
    const modal = document.getElementById('gsSyncSuccessModalGuru');
    if (modal) {
      modal.hidden = false;
      modal.style.pointerEvents = 'auto';
    }
  }

  function closeGsSyncSuccessModalGuru() {
    const modal = document.getElementById('gsSyncSuccessModalGuru');
    if (modal) {
      modal.hidden = true;
      modal.style.pointerEvents = 'none';
    }
  }

  async function submitGsSyncNowGuru() {
    const form = document.getElementById('gsSyncNowFormGuru');
    const btn = document.getElementById('gsSyncConfirmButtonGuru');
    const btnText = document.getElementById('gsSyncBtnTextGuru');
    const btnSpinner = document.getElementById('gsSyncBtnSpinnerGuru');
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
        closeGsSyncConfirmModalGuru();
        setTimeout(() => {
          openGsSyncSuccessModalGuru();
          startGsSyncPollingGuru();
        }, 300);
      } else {
        showGsDynamicToastGuru('danger', result.message || 'Terjadi kesalahan saat sinkronisasi.');
      }
    } catch (error) {
      console.error('Sync error:', error);
      showGsDynamicToastGuru('danger', 'Gagal menghubungi server. Silakan coba lagi.');
    } finally {
      btn.disabled = false;
      btnText.style.display = 'inline-block';
      btnSpinner.style.display = 'none';
    }
  }

  function showGsDynamicToastGuru(type, message) {
    const toastId = type === 'success' ? 'gsSyncSuccessToastGuru' : 'gsSyncErrorToastGuru';
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

  async function processGsQueueGuru() {
    const btn = document.getElementById('gsProcessQueueBtnGuru');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ti tabler-loader-2 animate-spin"></i> <span>Memproses...</span>';

    try {
      const response = await fetch('{{ route("admin.pengaturan.google-sheets-guru.process-queue") }}', {
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
        await refreshGsSyncStatusGuru();
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
        showGsDynamicToastGuru('danger', result.message || 'Gagal memproses antrian.');
      }
    } catch (error) {
      console.error('Process queue error:', error);
      showGsDynamicToastGuru('danger', 'Gagal menghubungi server. Silakan coba lagi.');
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  }

  async function refreshGsSyncStatusGuru() {
    try {
      const response = await fetch('{{ route("admin.pengaturan.google-sheets-guru.index") }}', {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      const result = await response.json();
      
      if (result.last_sync_at) {
        document.getElementById('sync-last-time-guru').textContent = result.last_sync_at;
      }
      if (result.status_badge_text) {
        const badge = document.getElementById('sync-status-badge-guru');
        badge.textContent = result.status_badge_text;
        badge.className = 'badge ' + (result.last_sync_status === 'success' ? 'bg-label-success' : 
                                       result.last_sync_status === 'in_progress' ? 'bg-label-warning' : 
                                       result.last_sync_status === 'failed' ? 'bg-label-danger' : 'bg-label-secondary');
      }
      if (result.last_sync_message) {
        const msgContainer = document.getElementById('sync-message-container-guru');
        if (msgContainer) {
          msgContainer.classList.remove('d-none');
          document.getElementById('sync-message-text-guru').textContent = result.last_sync_message;
        }
      }
    } catch (e) {
      console.warn('Failed to refresh sync status:', e);
    }
  }

  function confirmGsResetQueueGuru() {
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
        submitGsResetQueueGuru();
      }
    });
  }

  async function submitGsResetQueueGuru() {
    const btn = document.getElementById('gsResetQueueBtnGuru');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ti tabler-loader-2 animate-spin"></i> <span>Mereset...</span>';

    try {
      const response = await fetch('{{ route("admin.pengaturan.google-sheets-guru.reset-antrian") }}', {
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
        await refreshGsSyncStatusGuru();
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
        showGsDynamicToastGuru('danger', result.message || 'Gagal mereset antrian.');
      }
    } catch (error) {
      console.error('Reset queue error:', error);
      showGsDynamicToastGuru('danger', 'Gagal menghubungi server. Silakan coba lagi.');
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  }

  function openGsTestModalGuru() {
    const modal = document.getElementById('gsTestModalGuru');
    if (!modal) return;

    document.getElementById('gsTestLoadingGuru').style.display = 'block';
    document.getElementById('gsTestIconSuccessGuru').style.display = 'none';
    document.getElementById('gsTestIconErrorGuru').style.display = 'none';
    document.getElementById('gsTestActionsGuru').style.display = 'none';
    document.getElementById('gsTestTitleGuru').textContent = 'Test Koneksi';
    document.getElementById('gsTestDescriptionGuru').textContent = 'Menguji koneksi ke Google Sheets...';
    document.getElementById('gsTestModalContentGuru').className = 'sync-confirm-modal__content';

    modal.hidden = false;
    modal.style.pointerEvents = 'auto';

    submitGsTestGuru();
  }

  function closeGsTestModalGuru() {
    const modal = document.getElementById('gsTestModalGuru');
    if (modal) {
      modal.hidden = true;
      modal.style.pointerEvents = 'none';
    }
  }

  async function submitGsTestGuru() {
    const spreadsheetId = document.getElementById('spreadsheet_id_guru').value;
    const credentialsJson = document.getElementById('credentials_json_guru').value;
    const sheetRange = document.getElementById('sheet_range_guru').value;
    const hasSaved = {{ $guruSetting->id ? 'true' : 'false' }};

    if (!spreadsheetId) {
      document.getElementById('gsTestLoadingGuru').style.display = 'none';
      document.getElementById('gsTestIconErrorGuru').style.display = 'block';
      document.getElementById('gsTestTitleGuru').textContent = 'Validasi Gagal';
      document.getElementById('gsTestDescriptionGuru').textContent = 'Harap isi ID Spreadsheet terlebih dahulu.';
      document.getElementById('gsTestActionsGuru').style.display = 'flex';
      return;
    }

    try {
      const response = await fetch('{{ route('admin.pengaturan.google-sheets-guru.test') }}', {
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
      document.getElementById('gsTestLoadingGuru').style.display = 'none';

      if (result.success) {
        document.getElementById('gsTestIconSuccessGuru').style.display = 'block';
        document.getElementById('gsTestTitleGuru').textContent = 'Koneksi Berhasil!';
        document.getElementById('gsTestDescriptionGuru').textContent = result.message || 'Koneksi ke Google Sheets berhasil.';
        document.getElementById('gsTestModalContentGuru').className = 'sync-confirm-modal__content sync-confirm-modal__content--success';
      } else {
        document.getElementById('gsTestIconErrorGuru').style.display = 'block';
        document.getElementById('gsTestTitleGuru').textContent = 'Koneksi Gagal';
        document.getElementById('gsTestDescriptionGuru').textContent = result.message || 'Tidak dapat terhubung ke Google Sheets.';
      }
    } catch (error) {
      document.getElementById('gsTestLoadingGuru').style.display = 'none';
      document.getElementById('gsTestIconErrorGuru').style.display = 'block';
      document.getElementById('gsTestTitleGuru').textContent = 'Koneksi Gagal';
      document.getElementById('gsTestDescriptionGuru').textContent = 'Gagal menghubungi server.';
      console.error('Test connection error:', error);
    }

    document.getElementById('gsTestActionsGuru').style.display = 'flex';
  }

  async function createGsSheetTemplateGuru() {
    const btn = document.getElementById('gsCreateSheetBtnGuru');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ti tabler-loader-2 animate-spin"></i> <span>Membuat...</span>';

    try {
      const response = await fetch('{{ route("admin.pengaturan.google-sheets-guru.template.create") }}', {
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
        const inputEl = document.getElementById('spreadsheet_id_guru');
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

  async function detectGsMappingGuru() {
    const btn = document.getElementById('gsDetectMappingBtnGuru');
    const spinner = document.getElementById('gsDetectMappingSpinnerGuru');
    const resultDiv = document.getElementById('gsMappingResultGuru');
    const errorDiv = document.getElementById('gsMappingErrorGuru');
    const tableBody = document.getElementById('gsMappingTableBodyGuru');
    const warningDiv = document.getElementById('gsUnrecognizedWarningGuru');
    const warningText = document.getElementById('gsUnrecognizedWarningTextGuru');

    resultDiv.style.display = 'none';
    errorDiv.style.display = 'none';
    warningDiv.style.display = 'none';
    btn.disabled = true;
    spinner.style.display = 'inline-flex';

    try {
      const response = await fetch('{{ route("admin.pengaturan.google-sheets-guru.preview-mapping") }}', {
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
        document.getElementById('gsMappingErrorTextGuru').textContent = json.message || 'Gagal preview mapping.';
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
      document.getElementById('gsTotalHeadersGuru').textContent = data.total_headers || data.preview.length;
      document.getElementById('gsMatchedCountGuru').textContent = data.matched || 0;
      const unrecognizedCount = (data.unrecognized && data.unrecognized.length) || 0;
      document.getElementById('gsUnrecognizedCountGuru').textContent = unrecognizedCount;

      if (unrecognizedCount > 0) {
        warningText.textContent = unrecognizedCount + ' kolom tidak dikenal. Sinkronisasi tetap berjalan untuk kolom yang dikenal.';
        warningDiv.style.display = 'flex';
      }

      resultDiv.style.display = 'block';
      document.getElementById('gsMappingSummaryGuru').style.display = 'flex';
    } catch (error) {
      console.error(error);
      errorDiv.style.display = 'flex';
      document.getElementById('gsMappingErrorTextGuru').textContent = 'Terjadi kesalahan.';
    } finally {
      btn.disabled = false;
      spinner.style.display = 'none';
    }
  }

  let gsSyncPollingIntervalGuru = null;

  function startGsSyncPollingGuru() {
    if (gsSyncPollingIntervalGuru) return;
    const lastTimeEl = document.getElementById('sync-last-time-guru');
    const badgeEl = document.getElementById('sync-status-badge-guru');
    const msgContainer = document.getElementById('sync-message-container-guru');
    const msgEl = document.getElementById('sync-message-text-guru');

    if (!badgeEl) return;

    gsSyncPollingIntervalGuru = setInterval(async () => {
      try {
        const response = await fetch('{{ route('admin.pengaturan.google-sheets-guru.index') }}', {
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
          clearInterval(gsSyncPollingIntervalGuru);
          gsSyncPollingIntervalGuru = null;
        }
      } catch (e) {
        console.error(e);
      }
    }, 2500);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const currentStatus = '{{ $guruSetting->last_sync_status ?? "" }}';
    if (currentStatus === 'in_progress') {
      startGsSyncPollingGuru();
    }
  });

  window.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeGsSyncConfirmModalGuru();
      closeGsSyncSuccessModalGuru();
      closeGsTestModalGuru();
    }
  });
  </script>
</div>
