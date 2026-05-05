@extends('layouts/layoutMaster')

@section('title', $guru->exists ? 'Ubah Wali Kelas' : 'Tambah Wali Kelas')

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
              <i class="ti {{ $guru->exists ? 'tabler-pencil' : 'tabler-plus' }} text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}"
                      class="text-white text-decoration-none">Master Data</a></li>
                  <li class="breadcrumb-item"><a href="{{ route('admin.wali-kelas.index') }}"
                      class="text-white text-decoration-none">Wali Kelas</a></li>
                  <li class="breadcrumb-item active text-white">{{ $guru->exists ? 'Ubah' : 'Tambah' }}</li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                {{ $guru->exists ? 'Ubah Data Wali Kelas' : 'Tambah Wali Kelas Baru' }}
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
          <h6 class="card-title mb-0">Informasi Lengkap Wali Kelas</h6>
        </div>
        <div class="card-body p-4">
          <form action="{{ $guru->exists ? route('admin.wali-kelas.update', $guru) : route('admin.wali-kelas.store') }}"
            method="POST">
            @csrf
            @if ($guru->exists)
              @method('PUT')
            @endif

            @if (isset($user))
              <input type="hidden" name="user_id" value="{{ $user->id }}" />
            @endif

            <div class="row g-4">
              {{-- Identitas Utama --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="nama_lengkap">
                  <i class="ti tabler-user me-1 text-info"></i> Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input id="nama_lengkap" name="nama_lengkap" type="text"
                  class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Nama lengkap & gelar"
                  value="{{ old('nama_lengkap', $guru->nama_lengkap) }}" required>
                @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="nip">
                  <i class="ti tabler-id me-1 text-info"></i> NIP <span class="text-danger">*</span>
                </label>
                <input id="nip" name="nip" type="text"
                  class="form-control @error('nip') is-invalid @enderror" placeholder="Nomor Induk Pegawai"
                  value="{{ old('nip', $guru->nip) }}" required>
                @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="jenis_kelamin">
                  <i class="ti tabler-gender-bigender me-1 text-info"></i> Jenis Kelamin <span
                    class="text-danger">*</span>
                </label>
                <select id="jenis_kelamin" name="jenis_kelamin"
                  class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                  <option value="">Pilih jenis kelamin</option>
                  <option value="L" {{ old('jenis_kelamin', $guru->jenis_kelamin) === 'L' ? 'selected' : '' }}>
                    Laki-laki</option>
                  <option value="P" {{ old('jenis_kelamin', $guru->jenis_kelamin) === 'P' ? 'selected' : '' }}>
                    Perempuan</option>
                </select>
                @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="mata_pelajaran">
                  <i class="ti tabler-book me-1 text-info"></i> Mata Pelajaran <span class="text-danger">*</span>
                </label>
                <input id="mata_pelajaran" name="mata_pelajaran" type="text"
                  class="form-control @error('mata_pelajaran') is-invalid @enderror"
                  placeholder="Contoh: Matematika, B. Inggris"
                  value="{{ old('mata_pelajaran', $guru->mata_pelajaran) }}" required>
                @error('mata_pelajaran')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="jabatan">
                  <i class="ti tabler-briefcase me-1 text-info"></i> Jabatan
                </label>
                <input id="jabatan" name="jabatan" type="text"
                  class="form-control @error('jabatan') is-invalid @enderror" placeholder="Contoh: Wali Kelas X IPA 1"
                  value="{{ old('jabatan', $guru->jabatan) }}">
                @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="no_hp">
                  <i class="ti tabler-phone me-1 text-info"></i> No. HP / WhatsApp
                </label>
                <input id="no_hp" name="no_hp" type="text"
                  class="form-control @error('no_hp') is-invalid @enderror" placeholder="08xxxx"
                  value="{{ old('no_hp', $guru->no_hp) }}">
                @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="status">
                  <i class="ti tabler-circle-check me-1 text-info"></i> Status Aktif <span class="text-danger">*</span>
                </label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror"
                  required>
                  <option value="aktif" {{ old('status', $guru->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                  <option value="nonaktif" {{ old('status', $guru->status) === 'nonaktif' ? 'selected' : '' }}>
                    Nonaktif</option>
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- Kredensial Login --}}
              <div class="col-12 mt-4">
                <hr style="border-color:rgba(255,255,255,0.08) !important;">
                <h6 class="text-info fw-bold mb-3 d-flex align-items-center gap-2">
                  <i class="ti tabler-lock fs-5"></i> Akses Akun Wali Kelas
                </h6>
              </div>

              @if (isset($user))
                <div class="col-md-12">
                  <div class="alert alert-info mb-0" role="alert">
                    Profil wali kelas akan dibuat untuk akun login:
                    <strong>{{ $user->name }}</strong> ({{ $user->email }})
                  </div>
                </div>
              @else
                <div class="col-md-12">
                  <label class="form-label fw-semibold small" for="email">
                    <i class="ti tabler-mail me-1 text-info"></i> Email Login <span
                      class="text-muted">(opsional)</span>
                  </label>
                  <input id="email" name="email" type="email"
                    class="form-control @error('email') is-invalid @enderror" placeholder="nama@sekolah.sch.id"
                    value="{{ old('email', optional($guru->user)->email) }}">
                  @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold small" for="password">
                    <i class="ti tabler-key me-1 text-info"></i> Password Akun
                    @if ($guru->exists)
                      <span class="text-white-50 fw-normal ms-1">(kosongkan jika tidak diubah)</span>
                    @else
                      <span class="text-danger">*</span>
                    @endif
                  </label>
                  <input id="password" name="password" type="password"
                    class="form-control @error('password') is-invalid @enderror" placeholder="••••••••"
                    {{ $guru->exists ? '' : 'required' }}>
                  @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold small" for="password_confirmation">
                    <i class="ti tabler-circle-check me-1 text-info"></i> Konfirmasi Password
                  </label>
                  <input id="password_confirmation" name="password_confirmation" type="password"
                    class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="••••••••"
                    {{ $guru->exists ? '' : 'required' }}>
                  @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @endif
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('admin.wali-kelas.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ $guru->exists ? 'Perbarui Data' : 'Simpan Wali Kelas' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- QR CODE PREVIEW (Only for Edit) --}}
      @if ($guru->exists && $guru->qr_code)
        <div class="card border-0 shadow-sm mt-4"
          style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
          <div class="card-body p-4">
            <div class="d-flex align-items-center gap-4">
              <div class="bg-white p-2 rounded shadow-sm">
                <img src="{{ App\Support\QrCodeGenerator::renderDataUri($guru->qr_code, 120) }}"
                  alt="QR Code Wali Kelas" class="img-fluid" />
              </div>
              <div>
                <h6 class="mb-1 text-white fw-bold">ID Card QR Code</h6>
                <p class="text-white-50 small mb-2">Kode QR ini digunakan untuk proses absensi mandiri wali kelas.</p>
                <code class="px-2 py-1 rounded bg-label-info text-info fw-bold">{{ $guru->qr_code }}</code>
              </div>
            </div>
          </div>
        </div>
      @endif

    </div>
  </div>

@endsection
