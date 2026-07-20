@extends('layouts/layoutMaster')

@section('title', 'Wali Kelas')

@section('page-style')
  <style>
    .wk-row-hover {
      transition: background 0.15s ease;
    }

    .wk-row-hover:hover {
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

    /* SWEETALERT2 CUSTOM PREMIUM */
    .das-swal-popup {
      background: rgba(26, 26, 46, 0.95) !important;
      backdrop-filter: blur(16px) saturate(180%) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      border-radius: 5px !important;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
    }

    .das-swal-title {
      color: #fff !important;
      font-weight: 700 !important;
      font-size: 1.5rem !important;
      text-align: center !important;
      width: 100% !important;
      max-width: none !important;
      max-inline-size: none !important;
    }

    .das-swal-html {
      color: rgba(255, 255, 255, 0.7) !important;
      font-size: 0.95rem !important;
    }

    .das-swal-confirm {
      padding: 10px 24px !important;
      font-weight: 600 !important;
      border-radius: 5px !important;
      font-size: 0.875rem !important;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 4px 12px rgba(234, 84, 85, 0.3) !important;
    }

    .das-swal-cancel {
      padding: 10px 24px !important;
      font-weight: 600 !important;
      border-radius: 5px !important;
      font-size: 0.875rem !important;
      background: rgba(255, 255, 255, 0.05) !important;
      color: #fff !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    .das-swal-icon {
      border-color: rgba(255, 255, 255, 0.1) !important;
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
            <i class="ti tabler-users-group text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Wali Kelas
          </div>
          <h4 class="das-hero__title text-gradient-gold">Data Wali Kelas</h4>
          <p class="das-hero__subtitle">Kelola seluruh data wali kelas, jabatan, dan akses sistem.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.wali-kelas.cetak-qr') }}" class="btn das-btn --info">
          <i class="ti tabler-qrcode me-1"></i> Cetak QR Massal
        </a>
        <a href="{{ route('admin.wali-kelas.create') }}" class="btn das-btn --primary">
          <i class="ti tabler-plus me-1"></i> Tambah Wali Kelas
        </a>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGE --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('info'))
    <div class="alert alert-info alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-info-circle fs-5"></i>
      <span>{{ session('info') }}</span>
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

  {{-- FILTER PANEL --}}
  <div class="das-panel mb-4">
    <div class="das-panel__body">
      <form id="filterForm" method="GET" class="row gy-3 gx-3 align-items-end">
        <div class="col-md-5">
          <label class="form-label text-white-50 small fw-bold">Cari Wali Kelas</label>
          <input type="text" id="filterSearch" name="search" class="form-control"
            placeholder="Nama, NIP, Email, atau Mapel…" value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label text-white-50 small fw-bold">Status</label>
          <select id="filterStatus" name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
            <option value="belum lengkap" @selected(request('status') === 'belum lengkap')>Belum Lengkap</option>
          </select>
        </div>
        <div class="col-md-3">
          <div class="d-flex gap-2">
            <button type="submit" class="btn das-btn --info w-100">
              <i class="ti tabler-search me-1"></i> Cari
            </button>
            <button type="button" id="resetFilterBtn" class="btn das-btn --secondary" title="Reset">
              <i class="ti tabler-refresh"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- TABLE CARD --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Wali Kelas
      </h6>
      <div class="d-flex align-items-center gap-3">
        <select id="perPageSelect" class="form-select border-0 text-white w-auto"
          style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>

        <span class="das-chip --info d-none d-sm-inline-flex" id="totalCountSpan">
          {{ method_exists($waliKelasUsers, 'total') ? $waliKelasUsers->total() : count($waliKelasUsers) }} Wali Kelas
        </span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="waliKelasTableContainer">
        @include('admin.wali-kelas.table')
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
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      const container = document.getElementById('waliKelasTableContainer');
      const perPageSelect = document.getElementById('perPageSelect');
      const filterSearch = document.getElementById('filterSearch');
      const filterStatus = document.getElementById('filterStatus');
      const filterForm = document.getElementById('filterForm');
      const resetFilterBtn = document.getElementById('resetFilterBtn');
      const totalCountSpan = document.getElementById('totalCountSpan');
      let searchTimeout;

      let currentSortBy = '{{ $sortBy ?? 'nama_lengkap' }}';
      let currentSortDir = '{{ $sortDir ?? 'asc' }}';

      function fetchData(page = 1) {
        const search = encodeURIComponent(filterSearch.value || '');
        const perPage = perPageSelect.value || 10;
        const status = filterStatus.value || '';
        const url = `{{ route('admin.wali-kelas.index') }}?page=${page}&search=${search}&per_page=${perPage}&sort_by=${currentSortBy}&sort_dir=${currentSortDir}&status=${status}`;

        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

        fetch(url, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(res => {
            // Update total count header if we received total records header (like standard count or page)
            // or just parse from HTML. We can extract from layout or pass via response but since we only return partial table:
            return res.text();
          })
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
      if (filterSearch) {
        filterSearch.addEventListener('input', function() {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => fetchData(1), 450);
        });
      }

      if (filterStatus) {
        filterStatus.addEventListener('change', function() {
          fetchData(1);
        });
      }

      if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
          e.preventDefault();
          fetchData(1);
        });
      }

      if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
          if (filterSearch) filterSearch.value = '';
          if (filterStatus) filterStatus.value = '';
          fetchData(1);
        });
      }

      if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
          fetchData(1);
        });
      }

      // pagination clicks (capture delegated events)
      container.addEventListener('click', function(e) {
        const link = e.target.closest('a.das-page-btn');
        if (link) {
          e.preventDefault();
          const page = link.dataset.page || new URL(link.href).searchParams.get('page') || 1;
          fetchData(page);
        }
      });

      // sort clicks (capture delegated events)
      container.addEventListener('click', function(e) {
        const th = e.target.closest('th.sortable');
        if (th) {
          const sortBy = th.dataset.sortBy;
          if (currentSortBy === sortBy) {
            currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
          } else {
            currentSortBy = sortBy;
            currentSortDir = 'asc';
          }
          fetchData(1);
        }
      });

      // Event delegation for delete buttons
      document.addEventListener('click', function(e) {
        const btnDeleteWk = e.target.closest('.btn-hapus-wali-kelas');
        const btnDeleteUserWk = e.target.closest('.btn-hapus-user-wali-kelas');
        const btn = btnDeleteWk || btnDeleteUserWk;

        if (!btn) return;

        const url = btn.dataset.url;
        const nama = btn.dataset.nama || 'Wali Kelas';
        const isUserOnly = !!btnDeleteUserWk;

        const titleText = isUserOnly ? 'Hapus Akun Wali Kelas?' : 'Hapus Wali Kelas?';
        const htmlText = isUserOnly 
          ? `<div class="mt-2">Akun <b class="text-danger">"${nama}"</b> (profil belum lengkap) akan dihapus secara permanen dari sistem.</div>`
          : `<div class="mt-2">Data <b class="text-danger">"${nama}"</b> beserta relasi guru akan dihapus secara permanen dari sistem.</div>`;

        Swal.fire({
          title: titleText,
          html: htmlText,
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
          showClass: {
            popup: 'animate__animated animate__fadeInUp animate__faster'
          },
          hideClass: {
            popup: 'animate__animated animate__fadeOutDown animate__faster'
          },
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
          .then(res => {
            if (!res.ok) {
              return res.json().then(errData => {
                throw new Error(errData.message || 'Terjadi kesalahan pada server.');
              });
            }
            return res.json();
          })
          .then(data => {
            if (data.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message || 'Data berhasil dihapus.',
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
                showClass: {
                  popup: 'animate__animated animate__shakeX animate__faster'
                },
                hideClass: {
                  popup: 'animate__animated animate__fadeOut animate__faster'
                },
                background: 'transparent',
                buttonsStyling: false
              });
            }
          })
          .catch(err => {
            btn.disabled = false;
            console.error('Delete error:', err);
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: err.message || 'Terjadi kesalahan koneksi.',
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                htmlContainer: 'das-swal-html',
                confirmButton: 'btn btn-primary das-swal-confirm'
              },
              showClass: {
                popup: 'animate__animated animate__shakeX animate__faster'
              },
              hideClass: {
                popup: 'animate__animated animate__fadeOut animate__faster'
              },
              background: 'transparent',
              buttonsStyling: false
            });
          });
        });
      });
    });
  </script>
@endsection
