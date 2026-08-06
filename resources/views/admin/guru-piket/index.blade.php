@extends('layouts/layoutMaster')

@section('title', 'Data Guru Piket')

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

    /* SELECT2 DARK THEME OVERRIDE FOR MODAL */
    .select2-container {
      width: 100% !important;
      max-width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
      background-color: #0f172a !important;
      border: 1px solid rgba(255, 255, 255, 0.12) !important;
      color: #ffffff !important;
      height: 42px !important;
      border-radius: 6px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #ffffff !important;
      line-height: 40px !important;
      padding-left: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 40px !important;
    }
    .select2-dropdown {
      background-color: #1e293b !important;
      border: 1px solid rgba(255, 255, 255, 0.12) !important;
      color: #ffffff !important;
      z-index: 1065 !important;
      border-radius: 6px !important;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
      background-color: rgba(255, 255, 255, 0.06) !important;
      color: #ffffff !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #00bad1 !important;
      color: #ffffff !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
      background-color: #0f172a !important;
      border: 1px solid rgba(255, 255, 255, 0.12) !important;
      color: #ffffff !important;
      border-radius: 4px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
      color: #94a3b8 !important;
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
  @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
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
            <i class="ti tabler-user-shield text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Data Civitas</a> / Guru Piket
          </div>
          <h4 class="das-hero__title text-gradient-info">Data Guru Piket</h4>
          <p class="das-hero__subtitle">Kelola daftar guru piket harian, pemantauan absensi gerbang, dan rekapitulasi operasional.</p>
        </div>
      </div>

      <div class="das-hero__actions d-flex gap-2">
        <button type="button" class="btn das-btn --info" data-bs-toggle="modal" data-bs-target="#modalAssignGuruPiket">
          <i class="ti tabler-user-plus me-1"></i> Tetapkan Guru Piket
        </button>
        <a href="{{ route('piket.dashboard') }}" class="btn das-btn --secondary">
          <i class="ti tabler-dashboard me-1"></i> Portal Piket
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
          <label class="form-label text-white-50 small fw-bold">Cari Guru Piket</label>
          <input type="text" id="filterSearch" name="search" class="form-control"
            placeholder="Nama, NIP, Email…" value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label text-white-50 small fw-bold">Status</label>
          <select id="filterStatus" name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
          </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
          <button type="submit" class="btn das-btn --info flex-grow-1">
            <i class="ti tabler-search me-1"></i> Filter
          </button>
          <a href="{{ route('admin.guru-piket.index') }}" class="btn das-btn --secondary" title="Reset Filter">
            <i class="ti tabler-refresh"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- DATA TABLE CONTAINER --}}
  <div class="das-panel">
    <div class="das-panel__body p-0">
      <div id="tableContainer">
        @include('admin.guru-piket.table', ['guruPiketUsers' => $guruPiketUsers])
      </div>
    </div>
  </div>

  {{-- MODAL TETAPKAN GURU PIKET --}}
  <div class="modal fade" id="modalAssignGuruPiket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-panel border-0">
        <div class="modal-header border-bottom border-white-10">
          <h5 class="modal-title text-white font-weight-bold">
            <i class="ti tabler-user-plus text-info me-2"></i>Tetapkan Guru Piket
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formAssignGuruPiket" action="{{ route('admin.guru-piket.store') }}" method="POST">
          @csrf
          <div class="modal-body">
            <p class="text-white-50 small mb-3">
              Cari dan pilih guru aktif di bawah ini untuk ditetapkan menjadi <strong>Guru Piket</strong>.
            </p>

            <div class="mb-3">
              <label class="form-label text-white-50 small fw-bold required">Pilih Guru</label>
              <select id="guruSelect2" name="guru_id" class="form-select select2" required>
                <option value="">-- Cari Nama / NIP Guru --</option>
                @foreach ($availableGurus as $g)
                  <option value="{{ $g->id }}">
                    {{ $g->nama_lengkap }} (NIP: {{ $g->nip ?? '-' }})
                  </option>
                @endforeach
              </select>
              @if ($availableGurus->isEmpty())
                <div class="form-text text-warning mt-1">
                  <i class="ti tabler-alert-triangle me-1"></i> Semua guru aktif sudah ditugaskan sebagai Guru Piket atau belum memiliki akun pengguna.
                </div>
              @endif
            </div>
          </div>
          <div class="modal-footer border-top border-white-10">
            <button type="button" class="btn das-btn --secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn das-btn --info" {{ $availableGurus->isEmpty() ? 'disabled' : '' }}>
              <i class="ti tabler-check me-1"></i> Simpan Penugasan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  @vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const filterForm   = document.getElementById('filterForm');
      const tableContainer = document.getElementById('tableContainer');

      let currentSortBy  = '{{ $sortBy ?? "nama_lengkap" }}';
      let currentSortDir = '{{ $sortDir ?? "asc" }}';

      // Inisialisasi Select2 untuk Modal Tetapkan Guru Piket
      function initSelect2() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
          const $guruSelect = jQuery('#guruSelect2');
          if ($guruSelect.length) {
            $guruSelect.select2({
              placeholder: '-- Cari Nama / NIP Guru --',
              allowClear: true,
              dropdownParent: jQuery('#modalAssignGuruPiket'),
              language: {
                noResults: function () {
                  return 'Guru tidak ditemukan';
                }
              }
            });
          }
        }
      }

      // Re-init / reset select2 saat modal dibuka
      const modalEl = document.getElementById('modalAssignGuruPiket');
      if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function () {
          initSelect2();
        });
      }

      initSelect2();

      function fetchTable(url = null) {
        const formData = new FormData(filterForm);
        const params   = new URLSearchParams(formData);

        params.set('sort_by', currentSortBy);
        params.set('sort_dir', currentSortDir);

        const fetchUrl = url || `{{ route('admin.guru-piket.index') }}?${params.toString()}`;

        fetch(fetchUrl, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(res => res.text())
        .then(html => {
          tableContainer.innerHTML = html;
          attachTableEvents();
        })
        .catch(err => console.error('Error fetching table:', err));
      }

      filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        fetchTable();
      });

      document.getElementById('filterStatus').addEventListener('change', function () {
        fetchTable();
      });

      function attachTableEvents() {
        // Handle sorting click
        document.querySelectorAll('#tableContainer .sortable').forEach(th => {
          th.addEventListener('click', function () {
            const sortBy = this.getAttribute('data-sort-by');
            if (currentSortBy === sortBy) {
              currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
            } else {
              currentSortBy  = sortBy;
              currentSortDir = 'asc';
            }
            fetchTable();
          });
        });

        // Handle pagination link click
        document.querySelectorAll('#tableContainer .pagination a').forEach(link => {
          link.addEventListener('click', function (e) {
            e.preventDefault();
            fetchTable(this.getAttribute('href'));
          });
        });

        // Handle Delete / Cabut Status Guru Piket
        document.querySelectorAll('.btn-delete-guru-piket').forEach(btn => {
          btn.addEventListener('click', function () {
            const guruId = this.dataset.id;
            const nama   = this.dataset.nama;

            Swal.fire({
              title: 'Cabut Status Guru Piket?',
              html: `Apakah Anda yakin ingin mencabut status Guru Piket dari <strong>${nama}</strong>?`,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Ya, Cabut Status',
              cancelButtonText: 'Batal',
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                html: 'das-swal-html',
                confirmButton: 'das-swal-confirm btn btn-danger',
                cancelButton: 'das-swal-cancel'
              },
              buttonsStyling: false
            }).then(result => {
              if (result.isConfirmed) {
                fetch(`{{ url('/admin/guru-piket') }}/${guruId}`, {
                  method: 'DELETE',
                  headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                  }
                })
                .then(res => res.json())
                .then(data => {
                  if (data.success) {
                    Swal.fire({
                      title: 'Berhasil!',
                      text: data.message,
                      icon: 'success',
                      customClass: {
                        popup: 'das-swal-popup',
                        title: 'das-swal-title',
                        html: 'das-swal-html',
                        confirmButton: 'btn btn-primary'
                      }
                    }).then(() => window.location.reload());
                  } else {
                    Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error');
                  }
                })
                .catch(err => {
                  Swal.fire('Error!', 'Gagal menghubungi server.', 'error');
                });
              }
            });
          });
        });

        // Handle Impersonate
        document.querySelectorAll('.btn-impersonate-piket').forEach(btn => {
          btn.addEventListener('click', function () {
            const url  = this.dataset.url;
            const nama = this.dataset.nama;

            Swal.fire({
              title: 'Login Sebagai Guru Piket?',
              html: `Anda akan masuk sebagai <strong>${nama}</strong>.`,
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Ya, Login',
              cancelButtonText: 'Batal',
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                html: 'das-swal-html',
                confirmButton: 'das-swal-confirm btn btn-success',
                cancelButton: 'das-swal-cancel'
              },
              buttonsStyling: false
            }).then(result => {
              if (result.isConfirmed) {
                window.location.href = url;
              }
            });
          });
        });
      }

      attachTableEvents();
    });
  </script>
@endsection
