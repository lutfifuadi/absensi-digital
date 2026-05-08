@extends('layouts/layoutMaster')

@section('title', 'Siswa')

@section('page-style')
  <style>
    .siswa-row-hover {
      transition: background 0.15s ease;
    }

    .siswa-row-hover:hover {
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

    /* MODAL CUSTOM */
    .das-modal {
      background: #1a1a2e !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      border-radius: 12px !important;
      overflow: hidden;
      backdrop-filter: blur(12px) saturate(180%);
    }

    .das-modal-head {
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(115, 103, 240, 0.05);
      padding: 1.25rem;
    }

    .das-modal-title {
      font-size: 1rem;
      font-weight: 700;
      color: #fff;
      margin: 0;
    }

    .das-modal-body {
      padding: 1.5rem;
    }

    /* PAGINATION */
    .das-page-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 32px;
      height: 32px;
      padding: 0 8px;
      font-size: 0.78rem;
      font-weight: 600;
      border-radius: 5px;
      border: 1px solid rgba(255,255,255,0.08);
      background: transparent;
      color: #888;
      text-decoration: none;
      transition: all 0.18s ease;
      cursor: pointer;
      line-height: 1;
      font-family: inherit;
    }
    .das-page-btn:hover {
      background: rgba(255,255,255,0.08);
      color: #fff;
      border-color: rgba(255,255,255,0.12);
    }
    .das-page-active {
      background: #7367f0 !important;
      color: #fff !important;
      border-color: #7367f0 !important;
    }
    .das-page-dots {
      border-color: transparent;
      background: transparent;
      color: #555;
      pointer-events: none;
    }
    .page-item.disabled .das-page-btn {
      opacity: 0.35;
      pointer-events: none;
    }

    /* SEARCH INPUT */
    #searchInput::placeholder { color: rgba(255,255,255,0.4); }
    #searchInput:focus {
      outline: none;
      box-shadow: none;
      background: rgba(255,255,255,0.08) !important;
      border-color: rgba(115,103,240,0.5) !important;
    }
    #perPageSelect option { background: #1a1a2e; color: #ccc; }
    #perPageSelect:focus { outline: none; box-shadow: none; }

    /* SWEETALERT2 CUSTOM PREMIUM */
    .das-swal-popup {
      background: rgba(26, 26, 46, 0.95) !important;
      backdrop-filter: blur(16px) saturate(180%) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      border-radius: 20px !important;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
    }
    .das-swal-title {
      color: #fff !important;
      font-weight: 700 !important;
      font-size: 1.5rem !important;
    }
    .das-swal-html {
      color: rgba(255, 255, 255, 0.7) !important;
      font-size: 0.95rem !important;
    }
    .das-swal-confirm {
      padding: 10px 24px !important;
      font-weight: 600 !important;
      border-radius: 10px !important;
      font-size: 0.875rem !important;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 4px 12px rgba(234, 84, 85, 0.3) !important;
    }
    .das-swal-cancel {
      padding: 10px 24px !important;
      font-weight: 600 !important;
      border-radius: 10px !important;
      font-size: 0.875rem !important;
      background: rgba(255, 255, 255, 0.05) !important;
      color: #fff !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    .das-swal-icon {
      border-color: rgba(255,255,255,0.1) !important;
    }
  </style>
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-users text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Siswa
          </div>
          <h4 class="das-hero__title text-gradient-gold">Data Siswa</h4>
          <p class="das-hero__subtitle">Kelola seluruh data peserta didik, cetak kartu QR, dan riwayat profil.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <button type="button" class="btn das-btn --secondary" data-bs-toggle="modal" data-bs-target="#importModal">
          <i class="ti tabler-file-import me-1"></i> Import
        </button>
        <a href="{{ route('admin.siswa.cetak-qr') }}" class="btn das-btn --info">
          <i class="ti tabler-qrcode me-1"></i> Cetak QR
        </a>
        <button type="button" class="btn das-btn --danger" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
          <i class="ti tabler-trash me-1"></i> Hapus Semua Siswa
        </button>
        <a href="{{ route('admin.siswa.create') }}" class="btn das-btn --primary">
          <i class="ti tabler-plus me-1"></i> Tambah Siswa
        </a>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGES --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5"></i>
      <span>{{ session('error') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5"></i>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- TABLE CARD --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Siswa
      </h6>
      <div class="d-flex align-items-center gap-3">
        <div class="position-relative" style="max-width:300px;">
          <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size:0.85rem; pointer-events:none;"></i>
          <input type="text" id="searchInput" class="form-control border-0 text-white" placeholder="Cari nama, NIS, atau NISN..." style="background: rgba(255,255,255,0.05); height:38px; padding-left:2.2rem; font-size:0.85rem;">
        </div>

        <select id="perPageSelect" class="form-select border-0 text-white w-auto" style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>

        <span class="das-chip --info d-none d-sm-inline-flex">{{ method_exists($siswa, 'total') ? $siswa->total() : count($siswa) }} Siswa</span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="siswaTableContainer">
        @include('admin.siswa.table')
      </div>
    </div>
  </div>

  <!-- Modal Delete All Students -->
  <div class="modal fade" id="deleteAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title"><i class="ti tabler-trash me-2 text-danger"></i> Hapus Semua Siswa</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="deleteAllForm" action="{{ route('admin.siswa.destroy-all') }}" method="POST">
          @csrf
          @method('DELETE')
          <div class="das-modal-body">
            <p class="mb-3">Semua data siswa akan dihapus, termasuk data absensi siswa, absensi kegiatan, dan izin sakit. Tindakan ini tidak dapat dibatalkan.</p>
            <div id="deleteAllProgress" class="d-none">
              <div class="progress" style="height:8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%"></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Menghapus data...</small>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 p-4 pt-0">
            <button type="button" class="btn das-btn --secondary" data-bs-dismiss="modal" id="deleteAllCancelBtn">Batal</button>
            <button type="button" class="btn das-btn --danger" id="deleteAllSubmitBtn">Hapus Semua</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Import -->
  <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title"><i class="ti tabler-file-import me-2 text-info"></i>Import Data Siswa</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('admin.siswa.import.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="das-modal-body">
            <div class="mb-4">
              <label class="form-label text-white-50" for="import_file">Pilih File Excel / CSV</label>
              <input id="import_file" name="import_file" type="file" class="form-control bg-dark border-secondary text-white" accept=".xlsx,.xls,.csv" required>
              <div class="form-text text-white-50 small mt-2">Gunakan format file Excel (.xlsx) atau CSV yang sesuai.</div>
            </div>
            
            <div class="alert alert-info border-0 shadow-sm" style="background: rgba(0, 207, 232, 0.1); border-radius: 8px;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="mb-0 fw-bold text-info small"><i class="ti tabler-info-circle me-1"></i>Format Kolom:</p>
                <a href="{{ route('admin.siswa.download-sample') }}" class="btn btn-sm btn-label-info py-0 px-2" style="font-size: 0.65rem;">
                   <i class="ti tabler-download me-1"></i> Download Sampel
                </a>
              </div>
              <div class="d-flex flex-wrap gap-2">
                @foreach (['nis', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'no_hp', 'no_hp_ortu', 'kelas', 'tahun_akademik', 'status'] as $col)
                  <span class="badge bg-label-info" style="font-size: 0.65rem;">{{ $col }}</span>
                @endforeach
              </div>
            </div>
          </div>
          <div class="px-4 pb-4 pt-2 d-flex gap-2">
            <button type="button" class="btn btn-label-secondary w-100" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary w-100">
              <i class="ti tabler-upload me-1"></i> Mulai Import
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const container = document.getElementById('siswaTableContainer');
      const searchInput = document.getElementById('searchInput');
      const perPageSelect = document.getElementById('perPageSelect');
      let searchTimeout;

      function fetchData(page = 1) {
        const search = encodeURIComponent(searchInput.value || '');
        const perPage = perPageSelect.value || 10;
        const url = `{{ route('admin.siswa.index') }}?page=${page}&search=${search}&per_page=${perPage}`;

        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(res => res.text())
          .then(html => {
            container.innerHTML = html;
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';

            // re-init tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl);
            });
          })
          .catch(err => {
            console.error('Fetch error:', err);
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
          });
      }

      // debounce search
      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchData(1), 450);
      });

      perPageSelect.addEventListener('change', function() {
        fetchData(1);
      });

      // pagination clicks (capture delegated events)
      container.addEventListener('click', function(e) {
        const link = e.target.closest('a.das-page-btn');
        if (link) {
          e.preventDefault();
          const page = link.dataset.page || new URL(link.href).searchParams.get('page') || 1;
          fetchData(page);
        }
      });

      // Individual delete AJAX handler (delegated, works after fetchData re-render)
      container.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-hapus-siswa');
        if (!btn) return;

        const url = btn.dataset.url;
        const nama = btn.dataset.nama || 'siswa ini';

        Swal.fire({
          title: 'Hapus Siswa?',
          html: `<div class="mt-2">Data <b class="text-danger">"${nama}"</b> akan dihapus secara permanen beserta data absensinya.</div>`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, Hapus Data',
          cancelButtonText: 'Batalkan',
          customClass: {
            popup: 'das-swal-popup',
            title: 'das-swal-title',
            htmlContainer: 'das-swal-html',
            confirmButton: 'btn btn-danger das-swal-confirm me-2',
            cancelButton: 'btn das-swal-cancel',
            icon: 'das-swal-icon'
          },
          buttonsStyling: false,
          showClass: { popup: 'animate__animated animate__fadeInUp animate__faster' },
          hideClass: { popup: 'animate__animated animate__fadeOutDown animate__faster' },
          background: 'transparent',
          backdrop: `rgba(0,0,10,0.4)`,
        }).then((result) => {
          if (!result.isConfirmed) return;

          btn.disabled = true;

          fetch(url, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
            }
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message || 'Siswa berhasil dihapus.',
                customClass: {
                  popup: 'das-swal-popup',
                  title: 'das-swal-title',
                  htmlContainer: 'das-swal-html',
                  confirmButton: 'btn btn-success das-swal-confirm'
                },
                timer: 2000,
                showConfirmButton: false,
                background: 'transparent',
              });
              fetchData(1);
            } else {
              btn.disabled = false;
              Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message || 'Terjadi kesalahan.',
                customClass: {
                  popup: 'das-swal-popup',
                  title: 'das-swal-title',
                  htmlContainer: 'das-swal-html',
                  confirmButton: 'btn btn-primary das-swal-confirm'
                },
                showClass: { popup: 'animate__animated animate__shakeX animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' },
                background: 'transparent',
                buttonsStyling: false
              });
            }
          })
          .catch(err => {
            btn.disabled = false;
            console.error('Delete siswa error:', err);
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Terjadi kesalahan koneksi.',
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                htmlContainer: 'das-swal-html',
                confirmButton: 'btn btn-primary das-swal-confirm'
              },
              showClass: { popup: 'animate__animated animate__shakeX animate__faster' },
              hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' },
              background: 'transparent',
              buttonsStyling: false
            });
          });
        });
      });


      const deleteAllSubmitBtn = document.getElementById('deleteAllSubmitBtn');
      const deleteAllCancelBtn = document.getElementById('deleteAllCancelBtn');
      const deleteAllProgress = document.getElementById('deleteAllProgress');
      const deleteAllForm = document.getElementById('deleteAllForm');

      if (deleteAllSubmitBtn && deleteAllForm) {
        deleteAllSubmitBtn.addEventListener('click', function() {
          deleteAllSubmitBtn.disabled = true;
          if (deleteAllCancelBtn) deleteAllCancelBtn.disabled = true;
          if (deleteAllProgress) deleteAllProgress.classList.remove('d-none');

          const formData = new FormData(deleteAllForm);

          fetch(deleteAllForm.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
            },
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            const modalEl = document.getElementById('deleteAllModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            if (data.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message || 'Data siswa telah dihapus.',
                customClass: {
                  popup: 'das-swal-popup',
                  title: 'das-swal-title',
                  htmlContainer: 'das-swal-html',
                  confirmButton: 'btn btn-success das-swal-confirm'
                },
                showClass: { popup: 'animate__animated animate__zoomIn animate__faster' },
                hideClass: { popup: 'animate__animated animate__zoomOut animate__faster' },
                background: 'transparent',
                buttonsStyling: false
              });
              fetchData(1);
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message || 'Terjadi kesalahan.',
                customClass: {
                  popup: 'das-swal-popup',
                  title: 'das-swal-title',
                  htmlContainer: 'das-swal-html',
                  confirmButton: 'btn btn-primary das-swal-confirm'
                },
                showClass: { popup: 'animate__animated animate__shakeX animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' },
                background: 'transparent',
                buttonsStyling: false
              });
            }
          })
          .catch(err => {
            console.error('Delete all error:', err);
            const modalEl = document.getElementById('deleteAllModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Terjadi kesalahan koneksi.',
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                htmlContainer: 'das-swal-html',
                confirmButton: 'btn btn-primary das-swal-confirm'
              },
              showClass: { popup: 'animate__animated animate__shakeX animate__faster' },
              hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' },
              background: 'transparent',
              buttonsStyling: false
            });
          })
          .finally(() => {
            deleteAllSubmitBtn.disabled = false;
            if (deleteAllCancelBtn) deleteAllCancelBtn.disabled = false;
            if (deleteAllProgress) deleteAllProgress.classList.add('d-none');
          });
        });
      }

      // Toast helper (deprecated in favor of Swal, but keeping for compatibility)
      function showToast(type, message) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          icon: type === 'success' ? 'success' : 'error',
          title: message,
          customClass: {
            popup: 'das-swal-popup border-0 shadow-lg',
            title: 'das-swal-title fs-6',
          },
          background: 'rgba(26, 26, 46, 0.9)',
        });
      }

      // initial tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>
@endsection
