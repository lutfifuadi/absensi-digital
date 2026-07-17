@extends('layouts/layoutMaster')

@section('title', 'Detail Pengaduan - ' . $pengaduan->kode_unik)

@section('page-style')
<style>
    .detail-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 5px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .detail-card:hover {
        border-color: rgba(255, 255, 255, 0.12);
    }
    .detail-card__header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        background: rgba(115, 103, 240, 0.04);
        padding: 1rem 1.5rem;
    }
    .detail-card__title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #fff;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .detail-card__body {
        padding: 1.5rem;
    }

    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: rgba(255, 255, 255, 0.4);
        margin-bottom: 0.25rem;
    }
    .info-value {
        font-size: 0.95rem;
        color: #fff;
        font-weight: 500;
    }
    .info-value-lg {
        font-size: 1.1rem;
    }

    /* Badge status besar */
    .badge-status-lg {
        padding: 0.5rem 1.25rem;
        font-size: 0.9rem;
        font-weight: 700;
        border-radius: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-status-baru {
        background: rgba(255, 159, 67, 0.15) !important;
        color: #ff9f43 !important;
        border: 1px solid rgba(255, 159, 67, 0.3);
    }
    .badge-status-diproses {
        background: rgba(0, 207, 232, 0.15) !important;
        color: #00cfe8 !important;
        border: 1px solid rgba(0, 207, 232, 0.3);
    }
    .badge-status-selesai {
        background: rgba(40, 199, 111, 0.15) !important;
        color: #28c76f !important;
        border: 1px solid rgba(40, 199, 111, 0.3);
    }
    .badge-status-ditolak {
        background: rgba(234, 84, 85, 0.15) !important;
        color: #ea5455 !important;
        border: 1px solid rgba(234, 84, 85, 0.3);
    }

    /* Timeline / History */
    .timeline {
        position: relative;
        padding-left: 1.5rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 5px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: rgba(255, 255, 255, 0.1);
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.75rem;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #1a1a2e;
        border: 2px solid rgba(255, 255, 255, 0.2);
        z-index: 1;
    }
    .timeline-item.timeline-baru::before {
        border-color: #ff9f43;
        background: rgba(255, 159, 67, 0.2);
    }
    .timeline-item.timeline-diproses::before {
        border-color: #00cfe8;
        background: rgba(0, 207, 232, 0.2);
    }
    .timeline-item.timeline-selesai::before {
        border-color: #28c76f;
        background: rgba(40, 199, 111, 0.2);
    }
    .timeline-item.timeline-ditolak::before {
        border-color: #ea5455;
        background: rgba(234, 84, 85, 0.2);
    }
    .timeline-item .timeline-time {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.35);
        margin-bottom: 0.15rem;
    }
    .timeline-item .timeline-content {
        font-size: 0.88rem;
        color: rgba(255, 255, 255, 0.8);
    }
    .timeline-item .timeline-content strong {
        color: #fff;
    }
    .timeline-item .timeline-note {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.5);
        margin-top: 0.25rem;
        padding: 0.5rem 0.75rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 5px;
        border-left: 3px solid rgba(255, 255, 255, 0.1);
    }

    .text-gradient-gold {
        background: linear-gradient(135deg, #f5af19, #f12711);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
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

    .form-textarea {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #fff;
        font-size: 0.88rem;
        border-radius: 5px;
        transition: all 0.2s ease;
    }
    .form-textarea:focus {
        outline: none;
        box-shadow: none;
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: rgba(115, 103, 240, 0.5) !important;
        color: #fff;
    }
    .form-textarea::placeholder {
        color: rgba(255, 255, 255, 0.3);
    }

    .wa-link {
        color: #25D366;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .wa-link:hover {
        color: #128C7E;
        text-decoration: underline;
    }

    .das-btn.--primary {
        background: linear-gradient(135deg, #7367f0, #9e95f5);
        border: none;
        color: #fff;
        font-weight: 600;
        padding: 0.5rem 1.5rem;
        border-radius: 5px;
        transition: all 0.2s ease;
    }
    .das-btn.--primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(115, 103, 240, 0.35);
    }
    .das-btn.--primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    .das-btn.--secondary {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.7);
        font-weight: 500;
        padding: 0.5rem 1.25rem;
        border-radius: 5px;
        transition: all 0.2s ease;
    }
    .das-btn.--secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
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
                    <i class="ti tabler-report-search text-warning"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>

            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot"></span>
                    <a href="{{ route('admin.pengaduan.index') }}" class="text-white text-decoration-none">Pengaduan</a>
                    / Detail
                </div>
                <h4 class="das-hero__title text-gradient-gold">{{ $pengaduan->kode_unik }}</h4>
                <p class="das-hero__subtitle">Laporan pengaduan data tidak valid dari <strong>{{ $pengaduan->nama_lengkap }}</strong>.</p>
            </div>
        </div>

        <div class="das-hero__actions">
            <a href="{{ route('admin.pengaduan.index') }}" class="btn das-btn --secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

{{-- FLASH MESSAGES --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
        role="alert" style="border-radius:5px !important;">
        <i class="ti tabler-circle-check fs-5"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
        role="alert" style="border-radius:5px !important;">
        <i class="ti tabler-alert-triangle fs-5"></i>
        <span>{{ session('error') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
        role="alert" style="border-radius:5px !important;">
        <i class="ti tabler-alert-circle fs-5"></i>
        <div>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- ═══ KOLOM KIRI: Informasi Pengaduan ═══ --}}
    <div class="col-12 col-lg-8">
        {{-- Card Informasi --}}
        <div class="detail-card mb-4">
            <div class="detail-card__header">
                <h5 class="detail-card__title">
                    <i class="ti tabler-info-circle text-info"></i> Informasi Pengaduan
                </h5>
            </div>
            <div class="detail-card__body">
                <div class="row g-4">
                    <div class="col-12 col-sm-6">
                        <div class="info-label">Kode Unik</div>
                        <div class="info-value info-value-lg">{{ $pengaduan->kode_unik }}</div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="info-label">Status</div>
                        <div>
                            <span class="badge badge-status-lg badge-status-{{ $pengaduan->status }}">
                                {{ $pengaduan->status_label }}
                            </span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-value">{{ $pengaduan->nama_lengkap }}</div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="info-label">Status Pelapor</div>
                        <div>
                            @if($pengaduan->status_pelapor === 'siswa')
                                <span class="badge bg-label-info text-capitalize px-3 py-1">
                                    <i class="ti tabler-user me-1"></i>Siswa
                                </span>
                            @else
                                <span class="badge bg-label-warning text-capitalize px-3 py-1">
                                    <i class="ti tabler-user-heart me-1"></i>Orang Tua
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="info-label">No. WhatsApp</div>
                        <div>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pengaduan->nomor_wa) }}"
                                target="_blank" class="wa-link">
                                <i class="ti tabler-brand-whatsapp me-1"></i>{{ $pengaduan->nomor_wa }}
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="info-label">Kategori</div>
                        <div class="info-value">{{ $pengaduan->kategori }}</div>
                    </div>
                    <div class="col-12">
                        <div class="info-label">Tanggal Masuk</div>
                        <div class="info-value">{{ $pengaduan->created_at->format('d F Y H:i:s') }}</div>
                    </div>
                    <div class="col-12">
                        <div class="info-label">Deskripsi Pengaduan</div>
                        <div class="info-value" style="white-space: pre-wrap; line-height: 1.7; background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 5px !important; border: 1px solid rgba(255,255,255,0.06);">
                            {{ $pengaduan->deskripsi ?? '(Tidak ada deskripsi)' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline / Riwayat Status --}}
        <div class="detail-card">
            <div class="detail-card__header">
                <h5 class="detail-card__title">
                    <i class="ti tabler-history text-info"></i> Riwayat Status
                </h5>
            </div>
            <div class="detail-card__body">
                @php
                    $allLogs = collect();

                    // Add creation as first timeline entry
                    $allLogs->push((object) [
                        'status_dari' => '-',
                        'status_ke' => 'baru',
                        'catatan' => 'Pengaduan dibuat oleh sistem.',
                        'diubah_oleh' => 'sistem',
                        'created_at' => $pengaduan->created_at,
                        'is_creation' => true,
                    ]);

                    // Add existing logs
                    foreach ($pengaduan->logs as $log) {
                        $log->is_creation = false;
                        $allLogs->push($log);
                    }

                    // Sort by created_at ascending
                    $allLogs = $allLogs->sortBy('created_at');
                @endphp

                @if($allLogs->count() > 0)
                    <div class="timeline">
                        @foreach($allLogs as $log)
                            <div class="timeline-item timeline-{{ $log->status_ke }}">
                                <div class="timeline-time">
                                    {{ $log->created_at->format('d M Y H:i:s') }}
                                    @if(!$log->is_creation)
                                        &middot; oleh {{ str_replace('admin:', '', $log->diubah_oleh) }}
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    @if($log->is_creation)
                                        Pengaduan dibuat dengan status <strong>{{ ucfirst($log->status_ke) }}</strong>
                                    @else
                                        Status berubah dari <strong>{{ ucfirst($log->status_dari) }}</strong>
                                        ke <strong>{{ ucfirst($log->status_ke) }}</strong>
                                    @endif
                                </div>
                                @if($log->catatan)
                                    <div class="timeline-note">
                                        <i class="ti tabler-message me-1" style="font-size:0.7rem;"></i>
                                        {{ $log->catatan }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-white-50">
                        <i class="ti tabler-history" style="font-size:2rem;"></i>
                        <p class="mt-2 small">Belum ada riwayat perubahan status.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ KOLOM KANAN: Update Status ═══ --}}
    <div class="col-12 col-lg-4">
        @if(in_array($pengaduan->status, ['selesai', 'ditolak']))
            {{-- Status sudah final --}}
            <div class="detail-card">
                <div class="detail-card__header">
                    <h5 class="detail-card__title">
                        <i class="ti tabler-lock text-muted"></i> Status Final
                    </h5>
                </div>
                <div class="detail-card__body text-center py-4">
                    <div class="mb-3">
                        <span class="badge badge-status-lg badge-status-{{ $pengaduan->status }}">
                            {{ $pengaduan->status_label }}
                        </span>
                    </div>
                    <p class="text-white-50 small mb-0">
                        <i class="ti tabler-info-circle me-1"></i>
                        Status ini sudah final dan tidak dapat diubah lagi.
                    </p>
                    @if($pengaduan->verified_at)
                        <p class="text-white-50 extra-small mt-2 mb-0">
                            Diverifikasi pada: {{ $pengaduan->verified_at->format('d M Y H:i') }}
                        </p>
                    @endif
                    @if($pengaduan->catatan_admin)
                        <div class="mt-3 p-3 rounded-2 text-start" style="background: rgba(255,255,255,0.03); border-left: 3px solid currentColor;">
                            <div class="text-white-50 small mb-1">Catatan Admin:</div>
                            <div class="text-white" style="font-size:0.88rem;">{{ $pengaduan->catatan_admin }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Form Update Status --}}
            <div class="detail-card">
                <div class="detail-card__header">
                    <h5 class="detail-card__title">
                        <i class="ti tabler-edit text-warning"></i> Update Status
                    </h5>
                </div>
                <div class="detail-card__body">
                    <form action="{{ route('admin.pengaduan.update-status', $pengaduan->id) }}"
                        method="POST" id="updateStatusForm">
                        @csrf

                        {{-- Info status saat ini --}}
                        <div class="text-center mb-4">
                            <div class="info-label mb-2">Status Saat Ini</div>
                            <span class="badge badge-status-lg badge-status-{{ $pengaduan->status }}">
                                {{ $pengaduan->status_label }}
                            </span>
                        </div>

                        {{-- Dropdown Status --}}
                        <div class="mb-3">
                            <label for="status" class="form-label text-white-50 small">Ubah ke Status</label>
                            <select name="status" id="statusSelect"
                                class="form-select filter-select"
                                required>
                                <option value="">— Pilih Status —</option>
                                @foreach($availableStatuses as $st)
                                    <option value="{{ $st }}" {{ old('status') == $st ? 'selected' : '' }}>
                                        {{ ucfirst($st) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="statusFeedback">
                                Silakan pilih status tujuan.
                            </div>
                        </div>

                        {{-- Textarea Catatan --}}
                        <div class="mb-3">
                            <label for="catatan" class="form-label text-white-50 small">
                                Catatan / Tanggapan
                                <span class="text-danger" id="catatanRequired">*</span>
                            </label>
                            <textarea name="catatan" id="catatanTextarea" rows="4"
                                class="form-control form-textarea"
                                placeholder="{{ $pengaduan->status === 'diproses' ? 'Berikan catatan (wajib untuk Selesai/Ditolak)...' : 'Berikan catatan...' }}">{{ old('catatan') }}</textarea>
                            <div class="invalid-feedback" id="catatanFeedback">
                                Catatan wajib diisi untuk status Selesai atau Ditolak.
                            </div>
                            <small class="text-white-50 extra-small" id="catatanCounter">0 / 500</small>
                        </div>

                        {{-- Tombol --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn das-btn --primary" id="submitBtn">
                                <i class="ti tabler-device-floppy me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Info tambahan --}}
        <div class="detail-card mt-4">
            <div class="detail-card__header">
                <h5 class="detail-card__title">
                    <i class="ti tabler-info-circle text-muted"></i> Informasi
                </h5>
            </div>
            <div class="detail-card__body">
                <ul class="list-unstyled mb-0 text-white-50 small" style="line-height: 1.8;">
                    <li><i class="ti tabler-arrow-right text-info me-1"></i> Status <strong class="text-white">Baru</strong> → dapat diubah ke <strong class="text-info">Diproses</strong></li>
                    <li><i class="ti tabler-arrow-right text-info me-1"></i> Status <strong class="text-white">Diproses</strong> → dapat diubah ke <strong class="text-success">Selesai</strong> atau <strong class="text-danger">Ditolak</strong></li>
                    <li><i class="ti tabler-arrow-right text-info me-1"></i> Status <strong class="text-success">Selesai</strong> / <strong class="text-danger">Ditolak</strong> bersifat final</li>
                    <li class="mt-2"><i class="ti tabler-alert-triangle text-warning me-1"></i> Catatan wajib diisi jika status <strong class="text-danger">Ditolak</strong> atau <strong class="text-success">Selesai</strong></li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('updateStatusForm');
        if (!form) return;

        const statusSelect = document.getElementById('statusSelect');
        const catatanTextarea = document.getElementById('catatanTextarea');
        const catatanCounter = document.getElementById('catatanCounter');
        const catatanRequired = document.getElementById('catatanRequired');
        const submitBtn = document.getElementById('submitBtn');
        const catatanFeedback = document.getElementById('catatanFeedback');
        const statusFeedback = document.getElementById('statusFeedback');

        // Validasi client-side
        function validateForm() {
            let isValid = true;

            // Reset errors
            statusSelect.classList.remove('is-invalid');
            catatanTextarea.classList.remove('is-invalid');

            // Status harus dipilih
            if (!statusSelect.value) {
                statusSelect.classList.add('is-invalid');
                isValid = false;
            }

            // Catatan required untuk selesai/ditolak
            const selectedStatus = statusSelect.value;
            const requiresNote = ['selesai', 'ditolak'].includes(selectedStatus);
            const catatanValue = catatanTextarea.value.trim();

            if (requiresNote && !catatanValue) {
                catatanTextarea.classList.add('is-invalid');
                isValid = false;
            }

            // Tampilkan/sembunyikan required indicator
            if (requiresNote) {
                catatanRequired.style.display = 'inline';
            } else {
                catatanRequired.style.display = 'none';
            }

            submitBtn.disabled = !isValid;
            return isValid;
        }

        // Event listeners
        if (statusSelect) {
            statusSelect.addEventListener('change', validateForm);
        }
        if (catatanTextarea) {
            catatanTextarea.addEventListener('input', function() {
                const len = this.value.length;
                catatanCounter.textContent = len + ' / 500';
                if (len > 500) {
                    this.value = this.value.substring(0, 500);
                    catatanCounter.textContent = '500 / 500';
                }
                validateForm();
            });
        }

        // Initial validation
        validateForm();

        // Form submit validation
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                // Focus first error
                if (statusSelect.classList.contains('is-invalid')) {
                    statusSelect.focus();
                } else if (catatanTextarea.classList.contains('is-invalid')) {
                    catatanTextarea.focus();
                }
                return;
            }

            // Disable button on submit
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...';
        });
    });
</script>
@endsection
