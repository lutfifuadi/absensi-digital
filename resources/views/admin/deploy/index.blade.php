@extends('layouts/layoutMaster')

@section('title', 'Update Aplikasi')

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

    .deploy-card {
      background: var(--das-surface);
      border: 1px solid var(--das-border);
      border-radius: var(--das-radius);
      backdrop-filter: blur(6px);
      transition: all 0.3s ease;
    }

    .deploy-card:hover {
      border-color: rgba(115, 103, 240, 0.3);
      box-shadow: 0 8px 32px rgba(115, 103, 240, 0.15);
    }

    .status-card {
      background: var(--das-surface);
      border: 1px solid var(--das-border);
      border-radius: var(--das-radius);
      padding: 1.25rem;
      transition: all 0.3s ease;
    }

    .status-card:hover {
      border-color: var(--das-border-hover);
      transform: translateY(-2px);
    }

    .status-card__icon {
      width: 42px;
      height: 42px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      font-size: 1.25rem;
      flex-shrink: 0;
    }

    .status-card__label {
      font-size: 0.75rem;
      color: rgba(255, 255, 255, 0.5);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .status-card__value {
      font-size: 1.1rem;
      font-weight: 700;
      color: #fff;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      display: inline-block;
    }

    .status-dot.active {
      background: #28c76f;
      box-shadow: 0 0 8px rgba(40, 199, 111, 0.6);
      animation: pulse-dot 2s infinite;
    }

    .status-dot.inactive {
      background: #ea5455;
      box-shadow: 0 0 8px rgba(234, 84, 85, 0.6);
    }

    @keyframes pulse-dot {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .progress-area {
      background: var(--das-surface);
      border: 1px solid var(--das-border);
      border-radius: var(--das-radius);
      padding: 1.5rem;
      transition: all 0.3s ease;
    }

    .progress-log-item {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 8px 0;
      font-size: 0.85rem;
      border-bottom: 1px solid var(--das-border);
    }

    .progress-log-item:last-child {
      border-bottom: none;
    }

    .progress-log-icon {
      width: 22px;
      height: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 0.8rem;
    }

    .history-table {
      font-size: 0.85rem;
    }

    .history-table th {
      font-weight: 600;
      color: rgba(255, 255, 255, 0.5);
      text-transform: uppercase;
      font-size: 0.7rem;
      letter-spacing: 0.5px;
      border-bottom: 1px solid var(--das-border) !important;
    }

    .history-table td {
      border-bottom: 1px solid var(--das-border);
      vertical-align: middle;
    }

    .rollback-btn {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.35rem 0.75rem;
      border-radius: var(--das-radius);
      border: 1px solid rgba(234, 84, 85, 0.3);
      background: rgba(234, 84, 85, 0.1);
      color: #ea5455;
      cursor: pointer;
      transition: all 0.18s ease;
    }

    .rollback-btn:hover {
      background: rgba(234, 84, 85, 0.2);
      border-color: #ea5455;
    }

    .deploy-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.8rem;
      font-weight: 600;
      padding: 0.6rem 1.25rem;
      border-radius: var(--das-radius);
      border: none;
      cursor: pointer;
      transition: all 0.18s ease;
    }

    .deploy-btn:hover {
      transform: translateY(-2px);
    }

    .deploy-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none !important;
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
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-cloud-upload text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Sistem / Update Aplikasi
          </div>
          <h4 class="das-hero__title text-gradient-gold">Update Aplikasi</h4>
          <p class="das-hero__subtitle">Panel kontrol pembaruan aplikasi</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <span class="badge bg-label-info d-flex align-items-center gap-1 px-3 py-2" style="font-size:0.75rem;">
          <i class="ti tabler-shield-check"></i> Super Admin
        </span>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       STATUS CARDS
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="status-card h-100" id="card-queue">
        <div class="d-flex align-items-center gap-3">
          <div class="status-card__icon" style="background: rgba(40, 199, 111, 0.15);">
            <span class="status-dot active" id="queue-dot"></span>
          </div>
          <div>
            <div class="status-card__label">Queue</div>
            <div class="status-card__value" id="queue-value">Memuat...</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="status-card h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="status-card__icon" style="background: var(--das-info-soft); color: var(--das-info);">
            <i class="ti tabler-tag"></i>
          </div>
          <div>
            <div class="status-card__label">Versi Saat Ini</div>
            <div class="status-card__value" id="version-current">Memuat...</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="status-card h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="status-card__icon" style="background: var(--das-warning-soft); color: var(--das-warning);">
            <i class="ti tabler-git-commit"></i>
          </div>
          <div>
            <div class="status-card__label">Commit</div>
            <div class="status-card__value" id="version-commit">Memuat...</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="status-card h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="status-card__icon" style="background: var(--das-primary-soft); color: var(--das-primary);">
            <i class="ti tabler-git-branch"></i>
          </div>
          <div>
            <div class="status-card__label">Branch</div>
            <div class="status-card__value" id="version-branch">Memuat...</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       ACTION PANEL
  ═══════════════════════════════════════════════════════ --}}
  <div class="deploy-card p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <h6 class="mb-1 d-flex align-items-center gap-2" style="color: #fff;">
          <i class="ti tabler-rocket text-info"></i>
          Aksi Update
        </h6>
        <p class="mb-0" style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">
          Periksa atau jalankan pembaruan aplikasi
        </p>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="deploy-btn" id="btn-check-status"
          style="background: rgba(255,255,255,0.06); border: 1px solid var(--das-border); color: rgba(255,255,255,0.8);"
          onclick="checkStatus()">
          <i class="ti tabler-refresh"></i>
          Periksa Update
        </button>
        <button type="button" class="deploy-btn" id="btn-deploy"
          style="background: linear-gradient(135deg, #28c76f, #20b863); color: #fff; box-shadow: 0 4px 15px rgba(40,199,111,0.35);"
          onclick="confirmDeploy()">
          <i class="ti tabler-cloud-upload"></i>
          Update Aplikasi
        </button>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       PROGRESS AREA (hidden by default)
  ═══════════════════════════════════════════════════════ --}}
  <div class="progress-area mb-4 d-none" id="progress-area">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h6 class="mb-0 d-flex align-items-center gap-2" style="color: #fff;">
        <i class="ti tabler-progress-check text-info"></i>
        Progress Update
      </h6>
      <span class="badge bg-label-warning" id="progress-percentage">0%</span>
    </div>

    <div class="progress mb-4" style="height: 8px; background: rgba(255,255,255,0.08);">
      <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
        style="width: 0%; background: linear-gradient(90deg, #7367f0, #9e95f5);" id="progress-bar">
      </div>
    </div>

    <div id="progress-logs">
      <div class="progress-log-item">
        <span class="progress-log-icon text-secondary"><i class="ti tabler-circle"></i></span>
        <span style="color: rgba(255,255,255,0.5);">Menunggu proses dimulai...</span>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       HISTORY TABLE
  ═══════════════════════════════════════════════════════ --}}
  <div class="deploy-card">
    <div class="p-4 border-bottom" style="border-color: var(--das-border) !important;">
      <h6 class="mb-0 d-flex align-items-center gap-2" style="color: #fff;">
        <i class="ti tabler-history text-info"></i>
        Riwayat Update
      </h6>
    </div>
    <div class="table-responsive">
      <table class="table history-table mb-0">
        <thead>
          <tr>
            <th style="width: 50px;">#</th>
            <th>Versi</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Durasi</th>
            <th>Oleh</th>
            <th style="width: 100px;">Aksi</th>
          </tr>
        </thead>
        <tbody id="history-body">
          @forelse($history as $log)
            <tr>
              <td style="color: rgba(255,255,255,0.4);">{{ $loop->iteration }}</td>
              <td style="color: #fff; font-weight: 600;">{{ $log->version ?? '-' }}</td>
              <td style="color: rgba(255,255,255,0.7);">{{ $log->started_at?->format('d M Y H:i') ?? '-' }}</td>
              <td>
                @php
                  $statusBadge = match($log->status) {
                    'completed' => 'bg-label-success',
                    'failed' => 'bg-label-danger',
                    'running' => 'bg-label-warning',
                    default => 'bg-label-secondary',
                  };
                  $statusIcon = match($log->status) {
                    'completed' => 'tabler-check',
                    'failed' => 'tabler-x',
                    'running' => 'tabler-refresh',
                    default => 'tabler-minus',
                  };
                @endphp
                <span class="badge {{ $statusBadge }} d-flex align-items-center gap-1" style="width: fit-content;">
                  <i class="ti {{ $statusIcon }} fs-6"></i>
                  {{ ucfirst($log->status) }}
                </span>
              </td>
              <td style="color: rgba(255,255,255,0.7);">
                @if($log->duration_seconds)
                  {{ $log->duration_seconds }} dtk
                @else
                  -
                @endif
              </td>
              <td style="color: rgba(255,255,255,0.7);">{{ $log->trigger?->name ?? 'Sistem' }}</td>
              <td>
                @if($log->status === 'failed' && $log->backup_path)
                  <button class="rollback-btn" onclick="rollbackDeploy({{ $log->id }})">
                    <i class="ti tabler-rotate-2"></i> Rollback
                  </button>
                @else
                  <span style="color: rgba(255,255,255,0.2); font-size: 0.75rem;">-</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-4" style="color: rgba(255,255,255,0.4);">
                <i class="ti tabler-inbox" style="font-size: 1.5rem; display: block; margin-bottom: 8px;"></i>
                Belum ada riwayat update
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       MODAL KONFIRMASI DEPLOY
  ═══════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalDeploy" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head">
          <div class="das-modal-icon-circle" style="background: rgba(255, 159, 67, 0.15); border-color: rgba(255, 159, 67, 0.3);">
            <i class="ti tabler-alert-triangle text-warning" style="font-size: 1.5rem;"></i>
          </div>
          <h5>Konfirmasi Update</h5>
          <small style="color: rgba(255,255,255,0.5);">Backup database akan dibuat otomatis</small>
        </div>
        <div class="das-modal-body">
          <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">Yakin ingin memperbarui aplikasi ke versi terbaru?</p>
          <div class="alert d-flex align-items-center gap-2" style="background: rgba(0, 207, 232, 0.1); border: 1px solid rgba(0, 207, 232, 0.2); border-radius: var(--das-radius); padding: 12px;">
            <i class="ti tabler-info-circle text-info"></i>
            <span style="font-size: 0.85rem; color: rgba(255,255,255,0.7);">Backup DB akan disimpan sebelum update</span>
          </div>
          <input type="password" id="deployPassword" class="form-control das-input mt-3" placeholder="Masukkan password Anda">
          <div id="deployError" class="text-danger small mt-1 d-none">Password salah</div>
        </div>
        <div class="das-modal-footer">
          <button class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-warning" id="btnMulaiUpdate" onclick="startDeploy()">
            <i class="ti tabler-cloud-upload"></i> Mulai Update
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       TOAST NOTIFICATION
  ═══════════════════════════════════════════════════════ --}}
  <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

@endsection

@section('page-script')
<script>
  const csrfToken = '{{ csrf_token() }}';
  let deployPolling = null;
  let deployModal = null;

  document.addEventListener('DOMContentLoaded', function() {
    deployModal = new bootstrap.Modal(document.getElementById('modalDeploy'));

    checkStatus();
    setInterval(checkStatus, 30000);

    @if($isRunning)
      showProgressArea();
      deployPolling = setInterval(checkProgress, 2000);
    @endif
  });

  function checkStatus() {
    fetch('{{ route("admin.deploy.status") }}')
      .then(r => r.json())
      .then(data => {
        updateQueueCard(data.queue_running);
        document.getElementById('version-current').textContent = data.current_version || 'N/A';

        const commitEl = document.getElementById('version-commit');
        commitEl.textContent = data.current_version
          ? data.current_version.substring(0, 7)
          : 'N/A';

        document.getElementById('version-branch').textContent = 'main';

        const btnDeploy = document.getElementById('btn-deploy');
        if (data.can_deploy) {
          btnDeploy.disabled = false;
          btnDeploy.style.background = 'linear-gradient(135deg, #28c76f, #20b863)';
          btnDeploy.style.cursor = 'pointer';
        } else {
          btnDeploy.disabled = true;
          btnDeploy.style.background = 'rgba(255,255,255,0.08)';
          btnDeploy.style.cursor = 'not-allowed';
        }
      })
      .catch(() => {
        document.getElementById('queue-value').textContent = 'Error';
      });
  }

  function updateQueueCard(running) {
    const dot = document.getElementById('queue-dot');
    const value = document.getElementById('queue-value');
    const card = document.getElementById('card-queue');

    if (running) {
      dot.className = 'status-dot active';
      value.textContent = 'Aktif';
      card.style.borderColor = 'rgba(40, 199, 111, 0.3)';
    } else {
      dot.className = 'status-dot inactive';
      value.textContent = 'Mati';
      card.style.borderColor = 'rgba(234, 84, 85, 0.3)';
    }
  }

  function confirmDeploy() {
    const btnDeploy = document.getElementById('btn-deploy');
    if (btnDeploy.disabled) return;

    document.getElementById('deployPassword').value = '';
    document.getElementById('deployError').classList.add('d-none');
    deployModal.show();
  }

  function startDeploy() {
    const password = document.getElementById('deployPassword').value;
    const btnMulai = document.getElementById('btnMulaiUpdate');
    btnMulai.disabled = true;
    btnMulai.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

    fetch('{{ route("admin.deploy.run") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ password })
    })
    .then(r => r.json())
    .then(data => {
      btnMulai.disabled = false;
      btnMulai.innerHTML = '<i class="ti tabler-cloud-upload"></i> Mulai Update';

      if (data.success) {
        deployModal.hide();
        showProgressArea();
        deployPolling = setInterval(checkProgress, 2000);
      } else {
        showError(data.message || 'Password salah');
      }
    })
    .catch(err => {
      btnMulai.disabled = false;
      btnMulai.innerHTML = '<i class="ti tabler-cloud-upload"></i> Mulai Update';
      showError('Terjadi kesalahan: ' + err.message);
    });
  }

  function showError(msg) {
    const errEl = document.getElementById('deployError');
    errEl.textContent = msg;
    errEl.classList.remove('d-none');
  }

  function showProgressArea() {
    document.getElementById('progress-area').classList.remove('d-none');
  }

  function checkProgress() {
    fetch('{{ route("admin.deploy.progress") }}')
      .then(r => r.json())
      .then(data => {
        if (data.status === 'running') {
          updateProgressBar(data.percentage);
          updateLogs(data.log);
        } else if (data.status === 'completed') {
          stopPolling();
          updateProgressBar(100);
          updateLogs(data.log);
          showToast('success', 'Update berhasil!');
          checkStatus();
          setTimeout(() => window.location.reload(), 2000);
        } else if (data.status === 'failed') {
          stopPolling();
          updateLogs(data.log);
          showToast('danger', 'Update gagal!');
        }
      })
      .catch(() => {});
  }

  function updateProgressBar(pct) {
    pct = Math.min(100, Math.max(0, pct));
    document.getElementById('progress-bar').style.width = pct + '%';
    document.getElementById('progress-percentage').textContent = Math.round(pct) + '%';
  }

  function updateLogs(logs) {
    const container = document.getElementById('progress-logs');
    if (!logs || !logs.length) return;

    container.innerHTML = logs.map(log => {
      let icon = '<i class="ti tabler-circle" style="color: rgba(255,255,255,0.2);"></i>';
      let textColor = 'rgba(255,255,255,0.4)';

      switch (log.status) {
        case 'completed':
          icon = '<i class="ti tabler-check-circle" style="color: #28c76f;"></i>';
          textColor = 'rgba(255,255,255,0.7)';
          break;
        case 'running':
          icon = '<i class="ti tabler-loader spinner-border spinner-border-sm" style="color: #ff9f43;"></i>';
          textColor = '#ff9f43';
          break;
        case 'pending':
          icon = '<i class="ti tabler-circle" style="color: rgba(255,255,255,0.15);"></i>';
          textColor = 'rgba(255,255,255,0.3)';
          break;
        case 'failed':
          icon = '<i class="ti tabler-x-circle" style="color: #ea5455;"></i>';
          textColor = '#ea5455';
          break;
      }

      return `<div class="progress-log-item">
        <span class="progress-log-icon">${icon}</span>
        <span style="color: ${textColor};">${log.message || log}</span>
      </div>`;
    }).join('');
  }

  function stopPolling() {
    if (deployPolling) {
      clearInterval(deployPolling);
      deployPolling = null;
    }
    const btnDeploy = document.getElementById('btn-deploy');
    btnDeploy.disabled = false;
    btnDeploy.style.background = 'linear-gradient(135deg, #28c76f, #20b863)';
    btnDeploy.style.cursor = 'pointer';
  }

  function rollbackDeploy(id) {
    const password = prompt('Masukkan password Anda untuk konfirmasi rollback:');
    if (!password) return;

    fetch('/admin/deploy/' + id + '/rollback', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ password: password })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('success', data.message || 'Rollback berhasil');
        setTimeout(() => window.location.reload(), 1500);
      } else {
        showToast('danger', data.message || 'Rollback gagal');
      }
    })
    .catch(err => {
      showToast('danger', 'Terjadi kesalahan: ' + err.message);
    });
  }

  function showToast(type, message) {
    const container = document.getElementById('toast-container');
    const bgColor = type === 'success' ? '#28c76f' : type === 'danger' ? '#ea5455' : '#ff9f43';
    const icon = type === 'success' ? 'tabler-check-circle' : type === 'danger' ? 'tabler-alert-circle' : 'tabler-alert-triangle';

    const toast = document.createElement('div');
    toast.style.cssText = `
      background: rgba(15, 23, 42, 0.95);
      backdrop-filter: blur(12px);
      border: 1px solid ${bgColor}44;
      border-left: 4px solid ${bgColor};
      border-radius: 5px;
      padding: 12px 16px;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 10px;
      color: #fff;
      font-size: 0.85rem;
      box-shadow: 0 8px 32px rgba(0,0,0,0.3);
      animation: slideIn 0.3s ease;
      min-width: 280px;
    `;
    toast.innerHTML = `<i class="ti ${icon}" style="color: ${bgColor}; font-size: 1.1rem;"></i> ${message}`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }

  // Inject slideIn keyframe
  const styleSheet = document.createElement('style');
  styleSheet.textContent = `@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }`;
  document.head.appendChild(styleSheet);
</script>
@endsection
