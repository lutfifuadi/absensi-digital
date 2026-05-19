@extends('layouts/layoutMaster')

@section('title', 'Naik Kelas Massal')

@section('page-style')
<style>
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
    }
    .step.completed {
        color: #28c76f;
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
    }
    .step.completed .step-number {
        background: #28c76f;
        border-color: #28c76f;
    }
    .step-line {
        flex: 1;
        height: 1px;
        background: rgba(255, 255, 255, 0.08);
        max-width: 60px;
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
    <div class="step-line"></div>
    <div class="step {{ $preview ? 'active' : '' }}">
        <span class="step-number">2</span>
        <span>Preview & Konfirmasi</span>
    </div>
    <div class="step-line"></div>
    <div class="step {{ session('naik_kelas_result') ? 'completed' : '' }}">
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
            ({{ $preview['total_siswa'] }} siswa akan diproses)
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
                    <tr class="siswa-row-hover">
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
        <div class="d-flex align-items-center gap-3">
            <span class="text-white-50 small">Total: <strong class="text-white">{{ $preview['total_siswa'] }} siswa</strong></span>
            @php
                $xiiCount = collect($preview['detail'])->where('tingkat', 'XII')->sum('jumlah_siswa');
                $notFound = collect($preview['detail'])->where('bisa_diproses', false)->sum('jumlah_siswa');
            @endphp
            @if($xiiCount > 0)
                <span class="badge bg-label-warning">{{ $xiiCount }} siswa akan menjadi alumni</span>
            @endif
            @if($notFound > 0)
                <span class="badge bg-label-danger">{{ $notFound }} siswa tidak bisa diproses</span>
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
            Swal.fire({
                title: 'Konfirmasi Naik Kelas Massal',
                html: `
                    <div class="mt-2">
                        Semua siswa akan dipromosikan ke tingkat berikutnya.<br>
                        Siswa <strong class="text-warning">XII</strong> akan menjadi <strong>alumni</strong>.<br><br>
                        Tindakan ini <strong class="text-danger">tidak dapat dibatalkan</strong>.
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
                    confirmButton: 'btn btn-success das-swal-confirm me-2',
                    cancelButton: 'btn das-swal-cancel',
                    icon: 'das-swal-icon'
                },
                buttonsStyling: false,
                showClass: { popup: 'animate__animated animate__fadeInUp animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOutDown animate__faster' },
                background: 'transparent',
                backdrop: 'rgba(0,0,10,0.4)',
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
