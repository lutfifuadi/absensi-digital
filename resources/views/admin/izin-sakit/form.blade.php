@extends('layouts/layoutMaster')

@section('title', isset($izinSakit) ? 'Ubah Pengajuan Izin/Sakit' : 'Tambah Pengajuan Izin/Sakit')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-style')
<style>
  /* Select2 theme override */
  .select2-container {
    width: 100% !important;
    max-width: 100% !important;
  }
  .select2-container--default .select2-selection--single {
    background-color: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    color: #fff !important;
    height: 38px !important;
    border-radius: 6px !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #fff !important;
    line-height: 36px !important;
    padding-left: 12px !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
  }
  .select2-dropdown {
    background-color: #2f3349 !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    color: #fff !important;
    z-index: 1060 !important;
  }
  .select2-container--default .select2-results__option[aria-selected=true] {
    background-color: rgba(115, 103, 240, 0.2) !important;
    color: #fff !important;
  }
  .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #7367f0 !important;
    color: #fff !important;
  }
  .select2-container--default .select2-search--dropdown .select2-search__field {
    background-color: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    color: #fff !important;
  }

  /* ── Quota Info Card ───────────────────────────────── */
  .quota-card {
    background: var(--das-surface);
    border: 1px solid var(--das-border);
    border-radius: var(--das-radius);
    backdrop-filter: blur(6px);
    overflow: hidden;
    margin-bottom: 1.25rem;
  }
  .quota-card__head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.7rem 1.25rem;
    border-bottom: 1px solid var(--das-border);
  }
  .quota-card__head-icon {
    width: 28px; height: 28px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 207, 232, 0.12);
    color: #00cfe8;
    font-size: 0.9rem;
    flex-shrink: 0;
  }
  .quota-card__title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: rgba(255,255,255,0.6);
    margin: 0;
  }
  .quota-card__body {
    padding: 0.85rem 1.25rem;
  }
  .quota-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 0.6rem;
  }
  .quota-item {
    background: rgba(15, 23, 42, 0.25);
    border: 1px solid var(--das-border);
    border-radius: 4px;
    padding: 0.6rem 0.85rem;
    transition: all 0.2s;
  }
  .quota-item__name {
    font-size: 0.62rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: rgba(255,255,255,0.4);
    margin-bottom: 3px;
  }
  .quota-item__bar {
    height: 4px;
    background: rgba(255,255,255,0.08);
    border-radius: 2px;
    margin: 5px 0;
    overflow: hidden;
  }
  .quota-item__bar-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.6s ease;
  }
  .quota-item__stats {
    display: flex;
    justify-content: space-between;
    font-size: 0.72rem;
  }
  .quota-item__remaining {
    font-weight: 700;
  }
  .quota-item__remaining.--safe { color: var(--das-success); }
  .quota-item__remaining.--low { color: var(--das-warning); }
  .quota-item__remaining.--empty { color: var(--das-danger); }
  .quota-item__used {
    color: rgba(255,255,255,0.35);
  }
  .quota-message {
    padding: 0.5rem 0.85rem;
    border-radius: 4px;
    font-size: 0.78rem;
    font-weight: 600;
    margin-top: 0.5rem;
  }
  .quota-message.--success {
    background: rgba(40, 199, 111, 0.1);
    color: var(--das-success);
    border: 1px solid rgba(40, 199, 111, 0.2);
  }
  .quota-message.--warning {
    background: rgba(255, 159, 67, 0.1);
    color: var(--das-warning);
    border: 1px solid rgba(255, 159, 67, 0.2);
  }
  .quota-message.--danger {
    background: rgba(234, 84, 85, 0.1);
    color: var(--das-danger);
    border: 1px solid rgba(234, 84, 85, 0.2);
  }
  .quota-loading {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.4);
    padding: 0.5rem 0;
  }
  .quota-loading .spinner {
    width: 16px; height: 16px;
    border: 2px solid rgba(255,255,255,0.1);
    border-top-color: var(--das-primary);
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  .quota-card-hidden { display: none; }
</style>
@endsection

@section('content')

  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 4px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
              style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
              <i class="ti {{ isset($izinSakit) ? 'tabler-pencil' : 'tabler-plus' }} text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  @auth
                    @if(auth()->user()->isRole(\App\Models\User::ROLE_SISWA))
                      <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Siswa</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.izin-sakit.index') }}"
                          class="text-white text-decoration-none">Izin & Sakit</a></li>
                    @elseif(auth()->user()->isRole(\App\Models\User::ROLE_GURU))
                      <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Guru</a></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.izin-sakit.index') }}"
                          class="text-white text-decoration-none">Izin & Sakit</a></li>
                    @else
                      <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}"
                          class="text-white text-decoration-none">Admin</a></li>
                      <li class="breadcrumb-item"><span class="text-white">Master Data</span></li>
                      <li class="breadcrumb-item"><a href="{{ route('admin.izin-sakit.index') }}"
                          class="text-white text-decoration-none">Izin & Sakit</a></li>
                    @endif
                  @else
                    <li class="breadcrumb-item"><a href="{{ route('admin.izin-sakit.index') }}"
                        class="text-white text-decoration-none">Izin & Sakit</a></li>
                  @endauth
                  <li class="breadcrumb-item active text-white">{{ isset($izinSakit) ? 'Ubah' : 'Tambah' }}</li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                {{ isset($izinSakit) ? 'Ubah Pengajuan Izin/Sakit' : 'Tambah Pengajuan Izin/Sakit' }}
              </h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- QUOTA INFO CARD --}}
  <div class="row mb-4">
    <div class="col-12">
      <div id="quotaCard" class="quota-card quota-card-hidden mb-0">
        <div class="quota-card__head">
          <div class="quota-card__head-icon">
            <i class="ti tabler-chart-bar"></i>
          </div>
          <h6 class="quota-card__title">Sisa Kuota Izin / Sakit</h6>
          <span id="quotaPeriod" class="ms-auto text-white-50" style="font-size:0.6rem;"></span>
        </div>
        <div class="quota-card__body">
          {{-- Loading --}}
          <div id="quotaLoading" class="quota-loading" style="display:none;">
            <div class="spinner"></div>
            <span>Memeriksa sisa kuota...</span>
          </div>

          {{-- Error --}}
          <div id="quotaError" class="quota-message --danger" style="display:none;"></div>

          {{-- Grid --}}
          <div id="quotaGridContainer" style="display:none;">
            <div id="quotaGrid" class="quota-grid"></div>
            <div id="quotaMessage" class="quota-message" style="display:none;"></div>
          </div>

          {{-- No limits --}}
          <div id="quotaNoLimits" class="quota-message --success" style="display:none;">
            <i class="ti tabler-circle-check me-1"></i> Tidak ada batasan kuota untuk akun Anda.
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">

      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
          style="border-radius:8px; background: rgba(234, 84, 85, 0.15); color: #ea5455;">
          <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
          <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="card border-0 shadow-sm"
        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
        <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
          <i class="ti tabler-forms text-info"></i>
          <h6 class="card-title mb-0">Formulir Pengajuan</h6>
        </div>
        <div class="card-body p-4">
          <form
            action="{{ isset($izinSakit) ? route('admin.izin-sakit.update', $izinSakit) : route('admin.izin-sakit.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($izinSakit))
              @method('PUT')
            @endif

            <div class="row g-4">
              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="tipePengaju">
                  <i class="ti tabler-user-cog me-1 text-info"></i> Tipe <span class="text-danger">*</span>
                </label>
                <select name="tipe" class="form-select @error('tipe') is-invalid @enderror" id="tipePengaju" required>
                  <option value="">-- Pilih Tipe --</option>
                  @foreach (['siswa', 'guru', 'staff'] as $t)
                    <option value="{{ $t }}" @selected(old('tipe', $izinSakit->tipe ?? '') === $t)>{{ ucfirst($t) }}</option>
                  @endforeach
                </select>
                @error('tipe')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-8">
                <label class="form-label fw-semibold small" for="referenceId">
                  <i class="ti tabler-user me-1 text-info"></i> Nama <span class="text-danger">*</span>
                </label>
                <select name="reference_id" class="form-select select2 @error('reference_id') is-invalid @enderror" id="referenceId" data-placeholder="-- Pilih Nama --" required>
                  <option value="">-- Pilih Nama --</option>
                  <optgroup label="Siswa">
                    @foreach ($siswaOptions as $s)
                      <option value="{{ $s->id }}" data-tipe="siswa" data-user-id="{{ $s->user?->id ?? '' }}" @selected(old('reference_id', $izinSakit->reference_id ?? '') == $s->id && old('tipe', $izinSakit->tipe ?? '') === 'siswa')>
                        {{ $s->nama_lengkap }}
                      </option>
                    @endforeach
                  </optgroup>
                  <optgroup label="Guru">
                    @foreach ($guruOptions as $g)
                      <option value="{{ $g->id }}" data-tipe="guru" data-user-id="{{ $g->user?->id ?? '' }}" @selected(old('reference_id', $izinSakit->reference_id ?? '') == $g->id && old('tipe', $izinSakit->tipe ?? '') === 'guru')>
                        {{ $g->nama_lengkap }}
                      </option>
                    @endforeach
                  </optgroup>
                  <optgroup label="Staff TU">
                    @foreach ($staffOptions as $st)
                      <option value="{{ $st->id }}" data-tipe="staff" data-user-id="{{ $st->user?->id ?? '' }}" @selected(old('reference_id', $izinSakit->reference_id ?? '') == $st->id && old('tipe', $izinSakit->tipe ?? '') === 'staff')>
                        {{ $st->nama_lengkap }}
                      </option>
                    @endforeach
                  </optgroup>
                </select>
                @error('reference_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-3">
                <label class="form-label fw-semibold small" for="jenisIzin">
                  <i class="ti tabler-clipboard-text me-1 text-info"></i> Jenis <span class="text-danger">*</span>
                </label>
                <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" id="jenisIzin" required>
                  <option value="">-- Pilih Jenis --</option>
                  @foreach (['sakit', 'izin'] as $j)
                    <option value="{{ $j }}" @selected(old('jenis', $izinSakit->jenis ?? '') === $j)>{{ ucfirst($j) }}</option>
                  @endforeach
                </select>
                @error('jenis')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-3">
                <label class="form-label fw-semibold small" for="tanggalMulai">
                  <i class="ti tabler-calendar me-1 text-info"></i> Tanggal Mulai <span class="text-danger">*</span>
                </label>
                <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                  id="tanggalMulai"
                  value="{{ old('tanggal_mulai', isset($izinSakit) ? $izinSakit->tanggal_mulai->format('Y-m-d') : '') }}"
                  required>
                @error('tanggal_mulai')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-3">
                <label class="form-label fw-semibold small" for="tanggalSelesai">
                  <i class="ti tabler-calendar-due me-1 text-info"></i> Tanggal Selesai <span class="text-danger">*</span>
                </label>
                <input type="date" name="tanggal_selesai"
                  class="form-control @error('tanggal_selesai') is-invalid @enderror"
                  id="tanggalSelesai"
                  value="{{ old('tanggal_selesai', isset($izinSakit) ? $izinSakit->tanggal_selesai->format('Y-m-d') : '') }}"
                  required>
                @error('tanggal_selesai')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              @if (isset($izinSakit))
                <div class="col-md-3">
                  <label class="form-label fw-semibold small" for="statusIzin">
                    <i class="ti tabler-circle-check me-1 text-info"></i> Status
                  </label>
                  <select name="status" class="form-select" id="statusIzin">
                    @foreach (['pending', 'disetujui', 'ditolak'] as $s)
                      <option value="{{ $s }}" @selected(old('status', $izinSakit->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                  </select>
                </div>
              @endif

              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="keterangan">
                  <i class="ti tabler-note me-1 text-info"></i> Keterangan
                </label>
                <textarea id="keterangan" name="keterangan" class="form-control" rows="3">{{ old('keterangan', $izinSakit->keterangan ?? '') }}</textarea>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="lampiran">
                  <i class="ti tabler-paperclip me-1 text-info"></i> Lampiran Surat (maks. 100KB — JPG/PNG/PDF)
                </label>
                @if (isset($izinSakit) && $izinSakit->lampiran)
                  <div class="mb-2">
                    <a href="{{ Storage::url($izinSakit->lampiran) }}" target="_blank" class="btn btn-sm btn-info">
                      <i class="ti tabler-eye me-1"></i> Lihat Lampiran Lama
                    </a>
                  </div>
                @endif
                <input type="file" name="lampiran" id="lampiran" class="form-control @error('lampiran') is-invalid @enderror"
                  accept=".jpg,.jpeg,.png,.pdf">
                <small class="text-muted d-block mt-1">Upload file baru untuk mengganti lampiran lama.</small>
                @error('lampiran')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('admin.izin-sakit.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Batal
              </a>
              <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm" id="btnSubmit">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ isset($izinSakit) ? 'Perbarui' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script type="module">
    $(function() {
      const $refSelect = $('#referenceId');
      if ($refSelect.length) {
        $refSelect.wrap('<div class="position-relative w-100"></div>').select2({
          width: '100%',
          placeholder: $refSelect.data('placeholder') || '-- Pilih Nama --',
          allowClear: true,
          dropdownParent: $refSelect.parent()
        });

        const tipeSelect = document.getElementById('tipePengaju');

        // Synchronize Select2 change event with quota checker
        $refSelect.on('change', function () {
          var selectedOpt = this.options[this.selectedIndex];
          if (selectedOpt && selectedOpt.value) {
            var optTipe = selectedOpt.getAttribute('data-tipe');
            if (optTipe && tipeSelect && !tipeSelect.value) {
              tipeSelect.value = optTipe;
            }
          }
          if (typeof window.scheduleCheck === 'function') {
            window.scheduleCheck();
          }
        });

        if (tipeSelect) {
          tipeSelect.addEventListener('change', function () {
            if ($refSelect.val()) {
              var selectedOpt = $refSelect[0].options[$refSelect[0].selectedIndex];
              if (selectedOpt && selectedOpt.getAttribute('data-tipe') !== this.value) {
                $refSelect.val('').trigger('change.select2');
              }
            }
          });
        }
      }
    });
  </script>

  <script>
  /**
   * Quota Checker for Izin/Sakit Form
   *
   * Mengecek sisa kuota user via AJAX (check-quota endpoint)
   * - Admin mode: menunggu user memilih tipe + nama + jenis + tanggal
   * - Siswa/Guru mode: auto-check pada saat halaman dimuat
   */
  var scheduleCheck;

  document.addEventListener('DOMContentLoaded', function () {
    const quotaCard      = document.getElementById('quotaCard');
    const quotaLoading   = document.getElementById('quotaLoading');
    const quotaError     = document.getElementById('quotaError');
    const quotaGrid      = document.getElementById('quotaGrid');
    const quotaGridContainer = document.getElementById('quotaGridContainer');
    const quotaNoLimits  = document.getElementById('quotaNoLimits');
    const quotaMessage   = document.getElementById('quotaMessage');
    const quotaPeriod    = document.getElementById('quotaPeriod');
    const btnSubmit      = document.getElementById('btnSubmit');

    const tipeSelect     = document.getElementById('tipePengaju');
    const refSelect      = document.getElementById('referenceId');
    const jenisSelect    = document.getElementById('jenisIzin');
    const tanggalMulai   = document.getElementById('tanggalMulai');
    const tanggalSelesai = document.getElementById('tanggalSelesai');

    // ─── State ────────────────────────────────────────────
    var currentUserId = null;
    var isSelfMode = false;
    var checkTimeout = null;

    // ─── Schedule check with debounce ─────────────────────
    scheduleCheck = function() {
      if (checkTimeout) clearTimeout(checkTimeout);
      checkTimeout = setTimeout(doCheck, 600);
    };
    window.scheduleCheck = scheduleCheck;

  // ─── Main check function ──────────────────────────────
  function doCheck() {
    var userId = currentUserId;

    if (!isSelfMode) {
      // Admin mode — cari user_id dari data-user-id pada option terpilih
      var tipe = tipeSelect ? tipeSelect.value : '';
      var refId = refSelect ? refSelect.value : '';

      if (!tipe || !refId) {
        quotaCard.classList.add('quota-card-hidden');
        return;
      }

      var selectedOption = refSelect.options[refSelect.selectedIndex];
      var resolvedUserId = selectedOption ? selectedOption.getAttribute('data-user-id') : '';

      if (!resolvedUserId) {
        // Tidak ada user_id — mungkin referensi belum punya akun
        quotaCard.classList.remove('quota-card-hidden');
        quotaLoading.style.display = 'none';
        quotaError.style.display = 'block';
        quotaError.textContent = 'Referensi terpilih belum memiliki akun pengguna. Kuota tidak dapat diperiksa.';
        quotaGridContainer.style.display = 'none';
        quotaNoLimits.style.display = 'none';
        return;
      }

      userId = resolvedUserId;
    }

    var jenis = jenisSelect ? jenisSelect.value : '';
    var startDate = tanggalMulai ? tanggalMulai.value : '';
    var endDate = tanggalSelesai ? tanggalSelesai.value : '';

    if (!userId || !jenis || !startDate || !endDate) {
      return;
    }

    var leaveType = jenis === 'sakit' ? 'sick' : 'permission';

    // Show loading
    quotaCard.classList.remove('quota-card-hidden');
    quotaLoading.style.display = 'flex';
    quotaError.style.display = 'none';
    quotaGridContainer.style.display = 'none';
    quotaNoLimits.style.display = 'none';

    var url = '{{ route("admin.izin-sakit.check-quota") }}' +
      '?user_id=' + encodeURIComponent(userId) +
      '&leave_type=' + encodeURIComponent(leaveType) +
      '&start_date=' + encodeURIComponent(startDate) +
      '&end_date=' + encodeURIComponent(endDate) +
      '&_=' + Date.now();

    fetch(url, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(function (res) {
        if (!res.ok) {
          return res.json().then(function (err) {
            throw new Error(err.message || 'Server error (' + res.status + ')');
          }).catch(function () {
            throw new Error('Server error (' + res.status + ')');
          });
        }
        return res.json();
      })
      .then(function (data) {
        quotaLoading.style.display = 'none';

        if (!data.success) {
          quotaError.style.display = 'block';
          quotaError.textContent = 'Gagal memeriksa kuota.';
          return;
        }

        var balances = data.balances || [];

        if (balances.length === 0) {
          quotaNoLimits.style.display = 'block';
          return;
        }

        // Show grid
        quotaGridContainer.style.display = 'block';
        quotaGrid.innerHTML = '';

        // Set period info
        var periodText = balances[0].period_code ? 'Periode: ' + balances[0].period_code : '';
        quotaPeriod.textContent = periodText;

        // Render each balance item
        balances.forEach(function (item) {
          var total = item.max_days + item.extra_days;
          var used = item.used_days;
          var remaining = item.remaining;
          var pct = total > 0 ? Math.min(100, (used / total) * 100) : 0;

          var barColor = pct >= 100 ? 'var(--das-danger)' : (pct >= 75 ? 'var(--das-warning)' : 'var(--das-success)');
          var remClass = remaining <= 0 ? '--empty' : (remaining <= 3 ? '--low' : '--safe');

          var div = document.createElement('div');
          div.className = 'quota-item';
          div.innerHTML =
            '<div class="quota-item__name">' + escapeHtml(item.name) + '</div>' +
            '<div class="quota-item__bar"><div class="quota-item__bar-fill" style="width:' + pct + '%;background:' + barColor + ';"></div></div>' +
            '<div class="quota-item__stats">' +
              '<span class="quota-item__remaining ' + remClass + '">' + remaining + ' / ' + total + '</span>' +
              '<span class="quota-item__used">Terpakai ' + used + '</span>' +
            '</div>';
          quotaGrid.appendChild(div);
        });

        // Show message
        if (data.allowed) {
          quotaMessage.className = 'quota-message --success';
          quotaMessage.innerHTML = '<i class="ti tabler-circle-check me-1"></i> Kuota mencukupi untuk pengajuan ini.';
          quotaMessage.style.display = 'block';
          if (btnSubmit) btnSubmit.disabled = false;
        } else if (data.action_type === 'warning') {
          quotaMessage.className = 'quota-message --warning';
          quotaMessage.innerHTML = '<i class="ti tabler-alert-triangle me-1"></i> <strong>Perhatian:</strong> Kuota izin Anda menipis atau habis. Pengajuan tetap dapat dikirim, namun segera hubungi admin jika perlu dispensasi.';
          quotaMessage.style.display = 'block';
          if (btnSubmit) btnSubmit.disabled = false;
        } else if (data.action_type === 'block') {
          quotaMessage.className = 'quota-message --danger';
          quotaMessage.innerHTML = '<i class="ti tabler-ban me-1"></i> <strong>Kuota Habis:</strong> Maaf, kuota izin Anda sudah habis. Pengajuan tidak dapat dilanjutkan. Hubungi admin untuk dispensasi.';
          quotaMessage.style.display = 'block';
          if (btnSubmit) btnSubmit.disabled = true;
        }
      })
      .catch(function (err) {
        quotaLoading.style.display = 'none';
        quotaError.style.display = 'block';
        quotaError.textContent = 'Terjadi kesalahan koneksi: ' + err.message;
      });
  }

  // ─── Helper: escape HTML ──────────────────────────────
  function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
});
</script>
@endsection