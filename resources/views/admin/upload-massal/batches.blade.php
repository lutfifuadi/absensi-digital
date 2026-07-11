@extends('layouts/layoutMaster')

@section('title', 'Riwayat Batch Upload Massal')

@section('page-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
<style>
  .batch-row-hover {
    transition: background 0.15s ease;
  }
  .batch-row-hover:hover {
    background: rgba(255, 255, 255, 0.04) !important;
  }
  .action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    transition: all 0.2s ease;
    border: none;
    background: rgba(255, 255, 255, 0.05);
    color: inherit;
  }
  .action-btn:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.1);
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
        <div class="das-hero__logo-placeholder" style="width:52px;height:52px;border-radius:12px;background:rgba(0,207,232,0.15);display:flex;align-items:center;justify-content:center;border:1px solid rgba(0,207,232,0.3);">
          <i class="ti tabler-history text-info fs-3"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>

      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          <a href="{{ route('admin.upload-massal.index') }}" class="text-white text-decoration-none">Upload Massal</a> / Riwayat Batch
        </div>
        <h4 class="das-hero__title text-gradient-gold">Riwayat Batch Upload</h4>
        <p class="das-hero__subtitle">Kelola dan pantau seluruh riwayat upload massal foto siswa ke Google Drive.</p>
      </div>
    </div>
    
    <div class="das-hero__actions d-flex gap-2 flex-wrap justify-content-end">
      <button type="button" id="btnResetAllBatches" class="btn das-btn --danger">
        <i class="ti tabler-trash me-1"></i> Reset Semua Log
      </button>
      <a href="{{ route('admin.upload-massal.index') }}" class="btn das-btn --primary">
        <i class="ti tabler-plus me-1"></i> Buat Upload Baru
      </a>
    </div>
  </div>
</div>

{{-- FLASH MESSAGES --}}
@if (session('success'))
  <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
    <i class="ti tabler-circle-check fs-5"></i>
    <span>{{ session('success') }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
@endif

@if (session('error'))
  <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
    <i class="ti tabler-alert-circle fs-5"></i>
    <span>{{ session('error') }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- FILTER & TABLE PANEL --}}
<div class="das-panel">
  <div class="das-panel__header border-bottom py-3 px-4" style="border-color:rgba(255,255,255,0.08) !important;">
    <form method="GET" action="{{ route('admin.upload-massal.batches') }}" class="row gy-2 gx-2 align-items-end">
      <div class="col-12 col-md-3">
        <label class="form-label text-white-50 small mb-1 fw-bold">CARI BATCH</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text bg-transparent text-white-50 border-white-10" style="border-color:rgba(255,255,255,0.1);"><i class="ti tabler-search"></i></span>
          <input type="text" name="search" class="form-control bg-transparent text-white border-white-10" style="border-color:rgba(255,255,255,0.1);" placeholder="Nama batch..." value="{{ request('search') }}">
        </div>
      </div>
      
      <div class="col-6 col-md-2">
        <label class="form-label text-white-50 small mb-1 fw-bold">STATUS</label>
        <select name="status" class="form-select bg-transparent text-white border-white-10" style="background-color: #1e293b !important; border-color:rgba(255,255,255,0.1);" onchange="this.form.submit()">
          <option value="" style="background: #1e293b;">Semua Status</option>
          <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }} style="background: #1e293b;">Pending</option>
          <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }} style="background: #1e293b;">Processing</option>
          <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }} style="background: #1e293b;">Completed</option>
          <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }} style="background: #1e293b;">Failed</option>
          <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }} style="background: #1e293b;">Cancelled</option>
        </select>
      </div>

      <div class="col-6 col-md-2">
        <label class="form-label text-white-50 small mb-1 fw-bold">SUMBER</label>
        <select name="sumber" class="form-select bg-transparent text-white border-white-10" style="background-color: #1e293b !important; border-color:rgba(255,255,255,0.1);" onchange="this.form.submit()">
          <option value="" style="background: #1e293b;">Semua Sumber</option>
          <option value="web" {{ request('sumber') === 'web' ? 'selected' : '' }} style="background: #1e293b;">Web (Form)</option>
          <option value="zip" {{ request('sumber') === 'zip' ? 'selected' : '' }} style="background: #1e293b;">ZIP File</option>
        </select>
      </div>

      <div class="col-12 col-md-5 text-md-end mt-2 mt-md-0">
        <div class="d-flex gap-2 justify-content-md-end">
          <button type="submit" class="btn btn-label-primary fw-semibold">
            <i class="ti tabler-filter me-1"></i> Filter
          </button>
          @if (request()->filled('search') || request()->filled('status') || request()->filled('sumber'))
            <a href="{{ route('admin.upload-massal.batches') }}" class="btn btn-label-secondary fw-semibold">
              <i class="ti tabler-refresh me-1"></i> Reset
            </a>
          @endif
        </div>
      </div>
    </form>
  </div>
  <div class="das-panel__body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="color:inherit;">
        <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
          <tr>
            <th class="ps-4 py-3" style="width:46px;">#</th>
            <th class="py-3">Tanggal Dibuat</th>
            <th class="py-3">Nama Batch</th>
            <th class="py-3 text-center">Sumber</th>
            <th class="py-3 text-center">Total</th>
            <th class="py-3 text-center">Sukses</th>
            <th class="py-3 text-center">Gagal</th>
            <th class="py-3 text-center">Status</th>
            <th class="py-3">Pembuat</th>
            <th class="py-3 pe-4 text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($batches as $batch)
            <tr class="batch-row-hover">
              <td class="ps-4 text-white-50 small">
                {{ ($batches->currentPage() - 1) * $batches->perPage() + $loop->iteration }}
              </td>
              <td class="text-white-50 small">
                {{ \Carbon\Carbon::parse($batch->created_at)->locale('id')->translatedFormat('d M Y H:i') }}
              </td>
              <td>
                <div class="fw-bold text-white fs-6">{{ $batch->nama_batch }}</div>
              </td>
              <td class="text-center">
                <span class="badge text-uppercase {{ $batch->sumber === 'zip' ? 'bg-label-warning' : 'bg-label-info' }}">
                  {{ $batch->sumber }}
                </span>
              </td>
              <td class="text-center fw-semibold text-white">{{ $batch->total_items }}</td>
              <td class="text-center fw-semibold text-success">{{ $batch->success_count }}</td>
              <td class="text-center fw-semibold text-danger">{{ $batch->failed_count }}</td>
              <td class="text-center">
                <span class="das-chip text-capitalize" :class="{
                  '--primary': '{{ $batch->status }}' === 'pending',
                  '--warning': '{{ $batch->status }}' === 'processing',
                  '--success': '{{ $batch->status }}' === 'completed',
                  '--danger': '{{ $batch->status }}' === 'failed',
                  '--secondary': '{{ $batch->status }}' === 'cancelled'
                }">
                  {{ $batch->status }}
                </span>
              </td>
              <td class="text-white-50">
                {{ $batch->user?->name ?? 'Sistem' }}
              </td>
              <td class="pe-4 text-end">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ route('admin.upload-massal.batches.show', $batch->id) }}" class="action-btn text-info" title="Detail Progress" data-bs-toggle="tooltip">
                    <i class="ti tabler-eye fs-5"></i>
                  </a>
                  @if (auth()->user()->isSuperAdmin())
                    <form action="#" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data batch ini dari riwayat? Tindakan ini tidak akan menghapus foto di Google Drive.');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="action-btn text-danger" title="Hapus Batch" data-bs-toggle="tooltip" disabled>
                        <i class="ti tabler-trash fs-5"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="text-center py-5 text-white-50">
                <i class="ti tabler-notes-off fs-1 d-block mb-3 opacity-30"></i>
                Tidak ada data batch upload yang ditemukan.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if ($batches->hasPages())
      <div class="d-flex justify-content-between align-items-center py-3 px-4 border-top" style="border-color:rgba(255,255,255,0.08) !important;">
        <span class="text-white-50 small">
          Menampilkan {{ $batches->firstItem() }} sampai {{ $batches->lastItem() }} dari {{ $batches->total() }} batch
        </span>
        <div class="das-pagination">
          {{ $batches->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
        </div>
      </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi tooltip Bootstrap jika ada
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ── RESET SEMUA LOG ──────────────────────────────────────────────────
    const resetBtn = document.getElementById('btnResetAllBatches');
    if (resetBtn) {
      resetBtn.addEventListener('click', function(e) {
        e.preventDefault();

        Swal.fire({
          title: '<span class="text-danger">⚠️ Reset Semua Log Batch?</span>',
          html: `
            <div class="text-start px-2">
              <p class="mb-2"><strong>Apakah Anda yakin ingin menghapus semua riwayat batch upload?</strong></p>
              <ul class="text-start mb-0" style="font-size:0.9rem;">
                <li class="mb-1">Semua data batch dan item akan <strong>dihapus permanen</strong> dari database.</li>
                <li class="mb-1">File foto siswa di <strong>Google Drive TIDAK</strong> akan terhapus.</li>
                <li class="mb-1">File temporary lokal di storage akan dihapus.</li>
                <li class="text-danger fw-bold">Tindakan ini tidak dapat dibatalkan!</li>
              </ul>
              <hr>
              <div class="mb-1">Ketik <strong>"RESET"</strong> untuk konfirmasi:</div>
              <input type="text" id="resetConfirmInput" class="form-control text-center fw-bold" placeholder="Ketik RESET" autocomplete="off">
            </div>
          `,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, Reset Semua!',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#d44c4d',
          cancelButtonColor: '#6c757d',
          customClass: {
            confirmButton: 'btn das-btn --danger px-4 py-2 me-3',
            cancelButton: 'btn das-btn --secondary px-4 py-2',
          },
          buttonsStyling: false,
          didOpen: () => {
            // Focus ke input setelah modal terbuka
            const inputEl = document.getElementById('resetConfirmInput');
            if (inputEl) {
              inputEl.focus();
              // Listen untuk enter key
              inputEl.addEventListener('keydown', function(ev) {
                if (ev.key === 'Enter') {
                  Swal.clickConfirm();
                }
              });
            }
          },
          preConfirm: () => {
            const inputVal = document.getElementById('resetConfirmInput').value;
            if (inputVal !== 'RESET') {
              Swal.showValidationMessage('Anda harus mengetik "RESET" untuk konfirmasi.');
              return false;
            }
            return true;
          },
          allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
          if (result.isConfirmed) {
            // Kirim request DELETE
            fetch('{{ route('admin.upload-massal.batches.reset-all') }}', {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
              },
            })
            .then(response => {
              return response.json().then(data => ({
                status: response.status,
                ok: response.ok,
                data: data,
              }));
            })
            .then(({ status, ok, data }) => {
              if (ok && data.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Berhasil!',
                  text: data.message || 'Semua log batch berhasil direset.',
                  confirmButtonColor: '#28a745',
                  confirmButtonText: 'OK',
                }).then(() => {
                  window.location.reload();
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Gagal!',
                  text: data.message || 'Terjadi kesalahan saat mereset batch.',
                  confirmButtonColor: '#d44c4d',
                  confirmButtonText: 'Tutup',
                });
              }
            })
            .catch(() => {
              // Fallback: reload jika ada error jaringan
              window.location.reload();
            });
          }
        });
      });
    }
  });
</script>
@endpush
