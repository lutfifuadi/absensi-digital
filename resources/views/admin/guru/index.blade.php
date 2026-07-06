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

    /* Custom Soft & Outline Buttons for Dark Mode */
    .das-btn--purple-soft {
      background: rgba(115, 103, 240, 0.12) !important;
      border: 1px solid rgba(115, 103, 240, 0.25) !important;
      color: #a5a2f7 !important;
    }
    .das-btn--purple-soft:hover {
      background: rgba(115, 103, 240, 0.25) !important;
      color: #ffffff !important;
      box-shadow: 0 0 12px rgba(115, 103, 240, 0.2);
    }

    .das-btn--info-soft {
      background: rgba(0, 207, 232, 0.12) !important;
      border: 1px solid rgba(0, 207, 232, 0.25) !important;
      color: #00cfe8 !important;
    }
    .das-btn--info-soft:hover {
      background: rgba(0, 207, 232, 0.25) !important;
      color: #ffffff !important;
      box-shadow: 0 0 12px rgba(0, 207, 232, 0.2);
    }

    .das-btn--success-soft {
      background: rgba(40, 199, 111, 0.12) !important;
      border: 1px solid rgba(40, 199, 111, 0.25) !important;
      color: #28c76f !important;
    }
    .das-btn--success-soft:hover {
      background: rgba(40, 199, 111, 0.25) !important;
      color: #ffffff !important;
      box-shadow: 0 0 12px rgba(40, 199, 111, 0.2);
    }

    .das-btn--warning-soft {
      background: rgba(255, 159, 67, 0.12) !important;
      border: 1px solid rgba(255, 159, 67, 0.25) !important;
      color: #ff9f43 !important;
    }
    .das-btn--warning-soft:hover {
      background: rgba(255, 159, 67, 0.25) !important;
      color: #ffffff !important;
      box-shadow: 0 0 12px rgba(255, 159, 67, 0.2);
    }

    .das-btn--danger-outline {
      background: rgba(234, 84, 85, 0.05) !important;
      border: 1px solid rgba(234, 84, 85, 0.4) !important;
      color: #ea5455 !important;
    }
    .das-btn--danger-outline:hover {
      background: #ea5455 !important;
      color: #ffffff !important;
      box-shadow: 0 0 15px rgba(234, 84, 85, 0.4);
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
        <a href="{{ route('admin.pengaturan.google-sheets-guru.index') }}" class="btn das-btn das-btn--purple-soft">
          <i class="ti tabler-file-spreadsheet me-1"></i> GSheets Sync
        </a>
        <button type="button" class="btn das-btn das-btn--info-soft" data-bs-toggle="modal" data-bs-target="#importModal">
          <i class="ti tabler-file-import me-1"></i> Import
        </button>
        <div class="btn-group">
          <button type="button" class="btn das-btn das-btn--success-soft dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
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
        <a href="{{ route('admin.guru.cetak-qr') }}" class="btn das-btn das-btn--warning-soft">
          <i class="ti tabler-qrcode me-1"></i> Cetak QR
        </a>
        <button type="button" class="btn das-btn das-btn--danger-outline" data-bs-toggle="modal" data-bs-target="#deleteAllGuruModal">
          <i class="ti tabler-trash me-1"></i> Hapus Semua
        </button>
        <a href="{{ route('admin.guru.create') }}" class="btn das-btn --primary">
          <i class="ti tabler-plus me-1"></i> Tambah
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
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h2 class="das-panel__title mb-0 d-flex align-items-center gap-2" style="font-size:1rem;">
        <i class="ti tabler-list text-info"></i> Daftar Guru
      </h2>
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
                <td class="text-nowrap">
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
                <td class="pe-4 text-end">
                  <div class="d-flex justify-content-end gap-1">
                    @if($profile)
                      <a href="{{ route('admin.impersonate.login-as', $item->id) }}" class="action-btn text-success"
                        title="Login Sebagai Guru" data-bs-toggle="tooltip"
                        aria-label="Login sebagai {{ $displayName }}">
                        <i class="ti tabler-login fs-5"></i>
                      </a>
                      <a href="{{ route('admin.guru.generate-qr', $profile->id) }}" class="action-btn text-info"
                        title="Unduh QR" data-bs-toggle="tooltip"
                        aria-label="Unduh QR {{ $displayName }}">
                        <i class="ti tabler-qrcode fs-5"></i>
                      </a>
                      <a href="{{ route('admin.guru.edit', $profile->id) }}" class="action-btn text-warning"
                        title="Ubah" data-bs-toggle="tooltip"
                        aria-label="Ubah {{ $displayName }}">
                        <i class="ti tabler-pencil fs-5"></i>
                      </a>
                      <button type="button" class="action-btn text-danger" title="Hapus" data-bs-toggle="tooltip"
                        aria-label="Hapus {{ $displayName }}"
                        onclick="openHapusModal('{{ $profile->id }}', '{{ addslashes($profile->nama_lengkap) }}', '{{ $profile->nip }}')">
                        <i class="ti tabler-trash fs-5"></i>
                      </button>
                    @else
                      <a href="{{ route('admin.impersonate.login-as', $item->id) }}" class="action-btn text-success me-1"
                        title="Login Sebagai User" data-bs-toggle="tooltip"
                        aria-label="Login sebagai {{ $item->name }}">
                        <i class="ti tabler-login fs-5"></i>
                      </a>
                      <a href="{{ route('admin.guru.create', ['user_id' => $item->id]) }}" class="action-btn text-success"
                        title="Lengkapi Profil Guru" data-bs-toggle="tooltip"
                        aria-label="Lengkapi Profil Guru {{ $item->name }}">
                        <i class="ti tabler-user-plus fs-5"></i>
                      </a>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5">
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

  <!-- Modal Hapus Guru -->
  <div class="modal fade" id="modalHapusGuru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <div style="width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(234,84,85,0.2);border:1px solid rgba(234,84,85,0.35);">
              <i class="ti tabler-alert-triangle text-danger fs-5"></i>
            </div>
            <div>
              <h5 class="das-modal-title text-white fw-bold">Konfirmasi Hapus</h5>
              <small class="text-white-50">Tindakan ini tidak dapat dibatalkan.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="das-modal-body text-center py-4">
          <p class="mb-1">Yakin ingin menghapus guru:</p>
          <p class="fw-bold text-warning fs-5 mb-1" id="hapusNamaGuru">—</p>
          <p class="text-white-50 small" id="hapusNipGuru">—</p>
          <p class="text-white-50 small mt-3 mb-0">
            <i class="ti tabler-info-circle me-1"></i>
            Akun user dan semua data terkait akan ikut terhapus.
          </p>
        </div>
        <div class="px-4 pb-4 pt-2 d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-label-secondary px-4" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Batal
          </button>
          <form id="formHapusGuru" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger fw-semibold px-4 shadow-sm">
              <i class="ti tabler-trash me-1"></i> Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Hapus Semua Guru -->
  <div class="modal fade" id="deleteAllGuruModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <div style="width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(234,84,85,0.2);border:1px solid rgba(234,84,85,0.35);">
              <i class="ti tabler-alert-triangle text-danger fs-5"></i>
            </div>
            <div>
              <h5 class="das-modal-title text-white fw-bold">Hapus Semua Guru</h5>
              <small class="text-white-50">Tindakan destruktif & tidak dapat dibatalkan.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="deleteAllGuruCancelBtn"></button>
        </div>
        <form id="deleteAllGuruForm" action="{{ route('admin.guru.destroy-all') }}" method="POST">
          @csrf
          @method('DELETE')
          <div class="das-modal-body">
            <p class="mb-3 text-white-50">Tindakan ini akan menghapus <strong>seluruh data guru</strong> dari database, termasuk akun user terkait. Silakan lakukan konfirmasi berikut untuk melanjutkan:</p>
            
            <div class="mb-3">
              <label class="form-label text-white-50 mb-2" for="confirm_text">Silakan ketik kata kunci konfirmasi di bawah ini:</label>
              <div class="mb-3">
                <strong class="text-danger bg-label-danger px-2 py-1 rounded animate-pulse" style="font-size: 1.1rem; letter-spacing: 1px; display: inline-block;">HAPUS SEMUA GURU</strong>
              </div>
              <input type="text" id="confirm_text" name="konfirmasi" class="form-control bg-dark border-secondary text-white" placeholder='Ketik "HAPUS SEMUA GURU"' autocomplete="off">
              <div class="form-text text-white-50 small mt-2">Ketik secara tepat (Case-sensitive) untuk mengaktifkan tombol hapus.</div>
            </div>

            <div id="deleteAllGuruProgress" class="d-none mt-3">
              <div class="progress" style="height:8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" style="width:100%"></div>
              </div>
              <small class="text-white-50 mt-2 d-block text-center">Memproses penghapusan data...</small>
            </div>
          </div>
          <div class="px-4 pb-4 pt-2 d-flex gap-2">
            <button type="button" class="btn btn-label-secondary w-100" data-bs-dismiss="modal" id="deleteAllGuruCloseBtn">
              <i class="ti tabler-x me-1"></i> Batal
            </button>
            <button type="submit" id="deleteAllGuruSubmitBtn" class="btn btn-secondary w-100" disabled>
              <i class="ti tabler-trash me-1"></i> Hapus Sekarang
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

    // Modal hapus guru
    function openHapusModal(id, nama, nip) {
      document.getElementById('hapusNamaGuru').textContent = nama;
      document.getElementById('hapusNipGuru').textContent = 'NIP: ' + (nip || '-');
      document.getElementById('formHapusGuru').action = '{{ url("admin/guru") }}/' + id;
      new bootstrap.Modal(document.getElementById('modalHapusGuru')).show();
    }

    // Modal Hapus Semua Guru
    document.addEventListener('DOMContentLoaded', function() {
      const deleteAllForm = document.getElementById('deleteAllGuruForm');
      const confirmText = document.getElementById('confirm_text');
      const submitBtn = document.getElementById('deleteAllGuruSubmitBtn');
      const cancelBtn = document.getElementById('deleteAllGuruCancelBtn');
      const closeBtn = document.getElementById('deleteAllGuruCloseBtn');
      const progressArea = document.getElementById('deleteAllGuruProgress');

      function validateInputs() {
        const isTextValid = confirmText.value === 'HAPUS SEMUA GURU';

        if (isTextValid) {
          submitBtn.removeAttribute('disabled');
          submitBtn.classList.remove('btn-secondary');
          submitBtn.classList.add('btn-danger');
        } else {
          submitBtn.setAttribute('disabled', 'true');
          submitBtn.classList.remove('btn-danger');
          submitBtn.classList.add('btn-secondary');
        }
      }

      if (confirmText) {
        confirmText.addEventListener('input', validateInputs);
      }

      if (deleteAllForm) {
        deleteAllForm.addEventListener('submit', function(e) {
          // Disable buttons & show loading state
          submitBtn.setAttribute('disabled', 'true');
          cancelBtn.setAttribute('disabled', 'true');
          closeBtn.setAttribute('disabled', 'true');
          
          // Ganti text & icon
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses...';
          
          // Tampilkan progress bar
          if (progressArea) {
            progressArea.classList.remove('d-none');
          }
        });
      }

      // Reset modal on hide
      const deleteAllModalEl = document.getElementById('deleteAllGuruModal');
      if (deleteAllModalEl) {
        deleteAllModalEl.addEventListener('hidden.bs.modal', function() {
          deleteAllForm.reset();
          validateInputs();
          if (progressArea) {
            progressArea.classList.add('d-none');
          }
          submitBtn.innerHTML = '<i class="ti tabler-trash me-1"></i> Hapus Sekarang';
          submitBtn.removeAttribute('disabled');
          cancelBtn.removeAttribute('disabled');
          closeBtn.removeAttribute('disabled');
          validateInputs();
        });
      }
    });
  </script>
@endsection
