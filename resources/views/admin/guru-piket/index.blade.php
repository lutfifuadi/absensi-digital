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

    .sortable {
      cursor: pointer;
      user-select: none;
    }

    .sortable:hover {
      color: #fff !important;
    }

    .das-btn.--info {
      background: linear-gradient(135deg, #00bad1, #0284c7);
      color: #fff;
      border: none;
      box-shadow: 0 4px 12px rgba(0, 186, 209, 0.25);
    }

    .das-btn.--info:hover {
      background: linear-gradient(135deg, #00a5ba, #0270a9);
      color: #fff;
      box-shadow: 0 6px 16px rgba(0, 186, 209, 0.35);
    }

    .das-btn.--secondary {
      background: rgba(255, 255, 255, 0.08);
      color: #cbd5e1;
      border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .das-btn.--secondary:hover {
      background: rgba(255, 255, 255, 0.14);
      color: #fff;
    }

    /* Modal Redesign for Guru Piket */
    .das-modal {
      background: #1e293b !important;
      border: 1px solid rgba(255, 255, 255, 0.12) !important;
      border-radius: 12px !important;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6) !important;
      overflow: hidden;
    }

    .das-modal-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.02);
    }

    .das-modal-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #ffffff;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .das-modal-body {
      padding: 1.5rem;
    }

    .das-modal-foot {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 0.75rem;
      padding: 1rem 1.5rem;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(0, 0, 0, 0.15);
    }

    .modal-desc {
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.65);
      margin-bottom: 1.25rem;
      line-height: 1.4;
    }

    .form-label-custom {
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: rgba(255, 255, 255, 0.75);
      margin-bottom: 0.4rem;
      display: block;
    }

    .form-label-custom.required::after {
      content: ' *';
      color: #ea5455;
    }

    /* Select2 Dark Theme Customization */
    .select2-container--default .select2-selection--single {
      background-color: #0f172a !important;
      border: 1px solid rgba(255, 255, 255, 0.15) !important;
      border-radius: 8px !important;
      height: 42px !important;
      display: flex !important;
      align-items: center !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #f8fafc !important;
      padding-left: 12px !important;
      font-size: 0.9rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 40px !important;
    }

    .select2-dropdown {
      background-color: #1e293b !important;
      border: 1px solid rgba(255, 255, 255, 0.15) !important;
      border-radius: 8px !important;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5) !important;
      z-index: 1060 !important;
    }

    .select2-search__field {
      background-color: #0f172a !important;
      color: #ffffff !important;
      border: 1px solid rgba(255, 255, 255, 0.15) !important;
      border-radius: 6px !important;
    }

    .select2-results__option {
      color: #cbd5e1 !important;
      padding: 8px 12px !important;
      font-size: 0.875rem !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #00bad1 !important;
      color: #ffffff !important;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
      background-color: rgba(0, 186, 209, 0.2) !important;
      color: #00bad1 !important;
    }

    .btn-close {
      filter: invert(1) grayscale(100%) brightness(200%);
      opacity: 0.7;
      transition: opacity 0.2s ease;
    }

    .btn-close:hover {
      opacity: 1;
    }

    /* Modal Nav Pills Styling */
    .piket-nav-pills .nav-link {
      background: rgba(255, 255, 255, 0.05);
      color: rgba(255, 255, 255, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      transition: all 0.2s ease;
    }

    .piket-nav-pills .nav-link.active {
      background: linear-gradient(135deg, #00bad1, #0284c7) !important;
      color: #ffffff !important;
      border-color: #00bad1 !important;
      box-shadow: 0 4px 12px rgba(0, 186, 209, 0.3);
    }

    /* SweetAlert2 Glassmorphism Dark Styling */
    .das-swal-popup {
      background: #1e293b !important;
      border: 1px solid rgba(255, 255, 255, 0.12) !important;
      border-radius: 16px !important;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7) !important;
      color: #ffffff !important;
      padding: 1.5rem !important;
    }

    .das-swal-title {
      color: #ffffff !important;
      font-size: 1.25rem !important;
      font-weight: 700 !important;
      margin-bottom: 0.5rem !important;
    }

    .das-swal-html {
      color: rgba(255, 255, 255, 0.8) !important;
      font-size: 0.9rem !important;
    }

    .swal2-icon.swal2-question {
      border-color: #00bad1 !important;
      color: #00bad1 !important;
      background: rgba(0, 186, 209, 0.1) !important;
      box-shadow: 0 0 25px rgba(0, 186, 209, 0.25) !important;
    }

    .swal2-icon.swal2-warning {
      border-color: #ff9f43 !important;
      color: #ff9f43 !important;
      background: rgba(255, 159, 67, 0.1) !important;
      box-shadow: 0 0 25px rgba(255, 159, 67, 0.25) !important;
    }

    .swal2-icon.swal2-success {
      border-color: #28c76f !important;
      color: #28c76f !important;
      background: rgba(40, 199, 111, 0.1) !important;
      box-shadow: 0 0 25px rgba(40, 199, 111, 0.25) !important;
    }

    .swal2-icon.swal2-error {
      border-color: #ea5455 !important;
      color: #ea5455 !important;
      background: rgba(234, 84, 85, 0.1) !important;
      box-shadow: 0 0 25px rgba(234, 84, 85, 0.25) !important;
    }

    .swal2-actions {
      gap: 0.75rem !important;
      margin-top: 1.25rem !important;
      justify-content: center !important;
      width: 100% !important;
    }

    .das-swal-confirm-info {
      background: linear-gradient(135deg, #00bad1, #0284c7) !important;
      color: #ffffff !important;
      font-weight: 600 !important;
      padding: 0.65rem 1.5rem !important;
      border-radius: 8px !important;
      border: none !important;
      font-size: 0.875rem !important;
      box-shadow: 0 4px 14px rgba(0, 186, 209, 0.35) !important;
      transition: all 0.2s ease !important;
    }

    .das-swal-confirm-info:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(0, 186, 209, 0.45) !important;
    }

    .das-swal-confirm-danger {
      background: linear-gradient(135deg, #ea5455, #dc2626) !important;
      color: #ffffff !important;
      font-weight: 600 !important;
      padding: 0.65rem 1.5rem !important;
      border-radius: 8px !important;
      border: none !important;
      font-size: 0.875rem !important;
      box-shadow: 0 4px 14px rgba(234, 84, 85, 0.35) !important;
      transition: all 0.2s ease !important;
    }

    .das-swal-confirm-danger:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(234, 84, 85, 0.45) !important;
    }

    .das-swal-cancel {
      background: rgba(255, 255, 255, 0.08) !important;
      color: rgba(255, 255, 255, 0.8) !important;
      font-weight: 600 !important;
      padding: 0.65rem 1.5rem !important;
      border-radius: 8px !important;
      border: 1px solid rgba(255, 255, 255, 0.12) !important;
      font-size: 0.875rem !important;
      transition: all 0.2s ease !important;
    }

    .das-swal-cancel:hover {
      background: rgba(255, 255, 255, 0.12) !important;
      color: #ffffff !important;
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
            Data Civitas / Guru Piket
          </div>
          <h4 class="das-hero__title text-gradient-gold">Data Guru Piket</h4>
          <p class="das-hero__subtitle">Kelola penetapan dan penugasan Guru Piket harian sekolah.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <button type="button" class="btn das-btn --info" data-bs-toggle="modal" data-bs-target="#modalAssignGuruPiket">
          <i class="ti tabler-user-plus me-1"></i> Tambah / Tetapkan Guru Piket
        </button>
      </div>
    </div>
  </div>

  {{-- FILTER & SEARCH PANEL --}}
  <div class="das-panel mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__head">
      <h5 class="das-panel__title mb-0 fw-bold">
        <i class="ti tabler-filter text-info me-2"></i>Filter & Pencarian Guru Piket
      </h5>
    </div>
    <div class="das-panel__body py-3">
      <form id="filterForm" action="{{ route('admin.guru-piket.index') }}" method="GET" class="row g-3">
        <div class="col-md-5">
          <div class="input-group">
            <span class="input-group-text bg-dark border-secondary text-white-50"><i class="ti tabler-search"></i></span>
            <input type="text" name="search" id="filterSearch" value="{{ request('search') }}" class="form-control bg-dark text-white border-secondary" placeholder="Cari Nama, NIP, atau Email Guru Piket...">
          </div>
        </div>
        <div class="col-md-3">
          <select name="status" id="filterStatus" class="form-select bg-dark text-white border-secondary">
            <option value="">-- Semua Status --</option>
            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            <option value="belum lengkap" {{ request('status') === 'belum lengkap' ? 'selected' : '' }}>Belum Memiliki Akun</option>
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

  {{-- MODAL TAMBAH / TETAPKAN GURU PIKET --}}
  <div class="modal fade" id="modalAssignGuruPiket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content das-modal">
        <div class="das-modal-head">
          <h5 class="das-modal-title">
            <i class="ti tabler-user-plus text-info fs-4"></i> Tambah / Tetapkan Guru Piket
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        {{-- Nav Tabs --}}
        <div class="px-4 pt-3 pb-0 border-bottom border-white border-opacity-10">
          <ul class="nav nav-pills piket-nav-pills nav-justified gap-2" id="piketModalTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active fw-bold small py-2" id="tab-assign-tab" data-bs-toggle="pill" data-bs-target="#tab-assign" type="button" role="tab">
                <i class="ti tabler-user-check me-1"></i> 1. Pilih Guru Terdaftar
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link fw-bold small py-2" id="tab-create-tab" data-bs-toggle="pill" data-bs-target="#tab-create" type="button" role="tab">
                <i class="ti tabler-user-plus me-1"></i> 2. Buat Petugas Piket Baru (Manual)
              </button>
            </li>
          </ul>
        </div>

        <div class="tab-content" id="piketModalTabContent">
          {{-- TAB 1: ASSIGN GURU EXISTING --}}
          <div class="tab-pane fade show active" id="tab-assign" role="tabpanel">
            <form id="formAssignGuruPiket" action="{{ route('admin.guru-piket.store') }}" method="POST">
              @csrf
              <input type="hidden" name="mode" value="assign">
              <div class="das-modal-body">
                <div class="modal-desc">
                  Cari dan pilih guru aktif yang sudah terdaftar di bawah ini untuk ditugaskan sebagai <strong>Guru Piket</strong> harian sekolah.
                </div>

                <div class="mb-3">
                  <label class="form-label-custom required">Pilih Guru Aktif</label>
                  <select id="guruSelect2" name="guru_id" class="form-select select2" required>
                    <option value="">-- Cari Nama / NIP Guru --</option>
                    @foreach ($availableGurus as $g)
                      <option value="{{ $g->id }}">
                        {{ $g->nama_lengkap }} (NIP: {{ $g->nip ?? '-' }})
                      </option>
                    @endforeach
                  </select>
                  @if ($availableGurus->isEmpty())
                    <div class="form-text text-warning mt-2 d-flex align-items-center gap-1">
                      <i class="ti tabler-alert-triangle fs-6"></i> Semua guru aktif telah ditugaskan sebagai Guru Piket atau belum memiliki akun pengguna.
                    </div>
                  @endif
                </div>
              </div>
              <div class="das-modal-foot">
                <button type="button" class="btn btn-label-secondary px-4 py-2 fw-medium" data-bs-dismiss="modal">
                  <i class="ti tabler-x me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-info px-4 py-2 fw-semibold shadow-sm" {{ $availableGurus->isEmpty() ? 'disabled' : '' }}>
                  <i class="ti tabler-check me-1"></i> Simpan Penugasan
                </button>
              </div>
            </form>
          </div>

          {{-- TAB 2: CREATE PETUGAS PIKET BARU secara MANUAL --}}
          <div class="tab-pane fade" id="tab-create" role="tabpanel">
            <form id="formCreateGuruPiket" action="{{ route('admin.guru-piket.store') }}" method="POST">
              @csrf
              <input type="hidden" name="mode" value="create">
              <div class="das-modal-body">
                <div class="modal-desc">
                  Isikan formulir berikut untuk mendaftarkan <strong>Petugas Piket Baru</strong> secara manual ke dalam sistem. Akun pengguna akan dibuatkan secara otomatis.
                </div>

                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <label class="form-label-custom required">Nama Lengkap & Gelar</label>
                    <input type="text" name="nama" class="form-control bg-dark text-white border-secondary" placeholder="contoh: Bpk. Ahmad Fauzi, S.Pd." required>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label-custom">NIP / Username</label>
                    <input type="text" name="nip" class="form-control bg-dark text-white border-secondary" placeholder="contoh: 19850101... (Opsional)">
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label-custom required">Email Resmi / Akun</label>
                    <input type="email" name="email" class="form-control bg-dark text-white border-secondary" placeholder="contoh: ahmad@sekolah.sch.id" required>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label-custom required">Password Akun Piket</label>
                    <div class="input-group">
                      <input type="password" id="manual_password" name="password" class="form-control bg-dark text-white border-secondary" placeholder="Minimal 6 karakter..." required>
                      <button class="btn btn-outline-secondary" type="button" id="btnToggleManualPass">
                        <i class="ti tabler-eye text-white-50" id="iconManualPass"></i>
                      </button>
                    </div>
                  </div>
                  <div class="col-12">
                    <label class="form-label-custom">Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" class="form-control bg-dark text-white border-secondary" placeholder="contoh: 081234567890 (Opsional)">
                  </div>
                </div>
              </div>
              <div class="das-modal-foot">
                <button type="button" class="btn btn-label-secondary px-4 py-2 fw-medium" data-bs-dismiss="modal">
                  <i class="ti tabler-x me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-success px-4 py-2 fw-semibold shadow-sm">
                  <i class="ti tabler-user-plus me-1"></i> Buat & Simpan Petugas Piket
                </button>
              </div>
            </form>
          </div>
        </div>

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

      // Toggle Show/Hide Password di Form Manual
      const btnToggleManualPass = document.getElementById('btnToggleManualPass');
      const manualPasswordInput = document.getElementById('manual_password');
      const iconManualPass = document.getElementById('iconManualPass');

      if (btnToggleManualPass && manualPasswordInput && iconManualPass) {
        btnToggleManualPass.addEventListener('click', function () {
          const type = manualPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
          manualPasswordInput.setAttribute('type', type);
          if (type === 'password') {
            iconManualPass.className = 'ti tabler-eye text-white-50';
          } else {
            iconManualPass.className = 'ti tabler-eye-off text-warning';
          }
        });
      }

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

      // Submit Form Assign Guru Existing
      const formAssignGuruPiket = document.getElementById('formAssignGuruPiket');
      if (formAssignGuruPiket) {
        formAssignGuruPiket.addEventListener('submit', function (e) {
          e.preventDefault();
          const formData = new FormData(this);

          fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
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
                  htmlContainer: 'das-swal-html',
                  confirmButton: 'das-swal-confirm-info'
                },
                buttonsStyling: false
              }).then(() => window.location.reload());
            } else {
              Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error');
            }
          })
          .catch(err => {
            Swal.fire('Error!', 'Gagal menghubungi server.', 'error');
          });
        });
      }

      // Submit Form Create Petugas Piket Manual
      const formCreateGuruPiket = document.getElementById('formCreateGuruPiket');
      if (formCreateGuruPiket) {
        formCreateGuruPiket.addEventListener('submit', function (e) {
          e.preventDefault();
          const formData = new FormData(this);

          fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
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
                  htmlContainer: 'das-swal-html',
                  confirmButton: 'das-swal-confirm-info'
                },
                buttonsStyling: false
              }).then(() => window.location.reload());
            } else {
              let errMsg = data.message || 'Terjadi kesalahan.';
              if (data.errors) {
                errMsg = Object.values(data.errors).flat().join('<br>');
              }
              Swal.fire({
                title: 'Gagal Simpan!',
                html: errMsg,
                icon: 'error',
                customClass: {
                  popup: 'das-swal-popup',
                  title: 'das-swal-title',
                  htmlContainer: 'das-swal-html',
                  confirmButton: 'das-swal-confirm-danger'
                },
                buttonsStyling: false
              });
            }
          })
          .catch(err => {
            Swal.fire('Error!', 'Gagal menghubungi server.', 'error');
          });
        });
      }

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
              html: `
                <div class="d-flex flex-column align-items-center text-center">
                  <div class="mb-3 px-3 py-2 rounded-3 w-100" style="background: rgba(234, 84, 85, 0.1); border: 1px solid rgba(234, 84, 85, 0.2);">
                    <span class="text-white-50 small">Guru Terpilih:</span>
                    <div class="fw-bold text-danger fs-6 mt-1">${nama}</div>
                  </div>
                  <p class="text-white-50 small mb-0">
                    Status peran <strong>Guru Piket</strong> akan dihapus dari akun pengguna ini.
                  </p>
                </div>
              `,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: '<i class="ti tabler-user-minus me-1"></i> Ya, Cabut Status',
              cancelButtonText: '<i class="ti tabler-x me-1"></i> Batal',
              reverseButtons: true,
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                htmlContainer: 'das-swal-html',
                confirmButton: 'das-swal-confirm-danger',
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
                        htmlContainer: 'das-swal-html',
                        confirmButton: 'das-swal-confirm-info'
                      },
                      buttonsStyling: false
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

        // Handle Impersonate — harus menggunakan POST + CSRF (bukan GET)
        document.querySelectorAll('.btn-impersonate-piket').forEach(btn => {
          btn.addEventListener('click', function () {
            const url  = this.dataset.url;
            const nama = this.dataset.nama;

            Swal.fire({
              title: 'Login Sebagai Guru Piket?',
              html: `
                <div class="d-flex flex-column align-items-center text-center">
                  <div class="mb-3 px-3 py-2 rounded-3 w-100" style="background: rgba(0, 186, 209, 0.1); border: 1px solid rgba(0, 186, 209, 0.2);">
                    <span class="text-white-50 small">Akun Target:</span>
                    <div class="fw-bold text-info fs-6 mt-1">${nama}</div>
                  </div>
                  <p class="text-white-50 small mb-0">
                    <i class="ti tabler-shield-check text-success me-1"></i>
                    Anda akan masuk ke akun Guru Piket ini. Tindakan ini akan dicatat dalam log sistem.
                  </p>
                </div>
              `,
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: '<i class="ti tabler-login me-1"></i> Ya, Login Sekarang',
              cancelButtonText: '<i class="ti tabler-x me-1"></i> Batal',
              reverseButtons: true,
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                htmlContainer: 'das-swal-html',
                confirmButton: 'das-swal-confirm-info',
                cancelButton: 'das-swal-cancel'
              },
              buttonsStyling: false
            }).then(result => {
              if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;

                const csrfInput = document.createElement('input');
                csrfInput.type  = 'hidden';
                csrfInput.name  = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
              }
            });
          });
        });
      }

      attachTableEvents();
    });
  </script>
@endsection
