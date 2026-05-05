@extends('layouts/layoutMaster')

@section('title', $tahunAkademik->exists ? 'Ubah Tahun Akademik' : 'Tambah Tahun Akademik')

@section('content')

  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 4px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
              style="width:52px;height:52px;border-radius:12px !important;background:rgba(255,193,7,0.2);border:1px solid rgba(255,193,7,0.4);">
              <i class="ti {{ $tahunAkademik->exists ? 'tabler-pencil' : 'tabler-plus' }} text-warning fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}"
                      class="text-white text-decoration-none">Master Data</a></li>
                  <li class="breadcrumb-item"><a href="{{ route('admin.tahun-akademik.index') }}"
                      class="text-white text-decoration-none">Tahun Akademik</a></li>
                  <li class="breadcrumb-item active text-white">{{ $tahunAkademik->exists ? 'Ubah' : 'Tambah' }}</li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                {{ $tahunAkademik->exists ? 'Ubah Tahun Akademik' : 'Tambah Tahun Akademik' }}
              </h4>
              <p class="mb-0 text-white opacity-60 small">
                {{ $tahunAkademik->exists ? 'Perbarui data tahun akademik yang dipilih.' : 'Buat entri tahun akademik baru.' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-7">

      {{-- ERROR MESSAGES --}}
      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
          style="border-radius:8px;">
          <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
          <div>
            <div class="fw-semibold mb-1">Terdapat kesalahan input:</div>
            <ul class="mb-0 ps-3 small">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      @endif

      {{-- FORM CARD --}}
      <div class="card border-0 shadow-sm"
        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
        <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
          <i class="ti tabler-forms text-warning"></i>
          <h6 class="card-title mb-0">Formulir Tahun Akademik</h6>
        </div>
        <div class="card-body p-4">
          <form
            action="{{ $tahunAkademik->exists ? route('admin.tahun-akademik.update', $tahunAkademik) : route('admin.tahun-akademik.store') }}"
            method="POST">
            @csrf
            @if ($tahunAkademik->exists)
              @method('PUT')
            @endif

            {{-- Nama --}}
            <div class="mb-4">
              <label class="form-label fw-semibold small" for="nama">
                <i class="ti tabler-tag me-1 text-warning"></i> Nama Tahun Akademik
              </label>
              <input id="nama" name="nama" type="text" class="form-control @error('nama') is-invalid @enderror"
                placeholder="Contoh: 2025/2026" value="{{ old('nama', $tahunAkademik->nama) }}" required>
              @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Semester --}}
            <div class="mb-4">
              <label class="form-label fw-semibold small" for="semester">
                <i class="ti tabler-calendar-half me-1 text-warning"></i> Semester
              </label>
              <select id="semester" name="semester" class="form-select @error('semester') is-invalid @enderror" required>
                <option value="ganjil" {{ old('semester', $tahunAkademik->semester) === 'ganjil' ? 'selected' : '' }}>
                  Ganjil</option>
                <option value="genap" {{ old('semester', $tahunAkademik->semester) === 'genap' ? 'selected' : '' }}>
                  Genap</option>
              </select>
              @error('semester')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Tanggal --}}
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="tanggal_mulai">
                  <i class="ti tabler-calendar-event me-1 text-warning"></i> Tanggal Mulai
                </label>
                <input id="tanggal_mulai" name="tanggal_mulai" type="date"
                  class="form-control @error('tanggal_mulai') is-invalid @enderror"
                  value="{{ old('tanggal_mulai', optional($tahunAkademik->tanggal_mulai)->format('Y-m-d')) }}" required>
                @error('tanggal_mulai')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="tanggal_selesai">
                  <i class="ti tabler-calendar-due me-1 text-warning"></i> Tanggal Selesai
                </label>
                <input id="tanggal_selesai" name="tanggal_selesai" type="date"
                  class="form-control @error('tanggal_selesai') is-invalid @enderror"
                  value="{{ old('tanggal_selesai', optional($tahunAkademik->tanggal_selesai)->format('Y-m-d')) }}"
                  required>
                @error('tanggal_selesai')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            {{-- Status Aktif --}}
            <div class="mb-4">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_aktif" id="is_aktif" value="1"
                  {{ old('is_aktif', $tahunAkademik->is_aktif) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold small" for="is_aktif">
                  Tetapkan sebagai tahun akademik aktif
                </label>
              </div>
              <div class="text-white-50 small mt-1 ms-4 ps-2">
                <i class="ti tabler-info-circle me-1"></i>
                Hanya satu tahun akademik yang dapat aktif pada satu waktu.
              </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="d-flex align-items-center gap-3 pt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <button type="submit" class="btn btn-warning fw-semibold px-4 shadow-sm">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ $tahunAkademik->exists ? 'Perbarui' : 'Simpan' }}
              </button>
              <a href="{{ route('admin.tahun-akademik.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>

@endsection
