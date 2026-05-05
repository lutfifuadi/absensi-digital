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
      --das-radius: 12px;
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
      border-radius: 8px;
      padding: 20px;
    }

    .update-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      padding: 0.6rem 1.25rem;
      border-radius: 8px;
      border: 1px solid transparent;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
    }

    .update-btn:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(115, 103, 240, 0.3);
    }

    .update-btn:active:not(:disabled) {
      transform: translateY(0);
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

    /* Premium Modal Styles */
    .premium-modal .modal-content {
      background: #111827;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      overflow: hidden;
      color: #fff;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .modal-sidebar {
      background: rgba(255, 255, 255, 0.02);
      border-right: 1px solid rgba(255, 255, 255, 0.05);
      padding: 1.5rem;
      width: 260px;
    }

    .modal-main {
      padding: 2rem;
      flex: 1;
      max-height: 80vh;
      overflow-y: auto;
    }

    .history-item {
      padding: 12px;
      border-radius: 12px;
      margin-bottom: 10px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid transparent;
      cursor: default;
      transition: all 0.2s ease;
    }

    .history-item:hover {
      background: rgba(255, 255, 255, 0.06);
      border-color: rgba(115, 103, 240, 0.2);
    }

    .step-indicator {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .progress-step {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .progress-step.active {
      background: rgba(115, 103, 240, 0.1);
      border-color: rgba(115, 103, 240, 0.3);
      transform: scale(1.02);
    }

    .progress-step.completed {
      background: rgba(16, 185, 129, 0.1);
      border-color: rgba(16, 185, 129, 0.3);
    }

    .progress-step-icon {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.05);
      font-size: 16px;
      transition: all 0.3s ease;
    }

    .progress-step.active .progress-step-icon {
      background: var(--das-primary);
      color: #fff;
      box-shadow: 0 0 15px rgba(115, 103, 240, 0.4);
    }

    .progress-step.completed .progress-step-icon {
      background: var(--das-success);
      color: #fff;
    }

    .changelog-body {
      font-size: 0.95rem;
      line-height: 1.6;
      color: rgba(255, 255, 255, 0.8);
      white-space: pre-line;
    }

    .changelog-body h1, .changelog-body h2, .changelog-body h3 {
      color: #fff;
      margin-top: 1rem;
      margin-bottom: 0.5rem;
    }

    .changelog-body ul {
      padding-left: 1.2rem;
    }

    .changelog-body li {
      margin-bottom: 0.4rem;
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
          <h3 class="das-hero__title">Pembaruan Sistem</h3>
          <p class="das-hero__welcome">Periksa dan install versi terbaru aplikasi secara otomatis</p>
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
          <div class="update-card card border-0">
            <div class="card-body p-4">
              <div class="d-flex align-items-center gap-3 mb-4">
                <div class="das-avatar-circle bg-primary" style="width: 54px; height: 54px; font-size: 1.5rem;">
                  <i class="ti tabler-cloud-download"></i>
                </div>
                <div>
                  <h4 class="mb-1 text-white">Versi Baru Tersedia!</h4>
                  <p class="text-muted mb-0">Versi <strong>{{ $updateInfo['latest_version'] }}</strong> siap untuk dipasang.</p>
                </div>
              </div>

              <div class="new-version-badge">
                <i class="ti tabler-rocket"></i>
                Pembaruan Mayor {{ $updateInfo['latest_version'] }}
              </div>

              <div class="changelog-card mt-4 mb-4">
                <h6 class="mb-3 d-flex align-items-center gap-2 text-white">
                  <i class="ti tabler-list-details text-primary"></i>
                  Catatan Perubahan
                </h6>
                <div class="changelog-content text-muted-foreground" style="font-size: 0.9rem;">
                  {{ $updateInfo['changelog'] }}
                </div>
              </div>

              <div class="d-flex flex-wrap gap-3">
                <button type="button" id="btn-run-update" class="btn btn-primary update-btn">
                  <i class="ti tabler-download"></i>
                  Install Pembaruan Sekarang
                </button>
                <button type="button" id="btn-check-update" class="btn btn-outline-secondary update-btn">
                  <i class="ti tabler-refresh"></i>
                  Periksa Ulang
                </button>
              </div>

              <div class="info-row mt-4">
                <i class="ti tabler-shield-check text-success"></i>
                <span>Proses ini aman dan tidak akan menghapus data absensi Anda.</span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="update-card card h-100 border-0">
            <div class="card-body p-4">
              <h6 class="mb-4 d-flex align-items-center gap-2 text-white">
                <i class="ti tabler-info-circle text-info"></i>
                Informasi Rilis
              </h6>
              <div class="d-flex flex-column gap-4">
                <div>
                  <small class="text-muted d-block mb-1">Versi Terpasang</small>
                  <span class="fw-bold h5 text-white">v{{ $currentVersion }}</span>
                </div>
                <div>
                  <small class="text-muted d-block mb-1">Versi Terbaru</small>
                  <span class="fw-bold h5 text-success">v{{ $updateInfo['latest_version'] }}</span>
                </div>
                <div>
                  <small class="text-muted d-block mb-1">Terakhir Diperiksa</small>
                  <span class="text-white">{{ $updateInfo['last_check'] ?? '-' }}</span>
                </div>
              </div>
              
              <div class="alert alert-warning mt-4 mb-0 border-0" style="background: rgba(255, 159, 67, 0.1);">
                <div class="d-flex gap-2">
                  <i class="ti tabler-alert-triangle mt-1"></i>
                  <small class="text-warning">Pastikan server memiliki koneksi internet yang stabil selama proses update.</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="update-card card border-0">
        <div class="card-body p-5">
          <div class="success-state">
            <div class="success-icon mb-4">
              <i class="ti tabler-check"></i>
            </div>
            <h3 class="text-white mb-2">Sistem Sudah Terbaru</h3>
            <p class="text-muted mb-4">Anda menggunakan versi terbaik dan terbaru dari aplikasi ini.</p>
            <div class="d-flex justify-content-center gap-4 mb-5">
              <div class="text-center">
                <small class="text-muted d-block">Versi Sekarang</small>
                <span class="text-white fw-bold">v{{ $currentVersion }}</span>
              </div>
              <div class="text-center">
                <small class="text-muted d-block">Pengecekan Terakhir</small>
                <span class="text-white fw-bold">{{ $updateInfo['last_check'] ?? 'Baru saja' }}</span>
              </div>
            </div>
            <button type="button" id="btn-check-update" class="btn btn-primary update-btn px-5">
              <i class="ti tabler-refresh"></i>
              Periksa Pembaruan Sekarang
            </button>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>

{{-- Modal Konfirmasi Update (Premium) --}}
<div class="modal fade premium-modal" id="modalConfirmUpdate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0">
      <div class="d-flex flex-column flex-md-row">
        {{-- Sidebar History --}}
        <div class="modal-sidebar d-none d-md-block">
          <h6 class="text-white mb-4 d-flex align-items-center gap-2">
            <i class="ti tabler-history text-info"></i>
            Riwayat Versi
          </h6>
          <div id="history-container" style="max-height: 500px; overflow-y: auto;">
            {{-- History items injected here --}}
            <div class="text-muted small">Memuat riwayat...</div>
          </div>
        </div>
        
        {{-- Main Content --}}
        <div class="modal-main">
          <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
              <div class="badge bg-label-primary mb-2">Pembaruan Tersedia</div>
              <h2 class="text-white mb-1" id="modal-latest-version">v1.0.0</h2>
              <p class="text-muted mb-0">Rilis pada <span id="modal-release-date">-</span></p>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="changelog-container p-4 rounded-4 mb-5" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
            <h5 class="text-white mb-3">Apa yang baru?</h5>
            <div id="modal-changelog" class="changelog-body">
              {{-- Changelog text injected here --}}
            </div>
          </div>

          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 pt-4 border-top border-white border-opacity-10">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-warning bg-opacity-10 p-2 rounded-3">
                <i class="ti tabler-alert-triangle text-warning fs-3"></i>
              </div>
              <div class="small text-muted" style="max-width: 300px;">
                Sangat disarankan untuk mencadangkan database sebelum melanjutkan instalasi.
              </div>
            </div>
            <div class="d-flex gap-3">
              <button type="button" class="btn btn-label-secondary px-4 py-2" data-bs-dismiss="modal">Nanti Saja</button>
              <button type="button" id="btn-start-update" class="btn btn-primary px-5 py-2 shadow-lg">
                <i class="ti tabler-rocket me-2"></i>
                Install Sekarang
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal Progress Update --}}
<div class="modal fade" id="modalProgressUpdate" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content glass-card border-0 shadow-none">
      <div class="modal-body p-5">
        <div class="text-center mb-5">
          <div class="avatar avatar-xl bg-label-primary mb-4 p-3 rounded-circle" style="width: 80px; height: 80px;">
            <i class="ti tabler-cloud-download fs-1"></i>
          </div>
          <h3 class="text-white mb-2">Menginstall Pembaruan</h3>
          <p class="text-muted">Jangan tutup halaman ini hingga proses selesai...</p>
        </div>

        <div class="progress mb-5" style="height: 10px; border-radius: 20px; background: rgba(255,255,255,0.05);">
          <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%" id="update-progress"></div>
        </div>

        <div class="step-indicator" id="progress-steps">
          <div class="progress-step" data-step="1">
            <div class="progress-step-icon"><i class="ti tabler-download"></i></div>
            <div class="flex-grow-1">
              <div class="fw-bold text-white">Mengunduh Paket</div>
              <small class="text-muted">Mendownload file terbaru dari server...</small>
            </div>
            <div class="step-status"></div>
          </div>
          <div class="progress-step" data-step="2">
            <div class="progress-step-icon"><i class="ti tabler-folder"></i></div>
            <div class="flex-grow-1">
              <div class="fw-bold text-white">Sinkronisasi File</div>
              <small class="text-muted">Mengupdate komponen aplikasi...</small>
            </div>
            <div class="step-status"></div>
          </div>
          <div class="progress-step" data-step="3">
            <div class="progress-step-icon"><i class="ti tabler-database"></i></div>
            <div class="flex-grow-1">
              <div class="fw-bold text-white">Migrasi Database</div>
              <small class="text-muted">Memperbarui skema data...</small>
            </div>
            <div class="step-status"></div>
          </div>
          <div class="progress-step" data-step="4">
            <div class="progress-step-icon"><i class="ti tabler-bolt"></i></div>
            <div class="flex-grow-1">
              <div class="fw-bold text-white">Optimasi Sistem</div>
              <small class="text-muted">Membersihkan cache dan finalisasi...</small>
            </div>
            <div class="step-status"></div>
          </div>
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
  const btnStartUpdate = document.getElementById('btn-start-update');
  
  const modalConfirm = new bootstrap.Modal(document.getElementById('modalConfirmUpdate'));
  const modalProgress = new bootstrap.Modal(document.getElementById('modalProgressUpdate'));
  
  const progressBar = document.getElementById('update-progress');
  const progressSteps = document.querySelectorAll('.progress-step');

  // Handle Check Update
  if (btnCheckUpdate) {
    btnCheckUpdate.addEventListener('click', function() {
      const originalHtml = btnCheckUpdate.innerHTML;
      btnCheckUpdate.disabled = true;
      btnCheckUpdate.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memeriksa...';

      fetch("{{ route('admin.update.check') }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            title: data.update_available ? 'Update Tersedia!' : 'Sudah Terbaru',
            text: data.update_available ? 'Versi baru ditemukan, memuat detail...' : 'Sistem Anda sudah menggunakan versi terbaru.',
            icon: data.update_available ? 'info' : 'success',
            confirmButtonColor: '#7367f0'
          }).then(() => { if (data.update_available) window.location.reload(); });
        } else {
          throw new Error(data.message || 'Gagal memeriksa update');
        }
      })
      .catch(error => {
        Swal.fire('Error', error.message, 'error');
      })
      .finally(() => {
        btnCheckUpdate.disabled = false;
        btnCheckUpdate.innerHTML = originalHtml;
      });
    });
  }

  // Handle Open Modal Confirm
  if (btnRunUpdate) {
    btnRunUpdate.addEventListener('click', function() {
      console.log('Agen Dika: Membuka modal konfirmasi update...');
      const originalHtml = btnRunUpdate.innerHTML;
      btnRunUpdate.disabled = true;
      btnRunUpdate.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyiapkan...';

      fetch("{{ route('admin.update.data') }}", {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
      })
      .then(response => response.json())
      .then(data => {
        if (!data.success) throw new Error(data.message);
        
        const u = data.data;
        document.getElementById('modal-latest-version').textContent = 'v' + u.latest_version;
        document.getElementById('modal-release-date').textContent = u.release_date;
        document.getElementById('modal-changelog').textContent = u.changelog;

        // Render History
        const historyContainer = document.getElementById('history-container');
        if (u.changelog_history && u.changelog_history.length > 0) {
          historyContainer.innerHTML = u.changelog_history.reverse().map(h => `
            <div class="history-item">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-bold text-white small">v${h.version}</span>
                <span class="text-muted" style="font-size: 10px;">${new Date(h.date).toLocaleDateString()}</span>
              </div>
              <div class="text-muted small text-truncate">${h.changelog}</div>
            </div>
          `).join('');
        } else {
          historyContainer.innerHTML = '<div class="text-muted small p-3 text-center">Belum ada riwayat update.</div>';
        }

        modalConfirm.show();
      })
      .catch(error => {
        Swal.fire('Error', 'Gagal memuat data update: ' + error.message, 'error');
      })
      .finally(() => {
        btnRunUpdate.disabled = false;
        btnRunUpdate.innerHTML = originalHtml;
      });
    });
  }

  // Handle Start Real Update
  if (btnStartUpdate) {
    btnStartUpdate.addEventListener('click', function() {
      modalConfirm.hide();
      modalProgress.show();
      
      startUpdateAnimation();
      
      fetch("{{ route('admin.update.run') }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          completeUpdate(true);
        } else {
          throw new Error(data.message);
        }
      })
      .catch(error => {
        completeUpdate(false, error.message);
      });
    });
  }

  function startUpdateAnimation() {
    let progress = 0;
    const interval = setInterval(() => {
      if (progress < 92) {
        progress += Math.random() * 5;
        updateUI(Math.min(progress, 92));
      }
    }, 1000);
    window.updateInterval = interval;
  }

  function updateUI(p) {
    progressBar.style.width = p + '%';
    
    progressSteps.forEach((step, index) => {
      const stepNum = index + 1;
      const threshold = stepNum * 23;
      
      step.classList.remove('active', 'completed');
      if (p >= threshold) {
        step.classList.add('completed');
        step.querySelector('.step-status').innerHTML = '<i class="ti tabler-check text-success"></i>';
      } else if (p >= threshold - 23) {
        step.classList.add('active');
        step.querySelector('.step-status').innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span>';
      }
    });
  }

  function completeUpdate(success, message = '') {
    clearInterval(window.updateInterval);
    
    if (success) {
      progressBar.style.width = '100%';
      progressSteps.forEach(s => {
        s.classList.add('completed');
        s.querySelector('.step-status').innerHTML = '<i class="ti tabler-check text-success"></i>';
      });

      setTimeout(() => {
        Swal.fire({
          title: 'Pembaruan Berhasil!',
          text: 'Sistem telah diperbarui ke versi terbaru.',
          icon: 'success',
          confirmButtonColor: '#28c76f'
        }).then(() => {
          window.location.href = "{{ route('admin.update.index') }}";
        });
      }, 1000);
    } else {
      modalProgress.hide();
      Swal.fire('Gagal Memperbarui', message, 'error');
    }
  }
});
</script>
@endsection
