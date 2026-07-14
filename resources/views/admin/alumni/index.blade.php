@extends('layouts/layoutMaster')

@section('title', 'Alumni')

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

        /* MODAL CUSTOM GLASSMORPHIC PREMIUM */
        .das-modal {
            background: rgba(26, 26, 46, 0.9) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 16px !important;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4) !important;
        }

        .das-modal-head {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(115, 103, 240, 0.05);
            padding: 1.25rem;
        }

        .das-modal-head-clean {
            border-bottom: none !important;
            background: transparent !important;
            padding: 1.5rem 1.5rem 0.5rem 1.5rem !important;
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

        .das-modal-icon-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(234, 84, 85, 0.1);
            color: #ea5455;
            margin-bottom: 1.25rem;
            transition: all 0.3s ease;
        }

        .das-modal-icon-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(234, 84, 85, 0.2) 0%, rgba(234, 84, 85, 0) 70%);
            z-index: -1;
            pointer-events: none;
        }

        .das-modal-badge-name {
            display: inline-block;
            background: rgba(234, 84, 85, 0.15);
            color: #ff4d4f;
            border: 1px solid rgba(234, 84, 85, 0.3);
            padding: 4px 12px;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin: 6px 0;
            box-shadow: 0 2px 10px rgba(234, 84, 85, 0.1);
        }

        .das-danger-warning-card {
            background: rgba(234, 84, 85, 0.08);
            border: 1px dashed rgba(234, 84, 85, 0.3);
            border-radius: 12px;
            padding: 1.25rem;
            margin-top: 1rem;
            text-align: left;
        }

        .das-danger-warning-card p {
            margin: 0;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.875rem;
            line-height: 1.5;
        }

        /* BUTTONS PREMIUM */
        .das-btn-cancel {
            background: rgba(255, 255, 255, 0.05) !important;
            color: rgba(255, 255, 255, 0.7) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            padding: 10px 20px !important;
            font-size: 0.875rem !important;
            transition: all 0.25s ease !important;
        }

        .das-btn-cancel:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            border-color: rgba(255, 255, 255, 0.25) !important;
            transform: translateY(-1px);
        }

        .das-btn-danger-glow {
            background: #ea5455 !important;
            color: #fff !important;
            border: none !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            padding: 10px 24px !important;
            font-size: 0.875rem !important;
            transition: all 0.25s ease !important;
            box-shadow: 0 4px 14px rgba(234, 84, 85, 0.3) !important;
        }

        .das-btn-danger-glow:hover {
            background: #ff5c5c !important;
            box-shadow: 0 6px 20px rgba(234, 84, 85, 0.5) !important;
            transform: translateY(-1px);
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

        #perPageSelect option, #tahunLulusSelect option {
            background: #1a1a2e;
            color: #ccc;
        }

        #perPageSelect:focus, #tahunLulusSelect:focus {
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

        .extra-small {
            font-size: 0.7rem;
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
                        <i class="ti tabler-school text-info"></i>
                    </div>
                    <div class="das-hero__logo-glow"></div>
                </div>

                <div class="das-hero__meta">
                    <div class="das-hero__badge">
                        <span class="pulse-dot"></span>
                        <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> /
                        Alumni
                    </div>
                    <h4 class="das-hero__title text-gradient-gold">Alumni</h4>
                    <p class="das-hero__subtitle">Kelola dan lihat seluruh data siswa yang telah lulus (Alumni).
                    </p>
                </div>
            </div>

            <div class="das-hero__actions d-flex gap-2">
                <button type="button" class="btn btn-danger" id="btnHapusSemuaAlumni" data-url="{{ route('admin.alumni.destroy-all') }}" style="border-radius:var(--das-radius); padding: 0.5rem 1.25rem;">
                    <i class="ti tabler-trash me-1"></i> Hapus Semua Alumni
                </button>
                <a href="{{ route('admin.siswa.index') }}" class="btn das-btn --primary">
                    <i class="ti tabler-arrow-left me-1"></i> Kembali ke Data Siswa
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

    {{-- TABLE CARD --}}
    <div class="das-panel">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
            style="border-color:rgba(255,255,255,0.08) !important;">
            <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
                <i class="ti tabler-list text-info"></i> Daftar Alumni
            </h6>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="position-relative" style="max-width:250px;">
                    <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"
                        style="font-size:0.85rem; pointer-events:none;"></i>
                    <input type="text" id="searchInput" class="form-control border-0 text-white"
                        placeholder="Cari nama, NIS, atau NISN..."
                        value="{{ request('search') }}"
                        style="background: rgba(255,255,255,0.05); height:38px; padding-left:2.2rem; font-size:0.85rem;">
                </div>

                <select id="tahunLulusSelect" class="form-select border-0 text-white w-auto"
                    style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
                    <option value="">— Semua Tahun Lulus —</option>
                    @foreach($tahunAkademikOptions as $ta)
                        <option value="{{ $ta->id }}" {{ request('tahun_lulus') == $ta->id ? 'selected' : '' }}>
                            {{ $ta->nama }} {{ ucfirst($ta->semester) }}
                        </option>
                    @endforeach
                </select>

                <select id="perPageSelect" class="form-select border-0 text-white w-auto"
                    style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>

                <span class="das-chip --info d-none d-sm-inline-flex">
                    {{ method_exists($siswa, 'total') ? $siswa->total() : count($siswa) }} Alumni
                </span>
            </div>
        </div>
        <div class="das-panel__body p-0">
            <div id="alumniTableContainer">
                @include('admin.alumni.table')
            </div>
        </div>
    </div>

    <!-- Modal Hapus Satu Alumni -->
    <div class="modal fade" id="modalHapusAlumni" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal">
                <div class="modal-header das-modal-head-clean d-flex justify-content-center position-relative">
                    <h5 class="modal-title text-center text-white font-weight-bold fs-5 pt-3">Hapus Data Alumni</h5>
                </div>
                <div class="modal-body das-modal-body text-center text-white pt-2">
                    <div class="das-modal-icon-wrapper">
                        <i class="ti tabler-trash fs-1"></i>
                        <div class="das-modal-icon-glow"></div>
                    </div>
                    <p class="mb-3" style="color: rgba(255,255,255,0.7); font-size: 0.95rem;">Apakah Anda yakin ingin menghapus data alumni berikut?</p>
                    <div class="mb-3">
                        <span id="hapusAlumniNama" class="das-modal-badge-name fs-6"></span>
                    </div>
                    <p class="mb-0 text-muted px-2" style="font-size: 0.8rem; line-height: 1.4;">
                        Seluruh riwayat absensi terkait alumni ini akan dihapus secara permanen dari sistem. Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 d-flex justify-content-center gap-3">
                    <button type="button" class="btn das-btn-cancel px-4" data-bs-dismiss="modal">Batalkan</button>
                    <button type="button" class="btn das-btn-danger-glow px-4" id="btnConfirmHapus">Ya, Hapus Data</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Hapus Semua Alumni -->
    <div class="modal fade" id="modalHapusSemuaAlumni" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content das-modal">
                <div class="modal-header das-modal-head-clean d-flex justify-content-center position-relative">
                    <h5 class="modal-title text-center text-white font-weight-bold fs-5 pt-3">Hapus Semua Alumni</h5>
                </div>
                <div class="modal-body das-modal-body text-center text-white pt-2">
                    <div class="das-modal-icon-wrapper" style="background: rgba(234, 84, 85, 0.15); color: #ff4d4f;">
                        <i class="ti tabler-alert-triangle fs-1"></i>
                        <div class="das-modal-icon-glow" style="background: radial-gradient(circle, rgba(234, 84, 85, 0.25) 0%, rgba(234, 84, 85, 0) 70%);"></div>
                    </div>
                    <p class="mb-2" style="color: rgba(255,255,255,0.9); font-size: 1rem; font-weight: 600;">Apakah Anda yakin ingin mengosongkan seluruh data alumni?</p>
                    <p class="mb-3 px-3 text-muted" style="font-size: 0.85rem; line-height: 1.4;">Tindakan pembersihan massal ini bersifat destruktif dan akan berdampak luas pada sistem.</p>
                    
                    <div class="das-danger-warning-card mx-2">
                        <div class="d-flex gap-2">
                            <i class="ti tabler-shield-alert text-danger fs-4 mt-1"></i>
                            <div>
                                <h6 class="text-danger mb-1 font-weight-bold" style="font-size: 0.85rem; letter-spacing: 0.3px;">PERINGATAN KERAS & TINDAKAN BERBAHAYA:</h6>
                                <p style="font-size: 0.78rem; color: rgba(255, 255, 255, 0.75);">
                                    • Menghapus seluruh data siswa berstatus alumni.<br>
                                    • Menghapus seluruh akun user (login) alumni.<br>
                                    • Menghapus riwayat absensi terkait alumni secara permanen.
                                </p>
                                <div class="mt-2 text-danger font-weight-bold" style="font-size: 0.78rem;">
                                    Tindakan ini TIDAK dapat dibatalkan atau dipulihkan kembali!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 d-flex justify-content-center gap-3">
                    <button type="button" class="btn das-btn-cancel px-4" data-bs-dismiss="modal">Batalkan</button>
                    <button type="button" class="btn das-btn-danger-glow px-4" id="btnConfirmHapusSemua">Ya, Hapus Semua</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('vendor-script')
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('alumniTableContainer');
            const searchInput = document.getElementById('searchInput');
            const perPageSelect = document.getElementById('perPageSelect');
            const tahunLulusSelect = document.getElementById('tahunLulusSelect');
            let searchTimeout;

            let deleteTargetUrl = '';
            let deleteBtnElement = null;

            function fetchData(page = 1) {
                const search = encodeURIComponent(searchInput.value || '');
                const perPage = perPageSelect.value || 10;
                const tahunLulus = tahunLulusSelect.value || '';
                const url = `{{ route('admin.alumni.index') }}?page=${page}&search=${search}&tahun_lulus=${tahunLulus}&per_page=${perPage}`;

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
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => fetchData(1), 450);
            });

            perPageSelect.addEventListener('change', function() {
                fetchData(1);
            });

            tahunLulusSelect.addEventListener('change', function() {
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

            // Individual delete handler (Show Modal)
            container.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-hapus-alumni');
                if (!btn) return;

                deleteTargetUrl = btn.dataset.url;
                deleteBtnElement = btn;
                document.getElementById('hapusAlumniNama').textContent = btn.dataset.nama || 'alumni ini';

                new bootstrap.Modal(document.getElementById('modalHapusAlumni')).show();
            });

            // Confirm individual delete action
            const btnConfirmHapus = document.getElementById('btnConfirmHapus');
            if (btnConfirmHapus) {
                btnConfirmHapus.addEventListener('click', function() {
                    if (!deleteTargetUrl) return;

                    if (deleteBtnElement) {
                        deleteBtnElement.disabled = true;
                    }
                    btnConfirmHapus.disabled = true;
                    btnConfirmHapus.textContent = 'Memproses...';

                    fetch(deleteTargetUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            btnConfirmHapus.disabled = false;
                            btnConfirmHapus.textContent = 'Ya, Hapus Data';
                            if (deleteBtnElement) {
                                deleteBtnElement.disabled = false;
                            }

                            const modalEl = document.getElementById('modalHapusAlumni');
                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) {
                                modalInstance.hide();
                            }

                            if (data.success) {
                                fetchData(1);
                            } else {
                                alert(data.message || 'Terjadi kesalahan saat menghapus data.');
                            }
                        })
                        .catch(err => {
                            btnConfirmHapus.disabled = false;
                            btnConfirmHapus.textContent = 'Ya, Hapus Data';
                            if (deleteBtnElement) {
                                deleteBtnElement.disabled = false;
                            }
                            console.error('Delete alumni error:', err);
                            alert('Terjadi kesalahan koneksi.');
                        });
                });
            }

            // Bulk delete handler (Show Modal)
            const btnHapusSemua = document.getElementById('btnHapusSemuaAlumni');
            if (btnHapusSemua) {
                btnHapusSemua.addEventListener('click', function() {
                    new bootstrap.Modal(document.getElementById('modalHapusSemuaAlumni')).show();
                });
            }

            // Confirm bulk delete action
            const btnConfirmHapusSemua = document.getElementById('btnConfirmHapusSemua');
            if (btnConfirmHapusSemua) {
                btnConfirmHapusSemua.addEventListener('click', function() {
                    if (!btnHapusSemua) return;
                    const url = btnHapusSemua.dataset.url;

                    btnConfirmHapusSemua.disabled = true;
                    btnConfirmHapusSemua.textContent = 'Memproses...';
                    btnHapusSemua.disabled = true;

                    fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            btnConfirmHapusSemua.disabled = false;
                            btnConfirmHapusSemua.textContent = 'Ya, Hapus Semua';
                            btnHapusSemua.disabled = false;

                            const modalEl = document.getElementById('modalHapusSemuaAlumni');
                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) {
                                modalInstance.hide();
                            }

                            if (data.success) {
                                fetchData(1);
                            } else {
                                alert(data.message || 'Terjadi kesalahan saat menghapus semua data.');
                            }
                        })
                        .catch(err => {
                            btnConfirmHapusSemua.disabled = false;
                            btnConfirmHapusSemua.textContent = 'Ya, Hapus Semua';
                            btnHapusSemua.disabled = false;
                            console.error('Delete all alumni error:', err);
                            alert('Terjadi kesalahan koneksi.');
                        });
                });
            }
        });
    </script>
@endsection
