@extends('layouts/layoutMaster')

@section('title', 'Naik Kelas Massal')

@section('page-style')
<style>
    /* Step Indicator Styling */
    .step-indicator {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .step {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.4);
    }
    .step.active {
        color: #fff;
        font-weight: 600;
    }
    .step.completed {
        color: #28c76f;
    }
    .step.pending {
        color: rgba(255, 255, 255, 0.25);
    }
    .step-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        flex-shrink: 0;
    }
    .step.active .step-number {
        background: #7367f0;
        border-color: #7367f0;
        box-shadow: 0 0 10px rgba(115, 103, 240, 0.5);
    }
    .step.completed .step-number {
        background: #28c76f;
        border-color: #28c76f;
        box-shadow: 0 0 10px rgba(40, 199, 111, 0.5);
    }
    .step.pending .step-number {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.05);
    }
    .step-line {
        flex: 1;
        height: 2px;
        background: rgba(255, 255, 255, 0.08);
        max-width: 60px;
    }
    .step-line.completed {
        background: #28c76f;
    }
    .step-line.active {
        background: #7367f0;
    }

    /* Table Preview Row Styling */
    .table-preview-row-normal {
        background: transparent;
        transition: all 0.2s ease;
    }
    .table-preview-row-normal:hover {
        background: rgba(255, 255, 255, 0.03);
    }
    .table-preview-row-warning {
        background: rgba(255, 159, 67, 0.04);
        border-left: 3px solid #ff9f43 !important;
        transition: all 0.2s ease;
    }
    .table-preview-row-warning:hover {
        background: rgba(255, 159, 67, 0.08);
    }
    .table-preview-row-danger {
        background: rgba(234, 84, 85, 0.04);
        border-left: 3px solid #ea5455 !important;
        transition: all 0.2s ease;
    }
    .table-preview-row-danger:hover {
        background: rgba(234, 84, 85, 0.08);
    }

    /* Custom Form Select Option Styling */
    .form-select.bg-dark {
        background-color: #2f3349 !important;
        color: #cfd3ec !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }
    .form-select.bg-dark:focus {
        border-color: #7367f0 !important;
        box-shadow: 0 0 0 0.25rem rgba(115, 103, 240, 0.25) !important;
    }
    .form-select.bg-dark option {
        background-color: #2f3349 !important;
        color: #cfd3ec !important;
    }

    .tingkat-badge {
        font-size: 0.7rem;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
    .tingkat-X {
        background: rgba(115, 103, 240, 0.15);
        color: #7367f0;
    }
    .tingkat-XI {
        background: rgba(0, 207, 232, 0.15);
        color: #00cfe8;
    }
    .tingkat-XII {
        background: rgba(234, 84, 85, 0.15);
        color: #ea5455;
    }
    .mapping-arrow {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .result-card {
        background: rgba(40, 199, 111, 0.08);
        border: 1px solid rgba(40, 199, 111, 0.2);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .result-card.danger {
        background: rgba(234, 84, 85, 0.08);
        border-color: rgba(234, 84, 85, 0.2);
    }
    .result-number {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }
    .alumni-warning {
        background: rgba(255, 159, 67, 0.1);
        border-left: 3px solid #ff9f43;
        border-radius: 8px;
        padding: 1rem;
    }

    /* SweetAlert2 Custom Premium Styling */
    .das-swal-popup {
        background: rgba(26, 26, 46, 0.95) !important;
        backdrop-filter: blur(16px) saturate(180%) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 16px !important;
        box-shadow: 0 24px 48px rgba(0, 0, 0, 0.4) !important;
        padding: 2rem 1.5rem !important;
    }
    .das-swal-title {
        color: #fff !important;
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        margin-bottom: 0.75rem !important;
    }
    .das-swal-icon {
        margin: 1.25rem auto 0.75rem auto !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }
    .das-swal-html {
        color: rgba(255, 255, 255, 0.7) !important;
        font-size: 0.9rem !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .das-summary-box {
        background: rgba(15, 17, 28, 0.6) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 12px !important;
        padding: 1.25rem !important;
        margin-top: 1rem !important;
        margin-bottom: 1rem !important;
    }
    .das-summary-table {
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: collapse !important;
    }
    .das-summary-table td {
        padding: 0.6rem 0 !important;
        color: rgba(255, 255, 255, 0.75) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        font-size: 0.85rem !important;
    }
    .das-summary-table tr:last-child td {
        border-bottom: none !important;
    }
    .das-summary-table td.text-end {
        font-weight: 700 !important;
    }
    .das-swal-confirm {
        background: #28c76f !important;
        color: #fff !important;
        border-radius: 8px !important;
        padding: 10px 24px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(40, 199, 111, 0.3) !important;
        transition: all 0.2s ease !important;
    }
    .das-swal-confirm:hover {
        background: #20a65b !important;
        box-shadow: 0 6px 16px rgba(40, 199, 111, 0.4) !important;
    }
    .das-swal-cancel {
        background: rgba(255, 255, 255, 0.05) !important;
        color: rgba(255, 255, 255, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 8px !important;
        padding: 10px 24px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
    }
    .das-swal-cancel:hover {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
    }
</style>
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('content')

<div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>
    <div class="das-hero__inner">
        <div class="das-hero__identity">
            <div class="das-hero__logo-wrapper">
                <div class="das-hero__logo-placeholder">
                    <i class="ti tabler-trending-up text-success"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>
            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot"></span>
                    <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> /
                    <a href="{{ route('admin.siswa.index') }}" class="text-white text-decoration-none">Siswa</a> /
                    Naik Kelas Massal
                </div>
                <h4 class="das-hero__title text-gradient-gold">Naik Kelas Massal</h4>
                <p class="das-hero__subtitle">Promosikan seluruh siswa ke tingkat berikutnya di tahun ajaran baru secara otomatis.</p>
            </div>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
        <i class="ti tabler-circle-check fs-5"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
        <i class="ti tabler-alert-triangle fs-5"></i>
        <span>{{ session('error') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Step Indicator --}}
<div class="step-indicator">
    <div class="step {{ $preview ? 'completed' : 'active' }}">
        <span class="step-number">1</span>
        <span>Pilih Tahun Ajaran</span>
    </div>
    <div class="step-line {{ $preview ? 'completed' : '' }}"></div>
    <div class="step {{ $preview ? (session('naik_kelas_result') ? 'completed' : 'active') : 'pending' }}">
        <span class="step-number">2</span>
        <span>Preview & Konfirmasi</span>
    </div>
    <div class="step-line {{ session('naik_kelas_result') ? 'completed' : '' }}"></div>
    <div class="step {{ session('naik_kelas_result') ? 'completed' : 'pending' }}">
        <span class="step-number">3</span>
        <span>Selesai</span>
    </div>
</div>

{{-- Step 1: Form Pilih TA --}}
<div class="das-panel mb-4">
    <div class="das-panel__header border-bottom py-3 px-4" style="border-color:rgba(255,255,255,0.08) !important;">
        <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
            <i class="ti tabler-calendar-stats text-info"></i> Pilih Tahun Ajaran
        </h6>
    </div>
    <div class="das-panel__body p-4">
        <form method="GET" action="{{ route('admin.siswa.naik-kelas-massal') }}" id="filterForm">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-white-50 small fw-bold">TA SUMBER (Siswa Aktif)</label>
                    <select class="form-select bg-dark border-0 text-white" name="tahun_akademik_asal" id="ta_asal" required>
                        <option value="">— Pilih TA Asal —</option>
                        @foreach($tahunAkademikOptions as $ta)
                            <option value="{{ $ta->id }}" {{ (string) $tahunAkademikAsalId === (string) $ta->id ? 'selected' : '' }}>
                                {{ $ta->nama }} ({{ ucfirst($ta->semester) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white-50 small fw-bold">TA TUJUAN (Tahun Ajaran Baru)</label>
                    <select class="form-select bg-dark border-0 text-white" name="tahun_akademik_tujuan" id="ta_tujuan" required>
                        <option value="">— Pilih TA Tujuan —</option>
                        @foreach($tahunAkademikOptions as $ta)
                            <option value="{{ $ta->id }}" {{ (string) $tahunAkademikTujuanId === (string) $ta->id ? 'selected' : '' }}>
                                {{ $ta->nama }} ({{ ucfirst($ta->semester) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn das-btn --primary">
                        <i class="ti tabler-eye me-1"></i> Lihat Preview
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Step 2: Preview --}}
@if($preview)
<div class="das-panel mb-4">
    <div class="das-panel__header border-bottom py-3 px-4" style="border-color:rgba(255,255,255,0.08) !important;">
        <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
            <i class="ti tabler-list text-warning"></i> Preview Kenaikan Kelas
        </h6>
        <small class="text-white-50">
            Dari <strong class="text-info">{{ $preview['ta_asal']->nama }}</strong>
            → <strong class="text-success">{{ $preview['ta_tujuan']->nama }}</strong>
            (<span id="summary-total-siswa">{{ $preview['total_siswa'] }}</span> siswa akan diproses)
        </small>
    </div>
    <div class="das-panel__body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-borderless mb-0" style="font-size:0.85rem;">
                <thead style="background: rgba(255,255,255,0.03);">
                    <tr>
                        <th class="py-3 px-4 text-white-50 small fw-bold">Kelas Asal</th>
                        <th class="py-3 text-white-50 small fw-bold">Tingkat</th>
                        <th class="py-3 text-white-50 small fw-bold">Jumlah Siswa</th>
                        <th class="py-3 text-white-50 small fw-bold text-center" style="width:40px;"></th>
                        <th class="py-3 text-white-50 small fw-bold">Kelas Tujuan</th>
                        <th class="py-3 text-white-50 small fw-bold">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview['detail'] as $item)
                    @php
                        $rowClass = 'table-preview-row-normal';
                        if ($item['next_tingkat'] === null) {
                            $rowClass = 'table-preview-row-warning';
                        } elseif (!$item['kelas_tujuan']) {
                            $rowClass = 'table-preview-row-danger';
                        }
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td class="py-3 px-4">{{ $item['kelas_asal']->nama }}</td>
                        <td class="py-3">
                            <span class="tingkat-badge {{ 'tingkat-' . $item['tingkat'] }}">{{ $item['tingkat'] }}</span>
                        </td>
                        <td class="py-3">
                            <span class="fw-bold">{{ $item['jumlah_siswa'] }}</span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="mapping-arrow"><i class="ti tabler-arrow-right"></i></span>
                        </td>
                        <td class="py-3">
                            @if($item['next_tingkat'] === null)
                                <span class="badge bg-label-warning">ALUMNI</span>
                            @elseif($item['kelas_tujuan'])
                                <span class="text-white">{{ $item['kelas_tujuan']->nama }}</span>
                            @else
                                <span class="badge bg-label-danger">TIDAK DITEMUKAN</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($item['keterangan'])
                                <small class="text-white-50">{{ $item['keterangan'] }}</small>
                            @else
                                <small class="text-success">Siap diproses</small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="das-panel__footer border-top p-4 d-flex align-items-center justify-content-between flex-wrap gap-3" style="border-color:rgba(255,255,255,0.08) !important;">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="text-white-50 small">Total: <strong class="text-white">{{ $preview['total_siswa'] }} siswa</strong></span>
            @php
                $xiiCount = collect($preview['detail'])->where('tingkat', 'XII')->sum('jumlah_siswa');
                $notFound = collect($preview['detail'])->where('bisa_diproses', false)->sum('jumlah_siswa');
                $normalCount = $preview['total_siswa'] - $xiiCount - $notFound;
            @endphp
            <span class="d-none" id="summary-normal">{{ $normalCount }}</span>
            @if($xiiCount > 0)
                <span class="badge bg-label-warning"><span id="summary-alumni">{{ $xiiCount }}</span> siswa akan menjadi alumni</span>
            @else
                <span class="d-none" id="summary-alumni">0</span>
            @endif
            @if($notFound > 0)
                <span class="badge bg-label-danger"><span id="summary-error">{{ $notFound }}</span> siswa tidak bisa diproses</span>
            @else
                <span class="d-none" id="summary-error">0</span>
            @endif
        </div>
        <form method="POST" action="{{ route('admin.siswa.naik-kelas-massal.execute') }}" id="executeForm">
            @csrf
            <input type="hidden" name="tahun_akademik_asal" value="{{ $tahunAkademikAsalId }}">
            <input type="hidden" name="tahun_akademik_tujuan" value="{{ $tahunAkademikTujuanId }}">
            <button type="button" class="btn das-btn --success" id="executeBtn"
                @if($notFound > 0) disabled title="Ada kelas tujuan yang tidak ditemukan" @endif>
                <i class="ti tabler-player-play me-1"></i> Proses Naik Kelas Massal
            </button>
        </form>
    </div>
</div>
@endif

{{-- Step 3: Result --}}
@if(session('naik_kelas_result'))
@php $result = session('naik_kelas_result'); @endphp
<div class="das-panel mb-4">
    <div class="das-panel__header border-bottom py-3 px-4" style="border-color:rgba(255,255,255,0.08) !important;">
        <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
            <i class="ti tabler-check-circle text-success"></i> Hasil Eksekusi
        </h6>
    </div>
    <div class="das-panel__body p-4">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="result-card text-center">
                    <div class="result-number text-success">{{ $result['success'] }}</div>
                    <div class="text-white-50 small">Siswa Sukses</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="result-card {{ $result['failed'] > 0 ? 'danger' : '' }} text-center">
                    <div class="result-number {{ $result['failed'] > 0 ? 'text-danger' : 'text-white' }}">{{ $result['failed'] }}</div>
                    <div class="text-white-50 small">Siswa Gagal</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="result-card text-center">
                    <div class="result-number text-info">{{ $result['success'] + $result['failed'] }}</div>
                    <div class="text-white-50 small">Total Diproses</div>
                </div>
            </div>
        </div>

        @if(collect($result['details'])->where('sukses', '>', 0)->count() > 0)
        <div class="table-responsive">
            <table class="table table-dark table-borderless mb-0" style="font-size:0.85rem;">
                <thead style="background: rgba(255,255,255,0.03);">
                    <tr>
                        <th class="py-3 px-4 text-white-50 small fw-bold">Kelas Asal</th>
                        <th class="py-3 text-white-50 small fw-bold">Tingkat</th>
                        <th class="py-3 text-white-50 small fw-bold">Total</th>
                        <th class="py-3 text-white-50 small fw-bold text-success">Sukses</th>
                        <th class="py-3 text-white-50 small fw-bold text-danger">Gagal</th>
                        <th class="py-3 text-white-50 small fw-bold">Kelas Tujuan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($result['details'] as $det)
                    <tr class="siswa-row-hover">
                        <td class="py-3 px-4">{{ $det['kelas_asal'] }}</td>
                        <td class="py-3"><span class="tingkat-badge {{ 'tingkat-' . $det['tingkat'] }}">{{ $det['tingkat'] }}</span></td>
                        <td class="py-3">{{ $det['jumlah'] }}</td>
                        <td class="py-3 text-success fw-bold">{{ $det['sukses'] }}</td>
                        <td class="py-3 {{ $det['gagal'] > 0 ? 'text-danger fw-bold' : 'text-white-50' }}">{{ $det['gagal'] }}</td>
                        <td class="py-3">{{ $det['kelas_tujuan'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @php
            $xiiCount = collect($result['details'])->where('tingkat', 'XII')->sum('jumlah');
        @endphp
        @if($xiiCount > 0)
        <div class="alumni-warning mt-4">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="ti tabler-alert-triangle text-warning"></i>
                <strong class="text-warning">Perhatian!</strong>
            </div>
            <p class="mb-0 text-white-50 small">
                <strong>{{ $xiiCount }} siswa kelas XII</strong> telah diubah statusnya menjadi <strong>alumni</strong>.
                Data absensi mereka tetap tersimpan. Untuk melihat data alumni, filter status di halaman daftar siswa.
            </p>
        </div>
        @endif
    </div>
</div>
@endif

<div class="d-flex gap-2 mt-4">
    <a href="{{ route('admin.siswa.index') }}" class="btn das-btn --secondary">
        <i class="ti tabler-arrow-left me-1"></i> Kembali ke Daftar Siswa
    </a>
    @if($preview)
    <a href="{{ route('admin.siswa.naik-kelas-massal') }}" class="btn das-btn --secondary">
        <i class="ti tabler-refresh me-1"></i> Reset
    </a>
    @endif
</div>

@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const taAsal = document.getElementById('ta_asal');
    const taTujuan = document.getElementById('ta_tujuan');
    const filterForm = document.getElementById('filterForm');

    if (taAsal && taTujuan) {
        function validateForm() {
            if (taAsal.value && taTujuan.value && taAsal.value === taTujuan.value) {
                taTujuan.setCustomValidity('TA tujuan harus berbeda dari TA asal.');
            } else {
                taTujuan.setCustomValidity('');
            }
        }
        taAsal.addEventListener('change', validateForm);
        taTujuan.addEventListener('change', validateForm);
    }

    const executeBtn = document.getElementById('executeBtn');
    if (executeBtn) {
        executeBtn.addEventListener('click', function() {
            const totalSiswa = document.getElementById('summary-total-siswa')?.innerText || '0';
            const alumniSiswa = document.getElementById('summary-alumni')?.innerText || '0';
            const errorSiswa = document.getElementById('summary-error')?.innerText || '0';
            const normalSiswa = document.getElementById('summary-normal')?.innerText || '0';

            Swal.fire({
                title: 'Konfirmasi Naik Kelas Massal',
                html: `
                    <div class="text-start">
                        <p class="mb-3 text-center text-white-50">Apakah Anda yakin ingin memproses kenaikan kelas massal ini?</p>
                        <div class="das-summary-box">
                            <h6 class="text-white mb-2 fs-6 d-flex align-items-center gap-2">
                                <i class="ti tabler-info-circle text-info"></i> Ringkasan Proses
                            </h6>
                            <table class="das-summary-table">
                                <tr>
                                    <td><i class="ti tabler-users text-primary me-2"></i>Total Siswa Terdeteksi</td>
                                    <td class="text-end text-primary">${totalSiswa}</td>
                                </tr>
                                <tr>
                                    <td><i class="ti tabler-circle-check text-success me-2"></i>Naik Kelas (Normal)</td>
                                    <td class="text-end text-success">${normalSiswa}</td>
                                </tr>
                                <tr>
                                    <td><i class="ti tabler-school text-warning me-2"></i>Lulus (Menjadi Alumni)</td>
                                    <td class="text-end text-warning">${alumniSiswa}</td>
                                </tr>
                                <tr>
                                    <td><i class="ti tabler-circle-x text-danger me-2"></i>Tidak Bisa Diproses</td>
                                    <td class="text-end text-danger">${errorSiswa}</td>
                                </tr>
                            </table>
                        </div>
                        <p class="mt-3 text-danger text-center small fw-bold mb-0">
                            <i class="ti tabler-alert-triangle me-1"></i> Tindakan ini tidak dapat dibatalkan!
                        </p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Proses!',
                cancelButtonText: 'Batalkan',
                customClass: {
                    popup: 'das-swal-popup',
                    title: 'das-swal-title',
                    htmlContainer: 'das-swal-html',
                    confirmButton: 'das-swal-confirm me-2',
                    cancelButton: 'das-swal-cancel',
                    icon: 'das-swal-icon'
                },
                buttonsStyling: false,
                showClass: { popup: 'animate__animated animate__fadeInUp animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOutDown animate__faster' },
                background: 'transparent',
                backdrop: 'rgba(0,0,10,0.5)',
            }).then((result) => {
                if (result.isConfirmed) {
                    executeBtn.disabled = true;
                    executeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
                    document.getElementById('executeForm').submit();
                }
            });
        });
    }
});
</script>
@endsection
