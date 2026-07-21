@extends('layouts/layoutMaster')

@section('title', 'Absensi Manual Murid')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/select2/select2.scss'
  ])
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

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

    /* select2 dark override */
    .select2-container--default .select2-selection--single {
      background-color: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
      height: 38px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #fff !important;
      line-height: 36px !important;
      padding-left: 12px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 36px !important;
    }

    .select2-dropdown {
      background-color: #2f3349 !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      color: #fff !important;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
      background-color: rgba(115, 103, 240, 0.2) !important;
      color: #fff !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #7367f0 !important;
      color: #fff !important;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
      background-color: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
    }

    /* custom radio button layout */
    .absensi-radios .btn {
      transition: all 0.2s ease;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      color: rgba(255, 255, 255, 0.7);
    }

    .absensi-radios .btn-check:checked + .btn-outline-success {
      background-color: rgba(40, 199, 111, 0.2) !important;
      border-color: #28c76f !important;
      color: #28c76f !important;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(40, 199, 111, 0.25) !important;
    }

    .absensi-radios .btn-check:checked + .btn-outline-warning {
      background-color: rgba(255, 159, 67, 0.2) !important;
      border-color: #ff9f43 !important;
      color: #ff9f43 !important;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 159, 67, 0.25) !important;
    }

    .absensi-radios .btn-check:checked + .btn-outline-info {
      background-color: rgba(0, 207, 232, 0.2) !important;
      border-color: #00cfe8 !important;
      color: #00cfe8 !important;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 207, 232, 0.25) !important;
    }

    .absensi-radios .btn-check:checked + .btn-outline-danger {
      background-color: rgba(234, 84, 85, 0.2) !important;
      border-color: #ea5455 !important;
      color: #ea5455 !important;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(234, 84, 85, 0.25) !important;
    }

    .absensi-radios .btn-check:checked + .btn-outline-secondary {
      background-color: rgba(168, 179, 191, 0.2) !important;
      border-color: #a8b3bf !important;
      color: #a8b3bf !important;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(168, 179, 191, 0.25) !important;
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
            <i class="ti tabler-edit-circle text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Wali Kelas Portal
          </div>
          <h4 class="das-hero__title text-gradient-gold">Absensi Manual Murid</h4>
          <p class="das-hero__subtitle">Catat kehadiran murid bimbingan Anda secara individual dengan cepat.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('wali-kelas.belum-absen') }}" class="btn das-btn --secondary">
          <i class="ti tabler-arrow-left me-1"></i> Kembali ke Rekap
        </a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">

      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
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

      <div class="card glass-card">
        <div class="card-header border-bottom py-3 px-4" style="border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="card-title mb-0 d-flex align-items-center gap-2 text-white">
            <i class="ti tabler-edit-circle text-info"></i> Form Absensi Individual
          </h6>
        </div>
        <div class="card-body p-4">
          @if(!$kelasWaliId)
            <div class="alert alert-warning border-0 p-3" role="alert" style="background: rgba(255, 159, 67, 0.15); color: #ff9f43;">
              <i class="ti tabler-alert-triangle me-2 fs-5"></i>
              Anda belum terdaftar sebagai Wali Kelas pada Tahun Akademik aktif. Silakan hubungi admin sekolah.
            </div>
          @else
            <form action="{{ route('wali-kelas.absensi-manual.store') }}" method="POST">
              @csrf

              <div class="row">
                {{-- Pili Murid --}}
                <div class="col-md-6 mb-4">
                  <label class="form-label fw-bold text-white small" for="siswa_id">
                    <i class="ti tabler-user me-1 text-info"></i> NAMA MURID <span class="text-danger">*</span>
                  </label>
                  <select id="siswa_id" name="siswa_id" class="form-select select2" required data-placeholder="Ketik nama murid untuk mencari...">
                    <option value=""></option>
                    @foreach ($siswaOptions as $siswa)
                      <option value="{{ $siswa->id }}" @selected(old('siswa_id', $selectedSiswaId) == $siswa->id)>
                        {{ $siswa->nama_lengkap }} (NIS: {{ $siswa->nis ?? ($siswa->nisn ?? '-') }})
                      </option>
                    @endforeach
                  </select>
                </div>

                {{-- Tanggal --}}
                <div class="col-md-6 mb-4">
                  <label class="form-label fw-bold text-white small" for="tanggal">
                    <i class="ti tabler-calendar me-1 text-info"></i> TANGGAL ABSENSI <span class="text-danger">*</span>
                  </label>
                  <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal', $selectedTanggal) }}" required>
                </div>
              </div>

              {{-- Status --}}
              <div class="mb-4">
                <label class="form-label fw-bold text-white small mb-3">
                  <i class="ti tabler-activity me-1 text-info"></i> STATUS KEHADIRAN <span class="text-danger">*</span>
                </label>
                
                <div class="absensi-radios d-flex flex-wrap gap-2">
                  <input type="radio" class="btn-check" name="status" id="status_hadir" value="hadir" @checked(old('status', 'hadir') === 'hadir') required>
                  <label class="btn btn-outline-success fw-bold flex-fill py-2" for="status_hadir">
                    <i class="ti tabler-circle-check fs-5 me-1"></i> HADIR
                  </label>

                  <input type="radio" class="btn-check" name="status" id="status_terlambat" value="terlambat" @checked(old('status') === 'terlambat')>
                  <label class="btn btn-outline-warning fw-bold flex-fill py-2" for="status_terlambat">
                    <i class="ti tabler-clock fs-5 me-1"></i> TERLAMBAT
                  </label>

                  <input type="radio" class="btn-check" name="status" id="status_sakit" value="sakit" @checked(old('status') === 'sakit')>
                  <label class="btn btn-outline-info fw-bold flex-fill py-2" for="status_sakit">
                    <i class="ti tabler-stethoscope fs-5 me-1"></i> SAKIT
                  </label>

                  <input type="radio" class="btn-check" name="status" id="status_izin" value="izin" @checked(old('status') === 'izin')>
                  <label class="btn btn-outline-warning fw-bold flex-fill py-2" for="status_izin">
                    <i class="ti tabler-file-description fs-5 me-1"></i> IZIN
                  </label>

                  <input type="radio" class="btn-check" name="status" id="status_alpha" value="alpha" @checked(old('status') === 'alpha')>
                  <label class="btn btn-outline-danger fw-bold flex-fill py-2" for="status_alpha">
                    <i class="ti tabler-circle-x fs-5 me-1"></i> ALPHA
                  </label>
                </div>
              </div>

              {{-- Keterangan --}}
              <div class="mb-4">
                <label class="form-label fw-bold text-white small" for="keterangan">
                  <i class="ti tabler-notes me-1 text-info"></i> KETERANGAN / ALASAN
                </label>
                <textarea name="keterangan" id="keterangan" rows="3" class="form-control" placeholder="Tuliskan keterangan detail jika diperlukan (misal: Demam berdarah, Surat dokter terlampir, dll)...">{{ old('keterangan') }}</textarea>
              </div>

              <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3 mt-4" style="border-color:rgba(255,255,255,0.08) !important;">
                <button type="reset" class="btn btn-label-secondary">
                  <i class="ti tabler-refresh me-1"></i> Reset
                </button>
                <button type="submit" class="btn btn-info px-4">
                  <i class="ti tabler-device-floppy me-1"></i> Simpan Kehadiran
                </button>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>

@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/select2/select2.js'
  ])
@endsection

@section('page-script')
  <script>
    $(document).ready(function() {
      // Inisialisasi Select2
      $('.select2').each(function() {
        const $this = $(this);
        $this.wrap('<div class="position-relative"></div>').select2({
          placeholder: $this.data('placeholder'),
          dropdownParent: $this.parent(),
          width: '100%',
          allowClear: true
        });
      });
    });
  </script>
@endsection
