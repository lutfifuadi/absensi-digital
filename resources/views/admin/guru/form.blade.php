@extends('layouts/layoutMaster')

@section('title', $guru->exists ? 'Ubah Guru' : 'Tambah Guru')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/select2/select2.scss'
    ])
    <style>
        .select2-container--default .select2-selection--multiple {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
            min-height: 38px;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #7367f0 !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #7367f0 !important;
            border: none !important;
            color: #fff !important;
            border-radius: 4px;
            padding: 2px 8px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff !important;
            border-right: 1px solid rgba(255, 255, 255, 0.2) !important;
            margin-right: 5px !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
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
        .select2-container--default .select2-search--inline .select2-search__field {
            color: #fff !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 2px 6px !important;
        }
    </style>
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
              <i class="ti {{ $guru->exists ? 'tabler-pencil' : 'tabler-plus' }} text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}"
                      class="text-white text-decoration-none">Master Data</a></li>
                  <li class="breadcrumb-item"><a href="{{ route('admin.guru.index') }}"
                      class="text-white text-decoration-none">Guru</a></li>
                  <li class="breadcrumb-item active text-white">{{ $guru->exists ? 'Ubah' : 'Tambah' }}
                  </li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                {{ $guru->exists ? 'Ubah Data Guru' : 'Tambah Guru Baru' }}
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
          <h6 class="card-title mb-0">Informasi Lengkap Guru</h6>
        </div>
        <div class="card-body p-4">
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
                      <i class="ti tabler-user me-1 text-info"></i> Nama Lengkap <span class="text-danger">*</span>
                    </label>
                    <input id="nama_lengkap" name="nama_lengkap" type="text"
                      class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Nama lengkap & gelar"
                      value="{{ old('nama_lengkap', $guru->nama_lengkap ?? ($user->name ?? '')) }}" required>
                  </div>

                  <div class="row">
                    <div class="col-sm-6 mb-3">
                      <label class="form-label fw-semibold small" for="jenis_kelamin">
                        <i class="ti tabler-gender-bigender me-1 text-info"></i> Jenis Kelamin <span class="text-danger">*</span>
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
                        <i class="ti tabler-phone me-1 text-info"></i> No. HP / WhatsApp
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
                      <i class="ti tabler-id me-1 text-info"></i> NIP <span class="text-danger">*</span>
                    </label>
                    <input id="nip" name="nip" type="text"
                      class="form-control @error('nip') is-invalid @enderror" placeholder="Nomor Induk Pegawai"
                      value="{{ old('nip', $guru->nip ?? ($user->username ?? '')) }}" required>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold small" for="mapel_ids">
                      <i class="ti tabler-book me-1 text-info"></i> Mata Pelajaran <span class="text-danger">*</span>
                    </label>
                    <select id="mapel_ids" name="mapel_ids[]"
                      class="form-select select2 @error('mapel_ids') is-invalid @enderror" multiple data-placeholder="Pilih satu atau beberapa mata pelajaran..." required>
                      @foreach ($mapelOptions as $mapel)
                        <option value="{{ $mapel->id }}" {{ in_array($mapel->id, old('mapel_ids', $guru->mapels->pluck('id')->toArray())) ? 'selected' : '' }}>
                          {{ $mapel->nama_mapel }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="row">
                    <div class="col-sm-6 mb-3 mb-sm-0">
                      <label class="form-label fw-semibold small" for="jabatan">
                        <i class="ti tabler-stairs-up me-1 text-info"></i> Jabatan
                      </label>
                      <input id="jabatan" name="jabatan" type="text"
                        class="form-control @error('jabatan') is-invalid @enderror" placeholder="Contoh: Guru Tetap"
                        value="{{ old('jabatan', $guru->jabatan) }}">
                    </div>

                    <div class="col-sm-6">
                      <label class="form-label fw-semibold small" for="status">
                        <i class="ti tabler-circle-check me-1 text-info"></i> Status Aktif <span class="text-danger">*</span>
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
                        <i class="ti tabler-mail me-1 text-info"></i> Email Login <span class="text-muted">(opsional)</span>
                      </label>
                      <input id="email" name="email" type="email"
                        class="form-control @error('email') is-invalid @enderror" placeholder="nama@sekolah.sch.id"
                        value="{{ old('email', optional($guru->user)->email) }}">
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label fw-semibold small" for="password">
                          <i class="ti tabler-key me-1 text-info"></i> Password Akun
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
                          <i class="ti tabler-key me-1 text-info"></i> Konfirmasi Password
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
              <a href="{{ route('admin.guru.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
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