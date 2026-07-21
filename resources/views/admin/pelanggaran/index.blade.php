@extends('layouts/layoutMaster')

@section('title', 'Riwayat Pelanggaran Siswa')

@section('page-style')
  <style>
    .pelanggaran-row-hover {
      transition: background 0.15s ease;
    }

    .pelanggaran-row-hover:hover {
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

    .form-control::placeholder {
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

    .select2-container--default .select2-selection--single {
      background-color: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
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

    .hover-bg-primary-light:hover {
      background: rgba(115, 103, 240, 0.1) !important;
      border-color: rgba(115, 103, 240, 0.4) !important;
    }

    .extra-small {
      font-size: 0.7rem;
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
            <i class="ti tabler-swords text-danger"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Kesiswaan / Pelanggaran Siswa
          </div>
          <h4 class="das-hero__title text-gradient-gold">Riwayat Pelanggaran Siswa</h4>
          <p class="das-hero__subtitle">Manajemen pencatatan pelanggaran, perhitungan poin, dan penerbitan SP otomatis.</p>
        </div>
      </div>

      @can('create', App\Models\PelanggaranSiswa::class)
        <div class="das-hero__actions">
          <a href="{{ route('admin.pelanggaran.create') }}" class="btn das-btn --primary">
            <i class="ti tabler-plus me-1"></i> Catat Pelanggaran
          </a>
        </div>
      @endcan
    </div>
  </div>

  {{-- FLASH MESSAGES --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-x fs-5"></i>
      <span>{{ session('error') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- FILTER PANEL --}}
  <div class="das-panel mb-4">
    <div class="das-panel__body">
      <form id="filterForm" method="GET" action="{{ route('admin.pelanggaran.index') }}">
        <input type="hidden" name="per_page" id="hiddenPerPage" value="{{ request('per_page', 15) }}">
        <div class="row g-3">
          <!-- Filter Tahun Akademik -->
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Tahun Akademik</label>
            <select class="form-select" name="tahun_akademik_id" id="filterTa">
              @foreach($tahunAkademiks as $ta)
                <option value="{{ $ta->id }}" {{ $tahunAkademikId == $ta->id ? 'selected' : '' }}>
                  {{ $ta->nama }} ({{ ucfirst($ta->semester) }})
                </option>
              @endforeach
            </select>
          </div>

          <!-- Filter Kelas -->
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Kelas</label>
            <select class="form-select" name="kelas_id" id="filterKelas">
              <option value="">Semua Kelas</option>
              @foreach($kelas as $k)
                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                  {{ $k->nama }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Filter Kategori Pelanggaran -->
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Kategori</label>
            <select class="form-select" name="kategori_id" id="filterKategori">
              <option value="">Semua Kategori</option>
              @foreach($kategoris as $kat)
                <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                  {{ $kat->nama }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Filter Level SP -->
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Level SP</label>
            <select class="form-select" name="level_sp" id="filterSp">
              <option value="">Semua Level SP</option>
              <option value="SP1" {{ request('level_sp') == 'SP1' ? 'selected' : '' }}>SP1</option>
              <option value="SP2" {{ request('level_sp') == 'SP2' ? 'selected' : '' }}>SP2</option>
              <option value="SP3" {{ request('level_sp') == 'SP3' ? 'selected' : '' }}>SP3</option>
            </select>
          </div>

          <!-- Filter Bulan Kejadian -->
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">Bulan Kejadian</label>
            <input type="month" class="form-control" name="bulan" id="filterBulan" value="{{ request('bulan') }}">
          </div>

          <!-- Pencarian Nama/NIS -->
          <div class="col-md-6">
            <label class="form-label text-white-50 small fw-bold">Cari Siswa (Nama / NIS)</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-light"><i class="ti tabler-search text-muted"></i></span>
              <input type="text" class="form-control" name="search" id="filterSearch" value="{{ request('search') }}" placeholder="Ketik nama lengkap atau NIS siswa…">
            </div>
          </div>

          <!-- Tombol Aksi Filter -->
          <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn das-btn --info w-100"><i class="ti tabler-search me-1"></i> Cari</button>
            <a href="{{ route('admin.pelanggaran.index') }}" class="btn das-btn --secondary w-100"><i class="ti tabler-refresh me-1"></i> Reset</a>
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
        <i class="ti tabler-list text-danger"></i> Daftar Pelanggaran Siswa
      </h6>
      <div class="d-flex align-items-center gap-3">
        <select id="perPageSelect" class="form-select border-0 text-white w-auto"
          style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
          <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>

        <span class="das-chip --danger d-none d-sm-inline-flex">
          {{ method_exists($pelanggarans, 'total') ? $pelanggarans->total() : count($pelanggarans) }} Pelanggaran
        </span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="tableContainer">
        @include('admin.pelanggaran.table')
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus + Alasan -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title"><i class="ti tabler-trash me-2 text-danger"></i> Konfirmasi Penghapusan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="deleteForm" method="POST">
          @csrf
          @method('DELETE')
          <div class="das-modal-body text-light">
            <p class="mb-2">Apakah Anda yakin ingin menghapus catatan pelanggaran ini?</p>
            <div class="alert alert-warning border-0 shadow-sm d-flex gap-2 mb-3" style="background: rgba(255, 159, 67, 0.12); border-radius: 8px;">
              <i class="ti tabler-alert-circle text-warning fs-5 flex-shrink-0 mt-0.5"></i>
              <span class="small">
                Poin siswa akan berkurang, namun Surat Peringatan (SP) yang sudah terlanjur diterbitkan TETAP berlaku.
              </span>
            </div>
            <div class="mb-0">
              <label class="form-label text-white-50 small fw-bold">Tulis Alasan Penghapusan</label>
              <textarea class="form-control" name="alasan_penghapusan" rows="3" placeholder="Contoh: Salah input data, klarifikasi dengan wali kelas..." required></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 p-4 pt-0">
            <button type="button" class="btn das-btn --secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn das-btn --danger">Hapus Sekarang</button>
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
    document.addEventListener('DOMContentLoaded', function () {
      const filterForm = document.getElementById('filterForm');
      const tableContainer = document.getElementById('tableContainer');
      const perPageSelect = document.getElementById('perPageSelect');
      const hiddenPerPage = document.getElementById('hiddenPerPage');

      // AJAX reload function
      const reloadTable = () => {
        const formData = new FormData(filterForm);
        const queryParams = new URLSearchParams(formData).toString();
        
        tableContainer.style.opacity = '0.5';
        tableContainer.style.pointerEvents = 'none';

        fetch(`{{ route('admin.pelanggaran.index') }}?${queryParams}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.text())
        .then(html => {
          tableContainer.innerHTML = html;
          tableContainer.style.opacity = '1';
          tableContainer.style.pointerEvents = 'auto';

          // Re-init tooltips
          const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
          });
        })
        .catch(err => {
          console.error('Gagal mengambil data:', err);
          tableContainer.style.opacity = '1';
          tableContainer.style.pointerEvents = 'auto';
        });
      };

      // Auto filter on change / input with debounce
      let debounceTimeout;
      const inputs = filterForm.querySelectorAll('select, input:not([type="hidden"])');
      inputs.forEach(input => {
        input.addEventListener('change', reloadTable);
        if (input.type === 'text') {
          input.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(reloadTable, 500);
          });
        }
      });

      // Per Page change listener
      if (perPageSelect && hiddenPerPage) {
        perPageSelect.addEventListener('change', function () {
          hiddenPerPage.value = this.value;
          reloadTable();
        });
      }

      // Handle pagination click
      document.addEventListener('click', function (e) {
        const pageLink = e.target.closest('a.das-page-btn');
        if (pageLink) {
          e.preventDefault();
          const url = pageLink.getAttribute('href');
          
          tableContainer.style.opacity = '0.5';
          tableContainer.style.pointerEvents = 'none';

          fetch(url, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(response => response.text())
          .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';

            // Re-init tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl);
            });
          })
          .catch(err => {
            console.error('Gagal mengambil data pagination:', err);
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
          });
        }
      });

      // SweetAlert2 notification for flash session (if needed)
      @if(session('success'))
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: '{{ session('success') }}',
          customClass: {
            popup: 'das-swal-popup',
            title: 'das-swal-title',
            htmlContainer: 'das-swal-html',
            confirmButton: 'btn btn-success das-swal-confirm'
          },
          timer: 2500,
          showConfirmButton: false,
          background: 'transparent',
        });
      @endif

      @if(session('error'))
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: '{{ session('error') }}',
          customClass: {
            popup: 'das-swal-popup',
            title: 'das-swal-title',
            htmlContainer: 'das-swal-html',
            confirmButton: 'btn btn-primary das-swal-confirm'
          },
          background: 'transparent',
          buttonsStyling: false
        });
      @endif
    });

    // Buka Modal Delete
    function confirmDelete(actionUrl) {
      const deleteForm = document.getElementById('deleteForm');
      deleteForm.setAttribute('action', actionUrl);
      const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
      deleteModal.show();
    }
  </script>
@endsection
