@extends('layouts/layoutMaster')

@section('title', 'Manajemen Lisensi')

@section('page-style')
<style>
    :root {
        --das-primary: #7367f0;
        --das-success: #28c76f;
        --das-danger: #ea5455;
        --das-warning: #ff9f43;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.03) !important;
        backdrop-filter: blur(12px) saturate(180%);
        -webkit-backdrop-filter: blur(12px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        border-color: rgba(115, 103, 240, 0.3) !important;
        box-shadow: 0 8px 32px rgba(115, 103, 240, 0.15);
    }

    .license-header {
        position: relative;
        padding: 2.5rem;
        background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .license-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle at top right, rgba(115, 103, 240, 0.2), transparent 50%);
        z-index: 1;
    }

    .license-header__content {
        position: relative;
        z-index: 2;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge--active {
        background: rgba(40, 199, 111, 0.15);
        color: #28c76f;
        border: 1px solid rgba(40, 199, 111, 0.3);
    }

    .status-badge--inactive {
        background: rgba(234, 84, 85, 0.15);
        color: #ea5455;
        border: 1px solid rgba(234, 84, 85, 0.3);
    }

    .info-label {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.5);
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .info-value {
        font-size: 1rem;
        color: #fff;
        font-weight: 600;
    }

    .license-key-box {
        background: rgba(0, 0, 0, 0.2);
        border: 1px dashed rgba(255, 255, 255, 0.2);
        padding: 12px 16px;
        border-radius: 8px;
        font-family: 'Courier New', Courier, monospace;
        letter-spacing: 1px;
        color: #a5b4fc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-action {
        border-radius: 8px;
        font-weight: 600;
        padding: 10px 20px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-action:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="license-header">
        <div class="license-header__content d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="text-white fw-bold mb-1">Status Lisensi</h3>
                <p class="text-white-50 mb-0">Manajemen aktivasi dan verifikasi lisensi aplikasi</p>
            </div>
            <div class="status-badge {{ $status === 'active' ? 'status-badge--active' : 'status-badge--inactive' }}">
                <span class="dot" style="width: 8px; height: 8px; border-radius: 50%; background: currentColor;"></span>
                {{ $status === 'active' ? 'Terverifikasi' : 'Tidak Aktif' }}
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Detail Lisensi --}}
        <div class="col-lg-8">
            <div class="card glass-card h-100">
                <div class="card-body p-4">
                    <h5 class="text-white mb-4">Detail Lisensi</h5>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">Nama Lembaga</div>
                            <div class="info-value">{{ $schoolName }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Domain Terdaftar</div>
                            <div class="info-value text-primary">{{ $domain ?: '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="info-label">License Key</div>
                            <div class="license-key-box mt-2">
                                <span>{{ $licenseKey ? substr($licenseKey, 0, 8) . '-xxxx-xxxx-' . substr($licenseKey, -4) : 'BELUM DIAKTIFKAN' }}</span>
                                <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" onclick="copyToClipboard('{{ $licenseKey }}')" title="Salin License Key">
                                    <i class="ti tabler-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-action" onclick="window.location.reload()">
                            <i class="ti tabler-refresh"></i>
                            Cek Status Sekarang
                        </button>
                        @if($status !== 'active')
                        <a href="{{ route('license.warning') }}" class="btn btn-success btn-action">
                            <i class="ti tabler-key"></i>
                            Aktivasi Ulang
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Server --}}
        <div class="col-lg-4">
            <div class="card glass-card h-100">
                <div class="card-body p-4">
                    <h5 class="text-white mb-4">Informasi Tambahan</h5>
                    
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start gap-3 mb-3">
                            <div class="avatar avatar-sm bg-label-info rounded">
                                <i class="ti tabler-calendar"></i>
                            </div>
                            <div>
                                <div class="text-white-50 small">Pengecekan Terakhir</div>
                                <div class="text-white fw-semibold">{{ now()->format('d M Y H:i') }}</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3 mb-3">
                            <div class="avatar avatar-sm bg-label-success rounded">
                                <i class="ti tabler-shield-check"></i>
                            </div>
                            <div>
                                <div class="text-white-50 small">Tipe Lisensi</div>
                                <div class="text-white fw-semibold">Standard License</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3">
                            <div class="avatar avatar-sm bg-label-warning rounded">
                                <i class="ti tabler-help"></i>
                            </div>
                            <div>
                                <div class="text-white-50 small">Butuh Bantuan?</div>
                                <div class="text-white fw-semibold"><a href="#" class="text-warning">Hubungi Support</a></div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            alert('License Key berhasil disalin!');
        });
    }
</script>
@endsection
