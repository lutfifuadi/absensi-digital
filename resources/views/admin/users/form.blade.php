@extends('layouts/layoutMaster')

@section('title', isset($user) ? 'Edit User — ' . $user->name : 'Tambah User Baru')

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
            placeholder: 'Pilih Role',
            dropdownParent: $this.parent()
          });
        });
      }
    });
  </script>
@endsection

@section('page-style')
  <style>
    /* ═══════════════════════════════════════════════════════
       DASHBOARD DESIGN SYSTEM (ADAPTED)
    ═══════════════════════════════════════════════════════ */
    :root {
      --das-primary: #7367f0;
      --das-primary-soft: rgba(115, 103, 240, 0.12);
      --das-surface: rgba(15, 23, 42, 0.4);
      --das-border: rgba(255, 255, 255, 0.06);
      --das-radius: 5px;
    }

    /* HERO HEADER (COMPACT) */
    .das-hero-mini {
      position: relative;
      border-radius: var(--das-radius);
      overflow: hidden;
      margin-bottom: 2rem;
      background: linear-gradient(135deg, #1e1b4b 0%, #312d89 60%, #4338ca 100%);
    }

    .das-hero-mini__inner {
      position: relative;
      z-index: 2;
      padding: 2rem 2.5rem;
      display: flex;
      align-items: center;
      gap: 1.25rem;
    }

    .das-hero-mini__icon {
      width: 52px;
      height: 52px;
      background: rgba(115, 103, 240, 0.2);
      border: 1px solid rgba(115, 103, 240, 0.3);
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: #a5a2f7;
    }

    .das-hero-mini__title {
      font-size: 1.25rem;
      font-weight: 800;
      color: white;
      margin: 0;
    }

    /* PANEL */
    .das-panel {
      background: var(--das-surface);
      border: 1px solid var(--das-border);
      border-radius: var(--das-radius);
      overflow: hidden;
      backdrop-filter: blur(8px);
    }

    .das-panel__body {
      padding: 2.5rem;
    }

    /* FORM STYLING */
    .form-label {
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #777;
      margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
      background: rgba(255, 255, 255, 0.03) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      color: #fff !important;
      border-radius: 5px;
      padding: 0.65rem 1rem;
      font-size: 0.88rem;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--das-primary) !important;
      box-shadow: 0 0 0 2px var(--das-primary-soft) !important;
      background: rgba(255, 255, 255, 0.05) !important;
    }

    .form-control.is-invalid {
      border-color: #ea5455 !important;
    }

    /* BUTTONS */
    .das-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.8rem;
      font-weight: 700;
      padding: 0.65rem 1.5rem;
      border-radius: 5px;
      border: 1px solid transparent;
      cursor: pointer;
      transition: all 0.18s ease;
      text-decoration: none;
    }

    .das-btn--primary {
      background: var(--das-primary);
      color: white !important;
      border-color: var(--das-primary);
    }

    .das-btn--primary:hover {
      background: #6259e8;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(115, 103, 240, 0.3);
    }

    .das-btn--ghost {
      background: transparent;
      border-color: var(--das-border);
      color: #999 !important;
    }

    .das-btn--ghost:hover {
      background: rgba(255, 255, 255, 0.05);
      color: white !important;
    }

    /* ANIMATIONS */
    @keyframes slideInUp {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .slide-in-up { animation: slideInUp 0.5s ease-out; }
  </style>
@endsection

@section('content')
  <div class="slide-in-up">
    {{-- HERO MINI --}}
    <div class="das-hero-mini">
      <div class="das-hero-mini__inner">
        <div class="das-hero-mini__icon">
          <i class="ti tabler-{{ isset($user) ? 'edit' : 'user-plus' }}"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:0.6rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">
              <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-white text-decoration-none">Manajemen User</a></li>
              <li class="breadcrumb-item active text-white">{{ isset($user) ? 'Edit User' : 'Tambah User' }}</li>
            </ol>
          </nav>
          <h4 class="das-hero-mini__title">{{ isset($user) ? 'Sunting Data Pengguna' : 'Tambahkan Pengguna Baru' }}</h4>
        </div>
      </div>
    </div>

    {{-- MAIN FORM PANEL --}}
    <div class="das-panel">
      <div class="das-panel__body">
        <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
          @csrf
          @if (isset($user))
            @method('PUT')
          @endif

          <div class="row gy-4">
            <div class="col-md-6">
              <label class="form-label">Username <span class="text-danger">*</span></label>
              <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                placeholder="Masukkan username unik"
                value="{{ old('username', $user->username ?? '') }}" required>
              @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                placeholder="Masukkan nama lengkap user"
                value="{{ old('name', $user->name ?? '') }}" required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                placeholder="nama@email.com"
                value="{{ old('email', $user->email ?? '') }}" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Password {{ isset($user) ? '(Kosongkan jika tidak ingin diubah)' : '' }} <span
                  class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="••••••••"
                {{ isset($user) ? '' : 'required' }} autocomplete="new-password">
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Konfirmasi Password</label>
              <input type="password" name="password_confirmation" class="form-control" 
                placeholder="Ulangi password"
                autocomplete="new-password">
            </div>

            <div class="col-md-6">
              <label class="form-label">Level Hak Akses (Role) <span class="text-danger">*</span></label>
              <select name="roles[]" class="select2 form-select @error('roles') is-invalid @enderror @error('roles.*') is-invalid @enderror" multiple required data-placeholder="Pilih Hak Akses">
                @foreach ($roles as $key => $label)
                  <option value="{{ $key }}"
                    @selected(in_array((string)$key, old('roles', isset($user) ? (array)($user->roles ?? []) : []), true))>{{ $label }}</option>
                @endforeach
              </select>
              <div class="form-text text-white-50 small">Pilih satu atau lebih peran yang sesuai untuk pengguna ini.</div>
              @error('roles')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              @if ($errors->has('roles.*'))
                <div class="invalid-feedback">{{ $errors->first('roles.*') }}</div>
              @endif
            </div>
          </div>

          <div class="mt-5 d-flex gap-2">
            <button type="submit" class="das-btn das-btn--primary">
              <i class="ti tabler-device-floppy me-1"></i> {{ isset($user) ? 'Simpan Perubahan' : 'Daftarkan User' }}
            </button>
            <a href="{{ route('admin.users.index') }}" class="das-btn das-btn--ghost">
              Batal
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
