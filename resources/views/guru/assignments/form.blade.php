@extends('layouts/layoutMaster')

@section('title', isset($assignment) ? 'Edit Penugasan' : 'Buat Penugasan Baru')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/select2/select2.scss'
    ])
    <style>
        .select2-container--default .select2-selection--single {
            background-color: rgba(15, 23, 42, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            height: 38px;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff !important;
            padding-left: 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #7367f0 !important;
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
    </style>
@endsection

@section('content')
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-book-upload"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Portal Guru
          </div>
          <h4 class="das-hero__title text-gradient-gold">{{ isset($assignment) ? 'Edit Penugasan' : 'Buat Penugasan' }}</h4>
          <p class="das-hero__subtitle">Isi formulir berikut untuk mendistribusikan penugasan ke siswa.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('assignments.index') }}" class="das-btn das-btn--secondary">
          <i class="ti tabler-arrow-left me-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible border-0 mb-4" role="alert" style="border-radius:8px;">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="das-panel">
    <div class="das-panel__body">
      <form action="{{ isset($assignment) ? route('assignments.update', $assignment->id) : route('assignments.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if (isset($assignment))
          @method('PUT')
        @endif

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-white fw-bold">Pilih Kelas <span class="text-danger">*</span></label>
            <select name="kelas_id" class="form-select" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);" required>
              <option value="">-- Pilih Kelas --</option>
              @foreach ($kelasOptions as $k)
                <option value="{{ $k->id }}" @selected(old('kelas_id', $assignment->kelas_id ?? '') == $k->id)>{{ $k->nama }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label text-white fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
            <select name="mata_pelajaran" class="form-select select2" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);" required data-placeholder="-- Pilih Mata Pelajaran --">
              <option value="">-- Pilih Mata Pelajaran --</option>
              @foreach ($mapelOptions as $mapel)
                <option value="{{ $mapel->nama_mapel }}" @selected(old('mata_pelajaran', $assignment->mata_pelajaran ?? '') == $mapel->nama_mapel)>
                  {{ $mapel->nama_mapel }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label text-white fw-bold">Judul Tugas <span class="text-danger">*</span></label>
            <input type="text" name="judul" class="form-control" placeholder="cth: Latihan Halaman 24 Aljabar" value="{{ old('judul', $assignment->judul ?? '') }}" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label text-white fw-bold">Tanggal Tugas <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_tugas" class="form-control" value="{{ old('tanggal_tugas', isset($assignment) ? $assignment->tanggal_tugas->format('Y-m-d') : date('Y-m-d')) }}" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);" required>
          </div>

          <div class="col-12 mb-3">
            <label class="form-label text-white fw-bold">Deskripsi Tugas <span class="text-danger">*</span></label>
            <textarea name="deskripsi" class="form-control" rows="6" placeholder="Tuliskan instruksi tugas secara rinci..." style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);" required>{{ old('deskripsi', $assignment->deskripsi ?? '') }}</textarea>
          </div>

          <div class="col-12 mb-4">
            <label class="form-label text-white fw-bold">File Lampiran (Opsional)</label>
            <input type="file" name="file_lampiran" class="form-control" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);">
            <div class="form-text text-white-50">Format didukung: pdf, docx, xlsx, rar, zip, dll. Maks: 5MB</div>
            @if (isset($assignment) && $assignment->file_lampiran)
              <div class="mt-2 text-info">
                <i class="ti tabler-file"></i> File saat ini: <a href="{{ asset('storage/' . $assignment->file_lampiran) }}" target="_blank" class="text-decoration-underline text-info">{{ basename($assignment->file_lampiran) }}</a>
              </div>
            @endif
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <button type="submit" class="btn btn-info px-4">
            <i class="ti tabler-device-floppy me-1"></i> Simpan Penugasan
          </button>
        </div>
      </form>
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
          width: '100%'
        });
      });
    });
  </script>
@endsection
