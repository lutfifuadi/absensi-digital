@extends('layouts/layoutMaster')

@section('title', 'Ubah Jenis Pelanggaran')

@section('page-style')
  <style>
    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
    }

    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--bs-info) !important;
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.35) !important;
    }

    .form-select option {
      background: #1a1a2e;
      color: #ccc;
    }
  </style>
@endsection

@section('content')
  {{-- HERO HEADER --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-alert-circle text-warning"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.pelanggaran-jenis.index') }}" class="text-white text-decoration-none">Jenis Pelanggaran</a> / Ubah
          </div>
          <h4 class="das-hero__title text-gradient-gold">Ubah Jenis Pelanggaran</h4>
          <p class="das-hero__subtitle">Perbarui informasi dan bobot poin untuk jenis pelanggaran ini.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.pelanggaran-jenis.index') }}" class="btn btn-secondary">
          <i class="ti tabler-arrow-left me-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5"></i>
      <div>
        <ul class="mb-0 ps-3">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4" style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 text-white"><i class="ti tabler-pencil me-1 text-warning"></i> Formulir Ubah Jenis Pelanggaran</h6>
    </div>
    <div class="das-panel__body p-4 text-white">
      <form action="{{ route('admin.pelanggaran-jenis.update', $jenisPelanggaran->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="kategori_id" class="form-label">Kategori Pelanggaran <span class="text-danger">*</span></label>
            <select name="kategori_id" id="kategori_id" class="form-select" required>
              <option value="">-- Pilih Kategori --</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('kategori_id', $jenisPelanggaran->kategori_id) == $category->id ? 'selected' : '' }}>{{ $category->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label for="bobot_poin" class="form-label">Bobot Poin (1 - 100) <span class="text-danger">*</span></label>
            <input type="number" name="bobot_poin" id="bobot_poin" class="form-control" min="1" max="100" value="{{ old('bobot_poin', $jenisPelanggaran->bobot_poin) }}" required placeholder="Contoh: 10">
          </div>
        </div>

        <div class="mb-3">
          <label for="nama" class="form-label">Nama Pelanggaran <span class="text-danger">*</span></label>
          <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama', $jenisPelanggaran->nama) }}" required placeholder="Contoh: Terlambat masuk kelas">
        </div>

        <div class="mb-3">
          <label for="deskripsi" class="form-label">Deskripsi / Penjelasan Pelanggaran</label>
          <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4" placeholder="Tulis deskripsi opsional...">{{ old('deskripsi', $jenisPelanggaran->deskripsi) }}</textarea>
        </div>

        <div class="mb-3 form-check form-switch">
          <input class="form-check-input" type="checkbox" id="is_aktif" name="is_aktif" value="1" {{ old('is_aktif', $jenisPelanggaran->is_aktif) ? 'checked' : '' }}>
          <label class="form-check-input-label text-white" for="is_aktif">Status Aktif</label>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Simpan Perubahan</button>
          <a href="{{ route('admin.pelanggaran-jenis.index') }}" class="btn btn-outline-secondary text-white">Batal</a>
        </div>
      </form>
    </div>
  </div>
@endsection
