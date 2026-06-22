@extends('layouts/layoutMaster')

@section('title', 'Guru')

@section('page-style')
  <style>
    .guru-row-hover {
      transition: background 0.15s ease;
    }

    .guru-row-hover:hover {
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
            <i class="ti tabler-school text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Guru
          </div>
          <h4 class="das-hero__title text-gradient-gold">Data Guru</h4>
          <p class="das-hero__subtitle">Kelola seluruh data tenaga pendidik, jabatan, dan akses sistem.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <button type="button" class="btn das-btn --secondary" data-bs-toggle="modal" data-bs-target="#importModal">
          <i class="ti tabler-file-import me-1"></i> Import
        </button>
        <div class="btn-group">
          <button type="button" class="btn das-btn --success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ti tabler-download me-1"></i> Export
          </button>
          <ul class="dropdown-menu dropdown-menu-end das-modal border-0 shadow-lg" style="min-width: 200px;">
            <li>
              <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 text-white-50 hover-bg-primary-light" href="{{ route('admin.guru.export') }}">
                <i class="ti tabler-file-spreadsheet text-success fs-4"></i>
                <span>Export Excel (.xlsx)</span>
              </a>
            </li>
          </ul>
        </div>
        <a href="{{ route('admin.guru.cetak-qr') }}" class="btn das-btn --info">
          <i class="ti tabler-qrcode me-1"></i> Cetak QR Massal
        </a>
        <a href="{{ route('admin.guru.create') }}" class="btn das-btn --primary">
          <i class="ti tabler-plus me-1"></i> Tambah Guru
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

  {{-- TABLE CARD --}}
    <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Guru
      </h6>
      <div class="d-flex align-items-center gap-3">
        <div class="position-relative" style="max-width:300px;">
          <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size:0.85rem; pointer-events:none;"></i>
          <input type="text" id="searchInput" class="form-control border-0 text-white" placeholder="Cari nama, NIP, atau mapel..." style="background: rgba(255,255,255,0.05); height:38px; padding-left:2.2rem; font-size:0.85rem;">
        </div>
        <span class="das-chip --info">{{ count($guruUsers) }} Guru</span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="color:inherit;">
          <thead
            style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
            <tr>
              <th class="ps-4 py-3" style="width:46px;">#</th>
              <th class="py-3">Informasi Guru</th>
              <th class="py-3 d-none d-md-table-cell">NIP</th>
              <th class="py-3">Mata Pelajaran</th>
              <th class="py-3 text-center">Status</th>
              <th class="py-3 d-none d-lg-table-cell">Role</th>
              <th class="py-3 d-none d-xl-table-cell">Email Login</th>
              <th class="py-3 pe-4 text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($guruUsers as $item)
              @php
                $profile = $item->guru;
                $displayName = $profile->nama_lengkap ?? $item->name;
                $displayJabatan = $profile->jabatan ?? 'Guru';
                $displayNip = $profile->nip ?? '-';
                $displayMapel = $profile ? ($profile->mata_pelajaran ?: 'Belum diisi') : 'Belum diisi';
                $displayStatus = $profile->status ?? 'belum lengkap';
                $statusClass = $profile ? ($profile->status === 'aktif' ? 'success' : 'danger') : 'secondary';
              @endphp
              <tr class="guru-row-hover">
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
                  @if($profile && $profile->mata_pelajaran)
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
                    {{ implode(', ', array_map(fn($role) => str_replace('_', ' ', ucfirst($role)), $userRoles)) }}
                  @else
                    -
                  @endif
                </td>
                <td class="d-none d-xl-table-cell small text-white-50">
                  {{ $item->email ?? '-' }}
                </td>
                <td class="pe-4 text-end">
                  <div class="d-flex justify-content-end gap-1">
                    @if($profile)
                      <a href="{{ route('admin.impersonate.login-as', $item->id) }}" class="action-btn text-success"
                        title="Login Sebagai Guru" data-bs-toggle="tooltip">
                        <i class="ti tabler-login fs-5"></i>
                      </a>
                      <a href="{{ route('admin.guru.generate-qr', $profile->id) }}" class="action-btn text-info"
                        title="Unduh QR" data-bs-toggle="tooltip">
                        <i class="ti tabler-qrcode fs-5"></i>
                      </a>
                      <a href="{{ route('admin.guru.edit', $profile->id) }}" class="action-btn text-warning"
                        title="Ubah" data-bs-toggle="tooltip">
                        <i class="ti tabler-pencil fs-5"></i>
                      </a>
                      <form action="{{ route('admin.guru.destroy', $profile->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Yakin ingin menghapus guru ini?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn text-danger" title="Hapus" data-bs-toggle="tooltip">
                          <i class="ti tabler-trash fs-5"></i>
                        </button>
                      </form>
                    @else
                      <a href="{{ route('admin.impersonate.login-as', $item->id) }}" class="action-btn text-success me-1"
                        title="Login Sebagai User" data-bs-toggle="tooltip">
                        <i class="ti tabler-login fs-5"></i>
                      </a>
                      <a href="{{ route('admin.guru.create', ['user_id' => $item->id]) }}" class="action-btn text-success"
                        title="Lengkapi Profil Guru" data-bs-toggle="tooltip">
                        <i class="ti tabler-user-plus fs-5"></i>
                      </a>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-5">
                  <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                    <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
                    <span class="small">Belum ada data guru.</span>
                    <a href="{{ route('admin.guru.create') }}" class="btn btn-sm btn-label-info mt-1">
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

  <!-- Modal Import -->
  <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title"><i class="ti tabler-file-import me-2 text-info"></i>Import Data Guru</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('admin.guru.import.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="das-modal-body">
            <div class="mb-4">
              <label class="form-label text-white-50" for="import_file">Pilih File Excel / CSV</label>
              <input id="import_file" name="import_file" type="file" class="form-control bg-dark border-secondary text-white" accept=".xlsx,.xls,.csv" required>
              <div class="form-text text-white-50 small mt-2">Gunakan format file Excel (.xlsx) atau CSV yang sesuai.</div>
            </div>
            
            <div class="alert alert-info border-0 shadow-sm" style="background: rgba(0, 207, 232, 0.1); border-radius: 8px;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="mb-0 fw-bold text-info small"><i class="ti tabler-info-circle me-1"></i>Format Kolom:</p>
                <a href="{{ route('admin.guru.download-sample') }}" class="btn btn-sm btn-label-info py-0 px-2" style="font-size: 0.65rem;">
                   <i class="ti tabler-download me-1"></i> Download Sampel
                </a>
              </div>
              <div class="d-flex flex-wrap gap-2">
                @foreach (['nip', 'nama_lengkap', 'jenis_kelamin', 'mata_pelajaran', 'jabatan', 'no_hp', 'status'] as $col)
                  <span class="badge bg-label-info" style="font-size: 0.65rem;">{{ $col }}</span>
                @endforeach
              </div>
            </div>
          </div>
          <div class="px-4 pb-4 pt-2 d-flex gap-2">
            <button type="button" class="btn btn-label-secondary w-100" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary w-100">
              <i class="ti tabler-upload me-1"></i> Mulai Import
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Simple Search Filter
      const searchInput = document.getElementById('searchInput');
      const tableRows = document.querySelectorAll('.guru-row-hover');

      if (searchInput) {
        searchInput.addEventListener('input', function() {
          const query = this.value.toLowerCase();
          tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
          });
        });
      }
    });
  </script>
@endsection
