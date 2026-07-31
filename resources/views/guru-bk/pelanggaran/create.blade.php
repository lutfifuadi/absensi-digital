@extends('layouts/layoutMaster')

@section('title', 'Catat Pelanggaran Siswa — BK')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/select2/select2.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/select2/select2.js'
  ])
@endsection

@section('page-script')
  <script type="module">
    $(function() {
      const select2 = $('.select2');
      if (select2.length) {
        select2.each(function () {
          var $this = $(this);
          $this.wrap('<div class="position-relative"></div>').select2({
            placeholder: $this.data('placeholder') || 'Pilih / Cari...',
            dropdownParent: $this.parent()
          });
        });
      }
    });
  </script>
@endsection

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <style>
    .form-alert {
      transition: all .2s ease;
    }
    .form-control, .form-select, .btn {
      border-radius: 5px !important;
    }
    .position-relative .select2-container--default .select2-selection--single {
      background-color: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      border-radius: 5px !important;
      color: #fff !important;
      height: 42px !important;
      display: flex;
      align-items: center;
    }
    .position-relative .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #e0e0e0 !important;
      padding-left: 12px;
    }
    .position-relative .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 40px !important;
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
            <i class="ti tabler-alert-triangle text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('bk.pelanggaran.index') }}" class="text-white text-decoration-none">Pelanggaran BK</a> / Tambah
          </div>
          <h4 class="das-hero__title text-gradient-gold">Catat Pelanggaran Siswa</h4>
          <p class="das-hero__subtitle">Guru BK dapat memilih siswa dari kelas manapun untuk dicatat pelanggarannya.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm form-alert"
          style="border-radius:8px; background: rgba(234, 84, 85, 0.15); color: #ea5455;">
          <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
          <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="das-panel" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important; background:transparent;">
          <i class="ti tabler-forms text-info"></i>
          <h6 class="das-panel__title mb-0">Informasi Lengkap Pelanggaran</h6>
        </div>
        <div class="das-panel__body p-4">
          <form action="{{ route('bk.pelanggaran.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
              {{-- Pilih Siswa --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="siswa_id">
                  <i class="ti tabler-user me-1 text-info"></i> Siswa (Lintas Kelas) <span class="text-danger">*</span>
                </label>
                <select id="siswa_id" name="siswa_id" class="form-select select2" required>
                  <option value="">-- Pilih / Cari Siswa --</option>
                  @foreach($siswas as $siswa)
                    <option value="{{ $siswa->id }}" {{ (old('siswa_id', $selectedSiswaId) == $siswa->id) ? 'selected' : '' }}>
                      {{ $siswa->nama_lengkap }} (NIS: {{ $siswa->nis ?? '-' }}) - Kelas: {{ $siswa->kelas->nama ?? $siswa->kelas->nama_kelas ?? '-' }}
                    </option>
                  @endforeach
                </select>
              </div>

              {{-- Jenis Pelanggaran --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="jenis_id">
                  <i class="ti tabler-alert-triangle me-1 text-info"></i> Jenis Pelanggaran <span class="text-danger">*</span>
                </label>
                <select id="jenis_id" name="jenis_id" class="form-select select2" required>
                  <option value="">-- Pilih Jenis Pelanggaran --</option>
                  @foreach($jenisList as $jenis)
                    <option value="{{ $jenis->id }}" {{ old('jenis_id') == $jenis->id ? 'selected' : '' }}>
                      [{{ $jenis->kategori->nama ?? $jenis->kategori->nama_kategori ?? 'Kategori' }}] {{ $jenis->nama ?? $jenis->nama_jenis }} (+{{ $jenis->poin }} Poin)
                    </option>
                  @endforeach
                </select>
              </div>

              {{-- Tanggal Kejadian --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="tanggal_kejadian">
                  <i class="ti tabler-calendar me-1 text-info"></i> Tanggal Kejadian <span class="text-danger">*</span>
                </label>
                <input id="tanggal_kejadian" type="date" name="tanggal_kejadian" class="form-control"
                  value="{{ old('tanggal_kejadian', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
              </div>

              {{-- Upload Bukti Foto --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="bukti_foto">
                  <i class="ti tabler-camera me-1 text-info"></i> Upload Bukti Foto (Opsional)
                </label>
                <input id="bukti_foto" type="file" name="bukti_foto" class="form-control" accept="image/png, image/jpeg, image/jpg">
                <small class="text-body-secondary mt-1 d-block" style="font-size: 0.75rem;">Format: JPG, PNG. Maksimal 2MB.</small>
              </div>

              {{-- Keterangan Detail --}}
              <div class="col-12">
                <label class="form-label fw-semibold small" for="keterangan">
                  <i class="ti tabler-message-circle me-1 text-info"></i> Keterangan / Catatan Kejadian
                </label>
                <textarea id="keterangan" name="keterangan" class="form-control" rows="3"
                  placeholder="Tuliskan kronologi singkat atau catatan khusus dari Guru BK...">{{ old('keterangan') }}</textarea>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-4 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('bk.pelanggaran.index') }}" class="das-btn das-btn--secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="das-btn das-btn--primary">
                <i class="ti tabler-device-floppy me-1"></i> Simpan Pelanggaran
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

