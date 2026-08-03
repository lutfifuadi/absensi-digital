@extends('layouts/layoutMaster')

@section('title', isset($absensiSiswa) ? 'Ubah Absensi Siswa' : 'Tambah Absensi Siswa')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <style>
    .form-alert {
      transition: all .2s ease;
    }

    /* Strict max 5px border-radius and uniform 42px height for perfect alignment */
    .form-control, .form-select, .btn, .input-group-text {
      border-radius: 5px !important;
      height: 42px !important;
      font-size: 0.85rem !important;
    }
    textarea.form-control {
      height: auto !important;
    }

    /* Custom Single Live Search Styles */
    .selected-siswa-card {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      background: rgba(16, 185, 129, 0.08);
      border: 1px solid rgba(16, 185, 129, 0.25);
      border-radius: 5px !important;
      padding: 0.35rem 0.75rem;
      height: 42px !important;
      box-sizing: border-box;
    }
    .ssc-avatar {
      width: 26px;
      height: 26px;
      border-radius: 5px;
      background: linear-gradient(135deg, #10b981, #059669);
      color: #fff;
      font-weight: 800;
      font-size: 0.75rem;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .ssc-info {
      flex: 1;
      min-width: 0;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      overflow: hidden;
    }
    .ssc-name {
      font-weight: 700;
      font-size: 0.82rem;
      color: #fff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .ssc-sub {
      font-size: 0.72rem;
      color: #94a3b8;
      white-space: nowrap;
      flex-shrink: 0;
    }
    .ssc-remove-btn {
      background: rgba(234, 84, 85, 0.12);
      border: 1px solid rgba(234, 84, 85, 0.25);
      color: #ea5455;
      width: 26px;
      height: 26px;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s;
      flex-shrink: 0;
    }
    .ssc-remove-btn:hover {
      background: #ea5455;
      color: #fff;
    }

    .siswa-search-input-wrapper { position: relative; }
    .siswa-search-results {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      max-height: 240px;
      overflow-y: auto;
      background: #0f172a;
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 5px;
      margin-top: 4px;
      z-index: 100;
      box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    }
    .siswa-search-results:empty { display: none; }
    .siswa-search-item {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      padding: 0.55rem 0.85rem;
      cursor: pointer;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      transition: background 0.15s;
    }
    .siswa-search-item:hover {
      background: rgba(115, 103, 240, 0.2);
    }
    .ssi-name { font-weight: 600; font-size: 0.83rem; color: #fff; }
    .ssi-sub  { font-size: 0.72rem; color: #94a3b8; }
  </style>
@endsection

@section('content')
  {{-- HERO HEADER --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti {{ isset($absensiSiswa) ? 'tabler-pencil' : 'tabler-calendar-time' }} text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.absensi-siswa.index') }}" class="text-white text-decoration-none">Absensi</a> /
            {{ isset($absensiSiswa) ? 'Ubah' : 'Tambah' }}
          </div>
          <h4 class="das-hero__title text-gradient-gold">
            {{ isset($absensiSiswa) ? 'Ubah Data Absensi' : 'Tambah Absensi Baru' }}
          </h4>
          <p class="das-hero__subtitle">Catat absensi siswa dengan pencarian cepat nama / NIS.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      @if ($errors->any())
        <div
          class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm form-alert"
          style="border-radius:5px; background: rgba(234, 84, 85, 0.15); color: #ea5455;">
          <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
          <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="das-panel" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 5px;">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important; background:transparent;">
          <i class="ti tabler-forms text-info"></i>
          <h6 class="das-panel__title mb-0">Informasi Lengkap Absensi</h6>
        </div>
        <div class="das-panel__body p-4">
          <form
            action="{{ isset($absensiSiswa) ? route('admin.absensi-siswa.update', $absensiSiswa) : route('admin.absensi-siswa.store') }}"
            method="POST">
            @csrf
            @if (isset($absensiSiswa))
              @method('PUT')
            @endif

            <div class="row g-4">
              {{-- FIELD SISWA — CUSTOM SINGLE LIVE SEARCH --}}
              <div class="col-md-6" id="singleSiswaSearchSection">
                <label class="form-label fw-semibold small">
                  <i class="ti tabler-user me-1 text-info"></i> Siswa <span class="text-danger">*</span>
                </label>

                {{-- Hidden input yang dikirim ke controller --}}
                <input type="hidden" id="siswa_id" name="siswa_id" value="{{ old('siswa_id', $absensiSiswa->siswa_id ?? '') }}" required>

                {{-- Card Siswa Terpilih (Tampil jika sudah memilih 1 siswa) --}}
                <div id="selectedSiswaCard" class="selected-siswa-card" style="display: none;">
                  <div class="ssc-avatar" id="sscAvatar">A</div>
                  <div class="ssc-info">
                    <div class="ssc-name" id="sscName">Nama Siswa</div>
                    <div class="ssc-sub" id="sscSub">· NIS: -</div>
                  </div>
                  <button type="button" class="ssc-remove-btn" id="btnRemoveSelectedSiswa" title="Ganti Pilihan Siswa">
                    <i class="ti tabler-x"></i>
                  </button>
                </div>

                {{-- Input Live Search (Tampil jika belum ada siswa terpilih) --}}
                <div id="siswaSearchInputWrapper" class="siswa-search-input-wrapper">
                  <input type="text" id="siswaSearchInput" class="form-control" placeholder="Ketik nama atau NIS / NISN siswa..." autocomplete="off">
                  <div id="siswaSearchResults" class="siswa-search-results"></div>
                </div>
              </div>

              {{-- FIELD KELAS (READONLY / OTOMATIS TERKUNCI DARI SISWA) --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="kelas_display">
                  <i class="ti tabler-door me-1 text-info"></i> Kelas <span class="text-danger">*</span>
                </label>
                <input type="hidden" id="kelas_id" name="kelas_id" value="{{ old('kelas_id', $absensiSiswa->kelas_id ?? '') }}" required>
                <div class="input-group" style="height: 42px;">
                  <input type="text" id="kelas_display" class="form-control" placeholder="Pilih siswa terlebih dahulu..." readonly style="background: rgba(255,255,255,0.03); cursor: not-allowed; color: #e2e8f0; font-weight: 600;">
                  <span class="input-group-text" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #10b981;" title="Kelas otomatis terisi & terkunci dari data siswa">
                    <i class="ti tabler-lock-check"></i>
                  </span>
                </div>
              </div>

              {{-- FIELD TANGGAL --}}
              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="tanggal">
                  <i class="ti tabler-calendar me-1 text-info"></i> Tanggal <span class="text-danger">*</span>
                </label>
                <input id="tanggal" type="date" name="tanggal" class="form-control"
                  value="{{ old('tanggal', isset($absensiSiswa) ? $absensiSiswa->tanggal->format('Y-m-d') : date('Y-m-d')) }}"
                  required>
              </div>

              {{-- FIELD JAM MASUK --}}
              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="jam_masuk">
                  <i class="ti tabler-clock me-1 text-info"></i> Jam Masuk
                </label>
                <input id="jam_masuk" type="time" name="jam_masuk" class="form-control"
                  value="{{ old('jam_masuk', $absensiSiswa->jam_masuk ?? '') }}">
              </div>

              {{-- FIELD JAM PULANG --}}
              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="jam_pulang">
                  <i class="ti tabler-clock-play me-1 text-info"></i> Jam Pulang
                </label>
                <input id="jam_pulang" type="time" name="jam_pulang" class="form-control"
                  value="{{ old('jam_pulang', $absensiSiswa->jam_pulang ?? '') }}">
              </div>

              {{-- FIELD STATUS --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="status">
                  <i class="ti tabler-circle-check me-1 text-info"></i> Status <span class="text-danger">*</span>
                </label>
                <select id="status" name="status" class="form-select" required>
                  @php
                    $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();
                    $statuses = ['hadir', 'sakit', 'izin', 'alpha'];
                    if (!in_array($activeJenjang, ['SD/MI', 'SMP/MTs'])) {
                        $statuses[] = 'terlambat';
                    }
                  @endphp
                  @foreach ($statuses as $status)
                    <option value="{{ $status }}"
                      {{ old('status', $absensiSiswa->status ?? '') === $status ? 'selected' : '' }}>
                      {{ ucfirst($status) }}</option>
                  @endforeach
                </select>
              </div>

              {{-- FIELD METODE --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="metode">
                  <i class="ti tabler-layout-grid me-1 text-info"></i> Metode <span class="text-danger">*</span>
                </label>
                <select id="metode" name="metode" class="form-select" required>
                  @foreach (['manual', 'qr', 'rfid'] as $metode)
                    <option value="{{ $metode }}"
                      {{ old('metode', $absensiSiswa->metode ?? 'manual') === $metode ? 'selected' : '' }}>
                      {{ strtoupper($metode) }}</option>
                  @endforeach
                </select>
              </div>

              {{-- FIELD GURU --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="guru_id">
                  <i class="ti tabler-presentation me-1 text-info"></i> Guru
                </label>
                <select id="guru_id" name="guru_id" class="form-select">
                  <option value="">Tidak dipilih</option>
                  @foreach ($guruOptions as $guru)
                    <option value="{{ $guru->id }}"
                      {{ old('guru_id', $absensiSiswa->guru_id ?? '') == $guru->id ? 'selected' : '' }}>
                      {{ $guru->nama_lengkap }}</option>
                  @endforeach
                </select>
              </div>

              {{-- FIELD KETERANGAN --}}
              <div class="col-12">
                <label class="form-label fw-semibold small" for="keterangan">
                  <i class="ti tabler-message-circle me-1 text-info"></i> Keterangan
                </label>
                <textarea id="keterangan" name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan...">{{ old('keterangan', $absensiSiswa->keterangan ?? '') }}</textarea>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('admin.absensi-siswa.index') }}" class="btn das-btn --secondary" style="height: 38px !important;">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn das-btn --primary" style="background:#7367f0; color:#fff; border-color:#7367f0; height: 38px !important;">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ isset($absensiSiswa) ? 'Perbarui Absensi' : 'Simpan Absensi' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Form Data Options
      const ALL_SISWA = [
      @foreach ($siswaOptions as $s)
        {
          id: {{ $s->id }},
          nama: @json($s->nama_lengkap ?? ''),
          nis: @json($s->nis ?? '-'),
          nisn: @json($s->nisn ?? '-'),
          kelas_id: {{ $s->kelas_id ?? 'null' }},
          kelas_nama: @json($s->kelas->nama ?? 'Tanpa Kelas')
        }@if (!$loop->last),@endif
      @endforeach
      ];

      const hiddenInput = document.getElementById('siswa_id');
      const searchInputWrapper = document.getElementById('siswaSearchInputWrapper');
      const searchInput = document.getElementById('siswaSearchInput');
      const searchResults = document.getElementById('siswaSearchResults');
      const selectedCard = document.getElementById('selectedSiswaCard');
      const sscAvatar = document.getElementById('sscAvatar');
      const sscName = document.getElementById('sscName');
      const sscSub = document.getElementById('sscSub');
      const btnRemove = document.getElementById('btnRemoveSelectedSiswa');
      
      const hiddenKelasInput = document.getElementById('kelas_id');
      const kelasDisplay = document.getElementById('kelas_display');

      // Fungsi memilih 1 siswa
      function selectSingleSiswa(siswa) {
        hiddenInput.value = siswa.id;
        sscAvatar.innerText = (siswa.nama || '?').charAt(0).toUpperCase();
        sscName.innerText = siswa.nama;
        sscSub.innerText = `· NIS: ${siswa.nis}`;

        // Auto-fill & lock field Kelas
        if (hiddenKelasInput && kelasDisplay) {
          hiddenKelasInput.value = siswa.kelas_id || '';
          kelasDisplay.value = siswa.kelas_nama || 'Tanpa Kelas';
        }

        searchInputWrapper.style.display = 'none';
        selectedCard.style.display = 'flex';
        searchResults.innerHTML = '';
        searchInput.value = '';
      }

      // Fungsi membatalkan/mengganti pilihan
      function clearSingleSiswa() {
        hiddenInput.value = '';
        if (hiddenKelasInput && kelasDisplay) {
          hiddenKelasInput.value = '';
          kelasDisplay.value = '';
          kelasDisplay.placeholder = 'Pilih siswa terlebih dahulu...';
        }
        selectedCard.style.display = 'none';
        searchInputWrapper.style.display = 'block';
        searchInput.focus();
      }

      // Render daftar pencarian
      function renderSearchResults(query) {
        if (!query) {
          searchResults.innerHTML = '';
          return;
        }

        const q = query.toLowerCase();
        const filtered = ALL_SISWA.filter(s => 
          (s.nama && s.nama.toLowerCase().includes(q)) ||
          (s.nis && s.nis.toLowerCase().includes(q)) ||
          (s.nisn && s.nisn.toLowerCase().includes(q)) ||
          (s.kelas_nama && s.kelas_nama.toLowerCase().includes(q))
        ).slice(0, 15); // Maksimal 15 hasil

        if (filtered.length === 0) {
          searchResults.innerHTML = `
            <div style="padding: 0.75rem 1rem; color: #94a3b8; font-size: 0.8rem; text-align: center;">
              Tidak ada siswa yang cocok dengan "${query}"
            </div>
          `;
          return;
        }

        searchResults.innerHTML = filtered.map(s => `
          <div class="siswa-search-item" data-id="${s.id}">
            <div style="width: 26px; height: 26px; border-radius: 5px; background: rgba(115, 103, 240, 0.2); color: #7367f0; font-weight: 700; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              ${(s.nama || '?').charAt(0).toUpperCase()}
            </div>
            <div style="flex: 1; min-width: 0;">
              <div class="ssi-name">${s.nama}</div>
              <div class="ssi-sub">Kelas: ${s.kelas_nama} · NIS: ${s.nis}</div>
            </div>
          </div>
        `).join('');
      }

      // Event listener input search
      searchInput.addEventListener('input', function () {
        renderSearchResults(this.value.trim());
      });

      // Event listener klik hasil pencarian
      searchResults.addEventListener('click', function (e) {
        const item = e.target.closest('.siswa-search-item');
        if (item && item.dataset.id) {
          const found = ALL_SISWA.find(s => String(s.id) === String(item.dataset.id));
          if (found) {
            selectSingleSiswa(found);
          }
        }
      });

      // Event listener tombol remove
      btnRemove.addEventListener('click', function () {
        clearSingleSiswa();
      });

      // Init saat halaman dimuat (jika mode edit atau re-populate old value)
      const initialSiswaId = hiddenInput.value;
      if (initialSiswaId) {
        const initialFound = ALL_SISWA.find(s => String(s.id) === String(initialSiswaId));
        if (initialFound) {
          selectSingleSiswa(initialFound);
        }
      }

      // Klik di luar menutup dropdown hasil
      document.addEventListener('click', function (e) {
        if (!e.target.closest('#singleSiswaSearchSection')) {
          searchResults.innerHTML = '';
        }
      });
    });
  </script>
@endsection
