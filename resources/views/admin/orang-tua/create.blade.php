@extends('layouts/layoutMaster')

@section('title', 'Tambah Orang Tua')

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
    <div class="das-hero mb-4">
        <div class="das-hero__bg"></div>
        <div class="das-hero__glass"></div>
        <div class="das-hero__grid-lines"></div>

        <div class="das-hero__inner">
            <div class="das-hero__identity">
                <div class="das-hero__logo-wrapper">
                    <div class="das-hero__logo-placeholder">
                        <i class="ti tabler-plus text-info"></i>
                    </div>
                    <div class="das-hero__logo-glow"></div>
                </div>

                <div class="das-hero__meta">
                    <div class="das-hero__badge">
                        <span class="pulse-dot"></span>
                        <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / <a href="{{ route('admin.orang-tua.index') }}" class="text-white text-decoration-none">Orang Tua</a> / Tambah
                    </div>
                    <h4 class="das-hero__title text-gradient-gold">Tambah Orang Tua Baru</h4>
                    <p class="das-hero__subtitle">Buat akun orang tua / wali murid baru dan hubungkan ke siswa terkait.</p>
                </div>
            </div>

            <div class="das-hero__actions">
                <a href="{{ route('admin.orang-tua.index') }}" class="btn das-btn --secondary">
                    Kembali
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

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
                    style="border-radius:8px; background: rgba(234, 84, 85, 0.15); color: #ea5455;">
                    <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
                    <span class="small">{{ session('error') }}</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="das-panel">
                <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2"
                    style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
                    <i class="ti tabler-forms text-info"></i>
                    <h6 class="das-panel__title mb-0 text-white">Informasi Orang Tua</h6>
                </div>
                <div class="das-panel__body p-4">
                    <form action="{{ route('admin.orang-tua.store') }}" method="POST">
                        @csrf

                        <div class="row g-4">
                            {{-- Biodata --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-white" for="name">
                                    <i class="ti tabler-user me-1 text-primary"></i> Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input id="name" name="name" type="text"
                                    class="form-control text-white bg-transparent border-white-10 @error('name') is-invalid @enderror" 
                                    style="background-color:rgba(255,255,255,0.05) !important;"
                                    placeholder="Masukkan nama lengkap" value="{{ old('name') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-white" for="no_hp">
                                    <i class="ti tabler-brand-whatsapp me-1 text-primary"></i> WhatsApp / No. HP
                                </label>
                                <input id="no_hp" name="no_hp" type="text"
                                    class="form-control text-white bg-transparent border-white-10 @error('no_hp') is-invalid @enderror" 
                                    style="background-color:rgba(255,255,255,0.05) !important;"
                                    placeholder="Contoh: 081234567890" value="{{ old('no_hp') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-white" for="hubungan">
                                    <i class="ti tabler-git-merge me-1 text-primary"></i> Hubungan <span class="text-danger">*</span>
                                </label>
                                <select id="hubungan" name="hubungan" class="form-select text-white bg-transparent border-white-10 @error('hubungan') is-invalid @enderror" style="background-color:rgba(255,255,255,0.05) !important;" required>
                                    <option value="Ayah" {{ old('hubungan') == 'Ayah' ? 'selected' : '' }}>Ayah</option>
                                    <option value="Ibu" {{ old('hubungan') == 'Ibu' ? 'selected' : '' }}>Ibu</option>
                                    <option value="Wali" {{ old('hubungan') == 'Wali' ? 'selected' : '' }}>Wali</option>
                                    <option value="Lainnya" {{ old('hubungan') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-white" for="status">
                                    <i class="ti tabler-circle-check me-1 text-primary"></i> Status Akun <span class="text-danger">*</span>
                                </label>
                                <select id="status" name="status" class="form-select text-white bg-transparent border-white-10 @error('status') is-invalid @enderror" style="background-color:rgba(255,255,255,0.05) !important;" required>
                                    <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>

                            {{-- Akun & Login --}}
                            <div class="col-12">
                                <hr class="my-2 border-light">
                                <h6 class="text-white fw-bold mb-3"><i class="ti tabler-lock me-1 text-primary"></i> Akun Login</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-white" for="username">
                                    <i class="ti tabler-user-shield me-1 text-primary"></i> Username <span class="text-danger">*</span>
                                </label>
                                <input id="username" name="username" type="text"
                                    class="form-control text-white bg-transparent border-white-10 @error('username') is-invalid @enderror" 
                                    style="background-color:rgba(255,255,255,0.05) !important;"
                                    placeholder="Username untuk login" value="{{ old('username') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-white" for="email">
                                    <i class="ti tabler-mail me-1 text-primary"></i> Email <span class="text-danger">*</span>
                                </label>
                                <input id="email" name="email" type="email"
                                    class="form-control text-white bg-transparent border-white-10 @error('email') is-invalid @enderror" 
                                    style="background-color:rgba(255,255,255,0.05) !important;"
                                    placeholder="email@example.com" value="{{ old('email') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-white" for="password">
                                    <i class="ti tabler-key me-1 text-primary"></i> Password <span class="text-danger">*</span>
                                </label>
                                <input id="password" name="password" type="password"
                                    class="form-control text-white bg-transparent border-white-10 @error('password') is-invalid @enderror" 
                                    style="background-color:rgba(255,255,255,0.05) !important;"
                                    placeholder="Password minimal 6 karakter" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-white" for="password_confirmation">
                                    <i class="ti tabler-key me-1 text-primary"></i> Konfirmasi Password <span class="text-danger">*</span>
                                </label>
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                    class="form-control text-white bg-transparent border-white-10" 
                                    style="background-color:rgba(255,255,255,0.05) !important;"
                                    placeholder="Ulangi password" required>
                            </div>

                            {{-- Pemetaan Siswa (Select2) --}}
                            <div class="col-12">
                                <hr class="my-2 border-light">
                                <h6 class="text-white fw-bold mb-3"><i class="ti tabler-users-group me-1 text-primary"></i> Hubungkan ke Siswa</h6>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold small text-white" for="siswa_ids">
                                    <i class="ti tabler-users me-1 text-primary"></i> Pilih Siswa (Bisa multi-select)
                                </label>
                                <select id="siswa_ids" name="siswa_ids[]" class="form-select select2" multiple data-placeholder="Pilih satu atau beberapa siswa...">
                                    @foreach($siswaOptions as $siswa)
                                        <option value="{{ $siswa->id }}" {{ in_array($siswa->id, old('siswa_ids', [])) ? 'selected' : '' }}>
                                            {{ $siswa->nama_lengkap }} (NIS: {{ $siswa->nis ?? '-' }} / Kelas: {{ optional($siswa->kelas)->nama ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-5 d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                    <i class="ti tabler-device-floppy me-1"></i> Simpan Data
                                </button>
                                <a href="{{ route('admin.orang-tua.index') }}" class="btn btn-label-secondary px-4">
                                    Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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
