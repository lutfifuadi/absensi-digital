@extends('layouts/layoutMaster')

@section('title', 'Detail Pembelian Lisensi')

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
          <div class="das-hero__logo-placeholder" style="width: 52px; height: 52px; background: rgba(255,255,255,0.05); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.1);">
            <i class="ti tabler-license text-info fs-3"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.pembelian-lisensi.index') }}" class="text-white text-decoration-none">Lisensi</a> / Detail
          </div>
          <h4 class="das-hero__title text-gradient-gold">{{ $pembelian->nama_klien }}</h4>
          <p class="das-hero__subtitle">Informasi lengkap mengenai status pembayaran dan lisensi klien.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pembelian-lisensi.edit', $pembelian) }}" class="btn das-btn --warning">
              <i class="ti tabler-edit me-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.pembelian-lisensi.destroy', $pembelian) }}"
                  onsubmit="return confirm('Hapus data ini secara permanen?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn das-btn --danger">
                <i class="ti tabler-trash me-1"></i> Hapus
              </button>
            </form>
            <a href="{{ route('admin.pembelian-lisensi.index') }}" class="btn das-btn --secondary">
              <i class="ti tabler-arrow-left me-1"></i> Kembali
            </a>
        </div>
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

  <div class="row">
    <div class="col-md-8">
      <div class="das-panel">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2" style="border-color:rgba(255,255,255,0.08) !important;">
          <i class="ti tabler-id text-info"></i>
          <h6 class="das-panel__title mb-0">Informasi Klien</h6>
        </div>
        <div class="das-panel__body p-0">
          <table class="das-table">
            <tr>
              <th style="width:35%" class="ps-4">Nama Klien</th>
              <td class="text-white">{{ $pembelian->nama_klien }}</td>
            </tr>
            <tr>
              <th class="ps-4">Email Klien</th>
              <td class="text-white">{{ $pembelian->email_klien }}</td>
            </tr>
            <tr>
              <th class="ps-4">Domain</th>
              <td>
                @if($pembelian->domain)
                  <code class="text-info small">{{ $pembelian->domain }}</code>
                @else
                  <span class="text-muted fst-italic small">Belum terdaftar (aktivasi otomatis)</span>
                @endif
              </td>
            </tr>
            <tr>
              <th class="ps-4">Status Pembayaran</th>
              <td>
                <span class="das-chip {{ $pembelian->payment_status === 'lunas' ? '--success' : '--warning' }}">
                  {{ ucfirst($pembelian->payment_status) }}
                </span>
              </td>
            </tr>
            <tr>
              <th class="ps-4">Status Lisensi</th>
              <td>
                @php
                  $statusClass = match($pembelian->status) {
                    'active' => '--success',
                    'pending' => '--warning',
                    'expired' => '--secondary',
                    'revoked' => '--danger',
                    default => '--info'
                  };
                @endphp
                <span class="das-chip {{ $statusClass }}">{{ ucfirst($pembelian->status) }}</span>
              </td>
            </tr>
            <tr>
              <th class="ps-4">Masa Aktif</th>
              <td class="text-white">
                @if($pembelian->expires_at)
                  {{ $pembelian->expires_at->format('d M Y') }}
                  @if($pembelian->expires_at->isPast())
                    <span class="das-chip --danger ms-1">Kadaluarsa</span>
                  @endif
                @else
                  <span class="text-info fw-bold">Lifetime (Seumur Hidup)</span>
                @endif
              </td>
            </tr>
            <tr>
              <th class="ps-4">Aktivasi Terakhir</th>
              <td class="text-muted small">{{ $pembelian->activated_at?->format('d M Y H:i') ?? '-' }}</td>
            </tr>
            <tr>
              <th class="ps-4">Waktu Pendaftaran</th>
              <td class="text-muted small">{{ $pembelian->created_at->format('d M Y H:i') }}</td>
            </tr>
            @if($pembelian->catatan)
            <tr>
              <th class="ps-4">Catatan Internal</th>
              <td class="text-muted small">{{ $pembelian->catatan }}</td>
            </tr>
            @endif
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      {{-- License Key Box --}}
      <div class="das-panel mb-4" style="background: rgba(40, 199, 111, 0.03); border-color: {{ $pembelian->license_key ? 'rgba(40, 199, 111, 0.15)' : 'rgba(255, 159, 67, 0.15)' }} !important;">
        <div class="das-panel__body text-center py-4">
          @if($pembelian->license_key)
            <div class="mb-3 text-success"><i class="ti tabler-key fs-1"></i></div>
            <div class="small text-muted mb-2 text-uppercase fw-semibold letter-spacing-1">License Key</div>
            <div class="d-flex align-items-center gap-2 p-2 bg-dark rounded border border-success border-opacity-25 mb-2">
              <code class="text-success fs-6 fw-bold user-select-all flex-grow-1">{{ $pembelian->license_key }}</code>
              <button class="btn p-0 text-muted" onclick="navigator.clipboard.writeText('{{ $pembelian->license_key }}')" title="Copy Key">
                  <i class="ti tabler-copy fs-5"></i>
              </button>
            </div>
          @else
            <div class="mb-3 text-warning"><i class="ti tabler-clock fs-1"></i></div>
            <p class="text-muted small mb-0">License key belum digenerate.<br>Konfirmasi pembayaran lunas untuk memproses.</p>
          @endif
        </div>
      </div>

      {{-- Aksi Cepat --}}
      <div class="das-panel">
        <div class="das-panel__header border-bottom py-3 px-4" style="border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="das-panel__title mb-0">Aksi Cepat</h6>
        </div>
        <div class="das-panel__body p-4 d-grid gap-3">

          @if($pembelian->payment_status !== 'lunas' || empty($pembelian->license_key))
            <form method="POST" action="{{ route('admin.pembelian-lisensi.konfirmasi-pembayaran', $pembelian) }}"
                  onsubmit="return confirm('Konfirmasi pembayaran dan kirim email lisensi?')">
              @csrf
              <button type="submit" class="btn das-btn --success w-100 justify-content-center py-2">
                <i class="ti tabler-check me-1"></i> Konfirmasi Lunas
              </button>
            </form>
          @endif

          @if($pembelian->license_key)
            <form method="POST" action="{{ route('admin.pembelian-lisensi.kirim-ulang-email', $pembelian) }}">
              @csrf
              <button type="submit" class="btn das-btn --primary w-100 justify-content-center py-2">
                <i class="ti tabler-mail me-1"></i> Kirim Ulang Email
              </button>
            </form>
          @endif

          @if($pembelian->status === 'active')
            <form method="POST" action="{{ route('admin.pembelian-lisensi.revoke', $pembelian) }}"
                  onsubmit="return confirm('Yakin cabut lisensi ini?')">
              @csrf
              <button type="submit" class="btn das-btn --danger w-100 justify-content-center py-2">
                <i class="ti tabler-ban me-1"></i> Cabut Lisensi
              </button>
            </form>
          @endif

        </div>
      </div>
    </div>
  </div>

@endsection
