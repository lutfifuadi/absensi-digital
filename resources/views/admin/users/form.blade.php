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
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Toggle password visibility
      document.querySelectorAll('.toggle-password').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
          const targetId = this.dataset.target;
          const input = document.getElementById(targetId);
          if (input) {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            const icon = this.querySelector('i');
            if (icon) {
              icon.classList.toggle('tabler-eye');
              icon.classList.toggle('tabler-eye-off');
            }
          }
        });
      });
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

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.25) !important;
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
    }

    .password-wrapper .toggle-password:hover {
      color: rgba(255, 255, 255, 0.8);
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

  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 4px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
              style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
              <i class="ti {{ isset($user) ? 'tabler-pencil' : 'tabler-plus' }} text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}"
                      class="text-white text-decoration-none">Master Data</a></li>
                  <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}"
                      class="text-white text-decoration-none">Manajemen User</a></li>
                  <li class="breadcrumb-item active text-white">{{ isset($user) ? 'Edit' : 'Tambah' }}
                  </li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                {{ isset($user) ? 'Sunting Data Pengguna' : 'Tambahkan Pengguna Baru' }}
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
          <h6 class="card-title mb-0">Informasi Akun Pengguna</h6>
        </div>
        <div class="card-body p-4">
          <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}"
            method="POST">
            @csrf
            @if (isset($user))
              @method('PUT')
            @endif

            <div class="row g-4">
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="username">
                  <i class="ti tabler-user me-1 text-info"></i> Username <span class="text-danger">*</span>
                </label>
                <input id="username" name="username" type="text"
                  class="form-control @error('username') is-invalid @enderror"
                  placeholder="Masukkan username unik"
                  value="{{ old('username', $user->username ?? '') }}" required>
                @error('username')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="name">
                  <i class="ti tabler-user-check me-1 text-info"></i> Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input id="name" name="name" type="text"
                  class="form-control @error('name') is-invalid @enderror"
                  placeholder="Masukkan nama lengkap user"
                  value="{{ old('name', $user->name ?? '') }}" required>
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="email">
                  <i class="ti tabler-mail me-1 text-info"></i> Email <span class="text-danger">*</span>
                </label>
                <input id="email" name="email" type="email"
                  class="form-control @error('email') is-invalid @enderror"
                  placeholder="nama@email.com"
                  value="{{ old('email', $user->email ?? '') }}" required>
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="password">
                  <i class="ti tabler-key me-1 text-info"></i> Password
                  @if (isset($user))
                    <span class="text-white-50 fw-normal ms-1">(kosongkan jika tidak diubah)</span>
                  @else
                    <span class="text-danger">*</span>
                  @endif
                </label>
                <div class="password-wrapper">
                  <input id="password" name="password" type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="••••••••"
                    {{ isset($user) ? '' : 'required' }} autocomplete="new-password">
                  <span class="toggle-password" data-target="password">
                    <i class="ti tabler-eye"></i>
                  </span>
                </div>
                @error('password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="password_confirmation">
                  <i class="ti tabler-key me-1 text-info"></i> Konfirmasi Password
                </label>
                <div class="password-wrapper">
                  <input id="password_confirmation" name="password_confirmation" type="password"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    placeholder="Ulangi password"
                    {{ isset($user) ? '' : 'required' }} autocomplete="new-password">
                  <span class="toggle-password" data-target="password_confirmation">
                    <i class="ti tabler-eye"></i>
                  </span>
                </div>
                @error('password_confirmation')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small">
                  <i class="ti tabler-shield me-1 text-info"></i> Hak Akses (Role) <span class="text-danger">*</span>
                </label>
                <select name="roles[]" class="select2 form-select @error('roles') is-invalid @enderror @error('roles.*') is-invalid @enderror"
                  multiple required data-placeholder="Pilih Hak Akses">
                  @foreach ($roles as $key => $label)
                    <option value="{{ $key }}"
                      @selected(in_array((string)$key, old('roles', isset($user) ? (array)($user->roles ?? []) : []), true))>{{ $label }}</option>
                  @endforeach
                </select>
                <div class="form-text text-white-50 small mt-2">Pilih satu atau lebih peran yang sesuai untuk pengguna ini.</div>
                @error('roles')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if ($errors->has('roles.*'))
                  <div class="invalid-feedback">{{ $errors->first('roles.*') }}</div>
                @endif
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('admin.users.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ isset($user) ? 'Simpan Perubahan' : 'Daftarkan User' }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

@endsection
