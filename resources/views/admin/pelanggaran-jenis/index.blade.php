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

      <div class="das-hero__actions">
        <a href="{{ route('admin.pelanggaran-jenis.create') }}" class="btn das-btn --warning">
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
        <div class="col-6 col-md-2">
          <select id="filterStatus" name="is_aktif" class="form-select border-secondary text-white">
            <option value="">-- Status --</option>
            <option value="1" {{ request('is_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ request('is_aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
          </select>
        </div>
        <div class="col-6 col-md-1">
          <select id="perPageSelect" name="per_page" class="form-select border-secondary text-white">
            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
            <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
          </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100"><i class="ti tabler-filter me-1"></i> Filter</button>
          <a href="{{ route('admin.pelanggaran-jenis.index') }}" class="btn btn-secondary" title="Reset Filter"><i class="ti tabler-refresh"></i></a>
        </div>
      </form>
    </div>
  </div>

  {{-- DATA PANEL --}}
  <div class="das-panel" id="tableContainer">
    @include('admin.pelanggaran-jenis.table')
  </div>
@endsection

@section('page-script')
  <script>
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
            $('[data-bs-toggle="tooltip"]').tooltip();
          },
          error: function () {
            $container.removeClass('opacity-50');
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'Gagal memuat data jenis pelanggaran.',
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
        loadTable("{{ route('admin.pelanggaran-jenis.index') }}");
      });

      $('#filterKategori, #filterStatus, #perPageSelect').on('change', function () {
        loadTable("{{ route('admin.pelanggaran-jenis.index') }}");
      });

      $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        loadTable(url);
      });

      // Ajax Delete / Soft Delete Confirmation
      $(document).on('click', '.btn-delete-jenis', function (e) {
        e.preventDefault();
        var deleteUrl = $(this).data('url');
        var nama = $(this).data('nama');
        var count = $(this).data('count');

        var confirmHtml = 'Jenis pelanggaran <b>' + nama + '</b> akan dihapus.';
        if (count > 0) {
          confirmHtml += '<br><span class="text-warning small"><i class="ti tabler-alert-triangle me-1"></i> Data ini sudah tercatat ' + count + ' kali oleh siswa. Sistem hanya akan menonaktifkan/mengarsipkan (soft delete) data ini agar riwayat data siswa tetap aman.</span>';
        }

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
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: {
                      popup: 'das-swal-popup',
                      title: 'das-swal-title',
                      htmlContainer: 'das-swal-html'
                    }
                  });
                  loadTable(window.location.href);
                }
              },
              error: function () {
                Swal.fire({
                  icon: 'error',
                  title: 'Gagal',
                  text: 'Terjadi kesalahan saat menghapus data.',
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
