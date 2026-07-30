@extends('layouts/layoutMaster')

@section('title', 'Siswa')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/animate-css/animate.scss',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
    ])
@endsection

@section('page-style')
    <style>
        .siswa-row-hover {
            transition: background 0.15s ease;
        }

        .siswa-row-hover:hover {
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
        #searchInput::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        #searchInput:focus {
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

        input[type="radio"]:checked+.hover-bg-primary-light {
            background: rgba(115, 103, 240, 0.15) !important;
            border-color: #7367f0 !important;
        }

        input[type=\"radio\"]:checked+.hover-bg-primary-light .radio-indicator i {
            color: #7367f0 !important;
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

        .text-purple {
            color: #a5a2f7 !important;
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
                        <i class="ti tabler-users text-info"></i>
                    </div>
                    <div class="das-hero__logo-glow"></div>
                </div>

                <div class="das-hero__meta">
                    <div class="das-hero__badge">
                        <span class="pulse-dot"></span>
                        <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> /
                        Siswa
                    </div>
                    <h4 class="das-hero__title text-gradient-gold">Data Siswa</h4>
                    <p class="das-hero__subtitle">Kelola seluruh data peserta didik pada <span
                            class="text-info fw-bold">{{ session('tahun_ajaran_id') ? $tahunAjaranOptions->firstWhere('id', session('tahun_ajaran_id'))->nama ?? 'Tahun Ajaran' : 'Pilih Tahun Ajaran' }}</span>.
                    </p>
                </div>
            </div>

            <div class="das-hero__actions">
                @if(!$isWaliKelas)
                <button type="button" class="btn das-btn --purple" id="generateOrtuBtn" data-bs-toggle="tooltip" title="Generate Akun Ortu">
                    <i class="ti tabler-key"></i>
                </button>
                <button type="button" class="btn das-btn --warning" id="resetAllPasswordBtn" data-bs-toggle="tooltip" title="Reset All Password Siswa">
                    <i class="ti tabler-lock-open"></i>
                </button>
                <button type="button" class="btn das-btn --warning" id="syncGoogleSheetBtn" data-bs-toggle="tooltip" title="Sinkronisasi Google Sheet">
                    <i class="ti tabler-refresh"></i>
                </button>
                <button type="button" class="btn das-btn --secondary" data-bs-toggle="modal" data-bs-target="#importModal" data-bs-toggle="tooltip" title="Import Data">
                    <i class="ti tabler-file-import"></i>
                </button>
                <div class="btn-group">
                    <button type="button" class="btn das-btn --success dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false" title="Export Data">
                        <i class="ti tabler-download"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end das-modal border-0 shadow-lg" style="min-width: 200px;">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-white-50 hover-bg-primary-light"
                                href="javascript:void(0)" id="exportExcelBtn">
                                <i class="ti tabler-file-spreadsheet text-success fs-4"></i>
                                <span>Export Excel (.xlsx)</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-white-50 hover-bg-primary-light"
                                href="javascript:void(0)" id="exportCsvBtn">
                                <i class="ti tabler-file-type-csv text-info fs-4"></i>
                                <span>Export CSV (.csv)</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn das-btn --danger" data-bs-toggle="modal" data-bs-target="#deleteAllModal" title="Hapus Semua Siswa">
                    <i class="ti tabler-trash"></i>
                </button>
                @endif

                <div class="btn-group">
                    <button type="button" class="btn das-btn --info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Cek Status WA">
                        <i class="ti tabler-brand-whatsapp me-1" id="iconManualCheckWa"></i> Cek WA
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end das-modal border-0 shadow-lg" style="min-width: 250px;">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-white-50 hover-bg-primary-light" href="javascript:void(0)" id="btnCheckWaCurrentPage">
                                <i class="ti tabler-file-search text-info fs-4"></i>
                                <div>
                                    <div class="fw-bold text-white small">Cek Halaman Ini</div>
                                    <div class="extra-small text-white-50">Validasi nomor yang tampil di tabel</div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1 border-secondary opacity-25"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-white-50 hover-bg-primary-light" href="javascript:void(0)" id="btnCheckWaAllDatabase">
                                <i class="ti tabler-rocket text-success fs-4"></i>
                                <div>
                                    <div class="fw-bold text-white small">Cek Massal Semua Siswa</div>
                                    <div class="extra-small text-white-50">Validasi 1000+ data via progress</div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>

                <button type="button" class="btn das-btn --success" id="btnRegeneratePhoneSiswa" title="Generate Format WA" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ti tabler-phone"></i>
                </button>

                <button type="button" class="btn das-btn --purple" id="generateAllBarcodeBtn" title="Generate Barcode">
                    <i class="ti tabler-barcode"></i>
                </button>
                
                <button type="button" class="btn das-btn --info" data-bs-toggle="modal" data-bs-target="#downloadBarcodeModal" title="Unduh Barcode">
                    <i class="ti tabler-download"></i>
                </button>

                @if(!$isWaliKelas)
                <a href="{{ route('admin.siswa.create') }}" class="btn das-btn --primary" title="Tambah Siswa Baru">
                    <i class="ti tabler-plus"></i>
                </a>
                @endif
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

    @if (session('sync_error'))
        <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
            role="alert" style="border-radius:8px;">
            <i class="ti tabler-alert-triangle fs-5"></i>
            <span>{{ session('sync_error') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Notifikasi data siswa tanpa tahun akademik --}}
    @if (!empty($siswaNullTahun) && $siswaNullTahun > 0)
        <div class="alert alert-warning alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
            role="alert"
            style="border-radius:8px; background: rgba(255,159,67,0.12); border-left: 3px solid #ff9f43 !important;">
            <i class="ti tabler-alert-circle fs-5 text-warning flex-shrink-0"></i>
            <span>
                <strong>{{ $siswaNullTahun }} siswa</strong> tidak memiliki Tahun Akademik — data ini tetap ditampilkan
                namun perlu diperbaiki.
                Pastikan konfigurasi <strong>Google Sheet</strong> atau <strong>Import</strong> menyertakan kolom
                <code>tahun_ajaran</code>.
            </span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER PANEL --}}
    <div class="das-panel mb-4">
        <div class="das-panel__body">
            <form id="filterForm" method="GET" class="row gy-3 gx-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-white-50 small fw-bold">Cari Siswa</label>
                    <input type="text" id="filterSearch" name="search" class="form-control"
                        placeholder="Nama, NIS, atau NISN…" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white-50 small fw-bold">Filter Kelas</label>
                    <select id="filterKelas" name="kelas_id" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelasOptions as $k)
                            <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white-50 small fw-bold">Status</label>
                    <select id="filterStatus" name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                        <option value="alumni" @selected(request('status') === 'alumni')>Alumni</option>
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
    <div class="das-panel">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
            style="border-color:rgba(255,255,255,0.08) !important;">
            <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
                <i class="ti tabler-list text-info"></i> Daftar Siswa
            </h6>
            <div class="d-flex align-items-center gap-3">
                <select id="perPageSelect" class="form-select border-0 text-white w-auto"
                    style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>

                <span
                    class="das-chip --info d-none d-sm-inline-flex">{{ method_exists($siswa, 'total') ? $siswa->total() : count($siswa) }}
                    Siswa</span>
            </div>
        </div>
        <div class="das-panel__body p-0">
            <div id="siswaTableContainer">
                @include('admin.siswa.table')
            </div>
        </div>
    </div>

    <!-- Modal Gateway Tahun Ajaran -->
    <div class="modal fade" id="gatewayModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal shadow-lg border-primary">
                <div class="das-modal-head text-center">
                    <div class="avatar avatar-lg mx-auto mb-3" style="width: 64px; height: 64px;">
                        <span class="avatar-initial rounded-circle bg-label-primary shadow-sm">
                            <i class="ti tabler-calendar-stats fs-1"></i>
                        </span>
                    </div>
                    <h5 class="das-modal-title fs-4">Pilih Tahun Ajaran</h5>
                    <p class="text-white-50 small mb-0">Silakan pilih Tahun Ajaran aktif untuk melihat data siswa.</p>
                </div>
                <form action="{{ route('admin.set-tahun-akademik') }}" method="POST">
                    @csrf
                    <div class="das-modal-body py-4">
                        <div class="row g-3">
                            @forelse($tahunAjaranOptions as $thn)
                                <div class="col-12">
                                    <label class="w-100 cursor-pointer">
                                        <input type="radio" name="tahun_akademik_id" value="{{ $thn->id }}"
                                            class="d-none peer"
                                            {{ session('tahun_ajaran_id') == $thn->id ? 'checked' : '' }} required
                                            onchange="this.form.submit()">
                                        <div class="p-3 rounded-3 border border-2 transition-all d-flex align-items-center justify-content-between hover-bg-primary-light"
                                            style="border-color: rgba(255,255,255,0.08) !important; background: rgba(255,255,255,0.03);">
                                            <div>
                                                <div class="fw-bold text-white">{{ $thn->nama }}</div>
                                                <div class="text-white-50 small">{{ ucfirst($thn->semester) }}</div>
                                            </div>
                                            <div class="radio-indicator">
                                                <i class="ti tabler-circle text-white-50"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12 text-center py-3">
                                    <p class="text-warning mb-0">Belum ada data Tahun Ajaran. <br> <a
                                            href="{{ route('admin.tahun-akademik.index') }}" class="text-info">Tambah
                                            Sekarang</a></p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="p-4 pt-0 text-center">
                        <p class="text-muted extra-small">Gerbang ini memastikan data yang Anda kelola akurat sesuai
                            periode akademik.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete All Students -->
    <div class="modal fade" id="deleteAllModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal das-modal--danger shadow-lg">
                <div class="das-modal__head das-modal__head--danger">
                    <h5 class="das-modal__title"><i class="ti tabler-alert-triangle me-2"></i> Hapus Semua Siswa</h5>
                </div>
                <form id="deleteAllForm" action="{{ route('admin.siswa.destroy-all') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="das-modal__body text-center p-4">
                        <div class="dev-confirm-danger-icon">
                            <div class="dev-confirm-danger-icon__ring"></div>
                            <i class="ti tabler-trash dev-confirm-danger-icon__symbol"></i>
                        </div>
                        <p class="dev-confirm-message__main">Apakah Anda yakin ingin menghapus SELURUH data siswa?</p>
                        <p class="dev-confirm-message__warning text-start">
                            <i class="ti tabler-info-circle"></i>
                            <span>Semua data siswa akan dihapus permanen, termasuk riwayat absensi harian, absensi kegiatan, dan pengajuan izin/sakit.</span>
                        </p>
                        <div id="deleteAllProgress" class="d-none mt-3">
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" style="width:100%">
                                </div>
                            </div>
                            <small class="text-white-50 mt-2 d-block">Menghapus data...</small>
                        </div>
                    </div>
                    <div class="das-modal__foot d-flex justify-content-end gap-2">
                        <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal"
                            id="deleteAllCancelBtn"><i class="ti tabler-x"></i> Tidak, Batal</button>
                        <button type="button" class="das-btn das-btn--danger-solid" id="deleteAllSubmitBtn"><i class="ti tabler-trash"></i> Ya, Hapus Semua</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-head d-flex align-items-center justify-content-between">
                    <h5 class="das-modal-title"><i class="ti tabler-file-import me-2 text-info"></i>Import Data Siswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="importForm" action="{{ route('admin.siswa.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="importFormBody" class="das-modal-body">
                        <div class="mb-4">
                            <label class="form-label text-white-50" for="import_file">Pilih File Excel (.xlsx)</label>
                            <input id="import_file" name="import_file" type="file"
                                class="form-control bg-dark border-secondary text-white" accept=".xlsx"
                                required>
                            <div class="form-text text-white-50 small mt-2">Gunakan format file Excel (.xlsx) yang sesuai.</div>
                        </div>

                        <div class="alert alert-info border-0 shadow-sm"
                            style="background: rgba(0, 207, 232, 0.1); border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="mb-0 fw-bold text-info small"><i class="ti tabler-info-circle me-1"></i>Format
                                    Kolom:</p>
                                <a href="{{ route('admin.siswa.download-sample') }}"
                                    class="btn btn-sm btn-label-info py-0 px-2" style="font-size: 0.65rem;">
                                    <i class="ti tabler-download me-1"></i> Download Sampel
                                </a>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach (['nis', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'no_hp', 'no_hp_ortu', 'kelas', 'tahun_ajaran', 'status'] as $col)
                                    <span class="badge bg-label-info"
                                        style="font-size: 0.65rem;">{{ $col }}</span>
                                @endforeach
                            </div>
                            <ul class="mb-0 ps-3" style="font-size: 0.72rem; color: rgba(255,255,255,0.6);">
                                <li><code>tanggal_lahir</code>: format <strong>dd/mm/yyyy</strong> (cth:
                                    <code>01/06/2010</code>)
                                </li>
                                <li><code>jenis_kelamin</code>: isi <strong>L</strong> atau <strong>P</strong></li>
                                <li><code>tahun_ajaran</code>: Nama + Semester, cth:
                                    @foreach (\App\Models\TahunAkademik::orderBy('tanggal_mulai', 'desc')->take(3)->get() as $ta)
                                        <code>{{ $ta->nama }}
                                            {{ ucfirst($ta->semester) }}</code>{{ !$loop->last ? ',' : '' }}
                                    @endforeach
                                </li>
                                <li><code>status</code>: <strong>aktif</strong>, nonaktif, atau alumni</li>
                                <li>Jika siswa sudah ada (NISN sama), data akan <strong>diperbarui</strong> (tidak
                                    duplikat).</li>
                            </ul>
                        </div>

                        {{-- Progress Bar (hidden by default) --}}
                        <div id="importProgressArea" class="d-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-white-50">Mengimport data...</span>
                                <span class="small text-white-50" id="importProgressText">0%</span>
                            </div>
                            <div class="progress" style="height: 24px; border-radius: 6px; background: rgba(255,255,255,0.05);">
                                <div id="importProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                    role="progressbar" style="width: 0%; background: linear-gradient(135deg, #7367f0, #a55eea);" 
                                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-white-50" id="importProgressDetail">Memproses 0 dari 0 data...</small>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 pb-4 pt-2 d-flex gap-2">
                        <button type="button" id="importCancelBtn" class="btn btn-label-secondary w-100"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="importSubmitBtn" class="btn btn-primary w-100">
                            <i class="ti tabler-upload me-1"></i> Mulai Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    {{-- SYNC GOOGLE SHEET PROGRESS MODAL --}}
    <div class="modal fade" id="syncProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
            <div class="modal-content das-modal border-0 shadow-lg"
                style="background: #1e1e2e; border-radius: 16px; overflow: hidden;">
                <div class="das-modal-head d-flex align-items-center gap-2 px-4 py-3">
                    <i class="ti tabler-refresh text-warning fs-5"></i>
                    <h5 class="das-modal-title mb-0 fs-6 fw-bold">Google Sheet</h5>
                </div>
                <div class="modal-body px-4 pt-3 pb-4 text-center">
                    {{-- Spinner --}}
                    <div class="d-flex justify-content-center mb-3">
                        <div class="sync-spinner-wrapper"
                            style="width:64px;height:64px;position:relative;display:flex;align-items:center;justify-content:center;">
                            <div class="spinner-border text-warning" role="status"
                                style="width:64px;height:64px;border-width:4px;position:absolute;top:0;left:0;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <i class="ti tabler-cloud-upload text-warning" style="font-size:1.4rem;z-index:1;"></i>
                        </div>
                    </div>

                    {{-- Status Message --}}
                    <h6 class="text-white fw-semibold mb-1" id="syncProgressMessage" style="font-size:0.95rem;">Memulai
                        sinkronisasi...</h6>
                    <p class="text-white-50 small mb-3" id="syncProgressCount" style="min-height:1.2em;"></p>

                    {{-- Progress Bar --}}
                    <div class="progress w-100"
                        style="height:10px;background:rgba(255,255,255,0.08);border-radius:50px;overflow:hidden;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                            id="syncProgressBar" role="progressbar"
                            style="width:5%;border-radius:50px;transition:width 0.5s ease;" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <p class="text-white-50 extra-small mt-2 mb-0">Harap tunggu, jangan tutup halaman ini.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Impersonate -->
    <div class="modal fade" id="impersonateConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal">
                <div class="das-modal__head das-modal__head--success">
                    <h5 class="das-modal__title"><i class="ti tabler-user-share me-2"></i> Konfirmasi Impersonate</h5>
                </div>
                <div class="das-modal__body text-center p-4">
                    <div class="dev-confirm-success-icon">
                        <div class="dev-confirm-success-icon__ring"></div>
                        <i class="ti tabler-user-share dev-confirm-success-icon__symbol"></i>
                    </div>
                    <p class="dev-confirm-message__main">Anda akan masuk ke akun <b id="impersonateSiswaName" class="text-success"></b>.</p>
                    <p class="dev-confirm-message__warning text-start">
                        <i class="ti tabler-info-circle"></i>
                        <span>Seluruh tindakan & aktivitas yang dilakukan selama sesi impersonate akan dicatat dalam log sistem.</span>
                    </p>
                </div>
                <div class="das-modal__foot d-flex justify-content-end gap-2">
                    <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal"><i class="ti tabler-x"></i> Batal</button>
                    <button type="button" id="confirmImpersonateBtn" class="das-btn das-btn--success-solid"><i class="ti tabler-check"></i> Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Download Barcode Kelas -->
    <div class="modal fade" id="downloadBarcodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-head d-flex align-items-center justify-content-between">
                    <h5 class="das-modal-title"><i class="ti tabler-barcode me-2 text-info"></i>Unduh Barcode Kelas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.siswa.download-barcode-kelas') }}" method="GET">
                    <div class="das-modal-body">
                        <div class="mb-3">
                            <label class="form-label text-white-50">Pilih Kelas</label>
                            <select name="kelas_id" class="form-select" required>
                                <option value="">— Pilih Kelas —</option>
                                @foreach ($kelasOptions as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="px-4 pb-4 pt-2 d-flex gap-2">
                        <button type="button" class="btn btn-label-secondary w-100" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti tabler-download me-1"></i> Unduh ZIP
                        </button>
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
        document.addEventListener('DOMContentLoaded', function() {
            @if (!session('tahun_ajaran_id'))
                const gatewayModal = new bootstrap.Modal(document.getElementById('gatewayModal'));
                gatewayModal.show();
            @endif

            const container = document.getElementById('siswaTableContainer');
            const perPageSelect = document.getElementById('perPageSelect');
            const filterSearch = document.getElementById('filterSearch');
            const filterKelas = document.getElementById('filterKelas');
            const filterStatus = document.getElementById('filterStatus');
            const filterForm = document.getElementById('filterForm');
            const resetFilterBtn = document.getElementById('resetFilterBtn');
            let searchTimeout;

            let currentSortBy = '{{ $sortBy ?? 'nama_lengkap' }}';
            let currentSortDir = '{{ $sortDir ?? 'asc' }}';

            function fetchData(page = 1) {
                const search = encodeURIComponent(filterSearch.value || '');
                const perPage = perPageSelect.value || 10;
                const kelasId = filterKelas.value || '';
                const status = filterStatus.value || '';
                const url = `{{ route('admin.siswa.index') }}?page=${page}&search=${search}&per_page=${perPage}&sort_by=${currentSortBy}&sort_dir=${currentSortDir}&kelas_id=${kelasId}&status=${status}`;

                container.style.opacity = '0.5';
                container.style.pointerEvents = 'none';

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        container.innerHTML = html;
                        container.style.opacity = '1';
                        container.style.pointerEvents = 'auto';

                        // re-init tooltips
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll(
                            '[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function(tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        container.style.opacity = '1';
                        container.style.pointerEvents = 'auto';
                    });
            }

            // debounce search
            if (filterSearch) {
                filterSearch.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => fetchData(1), 450);
                });
            }

            if (filterKelas) {
                filterKelas.addEventListener('change', function() {
                    fetchData(1);
                });
            }

            if (filterStatus) {
                filterStatus.addEventListener('change', function() {
                    fetchData(1);
                });
            }

            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    fetchData(1);
                });
            }

            if (resetFilterBtn) {
                resetFilterBtn.addEventListener('click', function() {
                    if (filterSearch) filterSearch.value = '';
                    if (filterKelas) filterKelas.value = '';
                    if (filterStatus) filterStatus.value = '';
                    fetchData(1);
                });
            }

            perPageSelect.addEventListener('change', function() {
                fetchData(1);
            });

            // pagination clicks (capture delegated events)
            container.addEventListener('click', function(e) {
                const link = e.target.closest('a.das-page-btn');
                if (link) {
                    e.preventDefault();
                    const page = link.dataset.page || new URL(link.href).searchParams.get('page') || 1;
                    fetchData(page);
                }
            });

            // Select All Checkbox Handler (Delegated, so it works after table is updated)
            container.addEventListener('change', function(e) {
                const selectAllCheckbox = e.target.closest('#selectAllSiswa');
                if (selectAllCheckbox) {
                    const checkboxes = container.querySelectorAll('.siswa-checkbox');
                    checkboxes.forEach(cb => {
                        cb.checked = selectAllCheckbox.checked;
                    });
                    updateResetButtonState();
                }

                const singleCheckbox = e.target.closest('.siswa-checkbox');
                if (singleCheckbox) {
                    const selectAllCheckbox = container.querySelector('#selectAllSiswa');
                    if (selectAllCheckbox) {
                        const total = container.querySelectorAll('.siswa-checkbox').length;
                        const checked = container.querySelectorAll('.siswa-checkbox:checked').length;
                        selectAllCheckbox.checked = (total === checked && total > 0);
                        selectAllCheckbox.indeterminate = (checked > 0 && checked < total);
                    }
                    updateResetButtonState();
                }
            });

            function updateResetButtonState() {
                const resetAllBtn = document.getElementById('resetAllPasswordBtn');
                if (!resetAllBtn) return;
                
                const checkedCount = container.querySelectorAll('.siswa-checkbox:checked').length;
                
                if (checkedCount > 0) {
                    resetAllBtn.classList.remove('--warning');
                    resetAllBtn.classList.add('--primary');
                    resetAllBtn.setAttribute('title', `Reset Password ${checkedCount} Siswa Terpilih`);
                    
                    // Update bootstrap tooltip if available
                    const tooltip = bootstrap.Tooltip.getInstance(resetAllBtn);
                    if (tooltip) {
                        tooltip.setContent({ '.tooltip-inner': `Reset Password ${checkedCount} Siswa Terpilih` });
                    }
                } else {
                    resetAllBtn.classList.remove('--primary');
                    resetAllBtn.classList.add('--warning');
                    resetAllBtn.setAttribute('title', 'Reset All Password Siswa');
                    
                    const tooltip = bootstrap.Tooltip.getInstance(resetAllBtn);
                    if (tooltip) {
                        tooltip.setContent({ '.tooltip-inner': 'Reset All Password Siswa' });
                    }
                }
            }

            // sort clicks (capture delegated events)
            container.addEventListener('click', function(e) {
                const th = e.target.closest('th.sortable');
                if (th) {
                    const sortBy = th.dataset.sortBy;
                    if (currentSortBy === sortBy) {
                        currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSortBy = sortBy;
                        currentSortDir = 'asc';
                    }
                    fetchData(1);
                }
            });

            // Individual delete AJAX handler (delegated, works after fetchData re-render)
            container.addEventListener('click', function(e) {
                const btnImpersonate = e.target.closest('.btn-impersonate-siswa');
                if (btnImpersonate) {
                    const url = btnImpersonate.dataset.url;
                    const nama = btnImpersonate.dataset.nama || 'Siswa';

                    document.getElementById('impersonateSiswaName').textContent = nama;
                    const modalEl = document.getElementById('impersonateConfirmModal');
                    const confirmBtn = document.getElementById('confirmImpersonateBtn');
                    
                    // Reset confirm button state
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Ya, Lanjutkan';

                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();

                    // Cleanup any existing click handler on confirm button to prevent multiple submissions
                    const newConfirmBtn = confirmBtn.cloneNode(true);
                    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

                    newConfirmBtn.addEventListener('click', function() {
                        newConfirmBtn.disabled = true;
                        newConfirmBtn.innerHTML = '<i class="ti tabler-loader spinner me-1"></i> Memproses...';
                        
                        modal.hide();

                        // Create form element dynamically to do a POST request
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = url;

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        form.appendChild(csrfInput);

                        document.body.appendChild(form);
                        form.submit();
                    });
                    return;
                }

                const btn = e.target.closest('.btn-hapus-siswa');
                if (!btn) return;

                const url = btn.dataset.url;
                const nama = btn.dataset.nama || 'siswa ini';

                Swal.fire({
                    title: 'Hapus Siswa?',
                    html: `<div class=\"mt-2\">Data <b class=\"text-danger\">"${nama}"</b> akan dihapus secara permanen beserta data absensinya.</div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus Data',
                    cancelButtonText: 'Batalkan',
                    customClass: {
                        popup: 'das-swal-popup',
                        title: 'das-swal-title',
                        htmlContainer: 'das-swal-html',
                        confirmButton: 'btn btn-danger das-swal-confirm me-2',
                        cancelButton: 'btn das-swal-cancel',
                        icon: 'das-swal-icon'
                    },
                    buttonsStyling: false,
                    showClass: {
                        popup: 'animate__animated animate__fadeInUp animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutDown animate__faster'
                    },
                    background: 'transparent',
                    backdrop: `rgba(0,0,10,0.4)`,
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    btn.disabled = true;

                    fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message || 'Siswa berhasil dihapus.',
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-success das-swal-confirm'
                                    },
                                    timer: 2000,
                                    showConfirmButton: false,
                                    background: 'transparent',
                                });
                                fetchData(1);
                            } else {
                                btn.disabled = false;
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: data.message || 'Terjadi kesalahan.',
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-primary das-swal-confirm'
                                    },
                                    showClass: {
                                        popup: 'animate__animated animate__shakeX animate__faster'
                                    },
                                    hideClass: {
                                        popup: 'animate__animated animate__fadeOut animate__faster'
                                    },
                                    background: 'transparent',
                                    buttonsStyling: false
                                });
                            }
                        })
                        .catch(err => {
                            btn.disabled = false;
                            console.error('Delete siswa error:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan koneksi.',
                                customClass: {
                                    popup: 'das-swal-popup',
                                    title: 'das-swal-title',
                                    htmlContainer: 'das-swal-html',
                                    confirmButton: 'btn btn-primary das-swal-confirm'
                                },
                                showClass: {
                                    popup: 'animate__animated animate__shakeX animate__faster'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__fadeOut animate__faster'
                                },
                                background: 'transparent',
                                buttonsStyling: false
                            });
                        });
                });
            });


            const deleteAllSubmitBtn = document.getElementById('deleteAllSubmitBtn');
            const deleteAllCancelBtn = document.getElementById('deleteAllCancelBtn');
            const deleteAllProgress = document.getElementById('deleteAllProgress');
            const deleteAllForm = document.getElementById('deleteAllForm');

            if (deleteAllSubmitBtn && deleteAllForm) {
                deleteAllSubmitBtn.addEventListener('click', function() {
                    deleteAllSubmitBtn.disabled = true;
                    if (deleteAllCancelBtn) deleteAllCancelBtn.disabled = true;
                    if (deleteAllProgress) deleteAllProgress.classList.remove('d-none');

                    const formData = new FormData(deleteAllForm);

                    fetch(deleteAllForm.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            const modalEl = document.getElementById('deleteAllModal');
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message || 'Data siswa telah dihapus.',
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-success das-swal-confirm'
                                    },
                                    showClass: {
                                        popup: 'animate__animated animate__zoomIn animate__faster'
                                    },
                                    hideClass: {
                                        popup: 'animate__animated animate__zoomOut animate__faster'
                                    },
                                    background: 'transparent',
                                    buttonsStyling: false
                                });
                                fetchData(1);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: data.message || 'Terjadi kesalahan.',
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-primary das-swal-confirm'
                                    },
                                    showClass: {
                                        popup: 'animate__animated animate__shakeX animate__faster'
                                    },
                                    hideClass: {
                                        popup: 'animate__animated animate__fadeOut animate__faster'
                                    },
                                    background: 'transparent',
                                    buttonsStyling: false
                                });
                            }
                        })
                        .catch(err => {
                            console.error('Delete all error:', err);
                            const modalEl = document.getElementById('deleteAllModal');
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan koneksi.',
                                customClass: {
                                    popup: 'das-swal-popup',
                                    title: 'das-swal-title',
                                    htmlContainer: 'das-swal-html',
                                    confirmButton: 'btn btn-primary das-swal-confirm'
                                },
                                showClass: {
                                    popup: 'animate__animated animate__shakeX animate__faster'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__fadeOut animate__faster'
                                },
                                background: 'transparent',
                                buttonsStyling: false
                            });
                        })
                        .finally(() => {
                            deleteAllSubmitBtn.disabled = false;
                            if (deleteAllCancelBtn) deleteAllCancelBtn.disabled = false;
                            if (deleteAllProgress) deleteAllProgress.classList.add('d-none');
                        });
                });
            }

            // Export handlers
            const exportExcelBtn = document.getElementById('exportExcelBtn');
            const exportCsvBtn = document.getElementById('exportCsvBtn');

            function handleExport(format) {
                const search = encodeURIComponent(filterSearch.value || '');
                const url = `{{ route('admin.siswa.export') }}?format=${format}&search=${search}`;
                window.location.href = url;
            }

            if (exportExcelBtn) {
                exportExcelBtn.addEventListener('click', () => handleExport('xlsx'));
            }
            if (exportCsvBtn) {
                exportCsvBtn.addEventListener('click', () => handleExport('csv'));
            }

            // Generate All Barcode
            const generateAllBarcodeBtn = document.getElementById('generateAllBarcodeBtn');
            if (generateAllBarcodeBtn) {
                generateAllBarcodeBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Generate/Verifikasi Barcode?',
                        html: '<div class="text-center px-2">' +
                              '  <p class="text-white-50 mb-3" style="font-size:0.92rem; line-height:1.6;">' +
                              '    Sistem akan memverifikasi seluruh data siswa. Siswa yang belum memiliki NIS valid akan diberikan NIS otomatis berbasis NISN atau format default agar siap discan.' +
                              '  </p>' +
                              '</div>',
                        iconHtml: '<div class="rounded-circle d-flex align-items-center justify-content-center" style="width:70px; height:70px; background: rgba(115, 103, 240, 0.15); border: 2px solid rgba(115, 103, 240, 0.3); box-shadow: 0 0 15px rgba(115, 103, 240, 0.4);"><i class="ti tabler-barcode text-purple fs-1" style="font-size: 2.5rem !important;"></i></div>',
                        showCancelButton: true,
                        confirmButtonText: '<i class="ti tabler-bolt me-1"></i> Mulai Proses',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'das-swal-popup',
                            title: 'das-swal-title',
                            htmlContainer: 'das-swal-html',
                            confirmButton: 'btn das-btn --purple px-4 py-2 me-3',
                            cancelButton: 'btn das-btn das-swal-cancel px-4 py-2',
                            icon: 'border-0'
                        },
                        buttonsStyling: false,
                        showClass: {
                            popup: 'animate__animated animate__fadeInUp animate__faster'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutDown animate__faster'
                        },
                        background: 'transparent',
                        backdrop: `rgba(0,0,10,0.4)`,
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        Swal.fire({
                            title: 'Memproses Barcode...',
                            html: '<div class="text-center px-2">' +
                                  '  <p class="text-white-50 mb-3" style="font-size:0.92rem;">Sedang memverifikasi data dan menyiapkan barcode...</p>' +
                                  '  <div class="d-flex align-items-center justify-content-center gap-2 p-2 rounded" style="background: rgba(115, 103, 240, 0.08); border: 1px dashed rgba(115, 103, 240, 0.2);">' +
                                  '    <i class="ti tabler-loader spinner text-purple fs-4"></i>' +
                                  '    <span class="text-purple extra-small fw-semibold">Mohon tunggu sebentar...</span>' +
                                  '  </div>' +
                                  '</div>',
                            iconHtml: '<div class="rounded-circle d-flex align-items-center justify-content-center" style="width:70px; height:70px; background: rgba(115, 103, 240, 0.15); border: 2px solid rgba(115, 103, 240, 0.3);"><i class="ti tabler-loader spinner text-purple fs-1" style="font-size: 2.5rem !important;"></i></div>',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            customClass: {
                                popup: 'das-swal-popup',
                                title: 'das-swal-title',
                                htmlContainer: 'das-swal-html',
                                icon: 'border-0'
                            },
                            buttonsStyling: false,
                            background: 'transparent',
                            backdrop: `rgba(0,0,10,0.45)`,
                        });

                        fetch('{{ route('admin.siswa.generate-all-barcode') }}', {
                            method: 'POST',
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
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message || 'Kesiapan barcode siswa berhasil diverifikasi.',
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-success das-swal-confirm'
                                    },
                                    timer: 3000,
                                    showConfirmButton: false,
                                    background: 'transparent',
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(data.message || 'Terjadi kesalahan.');
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.message || 'Terjadi kesalahan saat memproses barcode.',
                                customClass: {
                                    popup: 'das-swal-popup',
                                    title: 'das-swal-title',
                                    htmlContainer: 'das-swal-html',
                                    confirmButton: 'btn btn-primary das-swal-confirm'
                                },
                                background: 'transparent',
                                buttonsStyling: false
                            });
                        });
                    });
                });
            }

            // Reset All Password / Terpilih
            const resetAllPasswordBtn = document.getElementById('resetAllPasswordBtn');
            if (resetAllPasswordBtn) {
                resetAllPasswordBtn.addEventListener('click', function() {
                    const checkedCheckboxes = document.querySelectorAll('.siswa-checkbox:checked');
                    const selectedIds = Array.from(checkedCheckboxes).map(cb => cb.value);
                    const isSpecific = selectedIds.length > 0;

                    const titleText = isSpecific 
                        ? `Reset password ${selectedIds.length} siswa terpilih?` 
                        : 'Reset password seluruh siswa aktif?';
                    const confirmHtml = isSpecific
                        ? `Tindakan ini akan mereset password <b class="text-warning">${selectedIds.length} siswa terpilih</b> menjadi default (NISN atau format default lainnya). Silakan masukkan password administrator Anda untuk melanjutkan.`
                        : 'Tindakan ini akan mereset password seluruh siswa menjadi default (biasanya NISN atau format default lainnya). Silakan masukkan password administrator Anda untuk melanjutkan.';

                    Swal.fire({
                        title: titleText,
                        html: '<div class="text-center px-2">' +
                              '  <p class="text-white-50 mb-3" style="font-size:0.92rem; line-height:1.6;">' +
                              confirmHtml +
                              '  </p>' +
                              '  <input type="password" id="adminPassword" class="form-control text-center bg-dark border-secondary text-white" placeholder="Masukkan Password Admin" autofocus>' +
                              '</div>',
                        iconHtml: '<div class="rounded-circle d-flex align-items-center justify-content-center" style="width:70px; height:70px; background: rgba(255, 159, 67, 0.15); border: 2px solid rgba(255, 159, 67, 0.3); box-shadow: 0 0 15px rgba(255, 159, 67, 0.4);"><i class="ti tabler-lock-open text-warning fs-1" style="font-size: 2.5rem !important;"></i></div>',
                        showCancelButton: true,
                        confirmButtonText: '<i class="ti tabler-shield-lock me-1"></i> Reset Password',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'das-swal-popup',
                            title: 'das-swal-title',
                            htmlContainer: 'das-swal-html',
                            confirmButton: 'btn btn-warning das-swal-confirm px-4 py-2 me-3',
                            cancelButton: 'btn das-btn das-swal-cancel px-4 py-2',
                            icon: 'border-0'
                        },
                        buttonsStyling: false,
                        showClass: {
                            popup: 'animate__animated animate__fadeInUp animate__faster'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutDown animate__faster'
                        },
                        background: 'transparent',
                        backdrop: `rgba(0,0,10,0.4)`,
                        preConfirm: () => {
                            const password = Swal.getPopup().querySelector('#adminPassword').value;
                            if (!password) {
                                Swal.showValidationMessage(`Password administrator wajib diisi!`);
                            }
                            return { password: password };
                        }
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        const adminPassword = result.value.password;

                        // Step 1: Loading modal initial detection
                        Swal.fire({
                            title: 'Menyiapkan Reset Password...',
                            html: '<div class="text-center px-2">' +
                                  '  <p class="text-white-50 mb-3" style="font-size:0.92rem;">Mengambil daftar siswa yang akan di-reset...</p>' +
                                  '  <div class="d-flex align-items-center justify-content-center gap-2 p-2 rounded" style="background: rgba(255, 159, 67, 0.08); border: 1px dashed rgba(255, 159, 67, 0.2);">' +
                                  '    <i class="ti tabler-loader spinner text-warning fs-4"></i>' +
                                  '    <span class="text-warning extra-small fw-semibold">Mohon tunggu sebentar...</span>' +
                                  '  </div>' +
                                  '</div>',
                            iconHtml: '<div class="rounded-circle d-flex align-items-center justify-content-center" style="width:70px; height:70px; background: rgba(255, 159, 67, 0.15); border: 2px solid rgba(255, 159, 67, 0.3);"><i class="ti tabler-loader spinner text-warning fs-1" style="font-size: 2.5rem !important;"></i></div>',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            customClass: {
                                popup: 'das-swal-popup',
                                title: 'das-swal-title',
                                htmlContainer: 'das-swal-html',
                                icon: 'border-0'
                            },
                            buttonsStyling: false,
                            background: 'transparent',
                            backdrop: `rgba(0,0,10,0.45)`,
                        });

                        // Get target student IDs
                        const getTargetIds = isSpecific
                            ? Promise.resolve(selectedIds)
                            : fetch('{{ route('admin.siswa.reset-password-all') }}', {
                                  method: 'POST',
                                  headers: {
                                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                      'X-Requested-With': 'XMLHttpRequest',
                                      'Content-Type': 'application/json',
                                      'Accept': 'application/json'
                                  },
                                  body: JSON.stringify({
                                      password: adminPassword,
                                      get_ids: 1
                                  })
                              })
                              .then(res => {
                                  if (!res.ok) {
                                      return res.json().then(data => {
                                          throw new Error(data.message || `HTTP error! status: ${res.status}`);
                                      });
                                  }
                                  return res.json();
                              })
                              .then(data => {
                                  if (!data.success) throw new Error(data.message || 'Gagal mengambil data siswa');
                                  return data.ids || [];
                              });

                        getTargetIds.then(ids => {
                            if (!ids || ids.length === 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Perhatian!',
                                    text: 'Tidak ada data siswa yang perlu di-reset.',
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-warning das-swal-confirm'
                                    },
                                    background: 'transparent',
                                    buttonsStyling: false
                                });
                                return;
                            }

                            const total = ids.length;
                            let processed = 0;
                            const batchSize = 50;

                            // Step 2: Display progress modal
                            Swal.fire({
                                title: 'Mereset Password Siswa...',
                                html: '<div class="text-center px-2">' +
                                      '  <p id="reset-progress-text" class="text-white-50 mb-3" style="font-size:0.92rem;">Memproses reset password untuk ' + total + ' siswa...</p>' +
                                      '  <div class="progress mb-3" style="height: 12px; background: rgba(255, 255, 255, 0.08); border-radius: 6px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.05);">' +
                                      '    <div id="reset-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 0%; height: 100%; transition: width 0.3s ease; border-radius: 6px;"></div>' +
                                      '  </div>' +
                                      '  <div class="d-flex align-items-center justify-content-center gap-2 p-2 rounded mb-2" style="background: rgba(255, 159, 67, 0.08); border: 1px dashed rgba(255, 159, 67, 0.2);">' +
                                      '    <i class="ti tabler-loader spinner text-warning fs-4"></i>' +
                                      '    <span class="text-warning extra-small fw-semibold">Harap tunggu, proses berjalan secara bertahap...</span>' +
                                      '  </div>' +
                                      '</div>',
                                iconHtml: '<div class="rounded-circle d-flex align-items-center justify-content-center" style="width:70px; height:70px; background: rgba(255, 159, 67, 0.15); border: 2px solid rgba(255, 159, 67, 0.3);"><i class="ti tabler-lock-open text-warning fs-1" style="font-size: 2.5rem !important;"></i></div>',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                customClass: {
                                    popup: 'das-swal-popup',
                                    title: 'das-swal-title',
                                    htmlContainer: 'das-swal-html',
                                    icon: 'border-0'
                                },
                                buttonsStyling: false,
                                background: 'transparent',
                                backdrop: `rgba(0,0,10,0.45)`,
                            });

                            // Batch processing recursive function
                            function processNextBatch() {
                                if (processed >= total) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: `Berhasil me-reset password untuk ${total} akun siswa.`,
                                        customClass: {
                                            popup: 'das-swal-popup',
                                            title: 'das-swal-title',
                                            htmlContainer: 'das-swal-html',
                                            confirmButton: 'btn btn-success das-swal-confirm'
                                        },
                                        timer: 2500,
                                        showConfirmButton: false,
                                        background: 'transparent',
                                    }).then(() => {
                                        fetchData(1);
                                    });
                                    return;
                                }

                                const batch = ids.slice(processed, processed + batchSize);

                                fetch('{{ route('admin.siswa.reset-password-all') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        password: adminPassword,
                                        siswa_ids: batch
                                    })
                                })
                                .then(res => {
                                    if (!res.ok) {
                                        return res.json().then(data => {
                                            throw new Error(data.message || `HTTP error! status: ${res.status}`);
                                        });
                                    }
                                    return res.json();
                                })
                                .then(data => {
                                    if (data.success) {
                                        processed += batch.length;
                                        const percent = Math.min(Math.round((processed / total) * 100), 100);
                                        const progressBar = document.getElementById('reset-progress-bar');
                                        const progressText = document.getElementById('reset-progress-text');
                                        if (progressBar) progressBar.style.width = percent + '%';
                                        if (progressText) progressText.textContent = `Mereset password ${processed} dari ${total} siswa (${percent}%)...`;
                                        
                                        processNextBatch();
                                    } else {
                                        throw new Error(data.message || 'Terjadi kesalahan saat mereset batch.');
                                    }
                                })
                                .catch(err => {
                                    console.error('Error batch reset:', err);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: err.message || 'Terjadi kesalahan saat mereset password.',
                                        customClass: {
                                            popup: 'das-swal-popup',
                                            title: 'das-swal-title',
                                            htmlContainer: 'das-swal-html',
                                            confirmButton: 'btn btn-primary das-swal-confirm'
                                        },
                                        background: 'transparent',
                                        buttonsStyling: false
                                    });
                                });
                            }

                            processNextBatch();
                        })
                        .catch(err => {
                            console.error('Error fetching target IDs:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.message || 'Terjadi kesalahan saat memuat data.',
                                customClass: {
                                    popup: 'das-swal-popup',
                                    title: 'das-swal-title',
                                    htmlContainer: 'das-swal-html',
                                    confirmButton: 'btn btn-primary das-swal-confirm'
                                },
                                background: 'transparent',
                                buttonsStyling: false
                            });
                        });
                    });
                });
            }

            // Generate Ortu Massal
            const generateOrtuBtn = document.getElementById('generateOrtuBtn');
            if (generateOrtuBtn) {
                generateOrtuBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Generate Akun Orang Tua?',
                        html: '<div class="text-center px-2">' +
                              '  <p class="text-white-50 mb-3" style="font-size:0.92rem; line-height:1.6;">' +
                              '    Sistem akan mendeteksi seluruh siswa yang belum memiliki wali murid, lalu membuatkan akun akses orang tua secara otomatis.' +
                              '  </p>' +
                              '  <div class="d-flex align-items-center justify-content-center gap-2 p-2 rounded mb-2" style="background: rgba(115, 103, 240, 0.08); border: 1px dashed rgba(115, 103, 240, 0.2);">' +
                              '    <i class="ti tabler-shield-check text-purple fs-4 animate-pulse"></i>' +
                              '    <span class="text-purple extra-small fw-semibold">Proses aman, data login otomatis diselaraskan</span>' +
                              '  </div>' +
                              '</div>',
                        iconHtml: '<div class="rounded-circle d-flex align-items-center justify-content-center" style="width:70px; height:70px; background: rgba(115, 103, 240, 0.15); border: 2px solid rgba(115, 103, 240, 0.3); box-shadow: 0 0 15px rgba(115, 103, 240, 0.4);"><i class="ti tabler-key text-purple fs-1" style="font-size: 2.5rem !important;"></i></div>',
                        showCancelButton: true,
                        confirmButtonText: '<i class="ti tabler-bolt me-1"></i> Mulai Proses',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'das-swal-popup',
                            title: 'das-swal-title',
                            htmlContainer: 'das-swal-html',
                            confirmButton: 'btn das-btn --purple px-4 py-2 me-3',
                            cancelButton: 'btn das-btn das-swal-cancel px-4 py-2',
                            icon: 'border-0'
                        },
                        buttonsStyling: false,
                        showClass: {
                            popup: 'animate__animated animate__fadeInUp animate__faster'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutDown animate__faster'
                        },
                        background: 'transparent',
                        backdrop: `rgba(0,0,10,0.4)`,
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        // 1. Tampilkan modal loading awal / deteksi
                        Swal.fire({
                            title: 'Mendeteksi Data...',
                            html: '<div class="text-center px-2">' +
                                  '  <p class="text-white-50 mb-3" style="font-size:0.92rem;">Sedang mencari siswa tanpa akun orang tua...</p>' +
                                  '  <div class="d-flex align-items-center justify-content-center gap-2 p-2 rounded" style="background: rgba(115, 103, 240, 0.08); border: 1px dashed rgba(115, 103, 240, 0.2);">' +
                                  '    <i class="ti tabler-loader spinner text-purple fs-4"></i>' +
                                  '    <span class="text-purple extra-small fw-semibold">Mohon tunggu sebentar...</span>' +
                                  '  </div>' +
                                  '</div>',
                            iconHtml: '<div class="rounded-circle d-flex align-items-center justify-content-center" style="width:70px; height:70px; background: rgba(115, 103, 240, 0.15); border: 2px solid rgba(115, 103, 240, 0.3);"><i class="ti tabler-loader spinner text-purple fs-1" style="font-size: 2.5rem !important;"></i></div>',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            customClass: {
                                popup: 'das-swal-popup',
                                title: 'das-swal-title',
                                htmlContainer: 'das-swal-html',
                                icon: 'border-0'
                            },
                            buttonsStyling: false,
                            background: 'transparent',
                            backdrop: `rgba(0,0,10,0.45)`,
                        });

                        // 2. Tarik daftar ID siswa yang belum punya ortu
                        fetch('{{ route('admin.siswa.generate-ortu-massal') }}?get_ids=1', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => {
                            if (!res.ok) {
                                return res.text().then(text => {
                                    throw new Error(text || `HTTP error! status: ${res.status}`);
                                });
                            }
                            const contentType = res.headers.get('content-type');
                            if (!contentType || !contentType.includes('application/json')) {
                                return res.text().then(text => {
                                    throw new Error('Respon server bukan JSON yang valid: ' + text.substring(0, 200));
                                });
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.message || 'Gagal memuat data siswa.');
                            }

                            const ids = data.ids || [];
                            if (ids.length === 0) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sudah Sinkron!',
                                    text: 'Semua siswa yang memiliki NISN/NIS sudah terhubung dengan akun orang tua.',
                                    showConfirmButton: true,
                                    allowOutsideClick: true,
                                    allowEscapeKey: true,
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-success das-swal-confirm'
                                    },
                                    background: 'transparent',
                                    buttonsStyling: false
                                });
                                return;
                            }

                            // 3. Tampilkan modal progress interaktif
                            const total = ids.length;
                            let processed = 0;
                            const batchSize = 10;

                            Swal.fire({
                                title: 'Memproses Akun...',
                                html: '<div class="text-center px-2">' +
                                      '  <p id="progress-text" class="text-white-50 mb-3" style="font-size:0.92rem;">Memulai pembuatan akun untuk ' + total + ' siswa...</p>' +
                                      '  <div class="progress mb-3" style="height: 12px; background: rgba(255, 255, 255, 0.08); border-radius: 6px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.05);">' +
                                      '    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; height: 100%; background: linear-gradient(45deg, #7367f0, #9e95f5); transition: width 0.4s ease; border-radius: 6px;"></div>' +
                                      '  </div>' +
                                      '  <div class="d-flex align-items-center justify-content-center gap-2 p-2 rounded mb-2" style="background: rgba(115, 103, 240, 0.08); border: 1px dashed rgba(115, 103, 240, 0.2);">' +
                                      '    <i class="ti tabler-loader spinner text-purple fs-4"></i>' +
                                      '    <span class="text-purple extra-small fw-semibold">Mohon jangan menutup atau memuat ulang halaman ini.</span>' +
                                      '  </div>' +
                                      '</div>',
                                iconHtml: '<div class="rounded-circle d-flex align-items-center justify-content-center" style="width:70px; height:70px; background: rgba(115, 103, 240, 0.15); border: 2px solid rgba(115, 103, 240, 0.3); box-shadow: 0 0 15px rgba(115, 103, 240, 0.4);"><i class="ti tabler-key text-purple fs-1" style="font-size: 2.5rem !important;"></i></div>',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                customClass: {
                                    popup: 'das-swal-popup',
                                    title: 'das-swal-title',
                                    htmlContainer: 'das-swal-html',
                                    icon: 'border-0'
                                },
                                buttonsStyling: false,
                                background: 'transparent',
                                backdrop: `rgba(0,0,10,0.45)`,
                            });

                            // 4. Fungsi rekursif untuk eksekusi batch
                            function processNextBatch() {
                                if (processed >= total) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Selesai!',
                                        text: 'Berhasil memproses semua akun orang tua untuk ' + total + ' siswa.',
                                        showConfirmButton: true,
                                        allowOutsideClick: true,
                                        allowEscapeKey: true,
                                        customClass: {
                                            popup: 'das-swal-popup',
                                            title: 'das-swal-title',
                                            htmlContainer: 'das-swal-html',
                                            confirmButton: 'btn btn-success das-swal-confirm'
                                        },
                                        background: 'transparent',
                                        buttonsStyling: false
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                    return;
                                }

                                const batch = ids.slice(processed, processed + batchSize);

                                fetch('{{ route('admin.siswa.generate-ortu-massal') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ siswa_ids: batch })
                                })
                                .then(res => {
                                    if (!res.ok) {
                                        return res.text().then(text => {
                                            throw new Error(text || `HTTP error! status: ${res.status}`);
                                        });
                                    }
                                    const contentType = res.headers.get('content-type');
                                    if (!contentType || !contentType.includes('application/json')) {
                                        return res.text().then(text => {
                                            throw new Error('Respon server bukan JSON yang valid: ' + text.substring(0, 200));
                                        });
                                    }
                                    return res.json();
                                })
                                .then(resData => {
                                    if (!resData.success) {
                                        throw new Error(resData.message || 'Terjadi kesalahan saat memproses.');
                                    }

                                    processed += batch.length;
                                    const percent = Math.round((processed / total) * 100);

                                    const pBar = document.getElementById('progress-bar');
                                    const pText = document.getElementById('progress-text');

                                    if (pBar) pBar.style.width = percent + '%';
                                    if (pText) {
                                        pText.innerHTML = 'Memproses data <strong>' + processed + '</strong> dari <strong>' + total + '</strong> siswa (' + percent + '%)';
                                    }

                                    setTimeout(processNextBatch, 100);
                                })
                                .catch(err => {
                                    console.error('Batch generation error:', err);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: err.message || 'Terjadi kesalahan koneksi saat memproses batch data.',
                                        showConfirmButton: true,
                                        allowOutsideClick: true,
                                        allowEscapeKey: true,
                                        customClass: {
                                            popup: 'das-swal-popup',
                                            title: 'das-swal-title',
                                            htmlContainer: 'das-swal-html',
                                            confirmButton: 'btn btn-primary das-swal-confirm'
                                        },
                                        background: 'transparent',
                                        buttonsStyling: false
                                    });
                                });
                            }

                            // Mulai loop batching
                            processNextBatch();
                        })
                        .catch(err => {
                            console.error('Initial ID fetch error:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.message || 'Terjadi kesalahan koneksi atau inisialisasi proses.',
                                showConfirmButton: true,
                                allowOutsideClick: true,
                                allowEscapeKey: true,
                                customClass: {
                                    popup: 'das-swal-popup',
                                    title: 'das-swal-title',
                                    htmlContainer: 'das-swal-html',
                                    confirmButton: 'btn btn-primary das-swal-confirm'
                                },
                                background: 'transparent',
                                buttonsStyling: false
                            });
                        });
                    });
                });
            }

            // Sync Google Sheet
            const syncBtn = document.getElementById('syncGoogleSheetBtn');
            const syncModal = new bootstrap.Modal(document.getElementById('syncProgressModal'));
            const syncMsg = document.getElementById('syncProgressMessage');
            const syncBar = document.getElementById('syncProgressBar');
            const syncCount = document.getElementById('syncProgressCount');
            let syncInterval;

            if (syncBtn) {
                syncBtn.addEventListener('click', function() {
                    syncMsg.textContent = 'Memulai sinkronisasi...';
                    syncBar.style.width = '0%';
                    syncCount.textContent = '';
                    syncBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-warning';
                    syncModal.show();

                    // Poll progress
                    syncInterval = setInterval(function() {
                        fetch('{{ route('admin.siswa.sync-progress') }}', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(res => res.json())
                            .then(prog => {
                                if (prog.total > 0) {
                                    const pct = Math.min(Math.round((prog.processed / prog.total) * 100), 99);
                                    syncBar.style.width = pct + '%';
                                    syncCount.textContent = prog.processed + ' / ' + prog.total + ' siswa diproses';
                                    syncMsg.textContent = prog.message || 'Sedang memproses...';
                                }
                            })
                            .catch(err => console.error('Progress poll error:', err));
                    }, 2000);

                    fetch('{{ route('admin.siswa.sync-google-sheet') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            clearInterval(syncInterval);
                            syncModal.hide();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Dijadwalkan!',
                                    text: data.message || 'Sinkronisasi Google Sheets telah dijadwalkan dan akan diproses di latar belakang.',
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-success das-swal-confirm'
                                    },
                                    timer: 3000,
                                    showConfirmButton: false,
                                    background: 'transparent',
                                }).then(() => {
                                    fetchData(1);
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Sinkronisasi Gagal',
                                    text: data.message || 'Terjadi kesalahan saat memulai sinkronisasi.',
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-primary das-swal-confirm'
                                    },
                                    background: 'transparent',
                                    buttonsStyling: false
                                });
                            }
                        })
                        .catch(err => {
                            clearInterval(syncInterval);
                            syncModal.hide();
                            console.error('Sync error:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Koneksi Gagal',
                                text: 'Gagal menghubungi server untuk sinkronisasi. Silakan coba lagi.',
                                customClass: {
                                    popup: 'das-swal-popup',
                                    title: 'das-swal-title',
                                    htmlContainer: 'das-swal-html',
                                    confirmButton: 'btn btn-primary das-swal-confirm'
                                },
                                background: 'transparent',
                                buttonsStyling: false
                            });
                        });
                });
            }

            // ─── Import Progress Bar ───────────────────────────────────────────
            const importForm = document.getElementById('importForm');
            if (importForm) {
                const importFormBody = document.getElementById('importFormBody');
                const importSubmitBtn = document.getElementById('importSubmitBtn');
                const importCancelBtn = document.getElementById('importCancelBtn');
                const importProgressArea = document.getElementById('importProgressArea');
                const importProgressBar = document.getElementById('importProgressBar');
                const importProgressText = document.getElementById('importProgressText');
                const importProgressDetail = document.getElementById('importProgressDetail');
                const importFileInput = document.getElementById('import_file');
                let progressInterval = null;

                importForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!importFileInput.files[0]) return;

                    document.querySelectorAll('#importFormBody > div:not(#importProgressArea)').forEach(el => el.style.display = 'none');
                    importProgressArea.classList.remove('d-none');
                    importSubmitBtn.disabled = true;
                    importSubmitBtn.innerHTML = '<i class="ti tabler-loader spinner"></i> Mengimport...';
                    importCancelBtn.disabled = true;

                    const formData = new FormData(importForm);

                    progressInterval = setInterval(function() {
                        fetch("{{ route('admin.siswa.import-progress') }}")
                            .then(res => res.json())
                            .then(data => {
                                const pct = data.total > 0 ? Math.min(100, Math.round((data.progress / data.total) * 100)) : 0;
                                importProgressBar.style.width = pct + '%';
                                importProgressBar.textContent = pct + '%';
                                importProgressText.textContent = pct + '%';
                                importProgressDetail.textContent = 'Memproses ' + data.progress + ' dari ' + data.total + ' data...';
                                if (data.progress >= data.total && data.total > 0) {
                                    clearInterval(progressInterval);
                                    importProgressBar.classList.remove('progress-bar-animated');
                                    importProgressBar.style.background = 'linear-gradient(135deg, #28c76f, #00d25a)';
                                    importProgressBar.textContent = 'Selesai!';
                                    importProgressDetail.textContent = 'Menyimpan data...';
                                }
                            }).catch(() => {});
                    }, 1000);

                    fetch(importForm.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(async res => {
                            const data = await res.json();
                            clearInterval(progressInterval);
                            
                            // Tampilkan tombol submit & cancel kembali
                            importSubmitBtn.disabled = false;
                            importSubmitBtn.innerHTML = '<i class="ti tabler-upload me-1"></i> Mulai Import';
                            importCancelBtn.disabled = false;
                            
                            if (res.ok && data.success) {
                                importProgressBar.style.width = '100%';
                                importProgressBar.textContent = 'Selesai!';
                                importProgressBar.classList.remove('progress-bar-animated');
                                importProgressBar.style.background = 'linear-gradient(135deg, #28c76f, #00d25a)';
                                importProgressDetail.textContent = data.message;
                                
                                // Jika ada error per baris, kumpulkan dan tampilkan menggunakan SweetAlert2
                                if (data.errors && data.errors.length > 0) {
                                    let errorHtml = '<div class="text-start mt-3" style="max-height: 250px; overflow-y: auto; font-size: 0.8rem; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">';
                                    errorHtml += '<ul class="list-unstyled mb-0">';
                                    data.errors.forEach(err => {
                                        errorHtml += `<li class="mb-2 text-warning"><i class="ti tabler-alert-triangle me-1"></i> <b>Baris ${err.row}:</b> ${err.error} <span class="text-white-50">(NISN: ${err.nisn}, Nama: ${err.nama})</span></li>`;
                                    });
                                    errorHtml += '</ul></div>';

                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Import Selesai dengan Catatan',
                                        html: `<div>Beberapa data berhasil diimport, namun terdapat <b>${data.errors.length} baris yang gagal</b>.</div>` + errorHtml,
                                        customClass: {
                                            popup: 'das-swal-popup',
                                            title: 'das-swal-title',
                                            htmlContainer: 'das-swal-html',
                                            confirmButton: 'btn btn-warning das-swal-confirm'
                                        },
                                        background: 'transparent',
                                        buttonsStyling: false
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: data.message || 'Data siswa berhasil diimport.',
                                        customClass: {
                                            popup: 'das-swal-popup',
                                            title: 'das-swal-title',
                                            htmlContainer: 'das-swal-html',
                                            confirmButton: 'btn btn-success das-swal-confirm'
                                        },
                                        timer: 2000,
                                        showConfirmButton: false,
                                        background: 'transparent',
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }
                            } else if (res.status === 422 && data.validation_errors) {
                                importProgressBar.classList.remove('progress-bar-animated');
                                importProgressBar.style.background = 'linear-gradient(135deg, #ea5455, #ff5b5b)';
                                importProgressDetail.textContent = 'Validasi file gagal.';
                                
                                let validationHtml = '<div class="text-start mt-3" style="max-height: 250px; overflow-y: auto; font-size: 0.8rem; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">';
                                validationHtml += '<ul class="list-unstyled mb-0">';
                                data.validation_errors.forEach(err => {
                                    const errMsg = Array.isArray(err.errors) ? err.errors.join(', ') : err.errors;
                                    validationHtml += `<li class="mb-2 text-danger"><i class="ti tabler-circle-x me-1"></i> <b>Baris ${err.row} (Kolom: ${err.attribute}):</b> ${errMsg}</li>`;
                                });
                                validationHtml += '</ul></div>';

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validasi Gagal',
                                    html: `<div>Terdapat kesalahan validasi pada data yang Anda upload:</div>` + validationHtml,
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-primary das-swal-confirm'
                                    },
                                    background: 'transparent',
                                    buttonsStyling: false
                                });
                            } else {
                                importProgressBar.classList.remove('progress-bar-animated');
                                importProgressBar.style.background = 'linear-gradient(135deg, #ea5455, #ff5b5b)';
                                importProgressDetail.textContent = data.message || 'Import gagal';
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: data.message || 'Terjadi kesalahan.',
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-primary das-swal-confirm'
                                    },
                                    background: 'transparent',
                                    buttonsStyling: false
                                });
                            }
                        }).catch(err => {
                            clearInterval(progressInterval);
                            importProgressBar.classList.remove('progress-bar-animated');
                            importProgressBar.style.background = 'linear-gradient(135deg, #ea5455, #ff5b5b)';
                            importProgressDetail.textContent = 'Gagal menghubungi server';
                            importSubmitBtn.disabled = false;
                            importSubmitBtn.innerHTML = '<i class="ti tabler-upload me-1"></i> Coba Lagi';
                            importCancelBtn.disabled = false;
                            
                            console.error('Fetch error:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Gagal menghubungi server. Silakan coba lagi.',
                                customClass: {
                                    popup: 'das-swal-popup',
                                    title: 'das-swal-title',
                                    htmlContainer: 'das-swal-html',
                                    confirmButton: 'btn btn-primary das-swal-confirm'
                                },
                                background: 'transparent',
                                buttonsStyling: false
                            });
                        });
                });

                document.getElementById('importModal').addEventListener('hidden.bs.modal', function() {
                    clearInterval(progressInterval);
                    importForm.reset();
                    importProgressArea.classList.add('d-none');
                    importProgressBar.style.width = '0%';
                    importProgressBar.textContent = '0%';
                    importProgressBar.className = 'progress-bar progress-bar-striped progress-bar-animated';
                    importProgressBar.style.background = 'linear-gradient(135deg, #7367f0, #a55eea)';
                    importProgressDetail.textContent = 'Memproses 0 dari 0 data...';
                    importSubmitBtn.disabled = false;
                    importSubmitBtn.innerHTML = '<i class="ti tabler-upload me-1"></i> Mulai Import';
                    importCancelBtn.disabled = false;
                    document.querySelectorAll('#importFormBody > div:not(#importProgressArea)').forEach(el => el.style.display = '');
                });
            }

            // btnRegeneratePhoneSiswa
            const btnRegeneratePhoneSiswa = document.getElementById('btnRegeneratePhoneSiswa');
            if (btnRegeneratePhoneSiswa) {
                btnRegeneratePhoneSiswa.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Generate Format WA?',
                        html: `<div class="mt-2 text-white-50">Format seluruh nomor WA Siswa & Ortu (08...) akan dikonversi ke standar internasional (<b class="text-success">628...</b>).</div>`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Format Sekarang',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                        customClass: {
                            popup: 'das-swal-popup',
                            title: 'das-swal-title',
                            htmlContainer: 'das-swal-html',
                            confirmButton: 'btn btn-success das-swal-confirm ms-2',
                            cancelButton: 'btn das-swal-cancel',
                            icon: 'das-swal-icon'
                        },
                        buttonsStyling: false,
                        showClass: {
                            popup: 'animate__animated animate__fadeInUp animate__faster'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutDown animate__faster'
                        },
                        background: 'transparent',
                        backdrop: `rgba(0,0,10,0.6)`
                    }).then(function(res) {
                        if (res.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses...',
                                html: '<div class="mt-2 text-white-50">Merapikan format nomor WA ke standar internasional...</div>',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                customClass: {
                                    popup: 'das-swal-popup',
                                    title: 'das-swal-title',
                                    htmlContainer: 'das-swal-html'
                                },
                                background: 'transparent',
                                backdrop: `rgba(0,0,10,0.6)`,
                                didOpen: () => Swal.showLoading()
                            });
                            fetch("{{ route('admin.siswa.regenerate-phone') }}", {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                            }).then(r => r.json()).then(d => {
                                if (d.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: d.message,
                                        confirmButtonText: 'OK',
                                        customClass: {
                                            popup: 'das-swal-popup',
                                            title: 'das-swal-title',
                                            htmlContainer: 'das-swal-html',
                                            confirmButton: 'btn btn-primary das-swal-confirm',
                                            icon: 'das-swal-icon'
                                        },
                                        buttonsStyling: false,
                                        background: 'transparent',
                                        backdrop: `rgba(0,0,10,0.6)`
                                    }).then(() => window.location.reload());
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: d.message,
                                        confirmButtonText: 'Tutup',
                                        customClass: {
                                            popup: 'das-swal-popup',
                                            title: 'das-swal-title',
                                            htmlContainer: 'das-swal-html',
                                            confirmButton: 'btn btn-primary das-swal-confirm',
                                            icon: 'das-swal-icon'
                                        },
                                        buttonsStyling: false,
                                        background: 'transparent',
                                        backdrop: `rgba(0,0,10,0.6)`
                                    });
                                }
                            }).catch(e => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Terjadi kesalahan sistem.',
                                    confirmButtonText: 'Tutup',
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-primary das-swal-confirm',
                                        icon: 'das-swal-icon'
                                    },
                                    buttonsStyling: false,
                                    background: 'transparent',
                                    backdrop: `rgba(0,0,10,0.6)`
                                });
                            });
                        }
                    });
                });
            }

            // initial tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // ═══════════════════════════════════════════════════════════════
            // BATCH WA VALIDITY CHECKER FOR SISWA TABLE
            // ═══════════════════════════════════════════════════════════════
            function checkTableWaNumbers() {
                const badges = document.querySelectorAll('.wa-status-badge[data-wa-number]');
                if (!badges.length) return;

                const numbersToCheck = [];
                badges.forEach(badge => {
                    const num = badge.getAttribute('data-wa-number');
                    const textSpan = badge.querySelector('.wa-status-text');
                    if (num && textSpan && textSpan.textContent.trim() === 'Cek WA') {
                        if (!numbersToCheck.includes(num)) numbersToCheck.push(num);
                    }
                });

                if (!numbersToCheck.length) return;

                const chunkSize = 5;
                for (let i = 0; i < numbersToCheck.length; i += chunkSize) {
                    const chunk = numbersToCheck.slice(i, i + chunkSize);
                    setTimeout(() => {
                        fetch('{{ route("admin.wa-gateway.batch-check-numbers") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ numbers: chunk })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status && data.results) {
                                badges.forEach(badge => {
                                    const num = badge.getAttribute('data-wa-number');
                                    if (num && data.results.hasOwnProperty(num)) {
                                        const isValid = data.results[num];
                                        const textSpan = badge.querySelector('.wa-status-text');
                                        if (isValid) {
                                            badge.className = 'badge wa-status-badge bg-label-success text-success px-2 py-1';
                                            if (textSpan) textSpan.textContent = 'Valid WA';
                                            badge.title = 'Terdaftar di WhatsApp';
                                        } else {
                                            badge.className = 'badge wa-status-badge bg-label-danger text-danger px-2 py-1';
                                            if (textSpan) textSpan.textContent = 'Tidak Valid';
                                            badge.title = 'Tidak terdaftar di WhatsApp';
                                        }
                                    }
                                });
                            }
                        })
                        .catch(err => console.error('Batch WA check error:', err));
                    }, i * 200);
                }
            }

            const runIdle = window.requestIdleCallback || function (cb) { setTimeout(cb, 500); };
            runIdle(function() {
                checkTableWaNumbers();
            });

            // Bulk WA Check for All Database Records (1000+ Records)
            function runBulkWaCheck(role) {
                let offset = 0;
                const limit = 25;
                let validTotal = 0;
                let isCancelled = false;

                if (typeof Swal === 'undefined') return;

                Swal.fire({
                    html: `
                        <div style="padding: 8px 4px;">
                            <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
                                <div style="width:48px; height:48px; border-radius:12px; background:linear-gradient(135deg,#25d366,#128c7e); display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 4px 15px rgba(37,211,102,0.4);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.558 4.122 1.532 5.85L.058 23.5l5.797-1.498A11.954 11.954 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.89 0-3.663-.52-5.178-1.427l-.37-.22-3.44.889.914-3.35-.24-.386A9.961 9.961 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                                </div>
                                <div style="text-align:left;">
                                    <div style="font-size:1rem; font-weight:700; color:#fff; line-height:1.2;">Verifikasi WA Massal</div>
                                    <div style="font-size:0.75rem; color:rgba(255,255,255,0.5); margin-top:2px;">Memproses seluruh data siswa di database...</div>
                                </div>
                            </div>

                            <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:16px; margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                    <span id="bulkWaStatusText" style="font-size:0.78rem; color:rgba(255,255,255,0.6);">Memulai kueri database...</span>
                                    <span id="bulkWaCounterText" style="font-size:0.78rem; font-weight:600; color:#25d366; font-family:monospace;">0 / 0</span>
                                </div>
                                <div style="background:rgba(255,255,255,0.07); border-radius:999px; height:10px; overflow:hidden; margin-bottom:8px;">
                                    <div id="bulkWaProgressBar" style="height:100%; width:0%; border-radius:999px; background:linear-gradient(90deg,#25d366,#128c7e); transition:width 0.4s ease; position:relative;">
                                        <div style="position:absolute;inset:0;background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,0.2) 50%,transparent 100%);animation:shimmer 1.5s infinite;"></div>
                                    </div>
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span id="bulkWaPercentText" style="font-size:0.72rem; color:rgba(255,255,255,0.4);">0%</span>
                                    <div style="display:flex; gap:16px;">
                                        <div style="text-align:center;">
                                            <div id="bulkWaValidCount" style="font-size:0.9rem; font-weight:700; color:#25d366;">0</div>
                                            <div style="font-size:0.65rem; color:rgba(255,255,255,0.4);">Valid WA</div>
                                        </div>
                                        <div style="text-align:center;">
                                            <div id="bulkWaInvalidCount" style="font-size:0.9rem; font-weight:700; color:#ff4757;">0</div>
                                            <div style="font-size:0.65rem; color:rgba(255,255,255,0.4);">Tidak Valid</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex; align-items:center; gap:8px; font-size:0.72rem; color:rgba(255,255,255,0.35);">
                                <div style="width:6px;height:6px;border-radius:50%;background:#25d366;animation:pulse-dot 1.2s infinite;flex-shrink:0;"></div>
                                Proses berjalan di background, halaman tetap bisa digunakan
                            </div>
                        </div>
                        <style>
                            @keyframes shimmer { 0%{transform:translateX(-100%)} 100%{transform:translateX(200%)} }
                            @keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(0.7)} }
                        </style>
                    `,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Hentikan Proses',
                    customClass: {
                        popup: 'border-0 shadow-lg',
                    },
                    buttonsStyling: true,
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        isCancelled = true;
                    }
                });

                function processChunk() {
                    if (isCancelled) return;

                    fetch('{{ route("admin.wa-gateway.check-all-role-numbers") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ role: role, offset: offset, limit: limit })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (isCancelled) return;

                        if (data.status) {
                            const total = data.total || 1;
                            offset += data.processed;

                            let chunkInvalid = 0;
                            Object.keys(data.results || {}).forEach(num => {
                                if (data.results[num]) validTotal++;
                                else chunkInvalid++;
                            });

                            const percent = Math.min(100, Math.round((offset / total) * 100));
                            const pBar = document.getElementById('bulkWaProgressBar');
                            const sText = document.getElementById('bulkWaStatusText');
                            const cText = document.getElementById('bulkWaCounterText');
                            const pText = document.getElementById('bulkWaPercentText');
                            const vCount = document.getElementById('bulkWaValidCount');
                            const iCount = document.getElementById('bulkWaInvalidCount');

                            if (pBar) pBar.style.width = percent + '%';
                            if (pText) pText.textContent = percent + '%';
                            if (sText) sText.textContent = `Memeriksa batch ${Math.ceil(offset / limit)} dari ${Math.ceil(total / limit)}...`;
                            if (cText) cText.textContent = `${Math.min(offset, total)} / ${total}`;
                            if (vCount) vCount.textContent = validTotal;
                            if (iCount) iCount.textContent = (offset - validTotal);

                            if (offset < total && data.processed > 0) {
                                processChunk();
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Verifikasi Massal Selesai!',
                                    text: `Selesai memverifikasi seluruh ${total} data. Ditemukan ${validTotal} nomor terdaftar di WhatsApp.`,
                                    confirmButtonText: 'Selesai'
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Bulk WA check error:', err);
                    });
                }

                processChunk();
            }

            const btnCheckWaAllDatabase = document.getElementById('btnCheckWaAllDatabase');
            if (btnCheckWaAllDatabase) {
                btnCheckWaAllDatabase.addEventListener('click', function() {
                    runBulkWaCheck('siswa');
                });
            }

            const btnCheckWaCurrentPage = document.getElementById('btnCheckWaCurrentPage');
            if (btnCheckWaCurrentPage) {
                btnCheckWaCurrentPage.addEventListener('click', function() {
                    triggerManualCheckCurrentPage();
                });
            }

            function triggerManualCheckCurrentPage() {
                const badges = document.querySelectorAll('.wa-status-badge[data-wa-number]');
                if (!badges.length) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'info', title: 'Info', text: 'Tidak ada nomor WA yang dapat dicek di tabel ini.', timer: 2000, showConfirmButton: false });
                    }
                    return;
                }

                const numbersToCheck = [];
                badges.forEach(badge => {
                    const num = badge.getAttribute('data-wa-number');
                    if (num && !numbersToCheck.includes(num)) numbersToCheck.push(num);
                });

                const icon = document.getElementById('iconManualCheckWa');
                if (icon) icon.classList.add('ti-spin');

                badges.forEach(badge => {
                    const textSpan = badge.querySelector('.wa-status-text');
                    badge.className = 'badge wa-status-badge bg-label-secondary text-white-50 px-2 py-1';
                    if (textSpan) textSpan.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:10px;height:10px;"></span> Cek WA';
                });

                fetch('{{ route("admin.wa-gateway.batch-check-numbers") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ numbers: numbersToCheck, force: true })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.results) {
                        let validCount = 0;
                        badges.forEach(badge => {
                            const num = badge.getAttribute('data-wa-number');
                            if (num && data.results.hasOwnProperty(num)) {
                                const isValid = data.results[num];
                                const textSpan = badge.querySelector('.wa-status-text');
                                if (isValid) {
                                    validCount++;
                                    badge.className = 'badge wa-status-badge bg-label-success text-success px-2 py-1';
                                    if (textSpan) textSpan.textContent = 'Valid WA';
                                    badge.title = 'Terdaftar di WhatsApp';
                                } else {
                                    badge.className = 'badge wa-status-badge bg-label-danger text-danger px-2 py-1';
                                    if (textSpan) textSpan.textContent = 'Tidak Valid';
                                    badge.title = 'Tidak terdaftar di WhatsApp';
                                }
                            }
                        });
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pengecekan WA Selesai',
                                text: `${validCount} dari ${numbersToCheck.length} nomor WA terverifikasi aktif di WhatsApp.`,
                                timer: 2500,
                                showConfirmButton: false
                            });
                        }
                    }
                })
                .catch(err => {
                    console.error('Manual WA check error:', err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal melakukan pengecekan nomor WA.' });
                    }
                })
                .finally(() => {
                    if (icon) icon.classList.remove('ti-spin');
                });
            }

            document.addEventListener('click', function(e) {
                const badge = e.target.closest('.wa-status-badge[data-wa-number]');
                if (!badge) return;

                const num = badge.getAttribute('data-wa-number');
                if (!num) return;

                const textSpan = badge.querySelector('.wa-status-text');
                if (textSpan) textSpan.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:10px;height:10px;"></span> ...';

                fetch('{{ route("admin.wa-gateway.batch-check-numbers") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ numbers: [num], force: true })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.results && data.results.hasOwnProperty(num)) {
                        const isValid = data.results[num];
                        if (isValid) {
                            badge.className = 'badge wa-status-badge bg-label-success text-success px-2 py-1';
                            if (textSpan) textSpan.textContent = 'Valid WA';
                            badge.title = 'Terdaftar di WhatsApp';
                        } else {
                            badge.className = 'badge wa-status-badge bg-label-danger text-danger px-2 py-1';
                            if (textSpan) textSpan.textContent = 'Tidak Valid';
                            badge.title = 'Tidak terdaftar di WhatsApp';
                        }
                    }
                })
                .catch(err => console.error('Single WA check error:', err));
            });
        });
    </script>
@endsection
