@extends('layouts.layoutMaster')

@php
  $isEdit = isset($leaveLimit);
  $title  = $isEdit ? 'Ubah Aturan Batasan Perizinan' : 'Tambah Aturan Batasan Perizinan';
  $formAction = $isEdit
    ? route('admin.leave-limits.update', $leaveLimit)
    : route('admin.leave-limits.store');
  $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

@section('title', $title)

@section('page-style')
<style>
  .breadcrumb-premium {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 0.35rem;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
  }
  .breadcrumb-premium a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: color 0.2s;
  }
  .breadcrumb-premium a:hover {
    color: #fff;
  }
  .breadcrumb-premium .sep {
    color: rgba(255, 255, 255, 0.3);
    font-size: 0.7rem;
  }
  .breadcrumb-premium .active {
    color: #ffd700;
  }

  .form-card {
    background: var(--das-surface);
    border: 1px solid var(--das-border);
    border-radius: var(--das-radius);
    backdrop-filter: blur(6px);
    overflow: hidden;
  }
  .form-card__head {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--das-border);
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .form-card__head-icon {
    width: 36px;
    height: 36px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(115, 103, 240, 0.15);
    color: #a5a2f7;
    font-size: 1.15rem;
    flex-shrink: 0;
  }
  .form-card__head-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
  }
  .form-card__body {
    padding: 1.75rem 1.5rem;
  }

  .form-premium .form-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: rgba(255, 255, 255, 0.55);
    margin-bottom: 0.35rem;
  }
  .form-premium .form-control,
  .form-premium .form-select {
    background: rgba(15, 23, 42, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #fff;
    border-radius: 5px;
    font-size: 0.88rem;
    padding: 0.5rem 0.85rem;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .form-premium .form-control:focus,
  .form-premium .form-select:focus {
    border-color: var(--das-primary);
    box-shadow: 0 0 0 2px rgba(115, 103, 240, 0.15);
    background: rgba(15, 23, 42, 0.6);
    color: #fff;
  }
  .form-premium .form-control::placeholder {
    color: rgba(255, 255, 255, 0.25);
  }
  .form-premium .form-control.is-invalid,
  .form-premium .form-select.is-invalid {
    border-color: var(--das-danger);
    box-shadow: 0 0 0 2px rgba(234, 84, 85, 0.15);
  }
  .form-premium .invalid-feedback {
    color: var(--das-danger);
    font-size: 0.72rem;
    margin-top: 0.25rem;
  }

  /* Radio group */
  .radio-group {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .radio-group .form-check {
    padding-left: 0;
    margin-bottom: 0;
  }
  .radio-group .form-check-input {
    display: none;
  }
  .radio-group .form-check-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.4rem 1rem;
    border-radius: 5px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(15, 23, 42, 0.3);
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
  }
  .radio-group .form-check-input:checked + .form-check-label {
    border-color: var(--das-primary);
    background: rgba(115, 103, 240, 0.15);
    color: #a5a2f7;
  }
  .radio-group .form-check-label:hover {
    border-color: rgba(255, 255, 255, 0.25);
  }

  /* Checkbox grid */
  .role-check-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 6px;
  }
  .role-check-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0.35rem 0.75rem;
    border-radius: 4px;
    background: rgba(15, 23, 42, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.05);
    cursor: pointer;
    transition: all 0.2s;
  }
  .role-check-item:hover {
    background: rgba(115, 103, 240, 0.08);
    border-color: rgba(115, 103, 240, 0.2);
  }
  .role-check-item input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: var(--das-primary);
    cursor: pointer;
  }
  .role-check-item label {
    font-size: 0.78rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.75);
    cursor: pointer;
    margin: 0;
    flex: 1;
  }

  /* Toggle switch */
  .toggle-switch {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
  }
  .toggle-switch input {
    display: none;
  }
  .toggle-switch .slider {
    width: 44px;
    height: 24px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    position: relative;
    transition: background 0.3s;
    flex-shrink: 0;
  }
  .toggle-switch .slider::after {
    content: '';
    position: absolute;
    top: 3px; left: 3px;
    width: 18px; height: 18px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.3s;
  }
  .toggle-switch input:checked + .slider {
    background: var(--das-success);
  }
  .toggle-switch input:checked + .slider::after {
    transform: translateX(20px);
  }
  .toggle-switch .toggle-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
  }

  /* Grades section */
  .grades-section {
    padding: 1rem 1.25rem;
    background: rgba(15, 23, 42, 0.25);
    border-radius: 5px;
    border: 1px dashed rgba(255, 255, 255, 0.08);
    margin-top: 0.75rem;
  }
  .grades-section.is-hidden {
    display: none;
  }

  /* Tindakan icons */
  .tindakan-icon {
    font-size: 1rem;
  }
</style>
@endsection

@section('content')
{{-- ═══════════════════════════════════════════════════════
     HERO HEADER + BREADCRUMB
═══════════════════════════════════════════════════════ --}}
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder" style="width:64px;height:64px;border-radius:5px;display:flex;align-items:center;justify-content:center;background:rgba(115,103,240,0.15);border:2px solid var(--das-hero-logo-border);">
          <i class="ti {{ $isEdit ? 'tabler-pencil' : 'tabler-plus' }}" style="font-size:1.6rem;color:#a5a2f7;"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>

      <div class="das-hero__meta">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb-premium">
            <li><a href="{{ route('admin.dashboard') }}"><i class="ti tabler-home" style="font-size:0.75rem;"></i></a></li>
            <span class="sep">/</span>
            <li><a href="#">Master Data</a></li>
            <span class="sep">/</span>
            <li><a href="{{ route('admin.leave-limits.index') }}">Batasan Izin</a></li>
            <span class="sep">/</span>
            <li class="active">{{ $isEdit ? 'Ubah' : 'Tambah' }}</li>
          </ol>
        </nav>
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          {{ $isEdit ? 'Ubah Aturan' : 'Aturan Baru' }}
        </div>
        <h4 class="das-hero__title text-gradient-gold">{{ $isEdit ? 'Ubah Aturan' : 'Tambah Aturan Baru' }}</h4>
        <p class="das-hero__subtitle">{{ $isEdit ? 'Perbarui konfigurasi batasan perizinan yang sudah ada.' : 'Buat aturan baru untuk membatasi jumlah hari izin/sakit.' }}</p>
      </div>
    </div>

    <div class="das-hero__actions">
      <a href="{{ route('admin.leave-limits.index') }}" class="das-btn das-btn--secondary">
        <i class="ti tabler-arrow-left me-1"></i> Kembali
      </a>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     FORM CARD
═══════════════════════════════════════════════════════ --}}
<div class="form-card">
  <div class="form-card__head">
    <div class="form-card__head-icon">
      <i class="ti tabler-settings"></i>
    </div>
    <h5 class="form-card__head-title">{{ $isEdit ? 'Form Ubah Aturan' : 'Form Aturan Baru' }}</h5>
  </div>

  <div class="form-card__body">
    <form action="{{ $formAction }}" method="POST" class="form-premium">
      @csrf
      @if($isEdit) @method('PUT') @endif

      <div class="row g-4">
        {{-- Nama Aturan --}}
        <div class="col-md-6">
          <div class="mb-0">
            <label class="form-label" for="name">Nama Aturan <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name"
                   value="{{ old('name', $isEdit ? $leaveLimit->name : '') }}"
                   class="form-control @error('name') is-invalid @enderror"
                   placeholder="Contoh: Batas Izin Siswa Sem 1" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        {{-- Tipe Perizinan --}}
        <div class="col-md-6">
          <div class="mb-0">
            <label class="form-label" for="leave_type">Tipe Perizinan <span class="text-danger">*</span></label>
            <select name="leave_type" id="leave_type"
                    class="form-select @error('leave_type') is-invalid @enderror" required>
              <option value="sick" {{ old('leave_type', $isEdit ? $leaveLimit->leave_type : '') == 'sick' ? 'selected' : '' }}>Sakit</option>
              <option value="permission" {{ old('leave_type', $isEdit ? $leaveLimit->leave_type : '') == 'permission' ? 'selected' : '' }}>Izin</option>
              <option value="all" {{ old('leave_type', $isEdit ? $leaveLimit->leave_type : '') == 'all' ? 'selected' : '' }}>Semua Jenis</option>
            </select>
            @error('leave_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        {{-- Batas Maksimal --}}
        <div class="col-md-4">
          <div class="mb-0">
            <label class="form-label" for="max_days">Batas Maksimal (Hari) <span class="text-danger">*</span></label>
            <input type="number" name="max_days" id="max_days"
                   value="{{ old('max_days', $isEdit ? $leaveLimit->max_days : '') }}"
                   class="form-control @error('max_days') is-invalid @enderror"
                   min="1" required placeholder="Misal: 5">
            @error('max_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        {{-- Periode --}}
        <div class="col-md-4">
          <div class="mb-0">
            <label class="form-label" for="period">Periode <span class="text-danger">*</span></label>
            <select name="period" id="period"
                    class="form-select @error('period') is-invalid @enderror" required>
              <option value="monthly" {{ old('period', $isEdit ? $leaveLimit->period : '') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
              <option value="semester" {{ old('period', $isEdit ? $leaveLimit->period : '') == 'semester' ? 'selected' : '' }}>Semester</option>
              <option value="yearly" {{ old('period', $isEdit ? $leaveLimit->period : '') == 'yearly' ? 'selected' : '' }}>Tahun Ajaran</option>
            </select>
            @error('period') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        {{-- Tipe Tindakan --}}
        <div class="col-md-4">
          <div class="mb-0">
            <label class="form-label">Tipe Tindakan <span class="text-danger">*</span></label>
            <div class="radio-group mt-1">
              @php
                $actionType = old('action_type', $isEdit ? $leaveLimit->action_type : 'warning');
              @endphp
              <div class="form-check">
                <input class="form-check-input" type="radio" name="action_type" value="warning" id="actionWarning"
                       {{ $actionType === 'warning' ? 'checked' : '' }}>
                <label class="form-check-label" for="actionWarning">
                  <i class="ti tabler-alert-triangle tindakan-icon" style="color:#ff9f43;"></i> Warning
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="action_type" value="block" id="actionBlock"
                       {{ $actionType === 'block' ? 'checked' : '' }}>
                <label class="form-check-label" for="actionBlock">
                  <i class="ti tabler-ban tindakan-icon" style="color:#ea5455;"></i> Block
                </label>
              </div>
            </div>
            <small class="d-block text-muted mt-1" style="font-size:0.68rem;">
              <strong>Warning</strong>: peringatan saja &bull;
              <strong>Block</strong>: blokir pengajuan jika kuota habis
            </small>
            @error('action_type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>
        </div>

        {{-- Target Role --}}
        <div class="col-12">
          <div class="mb-0">
            <label class="form-label">Target Role <span class="text-danger">*</span></label>
            <div class="role-check-grid mt-1">
              @php
                $roleLabels = [
                  'super_admin'   => 'Super Admin',
                  'admin_sekolah' => 'Admin Sekolah',
                  'operator'      => 'Operator',
                  'guru'          => 'Guru',
                  'wali_kelas'    => 'Wali Kelas',
                  'staff_tu'      => 'Staff TU',
                  'siswa'         => 'Siswa',
                  'orang_tua'     => 'Orang Tua',
                  'piket'         => 'Piket',
                ];
                $selectedRoles = old('target_roles', $isEdit ? ($leaveLimit->target_roles ?? []) : []);
              @endphp
              @foreach($roles as $role)
                <div class="role-check-item">
                  <input type="checkbox" name="target_roles[]" value="{{ $role }}"
                         id="role_{{ $role }}"
                         {{ in_array($role, $selectedRoles) ? 'checked' : '' }}>
                  <label for="role_{{ $role }}">{{ $roleLabels[$role] ?? $role }}</label>
                </div>
              @endforeach
            </div>
            @error('target_roles') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            <small class="d-block text-muted mt-1" style="font-size:0.68rem;">
              Pilih minimal satu role yang terkena aturan ini.
            </small>
          </div>
        </div>

        {{-- Target Kelas (conditional) --}}
        <div class="col-12">
          <div class="grades-section" id="gradesSection">
            <label class="form-label" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:rgba(255,255,255,0.55);margin-bottom:0.35rem;">
              <i class="ti tabler-school me-1"></i> Target Kelas <small class="text-muted" style="font-weight:400;text-transform:none;">(khusus role Siswa)</small>
            </label>
            <div class="d-flex flex-wrap gap-2 mt-1">
              @php
                $availableGrades = ['10', '11', '12', '13'];
                $selectedGrades = old('target_grades', $isEdit ? ($leaveLimit->target_grades ?? []) : []);
              @endphp
              @foreach($availableGrades as $grade)
                <div class="form-check" style="padding-left:0;">
                  <input class="form-check-input" type="checkbox" name="target_grades[]"
                         value="{{ $grade }}" id="grade_{{ $grade }}"
                         {{ in_array($grade, $selectedGrades) ? 'checked' : '' }}
                         style="display:none;">
                  <label class="form-check-label" for="grade_{{ $grade }}"
                         style="display:inline-flex;align-items:center;gap:6px;padding:0.3rem 0.85rem;border-radius:4px;border:1px solid rgba(255,255,255,0.08);background:rgba(15,23,42,0.2);color:rgba(255,255,255,0.6);font-size:0.78rem;font-weight:600;cursor:pointer;transition:all 0.2s;">
                    Kelas {{ $grade }}
                  </label>
                </div>
              @endforeach
            </div>
            <small class="d-block text-muted mt-1" style="font-size:0.68rem;">
              Kosongkan jika berlaku untuk semua kelas / non-siswa.
            </small>
          </div>
        </div>

        {{-- Status Aktif --}}
        <div class="col-12">
          <div class="mb-0 pt-2">
            @php
              $isActive = old('is_active', $isEdit ? $leaveLimit->is_active : true);
            @endphp
            <label class="toggle-switch">
              <input type="hidden" name="is_active" value="0">
              <input type="checkbox" name="is_active" value="1"
                     {{ $isActive ? 'checked' : '' }}>
              <span class="slider"></span>
              <span class="toggle-label">Aturan Aktif</span>
            </label>
            <small class="d-block text-muted mt-1" style="font-size:0.68rem;">
              Nonaktifkan untuk meliburkan aturan ini sementara tanpa menghapus.
            </small>
          </div>
        </div>
      </div>

      {{-- Action Buttons --}}
      <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top:1px solid var(--das-border);">
        <a href="{{ route('admin.leave-limits.index') }}" class="das-btn das-btn--secondary">
          <i class="ti tabler-x me-1"></i> Batal
        </a>
        <button type="submit" class="das-btn das-btn--primary">
          <i class="ti tabler-device-floppy me-1"></i>
          {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Aturan' }}
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // ─── Toggle grades section visibility based on "siswa" checkbox ──────
    var gradesSection = document.getElementById('gradesSection');
    var siswaCheckbox = document.getElementById('role_siswa');

    function toggleGradesVisibility() {
      if (siswaCheckbox && siswaCheckbox.checked) {
        gradesSection.classList.remove('is-hidden');
      } else {
        gradesSection.classList.add('is-hidden');
      }
    }

    if (siswaCheckbox && gradesSection) {
      toggleGradesVisibility();
      siswaCheckbox.addEventListener('change', toggleGradesVisibility);
    }

    // ─── Highlight checked grade labels ─────────────────────────────────
    document.querySelectorAll('.grades-section .form-check-input').forEach(function (cb) {
      cb.addEventListener('change', function () {
        var label = this.closest('.form-check')?.querySelector('.form-check-label');
        if (label) {
          if (this.checked) {
            label.style.borderColor = 'var(--das-primary)';
            label.style.background = 'rgba(115, 103, 240, 0.12)';
            label.style.color = '#a5a2f7';
          } else {
            label.style.borderColor = 'rgba(255, 255, 255, 0.08)';
            label.style.background = 'rgba(15, 23, 42, 0.2)';
            label.style.color = 'rgba(255, 255, 255, 0.6)';
          }
        }
      });
      // Trigger initial state
      cb.dispatchEvent(new Event('change'));
    });

    // ─── Highlight checked role items ───────────────────────────────────
    document.querySelectorAll('.role-check-item input[type="checkbox"]').forEach(function (cb) {
      cb.addEventListener('change', function () {
        var item = this.closest('.role-check-item');
        if (this.checked) {
          item.style.background = 'rgba(115, 103, 240, 0.1)';
          item.style.borderColor = 'rgba(115, 103, 240, 0.3)';
        } else {
          item.style.background = 'rgba(15, 23, 42, 0.2)';
          item.style.borderColor = 'rgba(255, 255, 255, 0.05)';
        }
      });
      // Trigger initial state
      cb.dispatchEvent(new Event('change'));
    });
  });
</script>
@endsection
