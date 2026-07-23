@extends('layouts/layoutMaster')

@section('title', $guru->exists ? 'Ubah Guru' : 'Tambah Guru')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/select2/select2.scss'
    ])
    <style>
        /* ── Custom Role Checkbox ── */
        .role-checkbox-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            transition: all 0.25s ease;
            cursor: pointer;
            user-select: none;
            height: 100%;
        }
        .role-checkbox-card:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(115, 103, 240, 0.25);
        }
        .role-checkbox-card .form-check-input:checked ~ .role-checkbox-label {
            color: #7367f0;
        }
        .role-checkbox-card .form-check-input:checked ~ .role-checkbox-icon {
            opacity: 1;
        }
        .role-checkbox-card .form-check-input:checked {
            background-color: #7367f0;
            border-color: #7367f0;
        }
        .role-checkbox-card.is-checked {
            background: rgba(115, 103, 240, 0.1);
            border-color: rgba(115, 103, 240, 0.3);
        }
        .role-checkbox-card.is-disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .role-checkbox-card.is-disabled:hover {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.06);
        }
        .role-checkbox-icon {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            flex-shrink: 0;
            transition: all 0.25s ease;
        }
        .role-checkbox-icon i {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.6);
            transition: color 0.25s ease;
        }
        .role-checkbox-card .form-check-input:checked ~ .role-checkbox-icon {
            background: rgba(115, 103, 240, 0.2);
        }
        .role-checkbox-card .form-check-input:checked ~ .role-checkbox-icon i {
            color: #7367f0;
        }
        .role-checkbox-label {
            font-size: 0.85rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            transition: color 0.25s ease;
            line-height: 1.2;
            margin: 0;
            cursor: pointer;
        }

        /* ── Kelas Dropdown Wrapper ── */
        #kelas-wrapper {
            transition: all 0.3s ease;
        }
        #kelas-wrapper.d-none {
            display: none !important;
        }
        #kelas-wrapper:not(.d-none) {
            animation: fadeSlideDown 0.3s ease forwards;
        }
        @keyframes fadeSlideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Password Wrapper ── */
        .password-wrapper {
            position: relative;
        }
        .password-wrapper .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.4);
            font-size: 1.1rem;
            transition: color 0.2s;
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
            z-index: 10;
        }
        .password-wrapper .toggle-password:hover {
            color: rgba(255, 255, 255, 0.8);
        }
        .password-wrapper .form-control {
            padding-right: 40px !important;
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
                      <i class="ti {{ $guru->exists ? 'tabler-pencil' : 'tabler-plus' }} text-info"></i>
                  </div>
                  <div class="das-hero__logo-glow"></div>
              </div>

              <div class="das-hero__meta">
                  <div class="das-hero__badge">
                      <span class="pulse-dot"></span>
                      <a href="{{ route('admin.master-data') }}" class="text-white-50 text-decoration-none">Master Data</a> / 
                      <a href="{{ route('admin.guru.index') }}" class="text-white-50 text-decoration-none">Guru</a> / 
                      <span class="text-white">{{ $guru->exists ? 'Ubah' : 'Tambah' }}</span>
                  </div>
                  <h4 class="das-hero__title text-gradient-gold">
                      {{ $guru->exists ? 'Ubah Data Guru' : 'Tambah Guru Baru' }}
                  </h4>
                  <p class="das-hero__subtitle">
                      {{ $guru->exists ? 'Silakan perbarui detail data tenaga pendidik di bawah ini.' : 'Lengkapi formulir untuk menambahkan tenaga pendidik baru.' }}
                  </p>
              </div>
          </div>

          <div class="das-hero__actions">
              <a href="{{ route('admin.guru.index') }}" class="btn das-btn --secondary">
                  <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
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
                      class="select2 form-select @error('mapel_ids') is-invalid @enderror"
                      multiple required data-placeholder="Ketik untuk mencari atau memilih mata pelajaran...">
                      @foreach ($mapelOptions as $mapel)
                        @if (trim($mapel->nama_mapel) !== '-' && trim($mapel->nama_mapel) !== '')
                          <option value="{{ $mapel->id }}" 
                            @selected(in_array((string)$mapel->id, old('mapel_ids', $guru->mapels->pluck('id')->map(fn($id) => (string)$id)->toArray()), true))>
                            {{ trim($mapel->nama_mapel) }}
                          </option>
                        @endif
                      @endforeach
                      {{-- Handle old input tags that are custom strings (newly typed tags upon validation failure) --}}
                      @if(old('mapel_ids'))
                        @foreach(old('mapel_ids') as $oldId)
                          @if(!is_numeric($oldId))
                            <option value="{{ $oldId }}" selected>{{ $oldId }}</option>
                          @endif
                        @endforeach
                      @endif
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

            {{-- Section 4: Role & Akses Tambahan --}}
            <div class="row g-4 mt-4">
                <div class="col-12">
                    <div class="p-4 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
                        <h6 class="text-info fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="ti tabler-shield-lock fs-5"></i> Role & Akses Tambahan
                        </h6>

                        <div class="row g-3">
                            @php
                                $roleIcons = [
                                    'guru'      => 'tabler-user',
                                    'wali_kelas'=> 'tabler-users',
                                    'staff_tu'  => 'tabler-building-arch',
                                    'piket'     => 'tabler-clock',
                                ];
                            @endphp

                            @foreach($roleOptions as $role)
                                @php
                                    $isGuru   = $role === 'guru';
                                    $isChecked = $isGuru || in_array($role, $userRoles ?? []);
                                @endphp
                                <div class="col-md-3 col-6">
                                    <label class="role-checkbox-card {{ $isChecked ? 'is-checked' : '' }} {{ $isGuru ? 'is-disabled' : '' }}"
                                        for="role_{{ $role }}">
                                        <input class="form-check-input role-checkbox d-none" type="checkbox"
                                            name="roles[]" value="{{ $role }}"
                                            id="role_{{ $role }}"
                                            {{ $isGuru ? 'checked disabled' : '' }}
                                            {{ $isChecked ? 'checked' : '' }}>
                                        <span class="role-checkbox-icon">
                                            <i class="ti {{ $roleIcons[$role] ?? 'tabler-shield' }}"></i>
                                        </span>
                                        <span class="role-checkbox-label">
                                            {{ ucwords(str_replace('_', ' ', $role)) }}
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        {{-- Dropdown Kelas — muncul hanya jika wali_kelas dipilih --}}
                        <div id="kelas-wrapper" class="mt-4 {{ in_array('wali_kelas', $userRoles ?? []) ? '' : 'd-none' }}">
                            <hr class="my-3" style="border-color:rgba(255,255,255,0.06);">
                            <label class="form-label fw-semibold small mb-2" for="kelas_id">
                                <i class="ti tabler-school me-1 text-info"></i> Pilih Kelas (Wali Kelas)
                            </label>
                            <select id="kelas_id" name="kelas_id"
                                class="form-select select2-kelas @error('kelas_id') is-invalid @enderror"
                                data-placeholder="Pilih kelas...">
                                <option value="">— Pilih Kelas —</option>
                                @foreach($kelasOptions as $kelas)
                                    <option value="{{ $kelas->id }}"
                                        {{ $kelasSaatIni && $kelasSaatIni->id == $kelas->id ? 'selected' : '' }}>
                                        {{ $kelas->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('admin.guru.index') }}" class="btn das-btn --secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn das-btn --info fw-semibold px-4">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ $guru->exists ? 'Perbarui Data' : 'Simpan Guru' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- DUAL QR CODE PREVIEW (Only for Edit) --}}
      @if ($guru->exists)
        <div class="card border-0 shadow-sm mt-4"
          style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
          <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
            style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
            <i class="ti tabler-qrcode text-info"></i>
            <h6 class="card-title mb-0">Dual QR Code Guru (ID Unik & NIP)</h6>
          </div>
          <div class="card-body p-4">
            <div class="row g-4">
              {{-- QR Code 1: ID Unik (UUID) --}}
              <div class="col-md-6">
                <div class="p-3 rounded-3 h-100 d-flex align-items-center gap-3"
                  style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
                  <div class="bg-white p-2 rounded shadow-sm flex-shrink-0">
                    <img src="{{ App\Support\QrCodeGenerator::renderDataUri($guru->qr_code ?? App\Support\QrCodeGenerator::generate('GURU'), 110) }}"
                      alt="QR Code ID Unik" class="img-fluid" style="width:110px; height:110px;" />
                  </div>
                  <div>
                    <span class="badge bg-label-primary mb-1"><i class="ti tabler-shield-check me-1"></i> QR Code ID Unik</span>
                    <p class="text-white-50 extra-small mb-2">Kode QR unik berbasis UUID untuk absensi tingkat keamanan tinggi.</p>
                    <code class="px-2 py-1 rounded bg-label-info text-info fw-bold extra-small text-break">{{ $guru->qr_code }}</code>
                  </div>
                </div>
              </div>

              {{-- QR Code 2: NIP Guru --}}
              <div class="col-md-6">
                <div class="p-3 rounded-3 h-100 d-flex align-items-center gap-3"
                  style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
                  <div class="bg-white p-2 rounded shadow-sm flex-shrink-0">
                    <img src="{{ App\Support\QrCodeGenerator::renderDataUri($guru->qr_code_nip ?? $guru->nip, 110) }}"
                      alt="QR Code NIP" class="img-fluid" style="width:110px; height:110px;" />
                  </div>
                  <div>
                    <span class="badge bg-label-success mb-1"><i class="ti tabler-id me-1"></i> QR Code NIP (Terdeteksi)</span>
                    <p class="text-white-50 extra-small mb-2">Kode QR berbasis NIP resmi guru yang langsung dapat di-scan oleh sistem.</p>
                    <code class="px-2 py-1 rounded bg-label-success text-success fw-bold extra-small text-break">{{ $guru->qr_code_nip ?? $guru->nip }}</code>
                  </div>
                </div>
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
  <script type="module">
    $(function() {
      // 1. Select2 for Mata Pelajaran (with tag support)
      const $mapelSelect = $('#mapel_ids');
      if ($mapelSelect.length) {
        $mapelSelect.wrap('<div class="position-relative"></div>').select2({
          placeholder: 'Ketik untuk mencari atau memilih mata pelajaran...',
          dropdownParent: $mapelSelect.parent(),
          width: '100%',
          tags: true,
          tokenSeparators: [',']
        });
      }

      // 2. Select2 for Kelas / select2-kelas
      const $kelasSelect = $('.select2-kelas');
      if ($kelasSelect.length) {
        $kelasSelect.wrap('<div class="position-relative"></div>').select2({
          placeholder: 'Pilih Kelas',
          dropdownParent: $kelasSelect.parent(),
          width: '100%'
        });
      }

      // 3. Toggle Kelas Dropdown (wali_kelas)
      const $waliKelasCheckbox = $('#role_wali_kelas');
      const $kelasWrapper = $('#kelas-wrapper');

      function toggleKelas(show) {
        if (show) {
          $kelasWrapper.removeClass('d-none');
        } else {
          $kelasWrapper.addClass('d-none');
        }
      }

      if ($waliKelasCheckbox.length) {
        toggleKelas($waliKelasCheckbox.is(':checked'));
        $waliKelasCheckbox.on('change', function() {
          toggleKelas($(this).is(':checked'));
        });
      }

      // 4. Role Checkbox – visual card toggle
      $(document).on('change', '.role-checkbox:not(:disabled)', function () {
        var $card = $(this).closest('.role-checkbox-card');
        if ($(this).is(':checked')) {
          $card.addClass('is-checked');
        } else {
          $card.removeClass('is-checked');
        }
      });

      // 5. Toggle password visibility
      $(document).on('click', '.toggle-password', function() {
        const targetId = $(this).data('target');
        const $input = $('#' + targetId);
        if ($input.length) {
          const isPassword = $input.attr('type') === 'password';
          $input.attr('type', isPassword ? 'text' : 'password');
          const $icon = $(this).find('i');
          if ($icon.length) {
            $icon.toggleClass('tabler-eye tabler-eye-off');
          }
        }
      });
    });
  </script>
@endsection