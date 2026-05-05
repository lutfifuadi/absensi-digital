@extends('layouts/layoutMaster')

@section('title', $staff->exists ? 'Ubah Staff TU' : 'Tambah Staff TU')

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
              <i class="ti {{ $staff->exists ? 'tabler-pencil' : 'tabler-plus' }} text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}"
                      class="text-white text-decoration-none">Master Data</a></li>
                  <li class="breadcrumb-item"><a href="{{ route('admin.staff-tata-usaha.index') }}"
                      class="text-white text-decoration-none">Staff TU</a></li>
                  <li class="breadcrumb-item active text-white">{{ $staff->exists ? 'Ubah' : 'Tambah' }}</li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                {{ $staff->exists ? 'Ubah Data Staff' : 'Tambah Staff Baru' }}
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
          <h6 class="card-title mb-0">Biodata Lengkap Staff Administrasi</h6>
        </div>
        <div class="card-body p-4">
          <form
            action="{{ $staff->exists ? route('admin.staff-tata-usaha.update', $staff) : route('admin.staff-tata-usaha.store') }}"
            method="POST">
            @csrf
            @if ($staff->exists)
              @method('PUT')
            @endif

            <div class="row g-4">
              {{-- Identitas Utama --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="nama_lengkap">
                  <i class="ti tabler-user me-1 text-info"></i> Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input id="nama_lengkap" name="nama_lengkap" type="text"
                  class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Nama lengkap staff"
                  value="{{ old('nama_lengkap', $staff->nama_lengkap) }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="nip">
                  <i class="ti tabler-id me-1 text-info"></i> NIP <span class="text-danger">*</span>
                </label>
                <input id="nip" name="nip" type="text"
                  class="form-control @error('nip') is-invalid @enderror" placeholder="Nomor Induk Pegawai"
                  value="{{ old('nip', $staff->nip) }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="jenis_kelamin">
                  <i class="ti tabler-gender-bigender me-1 text-info"></i> Jenis Kelamin <span
                    class="text-danger">*</span>
                </label>
                <select id="jenis_kelamin" name="jenis_kelamin"
                  class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                  <option value="">Pilih jenis kelamin</option>
                  <option value="L" {{ old('jenis_kelamin', $staff->jenis_kelamin) === 'L' ? 'selected' : '' }}>
                    Laki-laki</option>
                  <option value="P" {{ old('jenis_kelamin', $staff->jenis_kelamin) === 'P' ? 'selected' : '' }}>
                    Perempuan</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="jabatan">
                  <i class="ti tabler-briefcase me-1 text-info"></i> Jabatan
                </label>
                <input id="jabatan" name="jabatan" type="text"
                  class="form-control @error('jabatan') is-invalid @enderror" placeholder="Contoh: Administrasi Umum"
                  value="{{ old('jabatan', $staff->jabatan) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="no_hp">
                  <i class="ti tabler-phone me-1 text-info"></i> No. HP / WhatsApp
                </label>
                <input id="no_hp" name="no_hp" type="text"
                  class="form-control @error('no_hp') is-invalid @enderror" placeholder="08xxxx"
                  value="{{ old('no_hp', $staff->no_hp) }}">
              </div>

              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="status">
                  <i class="ti tabler-circle-check me-1 text-info"></i> Status Aktif <span class="text-danger">*</span>
                </label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                  <option value="aktif" {{ old('status', $staff->status) === 'aktif' ? 'selected' : '' }}>Aktif
                  </option>
                  <option value="nonaktif" {{ old('status', $staff->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif
                  </option>
                </select>
              </div>

              {{-- Kredensial Login --}}
              <div class="col-12 mt-4">
                <hr style="border-color:rgba(255,255,255,0.08) !important;">
                <h6 class="text-info fw-bold mb-3 d-flex align-items-center gap-2">
                  <i class="ti tabler-lock fs-5"></i> Akses Akun Staff
                </h6>
              </div>

              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="email">
                  <i class="ti tabler-mail me-1 text-info"></i> Email Login <span class="text-muted">(opsional)</span>
                </label>
                <input id="email" name="email" type="email"
                  class="form-control @error('email') is-invalid @enderror" placeholder="nama@sekolah.sch.id"
                  value="{{ old('email', optional($staff->user)->email) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="password">
                  <i class="ti tabler-key me-1 text-info"></i> Password Akun
                  @if ($staff->exists)
                    <span class="text-white-50 fw-normal ms-1">(kosongkan jika tidak diubah)</span>
                  @else
                    <span class="text-danger">*</span>
                  @endif
                </label>
                <input id="password" name="password" type="password"
                  class="form-control @error('password') is-invalid @enderror" placeholder="••••••••"
                  {{ $staff->exists ? '' : 'required' }}>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="password_confirmation">
                  <i class="ti tabler-circle-check me-1 text-info"></i> Konfirmasi Password
                </label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                  class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="••••••••"
                  {{ $staff->exists ? '' : 'required' }}>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('admin.staff-tata-usaha.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ $staff->exists ? 'Perbarui Data' : 'Simpan Staff' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- QR CODE PREVIEW (Only for Edit) --}}
      @if ($staff->exists && $staff->qr_code)
        <div class="card border-0 shadow-sm mt-4"
          style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
          <div class="card-body p-4">
            <div class="d-flex align-items-center gap-4">
              <div class="bg-white p-2 rounded shadow-sm">
                <img src="{{ App\Support\QrCodeGenerator::renderDataUri($staff->qr_code, 120) }}" alt="QR Code Staff"
                  class="img-fluid" />
              </div>
              <div>
                <h6 class="mb-1 text-white fw-bold">ID Card QR Code</h6>
                <p class="text-white-50 small mb-2">Gunakan kode ini untuk akses atau absensi staff.</p>
                <code class="px-2 py-1 rounded bg-label-info text-info fw-bold">{{ $staff->qr_code }}</code>
              </div>
            </div>
          </div>
        </div>
      @endif

    </div>
  </div>

@endsection
