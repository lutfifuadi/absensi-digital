@extends('layouts/layoutMaster')

@section('title', 'Isi Absensi Siswa per Jam — Absensi Cepat')

@section('page-style')
  <style>
    /* Override form control dark — pola absensi cepat */
    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
      border-radius: 6px !important;
      transition: border-color 0.2s ease, background 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--bs-info) !important;
      box-shadow: 0 0 0 3px rgba(0, 207, 232, 0.12);
    }

    .form-control::placeholder {
      opacity: 0.4;
    }

    .form-control[disabled],
    .form-select[disabled] {
      opacity: 0.45;
      cursor: not-allowed;
    }

    /* Radios styling — Gaya Absensi Cepat Button Pills */
    .absensi-radios .btn {
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      font-size: 0.72rem !important;
      padding: 0.25rem 0.55rem !important;
      border-radius: 5px !important;
      white-space: nowrap;
    }

    .absensi-radios .btn-check:checked + .btn {
      transform: translateY(-1px);
      font-weight: 700;
    }

    /* Status Hadir — Green Glow */
    .absensi-radios .btn-check:checked + .btn-outline-success {
      background-color: #28c76f !important;
      border-color: #28c76f !important;
      color: #fff !important;
      box-shadow: 0 3px 10px rgba(40, 199, 111, 0.5) !important;
    }
    .absensi-radios .btn-outline-success:hover {
      box-shadow: 0 2px 6px rgba(40, 199, 111, 0.35);
    }

    /* Status Terlambat — Primary Purple Glow */
    .absensi-radios .btn-check:checked + .btn-outline-primary {
      background-color: #7367f0 !important;
      border-color: #7367f0 !important;
      color: #fff !important;
      box-shadow: 0 3px 10px rgba(115, 103, 240, 0.5) !important;
    }
    .absensi-radios .btn-outline-primary:hover {
      box-shadow: 0 2px 6px rgba(115, 103, 240, 0.35);
    }

    /* Status Alpha — Danger Red Glow */
    .absensi-radios .btn-check:checked + .btn-outline-danger {
      background-color: #ea5455 !important;
      border-color: #ea5455 !important;
      color: #fff !important;
      box-shadow: 0 3px 10px rgba(234, 84, 85, 0.5) !important;
    }
    .absensi-radios .btn-outline-danger:hover {
      box-shadow: 0 2px 6px rgba(234, 84, 85, 0.35);
    }

    /* Status Izin — Warning Orange Glow */
    .absensi-radios .btn-check:checked + .btn-outline-warning {
      background-color: #ff9f43 !important;
      border-color: #ff9f43 !important;
      color: #fff !important;
      box-shadow: 0 3px 10px rgba(255, 159, 67, 0.5) !important;
    }
    .absensi-radios .btn-outline-warning:hover {
      box-shadow: 0 2px 6px rgba(255, 159, 67, 0.35);
    }

    /* Status Sakit — Info Cyan Glow */
    .absensi-radios .btn-check:checked + .btn-outline-info {
      background-color: #00cfe8 !important;
      border-color: #00cfe8 !important;
      color: #fff !important;
      box-shadow: 0 3px 10px rgba(0, 207, 232, 0.5) !important;
    }
    .absensi-radios .btn-outline-info:hover {
      box-shadow: 0 2px 6px rgba(0, 207, 232, 0.35);
    }

    /* Roster row hover & focus */
    .siswa-row-hover {
      transition: background 0.15s ease;
    }
    .siswa-row-hover:hover {
      background: rgba(255, 255, 255, 0.03) !important;
    }
    .siswa-row-hover:focus-within {
      background: rgba(0, 207, 232, 0.05) !important;
    }

    /* Sticky kolom nama */
    .roster-sticky {
      position: sticky;
      left: 0;
      background: #141b2d !important;
      z-index: 1;
    }

    .roster-table thead th {
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #8a92a6;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(0, 0, 0, 0.2);
    }

    /* Modal konfirmasi simpan */
    #modalSimpanAbsensi .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      overflow: hidden;
    }
  </style>
@endsection

@section('content')

  @php
    $user = auth()->user();
    $isToday = $tanggal === now()->toDateString();
    // Admin (super/admin_sekolah) bebas kapan pun; selain itu hanya boleh hari ini
    $canEdit = $isAdmin || $isToday;
    $isGuru = $user->isRole(\App\Models\User::ROLE_GURU);
    $isPengganti = $isGuru && $user->guru
        && app(\App\Services\AbsensiPerJamService::class)->isGuruPengganti($user->guru->id, $jadwal->id, $tanggal);

    $records = $sesiData['records'] ?? collect();
    $sesiTerisi = $sesiData['terisi'] ?? false;
    $jumlahTerisi = $sesiData['jumlah_terisi'] ?? 0;
  @endphp

  {{-- ═══════════════════════════════════════════════════════
       HERO HEADER (Gaya Absensi Cepat)
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
            @if ($isAdmin)
              Absensi / Absensi Kelas & Mapel / Form Roster Cepat
            @else
              Portal Guru / Absensi Kelas & Mapel / Form Roster Cepat
            @endif
          </div>
          <h4 class="das-hero__title text-gradient-gold">Absensi Kelas & Mapel</h4>
          <p class="das-hero__subtitle">
            <span class="text-white fw-bold">{{ $jadwal->kelas->nama ?? '-' }}</span> ·
            <span class="text-info fw-bold">{{ $jadwal->mata_pelajaran }}</span> ·
            {{ substr($jadwal->jam_mulai, 0, 5) }} – {{ substr($jadwal->jam_selesai, 0, 5) }} ·
            {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d F Y') }}
          </p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white rounded-pill">
            <i class="ti tabler-calendar me-1"></i>
            {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y') }}
          </span>
          <span class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white rounded-pill">
            <i class="ti tabler-keyboard me-1"></i> Shortcut: <span class="text-info ms-1 fw-bold">Keyboard Angka 1-5</span>
          </span>
          @if ($isPengganti)
            <span class="badge bg-label-warning p-2 px-3 rounded-pill">
              <i class="ti tabler-user-swap me-1"></i> Guru Pengganti
            </span>
          @endif
          @if ($sesiTerisi)
            <span class="badge bg-label-primary p-2 px-3 rounded-pill">
              <i class="ti tabler-check me-1"></i> Terisi ({{ $jumlahTerisi }})
            </span>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGE --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5"></i>
      <span>{{ session('error') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- VALIDASI ERROR --}}
  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
      <ul class="mb-0 ps-3 small">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- INFO: form dinonaktifkan (non-admin di luar tanggal hari ini) --}}
  @if (!$canEdit)
    <div class="alert alert-warning d-flex align-items-center gap-2 border-0 shadow-sm mb-4"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-calendar-x fs-5"></i>
      <span>Pengisian absensi dinonaktifkan untuk tanggal selain hari ini. Anda hanya dapat mengisi absensi pada
        tanggal hari ini.</span>
    </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════
       FORM ROSTER — GAYA ABSENSI CEPAT
  ═══════════════════════════════════════════════════════ --}}
  <form id="absensiForm" method="POST"
    action="{{ route('admin.absensi-per-jam.store', $jadwal->id) }}"
    x-data="absensiRoster({ sesiTerisi: {{ $sesiTerisi ? 'true' : 'false' }} })"
    @roster-change="recount" @submit.prevent="openConfirm">

    @csrf
    <input type="hidden" name="jadwal_pelajaran_id" value="{{ $jadwal->id }}">
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

    <div class="das-panel">
      {{-- Head panel --}}
      <div class="das-panel__head">
        <div class="das-panel__title">
          <i class="ti tabler-bolt text-info me-1"></i> Roster Absensi Kelas
          <span class="das-chip --info ms-1">{{ $roster->count() }} Siswa</span>
          @if ($sesiTerisi)
            <span class="das-chip --warning ms-1">
              <i class="ti tabler-pencil me-1"></i>Diedit
            </span>
          @endif
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
          <button type="button" class="das-btn das-btn--success" @click="setAllHadir()" @if (!$canEdit) disabled @endif>
            <i class="ti tabler-check-all me-1"></i> Tandai Semua Hadir
          </button>
        </div>
      </div>

      {{-- Tabel roster --}}
      <div class="table-responsive" style="max-height:65vh;overflow-y:auto;">
        <table class="das-table roster-table align-middle mb-0">
          <thead>
            <tr>
              <th class="ps-4 py-3 text-center" style="width:46px;">#</th>
              <th class="py-3 roster-sticky" style="min-width:200px;">Nama Siswa</th>
              <th class="py-3 text-center" style="min-width:340px;">Pilihan Status (Gaya Absensi Cepat)</th>
              <th class="py-3 text-center" style="min-width:120px;">Terlambat</th>
              <th class="py-3 pe-4" style="min-width:180px;">Keterangan</th>
            </tr>
          </thead>
          <tbody>
            @forelse($roster as $siswa)
              @php
                $i = $loop->index;
                $existing = $records->get($siswa->id);
                $status = old("rows.{$i}.status", $existing->status ?? 'hadir');
                $lama = old("rows.{$i}.lama_terlambat", $existing->lama_terlambat ?? '');
                $ket = old("rows.{$i}.keterangan", $existing->keterangan ?? '');
              @endphp
              <tr class="siswa-row-hover" x-data="{ status: '{{ $status }}' }" tabindex="0" @keydown.window="handleKeydown($event, {{ $i }})">
                <td class="ps-4 text-white-50 small text-center">{{ $loop->iteration }}</td>
                <td class="roster-sticky">
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-xs flex-shrink-0">
                      <span class="avatar-initial rounded-circle bg-label-info" style="font-size:0.65rem;">
                        {{ strtoupper(substr($siswa->nama_lengkap ?? 'S', 0, 1)) }}
                      </span>
                    </div>
                    <div>
                      <div class="fw-semibold text-white" style="font-size:.82rem;">{{ $siswa->nama_lengkap ?? '-' }}</div>
                      <div class="text-white-50" style="font-size:.68rem;">{{ $siswa->nis ?? '-' }}</div>
                    </div>
                  </div>
                </td>

                {{-- Status Pills (Absensi Cepat Style) --}}
                <td class="text-center py-2">
                  <input type="hidden" name="rows[{{ $i }}][siswa_id]" value="{{ $siswa->id }}">
                  <div class="absensi-radios btn-group btn-group-sm flex-wrap gap-1 justify-content-center" role="group">

                    {{-- HADIR --}}
                    <input type="radio" class="btn-check" name="rows[{{ $i }}][status]" id="status_{{ $i }}_hadir"
                      value="hadir" x-model="status" @change="$dispatch('roster-change')" data-roster-status
                      @if (!$canEdit) disabled @endif>
                    <label class="btn btn-outline-success" for="status_{{ $i }}_hadir">
                      <i class="ti tabler-user-check me-1"></i>Hadir
                    </label>

                    {{-- TERLAMBAT --}}
                    <input type="radio" class="btn-check" name="rows[{{ $i }}][status]" id="status_{{ $i }}_terlambat"
                      value="terlambat" x-model="status" @change="$dispatch('roster-change')" data-roster-status
                      @if (!$canEdit) disabled @endif>
                    <label class="btn btn-outline-primary" for="status_{{ $i }}_terlambat">
                      <i class="ti tabler-clock-exclamation me-1"></i>Terlambat
                    </label>

                    {{-- ALPHA --}}
                    <input type="radio" class="btn-check" name="rows[{{ $i }}][status]" id="status_{{ $i }}_alpha"
                      value="alpha" x-model="status" @change="$dispatch('roster-change')" data-roster-status
                      @if (!$canEdit) disabled @endif>
                    <label class="btn btn-outline-danger" for="status_{{ $i }}_alpha">
                      <i class="ti tabler-user-x me-1"></i>Alpha
                    </label>

                    {{-- IZIN --}}
                    <input type="radio" class="btn-check" name="rows[{{ $i }}][status]" id="status_{{ $i }}_izin"
                      value="izin" x-model="status" @change="$dispatch('roster-change')" data-roster-status
                      @if (!$canEdit) disabled @endif>
                    <label class="btn btn-outline-warning" for="status_{{ $i }}_izin">
                      <i class="ti tabler-file-description me-1"></i>Izin
                    </label>

                    {{-- SAKIT --}}
                    <input type="radio" class="btn-check" name="rows[{{ $i }}][status]" id="status_{{ $i }}_sakit"
                      value="sakit" x-model="status" @change="$dispatch('roster-change')" data-roster-status
                      @if (!$canEdit) disabled @endif>
                    <label class="btn btn-outline-info" for="status_{{ $i }}_sakit">
                      <i class="ti tabler-stethoscope me-1"></i>Sakit
                    </label>

                  </div>
                  @error("rows.{$i}.status")
                    <div class="invalid-feedback d-block text-center mt-1">{{ $message }}</div>
                  @enderror
                </td>

                {{-- Input Lama Terlambat --}}
                <td class="text-center">
                  <div x-show="status === 'terlambat'" x-transition>
                    <input type="number" name="rows[{{ $i }}][lama_terlambat]" min="1" max="600"
                      data-roster-lama
                      class="form-control form-control-sm text-center @error("rows.{$i}.lama_terlambat") is-invalid @enderror"
                      style="width:90px;margin:0 auto;"
                      value="{{ $lama }}"
                      placeholder="Menit"
                      :required="status === 'terlambat'"
                      @if (!$canEdit) disabled @endif
                      aria-label="Lama keterlambatan {{ $siswa->nama_lengkap }} (menit)">
                    @error("rows.{$i}.lama_terlambat")
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <span class="text-white-50 small" x-show="status !== 'terlambat'">-</span>
                </td>

                {{-- Input Keterangan --}}
                <td class="pe-4">
                  <input type="text" name="rows[{{ $i }}][keterangan]" maxlength="500"
                    data-roster-ket
                    class="form-control form-control-sm @error("rows.{$i}.keterangan") is-invalid @enderror"
                    value="{{ $ket }}"
                    placeholder="Catatan (opsional)"
                    @if (!$canEdit) disabled @endif
                    aria-label="Keterangan {{ $siswa->nama_lengkap }}">
                  @error("rows.{$i}.keterangan")
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center py-5">
                  <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                    <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
                    <span class="small">Tidak ada siswa aktif di kelas ini.</span>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Footer sticky: live counter + aksi --}}
      @if ($roster->isNotEmpty())
        <div class="p-3 d-flex align-items-center justify-content-between flex-wrap gap-2"
          style="border-top:1px solid rgba(255,255,255,0.06); background: rgba(0,0,0,0.15);">
          <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="das-chip --success"><i class="ti tabler-user-check me-1"></i>Hadir: <span x-text="counts.hadir" class="fw-bold">0</span></span>
            <span class="das-chip --primary"><i class="ti tabler-clock-exclamation me-1"></i>Terlambat: <span x-text="counts.terlambat" class="fw-bold">0</span></span>
            <span class="das-chip --danger"><i class="ti tabler-user-x me-1"></i>Alpha: <span x-text="counts.alpha" class="fw-bold">0</span></span>
            <span class="das-chip --warning"><i class="ti tabler-file-description me-1"></i>Izin: <span x-text="counts.izin" class="fw-bold">0</span></span>
            <span class="das-chip --info"><i class="ti tabler-stethoscope me-1"></i>Sakit: <span x-text="counts.sakit" class="fw-bold">0</span></span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.absensi-per-jam.index', ['tanggal' => $tanggal]) }}" class="das-btn das-btn--ghost">
              <i class="ti tabler-x me-1"></i> Batal
            </a>
            <button type="submit" class="das-btn das-btn--primary" @if (!$canEdit) disabled @endif>
              <i class="ti tabler-device-floppy me-1"></i> Simpan Absensi
            </button>
          </div>
        </div>
      @endif
    </div>
  </form>

  {{-- ═══════════════════════════════════════════════════════
       MODAL KONFIRMASI SIMPAN / TIMPA
  ═══════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalSimpanAbsensi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
      <div class="modal-content das-modal shadow-lg">

        <div class="das-modal__head" :class="isOverwrite ? 'das-modal__head--warning' : 'das-modal__head--info'">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header"
              :style="isOverwrite
                ? 'background:rgba(255,159,67,0.2);border:1px solid rgba(255,159,67,0.35);'
                : 'background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.35);'">
              <i class="ti fs-5" :class="isOverwrite ? 'tabler-alert-triangle text-warning' : 'tabler-device-floppy text-info'"></i>
            </div>
            <div>
              <h5 class="das-modal__title mb-0" x-text="isOverwrite ? 'Timpa Absensi' : 'Simpan Absensi'">Simpan Absensi</h5>
              <small class="text-white-50" x-text="isOverwrite ? 'Sesi sudah pernah diisi' : 'Konfirmasi sebelum menyimpan'">
                Konfirmasi sebelum menyimpan
              </small>
            </div>
          </div>
        </div>

        <div class="das-modal__body">
          <div class="text-center py-4">
            <div :class="isOverwrite ? 'dev-confirm-warning-icon' : 'dev-confirm-info-icon'">
              <div :class="isOverwrite ? 'dev-confirm-warning-icon__ring' : 'dev-confirm-info-icon__ring'"></div>
              <div :class="isOverwrite ? 'dev-confirm-warning-icon__symbol' : 'dev-confirm-info-icon__symbol'">
                <i class="ti" :class="isOverwrite ? 'tabler-alert-triangle' : 'tabler-device-floppy'"></i>
              </div>
            </div>
            <p class="mb-1 text-white-50" x-text="confirmMsg">Simpan absensi untuk seluruh siswa di kelas ini?</p>
            @if ($sesiTerisi)
              <small class="text-white-50 opacity-75">
                <i class="ti tabler-history me-1"></i>Perubahan akan tercatat sebagai edit pada sesi ini.
              </small>
            @endif
          </div>
        </div>

        <div class="das-modal__foot">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Batal
          </button>
          <button type="button" class="das-btn"
            :class="isOverwrite ? 'das-btn--warning-solid' : 'das-btn--primary'" @click="submitForm">
            <i class="ti tabler-device-floppy me-1"></i>
            <span x-text="isOverwrite ? 'Ya, Timpa' : 'Ya, Simpan'">Ya, Simpan</span>
          </button>
        </div>

      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    function absensiRoster(config = {}) {
      return {
        counts: { hadir: 0, terlambat: 0, sakit: 0, izin: 0, alpha: 0, dispen: 0 },
        sesiTerisi: config.sesiTerisi || false,
        confirmMsg: 'Simpan absensi untuk seluruh siswa di kelas ini?',
        isOverwrite: false,
        confirmModal: null,

        init() {
          this.recount();
        },

        // Hitung ulang counter status dari radio button ter-check (live)
        recount() {
          const c = { hadir: 0, terlambat: 0, sakit: 0, izin: 0, alpha: 0, dispen: 0 };
          document.querySelectorAll('[data-roster-status]:checked').forEach(radio => {
            if (c[radio.value] !== undefined) c[radio.value]++;
          });
          this.counts = c;
        },

        // Tandai Semua Hadir — set semua radio button hadir
        setAllHadir() {
          const msg = this.sesiTerisi
            ? 'Roster sudah pernah diisi. Timpa seluruh status menjadi Hadir?'
            : 'Tandai semua siswa sebagai Hadir?';
          if (!confirm(msg)) return;

          document.querySelectorAll('input[value="hadir"][data-roster-status]').forEach(radio => {
            radio.checked = true;
            radio.dispatchEvent(new Event('change', { bubbles: true }));
          });
          document.querySelectorAll('[data-roster-lama]').forEach(inp => inp.value = '');
          document.querySelectorAll('[data-roster-ket]').forEach(inp => inp.value = '');
          this.recount();
        },

        // Modal konfirmasi simpan
        openConfirm() {
          this.confirmMsg = this.sesiTerisi
            ? 'Sesi ini sudah pernah diisi. Menyimpan akan menimpa data lama untuk semua siswa.'
            : 'Simpan absensi untuk seluruh siswa di kelas ini?';
          this.isOverwrite = this.sesiTerisi;
          if (!this.confirmModal) {
            this.confirmModal = new bootstrap.Modal(document.getElementById('modalSimpanAbsensi'));
          }
          this.confirmModal.show();
        },

        submitForm() {
          if (this.confirmModal) this.confirmModal.hide();
          document.getElementById('absensiForm').submit();
        }
      };
    }
  </script>
@endsection
