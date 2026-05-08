@extends('layouts/layoutMaster')

@section('title', 'Pembelian & Distribusi Lisensi')

@section('page-style')
<style>
    .license-row-hover {
        transition: background 0.15s ease;
    }

    .license-row-hover:hover {
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
        text-decoration: none;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    /* SEARCH INPUT */
    #searchInput::placeholder { color: rgba(255,255,255,0.4); }
    #searchInput:focus {
        outline: none;
        box-shadow: none;
        background: rgba(255,255,255,0.08) !important;
        border-color: rgba(115,103,240,0.5) !important;
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
          <div class="das-hero__logo-placeholder" style="width: 64px; height: 64px; background: rgba(255,255,255,0.05); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.1);">
            <i class="ti tabler-license text-info" style="font-size: 2rem;"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <span class="text-white text-decoration-none">Sistem</span> / Lisensi
          </div>
          <h4 class="das-hero__title text-gradient-gold">Pembelian & Distribusi</h4>
          <p class="das-hero__subtitle">Kelola lisensi klien, konfirmasi pembayaran, dan pantau masa aktif.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.pembelian-lisensi.create') }}" class="btn das-btn --primary">
          <i class="ti tabler-plus me-1"></i> Tambah Pembelian
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

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5"></i>
      <span>{{ session('error') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- TABLE CARD --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Pembelian Lisensi
      </h6>
      <form action="{{ route('admin.pembelian-lisensi.index') }}" method="GET" class="d-flex align-items-center gap-2">
        <div class="position-relative" style="max-width:250px;">
          <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size:0.85rem; pointer-events:none;"></i>
          <input type="text" name="search" id="searchInput" class="form-control border-0 text-white" 
                 placeholder="Cari klien..." style="background: rgba(255,255,255,0.05); height:38px; padding-left:2.2rem; font-size:0.85rem;"
                 value="{{ request('search') }}">
        </div>
        
        <select name="status" class="form-select border-0 text-white w-auto" style="background: rgba(255,255,255,0.05); height:38px; font-size:0.85rem; cursor:pointer;">
            <option value="">Semua Status</option>
            <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>Pending</option>
            <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Aktif</option>
            <option value="expired"  {{ request('status') == 'expired'  ? 'selected' : '' }}>Expired</option>
            <option value="revoked"  {{ request('status') == 'revoked'  ? 'selected' : '' }}>Dicabut</option>
        </select>

        <button type="submit" class="btn das-btn --secondary" style="height: 38px;">
            <i class="ti tabler-filter"></i>
        </button>

        @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.pembelian-lisensi.index') }}" class="btn das-btn --danger" style="height: 38px;">
                <i class="ti tabler-x"></i>
            </a>
        @endif
      </form>
    </div>
    
    <div class="das-panel__body p-0">
      <div class="table-responsive">
        <table class="das-table">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th>Klien</th>
              <th>Domain</th>
              <th>License Key</th>
              <th class="text-center">Pembayaran</th>
              <th class="text-center">Status</th>
              <th>Masa Aktif</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pembelian as $item)
            <tr class="license-row-hover">
              <td class="text-center text-muted small">{{ $loop->iteration + ($pembelian->currentPage() - 1) * $pembelian->perPage() }}</td>
              <td>
                <div class="fw-bold text-white">{{ $item->nama_klien }}</div>
                <div class="small text-muted">{{ $item->email_klien }}</div>
              </td>
              <td>
                @if($item->domain)
                  <code class="text-info small">{{ $item->domain }}</code>
                @else
                  <span class="text-muted fst-italic small">N/A</span>
                @endif
              </td>
              <td>
                @if($item->license_key)
                  <div class="d-flex align-items-center gap-2">
                    <code class="text-warning small user-select-all">{{ substr($item->license_key, 0, 8) }}...</code>
                    <button class="btn p-0 text-muted" onclick="navigator.clipboard.writeText('{{ $item->license_key }}')" title="Copy Key">
                        <i class="ti tabler-copy fs-6"></i>
                    </button>
                  </div>
                @else
                  <span class="text-muted fst-italic small">Belum ada</span>
                @endif
              </td>
              <td class="text-center">
                @if($item->payment_status === 'lunas')
                  <span class="das-chip --success">Lunas</span>
                @else
                  <span class="das-chip --warning">Menunggu</span>
                @endif
              </td>
              <td class="text-center">
                @php
                  $statusClass = match($item->status) {
                    'active' => '--success',
                    'pending' => '--warning',
                    'expired' => '--secondary',
                    'revoked' => '--danger',
                    default => '--info'
                  };
                @endphp
                <span class="das-chip {{ $statusClass }}">{{ ucfirst($item->status) }}</span>
              </td>
              <td class="small">
                @if($item->expires_at)
                  <div class="text-white">{{ $item->expires_at->format('d M Y') }}</div>
                  <div class="text-muted" style="font-size: 0.7rem;">{{ $item->expires_at->diffForHumans() }}</div>
                @else
                  <span class="text-info fw-bold">Lifetime</span>
                @endif
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-2">
                  <a href="{{ route('admin.pembelian-lisensi.show', $item) }}" class="action-btn text-info" title="Detail">
                    <i class="ti tabler-eye"></i>
                  </a>
                  <a href="{{ route('admin.pembelian-lisensi.edit', $item) }}" class="action-btn text-warning" title="Edit">
                    <i class="ti tabler-edit"></i>
                  </a>
                  
                  @if($item->payment_status !== 'lunas' || empty($item->license_key))
                    <form method="POST" action="{{ route('admin.pembelian-lisensi.konfirmasi-pembayaran', $item) }}"
                          onsubmit="return confirm('Konfirmasi pembayaran lunas dan kirim email lisensi?')">
                      @csrf
                      <button type="submit" class="action-btn text-success" title="Konfirmasi Lunas">
                        <i class="ti tabler-check"></i>
                      </button>
                    </form>
                  @else
                    <form method="POST" action="{{ route('admin.pembelian-lisensi.kirim-ulang-email', $item) }}">
                      @csrf
                      <button type="submit" class="action-btn text-primary" title="Kirim Ulang Email">
                        <i class="ti tabler-mail"></i>
                      </button>
                    </form>
                  @endif

                  @if($item->status === 'active')
                    <form method="POST" action="{{ route('admin.pembelian-lisensi.revoke', $item) }}"
                          onsubmit="return confirm('Cabut lisensi ini?')">
                      @csrf
                      <button type="submit" class="action-btn text-danger" title="Cabut Lisensi">
                        <i class="ti tabler-ban"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-5">
                <i class="ti tabler-database-off d-block fs-1 mb-2 opacity-25"></i>
                Belum ada data pembelian lisensi.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if($pembelian->hasPages())
    <div class="das-panel__footer p-3 border-top" style="border-color:rgba(255,255,255,0.08) !important;">
        {{ $pembelian->links() }}
    </div>
    @endif
  </div>

@endsection
