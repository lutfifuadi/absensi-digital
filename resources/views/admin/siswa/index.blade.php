@extends('layouts/layoutMaster')

@section('title', 'Siswa')

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
                <button type="button" class="btn das-btn --purple" id="generateOrtuBtn">
                    <i class="ti tabler-key me-1"></i> Generate Ortu
                </button>
                <button type="button" class="btn das-btn --warning" id="syncGoogleSheetBtn">
                    <i class="ti tabler-refresh me-1"></i> Google Sheet
                </button>
                <button type="button" class="btn das-btn --secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="ti tabler-file-import me-1"></i> Import
                </button>
                <div class="btn-group">
                    <button type="button" class="btn das-btn --success dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="ti tabler-download me-1"></i> Export
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
                <button type="button" class="btn das-btn --danger" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                    <i class="ti tabler-trash me-1"></i> Hapus Siswa
                </button>
                <a href="{{ route('admin.siswa.create') }}" class="btn das-btn --primary">
                    <i class="ti tabler-plus me-1"></i> Tambah Siswa
                </a>
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
                    <input type="text" id="filterSearch" name="search" class="form-control bg-dark border-secondary text-white"
                        placeholder="Nama, NIS, atau NISN…" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white-50 small fw-bold">Filter Kelas</label>
                    <select id="filterKelas" name="kelas_id" class="form-select bg-dark border-secondary text-white">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelasOptions as $k)
                            <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white-50 small fw-bold">Status</label>
                    <select id="filterStatus" name="status" class="form-select bg-dark border-secondary text-white">
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
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-head d-flex align-items-center justify-content-between">
                    <h5 class="das-modal-title"><i class="ti tabler-trash me-2 text-danger"></i> Hapus Semua Siswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="deleteAllForm" action="{{ route('admin.siswa.destroy-all') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="das-modal-body">
                        <p class="mb-3">Semua data siswa akan dihapus, termasuk data absensi siswa, absensi kegiatan, dan
                            izin sakit. Tindakan ini tidak dapat dibatalkan.</p>
                        <div id="deleteAllProgress" class="d-none">
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%">
                                </div>
                            </div>
                            <small class="text-white-50 mt-2 d-block">Menghapus data...</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 p-4 pt-0">
                        <button type="button" class="btn das-btn --secondary" data-bs-dismiss="modal"
                            id="deleteAllCancelBtn">Batal</button>
                        <button type="button" class="btn das-btn --danger" id="deleteAllSubmitBtn">Hapus Semua</button>
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

            // initial tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection
