@extends('layouts/layoutMaster')

@section('title', 'Mata Pelajaran')

@section('page-style')
  <style>
    .mapel-row-hover {
      transition: background 0.15s ease;
    }

    .mapel-row-hover:hover {
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

    /* SEARCH INPUT */
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

    .hover-bg-primary-light:hover {
      background: rgba(115, 103, 240, 0.1) !important;
      border-color: rgba(115, 103, 240, 0.4) !important;
    }

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
      border-color: rgba(255, 255, 255, 0.1) !important;
    }

    .extra-small {
      font-size: 0.7rem;
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

    .das-btn.--warning {
      background: rgba(255, 159, 67, 0.15);
      border-color: rgba(255, 159, 67, 0.35);
      color: #ff9f43;
    }
    .das-btn.--warning:hover {
      background: rgba(255, 159, 67, 0.3);
      color: #ffffff;
      box-shadow: 0 0 12px rgba(255, 159, 67, 0.2);
    }

    .das-btn.--secondary {
      background: rgba(168, 170, 174, 0.15);
      border-color: rgba(168, 170, 174, 0.35);
      color: #a8aae0;
    }
    .das-btn.--secondary:hover {
      background: rgba(168, 170, 174, 0.3);
      color: #ffffff;
      box-shadow: 0 0 12px rgba(168, 170, 174, 0.2);
    }

    .das-btn.--success {
      background: rgba(40, 199, 111, 0.15);
      border-color: rgba(40, 199, 111, 0.35);
      color: #28c76f;
    }
    .das-btn.--success:hover {
      background: rgba(40, 199, 111, 0.3);
      color: #ffffff;
      box-shadow: 0 0 12px rgba(40, 199, 111, 0.2);
    }

    .das-btn.--danger {
      background: rgba(234, 84, 85, 0.15);
      border-color: rgba(234, 84, 85, 0.35);
      color: #ea5455;
    }
    .das-btn.--danger:hover {
      background: #ea5455;
      color: #ffffff;
      box-shadow: 0 0 15px rgba(234, 84, 85, 0.4);
    }

    .text-purple {
      color: #a5a2f7 !important;
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
      cursor: not-allowed;
    }
  </style>
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
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
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-book text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Mapel
          </div>
          <h4 class="das-hero__title text-gradient-gold">Mata Pelajaran</h4>
          <p class="das-hero__subtitle">Kelola kurikulum, kode, kelompok mata pelajaran, dan status keaktifan.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.mapel.create') }}" class="btn das-btn --primary">
          <i class="ti tabler-plus me-1"></i> Tambah
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

  {{-- PANEL FILTER & TABEL --}}
  <div class="das-panel mb-4">
    <div class="das-panel__body">
      <form id="filterForm" method="GET" class="row gy-3 gx-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label text-white-50 small fw-bold">Cari Mapel</label>
          <input type="text" id="filterSearch" name="search" class="form-control"
            placeholder="Kode atau nama mapel…" value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label text-white-50 small fw-bold">Kelompok Mapel</label>
          <select id="filterKelompok" name="kelompok" class="form-select">
            <option value="">Semua Kelompok</option>
            <option value="umum" @selected(request('kelompok') === 'umum')>Umum</option>
            <option value="kejuruan" @selected(request('kelompok') === 'kejuruan')>Kejuruan</option>
            <option value="muatan_lokal" @selected(request('kelompok') === 'muatan_lokal')>Muatan Lokal</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label text-white-50 small fw-bold">Status Mapel</label>
          <select id="filterStatus" name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="1" @selected(request('status') === '1')>Aktif</option>
            <option value="0" @selected(request('status') === '0')>Nonaktif</option>
          </select>
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
  <div class="das-panel mb-5">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Mata Pelajaran
      </h6>
      <div class="d-flex align-items-center gap-3">
        <select id="perPageSelect" class="form-select border-0 text-white w-auto"
          style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
          <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 (Default)</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>

        <span class="das-chip --info d-none d-sm-inline-flex">
          {{ method_exists($mapels, 'total') ? $mapels->total() : count($mapels) }} Mapel
        </span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="table-container">
        @include('admin.mapel.table')
      </div>
    </div>
  {{-- Modal Konfirmasi Hapus --}}
  <div class="modal fade" id="modalHapusMapel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
      <div class="modal-content das-modal border-0 shadow-lg">
        <div class="das-modal-head px-4 py-3 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <div style="width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(234,84,85,0.2);border:1px solid rgba(234,84,85,0.35);">
              <i class="ti tabler-alert-triangle text-danger fs-5"></i>
            </div>
            <div>
              <h5 class="das-modal-title text-white fw-bold">Konfirmasi Hapus</h5>
              <small class="text-white-50">Tindakan ini tidak dapat dibatalkan.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formHapusMapel" method="POST">
          @csrf
          @method('DELETE')
          <div class="das-modal-body text-center py-4">
            <p class="mb-1 text-white-50">Yakin ingin menghapus mata pelajaran:</p>
            <p class="fw-bold text-warning fs-5 mb-1" id="hapusNamaMapel">—</p>
            <p class="text-white-50 small" id="hapusKodeMapel">—</p>
            <p class="text-white-50 small mt-3 mb-0">
              <i class="ti tabler-info-circle me-1"></i>
              Data mata pelajaran ini akan dihapus secara permanen.
            </p>
          </div>
          <div class="px-4 pb-4 pt-2 d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-label-secondary px-4 w-100" data-bs-dismiss="modal" id="btnBatalHapus">
              <i class="ti tabler-x me-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-danger fw-semibold px-4 w-100 shadow-sm" id="btnSubmitHapus">
              <i class="ti tabler-trash me-1"></i> Hapus
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const filterSearch = document.getElementById('filterSearch');
      const filterKelompok = document.getElementById('filterKelompok');
      const filterStatus = document.getElementById('filterStatus');
      const filterForm = document.getElementById('filterForm');
      const resetFilterBtn = document.getElementById('resetFilterBtn');
      const perPageSelect = document.getElementById('perPageSelect');
      const container = document.getElementById('table-container');

      const modalHapusMapel = new bootstrap.Modal(document.getElementById('modalHapusMapel'));
      const hapusNamaMapel = document.getElementById('hapusNamaMapel');
      const hapusKodeMapel = document.getElementById('hapusKodeMapel');
      const formHapusMapel = document.getElementById('formHapusMapel');
      const btnSubmitHapus = document.getElementById('btnSubmitHapus');
      const btnBatalHapus = document.getElementById('btnBatalHapus');

      let searchTimeout;

      function fetchMapel(page = 1) {
        const search = encodeURIComponent(filterSearch.value || '');
        const kelompok = filterKelompok.value || '';
        const status = filterStatus.value || '';
        const perPage = perPageSelect.value || 15;
        const url = `{{ route('admin.mapel.index') }}?page=${page}&search=${search}&kelompok=${kelompok}&status=${status}&per_page=${perPage}`;

        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

        fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.text())
        .then(html => {
          container.innerHTML = html;
          container.style.opacity = '1';
          container.style.pointerEvents = 'auto';

          // Update total count chip
          const totalMatch = html.match(/class="das-chip --info.*?>\s*(\d+)\s+Mapel/);
          // (optional, if we need to update chip outside the table block. But wait, chip is outside table-container!)
          // Let's reload count if needed, or we can just fetch the count from table or keep it simple.
          // Wait, let's update the chip count using selector or from data-total if we put it in table.
          // In table.blade.php we can output a script or custom header, or we can just parse the total.
          // Actually, let's look at SiswaController or similar. In siswa/index.blade.php:
          // We can select the chip and update it. Let's see if we can get total from the pagination total if available.
          const totalBadge = document.querySelector('.das-chip.--info');
          if (totalBadge) {
            // Find total from page links
            const totalInput = document.getElementById('hidden-total-count');
            if (totalInput) {
              totalBadge.textContent = totalInput.value + ' Mapel';
            }
          }

          // Re-initialize tooltips
          const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
          });
        })
        .catch(error => {
          console.error('Error fetching mapel data:', error);
          container.style.opacity = '1';
          container.style.pointerEvents = 'auto';
        });
      }

      if (filterSearch) {
        filterSearch.addEventListener('input', function () {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => fetchMapel(1), 450);
        });
      }

      if (filterKelompok) {
        filterKelompok.addEventListener('change', () => fetchMapel(1));
      }

      if (filterStatus) {
        filterStatus.addEventListener('change', () => fetchMapel(1));
      }

      if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
          e.preventDefault();
          fetchMapel(1);
        });
      }

      if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function () {
          filterSearch.value = '';
          filterKelompok.value = '';
          filterStatus.value = '';
          fetchMapel(1);
        });
      }

      if (perPageSelect) {
        perPageSelect.addEventListener('change', () => fetchMapel(1));
      }

      // Handle pagination click
      container.addEventListener('click', function (e) {
        const pagLink = e.target.closest('.das-page-btn');
        if (pagLink) {
          e.preventDefault();
          const page = pagLink.dataset.page || new URL(pagLink.href).searchParams.get('page') || 1;
          fetchMapel(page);
        }
      });

      // Handle delete button click
      container.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-mapel');
        if (!btn) return;

        const url = btn.dataset.url;
        const nama = btn.dataset.nama || 'mata pelajaran ini';
        const tr = btn.closest('tr');
        const kode = tr ? tr.querySelector('td:nth-child(2)').textContent.trim() : '';

        hapusNamaMapel.textContent = nama;
        hapusKodeMapel.textContent = 'Kode: ' + (kode || '-');
        formHapusMapel.action = url;

        modalHapusMapel.show();
      });

      // Submit delete form via AJAX
      formHapusMapel.addEventListener('submit', function (e) {
        e.preventDefault();

        btnSubmitHapus.disabled = true;
        btnBatalHapus.disabled = true;
        btnSubmitHapus.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menghapus...';

        fetch(formHapusMapel.action, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          }
        })
        .then(res => res.json())
        .then(data => {
          modalHapusMapel.hide();
          
          if (data.success) {
            // Tampilkan flash alert simple / push notification
            fetchMapel(1);
          } else {
            alert(data.message || 'Gagal menghapus data.');
          }
        })
        .catch(err => {
          modalHapusMapel.hide();
          console.error('Delete mapel error:', err);
          alert('Terjadi kesalahan koneksi.');
        })
        .finally(() => {
          btnSubmitHapus.disabled = false;
          btnBatalHapus.disabled = false;
          btnSubmitHapus.innerHTML = '<i class="ti tabler-trash me-1"></i> Hapus';
        });
      });
    });
  </script>
@endsection
