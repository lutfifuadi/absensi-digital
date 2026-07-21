@extends('layouts/layoutMaster')

@section('title', 'Guru')

@section('page-style')
    <style>
        .guru-row-hover {
            transition: background 0.15s ease;
        }

        .guru-row-hover:hover {
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
                        <i class="ti tabler-school text-info"></i>
                    </div>
                    <div class="das-hero__logo-glow"></div>
                </div>

                <div class="das-hero__meta">
                    <div class="das-hero__badge">
                        <span class="pulse-dot"></span>
                        <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Guru
                    </div>
                    <h4 class="das-hero__title text-gradient-gold">Data Guru</h4>
                    <p class="das-hero__subtitle">Kelola seluruh data tenaga pendidik pada <span class="text-info fw-bold">{{ session('tahun_ajaran_id') ? 'Tahun Ajaran Aktif' : 'Pilih Tahun Ajaran' }}</span>.</p>
                </div>
            </div>

            <div class="das-hero__actions">
                <a href="{{ route('admin.pengaturan.google-sheets-guru.index') }}" class="btn das-btn --purple" title="GSheets Sync" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ti tabler-file-spreadsheet"></i>
                </a>
                <button type="button" class="btn das-btn --secondary" data-bs-toggle="modal" data-bs-target="#importModal" title="Import" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ti tabler-file-import"></i>
                </button>
                <div class="btn-group">
                    <button type="button" class="btn das-btn --success dropdown-toggle hide-arrow" data-bs-toggle="dropdown"
                        aria-expanded="false" title="Export" data-bs-toggle="tooltip" data-bs-placement="top">
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
                <a href="{{ route('admin.guru.cetak-qr') }}" class="btn das-btn --warning" title="Cetak QR" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ti tabler-qrcode"></i>
                </a>
                <button type="button" class="btn das-btn --danger d-none" id="btnDeleteBulk" title="Hapus Terpilih" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ti tabler-trash"></i> <span id="selectedGuruCount" class="badge bg-white text-danger ms-1" style="font-size: 0.7rem; padding: 2px 5px; border-radius: 4px;">0</span>
                </button>
                <button type="button" class="btn das-btn --danger" data-bs-toggle="modal" data-bs-target="#deleteAllGuruModal" title="Hapus Semua" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ti tabler-trash"></i>
                </button>
                <a href="{{ route('admin.guru.create') }}" class="btn das-btn --primary" title="Tambah Guru" data-bs-toggle="tooltip" data-bs-placement="top">
                    <i class="ti tabler-plus"></i>
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
                <div class="col-md-4">
                    <label class="form-label text-white-50 small fw-bold">Cari Guru</label>
                    <input type="text" id="filterSearch" name="search" class="form-control"
                        placeholder="Nama, NIP, atau Mata Pelajaran…" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white-50 small fw-bold">Filter Jabatan</label>
                    <select id="filterJabatan" name="jabatan" class="form-select">
                        <option value="">Semua Jabatan</option>
                        @foreach ($jabatanOptions ?? [] as $jab)
                            <option value="{{ $jab }}" @selected(request('jabatan') == $jab)>{{ $jab }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white-50 small fw-bold">Status</label>
                    <select id="filterStatus" name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
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
                <i class="ti tabler-list text-info"></i> Daftar Guru
            </h6>
            <div class="d-flex align-items-center gap-3">
                <select id="perPageSelect" class="form-select border-0 text-white w-auto"
                    style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>

                <span class="das-chip --info d-none d-sm-inline-flex">{{ $guruUsers->total() }} Guru</span>
            </div>
        </div>
        <div class="das-panel__body p-0">
            <div id="guruTableContainer">
                @include('admin.guru.table')
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
                    <p class="text-white-50 small mb-0">Silakan pilih Tahun Ajaran aktif untuk melihat data guru.</p>
                </div>
                <form action="{{ route('admin.set-tahun-akademik') }}" method="POST">
                    @csrf
                    <div class="das-modal-body py-4">
                        <div class="row g-3">
                            @forelse(\App\Models\TahunAkademik::orderBy('tanggal_mulai', 'desc')->get() as $thn)
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

    <!-- Modal Hapus Semua Guru -->
    <div class="modal fade" id="deleteAllGuruModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-head d-flex align-items-center justify-content-between">
                    <h5 class="das-modal-title"><i class="ti tabler-trash me-2 text-danger"></i> Hapus Semua Guru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="deleteAllForm" action="{{ route('admin.guru.destroy-all') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="das-modal-body">
                        <p class="mb-3">Semua data guru akan dihapus, termasuk akun user terkait. Tindakan ini tidak dapat dibatalkan.</p>
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
                    <h5 class="das-modal-title"><i class="ti tabler-file-import me-2 text-info"></i>Import Data Guru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="importForm" action="{{ route('admin.guru.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="importFormBody" class="das-modal-body">
                        <div class="mb-4">
                            <label class="form-label text-white-50" for="import_file">Pilih File Excel / CSV</label>
                            <input id="import_file" name="import_file" type="file"
                                class="form-control bg-dark border-secondary text-white" accept=".xlsx,.xls,.csv"
                                required>
                            <div class="form-text text-white-50 small mt-2">Gunakan format file Excel (.xlsx) atau CSV yang sesuai.</div>
                        </div>

                        <div class="alert alert-info border-0 shadow-sm"
                            style="background: rgba(0, 207, 232, 0.1); border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="mb-0 fw-bold text-info small"><i class="ti tabler-info-circle me-1"></i>Format
                                    Kolom:</p>
                                <a href="{{ route('admin.guru.download-sample') }}"
                                    class="btn btn-sm btn-label-info py-0 px-2" style="font-size: 0.65rem;">
                                    <i class="ti tabler-download me-1"></i> Download Sampel
                                </a>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach (['nip', 'nama_lengkap', 'jenis_kelamin', 'mata_pelajaran', 'jabatan', 'no_hp', 'status'] as $col)
                                    <span class="badge bg-label-info"
                                        style="font-size: 0.65rem;">{{ $col }}</span>
                                @endforeach
                            </div>
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

    <!-- Modal Konfirmasi Impersonate -->
    <div class="modal fade" id="impersonateConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content das-modal shadow-lg" style="border-radius: 5px !important;">
                <div class="das-modal-head py-3 px-4">
                    <h5 class="das-modal-title"><i class="ti tabler-login me-2 text-success"></i> Konfirmasi Login As</h5>
                </div>
                <div class="das-modal-body p-4 text-white">
                    <p class="mb-0">Anda akan masuk ke dalam akun <b id="impersonateGuruName" class="text-warning"></b>. Seluruh aktivitas akan dicatat dalam log sistem.</p>
                </div>
                <div class="d-flex justify-content-end gap-2 px-4 pb-4 pt-2">
                    <button type="button" class="btn btn-label-secondary w-50" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="confirmImpersonateBtn" class="btn btn-success w-50">Ya, Lanjutkan</button>
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

            const container = document.getElementById('guruTableContainer');
            const perPageSelect = document.getElementById('perPageSelect');
            const filterSearch = document.getElementById('filterSearch');
            const filterJabatan = document.getElementById('filterJabatan');
            const filterStatus = document.getElementById('filterStatus');
            const filterForm = document.getElementById('filterForm');
            const resetFilterBtn = document.getElementById('resetFilterBtn');
            let searchTimeout;

            let currentSortBy = '{{ $sortBy ?? 'nama_lengkap' }}';
            let currentSortDir = '{{ $sortDir ?? 'asc' }}';

            function fetchData(page = 1) {
                const search = encodeURIComponent(filterSearch.value || '');
                const perPage = perPageSelect.value || 10;
                const jabatan = filterJabatan.value || '';
                const status = filterStatus.value || '';
                const url = `{{ route('admin.guru.index') }}?page=${page}&search=${search}&per_page=${perPage}&sort_by=${currentSortBy}&sort_dir=${currentSortDir}&jabatan=${jabatan}&status=${status}`;

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

                        // Reset select all checkbox and bulk button state
                        updateBulkDeleteButtonState();

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

            if (filterJabatan) {
                filterJabatan.addEventListener('change', function() {
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
                    if (filterJabatan) filterJabatan.value = '';
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

            // Individual delete AJAX handler (delegated)
            container.addEventListener('click', function(e) {
                const btnImpersonate = e.target.closest('.btn-impersonate-guru');
                if (btnImpersonate) {
                    const url = btnImpersonate.dataset.url;
                    const nama = btnImpersonate.dataset.nama || 'Guru';

                    document.getElementById('impersonateGuruName').textContent = nama;
                    const modalEl = document.getElementById('impersonateConfirmModal');
                    const confirmBtn = document.getElementById('confirmImpersonateBtn');
                    
                    // Reset confirm button state
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Ya, Lanjutkan';

                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();

                    // Cleanup any existing click handler
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

                const btn = e.target.closest('.btn-hapus-guru');
                if (!btn) return;

                const url = btn.dataset.url;
                const nama = btn.dataset.nama || 'guru ini';

                Swal.fire({
                    title: 'Hapus Guru?',
                    html: `<div class="mt-2">Data <b class="text-danger">"${nama}"</b> akan dihapus secara permanen beserta akun user terkait.</div>`,
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
                                    text: data.message || 'Guru berhasil dihapus.',
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
                            console.error('Delete guru error:', err);
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

            // Delete All handler
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
                            const modalEl = document.getElementById('deleteAllGuruModal');
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message || 'Data guru telah dihapus.',
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
                            const modalEl = document.getElementById('deleteAllGuruModal');
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
                const url = `{{ route('admin.guru.export') }}?format=${format}&search=${search}`;
                window.location.href = url;
            }

            if (exportExcelBtn) {
                exportExcelBtn.addEventListener('click', () => handleExport('xlsx'));
            }
            if (exportCsvBtn) {
                exportCsvBtn.addEventListener('click', () => handleExport('csv'));
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
                        // Since guru doesn't have import progress endpoint, we'll show indeterminate
                        importProgressBar.style.width = '50%';
                        importProgressBar.textContent = '50%';
                        importProgressText.textContent = '50%';
                        importProgressDetail.textContent = 'Memproses data...';
                    }, 1000);

                    fetch(importForm.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(async res => {
                            const data = await res.json();
                            clearInterval(progressInterval);
                            
                            importSubmitBtn.disabled = false;
                            importSubmitBtn.innerHTML = '<i class="ti tabler-upload me-1"></i> Mulai Import';
                            importCancelBtn.disabled = false;
                            
                            if (res.ok && data.success) {
                                importProgressBar.style.width = '100%';
                                importProgressBar.textContent = 'Selesai!';
                                importProgressBar.classList.remove('progress-bar-animated');
                                importProgressBar.style.background = 'linear-gradient(135deg, #28c76f, #00d25a)';
                                importProgressDetail.textContent = data.message || 'Import berhasil.';

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message || 'Data guru berhasil diimport.',
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

            // ─── Bulk Delete Handlers ──────────────────────────────────────────
            function updateBulkDeleteButtonState() {
                const checkedBoxes = document.querySelectorAll('.select-guru-cb:checked');
                const checkedCount = checkedBoxes.length;
                const btn = document.getElementById('btnDeleteBulk');
                const countSpan = document.getElementById('selectedGuruCount');
                
                if (btn && countSpan) {
                    if (checkedCount > 0) {
                        countSpan.textContent = checkedCount;
                        btn.classList.remove('d-none');
                    } else {
                        btn.classList.add('d-none');
                    }
                }
            }

            // Select All listener
            document.addEventListener('change', function(e) {
                if (e.target && e.target.id === 'selectAllGuru') {
                    const isChecked = e.target.checked;
                    document.querySelectorAll('.select-guru-cb').forEach(cb => {
                        cb.checked = isChecked;
                    });
                    updateBulkDeleteButtonState();
                }
            });

            // Individual checkbox listener
            document.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('select-guru-cb')) {
                    const allCbs = document.querySelectorAll('.select-guru-cb');
                    const checkedCbs = document.querySelectorAll('.select-guru-cb:checked');
                    const selectAll = document.getElementById('selectAllGuru');
                    if (selectAll) {
                        selectAll.checked = allCbs.length === checkedCbs.length && allCbs.length > 0;
                    }
                    updateBulkDeleteButtonState();
                }
            });

            // Bulk Delete Click handler
            const btnDeleteBulk = document.getElementById('btnDeleteBulk');
            if (btnDeleteBulk) {
                btnDeleteBulk.addEventListener('click', function() {
                    const checkedBoxes = document.querySelectorAll('.select-guru-cb:checked');
                    const ids = [];
                    checkedBoxes.forEach(cb => ids.push(cb.value));

                    if (ids.length === 0) return;

                    Swal.fire({
                        title: 'Hapus Guru Terpilih?',
                        html: `<div class="mt-2">Apakah Anda yakin ingin menghapus <b class="text-danger">${ids.length} guru</b> yang dipilih secara permanen beserta akun user terkait?</div>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus Terpilih',
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

                        btnDeleteBulk.disabled = true;
                        btnDeleteBulk.innerHTML = '<i class="ti tabler-loader spinner me-1"></i> Menghapus...';

                        fetch('{{ route("admin.guru.destroy-bulk") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ ids: ids })
                        })
                        .then(res => res.json())
                        .then(data => {
                            btnDeleteBulk.disabled = false;
                            btnDeleteBulk.innerHTML = '<i class="ti tabler-trash me-1"></i> Hapus Terpilih (<span id="selectedGuruCount">0</span>)';
                            
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message || 'Guru terpilih berhasil dihapus.',
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
                        })
                        .catch(err => {
                            btnDeleteBulk.disabled = false;
                            btnDeleteBulk.innerHTML = '<i class="ti tabler-trash me-1"></i> Hapus Terpilih (<span id="selectedGuruCount">0</span>)';
                            console.error('Bulk delete error:', err);
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
                                background: 'transparent',
                                buttonsStyling: false
                            });
                        });
                    });
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
