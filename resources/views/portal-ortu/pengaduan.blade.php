@extends('layouts/layoutMaster')

@section('title', 'Layanan Pengaduan Transparan')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
<style>
  /* Premium Border Radius Constraint (Max 5px, custom request: border-radius 4px) */
  .rounded-2, .form-control, .form-select, .btn, .card, .modal-content, .alert, .badge {
    border-radius: 4px !important;
  }
  
  /* Custom select2 border radius */
  .select2-container--default .select2-selection--single, .select2-dropdown, .select2-container--default .select2-search--dropdown .select2-search__field, .select2-results__option {
    border-radius: 4px !important;
  }

  #deskripsiCountWrapper {
    transition: color 0.2s ease;
  }

  /* Form Labels & Icons */
  #modalCreatePengaduan label.form-label {
    color: #cbd5e1 !important;
  }
  #modalCreatePengaduan label.form-label i {
    color: #64748b !important;
  }

  /* Dark Input Style matching theme */
  #modalCreatePengaduan .form-control, #modalCreatePengaduan .form-select {
    background-color: #0f172a !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
  }
  #modalCreatePengaduan .form-control::placeholder {
    color: #475569 !important;
  }
  #modalCreatePengaduan .form-control:hover, #modalCreatePengaduan .form-select:hover {
    border-color: #475569 !important;
  }
  #modalCreatePengaduan .form-control:focus, #modalCreatePengaduan .form-select:focus {
    border-color: #696cff !important;
    box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25) !important;
  }

  /* Select2 Dark Theme Override inside Modal */
  .select2-container {
    width: 100% !important;
    max-width: 100% !important;
  }
  .select2-container--default .select2-selection--single {
    background-color: #0f172a !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
    height: 40px !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #ffffff !important;
    line-height: 38px !important;
    padding-left: 12px !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
  }
  .select2-dropdown {
    background-color: #1e293b !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
    z-index: 1060 !important;
  }
  .select2-container--default .select2-results__option[aria-selected=true] {
    background-color: rgba(255, 255, 255, 0.06) !important;
    color: #ffffff !important;
  }
  .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #696cff !important;
    color: #ffffff !important;
  }
  .select2-container--default .select2-search--dropdown .select2-search__field {
    background-color: #0f172a !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
  }
  .select2-container--default .select2-results__group {
    color: #cbd5e1 !important;
    font-weight: 600;
  }
  .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #475569 !important;
  }

  /* Modals overrides */
  .modal-content {
    background-color: #1e293b !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
  }
  .modal-content h4, .modal-content h5, .modal-content .modal-title, .modal-content .fw-bold {
    color: #ffffff !important;
  }
  .modal-content p, .modal-content .text-muted {
    color: #cbd5e1 !important;
  }
  .modal-content .btn-outline-secondary {
    border-color: rgba(255, 255, 255, 0.15) !important;
    color: #cbd5e1 !important;
  }
  .modal-content .btn-outline-secondary:hover {
    background-color: rgba(255, 255, 255, 0.05) !important;
    color: #ffffff !important;
  }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 text-white overflow-hidden shadow-sm"
            style="background: linear-gradient(135deg, #7367f0 0%, #4338ca 100%); border-radius: 4px !important;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                            style="width:52px;height:52px;border-radius:4px !important;background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);">
                            <i class="ti tabler-message-dots text-white fs-3"></i>
                        </div>
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.8;">
                                    <li class="breadcrumb-item"><a href="{{ route('ortu.dashboard') }}"
                                             class="text-white text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Layanan Pengaduan</li>
                                </ol>
                            </nav>
                            <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Portal Layanan Pengaduan Transparan</h4>
                        </div>
                    </div>
                    <button type="button" class="btn btn-warning fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreatePengaduan">
                        <i class="ti tabler-plus me-1"></i> Buat Pengaduan Baru
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
  <div class="col-md-5">
    <div class="card mb-4">
      <div class="card-header pb-2">
        <h5 class="card-title mb-0">Daftar Pengaduan Saya</h5>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          @forelse($pengaduanList as $p)
            @php
              $isActive = $activePengaduan && $activePengaduan->id === $p->id;
            @endphp
            <a href="{{ route('ortu.pengaduan', ['id' => $p->id]) }}" class="list-group-item list-group-item-action {{ $isActive ? 'active' : '' }} p-3 rounded mb-2">
              <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                <small class="fw-bold font-monospace {{ $isActive ? 'text-white' : 'text-muted' }}">#{{ $p->kode_unik }}</small>
                <span class="badge bg-{{ $p->status_color }} {{ $p->status === 'baru' ? 'text-dark' : '' }}">{{ strtoupper($p->status_label) }}</span>
              </div>
              <h6 class="mb-1 fw-bold {{ $isActive ? 'text-white' : '' }}">{{ Str::limit($p->kategori, 40) }}</h6>
              <small class="{{ $isActive ? 'text-white opacity-75' : 'text-muted' }}">
                Tanggal: {{ $p->created_at->translatedFormat('d F Y H:i') }} WIB
              </small>
            </a>
          @empty
            <div class="text-center py-5">
              <div class="avatar avatar-md mx-auto mb-3 bg-label-secondary">
                <i class="ti tabler-message-off fs-3"></i>
              </div>
              <p class="text-muted mb-0">Belum ada riwayat pengaduan</p>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-7">
    @if($activePengaduan)
      <div class="card">
        <div class="card-header border-bottom">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="text-muted font-monospace small">#{{ $activePengaduan->kode_unik }}</span>
              <h5 class="mb-0 fw-bold">{{ $activePengaduan->kategori }}</h5>
            </div>
            <span class="badge bg-label-{{ $activePengaduan->status_color }}">{{ strtoupper($activePengaduan->status_label) }}</span>
          </div>
        </div>
        <div class="card-body pt-4">
          <!-- Detail deskripsi pengaduan -->
          <div class="mb-4 p-3 bg-light rounded text-dark" style="background-color: rgba(255,255,255,0.03) !important; border: 1px solid rgba(255,255,255,0.08);">
            <h6 class="fw-bold text-white mb-2">Deskripsi Pengaduan:</h6>
            <p class="mb-0 text-muted" style="white-space: pre-line;">{{ $activePengaduan->deskripsi }}</p>
            @if($activePengaduan->catatan_admin)
              <div class="mt-3 pt-3 border-top border-secondary-subtle">
                <h6 class="fw-bold text-warning mb-1">Catatan Admin:</h6>
                <p class="mb-0 text-warning" style="white-space: pre-line;">{{ $activePengaduan->catatan_admin }}</p>
              </div>
            @endif
          </div>

          <!-- Visual Timeline Stepper -->
          <h6 class="fw-bold text-white mb-3"><i class="ti tabler-history me-1"></i> Riwayat Status Pengaduan</h6>
          <ul class="timeline timeline-dashed">
            @forelse($activeLogs as $log)
              @php
                $color = 'secondary';
                if ($log->status_ke === 'baru') $color = 'warning';
                elseif ($log->status_ke === 'diproses') $color = 'info';
                elseif ($log->status_ke === 'selesai') $color = 'success';
                elseif ($log->status_ke === 'ditolak') $color = 'danger';

                // Label bahasa Indonesia untuk status_ke
                $statusLabel = match($log->status_ke) {
                  'baru' => 'Baru',
                  'diproses' => 'Diproses',
                  'selesai' => 'Selesai',
                  'ditolak' => 'Ditolak',
                  default => ucfirst($log->status_ke)
                };
              @endphp
              <li class="timeline-item timeline-item-transparent pb-4">
                <span class="timeline-point timeline-point-{{ $color }}"></span>
                <div class="timeline-event">
                  <div class="timeline-header mb-1">
                    <h6 class="mb-0 fw-bold text-{{ $color }}">{{ strtoupper($statusLabel) }}</h6>
                    <small class="text-muted">{{ $log->created_at->translatedFormat('d M Y H:i') }} WIB</small>
                  </div>
                  <p class="mb-2 text-muted">{{ $log->catatan ?? 'Status pengaduan diubah.' }}</p>
                  <div class="badge bg-label-secondary">Diubah oleh: {{ ucfirst($log->diubah_oleh) }}</div>
                </div>
              </li>
            @empty
              <li class="text-muted small">Belum ada riwayat perubahan status</li>
            @endforelse
          </ul>
        </div>
      </div>
    @else
      <div class="card">
        <div class="card-body text-center py-5">
          <div class="avatar avatar-lg mx-auto mb-3 bg-label-secondary" style="width: 80px; height: 80px;">
            <i class="ti tabler-message-dots fs-1"></i>
          </div>
          <h5 class="fw-bold text-white">Detail Pengaduan</h5>
          <p class="text-muted mx-auto" style="max-width: 320px;">Pilih salah satu pengaduan di sebelah kiri untuk melihat detail status dan riwayat tindak lanjut.</p>
        </div>
      </div>
    @endif
  </div>
</div>

<!-- Modal Create Pengaduan -->
<div class="modal fade" id="modalCreatePengaduan" tabindex="-1" aria-labelledby="modalCreatePengaduanLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-bottom border-secondary-subtle justify-content-center position-relative">
        <h5 class="modal-title fw-bold w-100 text-center mb-0" id="modalCreatePengaduanLabel">
          <i class="ti tabler-flag me-2 text-danger"></i>Buat Pengaduan Baru
        </h5>
        <button type="button" class="btn-close btn-close-white position-absolute" style="right: 1.25rem; top: 1.25rem;" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="pengaduanForm" novalidate>
          @csrf
          <input type="hidden" name="status_pelapor" value="orang_tua">

          <div class="row">
            {{-- Nama Pelapor --}}
            <div class="col-md-6 mb-3">
              <label for="nama_lengkap" class="form-label fw-semibold text-secondary">
                <i class="ti tabler-user me-1"></i> Nama Pelapor <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control rounded-2" id="nama_lengkap" name="nama_lengkap"
                     value="{{ auth()->user()->name }}" required
                     readonly
                     style="padding: 0.6rem 0.8rem; background-color: #1e293b !important; border-color: rgba(255,255,255,0.08) !important; color: #94a3b8 !important;">
              <div class="invalid-feedback" id="nama_lengkap-error">Nama pelapor wajib terisi</div>
            </div>

            {{-- Nomor WhatsApp --}}
            <div class="col-md-6 mb-3">
              <label for="nomor_wa" class="form-label fw-semibold text-secondary">
                <i class="ti tabler-brand-whatsapp me-1"></i> Nomor WhatsApp <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <input type="tel" class="form-control" id="nomor_wa" name="nomor_wa"
                       placeholder="08xxxxxxxxxx / 628xxxxxxxxxx" required
                       value="{{ auth()->user()->no_hp ?? '' }}"
                       autocomplete="tel" minlength="10" maxlength="16"
                       style="padding: 0.6rem 0.8rem; border-top-left-radius: 4px !important; border-bottom-left-radius: 4px !important; border-top-right-radius: 0px !important; border-bottom-right-radius: 0px !important;">
                <span class="input-group-text bg-0f172a border-start-0" id="waStatusIndicator" style="border: 1px solid rgba(255, 255, 255, 0.12) !important; background-color: #0f172a !important; color: #64748b; border-top-right-radius: 4px !important; border-bottom-right-radius: 4px !important; border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;">
                  <i class="ti tabler-brand-whatsapp text-muted" id="waStatusIcon"></i>
                </span>
              </div>
              <div class="invalid-feedback d-block" id="nomor_wa-error" style="display: none !important;">Nomor WhatsApp harus diawali 08 atau 628 dan berisi 10-16 digit</div>
              <div class="d-flex align-items-center gap-2 mt-2 py-2 px-3 mb-0" role="alert" style="background-color: rgba(37, 211, 102, 0.1) !important; border: 1px solid rgba(37, 211, 102, 0.2) !important; border-left: 3px solid #25D366 !important; border-radius: 4px !important;">
                <i class="ti tabler-brand-whatsapp fs-5 flex-shrink-0" style="color: #25D366 !important;"></i>
                <span class="small" style="color: #25D366 !important;">Nomor WA digunakan untuk menerima <strong style="color: #25D366 !important;">kode tracking</strong></span>
              </div>
            </div>
          </div>

          {{-- Kategori --}}
          <div class="mb-3">
            <label for="kategori" class="form-label fw-semibold text-secondary">
              <i class="ti tabler-category me-1"></i> Kategori <span class="text-danger">*</span>
            </label>
            <select class="form-select select2 rounded-2" id="kategori" name="kategori" required
                    data-placeholder="— Pilih Kategori Pengaduan —">
              <option value="">— Pilih Kategori Pengaduan —</option>
              <optgroup label="Ketidakvalidan Status Presensi">
                <option value="Kehadiran Tercatat Alpa (Padahal Hadir)">Kehadiran Tercatat Alpa (Padahal Hadir)</option>
                <option value="Status Izin/Sakit Belum Diperbarui">Status Izin/Sakit Belum Diperbarui</option>
                <option value="Jam Presensi / Terlambat Tidak Sesuai">Jam Presensi / Terlambat Tidak Sesuai</option>
                <option value="Perbedaan Data Rekapitulasi Bulanan">Perbedaan Data Rekapitulasi Bulanan</option>
              </optgroup>
              <optgroup label="Kendala Teknis & Aplikasi">
                <option value="Gagal Scan QR / Sensor RFID">Gagal Scan QR / Sensor RFID</option>
                <option value="Masalah Aplikasi / GPS (Presensi Mandiri)">Masalah Aplikasi / GPS (Presensi Mandiri)</option>
                <option value="Notifikasi Presensi Tidak Masuk / Salah">Notifikasi Presensi Tidak Masuk / Salah</option>
              </optgroup>
              <optgroup label="Kesalahan Data Profil">
                <option value="Biodata Profil Salah (Nama/NIS/Kelas)">Biodata Profil Salah (Nama/NIS/Kelas)</option>
              </optgroup>
              <option value="Lainnya">Lainnya</option>
            </select>
            <div class="invalid-feedback" id="kategori-error">Silakan pilih kategori pengaduan</div>
          </div>

          {{-- Deskripsi --}}
          <div class="mb-3">
            <label for="deskripsi" class="form-label fw-semibold text-secondary">
              <i class="ti tabler-file-description me-1"></i> Deskripsi Pengaduan <span class="text-danger">*</span>
            </label>
            <textarea class="form-control rounded-2" id="deskripsi" name="deskripsi"
                      placeholder="Jelaskan secara mendetail pengaduan atau kesalahan data yang ingin dilaporkan..." required
                      minlength="10" maxlength="2000" style="min-height:120px;resize:vertical; padding: 0.6rem 0.8rem;"></textarea>
            <div class="d-flex justify-content-between mt-1">
              <div class="invalid-feedback d-inline" id="deskripsi-error">Deskripsi minimal 10 karakter</div>
              <small id="deskripsiCountWrapper" style="color: #94a3b8;"><span id="deskripsiCount">0</span>/2000</small>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer border-top border-secondary-subtle justify-content-center">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" form="pengaduanForm" class="btn btn-primary" id="submitBtn">
          <span id="submitText"><i class="ti tabler-send me-1"></i> Kirim Pengaduan</span>
          <span id="submitLoading" style="display:none;">
            <span class="spinner-border spinner-border-sm me-1" role="status"></span> Mengirim...
          </span>
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('pengaduanForm');
  const submitBtn = document.getElementById('submitBtn');
  const submitText = document.getElementById('submitText');
  const submitLoading = document.getElementById('submitLoading');
  const deskripsi = document.getElementById('deskripsi');
  const deskripsiCount = document.getElementById('deskripsiCount');
  
  const modalCreatePengaduanEl = document.getElementById('modalCreatePengaduan');
  let modalInstance = null;
  if (typeof bootstrap !== 'undefined') {
    modalInstance = bootstrap.Modal.getInstance(modalCreatePengaduanEl) || new bootstrap.Modal(modalCreatePengaduanEl);
  }

  // ── Inisialisasi Select2 untuk Kategori ──
  function initSelect2() {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
      const $kategori = jQuery('#kategori');
      if ($kategori.length) {
        $kategori.select2({
          placeholder: $kategori.data('placeholder'),
          dropdownParent: jQuery('#modalCreatePengaduan')
        });

        // Sinkronisasi dengan validasi JS
        $kategori.on('change', function() {
          validateField(this);
        });
      }
    } else {
      setTimeout(initSelect2, 50);
    }
  }

  // Jalankan inisialisasi Select2 secara aman
  if (document.readyState === 'complete') {
    initSelect2();
  } else {
    window.addEventListener('load', initSelect2);
  }

  // helper untuk sinkronisasi value kategori jika select2 digunakan
  function getKategoriValue() {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
      return jQuery('#kategori').val();
    }
    return document.getElementById('kategori').value;
  }

  // helper untuk trigger reset kategori select2
  function resetKategoriSelect2() {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
      jQuery('#kategori').val('').trigger('change.select2');
    } else {
      document.getElementById('kategori').value = '';
    }
  }

  // ── Character counter for deskripsi ──
  if (deskripsi) {
    deskripsi.addEventListener('input', function() {
      const len = this.value.length;
      deskripsiCount.textContent = len;
      if (len > 2000) {
        this.value = this.value.substring(0, 2000);
        deskripsiCount.textContent = 2000;
      }
      
      const count = this.value.length;
      const wrapper = document.getElementById('deskripsiCountWrapper');
      if (wrapper) {
        if (count > 1950) {
          wrapper.style.color = '#ea5455';
        } else if (count > 1800) {
          wrapper.style.color = '#ff9f43';
        } else {
          wrapper.style.color = '#94a3b8';
        }
      }
    });
  }

  // ── Validasi nomor WA Indonesia (format 08xx atau 628xx) ──
  function isValidWA(number) {
    const clean = number.replace(/\D/g, '');
    return /^(08|628)[0-9]{8,14}$/.test(clean);
  }

  // ── Elemen Status Indicator WA ──
  const waInput = document.getElementById('nomor_wa');
  const waStatusIndicator = document.getElementById('waStatusIndicator');
  const waStatusIcon = document.getElementById('waStatusIcon');
  const waError = document.getElementById('nomor_wa-error');

  function setWaStatus(status, message = '') {
    if (!waStatusIcon || !waStatusIndicator || !waInput || !waError) return;
    
    if (status === 'default') {
      waStatusIcon.className = 'ti tabler-brand-whatsapp text-muted';
      waStatusIndicator.style.borderColor = 'rgba(255, 255, 255, 0.12)';
      waInput.style.borderColor = 'rgba(255, 255, 255, 0.12)';
      waInput.classList.remove('is-valid', 'is-invalid');
      waError.style.setProperty('display', 'none', 'important');
    } else if (status === 'loading') {
      waStatusIcon.className = 'spinner-border spinner-border-sm text-primary';
      waStatusIndicator.style.borderColor = 'rgba(255, 255, 255, 0.12)';
      waInput.style.borderColor = 'rgba(255, 255, 255, 0.12)';
      waInput.classList.remove('is-valid', 'is-invalid');
      waError.style.setProperty('display', 'none', 'important');
    } else if (status === 'valid') {
      waStatusIcon.className = 'ti tabler-circle-check-filled text-success';
      waStatusIndicator.style.borderColor = '#28c76f';
      waInput.style.borderColor = '#28c76f';
      waInput.classList.remove('is-invalid');
      waInput.classList.add('is-valid');
      waError.style.setProperty('display', 'none', 'important');
    } else if (status === 'invalid') {
      waStatusIcon.className = 'ti tabler-circle-x-filled text-danger';
      waStatusIndicator.style.borderColor = '#ea5455';
      waInput.style.borderColor = '#ea5455';
      waInput.classList.remove('is-valid');
      waInput.classList.add('is-invalid');
      if (message) {
        waError.textContent = message;
      } else {
        waError.textContent = 'Nomor WhatsApp harus diawali 08 atau 628 dan berisi 10-16 digit';
      }
      waError.style.setProperty('display', 'block', 'important');
    }
  }

  let checkingWaAbortController = null;

  async function checkWaApi(number) {
    if (checkingWaAbortController) {
      checkingWaAbortController.abort();
    }
    checkingWaAbortController = new AbortController();
    const signal = checkingWaAbortController.signal;

    setWaStatus('loading');
    submitBtn.disabled = true;

    try {
      const response = await fetch('/api/pengaduan/cek-wa?nomor_wa=' + encodeURIComponent(number), {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
        signal: signal
      });

      const result = await response.json();

      if (response.ok && result.valid === true) {
        setWaStatus('valid');
        submitBtn.disabled = false;
      } else {
        const errorMsg = result.message || 'Nomor WhatsApp tidak terdaftar atau tidak aktif.';
        setWaStatus('invalid', errorMsg);
        submitBtn.disabled = false;
      }
    } catch (err) {
      if (err.name === 'AbortError') return;
      console.error('Gagal mengecek nomor WA:', err);
      setWaStatus('invalid', 'Terjadi kesalahan saat memeriksa nomor WhatsApp.');
      submitBtn.disabled = false;
    }
  }

  // Trigger validasi real-time saat change atau blur
  if (waInput) {
    waInput.addEventListener('change', handleWaValidation);
    waInput.addEventListener('blur', handleWaValidation);
  }

  function handleWaValidation() {
    if (!waInput) return;
    const val = waInput.value.trim();
    if (!val) {
      setWaStatus('default');
      return;
    }

    if (!isValidWA(val)) {
      setWaStatus('invalid', 'Nomor WhatsApp harus diawali 08 atau 628 dan berisi 10-16 digit');
      return;
    }

    checkWaApi(val);
  }

  // ── Validasi client-side per field ──
  function validateField(input) {
    const field = input.id;
    let valid = true;

    if (input.hasAttribute('required') && !input.value.trim()) {
      valid = false;
    }

    if (field === 'kategori' && getKategoriValue() === '') {
      valid = false;
    }

    const minLen = input.getAttribute('minlength');
    if (valid && minLen && input.value.trim().length < parseInt(minLen)) {
      valid = false;
    }

    if (field === 'nomor_wa') {
      const val = input.value.trim();
      if (!val) {
        valid = false;
      } else if (!isValidWA(val)) {
        valid = false;
      } else if (input.classList.contains('is-invalid')) {
        valid = false;
      }
    }

    if (field !== 'nomor_wa') {
      input.classList.toggle('is-invalid', !valid);
    }
    return valid;
  }

  // ── Real-time validation on blur & input ──
  form.querySelectorAll('[required], #nomor_wa').forEach(input => {
    if (input.id !== 'nomor_wa') {
      input.addEventListener('blur', function() { validateField(this); });
      input.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) validateField(this);
      });
    } else {
      input.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
          const val = this.value.trim();
          if (isValidWA(val)) {
            this.classList.remove('is-invalid');
            waError.style.setProperty('display', 'none', 'important');
          }
        }
      });
    }
  });

  // ── Submit handler ──
  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    // Validate all fields
    let allValid = true;
    const fields = form.querySelectorAll('[required], #nomor_wa');
    fields.forEach(input => {
      if (!validateField(input)) allValid = false;
    });

    if (!allValid) {
      const firstError = form.querySelector('.is-invalid');
      if (firstError) {
        firstError.focus();
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return;
    }

    // Show loading
    submitBtn.disabled = true;
    submitText.style.display = 'none';
    submitLoading.style.display = 'inline';

    // Prepare data
    const formData = new FormData(form);
    const data = {
      nama_lengkap: formData.get('nama_lengkap').trim(),
      status_pelapor: formData.get('status_pelapor'),
      kategori: getKategoriValue(),
      deskripsi: formData.get('deskripsi').trim(),
      nomor_wa: formData.get('nomor_wa').trim(),
    };

    try {
      const response = await fetch('/api/pengaduan', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(data),
      });

      const result = await response.json();

      if (!response.ok) {
        if (result.errors) {
          const firstKey = Object.keys(result.errors)[0];
          const fieldEl = document.getElementById(firstKey);
          if (fieldEl) {
            fieldEl.classList.add('is-invalid');
            const errorEl = document.getElementById(firstKey + '-error');
            if (errorEl) errorEl.textContent = result.errors[firstKey][0];
            fieldEl.focus();
            fieldEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
          throw new Error(result.message || 'Validasi gagal');
        }
        throw new Error(result.message || 'Terjadi kesalahan saat mengirim pengaduan');
      }

      // Hide modal first
      if (modalInstance) {
        modalInstance.hide();
      }

      // Success Notification using SweetAlert2
      const kodeUnik = result.kode_unik || (result.data && result.data.kode_unik) || '—';
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'success',
          title: 'Pengaduan Terkirim!',
          html: `Pengaduan Anda telah terdaftar dengan kode:<br><strong class="fs-4 text-success">${kodeUnik}</strong><br><br><small class="text-muted">Kode tracking telah dikirim ke nomor WhatsApp Anda.</small>`,
          confirmButtonColor: '#696cff',
          confirmButtonText: 'OK',
          customClass: {
            popup: 'bg-1e293b text-white border-secondary-subtle',
            title: 'text-white',
            htmlContainer: 'text-muted'
          }
        }).then(() => {
          window.location.reload();
        });
      } else {
        alert('Pengaduan berhasil dikirim! Kode Tracking Anda: ' + kodeUnik);
        window.location.reload();
      }

      // Reset form
      form.reset();
      resetKategoriSelect2();
      form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      if (deskripsiCount) deskripsiCount.textContent = '0';

    } catch (error) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'error',
          title: 'Gagal Mengirim Pengaduan',
          text: error.message || 'Terjadi kesalahan saat memproses pengaduan Anda. Silakan coba lagi.',
          confirmButtonColor: '#696cff',
          confirmButtonText: 'Coba Lagi'
        });
      } else {
        alert('Gagal: ' + (error.message || 'Terjadi kesalahan. Silakan coba lagi.'));
      }
    } finally {
      submitBtn.disabled = false;
      submitText.style.display = 'inline';
      submitLoading.style.display = 'none';
    }
  });

  // Pre-validate or check prefilled WA if valid
  if (waInput && waInput.value.trim()) {
    handleWaValidation();
  }
});
</script>
@endsection
