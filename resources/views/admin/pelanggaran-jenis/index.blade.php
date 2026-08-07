@extends('layouts/layoutMaster')

@section('title', 'Jenis Pelanggaran')

@section('page-style')
  <style>
    .jenis-row-hover {
      transition: background 0.15s ease;
    }

    .jenis-row-hover:hover {
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

    #perPageSelect option,
    #filterKategori option {
      background: #1a1a2e;
      color: #ccc;
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
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-alert-circle text-warning"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Master Data / Jenis Pelanggaran
          </div>
          <h4 class="das-hero__title text-gradient-gold">Jenis Pelanggaran</h4>
          <p class="das-hero__subtitle">Kelola butir-butir pelanggaran beserta bobot poin masing-masing.</p>
        </div>
      </div>

      <div class="das-hero__actions d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-warning text-warning" data-bs-toggle="modal" data-bs-target="#modalPresetTataTertib" style="border-radius: 4px;">
          <i class="ti tabler-bolt me-1"></i> ⚡ Preset Tata Tertib
        </button>
        <button type="button" class="btn btn-outline-info text-info" data-bs-toggle="modal" data-bs-target="#modalImportJenis" style="border-radius: 4px;">
          <i class="ti tabler-file-upload me-1"></i> 📥 Import Excel
        </button>
        <a href="{{ route('admin.pelanggaran-jenis.export') }}" class="btn btn-outline-success text-success" style="border-radius: 4px;">
          <i class="ti tabler-file-download me-1"></i> 📤 Export Excel
        </a>
        <a href="{{ route('admin.pelanggaran-jenis.create') }}" class="btn das-btn --warning" style="border-radius: 4px;">
          <i class="ti tabler-plus me-1"></i> Tambah Jenis Pelanggaran
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

  {{-- FILTER & SEARCH PANEL --}}
  <div class="das-panel mb-4">
    <div class="das-panel__body py-3 px-4">
      <form id="filterForm" class="row g-3 align-items-center">
        <div class="col-12 col-md-4">
          <div class="input-group input-group-merge">
            <span class="input-group-text bg-transparent border-secondary text-white-50"><i class="ti tabler-search"></i></span>
            <input type="text" id="filterSearch" name="search" class="form-control border-secondary text-white" placeholder="Cari nama pelanggaran..." value="{{ request('search') }}">
          </div>
        </div>
        <div class="col-6 col-md-3">
          <select id="filterKategori" name="kategori_id" class="form-select border-secondary text-white">
            <option value="">-- Semua Kategori --</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}" {{ request('kategori_id') == $category->id ? 'selected' : '' }}>{{ $category->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-3">
          <select id="filterStatus" name="is_aktif" class="form-select border-secondary text-white">
            <option value="">-- Semua Status --</option>
            <option value="1" {{ request('is_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ request('is_aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
          </select>
        </div>
        <div class="col-12 col-md-2 text-end">
          <button type="button" id="btnResetFilter" class="btn btn-outline-secondary w-100" style="border-radius: 4px;">
            <i class="ti tabler-rotate me-1"></i> Reset
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- MAIN DATA TABLE --}}
  <div class="card bg-dark text-white border-0 shadow-sm" style="border-radius: 10px; background: rgba(15, 23, 42, 0.6) !important; backdrop-filter: blur(10px);">
    <div class="card-header border-bottom border-secondary d-flex align-items-center justify-content-between py-3 px-4" style="background: rgba(255, 255, 255, 0.02);">
      <div class="d-flex align-items-center gap-2">
        <i class="ti tabler-list-check fs-4 text-warning"></i>
        <h5 class="card-title text-white mb-0 fs-6 fw-bold">Daftar Jenis Pelanggaran & Bobot Poin</h5>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="text-white-50 small">Tampilkan:</span>
        <select id="perPageSelect" class="form-select form-select-sm border-secondary text-white" style="width: 75px; border-radius: 4px;">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </div>
    </div>
    <div class="card-body p-0" id="tableContainer">
      @include('admin.pelanggaran-jenis.table')
    </div>
  </div>

  {{-- MODAL PRESET TATA TERTIB --}}
  <div class="modal fade" id="modalPresetTataTertib" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-white border border-secondary" style="border-radius: 10px;">
        <div class="modal-header border-bottom border-secondary">
          <h5 class="modal-title text-warning fw-bold d-flex align-items-center gap-2">
            <i class="ti tabler-bolt fs-4"></i> Terapkan Preset Tata Tertib Sekolah
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formPresetTataTertib" action="{{ route('admin.pelanggaran-jenis.apply-preset') }}" method="POST">
          @csrf
          <div class="modal-body py-4">
            <p class="text-white-50 small mb-3">
              Pilih template tata tertib dasar yang sesuai dengan jenjang / tipe sekolah Anda untuk mengisikan otomatis butir-butir jenis pelanggaran & bobot poin standar.
            </p>
            <div class="d-flex flex-column gap-3">
              <label class="p-3 border border-secondary rounded d-flex align-items-center gap-3 cursor-pointer" style="background: rgba(255, 255, 255, 0.03);">
                <input type="radio" name="preset" value="sma_smk" class="form-check-input mt-0" checked>
                <div>
                  <div class="fw-bold text-white mb-1">🏫 SMA / SMK Standard</div>
                  <div class="text-white-50 extra-small">Skala poin 3 - 100 poin (Kedisiplinan, Kehadiran, Etika, Pelanggaran Berat & Keamanan).</div>
                </div>
              </label>
              <label class="p-3 border border-secondary rounded d-flex align-items-center gap-3 cursor-pointer" style="background: rgba(255, 255, 255, 0.03);">
                <input type="radio" name="preset" value="madrasah" class="form-check-input mt-0">
                <div>
                  <div class="fw-bold text-warning mb-1">🌙 Madrasah / Pesantren (MA / MTs)</div>
                  <div class="text-white-50 extra-small">Termasuk kelengkapan peci/jilbab, sholat berjamaah, akhlakul karimah, & syariat.</div>
                </div>
              </label>
              <label class="p-3 border border-secondary rounded d-flex align-items-center gap-3 cursor-pointer" style="background: rgba(255, 255, 255, 0.03);">
                <input type="radio" name="preset" value="smp_mts" class="form-check-input mt-0">
                <div>
                  <div class="fw-bold text-info mb-1">🎓 SMP / Sekolah Menengah Pertama</div>
                  <div class="text-white-50 extra-small">Kedisiplinan dasar, tata krama, presensi harian, & sanksi edukatif.</div>
                </div>
              </label>
            </div>
          </div>
          <div class="modal-footer border-top border-secondary">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 4px;">Batal</button>
            <button type="submit" class="btn btn-warning fw-bold" style="border-radius: 4px;">
              <i class="ti tabler-check me-1"></i> Terapkan Preset Ini
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- MODAL IMPORT EXCEL --}}
  <div class="modal fade" id="modalImportJenis" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-white border border-secondary" style="border-radius: 10px;">
        <div class="modal-header border-bottom border-secondary">
          <h5 class="modal-title text-info fw-bold d-flex align-items-center gap-2">
            <i class="ti tabler-file-upload fs-4"></i> Import Master Jenis Pelanggaran
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formImportJenis" action="{{ route('admin.pelanggaran-jenis.import') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="modal-body py-4">
            <div class="alert alert-info border-0 text-white mb-3 p-3" style="background: rgba(0, 207, 232, 0.15); border-radius: 6px;">
              <div class="fw-bold mb-1"><i class="ti tabler-info-circle me-1"></i> Unduh Template Excel</div>
              <p class="extra-small mb-2 text-white-50">Gunakan format template yang telah disediakan agar proses impor data berjalan lancar tanpa error.</p>
              <a href="{{ route('admin.pelanggaran-jenis.template') }}" class="btn btn-sm btn-info fw-bold" style="border-radius: 4px;">
                <i class="ti tabler-download me-1"></i> Download Template Excel (.xlsx)
              </a>
            </div>
            <div class="mb-3">
              <label for="inputImportFile" class="form-label text-white fw-semibold">Pilih File Excel / CSV <span class="text-danger">*</span></label>
              <input type="file" class="form-control" id="inputImportFile" name="import_file" accept=".xlsx,.xls,.csv" required>
              <div class="form-text text-white-50 extra-small">Format yang didukung: .xlsx, .xls, .csv (Maks 10 MB).</div>
            </div>
          </div>
          <div class="modal-footer border-top border-secondary">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 4px;">Batal</button>
            <button type="submit" class="btn btn-info fw-bold" style="border-radius: 4px;">
              <i class="ti tabler-upload me-1"></i> Unggah & Impor
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
      const tableContainer = document.getElementById('tableContainer');
      const filterSearch = document.getElementById('filterSearch');
      const filterKategori = document.getElementById('filterKategori');
      const filterStatus = document.getElementById('filterStatus');
      const btnResetFilter = document.getElementById('btnResetFilter');
      const perPageSelect = document.getElementById('perPageSelect');

      function loadTable(url) {
        tableContainer.style.opacity = '0.5';
        fetch(url, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
          tableContainer.innerHTML = html;
          tableContainer.style.opacity = '1';
        })
        .catch(err => {
          tableContainer.style.opacity = '1';
          console.error('Error loading table:', err);
        });
      }

      function applyFilter() {
        const search = filterSearch.value;
        const kat = filterKategori.value;
        const status = filterStatus.value;
        const perPage = perPageSelect.value;
        const url = new URL("{{ route('admin.pelanggaran-jenis.index') }}", window.location.origin);
        if (search) url.searchParams.set('search', search);
        if (kat) url.searchParams.set('kategori_id', kat);
        if (status) url.searchParams.set('is_aktif', status);
        if (perPage) url.searchParams.set('per_page', perPage);

        loadTable(url.toString());
      }

      let searchTimeout;
      filterSearch.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilter, 400);
      });

      filterKategori.addEventListener('change', applyFilter);
      filterStatus.addEventListener('change', applyFilter);
      perPageSelect.addEventListener('change', applyFilter);

      btnResetFilter.addEventListener('click', function () {
        filterSearch.value = '';
        filterKategori.value = '';
        filterStatus.value = '';
        applyFilter();
      });

      // FAST EDIT POIN HANDLER
      document.addEventListener('click', function (e) {
        const btnFastPoin = e.target.closest('.btn-fast-edit-poin');
        if (btnFastPoin) {
          e.preventDefault();
          const updateUrl = btnFastPoin.dataset.url;
          const nama = btnFastPoin.dataset.nama || 'jenis pelanggaran ini';
          const currentPoin = btnFastPoin.dataset.poin || 0;

          if (typeof Swal !== 'undefined') {
            Swal.fire({
              title: 'Ubah Bobot Poin',
              html: 'Masukkan bobot poin baru untuk pelanggaran:<br><b>' + nama + '</b>',
              input: 'number',
              inputValue: currentPoin,
              inputAttributes: {
                min: 0,
                max: 255,
                step: 1
              },
              showCancelButton: true,
              confirmButtonText: 'Simpan Poin',
              cancelButtonText: 'Batal',
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                htmlContainer: 'das-swal-html',
                confirmButton: 'btn btn-warning das-swal-confirm me-2',
                cancelButton: 'btn btn-secondary das-swal-cancel'
              },
              inputValidator: (value) => {
                if (!value || isNaN(value) || parseInt(value) < 0) {
                  return 'Harap masukkan angka bobot poin yang valid (>= 0)!';
                }
              }
            }).then((result) => {
              if (result.isConfirmed) {
                const newPoin = parseInt(result.value);
                fetch(updateUrl, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                  },
                  body: '_token=' + encodeURIComponent('{{ csrf_token() }}') + '&_method=PATCH&bobot_poin=' + newPoin
                })
                .then(res => res.json())
                .then(response => {
                  if (response.success) {
                    Swal.fire({
                      icon: 'success',
                      title: 'Berhasil!',
                      text: response.message,
                      timer: 1500,
                      showConfirmButton: false,
                      customClass: {
                        popup: 'das-swal-popup',
                        title: 'das-swal-title',
                        htmlContainer: 'das-swal-html'
                      }
                    });
                    loadTable(window.location.href);
                  }
                })
                .catch(err => {
                  console.error('Update poin error:', err);
                });
              }
            });
          }
          return;
        }

        // DELETE HANDLER
        const btnDelete = e.target.closest('.btn-delete-jenis');
        if (btnDelete) {
          e.preventDefault();
          const deleteUrl = btnDelete.dataset.url;
          const nama = btnDelete.dataset.nama || 'jenis pelanggaran ini';
          const count = parseInt(btnDelete.dataset.count || 0);

          let confirmHtml = 'Jenis pelanggaran <b>' + nama + '</b> akan dihapus.';
          if (count > 0) {
            confirmHtml += '<br><span class="text-warning small"><i class="ti tabler-alert-triangle me-1"></i> Data ini sudah tercatat ' + count + ' kali oleh siswa. Sistem hanya akan menonaktifkan/mengarsipkan (soft delete) data ini agar riwayat data siswa tetap aman.</span>';
          }

          if (typeof Swal !== 'undefined') {
            Swal.fire({
              title: 'Hapus Jenis Pelanggaran?',
              html: confirmHtml,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#ea5455',
              cancelButtonColor: '#82868b',
              confirmButtonText: 'Ya, Hapus!',
              cancelButtonText: 'Batal',
              reverseButtons: true,
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                htmlContainer: 'das-swal-html',
                confirmButton: 'btn btn-danger das-swal-confirm me-2',
                cancelButton: 'btn btn-secondary das-swal-cancel'
              }
            }).then((result) => {
              if (result.isConfirmed) {
                fetch(deleteUrl, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                  },
                  body: '_token=' + encodeURIComponent('{{ csrf_token() }}') + '&_method=DELETE'
                })
                .then(res => res.json())
                .then(response => {
                  if (response.success) {
                    Swal.fire({
                      icon: 'success',
                      title: 'Berhasil!',
                      text: response.message,
                      timer: 1500,
                      showConfirmButton: false,
                      customClass: {
                        popup: 'das-swal-popup',
                        title: 'das-swal-title',
                        htmlContainer: 'das-swal-html'
                      }
                    });
                    loadTable(window.location.href);
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
