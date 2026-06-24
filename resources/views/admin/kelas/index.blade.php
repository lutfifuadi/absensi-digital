@extends('layouts/layoutMaster')

@section('title', 'Kelas')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-style')
  <style>
    .kelas-row-hover {
      transition: background 0.15s ease;
    }

    .kelas-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .action-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 5px 12px;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 500;
      text-decoration: none;
      border: none;
      cursor: pointer;
      transition: opacity 0.15s ease, transform 0.15s ease;
      color: #fff !important;
    }

    .action-btn:hover {
      opacity: 0.85;
      transform: translateY(-1px);
    }

    #modalKelas .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #1e1e2d;
      border-radius: 12px;
      overflow: hidden;
    }

    #modalKelas .modal-header {
      background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1.25rem 1.5rem;
    }

    #modalKelas .modal-body {
      padding: 1.5rem;
    }

    #modalKelas .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1rem 1.5rem;
      background: rgba(255, 255, 255, 0.02);
    }

    #modalKelas .form-control,
    #modalKelas .form-select,
    #modalKelas .select2-container .select2-selection--single {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: inherit;
      border-radius: 8px;
      transition: border-color 0.2s ease, background 0.2s ease;
    }

    #modalKelas .select2-container .select2-selection--single {
      height: 38px;
      display: flex;
      align-items: center;
    }

    #modalKelas .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #cdd2e0 !important;
      padding-left: 12px;
    }

    #modalKelas .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 36px;
    }

    #modalKelas .select2-dropdown {
      background: #1e1e2d;
      border-color: rgba(255, 255, 255, 0.12);
      color: #cdd2e0;
    }

    #modalKelas .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #0f3460;
    }

    #modalKelas .form-control:focus,
    #modalKelas .form-select:focus,
    #modalKelas .select2-container--default.select2-container--focus .select2-selection--single {
      background: rgba(255, 255, 255, 0.09);
      border-color: rgba(40, 199, 111, 0.6);
      box-shadow: 0 0 0 3px rgba(40, 199, 111, 0.12);
    }

    #modalKelas .form-control::placeholder {
      opacity: 0.4;
    }

    #modalKelas .form-select option {
      background: #1e1e2d;
      color: #cdd2e0;
    }

    #modalHapusKelas .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #1e1e2d;
      border-radius: 12px;
      overflow: hidden;
    }

    #modalHapusKelas .modal-header {
      background: linear-gradient(135deg, #2d1a1a 0%, #3d0f0f 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    #modalHapusKelas .modal-footer {
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

    .tingkat-badge {
      min-width: 36px;
      text-align: center;
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
      border: 1px solid rgba(255,255,255,0.08);
      background: transparent;
      color: #888;
      text-decoration: none;
      transition: all 0.18s ease;
      cursor: pointer;
      line-height: 1;
      font-family: inherit;
    }
    .das-page-btn:hover {
      background: rgba(255,255,255,0.08);
      color: #fff;
      border-color: rgba(255,255,255,0.12);
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
    #searchInput::placeholder { color: rgba(255,255,255,0.4); }
    #searchInput:focus {
      outline: none;
      box-shadow: none;
      background: rgba(255,255,255,0.08) !important;
      border-color: rgba(115,103,240,0.5) !important;
    }
    #perPageSelect option { background: #1a1a2e; color: #ccc; }
    #perPageSelect:focus { outline: none; box-shadow: none; }

    /* SEGMENTED TAB FILTERS */
    .tingkat-tab-btn {
      color: rgba(255, 255, 255, 0.6) !important;
      border: none;
      box-shadow: none !important;
      font-size: 0.8rem;
      font-weight: 600;
      padding: 0.35rem 1.1rem;
      border-radius: var(--das-radius, 5px);
      transition: all 0.2s ease-in-out;
      background: transparent;
    }
    .tingkat-tab-btn:hover {
      color: #fff !important;
      background: rgba(255, 255, 255, 0.05);
    }
    .tingkat-tab-btn.active {
      background: var(--das-primary) !important;
      color: #fff !important;
      box-shadow: 0 4px 12px rgba(115, 103, 240, 0.3) !important;
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
            <i class="ti tabler-door text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Kelas
          </div>
          <h4 class="das-hero__title text-gradient-gold">Data Kelas</h4>
          <p class="das-hero__subtitle">Kelola kelas, wali kelas, dan tahun akademik.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <button type="button" class="btn das-btn --secondary" data-bs-toggle="modal" data-bs-target="#modalImportKelas">
          <i class="ti tabler-file-import me-1"></i> Import
        </button>
        <button type="button" class="btn das-btn --secondary" data-bs-toggle="modal" data-bs-target="#modalCopyKelas">
          <i class="ti tabler-copy me-1"></i> Copy dari TA Sebelumnya
        </button>
        <button type="button" class="btn das-btn --primary" onclick="openTambahKelas()">
          <i class="ti tabler-plus me-1"></i> Tambah Kelas
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

  {{-- TABLE CARD --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-none d-lg-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Kelas
      </h6>

      <!-- Filter Segmented Tab (Desktop & Tablet) -->
      <div class="d-none d-md-flex align-items-center">
        <div class="tingkat-filter-pill p-1" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--das-radius, 5px); display: flex; gap: 4px; backdrop-filter: blur(8px);">
          <button type="button" class="btn tingkat-tab-btn {{ $tingkat === null || $tingkat === '' ? 'active' : '' }}" data-tingkat="">
            Semua
          </button>
          <button type="button" class="btn tingkat-tab-btn {{ $tingkat === 'X' ? 'active' : '' }}" data-tingkat="X">
            Tingkat X
          </button>
          <button type="button" class="btn tingkat-tab-btn {{ $tingkat === 'XI' ? 'active' : '' }}" data-tingkat="XI">
            Tingkat XI
          </button>
          <button type="button" class="btn tingkat-tab-btn {{ $tingkat === 'XII' ? 'active' : '' }}" data-tingkat="XII">
            Tingkat XII
          </button>
        </div>
      </div>

      <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-3 flex-grow-1 flex-md-grow-0 w-100 w-md-auto">
        <div class="position-relative flex-grow-1 flex-md-grow-0" style="max-width:300px;">
          <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size:0.85rem; pointer-events:none;"></i>
          <input type="text" id="searchInput" class="form-control border-0 text-white" placeholder="Cari nama atau jurusan..." style="background: rgba(255,255,255,0.05); height:38px; padding-left:2.2rem; font-size:0.85rem;">
        </div>

        <!-- Filter Dropdown (Mobile fallback) -->
        <select id="tingkatSelect" class="form-select border-0 text-white w-auto d-md-none" style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="" {{ $tingkat === null || $tingkat === '' ? 'selected' : '' }}>Semua Tingkat</option>
          <option value="X" {{ $tingkat == 'X' ? 'selected' : '' }}>Tingkat X</option>
          <option value="XI" {{ $tingkat == 'XI' ? 'selected' : '' }}>Tingkat XI</option>
          <option value="XII" {{ $tingkat == 'XII' ? 'selected' : '' }}>Tingkat XII</option>
        </select>

        <select id="perPageSelect" class="form-select border-0 text-white w-auto" style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
          <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>

        <span class="das-chip --info d-none d-sm-inline-flex" id="totalKelasChip">{{ method_exists($kelas, 'total') ? $kelas->total() : count($kelas) }} Kelas</span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="kelasTableContainer">
        @include('admin.kelas.table')
      </div>
    </div>

  </div>

  {{-- ══════════════════════════════════════════════ --}}
  {{-- MODAL TAMBAH / UBAH KELAS --}}
  {{-- ══════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:540px;">
      <div class="modal-content shadow-lg">

        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header"
              style="background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.35);">
              <i id="modalKelasIcon" class="ti tabler-plus text-info fs-5"></i>
            </div>
            <div>
              <h5 id="modalKelasTitle" class="modal-title mb-0 text-white fw-bold">Tambah Kelas</h5>
              <small id="modalKelasSubtitle" class="text-white-50">Isi form di bawah untuk menambah data kelas.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>

        <form id="formKelas" method="POST">
          @csrf
          <span id="methodSpoofKelas"></span>

          <div class="modal-body">

            @if ($errors->any())
              <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 border-0 mb-3"
                style="border-radius:8px;font-size:0.85rem;">
                <i class="ti tabler-alert-circle fs-5 flex-shrink-0 mt-1"></i>
                <ul class="mb-0 ps-2">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
              </div>
            @endif

            {{-- Nama Kelas --}}
            <div class="mb-3">
              <label class="form-label fw-semibold small" for="modal_nama">
                <i class="ti tabler-door me-1 text-info"></i> Nama Kelas
              </label>
              <input id="modal_nama" name="nama" type="text"
                class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: X IPA 1"
                value="{{ old('nama') }}" required>
              @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Tingkat & Jurusan --}}
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="modal_tingkat">
                  <i class="ti tabler-stairs me-1 text-info"></i> Tingkat
                </label>
                <select id="modal_tingkat" name="tingkat" class="form-select @error('tingkat') is-invalid @enderror"
                  required>
                  <option value="">Pilih</option>
                  <option value="X" {{ old('tingkat') === 'X' ? 'selected' : '' }}>X</option>
                  <option value="XI" {{ old('tingkat') === 'XI' ? 'selected' : '' }}>XI</option>
                  <option value="XII" {{ old('tingkat') === 'XII' ? 'selected' : '' }}>XII</option>
                </select>
                @error('tingkat')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-8">
                <label class="form-label fw-semibold small" for="modal_jurusan">
                  <i class="ti tabler-books me-1 text-info"></i> Jurusan
                </label>
                <input id="modal_jurusan" name="jurusan" type="text"
                  class="form-control @error('jurusan') is-invalid @enderror" placeholder="Contoh: IPA"
                  value="{{ old('jurusan') }}" required>
                @error('jurusan')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            {{-- Tahun Akademik --}}
            <div class="mb-3">
              <label class="form-label fw-semibold small" for="modal_tahun">
                <i class="ti tabler-calendar-stats me-1 text-info"></i> Tahun Akademik
              </label>
              <select id="modal_tahun" name="tahun_akademik_id"
                class="form-select @error('tahun_akademik_id') is-invalid @enderror" required>
                <option value="">Pilih tahun akademik</option>
                @foreach ($tahunAkademikOptions as $tahun)
                  <option value="{{ $tahun->id }}" {{ old('tahun_akademik_id', session('tahun_ajaran_id', session('tahun_akademik_id'))) == $tahun->id ? 'selected' : '' }}>
                    {{ $tahun->nama }} — {{ ucfirst($tahun->semester) }}
                    @if ($tahun->is_aktif)
                      ✓ Aktif
                    @endif
                  </option>
                @endforeach
              </select>
              @error('tahun_akademik_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

             {{-- Wali Kelas --}}
            <div class="mb-3">
              <label class="form-label fw-semibold small" for="modal_wali">
                <i class="ti tabler-user-check me-1 text-info"></i> Wali Kelas
                <span class="text-white-50 fw-normal">(opsional)</span>
              </label>
              <select id="modal_wali" name="wali_kelas_id"
                class="form-select @error('wali_kelas_id') is-invalid @enderror">
                <option value="">— Tidak ada wali kelas —</option>
                @foreach ($guruOptions as $guru)
                  <option value="{{ $guru->id }}" {{ old('wali_kelas_id') == $guru->id ? 'selected' : '' }}>
                    {{ $guru->nama_lengkap }}{{ $guru->nip ? ' (' . $guru->nip . ')' : '' }}
                  </option>
                @endforeach
              </select>
              @error('wali_kelas_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <hr class="my-3 border-secondary opacity-25">
            <h6 class="mb-3 text-info fw-semibold"><i class="ti tabler-settings-automation me-2"></i>Pengaturan Absensi</h6>

            <div class="mb-3">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="modal_is_aktif_absensi" name="is_aktif_absensi" value="1">
                <label class="form-check-label fw-semibold small" for="modal_is_aktif_absensi">Sistem Absensi Aktif</label>
              </div>
              <small class="text-white-50 d-block mt-1">Jika dimatikan, siswa di kelas ini tidak akan ditandai Alpha.</small>
            </div>

            <div class="mb-3">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="modal_kustomisasi_jam" name="kustomisasi_jam" value="1" onchange="toggleModalJamKhusus()">
                <label class="form-check-label fw-semibold small" for="modal_kustomisasi_jam">Gunakan Jam Masuk/Pulang Khusus</label>
              </div>
              <small class="text-white-50 d-block mt-1 mb-3">Jika dimatikan, kelas ini akan mengikuti jam masuk global.</small>

              <div class="row g-3" id="modal_jam_khusus_container" style="display:none;">
                <div class="col-md-6">
                  <label class="form-label small" for="modal_jam_masuk">Jam Masuk</label>
                  <input type="time" class="form-control bg-dark border-secondary text-white" id="modal_jam_masuk" name="jam_masuk">
                </div>
                <div class="col-md-6">
                  <label class="form-label small" for="modal_jam_pulang">Jam Pulang</label>
                  <input type="time" class="form-control bg-dark border-secondary text-white" id="modal_jam_pulang" name="jam_pulang">
                </div>
              </div>
            </div>

          </div>

          <div class="modal-footer gap-2">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              <i class="ti tabler-x me-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
              <i id="kelasSubmitIcon" class="ti tabler-device-floppy me-1"></i>
              <span id="kelasSubmitText">Simpan</span>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════ --}}
  {{-- MODAL HAPUS KELAS --}}
  {{-- ══════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalHapusKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
      <div class="modal-content shadow-lg">
        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div
              style="width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(234,84,85,0.2);border:1px solid rgba(234,84,85,0.35);">
              <i class="ti tabler-alert-triangle text-danger fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Konfirmasi Hapus</h5>
              <small class="text-white-50">Tindakan ini tidak dapat dibatalkan.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center py-4">
          <p class="mb-1 text-white-50">Yakin ingin menghapus kelas:</p>
          <p class="fw-bold text-info fs-6 mb-0" id="hapusKelasNama">—</p>
        </div>
        <div class="modal-footer gap-2 justify-content-center">
          <button type="button" class="btn btn-label-secondary px-4" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Batal
          </button>
          <form id="formHapusKelas" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger fw-semibold px-4 shadow-sm">
              <i class="ti tabler-trash me-1"></i> Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalDetailWaliKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
      <div class="modal-content shadow-lg">
        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header" style="background:rgba(56,148,255,0.15);border:1px solid rgba(56,148,255,0.25);">
              <i class="ti tabler-user-check text-info fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Detail Wali Kelas</h5>
              <small class="text-white-50">Informasi wali kelas untuk kelas yang dipilih.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <p class="small text-white-50 mb-1">Kelas</p>
            <h6 id="detailKelasNama" class="fw-semibold text-white"></h6>
          </div>
          <div class="mb-3">
            <p class="small text-white-50 mb-1">Nama Wali Kelas</p>
            <h6 id="detailWaliNama" class="fw-semibold text-white"></h6>
          </div>
          <div class="mb-0">
            <p class="small text-white-50 mb-1">NIP</p>
            <p id="detailWaliNip" class="mb-0 text-white-75"></p>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-label-secondary px-4" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════ --}}
  {{-- MODAL IMPORT KELAS --}}
  {{-- ══════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalImportKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
      <div class="modal-content shadow-lg" style="background:#1e1e2d; border:1px solid rgba(255,255,255,0.1); border-radius:12px; overflow:hidden;">
        <div class="modal-header" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header" style="background:rgba(0,207,232,0.2); border:1px solid rgba(0,207,232,0.35);">
              <i class="ti tabler-file-import text-info fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Import Data Kelas</h5>
              <small class="text-white-50">Upload file Excel atau CSV untuk import data.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.kelas.import.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="modal-body p-4">
            <div class="mb-4">
              <label class="form-label text-white-50" for="import_file">Pilih File Excel / CSV</label>
              <input id="import_file" name="import_file" type="file" class="form-control bg-dark border-secondary text-white" accept=".xlsx,.xls,.csv" required>
            </div>
            
            <div class="alert alert-info border-0 shadow-sm mb-0" style="background: rgba(0, 207, 232, 0.1); border-radius: 8px;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="mb-0 fw-bold text-info small"><i class="ti tabler-info-circle me-1"></i>Format Kolom:</p>
                <a href="{{ route('admin.kelas.download-sample') }}" class="btn btn-sm btn-label-info py-0 px-2" style="font-size: 0.65rem;">
                   <i class="ti tabler-download me-1"></i> Download Sampel
                </a>
              </div>
              <div class="d-flex flex-wrap gap-2">
                @foreach (['nama', 'tingkat', 'jurusan', 'wali_kelas', 'tahun_akademik'] as $col)
                  <span class="badge bg-label-info" style="font-size: 0.65rem;">{{ $col }}</span>
                @endforeach
              </div>
            </div>
          </div>
          <div class="modal-footer gap-2 px-4 pb-4 border-0">
            <button type="button" class="btn btn-label-secondary w-100" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-info w-100 fw-semibold">
              <i class="ti tabler-upload me-1"></i> Mulai Import
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════ --}}
  {{-- MODAL COPY KELAS DARI TA SEBELUMNYA --}}
  {{-- ══════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalCopyKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width:720px;">
      <div class="modal-content shadow-lg" style="border:1px solid rgba(255,255,255,0.1); background:#1e1e2d; border-radius:12px; overflow:hidden;">
        <div class="modal-header" style="background:linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); border-bottom:1px solid rgba(255,255,255,0.08);">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header" style="background:rgba(0,207,232,0.2); border:1px solid rgba(0,207,232,0.35);">
              <i class="ti tabler-copy text-info fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Copy Kelas dari Tahun Ajaran Lain</h5>
              <small class="text-white-50">Salin kelas dari tahun ajaran sumber ke tahun ajaran tujuan.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          {{-- TA Selection --}}
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold small" for="copySourceTa">
                <i class="ti tabler-source-code me-1 text-info"></i> TA Sumber
              </label>
              <select id="copySourceTa" class="form-select" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#cdd2e0; border-radius:8px;">
                <option value="">Pilih TA Sumber</option>
                @foreach ($tahunAkademikList as $ta)
                  <option value="{{ $ta->id }}">{{ $ta->nama }} — {{ ucfirst($ta->semester) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small" for="copyTargetTa">
                <i class="ti tabler-target me-1 text-info"></i> TA Tujuan
              </label>
              <select id="copyTargetTa" class="form-select" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#cdd2e0; border-radius:8px;">
                <option value="">Pilih TA Tujuan</option>
                @foreach ($tahunAkademikList as $ta)
                  <option value="{{ $ta->id }}">{{ $ta->nama }} — {{ ucfirst($ta->semester) }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="d-flex gap-2 mb-4">
            <button type="button" id="btnPreviewCopy" class="btn btn-info fw-semibold px-4 shadow-sm" onclick="previewCopyKelas()">
              <i id="previewCopyIcon" class="ti tabler-eye me-1"></i>
              <span id="previewCopyText">Preview</span>
            </button>
          </div>

          {{-- Preview Result --}}
          <div id="copyPreviewResult" style="display:none;">
            <hr class="border-secondary opacity-25 my-3">
            <div id="copySummary" class="alert d-flex align-items-center gap-2 mb-3 border-0" style="border-radius:8px; background:rgba(0,207,232,0.1);">
              <i class="ti tabler-info-circle text-info fs-5"></i>
              <span id="copySummaryText" class="text-white-50 small"></span>
            </div>

            {{-- Tabel Kelas Baru --}}
            <h6 class="text-info fw-semibold mb-2"><i class="ti tabler-plus-circle me-1"></i>Kelas yang Akan Dibuat</h6>
            <div class="table-responsive mb-3" style="max-height:200px; overflow-y:auto; border:1px solid rgba(255,255,255,0.08); border-radius:8px;">
              <table class="table table-dark table-sm mb-0" style="font-size:0.8rem;">
                <thead>
                  <tr>
                    <th>Nama Kelas</th>
                    <th>Tingkat</th>
                    <th>Jurusan</th>
                  </tr>
                </thead>
                <tbody id="copyBaruBody"></tbody>
              </table>
            </div>

            {{-- Tabel Kelas Skip --}}
            <h6 class="text-warning fw-semibold mb-2"><i class="ti tabler-alert-triangle me-1"></i>Kelas yang Di-skip (Sudah Ada)</h6>
            <div class="table-responsive mb-3" style="max-height:200px; overflow-y:auto; border:1px solid rgba(255,255,255,0.08); border-radius:8px;">
              <table class="table table-dark table-sm mb-0" style="font-size:0.8rem;">
                <thead>
                  <tr>
                    <th>Nama Kelas</th>
                    <th>Tingkat</th>
                    <th>Jurusan</th>
                  </tr>
                </thead>
                <tbody id="copySkipBody"></tbody>
              </table>
            </div>

            <div class="d-flex gap-2 mt-3">
              <button type="button" id="btnExecuteCopy" class="btn btn-success fw-semibold px-4 shadow-sm" onclick="executeCopyKelas()">
                <i id="executeCopyIcon" class="ti tabler-copy me-1"></i>
                <span id="executeCopyText">Copy Sekarang</span>
              </button>
            </div>
          </div>

          <div id="copyErrorContainer" style="display:none;">
            <div class="alert alert-danger d-flex align-items-center gap-2 border-0" style="border-radius:8px;">
              <i class="ti tabler-alert-circle text-danger fs-5"></i>
              <span id="copyErrorText" class="small"></span>
            </div>
          </div>
        </div>
        <div class="modal-footer gap-2" style="border-top:1px solid rgba(255,255,255,0.08); padding:1rem 1.5rem; background:rgba(255,255,255,0.02);">
          <button type="button" class="btn btn-label-secondary px-4" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    // ─── Search & Pagination AJAX ───────────────────────────────────────────
    (function() {
      const container = document.getElementById('kelasTableContainer');
      const searchInput = document.getElementById('searchInput');
      const tingkatSelect = document.getElementById('tingkatSelect');
      const perPageSelect = document.getElementById('perPageSelect');
      const tabButtons = document.querySelectorAll('.tingkat-tab-btn');
      let selectedTingkat = '{{ $tingkat ?? "" }}';
      let searchTimeout;

      function fetchData(page = 1) {
        const search = encodeURIComponent(searchInput.value || '');
        const perPage = perPageSelect.value || 10;
        const tingkat = encodeURIComponent(selectedTingkat);
        const url = `{{ route('admin.kelas.index') }}?page=${page}&search=${search}&per_page=${perPage}&tingkat=${tingkat}`;

        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(res => res.text())
          .then(html => {
            container.innerHTML = html;
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';

            // Sync total count badge dynamically
            const tableWrapper = container.querySelector('.table-responsive');
            if (tableWrapper && tableWrapper.dataset.total !== undefined) {
              const totalChip = document.getElementById('totalKelasChip');
              if (totalChip) {
                totalChip.textContent = `${tableWrapper.dataset.total} Kelas`;
              }
            }
          })
          .catch(err => {
            console.error('Fetch error:', err);
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
          });
      }

      // Sync active state of UI components (tabs vs mobile select)
      function syncTingkatUI(val) {
        selectedTingkat = val;
        tingkatSelect.value = val;
        
        tabButtons.forEach(btn => {
          if ((btn.dataset.tingkat || '') === val) {
            btn.classList.add('active');
          } else {
            btn.classList.remove('active');
          }
        });
      }

      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchData(1), 450);
      });

      // Desktop/Tablet Tab Buttons Click handler
      tabButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const targetVal = this.dataset.tingkat || '';
          syncTingkatUI(targetVal);
          fetchData(1);
        });
      });

      // Mobile Select Dropdown Change handler
      tingkatSelect.addEventListener('change', function() {
        syncTingkatUI(this.value);
        fetchData(1);
      });

      perPageSelect.addEventListener('change', function() {
        fetchData(1);
      });

      container.addEventListener('click', function(e) {
        const link = e.target.closest('a.das-page-btn');
        if (link) {
          e.preventDefault();
          const page = link.dataset.page || new URL(link.href).searchParams.get('page') || 1;
          fetchData(page);
        }
      });
    })();
    // ─── End Search & Pagination ─────────────────────────────────────────────

    const kelasStoreUrl = "{{ route('admin.kelas.store') }}";
    const kelasUpdateBase = "{{ url('admin/kelas') }}";

    // Init Select2 saat modal ditampilkan (bukan DOMContentLoaded)
    document.getElementById('modalKelas').addEventListener('shown.bs.modal', function() {
      if (!$('#modal_wali').data('select2')) {
        $('#modal_wali').select2({
          dropdownParent: $('#modalKelas'),
          placeholder: '\u2014 Tidak ada wali kelas \u2014',
          allowClear: true,
          width: '100%'
        });
      }
    });

    document.getElementById('modalKelas').addEventListener('hidden.bs.modal', function() {
      if ($('#modal_wali').data('select2')) {
        $('#modal_wali').select2('destroy');
      }
    });

    function toggleModalJamKhusus() {
      const isChecked = document.getElementById('modal_kustomisasi_jam').checked;
      const container = document.getElementById('modal_jam_khusus_container');
      if (isChecked) {
        container.style.display = 'flex';
      } else {
        container.style.display = 'none';
        document.getElementById('modal_jam_masuk').value = '';
        document.getElementById('modal_jam_pulang').value = '';
      }
    }

    function openTambahKelas() {
      const form = document.getElementById('formKelas');
      form.action = kelasStoreUrl;
      document.getElementById('methodSpoofKelas').innerHTML = '';

      document.getElementById('modal_nama').value = '';
      document.getElementById('modal_tingkat').value = '';
      document.getElementById('modal_jurusan').value = '';
      document.getElementById('modal_tahun').value = '';
      document.getElementById('modal_wali').value = '';
      document.getElementById('modal_is_aktif_absensi').checked = true;
      document.getElementById('modal_kustomisasi_jam').checked = false;
      document.getElementById('modal_jam_masuk').value = '';
      document.getElementById('modal_jam_pulang').value = '';
      document.getElementById('modal_jam_khusus_container').style.display = 'none';

      document.getElementById('modalKelasTitle').textContent = 'Tambah Kelas';
      document.getElementById('modalKelasSubtitle').textContent = 'Isi form di bawah untuk menambah data kelas.';
      document.getElementById('modalKelasIcon').className = 'ti tabler-plus text-info fs-5';
      document.getElementById('kelasSubmitText').textContent = 'Simpan';
      document.getElementById('kelasSubmitIcon').className = 'ti tabler-device-floppy me-1';

      new bootstrap.Modal(document.getElementById('modalKelas')).show();
    }

    function openEditKelas(data) {
      const form = document.getElementById('formKelas');
      form.action = kelasUpdateBase + '/' + data.id;
      document.getElementById('methodSpoofKelas').innerHTML = '<input type="hidden" name="_method" value="PUT">';

      document.getElementById('modal_nama').value = data.nama;
      document.getElementById('modal_tingkat').value = data.tingkat;
      document.getElementById('modal_jurusan').value = data.jurusan;
      document.getElementById('modal_tahun').value = data.tahun_akademik_id ?? '';
      document.getElementById('modal_wali').value = data.wali_kelas_id ?? '';
      document.getElementById('modal_is_aktif_absensi').checked = data.is_aktif_absensi ?? true;
      document.getElementById('modal_kustomisasi_jam').checked = data.kustomisasi_jam ?? false;
      document.getElementById('modal_jam_masuk').value = data.jam_masuk || '';
      document.getElementById('modal_jam_pulang').value = data.jam_pulang || '';
      document.getElementById('modal_jam_khusus_container').style.display = data.kustomisasi_jam ? '' : 'none';

      document.getElementById('modalKelasTitle').textContent = 'Ubah Kelas';
      document.getElementById('modalKelasSubtitle').textContent = 'Perbarui data kelas yang dipilih.';
      document.getElementById('modalKelasIcon').className = 'ti tabler-pencil text-info fs-5';
      document.getElementById('kelasSubmitText').textContent = 'Perbarui';
      document.getElementById('kelasSubmitIcon').className = 'ti tabler-refresh me-1';

      new bootstrap.Modal(document.getElementById('modalKelas')).show();
    }

    function openHapusKelas(id, nama) {
      document.getElementById('hapusKelasNama').textContent = nama;
      document.getElementById('formHapusKelas').action = kelasUpdateBase + '/' + id;
      new bootstrap.Modal(document.getElementById('modalHapusKelas')).show();
    }

    function openDetailWaliKelas(data) {
      if (typeof data === 'string') {
        data = JSON.parse(data);
      }
      document.getElementById('detailKelasNama').textContent = data.kelas;
      document.getElementById('detailWaliNama').textContent = data.nama;
      document.getElementById('detailWaliNip').textContent = data.nip;
      new bootstrap.Modal(document.getElementById('modalDetailWaliKelas')).show();
    }

    @if ($errors->any())
      document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('modalKelas')).show();
      });
    @endif

    // ─── Copy Kelas dari TA Sebelumnya ─────────────────────────────────────
    const previewCopyUrl = "{{ route('admin.kelas.preview-copy') }}";
    const executeCopyUrl = "{{ route('admin.kelas.execute-copy') }}";

    function previewCopyKelas() {
      const sourceId = document.getElementById('copySourceTa').value;
      const targetId = document.getElementById('copyTargetTa').value;

      if (!sourceId || !targetId) {
        showCopyError('Pilih TA Sumber dan TA Tujuan terlebih dahulu.');
        return;
      }
      if (sourceId === targetId) {
        showCopyError('TA Sumber dan TA Tujuan tidak boleh sama.');
        return;
      }

      const btn = document.getElementById('btnPreviewCopy');
      const icon = document.getElementById('previewCopyIcon');
      const text = document.getElementById('previewCopyText');
      btn.disabled = true;
      icon.className = 'ti tabler-loader ti-spin me-1';
      text.textContent = 'Memproses...';

      document.getElementById('copyPreviewResult').style.display = 'none';
      document.getElementById('copyErrorContainer').style.display = 'none';

      fetch(previewCopyUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ source_ta_id: sourceId, target_ta_id: targetId })
      })
      .then(res => res.json())
      .then(data => {
        btn.disabled = false;
        icon.className = 'ti tabler-eye me-1';
        text.textContent = 'Preview';

        if (!data.success) {
          showCopyError(data.message || 'Gagal memuat preview.');
          return;
        }

        const d = data.data;
        document.getElementById('copySummaryText').textContent =
          `${d.total_baru} kelas akan dibuat, ${d.total_skip} kelas di-skip (dari total ${d.total_sumber} kelas di ${d.ta_sumber}).`;

        // Kelas baru
        const baruBody = document.getElementById('copyBaruBody');
        baruBody.innerHTML = '';
        if (d.kelas_baru.length === 0) {
          baruBody.innerHTML = '<tr><td colspan="3" class="text-center text-white-50 py-3">Tidak ada kelas baru.</td></tr>';
        } else {
          d.kelas_baru.forEach(k => {
            baruBody.innerHTML += `<tr><td>${k.nama}</td><td>${k.tingkat}</td><td>${k.jurusan}</td></tr>`;
          });
        }

        // Kelas skip
        const skipBody = document.getElementById('copySkipBody');
        skipBody.innerHTML = '';
        if (d.kelas_skip.length === 0) {
          skipBody.innerHTML = '<tr><td colspan="3" class="text-center text-white-50 py-3">Tidak ada kelas yang di-skip.</td></tr>';
        } else {
          d.kelas_skip.forEach(k => {
            skipBody.innerHTML += `<tr><td>${k.nama}</td><td>${k.tingkat}</td><td>${k.jurusan}</td></tr>`;
          });
        }

        // Tampilkan/sembunyikan tombol execute
        document.getElementById('btnExecuteCopy').style.display = d.total_baru > 0 ? '' : 'none';
        document.getElementById('copyPreviewResult').style.display = 'block';
      })
      .catch(err => {
        btn.disabled = false;
        icon.className = 'ti tabler-eye me-1';
        text.textContent = 'Preview';
        showCopyError('Terjadi kesalahan. Silakan coba lagi.');
        console.error('Preview copy error:', err);
      });
    }

    function executeCopyKelas() {
      const sourceId = document.getElementById('copySourceTa').value;
      const targetId = document.getElementById('copyTargetTa').value;

      if (!confirm('Yakin akan menyalin kelas sekarang?')) return;

      const btn = document.getElementById('btnExecuteCopy');
      const icon = document.getElementById('executeCopyIcon');
      const text = document.getElementById('executeCopyText');
      btn.disabled = true;
      icon.className = 'ti tabler-loader ti-spin me-1';
      text.textContent = 'Menyalin...';

      fetch(executeCopyUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ source_ta_id: sourceId, target_ta_id: targetId })
      })
      .then(res => res.json())
      .then(data => {
        btn.disabled = false;
        icon.className = 'ti tabler-copy me-1';
        text.textContent = 'Copy Sekarang';

        if (!data.success) {
          showCopyError(data.message || 'Gagal menyalin kelas.');
          return;
        }

        alert(data.message);
        location.reload();
      })
      .catch(err => {
        btn.disabled = false;
        icon.className = 'ti tabler-copy me-1';
        text.textContent = 'Copy Sekarang';
        showCopyError('Terjadi kesalahan. Silakan coba lagi.');
        console.error('Execute copy error:', err);
      });
    }

    function showCopyError(msg) {
      const container = document.getElementById('copyErrorContainer');
      document.getElementById('copyErrorText').textContent = msg;
      container.style.display = 'block';
    }

    // Reset modal saat ditutup
    document.getElementById('modalCopyKelas').addEventListener('hidden.bs.modal', function() {
      document.getElementById('copyPreviewResult').style.display = 'none';
      document.getElementById('copyErrorContainer').style.display = 'none';
      document.getElementById('btnPreviewCopy').disabled = false;
      document.getElementById('btnExecuteCopy').disabled = false;
      document.getElementById('previewCopyIcon').className = 'ti tabler-eye me-1';
      document.getElementById('previewCopyText').textContent = 'Preview';
      document.getElementById('executeCopyIcon').className = 'ti tabler-copy me-1';
      document.getElementById('executeCopyText').textContent = 'Copy Sekarang';
    });
    // ─── End Copy Kelas ─────────────────────────────────────────────────────
  </script>
@endsection
