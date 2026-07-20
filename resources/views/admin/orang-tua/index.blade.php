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

    {{-- TABLE DATA --}}
    <div class="das-panel">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
            style="border-color:rgba(255,255,255,0.08) !important;">
            <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
                <i class="ti tabler-list text-info"></i> Daftar Orang Tua
            </h6>
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative" style="max-width:300px;">
                    <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"
                        style="font-size:0.85rem; pointer-events:none;"></i>
                    <input type="text" id="searchInput" class="form-control border-0 text-white"
                        placeholder="Cari nama, email, username..." value="{{ request('search') }}"
                        style="background: rgba(255,255,255,0.05); height:38px; padding-left:2.2rem; font-size:0.85rem;">
                </div>

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
            const searchInput = document.getElementById('searchInput');
            const perPageSelect = document.getElementById('perPageSelect');
            let searchTimeout;

            function fetchData(page = 1) {
                const search = encodeURIComponent(searchInput.value || '');
                const perPage = perPageSelect.value || 10;
                const url = `{{ route('admin.orang-tua.index') }}?page=${page}&search=${search}&per_page=${perPage}`;

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

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    fetchData(1);
                }, 400);
            });

            perPageSelect.addEventListener('change', function() {
                fetchData(1);
            });

            // Pagination Ajax
            document.addEventListener('click', function(e) {
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
        });
    </script>
@endsection
