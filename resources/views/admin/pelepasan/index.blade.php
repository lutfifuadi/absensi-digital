@extends('layouts/layoutMaster')

@section('title', 'Absensi Pelepasan Kelas XII')

@section('page-style')
    <style>
        /* Page Entry Animation */
        @keyframes slideInUp {
            from { transform: translateY(15px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .slide-in-up {
            animation: slideInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Stat Card Danger Modifier */
        .das-stat-card--danger::before {
            background: var(--das-danger) !important;
        }
        
        .das-stat-card--danger .das-stat-card__icon {
            background: rgba(234, 84, 85, 0.15) !important;
            color: var(--das-danger) !important;
        }
        
        .das-stat-card--danger .das-stat-card__val {
            color: var(--das-danger) !important;
        }

        /* Clickable Stat Cards */
        .das-stat-card-clickable {
            cursor: pointer;
            transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.18s ease !important;
        }
        .das-stat-card-clickable:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25) !important;
        }
        .das-stat-card-clickable:active {
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

        /* SCAN TOAST NOTIFICATION */
        #scanToast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            pointer-events: none;
        }

        .scan-toast-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 0.85rem 1.25rem;
            font-size: 0.85rem;
            color: #fff;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            animation: toastSlideIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            pointer-events: auto;
            max-width: 360px;
        }

        .scan-toast-item.toast-success {
            border-left: 3px solid #28c76f;
        }

        .scan-toast-item.toast-warning {
            border-left: 3px solid #ff9f43;
        }

        .scan-toast-item.toast-danger {
            border-left: 3px solid #ea5455;
        }

        .scan-toast-icon {
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .scan-toast-body {
            flex: 1;
        }

        .scan-toast-name {
            font-weight: 700;
            margin-bottom: 2px;
        }

        .scan-toast-msg {
            color: rgba(255,255,255,0.6);
            font-size: 0.78rem;
        }

        @keyframes toastSlideIn {
            from { transform: translateX(30px); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }

        @keyframes toastFadeOut {
            from { transform: translateX(0);    opacity: 1; }
            to   { transform: translateX(30px); opacity: 0; }
        }

        /* ICON ACTION BUTTONS */
        .das-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: #cbd5e1;
            font-size: 1.05rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
        }

        .das-icon-btn:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .das-icon-btn.--danger  { border-color: rgba(234,84,85,0.3); color: #ea5455; }
        .das-icon-btn.--danger:hover  { background: rgba(234,84,85,0.15); box-shadow: 0 4px 12px rgba(234,84,85,0.25); }
        .das-icon-btn.--success { border-color: rgba(40,199,111,0.3); color: #28c76f; }
        .das-icon-btn.--success:hover { background: rgba(40,199,111,0.15); box-shadow: 0 4px 12px rgba(40,199,111,0.25); }
        .das-icon-btn.--primary { border-color: rgba(115,103,240,0.3); color: #a5a2f7; }
        .das-icon-btn.--primary:hover { background: rgba(115,103,240,0.15); box-shadow: 0 4px 12px rgba(115,103,240,0.25); }
        .das-icon-btn.--secondary { border-color: rgba(255,255,255,0.1); color: #94a3b8; }
        .das-icon-btn.--secondary:hover { background: rgba(255,255,255,0.08); }

        /* Tooltip */
        .das-icon-btn::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15,23,42,0.97);
            color: #f1f5f9;
            font-size: 0.72rem;
            font-weight: 600;
            white-space: nowrap;
            padding: 5px 10px;
            border-radius: 7px;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.18s, transform 0.18s;
            transform: translateX(-50%) translateY(4px);
            z-index: 999;
        }

        .das-icon-btn:hover::after {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    </style>
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

{{-- Scan Toast Container --}}
<div id="scanToast"></div>

@section('content')

    {{-- ── HERO HEADER ────────────────────────────────── --}}
    <div class="das-hero mb-4 slide-in-up">
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
                        Wisuda &amp; Alumni / Absensi Pelepasan XII
                    </div>
                    <h4 class="das-hero__title text-gradient-gold">Absensi Pelepasan Kelas XII</h4>
                    <p class="das-hero__subtitle">Pencatatan kehadiran, Live Board wisuda, dan notifikasi real-time ke orang tua siswa.</p>
                </div>
            </div>

            <div class="das-hero__actions" style="gap:0.5rem;">
                @if(auth()->user()->hasAnyRole(['super_admin', 'admin_sekolah']))
                    <a href="{{ route('admin.pelepasan.settings') }}"
                        class="das-icon-btn --warning"
                        data-tooltip="Pengaturan Pelepasan">
                        <i class="ti tabler-settings"></i>
                    </a>
                    <button type="button" id="resetKehadiranBtn"
                        class="das-icon-btn --danger"
                        data-tooltip="Reset Kehadiran">
                        <i class="ti tabler-refresh"></i>
                    </button>
                @endif
                <a href="{{ route('admin.pelepasan.liveboard') }}" target="_blank"
                    class="das-icon-btn --success"
                    data-tooltip="Buka Live Board">
                    <i class="ti tabler-device-tv"></i>
                </a>
                <button type="button" id="scanQrModalBtn"
                    class="das-icon-btn --success"
                    data-tooltip="Scan QR Kamera">
                    <i class="ti tabler-camera"></i>
                </button>
                <a href="{{ route('admin.pelepasan.scan.page') }}" target="_blank"
                    class="das-icon-btn --primary"
                    data-tooltip="Scan via HP (Mobile)">
                    <i class="ti tabler-device-mobile"></i>
                </a>
                <a href="{{ route('admin.pelepasan.export') }}"
                    class="das-icon-btn --secondary"
                    data-tooltip="Export Excel">
                    <i class="ti tabler-download"></i>
                </a>
                <a href="{{ route('admin.pelepasan.cetak-kartu') }}"
                    class="das-icon-btn --primary"
                    data-tooltip="Cetak Kartu Peserta">
                    <i class="ti tabler-id"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ── STAT CARDS ──────────────────────────────────── --}}
    <div class="das-stats-row mb-4 slide-in-up" style="position: relative; bottom: 0; left: 0; right: 0;">
        <div class="das-stat-card das-stat-card--info das-stat-card-clickable" id="cardTotalSiswa">
            <div class="das-stat-card__icon"><i class="ti tabler-users"></i></div>
            <div class="das-stat-card__body">
                <div id="statTotal" class="das-stat-card__val">{{ $totalSiswa }}</div>
                <div class="das-stat-card__label">Target Lulusan (XII)</div>
            </div>
        </div>

        <div class="das-stat-card das-stat-card--success das-stat-card-clickable" id="cardHadir">
            <div class="das-stat-card__icon"><i class="ti tabler-check"></i></div>
            <div class="das-stat-card__body">
                <div id="statHadir" class="das-stat-card__val">{{ $totalHadir }}</div>
                <div class="das-stat-card__label">Hadir / Tap Masuk</div>
            </div>
        </div>

        <div class="das-stat-card das-stat-card--danger das-stat-card-clickable" id="cardBelumHadir">
            <div class="das-stat-card__icon"><i class="ti tabler-user-x"></i></div>
            <div class="das-stat-card__body">
                <div id="statBelumHadir" class="das-stat-card__val">{{ $totalBelumHadir }}</div>
                <div class="das-stat-card__label">Belum</div>
            </div>
        </div>

        <div class="das-stat-card das-stat-card--primary">
            <div class="das-stat-card__icon"><i class="ti tabler-chart-pie"></i></div>
            <div class="das-stat-card__body">
                <div id="statPersen" class="das-stat-card__val">{{ $persenHadir }}%</div>
                <div class="das-stat-card__label">Rasio Kehadiran</div>
            </div>
        </div>
    </div>

    {{-- ── GRID LAYOUT ────────────────────────────────── --}}
    <div class="row g-4 slide-in-up">
        
        {{-- ── LEFT PANEL: SIMULATION & HELP ────────────── --}}
        <div class="col-xl-4 col-md-5">
            <div class="das-panel mb-4">
                <div class="das-panel__head">
                    <div class="das-panel__title">
                        <span class="das-panel__icon-dot --primary"></span>
                        Simulasi Tap Scanner
                    </div>
                </div>
                <div class="das-panel__body">
                    <form id="scanSimForm">
                        @csrf
                        <div class="mb-3">
                            <label class="das-form-label">Masukkan NISN / Barcode Siswa</label>
                            <input type="text" id="scanInput" class="form-control das-form-control form-control-lg text-center" 
                                   placeholder="Tap barcode / input NISN" autocomplete="off">
                        </div>
                        <button type="submit" class="btn das-btn --primary w-100 justify-content-center py-2">
                            <i class="ti tabler-qrcode"></i> Kirim Input Manual
                        </button>
                    </form>

                    <div id="scanResultArea" class="mt-3 d-none">
                        <div class="p-3 rounded border border-secondary text-white" style="background:rgba(255,255,255,0.02);">
                            <div class="d-flex align-items-center gap-3">
                                <div id="resultStatusIcon" class="fs-1"></div>
                                <div>
                                    <h6 id="resultStudentName" class="mb-0 text-white fw-bold"></h6>
                                    <p id="resultStudentInfo" class="mb-0 small text-muted"></p>
                                    <p id="resultMsg" class="mb-0 small mt-1"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Guide Card -->
            <div class="das-panel">
                <div class="das-panel__head">
                    <div class="das-panel__title">
                        <span class="das-panel__icon-dot --info"></span>
                        Panduan Singkat
                    </div>
                </div>
                <div class="das-panel__body">
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                        <li class="d-flex align-items-start gap-3">
                            <div class="avatar avatar-xs mt-1">
                                <span class="avatar-initial rounded bg-label-info"><i class="ti tabler-info-circle fs-6"></i></span>
                            </div>
                            <span class="small text-white-50">Gunakan scanner fisik pada halaman <strong>Live Board</strong> untuk registrasi otomatis di panggung.</span>
                        </li>
                        <li class="d-flex align-items-start gap-3">
                            <div class="avatar avatar-xs mt-1">
                                <span class="avatar-initial rounded bg-label-success"><i class="ti tabler-mail-fast fs-6"></i></span>
                            </div>
                            <span class="small text-white-50">Setiap tap kartu yang berhasil akan memicu pengiriman pesan WhatsApp ke orang tua siswa secara real-time.</span>
                        </li>
                        <li class="d-flex align-items-start gap-3">
                            <div class="avatar avatar-xs mt-1">
                                <span class="avatar-initial rounded bg-label-warning"><i class="ti tabler-alert-triangle fs-6"></i></span>
                            </div>
                            <span class="small text-white-50">Jika barcode kartu wisudawan rusak atau tidak terbaca, gunakan kolom simulasi di atas untuk memasukkan NISN secara manual.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── RIGHT PANEL: MAIN ATTENDANCE TABLE ─────────── --}}
        <div class="col-xl-8 col-md-7">
            <div class="das-panel">
                <div class="das-panel__head">
                    <div class="das-panel__title">
                        <span class="das-panel__icon-dot --success"></span>
                        Daftar Kehadiran Wisudawan
                    </div>
                </div>

                <div class="px-4 py-3 border-bottom d-flex align-items-center" style="border-color:var(--das-border)!important;">
                    <div class="row g-2 w-100 m-0">
                        <div class="col-12 col-md px-0">
                            <div class="position-relative w-100">
                                <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size:0.85rem; pointer-events:none;"></i>
                                <input type="text" id="pelepasanSearch" class="form-control border-0 text-white w-100" placeholder="Cari nama, NISN, atau NIS..." style="background: rgba(255,255,255,0.05); height:38px; padding-left:2.2rem; font-size:0.85rem;" value="{{ $search }}">
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-auto px-0 ps-md-2" style="min-width: 180px;">
                            <select id="pelepasanStatus" class="form-select border-0 text-white w-100" style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ $status === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="belum_hadir" {{ $status === 'belum_hadir' ? 'selected' : '' }}>Belum</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-auto px-0 ps-md-2">
                            <button type="button" id="pelepasanResetBtn" class="btn das-btn --secondary w-100 justify-content-center {{ !$search && !$status ? 'd-none' : '' }}" style="height:38px;">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div id="pelepasanTableContainer">
                    @include('admin.pelepasan.table')
                </div>
            </div>
        </div>
    </div>

    {{-- ── MODAL SCAN QR KAMERA ──────────────────────────────── --}}
    <div id="scanQrModal" class="scan-modal-overlay" style="display:none;">
        <div class="scan-modal">

            {{-- Accent gradient bar --}}
            <div class="scan-modal__accent-bar"></div>

            {{-- Header --}}
            <div class="scan-modal__header">
                <div class="scan-modal__title">
                    <div class="scan-modal__icon-badge">
                        <i class="ti tabler-camera"></i>
                    </div>
                    <div class="scan-modal__title-text">
                        <span class="scan-modal__title-main">Scan QR Kamera</span>
                        <span class="scan-modal__title-sub">Pelepasan Kelas XII · Absensi Otomatis</span>
                    </div>
                </div>
                <button type="button" id="scanModalClose" class="scan-modal__close">
                    <i class="ti tabler-x"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="scan-modal__body">

                {{-- Camera placeholder --}}
                <div class="scan-modal__placeholder" id="scanModalPlaceholder">
                    <div class="scan-modal__placeholder-badge">OFFLINE</div>
                    <div class="scan-modal__pulse-ring">
                        <div class="scan-modal__pulse-ring-inner">
                            <i class="ti tabler-camera-off"></i>
                        </div>
                    </div>
                    <p class="scan-modal__placeholder-title">Kamera Belum Aktif</p>
                    <span class="scan-modal__placeholder-hint">Tekan tombol di bawah untuk memulai sesi scan</span>
                </div>

                {{-- Camera viewport --}}
                <div class="scan-modal__camera" id="scanModalCamera" style="display:none;">
                    <div class="scan-modal__live-badge">
                        <span class="scan-modal__live-dot"></span>LIVE
                    </div>
                    <div id="modalReader"></div>
                    <div class="scan-modal__frame">
                        <div class="scan-modal__frame-box">
                            <span class="scan-modal__corner-tl"></span>
                            <span class="scan-modal__corner-tr"></span>
                            <span class="scan-modal__corner-br"></span>
                            <span class="scan-modal__corner-bl"></span>
                        </div>
                        <div class="scan-modal__line"></div>
                    </div>
                </div>

                {{-- Status bar --}}
                <div class="scan-modal__status" id="scanModalStatus" style="display:none;">
                    <span class="scan-modal__dot"></span>
                    <span>Kamera aktif — arahkan ke QR code siswa</span>
                </div>

            </div>

            {{-- Footer --}}
            <div class="scan-modal__footer">
                <button type="button" id="scanModalToggle" class="scan-modal__btn scan-modal__btn--start">
                    <i class="ti tabler-camera"></i>
                    <span>Mulai Kamera</span>
                </button>
                <div class="scan-modal__manual">
                    <div class="scan-modal__divider">
                        <span class="scan-modal__divider-line"></span>
                        <span class="scan-modal__divider-text">atau input manual</span>
                        <span class="scan-modal__divider-line"></span>
                    </div>
                    <div class="scan-modal__input-row">
                        <input type="text" id="scanModalManual" class="scan-modal__input"
                               placeholder="Ketik NISN / NIS..." autocomplete="off">
                        <button type="button" id="scanModalManualBtn" class="scan-modal__submit">
                            <i class="ti tabler-send"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── STYLE MODAL ─────────────────────────────────────────── --}}
    <style>
        /* ── OVERLAY ─────────────────────────────────────────────── */
        .scan-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.82);
            backdrop-filter: blur(12px) saturate(1.4);
            -webkit-backdrop-filter: blur(12px) saturate(1.4);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            animation: smOverlayIn 0.25s ease forwards;
        }
        @keyframes smOverlayIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* ── MODAL CONTAINER ─────────────────────────────────────── */
        .scan-modal {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            width: 100%;
            max-width: 430px;
            max-height: 92vh;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow:
                0 32px 80px rgba(0, 0, 0, 0.7),
                0 0 0 1px rgba(115, 103, 240, 0.12);
            animation: smSlideUp 0.35s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            position: relative;
        }
        .scan-modal::-webkit-scrollbar { width: 4px; }
        .scan-modal::-webkit-scrollbar-track { background: transparent; }
        .scan-modal::-webkit-scrollbar-thumb { background: rgba(115,103,240,0.3); border-radius: 2px; }

        @keyframes smSlideUp {
            from { transform: translateY(24px) scale(0.97); opacity: 0; }
            to   { transform: translateY(0) scale(1); opacity: 1; }
        }

        /* ── ACCENT BAR ──────────────────────────────────────────── */
        .scan-modal__accent-bar {
            height: 3px;
            background: linear-gradient(90deg, #7367f0, #a855f7, #7367f0);
            background-size: 200% 100%;
            animation: smAccentShift 3s linear infinite;
            border-radius: 5px 5px 0 0;
        }
        @keyframes smAccentShift {
            0%   { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        /* ── HEADER ──────────────────────────────────────────────── */
        .scan-modal__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }
        .scan-modal__title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .scan-modal__icon-badge {
            width: 38px;
            height: 38px;
            border-radius: 5px;
            background: linear-gradient(135deg, rgba(115,103,240,0.25), rgba(168,85,247,0.15));
            border: 1px solid rgba(115, 103, 240, 0.35);
            box-shadow: 0 0 14px rgba(115, 103, 240, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #a78bfa;
            font-size: 1.1rem;
        }
        .scan-modal__title-text {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
        }
        .scan-modal__title-main {
            font-weight: 700;
            font-size: 0.95rem;
            color: #f1f5f9;
            line-height: 1.2;
        }
        .scan-modal__title-sub {
            font-size: 0.7rem;
            color: #7367f0;
            font-weight: 500;
            letter-spacing: 0.02em;
        }
        .scan-modal__close {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #64748b;
            width: 34px;
            height: 34px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.1rem;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .scan-modal__close:hover {
            background: rgba(234, 84, 85, 0.15);
            border-color: rgba(234, 84, 85, 0.4);
            color: #ea5455;
            transform: scale(1.05);
        }

        /* ── BODY ────────────────────────────────────────────────── */
        .scan-modal__body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        /* ── PLACEHOLDER ─────────────────────────────────────────── */
        .scan-modal__placeholder {
            aspect-ratio: 1;
            max-height: 290px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px dashed rgba(115, 103, 240, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            color: #475569;
            position: relative;
            overflow: hidden;
        }
        .scan-modal__placeholder-badge {
            position: absolute;
            top: 0.6rem;
            right: 0.6rem;
            background: rgba(100, 116, 139, 0.12);
            border: 1px solid rgba(100, 116, 139, 0.3);
            color: #64748b;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            padding: 0.2rem 0.55rem;
            border-radius: 3px;
        }
        /* Pulse ring animation wrapper */
        .scan-modal__pulse-ring {
            position: relative;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .scan-modal__pulse-ring::before,
        .scan-modal__pulse-ring::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid rgba(115, 103, 240, 0.25);
            animation: smPulseRing 2.4s ease-out infinite;
        }
        .scan-modal__pulse-ring::after {
            animation-delay: 1.2s;
        }
        .scan-modal__pulse-ring-inner {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #334155;
            font-size: 1.6rem;
            position: relative;
            z-index: 1;
        }
        @keyframes smPulseRing {
            0%   { transform: scale(1);    opacity: 0.6; }
            80%  { transform: scale(1.8);  opacity: 0; }
            100% { transform: scale(1.8);  opacity: 0; }
        }
        .scan-modal__placeholder-title {
            font-size: 0.88rem;
            font-weight: 600;
            color: #64748b;
            margin: 0;
        }
        .scan-modal__placeholder-hint {
            font-size: 0.72rem;
            color: #334155;
            text-align: center;
            padding: 0 1.5rem;
            line-height: 1.5;
        }

        /* ── CAMERA VIEWPORT ─────────────────────────────────────── */
        .scan-modal__camera {
            position: relative;
            border-radius: 5px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 1;
            max-height: 290px;
        }
        #modalReader { width: 100% !important; height: 100% !important; }
        #modalReader video { width: 100% !important; height: 100% !important; object-fit: cover; }
        #modalReader img { display: none !important; }
        #modalReader__dashboard { display: none !important; }

        /* LIVE badge */
        .scan-modal__live-badge {
            position: absolute;
            top: 0.6rem;
            right: 0.6rem;
            z-index: 20;
            background: rgba(40, 199, 111, 0.15);
            border: 1px solid rgba(40, 199, 111, 0.4);
            color: #28c76f;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            padding: 0.2rem 0.55rem;
            border-radius: 3px;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .scan-modal__live-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #28c76f;
            box-shadow: 0 0 6px #28c76f;
            animation: smBlink 1.2s ease infinite;
            display: inline-block;
        }

        /* Frame corners */
        .scan-modal__frame {
            position: absolute;
            inset: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .scan-modal__frame-box {
            width: 58%;
            aspect-ratio: 1;
            position: relative;
        }
        .scan-modal__corner-tl,
        .scan-modal__corner-tr,
        .scan-modal__corner-br,
        .scan-modal__corner-bl {
            position: absolute;
            width: 28px;
            height: 28px;
            border-style: solid;
            border-color: #7367f0;
        }
        .scan-modal__corner-tl { top: 0; left: 0; border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
        .scan-modal__corner-tr { top: 0; right: 0; border-width: 3px 3px 0 0; border-radius: 0 4px 0 0; }
        .scan-modal__corner-br { bottom: 0; right: 0; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }
        .scan-modal__corner-bl { bottom: 0; left: 0; border-width: 0 0 3px 3px; border-radius: 0 0 0 4px; }

        /* Scan line */
        .scan-modal__line {
            position: absolute;
            left: 6%;
            right: 6%;
            height: 2px;
            background: linear-gradient(to right, transparent, #7367f0 20%, #a78bfa 50%, #7367f0 80%, transparent);
            box-shadow: 0 0 10px rgba(115, 103, 240, 0.8), 0 0 20px rgba(115, 103, 240, 0.3);
            animation: smScanLine 2.2s cubic-bezier(0.45, 0, 0.55, 1) infinite;
        }
        @keyframes smScanLine {
            0%   { top: 6%;  opacity: 0; }
            8%   { opacity: 1; }
            92%  { opacity: 1; }
            100% { top: 94%; opacity: 0; }
        }

        /* ── STATUS BAR ──────────────────────────────────────────── */
        .scan-modal__status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.5rem 1rem;
            background: rgba(40, 199, 111, 0.08);
            border: 1px solid rgba(40, 199, 111, 0.22);
            border-radius: 5px;
            font-size: 0.77rem;
            font-weight: 500;
            color: #4ade80;
        }
        .scan-modal__dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #28c76f;
            box-shadow: 0 0 7px #28c76f;
            animation: smBlink 1.5s ease infinite;
            flex-shrink: 0;
        }
        @keyframes smBlink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }

        /* ── FOOTER ──────────────────────────────────────────────── */
        .scan-modal__footer {
            padding: 0 1.25rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        /* Toggle button */
        .scan-modal__btn {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 5px;
            border: none;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            transition: all 0.25s ease;
            font-family: inherit;
            position: relative;
            overflow: hidden;
        }
        /* Shimmer pseudo-element */
        .scan-modal__btn::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.18), transparent);
            transform: skewX(-20deg);
            transition: none;
        }
        .scan-modal__btn:hover::after {
            animation: smShimmer 0.55s ease forwards;
        }
        @keyframes smShimmer {
            from { left: -100%; }
            to   { left: 160%; }
        }
        .scan-modal__btn--start {
            background: linear-gradient(135deg, #7367f0 0%, #9b59ef 50%, #a855f7 100%);
            color: #fff;
            box-shadow: 0 4px 18px rgba(115, 103, 240, 0.35);
        }
        .scan-modal__btn--start:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(115, 103, 240, 0.5);
        }
        .scan-modal__btn--start:active {
            transform: translateY(0);
            box-shadow: 0 4px 14px rgba(115, 103, 240, 0.3);
        }
        .scan-modal__btn--stop {
            background: rgba(234, 84, 85, 0.08);
            border: 1px solid rgba(234, 84, 85, 0.3);
            color: #ea5455;
        }
        .scan-modal__btn--stop:hover {
            background: rgba(234, 84, 85, 0.16);
            border-color: rgba(234, 84, 85, 0.5);
            box-shadow: 0 4px 16px rgba(234, 84, 85, 0.2);
            transform: translateY(-1px);
        }
        .scan-modal__btn--stop:active {
            transform: translateY(0);
        }

        /* Manual input section */
        .scan-modal__manual {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }
        .scan-modal__divider {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .scan-modal__divider-line {
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.07);
        }
        .scan-modal__divider-text {
            font-size: 0.68rem;
            color: #475569;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            white-space: nowrap;
        }
        .scan-modal__input-row {
            display: flex;
            gap: 0.5rem;
        }
        .scan-modal__input {
            flex: 1;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            padding: 0.68rem 0.9rem;
            color: #f1f5f9;
            font-size: 0.85rem;
            outline: none;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .scan-modal__input:focus {
            border-color: rgba(115, 103, 240, 0.6);
            box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.1);
        }
        .scan-modal__input::placeholder { color: #334155; }
        .scan-modal__submit {
            background: linear-gradient(135deg, #7367f0, #9b59ef);
            border: none;
            border-radius: 5px;
            padding: 0 1.1rem;
            color: #fff;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 10px rgba(115, 103, 240, 0.3);
        }
        .scan-modal__submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(115, 103, 240, 0.45);
        }
        .scan-modal__submit:active { transform: translateY(0); }
    </style>

@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ─── Fungsi doScan terpusat ────────────────────────────────────────
            // ── Notifikasi Suara ────────────────────────────────────
            function playChime() {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) return;
                    const ctx = new AudioContext();
                    let osc1 = ctx.createOscillator();
                    let gain1 = ctx.createGain();
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    osc1.type = 'sine';
                    osc1.frequency.value = 659.25;
                    gain1.gain.setValueAtTime(0.1, ctx.currentTime);
                    gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                    osc1.start();
                    osc1.stop(ctx.currentTime + 0.3);
                    let osc2 = ctx.createOscillator();
                    let gain2 = ctx.createGain();
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.type = 'sine';
                    osc2.frequency.value = 880.00;
                    gain2.gain.setValueAtTime(0.1, ctx.currentTime + 0.15);
                    gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.55);
                    osc2.start(ctx.currentTime + 0.15);
                    osc2.stop(ctx.currentTime + 0.55);
                } catch (e) { console.error("Audio failure: ", e); }
            }

            function doScan(qrCode) {
                fetch("{{ route('admin.pelepasan.scan.store') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ qr_code: qrCode })
                })
                .then(async response => {
                    const data = await response.json();

                    if (response.ok) {
                        if (data.is_new) {
                            playChime();
                            showToast('success', '✅', data.siswa_nama, `${data.siswa_kelas} — ${data.waktu} · WA: ${data.wa_status === 'sent' ? 'Terkirim' : 'Tidak Terkirim'}`);
                        } else {
                            showToast('warning', '⚠️', data.siswa_nama, 'Sudah scan sebelumnya — data tidak diubah');
                        }
                        updateStats(data.total_hadir);
                        fetchData(1);
                    } else {
                        showToast('danger', '❌', 'Scan Gagal', data.message || 'Kartu tidak dikenal');
                    }

                    // Clear input manual setelah scan
                    document.getElementById('scanInput').value = '';
                })
                .catch(() => {
                    showToast('danger', '❌', 'Error Server', 'Gagal menghubungi server');
                });
            }

            // ─── Toast Notification ──────────────────────────────────────────
            function showToast(type, icon, name, msg) {
                const container = document.getElementById('scanToast');
                const item = document.createElement('div');
                item.className = `scan-toast-item toast-${type}`;
                item.innerHTML = `
                    <div class="scan-toast-icon">${icon}</div>
                    <div class="scan-toast-body">
                        <div class="scan-toast-name">${name}</div>
                        <div class="scan-toast-msg">${msg}</div>
                    </div>
                `;
                container.appendChild(item);

                // Auto remove after 4s
                setTimeout(() => {
                    item.style.animation = 'toastFadeOut 0.35s ease forwards';
                    setTimeout(() => item.remove(), 350);
                }, 4000);
            }

            // ─── Form Simulasi Manual ────────────────────────────────────────
            const scanSimForm = document.getElementById('scanSimForm');
            const scanInput   = document.getElementById('scanInput');

            scanSimForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const qrCode = scanInput.value.trim();
                if (!qrCode) return;
                doScan(qrCode);
            });

            // Tekan Enter di scanInput juga trigger scan
            scanInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const qrCode = scanInput.value.trim();
                    if (!qrCode) return;
                    doScan(qrCode);
                }
            });

            // ─── AJAX Search, Filter & Pagination Logic ───────────────────────
            const container = document.getElementById('pelepasanTableContainer');
            const searchInputTable = document.getElementById('pelepasanSearch');
            const statusSelect = document.getElementById('pelepasanStatus');
            const resetBtn = document.getElementById('pelepasanResetBtn');
            let searchTimeout;

            function fetchData(page = 1) {
                const search = encodeURIComponent(searchInputTable.value || '');
                const status = statusSelect.value || '';
                const url = `{{ route('admin.pelepasan.index') }}?page=${page}&search=${search}&status=${status}`;

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

                    // Toggle Reset Button visibility
                    if (searchInputTable.value || statusSelect.value) {
                        resetBtn.classList.remove('d-none');
                    } else {
                        resetBtn.classList.add('d-none');
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                });
            }

            // Debounced Search Input
            searchInputTable.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => fetchData(1), 450);
            });

            // Status Filter Change
            statusSelect.addEventListener('change', function() {
                fetchData(1);
            });

            // Clickable Stat Cards Event Handlers
            const cardTotalSiswa = document.getElementById('cardTotalSiswa');
            const cardHadir = document.getElementById('cardHadir');
            const cardBelumHadir = document.getElementById('cardBelumHadir');

            if (cardTotalSiswa) {
                cardTotalSiswa.addEventListener('click', function() {
                    statusSelect.value = '';
                    fetchData(1);
                });
            }

            if (cardHadir) {
                cardHadir.addEventListener('click', function() {
                    statusSelect.value = 'hadir';
                    fetchData(1);
                });
            }

            if (cardBelumHadir) {
                cardBelumHadir.addEventListener('click', function() {
                    statusSelect.value = 'belum_hadir';
                    fetchData(1);
                });
            }

            // Reset Filters Click
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchInputTable.value = '';
                statusSelect.value = '';
                fetchData(1);
            });

            // AJAX Pagination Clicks
            container.addEventListener('click', function(e) {
                const link = e.target.closest('a.das-page-btn');
                if (link) {
                    e.preventDefault();
                    const page = link.dataset.page || new URL(link.href).searchParams.get('page') || 1;
                    fetchData(page);
                }
            });

            // ─── Stats Card Updater ──────────────────────────────────────────
            function updateStats(totalHadir) {
                const totalSiswa = parseInt(document.getElementById('statTotal').textContent) || 0;
                const totalBelumHadir = Math.max(0, totalSiswa - totalHadir);
                const persenHadir = totalSiswa > 0 ? ((totalHadir / totalSiswa) * 100).toFixed(1) : '0.0';
                
                document.getElementById('statHadir').textContent = totalHadir;
                document.getElementById('statBelumHadir').textContent = totalBelumHadir;
                document.getElementById('statPersen').textContent = persenHadir + '%';
            }

            // ─── Auto-Polling Real-time Data ────────────────────────────────────
            const REALTIME_URL = "{{ route('admin.pelepasan.realtime') }}";
            let prevTotalHadir = -1;

            function pollRealtimeData() {
                fetch(REALTIME_URL, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('statHadir').textContent = data.total_hadir;
                        document.getElementById('statBelumHadir').textContent = data.total_belum_hadir;
                        document.getElementById('statPersen').textContent = data.persen_hadir + '%';

                        if (prevTotalHadir !== -1 && prevTotalHadir !== data.total_hadir) {
                            fetchData(1);
                        }
                        prevTotalHadir = data.total_hadir;
                    }
                })
                .catch(err => console.error('Polling error:', err));
            }

            setInterval(pollRealtimeData, 3000);

            pollRealtimeData();

            // ─── Reset Kehadiran administrative action ─────────────────────────
            const resetKehadiranBtn = document.getElementById('resetKehadiranBtn');
            if (resetKehadiranBtn) {
                resetKehadiranBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Mereset Kehadiran?',
                        html: '<div class="mt-2">Semua data scan masuk siswa untuk <b>Pelepasan Kelas XII</b> akan dihapus/direset kembali ke awal. Tindakan ini tidak dapat dibatalkan!</div>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Reset Semua',
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
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading
                            Swal.fire({
                                title: 'Memproses Reset...',
                                html: '<div class="mt-2">Mohon tunggu sebentar, sedang menghapus data absensi kegiatan...</div>',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                                customClass: {
                                    popup: 'das-swal-popup',
                                    title: 'das-swal-title',
                                    htmlContainer: 'das-swal-html'
                                },
                                background: 'transparent'
                            });

                            fetch("{{ route('admin.pelepasan.reset') }}", {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(async res => {
                                const data = await res.json();
                                if (res.ok && data.success) {
                                    Swal.fire({
                                        title: 'Reset Berhasil',
                                        html: `<div class="mt-2">${data.message}</div>`,
                                        icon: 'success',
                                        confirmButtonText: 'OK',
                                        customClass: {
                                            popup: 'das-swal-popup',
                                            title: 'das-swal-title',
                                            htmlContainer: 'das-swal-html',
                                            confirmButton: 'btn btn-primary das-swal-confirm'
                                        },
                                        buttonsStyling: false,
                                        background: 'transparent'
                                    });
                                    // Reset stats in DOM
                                    updateStats(0);
                                    // Refresh table
                                    fetchData(1);
                                } else {
                                    Swal.fire({
                                        title: 'Gagal',
                                        html: `<div class="mt-2">${data.message || 'Terjadi kesalahan saat mereset data.'}</div>`,
                                        icon: 'error',
                                        confirmButtonText: 'OK',
                                        customClass: {
                                            popup: 'das-swal-popup',
                                            title: 'das-swal-title',
                                            htmlContainer: 'das-swal-html',
                                            confirmButton: 'btn btn-primary das-swal-confirm'
                                        },
                                        buttonsStyling: false,
                                        background: 'transparent'
                                    });
                                }
                            })
                            .catch(err => {
                                console.error('Reset error:', err);
                                Swal.fire({
                                    title: 'Error Server',
                                    html: '<div class="mt-2">Gagal menghubungi server. Hubungi administrator.</div>',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'das-swal-popup',
                                        title: 'das-swal-title',
                                        htmlContainer: 'das-swal-html',
                                        confirmButton: 'btn btn-primary das-swal-confirm'
                                    },
                                    buttonsStyling: false,
                                    background: 'transparent'
                                });
                            });
                        }
                    });
                });
            }

            // ─── Modal Scan QR Kamera ─────────────────────────────────────────
            const scanModalOverlay = document.getElementById('scanQrModal');
            const scanModalBtn     = document.getElementById('scanQrModalBtn');
            const scanModalClose   = document.getElementById('scanModalClose');
            const scanModalToggle  = document.getElementById('scanModalToggle');
            const scanModalPlaceholder = document.getElementById('scanModalPlaceholder');
            const scanModalCamera  = document.getElementById('scanModalCamera');
            const scanModalStatus  = document.getElementById('scanModalStatus');
            const scanModalManual  = document.getElementById('scanModalManual');
            const scanModalManualBtn = document.getElementById('scanModalManualBtn');

            let modalQrCode = null;
            let isModalCameraOn = false;
            let lastModalScan = '';
            let lastModalScanTime = 0;

            // Buka modal
            scanModalBtn.addEventListener('click', function() {
                scanModalOverlay.style.display = 'flex';
                // Auto-start kamera jika sudah pernah diizinkan
                if (navigator.permissions) {
                    navigator.permissions.query({ name: 'camera' }).then(p => {
                        if (p.state === 'granted') setTimeout(startModalCamera, 500);
                    }).catch(() => {});
                }
            });

            // Tutup modal
            function closeScanModal() {
                stopModalCamera();
                scanModalOverlay.style.display = 'none';
            }
            scanModalClose.addEventListener('click', closeScanModal);
            scanModalOverlay.addEventListener('click', function(e) {
                if (e.target === scanModalOverlay) closeScanModal();
            });

            // Toggle kamera
            scanModalToggle.addEventListener('click', function() {
                if (isModalCameraOn) {
                    stopModalCamera();
                } else {
                    startModalCamera();
                }
            });

            function startModalCamera() {
                scanModalPlaceholder.style.display = 'none';
                scanModalCamera.style.display = 'block';
                scanModalStatus.style.display = 'block';

                scanModalToggle.className = 'scan-modal__btn scan-modal__btn--stop';
                scanModalToggle.innerHTML = '<i class="ti tabler-camera-off"></i> Matikan Kamera';

                modalQrCode = new Html5Qrcode("modalReader");

                Html5Qrcode.getCameras().then(cameras => {
                    if (!cameras || cameras.length === 0) {
                        showToast('danger', '📵', 'Kamera tidak ditemukan', 'Pastikan izin kamera sudah diberikan');
                        stopModalCamera();
                        return;
                    }

                    const backCam = cameras.find(c =>
                        c.label.toLowerCase().includes('back') ||
                        c.label.toLowerCase().includes('belakang') ||
                        c.label.toLowerCase().includes('rear')
                    );
                    const camId = backCam ? backCam.id : cameras[cameras.length - 1].id;

                    modalQrCode.start(
                        camId,
                        { fps: 15, qrbox: { width: 200, height: 200 }, aspectRatio: 1.0, disableFlip: false },
                        function(decodedText) {
                            const now = Date.now();
                            if (decodedText === lastModalScan && (now - lastModalScanTime) < 3000) return;
                            lastModalScan = decodedText;
                            lastModalScanTime = now;
                            // Scan berhasil, proses & tutup modal
                            doScan(decodedText);
                            closeScanModal();
                        },
                        function() {}
                    ).catch(function(err) {
                        console.error(err);
                        showToast('danger', '❌', 'Gagal akses kamera', err.message || 'Coba izinkan akses kamera di browser');
                        stopModalCamera();
                    });

                    isModalCameraOn = true;

                }).catch(function() {
                    showToast('danger', '❌', 'Tidak bisa membuka kamera', 'Pastikan izin kamera diberikan di browser');
                    stopModalCamera();
                });
            }

            function stopModalCamera() {
                isModalCameraOn = false;
                if (modalQrCode) {
                    modalQrCode.stop().then(function() {
                        modalQrCode.clear();
                        modalQrCode = null;
                    }).catch(function() {});
                }
                scanModalPlaceholder.style.display = 'flex';
                scanModalCamera.style.display = 'none';
                scanModalStatus.style.display = 'none';
                scanModalToggle.className = 'scan-modal__btn scan-modal__btn--start';
                scanModalToggle.innerHTML = '<i class="ti tabler-camera"></i> Mulai Kamera';
            }

            // Manual input di modal
            function submitModalManual() {
                const val = scanModalManual.value.trim();
                if (!val) return;
                scanModalManual.value = '';
                doScan(val);
                closeScanModal();
            }
            scanModalManualBtn.addEventListener('click', submitModalManual);
            scanModalManual.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') submitModalManual();
            });
        });
    </script>
@endsection
