@extends('layouts.layoutMaster')

@section('title', 'Dispensasi Kuota Izin: ' . $user->name)

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

  /* User Info Card */
  .user-info-card {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.25rem 1.5rem;
    background: rgba(15, 23, 42, 0.35);
    border: 1px solid var(--das-border);
    border-radius: var(--das-radius);
    margin-bottom: 1.5rem;
    backdrop-filter: blur(6px);
  }
  .user-avatar {
    width: 56px;
    height: 56px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    font-weight: 800;
    background: rgba(115, 103, 240, 0.15);
    color: #a5a2f7;
    border: 2px solid rgba(115, 103, 240, 0.3);
    flex-shrink: 0;
  }
  .user-detail-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 2px;
  }
  .user-detail-role {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .user-detail-extra {
    margin-left: auto;
    text-align: right;
  }
  .user-detail-extra small {
    display: block;
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }
  .user-detail-extra strong {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--das-success);
  }

  /* Summary cards */
  .summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.5rem;
  }
  .summary-item {
    background: rgba(15, 23, 42, 0.25);
    border: 1px solid var(--das-border);
    border-radius: var(--das-radius);
    padding: 0.85rem 1rem;
    transition: all 0.2s;
  }
  .summary-item:hover {
    background: rgba(15, 23, 42, 0.4);
    border-color: rgba(255, 255, 255, 0.12);
  }
  .summary-item__name {
    font-size: 0.72rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.5);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
  }
  .summary-item__stats {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.82rem;
  }
  .summary-item__remaining {
    font-size: 1.25rem;
    font-weight: 800;
    color: #fff;
  }
  .summary-item__remaining.--low {
    color: var(--das-warning);
  }
  .summary-item__remaining.--empty {
    color: var(--das-danger);
  }
  .summary-item__detail {
    color: rgba(255, 255, 255, 0.45);
    font-size: 0.7rem;
  }
  .summary-item__detail span {
    display: block;
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
        <div class="das-hero__logo-placeholder" style="width:64px;height:64px;border-radius:5px;display:flex;align-items:center;justify-content:center;background:rgba(0,207,232,0.15);border:2px solid var(--das-hero-logo-border);">
          <i class="ti tabler-gift" style="font-size:1.6rem;color:#00cfe8;"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>

      <div class="das-hero__meta">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb-premium">
            <li><a href="{{ route('admin.dashboard') }}"><i class="ti tabler-home" style="font-size:0.75rem;"></i></a></li>
            <span class="sep">/</span>
            <li><a href="{{ route('admin.leave-limits.index') }}">Batasan Izin</a></li>
            <span class="sep">/</span>
            <li class="active">Dispensasi</li>
          </ol>
        </nav>
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Kuota Ekstra
        </div>
        <h4 class="das-hero__title text-gradient-gold">Dispensasi Kuota Izin</h4>
        <p class="das-hero__subtitle">Berikan tambahan kuota hari izin/sakit kepada user tertentu.</p>
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
     USER INFO
═══════════════════════════════════════════════════════ --}}
<div class="user-info-card">
  <div class="user-avatar">
    {{ strtoupper(substr($user->name, 0, 1)) }}
  </div>
  <div>
    <h5 class="user-detail-name">{{ $user->name }}</h5>
    <span class="user-detail-role">
      <i class="ti tabler-user-check me-1" style="font-size:0.65rem;"></i>
      {{ str_replace('_', ' ', ucfirst($user->role)) }}
      @if($user->email)
        &middot; {{ $user->email }}
      @endif
    </span>
  </div>
  <div class="user-detail-extra">
    <small>Total Limit Aktif</small>
    <strong>{{ $limits->count() }}</strong>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     CURRENT LIMIT SUMMARY
═══════════════════════════════════════════════════════ --}}
@if(isset($currentBalance) && $currentBalance !== null)
  <div class="summary-grid">
    @php
      $remaining = $currentBalance;
      $remainingClass = '';
      if ($remaining <= 0) {
        $remainingClass = '--empty';
      } elseif ($remaining <= 3) {
        $remainingClass = '--low';
      }
    @endphp
    <div class="summary-item">
      <div class="summary-item__name">Sisa Kuota Saat Ini</div>
      <div class="summary-item__stats">
        <span class="summary-item__remaining {{ $remainingClass }}">{{ $remaining }}</span>
        <div class="summary-item__detail">
          <span>hari tersisa</span>
        </div>
      </div>
    </div>
  </div>
@endif

{{-- ═══════════════════════════════════════════════════════
     DISPENSATION FORM
═══════════════════════════════════════════════════════ --}}
<div class="form-card">
  <div class="form-card__head">
    <div class="form-card__head-icon">
      <i class="ti tabler-plus"></i>
    </div>
    <h5 class="form-card__head-title">Form Tambah Kuota Dispensasi</h5>
  </div>

  <div class="form-card__body">
    <form action="{{ route('admin.leave-limits.grant-dispensation', $user) }}" method="POST" class="form-premium">
      @csrf

      <div class="row g-4">
        {{-- Pilih Aturan Limit --}}
        <div class="col-md-6">
          <div class="mb-0">
            <label class="form-label" for="leave_limit_id">Pilih Aturan Limit <span class="text-danger">*</span></label>
            <select name="leave_limit_id" id="leave_limit_id"
                    class="form-select @error('leave_limit_id') is-invalid @enderror" required>
              <option value="">-- Pilih Aturan --</option>
              @foreach($limits as $limit)
                <option value="{{ $limit->id }}" {{ old('leave_limit_id') == $limit->id ? 'selected' : '' }}>
                  {{ $limit->name }} ({{ $limit->leave_type === 'sick' ? 'Sakit' : ($limit->leave_type === 'permission' ? 'Izin' : 'Semua') }} — {{ $limit->max_days }} hari/{{ $limit->period }})
                </option>
              @endforeach
            </select>
            @error('leave_limit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        {{-- Tambahan Hari --}}
        <div class="col-md-3">
          <div class="mb-0">
            <label class="form-label" for="extra_days">Tambahan Hari <span class="text-danger">*</span></label>
            <input type="number" name="extra_days" id="extra_days"
                   value="{{ old('extra_days') }}"
                   class="form-control @error('extra_days') is-invalid @enderror"
                   min="1" required placeholder="Misal: 3">
            @error('extra_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        {{-- Alasan Dispensasi --}}
        <div class="col-12">
          <div class="mb-0">
            <label class="form-label" for="reason">Alasan Dispensasi <span class="text-danger">*</span></label>
            <textarea name="reason" id="reason" rows="3"
                      class="form-control @error('reason') is-invalid @enderror"
                      required placeholder="Contoh: Siswa ini mengalami musibah bencana alam, perlu kuota tambahan.">{{ old('reason') }}</textarea>
            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      {{-- Action Buttons --}}
      <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top:1px solid var(--das-border);">
        <a href="{{ route('admin.leave-limits.index') }}" class="das-btn das-btn--secondary">
          <i class="ti tabler-x me-1"></i> Batal
        </a>
        <button type="submit" class="das-btn das-btn--success">
          <i class="ti tabler-gift me-1"></i> Berikan Dispensasi
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
