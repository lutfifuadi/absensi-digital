@extends('layouts/layoutFront')

@section('title', 'Cek Status Pengaduan')

@section('content')
<style>
  .rounded-2, .form-control, .form-select, .btn, .card, .modal-content, .alert, .badge {
    border-radius: 5px !important;
  }
  .form-control:focus, .form-select:focus {
    border-color: #696cff !important;
    box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25) !important;
  }
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
  .spin-animation {
    animation: spin 1.5s linear infinite;
    display: inline-block;
  }
  @keyframes shimmerAnimation {
    0% {
      background-position: -200% 0;
    }
    100% {
      background-position: 200% 0;
    }
  }
  .shimmer-element {
    background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
    background-size: 200% 100%;
    animation: shimmerAnimation 1.5s infinite linear;
    border-radius: 5px !important;
    display: block;
  }
  /* Status Badges */
  .badge-status-baru {
    background-color: rgba(255, 159, 67, 0.15) !important;
    color: #ff9f43 !important;
    border: 1px solid rgba(255, 159, 67, 0.3) !important;
  }
  .badge-status-diproses {
    background-color: rgba(105, 108, 255, 0.15) !important;
    color: #8286ff !important;
    border: 1px solid rgba(105, 108, 255, 0.3) !important;
  }
  .badge-status-selesai {
    background-color: rgba(40, 199, 111, 0.15) !important;
    color: #28c76f !important;
    border: 1px solid rgba(40, 199, 111, 0.3) !important;
  }
  .badge-status-ditolak {
    background-color: rgba(234, 84, 85, 0.15) !important;
    color: #ea5455 !important;
    border: 1px solid rgba(234, 84, 85, 0.3) !important;
  }
  /* Pelapor Badges */
  .badge-pelapor-siswa {
    background-color: rgba(105, 108, 255, 0.15) !important;
    color: #8286ff !important;
    border: 1px solid rgba(105, 108, 255, 0.3) !important;
    border-radius: 5px !important;
  }
  .badge-pelapor-ortu {
    background-color: rgba(255, 159, 67, 0.15) !important;
    color: #ff9f43 !important;
    border: 1px solid rgba(255, 159, 67, 0.3) !important;
    border-radius: 5px !important;
  }
  /* Timeline dots */
  .timeline-dot-baru {
    width: 32px;
    height: 32px;
    background: rgba(255, 159, 67, 0.15);
    border: 1px solid rgba(255, 159, 67, 0.3);
    color: #ff9f43;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px !important;
  }
  .timeline-dot-diproses {
    width: 32px;
    height: 32px;
    background: rgba(105, 108, 255, 0.15);
    border: 1px solid rgba(105, 108, 255, 0.3);
    color: #8286ff;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px !important;
  }
  .timeline-dot-selesai {
    width: 32px;
    height: 32px;
    background: rgba(40, 199, 111, 0.15);
    border: 1px solid rgba(40, 199, 111, 0.3);
    color: #28c76f;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px !important;
  }
  .timeline-dot-ditolak {
    width: 32px;
    height: 32px;
    background: rgba(234, 84, 85, 0.15);
    border: 1px solid rgba(234, 84, 85, 0.3);
    color: #ea5455;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px !important;
  }
</style>

{{-- Hero Section --}}
<section class="text-white text-center pt-5 pb-5 position-relative overflow-hidden" style="background:linear-gradient(135deg,#0f172a,#1a2744,#0f172a); padding-top: 7rem !important; padding-bottom: 8rem !important;">
  <!-- Background decorative gradients -->
  <div class="position-absolute rounded-circle" style="top:-50%; left:-20%; width: 50%; height: 200%; background: radial-gradient(circle, rgba(105, 108, 255, 0.08) 0%, rgba(105, 108, 255, 0) 70%); filter: blur(50px); pointer-events: none; z-index: 1;"></div>
  <div class="position-absolute rounded-circle" style="top:-50%; right:-20%; width: 50%; height: 200%; background: radial-gradient(circle, rgba(234, 84, 85, 0.06) 0%, rgba(234, 84, 85, 0) 70%); filter: blur(50px); pointer-events: none; z-index: 1;"></div>
  <!-- Dot pattern overlay -->
  <div class="position-absolute" style="top:0; left:0; width:100%; height:100%; background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 0); background-size: 24px 24px; pointer-events: none; z-index: 1; opacity: 0.6;"></div>

  <div class="container position-relative" style="z-index: 2;">
  </div>
</section>

{{-- Main Section --}}
<section style="padding-bottom:4rem; background-color:#0f172a;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-9 col-lg-6">

        {{-- Search Card --}}
        <div class="card border-0 p-4 position-relative" style="margin-top: -11rem !important; background-color: #1e293b !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; border-radius: 5px !important; z-index:2; box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25) !important;">
          <form id="cekForm" novalidate>
            @csrf
            <div class="mb-0">
              <label for="kode_unik" class="form-label fw-semibold text-secondary">
                <i class="ti tabler-key me-1"></i> Kode Unik
              </label>
              <div class="input-group search-input-group">
                <input type="text" class="form-control" id="kode_unik" name="kode"
                       placeholder="Masukkan kode unik..." required
                       autocomplete="off"
                       style="text-transform:uppercase;letter-spacing:1px;font-family:monospace; padding: 0.6rem 0.8rem; border-radius: 5px 0 0 5px !important;">
                <button class="btn text-white px-4 fw-semibold" type="submit" id="cekBtn" style="background-color: #696cff; border-color: #696cff; border-radius: 0 5px 5px 0 !important;">
                  <span id="cekText">
                    <i class="ti tabler-search me-1"></i> Cek Status
                  </span>
                  <span id="cekLoading" style="display:none;">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                    Mencari...
                  </span>
                </button>
              </div>
              <div class="invalid-feedback" id="kode_unik-error">Silakan masukkan kode unik pengaduan</div>
              <small class="text-muted mt-2 d-block small">
                <i class="ti tabler-info-circle me-1 text-info"></i>
                Kode unik dikirimkan ke WhatsApp Anda saat pengaduan berhasil dibuat
              </small>
            </div>
          </form>
        </div>

        {{-- Loading Skeleton --}}
        <div id="loadingSkeleton" style="display:none;" class="mt-4" aria-hidden="true">
          <div class="card border-0 overflow-hidden" style="background-color: #1e293b !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25) !important; border-radius: 5px !important;">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="shimmer-element" style="width: 35%; height: 24px;"></span>
                <span class="shimmer-element" style="width: 25%; height: 28px;"></span>
              </div>
              <div class="row g-3 mb-4">
                <div class="col-sm-6"><span class="shimmer-element" style="width: 100%; height: 64px;"></span></div>
                <div class="col-sm-6"><span class="shimmer-element" style="width: 100%; height: 64px;"></span></div>
                <div class="col-sm-6"><span class="shimmer-element" style="width: 100%; height: 64px;"></span></div>
                <div class="col-sm-6"><span class="shimmer-element" style="width: 100%; height: 64px;"></span></div>
              </div>
              <span class="shimmer-element" style="width: 100%; height: 80px;"></span>
            </div>
          </div>
        </div>

        {{-- Result Container --}}
        <div id="resultContainer"></div>

        {{-- Quick Links --}}
        <div class="text-center mt-4">
          <p class="text-muted small mb-2">Belum punya kode? Buat pengaduan baru</p>
          <a href="{{ route('pengaduan.form') }}" class="btn btn-outline-primary btn-sm rounded-2 px-4 fw-semibold">
            <i class="ti tabler-flag me-1"></i> Laporkan Data Tidak Valid
          </a>
        </div>

      </div>
    </div>
  </div>
</section>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('cekForm');
  const cekBtn = document.getElementById('cekBtn');
  const cekText = document.getElementById('cekText');
  const cekLoading = document.getElementById('cekLoading');
  const resultContainer = document.getElementById('resultContainer');
  const loadingSkeleton = document.getElementById('loadingSkeleton');
  const kodeInput = document.getElementById('kode_unik');

  // ── Status display config ──
  const statusConfig = {
    'baru':     { label: 'Baru',     class: 'badge-status-baru',     dotClass: 'timeline-dot-baru',     icon: 'tabler-clock' },
    'diproses': { label: 'Diproses', class: 'badge-status-diproses', dotClass: 'timeline-dot-diproses', icon: 'tabler-loader-2' },
    'selesai':  { label: 'Selesai',  class: 'badge-status-selesai',  dotClass: 'timeline-dot-selesai',  icon: 'tabler-check' },
    'ditolak':  { label: 'Ditolak',  class: 'badge-status-ditolak',  dotClass: 'timeline-dot-ditolak',  icon: 'tabler-x' },
  };

  function getStatusInfo(status) {
    return statusConfig[status] || { label: status, class: 'badge-status-baru', dotClass: 'timeline-dot-baru', icon: 'tabler-clock' };
  }

  // ── Status pelapor mapping ──
  const statusPelaporMap = {
    'siswa': 'Siswa',
    'orang_tua': 'Orang Tua/Wali',
  };

  // ── Format date ──
  function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  // ── Build result HTML ──
  function buildResultHTML(data, logs) {
    const statusInfo = getStatusInfo(data.status);

    // Build timeline from logs using custom timeline component
    let timelineHTML = '';
    if (logs && logs.length > 0) {
      timelineHTML += `<div class="timeline-container ps-2 mt-2">`;
      logs.forEach((item, index) => {
        const itemStatus = getStatusInfo(item.status_ke);
        const isCurrent = item.status_ke === data.status && index === logs.length - 1;
        const spinClass = item.status_ke === 'diproses' ? 'spin-animation' : '';

        timelineHTML += `
          <div class="d-flex gap-3 mb-4 position-relative">
            ${index < logs.length - 1 ? `<div class="position-absolute" style="left: 15px; top: 32px; bottom: -32px; width: 2px; border-left: 2px solid rgba(255, 255, 255, 0.1);"></div>` : ''}
            <div class="flex-shrink-0 d-flex align-items-center justify-content-center ${itemStatus.dotClass}" style="width: 32px; height: 32px; z-index: 1;">
              <i class="ti ${itemStatus.icon} ${spinClass} fs-5"></i>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                <span class="badge ${itemStatus.class} px-2 py-1 small" style="font-size: 0.75rem;">${itemStatus.label}</span>
                <small class="text-muted text-end" style="font-size: 0.75rem;">${formatDate(item.created_at)}</small>
              </div>
              ${item.diubah_oleh && item.diubah_oleh !== 'sistem' ? `
                <div class="d-flex align-items-center gap-1 mb-2">
                  <small class="text-muted" style="font-size: 0.75rem;"><i class="ti tabler-user-circle me-1"></i>oleh ${item.diubah_oleh}</small>
                </div>
              ` : ''}
              ${item.catatan ? `
                <div class="rounded-2 p-2 mt-1 small" style="font-size: 0.85rem; background-color: #0f172a !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; border-left: 2px solid #cbd5e1 !important; color: #cbd5e1 !important; border-radius: 0 5px 5px 0 !important;">
                  <i class="ti tabler-message-2 me-1 text-muted"></i>${item.catatan}
                </div>
              ` : ''}
            </div>
          </div>
        `;
      });
      timelineHTML += `</div>`;
    }

    // Admin note from catatan_admin field (latest note)
    let adminNoteHTML = '';
    if (data.catatan_admin) {
      adminNoteHTML = `
        <div class="mt-4 p-3" style="background-color: #0f172a !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; border-left: 3px solid #ff9f43 !important; border-radius: 0 5px 5px 0 !important; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.01);">
          <div class="d-flex align-items-start gap-2">
            <i class="ti tabler-message-report mt-1 flex-shrink-0 fs-5" style="color: #ff9f43;"></i>
            <div>
              <div class="fw-semibold small mb-1" style="color: #ff9f43;">Catatan Admin</div>
              <p class="mb-0 small text-secondary lh-base" style="color: #cbd5e1 !important;">${data.catatan_admin}</p>
            </div>
          </div>
        </div>
      `;
    }

    const statusPelaporLabel = statusPelaporMap[data.status_pelapor] || data.status_pelapor || '—';
    const statusPelaporBadge = data.status_pelapor === 'siswa' ? 'badge-pelapor-siswa' : 'badge-pelapor-ortu';

    return `
      <div class="card border-0 overflow-hidden mt-4" style="animation:fadeSlideUp 0.4s ease; background-color: #1e293b !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25) !important; border-radius: 5px !important;">
        <style>@keyframes fadeSlideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}</style>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-4 border-bottom" style="border-color: rgba(255, 255, 255, 0.08) !important; background: linear-gradient(135deg, #1e293b, #0f172a) !important; color: #ffffff !important;">
          <div>
            <small class="text-muted text-uppercase small lh-1" style="letter-spacing:0.5px; font-size: 0.75rem;">Kode Tracking</small>
            <div class="fs-5 fw-bold font-monospace" style="letter-spacing:2px;color:#ffffff !important;">
              ${data.kode_unik}
            </div>
          </div>
          <span class="badge ${statusInfo.class} fs-6 px-3 py-2">
            <i class="ti ${statusInfo.icon} me-1 ${data.status === 'diproses' ? 'spin-animation' : ''}"></i>
            ${data.status_label || statusInfo.label}
          </span>
        </div>

        <div class="card-body p-4">
          {{-- Info Grid --}}
          <div class="row row-cols-1 row-cols-sm-2 g-3 mb-4">
            <div class="col">
              <div class="p-3" style="background-color: #0f172a !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; border-radius: 5px !important;">
                <div class="small text-muted text-uppercase mb-1" style="letter-spacing:0.5px; font-size: 0.7rem;">Nama Pelapor</div>
                <div class="fw-semibold" style="color: #ffffff !important;">${data.nama_lengkap || '—'}</div>
              </div>
            </div>
            <div class="col">
              <div class="p-3" style="background-color: #0f172a !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; border-radius: 5px !important;">
                <div class="small text-muted text-uppercase mb-1" style="letter-spacing:0.5px; font-size: 0.7rem;">Status Pelapor</div>
                <div class="mt-1"><span class="badge ${statusPelaporBadge} px-2 py-1 small">${statusPelaporLabel}</span></div>
              </div>
            </div>
            <div class="col">
              <div class="p-3" style="background-color: #0f172a !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; border-radius: 5px !important;">
                <div class="small text-muted text-uppercase mb-1" style="letter-spacing:0.5px; font-size: 0.7rem;">Kategori</div>
                <div class="fw-semibold" style="color: #ffffff !important;">${data.kategori || '—'}</div>
              </div>
            </div>
            <div class="col">
              <div class="p-3" style="background-color: #0f172a !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; border-radius: 5px !important;">
                <div class="small text-muted text-uppercase mb-1" style="letter-spacing:0.5px; font-size: 0.7rem;">Tanggal Pengajuan</div>
                <div class="fw-semibold small" style="color: #ffffff !important;">${formatDate(data.created_at)}</div>
              </div>
            </div>
          </div>

          {{-- Deskripsi --}}
          <div class="mb-4">
            <label class="form-label fw-semibold small text-uppercase mb-2 text-secondary" style="letter-spacing:0.5px; font-size: 0.75rem;">
              <i class="ti tabler-file-text me-1 text-muted"></i> Deskripsi
            </label>
            <div class="p-3 small lh-lg" style="background-color: #0f172a !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; border-left: 3px solid #696cff !important; color: #cbd5e1 !important; border-radius: 0 5px 5px 0 !important;">
              ${data.deskripsi || '—'}
            </div>
          </div>

          {{-- Admin Note --}}
          ${adminNoteHTML}

          {{-- Timeline --}}
          ${timelineHTML ? `
            <div class="mt-4">
              <label class="form-label fw-semibold small text-uppercase mb-3 text-secondary" style="letter-spacing:0.5px; font-size: 0.75rem;">
                <i class="ti tabler-timeline me-1 text-muted"></i> Riwayat Status
              </label>
              ${timelineHTML}
            </div>
          ` : ''}
        </div>

        <div class="card-footer text-center py-3" style="background-color: #1e293b !important; border-top: 1px solid rgba(255, 255, 255, 0.08) !important; color: #94a3b8 !important;">
          <small class="text-muted small">
            <i class="ti tabler-brand-whatsapp me-1 text-success"></i>
            Update status akan dikirimkan ke WhatsApp Anda
          </small>
        </div>
      </div>
    `;
  }

  // ── Build not found HTML ──
  function buildNotFoundHTML() {
    return `
      <div class="card border-0 text-center mt-4" style="animation:fadeSlideUp 0.4s ease; background-color: #1e293b !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; color: #ffffff !important; box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25) !important; border-radius: 5px !important;">
        <style>@keyframes fadeSlideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}</style>
        <div class="card-body p-5">
          <div class="d-flex align-items-center justify-content-center mx-auto mb-4" style="width:80px; height:80px; background: linear-gradient(135deg, rgba(234,84,85,0.12), rgba(234,84,85,0.06)); border: 1px solid rgba(234, 84, 85, 0.2); border-radius: 5px !important; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);">
            <i class="ti tabler-search-off text-danger fs-1"></i>
          </div>
          <h5 class="fw-bold mb-2">Kode Unik Tidak Ditemukan</h5>
          <p class="text-muted mb-4 small" style="color: #94a3b8 !important;">
            Kode yang Anda masukkan tidak ditemukan dalam sistem.<br>
            Periksa kembali kode yang diterima melalui WhatsApp Anda.
          </p>
          <button class="btn btn-outline-primary px-4 fw-semibold" style="border-radius: 5px !important;"
                  onclick="document.getElementById('kode_unik').select();document.getElementById('kode_unik').focus();">
            <i class="ti tabler-reload me-1"></i> Coba Lagi
          </button>
        </div>
      </div>
    `;
  }

  // ── Submit handler ──
  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const kode = kodeInput.value.trim();

    if (!kode) {
      kodeInput.classList.add('is-invalid');
      kodeInput.focus();
      return;
    }
    kodeInput.classList.remove('is-invalid');

    // Show loading
    cekBtn.disabled = true;
    cekText.style.display = 'none';
    cekLoading.style.display = 'inline';
    resultContainer.innerHTML = '';
    loadingSkeleton.style.display = 'block';

    try {
      // API endpoint: GET /api/pengaduan/cek?kode=XXX
      const response = await fetch('/api/pengaduan/cek?kode=' + encodeURIComponent(kode), {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
      });

      const result = await response.json();

      loadingSkeleton.style.display = 'none';

      if (!response.ok) {
        if (response.status === 404) {
          resultContainer.innerHTML = buildNotFoundHTML();
        } else {
          throw new Error(result.message || 'Terjadi kesalahan');
        }
        return;
      }

      // Success — tampilkan data
      const data = result.data;
      const logs = result.logs || [];
      resultContainer.innerHTML = buildResultHTML(data, logs);

      // Scroll ke hasil
      resultContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

    } catch (error) {
      loadingSkeleton.style.display = 'none';

      if (error.message && (error.message.toLowerCase().includes('not found') || error.message.toLowerCase().includes('tidak ditemukan'))) {
        resultContainer.innerHTML = buildNotFoundHTML();
      } else {
        resultContainer.innerHTML = `
          <div class="card border-0 text-center mt-4" style="animation:fadeSlideUp 0.4s ease; background-color: #1e293b !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; color: #ffffff !important; box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25) !important; border-radius: 5px !important;">
            <style>@keyframes fadeSlideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}</style>
            <div class="card-body p-5">
              <div class="d-flex align-items-center justify-content-center mx-auto mb-4" style="width:80px; height:80px; background: linear-gradient(135deg, rgba(234,84,85,0.12), rgba(234,84,85,0.06)); border: 1px solid rgba(234, 84, 85, 0.2); border-radius: 5px !important; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);">
                <i class="ti tabler-alert-triangle text-danger fs-1"></i>
              </div>
              <h5 class="fw-bold mb-2">Terjadi Kesalahan</h5>
              <p class="text-muted mb-4 small" style="color: #94a3b8 !important;">${error.message || 'Gagal menghubungi server. Silakan coba lagi.'}</p>
              <button class="btn btn-outline-primary px-4 fw-semibold" style="border-radius: 5px !important;"
                      onclick="document.getElementById('cekForm').dispatchEvent(new Event('submit'))">
                <i class="ti tabler-reload me-1"></i> Coba Lagi
              </button>
            </div>
          </div>
        `;
      }
    } finally {
      cekBtn.disabled = false;
      cekText.style.display = 'inline';
      cekLoading.style.display = 'none';
    }
  });

  // ── Auto uppercase on input ──
  kodeInput.addEventListener('input', function() {
    this.value = this.value.toUpperCase();
    if (this.classList.contains('is-invalid') && this.value.trim()) {
      this.classList.remove('is-invalid');
    }
  });
});
</script>
@endsection
