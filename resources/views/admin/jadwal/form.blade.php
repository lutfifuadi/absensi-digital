@extends('layouts/layoutMaster')

@section('title', isset($jadwal) ? 'Ubah Jadwal' : 'Tambah Jadwal')

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
            placeholder: $this.data('placeholder'),
            dropdownParent: $this.parent()
          });
        });
      }
    });
  </script>
@endsection

@section('content')

  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 4px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
              style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
              <i class="ti {{ isset($jadwal) ? 'tabler-pencil' : 'tabler-plus' }} text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}"
                      class="text-white text-decoration-none">Master Data</a></li>
                  <li class="breadcrumb-item"><a href="{{ route('admin.jadwal.index') }}"
                      class="text-white text-decoration-none">Jadwal Pelajaran</a></li>
                  <li class="breadcrumb-item active text-white">{{ isset($jadwal) ? 'Ubah' : 'Tambah' }}</li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                {{ isset($jadwal) ? 'Ubah Jadwal' : 'Tambah Jadwal' }}
              </h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-7">

      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
          style="border-radius:8px;">
          <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
          <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="card border-0 shadow-sm"
        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
        <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
          <i class="ti tabler-forms text-info"></i>
          <h6 class="card-title mb-0">Formulir Jadwal</h6>
        </div>
        <div class="card-body p-4">
          <form action="{{ isset($jadwal) ? route('admin.jadwal.update', $jadwal) : route('admin.jadwal.store') }}"
            method="POST">
            @csrf
            @if (isset($jadwal))
              @method('PUT')
            @endif

            <div class="row g-4">
              {{-- Kelas --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="kelas_id">
                  <i class="ti tabler-door me-1 text-info"></i> Kelas <span class="text-danger">*</span>
                </label>
                <select id="kelas_id" name="kelas_id" class="select2 form-select @error('kelas_id') is-invalid @enderror" required
                  data-placeholder="-- Pilih Kelas --">
                  <option value="">-- Pilih Kelas --</option>
                  @foreach ($kelasOptions as $k)
                    <option value="{{ $k->id }}" @selected(old('kelas_id', $jadwal->kelas_id ?? '') == $k->id)>{{ $k->nama }}</option>
                  @endforeach
                </select>
                @error('kelas_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              {{-- Guru --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="guru_id">
                  <i class="ti tabler-user-check me-1 text-info"></i> Guru Pengampu
                </label>
                <select id="guru_id" name="guru_id" class="select2 form-select @error('guru_id') is-invalid @enderror"
                  data-placeholder="-- Pilih Guru (Opsional) --">
                  <option value="">-- Pilih Guru (Opsional) --</option>
                  @foreach ($guruOptions as $g)
                    <option value="{{ $g->id }}" @selected(old('guru_id', $jadwal->guru_id ?? '') == $g->id)>{{ $g->nama_lengkap }}</option>
                  @endforeach
                </select>
                @error('guru_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              {{-- Mata Pelajaran --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="mata_pelajaran">
                  <i class="ti tabler-book me-1 text-info"></i> Mata Pelajaran <span class="text-danger">*</span>
                </label>
                <select id="mata_pelajaran" name="mata_pelajaran" class="select2 form-select @error('mata_pelajaran') is-invalid @enderror" required
                  data-placeholder="-- Pilih Mata Pelajaran --">
                  <option value="">-- Pilih Mata Pelajaran --</option>
                  @foreach ($mapelOptions as $m)
                    <option value="{{ $m->nama_mapel }}" @selected(old('mata_pelajaran', $jadwal->mata_pelajaran ?? '') == $m->nama_mapel)>{{ $m->nama_mapel }}</option>
                  @endforeach
                </select>
                @error('mata_pelajaran')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              {{-- Hari --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="hari">
                  <i class="ti tabler-calendar-event me-1 text-info"></i> Hari <span class="text-danger">*</span>
                </label>
                <select id="hari" name="hari" class="select2 form-select @error('hari') is-invalid @enderror" required
                  data-placeholder="-- Pilih Hari --">
                  <option value="">-- Pilih Hari --</option>
                  @foreach ($hariOptions as $h)
                    <option value="{{ $h }}" @selected(old('hari', $jadwal->hari ?? '') === $h)>{{ $h }}</option>
                  @endforeach
                </select>
                @error('hari')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              {{-- Jam Mulai --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="jam_mulai">
                  <i class="ti tabler-clock-play me-1 text-info"></i> Jam Mulai <span class="text-danger">*</span>
                </label>
                <input id="jam_mulai" name="jam_mulai" type="time"
                  class="form-control @error('jam_mulai') is-invalid @enderror"
                  value="{{ old('jam_mulai', isset($jadwal) ? substr($jadwal->jam_mulai, 0, 5) : '') }}" required>
                @error('jam_mulai')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              {{-- Jam Selesai --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="jam_selesai">
                  <i class="ti tabler-clock-stop me-1 text-info"></i> Jam Selesai <span class="text-danger">*</span>
                </label>
                <input id="jam_selesai" name="jam_selesai" type="time"
                  class="form-control @error('jam_selesai') is-invalid @enderror"
                  value="{{ old('jam_selesai', isset($jadwal) ? substr($jadwal->jam_selesai, 0, 5) : '') }}" required>
                @error('jam_selesai')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="d-flex align-items-center gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ isset($jadwal) ? 'Perbarui' : 'Simpan' }}
              </button>
              <a href="{{ route('admin.jadwal.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

@endsection
