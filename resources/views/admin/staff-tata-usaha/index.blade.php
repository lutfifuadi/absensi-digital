@extends('layouts/layoutMaster')

@section('title', 'Staff TU')

@section('page-style')
  <style>
    .staff-row-hover {
      transition: background 0.15s ease;
    }

    .staff-row-hover:hover {
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

    .das-btn.--purple {
      background: rgba(115, 103, 240, 0.15);
      border-color: rgba(115, 103, 240, 0.35);
      color: #a5a2f7;
    }
    .das-btn.--purple:hover {
      background: rgba(115, 103, 240, 0.3);
      color: #ffffff;
      box-shadow: 0 0 12px rgba(115, 103, 240, 0.2);
    }

    .text-purple {
      color: #a5a2f7 !important;
    }
  </style>
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
            <i class="ti tabler-building-fortress text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Staff TU
          </div>
          <h4 class="das-hero__title text-gradient-gold">Data Staff TU</h4>
          <p class="das-hero__subtitle">Kelola data administrasi staff, jabatan, dan kredensial.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.staff-tata-usaha.cetak-qr') }}" class="btn das-btn --info">
          <i class="ti tabler-qrcode me-1"></i> Cetak QR Massal
        </a>
        <button type="button" class="btn das-btn --purple" id="toggleCetakKartuBtn">
          <i class="ti tabler-id me-1"></i> Cetak Kartu
        </button>
        <a href="{{ route('admin.staff-tata-usaha.create') }}" class="btn das-btn --primary">
          <i class="ti tabler-plus me-1"></i> Tambah Staff
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

  {{-- FILTER PANEL --}}
  <div class="das-panel mb-4">
    <div class="das-panel__body">
      <form id="filterForm" method="GET" class="row gy-3 gx-3 align-items-end">
        <div class="col-md-5">
          <label class="form-label text-white-50 small fw-bold">Cari Staff TU</label>
          <input type="text" id="filterSearch" name="search" class="form-control"
            placeholder="Nama, NIP, jabatan, No. HP, email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label text-white-50 small fw-bold">Status</label>
          <select id="filterStatus" name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
          </select>
        </div>
        <div class="col-md-4">
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

  {{-- FORM CETAK KARTU PILIHAN --}}
  <form id="cetakKartuStaffForm" method="POST" action="{{ route('admin.staff-tata-usaha.cetak-kartu-pilihan') }}" class="d-none mb-3">
    @csrf
    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background: rgba(115, 103, 240, 0.1); border: 1px solid rgba(115, 103, 240, 0.2);">
      <i class="ti tabler-id text-purple fs-5"></i>
      <span class="small text-white-50">Dipilih: <strong id="staffSelectedCount">0</strong> staff</span>
      <span class="text-white-50 mx-1">|</span>
      <button type="button" class="btn btn-sm btn-label-secondary" onclick="$('.staff-checkbox').prop('checked', false).trigger('change');">
        <i class="ti tabler-x"></i> Batal
      </button>
      <button type="submit" class="btn btn-sm btn-label-primary ms-auto">
        <i class="ti tabler-printer me-1"></i> Cetak Kartu Pilihan
      </button>
    </div>
  </form>

  {{-- TABLE CARD --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Staff Administrasi
      </h6>
      <div class="d-flex align-items-center gap-3">
        <select id="perPageSelect" class="form-select border-0 text-white w-auto"
          style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
        </select>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="staffTableContainer">
        @include('admin.staff-tata-usaha.table')
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      const container = document.getElementById('staffTableContainer');
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
        const status = filterStatus ? filterStatus.value || '' : '';
        const url = `{{ route('admin.staff-tata-usaha.index') }}?page=${page}&search=${search}&per_page=${perPage}&sort_by=${currentSortBy}&sort_dir=${currentSortDir}&status=${status}`;

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

            // re-init tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // re-init checkbox counter (karena checkbox di-render ulang)
            updateStaffSelectedCount();

            // re-init checkAll listener
            const checkAll = document.getElementById('checkAllStaff');
            if (checkAll) {
              checkAll.addEventListener('change', function() {
                $('.staff-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
              });
            }
          })
          .catch(err => {
            console.error('Fetch error:', err);
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
          });
      }

      function updateStaffSelectedCount() {
        var count = $('.staff-checkbox:checked').length;
        $('#staffSelectedCount').text(count);
      }

      // debounce search
      if (filterSearch) {
        filterSearch.addEventListener('input', function() {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => fetchData(1), 450);
        });
      }

      // filter status change
      if (filterStatus) {
        filterStatus.addEventListener('change', function() {
          fetchData(1);
        });
      }

      // form submit
      if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
          e.preventDefault();
          fetchData(1);
        });
      }

      // reset button
      if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
          if (filterSearch) filterSearch.value = '';
          if (filterStatus) filterStatus.value = '';
          fetchData(1);
        });
      }

      perPageSelect.addEventListener('change', function() {
        fetchData(1);
      });

      // sort clicks - delegated
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

      // pagination clicks - delegated
      container.addEventListener('click', function(e) {
        const pageBtn = e.target.closest('.das-page-btn');
        if (pageBtn && !pageBtn.classList.contains('das-page-active') && !pageBtn.parentNode.classList.contains('disabled')) {
          e.preventDefault();
          const page = pageBtn.getAttribute('data-page') || pageBtn.textContent.trim();
          if (page && !isNaN(page)) {
            fetchData(page);
          }
        }
      });

      // ─── Cetak Kartu Toggle ───────────────────────────────────────────
      const toggleCetakKartuBtn = document.getElementById('toggleCetakKartuBtn');
      if (toggleCetakKartuBtn) {
        toggleCetakKartuBtn.addEventListener('click', function() {
          document.getElementById('cetakKartuStaffForm').classList.toggle('d-none');
        });
      }

      // ─── Cetak Kartu Checkbox Logic ───────────────────────────────────
      $(document).on('change', '.staff-checkbox', function() {
        updateStaffSelectedCount();
      });

      // Checkbox update count awal
      updateStaffSelectedCount();
    });
  </script>
@endsection
