@extends('layouts/layoutMaster')

@section('title', $isEdit ? 'Edit Pembelian Lisensi' : 'Tambah Pembelian Lisensi')

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
            <i class="ti {{ $isEdit ? 'tabler-edit' : 'tabler-plus' }} text-info fs-3"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.pembelian-lisensi.index') }}" class="text-white text-decoration-none">Lisensi</a> / {{ $isEdit ? 'Edit' : 'Tambah' }}
          </div>
          <h4 class="das-hero__title text-gradient-gold">{{ $isEdit ? 'Ubah Data Pembelian' : 'Tambah Pembelian Baru' }}</h4>
          <p class="das-hero__subtitle">Masukkan detail pembelian lisensi klien untuk proses distribusi otomatis.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.pembelian-lisensi.index') }}" class="btn das-btn --secondary">
          <i class="ti tabler-arrow-left me-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
      <div class="das-panel">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2" style="border-color:rgba(255,255,255,0.08) !important;">
          <i class="ti tabler-forms text-info"></i>
          <h6 class="das-panel__title mb-0">Formulir Pembelian</h6>
        </div>
        <div class="das-panel__body p-4">
          <form method="POST" action="{{ $isEdit ? route('admin.pembelian-lisensi.update', $pembelian) : route('admin.pembelian-lisensi.store') }}">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="mb-4">
              <label class="form-label text-white-50 fw-semibold small">Nama Klien / Sekolah <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text bg-dark border-secondary text-muted"><i class="ti tabler-school fs-5"></i></span>
                <input type="text" name="nama_klien" class="form-control bg-dark border-secondary text-white @error('nama_klien') is-invalid @enderror"
                       value="{{ old('nama_klien', $pembelian->nama_klien) }}"
                       placeholder="Contoh: SMA Negeri 1 Bandung" required>
              </div>
              @error('nama_klien')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
              <label class="form-label text-white-50 fw-semibold small">Email Klien <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text bg-dark border-secondary text-muted"><i class="ti tabler-mail fs-5"></i></span>
                <input type="email" name="email_klien" class="form-control bg-dark border-secondary text-white @error('email_klien') is-invalid @enderror"
                       value="{{ old('email_klien', $pembelian->email_klien) }}"
                       placeholder="email@sekolah.sch.id" required>
              </div>
              <div class="form-text text-muted small mt-1">Email ini akan menerima license key dan link download aplikasi.</div>
              @error('email_klien')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
              <label class="form-label text-white-50 fw-semibold small">Domain (opsional)</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text bg-dark border-secondary text-muted"><i class="ti tabler-world fs-5"></i></span>
                <input type="text" name="domain" class="form-control bg-dark border-secondary text-white @error('domain') is-invalid @enderror"
                       value="{{ old('domain', $pembelian->domain) }}"
                       placeholder="absensi.sekolah.sch.id">
              </div>
              <div class="form-text text-muted small mt-1">Domain target instalasi. Bisa dikosongkan (akan terisi saat aktivasi).</div>
              @error('domain')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="row g-4 mb-4">
              <div class="col-md-6">
                <label class="form-label text-white-50 fw-semibold small">Status Pembayaran <span class="text-danger">*</span></label>
                <select name="payment_status" class="form-select bg-dark border-secondary text-white @error('payment_status') is-invalid @enderror" required>
                  <option value="menunggu" {{ old('payment_status', $pembelian->payment_status) == 'menunggu' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                  <option value="lunas"    {{ old('payment_status', $pembelian->payment_status) == 'lunas'    ? 'selected' : '' }}>Lunas (Proses Lisensi)</option>
                </select>
                @error('payment_status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <label class="form-label text-white-50 fw-semibold small">Berlaku Hingga</label>
                <input type="date" name="expires_at" class="form-control bg-dark border-secondary text-white @error('expires_at') is-invalid @enderror"
                       value="{{ old('expires_at', $pembelian->expires_at?->format('Y-m-d')) }}">
                <div class="form-text text-muted small mt-1">Kosongkan untuk Lifetime.</div>
                @error('expires_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label text-white-50 fw-semibold small">Catatan Internal</label>
              <textarea name="catatan" class="form-control bg-dark border-secondary text-white @error('catatan') is-invalid @enderror"
                        rows="3" placeholder="Catatan tambahan untuk admin...">{{ old('catatan', $pembelian->catatan) }}</textarea>
              @error('catatan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-end gap-3 pt-4 border-top" style="border-color:rgba(255,255,255,0.08) !important;">
              <button type="submit" class="btn das-btn --primary px-4">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan & Proses' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="das-panel mb-4" style="background: rgba(0, 207, 232, 0.03); border-color: rgba(0, 207, 232, 0.15) !important;">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2" style="border-color:rgba(0, 207, 232, 0.15) !important;">
          <i class="ti tabler-info-circle text-info"></i>
          <h6 class="das-panel__title mb-0 text-info">Panduan Sistem</h6>
        </div>
        <div class="das-panel__body p-4">
          <ul class="list-unstyled small text-muted mb-0">
            <li class="mb-3 d-flex gap-2">
              <i class="ti tabler-circle-number-1 text-info fs-5"></i>
              <span>Isi data klien dengan benar, pastikan email dapat menerima pesan masuk.</span>
            </li>
            <li class="mb-3 d-flex gap-2">
              <i class="ti tabler-circle-number-2 text-info fs-5"></i>
              <span>Status <strong>Lunas</strong> akan memicu generate <strong>License Key</strong> secara otomatis.</span>
            </li>
            <li class="mb-3 d-flex gap-2">
              <i class="ti tabler-circle-number-3 text-info fs-5"></i>
              <span>Email berisi detail lisensi & link download akan langsung dikirim setelah status Lunas.</span>
            </li>
            <li class="d-flex gap-2">
              <i class="ti tabler-help text-info fs-5"></i>
              <span>Jika email tidak masuk, admin bisa mengirim ulang via halaman Detail Lisensi.</span>
            </li>
          </ul>
        </div>
      </div>

      @if($isEdit && $pembelian->license_key)
        <div class="das-panel" style="background: rgba(40, 199, 111, 0.03); border-color: rgba(40, 199, 111, 0.15) !important;">
          <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2" style="border-color:rgba(40, 199, 111, 0.15) !important;">
            <i class="ti tabler-key text-success"></i>
            <h6 class="das-panel__title mb-0 text-success">Lisensi Aktif</h6>
          </div>
          <div class="das-panel__body p-4">
            <label class="form-label text-white-50 fw-semibold small">License Key</label>
            <div class="d-flex align-items-center gap-2 p-2 bg-dark rounded border border-success border-opacity-25 mb-3">
              <code class="text-success small user-select-all flex-grow-1">{{ $pembelian->license_key }}</code>
              <button class="btn p-0 text-muted" onclick="navigator.clipboard.writeText('{{ $pembelian->license_key }}')" title="Copy Key">
                  <i class="ti tabler-copy fs-5"></i>
              </button>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <span class="small text-muted">Status:</span>
              <span class="das-chip {{ $pembelian->status === 'active' ? '--success' : '--warning' }}">{{ ucfirst($pembelian->status) }}</span>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>

@endsection
