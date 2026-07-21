@extends('layouts/layoutMaster')

@section('title', 'Absensi Staff TU')

@section('page-style')
  <style>
    .staff-row-hover {
      transition: background 0.15s ease;
    }

    .staff-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .staff-action-btn {
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

    .staff-action-btn:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.1);
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
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: transparent;
      color: #888;
      text-decoration: none;
      transition: all 0.18s ease;
      cursor: pointer;
      line-height: 1;
      font-family: inherit;
    }

    .das-page-btn:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      border-color: rgba(255, 255, 255, 0.12);
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

    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
    }

    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--bs-info) !important;
    }

    .form-control::placeholder,
    #filterSearch::placeholder {
      color: rgba(255, 255, 255, 0.35) !important;
    }

    #perPageSelect option {
      background: #1a1a2e;
      color: #ccc;
    }

    #perPageSelect:focus {
      outline: none;
      box-shadow: none;
    }

    .extra-small {
      font-size: 0.7rem;
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
            <i class="ti tabler-users text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Absensi Staff TU
          </div>
          <h4 class="das-hero__title text-gradient-gold">Absensi Staff TU</h4>
          <p class="das-hero__subtitle">Kelola kehadiran staff tata usaha secara terstruktur.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.absensi-staff.create') }}" class="btn das-btn --primary">
          <i class="ti tabler-plus me-1"></i> Tambah Absensi
        </a>
      </div>
    </div>
  </div>

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
        <div class="col-md-4">
          <label class="form-label text-white-50 small fw-bold">Cari Staff</label>
          <input type="text" id="filterSearch" name="search" class="form-control"
            placeholder="Nama atau NIP…" value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label text-white-50 small fw-bold">Status</label>
          <select id="filterStatus" name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="hadir" @selected(request('status') === 'hadir')>Hadir</option>
            <option value="sakit" @selected(request('status') === 'sakit')>Sakit</option>
            <option value="izin" @selected(request('status') === 'izin')>Izin</option>
            <option value="alpha" @selected(request('status') === 'alpha')>Alpha</option>
            <option value="terlambat" @selected(request('status') === 'terlambat')>Terlambat</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label text-white-50 small fw-bold">Filter Tanggal</label>
          <input type="date" id="filterTanggal" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
        </div>
        <div class="col-md-2">
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
        <i class="ti tabler-list text-info"></i> Daftar Absensi Staff
      </h6>
      <div class="d-flex align-items-center gap-3">
        <select id="perPageSelect" class="form-select border-0 text-white w-auto"
          style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>

        <span
          class="das-chip --info d-none d-sm-inline-flex">{{ method_exists($absensi, 'total') ? $absensi->total() : count($absensi) }}
          Absensi</span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="absensiStaffTableContainer">
        @include('admin.absensi-staff.table')
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const container = document.getElementById('absensiStaffTableContainer');
      const perPageSelect = document.getElementById('perPageSelect');
      const filterSearch = document.getElementById('filterSearch');
      const filterStatus = document.getElementById('filterStatus');
      const filterTanggal = document.getElementById('filterTanggal');
      const filterForm = document.getElementById('filterForm');
      const resetFilterBtn = document.getElementById('resetFilterBtn');
      let searchTimeout;

      function fetchData(page = 1) {
        const search = encodeURIComponent(filterSearch.value || '');
        const perPage = perPageSelect.value || 10;
        const status = filterStatus.value || '';
        const tanggal = filterTanggal.value || '';
        const url = `{{ route('admin.absensi-staff.index') }}?page=${page}&search=${search}&per_page=${perPage}&status=${status}&tanggal=${tanggal}`;

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

      if (filterTanggal) {
        filterTanggal.addEventListener('change', function() {
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
          if (filterTanggal) filterTanggal.value = '';
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
    });
  </script>
@endsection
