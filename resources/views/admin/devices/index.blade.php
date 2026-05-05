@extends('layouts/layoutMaster')

@section('title', 'Manajemen Keamanan Perangkat — ' . ($pengaturanArr['nama_sekolah'] ?? 'Sistem Absensi'))

@section('content')

{{-- PAGE HEADER --}}
<div class="dev-page-header mb-4">
  <div class="dev-page-header__bg"></div>
  <div class="dev-page-header__glass"></div>
  <div class="dev-page-header__inner">
    <div class="dev-page-header__left">
      <div class="dev-page-header__icon-wrap">
        <i class="ti tabler-shield-lock"></i>
      </div>
      <div>
        <div class="dev-page-header__badge">
          <span class="pulse-dot"></span>
          Sistem Keamanan
        </div>
        <h4 class="dev-page-header__title">Manajemen Perangkat</h4>
        <p class="dev-page-header__sub">Kelola perangkat yang terdeteksi mengakses sistem absensi</p>
      </div>
    </div>
    <div class="dev-page-header__stats">
      <div class="dev-stat-mini">
        <div class="dev-stat-mini__val">{{ $devices->count() }}</div>
        <div class="dev-stat-mini__label">Total Perangkat</div>
      </div>
      <div class="dev-stat-mini dev-stat-mini--success">
        <div class="dev-stat-mini__val">{{ $devices->where('is_authorized', true)->count() }}</div>
        <div class="dev-stat-mini__label">Diizinkan</div>
      </div>
      <div class="dev-stat-mini dev-stat-mini--warning">
        <div class="dev-stat-mini__val">{{ $devices->where('is_authorized', false)->count() }}</div>
        <div class="dev-stat-mini__label">Pending</div>
      </div>
    </div>
  </div>
</div>

{{-- BREADCRUMB --}}
<div class="dev-breadcrumb mb-4">
  <span class="dev-breadcrumb__item text-muted">Sistem</span>
  <span class="dev-breadcrumb__sep"><i class="ti tabler-chevron-right"></i></span>
  <span class="dev-breadcrumb__item dev-breadcrumb__item--active">Keamanan Perangkat</span>
</div>

{{-- MAIN PANEL --}}
<div class="dev-panel">
  <div class="dev-panel__head">
    <div class="dev-panel__title">
      <span class="dev-panel__icon-dot dev-panel__icon-dot--primary"></span>
      Daftar Perangkat Terdeteksi
    </div>
    <div class="d-flex align-items-center gap-2">
      <div class="dev-search-wrap">
        <i class="ti tabler-search dev-search-wrap__icon"></i>
        <input type="text" id="deviceSearch" placeholder="Cari perangkat..." class="dev-search-input">
      </div>
      <span class="das-chip --info">Total: {{ $devices->count() }}</span>
    </div>
  </div>

  {{-- TABLE --}}
  <div class="table-responsive">
    <table class="dev-table" id="deviceTable">
      <thead>
        <tr>
          <th width="130">STATUS</th>
          <th>NAMA PERANGKAT</th>
          <th width="240">ID PERANGKAT (UUID)</th>
          <th width="130">IP TERAKHIR</th>
          <th width="150">TERAKHIR AKTIF</th>
          <th width="130" class="text-center">AKSI</th>
        </tr>
      </thead>
      <tbody>
        @forelse($devices as $device)
        <tr class="dev-table__row" data-name="{{ strtolower($device->device_name) }}" data-uuid="{{ strtolower($device->device_uuid) }}">

          {{-- STATUS --}}
          <td>
            @if($device->is_authorized)
              <span class="dev-status-badge dev-status-badge--authorized">
                <i class="ti tabler-shield-check"></i> Diizinkan
              </span>
            @else
              <span class="dev-status-badge dev-status-badge--pending">
                <i class="ti tabler-clock-pause"></i> Pending
              </span>
            @endif
          </td>

          {{-- NAMA PERANGKAT --}}
          <td>
            <div class="dev-device-info">
              <div class="dev-device-info__icon dev-device-info__icon--{{ $device->is_authorized ? 'authorized' : 'pending' }}">
                <i class="ti tabler-device-tablet"></i>
              </div>
              <div class="dev-device-info__body">
                <div class="dev-device-info__name">{{ $device->device_name ?: 'Perangkat Baru' }}</div>
                <div class="dev-device-info__agent">{{ \Illuminate\Support\Str::limit($device->user_agent, 45) }}</div>
              </div>
            </div>
          </td>

          {{-- UUID --}}
          <td>
            <div class="dev-uuid-wrap">
              <code class="dev-uuid">{{ $device->device_uuid }}</code>
            </div>
          </td>

          {{-- IP --}}
          <td>
            <div class="dev-ip-badge">
              <i class="ti tabler-network"></i>
              {{ $device->ip_address }}
            </div>
          </td>

          {{-- LAST ACTIVE --}}
          <td>
            <div class="dev-time-wrap">
              @if($device->last_active_at)
                <i class="ti tabler-clock"></i>
                <span>{{ $device->last_active_at->diffForHumans() }}</span>
              @else
                <span class="text-muted">—</span>
              @endif
            </div>
          </td>

          {{-- AKSI --}}
          <td>
            <div class="dev-action-group">
              {{-- Tombol Izinkan / Edit --}}
              <button type="button"
                class="dev-btn {{ $device->is_authorized ? 'dev-btn--info' : 'dev-btn--success' }} btn-authorize"
                data-device-name="{{ $device->device_name ?: 'Perangkat Baru' }}"
                data-device-uuid-short="{{ \Illuminate\Support\Str::limit($device->device_uuid, 32) }}..."
                data-is-authorized="{{ $device->is_authorized ? '1' : '0' }}"
                data-action="{{ route('admin.devices.authorize', $device->id) }}"
                data-current-name="{{ $device->device_name }}"
                title="{{ $device->is_authorized ? 'Edit Nama Perangkat' : 'Izinkan Perangkat' }}">
                <i class="ti {{ $device->is_authorized ? 'tabler-edit' : 'tabler-shield-check' }}"></i>
                <span>{{ $device->is_authorized ? 'Edit' : 'Izinkan' }}</span>
              </button>


              {{-- Tombol Hapus: trigger modal konfirmasi --}}
              <button type="button"
                class="dev-btn dev-btn--danger"
                title="Hapus Perangkat"
                data-bs-toggle="modal"
                data-bs-target="#deleteConfirmModal"
                data-device-name="{{ $device->device_name ?: 'Perangkat Baru' }}"
                data-device-uuid="{{ \Illuminate\Support\Str::limit($device->device_uuid, 20) }}..."
                data-action="{{ route('admin.devices.destroy', $device->id) }}">
                <i class="ti tabler-trash"></i>
                <span>Hapus</span>
              </button>
            </div>
          </td>
        </tr>



        @empty
        <tr>
          <td colspan="6">
            <div class="dev-empty-state">
              <div class="dev-empty-state__icon">
                <i class="ti tabler-device-tablet-off"></i>
              </div>
              <div class="dev-empty-state__title">Belum Ada Perangkat</div>
              <div class="dev-empty-state__sub">Perangkat yang mengakses sistem akan muncul di sini secara otomatis.</div>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL IZINKAN / EDIT (Generik — satu untuk semua device)  --}}
{{-- ============================================================ --}}
<div class="modal fade" id="authorizeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content das-modal">
      <form id="authorizeForm" action="" method="POST">
        @csrf
        <div class="das-modal__head">
          <h5 class="das-modal__title">
            <i class="ti tabler-shield-check me-2"></i>
            <span id="authorizeModalTitle">Izinkan Perangkat</span>
          </h5>
        </div>
        <div class="das-modal__body">
          {{-- Konfirmasi Banner --}}
          <div class="dev-confirm-banner" id="authorizeBanner">
            <div class="dev-confirm-banner__icon">
              <i class="ti" id="authorizeBannerIcon"></i>
            </div>
            <div class="dev-confirm-banner__text">
              <div class="dev-confirm-banner__title" id="authorizeBannerTitle"></div>
              <div class="dev-confirm-banner__sub" id="authorizeBannerSub"></div>
            </div>
          </div>
          <div class="dev-modal-device-preview">
            <div class="dev-modal-device-preview__icon">
              <i class="ti tabler-device-tablet"></i>
            </div>
            <div>
              <div class="dev-modal-device-preview__name" id="authorizeDeviceName">—</div>
              <code class="dev-modal-device-preview__uuid" id="authorizeDeviceUuid">—</code>
            </div>
          </div>
          <div style="padding: 1.25rem 1.25rem 0;">
            <div class="dev-form-group">
              <label class="dev-form-label">Nama Perangkat (Label)</label>
              <input type="text" name="device_name" id="authorizeDeviceNameInput"
                     class="dev-form-input" placeholder="Misal: Tablet Guru Piket A" required>
            </div>
            <div class="dev-form-hint">
              <i class="ti tabler-info-circle"></i>
              Berikan nama yang mudah dikenali agar mudah diidentifikasi di kemudian hari.
            </div>
          </div>
        </div>
        <div class="das-modal__foot d-flex gap-2 justify-content-end">
          <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal">
            <i class="ti tabler-x"></i> Tidak, Batal
          </button>
          <button type="submit" class="das-btn das-btn--primary" id="authorizeSubmitBtn">
            <i class="ti tabler-check"></i> <span>Ya, Izinkan</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
{{-- /MODAL IZINKAN --}}

{{-- ============================================================ --}}
{{-- MODAL KONFIRMASI HAPUS (Generik — satu untuk semua device) --}}
{{-- ============================================================ --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true" aria-labelledby="deleteConfirmModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-dialog--confirm">
    <div class="modal-content das-modal das-modal--danger">

      {{-- Form Hapus Generik --}}
      <form id="genericDeleteForm" action="" method="POST">
        @csrf
        @method('DELETE')

        {{-- Header --}}
        <div class="das-modal__head das-modal__head--danger">
          <h5 class="das-modal__title" id="deleteConfirmModalLabel">
            <i class="ti tabler-alert-triangle me-2"></i>Konfirmasi Hapus
          </h5>
        </div>

        {{-- Body --}}
        <div class="das-modal__body">
          {{-- Icon Danger --}}
          <div class="dev-confirm-danger-icon">
            <div class="dev-confirm-danger-icon__ring"></div>
            <i class="ti tabler-trash dev-confirm-danger-icon__symbol"></i>
          </div>

          {{-- Pesan --}}
          <div class="dev-confirm-message">
            <p class="dev-confirm-message__main">Apakah Anda yakin ingin menghapus perangkat ini?</p>
            <div class="dev-confirm-device-card" id="deleteModalDeviceCard">
              <i class="ti tabler-device-tablet dev-confirm-device-card__icon"></i>
              <div>
                <div class="dev-confirm-device-card__name" id="deleteModalDeviceName">—</div>
                <div class="dev-confirm-device-card__uuid" id="deleteModalDeviceUuid">—</div>
              </div>
            </div>
            <p class="dev-confirm-message__warning">
              <i class="ti tabler-info-circle"></i>
              Perangkat yang dihapus tidak dapat dikembalikan. Perangkat perlu mendaftar ulang untuk mengakses sistem.
            </p>
          </div>
        </div>

        {{-- Footer --}}
        <div class="das-modal__foot d-flex gap-2 justify-content-end">
          <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal">
            <i class="ti tabler-x"></i> Tidak, Batal
          </button>
          <button type="submit" class="das-btn das-btn--danger-solid" id="deleteConfirmBtn">
            <i class="ti tabler-trash"></i> Ya, Hapus
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
{{-- /MODAL HAPUS --}}

@endsection


@section('page-style')
<style>
:root {
  --das-primary:      #7367f0;
  --das-primary-soft: rgba(115,103,240,0.12);
  --das-success:      #28c76f;
  --das-success-soft: rgba(40,199,111,0.12);
  --das-info:         #00cfe8;
  --das-info-soft:    rgba(0,207,232,0.12);
  --das-warning:      #ff9f43;
  --das-warning-soft: rgba(255,159,67,0.12);
  --das-danger:       #ea5455;
  --das-danger-soft:  rgba(234,84,85,0.12);
  --das-surface:      rgba(15, 23, 42, 0.4);
  --das-surface-hover:rgba(30, 41, 59, 0.6);
  --das-border:       rgba(255,255,255,0.06);
  --das-border-hover: rgba(255,255,255,0.12);
  --das-radius:       5px;
  --das-radius-sm:    5px;
}

/* ── PAGE HEADER ── */
.dev-page-header {
  position: relative;
  border-radius: var(--das-radius);
  overflow: hidden;
}
.dev-page-header__bg {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%);
  z-index: 0;
}
.dev-page-header__glass {
  position: absolute; inset: 0;
  background:
    radial-gradient(circle at top right, rgba(115,103,240,0.15), transparent 40%),
    linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
  background-size: auto, 40px 40px, 40px 40px;
  z-index: 1;
}
.dev-page-header__inner {
  position: relative; z-index: 2;
  display: flex; align-items: center; justify-content: space-between;
  padding: 2rem 2.5rem;
  gap: 1.5rem; flex-wrap: wrap;
}
.dev-page-header__left {
  display: flex; align-items: center; gap: 1.25rem;
}
.dev-page-header__icon-wrap {
  width: 62px; height: 62px; border-radius: 5px;
  background: rgba(115,103,240,0.25);
  border: 1px solid rgba(115,103,240,0.35);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.75rem; color: #a5a2f7;
  flex-shrink: 0;
}
.dev-page-header__badge {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 0.62rem; font-weight: 700;
  letter-spacing: 1px; text-transform: uppercase;
  background: rgba(115,103,240,0.2);
  border: 1px solid rgba(115,103,240,0.3);
  color: #a5a2f7;
  padding: 3px 10px; border-radius: 20px; margin-bottom: 6px;
}
.dev-page-header__title {
  font-size: 1.4rem; font-weight: 800;
  background: linear-gradient(to right, #fff, #ffd700);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  margin: 0 0 4px;
}
.dev-page-header__sub {
  margin: 0; font-size: 0.82rem; color: rgba(255,255,255,0.55);
}
.dev-page-header__stats {
  display: flex; gap: 1rem; flex-wrap: wrap;
}

/* Mini stats in header */
.dev-stat-mini {
  background: rgba(0,0,0,0.25);
  border: 1px solid rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
  border-radius: var(--das-radius-sm);
  padding: 0.75rem 1.25rem;
  text-align: center; min-width: 90px;
}
.dev-stat-mini--success { border-color: rgba(40,199,111,0.3); background: rgba(40,199,111,0.1); }
.dev-stat-mini--warning { border-color: rgba(255,159,67,0.3); background: rgba(255,159,67,0.1); }
.dev-stat-mini__val {
  font-size: 1.5rem; font-weight: 800; color: #fff; line-height: 1;
}
.dev-stat-mini--success .dev-stat-mini__val { color: var(--das-success); }
.dev-stat-mini--warning .dev-stat-mini__val { color: var(--das-warning); }
.dev-stat-mini__label {
  font-size: 0.6rem; font-weight: 700; letter-spacing: 0.7px;
  text-transform: uppercase; color: rgba(255,255,255,0.5);
  margin-top: 4px;
}

/* Pulse dot (reused from dashboard) */
.pulse-dot {
  width: 6px; height: 6px; background: #a5a2f7; border-radius: 50%;
  animation: pulseGlow 1.5s infinite; display: inline-block;
}
@keyframes pulseGlow {
  50%  { transform: scale(1.2); opacity: 1; }
  100% { transform: scale(0.8); opacity: 0.5; }
}

/* ── BREADCRUMB ── */
.dev-breadcrumb {
  display: flex; align-items: center; gap: 6px;
  font-size: 0.78rem;
}
.dev-breadcrumb__sep { color: #555; font-size: 0.7rem; }
.dev-breadcrumb__item { color: #666; }
.dev-breadcrumb__item--active { color: #aaa; font-weight: 600; }

/* ── PANEL ── */
.dev-panel {
  background: var(--das-surface);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius);
  overflow: hidden;
  backdrop-filter: blur(6px);
}
.dev-panel__head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0.9rem 1.25rem;
  border-bottom: 1px solid var(--das-border);
  flex-wrap: wrap; gap: 0.75rem;
}
.dev-panel__title {
  font-size: 0.82rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.6px;
  display: flex; align-items: center; gap: 8px;
  color: #ccc;
}
.dev-panel__icon-dot {
  width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.dev-panel__icon-dot--primary { background: var(--das-primary); box-shadow: 0 0 6px var(--das-primary); }

/* ── SEARCH ── */
.dev-search-wrap {
  position: relative; display: flex; align-items: center;
}
.dev-search-wrap__icon {
  position: absolute; left: 8px; font-size: 0.85rem; color: #555; pointer-events: none;
}
.dev-search-input {
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius-sm);
  color: #ccc; font-size: 0.78rem;
  padding: 0.35rem 0.75rem 0.35rem 2rem;
  width: 200px; outline: none;
  transition: all 0.2s;
}
.dev-search-input::placeholder { color: #555; }
.dev-search-input:focus {
  border-color: rgba(115,103,240,0.5);
  background: rgba(115,103,240,0.06);
}

/* ── TABLE ── */
.dev-table {
  width: 100%; border-collapse: collapse; font-size: 0.82rem;
}
.dev-table thead th {
  font-size: 0.6rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.8px;
  color: #555; padding: 0.65rem 1.1rem;
  border-bottom: 1px solid var(--das-border);
  background: transparent; white-space: nowrap;
}
.dev-table tbody td {
  padding: 0.75rem 1.1rem;
  border-bottom: 1px solid var(--das-border);
  color: #ccc; vertical-align: middle;
}
.dev-table tbody tr:last-child td { border-bottom: none; }
.dev-table tbody tr { transition: background 0.15s; }
.dev-table tbody tr:hover td { background: var(--das-surface-hover); }

/* ── STATUS BADGE ── */
.dev-status-badge {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 0.65rem; font-weight: 700;
  padding: 4px 10px; border-radius: 20px;
  text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap;
}
.dev-status-badge .ti { font-size: 0.75rem; }
.dev-status-badge--authorized {
  background: var(--das-success-soft); color: var(--das-success);
  border: 1px solid rgba(40,199,111,0.2);
}
.dev-status-badge--pending {
  background: var(--das-warning-soft); color: var(--das-warning);
  border: 1px solid rgba(255,159,67,0.2);
}

/* ── DEVICE INFO CELL ── */
.dev-device-info {
  display: flex; align-items: center; gap: 0.75rem;
}
.dev-device-info__icon {
  width: 38px; height: 38px; border-radius: var(--das-radius-sm);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; flex-shrink: 0;
}
.dev-device-info__icon--authorized {
  background: var(--das-success-soft); color: var(--das-success);
}
.dev-device-info__icon--pending {
  background: var(--das-warning-soft); color: var(--das-warning);
}
.dev-device-info__name {
  font-size: 0.82rem; font-weight: 700; color: #e2e8f0;
}
.dev-device-info__agent {
  font-size: 0.68rem; color: #555; margin-top: 2px;
  max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* ── UUID ── */
.dev-uuid-wrap { display: flex; align-items: center; }
.dev-uuid {
  font-family: monospace; font-size: 0.72rem;
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--das-border);
  border-radius: 4px;
  padding: 3px 8px; color: #a5a2f7;
  display: block; max-width: 220px;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* ── IP BADGE ── */
.dev-ip-badge {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 0.75rem; color: #94a3b8;
  font-family: monospace;
}
.dev-ip-badge .ti { color: var(--das-info); font-size: 0.8rem; }

/* ── TIME ── */
.dev-time-wrap {
  display: flex; align-items: center; gap: 5px;
  font-size: 0.75rem; color: #64748b;
}
.dev-time-wrap .ti { font-size: 0.8rem; color: #475569; }

/* ── ACTION GROUP ── */
.dev-action-group {
  display: flex; align-items: center; gap: 6px; justify-content: center;
}
.dev-btn {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 0.72rem; font-weight: 600;
  padding: 0.35rem 0.7rem; border-radius: 5px;
  border: 1px solid transparent; cursor: pointer;
  transition: all 0.18s ease; text-decoration: none;
  white-space: nowrap;
}
.dev-btn--success {
  background: var(--das-success-soft); color: var(--das-success);
  border-color: rgba(40,199,111,0.25);
}
.dev-btn--success:hover {
  background: var(--das-success); color: white; border-color: var(--das-success);
}
.dev-btn--info {
  background: var(--das-info-soft); color: var(--das-info);
  border-color: rgba(0,207,232,0.25);
}
.dev-btn--info:hover {
  background: var(--das-info); color: white; border-color: var(--das-info);
}
.dev-btn--danger {
  background: var(--das-danger-soft); color: var(--das-danger);
  border-color: rgba(234,84,85,0.25);
}
.dev-btn--danger:hover {
  background: var(--das-danger); color: white; border-color: var(--das-danger);
}

/* ── EMPTY STATE ── */
.dev-empty-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 3.5rem 2rem; gap: 0.5rem; text-align: center;
}
.dev-empty-state__icon {
  width: 60px; height: 60px; border-radius: 5px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--das-border);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.75rem; color: #555; margin-bottom: 0.5rem;
}
.dev-empty-state__title { font-size: 0.9rem; font-weight: 700; color: #888; }
.dev-empty-state__sub { font-size: 0.75rem; color: #555; max-width: 320px; }

/* ── MODAL (reuse das-modal from dashboard) ── */
.das-modal {
  background: #1a1a2e;
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius);
}
.das-modal__head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1rem 1.25rem;
  background: var(--das-primary-soft);
  border-bottom: 1px solid var(--das-border);
}
.das-modal__head--danger {
  background: rgba(234,84,85,0.12);
}
.das-modal--danger {
  border-color: rgba(234,84,85,0.25);
}
.das-modal__title { font-size: 0.9rem; font-weight: 700; color: #ddd; margin: 0; }
.das-modal__body { padding: 0; }
.das-modal__foot {
  padding: 0.85rem 1.25rem;
  border-top: 1px solid var(--das-border);
}
.das-btn {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 0.75rem; font-weight: 600;
  padding: 0.45rem 0.85rem; border-radius: 5px;
  border: 1px solid transparent; cursor: pointer;
  transition: all 0.18s ease;
}
.das-btn--ghost {
  background: transparent; border-color: var(--das-border); color: #999;
}
.das-btn--ghost:hover { background: var(--das-surface-hover); color: white; }
.das-btn--primary {
  background: var(--das-primary); color: white; border-color: var(--das-primary);
}
.das-btn--primary:hover { background: #6259e8; }
.das-btn--danger-solid {
  background: var(--das-danger); color: white; border-color: var(--das-danger);
}
.das-btn--danger-solid:hover { background: #d63031; border-color: #d63031; }

/* ── KONFIRMASI BANNER (di dalam modal authorize) ── */
.dev-confirm-banner {
  display: flex; align-items: flex-start; gap: 0.85rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--das-border);
}
.dev-confirm-banner--success {
  background: rgba(40,199,111,0.07);
  border-bottom-color: rgba(40,199,111,0.15);
}
.dev-confirm-banner--info {
  background: rgba(0,207,232,0.07);
  border-bottom-color: rgba(0,207,232,0.15);
}
.dev-confirm-banner__icon {
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; flex-shrink: 0;
}
.dev-confirm-banner--success .dev-confirm-banner__icon {
  background: rgba(40,199,111,0.15);
  color: var(--das-success);
}
.dev-confirm-banner--info .dev-confirm-banner__icon {
  background: rgba(0,207,232,0.15);
  color: var(--das-info);
}
.dev-confirm-banner__title {
  font-size: 0.82rem; font-weight: 700; color: #e2e8f0; margin-bottom: 2px;
}
.dev-confirm-banner__sub {
  font-size: 0.72rem; color: #64748b; line-height: 1.4;
}

/* ── MODAL HAPUS — Danger Icon ── */
.dev-confirm-danger-icon {
  position: relative;
  width: 72px; height: 72px;
  margin: 2rem auto 1.25rem;
  display: flex; align-items: center; justify-content: center;
}
.dev-confirm-danger-icon__ring {
  position: absolute; inset: 0;
  border-radius: 50%;
  border: 2px solid rgba(234,84,85,0.35);
  background: rgba(234,84,85,0.1);
  animation: dangerPulse 2s ease-in-out infinite;
}
@keyframes dangerPulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.08); opacity: 0.75; }
}
.dev-confirm-danger-icon__symbol {
  position: relative; z-index: 1;
  font-size: 1.75rem; color: var(--das-danger);
}

/* ── MODAL HAPUS — Pesan ── */
.dev-confirm-message {
  padding: 0 1.25rem 1.25rem;
  text-align: center;
}
.dev-confirm-message__main {
  font-size: 0.9rem; font-weight: 700; color: #e2e8f0;
  margin: 0 0 1rem;
}
.dev-confirm-device-card {
  display: flex; align-items: center; gap: 0.75rem;
  background: rgba(234,84,85,0.06);
  border: 1px solid rgba(234,84,85,0.2);
  border-radius: var(--das-radius);
  padding: 0.65rem 0.9rem;
  margin-bottom: 1rem;
  text-align: left;
}
.dev-confirm-device-card__icon {
  font-size: 1.2rem; color: var(--das-danger); flex-shrink: 0;
}
.dev-confirm-device-card__name {
  font-size: 0.82rem; font-weight: 700; color: #e2e8f0;
}
.dev-confirm-device-card__uuid {
  font-size: 0.68rem; color: #64748b; font-family: monospace;
}
.dev-confirm-message__warning {
  display: flex; align-items: flex-start; gap: 6px; justify-content: center;
  font-size: 0.7rem; color: #64748b; line-height: 1.4;
  margin: 0;
}
.dev-confirm-message__warning .ti { color: var(--das-warning); flex-shrink: 0; margin-top: 1px; }

/* Modal device preview */
.dev-modal-device-preview {
  display: flex; align-items: center; gap: 1rem;
  padding: 1.25rem;
  background: rgba(255,255,255,0.03);
  border-bottom: 1px solid var(--das-border);
}
.dev-modal-device-preview__icon {
  width: 48px; height: 48px; border-radius: 5px;
  background: var(--das-warning-soft);
  color: var(--das-warning);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem; flex-shrink: 0;
}
.dev-modal-device-preview__name {
  font-size: 0.85rem; font-weight: 700; color: #e2e8f0; margin-bottom: 3px;
}
.dev-modal-device-preview__uuid {
  font-family: monospace; font-size: 0.7rem; color: #666;
}

/* Form */
.dev-form-group { margin-bottom: 1rem; }
.dev-form-label {
  display: block; font-size: 0.75rem; font-weight: 700;
  color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;
}
.dev-form-input {
  width: 100%; background: rgba(255,255,255,0.04);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius-sm);
  color: #e2e8f0; font-size: 0.82rem;
  padding: 0.55rem 0.85rem; outline: none;
  transition: all 0.2s;
}
.dev-form-input::placeholder { color: #555; }
.dev-form-input:focus {
  border-color: rgba(115,103,240,0.5);
  background: rgba(115,103,240,0.06);
  box-shadow: 0 0 0 3px rgba(115,103,240,0.08);
}
.dev-form-hint {
  display: flex; align-items: flex-start; gap: 6px;
  font-size: 0.72rem; color: #555; line-height: 1.5;
  padding: 0.65rem 0.85rem;
  background: rgba(255,255,255,0.02);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius-sm);
  margin-bottom: 1.25rem;
}
.dev-form-hint .ti { color: var(--das-info); flex-shrink: 0; margin-top: 1px; }

/* ── CHIPS (from dashboard) ── */
.das-chip {
  display: inline-flex; align-items: center;
  font-size: 0.65rem; font-weight: 700;
  padding: 2px 9px; border-radius: 20px;
  text-transform: uppercase; letter-spacing: 0.4px;
}
.das-chip.--info { background: var(--das-info-soft); color: var(--das-info); }
 
/* ── RESPONSIVE MODAL WIDTH ── */
.modal-dialog--confirm {
  max-width: 400px;
  width: 100%;
}
@media (max-width: 576px) {
  .modal-dialog--confirm {
    max-width: 92%;
    margin-left: auto;
    margin-right: auto;
  }
}

/* ── RESPONSIVE ── */
@media (max-width: 992px) {
  .dev-page-header__stats { display: none; }
  .dev-page-header__inner { padding: 1.5rem 1.25rem; }
}
@media (max-width: 576px) {
  .dev-search-input { width: 140px; }
  .dev-table thead th:nth-child(3),
  .dev-table tbody td:nth-child(3) { display: none; }
}
</style>
@endsection


@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {

  // ── Search filter ──
  const searchInput = document.getElementById('deviceSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const q = this.value.toLowerCase().trim();
      document.querySelectorAll('#deviceTable tbody tr.dev-table__row').forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const uuid = row.getAttribute('data-uuid') || '';
        row.style.display = (name.includes(q) || uuid.includes(q)) ? '' : 'none';
      });
    });
  }

  // ── Modal Izinkan / Edit ──
  document.querySelectorAll('.btn-authorize').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const isAuthorized = this.getAttribute('data-is-authorized') === '1';
      const deviceName   = this.getAttribute('data-device-name') || 'Perangkat Baru';
      const deviceUuid   = this.getAttribute('data-device-uuid-short') || '';
      const action       = this.getAttribute('data-action');
      const currentName  = this.getAttribute('data-current-name') || '';

      // Form action
      document.getElementById('authorizeForm').setAttribute('action', action);

      // Title
      document.getElementById('authorizeModalTitle').textContent =
        isAuthorized ? 'Edit Nama Perangkat' : 'Izinkan Perangkat';

      // Banner
      const banner = document.getElementById('authorizeBanner');
      banner.className = 'dev-confirm-banner ' +
        (isAuthorized ? 'dev-confirm-banner--info' : 'dev-confirm-banner--success');
      document.getElementById('authorizeBannerIcon').className =
        'ti ' + (isAuthorized ? 'tabler-edit' : 'tabler-shield-check');
      document.getElementById('authorizeBannerTitle').textContent =
        isAuthorized ? 'Apakah Anda ingin mengubah nama perangkat ini?' : 'Apakah Anda yakin ingin mengizinkan perangkat ini?';
      document.getElementById('authorizeBannerSub').textContent =
        isAuthorized ? 'Perubahan nama akan membantu identifikasi perangkat di daftar ini.' : 'Perangkat yang diizinkan dapat mengakses fitur Scan QR dan Live Board.';

      // Preview
      document.getElementById('authorizeDeviceName').textContent = deviceName;
      document.getElementById('authorizeDeviceUuid').textContent = deviceUuid;

      // Input
      document.getElementById('authorizeDeviceNameInput').value = currentName;

      // Submit button text
      document.querySelector('#authorizeSubmitBtn span').textContent =
        isAuthorized ? 'Simpan Perubahan' : 'Ya, Izinkan';

      // Tampilkan modal
      const modal = new bootstrap.Modal(document.getElementById('authorizeModal'));
      modal.show();
    });
  });

  // ── Modal Konfirmasi Hapus ──
  const deleteModal = document.getElementById('deleteConfirmModal');
  if (deleteModal) {
    deleteModal.addEventListener('show.bs.modal', function (event) {
      const triggerBtn = event.relatedTarget;
      const deviceName = triggerBtn.getAttribute('data-device-name') || 'Perangkat';
      const deviceUuid = triggerBtn.getAttribute('data-device-uuid') || '';
      const actionUrl  = triggerBtn.getAttribute('data-action');

      // Isi data perangkat ke dalam modal
      document.getElementById('deleteModalDeviceName').textContent = deviceName;
      document.getElementById('deleteModalDeviceUuid').textContent = deviceUuid;

      // Set form action
      document.getElementById('genericDeleteForm').setAttribute('action', actionUrl);
    });
  }

});
</script>
@endsection