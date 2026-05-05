@extends('layouts/layoutMaster')

@section('title', 'Pembaruan Sistem')

@section('page-style')
  <style>
    .update-hero {
      position: relative;
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
      border-radius: 16px;
      padding: 40px;
      overflow: hidden;
      margin-bottom: 24px;
    }

    .update-hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
      opacity: 0.5;
    }

    .update-hero .hero-content {
      position: relative;
      z-index: 1;
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
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 16px;
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
    }

    .update-card:hover {
      border-color: rgba(115, 103, 240, 0.3);
      box-shadow: 0 8px 32px rgba(115, 103, 240, 0.15);
    }

    .changelog-card {
      background: linear-gradient(135deg, rgba(115, 103, 240, 0.08) 0%, rgba(16, 185, 129, 0.05) 100%);
      border: 1px solid rgba(115, 103, 240, 0.15);
      border-radius: 12px;
      padding: 20px;
    }

    .changelog-item {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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
      position: relative;
      overflow: hidden;
      padding: 14px 32px;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
    }

    .update-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s ease;
    }

    .update-btn:hover::before {
      left: 100%;
    }

    .info-row {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.5);
    }

    .info-row i {
      font-size: 1rem;
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
    <div class="update-hero">
      <div class="hero-content d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h3 class="text-white mb-2" style="font-weight: 700;">Pembaruan Sistem</h3>
          <p class="text-white-50 mb-0">Periksa dan install versi terbaru aplikasi</p>
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
                <div class="avatar avatar-lg bg-label-warning rounded-3">
                  <i class="ti tabler-cloud-download fs-2"></i>
                </div>
                <div>
                  <h5 class="mb-1">Update Tersedia!</h5>
                  <p class="text-muted mb-0">Versi {{ $updateInfo['latest_version'] }} siap untuk diinstall</p>
                </div>
              </div>

              <div class="new-version-badge">
                <i class="ti tabler-rocket"></i>
                New Version {{ $updateInfo['latest_version'] }}
              </div>

              <div class="changelog-card mt-4">
                <h6 class="mb-3 d-flex align-items-center gap-2">
                  <i class="ti tabler-list-details text-primary"></i>
                  Changelog
                </h6>
                <div class="changelog-content" style="white-space: pre-line; font-size: 0.9rem; color: rgba(255,255,255,0.8);">
                  {{ $updateInfo['changelog'] }}
                </div>
              </div>

              <div class="mt-4">
                <button type="button" id="btn-run-update" class="btn btn-primary update-btn">
                  <i class="ti tabler-download me-2"></i>
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
              <h6 class="mb-3 d-flex align-items-center gap-2">
                <i class="ti tabler-info-circle text-info"></i>
                Info Sistem
              </h6>
              <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Versi Saat Ini</span>
                  <span class="fw-semibold">{{ $currentVersion }}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Versi Terbaru</span>
                  <span class="fw-semibold text-success">{{ $updateInfo['latest_version'] }}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Terakhir Dicek</span>
                  <span class="">{{ $updateInfo['last_check'] ?? '-' }}</span>
                </div>
              </div>
              <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
              <button type="button" id="btn-check-update" class="btn btn-outline-primary w-100">
                <i class="ti tabler-refresh me-2"></i>
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
            <button type="button" id="btn-check-update" class="btn btn-outline-primary update-btn">
              <i class="ti tabler-refresh me-2"></i>
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
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const btnCheckUpdate = document.getElementById('btn-check-update');
  const btnRunUpdate = document.getElementById('btn-run-update');
  const modalProgress = new bootstrap.Modal(document.getElementById('modalProgressUpdate'));
  const progressBar = document.getElementById('update-progress');
  const progressSteps = document.querySelectorAll('.progress-step');

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

      fetch("{{ route('admin.update.check') }}", {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            title: data.update_available ? 'Update Tersedia!' : 'Sudah Terbaru',
            text: data.update_available ? 'Versi baru ditemukan, halaman akan di-refresh.' : 'Sistem Anda sudah menggunakan versi terbaru.',
            icon: data.update_available ? 'info' : 'success',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.reload();
          });
        } else {
          throw new Error(data.message || 'Gagal memeriksa update');
        }
      })
      .catch(error => {
        Swal.fire('Error', error.message, 'error');
        btnCheckUpdate.disabled = false;
        btnCheckUpdate.innerHTML = '<i class="ti tabler-refresh me-2"></i> Periksa Pembaruan';
      });
    });
  }

  if (btnRunUpdate) {
    btnRunUpdate.onclick = function() {
      console.log('Tombol Install diklik');
      
      if (typeof Swal === 'undefined') {
        if (confirm('Konfirmasi Pembaruan: Sistem akan diperbarui ke versi terbaru. Lanjutkan?')) {
          startUpdateProcess();
        }
        return;
      }

      Swal.fire({
        title: 'Konfirmasi Pembaruan',
        html: `
          <div class="text-start">
            <p class="mb-2">Sistem akan diperbarui ke versi terbaru.</p>
            <div class="alert alert-warning mb-0">
              <i class="ti tabler-alert-triangle me-2"></i>
              Pastikan Anda telah melakukan backup database sebelum melanjutkan.
            </div>
          </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#7367f0',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Perbarui!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          startUpdateProcess();
        }
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
          Swal.fire({
            title: 'Berhasil!',
            text: data.message,
            icon: 'success',
            confirmButtonText: 'Selesai'
          }).then(() => {
            window.location.href = "{{ route('admin.update.index') }}";
          });
        }, 1500);
      } else {
        throw new Error(data.message || 'Gagal menjalankan update');
      }
    })
    .catch(error => {
      clearInterval(interval);
      modalProgress.hide();
      Swal.fire('Gagal', error.message, 'error');
    });
  }
});
</script>
@endsection