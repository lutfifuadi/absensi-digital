@extends('layouts/layoutMaster')

@section('title', 'Pengaturan Profil & Ganti Password')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 text-white overflow-hidden shadow-sm"
            style="background: linear-gradient(135deg, #7367f0 0%, #4338ca 100%); border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                            style="width:52px;height:52px;border-radius:10px !important;background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);">
                            <i class="ti tabler-settings text-white fs-3"></i>
                        </div>
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.8;">
                                    <li class="breadcrumb-item"><a href="{{ route('ortu.dashboard') }}"
                                            class="text-white text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Pengaturan</li>
                                </ol>
                            </nav>
                            <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Pengaturan Profil & Keamanan</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Kolom Edit Data Diri -->
    <div class="col-md-6 col-12 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="ti tabler-user me-2 text-primary"></i>Data Diri</h5>
            </div>
            <div class="card-body pt-4">
                @if(session('success') && !session('password_success'))
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 border-0" role="alert" style="border-radius: 8px;">
                        <i class="ti tabler-circle-check fs-5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <form action="{{ route('ortu.pengaturan.profil') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1" for="name">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror py-2" id="name" name="name" value="{{ old('name', $user->name) }}" required placeholder="Masukkan Nama Lengkap">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1" for="hubungan">Hubungan <span class="text-danger">*</span></label>
                        <select class="form-select @error('hubungan') is-invalid @enderror py-2" id="hubungan" name="hubungan" required>
                            <option value="" disabled>Pilih Hubungan</option>
                            <option value="Ayah" {{ old('hubungan', $user->hubungan) == 'Ayah' ? 'selected' : '' }}>Ayah</option>
                            <option value="Ibu" {{ old('hubungan', $user->hubungan) == 'Ibu' ? 'selected' : '' }}>Ibu</option>
                            <option value="Wali" {{ old('hubungan', $user->hubungan) == 'Wali' ? 'selected' : '' }}>Wali</option>
                        </select>
                        @error('hubungan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1" for="no_hp">Nomor WhatsApp/Telepon <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('no_hp') is-invalid @enderror py-2" id="no_hp" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" required placeholder="Contoh: 08123456789">
                        @error('no_hp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1" for="email">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror py-2" id="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="Masukkan Alamat Email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1" for="alamat">Alamat Rumah</label>
                        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" placeholder="Masukkan Alamat Rumah">{{ old('alamat', $user->alamat) }}</textarea>
                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2 w-100 fw-semibold">
                        <i class="ti tabler-device-floppy"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Ganti Password -->
    <div class="col-md-6 col-12 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="ti tabler-lock me-2 text-warning"></i>Ganti Password</h5>
            </div>
            <div class="card-body pt-4">
                @if(session('success') && session('password_success'))
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 border-0" role="alert" style="border-radius: 8px;">
                        <i class="ti tabler-circle-check fs-5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <form action="{{ route('ortu.pengaturan.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4 form-password-toggle">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1" for="password_lama">Password Lama <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="password" class="form-control @error('password_lama') is-invalid @enderror py-2" id="password_lama" name="password_lama" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required>
                            <span class="input-group-text cursor-pointer"><i class="ti tabler-eye-off"></i></span>
                        </div>
                        @error('password_lama')
                            <span class="text-danger small mt-1.5 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4 form-password-toggle">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1" for="password">Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="password" class="form-control @error('password') is-invalid @enderror py-2" id="password" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required>
                            <span class="input-group-text cursor-pointer"><i class="ti tabler-eye-off"></i></span>
                        </div>
                        <small class="text-muted mt-1.5 d-block">Minimal 8 karakter kombinasi huruf & angka</small>
                        @error('password')
                            <span class="text-danger small mt-1.5 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4 form-password-toggle">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1" for="password_confirmation">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="password" class="form-control py-2" id="password_confirmation" name="password_confirmation" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required>
                            <span class="input-group-text cursor-pointer"><i class="ti tabler-eye-off"></i></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning text-white d-flex align-items-center justify-content-center gap-2 py-2 w-100 fw-semibold">
                        <i class="ti tabler-key"></i> Perbarui Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>@endsection
@endsection
