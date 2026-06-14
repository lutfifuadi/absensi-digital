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

        /* HIDDEN PHYSICAL SCANNER INPUT */
        #physicalScannerInput {
            position: fixed;
            left: -9999px;
            top: 0;
            width: 1px;
            height: 1px;
            opacity: 0;
            z-index: -1;
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

        /* SCANNER STATUS INDICATOR */
        #scannerStatusBar {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            color: #94a3b8;
            margin-top: 0.5rem;
            padding: 0.4rem 0.75rem;
            background: rgba(255,255,255,0.03);
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.06);
        }

        #scannerStatusDot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #28c76f;
            box-shadow: 0 0 6px #28c76f;
            flex-shrink: 0;
        }

        #scannerStatusDot.offline {
            background: #ea5455;
            box-shadow: 0 0 6px #ea5455;
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

{{-- Hidden Physical Scanner Input (untuk scanner QR fisik) --}}
<input type="text" id="physicalScannerInput" autocomplete="off" aria-hidden="true">

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
                <a href="{{ route('admin.pelepasan.scan.page') }}" target="_blank"
                    class="das-icon-btn --primary"
                    data-tooltip="Scan via HP">
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
        <div class="das-stat-card das-stat-card--info">
            <div class="das-stat-card__icon"><i class="ti tabler-users"></i></div>
            <div class="das-stat-card__body">
                <div id="statTotal" class="das-stat-card__val">{{ $totalSiswa }}</div>
                <div class="das-stat-card__label">Target Lulusan (XII)</div>
            </div>
        </div>

        <div class="das-stat-card das-stat-card--success">
            <div class="das-stat-card__icon"><i class="ti tabler-check"></i></div>
            <div class="das-stat-card__body">
                <div id="statHadir" class="das-stat-card__val">{{ $totalHadir }}</div>
                <div class="das-stat-card__label">Hadir / Tap Masuk</div>
            </div>
        </div>

        <div class="das-stat-card das-stat-card--danger">
            <div class="das-stat-card__icon"><i class="ti tabler-user-x"></i></div>
            <div class="das-stat-card__body">
                <div id="statBelumHadir" class="das-stat-card__val">{{ $totalBelumHadir }}</div>
                <div class="das-stat-card__label">Belum Hadir</div>
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
                            <div id="scannerStatusBar" class="mt-2">
                                <span id="scannerStatusDot"></span>
                                <span id="scannerStatusText">Scanner fisik terhubung — arahkan QR ke scanner</span>
                            </div>
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
                                <option value="belum_hadir" {{ $status === 'belum_hadir' ? 'selected' : '' }}>Belum Hadir</option>
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

@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ─── Physical Scanner Input Management ───────────────────────────
            const physicalScannerInput = document.getElementById('physicalScannerInput');
            const scannerStatusDot  = document.getElementById('scannerStatusDot');
            const scannerStatusText = document.getElementById('scannerStatusText');

            function keepScannerFocus() {
                // Hanya fokus physical scanner jika tidak ada elemen form yang aktif
                const active = document.activeElement;
                const isFormEl = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT' || active.tagName === 'BUTTON');
                if (!isFormEl) {
                    physicalScannerInput.focus();
                }
            }

            physicalScannerInput.addEventListener('focus', () => {
                scannerStatusDot.classList.remove('offline');
                scannerStatusText.textContent = 'Scanner fisik terhubung — arahkan QR ke scanner';
            });

            physicalScannerInput.addEventListener('blur', () => {
                scannerStatusDot.classList.add('offline');
                scannerStatusText.textContent = 'Klik area kosong untuk mengaktifkan scanner fisik';
                // Coba refocus setelah 500ms jika tidak ada form yang aktif
                setTimeout(keepScannerFocus, 500);
            });

            // Fokus awal + setiap klik di area kosong
            keepScannerFocus();
            document.addEventListener('click', function(e) {
                const tag = e.target.tagName;
                if (tag !== 'INPUT' && tag !== 'TEXTAREA' && tag !== 'SELECT' && tag !== 'BUTTON' && tag !== 'A') {
                    keepScannerFocus();
                }
            });

            // Ketika user selesai isi form manual, kembalikan fokus ke physical scanner
            document.getElementById('scanInput').addEventListener('blur', function() {
                setTimeout(keepScannerFocus, 300);
            });

            // Physical scanner: listen keypress Enter
            physicalScannerInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const qrCode = physicalScannerInput.value.trim();
                    physicalScannerInput.value = '';
                    if (qrCode) doScan(qrCode, true);
                }
            });

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

            function doScan(qrCode, fromPhysical = false) {
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

                    if (!fromPhysical) {
                        // Clear & refocus form manual
                        document.getElementById('scanInput').value = '';
                    }

                    // Kembalikan fokus ke physical scanner
                    setTimeout(keepScannerFocus, 200);
                })
                .catch(() => {
                    showToast('danger', '❌', 'Error Server', 'Gagal menghubungi server');
                    setTimeout(keepScannerFocus, 200);
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
                doScan(qrCode, false);
            });

            // Tekan Enter di scanInput juga trigger scan
            scanInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const qrCode = scanInput.value.trim();
                    if (!qrCode) return;
                    doScan(qrCode, false);
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
        });
    </script>
@endsection
