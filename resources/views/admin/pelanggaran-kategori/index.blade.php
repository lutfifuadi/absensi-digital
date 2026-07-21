@extends('layouts/layoutMaster')

@section('title', 'Kategori Pelanggaran')

@section('page-style')
  <style>
    .kategori-row-hover {
      transition: background 0.15s ease;
    }

    .kategori-row-hover:hover {
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

    .color-preview {
      width: 24px;
      height: 24px;
      border-radius: 6px;
      display: inline-block;
      border: 1px solid rgba(255,255,255,0.2);
      vertical-align: middle;
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
            <i class="ti tabler-swords text-warning"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Master Data / Kategori Pelanggaran
          </div>
          <h4 class="das-hero__title text-gradient-gold">Kategori Pelanggaran</h4>
          <p class="das-hero__subtitle">Kelola kategori utama poin pelanggaran siswa.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <button type="button" class="btn das-btn --warning" onclick="openCreateModal()">
          <i class="ti tabler-plus me-1"></i> Tambah Kategori
        </button>
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
        <div class="col-12 col-md-5">
          <div class="input-group input-group-merge">
            <span class="input-group-text bg-transparent border-secondary text-white-50"><i class="ti tabler-search"></i></span>
            <input type="text" id="filterSearch" name="search" class="form-control border-secondary text-white" placeholder="Cari nama atau deskripsi kategori..." value="{{ request('search') }}">
          </div>
        </div>
        <div class="col-6 col-md-3">
          <select id="filterStatus" name="is_aktif" class="form-select border-secondary">
            <option value="">-- Status Keaktifan --</option>
            <option value="1" {{ request('is_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ request('is_aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <select id="perPageSelect" name="per_page" class="form-select border-secondary">
            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 baris</option>
            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 baris</option>
            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 baris</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 baris</option>
          </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100"><i class="ti tabler-filter me-1"></i> Filter</button>
          <a href="{{ route('admin.pelanggaran-kategori.index') }}" class="btn btn-secondary" title="Reset Filter"><i class="ti tabler-refresh"></i></a>
        </div>
      </form>
    </div>
  </div>

  {{-- DATA PANEL --}}
  <div class="das-panel" id="tableContainer">
    @include('admin.pelanggaran-kategori.table')
  </div>

  {{-- MODAL TAMBAH/EDIT --}}
  <div class="modal fade" id="modalKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title" id="modalTitle">Tambah Kategori Pelanggaran</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formKategori" method="POST" action="">
          @csrf
          <input type="hidden" name="_method" id="formMethod" value="POST">
          <div class="das-modal-body text-white">
            <div class="mb-3">
              <label for="inputNama" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="inputNama" name="nama" required placeholder="Contoh: Kerapian, Kedisiplinan">
            </div>
            <div class="mb-3">
              <label for="inputDeskripsi" class="form-label">Deskripsi</label>
              <textarea class="form-control" id="inputDeskripsi" name="deskripsi" rows="3" placeholder="Deskripsi singkat kategori pelanggaran..."></textarea>
            </div>
            <div class="row mb-3">
              <div class="col-6">
                <label for="inputWarna" class="form-label">Warna Tag</label>
                <div class="d-flex gap-2 align-items-center">
                  <input type="color" class="form-control form-control-color" id="inputWarna" name="warna" value="#ef4444" style="width: 50px; height: 38px; padding: 0.2rem;">
                  <span class="small text-white-50">Hex color tag</span>
                </div>
              </div>
              <div class="col-6">
                <label for="inputUrutan" class="form-label">Urutan Tampil</label>
                <input type="number" class="form-control" id="inputUrutan" name="urutan" min="0" value="0">
              </div>
            </div>
            <div class="mb-3 form-check form-switch">
              <input class="form-check-input" type="checkbox" id="inputIsAktif" name="is_aktif" value="1" checked>
              <label class="form-check-input-label text-white" for="inputIsAktif">Status Aktif</label>
            </div>
          </div>
          <div class="modal-footer border-top border-secondary p-3">
            <button type="button" class="btn btn-outline-secondary text-white" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Kategori</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    var modalKategori = new bootstrap.Modal(document.getElementById('modalKategori'));
    var formKategori = document.getElementById('formKategori');
    var modalTitle = document.getElementById('modalTitle');
    var formMethod = document.getElementById('formMethod');
    var btnSubmit = document.getElementById('btnSubmit');

    function openCreateModal() {
      modalTitle.innerText = "Tambah Kategori Pelanggaran";
      formKategori.action = "{{ route('admin.pelanggaran-kategori.store') }}";
      formMethod.value = "POST";
      
      document.getElementById('inputNama').value = "";
      document.getElementById('inputDeskripsi').value = "";
      document.getElementById('inputWarna').value = "#ef4444";
      document.getElementById('inputUrutan').value = "0";
      document.getElementById('inputIsAktif').checked = true;
      
      modalKategori.show();
    }

    function openEditModal(data) {
      modalTitle.innerText = "Ubah Kategori Pelanggaran";
      formKategori.action = "{{ route('admin.pelanggaran-kategori.update', ':id') }}".replace(':id', data.id);
      formMethod.value = "PUT";

      document.getElementById('inputNama').value = data.nama;
      document.getElementById('inputDeskripsi').value = data.deskripsi || "";
      document.getElementById('inputWarna').value = data.warna || "#ef4444";
      document.getElementById('inputUrutan').value = data.urutan || 0;
      document.getElementById('inputIsAktif').checked = data.is_aktif;

      modalKategori.show();
    }

    // Ajax Filtering & Pagination
    $(function () {
      function loadTable(url) {
        var $container = $('#tableContainer');
        $container.addClass('opacity-50');

        $.ajax({
          url: url,
          type: 'GET',
          data: $('#filterForm').serialize(),
          success: function (html) {
            $container.html(html);
            $container.removeClass('opacity-50');
            // Re-initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
          },
          error: function () {
            $container.removeClass('opacity-50');
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'Gagal memuat data kategori.',
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                htmlContainer: 'das-swal-html'
              }
            });
          }
        });
      }

      $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        loadTable("{{ route('admin.pelanggaran-kategori.index') }}");
      });

      $('#filterStatus, #perPageSelect').on('change', function () {
        loadTable("{{ route('admin.pelanggaran-kategori.index') }}");
      });

      $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        loadTable(url);
      });

      // Ajax Delete Confirmation
      $(document).on('click', '.btn-delete-kategori', function (e) {
        e.preventDefault();
        var deleteUrl = $(this).data('url');
        var nama = $(this).data('nama');

        Swal.fire({
          title: 'Hapus Kategori?',
          html: 'Kategori <b>' + nama + '</b> akan dihapus secara permanen.',
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
            confirmButton: 'btn btn-danger das-swal-confirm',
            cancelButton: 'btn btn-secondary das-swal-cancel'
          }
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: deleteUrl,
              type: 'POST',
              data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
              },
              success: function (response) {
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
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: response.message || 'Kategori tidak dapat dihapus.',
                    customClass: {
                      popup: 'das-swal-popup',
                      title: 'das-swal-title',
                      htmlContainer: 'das-swal-html'
                    }
                  });
                }
              },
              error: function (xhr) {
                var msg = 'Terjadi kesalahan sistem.';
                if (xhr.status === 422 && xhr.responseJSON) {
                  msg = xhr.responseJSON.message;
                }
                Swal.fire({
                  icon: 'error',
                  title: 'Gagal',
                  text: msg,
                  customClass: {
                    popup: 'das-swal-popup',
                    title: 'das-swal-title',
                    htmlContainer: 'das-swal-html'
                  }
                });
              }
            });
          }
        });
      });
    });
  </script>
@endsection
