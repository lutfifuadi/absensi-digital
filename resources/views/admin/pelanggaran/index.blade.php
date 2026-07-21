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

    .select2-container--default .select2-selection--single {
      background-color: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
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
  </style>
@endsection

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h4 class="fw-bold mb-1"><span class="text-muted fw-light">Kesiswaan /</span> Pelanggaran Siswa</h4>
        <p class="text-muted mb-0">Manajemen pencatatan pelanggaran, perhitungan poin, dan penerbitan SP otomatis.</p>
      </div>
      <div>
        @can('create', App\Models\PelanggaranSiswa::class)
          <a href="{{ route('admin.pelanggaran.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="ti ti-plus fs-5"></i> Catat Pelanggaran
          </a>
        @endcan
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible d-flex align-items-center mb-4" role="alert">
        <i class="ti ti-circle-check me-2 fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible d-flex align-items-center mb-4" role="alert">
        <i class="ti ti-circle-x me-2 fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <!-- Panel Filter -->
    <div class="card mb-4 bg-glass border-light shadow-sm">
      <div class="card-body">
        <h5 class="card-title text-white mb-3 d-flex align-items-center gap-2">
          <i class="ti ti-adjustments-horizontal text-info"></i> Panel Filter
        </h5>
        
        <form id="filterForm" method="GET" action="{{ route('admin.pelanggaran.index') }}">
          <div class="row g-3">
            <!-- Filter Tahun Akademik -->
            <div class="col-md-3">
              <label class="form-label text-light small fw-medium">Tahun Akademik</label>
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
              <label class="form-label text-light small fw-medium">Kelas</label>
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
              <label class="form-label text-light small fw-medium">Kategori</label>
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
              <label class="form-label text-light small fw-medium">Level SP</label>
              <select class="form-select" name="level_sp" id="filterSp">
                <option value="">Semua Level SP</option>
                <option value="SP1" {{ request('level_sp') == 'SP1' ? 'selected' : '' }}>SP1</option>
                <option value="SP2" {{ request('level_sp') == 'SP2' ? 'selected' : '' }}>SP2</option>
                <option value="SP3" {{ request('level_sp') == 'SP3' ? 'selected' : '' }}>SP3</option>
              </select>
            </div>

            <!-- Filter Bulan Kejadian -->
            <div class="col-md-3">
              <label class="form-label text-light small fw-medium">Bulan Kejadian</label>
              <input type="month" class="form-control" name="bulan" id="filterBulan" value="{{ request('bulan') }}">
            </div>

            <!-- Pencarian Nama/NIS -->
            <div class="col-md-6">
              <label class="form-label text-light small fw-medium">Cari Siswa (Nama / NIS)</label>
              <div class="input-group">
                <span class="input-group-text bg-transparent border-light"><i class="ti ti-search text-muted"></i></span>
                <input type="text" class="form-control" name="search" id="filterSearch" value="{{ request('search') }}" placeholder="Ketik nama lengkap atau NIS siswa...">
              </div>
            </div>

            <!-- Tombol Aksi Filter -->
            <div class="col-md-3 d-flex align-items-end gap-2">
              <button type="submit" class="btn btn-info w-100"><i class="ti ti-filter me-1"></i> Terapkan</button>
              <a href="{{ route('admin.pelanggaran.index') }}" class="btn btn-secondary w-100"><i class="ti ti-refresh me-1"></i> Reset</a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabel Riwayat Pelanggaran -->
    <div class="card bg-glass border-light shadow-sm">
      <div id="tableContainer">
        @include('admin.pelanggaran.table')
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus + Alasan -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-glass border-light" style="background-color: #1a1a2e;">
        <div class="modal-header border-light">
          <h5 class="modal-title text-white"><i class="ti ti-trash text-danger me-2"></i> Konfirmasi Penghapusan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="deleteForm" method="POST">
          @csrf
          @method('DELETE')
          <div class="modal-body text-light">
            <p>Apakah Anda yakin ingin menghapus catatan pelanggaran ini?</p>
            <p class="text-warning small mb-3">
              <i class="ti ti-info-circle me-1"></i> Poin siswa akan berkurang, namun Surat Peringatan (SP) yang sudah terlanjur diterbitkan tetap berlaku.
            </p>
            <div class="mb-3">
              <label class="form-label required">Tulis Alasan Penghapusan</label>
              <textarea class="form-control" name="alasan_penghapusan" rows="3" placeholder="Contoh: Salah input data, klarifikasi dengan wali kelas..." required></textarea>
            </div>
          </div>
          <div class="modal-footer border-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">Hapus Sekarang</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const filterForm = document.getElementById('filterForm');
      const tableContainer = document.getElementById('tableContainer');

      // AJAX reload function
      const reloadTable = () => {
        const formData = new FormData(filterForm);
        const queryParams = new URLSearchParams(formData).toString();
        
        fetch(`{{ route('admin.pelanggaran.index') }}?${queryParams}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.text())
        .then(html => {
          tableContainer.innerHTML = html;
        })
        .catch(err => console.error('Gagal mengambil data:', err));
      };

      // Auto filter on change / input with debounce
      let debounceTimeout;
      const inputs = filterForm.querySelectorAll('select, input');
      inputs.forEach(input => {
        input.addEventListener('change', reloadTable);
        if (input.type === 'text') {
          input.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(reloadTable, 500);
          });
        }
      });

      // Handle pagination click
      document.addEventListener('click', function (e) {
        const pageLink = e.target.closest('.pagination a');
        if (pageLink) {
          e.preventDefault();
          const url = pageLink.getAttribute('href');
          
          fetch(url, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(response => response.text())
          .then(html => {
            tableContainer.innerHTML = html;
          })
          .catch(err => console.error('Gagal mengambil data pagination:', err));
        }
      });
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
