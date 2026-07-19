@extends('layouts/layoutMaster')

@section('title', 'Manajemen User')

@section('page-style')
    <style>
        .user-row-hover {
            transition: background 0.15s ease;
        }

        .user-row-hover:hover {
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
                        <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / User Management
                    </div>
                    <h4 class="das-hero__title text-gradient-gold">Manajemen User</h4>
                    <p class="das-hero__subtitle">Kelola seluruh akun pengguna, hak akses, dan pengaturan keamanan sistem.</p>
                </div>
            </div>

            <div class="das-hero__actions">
                <a href="{{ route('admin.users.create') }}" class="btn das-btn --primary">
                    <i class="ti tabler-plus me-1"></i> Tambah User Baru
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
                <div class="col-md-3">
                    <label class="form-label text-white-50 small fw-bold">Cari User</label>
                    <input type="text" id="filterSearch" name="search" class="form-control"
                        placeholder="Nama, Username, atau Email…" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white-50 small fw-bold">Filter Hak Akses</label>
                    <select id="filterRole" name="role" class="form-select">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $val => $label)
                            <option value="{{ $val }}" @selected(request('role') == $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white-50 small fw-bold">Tanggal Join (Mulai - Sampai)</label>
                    <div class="d-flex align-items-center gap-1">
                        <input type="date" id="startDate" name="start_date" class="form-control"
                            value="{{ request('start_date') }}" style="color-scheme: dark;">
                        <span class="text-white-50 small">s/d</span>
                        <input type="date" id="endDate" name="end_date" class="form-control"
                            value="{{ request('end_date') }}" style="color-scheme: dark;">
                    </div>
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
                <i class="ti tabler-list text-info"></i> Daftar User
            </h6>
            <div class="d-flex align-items-center gap-3">
                <select id="perPageSelect" class="form-select border-0 text-white w-auto"
                    style="background: rgba(255,255,255,0.05);height:38px;font-size:0.85rem;cursor:pointer;">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>

                <span class="das-chip --info d-none d-sm-inline-flex">{{ $users->total() }} User</span>
            </div>
        </div>
        <div class="das-panel__body p-0">
            <div id="userTableContainer">
                @include('admin.users.table')
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
                    <p class="mb-0">Anda akan masuk ke dalam akun <b id="impersonateUserName" class="text-warning"></b>. Seluruh aktivitas akan dicatat dalam log sistem.</p>
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

            const container = document.getElementById('userTableContainer');
            const perPageSelect = document.getElementById('perPageSelect');
            const filterSearch = document.getElementById('filterSearch');
            const filterRole = document.getElementById('filterRole');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const filterForm = document.getElementById('filterForm');
            const resetFilterBtn = document.getElementById('resetFilterBtn');
            let searchTimeout;

            let currentSortBy = '{{ $sortBy ?? 'name' }}';
            let currentSortDir = '{{ $sortDir ?? 'asc' }}';

            function fetchData(page = 1) {
                const search = encodeURIComponent(filterSearch.value || '');
                const perPage = perPageSelect.value || 10;
                const role = filterRole.value || '';
                const sd = startDate.value || '';
                const ed = endDate.value || '';
                const url = `{{ route('admin.users.index') }}?page=${page}&search=${search}&per_page=${perPage}&sort_by=${currentSortBy}&sort_dir=${currentSortDir}&role=${role}&start_date=${sd}&end_date=${ed}`;

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

            if (filterRole) {
                filterRole.addEventListener('change', function() {
                    fetchData(1);
                });
            }

            if (startDate) {
                startDate.addEventListener('change', function() {
                    fetchData(1);
                });
            }

            if (endDate) {
                endDate.addEventListener('change', function() {
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
                    if (filterRole) filterRole.value = '';
                    if (startDate) startDate.value = '';
                    if (endDate) endDate.value = '';
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

            // Impersonate and Delete handlers
            container.addEventListener('click', function(e) {
                // Impersonate handler
                const btnImpersonate = e.target.closest('.btn-impersonate-user');
                if (btnImpersonate) {
                    const url = btnImpersonate.dataset.url;
                    const nama = btnImpersonate.dataset.nama || 'User';

                    document.getElementById('impersonateUserName').textContent = nama;
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

                // Delete handler
                const btn = e.target.closest('.btn-hapus-user');
                if (!btn) return;

                const url = btn.dataset.url;
                const nama = btn.dataset.nama || 'user ini';

                Swal.fire({
                    title: 'Hapus User?',
                    html: `<div class="mt-2">Data <b class="text-danger">"${nama}"</b> akan dihapus secara permanen beserta data terkait.</div>`,
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
                                    text: data.message || 'User berhasil dihapus.',
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
                            console.error('Delete user error:', err);
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

            // Initialize tooltips on page load
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

        });
    </script>
@endsection
