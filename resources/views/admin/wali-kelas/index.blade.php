@extends('layouts/layoutMaster')

@section('title', 'Wali Kelas')

@section('page-style')
  <style>
    .wk-row-hover {
      transition: background 0.15s ease;
    }

    .wk-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      transition: all 0.2s ease;
      border: none;
      background: rgba(255, 255, 255, 0.05);
      color: inherit;
    }

    .action-btn:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.1);
    }
  </style>
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-users-group text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Wali Kelas
          </div>
          <h4 class="das-hero__title text-gradient-gold">Data Wali Kelas</h4>
          <p class="das-hero__subtitle">Kelola seluruh data wali kelas, jabatan, dan akses sistem.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.wali-kelas.cetak-qr') }}" class="btn das-btn --info">
          <i class="ti tabler-qrcode me-1"></i> Cetak QR Massal
        </a>
        <a href="{{ route('admin.wali-kelas.create') }}" class="btn das-btn --primary">
          <i class="ti tabler-plus me-1"></i> Tambah Wali Kelas
        </a>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGE --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('info'))
    <div class="alert alert-info alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-info-circle fs-5"></i>
      <span>{{ session('info') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5"></i>
      <span>{{ session('error') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- TABLE CARD --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Wali Kelas
      </h6>
      <span class="das-chip --info">{{ count($waliKelasUsers) }} Wali Kelas</span>
    </div>
    <div class="das-panel__body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="color:inherit;">
          <thead
            style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
            <tr>
              <th class="ps-4 py-3" style="width:46px;">#</th>
              <th class="py-3">Informasi Wali Kelas</th>
              <th class="py-3 d-none d-md-table-cell">NIP</th>
              <th class="py-3">Mata Pelajaran</th>
              <th class="py-3 text-center">Status</th>
              <th class="py-3 d-none d-lg-table-cell">Role</th>
              <th class="py-3 d-none d-xl-table-cell">Email Login</th>
              <th class="py-3 pe-4 text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($waliKelasUsers as $item)
              @php
                $profile        = $item->guru;
                $displayName    = $profile->nama_lengkap ?? $item->name;
                $displayJabatan = $profile->jabatan ?? 'Wali Kelas';
                $displayNip     = $profile->nip ?? '-';
                $displayMapel   = $profile ? ($profile->mata_pelajaran ?: 'Belum diisi') : 'Belum diisi';
                $displayStatus  = $profile->status ?? 'belum lengkap';
                $statusClass    = $profile ? ($profile->status === 'aktif' ? 'success' : 'danger') : 'secondary';
              @endphp
              <tr class="wk-row-hover">
                <td class="ps-4 text-white-50 small">{{ $loop->iteration }}</td>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md">
                      <span class="avatar-initial rounded-circle bg-label-info" style="font-size:0.85rem;">
                        {{ strtoupper(substr($displayName, 0, 1)) }}{{ strtoupper(substr(strrchr($displayName, ' ') ?: $displayName, 1, 1)) }}
                      </span>
                    </div>
                    <div>
                      <div class="fw-bold mb-0" style="font-size:0.9rem;">{{ $displayName }}</div>
                      <div class="text-white-50 small" style="font-size:0.72rem;">{{ $displayJabatan }}</div>
                    </div>
                  </div>
                </td>
                <td class="d-none d-md-table-cell text-white-50 small">
                  {{ $displayNip }}
                </td>
                <td>
                  @if ($profile && $profile->mata_pelajaran)
                    <span class="badge bg-label-warning text-capitalize">{{ $displayMapel }}</span>
                  @else
                    <span class="text-white-50 small">Belum diisi</span>
                  @endif
                </td>
                <td class="text-center">
                  <span
                    class="badge bg-label-{{ $statusClass }} text-capitalize px-2">{{ $displayStatus }}</span>
                </td>
                <td class="d-none d-lg-table-cell text-capitalize small text-white-50">
                  @php
                    $userRoles = [];
                    if ($item->role) {
                      $userRoles[] = $item->role;
                    }
                    $userRoles = array_unique(array_filter(array_merge($userRoles, $item->roles ?? [])));
                  @endphp
                  @if (count($userRoles) > 0)
                    {{ implode(', ', array_map(fn ($role) => str_replace('_', ' ', ucfirst($role)), $userRoles)) }}
                  @else
                    -
                  @endif
                </td>
                <td class="d-none d-xl-table-cell small text-white-50">
                  {{ $item->email ?? '-' }}
                </td>
                <td class="pe-4 text-end">
                  <div class="d-flex justify-content-end gap-1">
                    @if ($profile)
                      <a href="{{ route('admin.wali-kelas.generate-qr', $profile->id) }}" class="action-btn text-info"
                        title="Unduh QR" data-bs-toggle="tooltip">
                        <i class="ti tabler-qrcode fs-5"></i>
                      </a>
                      <a href="{{ route('admin.wali-kelas.edit', $profile->id) }}" class="action-btn text-warning"
                        title="Ubah" data-bs-toggle="tooltip">
                        <i class="ti tabler-pencil fs-5"></i>
                      </a>
                      <form action="{{ route('admin.wali-kelas.destroy', $profile->id) }}" method="POST"
                        class="d-inline" onsubmit="return confirm('Yakin ingin menghapus wali kelas ini?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn text-danger" title="Hapus" data-bs-toggle="tooltip">
                          <i class="ti tabler-trash fs-5"></i>
                        </button>
                      </form>
                    @else
                      <a href="{{ route('admin.wali-kelas.create', ['user_id' => $item->id]) }}"
                        class="action-btn text-warning" title="Lengkapi Profil Wali Kelas" data-bs-toggle="tooltip">
                        <i class="ti tabler-pencil fs-5"></i>
                      </a>
                      <form action="{{ route('admin.wali-kelas.destroy-user', $item->id) }}" method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Yakin ingin menghapus akun wali kelas ini? Profil belum lengkap.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn text-danger" title="Hapus Akun" data-bs-toggle="tooltip">
                          <i class="ti tabler-trash fs-5"></i>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-5">
                  <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                    <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
                    <span class="small">Belum ada data wali kelas.</span>
                    <a href="{{ route('admin.wali-kelas.create') }}" class="btn btn-sm btn-label-info mt-1">
                      <i class="ti tabler-plus me-1"></i> Tambah Sekarang
                    </a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>
@endsection
