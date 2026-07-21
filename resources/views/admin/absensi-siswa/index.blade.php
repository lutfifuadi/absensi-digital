@extends('layouts/layoutMaster')

@section('title', 'Absensi Siswa')

@section('page-style')
  <style>
    .absensi-row-hover {
      transition: background 0.15s ease;
    }

    .absensi-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .absensi-action-btn {
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

    .absensi-action-btn:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.1);
    }

    /* SEARCH INPUT STYLING (sama seperti halaman siswa) */
    #filterSearch::placeholder {
      color: rgba(255, 255, 255, 0.4);
    }

    #filterSearch:focus {
      outline: none;
      box-shadow: none;
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: rgba(115, 103, 240, 0.5) !important;
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

    /* PAGINATION (sama seperti halaman siswa) */
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
            <i class="ti tabler-calendar-check"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Log Kehadiran Realtime
          </div>
          <h4 class="das-hero__title text-gradient-gold">Absensi Siswa</h4>
          <p class="das-hero__subtitle">Catat dan pantau kehadiran siswa harian dengan efisien.</p>
        </div>
      </div>

      @if(!$isWaliKelas)
      <div class="das-hero__actions">
        <div class="d-flex gap-2">
          <a href="{{ route('admin.absensi-siswa.scan') }}" class="das-btn das-btn--success">
            <i class="ti tabler-qrcode me-1"></i> Mode Scanner
          </a>
          <a href="{{ route('admin.absensi-siswa.create') }}" class="das-btn das-btn--primary">
            <i class="ti tabler-plus me-1"></i> Input Manual
          </a>
        </div>
      </div>
      @endif
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px; background: var(--das-success-soft); color: var(--das-success);">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: FILTER PANEL
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4">
    <div class="das-panel__body">
      <form id="filterForm" method="GET" class="row gy-3 gx-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label text-white-50 small fw-bold">Cari Nama Siswa</label>
          <input type="text" id="filterSearch" name="search" class="form-control"
            placeholder="Cari nama siswa..." value="{{ $search ?? '' }}">
        </div>
        <div class="col-md-2">
          <label class="form-label text-white-50 small fw-bold">Filter Kelas</label>
          <select id="filterKelas" name="kelas_id" class="form-select">
            <option value="">Semua Kelas</option>
            @foreach ($kelasOptions as $k)
              <option value="{{ $k->id }}" @selected(($selectedKelasId ?? '') == $k->id)>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label text-white-50 small fw-bold">Status</label>
          <select id="filterStatus" name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="hadir" @selected(($selectedStatus ?? '') === 'hadir')>Hadir</option>
            <option value="sakit" @selected(($selectedStatus ?? '') === 'sakit')>Sakit</option>
            <option value="izin" @selected(($selectedStatus ?? '') === 'izin')>Izin</option>
            <option value="alpha" @selected(($selectedStatus ?? '') === 'alpha')>Alpha</option>
            <option value="terlambat" @selected(($selectedStatus ?? '') === 'terlambat')>Terlambat</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label text-white-50 small fw-bold">Tanggal Dari</label>
          <input type="date" id="filterTanggalFrom" name="tanggal_from" class="form-control"
            value="{{ $tanggalFrom ?? '' }}">
        </div>
        <div class="col-md-2">
          <label class="form-label text-white-50 small fw-bold">Tanggal Sampai</label>
          <input type="date" id="filterTanggalTo" name="tanggal_to" class="form-control"
            value="{{ $tanggalTo ?? '' }}">
        </div>
        <div class="col-md-1">
          <div class="d-flex gap-2">
            <button type="submit" class="btn das-btn --info w-100">
              <i class="ti tabler-search"></i>
            </button>
            <button type="button" id="resetFilterBtn" class="btn das-btn --secondary" title="Reset">
              <i class="ti tabler-refresh"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: DATA TABLE
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Kehadiran
      </h6>
      <div class="d-flex align-items-center gap-3">
        <select id="perPageSelect" class="form-select border-0 text-white w-auto"
          style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="10" {{ ($perPage ?? 50) == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ ($perPage ?? 50) == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ ($perPage ?? 50) == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ ($perPage ?? 50) == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span class="das-chip --info d-none d-sm-inline-flex">{{ method_exists($absensi, 'total') ? $absensi->total() : count($absensi) }} Baris Data</span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="table-container">
        @include('admin.absensi-siswa.table')
      </div>
    </div>
  </div>

  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
      <div class="modal-content">
        <div class="modal-body text-center p-5">
          <div class="mb-4">
            <span
              style="
              display: inline-flex;
              align-items: center;
              justify-content: center;
              width: 80px; height: 80px;
              border-radius: 50%;
              background: rgba(234, 84, 85, 0.12);
              font-size: 2.5rem;
            ">🗑️</span>
          </div>

          <h5 class="modal-title mb-1" id="deleteConfirmModalLabel">Hapus Data Absensi?</h5>
          <p class="text-muted mb-1" style="font-size: 0.9rem;">
            Anda akan menghapus data absensi:
          </p>
          <p class="fw-bold mb-0" id="modal-siswa-name" style="font-size: 1rem;"></p>
          <p class="text-muted" id="modal-siswa-date" style="font-size: 0.82rem;"></p>

          <div class="alert alert-warning py-2 px-3 mt-3 mb-0" role="alert" style="font-size: 0.82rem;">
            <i class="ti tabler-alert-triangle me-1"></i>
            Tindakan ini <strong>tidak dapat dibatalkan</strong>.
          </div>
        </div>

        <div class="modal-footer d-flex justify-content-center gap-3 border-0 pt-0 pb-4">
          <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Batal
          </button>
          <form id="deleteForm" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger px-4">
              <i class="ti tabler-trash me-1"></i> Ya, Hapus!
            </button>
          </form>
        </div>
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

      // ═══════════════════════════════════════════════════════
      // AJAX LIVE SEARCH — SAMA PERSIS POLA SISWA
      // ═══════════════════════════════════════════════════════

      const container = document.getElementById('table-container');
      const perPageSelect = document.getElementById('perPageSelect');
      const filterSearch = document.getElementById('filterSearch');
      const filterKelas = document.getElementById('filterKelas');
      const filterStatus = document.getElementById('filterStatus');
      const filterTanggalFrom = document.getElementById('filterTanggalFrom');
      const filterTanggalTo = document.getElementById('filterTanggalTo');
      const filterForm = document.getElementById('filterForm');
      const resetFilterBtn = document.getElementById('resetFilterBtn');
      let searchTimeout;

      let currentSortBy = '{{ $sortBy ?? 'tanggal' }}';
      let currentSortDir = '{{ $sortDir ?? 'desc' }}';

      function fetchData(page = 1) {
        const search = encodeURIComponent(filterSearch.value || '');
        const perPage = perPageSelect.value || 50;
        const kelasId = filterKelas.value || '';
        const status = filterStatus.value || '';
        const tanggalFrom = filterTanggalFrom.value || '';
        const tanggalTo = filterTanggalTo.value || '';
        const url = `{{ route('admin.absensi-siswa.index') }}?page=${page}&search=${search}&per_page=${perPage}&sort_by=${currentSortBy}&sort_dir=${currentSortDir}&kelas_id=${kelasId}&status=${status}&tanggal_from=${tanggalFrom}&tanggal_to=${tanggalTo}`;

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

      // auto-submit on dropdown/date change
      if (filterKelas) {
        filterKelas.addEventListener('change', function() {
          fetchData(1);
        });
      }

      if (filterStatus) {
        filterStatus.addEventListener('change', function() {
          fetchData(1);
        });
      }

      if (filterTanggalFrom) {
        filterTanggalFrom.addEventListener('change', function() {
          fetchData(1);
        });
      }

      if (filterTanggalTo) {
        filterTanggalTo.addEventListener('change', function() {
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
          if (filterKelas) filterKelas.value = '';
          if (filterStatus) filterStatus.value = '';
          if (filterTanggalFrom) filterTanggalFrom.value = '';
          if (filterTanggalTo) filterTanggalTo.value = '';
          fetchData(1);
        });
      }

      if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
          fetchData(1);
        });
      }

      // pagination clicks (delegated)
      container.addEventListener('click', function(e) {
        const link = e.target.closest('a.das-page-btn');
        if (link) {
          e.preventDefault();
          const page = link.dataset.page || new URL(link.href).searchParams.get('page') || 1;
          fetchData(page);
        }
      });

      // sort clicks (delegated)
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
    });

    function confirmDelete(actionUrl, siswaName, tanggal) {
      document.getElementById('deleteForm').action = actionUrl;
      document.getElementById('modal-siswa-name').textContent = siswaName;
      document.getElementById('modal-siswa-date').textContent = 'Tanggal: ' + tanggal;

      const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
      modal.show();
    }
  </script>
@endsection
