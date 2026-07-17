@extends('layouts/layoutMaster')

@section('title', 'Master Jurusan')

@section('page-style')
  <style>
    .jurusan-row-hover {
      transition: background 0.15s ease;
    }

    .jurusan-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .action-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 0.78rem;
      font-weight: 500;
      text-decoration: none;
      border: none;
      cursor: pointer;
      transition: opacity 0.15s ease, transform 0.15s ease;
    }

    .action-btn:hover {
      opacity: 0.85;
      transform: translateY(-1px);
    }

    #modalTambahJurusan .modal-content,
    #modalEditJurusan .modal-content,
    #modalHapusJurusan .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #1e1e2d;
      border-radius: 12px;
      overflow: hidden;
    }

    #modalTambahJurusan .modal-header,
    #modalEditJurusan .modal-header {
      background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1.25rem 1.5rem;
    }

    #modalTambahJurusan .modal-body,
    #modalEditJurusan .modal-body {
      padding: 1.5rem;
    }

    #modalTambahJurusan .modal-footer,
    #modalEditJurusan .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1rem 1.5rem;
      background: rgba(255, 255, 255, 0.02);
    }

    #modalHapusJurusan .modal-header {
      background: linear-gradient(135deg, #2d1a1a 0%, #3d0f0f 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    #modalHapusJurusan .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.02);
    }

    .modal-icon-header {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
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
            <i class="ti tabler-books text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a>
            / Master Jurusan
          </div>
          <h4 class="das-hero__title text-gradient-gold">Master Jurusan</h4>
          <p class="das-hero__subtitle">Kelola kode dan nama jurusan/program keahlian sekolah.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <button type="button" class="btn das-btn --primary" onclick="openTambahJurusan()">
          <i class="ti tabler-plus me-1"></i> Tambah Jurusan
        </button>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGES --}}
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

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
      style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
      <ul class="mb-0 ps-3 small">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: TABEL DATA JURUSAN
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-books text-info"></i> Daftar Jurusan
      </h6>
      <div class="d-flex align-items-center gap-3 flex-wrap">
        {{-- Search --}}
        <div class="position-relative" style="min-width:220px;max-width:320px;">
          <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"
            style="font-size:0.85rem;pointer-events:none;"></i>
          <input type="text" id="searchJurusan" class="form-control border-0 text-white"
            placeholder="Cari kode atau nama..." value="{{ request('search') }}"
            style="background:rgba(255,255,255,0.05);height:38px;padding-left:2.2rem;font-size:0.85rem;">
        </div>
        {{-- Per page --}}
        <select id="perPageJurusan" class="form-select border-0 text-white w-auto"
          style="background:rgba(255,255,255,0.05);height:38px;font-size:0.85rem;cursor:pointer;">
          <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
        </select>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="jurusanTableContainer">
        @include('admin.jurusan.table')
      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════════════
       MODAL TAMBAH JURUSAN
  ══════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalTambahJurusan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
      <div class="modal-content shadow-lg">

        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header"
              style="background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.35);">
              <i class="ti tabler-plus text-info fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Tambah Jurusan</h5>
              <small class="text-white-50">Tambahkan master program keahlian baru.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('admin.jurusan.store') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label small fw-semibold text-white-75" for="kode">Kode Jurusan <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="kode" name="kode" placeholder="Contoh: TKJ" required style="text-transform: uppercase;">
              <small class="text-white-50">Maksimal 20 karakter, unik.</small>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold text-white-75" for="nama">Nama Jurusan <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh: Teknik Komputer & Jaringan" required>
            </div>
          </div>

          <div class="modal-footer gap-2">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              <i class="ti tabler-x me-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
              <i class="ti tabler-device-floppy me-1"></i> Simpan
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════════════
       MODAL EDIT JURUSAN
  ══════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalEditJurusan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
      <div class="modal-content shadow-lg">

        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header"
              style="background:rgba(255,159,67,0.2);border:1px solid rgba(255,159,67,0.35);">
              <i class="ti tabler-pencil text-warning fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Ubah Jurusan</h5>
              <small class="text-white-50">Ubah data program keahlian.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>

        <form id="formEditJurusan" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label small fw-semibold text-white-75" for="edit_kode">Kode Jurusan <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="edit_kode" name="kode" required style="text-transform: uppercase;">
              <small class="text-white-50">Maksimal 20 karakter, unik.</small>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold text-white-75" for="edit_nama">Nama Jurusan <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="edit_nama" name="nama" required>
            </div>
          </div>

          <div class="modal-footer gap-2">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              <i class="ti tabler-x me-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-warning fw-semibold px-4 shadow-sm">
              <i class="ti tabler-device-floppy me-1"></i> Simpan Perubahan
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════════════
       MODAL KONFIRMASI HAPUS JURUSAN
  ══════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalHapusJurusan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
      <div class="modal-content shadow-lg">
        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div
              style="width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(234,84,85,0.2);border:1px solid rgba(234,84,85,0.35);">
              <i class="ti tabler-alert-triangle text-danger fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Hapus Jurusan</h5>
              <small class="text-white-50">Tindakan ini tidak bisa dibatalkan.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center py-4">
          <p class="mb-1 text-white-50">Apakah Anda yakin ingin menghapus jurusan berikut?</p>
          <p class="fw-bold text-warning fs-6 mb-2" id="hapusJurusanName">—</p>
          <p class="small text-white-50 mb-0">
            <i class="ti tabler-info-circle me-1"></i>
            Jurusan hanya dapat dihapus jika tidak ada kelas yang terhubung.
          </p>
        </div>
        <div class="modal-footer gap-2 justify-content-center">
          <button type="button" class="btn btn-label-secondary px-4" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Batal
          </button>
          <form id="formHapusJurusan" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger fw-semibold px-4 shadow-sm">
              <i class="ti tabler-trash me-1"></i> Ya, Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    // ── Open Modals ─────────────────────────────────────────
    function openTambahJurusan() {
      document.getElementById('kode').value = '';
      document.getElementById('nama').value = '';
      new bootstrap.Modal(document.getElementById('modalTambahJurusan')).show();
    }

    function openEditJurusan(data) {
      document.getElementById('edit_kode').value = data.kode;
      document.getElementById('edit_nama').value = data.nama;
      document.getElementById('formEditJurusan').action = "{{ url('admin/jurusan') }}/" + data.id;
      new bootstrap.Modal(document.getElementById('modalEditJurusan')).show();
    }

    function openHapusJurusan(id, name) {
      document.getElementById('hapusJurusanName').textContent = name;
      document.getElementById('formHapusJurusan').action = "{{ url('admin/jurusan') }}/" + id;
      new bootstrap.Modal(document.getElementById('modalHapusJurusan')).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
      // ── Live search & pagination ──────────────────────────
      const container     = document.getElementById('jurusanTableContainer');
      const searchInput   = document.getElementById('searchJurusan');
      const perPageSelect = document.getElementById('perPageJurusan');
      const indexUrl      = '{{ route('admin.jurusan.index') }}';
      let searchTimeout;

      function fetchJurusan(page) {
        page = page || 1;
        const search  = encodeURIComponent(searchInput.value || '');
        const perPage = perPageSelect.value || 10;
        const url     = indexUrl + '?page=' + page + '&search=' + search + '&per_page=' + perPage;

        container.style.opacity      = '0.45';
        container.style.pointerEvents = 'none';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(function (res) { return res.text(); })
          .then(function (html) {
            container.innerHTML         = html;
            container.style.opacity      = '1';
            container.style.pointerEvents = 'auto';
          })
          .catch(function () {
            container.style.opacity      = '1';
            container.style.pointerEvents = 'auto';
          });
      }

      // Debounce search
      searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () { fetchJurusan(1); }, 400);
      });

      // Per-page change
      perPageSelect.addEventListener('change', function () {
        fetchJurusan(1);
      });

      // Pagination link click delegation
      container.addEventListener('click', function (e) {
        const link = e.target.closest('a.das-page-btn');
        if (link) {
          e.preventDefault();
          const page = link.dataset.page
            || (link.href ? new URL(link.href).searchParams.get('page') : null)
            || 1;
          fetchJurusan(page);
        }
      });
    });
  </script>
@endsection
