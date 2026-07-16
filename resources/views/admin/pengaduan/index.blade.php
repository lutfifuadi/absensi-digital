@extends('layouts/layoutMaster')

@section('title', 'Daftar Pengaduan')

@section('page-style')
<style>
    .pengaduan-row-hover {
        transition: background 0.15s ease;
    }
    .pengaduan-row-hover:hover {
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

    .stat-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 5px;
        padding: 1rem 1.25rem;
        transition: all 0.2s ease;
    }
    .stat-card:hover {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(255, 255, 255, 0.12);
        transform: translateY(-2px);
    }
    .stat-card .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .stat-card .stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.6;
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

    .filter-select {
        background: rgba(255, 255, 255, 0.05);
        height: 38px;
        font-size: 0.85rem;
        cursor: pointer;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .filter-select:focus {
        outline: none;
        box-shadow: none;
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: rgba(115, 103, 240, 0.5) !important;
    }
    .filter-select option {
        background: #1a1a2e;
        color: #ccc;
    }

    .date-input {
        background: rgba(255, 255, 255, 0.05);
        height: 38px;
        font-size: 0.85rem;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .date-input:focus {
        outline: none;
        box-shadow: none;
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: rgba(115, 103, 240, 0.5) !important;
    }
    .date-input::-webkit-calendar-picker-indicator {
        filter: invert(0.7);
    }

    /* Badge status colors - more vibrant */
    .badge-status-baru {
        background: rgba(255, 159, 67, 0.15) !important;
        color: #ff9f43 !important;
        border: 1px solid rgba(255, 159, 67, 0.25);
    }
    .badge-status-diproses {
        background: rgba(0, 207, 232, 0.15) !important;
        color: #00cfe8 !important;
        border: 1px solid rgba(0, 207, 232, 0.25);
    }
    .badge-status-selesai {
        background: rgba(40, 199, 111, 0.15) !important;
        color: #28c76f !important;
        border: 1px solid rgba(40, 199, 111, 0.25);
    }
    .badge-status-ditolak {
        background: rgba(234, 84, 85, 0.15) !important;
        color: #ea5455 !important;
        border: 1px solid rgba(234, 84, 85, 0.25);
    }

    .pulse-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #28c76f;
        display: inline-block;
        animation: pulse-dot 2s infinite;
        margin-right: 6px;
    }
    @keyframes pulse-dot {
        0% { opacity: 1; }
        50% { opacity: 0.4; }
        100% { opacity: 1; }
    }

    .text-gradient-gold {
        background: linear-gradient(135deg, #f5af19, #f12711);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════════════
     HERO HEADER
═══════════════════════════════════════════════════════ --}}
<div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
        <div class="das-hero__identity">
            <div class="das-hero__logo-wrapper">
                <div class="das-hero__logo-placeholder">
                    <i class="ti tabler-report text-warning"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>

            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot"></span>
                    <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Layanan</a> /
                    Pengaduan
                </div>
                <h4 class="das-hero__title text-gradient-gold">Layanan Pengaduan Data Tidak Valid</h4>
                <p class="das-hero__subtitle">Kelola dan verifikasi laporan pengaduan data yang tidak valid dari siswa
                    maupun orang tua.</p>
            </div>
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

{{-- STATISTIC CARDS --}}
<div class="row g-3 mb-4" id="pengaduanStatsGrid">
    <div class="col-6 col-md">
        <div class="stat-card d-flex align-items-center gap-3" style="cursor: pointer;" data-status="semua">
            <div class="stat-icon" style="background: rgba(115, 103, 240, 0.12); color: #7367f0;">
                <i class="ti tabler-list"></i>
            </div>
            <div>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card d-flex align-items-center gap-3" style="cursor: pointer;" data-status="baru">
            <div class="stat-icon" style="background: rgba(255, 159, 67, 0.12); color: #ff9f43;">
                <i class="ti tabler-clock"></i>
            </div>
            <div>
                <div class="stat-value">{{ $stats['baru'] }}</div>
                <div class="stat-label">Baru</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card d-flex align-items-center gap-3" style="cursor: pointer;" data-status="diproses">
            <div class="stat-icon" style="background: rgba(0, 207, 232, 0.12); color: #00cfe8;">
                <i class="ti tabler-refresh"></i>
            </div>
            <div>
                <div class="stat-value">{{ $stats['diproses'] }}</div>
                <div class="stat-label">Diproses</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card d-flex align-items-center gap-3" style="cursor: pointer;" data-status="selesai">
            <div class="stat-icon" style="background: rgba(40, 199, 111, 0.12); color: #28c76f;">
                <i class="ti tabler-check"></i>
            </div>
            <div>
                <div class="stat-value">{{ $stats['selesai'] }}</div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card d-flex align-items-center gap-3" style="cursor: pointer;" data-status="ditolak">
            <div class="stat-icon" style="background: rgba(234, 84, 85, 0.12); color: #ea5455;">
                <i class="ti tabler-x"></i>
            </div>
            <div>
                <div class="stat-value">{{ $stats['ditolak'] }}</div>
                <div class="stat-label">Ditolak</div>
            </div>
        </div>
    </div>
</div>

{{-- FILTER & TABLE CARD --}}
<div class="das-panel" id="pengaduanTableCard">
    <div class="das-panel__header border-bottom py-3 px-4"
        style="border-color:rgba(255,255,255,0.08) !important;">

        {{-- Filter Baris 1: Status + Date Range --}}
        <form id="filterForm" method="GET" action="{{ route('admin.pengaduan.index') }}" class="row g-2 mb-3">
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label text-white-50 small mb-1">Status</label>
                <select name="status" class="form-select filter-select">
                    <option value="semua" {{ request('status') == 'semua' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                    <option value="baru" {{ request('status') == 'baru' ? 'selected' : '' }}>Baru</option>
                    <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label text-white-50 small mb-1">Dari Tanggal</label>
                <input type="date" name="dari" class="form-control date-input" value="{{ request('dari') }}">
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label text-white-50 small mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control date-input" value="{{ request('sampai') }}">
            </div>
            <div class="col-12 col-sm-6 col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn das-btn --primary flex-grow-1">
                    <i class="ti tabler-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.pengaduan.index') }}" class="btn das-btn --secondary">
                    <i class="ti tabler-refresh"></i>
                </a>
            </div>
        </form>

        {{-- Baris 2: Search + Info --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
                <i class="ti tabler-list text-info"></i> Daftar Pengaduan
            </h6>
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative" style="max-width:300px;">
                    <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"
                        style="font-size:0.85rem; pointer-events:none;"></i>
                    <input type="text" id="searchInput" class="form-control border-0 text-white"
                        placeholder="Cari kode unik atau nama..."
                        style="background: rgba(255,255,255,0.05); height:38px; padding-left:2.2rem; font-size:0.85rem;"
                        value="{{ request('search') }}">
                </div>
                @if(auth()->user()->isSuperAdmin())
                <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-2" type="button" data-bs-toggle="modal" data-bs-target="#resetConfirmModal" style="height: 38px; border-radius: 5px !important;">
                    <i class="ti tabler-trash fs-5"></i>
                    <span>Reset Data</span>
                </button>
                @endif
                <span class="das-chip --info d-none d-sm-inline-flex">{{ $pengaduan->total() }} Pengaduan</span>
            </div>
        </div>
    </div>

    <div class="das-panel__body p-0">
        @include('admin.pengaduan._table')
    </div>
</div>

@if(auth()->user()->isSuperAdmin())
{{-- Modal Konfirmasi Kustom Reset Data --}}
<div class="modal fade" id="resetConfirmModal" tabindex="-1" aria-labelledby="resetConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background: #1e1e2d; border-radius: 5px !important;">
            <div class="modal-header border-0 bg-danger bg-opacity-10 py-3" style="border-top-left-radius: 5px !important; border-top-right-radius: 5px !important;">
                <h5 class="modal-title text-danger d-flex align-items-center gap-2 fw-semibold fs-5" id="resetConfirmModalLabel">
                    <i class="ti tabler-alert-triangle fs-4"></i>
                    <span>Reset Semua Data Pengaduan</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-danger fw-medium mb-3" style="font-size: 0.95rem;">
                    Peringatan! Tindakan ini akan menghapus seluruh data pengaduan dan riwayat log secara permanen. Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="mb-2 text-white-50" style="font-size: 0.9rem;">
                    Ketik kata 'RESET' di bawah ini untuk mengonfirmasi:
                </div>
                <input type="text" id="confirmResetInput" class="form-control text-white border-secondary bg-dark text-center fw-bold" style="border-radius: 5px !important; font-size: 1rem; letter-spacing: 2px;" placeholder="RESET" autocomplete="off">
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-end gap-2 pb-4">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 5px !important;">Batal</button>
                <form action="{{ route('admin.pengaduan.reset') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" id="btnConfirmReset" class="btn btn-danger" style="border-radius: 5px !important;" disabled>Ya, Hapus Semua Data</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        if (!form) return;

        const searchInput = document.getElementById('searchInput');
        const statusSelect = form.querySelector('select[name="status"]');
        const dariInput = form.querySelector('input[name="dari"]');
        const sampaiInput = form.querySelector('input[name="sampai"]');
        const tableCard = document.getElementById('pengaduanTableCard');
        const statsGrid = document.getElementById('pengaduanStatsGrid');
        const resetButton = form.querySelector('a.btn.das-btn.--secondary');

        let currentUrl = window.location.search ? window.location.pathname + window.location.search : window.location.pathname;
        let searchTimeout;
        let lastSearch = searchInput ? searchInput.value.trim() : '';

        function fetchData(url, isPolling = false) {
            if (!isPolling && tableCard) {
                tableCard.style.opacity = '0.5';
                tableCard.style.pointerEvents = 'none';
            }

            currentUrl = url;

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newCard = doc.getElementById('pengaduanTableCard');
                const newGrid = doc.getElementById('pengaduanStatsGrid');
                if (tableCard && newCard) {
                    tableCard.innerHTML = newCard.innerHTML;
                }
                if (statsGrid && newGrid) {
                    statsGrid.innerHTML = newGrid.innerHTML;
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
            })
            .finally(() => {
                if (!isPolling && tableCard) {
                    tableCard.style.opacity = '1';
                    tableCard.style.pointerEvents = 'auto';
                }
            });
        }

        function triggerSearch() {
            const params = new URLSearchParams();
            const searchValue = searchInput ? searchInput.value.trim() : '';

            if (searchValue.length >= 2) {
                params.append('search', searchValue);
            }

            if (statusSelect && statusSelect.value && statusSelect.value !== 'semua') {
                params.append('status', statusSelect.value);
            }
            if (dariInput && dariInput.value) {
                params.append('dari', dariInput.value);
            }
            if (sampaiInput && sampaiInput.value) {
                params.append('sampai', sampaiInput.value);
            }

            const url = `${window.location.pathname}?${params.toString()}`;
            fetchData(url);
        }

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const val = searchInput.value.trim();
                if (val.length >= 2 || val.length === 0) {
                    if (val !== lastSearch) {
                        lastSearch = val;
                        searchTimeout = setTimeout(triggerSearch, 400);
                    }
                } else if (lastSearch !== '') {
                    lastSearch = '';
                    searchTimeout = setTimeout(triggerSearch, 400);
                }
            });
        }

        if (statusSelect) statusSelect.addEventListener('change', triggerSearch);
        if (dariInput) dariInput.addEventListener('change', triggerSearch);
        if (sampaiInput) sampaiInput.addEventListener('change', triggerSearch);

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            triggerSearch();
        });

        if (resetButton) {
            resetButton.addEventListener('click', function(e) {
                e.preventDefault();
                form.reset();
                if (searchInput) searchInput.value = '';
                if (statusSelect) statusSelect.value = 'semua';
                if (dariInput) dariInput.value = '';
                if (sampaiInput) sampaiInput.value = '';
                lastSearch = '';
                triggerSearch();
            });
        }

        if (tableCard) {
            tableCard.addEventListener('click', function(e) {
                const link = e.target.closest('a.das-page-btn');
                if (link) {
                    e.preventDefault();
                    const page = link.dataset.page || new URL(link.href).searchParams.get('page') || 1;
                    const parsedUrl = new URL(link.href);
                    fetchData(parsedUrl.pathname + parsedUrl.search);
                }
            });
        }

        // Event Click Stat Card
        const setupStatCardListeners = () => {
            document.querySelectorAll('.stat-card').forEach(card => {
                card.addEventListener('click', function() {
                    const status = this.getAttribute('data-status');
                    if (statusSelect) {
                        statusSelect.value = status === 'semua' ? 'semua' : status;
                        triggerSearch();
                    }
                });
            });
        };

        setupStatCardListeners();

        // Since stat cards are updated by DOMParser innerHTML, we need to delegate or re-bind click listeners when statsGrid changes.
        // We can do delegation on statsGrid instead or re-setup. Let's delegate:
        if (statsGrid) {
            statsGrid.addEventListener('click', function(e) {
                const card = e.target.closest('.stat-card');
                if (card) {
                    const status = card.getAttribute('data-status');
                    if (statusSelect) {
                        statusSelect.value = status === 'semua' ? 'semua' : status;
                        triggerSearch();
                    }
                }
            });
        }

        // ── RESET DATA CONFIRMATION HANDLER ──
        @if(auth()->user()->isSuperAdmin())
        const resetModal = document.getElementById('resetConfirmModal');
        const confirmResetInput = document.getElementById('confirmResetInput');
        const btnConfirmReset = document.getElementById('btnConfirmReset');

        if (resetModal && confirmResetInput && btnConfirmReset) {
            confirmResetInput.addEventListener('input', function() {
                if (this.value === 'RESET') {
                    btnConfirmReset.disabled = false;
                } else {
                    btnConfirmReset.disabled = true;
                }
            });

            resetModal.addEventListener('hidden.bs.modal', function() {
                confirmResetInput.value = '';
                btnConfirmReset.disabled = true;
            });
        }
        @endif
    });
</script>
@endsection
