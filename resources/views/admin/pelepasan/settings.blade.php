@extends('layouts/layoutMaster')

@section('title', 'Pengaturan Pelepasan')

@section('page-style')
    <style>
        /* ── ANIMATIONS ────────────────────────────────────────── */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(15px); }
            to   { opacity: 1; transform: translateY(0);   }
        }
        .slide-in-up {
            animation: slideInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* ── HERO LOGO PLACEHOLDER & GLOW ─────────────────────── */
        .das-hero__logo-wrapper {
            position: relative;
        }
        .das-hero__logo-placeholder {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.15);
            font-size: 1.8rem;
            position: relative;
            z-index: 2;
        }
        .das-hero__logo-glow {
            position: absolute;
            inset: -5px;
            background: var(--das-warning, #ff9f43);
            filter: blur(15px);
            opacity: 0.25;
            z-index: 1;
            border-radius: 50%;
        }

        /* ── ICON ACTION BUTTONS (same as index) ──────────────── */
        .das-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            font-size: 1.05rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
        }
        .das-icon-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        .das-icon-btn.--danger  { border-color: rgba(234,84,85,0.3); color: #ea5455; }
        .das-icon-btn.--danger:hover  { background: rgba(234,84,85,0.15); box-shadow: 0 4px 12px rgba(234,84,85,0.25); }
        .das-icon-btn.--success { border-color: rgba(40,199,111,0.3); color: #28c76f; }
        .das-icon-btn.--success:hover { background: rgba(40,199,111,0.15); box-shadow: 0 4px 12px rgba(40,199,111,0.25); }
        .das-icon-btn.--primary { border-color: rgba(115,103,240,0.3); color: #a5a2f7; }
        .das-icon-btn.--primary:hover { background: rgba(115,103,240,0.15); box-shadow: 0 4px 12px rgba(115,103,240,0.25); }
        .das-icon-btn.--secondary { border-color: rgba(255,255,255,0.1); color: #94a3b8; }
        .das-icon-btn.--secondary:hover { background: rgba(255,255,255,0.08); }

        /* Tooltip for icon buttons */
        .das-icon-btn::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.97);
            color: #f1f5f9;
            font-size: 0.72rem;
            font-weight: 600;
            white-space: nowrap;
            padding: 5px 10px;
            border-radius: 7px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
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

        /* ── FORM CONTROLS THEMED ─────────────────────────────── */
        .das-form-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 0.5rem;
            letter-spacing: 0.3px;
        }
        .das-form-control {
            background: rgba(255, 255, 255, 0.04) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #f1f5f9 !important;
            border-radius: 5px !important;
            transition: border-color 0.2s, box-shadow 0.2s !important;
            backdrop-filter: blur(6px);
        }
        .das-form-control:focus {
            border-color: rgba(115, 103, 240, 0.5) !important;
            box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.1) !important;
            background: rgba(255, 255, 255, 0.06) !important;
        }
        .das-form-control option {
            background: #1e293b;
            color: #f1f5f9;
        }

        /* ── DETAIL KEGIATAN CARD ─────────────────────────────── */
        .detail-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 5px;
            backdrop-filter: blur(6px);
            transition: all 0.3s ease;
        }
        .detail-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.05);
        }
        .detail-card__label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .detail-card__value {
            font-size: 0.92rem;
            font-weight: 600;
            color: #f1f5f9;
        }

        /* ── THEMED ALERT ──────────────────────────────────────── */
        .das-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 1rem 1.25rem;
            border-radius: 5px;
            border: 1px solid;
            backdrop-filter: blur(6px);
        }
        .das-alert--info {
            background: rgba(0, 207, 232, 0.06);
            border-color: rgba(0, 207, 232, 0.2);
            color: rgba(255, 255, 255, 0.75);
        }
        .das-alert--info .das-alert__icon {
            color: #00cfe8;
            font-size: 1.2rem;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .das-alert__title {
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 2px;
            font-size: 0.85rem;
        }
        .das-alert__text {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.55);
            line-height: 1.5;
        }
    </style>
@endsection

@section('content')
    {{-- ─── HERO HEADER ────────────────────────────────────────────── --}}
    <div class="das-hero mb-4 slide-in-up">
        <div class="das-hero__bg"></div>
        <div class="das-hero__glass"></div>
        <div class="das-hero__grid-lines"></div>
        <div class="das-hero__inner">
            <div class="das-hero__identity">
                <div class="das-hero__logo-wrapper">
                    <div class="das-hero__logo-placeholder">
                        <i class="ti tabler-settings text-warning"></i>
                    </div>
                    <div class="das-hero__logo-glow"></div>
                </div>
                <div class="das-hero__meta">
                    <div class="das-hero__badge">
                        <span class="pulse-dot"></span>
                        Pengaturan / Pelepasan Kelas XII
                    </div>
                    <h4 class="das-hero__title text-gradient-gold">Pengaturan Kegiatan Pelepasan</h4>
                    <p class="das-hero__subtitle">Pilih kegiatan khusus untuk absensi pelepasan kelas XII</p>
                </div>
            </div>
            <div class="das-hero__actions" style="gap:0.5rem;">
                <a href="{{ route('admin.pelepasan.index') }}" class="das-icon-btn --secondary" data-tooltip="Kembali">
                    <i class="ti tabler-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ─── MAIN CONTENT ───────────────────────────────────────────── --}}
    <div class="row g-4 slide-in-up">
        <div class="col-12">

            {{-- ── SUCCESS ALERT ── --}}
            @if(session('success'))
                <div class="das-alert mb-4" style="background:rgba(40,199,111,0.06);border-color:rgba(40,199,111,0.25);">
                    <i class="ti tabler-check-circle" style="color:#28c76f;font-size:1.2rem;flex-shrink:0;margin-top:1px;"></i>
                    <div>
                        <div style="color:#e2e8f0;font-size:0.85rem;">{{ session('success') }}</div>
                    </div>
                </div>
            @endif

            {{-- ── SETTINGS PANEL ── --}}
            <div class="das-panel">
                <div class="das-panel__head">
                    <div class="das-panel__title">
                        <span class="das-panel__icon-dot --warning"></span>
                        Pilih Kegiatan Pelepasan
                    </div>
                </div>
                <div class="das-panel__body">
                    <form action="{{ route('admin.pelepasan.settings.save') }}" method="POST">
                        @csrf

                        {{-- ── SELECT KEGIATAN ── --}}
                        <div class="mb-4">
                            <label for="kegiatan_id" class="das-form-label">Pilih Kegiatan</label>
                            <select name="kegiatan_id" id="kegiatan_id" class="form-select das-form-control">
                                <option value="">-- Pilih Kegiatan Pelepasan --</option>
                                @foreach($kegiatans as $k)
                                    <option value="{{ $k->id }}" class="bg-dark" {{ $currentKegiatanId == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kegiatan }} @if($k->tanggal_pelaksanaan)({{ $k->tanggal_pelaksanaan->format('d M Y') }})@else(Fleksibel)@endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-2" style="font-size:0.78rem;color:#64748b;">
                                Kegiatan yang muncul adalah kegiatan bertipe <strong style="color:#94a3b8;">LAINNYA</strong> atau <strong style="color:#94a3b8;">PELEPASAN</strong> pada tahun akademik aktif.
                            </div>
                        </div>

                        {{-- ── DETAIL KEGIATAN ── --}}
                        <div id="kegiatan-detail" class="detail-card p-3 mb-4 d-none">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h6 class="text-white fw-bold mb-0" style="font-size:0.82rem;letter-spacing:0.4px;text-transform:uppercase;">
                                    <i class="ti tabler-info-circle me-1" style="color:#7367f0;"></i>
                                    Detail Kegiatan Terpilih
                                </h6>
                                <a href="#" id="edit-kegiatan-link" class="das-btn --warning" style="font-size:0.7rem;padding:0.3rem 0.65rem;">
                                    <i class="ti tabler-edit me-1"></i> Ubah
                                </a>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="detail-card__label">Tanggal Pelaksanaan</div>
                                    <span id="detail-tanggal" class="detail-card__value">-</span>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-card__label">Lokasi</div>
                                    <span id="detail-lokasi" class="detail-card__value">-</span>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-card__label">Waktu</div>
                                    <span id="detail-waktu" class="detail-card__value">-</span>
                                </div>
                            </div>
                        </div>

                        {{-- ── INFO ALERT ── --}}
                        <div class="das-alert das-alert--info mb-4">
                            <i class="ti tabler-info-circle das-alert__icon"></i>
                            <div>
                                <div class="das-alert__title">Belum ada kegiatan pelepasan?</div>
                                <p class="das-alert__text mb-2">Anda bisa membuat kegiatan baru terlebih dahulu di halaman manajemen kegiatan.</p>
                                <a href="{{ route('admin.kegiatan.create') }}" class="btn das-btn --info" style="font-size:0.72rem;">
                                    <i class="ti tabler-plus"></i> Buat Kegiatan Baru
                                </a>
                            </div>
                        </div>

                        {{-- ── SUBMIT ── --}}
                        <button type="submit" class="btn das-btn --primary">
                            <i class="ti tabler-device-floppy"></i> Simpan Pengaturan
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('kegiatan_id');
            const detail = document.getElementById('kegiatan-detail');
            const kTanggal = document.getElementById('detail-tanggal');
            const kLokasi = document.getElementById('detail-lokasi');
            const kWaktu = document.getElementById('detail-waktu');
            const editLink = document.getElementById('edit-kegiatan-link');

            const kData = {
                @foreach($kegiatans as $k)
                '{{ $k->id }}': {
                    tanggal: '{{ $k->tanggal_pelaksanaan ? $k->tanggal_pelaksanaan->format('l, d F Y') : 'Fleksibel' }}',
                    lokasi: '{{ $k->lokasi ?? "-" }}',
                    waktu: '{{ $k->waktu_mulai && $k->waktu_selesai ? $k->waktu_mulai . ' - ' . $k->waktu_selesai : 'Seharian' }}',
                    editUrl: '{{ route('admin.kegiatan.edit', $k->id) }}'
                },
                @endforeach
            };

            function updateDetail() {
                const val = select.value;
                if (val && kData[val]) {
                    kTanggal.textContent = kData[val].tanggal;
                    kLokasi.textContent = kData[val].lokasi;
                    kWaktu.textContent = kData[val].waktu;
                    editLink.href = kData[val].editUrl;
                    detail.classList.remove('d-none');
                } else {
                    detail.classList.add('d-none');
                }
            }

            select.addEventListener('change', updateDetail);
            updateDetail();
        });
    </script>
@endsection
