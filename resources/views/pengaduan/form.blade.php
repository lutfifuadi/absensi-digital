@extends('layouts/layoutFront')

@section('title', 'Laporkan Data Tidak Valid')

@section('content')
<style>
  .rounded-2, .form-control, .form-select, .btn, .card, .modal-content, .alert, .badge {
    border-radius: 5px !important;
  }
  #deskripsiCountWrapper {
    transition: color 0.2s ease;
  }

  /* Form Labels & Icons */
  #formCard label.form-label {
    color: #cbd5e1 !important;
  }
  #formCard label.form-label i {
    color: #64748b !important;
  }

  /* Dark Input Style */
  .form-control, .form-select {
    background-color: #0f172a !important; /* Sunken effect di card */
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
  }
  .form-control::placeholder {
    color: #475569 !important; /* Slate 600 */
  }
  .form-control:hover, .form-select:hover {
    border-color: #475569 !important;
  }
  .form-control:focus, .form-select:focus {
    border-color: #696cff !important;
    box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25) !important;
  }

  /* Modals overrides */
  .modal-content {
    background-color: #1e293b !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
  }
  .modal-content h4, .modal-content .fw-bold {
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

{{-- Form Section --}}
<section style="padding-bottom:4rem; background-color:#0f172a;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-9 col-lg-6">

        {{-- Form Card --}}
        <div class="card border-0 p-4 p-md-5 position-relative" style="margin-top: -11rem !important; background-color: #1e293b !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; border-radius: 5px !important; z-index:2; box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25) !important;" id="formCard">
          <div class="text-center mb-4">
            <div class="d-flex align-items-center justify-content-center mx-auto mb-3" style="width:64px; height:64px; background: linear-gradient(135deg, rgba(234,84,85,0.12), rgba(234,84,85,0.06)); border: 1px solid rgba(234, 84, 85, 0.2); border-radius: 5px !important;">
              <i class="ti tabler-flag text-danger fs-3"></i>
            </div>
            <h4 class="fw-bold mb-1">Form Pengaduan</h4>
            <p class="text-muted small mb-0">Isi data dengan benar untuk membantu kami memverifikasi</p>
            <hr class="my-4" style="height: 1px; border: 0; background: linear-gradient(to right, rgba(226, 232, 240, 0), rgba(226, 232, 240, 1), rgba(226, 232, 240, 0));">
          </div>

          <form id="pengaduanForm" novalidate>
            @csrf

            {{-- Nama Lengkap --}}
            <div class="mb-3">
              <label for="nama_lengkap" class="form-label fw-semibold text-secondary">
                <i class="ti tabler-user me-1" style="color: #94a3b8;"></i> Nama Lengkap <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control rounded-2" id="nama_lengkap" name="nama_lengkap"
                     placeholder="Masukkan nama lengkap Anda" required
                     autocomplete="name" minlength="3" maxlength="100"
                     style="padding: 0.6rem 0.8rem;">
              <div class="invalid-feedback" id="nama_lengkap-error">Silakan masukkan nama lengkap Anda (min. 3 karakter)</div>
            </div>

            {{-- Status Pelapor --}}
            <div class="mb-3">
              <label for="status_pelapor" class="form-label fw-semibold text-secondary">
                <i class="ti tabler-users me-1" style="color: #94a3b8;"></i> Status Pelapor <span class="text-danger">*</span>
              </label>
              <select class="form-select rounded-2" id="status_pelapor" name="status_pelapor" required
                      style="padding: 0.6rem 0.8rem;">
                <option value="">— Pilih Status —</option>
                <option value="siswa">Siswa</option>
                <option value="orang_tua">Orang Tua/Wali</option>
              </select>
              <div class="invalid-feedback" id="status_pelapor-error">Silakan pilih status pelapor</div>
            </div>

            {{-- Kategori --}}
            <div class="mb-3">
              <label for="kategori" class="form-label fw-semibold text-secondary">
                <i class="ti tabler-category me-1" style="color: #94a3b8;"></i> Kategori <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control rounded-2" id="kategori" name="kategori"
                     placeholder="Contoh: Nama salah, NIS tidak sesuai..." required
                     minlength="3" maxlength="100"
                     style="padding: 0.6rem 0.8rem;">
              <div class="invalid-feedback" id="kategori-error">Silakan isi kategori pengaduan (min. 3 karakter)</div>
            </div>

            {{-- Deskripsi --}}
            <div class="mb-3">
              <label for="deskripsi" class="form-label fw-semibold text-secondary">
                <i class="ti tabler-file-description me-1" style="color: #94a3b8;"></i> Deskripsi <span class="text-danger">*</span>
              </label>
              <textarea class="form-control rounded-2" id="deskripsi" name="deskripsi"
                        placeholder="Jelaskan data apa yang tidak valid dan berikan informasi selengkap mungkin..." required
                        minlength="10" maxlength="2000" style="min-height:120px;resize:vertical; padding: 0.6rem 0.8rem;"></textarea>
              <div class="d-flex justify-content-between mt-1">
                <div class="invalid-feedback d-inline" id="deskripsi-error">Deskripsi minimal 10 karakter</div>
                <small id="deskripsiCountWrapper" style="color: #94a3b8;"><span id="deskripsiCount">0</span>/2000</small>
              </div>
            </div>

            {{-- Nomor WhatsApp --}}
            <div class="mb-4">
              <label for="nomor_wa" class="form-label fw-semibold text-secondary">
                <i class="ti tabler-brand-whatsapp me-1" style="color: #94a3b8;"></i> Nomor WhatsApp <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <input type="tel" class="form-control" id="nomor_wa" name="nomor_wa"
                       placeholder="08xxxxxxxxxx" required
                       autocomplete="tel" minlength="10" maxlength="15"
                       style="padding: 0.6rem 0.8rem; border-top-left-radius: 5px !important; border-bottom-left-radius: 5px !important; border-top-right-radius: 0px !important; border-bottom-right-radius: 0px !important;">
                <span class="input-group-text bg-0f172a border-start-0" id="waStatusIndicator" style="border: 1px solid rgba(255, 255, 255, 0.12) !important; background-color: #0f172a !important; color: #64748b; border-top-right-radius: 5px !important; border-bottom-right-radius: 5px !important; border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;">
                  <i class="ti tabler-brand-whatsapp text-muted" id="waStatusIcon"></i>
                </span>
              </div>
              <div class="invalid-feedback d-block" id="nomor_wa-error" style="display: none !important;">Nomor WhatsApp harus diawali 08 dan berisi 10-15 digit</div>
              <div class="d-flex align-items-center gap-2 mt-2 py-3 px-3 mb-0" role="alert" style="background-color: rgba(37, 211, 102, 0.1) !important; border: 1px solid rgba(37, 211, 102, 0.2) !important; border-left: 3px solid #25D366 !important; border-radius: 5px !important;">
                <i class="ti tabler-brand-whatsapp fs-5 flex-shrink-0" style="color: #25D366 !important;"></i>
                <span class="small" style="color: #25D366 !important;">Nomor WA akan digunakan untuk mengirim <strong style="color: #25D366 !important;">kode tracking</strong> pengaduan Anda</span>
              </div>
            </div>

            {{-- Tombol Submit --}}
            <button type="submit" class="btn w-100 py-3 fw-semibold text-white" id="submitBtn" style="background-color: #696cff; border-color: #696cff; letter-spacing: 0.5px;">
              <span id="submitText">
                <i class="ti tabler-send me-2"></i> Kirim Pengaduan
              </span>
              <span id="submitLoading" style="display:none;">
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Mengirim...
              </span>
            </button>
          </form>
        </div>

        {{-- Info tambahan --}}
        <div class="text-center mt-4">
          <p class="text-muted small mb-0">
            Sudah melapor?
            <a href="{{ route('pengaduan.cek') }}" class="fw-semibold ms-1" style="color: #696cff; text-decoration: none;">
              <i class="ti tabler-search me-1"></i>Cek Status Pengaduan
            </a>
          </p>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- Success Modal -->
<div class="modal fade" id="successModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="background-color: #1e293b !important; border: 1px solid rgba(255, 255, 255, 0.1) !important;">
      <div class="modal-body text-center p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(40,199,111,0.15), rgba(40,199,111,0.06)); border: 1px solid rgba(40, 199, 111, 0.2); border-radius: 5px !important;">
          <i class="ti tabler-check text-success" style="font-size: 3rem;"></i>
        </div>
        <h4 class="fw-bold mb-2">Pengaduan Berhasil Dikirim!</h4>
        <p class="text-muted small mb-4">Pengaduan Anda telah kami terima dan sedang diproses.</p>
        
        <div class="p-3 mb-3" style="background: rgba(40, 199, 111, 0.07); border: 1px solid rgba(40, 199, 111, 0.2); border-radius: 5px !important;">
          <small class="text-muted d-block mb-1">Kode Tracking Anda:</small>
          <strong id="modalKodeUnik" class="fs-4 font-monospace" style="letter-spacing: 2px; color: #28c76f;">PGN-xxx</strong>
        </div>
        
        <p class="small text-muted mb-4">
          <i class="ti tabler-brand-whatsapp text-success me-1"></i>
          Kode tracking telah dikirim ke nomor WhatsApp Anda.
        </p>
 
        <div class="d-grid gap-2">
          <a href="#" id="btnCekStatus" class="btn py-2 fw-semibold text-white" style="background-color: #696cff; border-color: #696cff;">
            <i class="ti tabler-search me-1"></i> Cek Status Pengaduan
          </a>
          <button type="button" class="btn btn-outline-secondary py-2 fw-semibold" data-bs-dismiss="modal" style="border: 1px solid rgba(255, 255, 255, 0.15) !important; color: #cbd5e1 !important;">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
 
<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="background-color: #1e293b !important; border: 1px solid rgba(255, 255, 255, 0.1) !important;">
      <div class="modal-body text-center p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(234,84,85,0.12), rgba(234,84,85,0.06)); border: 1px solid rgba(234, 84, 85, 0.2); border-radius: 5px !important; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);">
          <i class="ti tabler-x text-danger" style="font-size: 3rem;"></i>
        </div>
        <h4 class="fw-bold mb-2">Gagal Mengirim Pengaduan</h4>
        <p id="errorModalMessage" class="text-muted small mb-4">Terjadi kesalahan saat memproses pengaduan Anda. Silakan coba lagi.</p>
        
        <div class="d-grid">
          <button type="button" class="btn py-2 fw-semibold text-white" data-bs-dismiss="modal" style="background-color: #696cff; border-color: #696cff;">
            Coba Lagi
          </button>
        </div>
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

  // Inisialisasi modal Bootstrap 5
  const successModal = new bootstrap.Modal(document.getElementById('successModal'));
  const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
  const modalKodeUnik = document.getElementById('modalKodeUnik');
  const btnCekStatus = document.getElementById('btnCekStatus');
  const errorModalMessage = document.getElementById('errorModalMessage');

  // ── Character counter for deskripsi ──
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

  // ── Validasi nomor WA Indonesia (format 08xx) ──
  function isValidWA(number) {
    const clean = number.replace(/\D/g, '');
    // Harus diawali 08, panjang 10-15 digit
    return /^08[0-9]{8,13}$/.test(clean);
  }

  // ── Elemen Status Indicator WA ──
  const waInput = document.getElementById('nomor_wa');
  const waStatusIndicator = document.getElementById('waStatusIndicator');
  const waStatusIcon = document.getElementById('waStatusIcon');
  const waError = document.getElementById('nomor_wa-error');

  function setWaStatus(status, message = '') {
    // status: 'default', 'loading', 'valid', 'invalid'
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
        waError.textContent = 'Nomor WhatsApp harus diawali 08 dan berisi 10-15 digit';
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
        submitBtn.disabled = false; // Tetap aktifkan submit sesuai spek: "Tetap aktifkan tombol submit (atau biarkan disabled sampai input diperbaiki)." Kita biarkan aktif/nonaktif sesuai kebutuhan, namun di spek "Tetap aktifkan tombol submit" setelah respon jika tidak valid.
      }
    } catch (err) {
      if (err.name === 'AbortError') return;
      console.error('Gagal mengecek nomor WA:', err);
      setWaStatus('invalid', 'Terjadi kesalahan saat memeriksa nomor WhatsApp.');
      submitBtn.disabled = false;
    }
  }

  // Trigger validasi real-time saat change atau blur
  waInput.addEventListener('change', handleWaValidation);
  waInput.addEventListener('blur', handleWaValidation);

  function handleWaValidation() {
    const val = waInput.value.trim();
    if (!val) {
      setWaStatus('default');
      return;
    }

    if (!isValidWA(val)) {
      setWaStatus('invalid', 'Nomor WhatsApp harus diawali 08 dan berisi 10-15 digit');
      return;
    }

    checkWaApi(val);
  }

  // ── Validasi client-side per field ──
  function validateField(input) {
    const field = input.id;
    let valid = true;

    // Required check
    if (input.hasAttribute('required') && !input.value.trim()) {
      valid = false;
    }

    // Status select check
    if (field === 'status_pelapor' && input.value === '') {
      valid = false;
    }

    // Minlength check
    const minLen = input.getAttribute('minlength');
    if (valid && minLen && input.value.trim().length < parseInt(minLen)) {
      valid = false;
    }

    // WA format check
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
            // Jika valid secara lokal, hapus error lokal, tetapi butuh re-cek atau tunggu blur/change
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
      kategori: formData.get('kategori').trim(),
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
        // Handle validation errors from backend
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

      // ── Success ──
      const kodeUnik = result.kode_unik || (result.data && result.data.kode_unik) || '—';

      // Set value ke modal
      if (modalKodeUnik) {
        modalKodeUnik.textContent = kodeUnik;
      }
      if (btnCekStatus) {
        btnCekStatus.href = '{{ route("pengaduan.cek") }}?kode=' + encodeURIComponent(kodeUnik);
      }

      // Reset form
      form.reset();
      form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      deskripsiCount.textContent = '0';

      // Tampilkan modal
      successModal.show();

    } catch (error) {
      if (errorModalMessage) {
        errorModalMessage.textContent = error.message || 'Terjadi kesalahan saat memproses pengaduan Anda. Silakan coba lagi.';
      }
      errorModal.show();
    } finally {
      submitBtn.disabled = false;
      submitText.style.display = 'inline';
      submitLoading.style.display = 'none';
    }
  });
});
</script>
@endsection
