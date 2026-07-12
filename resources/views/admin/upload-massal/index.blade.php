@extends('layouts/layoutMaster')

@section('title', 'Upload Massal Foto ke Google Drive')

@section('page-style')
<style>
  .upload-zone {
    border: 2px dashed rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    padding: 2.5rem 1.5rem;
    text-align: center;
    transition: all 0.2s ease;
    cursor: pointer;
  }
  .upload-zone:hover, .upload-zone.dragging {
    border-color: var(--das-primary, #7367f0);
    background: rgba(115, 103, 240, 0.05);
  }
  .preview-container {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 6px;
    background: rgba(15, 23, 42, 0.2);
  }
  .preview-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }
  .preview-item:last-child {
    border-bottom: none;
  }
  .preview-thumbnail {
    width: 42px;
    height: 42px;
    border-radius: 4px;
    object-fit: cover;
    background: rgba(255, 255, 255, 0.05);
  }
  .loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(8px);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  .nav-tabs-glass {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
  }
  .nav-tabs-glass .nav-link {
    color: rgba(255, 255, 255, 0.6) !important;
    border: none !important;
    border-bottom: 2px solid transparent !important;
    background: transparent !important;
    font-weight: 600;
    padding: 0.75rem 1.25rem;
    transition: all 0.15s ease;
  }
  .nav-tabs-glass .nav-link:hover {
    color: #fff !important;
  }
  .nav-tabs-glass .nav-link.active {
    color: var(--das-primary, #7367f0) !important;
    border-bottom-color: var(--das-primary, #7367f0) !important;
    background: transparent !important;
  }
</style>
@endsection

@section('content')
{{-- HERO HEADER --}}
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder" style="width:52px;height:52px;border-radius:12px;background:rgba(115,103,240,0.15);display:flex;align-items:center;justify-content:center;border:1px solid rgba(115,103,240,0.3);">
          <i class="ti tabler-cloud-upload text-primary fs-3"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>

      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          <a href="{{ route('admin.upload-massal.batches') }}" class="text-white text-decoration-none">Upload Massal</a> / Baru
        </div>
        <h4 class="das-hero__title text-gradient-gold">Upload Massal Foto</h4>
        <p class="das-hero__subtitle">Upload beberapa file foto siswa sekaligus atau import dari berkas ZIP.</p>
      </div>
    </div>
    
    <div class="das-hero__actions">
      <a href="{{ route('admin.upload-massal.batches') }}" class="btn das-btn --secondary">
        <i class="ti tabler-history me-1"></i> Riwayat Batch
      </a>
    </div>
  </div>
</div>

<div x-data="uploadMassal({
  uploadUrl: '{{ route('admin.upload-massal.upload') }}',
  zipUrl: '{{ route('admin.upload-massal.import-zip') }}',
  checkStudentUrl: '{{ url('/admin/upload-massal/check-student') }}',
  csrfToken: '{{ csrf_token() }}',
  driveConnected: {{ $driveConnected ? 'true' : 'false' }}
})" x-init="init()">

  {{-- Loading Overlay --}}
  <div class="loading-overlay" x-show="loading" x-cloak x-transition>
    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
    <h5 class="text-white mb-1" x-text="loadingTitle">Mengunggah berkas...</h5>
    <p class="text-white-50 small mb-0" x-text="loadingSubtitle">Harap tunggu, jangan menutup halaman ini.</p>
    <div class="w-25 mt-3" x-show="uploadPercent > 0">
      <div class="progress" style="height: 8px;">
        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" :style="'width: ' + uploadPercent + '%'"></div>
      </div>
      <div class="text-center text-white-50 small mt-1" x-text="uploadPercent + '%'"></div>
    </div>
  </div>

  {{-- Notification Alert --}}
  <template x-if="alert.show">
    <div :class="'alert alert-' + alert.type + ' alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm animate__animated animate__fadeIn'" role="alert" style="border-radius:8px;">
      <i :class="'ti ' + (alert.type === 'success' ? 'tabler-circle-check' : 'tabler-alert-circle') + ' fs-5'"></i>
      <span x-text="alert.message"></span>
      <button type="button" class="btn-close ms-auto" @click="alert.show = false"></button>
    </div>
  </template>

  {{-- QUEUE STATUS BANNER: Inactive --}}
  <template x-if="queueStatus.status === 'inactive'">
    <div class="alert d-flex align-items-start gap-3 mb-4 border-0 shadow-sm animate__animated animate__fadeIn" role="alert" style="background: rgba(234, 84, 85, 0.1); border: 1px solid rgba(234, 84, 85, 0.25) !important; border-radius: 8px; color: #fff;">
      <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0" style="width: 36px; height: 36px;">
        <i class="ti tabler-alert-triangle fs-4"></i>
      </div>
      <div class="flex-grow-1">
        <strong class="text-white">Queue Upload Tidak Aktif</strong>
        <p class="mb-0 text-white-50 small">
          Batch upload tidak akan diproses sampai queue worker dihidupkan.
          <template x-if="queueStatus.lastHeartbeat">
            <span class="d-block mt-1">
              <i class="ti tabler-clock me-1"></i>Heartbeat terakhir: <span x-text="queueStatus.lastHeartbeat"></span>
            </span>
          </template>
        </p>
        <div class="mt-2 d-flex gap-2">
          {{-- Tombol Hidupkan Queue --}}
          <button type="button" class="btn btn-sm btn-danger fw-semibold d-inline-flex align-items-center gap-1" @click="queueAction('start')" :disabled="queueActionLoading">
            <template x-if="queueActionLoading && queueActionType === 'start'">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </template>
            <template x-if="!(queueActionLoading && queueActionType === 'start')">
              <i class="ti tabler-player-play fs-5"></i>
            </template>
            Hidupkan Queue
          </button>
          {{-- Tombol Restart Queue --}}
          <button type="button" class="btn btn-sm btn-outline-danger fw-semibold d-inline-flex align-items-center gap-1" @click="queueAction('restart')" :disabled="queueActionLoading">
            <template x-if="queueActionLoading && queueActionType === 'restart'">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </template>
            <template x-if="!(queueActionLoading && queueActionType === 'restart')">
              <i class="ti tabler-refresh fs-5"></i>
            </template>
            Restart Queue
          </button>
        </div>
      </div>
      <button type="button" class="btn-close btn-close-white ms-auto flex-shrink-0" @click="queueStatus.status = 'active'"></button>
    </div>
  </template>

  {{-- QUEUE STATUS BANNER: Active (brief notification) --}}
  <template x-if="showActiveBanner">
    <div class="alert d-flex align-items-start gap-3 mb-4 border-0 shadow-sm animate__animated animate__fadeIn" role="alert" style="background: rgba(40, 199, 111, 0.1); border: 1px solid rgba(40, 199, 111, 0.25) !important; border-radius: 8px; color: #fff;">
      <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0" style="width: 36px; height: 36px;">
        <i class="ti tabler-circle-check fs-4"></i>
      </div>
      <div class="flex-grow-1">
        <strong class="text-white">Queue Upload Aktif</strong>
        <p class="mb-0 text-white-50 small">Queue upload aktif — semua batch akan diproses.</p>
        <div class="mt-2 d-flex gap-2">
          {{-- Tombol Restart Queue --}}
          <button type="button" class="btn btn-sm btn-success fw-semibold d-inline-flex align-items-center gap-1" @click="queueAction('restart')" :disabled="queueActionLoading">
            <template x-if="queueActionLoading && queueActionType === 'restart'">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </template>
            <template x-if="!(queueActionLoading && queueActionType === 'restart')">
              <i class="ti tabler-refresh fs-5"></i>
            </template>
            Restart Queue
          </button>
          {{-- Tombol Matikan Queue --}}
          <button type="button" class="btn btn-sm btn-outline-success fw-semibold d-inline-flex align-items-center gap-1" @click="queueAction('stop')" :disabled="queueActionLoading">
            <template x-if="queueActionLoading && queueActionType === 'stop'">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </template>
            <template x-if="!(queueActionLoading && queueActionType === 'stop')">
              <i class="ti tabler-player-stop fs-5"></i>
            </template>
            Matikan Queue
          </button>
        </div>
      </div>
    </div>
  </template>

  <div class="row">
    {{-- FORM PANEL UTAMA --}}
    <div class="col-lg-8 mb-4">
      <div class="das-panel">
        <div class="das-panel__head p-0 border-bottom-0">
          <ul class="nav nav-tabs nav-tabs-glass w-100 px-4 pt-2" role="tablist">
            <li class="nav-item">
              <button class="nav-link active" @click="tab = 'files'" :class="{ 'active': tab === 'files' }" type="button">
                <i class="ti tabler-files me-1"></i> Upload File
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" @click="tab = 'zip'" :class="{ 'active': tab === 'zip' }" type="button">
                <i class="ti tabler-file-zip me-1"></i> Import ZIP
              </button>
            </li>
          </ul>
        </div>
        <div class="das-panel__body py-4">
          
          {{-- TAB 1: UPLOAD FILES --}}
          <div x-show="tab === 'files'">
            <form @submit.prevent="submitFiles">
              <div class="mb-4">
                <label class="form-label text-white-50 small mb-1 fw-bold">NAMA BATCH (OPSIONAL)</label>
                <input type="text" x-model="namaBatchFiles" class="form-control bg-transparent text-white border-white-10" style="border-color:rgba(255,255,255,0.1);" placeholder="Contoh: Foto Siswa Baru 2026">
                <div class="form-text text-white-50 mt-1 small">Jika dikosongkan, nama batch otomatis akan dibuat berdasarkan tanggal saat ini.</div>
              </div>

              <div class="mb-4">
                <label class="form-label text-white-50 small mb-1 fw-bold">PILIH FOTO SISWA</label>
                <div 
                  class="upload-zone" 
                  :class="{ 'dragging': isDragging }"
                  @dragover.prevent="isDragging = true"
                  @dragleave.prevent="isDragging = false"
                  @drop.prevent="handleFileDrop($event)"
                  @click="$refs.fileInput.click()"
                >
                  <input type="file" x-ref="fileInput" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="d-none" @change="handleFileSelect($event)">
                  <i class="ti tabler-cloud-upload text-primary fs-1 mb-2"></i>
                  <h6 class="text-white mb-1">Tarik dan Lepaskan Berkas di Sini</h6>
                  <p class="text-white-50 small mb-0">Atau klik untuk menelusuri file (Maks. 500 file, masing-masing maks. 5MB)</p>
                </div>
              </div>

              {{-- PREVIEW FILES --}}
              <div class="mb-4" x-show="filesList.length > 0">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label class="form-label text-white-50 small mb-0 fw-bold">DAFTAR PRATINJAU FILE (<span x-text="filesList.length"></span> File)</label>
                  <button type="button" @click="clearFiles" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">
                    <i class="ti tabler-trash me-1"></i> Bersihkan Semua
                  </button>
                </div>
                
                <div class="preview-container">
                  <template x-for="(item, index) in filesList" :key="index">
                    <div class="preview-item">
                      <img :src="item.previewUrl" class="preview-thumbnail">
                      <div class="flex-grow-1 min-w-0">
                        <div class="text-white fw-semibold text-truncate" x-text="item.file.name"></div>
                        <div class="text-white-50 small" x-text="formatBytes(item.file.size)"></div>
                      </div>
                      
                      {{-- Deteksi NISN --}}
                      <div class="d-flex align-items-center gap-2">
                        <template x-if="item.nisn">
                          <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-label-success">NISN: <span x-text="item.nisn"></span></span>
                            <span class="text-success small" x-show="item.studentName" x-text="'(' + item.studentName + ' - ' + item.studentKelas + ')'"></span>
                            <span class="text-white-50 small" x-show="item.checking">
                              <span class="spinner-border spinner-border-sm text-info" role="status"></span>
                            </span>
                            <span class="badge bg-label-danger" x-show="!item.checking && item.notFound">Tidak Terdaftar</span>
                          </div>
                        </template>
                        <template x-if="!item.nisn">
                          <span class="badge bg-label-warning">Siswa tidak dikenal</span>
                        </template>

                        <button type="button" @click="removeFile(index)" class="btn btn-sm btn-icon text-danger p-0 border-0 bg-transparent">
                          <i class="ti tabler-x fs-4"></i>
                        </button>
                      </div>

                    </div>
                  </template>
                </div>
              </div>

              <div class="d-flex justify-content-end">
                <button type="submit" class="btn das-btn --primary fw-semibold px-4" :disabled="filesList.length === 0 || !driveConnected">
                  <i class="ti tabler-upload me-1"></i> Mulai Upload Massal
                </button>
              </div>
            </form>
          </div>

          {{-- TAB 2: IMPORT ZIP --}}
          <div x-show="tab === 'zip'" x-cloak>
            <form @submit.prevent="submitZip">
              <div class="mb-4">
                <label class="form-label text-white-50 small mb-1 fw-bold">NAMA BATCH (OPSIONAL)</label>
                <input type="text" x-model="namaBatchZip" class="form-control bg-transparent text-white border-white-10" style="border-color:rgba(255,255,255,0.1);" placeholder="Contoh: Foto Angkatan ZIP 2026">
                <div class="form-text text-white-50 mt-1 small">Jika dikosongkan, nama batch otomatis akan dibuat berdasarkan tanggal saat ini.</div>
              </div>

              <div class="mb-4">
                <label class="form-label text-white-50 small mb-1 fw-bold">PILIH FILE ZIP</label>
                <div 
                  class="upload-zone" 
                  :class="{ 'dragging': isDraggingZip }"
                  @dragover.prevent="isDraggingZip = true"
                  @dragleave.prevent="isDraggingZip = false"
                  @drop.prevent="handleZipDrop($event)"
                  @click="$refs.zipInput.click()"
                >
                  <input type="file" x-ref="zipInput" accept=".zip" class="d-none" @change="handleZipSelect($event)">
                  <i class="ti tabler-file-zip text-warning fs-1 mb-2"></i>
                  <h6 class="text-white mb-1" x-text="zipFile ? zipFile.name : 'Tarik dan Lepaskan File ZIP di Sini'"></h6>
                  <p class="text-white-50 small mb-0" x-text="zipFile ? formatBytes(zipFile.size) : 'Atau klik untuk memilih file ZIP (Maks. 100MB)'"></p>
                </div>
              </div>

              <div class="alert alert-info border-0 shadow-none d-flex gap-2" role="alert" style="background: rgba(0, 207, 232, 0.08); border-radius: 8px; color: #fff;">
                <i class="ti tabler-info-circle text-info fs-4 flex-shrink-0 mt-0.5"></i>
                <div class="small text-white-50">
                  <strong class="text-white">Panduan ZIP File:</strong>
                  <ul class="mb-0 ps-3 mt-1">
                    <li>Nama file foto di dalam ZIP harus mengandung 10 digit NISN siswa agar dapat diidentifikasi secara otomatis (contoh: <code>0012345678_foto.jpg</code>).</li>
                    <li>Sistem hanya memproses file gambar (.jpg, .jpeg, .png, .webp). Folder di dalam ZIP akan dilewati.</li>
                    <li>Proses ekstraksi & pencocokan dilakukan secara async di background queue server.</li>
                  </ul>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <button type="button" x-show="zipFile" @click="clearZip" class="btn btn-label-secondary fw-semibold">
                  <i class="ti tabler-refresh me-1"></i> Reset
                </button>
                <button type="submit" class="btn das-btn --warning fw-semibold px-4 ms-auto" :disabled="!zipFile || !driveConnected">
                  <i class="ti tabler-cloud-upload me-1"></i> Unggah & Proses ZIP
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>

    {{-- SIDEBAR: GOOGLE DRIVE STATUS PANEL --}}
    <div class="col-lg-4 mb-4">
      <div class="das-panel">
        <div class="das-panel__head">
          <h6 class="das-panel__title mb-0">
            <i class="ti tabler-brand-google-drive text-primary"></i> Status Google Drive
          </h6>
        </div>
        <div class="das-panel__body">
          
          <template x-if="driveConnected">
            <div>
              <div class="mb-4 p-3 rounded d-flex align-items-center gap-3" style="background: rgba(40, 199, 111, 0.08); border: 1px solid rgba(40, 199, 111, 0.15);">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                  <i class="ti tabler-cloud-check fs-4"></i>
                </div>
                <div>
                  <span class="badge bg-success mb-1">Terhubung</span>
                  <div class="text-white fw-semibold small" x-text="driveInfo.email || 'Memuat detail...'"></div>
                </div>
              </div>

              <template x-if="driveInfo.loading">
                <div class="text-center py-3">
                  <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                  <div class="text-white-50 small">Memeriksa kuota penyimpanan...</div>
                </div>
              </template>

              <template x-if="!driveInfo.loading && driveInfo.connected">
                <div>
                  <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="text-white-50 small">Kuota Penyimpanan</span>
                      <span class="text-white fw-bold small" x-text="driveInfo.storageText">0 GB / 0 GB (0%)</span>
                    </div>
                    <div class="progress" style="height: 8px; background: rgba(255, 255, 255, 0.05); border-radius: 4px;">
                      <div class="progress-bar bg-success" role="progressbar" :style="'width: ' + driveInfo.storagePercent + '%'"></div>
                    </div>
                  </div>

                  <div class="mb-0">
                    <span class="text-white-50 small d-block">ID Folder Root</span>
                    <code class="text-warning small text-break" x-text="driveInfo.rootFolderId || '-'"></code>
                  </div>
                </div>
              </template>
            </div>
          </template>

          <template x-if="!driveConnected">
            <div>
              <div class="mb-4 p-3 rounded d-flex align-items-center gap-3" style="background: rgba(234, 84, 85, 0.08); border: 1px solid rgba(234, 84, 85, 0.15);">
                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                  <i class="ti tabler-cloud-off fs-4"></i>
                </div>
                <div>
                  <span class="badge bg-danger mb-1">Tidak Terhubung</span>
                  <div class="text-white-50 small">Koneksi Google Drive belum diatur atau dinonaktifkan.</div>
                </div>
              </div>

              <div class="alert alert-warning border-0 shadow-none small text-white-50 mb-3" style="background: rgba(255, 159, 67, 0.08);">
                Anda tidak dapat melakukan upload massal foto sebelum mengaktifkan dan mengotorisasi Google Drive di panel pengaturan.
              </div>

              <a href="{{ route('admin.pengaturan.index', ['tab' => 'google-drive']) }}" class="btn btn-label-primary w-100 fw-semibold">
                <i class="ti tabler-settings me-1"></i> Konfigurasi Google Drive
              </a>
            </div>
          </template>

        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('uploadMassal', (config) => ({
      tab: 'files',
      uploadUrl: config.uploadUrl,
      zipUrl: config.zipUrl,
      checkStudentUrl: config.checkStudentUrl,
      csrfToken: config.csrfToken,
      isDragging: false,
      isDraggingZip: false,
      loading: false,
      loadingTitle: '',
      loadingSubtitle: '',
      uploadPercent: 0,
      
      // State Files
      namaBatchFiles: '',
      filesList: [],
      
      // State ZIP
      namaBatchZip: '',
      zipFile: null,

      // Google Drive Info State
      driveConnected: config.driveConnected,
      driveInfo: {
        loading: true,
        connected: false,
        email: '',
        storageText: '',
        storagePercent: 0,
        rootFolderId: ''
      },

      alert: {
        show: false,
        type: 'danger',
        message: ''
      },

      // Queue Status
      queueStatus: {
        status: 'active',
        lastHeartbeat: null,
        pendingBatches: 0,
        checking: true
      },
      showActiveBanner: false,

      // Queue Actions
      queueActionLoading: false,
      queueActionType: '',

      init() {
        if (this.driveConnected) {
          this.fetchDriveStatus();
        }
        this.fetchQueueStatus();
        setInterval(() => {
          this.fetchQueueStatus();
        }, 30000);
      },

      fetchDriveStatus() {
        this.driveInfo.loading = true;
        fetch("{{ route('admin.google.status') }}")
          .then(res => res.json())
          .then(data => {
            this.driveInfo.loading = false;
            if (data.connected) {
              this.driveInfo.connected = true;
              this.driveInfo.email = data.email;
              this.driveInfo.rootFolderId = data.root_folder_id;
              
              if (data.storage.is_unlimited) {
                this.driveInfo.storageText = `${data.storage.usage_gb} GB / Unlimited`;
                this.driveInfo.storagePercent = 0;
              } else {
                this.driveInfo.storageText = `${data.storage.usage_gb} GB / ${data.storage.limit_gb} GB (${data.storage.used_percent}%)`;
                this.driveInfo.storagePercent = data.storage.used_percent;
              }
            } else {
              this.driveConnected = false;
              this.driveInfo.connected = false;
            }
          })
          .catch(err => {
            console.error('Drive status fetch error:', err);
            this.driveInfo.loading = false;
            this.driveConnected = false;
            this.driveInfo.connected = false;
          });
      },

      fetchQueueStatus() {
        fetch('/admin/queue-status')
          .then(res => res.json())
          .then(data => {
            const prevStatus = this.queueStatus.status;
            const wasChecking = this.queueStatus.checking;
            this.queueStatus.checking = false;
            this.queueStatus.status = data.status;
            this.queueStatus.lastHeartbeat = data.last_heartbeat;
            this.queueStatus.pendingBatches = data.pending_batches;

            // Show active banner briefly on first load or when recovering from inactive
            if (data.status === 'active' && (wasChecking || prevStatus === 'inactive')) {
              this.showActiveBanner = true;
              setTimeout(() => { this.showActiveBanner = false; }, 5000);
            }
          })
          .catch(err => {
            console.error('Queue status fetch error:', err);
            this.queueStatus.checking = false;
          });
      },

      async queueAction(action) {
        this.queueActionLoading = true;
        this.queueActionType = action;

        const actionLabels = {
          start: 'menghidupkan',
          stop: 'mematikan',
          restart: 'merestart'
        };

        try {
          const res = await fetch(`/admin/queue/${action}`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': this.csrfToken,
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
          const data = await res.json();

          if (data.success) {
            this.showAlert('success', data.message || `Queue berhasil ${actionLabels[action] || action}.`);
            // Refresh status queue otomatis
            this.fetchQueueStatus();
          } else {
            this.showAlert('danger', data.message || `Gagal ${actionLabels[action] || action} queue.`);
          }
        } catch (err) {
          this.showAlert('danger', `Terjadi kesalahan koneksi saat ${actionLabels[action] || action} queue.`);
        } finally {
          this.queueActionLoading = false;
          this.queueActionType = '';
        }
      },

      showAlert(type, message) {
        this.alert.show = true;
        this.alert.type = type;
        this.alert.message = message;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      },

      // Helpers
      formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
      },

      // File Selection & Drag Drop
      handleFileSelect(e) {
        const selectedFiles = Array.from(e.target.files);
        this.processFiles(selectedFiles);
      },

      handleFileDrop(e) {
        this.isDragging = false;
        if (e.dataTransfer.files) {
          this.processFiles(Array.from(e.dataTransfer.files));
        }
      },

      processFiles(files) {
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        // Cek total file saat ini + file baru agar tidak melebihi 500
        if (this.filesList.length + files.length > 500) {
          this.showAlert('danger', 'Maksimal 500 file yang dapat diupload sekaligus.');
          return;
        }

        files.forEach(file => {
          if (!validTypes.includes(file.type)) {
            this.showAlert('danger', `File ${file.name} dilewati karena bukan file gambar yang didukung.`);
            return;
          }
          if (file.size > maxSize) {
            this.showAlert('danger', `File ${file.name} dilewati karena melebihi batas ukuran 5MB.`);
            return;
          }

          // Deteksi NISN 10 digit di nama file
          const nisnMatch = file.name.match(/\b(\d{10})\b/);
          const nisn = nisnMatch ? nisnMatch[1] : null;

          const item = {
            file: file,
            previewUrl: URL.createObjectURL(file),
            nisn: nisn,
            studentName: '',
            studentKelas: '',
            checking: false,
            notFound: false
          };

          this.filesList.push(item);

          if (nisn) {
            this.checkStudent(item);
          }
        });
      },

      checkStudent(item) {
        item.checking = true;
        fetch(`${this.checkStudentUrl}/${item.nisn}`)
          .then(res => {
            if (!res.ok) throw new Error('Not Found');
            return res.json();
          })
          .then(res => {
            item.checking = false;
            if (res.success) {
              item.studentName = res.data.nama;
              item.studentKelas = res.data.kelas;
            } else {
              item.notFound = true;
            }
          })
          .catch(() => {
            item.checking = false;
            item.notFound = true;
          });
      },

      removeFile(index) {
        // Hapus preview object URL to prevent memory leak
        URL.revokeObjectURL(this.filesList[index].previewUrl);
        this.filesList.splice(index, 1);
      },

      clearFiles() {
        this.filesList.forEach(item => URL.revokeObjectURL(item.previewUrl));
        this.filesList = [];
        if (this.$refs.fileInput) this.$refs.fileInput.value = '';
      },

      // Zip Handling
      handleZipSelect(e) {
        const file = e.target.files[0];
        this.processZip(file);
      },

      handleZipDrop(e) {
        this.isDraggingZip = false;
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
          this.processZip(e.dataTransfer.files[0]);
        }
      },

      processZip(file) {
        if (file.type !== 'application/zip' && !file.name.endsWith('.zip')) {
          this.showAlert('danger', 'Harap pilih berkas dengan format ZIP (.zip).');
          return;
        }
        if (file.size > 100 * 1024 * 1024) { // 100MB
          this.showAlert('danger', 'Ukuran berkas ZIP melebihi batas maksimal 100MB.');
          return;
        }
        this.zipFile = file;
      },

      clearZip() {
        this.zipFile = null;
        if (this.$refs.zipInput) this.$refs.zipInput.value = '';
      },

      // Submit Form
      submitFiles() {
        if (this.filesList.length === 0) return;

        this.loading = true;
        this.loadingTitle = 'Mengunggah berkas...';
        this.loadingSubtitle = 'Sedang mengirim data ke server, mohon tidak menutup halaman.';
        this.uploadPercent = 0;

        const formData = new FormData();
        if (this.namaBatchFiles) {
          formData.append('nama_batch', this.namaBatchFiles);
        }
        
        this.filesList.forEach(item => {
          formData.append('files[]', item.file);
        });

        // AJAX request using XMLHttpRequest to support upload progress bar
        const xhr = new XMLHttpRequest();
        xhr.open('POST', this.uploadUrl, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.onprogress = (e) => {
          if (e.lengthComputable) {
            this.uploadPercent = Math.round((e.loaded / e.total) * 100);
            if (this.uploadPercent === 100) {
              this.loadingTitle = 'Memproses data di server...';
              this.loadingSubtitle = 'Proses upload selesai. Server sedang membuat batch & dispatching queue...';
            }
          }
        };

        xhr.onload = () => {
          this.loading = false;
          if (xhr.status >= 200 && xhr.status < 300) {
            try {
              const res = JSON.parse(xhr.responseText);
              if (res.success && res.redirect_url) {
                window.location.href = res.redirect_url;
              } else {
                this.showAlert('danger', res.message || 'Terjadi kesalahan saat memproses.');
              }
            } catch (err) {
              this.showAlert('danger', 'Gagal memproses respon dari server.');
            }
          } else {
            try {
              const res = JSON.parse(xhr.responseText);
              this.showAlert('danger', res.message || 'Gagal melakukan upload. Periksa kembali ukuran & tipe file.');
            } catch (err) {
              this.showAlert('danger', 'Gagal melakukan upload. Terjadi kesalahan server.');
            }
          }
        };

        xhr.onerror = () => {
          this.loading = false;
          this.showAlert('danger', 'Terjadi kesalahan koneksi saat melakukan upload.');
        };

        xhr.send(formData);
      },

      submitZip() {
        if (!this.zipFile) return;

        this.loading = true;
        this.loadingTitle = 'Mengunggah berkas ZIP...';
        this.loadingSubtitle = 'Sedang mentransfer file ZIP ke server, mohon tunggu.';
        this.uploadPercent = 0;

        const formData = new FormData();
        if (this.namaBatchZip) {
          formData.append('nama_batch', this.namaBatchZip);
        }
        formData.append('file_zip', this.zipFile);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', this.zipUrl, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.onprogress = (e) => {
          if (e.lengthComputable) {
            this.uploadPercent = Math.round((e.loaded / e.total) * 100);
            if (this.uploadPercent === 100) {
              this.loadingTitle = 'Mengekstrak berkas ZIP di server...';
              this.loadingSubtitle = 'File ZIP selesai diunggah. Server sedang memulai proses ekstraksi di antrean...';
            }
          }
        };

        xhr.onload = () => {
          this.loading = false;
          if (xhr.status >= 200 && xhr.status < 300) {
            try {
              const res = JSON.parse(xhr.responseText);
              if (res.success && res.redirect_url) {
                window.location.href = res.redirect_url;
              } else {
                this.showAlert('danger', res.message || 'Terjadi kesalahan saat memproses file ZIP.');
              }
            } catch (err) {
              this.showAlert('danger', 'Gagal memproses respon ZIP dari server.');
            }
          } else {
            try {
              const res = JSON.parse(xhr.responseText);
              this.showAlert('danger', res.message || 'Gagal melakukan upload ZIP. Pastikan file valid.');
            } catch (err) {
              this.showAlert('danger', 'Gagal melakukan upload ZIP. Terjadi kesalahan server.');
            }
          }
        };

        xhr.onerror = () => {
          this.loading = false;
          this.showAlert('danger', 'Terjadi kesalahan koneksi saat mengunggah ZIP.');
        };

        xhr.send(formData);
      }

    }));
  });
</script>
@endpush
