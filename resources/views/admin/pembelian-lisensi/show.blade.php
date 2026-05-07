@extends('layouts/layoutMaster')

@section('title', 'Detail Pembelian Lisensi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / <a href="{{ route('admin.pembelian-lisensi.index') }}">Pembelian Lisensi</a> /</span>
        Detail
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            {{ session('info') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Klien</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.pembelian-lisensi.edit', $pembelian) }}"
                           class="btn btn-sm btn-warning">
                            <i class="ti ti-edit me-1"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('admin.pembelian-lisensi.destroy', $pembelian) }}"
                              onsubmit="return confirm('Hapus data ini secara permanen?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="ti ti-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width:35%" class="text-muted">Nama Klien</th>
                            <td>{{ $pembelian->nama_klien }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Email</th>
                            <td>{{ $pembelian->email_klien }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Domain</th>
                            <td>
                                @if($pembelian->domain)
                                    <code>{{ $pembelian->domain }}</code>
                                @else
                                    <span class="text-muted fst-italic">Belum diset (akan terisi otomatis saat aktivasi)</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status Pembayaran</th>
                            <td>
                                <span class="badge rounded-pill {{ $pembelian->payment_status === 'lunas' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($pembelian->payment_status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status Lisensi</th>
                            <td>
                                @php
                                    $badgeColor = match($pembelian->status) {
                                        'active'  => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'expired' => 'bg-secondary',
                                        'revoked' => 'bg-danger',
                                        default   => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $badgeColor }}">{{ ucfirst($pembelian->status) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Berlaku Hingga</th>
                            <td>
                                @if($pembelian->expires_at)
                                    {{ $pembelian->expires_at->format('d M Y') }}
                                    @if($pembelian->expires_at->isPast())
                                        <span class="badge bg-danger ms-1">Kadaluarsa</span>
                                    @endif
                                @else
                                    <span class="text-success">Seumur Hidup</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Diaktifkan</th>
                            <td>{{ $pembelian->activated_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Dibuat</th>
                            <td>{{ $pembelian->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @if($pembelian->catatan)
                        <tr>
                            <th class="text-muted">Catatan</th>
                            <td>{{ $pembelian->catatan }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- License Key Box --}}
            <div class="card mb-3 {{ $pembelian->license_key ? 'border-success' : 'border-warning' }}">
                <div class="card-body text-center">
                    @if($pembelian->license_key)
                        <div class="mb-2 text-success"><i class="ti ti-key" style="font-size:2rem"></i></div>
                        <div class="small text-muted mb-1 text-uppercase fw-semibold letter-spacing-1">License Key</div>
                        <code class="d-block fs-6 fw-bold user-select-all py-2 px-3 bg-light rounded">
                            {{ $pembelian->license_key }}
                        </code>
                    @else
                        <div class="mb-2 text-warning"><i class="ti ti-clock" style="font-size:2rem"></i></div>
                        <p class="text-muted small mb-0">License key belum digenerate.<br>Konfirmasi pembayaran lunas untuk generate.</p>
                    @endif
                </div>
            </div>

            {{-- Aksi --}}
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Aksi Cepat</h6></div>
                <div class="card-body d-grid gap-2">

                    @if($pembelian->payment_status !== 'lunas' || empty($pembelian->license_key))
                    <form method="POST" action="{{ route('admin.pembelian-lisensi.konfirmasi-pembayaran', $pembelian) }}"
                          onsubmit="return confirm('Konfirmasi pembayaran dan kirim email lisensi ke {{ $pembelian->email_klien }}?')">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="ti ti-check me-1"></i> Konfirmasi Lunas & Kirim Lisensi
                        </button>
                    </form>
                    @endif

                    @if($pembelian->license_key)
                    <form method="POST" action="{{ route('admin.pembelian-lisensi.kirim-ulang-email', $pembelian) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="ti ti-mail me-1"></i> Kirim Ulang Email Lisensi
                        </button>
                    </form>
                    @endif

                    @if($pembelian->status === 'active')
                    <form method="POST" action="{{ route('admin.pembelian-lisensi.revoke', $pembelian) }}"
                          onsubmit="return confirm('Yakin cabut lisensi ini? Klien tidak dapat menggunakan aplikasi.')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="ti ti-ban me-1"></i> Cabut Lisensi
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('admin.pembelian-lisensi.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
