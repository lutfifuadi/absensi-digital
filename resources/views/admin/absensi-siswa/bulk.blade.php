@extends('layouts/layoutMaster')

@section('title', 'Absensi Cepat — Bulk Input')

@php
  $isPiketRoute = request()->is('piket/*');
  $isGuruRoute = request()->is('guru/*');
  $isWaliKelasRoute = request()->is('wali-kelas/*') || request()->routeIs('wali-kelas.*');
  $urlBulkForm = $isWaliKelasRoute ? route('wali-kelas.absensi-cepat') : ($isPiketRoute ? route('piket.absensi-cepat') : ($isGuruRoute ? route('guru.absensi-cepat') : route('admin.absensi-cepat')));
  $urlBulkStore = $isWaliKelasRoute ? route('wali-kelas.absensi-cepat.store') : ($isPiketRoute ? route('piket.absensi-cepat.store') : ($isGuruRoute ? route('guru.absensi-cepat.store') : route('admin.absensi-cepat.store')));
  $urlStoreSingle = $isWaliKelasRoute ? route('wali-kelas.absensi-cepat.store-single') : ($isPiketRoute ? route('piket.absensi-cepat.store-single') : ($isGuruRoute ? route('guru.absensi-cepat.store-single') : route('admin.absensi-cepat.store-single')));
@endphp

@section('page-style')
  <style>
    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
    }

    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--bs-info) !important;
    }

    .absensi-radios .btn {
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .absensi-radios .btn-check:checked + .btn {
      transform: translateY(-2px);
      font-weight: 700;
    }

    /* Status Hadir — Green Shadow */
    .absensi-radios .btn-check:checked + .btn-outline-success {
      background-color: #28c76f !important;
      border-color: #28c76f !important;
      color: #fff !important;
      box-shadow: 0 4px 14px rgba(40, 199, 111, 0.55) !important;
    }
    .absensi-radios .btn-outline-success:hover {
      box-shadow: 0 2px 8px rgba(40, 199, 111, 0.35);
    }

    /* Status Sakit — Info Blue Shadow */
    .absensi-radios .btn-check:checked + .btn-outline-info {
      background-color: #00cfe8 !important;
      border-color: #00cfe8 !important;
      color: #fff !important;
      box-shadow: 0 4px 14px rgba(0, 207, 232, 0.55) !important;
    }
    .absensi-radios .btn-outline-info:hover {
      box-shadow: 0 2px 8px rgba(0, 207, 232, 0.35);
    }

    /* Status Izin — Warning Orange Shadow */
    .absensi-radios .btn-check:checked + .btn-outline-warning {
      background-color: #ff9f43 !important;
      border-color: #ff9f43 !important;
      color: #fff !important;
      box-shadow: 0 4px 14px rgba(255, 159, 67, 0.55) !important;
    }
    .absensi-radios .btn-outline-warning:hover {
      box-shadow: 0 2px 8px rgba(255, 159, 67, 0.35);
    }

    /* Status Alpha — Danger Red Shadow */
    .absensi-radios .btn-check:checked + .btn-outline-danger {
      background-color: #ea5455 !important;
      border-color: #ea5455 !important;
      color: #fff !important;
      box-shadow: 0 4px 14px rgba(234, 84, 85, 0.55) !important;
    }
    .absensi-radios .btn-outline-danger:hover {
      box-shadow: 0 2px 8px rgba(234, 84, 85, 0.35);
    }

    /* Status Terlambat — Primary Purple Shadow */
    .absensi-radios .btn-check:checked + .btn-outline-primary {
      background-color: #7367f0 !important;
      border-color: #7367f0 !important;
      color: #fff !important;
      box-shadow: 0 4px 14px rgba(115, 103, 240, 0.55) !important;
    }
    .absensi-radios .btn-outline-primary:hover {
      box-shadow: 0 2px 8px rgba(115, 103, 240, 0.35);
    }

    .student-row:hover {
      background: rgba(255, 255, 255, 0.02) !important;
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
            <i class="ti tabler-bolt text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Absensi / Absensi Cepat
          </div>
          <h4 class="das-hero__title text-gradient-gold">Absensi Cepat</h4>
          <p class="das-hero__subtitle">Input absensi massal untuk memproses data seluruh kelas dalam satu langkah.</p>
        </div>
      </div>

      <div class="das-hero__actions d-flex gap-2 align-items-center flex-wrap">
        @if(isset($isGuru) && $isGuru)
          <div class="badge bg-info bg-opacity-25 p-2 px-3 border border-info border-opacity-30 text-white rounded-pill">
            <i class="ti tabler-calendar-time me-1"></i> Khusus Kelas Mengajar Anda
          </div>
        @endif
        <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white rounded-pill">
          <i class="ti tabler-keyboard me-1"></i> Shortcut Keyboard: <span class="text-info ms-1 fw-bold">Angka 1-5</span>
        </div>
      </div>
    </div>
  </div>

  <div class="das-panel mb-4">
    <div class="das-panel__body">
      <form action="{{ $urlBulkForm }}" method="GET" id="form-filter">
        <div class="row align-items-end g-3">
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold" for="kelas_id">Pilih Kelas</label>
            @if(isset($isWaliKelas) && $isWaliKelas)
              <select id="kelas_id_disabled" class="form-select" disabled>
                @foreach ($kelasOptions as $kelas)
                  <option value="{{ $kelas->id }}" selected>{{ $kelas->nama }}</option>
                @endforeach
              </select>
              <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
            @else
              <select name="kelas_id" id="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                @foreach ($kelasOptions as $kelas)
                  <option value="{{ $kelas->id }}" {{ $selectedKelasId == $kelas->id ? 'selected' : '' }}>
                    {{ $kelas->nama }}
                  </option>
                @endforeach
              </select>
            @endif
          </div>
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold" for="tanggal">Tanggal Absensi</label>
            <input type="date" name="tanggal" id="tanggal_filter" class="form-control" value="{{ request('tanggal', now()->toDateString()) }}">
          </div>
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold" for="filter_status_select">Filter Status Absensi</label>
            <select name="status_filter" id="filter_status_select" class="form-select" onchange="filterStudents()">
              <option value="all" {{ request('status_filter') == 'all' ? 'selected' : '' }}>🔍 Semua Status Absensi</option>
              <option value="unselected" {{ request('status_filter') == 'unselected' ? 'selected' : '' }}>❓ Belum Dipilih / Belum Absen</option>
              <option value="hadir" {{ request('status_filter') == 'hadir' ? 'selected' : '' }}>🟢 Hadir</option>
              <option value="sakit" {{ request('status_filter') == 'sakit' ? 'selected' : '' }}>🔵 Sakit</option>
              <option value="izin" {{ request('status_filter') == 'izin' ? 'selected' : '' }}>🟡 Izin</option>
              <option value="alpha" {{ request('status_filter') == 'alpha' ? 'selected' : '' }}>🔴 Alpha</option>
              @php
                $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();
              @endphp
              @if(!in_array($activeJenjang, ['SD/MI', 'SMP/MTs']))
                <option value="terlambat" {{ request('status_filter') == 'terlambat' ? 'selected' : '' }}>🟣 Terlambat</option>
              @endif
            </select>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn das-btn --info w-100">
              <i class="ti tabler-refresh me-1"></i> Muat Siswa
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  @if ($selectedKelasId && count($siswa) > 0)
    <form action="{{ $urlBulkStore }}" method="POST">
      @csrf
      <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
      <input type="hidden" name="tanggal" id="tanggal_submit" value="{{ request('tanggal', now()->toDateString()) }}">

      <!-- Floating Bulk Action Bar -->
      <div id="bulk-action-bar" class="das-panel mb-3 p-3 border border-info border-opacity-30 rounded-3 shadow-lg"
           style="display: none; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); position: sticky; top: 75px; z-index: 1020;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-info p-2 px-3 fw-bold rounded-pill text-white" id="selected-count-badge">0 Terpilih</span>
            <button type="button" class="btn btn-link text-white-50 p-0 ms-2 small text-decoration-underline" onclick="uncheckAllRows()">Batal Pilih</button>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="text-white-50 small fw-bold me-1">Set Status Terpilih:</span>
            <button type="button" class="btn btn-sm das-btn --success" onclick="applyBulkStatusSelected('hadir')">
              <i class="ti tabler-check me-1"></i> Hadir (H)
            </button>
            <button type="button" class="btn btn-sm das-btn --info" onclick="applyBulkStatusSelected('sakit')">
              <i class="ti tabler-stethoscope me-1"></i> Sakit (S)
            </button>
            <button type="button" class="btn btn-sm das-btn --warning" onclick="applyBulkStatusSelected('izin')">
              <i class="ti tabler-file-description me-1"></i> Izin (I)
            </button>
            <button type="button" class="btn btn-sm das-btn --danger" onclick="applyBulkStatusSelected('alpha')">
              <i class="ti tabler-x me-1"></i> Alpha (A)
            </button>
            @if(!in_array($activeJenjang, ['SD/MI', 'SMP/MTs']))
              <button type="button" class="btn btn-sm das-btn --primary" onclick="applyBulkStatusSelected('terlambat')">
                <i class="ti tabler-clock me-1"></i> Terlambat (T)
              </button>
            @endif
          </div>
          <!-- Jam Masuk Massal -->
          <div class="d-flex align-items-center gap-1.5 bg-black bg-opacity-25 p-1 px-2 border border-white border-opacity-10 rounded-3 ms-md-2">
            <span class="text-white-50 small fw-bold me-1">Jam Masuk:</span>
            <input type="time" id="bulk_jam_masuk_input" class="form-control form-control-sm text-center" style="width: 110px;" value="{{ now()->format('H:i') }}">
            <button type="button" class="btn btn-sm das-btn --info ms-1" onclick="applyBulkJamMasukSelected()">
              Terapkan Jam
            </button>
          </div>
        </div>
      </div>

      <div class="das-panel overflow-hidden mb-4">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot --info"></span>
            Daftar Siswa — <span class="text-info">{{ $siswa[0]->kelas->nama ?? '' }}</span>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn das-btn --success" onclick="markAll('hadir')">
              <i class="ti tabler-check me-1"></i> Tandai Semua Hadir
            </button>
            <button type="button" class="btn das-btn --secondary" onclick="resetForm()">
              <i class="ti tabler-rotate me-1"></i> Reset
            </button>
          </div>
        </div>

        <!-- Toolbar Cari Siswa -->
        <div class="p-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3"
             style="border-color: rgba(255,255,255,0.08) !important; background: rgba(255,255,255,0.02);">
          <div class="d-flex align-items-center gap-2 flex-grow-1 w-100">
            <div class="position-relative flex-grow-1 w-100">
              <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-white-50" style="pointer-events: none; z-index: 5; font-size: 1.1rem;"></i>
              <input type="text" id="search_siswa_input" class="form-control form-control-sm w-100" style="padding-left: 2.5rem !important;" placeholder="Cari nama atau NIS..." onkeyup="filterStudents()">
            </div>
          </div>
          <div class="text-white-50 small fw-medium text-nowrap" id="filtered-count-info">
            Menampilkan <span id="visible-count">{{ count($siswa) }}</span> dari {{ count($siswa) }} siswa
          </div>
        </div>

        <div class="table-responsive">
          <table class="das-table align-middle mb-0">
            <thead>
              <tr>
                <th width="40" class="text-center ps-4">
                  <input type="checkbox" id="select_all_checkbox" onchange="toggleSelectAllRows(this.checked)" title="Pilih Semua" class="form-check-input cursor-pointer">
                </th>
                <th width="40" class="text-center">#</th>
                <th>Nama Siswa</th>
                <th width="120" class="text-center">Waktu</th>
                <th class="text-center">Status Presensi</th>
                <th class="text-end pe-4" style="width: 220px;">Keterangan</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($siswa as $index => $s)
                @php
                  $existingAbsensi = $s->absensi->first();
                  $existingStatus  = $existingAbsensi?->status;
                  $existingKet     = $existingAbsensi?->keterangan;
                  $existingJamMasuk = $existingAbsensi?->jam_masuk ? substr($existingAbsensi->jam_masuk, 0, 5) : '';
                @endphp
                <tr class="student-row" data-name="{{ strtolower($s->nama_lengkap) }}" data-nis="{{ strtolower($s->nis ?? '') }}" data-nisn="{{ strtolower($s->nisn ?? '') }}">
                  <td class="text-center ps-4">
                    <input type="checkbox" class="form-check-input row-checkbox cursor-pointer" onchange="updateBulkActionBar()">
                  </td>
                  <td class="text-center text-white-50 small">{{ $index + 1 }}</td>
                  <td>
                    <div class="fw-bold text-white">{{ $s->nama_lengkap }}</div>
                    <div class="small text-white-50 opacity-75">{{ $s->nis }} / {{ $s->nisn }}</div>
                    <input type="hidden" name="absensi[{{ $index }}][siswa_id]" value="{{ $s->id }}">
                  </td>
                  <td class="text-center">
                    <input type="time" name="absensi[{{ $index }}][jam_masuk]" class="form-control form-control-sm text-center mx-auto jam-masuk-input" style="max-width:110px;" value="{{ $existingJamMasuk }}" onchange="autoSaveSingle('{{ $s->id }}', '{{ addslashes($s->nama_lengkap) }}', null, this.closest('tr'))" title="Jam Masuk Presensi">
                  </td>
                  <td>
                    <div class="d-flex justify-content-center gap-2 absensi-radios">
                      {{-- HADIR --}}
                      <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]" 
                        id="h-{{ $s->id }}" value="hadir" {{ $existingStatus === 'hadir' ? 'checked' : '' }} autocomplete="off" onchange="updateSummary(); autoSaveSingle('{{ $s->id }}', '{{ addslashes($s->nama_lengkap) }}', this.value, this.closest('tr'))">
                      <label class="btn btn-sm btn-outline-success rounded-pill px-3" for="h-{{ $s->id }}" title="Hadir">H</label>

                      {{-- SAKIT --}}
                      <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]" 
                        id="s-{{ $s->id }}" value="sakit" {{ $existingStatus === 'sakit' ? 'checked' : '' }} autocomplete="off" onchange="updateSummary(); autoSaveSingle('{{ $s->id }}', '{{ addslashes($s->nama_lengkap) }}', this.value, this.closest('tr'))">
                      <label class="btn btn-sm btn-outline-info rounded-pill px-3" for="s-{{ $s->id }}" title="Sakit">S</label>

                      {{-- IZIN --}}
                      <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]" 
                        id="i-{{ $s->id }}" value="izin" {{ $existingStatus === 'izin' ? 'checked' : '' }} autocomplete="off" onchange="updateSummary(); autoSaveSingle('{{ $s->id }}', '{{ addslashes($s->nama_lengkap) }}', this.value, this.closest('tr'))">
                      <label class="btn btn-sm btn-outline-warning rounded-pill px-3" for="i-{{ $s->id }}" title="Izin">I</label>

                      {{-- ALPHA --}}
                      <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]" 
                        id="a-{{ $s->id }}" value="alpha" {{ $existingStatus === 'alpha' ? 'checked' : '' }} autocomplete="off" onchange="updateSummary(); autoSaveSingle('{{ $s->id }}', '{{ addslashes($s->nama_lengkap) }}', this.value, this.closest('tr'))">
                      <label class="btn btn-sm btn-outline-danger rounded-pill px-3" for="a-{{ $s->id }}" title="Alpha">A</label>

                      @php
                        $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();
                      @endphp
                      @if(!in_array($activeJenjang, ['SD/MI', 'SMP/MTs']))
                        {{-- TERLAMBAT --}}
                        <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]" 
                          id="t-{{ $s->id }}" value="terlambat" {{ $existingStatus === 'terlambat' ? 'checked' : '' }} autocomplete="off" onchange="updateSummary(); autoSaveSingle('{{ $s->id }}', '{{ addslashes($s->nama_lengkap) }}', this.value, this.closest('tr'))">
                        <label class="btn btn-sm btn-outline-primary rounded-pill px-3" for="t-{{ $s->id }}" title="Terlambat">T</label>
                      @endif
                    </div>
                  </td>
                  <td class="pe-4 text-end">
                    <input type="text" name="absensi[{{ $index }}][keterangan]" class="form-control form-control-sm ms-auto" style="max-width:200px;" placeholder="..." value="{{ $existingKet }}" onblur="autoSaveSingle('{{ $s->id }}', '{{ addslashes($s->nama_lengkap) }}', null, this.closest('tr'))">
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="border-top p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3"
          style="border-color:rgba(255,255,255,0.08) !important; background:rgba(255,255,255,0.02);">
          <div class="d-flex flex-wrap gap-4 fw-bold" id="summary-badge" style="font-size:0.85rem;">
            <div class="text-success d-flex align-items-center gap-1"><i class="ti tabler-circle-check fs-5"></i> <span id="sum-h">0</span> Hadir</div>
            <div class="text-info d-flex align-items-center gap-1"><i class="ti tabler-stethoscope fs-5"></i> <span id="sum-s">0</span> Sakit</div>
            <div class="text-warning d-flex align-items-center gap-1"><i class="ti tabler-file-description fs-5"></i> <span id="sum-i">0</span> Izin</div>
            <div class="text-danger d-flex align-items-center gap-1"><i class="ti tabler-x fs-5"></i> <span id="sum-a">0</span> Alpha</div>
            @if(!in_array($activeJenjang, ['SD/MI', 'SMP/MTs']))
              <div class="text-primary d-flex align-items-center gap-1"><i class="ti tabler-clock fs-5"></i> <span id="sum-t">0</span> Telat</div>
            @else
              <div class="d-none"><span id="sum-t">0</span></div>
            @endif
            <div class="text-white-50 opacity-75 d-flex align-items-center gap-1"><i class="ti tabler-help-circle fs-5"></i> <span id="sum-u">0</span> Belum Dipilih</div>
          </div>
          <div class="flex-shrink-0">
            <span class="badge rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1.5"
                  style="background: rgba(40, 199, 111, 0.2) !important; border: 1px solid rgba(40, 199, 111, 0.6) !important; color: #28c76f !important; font-size: 0.825rem;">
              <i class="ti tabler-cloud-check fs-5"></i> Simpan Otomatis Aktif ⚡
            </span>
          </div>
        </div>
      </div>
    </form>
  @elseif($selectedKelasId)
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center" role="alert" style="background:rgba(0,207,232,0.1);">
       <i class="ti tabler-info-circle me-3 fs-3 text-info"></i>
       <div class="text-info fw-medium">Tidak ada siswa aktif ditemukan di kelas ini.</div>
    </div>
  @else
    <div class="das-panel">
      <div class="das-panel__body text-center py-5">
        <div class="avatar avatar-xl bg-label-info mx-auto mb-4 shadow-sm" style="width:72px; height:72px;">
          <span class="avatar-initial rounded-circle"><i class="ti tabler-users-group fs-1"></i></span>
        </div>
        <h5 class="text-white fw-bold">Silahkan Pilih Kelas</h5>
        <p class="text-white-50 opacity-50 mx-auto" style="max-width:400px;">Pilih kelas di atas untuk memuat daftar siswa dan melakukan pengisian absensi massal dengan cepat.</p>
      </div>
    </div>
  @endif

  {{-- Toast Notification Container --}}
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="autoSaveToast" class="toast align-items-center text-white bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="ti tabler-circle-check fs-5"></i>
          <span id="toastMessage">Otomatis Tersimpan</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
<script>
  const urlStoreSingle = "{{ $urlStoreSingle }}";
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "{{ csrf_token() }}";

  // Filter & Search Functions
  window.filterStudents = function() {
    const query = (document.getElementById('search_siswa_input')?.value || '').toLowerCase().trim();
    const statusFilter = document.getElementById('filter_status_select')?.value || 'all';
    const rows = document.querySelectorAll('tr.student-row');
    let visibleCount = 0;

    rows.forEach(row => {
      const name = row.getAttribute('data-name') || '';
      const nis = row.getAttribute('data-nis') || '';
      const nisn = row.getAttribute('data-nisn') || '';

      const matchesSearch = !query || name.includes(query) || nis.includes(query) || nisn.includes(query);

      const checkedRadio = row.querySelector('input[type="radio"]:checked');
      const currentStatus = checkedRadio ? checkedRadio.value : 'unselected';

      let matchesStatus = true;
      if (statusFilter !== 'all') {
        if (statusFilter === 'unselected') {
          matchesStatus = !checkedRadio;
        } else {
          matchesStatus = currentStatus === statusFilter;
        }
      }

      if (matchesSearch && matchesStatus) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
        const rowCb = row.querySelector('.row-checkbox');
        if (rowCb) rowCb.checked = false;
      }
    });

    const visibleEl = document.getElementById('visible-count');
    if (visibleEl) visibleEl.innerText = visibleCount;

    window.updateBulkActionBar();
  };

  // Bulk Selection Controls
  window.toggleSelectAllRows = function(isChecked) {
    const visibleRows = document.querySelectorAll('tr.student-row:not([style*="display: none"])');
    visibleRows.forEach(row => {
      const cb = row.querySelector('.row-checkbox');
      if (cb) cb.checked = isChecked;
    });
    window.updateBulkActionBar();
  };

  window.uncheckAllRows = function() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
    const selectAllCb = document.getElementById('select_all_checkbox');
    if (selectAllCb) selectAllCb.checked = false;
    window.updateBulkActionBar();
  };

  window.updateBulkActionBar = function() {
    const checkedCbs = document.querySelectorAll('tr.student-row:not([style*="display: none"]) .row-checkbox:checked');
    const totalVisibleCbs = document.querySelectorAll('tr.student-row:not([style*="display: none"]) .row-checkbox');
    const selectAllCb = document.getElementById('select_all_checkbox');
    
    if (selectAllCb) {
      selectAllCb.checked = totalVisibleCbs.length > 0 && checkedCbs.length === totalVisibleCbs.length;
    }

    const actionBar = document.getElementById('bulk-action-bar');
    const countBadge = document.getElementById('selected-count-badge');

    if (checkedCbs.length > 0) {
      if (actionBar) actionBar.style.display = 'block';
      if (countBadge) countBadge.innerText = `${checkedCbs.length} Terpilih`;
    } else {
      if (actionBar) actionBar.style.display = 'none';
    }
  };

  window.applyBulkJamMasukSelected = function() {
    const bulkJam = document.getElementById('bulk_jam_masuk_input')?.value;
    if (!bulkJam) {
      window.showToast('danger', 'Pilih jam masuk terlebih dahulu');
      return;
    }
    const checkedRows = document.querySelectorAll('tr.student-row:not([style*="display: none"]) .row-checkbox:checked');
    if (checkedRows.length === 0) return;

    let count = 0;
    checkedRows.forEach(cb => {
      const row = cb.closest('tr');
      if (!row) return;

      const jamInp = row.querySelector('input[name*="[jam_masuk]"]');
      if (jamInp) jamInp.value = bulkJam;

      const checkedRadio = row.querySelector('input[type="radio"]:checked');
      let statusVal = checkedRadio ? checkedRadio.value : 'hadir';
      if (!checkedRadio) {
        const hRadio = row.querySelector('input[value="hadir"]');
        if (hRadio) hRadio.checked = true;
      }

      const siswaIdInp = row.querySelector('input[name*="[siswa_id]"]');
      const siswaId = siswaIdInp ? siswaIdInp.value : null;
      const namaEl = row.querySelector('.fw-bold.text-white');
      const nama = namaEl ? namaEl.innerText : '';
      if (siswaId) {
        window.autoSaveSingle(siswaId, nama, statusVal, row);
        count++;
      }
    });

    window.updateSummary();
    window.uncheckAllRows();
    window.showToast('success', `Jam Masuk ${bulkJam} diterapkan ke ${count} siswa terpilih`);
  };

  window.applyBulkStatusSelected = function(statusVal) {
    const checkedRows = document.querySelectorAll('tr.student-row:not([style*="display: none"]) .row-checkbox:checked');
    if (checkedRows.length === 0) return;

    const now = new Date();
    const currentTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

    let count = 0;
    checkedRows.forEach(cb => {
      const row = cb.closest('tr');
      if (!row) return;

      const jamInp = row.querySelector('input[name*="[jam_masuk]"]');
      if (jamInp && !jamInp.value) {
        jamInp.value = currentTime;
      }

      const radio = row.querySelector(`input[value="${statusVal}"]`);
      if (radio) {
        radio.checked = true;
        const siswaIdInp = row.querySelector('input[name*="[siswa_id]"]');
        const siswaId = siswaIdInp ? siswaIdInp.value : null;
        const namaEl = row.querySelector('.fw-bold.text-white');
        const nama = namaEl ? namaEl.innerText : '';
        if (siswaId) {
          window.autoSaveSingle(siswaId, nama, statusVal, row);
          count++;
        }
      }
    });

    window.updateSummary();
    window.uncheckAllRows();
    window.showToast('success', `Status ${statusVal.toUpperCase()} diterapkan ke ${count} siswa terpilih`);
  };

  window.autoSaveSingle = function(siswaId, namaSiswa, statusVal, rowEl) {
    if (!rowEl) return;
    const kelasId = document.querySelector('input[name="kelas_id"]')?.value || "{{ $selectedKelasId }}";
    const tanggal = document.getElementById('tanggal_submit')?.value || "{{ request('tanggal', now()->toDateString()) }}";

    if (!statusVal) {
      const checkedRadio = rowEl.querySelector('input[type="radio"]:checked');
      if (!checkedRadio) return;
      statusVal = checkedRadio.value;
    }

    const jamInp = rowEl.querySelector('input[name*="[jam_masuk]"]');
    const jamMasukVal = jamInp ? jamInp.value : '';

    const ketInp = rowEl.querySelector('input[name*="[keterangan]"]');
    const keterangan = ketInp ? ketInp.value : '';

    fetch(urlStoreSingle, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        kelas_id: kelasId,
        siswa_id: siswaId,
        tanggal: tanggal,
        status: statusVal,
        jam_masuk: jamMasukVal,
        keterangan: keterangan
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const jamInfo = jamMasukVal ? ` (${jamMasukVal})` : '';
        window.showToast('success', `${namaSiswa}: Status ${statusVal.toUpperCase()}${jamInfo} tersimpan`);
      } else {
        window.showToast('danger', data.message || 'Gagal menyimpan absensi');
      }
    })
    .catch(err => {
      console.error('AutoSave Error:', err);
      window.showToast('danger', 'Koneksi bermasalah saat auto-save');
    });
  };

  window.showToast = function(type, msg) {
    const toastEl = document.getElementById('autoSaveToast');
    if (!toastEl) return;

    toastEl.className = `toast align-items-center text-white bg-${type} border-0 shadow`;
    const msgEl = document.getElementById('toastMessage');
    if (msgEl) msgEl.innerText = msg;

    if (window.bootstrap && window.bootstrap.Toast) {
      const bsToast = window.bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2000 });
      bsToast.show();
    }
  };

  window.updateSummary = function() {
    const totalRows = document.querySelectorAll('tr.student-row').length;
    const h = document.querySelectorAll('input[value="hadir"]:checked').length;
    const s = document.querySelectorAll('input[value="sakit"]:checked').length;
    const i = document.querySelectorAll('input[value="izin"]:checked').length;
    const a = document.querySelectorAll('input[value="alpha"]:checked').length;
    const t = document.querySelectorAll('input[value="terlambat"]:checked').length;
    const u = totalRows - (h + s + i + a + t);

    const sumH = document.getElementById('sum-h');
    const sumS = document.getElementById('sum-s');
    const sumI = document.getElementById('sum-i');
    const sumA = document.getElementById('sum-a');
    const sumT = document.getElementById('sum-t');
    const sumU = document.getElementById('sum-u');

    if(sumH) sumH.innerText = h;
    if(sumS) sumS.innerText = s;
    if(sumI) sumI.innerText = i;
    if(sumA) sumA.innerText = a;
    if(sumT) sumT.innerText = t;
    if(sumU) sumU.innerText = Math.max(0, u);
  };

  window.markAll = function(status) {
    const rows = document.querySelectorAll('tr.student-row');
    rows.forEach(row => {
      const radio = row.querySelector(`input[value="${status}"]`);
      if (radio) {
        radio.checked = true;
        const siswaIdInp = row.querySelector('input[name*="[siswa_id]"]');
        const siswaId = siswaIdInp ? siswaIdInp.value : null;
        const namaEl = row.querySelector('.fw-bold.text-white');
        const nama = namaEl ? namaEl.innerText : '';
        if (siswaId) {
          window.autoSaveSingle(siswaId, nama, status, row);
        }
      }
    });
    window.updateSummary();
  };

  window.resetForm = function() {
    if(confirm('Kosongkan semua pilihan status pada tabel ini?')) {
      document.querySelectorAll('tr.student-row input[type="radio"]').forEach(radio => radio.checked = false);
      window.updateSummary();
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    window.updateSummary();
    window.filterStudents();

    // Sync tanggal filter ke tanggal submit
    const tFilter = document.getElementById('tanggal_filter');
    const tSubmit = document.getElementById('tanggal_submit');
    if (tFilter && tSubmit) {
       tFilter.addEventListener('change', () => {
          tSubmit.value = tFilter.value;
       });
    }
  });

  // Keyboard Shortcuts: 1-5 to select status for focused row
  document.addEventListener('keydown', (e) => {
     if (['1','2','3','4','5'].includes(e.key)) {
        const active = document.activeElement;
        const row = active.closest('tr');
        if (row) {
           const map = {'1':'hadir', '2':'sakit', '3':'izin', '4':'alpha', '5':'terlambat'};
           const statusVal = map[e.key];
           const radio = row.querySelector(`input[value="${statusVal}"]`);
           if (radio) {
              radio.checked = true;
              window.updateSummary();
              const siswaIdInp = row.querySelector('input[name*="[siswa_id]"]');
              const siswaId = siswaIdInp ? siswaIdInp.value : null;
              const namaEl = row.querySelector('.fw-bold.text-white');
              const nama = namaEl ? namaEl.innerText : '';
              if (siswaId) {
                window.autoSaveSingle(siswaId, nama, statusVal, row);
              }
           }
        }
     }
  });
</script>
@endsection
