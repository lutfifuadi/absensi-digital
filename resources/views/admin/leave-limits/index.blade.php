@extends('layouts.layoutMaster')

@section('title', 'Batasan Perizinan')

@php
  $configData = Helper::appClasses();
@endphp

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('page-style')
<style>
  .limit-row-hover {
    transition: background 0.15s ease;
  }
  .limit-row-hover:hover {
    background: rgba(255, 255, 255, 0.04) !important;
  }
  .limit-action-btn {
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
  .limit-action-btn:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.1);
  }
  .badge-leave-type {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.25em 0.65em;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }
  .badge-leave-type--sick {
    background: rgba(0, 207, 232, 0.15);
    color: #00cfe8;
  }
  .badge-leave-type--permission {
    background: rgba(255, 159, 67, 0.15);
    color: #ff9f43;
  }
  .badge-leave-type--all {
    background: rgba(115, 103, 240, 0.15);
    color: #a5a2f7;
  }
  .badge-action {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2em 0.6em;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  .badge-action--warning {
    background: rgba(255, 159, 67, 0.15);
    color: #ff9f43;
  }
  .badge-action--block {
    background: rgba(234, 84, 85, 0.15);
    color: #ea5455;
  }
  .limit-period {
    font-size: 0.75rem;
    color: var(--das-text-main, #94a3b8);
  }
  .target-chip {
    display: inline-block;
    font-size: 0.6rem;
    font-weight: 600;
    padding: 0.15em 0.5em;
    border-radius: 3px;
    background: rgba(255, 255, 255, 0.06);
    color: var(--das-text-main, #94a3b8);
    margin: 1px 2px;
    text-transform: lowercase;
  }
  .target-chip::first-letter {
    text-transform: uppercase;
  }
</style>
@endsection

@section('content')
{{-- ═══════════════════════════════════════════════════════
     HERO HEADER
═══════════════════════════════════════════════════════ --}}
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder" style="width:64px;height:64px;border-radius:5px;display:flex;align-items:center;justify-content:center;background:rgba(115,103,240,0.15);border:2px solid var(--das-hero-logo-border);">
          <i class="ti tabler-clipboard-list" style="font-size:1.6rem;color:#a5a2f7;"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>

      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Pengaturan Kuota
        </div>
        <h4 class="das-hero__title text-gradient-gold">Batasan Perizinan</h4>
        <p class="das-hero__subtitle">Kelola aturan batas maksimal hari izin & sakit berdasarkan role, kelas, dan periode.</p>
      </div>
    </div>

    <div class="das-hero__actions d-flex gap-2">
      <a href="{{ route('admin.leave-limits.create') }}" class="das-btn das-btn--info">
        <i class="ti tabler-plus me-1"></i> Tambah Aturan
      </a>
    </div>
  </div>
</div>

{{-- ── Flash Messages ──────────────────────────────── --}}
@foreach (['success', 'error'] as $msg)
  @if (session($msg))
    <div
      class="alert alert-{{ $msg === 'success' ? 'success' : 'danger' }} alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:5px; background: var(--das-{{ $msg === 'success' ? 'success' : 'danger' }}-soft); color: var(--das-{{ $msg === 'success' ? 'success' : 'danger' }});">
      <i class="ti {{ $msg === 'success' ? 'tabler-circle-check' : 'tabler-alert-circle' }} fs-5"></i>
      <span>{{ session($msg) }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif
@endforeach

{{-- ═══════════════════════════════════════════════════════
     FILTERS
═══════════════════════════════════════════════════════ --}}
<div class="das-panel mb-4">
  <div class="das-panel__body py-3">
    <form method="GET" class="row gy-2 gx-2 align-items-end">
      <div class="col-6 col-md-3">
        <label class="form-label text-white-50 small mb-1 fw-bold">CARI</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama aturan..." value="{{ request('search') }}" style="background:rgba(15,23,42,0.4);color:white;border:1px solid rgba(255,255,255,0.1);">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label text-white-50 small mb-1 fw-bold">TIPE IZIN</label>
        <select name="leave_type" class="form-select form-select-sm" onchange="this.form.submit()" style="background:rgba(15,23,42,0.4);color:white;border:1px solid rgba(255,255,255,0.1);">
          <option value="">Semua</option>
          <option value="sick" @selected(request('leave_type') === 'sick')>Sakit</option>
          <option value="permission" @selected(request('leave_type') === 'permission')>Izin</option>
          <option value="all" @selected(request('leave_type') === 'all')>Semua Jenis</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label text-white-50 small mb-1 fw-bold">STATUS</label>
        <select name="is_active" class="form-select form-select-sm" onchange="this.form.submit()" style="background:rgba(15,23,42,0.4);color:white;border:1px solid rgba(255,255,255,0.1);">
          <option value="">Semua</option>
          <option value="1" @selected(request('is_active') === '1')>Aktif</option>
          <option value="0" @selected(request('is_active') === '0')>Nonaktif</option>
        </select>
      </div>
      <div class="col-6 col-md-2 d-flex align-items-end">
        <button type="submit" class="das-btn das-btn--secondary w-100">
          <i class="ti tabler-filter me-1"></i> Filter
        </button>
      </div>
      <div class="col-6 col-md-2 d-flex align-items-end">
        <a href="{{ route('admin.leave-limits.index') }}" class="das-btn das-btn--secondary w-100" style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.1);">
          <i class="ti tabler-x me-1"></i> Reset
        </a>
      </div>
    </form>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     TABLE
═══════════════════════════════════════════════════════ --}}
<div class="das-panel mb-4">
  <div class="das-panel__head">
    <div class="das-panel__title">
      <span class="das-panel__icon-dot --warning"></span>
      Daftar Aturan Limit
    </div>
    <div class="das-chip --warning">{{ $leaveLimits->total() }} Record</div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="color:inherit;">
        <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
          <tr>
            <th class="ps-4 py-3">Nama Aturan</th>
            <th class="py-3">Tipe Izin</th>
            <th class="py-3 text-center">Limit (Hari)</th>
            <th class="py-3 text-center">Periode</th>
            <th class="py-3 text-center">Target</th>
            <th class="py-3 text-center">Tindakan</th>
            <th class="py-3 text-center">Status</th>
            <th class="py-3 pe-4 text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($leaveLimits as $limit)
            <tr class="limit-row-hover">
              <td class="ps-4">
                <div class="fw-medium">{{ $limit->name }}</div>
              </td>
              <td>
                @php
                  $typeClasses = [
                    'sick' => 'badge-leave-type--sick',
                    'permission' => 'badge-leave-type--permission',
                    'all' => 'badge-leave-type--all',
                  ];
                  $typeLabels = [
                    'sick' => 'Sakit',
                    'permission' => 'Izin',
                    'all' => 'Semua',
                  ];
                @endphp
                <span class="badge-leave-type {{ $typeClasses[$limit->leave_type] ?? '' }}">
                  {{ $typeLabels[$limit->leave_type] ?? $limit->leave_type }}
                </span>
              </td>
              <td class="text-center fw-bold" style="font-size:1.05rem;">
                {{ $limit->max_days }}
                <small class="d-block text-muted" style="font-size:0.6rem;font-weight:400;">hari</small>
              </td>
              <td class="text-center">
                <span class="limit-period">
                  @php
                    $periodLabels = ['monthly' => 'Bulanan', 'semester' => 'Semester', 'yearly' => 'Tahunan'];
                  @endphp
                  {{ $periodLabels[$limit->period] ?? $limit->period }}
                </span>
              </td>
              <td class="text-center" style="max-width:180px;">
                <div class="d-flex flex-wrap justify-content-center gap-0">
                  @php
                    $roleLabels = [
                      'super_admin' => 'Super Admin',
                      'admin_sekolah' => 'Admin Sekolah',
                      'operator' => 'Operator',
                      'guru' => 'Guru',
                      'wali_kelas' => 'Wali Kelas',
                      'staff_tu' => 'Staff TU',
                      'siswa' => 'Siswa',
                      'orang_tua' => 'Orang Tua',
                      'piket' => 'Piket',
                    ];
                    $targetRoles = $limit->target_roles ?? [];
                    $targetGrades = $limit->target_grades ?? [];
                  @endphp
                  @foreach($targetRoles as $role)
                    <span class="target-chip">{{ $roleLabels[$role] ?? $role }}</span>
                  @endforeach
                  @if(!empty($targetGrades))
                    <span class="target-chip" style="background:rgba(115,103,240,0.12);color:#a5a2f7;">
                      <i class="ti tabler-school" style="font-size:0.55rem;"></i> Kelas: {{ implode(', ', $targetGrades) }}
                    </span>
                  @endif
                  @if(empty($targetRoles) && empty($targetGrades))
                    <span class="text-muted small">—</span>
                  @endif
                </div>
              </td>
              <td class="text-center">
                @if($limit->action_type === 'warning')
                  <span class="badge-action badge-action--warning">
                    <i class="ti tabler-alert-triangle" style="font-size:0.6rem;"></i> Warning
                  </span>
                @else
                  <span class="badge-action badge-action--block">
                    <i class="ti tabler-ban" style="font-size:0.6rem;"></i> Block
                  </span>
                @endif
              </td>
              <td class="text-center">
                <div class="form-check form-switch d-flex justify-content-center" style="padding-left:0;">
                  <input type="checkbox" class="form-check-input toggle-status" data-id="{{ $limit->id }}"
                         role="switch" {{ $limit->is_active ? 'checked' : '' }}
                         style="margin-left:0;cursor:pointer;{{ $limit->is_active ? 'background-color:var(--das-success);border-color:var(--das-success);' : '' }}">
                </div>
              </td>
              <td class="pe-4 text-end">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ route('admin.leave-limits.edit', $limit) }}"
                     class="limit-action-btn text-warning" title="Edit" data-bs-toggle="tooltip">
                    <i class="ti tabler-pencil fs-5"></i>
                  </a>
                  <button type="button" class="limit-action-btn text-danger btn-delete"
                          data-id="{{ $limit->id }}" data-name="{{ $limit->name }}"
                          title="Hapus" data-bs-toggle="tooltip">
                    <i class="ti tabler-trash fs-5"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                  <i class="ti tabler-clipboard-off" style="font-size:2.5rem;"></i>
                  <span class="small">Belum ada aturan batasan perizinan.</span>
                  <a href="{{ route('admin.leave-limits.create') }}" class="das-btn das-btn--primary mt-2">
                    <i class="ti tabler-plus me-1"></i> Tambah Aturan Pertama
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if ($leaveLimits->hasPages())
    <div class="card-footer">{{ $leaveLimits->links() }}</div>
  @endif
</div>
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // ─── SweetAlert2 Delete Confirmation ─────────────────────────
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        var id = this.dataset.id;
        var name = this.dataset.name;
        var url = '{{ route('admin.leave-limits.destroy', ':id') }}'.replace(':id', id);

        Swal.fire({
          title: 'Hapus Aturan?',
          text: 'Aturan "' + name + '" akan dihapus permanen beserta semua saldo terkait.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, Hapus!',
          cancelButtonText: 'Batal',
          customClass: {
            popup: 'das-swal-popup',
            title: 'das-swal-title',
            confirmButton: 'das-btn das-btn--danger me-2',
            cancelButton: 'das-btn das-btn--secondary'
          },
          buttonsStyling: false,
          reverseButtons: true,
          focusCancel: true,
          background: '#1a1a2e',
          color: '#fff'
        }).then(function (result) {
          if (result.isConfirmed) {
            // Submit via form untuk kompatibilitas dengan RedirectResponse
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.style.display = 'none';

            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            var methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
          }
        });
      });
    });

    // ─── BUG-002: Toggle Status via AJAX ─────────────────────────
    document.querySelectorAll('.toggle-status').forEach(function (checkbox) {
      checkbox.addEventListener('change', function () {
        var id = this.dataset.id;
        var url = '{{ route('admin.leave-limits.toggle-status', ':id') }}'.replace(':id', id);
        var self = this;

        fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        })
        .then(function (res) {
          if (!res.ok) throw new Error('Gagal mengubah status');
          return res.json();
        })
        .then(function (data) {
          if (data.success) {
            // Update tampilan checkbox
            self.checked = data.is_active;
            if (data.is_active) {
              self.style.backgroundColor = 'var(--das-success)';
              self.style.borderColor = 'var(--das-success)';
            } else {
              self.style.backgroundColor = '';
              self.style.borderColor = '';
            }
            // Tampilkan toast/alert sukses
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: data.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#1a1a2e',
                color: '#fff',
              });
            }
          }
        })
        .catch(function (err) {
          // Kembalikan checkbox ke posisi semula jika gagal
          self.checked = !self.checked;
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: err.message,
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000,
              background: '#1a1a2e',
              color: '#fff',
            });
          }
        });
      });
    });
  });
</script>
@endsection
