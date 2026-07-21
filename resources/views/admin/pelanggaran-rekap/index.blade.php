@extends('layouts/layoutMaster')

@section('title', 'Rekap Pelanggaran Siswa')

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

        .hover-bg-primary-light:hover {
            background: rgba(115, 103, 240, 0.1) !important;
            border-color: rgba(115, 103, 240, 0.4) !important;
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
                        <i class="ti tabler-shield-alert text-danger"></i>
                    </div>
                    <div class="das-hero__logo-glow"></div>
                </div>

                <div class="das-hero__meta">
                    <div class="das-hero__badge">
                        <span class="pulse-dot"></span>
                        <a href="javascript:void(0)" class="text-white text-decoration-none">Point Pelanggaran</a> /
                        Rekap
                    </div>
                    <h4 class="das-hero__title text-gradient-gold">Rekap Pelanggaran Siswa</h4>
                    <p class="das-hero__subtitle">Kelola dan pantau akumulasi point serta status surat peringatan (SP) siswa.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER PANEL --}}
    <div class="das-panel mb-4">
        <div class="das-panel__body">
            <form id="filterForm" method="GET" class="row gy-3 gx-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-white-50 small fw-bold">Cari Siswa</label>
                    <input type="text" id="filterSearch" name="search" class="form-control"
                        placeholder="Nama atau NIS..." value="{{ request('search') }}">
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
                    <label class="form-label text-white-50 small fw-bold">Filter Level SP</label>
                    <select id="filterLevelSp" name="level_sp" class="form-select">
                        <option value="">Semua SP</option>
                        <option value="SP1" @selected(request('level_sp') === 'SP1')>SP 1</option>
                        <option value="SP2" @selected(request('level_sp') === 'SP2')>SP 2</option>
                        <option value="SP3" @selected(request('level_sp') === 'SP3')>SP 3</option>
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
                <i class="ti tabler-list text-info"></i> Daftar Rekap Pelanggaran Siswa
            </h6>
        </div>
        <div class="das-panel__body p-0">
            <div id="rekapTableContainer">
                @include('admin.pelanggaran-rekap.table')
            </div>
        </div>
    </div>

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm = document.getElementById('filterForm');
            const rekapTableContainer = document.getElementById('rekapTableContainer');

            function fetchFilteredData() {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData).toString();
                
                rekapTableContainer.style.opacity = '0.5';
                
                fetch(`{{ route('admin.pelanggaran-siswa.rekap') }}?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    rekapTableContainer.innerHTML = html;
                    rekapTableContainer.style.opacity = '1';
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    rekapTableContainer.style.opacity = '1';
                });
            }

            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                fetchFilteredData();
            });

            document.getElementById('resetFilterBtn').addEventListener('click', function () {
                document.getElementById('filterSearch').value = '';
                document.getElementById('filterKelas').value = '';
                document.getElementById('filterLevelSp').value = '';
                fetchFilteredData();
            });

            // AJAX Pagination delegation
            rekapTableContainer.addEventListener('click', function (e) {
                const pageLink = e.target.closest('.pagination a');
                if (pageLink) {
                    e.preventDefault();
                    const url = new URL(pageLink.href);
                    const page = url.searchParams.get('page');
                    
                    const formData = new FormData(filterForm);
                    formData.append('page', page);
                    const params = new URLSearchParams(formData).toString();

                    rekapTableContainer.style.opacity = '0.5';

                    fetch(`{{ route('admin.pelanggaran-siswa.rekap') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        rekapTableContainer.innerHTML = html;
                        rekapTableContainer.style.opacity = '1';
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        rekapTableContainer.style.opacity = '1';
                    });
                }
            });
        });
    </script>
@endsection

@endsection
