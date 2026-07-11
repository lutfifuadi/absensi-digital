@extends('layouts/layoutMaster')

@section('title', 'Detail Batch Upload Massal')

@section('page-style')
<style>
  .batch-row-hover {
    transition: background 0.15s ease;
  }
  .batch-row-hover:hover {
    background: rgba(255, 255, 255, 0.04) !important;
  }
  .progress {
    background-color: rgba(255, 255, 255, 0.08);
    height: 1.5rem;
    border-radius: 8px;
    overflow: hidden;
  }
  .progress-bar-animated {
    transition: width 0.4s ease;
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
          <a href="{{ route('admin.upload-massal.batches') }}" class="text-white text-decoration-none">Riwayat Batch</a> / Detail Batch
        </div>
        <h4 class="das-hero__title text-gradient-gold">Detail Batch Upload</h4>
        <p class="das-hero__subtitle">Pantau progress upload foto ke Google Drive secara real-time.</p>
      </div>
    </div>
  </div>
</div>

{{-- Main Container with Alpine.js State Management --}}
<div x-data="batchDetail({
  batchId: {{ $batch->id }},
  initialStatus: '{{ $batch->status }}',
  progressPercent: {{ $batch->progressPercent() }},
  totalItems: {{ $batch->total_items }},
  successCount: {{ $batch->success_count }},
  failedCount: {{ $batch->failed_count }},
  pendingCount: {{ $batch->total_items - ($batch->success_count + $batch->failed_count) }},
  progressUrl: '{{ route('admin.upload-massal.batches.progress', $batch->id) }}',
  itemsUrl: '{{ route('admin.upload-massal.batches.items', $batch->id) }}',
  retryUrl: '{{ route('admin.upload-massal.batches.retry', $batch->id) }}',
  cancelUrl: '{{ route('admin.upload-massal.batches.cancel', $batch->id) }}',
  csrfToken: '{{ csrf_token() }}'
})" x-init="init()">

  {{-- Notification Alert --}}
  <template x-if="alert.show">
    <div :class="'alert alert-' + alert.type + ' alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm'" role="alert" style="border-radius:8px;">
      <i :class="'ti ' + (alert.type === 'success' ? 'tabler-circle-check' : 'tabler-alert-circle') + ' fs-5'"></i>
      <span x-text="alert.message"></span>
      <button type="button" class="btn-close ms-auto" @click="alert.show = false"></button>
    </div>
  </template>

  {{-- Batch Metadata Cards --}}
  <div class="row mb-4">
    <div class="col-md-8">
      <div class="das-panel h-100">
        <div class="das-panel__head py-3 px-4 border-bottom" style="border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="das-panel__title mb-0">
            <i class="ti tabler-info-circle text-info"></i> Informasi Batch
          </h6>
          <span class="das-chip text-capitalize" :class="{
            '--primary': status === 'pending',
            '--warning': status === 'processing',
            '--success': status === 'completed',
            '--danger': status === 'failed',
            '--secondary': status === 'cancelled'
          }" x-text="status"></span>
        </div>
        <div class="das-panel__body">
          <div class="row g-3">
            <div class="col-sm-6">
              <span class="text-white-50 small d-block">Nama Batch</span>
              <span class="fw-bold text-white fs-5">{{ $batch->nama_batch }}</span>
            </div>
            <div class="col-sm-6">
              <span class="text-white-50 small d-block">Pembuat Batch</span>
              <span class="fw-bold text-white fs-5">{{ $batch->user?->name ?? 'Sistem' }}</span>
            </div>
            <div class="col-sm-6">
              <span class="text-white-50 small d-block">Tanggal Dibuat</span>
              <span class="fw-bold text-white">{{ \Carbon\Carbon::parse($batch->created_at)->locale('id')->translatedFormat('d F Y H:i:s') }}</span>
            </div>
            <div class="col-sm-6">
              <span class="text-white-50 small d-block">Sumber Upload</span>
              <span class="badge text-uppercase" :class="sumber === 'zip' ? 'bg-label-warning' : 'bg-label-info'">
                <span x-text="sumber"></span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    {{-- Progress Card --}}
    <div class="col-md-4">
      <div class="das-panel h-100">
        <div class="das-panel__head py-3 px-4 border-bottom" style="border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="das-panel__title mb-0">
            <i class="ti tabler-device-heart-monitor text-primary"></i> Progress Upload
          </h6>
        </div>
        <div class="das-panel__body d-flex flex-column justify-content-center py-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-white-50 small">Persentase</span>
            <span class="fw-bold text-primary fs-4" x-text="progressPercent + '%'"></span>
          </div>
          <div class="progress mb-3">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                 role="progressbar" 
                 :style="'width: ' + progressPercent + '%'" 
                 :aria-valuenow="progressPercent" 
                 aria-valuemin="0" 
                 aria-valuemax="100"></div>
          </div>
          <p class="text-center text-white-50 small mb-0" x-show="status === 'processing' || status === 'pending'">
            <span class="spinner-border spinner-border-sm me-2 text-primary" role="status"></span>
            Memproses data... Halaman akan melakukan refresh info.
          </p>
          <p class="text-center text-white-50 small mb-0" x-show="status === 'completed'">
            <i class="ti tabler-circle-check text-success me-1"></i> Selesai diproses.
          </p>
          <p class="text-center text-white-50 small mb-0" x-show="status === 'failed'">
            <i class="ti tabler-circle-x text-danger me-1"></i> Seluruh item gagal.
          </p>
          <p class="text-center text-white-50 small mb-0" x-show="status === 'cancelled'">
            <i class="ti tabler-ban text-secondary me-1"></i> Proses dibatalkan.
          </p>
        </div>
      </div>
    </div>
  </div>

  {{-- Counter Cards --}}
  <div class="row mb-4 gy-3">
    {{-- Total --}}
    <div class="col-6 col-lg-3">
      <div class="das-stat-card das-stat-card--primary text-decoration-none">
        <div class="das-stat-card__icon">
          <i class="ti tabler-files"></i>
        </div>
        <div class="das-stat-card__body">
          <div class="das-stat-card__val" x-text="counters.total">0</div>
          <div class="das-stat-card__label">Total Item</div>
        </div>
      </div>
    </div>
    {{-- Sukses --}}
    <div class="col-6 col-lg-3">
      <div class="das-stat-card das-stat-card--success text-decoration-none">
        <div class="das-stat-card__icon">
          <i class="ti tabler-circle-check"></i>
        </div>
        <div class="das-stat-card__body">
          <div class="das-stat-card__val text-success" x-text="counters.success">0</div>
          <div class="das-stat-card__label">Sukses</div>
        </div>
      </div>
    </div>
    {{-- Gagal --}}
    <div class="col-6 col-lg-3">
      <div class="das-stat-card das-stat-card--danger text-decoration-none">
        <div class="das-stat-card__icon">
          <i class="ti tabler-circle-x"></i>
        </div>
        <div class="das-stat-card__body">
          <div class="das-stat-card__val text-danger" x-text="counters.failed">0</div>
          <div class="das-stat-card__label">Gagal</div>
        </div>
      </div>
    </div>
    {{-- Pending --}}
    <div class="col-6 col-lg-3">
      <div class="das-stat-card das-stat-card--warning text-decoration-none">
        <div class="das-stat-card__icon">
          <i class="ti tabler-reload"></i>
        </div>
        <div class="das-stat-card__body">
          <div class="das-stat-card__val text-warning" x-text="counters.pending">0</div>
          <div class="das-stat-card__label">Pending</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Items Table Panel --}}
  <div class="das-panel mb-4">
    <div class="das-panel__head border-bottom py-3 px-4 d-flex align-items-center justify-content-between" style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Item Batch
      </h6>
      <div class="d-flex align-items-center gap-2">
        <select class="form-select form-select-sm" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1); width: auto;" x-model="filterStatus" @change="fetchItems(1)">
          <option value="">Semua Status</option>
          <option value="pending">Pending</option>
          <option value="processing">Processing</option>
          <option value="success">Success</option>
          <option value="failed">Failed</option>
        </select>
        <div class="input-group input-group-merge input-group-sm" style="width: 200px;">
          <span class="input-group-text bg-transparent text-white-50 border-white-10" style="border-color:rgba(255,255,255,0.1);"><i class="ti tabler-search"></i></span>
          <input type="text" class="form-control bg-transparent text-white border-white-10" style="border-color:rgba(255,255,255,0.1);" placeholder="Cari File / Siswa..." x-model="search" @input.debounce.500ms="fetchItems(1)">
        </div>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="color:inherit;">
          <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
            <tr>
              <th class="ps-4 py-3" style="width:60px;">No</th>
              <th class="py-3">Nama File</th>
              <th class="py-3">NISN</th>
              <th class="py-3">Nama Siswa</th>
              <th class="py-3">Status</th>
              <th class="py-3">Pesan Error</th>
              <th class="py-3 pe-4 text-end">Waktu Proses</th>
            </tr>
          </thead>
          <tbody>
            <template x-if="loadingItems">
              <tr>
                <td colspan="7" class="text-center py-5 text-white-50">
                  <div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>
                  Memuat data item...
                </td>
              </tr>
            </template>
            <template x-if="!loadingItems && items.length === 0">
              <tr>
                <td colspan="7" class="text-center py-5 text-white-50">
                  <i class="ti tabler-notes-off fs-2 d-block mb-2 opacity-50"></i>
                  Tidak ada item ditemukan.
                </td>
              </tr>
            </template>
            <template x-if="!loadingItems && items.length > 0">
              <template x-for="(item, index) in items" :key="item.id">
                <tr class="batch-row-hover">
                  <td class="ps-4 text-white-50 small" x-text="pagination.from + index"></td>
                  <td class="fw-bold text-white" x-text="item.original_filename"></td>
                  <td>
                    <span class="text-white-50" x-text="item.siswa ? item.siswa.nisn : '-'"></span>
                  </td>
                  <td>
                    <div class="d-flex flex-column">
                      <span class="text-white fw-semibold" x-text="item.siswa ? item.siswa.nama_lengkap : '-'"></span>
                      <span class="text-white-50 small" style="font-size:0.72rem;" x-text="item.siswa && item.siswa.kelas ? item.siswa.kelas.nama : ''"></span>
                    </div>
                  </td>
                  <td>
                    <span class="das-chip text-capitalize" :class="{
                      '--primary': item.status === 'pending',
                      '--warning': item.status === 'processing',
                      '--success': item.status === 'success',
                      '--danger': item.status === 'failed'
                    }" x-text="item.status"></span>
                  </td>
                  <td>
                    <span class="text-danger small" x-text="item.error_message || '-'"></span>
                  </td>
                  <td class="pe-4 text-end text-white-50 small" x-text="formatDate(item.processed_at)"></td>
                </tr>
              </template>
            </template>
          </tbody>
        </table>
      </div>

      {{-- AJAX Pagination --}}
      <div class="d-flex justify-content-between align-items-center py-3 px-4 border-top" style="border-color:rgba(255,255,255,0.08) !important;" x-show="pagination.last_page > 1">
        <span class="text-white-50 small">
          Menampilkan <span class="text-white" x-text="pagination.from"></span> sampai <span class="text-white" x-text="pagination.to"></span> dari <span class="text-white" x-text="pagination.total"></span> item
        </span>
        <nav aria-label="Page navigation">
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="pagination.current_page === 1 ? 'disabled' : ''">
              <button class="page-link" @click="fetchItems(pagination.current_page - 1)" aria-label="Previous">
                <i class="ti tabler-chevron-left fs-6"></i>
              </button>
            </li>
            <template x-for="page in getPageRange()" :key="page">
              <li class="page-item" :class="pagination.current_page === page ? 'active' : ''">
                <button class="page-link" @click="fetchItems(page)" x-text="page"></button>
              </li>
            </template>
            <li class="page-item" :class="pagination.current_page === pagination.last_page ? 'disabled' : ''">
              <button class="page-link" @click="fetchItems(pagination.current_page + 1)" aria-label="Next">
                <i class="ti tabler-chevron-right fs-6"></i>
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>

  {{-- Bottom Action Buttons --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
    <a href="{{ route('admin.upload-massal.batches') }}" class="btn btn-label-secondary fw-semibold">
      <i class="ti tabler-arrow-left me-1"></i> Kembali ke Riwayat
    </a>
    <div class="d-flex gap-2">
      {{-- Retry button - only active if there are failed items --}}
      <button class="btn das-btn --warning fw-semibold" 
              :disabled="loadingAction || counters.failed === 0" 
              @click="retryFailedItems()">
        <span class="spinner-border spinner-border-sm me-1" x-show="loadingAction" role="status"></span>
        <i class="ti tabler-refresh me-1" x-show="!loadingAction"></i> Retry Gagal
      </button>

      {{-- Cancel button - only active if status is pending or processing --}}
      <button class="btn das-btn --danger fw-semibold" 
              :disabled="loadingAction || (status !== 'pending' && status !== 'processing')" 
              @click="cancelProcess()">
        <span class="spinner-border spinner-border-sm me-1" x-show="loadingAction" role="status"></span>
        <i class="ti tabler-ban me-1" x-show="!loadingAction"></i> Batalkan Proses
      </button>

      {{-- Download error log (generates and downloads CSV client-side / endpoint logic representation) --}}
      <button class="btn btn-label-info fw-semibold" 
              :disabled="counters.failed === 0" 
              @click="downloadErrorLog()">
        <i class="ti tabler-download me-1"></i> Download Log Error (CSV)
      </button>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('batchDetail', (config) => ({
      batchId: config.batchId,
      status: config.initialStatus,
      sumber: '{{ $batch->sumber }}',
      progressPercent: Number(config.progressPercent || 0),
      counters: {
        total: Number(config.totalItems || 0),
        success: Number(config.successCount || 0),
        failed: Number(config.failedCount || 0),
        pending: Number(config.pendingCount || 0)
      },
      items: [],
      filterStatus: '',
      search: '',
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 1,
        to: 1,
        total: 0
      },
      alert: {
        show: false,
        type: 'success',
        message: ''
      },
      loadingItems: false,
      loadingAction: false,
      pollingInterval: null,

      init() {
        this.fetchItems(1);
        this.startPolling();
      },

      startPolling() {
        if (this.status === 'pending' || this.status === 'processing') {
          this.pollingInterval = setInterval(() => {
            this.checkProgress();
          }, 3000);
        }
      },

      stopPolling() {
        if (this.pollingInterval) {
          clearInterval(this.pollingInterval);
          this.pollingInterval = null;
        }
      },

      checkProgress() {
        fetch(config.progressUrl)
          .then(res => res.json())
          .then(data => {
            this.status = data.status;
            this.counters.total = data.total_items;
            this.counters.success = data.success_count;
            this.counters.failed = data.failed_count;
            this.counters.pending = data.total_items - (data.success_count + data.failed_count);
            this.progressPercent = data.progress_percent;

            if (this.status !== 'pending' && this.status !== 'processing') {
              this.stopPolling();
              this.showAlert('success', 'Proses upload batch selesai.');
            }
            this.fetchItems(this.pagination.current_page);
          })
          .catch(err => {
            console.error('Gagal mengambil data progress:', err);
          });
      },

      fetchItems(page = 1) {
        this.loadingItems = true;
        const url = new URL(config.itemsUrl);
        url.searchParams.append('page', page);
        if (this.filterStatus) url.searchParams.append('status', this.filterStatus);
        if (this.search) url.searchParams.append('search', this.search);

        fetch(url)
          .then(res => res.json())
          .then(data => {
            this.items = data.data;
            this.pagination.current_page = data.current_page;
            this.pagination.last_page = data.last_page;
            this.pagination.from = data.from || 0;
            this.pagination.to = data.to || 0;
            this.pagination.total = data.total;
            this.loadingItems = false;
          })
          .catch(err => {
            console.error('Gagal mengambil data item:', err);
            this.loadingItems = false;
          });
      },

      retryFailedItems() {
        if (confirm('Apakah Anda yakin ingin memproses ulang seluruh item yang gagal di batch ini?')) {
          this.loadingAction = true;
          fetch(config.retryUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': config.csrfToken
            }
          })
          .then(res => res.json())
          .then(data => {
            this.loadingAction = false;
            if (data.success) {
              this.showAlert('success', data.message || 'Sukses memproses ulang item.');
              this.status = 'processing';
              this.startPolling();
              this.checkProgress();
            } else {
              this.showAlert('danger', data.message || 'Gagal memproses ulang.');
            }
          })
          .catch(err => {
            console.error('Gagal saat mengirim request retry:', err);
            this.loadingAction = false;
            this.showAlert('danger', 'Terjadi kesalahan sistem saat memproses ulang.');
          });
        }
      },

      cancelProcess() {
        if (confirm('Apakah Anda yakin ingin membatalkan proses batch upload ini? Tindakan ini tidak dapat dibatalkan.')) {
          this.loadingAction = true;
          fetch(config.cancelUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': config.csrfToken
            }
          })
          .then(res => res.json())
          .then(data => {
            this.loadingAction = false;
            if (data.success) {
              this.showAlert('success', data.message || 'Proses batch dibatalkan.');
              this.status = 'cancelled';
              this.stopPolling();
              this.checkProgress();
            } else {
              this.showAlert('danger', data.message || 'Gagal membatalkan proses.');
            }
          })
          .catch(err => {
            console.error('Gagal saat mengirim request cancel:', err);
            this.loadingAction = false;
            this.showAlert('danger', 'Terjadi kesalahan sistem saat membatalkan proses.');
          });
        }
      },

      downloadErrorLog() {
        // Ambil data item yang berstatus failed untuk dijadikan log CSV
        const url = new URL(config.itemsUrl);
        url.searchParams.append('status', 'failed');
        url.searchParams.append('per_page', 1000); // Batas log error max 1000

        fetch(url)
          .then(res => res.json())
          .then(data => {
            const failedItems = data.data;
            if (failedItems.length === 0) {
              this.showAlert('danger', 'Tidak ada log error yang bisa diunduh.');
              return;
            }

            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "No,Nama File,NISN,Nama Siswa,Error Message,Waktu Proses\n";

            failedItems.forEach((item, index) => {
              const row = [
                index + 1,
                `"${item.original_filename}"`,
                item.siswa ? `"${item.siswa.nisn}"` : '""',
                item.siswa ? `"${item.siswa.nama_lengkap}"` : '""',
                `"${item.error_message || ''}"`,
                `"${this.formatDate(item.processed_at)}"`
              ].join(",");
              csvContent += row + "\n";
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `log_error_batch_${this.batchId}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          })
          .catch(err => {
            console.error('Gagal mengunduh log error:', err);
            this.showAlert('danger', 'Terjadi kesalahan saat membuat file CSV.');
          });
      },

      showAlert(type, message) {
        this.alert.show = true;
        this.alert.type = type;
        this.alert.message = message;
        // Auto hide after 5 seconds
        setTimeout(() => {
          this.alert.show = false;
        }, 5000);
      },

      formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleString('id-ID', {
          day: '2-digit',
          month: 'short',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
        });
      },

      getPageRange() {
        const current = this.pagination.current_page;
        const last = this.pagination.last_page;
        const delta = 2;
        const left = current - delta;
        const right = current + delta + 1;
        const range = [];
        const rangeWithDots = [];
        let l;

        for (let i = 1; i <= last; i++) {
          if (i === 1 || i === last || (i >= left && i < right)) {
            range.push(i);
          }
        }

        return range;
      }
    }));
  });
</script>
@endpush
