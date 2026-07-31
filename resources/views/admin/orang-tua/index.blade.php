@extends('layouts/layoutMaster')

@section('title', 'Orang Tua')

@section('page-style')
    <style>
        .ortu-row-hover {
            transition: background 0.15s ease;
        }

        .ortu-row-hover:hover {
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

        .form-control::placeholder {
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

        input[type="radio"]:checked+.hover-bg-primary-light .radio-indicator i {
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

    {{-- HERO HEADER --}}
    <div class="das-hero mb-4">
        <div class="das-hero__bg"></div>
        <div class="das-hero__glass"></div>
        <div class="das-hero__grid-lines"></div>

        <div class="das-hero__inner">
            <div class="das-hero__identity">
                <div class="das-hero__logo-wrapper">
                    <div class="das-hero__logo-placeholder">
                        <i class="ti tabler-user-heart text-info"></i>
                    </div>
                    <div class="das-hero__logo-glow"></div>
                </div>

                <div class="das-hero__meta">
                    <div class="das-hero__badge">
                        <span class="pulse-dot"></span>
                        <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Orang Tua
                    </div>
                    <h4 class="das-hero__title text-gradient-gold">Data Orang Tua</h4>
                    <p class="das-hero__subtitle">Kelola seluruh data orang tua / wali siswa beserta akun login portal orang tua.</p>
                </div>
            </div>

            <div class="das-hero__actions">
                <button type="button" class="btn das-btn --warning" id="btnSyncOrtu">
                    <i class="ti tabler-refresh me-1"></i> Sync Data
                </button>
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
                                    <div class="fw-bold text-white small">Cek Massal Semua Ortu</div>
                                    <div class="extra-small text-white-50">Validasi seluruh ortu via progress</div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn das-btn --success" id="btnRegeneratePhone">
                    <i class="ti tabler-phone me-1"></i> Generate Format WA
                </button>
                <button type="button" class="btn das-btn --info" id="btnResetPasswordAll">
                    <i class="ti tabler-key me-1"></i> Reset PW Massal
                </button>
                <button type="button" class="btn das-btn --danger" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                    <i class="ti tabler-trash me-1"></i> Hapus Semua
                </button>
                <a href="{{ route('admin.orang-tua.create') }}" class="btn das-btn --primary">
                    <i class="ti tabler-plus me-1"></i> Tambah Ortu
                </a>
            </div>
        </div>
    </div>

    {{-- FILTER PANEL --}}
    <div class="das-panel mb-4">
        <div class="das-panel__body">
            <form id="filterForm" method="GET" class="row gy-3 gx-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-white-50 small fw-bold">Cari Orang Tua</label>
                    <input type="text" id="filterSearch" name="search" class="form-control"
                        placeholder="Nama ortu, email, username, No. HP, nama siswa..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white-50 small fw-bold">Status</label>
                    <select id="filterStatus" name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-4">
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

    {{-- TABLE DATA --}}
    <div class="das-panel">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
            style="border-color:rgba(255,255,255,0.08) !important;">
            <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
                <i class="ti tabler-list text-info"></i> Daftar Orang Tua
            </h6>
            <div class="d-flex align-items-center gap-3">
                <select id="perPageSelect" class="form-select border-0 text-white w-auto"
                    style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </div>
        <div class="das-panel__body p-0">
            <div id="ortuTableContainer">
                @include('admin.orang-tua.table')
            </div>
        </div>
    </div>

    <!-- Modal Delete All -->
    <div class="modal fade" id="deleteAllModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-head d-flex align-items-center justify-content-between">
                    <h5 class="das-modal-title"><i class="ti tabler-trash me-2 text-danger"></i> Konfirmasi Hapus Semua</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="deleteAllForm" action="{{ route('admin.orang-tua.destroy-all') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="das-modal-body">
                        <p class="mb-3 text-white">Apakah Anda yakin ingin menghapus <strong>semua data Orang Tua</strong>?</p>
                        <ul class="text-danger ps-3 mb-0 small">
                            <li>Semua akun orang tua akan dihapus dari sistem.</li>
                            <li>Relasi wali di tabel Siswa akan dikosongkan.</li>
                            <li>Tindakan ini tidak dapat dibatalkan!</li>
                        </ul>
                    </div>
                    <div class="d-flex justify-content-end gap-2 p-4 pt-0">
                        <button type="button" class="btn btn-label-secondary w-50" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger w-50">Ya, Hapus Semua</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sync Data Ortu -->
    <div class="modal fade" id="syncModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-head d-flex align-items-center justify-content-between">
                    <h5 class="das-modal-title"><i class="ti tabler-refresh me-2 text-warning"></i> Sinkronisasi Data?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="das-modal-body">
                    <p class="mb-0 text-white">Aksi ini akan membuat akun orang tua otomatis untuk siswa yang belum punya wali, dan merapikan relasinya.</p>
                </div>
                <div class="d-flex justify-content-end gap-2 p-4 pt-0">
                    <button type="button" class="btn btn-label-secondary w-50" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="confirmSyncBtn" class="btn btn-warning w-50 text-dark fw-bold">Ya, Sinkronkan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reset Password Massal -->
    <div class="modal fade" id="resetPasswordAllModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-head d-flex align-items-center justify-content-between">
                    <h5 class="das-modal-title"><i class="ti tabler-key me-2 text-info"></i> Reset Password Massal?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="das-modal-body">
                    <p class="mb-0 text-white">Semua password akun Orang Tua akan di-reset menjadi NISN anak masing-masing (atau password123 jika NISN kosong)!</p>
                </div>
                <div class="d-flex justify-content-end gap-2 p-4 pt-0">
                    <button type="button" class="btn btn-label-secondary w-50" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="confirmResetPasswordAllBtn" class="btn btn-info w-50 text-white fw-bold">Ya, Reset Semua!</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Delete Individual -->
    <div class="modal fade" id="deleteIndividualModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-head d-flex align-items-center justify-content-between">
                    <h5 class="das-modal-title"><i class="ti tabler-alert-triangle me-2 text-danger"></i> Apakah Anda yakin?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="deleteIndividualForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="das-modal-body">
                        <p class="mb-0 text-white">Data Orang Tua <strong id="deleteOrtuName" class="text-warning"></strong> akan dihapus permanen!</p>
                    </div>
                    <div class="d-flex justify-content-end gap-2 p-4 pt-0">
                        <button type="button" class="btn btn-label-secondary w-50" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger w-50">Ya, Hapus!</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Impersonate Ortu -->
    <div class="modal fade" id="impersonateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-head d-flex align-items-center justify-content-between">
                    <h5 class="das-modal-title"><i class="ti tabler-login me-2 text-success"></i> Konfirmasi Login As</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="das-modal-body">
                    <p class="mb-0 text-white">Anda akan masuk ke dalam akun <b id="impersonateOrtuName" class="text-warning"></b>. Seluruh aktivitas akan dicatat dalam log sistem.</p>
                </div>
                <div class="d-flex justify-content-end gap-2 p-4 pt-0">
                    <button type="button" class="btn btn-label-secondary w-50" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="confirmImpersonateBtn" class="btn btn-success w-50">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reset Password Individual -->
    <div class="modal fade" id="resetPwIndividualModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-head d-flex align-items-center justify-content-between">
                    <h5 class="das-modal-title"><i class="ti tabler-key me-2 text-primary"></i> Reset Password?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="das-modal-body">
                    <p class="mb-0 text-white">Password untuk Orang Tua <strong id="resetPwOrtuName" class="text-warning"></strong> akan di-reset ke password default (NISN anak / password123)!</p>
                </div>
                <div class="d-flex justify-content-end gap-2 p-4 pt-0">
                    <button type="button" class="btn btn-label-secondary w-50" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="confirmResetPwIndividualBtn" class="btn btn-primary w-50">Ya, Reset!</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Processing / Loading -->
    <div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-body p-5 text-center text-white">
                    <div class="spinner-border text-primary mb-4" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="mb-2 text-white" id="processingTitle">Memproses...</h5>
                    <p class="mb-0 text-white-50" id="processingMessage">Mohon tunggu sebentar, jangan tutup halaman ini.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Result (Success / Error / Alert) -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content das-modal shadow-lg">
                <div class="das-modal-body p-5 text-center text-white">
                    <div class="mb-4" id="resultIconContainer">
                        <!-- Icon will be set dynamically via JS -->
                    </div>
                    <h5 class="mb-2 text-white" id="resultTitle">Hasil</h5>
                    <p class="mb-4 text-white-50" id="resultMessage"></p>
                    <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Tutup</button>
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
            // Helper functions for custom modal dialogs
            function showProcessing(title, message) {
                document.getElementById('processingTitle').textContent = title || 'Memproses...';
                document.getElementById('processingMessage').textContent = message || 'Mohon tunggu sebentar, jangan tutup halaman ini.';
                const modalEl = document.getElementById('processingModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }

            function hideProcessing() {
                const modalEl = document.getElementById('processingModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }

            function showResult(success, title, message, callback) {
                const iconContainer = document.getElementById('resultIconContainer');
                if (success) {
                    iconContainer.innerHTML = `<span class="d-inline-flex align-items-center justify-content-center bg-label-success rounded-circle p-3 mb-2 animate__animated animate__bounceIn">
                        <i class="ti tabler-circle-check text-success" style="font-size: 3rem;"></i>
                    </span>`;
                    document.getElementById('resultTitle').className = 'mb-2 text-success fw-bold';
                } else {
                    iconContainer.innerHTML = `<span class="d-inline-flex align-items-center justify-content-center bg-label-danger rounded-circle p-3 mb-2 animate__animated animate__shakeX">
                        <i class="ti tabler-circle-x text-danger" style="font-size: 3rem;"></i>
                    </span>`;
                    document.getElementById('resultTitle').className = 'mb-2 text-danger fw-bold';
                }
                
                document.getElementById('resultTitle').textContent = title;
                document.getElementById('resultMessage').textContent = message;
                
                const modalEl = document.getElementById('resultModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
                
                if (callback) {
                    const handleHidden = () => {
                        callback();
                        modalEl.removeEventListener('hidden.bs.modal', handleHidden);
                    };
                    modalEl.addEventListener('hidden.bs.modal', handleHidden);
                }
            }

            // Delete All Logic
            const deleteAllForm = document.getElementById('deleteAllForm');
            if (deleteAllForm) {
                deleteAllForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const btnSubmit = this.querySelector('button[type="submit"]');
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<i class="ti tabler-loader animate-spin me-1"></i> Menghapus...';
                    
                    fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(this)
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Tutup modal
                        const modalEl = document.getElementById('deleteAllModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        
                        if (data.success) {
                            showResult(true, 'Berhasil!', data.message, () => {
                                fetchData(1); // Refresh data table
                            });
                        } else {
                            showResult(false, 'Oops...', data.message || 'Terjadi kesalahan!');
                        }
                    })
                    .catch(error => {
                        const modalEl = document.getElementById('deleteAllModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        
                        showResult(false, 'Error!', 'Gagal terhubung ke server.');
                    })
                    .finally(() => {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = 'Ya, Hapus Semua';
                    });
                });
            }

            // Sync Logic
            const btnSync = document.getElementById('btnSyncOrtu');
            if (btnSync) {
                btnSync.addEventListener('click', function() {
                    const syncModalEl = document.getElementById('syncModal');
                    const syncModal = bootstrap.Modal.getOrCreateInstance(syncModalEl);
                    syncModal.show();
                });
            }

            const confirmSyncBtn = document.getElementById('confirmSyncBtn');
            if (confirmSyncBtn) {
                confirmSyncBtn.addEventListener('click', function() {
                    const syncModalEl = document.getElementById('syncModal');
                    const syncModal = bootstrap.Modal.getInstance(syncModalEl);
                    if (syncModal) syncModal.hide();

                    showProcessing('Sinkronisasi berjalan...', 'Mohon tunggu, jangan tutup halaman ini.');

                    fetch("{{ route('admin.orang-tua.sync') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideProcessing();
                        if (data.success) {
                            showResult(true, 'Sinkronisasi Selesai!', data.message, () => {
                                fetchData(1);
                            });
                        } else {
                            showResult(false, 'Gagal!', data.message || 'Gagal mensinkronkan data.');
                        }
                    })
                    .catch(error => {
                        hideProcessing();
                        showResult(false, 'Error!', 'Terjadi kesalahan sistem.');
                    });
                });
            }

            // Reset Password Massal
            const btnResetPasswordAll = document.getElementById('btnResetPasswordAll');
            if (btnResetPasswordAll) {
                btnResetPasswordAll.addEventListener('click', function() {
                    const resetModalEl = document.getElementById('resetPasswordAllModal');
                    const resetModal = bootstrap.Modal.getOrCreateInstance(resetModalEl);
                    resetModal.show();
                });
            }

            const confirmResetPasswordAllBtn = document.getElementById('confirmResetPasswordAllBtn');
            if (confirmResetPasswordAllBtn) {
                confirmResetPasswordAllBtn.addEventListener('click', function() {
                    const resetModalEl = document.getElementById('resetPasswordAllModal');
                    const resetModal = bootstrap.Modal.getInstance(resetModalEl);
                    if (resetModal) resetModal.hide();

                    showProcessing('Memproses Reset...', 'Mohon tunggu, jangan tutup halaman ini.');

                    fetch("{{ route('admin.orang-tua.reset-password-all') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideProcessing();
                        if (data.success) {
                            showResult(true, 'Reset Selesai!', data.message, () => {
                                fetchData(1);
                            });
                        } else {
                            showResult(false, 'Gagal!', data.message || 'Gagal me-reset password massal.');
                        }
                    })
                    .catch(error => {
                        hideProcessing();
                        showResult(false, 'Error!', 'Terjadi kesalahan sistem.');
                    });
                });
            }

            const container = document.getElementById('ortuTableContainer');
            const perPageSelect = document.getElementById('perPageSelect');
            const filterSearch = document.getElementById('filterSearch');
            const filterStatus = document.getElementById('filterStatus');
            const filterForm = document.getElementById('filterForm');
            const resetFilterBtn = document.getElementById('resetFilterBtn');
            let searchTimeout;

            let currentSortBy = '{{ $sortBy ?? 'name' }}';
            let currentSortDir = '{{ $sortDir ?? 'asc' }}';

            function fetchData(page = 1) {
                const search = encodeURIComponent(filterSearch.value || '');
                const perPage = perPageSelect.value || 10;
                const status = filterStatus ? filterStatus.value || '' : '';
                const url = `{{ route('admin.orang-tua.index') }}?page=${page}&search=${search}&per_page=${perPage}&sort_by=${currentSortBy}&sort_dir=${currentSortDir}&status=${status}`;

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
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
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

            // filter status change
            if (filterStatus) {
                filterStatus.addEventListener('change', function() {
                    fetchData(1);
                });
            }

            // form submit
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    fetchData(1);
                });
            }

            // reset button
            if (resetFilterBtn) {
                resetFilterBtn.addEventListener('click', function() {
                    if (filterSearch) filterSearch.value = '';
                    if (filterStatus) filterStatus.value = '';
                    fetchData(1);
                });
            }

            perPageSelect.addEventListener('change', function() {
                fetchData(1);
            });

            // sort clicks - delegated
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

            // pagination clicks
            container.addEventListener('click', function(e) {
                const pageBtn = e.target.closest('.das-page-btn');
                if (pageBtn && !pageBtn.classList.contains('das-page-active') && !pageBtn.parentNode.classList.contains('disabled')) {
                    e.preventDefault();
                    const page = pageBtn.getAttribute('data-page') || pageBtn.textContent.trim();
                    if (page && !isNaN(page)) {
                        fetchData(page);
                    }
                }
            });

            // Hapus Data
            document.addEventListener('click', function(e) {
                const btnHapus = e.target.closest('.btn-hapus-ortu');
                if (btnHapus) {
                    const url = btnHapus.getAttribute('data-url');
                    const nama = btnHapus.getAttribute('data-nama');

                    const deleteForm = document.getElementById('deleteIndividualForm');
                    if (deleteForm) {
                        deleteForm.setAttribute('action', url);
                    }
                    document.getElementById('deleteOrtuName').textContent = nama;

                    const modalEl = document.getElementById('deleteIndividualModal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            });

            // Reset Password Individu
            let resetPwUrl = '';
            document.addEventListener('click', function(e) {
                const btnReset = e.target.closest('.btn-reset-password-ortu');
                if (btnReset) {
                    resetPwUrl = btnReset.getAttribute('data-url');
                    const nama = btnReset.getAttribute('data-nama');

                    document.getElementById('resetPwOrtuName').textContent = nama;
                    const modalEl = document.getElementById('resetPwIndividualModal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            });

            const confirmResetPwIndividualBtn = document.getElementById('confirmResetPwIndividualBtn');
            if (confirmResetPwIndividualBtn) {
                confirmResetPwIndividualBtn.addEventListener('click', function() {
                    const modalEl = document.getElementById('resetPwIndividualModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    showProcessing('Memproses...', 'Mohon tunggu.');

                    fetch(resetPwUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideProcessing();
                        if (data.success) {
                            showResult(true, 'Berhasil!', data.message, () => {
                                fetchData(1);
                            });
                        } else {
                            showResult(false, 'Gagal!', data.message || 'Gagal me-reset password.');
                        }
                    })
                    .catch(error => {
                        hideProcessing();
                        showResult(false, 'Error!', 'Terjadi kesalahan sistem.');
                    });
                });
            }

            // Impersonate
            let impersonateUrl = '';
            document.addEventListener('click', function(e) {
                const btnImpersonate = e.target.closest('.btn-impersonate-ortu');
                if (btnImpersonate) {
                    impersonateUrl = btnImpersonate.getAttribute('data-url');
                    const nama = btnImpersonate.getAttribute('data-nama');

                    document.getElementById('impersonateOrtuName').textContent = nama;
                    const modalEl = document.getElementById('impersonateModal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            });

            const confirmImpersonateBtn = document.getElementById('confirmImpersonateBtn');
            if (confirmImpersonateBtn) {
                confirmImpersonateBtn.addEventListener('click', function() {
                    const modalEl = document.getElementById('impersonateModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    confirmImpersonateBtn.disabled = true;
                    confirmImpersonateBtn.innerHTML = '<i class="ti tabler-loader spinner me-1"></i> Memproses...';

                    // Create form element dynamically to do a POST request
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = impersonateUrl;

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrfInput);

                    document.body.appendChild(form);
                    form.submit();
                });
            }

            const btnRegeneratePhone = document.getElementById('btnRegeneratePhone');
            if (btnRegeneratePhone) {
                btnRegeneratePhone.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Generate Format WA?',
                        html: `<div class="mt-2 text-white-50">Format seluruh nomor WA Orang Tua (08...) akan dikonversi ke standar internasional (<b class="text-success">628...</b>).</div>`,
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
                            fetch("{{ route('admin.orang-tua.regenerate-phone') }}", {
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
            // ═══════════════════════════════════════════════════════════════
            // BATCH WA VALIDITY CHECKER FOR ORANG TUA TABLE
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
                        <div style="padding: 16px 8px;">
                            <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
                                <div style="width:48px; height:48px; border-radius:12px; background:linear-gradient(135deg,#25d366,#128c7e); display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 4px 15px rgba(37,211,102,0.4);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.558 4.122 1.532 5.85L.058 23.5l5.797-1.498A11.954 11.954 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.89 0-3.663-.52-5.178-1.427l-.37-.22-3.44.889.914-3.35-.24-.386A9.961 9.961 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                                </div>
                                <div style="text-align:left;">
                                    <div style="font-size:1rem; font-weight:700; color:#fff; line-height:1.2;">Verifikasi WA Massal</div>
                                    <div style="font-size:0.75rem; color:rgba(255,255,255,0.5); margin-top:2px;">Memproses seluruh data orang tua di database...</div>
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
                    width: '520px',
                    padding: '2rem',
                    background: '#1e2130',
                    customClass: {
                        popup: 'border-0 shadow-lg',
                        cancelButton: 'swal2-cancel',
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

                            Object.keys(data.results || {}).forEach(num => {
                                if (data.results[num]) validTotal++;
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
                                    text: `Selesai memverifikasi seluruh ${total} data orang tua. Ditemukan ${validTotal} nomor terdaftar di WhatsApp.`,
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
                    runBulkWaCheck('orang_tua');
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
