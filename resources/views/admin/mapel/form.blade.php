@extends('layouts/layoutMaster')

@section('title', $mapel->exists ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran')

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

    .das-btn.--secondary {
      background: rgba(168, 170, 174, 0.15);
      border-color: rgba(168, 170, 174, 0.35);
      color: #a8aae0;
    }
    .das-btn.--secondary:hover {
      background: rgba(168, 170, 174, 0.3);
      color: #ffffff;
      box-shadow: 0 0 12px rgba(168, 170, 174, 0.2);
    }

    .das-btn.--primary {
      background: rgba(115, 103, 240, 0.15) !important;
      border-color: rgba(115, 103, 240, 0.35) !important;
      color: #a5a2f7 !important;
    }
    .das-btn.--primary:hover {
      background: rgba(115, 103, 240, 0.35) !important;
      color: #ffffff !important;
      box-shadow: 0 0 15px rgba(115, 103, 240, 0.4) !important;
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
            <i class="ti {{ $mapel->exists ? 'tabler-pencil' : 'tabler-plus' }} text-info fs-3"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / 
            <a href="{{ route('admin.mapel.index') }}" class="text-white text-decoration-none">Mapel</a> / 
            {{ $mapel->exists ? 'Ubah' : 'Tambah' }}
          </div>
          <h4 class="das-hero__title text-gradient-gold">{{ $mapel->exists ? 'Ubah Data Mapel' : 'Tambah Mapel Baru' }}</h4>
          <p class="das-hero__subtitle">Isi formulir dengan data yang valid untuk {{ $mapel->exists ? 'memperbarui' : 'mendaftarkan' }} mata pelajaran.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="das-panel mb-5">
    <div class="das-panel__body">
      <form action="{{ $mapel->exists ? route('admin.mapel.update', $mapel->id) : route('admin.mapel.store') }}" method="POST">
        @csrf
        @if ($mapel->exists)
          @method('PUT')
        @endif

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label text-white-50" for="kode_mapel">Kode Mapel <span class="text-danger">*</span></label>
            <input type="text" id="kode_mapel" name="kode_mapel" class="form-control @error('kode_mapel') is-invalid @enderror" 
              placeholder="Cth: MP001" value="{{ old('kode_mapel', $mapel->kode_mapel) }}" required>
            @error('kode_mapel')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label text-white-50" for="nama_mapel">Nama Mata Pelajaran <span class="text-danger">*</span></label>
            <input type="text" id="nama_mapel" name="nama_mapel" class="form-control @error('nama_mapel') is-invalid @enderror" 
              placeholder="Cth: Matematika" value="{{ old('nama_mapel', $mapel->nama_mapel) }}" required>
            @error('nama_mapel')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label text-white-50" for="kelompok">Kelompok Mata Pelajaran <span class="text-danger">*</span></label>
            <select id="kelompok" name="kelompok" class="form-select @error('kelompok') is-invalid @enderror" required>
              <option value="umum" {{ old('kelompok', $mapel->kelompok) === 'umum' ? 'selected' : '' }}>Umum</option>
              <option value="kejuruan" {{ old('kelompok', $mapel->kelompok) === 'kejuruan' ? 'selected' : '' }}>Kejuruan</option>
              <option value="muatan_lokal" {{ old('kelompok', $mapel->kelompok) === 'muatan_lokal' ? 'selected' : '' }}>Muatan Lokal</option>
            </select>
            @error('kelompok')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label text-white-50" for="status">Status Keaktifan <span class="text-danger">*</span></label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
              <option value="1" {{ old('status', $mapel->status ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
              <option value="0" {{ old('status', $mapel->status ?? 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 pt-2">
          <a href="{{ route('admin.mapel.index') }}" class="btn das-btn --secondary">
            Batal
          </a>
          <button type="submit" class="btn das-btn --primary">
            {{ $mapel->exists ? 'Perbarui Data' : 'Simpan Data' }}
          </button>
        </div>
      </form>
    </div>
  </div>

@endsection
