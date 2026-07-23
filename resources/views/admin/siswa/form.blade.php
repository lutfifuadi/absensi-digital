@extends('layouts/layoutMaster')

@section('title', isset($siswa) && $siswa->exists ? 'Ubah Siswa — ' . $siswa->nama_lengkap : 'Tambah Siswa')

@section('content')

{{-- ═══════════════════════════════════════════════════════
     SECTION 1: HERO HEADER — Identitas Form & Breadcrumb
═══════════════════════════════════════════════════════ --}}
<div class="das-hero mb-6">
  <div class="das-hero__bg" aria-hidden="true"></div>
  <div class="das-hero__scanline" aria-hidden="true"></div>
  <div class="das-hero__grid-lines" aria-hidden="true"></div>

  <div class="das-hero__inner flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder">
          <i class="ti {{ isset($siswa) && $siswa->exists ? 'tabler-user-edit' : 'tabler-user-plus' }} fs-2"></i>
        </div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="das-hero__pulse-dot" aria-hidden="true"></span>
          <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none opacity-75">Master Data</a> /
          <a href="{{ route('admin.siswa.index') }}" class="text-white text-decoration-none opacity-75">Siswa</a> /
          <span>{{ isset($siswa) && $siswa->exists ? 'Edit' : 'Tambah' }}</span>
        </div>
        <h3 class="das-hero__school text-gradient-gold mb-1" style="font-size: 1.35rem; line-height: 1.3;">
          {{ isset($siswa) && $siswa->exists ? 'Edit Data Siswa — ' . $siswa->nama_lengkap : 'Tambah Siswa Baru' }}
        </h3>
        <p class="das-hero__welcome mb-0">
          {{ isset($siswa) && $siswa->exists ? 'Perbarui informasi biodata, data akademik, dan foto siswa.' : 'Isi formulir lengkap untuk menambahkan siswa baru ke sistem.' }}
        </p>
      </div>
    </div>

    <div class="das-hero__actions d-flex gap-2 ms-md-auto">
      <a href="{{ isset($siswa) && $siswa->exists ? route('admin.siswa.profil', $siswa->id) : route('admin.siswa.index') }}" class="btn btn-label-secondary btn-sm d-flex align-items-center gap-1 shadow-sm">
        <i class="ti tabler-arrow-left"></i> {{ isset($siswa) && $siswa->exists ? 'Lihat Profil' : 'Kembali' }}
      </a>
    </div>
  </div>
</div>{{-- /das-hero --}}


{{-- ═══════════════════════════════════════════════════════
     SECTION 2: ALERT ERRORS & FORM BODY
═══════════════════════════════════════════════════════ --}}
@if ($errors->any())
  <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-6 border-0 shadow-sm"
    style="border-radius:8px; background: rgba(234, 84, 85, 0.15); color: #ea5455;">
    <i class="ti tabler-alert-circle fs-4 mt-1 flex-shrink-0"></i>
    <div class="w-100">
      <h6 class="alert-heading fw-bold mb-1">Terdapat kesalahan pengisian formulir:</h6>
      <ul class="mb-0 ps-3 small">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
@endif

<form action="{{ isset($siswa) && $siswa->exists ? route('admin.siswa.update', $siswa) : route('admin.siswa.store') }}"
      method="POST"
      enctype="multipart/form-data">
  @csrf
  @if (isset($siswa) && $siswa->exists)
    @method('PUT')
  @endif

  <div class="row g-6">
    {{-- Kolom Utama Form (Kiri) --}}
    <div class="col-xl-8 col-lg-7">
      {{-- Card 1: Identitas & Biodata --}}
      <div class="card card-grad-primary mb-6">
        <div class="card-header pb-2 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti tabler-id fs-4"></i>
              </span>
            </div>
            <div>
              <h5 class="card-title mb-0">Identitas & Biodata Siswa</h5>
              <small class="text-body-secondary">Data pokok dan informasi pribadi</small>
            </div>
          </div>
        </div>
        <div class="card-body pt-3">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold small" for="nis">
                <i class="ti tabler-id me-1 text-info"></i> NIS
              </label>
              <input id="nis" name="nis" type="text"
                class="form-control @error('nis') is-invalid @enderror" placeholder="Nomor Induk Siswa (opsional)"
                value="{{ old('nis', $siswa->nis ?? '') }}">
              @error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small" for="nisn">
                <i class="ti tabler-id-badge me-1 text-info"></i> NISN <span class="text-danger">*</span>
              </label>
              <input id="nisn" name="nisn" type="text"
                class="form-control @error('nisn') is-invalid @enderror" placeholder="NIS Nasional"
                value="{{ old('nisn', $siswa->nisn ?? '') }}" required>
              @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold small" for="nama_lengkap">
                <i class="ti tabler-user me-1 text-info"></i> Nama Lengkap <span class="text-danger">*</span>
              </label>
              <input id="nama_lengkap" name="nama_lengkap" type="text"
                class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Masukkan nama lengkap siswa"
                value="{{ old('nama_lengkap', $siswa->nama_lengkap ?? '') }}" required>
              @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small" for="jenis_kelamin">
                <i class="ti tabler-gender-bigender me-1 text-info"></i> Jenis Kelamin <span class="text-danger">*</span>
              </label>
              <select id="jenis_kelamin" name="jenis_kelamin"
                class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                <option value="">— Pilih Jenis Kelamin —</option>
                <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
              </select>
              @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small" for="tanggal_lahir">
                <i class="ti tabler-calendar me-1 text-info"></i> Tanggal Lahir <span class="text-danger">*</span>
              </label>
              <input id="tanggal_lahir" name="tanggal_lahir" type="date"
                class="form-control @error('tanggal_lahir') is-invalid @enderror"
                value="{{ old('tanggal_lahir', isset($siswa) && $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('Y-m-d') : '') }}"
                required>
              @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small" for="tempat_lahir">
                <i class="ti tabler-map-pin me-1 text-info"></i> Tempat Lahir <span class="text-danger">*</span>
              </label>
              <input id="tempat_lahir" name="tempat_lahir" type="text"
                class="form-control @error('tempat_lahir') is-invalid @enderror" placeholder="Kota tempat lahir"
                value="{{ old('tempat_lahir', $siswa->tempat_lahir ?? '') }}" required>
              @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small" for="no_hp">
                <i class="ti tabler-brand-whatsapp me-1 text-info"></i> No. WA / HP Siswa
              </label>
              <input id="no_hp" name="no_hp" type="text"
                class="form-control @error('no_hp') is-invalid @enderror" placeholder="08xxxxxxxxxx"
                value="{{ old('no_hp', $siswa->no_hp ?? '') }}">
              @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small" for="no_hp_ortu">
                <i class="ti tabler-phone me-1 text-info"></i> No. HP Orang Tua / Wali <span class="text-danger">*</span>
              </label>
              <input id="no_hp_ortu" name="no_hp_ortu" type="text"
                class="form-control @error('no_hp_ortu') is-invalid @enderror" placeholder="08xxxxxxxxxx"
                value="{{ old('no_hp_ortu', $siswa->no_hp_ortu ?? '') }}" required>
              @error('no_hp_ortu')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold small" for="alamat">
                <i class="ti tabler-map-2 me-1 text-info"></i> Alamat Lengkap
              </label>
              <textarea id="alamat" name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2"
                placeholder="Alamat domisili lengkap...">{{ old('alamat', $siswa->alamat ?? '') }}</textarea>
              @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>

      {{-- Card 2: Penempatan Akademik & Status (Pindah ke bawah Identitas & Biodata) --}}
      <div class="card card-grad-primary mb-6">
        <div class="card-header pb-2">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="ti tabler-school fs-4"></i>
              </span>
            </div>
            <div>
              <h5 class="card-title mb-0">Akademik & Status</h5>
              <small class="text-body-secondary">Penempatan kelas & status siswa</small>
            </div>
          </div>
        </div>
        <div class="card-body pt-3">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold small" for="kelas_id">
                <i class="ti tabler-door me-1 text-info"></i> Kelas <span class="text-danger">*</span>
              </label>
              <select id="kelas_id" name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required>
                <option value="">— Pilih Kelas —</option>
                @foreach ($kelasOptions as $kelas)
                  <option value="{{ $kelas->id }}"
                    {{ old('kelas_id', $siswa->kelas_id ?? '') == $kelas->id ? 'selected' : '' }}>
                    {{ $kelas->nama }} — {{ $kelas->jurusan?->nama ?? 'Umum' }} ({{ $kelas->tahunAkademik->nama ?? '-' }})
                  </option>
                @endforeach
              </select>
              @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold small" for="tahun_akademik_id">
                <i class="ti tabler-calendar-stats me-1 text-info"></i> Tahun Akademik <span class="text-danger">*</span>
              </label>
              <select id="tahun_akademik_id" name="tahun_akademik_id"
                class="form-select @error('tahun_akademik_id') is-invalid @enderror" required>
                <option value="">— Pilih Tahun Akademik —</option>
                @foreach ($tahunAkademikOptions as $tahun)
                  <option value="{{ $tahun->id }}"
                    {{ old('tahun_akademik_id', $siswa->tahun_akademik_id ?? '') == $tahun->id ? 'selected' : '' }}>
                    {{ $tahun->nama }} — {{ ucfirst($tahun->semester) }}
                  </option>
                @endforeach
              </select>
              @error('tahun_akademik_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold small" for="status">
                <i class="ti tabler-circle-check me-1 text-info"></i> Status Siswa <span class="text-danger">*</span>
              </label>
              <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="aktif" {{ old('status', $siswa->status ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ old('status', $siswa->status ?? '') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                <option value="alumni" {{ old('status', $siswa->status ?? '') === 'alumni' ? 'selected' : '' }}>Alumni</option>
              </select>
              @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Kolom Samping (Kanan): Foto, Dual QR & Action Buttons --}}
    <div class="col-xl-4 col-lg-5">
      {{-- Card 3: Foto Siswa --}}
      <div class="card card-grad-primary mb-6">
        <div class="card-header pb-2">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti tabler-photo fs-4"></i>
              </span>
            </div>
            <div>
              <h5 class="card-title mb-0">Foto Profil Siswa</h5>
              <small class="text-body-secondary">Format JPG/PNG, maks 2MB</small>
            </div>
          </div>
        </div>
        <div class="card-body pt-3">
          <div class="mb-3">
            <input id="foto" name="foto" type="file" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
            @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          @if(isset($siswa) && $siswa->foto)
            <div class="p-3 bg-label-secondary bg-opacity-10 rounded border text-center">
              <small class="d-block text-body-secondary mb-2 fw-semibold">Foto Saat Ini:</small>
              @php
                $photoUrl = '';
                if (strlen($siswa->foto) > 30) {
                    $photoUrl = 'https://drive.google.com/thumbnail?id=' . $siswa->foto . '&sz=w200&_t=' . time();
                } else {
                    $photoUrl = asset('storage/' . $siswa->foto);
                }
              @endphp
              <img src="{{ $photoUrl }}" alt="Foto Siswa" class="rounded shadow-sm img-fluid" style="max-width: 140px; max-height: 180px; object-fit: cover;">
            </div>
          @endif
        </div>
      </div>

      {{-- Dual QR & Barcode Preview (Only for Edit) --}}
      @if (isset($siswa) && $siswa->exists && $siswa->qr_code)
        <div class="card card-grad-gold mb-6">
          <div class="card-header pb-2">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="ti tabler-qrcode fs-4"></i>
                </span>
              </div>
              <div>
                <h5 class="card-title mb-0">Dual QR & Barcode</h5>
                <small class="text-body-secondary">Preview identitas presensi</small>
              </div>
            </div>
          </div>
          <div class="card-body pt-3">
            <style>
              .barcode-container svg {
                width: 100% !important;
                height: 50px !important;
              }
            </style>
            <div class="d-flex flex-column gap-3">
              <!-- QR Code -->
              <div class="p-3 bg-white rounded shadow-sm text-center border">
                <img src="{{ App\Support\QrCodeGenerator::renderDataUri($siswa->qr_code, 120) }}" alt="QR Code Siswa"
                  class="img-fluid mb-2" style="max-width: 120px; height: auto; object-fit: contain;" />
                <div>
                  <small class="text-muted d-block font-monospace">UUID QR</small>
                  <code class="px-2 py-1 rounded bg-label-info text-info fw-bold small">{{ $siswa->qr_code }}</code>
                </div>
              </div>

              <!-- Barcode -->
              <div class="p-3 bg-white rounded shadow-sm text-center border">
                <div class="w-100 text-center barcode-container mb-2">
                  {!! App\Support\BarcodeGenerator::renderSvg($siswa->nis ?: $siswa->nisn ?: 'SISWA'.$siswa->id) !!}
                </div>
                <div>
                  <small class="text-muted d-block font-monospace">NIS Barcode</small>
                  <code class="px-2 py-1 rounded bg-label-info text-info fw-bold small">{{ App\Support\BarcodeGenerator::getFormattedData($siswa->nis ?: $siswa->nisn ?: 'SISWA'.$siswa->id) }}</code>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif

      {{-- Action Buttons Card --}}
      <div class="card shadow-sm mb-6">
        <div class="card-body p-4 d-grid gap-2">
          <button type="submit" class="btn btn-primary btn-lg fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2">
            <i class="ti tabler-device-floppy fs-4"></i>
            {{ isset($siswa) && $siswa->exists ? 'Perbarui Data Siswa' : 'Simpan Siswa' }}
          </button>
          <a href="{{ isset($siswa) && $siswa->exists ? route('admin.siswa.profil', $siswa->id) : route('admin.siswa.index') }}" class="btn btn-label-secondary d-flex align-items-center justify-content-center gap-1">
            <i class="ti tabler-arrow-left"></i> Batal / Kembali
          </a>
        </div>
      </div>
    </div>
  </div>
</form>

@endsection
