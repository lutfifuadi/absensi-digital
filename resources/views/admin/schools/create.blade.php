@extends('layouts/layoutMaster')

@section('title', 'Tambah Sekolah — SaaS')

@section('content')
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder">
          <i class="ti tabler-plus"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Onboarding Tenant
        </div>
        <h4 class="das-hero__title text-gradient-gold">Registrasi Sekolah</h4>
        <p class="das-hero__subtitle">Tambahkan instansi baru ke dalam ekosistem sistem absensi.</p>
      </div>
    </div>
    <div class="das-hero__actions">
      <a href="{{ route('admin.schools.index') }}" class="das-btn das-btn--ghost text-white">
        <i class="ti tabler-arrow-left"></i> Kembali ke Daftar
      </a>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="das-panel bounce-in">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot --primary"></span>
          Formulir Data Sekolah
        </div>
      </div>
      <div class="das-panel__body p-4">
        <form action="{{ route('admin.schools.store') }}" method="POST">
          @csrf
          <div class="row gy-3">
            <div class="col-12">
              <label class="form-label text-white-50 fw-bold small text-uppercase" for="name">Nama Sekolah</label>
              <div class="input-group input-group-merge border-0 shadow-sm">
                <span class="input-group-text bg-label-dark border-0"><i class="ti tabler-school"></i></span>
                <input type="text" class="form-control bg-label-dark border-0 text-white" id="name" name="name" placeholder="Contoh: SMA Negeri 1 Jakarta" required />
              </div>
            </div>

            <div class="col-12">
              <label class="form-label text-white-50 fw-bold small text-uppercase" for="subdomain">Subdomain Akses</label>
              <div class="input-group input-group-merge border-0 shadow-sm">
                <span class="input-group-text bg-label-dark border-0"><i class="ti tabler-world"></i></span>
                <input type="text" id="subdomain" name="subdomain" class="form-control bg-label-dark border-0 text-white" placeholder="sman1jkt" required />
                <span class="input-group-text bg-label-dark border-0 text-muted">.{{ explode('.', request()->getHost(), 2)[1] ?? request()->getHost() }}</span>
              </div>
              <div class="form-text text-muted small">Digunakan untuk URL unik sekolah (misal: sman1jkt.absensi.com)</div>
            </div>

            <div class="col-12">
              <label class="form-label text-white-50 fw-bold small text-uppercase" for="email">Email Admin Sekolah (Opsional)</label>
              <div class="input-group input-group-merge border-0 shadow-sm">
                <span class="input-group-text bg-label-dark border-0"><i class="ti tabler-mail"></i></span>
                <input type="email" id="email" name="email" class="form-control bg-label-dark border-0 text-white" placeholder="admin@sekolah.sch.id" />
              </div>
            </div>
          </div>

          <div class="mt-5 d-flex gap-2">
            <button type="submit" class="das-btn das-btn--primary px-4">
              <i class="ti tabler-device-floppy me-1"></i> Simpan Sekolah
            </button>
            <a href="{{ route('admin.schools.index') }}" class="das-btn das-btn--secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
