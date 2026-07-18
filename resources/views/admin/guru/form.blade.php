@extends('layouts/layoutMaster')

@section('title', $guru->exists ? 'Ubah Guru' : 'Tambah Guru')

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
            <i class="ti {{ $guru->exists ? 'tabler-pencil' : 'tabler-plus' }} text-info fs-3"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / 
            <a href="{{ route('admin.guru.index') }}" class="text-white text-decoration-none">Guru</a> / 
            <span class="text-white-50">{{ $guru->exists ? 'Ubah' : 'Tambah' }}</span>
          </div>
          <h4 class="das-hero__title text-gradient-gold">{{ $guru->exists ? 'Ubah Data Guru' : 'Tambah Guru Baru' }}</h4>
          <p class="das-hero__subtitle">Isi formulir dengan data yang valid untuk {{ $guru->exists ? 'memperbarui' : 'mendaftarkan' }} tenaga pendidik.</p>
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

      <div class="das-panel">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important;">
          <i class="ti tabler-forms text-info"></i>
          <h6 class="das-panel__title mb-0">Informasi Lengkap Guru</h6>
        </div>
        <div class="das-panel__body p-4">
          <form action="{{ $guru->exists ? route('admin.guru.update', $guru) : route('admin.guru.store') }}"
            method="POST">
            @csrf
            @if ($guru->exists)
              @method('PUT')
            @endif

            @if (isset($user))
              <input type="hidden" name="user_id" value="{{ $user->id }}" />
            @endif

            <div class="row g-4">
              {{-- Section 1: Informasi Profil --}}
              <div class="col-md-7">
                <div class="p-4 rounded-3 h-100" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
                  <h6 class="text-info fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="ti tabler-user-check fs-5"></i> Profil Guru
                  </h6>
                  
                  <div class="mb-3">
                    <label class="form-label fw-semibold small" for="nama_lengkap">
                      Nama Lengkap <span class="text-danger">*</span>
                    </label>
                    <input id="nama_lengkap" name="nama_lengkap" type="text"
                      class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Nama lengkap & gelar"
                      value="{{ old('nama_lengkap', $guru->nama_lengkap ?? ($user->name ?? '')) }}" required>
                  </div>

                  <div class="row">
                    <div class="col-sm-6 mb-3">
                      <label class="form-label fw-semibold small" for="jenis_kelamin">
                        Jenis Kelamin <span class="text-danger">*</span>
                      </label>
                      <select id="jenis_kelamin" name="jenis_kelamin"
                        class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                        <option value="">Pilih jenis kelamin</option>
                        <option value="L" {{ old('jenis_kelamin', $guru->jenis_kelamin) === 'L' ? 'selected' : '' }}>
                          Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $guru->jenis_kelamin) === 'P' ? 'selected' : '' }}>
                          Perempuan</option>
                      </select>
                    </div>

                    <div class="col-sm-6 mb-3">
                      <label class="form-label fw-semibold small" for="no_hp">
                        No. HP / WhatsApp
                      </label>
                      <input id="no_hp" name="no_hp" type="text"
                        class="form-control @error('no_hp') is-invalid @enderror" placeholder="e.g. 08123456789"
                        value="{{ old('no_hp', $guru->no_hp) }}">
                    </div>
                  </div>
                </div>
              </div>

              {{-- Section 2: Kepegawaian --}}
              <div class="col-md-5">
                <div class="p-4 rounded-3 h-100" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
                  <h6 class="text-info fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="ti tabler-briefcase fs-5"></i> Kepegawaian & Status
                  </h6>

                  <div class="mb-3">
                    <label class="form-label fw-semibold small" for="nip">
                      NIP <span class="text-danger">*</span>
                    </label>
                    <input id="nip" name="nip" type="text"
                      class="form-control @error('nip') is-invalid @enderror" placeholder="Nomor Induk Pegawai"
                      value="{{ old('nip', $guru->nip ?? ($user->username ?? '')) }}" required>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold small" for="mata_pelajaran">
                      Mata Pelajaran <span class="text-danger">*</span>
                    </label>
                    <select id="mata_pelajaran" name="mata_pelajaran"
                      class="form-select @error('mata_pelajaran') is-invalid @enderror" required>
                      <option value="">Pilih Mata Pelajaran</option>
                      @foreach ($mapelOptions as $mapel)
                        <option value="{{ $mapel->nama_mapel }}" {{ old('mata_pelajaran', $guru->mata_pelajaran) === $mapel->nama_mapel ? 'selected' : '' }}>
                          {{ $mapel->nama_mapel }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="row">
                    <div class="col-sm-6 mb-3 mb-sm-0">
                      <label class="form-label fw-semibold small" for="jabatan">
                        Jabatan
                      </label>
                      <input id="jabatan" name="jabatan" type="text"
                        class="form-control @error('jabatan') is-invalid @enderror" placeholder="Contoh: Guru Tetap"
                        value="{{ old('jabatan', $guru->jabatan) }}">
                    </div>

                    <div class="col-sm-6">
                      <label class="form-label fw-semibold small" for="status">
                        Status Aktif <span class="text-danger">*</span>
                      </label>
                      <select id="status" name="status" class="form-select @error('status') is-invalid @enderror"
                        required>
                        <option value="aktif" {{ old('status', $guru->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $guru->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Section 3: Kredensial Login --}}
              <div class="col-12 mt-4">
                <div class="p-4 rounded-3" style="background: rgba(115, 103, 240, 0.03); border: 1px dashed rgba(115, 103, 240, 0.15);">
                  <h6 class="text-info fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="ti tabler-lock fs-5"></i> Akses Akun Login
                  </h6>

                  @if (isset($user))
                    <div class="alert alert-info border-0 shadow-sm mb-0" role="alert" style="background: rgba(0, 207, 232, 0.1);">
                      <i class="ti tabler-info-circle me-2 fs-5"></i>
                      Profil guru akan otomatis diselaraskan dengan akun login:
                      <strong class="text-white">{{ $user->name }}</strong> ({{ $user->email }})
                    </div>
                  @else
                    <div class="mb-3">
                      <label class="form-label fw-semibold small" for="email">
                        Email Login <span class="text-muted">(opsional)</span>
                      </label>
                      <input id="email" name="email" type="email"
                        class="form-control @error('email') is-invalid @enderror" placeholder="nama@sekolah.sch.id"
                        value="{{ old('email', optional($guru->user)->email) }}">
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label fw-semibold small" for="password">
                          Password Akun
                          @if ($guru->exists)
                            <span class="text-white-50 fw-normal ms-1">(kosongkan jika tidak diubah)</span>
                          @else
                            <span class="text-danger">*</span>
                          @endif
                        </label>
                        <div class="password-wrapper">
                          <input id="password" name="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" placeholder="••••••••"
                            {{ $guru->exists ? '' : 'required' }}>
                          <span class="toggle-password" data-target="password">
                            <i class="ti tabler-eye"></i>
                          </span>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label fw-semibold small" for="password_confirmation">
                          Konfirmasi Password
                        </label>
                        <div class="password-wrapper">
                          <input id="password_confirmation" name="password_confirmation" type="password"
                            class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="••••••••"
                            {{ $guru->exists ? '' : 'required' }}>
                          <span class="toggle-password" data-target="password_confirmation">
                            <i class="ti tabler-eye"></i>
                          </span>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('admin.guru.index') }}" class="btn das-btn --secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn das-btn --primary px-4 shadow-sm">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ $guru->exists ? 'Perbarui Data' : 'Simpan Guru' }}
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
                <img src="{{ App\Support\QrCodeGenerator::renderDataUri($guru->qr_code, 120) }}" alt="QR Code Guru"
                  class="img-fluid" />
              </div>
              <div>
                <h6 class="mb-1 text-white fw-bold">ID Card QR Code</h6>
                <p class="text-white-50 small mb-2">Kode QR ini digunakan untuk proses absensi mandiri guru.</p>
                <code class="px-2 py-1 rounded bg-label-info text-info fw-bold">{{ $guru->qr_code }}</code>
              </div>
            </div>
          </div>
        </div>
      @endif

    </div>
  </div>

@endsection
