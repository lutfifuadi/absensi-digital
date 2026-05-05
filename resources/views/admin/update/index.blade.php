@extends('layouts/layoutMaster')

@section('title', 'Pembaruan Sistem')

@section('page-style')
  <style>
    :root {
      --das-primary: #7367f0;
      --das-primary-soft: rgba(115, 103, 240, 0.12);
      --das-success: #28c76f;
      --das-success-soft: rgba(40, 199, 111, 0.12);
      --das-info: #00cfe8;
      --das-info-soft: rgba(0, 207, 232, 0.12);
      --das-warning: #ff9f43;
      --das-warning-soft: rgba(255, 159, 67, 0.12);
      --das-danger: #ea5455;
      --das-danger-soft: rgba(234, 84, 85, 0.12);
      --das-dark: #4b4b4b;
      --das-secondary: #a8aaae;
      --das-surface: rgba(15, 23, 42, 0.4);
      --das-surface-hover: rgba(30, 41, 59, 0.6);
      --das-border: rgba(255, 255, 255, 0.06);
      --das-border-hover: rgba(255, 255, 255, 0.12);
      --das-radius: 5px;
    }

    .glass-card {
      background: rgba(255, 255, 255, 0.03) !important;
      backdrop-filter: blur(12px) saturate(180%);
      -webkit-backdrop-filter: blur(12px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      border-radius: var(--das-radius);
    }

    .das-hero {
      position: relative;
      border-radius: var(--das-radius);
      overflow: hidden;
      margin-bottom: 2rem;
    }

    .das-hero__bg {
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%);
      z-index: 0;
    }

    .das-hero__glass {
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top right, rgba(115, 103, 240, 0.15), transparent 40%);
      z-index: 1;
    }

    .das-hero__grid-lines {
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
      background-size: 40px 40px;
      z-index: 1;
    }

    .das-hero__inner {
      position: relative;
      z-index: 2;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 2.5rem;
      gap: 1.5rem;
      flex-wrap: wrap;
    }

    .das-hero__title {
      font-size: 1.5rem;
      font-weight: 800;
      color: white;
      margin: 0 0 4px;
    }

    .das-hero__welcome {
      margin: 0;
      font-size: 0.88rem;
      color: rgba(255, 255, 255, 0.6);
    }

    .version-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.15);
      padding: 8px 16px;
      border-radius: 50px;
      font-size: 0.85rem;
      font-weight: 500;
      color: #fff;
    }

    .version-badge .dot {
      width: 8px;
      height: 8px;
      background: #10b981;
      border-radius: 50%;
      animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .update-card {
      background: var(--das-surface);
      border: 1px solid var(--das-border);
      border-radius: var(--das-radius);
      overflow: hidden;
      backdrop-filter: blur(6px);
      transition: all 0.3s ease;
    }

    .update-card:hover {
      border-color: rgba(115, 103, 240, 0.3);
      box-shadow: 0 8px 32px rgba(115, 103, 240, 0.15);
    }

    .changelog-card {
      background: rgba(115, 103, 240, 0.08);
      border: 1px solid rgba(115, 103, 240, 0.15);
      border-radius: var(--das-radius);
      padding: 20px;
    }

    .changelog-item {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid var(--das-border);
    }

    .changelog-item:last-child {
      border-bottom: none;
    }

    .changelog-icon {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(115, 103, 240, 0.2);
      border-radius: 8px;
      color: #a5b4fc;
      flex-shrink: 0;
    }

    .update-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.5rem 1rem;
      border-radius: var(--das-radius);
      border: 1px solid transparent;
      cursor: pointer;
      transition: all 0.18s ease;
      text-decoration: none;
      white-space: nowrap;
    }

    .update-btn:hover {
      transform: translateY(-2px);
    }

    .info-row {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.5);
    }

    .success-state {
      text-align: center;
      padding: 40px;
    }

    .success-icon {
      width: 100px;
      height: 100px;
      margin: 0 auto 24px;
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 48px;
      color: #fff;
      box-shadow: 0 20px 40px rgba(16, 185, 129, 0.3);
    }

    .new-version-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: #fff;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 16px;
      animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
      0%, 100% { box-shadow: 0 0 10px rgba(245, 158, 11, 0.4); }
      50% { box-shadow: 0 0 20px rgba(245, 158, 11, 0.6); }
    }

    .progress-modal .modal-content {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 16px;
    }

    .progress-step {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 8px;
      margin-bottom: 8px;
      transition: all 0.3s ease;
    }

    .progress-step.active {
      background: rgba(115, 103, 240, 0.15);
      border-left: 3px solid #7367f0;
    }

    .progress-step.completed {
      background: rgba(16, 185, 129, 0.1);
    }

    .progress-step-icon {
      width: 28px;
      height: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      font-size: 14px;
    }

    .progress-step.active .progress-step-icon {
      background: #7367f0;
      color: #fff;
    }

    .progress-step.completed .progress-step-icon {
      background: #10b981;
      color: #fff;
    }
  </style>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    {{-- Hero Section --}}
    <div class="das-hero">
      <div class="das-hero__bg"></div>
      <div class="das-hero__glass"></div>
      <div class="das-hero__grid-lines"></div>
      <div class="das-hero__inner">
        <div>
          <h3 class="das-hero__title" style="font-weight: 700;">Pembaruan Sistem</h3>
          <p class="das-hero__welcome">Periksa dan install versi terbaru aplikasi</p>
        </div>
        <div class="version-badge">
          <span class="dot"></span>
          <span>v{{ $currentVersion }}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    @if($updateInfo && isset($updateInfo['latest_version']))
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="update-card card">
            <div class="card-body p-4">
              <div class="d-flex align-items-center gap-3 mb-4">
                <div class="das-avatar-circle" style="width: 48px; height: 48px; font-size: 1.25rem;">
                  <i class="ti tabler-cloud-download"></i>
                </div>
                <div>
                  <h5 class="mb-1" style="color: white;">Update Tersedia!</h5>
                  <p class="text-muted mb-0">Versi {{ $updateInfo['latest_version'] }} siap untuk diinstall</p>
                </div>
              </div>

              <div class="new-version-badge">
                <i class="ti tabler-rocket"></i>
                New Version {{ $updateInfo['latest_version'] }}
              </div>

              <div class="changelog-card mt-4">
                <h6 class="mb-3 d-flex align-items-center gap-2" style="color: #ccc;">
                  <i class="ti tabler-list-details text-primary"></i>
                  Changelog
                </h6>
                <div class="changelog-content" style="white-space: pre-line; font-size: 0.9rem; color: rgba(255,255,255,0.8);">
                  {{ $updateInfo['changelog'] }}
                </div>
              </div>

              <div class="mt-4">
                <button type="button" id="btn-run-update" class="btn btn-primary update-btn das-btn das-btn--primary">
                  <i class="ti tabler-download"></i>
                  Install Pembaruan
                </button>
              </div>

              <div class="info-row mt-3">
                <i class="ti tabler-alert-triangle text-warning"></i>
                <span>Pastikan backup database sebelum melanjutkan</span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="update-card card h-100">
            <div class="card-body p-4">
              <h6 class="mb-3 d-flex align-items-center gap-2" style="color: #ccc;">
                <i class="ti tabler-info-circle text-info"></i>
                Info Sistem
              </h6>
              <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Versi Saat Ini</span>
                  <span class="fw-semibold" style="color: #ccc;">{{ $currentVersion }}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Versi Terbaru</span>
                  <span class="fw-semibold text-success">{{ $updateInfo['latest_version'] }}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Terakhir Dicek</span>
                  <span style="color: #ccc;">{{ $updateInfo['last_check'] ?? '-' }}</span>
                </div>
              </div>
              <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
              <button type="button" id="btn-check-update" class="btn btn-outline-primary w-100 update-btn das-btn das-btn--ghost">
                <i class="ti tabler-refresh"></i>
                Periksa Pembaruan Manual
              </button>
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="update-card card">
        <div class="card-body p-5">
          <div class="success-state">
            <div class="success-icon">
              <i class="ti tabler-check"></i>
            </div>
            <h4 class="text-white mb-2">Sistem Sudah Terbaru</h4>
            <p class="text-muted mb-4">Anda menggunakan versi terbaru dari {{ config('app.name') }}</p>
            <div class="info-row justify-content-center mb-4">
              <i class="ti tabler-clock"></i>
              <span>Terakhir diperiksa: {{ $updateInfo['last_check'] ?? 'Baru saja' }}</span>
            </div>
            <button type="button" id="btn-check-update" class="btn btn-outline-primary update-btn das-btn das-btn--ghost">
              <i class="ti tabler-refresh"></i>
              Periksa Pembaruan
            </button>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>

{{-- Modal Progress Update --}}
<div class="modal fade" id="modalProgressUpdate" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg progress-modal">
    <div class="modal-content">
      <div class="modal-body p-4">
        <div class="text-center mb-4">
          <div class="avatar avatar-xl bg-label-primary mb-3">
            <i class="ti tabler-download fs-1"></i>
          </div>
          <h5 class="mb-1">Sedang Memproses Pembaruan</h5>
          <p class="text-muted">Mohon tunggu, sistem sedang diperbarui...</p>
        </div>

        <div class="progress mb-4" style="height: 8px;">
          <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="update-progress"></div>
        </div>

        <div class="d-flex flex-column gap-2" id="progress-steps">
          <div class="progress-step" data-step="1">
            <div class="progress-step-icon"><i class="ti tabler-download"></i></div>
            <div>
              <div class="fw-semibold">Mengunduh Paket</div>
              <small class="text-muted">Mendownload file pembaruan...</small>
            </div>
          </div>
          <div class="progress-step" data-step="2">
            <div class="progress-step-icon"><i class="ti tabler-folder"></i></div>
            <div>
              <div class="fw-semibold">Mengekstrak File</div>
              <small class="text-muted">Mengextract file update...</small>
            </div>
          </div>
          <div class="progress-step" data-step="3">
            <div class="progress-step-icon"><i class="ti tabler-database"></i></div>
            <div>
              <div class="fw-semibold">Migrasi Database</div>
              <small class="text-muted">Menjalankan migrasi database...</small>
            </div>
          </div>
          <div class="progress-step" data-step="4">
            <div class="progress-step-icon"><i class="ti tabler-broom"></i></div>
            <div>
              <div class="fw-semibold">Membersihkan Cache</div>
              <small class="text-muted">Optimasi sistem...</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Custom Alert/Confirm Modal --}}
<div class="modal fade" id="modalCustomAlert" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
    <div class="modal-content" style="background: linear-gradient(145deg, #0f1729 0%, #1a2545 60%, #1e2d50 100%); border: 1px solid rgba(115,103,240,0.25); border-radius: 20px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.5);">

      {{-- Decorative top accent bar --}}
      <div id="custom-alert-accent-bar" style="height: 4px; width: 100%; background: linear-gradient(90deg, #7367f0, #9e95f5);"></div>

      <div class="modal-body p-0">
        <div class="text-center p-5 pb-4">

          {{-- Icon circle --}}
          <div id="custom-alert-icon-container" style="
            width: 80px; height: 80px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            background: rgba(115,103,240,0.15);
            border: 2px solid rgba(115,103,240,0.3);
            transition: all 0.3s ease;
          ">
            <i id="custom-alert-icon" class="ti tabler-info-circle" style="font-size: 2.2rem; color: #7367f0;"></i>
          </div>

          {{-- Title --}}
          <h4 id="custom-alert-title" class="fw-bold mb-2" style="color: #fff; font-size: 1.3rem;">Informasi</h4>

          {{-- Message --}}
          <div id="custom-alert-message" class="mb-0" style="color: rgba(255,255,255,0.65); font-size: 0.925rem; line-height: 1.6;"></div>

        </div>

        {{-- Divider --}}
        <div style="height: 1px; background: rgba(255,255,255,0.07); margin: 0 1.5rem;"></div>

        {{-- Actions --}}
        <div class="d-flex gap-2 p-4 pt-3">
          <button type="button" id="custom-alert-cancel"
            style="display:none; flex:1; padding: 10px 0; border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.7); transition: all 0.2s;"
            onmouseover="this.style.background='rgba(255,255,255,0.1)'"
            onmouseout="this.style.background='rgba(255,255,255,0.06)'"
            data-bs-dismiss="modal">Batal</button>
          <button type="button" id="custom-alert-confirm"
            style="flex:1; padding: 10px 0; border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: linear-gradient(135deg,#7367f0,#9e95f5); border: none; color: #fff; box-shadow: 0 4px 15px rgba(115,103,240,0.35); transition: all 0.2s;"
            onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(115,103,240,0.5)'"
            onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 15px rgba(115,103,240,0.35)'">OK</button>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const btnCheckUpdate = document.getElementById('btn-check-update');
  const btnRunUpdate = document.getElementById('btn-run-update');
  const modalProgress = new bootstrap.Modal(document.getElementById('modalProgressUpdate'));
  const progressBar = document.getElementById('update-progress');
  const progressSteps = document.querySelectorAll('.progress-step');

  // Initialize Custom Modal
  const customModalEl = document.getElementById('modalCustomAlert');
  const customModal = customModalEl ? new bootstrap.Modal(customModalEl) : null;
  
  function showCustomModal(options) {
    if (!customModal) return;

    document.getElementById('custom-alert-title').innerHTML = options.title || 'Informasi';
    document.getElementById('custom-alert-message').innerHTML = options.message || '';
    
    const iconContainer = document.getElementById('custom-alert-icon-container');
    const icon = document.getElementById('custom-alert-icon');
    const accentBar = document.getElementById('custom-alert-accent-bar');
    const btnConfirm = document.getElementById('custom-alert-confirm');
    const btnCancel = document.getElementById('custom-alert-cancel');

    // Color themes per type
    const themes = {
      success: { gradient: 'linear-gradient(90deg,#28c76f,#48da89)', iconColor: '#28c76f', iconBg: 'rgba(40,199,111,0.15)', iconBorder: 'rgba(40,199,111,0.3)', btnGradient: 'linear-gradient(135deg,#28c76f,#48da89)', btnShadow: 'rgba(40,199,111,0.35)' },
      info:    { gradient: 'linear-gradient(90deg,#00cfe8,#1ce7ff)', iconColor: '#00cfe8', iconBg: 'rgba(0,207,232,0.15)', iconBorder: 'rgba(0,207,232,0.3)', btnGradient: 'linear-gradient(135deg,#00cfe8,#1ce7ff)', btnShadow: 'rgba(0,207,232,0.35)' },
      danger:  { gradient: 'linear-gradient(90deg,#ea5455,#f08182)', iconColor: '#ea5455', iconBg: 'rgba(234,84,85,0.15)', iconBorder: 'rgba(234,84,85,0.3)', btnGradient: 'linear-gradient(135deg,#ea5455,#f08182)', btnShadow: 'rgba(234,84,85,0.35)' },
      warning: { gradient: 'linear-gradient(90deg,#ff9f43,#ffb976)', iconColor: '#ff9f43', iconBg: 'rgba(255,159,67,0.15)', iconBorder: 'rgba(255,159,67,0.3)', btnGradient: 'linear-gradient(135deg,#7367f0,#9e95f5)', btnShadow: 'rgba(115,103,240,0.35)' },
      primary: { gradient: 'linear-gradient(90deg,#7367f0,#9e95f5)', iconColor: '#7367f0', iconBg: 'rgba(115,103,240,0.15)', iconBorder: 'rgba(115,103,240,0.3)', btnGradient: 'linear-gradient(135deg,#7367f0,#9e95f5)', btnShadow: 'rgba(115,103,240,0.35)' },
    };
    const theme = themes[options.color] || themes.primary;

    // Apply accent bar
    if (accentBar) accentBar.style.background = theme.gradient;

    // Apply icon
    iconContainer.style.background = theme.iconBg;
    iconContainer.style.borderColor = theme.iconBorder;
    icon.className = options.icon || 'ti tabler-info-circle';
    icon.style.fontSize = '2.2rem';
    icon.style.color = theme.iconColor;

    // Apply confirm button
    btnConfirm.innerHTML = options.confirmText || 'OK';
    btnConfirm.style.background = theme.btnGradient;
    btnConfirm.style.boxShadow = `0 4px 15px ${theme.btnShadow}`;

    // Cancel button
    if (options.type === 'confirm') {
      btnCancel.style.display = 'flex';
      btnCancel.innerHTML = options.cancelText || 'Batal';
    } else {
      btnCancel.style.display = 'none';
    }

    // Clone to unbind stale events
    const newBtnConfirm = btnConfirm.cloneNode(true);
    btnConfirm.parentNode.replaceChild(newBtnConfirm, btnConfirm);
    // Re-attach hover styles
    newBtnConfirm.style.background = theme.btnGradient;
    newBtnConfirm.style.boxShadow = `0 4px 15px ${theme.btnShadow}`;
    newBtnConfirm.onmouseover = function() { this.style.transform='translateY(-1px)'; this.style.boxShadow=`0 6px 20px ${theme.btnShadow.replace('0.35','0.55')}`; };
    newBtnConfirm.onmouseout  = function() { this.style.transform='translateY(0)'; this.style.boxShadow=`0 4px 15px ${theme.btnShadow}`; };
    
    newBtnConfirm.onclick = function() {
      customModal.hide();
      if (options.onConfirm) options.onConfirm();
    };

    customModal.show();
  }

  function updateSteps(step) {
    progressSteps.forEach((el, index) => {
      el.classList.remove('active', 'completed');
      if (index + 1 < step) {
        el.classList.add('completed');
      } else if (index + 1 === step) {
        el.classList.add('active');
      }
    });
  }

  if (btnCheckUpdate) {
    btnCheckUpdate.addEventListener('click', function() {
      btnCheckUpdate.disabled = true;
      btnCheckUpdate.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Memeriksa...';

      console.log('[Update] Memulai pengecekan update...');

      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 90000); // 90 detik

      fetch("{{ route('admin.update.check') }}", {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        signal: controller.signal
      })
      .then(response => {
        clearTimeout(timeoutId);
        console.log('[Update] HTTP Status:', response.status);
        return response.json();
      })
      .then(data => {
        console.log('[Update] Response data:', data);
        if (data.success) {
          showCustomModal({
            title: data.update_available ? 'Update Tersedia!' : 'Sudah Terbaru',
            message: data.update_available ? `Versi ${data.data.latest_version} ditemukan, halaman akan di-refresh.` : 'Sistem Anda sudah menggunakan versi terbaru.',
            iconBg: data.update_available ? 'bg-label-info' : 'bg-label-success',
            icon: data.update_available ? 'ti tabler-info-circle' : 'ti tabler-check',
            color: data.update_available ? 'info' : 'success',
            onConfirm: () => window.location.reload()
          });
        } else {
          throw new Error(data.message || 'Gagal memeriksa update');
        }
      })
      .catch(error => {
        clearTimeout(timeoutId);
        console.error('[Update] Error:', error.message);
        const msg = error.name === 'AbortError' ? 'Koneksi timeout (90 detik). Periksa koneksi internet Anda.' : error.message;
        
        showCustomModal({
          title: 'Error',
          message: msg,
          iconBg: 'bg-label-danger',
          icon: 'ti tabler-alert-circle',
          color: 'danger'
        });
        
        btnCheckUpdate.disabled = false;
        btnCheckUpdate.innerHTML = '<i class="ti tabler-refresh me-2"></i> Periksa Pembaruan';
      });
    });
  }

  if (btnRunUpdate) {
    btnRunUpdate.onclick = function() {
      console.log('Tombol Install diklik');
      
      showCustomModal({
        type: 'confirm',
        title: 'Konfirmasi Pembaruan',
        message: `
          <div class="text-start">
            <p class="mb-3 text-white" style="font-size: 0.95rem;">Sistem akan diperbarui ke versi terbaru.</p>
            <div class="d-flex align-items-center gap-3 p-3 rounded" style="background: rgba(255, 159, 67, 0.15); border: 1px solid rgba(255, 159, 67, 0.3);">
              <i class="ti tabler-alert-triangle text-warning fs-3"></i>
              <span class="text-warning mb-0" style="font-size: 0.85rem; line-height: 1.4;">Pastikan Anda telah melakukan backup database sebelum melanjutkan.</span>
            </div>
          </div>
        `,
        iconBg: 'bg-label-warning',
        icon: 'ti tabler-alert-triangle',
        color: 'warning',
        confirmText: 'Ya, Perbarui!',
        cancelText: 'Batal',
        onConfirm: startUpdateProcess
      });
    };
  }

  function startUpdateProcess() {
    modalProgress.show();
    updateSteps(1);

    let progress = 0;
    const interval = setInterval(() => {
      if (progress < 90) {
        progress += Math.random() * 8 + 2;
        updateProgress(Math.min(progress, 90));
      }
    }, 1200);

    function updateProgress(p) {
      progressBar.style.width = p + '%';
      if (p < 25) updateSteps(1);
      else if (p < 50) updateSteps(2);
      else if (p < 75) updateSteps(3);
      else updateSteps(4);
    }

    fetch("{{ route('admin.update.run') }}", {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      clearInterval(interval);
      progressBar.style.width = '100%';
      progressSteps.forEach(el => el.classList.add('completed'));

      if (data.success) {
        setTimeout(() => {
          modalProgress.hide();
          showCustomModal({
            title: 'Berhasil!',
            message: data.message,
            iconBg: 'bg-label-success',
            icon: 'ti tabler-check',
            color: 'success',
            onConfirm: () => window.location.href = "{{ route('admin.update.index') }}"
          });
        }, 1500);
      } else {
        throw new Error(data.message || 'Gagal menjalankan update');
      }
    })
    .catch(error => {
      clearInterval(interval);
      modalProgress.hide();
      showCustomModal({
        title: 'Gagal',
        message: error.message,
        iconBg: 'bg-label-danger',
        icon: 'ti tabler-alert-circle',
        color: 'danger'
      });
    });
  }
});
</script>
@endsection