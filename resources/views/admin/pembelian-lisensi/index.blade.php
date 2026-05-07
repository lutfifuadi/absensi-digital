@extends('layouts/layoutMaster')

@section('title', 'Pembelian & Distribusi Lisensi')

@section('page-style')
<style>
    .badge-status-pending   { background: #ff9f43; color: #fff; }
    .badge-status-active    { background: #28c76f; color: #fff; }
    .badge-status-expired   { background: #82868b; color: #fff; }
    .badge-status-revoked   { background: #ea5455; color: #fff; }
    .badge-payment-menunggu { background: #ff9f43; color: #fff; }
    .badge-payment-lunas    { background: #28c76f; color: #fff; }
    .action-btn { min-width: 36px; }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Pembelian & Distribusi Lisensi
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Pembelian Lisensi</h5>
            <a href="{{ route('admin.pembelian-lisensi.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i> Tambah Pembelian
            </a>
        </div>

        {{-- Filter --}}
        <div class="card-body border-bottom py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Cari nama, email, domain, license key..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>Pending</option>
                        <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Aktif</option>
                        <option value="expired"  {{ request('status') == 'expired'  ? 'selected' : '' }}>Expired</option>
                        <option value="revoked"  {{ request('status') == 'revoked'  ? 'selected' : '' }}>Dicabut</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_status" class="form-select form-select-sm">
                        <option value="">Semua Pembayaran</option>
                        <option value="menunggu" {{ request('payment_status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="lunas"    {{ request('payment_status') == 'lunas'    ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary btn-sm w-100">
                        <i class="ti ti-search me-1"></i> Filter
                    </button>
                </div>
                @if(request()->hasAny(['search','status','payment_status']))
                <div class="col-md-1">
                    <a href="{{ route('admin.pembelian-lisensi.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="ti ti-x"></i>
                    </a>
                </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Klien</th>
                        <th>Domain</th>
                        <th>License Key</th>
                        <th>Pembayaran</th>
                        <th>Status Lisensi</th>
                        <th>Berlaku</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembelian as $item)
                    <tr>
                        <td>{{ $loop->iteration + ($pembelian->currentPage() - 1) * $pembelian->perPage() }}</td>
                        <td>
                            <div class="fw-semibold">{{ $item->nama_klien }}</div>
                            <small class="text-muted">{{ $item->email_klien }}</small>
                        </td>
                        <td>
                            @if($item->domain)
                                <code class="small">{{ $item->domain }}</code>
                            @else
                                <span class="text-muted fst-italic small">belum diset</span>
                            @endif
                        </td>
                        <td>
                            @if($item->license_key)
                                <code class="small user-select-all">{{ $item->license_key }}</code>
                            @else
                                <span class="text-muted fst-italic small">belum digenerate</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge rounded-pill badge-payment-{{ $item->payment_status }}">
                                {{ ucfirst($item->payment_status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge rounded-pill badge-status-{{ $item->status }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="small">
                            @if($item->expires_at)
                                {{ $item->expires_at->format('d M Y') }}
                            @else
                                <span class="text-success">Seumur Hidup</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('admin.pembelian-lisensi.show', $item) }}"
                                   class="btn btn-sm btn-outline-info action-btn" title="Detail">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <a href="{{ route('admin.pembelian-lisensi.edit', $item) }}"
                                   class="btn btn-sm btn-outline-warning action-btn" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                @if($item->payment_status !== 'lunas' || empty($item->license_key))
                                <form method="POST" action="{{ route('admin.pembelian-lisensi.konfirmasi-pembayaran', $item) }}"
                                      onsubmit="return confirm('Konfirmasi pembayaran lunas dan kirim email lisensi ke {{ $item->email_klien }}?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success action-btn" title="Konfirmasi Lunas & Kirim Lisensi">
                                        <i class="ti ti-check"></i>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.pembelian-lisensi.kirim-ulang-email', $item) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary action-btn" title="Kirim Ulang Email">
                                        <i class="ti ti-mail"></i>
                                    </button>
                                </form>
                                @endif
                                @if($item->status === 'active')
                                <form method="POST" action="{{ route('admin.pembelian-lisensi.revoke', $item) }}"
                                      onsubmit="return confirm('Cabut lisensi ini? Klien tidak akan bisa menggunakannya lagi.')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger action-btn" title="Cabut Lisensi">
                                        <i class="ti ti-ban"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Belum ada data pembelian lisensi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pembelian->hasPages())
        <div class="card-footer">
            {{ $pembelian->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
