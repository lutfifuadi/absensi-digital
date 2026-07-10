@extends('layouts/layoutMaster')

@section('title', isset($siswa) && $siswa->exists ? 'Ubah Siswa' : 'Tambah Siswa')

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
              <i class="ti {{ isset($siswa) && $siswa->exists ? 'tabler-pencil' : 'tabler-plus' }} text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}"
                      class="text-white text-decoration-none">Master Data</a></li>
                  <li class="breadcrumb-item"><a href="{{ route('admin.siswa.index') }}"
                      class="text-white text-decoration-none">Siswa</a></li>
                  <li class="breadcrumb-item active text-white">{{ isset($siswa) && $siswa->exists ? 'Ubah' : 'Tambah' }}
                  </li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                {{ isset($siswa) && $siswa->exists ? 'Ubah Data Siswa' : 'Tambah Siswa Baru' }}
              </h4>
            </div>
          </div>
        </div>
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

      <div class="card border-0 shadow-sm"
        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
        <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
          <i class="ti tabler-forms text-info"></i>
          <h6 class="card-title mb-0">Biodata Lengkap Siswa</h6>
        </div>
        <div class="card-body p-4">
          <form
            action="{{ isset($siswa) && $siswa->exists ? route('admin.siswa.update', $siswa) : route('admin.siswa.store') }}"
            method="POST"
            enctype="multipart/form-data">
            @csrf
            @if (isset($siswa) && $siswa->exists)
              @method('PUT')
            @endif

            <div class="row g-4">
              {{-- Identitas Utama --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="nis">
                  <i class="ti tabler-id me-1 text-info"></i> NIS
                </label>
                <input id="nis" name="nis" type="text"
                  class="form-control @error('nis') is-invalid @enderror" placeholder="Nomor Induk Siswa (opsional)"
                  value="{{ old('nis', $siswa->nis ?? '') }}">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="nisn">
                  <i class="ti tabler-id-badge me-1 text-info"></i> NISN <span class="text-danger">*</span>
                </label>
                <input id="nisn" name="nisn" type="text"
                  class="form-control @error('nisn') is-invalid @enderror" placeholder="NIS Nasional"
                  value="{{ old('nisn', $siswa->nisn ?? '') }}" required>
              </div>

              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="nama_lengkap">
                  <i class="ti tabler-user me-1 text-info"></i> Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input id="nama_lengkap" name="nama_lengkap" type="text"
                  class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Masukkan nama lengkap"
                  value="{{ old('nama_lengkap', $siswa->nama_lengkap ?? '') }}" required>
              </div>

              {{-- Detail Personal --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="jenis_kelamin">
                  <i class="ti tabler-gender-bigender me-1 text-info"></i> Jenis Kelamin <span
                    class="text-danger">*</span>
                </label>
                <select id="jenis_kelamin" name="jenis_kelamin"
                  class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                  <option value="">Pilih jenis kelamin</option>
                  <option value="L"
                    {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'L' ? 'selected' : '' }}>
                    Laki-laki</option>
                  <option value="P"
                    {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'P' ? 'selected' : '' }}>
                    Perempuan</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="tanggal_lahir">
                  <i class="ti tabler-calendar me-1 text-info"></i> Tanggal Lahir <span class="text-danger">*</span>
                </label>
                <input id="tanggal_lahir" name="tanggal_lahir" type="date"
                  class="form-control @error('tanggal_lahir') is-invalid @enderror"
                  value="{{ old('tanggal_lahir', isset($siswa) && $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('Y-m-d') : '') }}"
                  required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="tempat_lahir">
                  <i class="ti tabler-map-pin me-1 text-info"></i> Tempat Lahir <span class="text-danger">*</span>
                </label>
                <input id="tempat_lahir" name="tempat_lahir" type="text"
                  class="form-control @error('tempat_lahir') is-invalid @enderror" placeholder="Kota lahir"
                  value="{{ old('tempat_lahir', $siswa->tempat_lahir ?? '') }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="no_hp">
                  <i class="ti tabler-phone me-1 text-info"></i> No. WhatsApp / HP Siswa
                </label>
                <input id="no_hp" name="no_hp" type="text"
                  class="form-control @error('no_hp') is-invalid @enderror" placeholder="08xxxx"
                  value="{{ old('no_hp', $siswa->no_hp ?? '') }}">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="no_hp_ortu">
                  <i class="ti tabler-phone me-1 text-info"></i> No. HP Orang Tua <span class="text-danger">*</span>
                </label>
                <input id="no_hp_ortu" name="no_hp_ortu" type="text"
                  class="form-control @error('no_hp_ortu') is-invalid @enderror" placeholder="08xxxx"
                  value="{{ old('no_hp_ortu', $siswa->no_hp_ortu ?? '') }}" required>
              </div>

              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="alamat">
                  <i class="ti tabler-map-2 me-1 text-info"></i> Alamat Lengkap
                </label>
                <textarea id="alamat" name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2"
                  placeholder="Jl. Contoh No. 123...">{{ old('alamat', $siswa->alamat ?? '') }}</textarea>
              </div>

              {{-- Akademik --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="kelas_id">
                  <i class="ti tabler-door me-1 text-info"></i> Kelas <span class="text-danger">*</span>
                </label>
                <select id="kelas_id" name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror"
                  required>
                  <option value="">Pilih kelas</option>
                  @foreach ($kelasOptions as $kelas)
                    <option value="{{ $kelas->id }}"
                      {{ old('kelas_id', $siswa->kelas_id ?? '') == $kelas->id ? 'selected' : '' }}>
                      {{ $kelas->nama }} — {{ $kelas->jurusan }} ({{ $kelas->tahunAkademik->nama ?? '-' }})</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="tahun_akademik_id">
                  <i class="ti tabler-calendar-stats me-1 text-info"></i> Tahun Akademik <span
                    class="text-danger">*</span>
                </label>
                <select id="tahun_akademik_id" name="tahun_akademik_id"
                  class="form-select @error('tahun_akademik_id') is-invalid @enderror" required>
                  <option value="">Pilih tahun akademik</option>
                  @foreach ($tahunAkademikOptions as $tahun)
                    <option value="{{ $tahun->id }}"
                      {{ old('tahun_akademik_id', $siswa->tahun_akademik_id ?? '') == $tahun->id ? 'selected' : '' }}>
                      {{ $tahun->nama }} — {{ ucfirst($tahun->semester) }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="status">
                  <i class="ti tabler-circle-check me-1 text-info"></i> Status Siswa <span class="text-danger">*</span>
                </label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror"
                  required>
                  <option value="aktif" {{ old('status', $siswa->status ?? 'aktif') === 'aktif' ? 'selected' : '' }}>
                    Aktif</option>
                  <option value="nonaktif" {{ old('status', $siswa->status ?? '') === 'nonaktif' ? 'selected' : '' }}>
                    Nonaktif</option>
                  <option value="alumni" {{ old('status', $siswa->status ?? '') === 'alumni' ? 'selected' : '' }}>Alumni
                  </option>
                </select>
              </div>

              {{-- Foto Siswa --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="foto">
                  <i class="ti tabler-photo me-1 text-info"></i> Foto Siswa (JPEG/PNG, Maks. 2MB)
                </label>
                <input id="foto" name="foto" type="file" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                
                {{-- Preview Current Photo --}}
                @if(isset($siswa) && $siswa->foto)
                  <div class="mt-2">
                    <span class="d-block text-white-50 small mb-1">Foto Saat Ini:</span>
                    @php
                      // Check if it's a google drive ID or local path
                      $photoUrl = '';
                      if (strlen($siswa->foto) > 30) {
                          // Google Drive ID
                          $photoUrl = 'https://drive.google.com/thumbnail?id=' . $siswa->foto . '&sz=w200';
                      } else {
                          $photoUrl = asset('storage/' . $siswa->foto);
                      }
                    @endphp
                    <img src="{{ $photoUrl }}" alt="Foto Siswa" class="rounded shadow-sm" style="max-width: 120px; max-height: 160px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);">
                  </div>
                @endif
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('admin.siswa.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ isset($siswa) && $siswa->exists ? 'Perbarui Data' : 'Simpan Siswa' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- QR CODE PREVIEW (Only for Edit) --}}
      @if (isset($siswa) && $siswa->exists && $siswa->qr_code)
        <div class="card border-0 shadow-sm mt-4"
          style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
          <div class="card-body p-4">
            <div class="d-flex align-items-center gap-4">
              <div class="bg-white p-2 rounded shadow-sm">
                <img src="{{ App\Support\QrCodeGenerator::renderDataUri($siswa->qr_code, 120) }}" alt="QR Code Siswa"
                  class="img-fluid" />
              </div>
              <div>
                <h6 class="mb-1 text-white fw-bold">ID Card QR Code</h6>
                <p class="text-white-50 small mb-2">Gunakan kode ini untuk absensi mandiri siswa via scan QR.</p>
                <code class="px-2 py-1 rounded bg-label-info text-info fw-bold">{{ $siswa->qr_code }}</code>
              </div>
            </div>
          </div>
        </div>
      @endif

    </div>
  </div>

@endsection
