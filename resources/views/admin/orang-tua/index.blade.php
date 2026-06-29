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
                <a href="{{ route('admin.orang-tua.create') }}" class="btn das-btn --primary">
                    <i class="ti tabler-plus me-1"></i> Tambah Orang Tua
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

@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: `Data Orang Tua "${nama}" akan dihapus permanen!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ea5455',
                        cancelButtonColor: '#82868b',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'das-modal border-0 shadow-lg text-white',
                            confirmButton: 'btn btn-danger',
                            cancelButton: 'btn btn-secondary ms-2'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.setAttribute('method', 'POST');
                            form.setAttribute('action', url);

                            const csrfToken = document.createElement('input');
                            csrfToken.setAttribute('type', 'hidden');
                            csrfToken.setAttribute('name', '_token');
                            csrfToken.setAttribute('value', '{{ csrf_token() }}');
                            form.appendChild(csrfToken);

                            const method = document.createElement('input');
                            method.setAttribute('type', 'hidden');
                            method.setAttribute('name', '_method');
                            method.setAttribute('value', 'DELETE');
                            form.appendChild(method);

                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>
@endsection
