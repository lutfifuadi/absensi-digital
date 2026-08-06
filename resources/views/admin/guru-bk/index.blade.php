@extends('layouts/layoutMaster')

@section('title', 'Data Guru BK')

@section('page-style')
  <style>
    .bk-row-hover {
      transition: background 0.15s ease;
    }

    .bk-row-hover:hover {
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
            <i class="ti tabler-user-heart text-warning"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Data Civitas</a> / Guru BK
          </div>
          <h4 class="das-hero__title text-gradient-gold">Data Guru BK</h4>
          <p class="das-hero__subtitle">Kelola daftar guru bimbingan konseling dan hak akses pemantauan siswa lintas kelas.</p>
        </div>
      </div>

      <div class="das-hero__actions d-flex gap-2">
        <button type="button" class="das-btn das-btn--warning" data-bs-toggle="modal" data-bs-target="#modalAssignGuruBk">
          <i class="ti tabler-user-plus me-1"></i> Tetapkan Guru BK
        </button>
        <a href="{{ route('bk.dashboard') }}" class="das-btn das-btn--info">
          <i class="ti tabler-dashboard me-1"></i> Dashboard BK
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
          <label class="form-label text-white-50 small fw-bold">Cari Guru BK</label>
          <input type="text" id="filterSearch" name="search" class="form-control"
            placeholder="Nama, NIP, Email…" value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label text-white-50 small fw-bold">Status</label>
          <select id="filterStatus" name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
          </select>
        </div>
        <div class="col-md-3">
          <div class="d-flex gap-2">
            <button type="submit" class="das-btn das-btn--info w-100">
              <i class="ti tabler-search me-1"></i> Cari
            </button>
            <button type="button" id="resetFilterBtn" class="das-btn das-btn--secondary" title="Reset">
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
        <i class="ti tabler-list text-warning"></i> Daftar Guru Bimbingan Konseling (BK)
      </h6>
      <div class="d-flex align-items-center gap-3">
        <select id="perPageSelect" class="form-select border-0 text-white w-auto"
          style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
        </select>

        <span class="das-chip --warning d-none d-sm-inline-flex" id="totalCountSpan">
          {{ method_exists($guruBkUsers, 'total') ? $guruBkUsers->total() : count($guruBkUsers) }} Guru BK
        </span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="guruBkTableContainer">
        @include('admin.guru-bk.table')
      </div>
    </div>
  </div>

  {{-- Modal Assign Guru BK --}}
  <div class="modal fade" id="modalAssignGuruBk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal">
        <div class="das-modal__head das-modal__head--warning">
          <h5 class="das-modal__title"><i class="ti tabler-user-plus me-2 text-warning"></i> Tetapkan Guru Sebagai Guru BK</h5>
        </div>
        <form action="{{ route('admin.guru-bk.store') }}" method="POST">
          @csrf
          <div class="das-modal__body p-4">
            <div class="mb-3">
              <label class="form-label text-white-50">Pilih Guru Aktif <span class="text-danger">*</span></label>
              <select name="guru_id" class="form-select bg-dark text-white border-white-10" required>
                <option value="">-- Pilih Guru --</option>
                @foreach($availableGurus as $g)
                  <option value="{{ $g->id }}">{{ $g->nama_lengkap }} (NIP: {{ $g->nip ?? '-' }})</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label text-white-50">Batas Bimbingan Konseling / Bulan</label>
              <input type="number" name="konseling_limit" class="form-control bg-dark text-white border-white-10" value="10" min="1">
            </div>
          </div>
          <div class="das-modal__foot d-flex justify-content-end gap-2 p-3 border-top border-white-10">
            <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal"><i class="ti tabler-x"></i> Batal</button>
            <button type="submit" class="das-btn das-btn--warning-solid"><i class="ti tabler-check"></i> Simpan Guru BK</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Impersonate -->
  <div class="modal fade" id="impersonateConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal">
        <div class="das-modal__head das-modal__head--success">
          <h5 class="das-modal__title"><i class="ti tabler-user-share me-2"></i> Konfirmasi Impersonate</h5>
        </div>
        <div class="das-modal__body text-center p-4">
          <div class="dev-confirm-success-icon">
            <div class="dev-confirm-success-icon__ring"></div>
            <i class="ti tabler-user-share dev-confirm-success-icon__symbol"></i>
          </div>
          <p class="dev-confirm-message__main">Anda akan masuk ke akun <b id="impersonateBkName" class="text-success"></b>.</p>
          <p class="dev-confirm-message__warning text-start">
            <i class="ti tabler-info-circle"></i>
            <span>Seluruh tindakan & aktivitas yang dilakukan selama sesi impersonate akan dicatat dalam log sistem.</span>
          </p>
        </div>
        <div class="das-modal__foot d-flex justify-content-end gap-2">
          <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal"><i class="ti tabler-x"></i> Batal</button>
          <button type="button" id="confirmImpersonateBtn" class="das-btn das-btn--success-solid"><i class="ti tabler-check"></i> Ya, Lanjutkan</button>
        </div>
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

      const container = document.getElementById('guruBkTableContainer');
      const perPageSelect = document.getElementById('perPageSelect');
      const filterSearch = document.getElementById('filterSearch');
      const filterStatus = document.getElementById('filterStatus');
      const filterForm = document.getElementById('filterForm');
      const resetFilterBtn = document.getElementById('resetFilterBtn');
      let searchTimeout;

      let currentSortBy = '{{ $sortBy ?? 'nama_lengkap' }}';
      let currentSortDir = '{{ $sortDir ?? 'asc' }}';

      function fetchData(page = 1) {
        const search = encodeURIComponent(filterSearch.value || '');
        const perPage = perPageSelect.value || 10;
        const status = filterStatus.value || '';
        const url = `{{ route('admin.guru-bk.index') }}?page=${page}&search=${search}&per_page=${perPage}&sort_by=${currentSortBy}&sort_dir=${currentSortDir}&status=${status}`;

        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

        fetch(url, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(res => res.text())
          .then(html => {
            container.innerHTML = html;
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';

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

      if (filterSearch) {
        filterSearch.addEventListener('input', function() {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => fetchData(1), 300);
        });
      }

      if (filterStatus) {
        filterStatus.addEventListener('change', function() {
          fetchData(1);
        });
      }

      if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
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
          filterSearch.value = '';
          filterStatus.value = '';
          fetchData(1);
        });
      }

      // Handle Sorting & Pagination Delegation
      document.addEventListener('click', function(e) {
        const sortHeader = e.target.closest('.sortable');
        if (sortHeader) {
          const sortBy = sortHeader.getAttribute('data-sort-by');
          if (sortBy === currentSortBy) {
            currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
          } else {
            currentSortBy = sortBy;
            currentSortDir = 'asc';
          }
          fetchData(1);
          return;
        }

        const paginationLink = e.target.closest('.pagination a');
        if (paginationLink) {
          e.preventDefault();
          const href = paginationLink.getAttribute('href');
          if (href) {
            const urlObj = new URL(href);
            const page = urlObj.searchParams.get('page') || 1;
            fetchData(page);
          }
        }
      });

      // Handle Impersonate Modal
      let impersonateUrl = '';
      document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-impersonate-bk');
        if (btn) {
          impersonateUrl = btn.getAttribute('data-url');
          const nama = btn.getAttribute('data-nama');
          document.getElementById('impersonateBkName').textContent = nama;
          const modal = new bootstrap.Modal(document.getElementById('impersonateConfirmModal'));
          modal.show();
        }
      });

      const confirmBtn = document.getElementById('confirmImpersonateBtn');
      if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
          if (!impersonateUrl) return;
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = impersonateUrl;
          const csrf = document.createElement('input');
          csrf.type = 'hidden';
          csrf.name = '_token';
          csrf.value = '{{ csrf_token() }}';
          form.appendChild(csrf);
          document.body.appendChild(form);
          form.submit();
        });
      }

      // Handle Delete / Remove Guru BK
      document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-delete-guru-bk');
        if (btn) {
          const guruId = btn.getAttribute('data-id');
          const nama = btn.getAttribute('data-nama');

          if (window.Swal) {
            Swal.fire({
              title: 'Cabut Status Guru BK?',
              html: `Apakah Anda yakin ingin mencabut status Guru BK dari <b>${nama}</b>?`,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Ya, Cabut Status',
              cancelButtonText: 'Batal',
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                htmlContainer: 'das-swal-html',
                confirmButton: 'das-swal-confirm btn btn-danger',
                cancelButton: 'das-swal-cancel btn btn-secondary',
                icon: 'das-swal-icon'
              }
            }).then((result) => {
              if (result.isConfirmed) {
                fetch(`/admin/guru-bk/${guruId}`, {
                  method: 'DELETE',
                  headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                  }
                })
                .then(res => res.json())
                .then(data => {
                  if (data.success) {
                    fetchData(1);
                  }
                });
              }
            });
          }
        }
      });
    });
  </script>
@endsection
