@extends('layouts/layoutMaster')

@section('title', 'Isi Absensi Siswa per Jam — Absensi Cepat')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
  <style>
    /* Override form control dark — pola absensi cepat */
    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
      border-radius: 5px !important;
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
      padding: 0.28rem 0.6rem !important;
      border-radius: 6px !important;
      white-space: nowrap;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 0.3rem !important;
      line-height: 1.2 !important;
    }
    .absensi-radios .btn i {
      font-size: 0.88rem !important;
      line-height: 1 !important;
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

    /* Status Bolos — Dark Glow */
    .absensi-radios .btn-check:checked + .btn-outline-dark {
      background-color: #4b4b4b !important;
      border-color: #6e6b7b !important;
      color: #fff !important;
      box-shadow: 0 3px 10px rgba(110, 107, 123, 0.5) !important;
    }
    .absensi-radios .btn-outline-dark:hover {
      box-shadow: 0 2px 6px rgba(110, 107, 123, 0.35);
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


    /* Modal Layout & Proportion Refinements */
    #modalSimpanAbsensi .modal-content {
      background: #1e2640 !important;
      border: 1px solid rgba(255, 255, 255, 0.15) !important;
      border-radius: 16px !important;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6) !important;
      overflow: hidden;
    }

    #modalSimpanAbsensi .modal-header-custom {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    #modalSimpanAbsensi .modal-header-custom.--warning {
      background: linear-gradient(90deg, rgba(255, 159, 67, 0.18) 0%, rgba(30, 38, 64, 0) 100%);
    }
    #modalSimpanAbsensi .modal-header-custom.--info {
      background: linear-gradient(90deg, rgba(0, 207, 232, 0.18) 0%, rgba(30, 38, 64, 0) 100%);
    }

    .modal-icon-badge {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0;
    }
    .modal-icon-badge.--warning {
      background: rgba(255, 159, 67, 0.2);
      border: 1px solid rgba(255, 159, 67, 0.4);
      color: #ff9f43;
    }
    .modal-icon-badge.--info {
      background: rgba(0, 207, 232, 0.2);
      border: 1px solid rgba(0, 207, 232, 0.4);
      color: #00cfe8;
    }

    .modal-center-icon-wrapper {
      width: 76px;
      height: 76px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0.5rem auto 1.25rem auto;
    }
    .modal-center-icon-wrapper.--warning {
      background: rgba(255, 159, 67, 0.15);
      border: 2px solid rgba(255, 159, 67, 0.4);
      color: #ff9f43;
      box-shadow: 0 0 30px rgba(255, 159, 67, 0.3);
    }
    .modal-center-icon-wrapper.--info {
      background: rgba(0, 207, 232, 0.15);
      border: 2px solid rgba(0, 207, 232, 0.4);
      color: #00cfe8;
      box-shadow: 0 0 30px rgba(0, 207, 232, 0.3);
    }
    .modal-center-icon-wrapper i {
      font-size: 2.4rem;
      line-height: 1;
    }

    #modalSimpanAbsensi .modal-body-custom {
      padding: 2rem 1.75rem 1.75rem 1.75rem;
      text-align: center;
    }

    #modalSimpanAbsensi .modal-footer-custom {
      padding: 1.1rem 1.5rem;
      background: rgba(0, 0, 0, 0.25);
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 0.75rem;
    }

    #modalSimpanAbsensi .modal-btn-confirm-warning {
      background: linear-gradient(135deg, #ff9f43 0%, #d97706 100%) !important;
      color: #ffffff !important;
      font-weight: 700 !important;
      border: none !important;
      padding: 0.6rem 1.35rem !important;
      border-radius: 8px !important;
      box-shadow: 0 4px 15px rgba(255, 159, 67, 0.45) !important;
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      transition: all 0.2s ease;
    }
    #modalSimpanAbsensi .modal-btn-confirm-warning:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 159, 67, 0.65) !important;
    }

    #modalSimpanAbsensi .modal-btn-confirm-info {
      background: linear-gradient(135deg, #7367f0 0%, #5e50ee 100%) !important;
      color: #ffffff !important;
      font-weight: 700 !important;
      border: none !important;
      padding: 0.6rem 1.35rem !important;
      border-radius: 8px !important;
      box-shadow: 0 4px 15px rgba(115, 103, 240, 0.45) !important;
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      transition: all 0.2s ease;
    }
    #modalSimpanAbsensi .modal-btn-confirm-info:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(115, 103, 240, 0.65) !important;
    }

    #modalSimpanAbsensi .modal-btn-cancel {
      background: rgba(255, 255, 255, 0.08) !important;
      color: #cbd5e1 !important;
      border: 1px solid rgba(255, 255, 255, 0.15) !important;
      font-weight: 600 !important;
      padding: 0.6rem 1.35rem !important;
      border-radius: 8px !important;
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      transition: all 0.2s ease;
    }
    #modalSimpanAbsensi .modal-btn-cancel:hover {
      background: rgba(255, 255, 255, 0.15) !important;
      color: #ffffff !important;
    }

    /* Enforce 5px border-radius across all UI elements on this page */
    .das-hero,
    .das-hero__badge,
    .das-hero__logo-wrapper,
    .das-hero__logo-placeholder,
    .das-panel,
    .das-chip,
    .das-btn,
    .btn,
    .badge,
    .alert,
    .alert-flash-success,
    .alert-flash-danger,
    .alert-flash-warning,
    .form-control,
    .form-select,
    .absensi-radios .btn,
    #modalSimpanAbsensi .modal-content,
    .modal-icon-badge,
    #modalSimpanAbsensi .modal-btn-confirm-warning,
    #modalSimpanAbsensi .modal-btn-confirm-info,
    #modalSimpanAbsensi .modal-btn-cancel {
      border-radius: 5px !important;
    }
    .alert-flash-success {
      background: linear-gradient(135deg, #28c76f 0%, #1f9d55 100%) !important;
      color: #ffffff !important;
      border: 1px solid rgba(255, 255, 255, 0.3) !important;
      box-shadow: 0 6px 22px rgba(40, 199, 111, 0.5) !important;
    }
    .alert-flash-danger {
      background: linear-gradient(135deg, #ea5455 0%, #c53030 100%) !important;
      color: #ffffff !important;
      border: 1px solid rgba(255, 255, 255, 0.3) !important;
      box-shadow: 0 6px 22px rgba(234, 84, 85, 0.5) !important;
    }
    .alert-flash-warning {
      background: linear-gradient(135deg, #ff9f43 0%, #d97706 100%) !important;
      color: #ffffff !important;
      border: 1px solid rgba(255, 255, 255, 0.3) !important;
      box-shadow: 0 6px 22px rgba(255, 159, 67, 0.5) !important;
    }

    /* Enhanced Button & Header Styling — Bright & Striking */
    .das-btn--success {
      background: linear-gradient(135deg, #28c76f 0%, #20c997 100%) !important;
      color: #ffffff !important;
      font-weight: 700 !important;
      border: none !important;
      box-shadow: 0 4px 14px rgba(40, 199, 111, 0.45) !important;
      transition: all 0.2s ease;
    }
    .das-btn--success:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(40, 199, 111, 0.6) !important;
    }
    .das-chip {
      font-weight: 700 !important;
      letter-spacing: 0.3px;
    }

    /* Modern Auto-Save Toast Styling (Glassmorphism & Glow) */
    .toast-autosave {
      background: rgba(18, 24, 38, 0.94) !important;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(40, 199, 111, 0.4) !important;
      box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), 0 0 24px rgba(40, 199, 111, 0.2) !important;
      border-radius: 14px !important;
      min-width: 320px;
    }
    .toast-autosave--error {
      border: 1px solid rgba(234, 84, 85, 0.4) !important;
      box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), 0 0 24px rgba(234, 84, 85, 0.2) !important;
    }
    .toast-autosave__icon-box {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(40, 199, 111, 0.2);
      color: #28c76f;
      box-shadow: 0 0 14px rgba(40, 199, 111, 0.35);
      animation: toastIconPulse 2s infinite ease-in-out;
    }
    .toast-autosave__icon-box--error {
      background: rgba(234, 84, 85, 0.2);
      color: #ea5455;
      box-shadow: 0 0 14px rgba(234, 84, 85, 0.35);
      animation: none;
    }
    @keyframes toastIconPulse {
      0% { transform: scale(1); box-shadow: 0 0 8px rgba(40, 199, 111, 0.3); }
      50% { transform: scale(1.08); box-shadow: 0 0 18px rgba(40, 199, 111, 0.6); }
      100% { transform: scale(1); box-shadow: 0 0 8px rgba(40, 199, 111, 0.3); }
    }
    .badge-dot-pulse {
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: #28c76f;
      box-shadow: 0 0 8px #28c76f;
      animation: dotPulse 1.5s infinite ease-in-out;
    }
    @keyframes dotPulse {
      0% { transform: scale(0.9); opacity: 0.7; }
      50% { transform: scale(1.3); opacity: 1; }
      100% { transform: scale(0.9); opacity: 0.7; }
    }
    .row-saved-flash {
      animation: flashGreenRow 1.2s ease-out;
    }
    @keyframes flashGreenRow {
      0% { background-color: rgba(40, 199, 111, 0.3) !important; }
      100% { background-color: transparent !important; }
    }

    /* SweetAlert2 Toast Ultra-Neat Override (No circular ring, perfectly aligned) */
    .custom-swal-toast-popup {
      background: #1e2640 !important;
      border: 1px solid rgba(40, 199, 111, 0.4) !important;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 15px rgba(40, 199, 111, 0.2) !important;
      border-radius: 12px !important;
      padding: 0.65rem 1rem !important;
      align-items: center !important;
    }
    .custom-swal-toast-title {
      font-size: 0.84rem !important;
      font-weight: 600 !important;
      color: #ffffff !important;
      margin: 0 !important;
      padding: 0 !important;
      line-height: 1.4 !important;
    }
    .swal2-toast .swal2-icon {
      margin: 0 0.65rem 0 0 !important;
      transform: scale(0.75) !important;
    }
    .swal2-toast .swal2-timer-progress-bar-container,
    .swal2-toast .swal2-timer-progress-bar {
      display: none !important;
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

    $waNomorAdminRaw = \App\Models\Pengaturan::where('key', 'wa_nomor_admin')->value('value') ?? '';
    $cleanWaAdmin = preg_replace('/[^0-9]/', '', $waNomorAdminRaw);
    if (str_starts_with($cleanWaAdmin, '0')) {
        $cleanWaAdmin = '62' . substr($cleanWaAdmin, 1);
    }

    $defaultWaTemplate = \App\Models\Pengaturan::where('key', 'wa_template_rekap_presensi')->value('value')
        ?: "*LAPORAN KONDISI MURID MATA PELAJARAN {mapel}*\nKelas: {kelas}\nHari/Tanggal: {hari_tanggal}\nJam ke: {jam_ke}\n\nJumlah Murid: {jumlah_murid} orang\n* Hadir : {total_hadir} orang\n* Alpa : {total_alpa} Orang\n{daftar_alpa}\n* Izin : {total_izin} Orang\n{daftar_izin}\n* Sakit : {total_sakit} Orang\n{daftar_sakit}\n* Terlambat : {total_terlambat} Orang\n{daftar_terlambat}";
  @endphp

  {{-- ═══════════════════════════════════════════════════════
       MAIN ALPINE COMPONENT WRAPPER
  ═══════════════════════════════════════════════════════ --}}
  <div x-data="absensiRoster({ sesiTerisi: {{ $sesiTerisi ? 'true' : 'false' }} })" @roster-change="recount">

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
          <button type="button" class="btn btn-sm btn-success fw-bold rounded-pill px-3 shadow-sm" @click="openWAModal()">
            <i class="ti tabler-brand-whatsapp me-1 fs-5"></i> Rekap & Share WA
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGE --}}
  @if (session('success'))
    <div class="alert alert-flash-success alert-dismissible d-flex align-items-center gap-2 mb-4 p-3 shadow-lg" role="alert">
      <i class="ti tabler-circle-check fs-4 text-white"></i>
      <span class="fw-bold fs-6 text-white">{{ session('success') }}</span>
      <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" style="opacity: 0.9;"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-flash-danger alert-dismissible d-flex align-items-center gap-2 mb-4 p-3 shadow-lg" role="alert">
      <i class="ti tabler-alert-circle fs-4 text-white"></i>
      <span class="fw-bold fs-6 text-white">{{ session('error') }}</span>
      <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" style="opacity: 0.9;"></button>
    </div>
  @endif

  {{-- VALIDASI ERROR --}}
  @if ($errors->any())
    <div class="alert alert-flash-danger alert-dismissible d-flex align-items-start gap-2 mb-4 p-3 shadow-lg" role="alert">
      <i class="ti tabler-alert-circle fs-4 text-white mt-1 flex-shrink-0"></i>
      <ul class="mb-0 ps-3 small fw-bold text-white">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" style="opacity: 0.9;"></button>
    </div>
  @endif

  {{-- INFO: form dinonaktifkan (non-admin di luar tanggal hari ini) --}}
  @if (!$canEdit)
    <div class="alert alert-flash-warning d-flex align-items-center gap-2 mb-4 p-3 shadow-lg" role="alert">
      <i class="ti tabler-calendar-x fs-4 text-white"></i>
      <span class="fw-bold text-white small">Pengisian absensi dinonaktifkan untuk tanggal selain hari ini. Anda hanya dapat mengisi absensi pada tanggal hari ini.</span>
    </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════
       FORM ROSTER — GAYA ABSENSI CEPAT
  ═══════════════════════════════════════════════════════ --}}
  <div>
    <form id="absensiForm" method="POST"
      action="{{ route('admin.absensi-per-jam.store', $jadwal->id) }}"
      @submit.prevent="openConfirm">

    @csrf
    <input type="hidden" name="jadwal_pelajaran_id" value="{{ $jadwal->id }}">
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

    <div class="das-panel">
      {{-- Head panel --}}
      <div class="das-panel__head">
        <div class="das-panel__title d-flex align-items-center flex-wrap gap-1">
          <i class="ti tabler-bolt text-info me-1"></i> Roster Absensi Kelas
          <span class="das-chip --info ms-1">{{ $roster->count() }} Siswa</span>
          @if ($sesiTerisi)
            <span class="das-chip --warning ms-1">
              <i class="ti tabler-pencil me-1"></i>Diedit
            </span>
          @endif
          <span class="das-chip --success ms-1 d-inline-flex align-items-center gap-1" title="Perubahan otomatis tersimpan ke Database">
            <span class="badge-dot-pulse"></span> Auto-Save DB
          </span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
          <button type="button" class="das-btn das-btn--success" @click="setAllHadir()" @if (!$canEdit) disabled @endif>
            <i class="ti tabler-check-all me-1"></i> Tandai Semua Hadir
          </button>
        </div>
      </div>

      {{-- Tabel roster dengan PerfectScrollbar --}}
      <div id="rosterTableContainer" class="table-responsive position-relative" style="max-height:65vh;">
        <table class="das-table roster-table align-middle mb-0">
          <thead>
            <tr>
              <th class="ps-4 py-3 text-center" style="width:46px;">#</th>
              <th class="py-3 roster-sticky" style="min-width:200px;">Nama Siswa</th>
              <th class="py-3 text-center" style="min-width:340px;">Pilihan Status (Gaya Absensi Cepat)</th>
              <th class="py-3 text-center" style="min-width:120px;">Terlambat</th>
              <th class="py-3 pe-4 text-center" style="width:130px; min-width:120px;">Keterangan</th>
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
              <tr class="siswa-row-hover" x-data="{ status: '{{ $status }}' }" tabindex="0">
                <td class="ps-4 text-white-50 small text-center">{{ $loop->iteration }}</td>
                <td class="roster-sticky">
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-xs flex-shrink-0">
                      <span class="avatar-initial rounded-circle bg-label-info" style="font-size:0.65rem;">
                        {{ strtoupper(substr($siswa->nama_lengkap ?? 'S', 0, 1)) }}
                      </span>
                    </div>
                    <div>
                      <div class="fw-semibold text-white d-flex align-items-center gap-1 flex-wrap" style="font-size:.82rem;">
                        <span>{{ $siswa->nama_lengkap ?? '-' }}</span>
                        <template x-if="status !== 'hadir'">
                          <a :href="getJapriUrl('{{ addslashes($siswa->nama_lengkap ?? 'Siswa') }}', status, '{{ $siswa->no_hp_ortu ?? $siswa->no_hp ?? '' }}')" 
                             target="_blank" 
                             class="badge bg-label-success ms-1 px-1.5 py-0.5 text-success border-0 d-inline-flex align-items-center gap-1" 
                             title="Japri Ortu WA"
                             style="text-decoration:none; font-size:0.65rem;">
                            <i class="ti tabler-brand-whatsapp"></i>Japri
                          </a>
                        </template>
                      </div>
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
                      value="hadir" x-model="status" @change="$dispatch('roster-change'); autoSaveSingle({{ $siswa->id }}, '{{ addslashes($siswa->nama_lengkap) }}', $el.value, $el.closest('tr'))" data-roster-status
                      @if (!$canEdit) disabled @endif>
                    <label class="btn btn-outline-success" for="status_{{ $i }}_hadir">
                      <i class="ti tabler-user-check me-1"></i>Hadir
                    </label>

                    {{-- TERLAMBAT --}}
                    <input type="radio" class="btn-check" name="rows[{{ $i }}][status]" id="status_{{ $i }}_terlambat"
                      value="terlambat" x-model="status" @change="$dispatch('roster-change'); autoSaveSingle({{ $siswa->id }}, '{{ addslashes($siswa->nama_lengkap) }}', $el.value, $el.closest('tr'))" data-roster-status
                      @if (!$canEdit) disabled @endif>
                    <label class="btn btn-outline-primary" for="status_{{ $i }}_terlambat">
                      <i class="ti tabler-clock-exclamation me-1"></i>Terlambat
                    </label>

                    {{-- ALPHA --}}
                    <input type="radio" class="btn-check" name="rows[{{ $i }}][status]" id="status_{{ $i }}_alpha"
                      value="alpha" x-model="status" @change="$dispatch('roster-change'); autoSaveSingle({{ $siswa->id }}, '{{ addslashes($siswa->nama_lengkap) }}', $el.value, $el.closest('tr'))" data-roster-status
                      @if (!$canEdit) disabled @endif>
                    <label class="btn btn-outline-danger" for="status_{{ $i }}_alpha">
                      <i class="ti tabler-user-x me-1"></i>Alpha
                    </label>

                    {{-- IZIN --}}
                    <input type="radio" class="btn-check" name="rows[{{ $i }}][status]" id="status_{{ $i }}_izin"
                      value="izin" x-model="status" @change="$dispatch('roster-change'); autoSaveSingle({{ $siswa->id }}, '{{ addslashes($siswa->nama_lengkap) }}', $el.value, $el.closest('tr'))" data-roster-status
                      @if (!$canEdit) disabled @endif>
                    <label class="btn btn-outline-warning" for="status_{{ $i }}_izin">
                      <i class="ti tabler-file-description me-1"></i>Izin
                    </label>

                    {{-- SAKIT --}}
                    <input type="radio" class="btn-check" name="rows[{{ $i }}][status]" id="status_{{ $i }}_sakit"
                      value="sakit" x-model="status" @change="$dispatch('roster-change'); autoSaveSingle({{ $siswa->id }}, '{{ addslashes($siswa->nama_lengkap) }}', $el.value, $el.closest('tr'))" data-roster-status
                      @if (!$canEdit) disabled @endif>
                    <label class="btn btn-outline-info" for="status_{{ $i }}_sakit">
                      <i class="ti tabler-stethoscope me-1"></i>Sakit
                    </label>

                    {{-- BOLOS --}}
                    <input type="radio" class="btn-check" name="rows[{{ $i }}][status]" id="status_{{ $i }}_bolos"
                      value="bolos" x-model="status" @change="$dispatch('roster-change'); autoSaveSingle({{ $siswa->id }}, '{{ addslashes($siswa->nama_lengkap) }}', $el.value, $el.closest('tr'))" data-roster-status
                      @if (!$canEdit) disabled @endif>
                    <label class="btn btn-outline-dark" for="status_{{ $i }}_bolos">
                      <i class="ti tabler-walk me-1"></i>Bolos
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
                      @input="autoSaveSingle({{ $siswa->id }}, '{{ addslashes($siswa->nama_lengkap) }}', status, $el.closest('tr'))"
                      @if (!$canEdit) disabled @endif
                      aria-label="Lama keterlambatan {{ $siswa->nama_lengkap }} (menit)">
                    @error("rows.{$i}.lama_terlambat")
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <span class="text-white-50 small" x-show="status !== 'terlambat'">-</span>
                </td>

                {{-- Input Keterangan --}}
                <td class="pe-4 text-center" style="width:130px;">
                  <input type="text" name="rows[{{ $i }}][keterangan]" maxlength="500"
                    data-roster-ket
                    class="form-control form-control-sm text-center @error("rows.{$i}.keterangan") is-invalid @enderror"
                    style="width:125px; margin:0 auto; font-size:0.78rem;"
                    value="{{ $ket }}"
                    placeholder="Catatan"
                    @input="$el.dataset.userEdited = 'true'; autoSaveSingle({{ $siswa->id }}, '{{ addslashes($siswa->nama_lengkap) }}', status, $el.closest('tr'))"
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
            <span class="das-chip --secondary"><i class="ti tabler-walk me-1"></i>Bolos: <span x-text="counts.bolos" class="fw-bold">0</span></span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="das-btn das-btn--success" @click="openWAModal()">
              <i class="ti tabler-brand-whatsapp me-1"></i> Rekap & Share WA
            </button>
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
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
      <div class="modal-content shadow-lg">

        {{-- Header --}}
        <div class="modal-header-custom" :class="isOverwrite ? '--warning' : '--info'">
          <div class="modal-icon-badge" :class="isOverwrite ? '--warning' : '--info'">
            <i class="ti" :class="isOverwrite ? 'tabler-alert-triangle' : 'tabler-device-floppy'"></i>
          </div>
          <div>
            <h5 class="fw-bold text-white mb-0" style="font-size:1.05rem;" x-text="isOverwrite ? 'Timpa Absensi' : 'Simpan Absensi'">Simpan Absensi</h5>
            <small class="text-white-50 fs-7" x-text="isOverwrite ? 'Sesi sudah pernah diisi' : 'Konfirmasi sebelum menyimpan'">Konfirmasi sebelum menyimpan</small>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        {{-- Body --}}
        <div class="modal-body-custom">
          <div class="modal-center-icon-wrapper" :class="isOverwrite ? '--warning' : '--info'">
            <i class="ti" :class="isOverwrite ? 'tabler-alert-triangle-filled' : 'tabler-device-floppy'"></i>
          </div>
          <h6 class="fw-bold text-white fs-6 mb-2" x-text="isOverwrite ? 'Konfirmasi Timpa Data Absensi' : 'Konfirmasi Simpan Absensi'">Konfirmasi Simpan Absensi</h6>
          <p class="text-white-50 small mb-3 px-2 lh-base" x-text="confirmMsg">Simpan absensi untuk seluruh siswa di kelas ini?</p>
          @if ($sesiTerisi)
            <div class="d-inline-flex align-items-center gap-1 p-2 px-3 rounded-pill bg-black bg-opacity-30 border border-warning border-opacity-20 text-warning small">
              <i class="ti tabler-history fs-6 me-1"></i>
              <span>Perubahan akan tercatat sebagai edit pada sesi ini.</span>
            </div>
          @endif
        </div>

        {{-- Footer --}}
        <div class="modal-footer-custom">
          <button type="button" class="modal-btn-cancel" data-bs-dismiss="modal">
            <i class="ti tabler-x fs-6 me-1"></i> Batal
          </button>
          <button type="button" :class="isOverwrite ? 'modal-btn-confirm-warning' : 'modal-btn-confirm-info'" @click="submitForm">
            <i class="ti" :class="isOverwrite ? 'tabler-refresh' : 'tabler-device-floppy'"></i>
            <span x-text="isOverwrite ? 'Ya, Timpa Data' : 'Ya, Simpan'">Ya, Simpan</span>
          </button>
        </div>
      </div>
    </div>
  </div>
  {{-- ═══════════════════════════════════════════════════════
       MODAL SHARE REKAP WHATSAPP
  ═══════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalShareWA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content shadow-lg" style="background:#1e2640; border: 1px solid rgba(255,255,255,0.15); border-radius:16px;">
        <div class="modal-header border-bottom border-white border-opacity-10 py-3 px-4 d-flex align-items-center">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-sm rounded p-2 d-flex align-items-center justify-content-center" style="background:rgba(37,211,102,0.18); color:#25d366;">
              <i class="ti tabler-brand-whatsapp fs-4"></i>
            </div>
            <div>
              <h5 class="fw-bold text-white mb-0" style="font-size:1.05rem;">Rekap Presensi & Share WhatsApp</h5>
              <small class="text-white-50 fs-7">Pratinjau pesan rekapitulasi presensi yang siap dikirim ke WhatsApp</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Stat Pills -->
          <div class="d-flex flex-wrap gap-2 mb-3 p-3 rounded" style="background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.06);">
            <span class="das-chip --success"><i class="ti tabler-user-check me-1"></i>Hadir: <span x-text="counts.hadir" class="fw-bold">0</span></span>
            <span class="das-chip --danger"><i class="ti tabler-user-x me-1"></i>Alpha: <span x-text="counts.alpha" class="fw-bold">0</span></span>
            <span class="das-chip --warning"><i class="ti tabler-file-description me-1"></i>Izin: <span x-text="counts.izin" class="fw-bold">0</span></span>
            <span class="das-chip --info"><i class="ti tabler-stethoscope me-1"></i>Sakit: <span x-text="counts.sakit" class="fw-bold">0</span></span>
            <span class="das-chip --primary"><i class="ti tabler-clock-exclamation me-1"></i>Terlambat: <span x-text="counts.terlambat" class="fw-bold">0</span></span>
          </div>

          <!-- Generated WA Text Box -->
          <div class="position-relative mb-3">
            <label class="form-label text-white-50 small fw-bold mb-2">Pratinjau Redaksi Teks WA:</label>
            <textarea x-model="generatedWAText" class="form-control font-monospace text-white bg-dark bg-opacity-50 p-3" rows="10" readonly style="font-size:0.85rem; line-height:1.6; border-radius:10px !important; border:1px solid rgba(255,255,255,0.15);"></textarea>
          </div>

          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-info" @click="openEditorModal()">
              <i class="ti tabler-edit me-1"></i>Atur Redaksi Teks
            </button>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-success" @click="copyWAText()">
                <i class="ti tabler-copy me-1"></i>Salin Teks
              </button>
              <button type="button" class="btn btn-sm btn-success fw-bold" @click="openWAGroup()" title="Kirim rekap presensi ke Nomor Admin ({{ $cleanWaAdmin ?? 'Admin' }})">
                <i class="ti tabler-brand-whatsapp me-1"></i>Kirim Rekap ke WA Admin
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       MODAL EDITOR REDAKSI TEMPLATE WA
  ═══════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalEditTemplateWA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content shadow-lg" style="background:#1e2640; border: 1px solid rgba(255,255,255,0.15); border-radius:16px;">
        <div class="modal-header border-bottom border-white border-opacity-10 py-3 px-4 d-flex align-items-center">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-sm rounded p-2 d-flex align-items-center justify-content-center" style="background:rgba(115,103,240,0.18); color:#7367f0;">
              <i class="ti tabler-settings-automation fs-4"></i>
            </div>
            <div>
              <h5 class="fw-bold text-white mb-0" style="font-size:1.05rem;">Pengaturan Redaksi Template WA</h5>
              <small class="text-white-50 fs-7">Sesuaikan susunan kata-kata rekap presensi sesuai keinginan Anda</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Variable Chips -->
          <div class="mb-3">
            <label class="form-label text-white-50 small fw-bold mb-2">Klik Variabel untuk Menyisipkan ke Editor:</label>
            <div class="d-flex flex-wrap gap-1">
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{mapel}')">+ Mapel</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{kelas}')">+ Kelas</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{hari_tanggal}')">+ Tanggal</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{jam_ke}')">+ Jam Ke</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{jumlah_murid}')">+ Total Murid</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{total_hadir}')">+ Hadir</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{total_alpa}')">+ Total Alpa</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{daftar_alpa}')">+ Daftar Alpa</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{total_izin}')">+ Total Izin</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{daftar_izin}')">+ Daftar Izin</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{total_sakit}')">+ Total Sakit</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{daftar_sakit}')">+ Daftar Sakit</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{total_terlambat}')">+ Total Terlambat</button>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="insertTag('{daftar_terlambat}')">+ Daftar Terlambat</button>
            </div>
          </div>

          <!-- Textarea Editor -->
          <div class="mb-3">
            <label class="form-label text-white-50 small fw-bold mb-2">Editor Template Teks:</label>
            <textarea id="templateWaTextarea" x-model="editorTemplateWA" class="form-control font-monospace text-white bg-dark p-3" rows="9" style="font-size:0.85rem; line-height:1.5; border-radius:10px !important; border:1px solid rgba(255,255,255,0.2);"></textarea>
          </div>

          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-warning" @click="resetDefaultTemplate()">
              <i class="ti tabler-rotate-2 me-1"></i>Reset ke Default Sekolah
            </button>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                Batal
              </button>
              <button type="button" class="btn btn-sm btn-primary fw-bold" @click="saveCustomTemplate()">
                <i class="ti tabler-device-floppy me-1"></i>Simpan Redaksi
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- FLOATING TOAST NOTIFICATION CONTAINER (AUTO-SAVE TOP-RIGHT) --}}
<div id="autoSaveToastContainer" class="toast-container position-fixed top-0 end-0 p-4" style="z-index: 9999;">
  <div id="toastNotification" class="toast toast-autosave border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex align-items-center p-3">
      <div id="toastIconBox" class="toast-autosave__icon-box me-3">
        <i id="toastIcon" class="ti tabler-cloud-check fs-4"></i>
      </div>
      <div class="toast-body p-0 me-auto">
        <div class="d-flex align-items-center gap-1.5 mb-1">
          <span id="toastBadge" class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 px-2 py-0.5" style="font-size: 0.68rem; font-weight: 700;">
            <i class="ti tabler-database-check me-1"></i>TERSIMPAN DI DATABASE
          </span>
        </div>
        <div id="toastTitle" class="fw-bold text-white small" style="font-size:0.88rem;">Otomatis Tersimpan</div>
        <div id="toastMessage" class="text-white-50 fs-7" style="font-size:0.78rem;">Status absensi berhasil diperbarui.</div>
      </div>
      <button type="button" class="btn-close btn-close-white ms-3 mb-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="progress" style="height: 3px; background: rgba(255,255,255,0.08);">
      <div id="toastProgressBar" class="progress-bar bg-success" style="width: 100%;"></div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
  <script>
    window.showAutoSaveToast = function(type, title, msg) {
      const displayTitle = msg ? `${msg}` : title;
      const iconType = type === 'success' ? 'success' : 'error';

      if (typeof Swal !== 'undefined') {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: iconType,
          title: displayTitle,
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: false,
          background: '#1e2640',
          color: '#ffffff',
          customClass: {
            popup: 'custom-swal-toast-popup',
            title: 'custom-swal-toast-title'
          }
        });
        return;
      }

      // Safe Pure JS Toast Fallback (no bootstrap JS dependency needed)
      let container = document.getElementById('autoSaveToastContainer');
      if (!container) {
        container = document.createElement('div');
        container.id = 'autoSaveToastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-4';
        container.style.zIndex = '99999';
        document.body.appendChild(container);
      }

      const toastNode = document.createElement('div');
      toastNode.className = `toast toast-autosave ${type === 'error' ? 'toast-autosave--error' : ''} show border-0`;
      toastNode.setAttribute('role', 'alert');
      toastNode.innerHTML = `
        <div class="d-flex align-items-center p-3">
          <div class="toast-autosave__icon-box ${type === 'error' ? 'toast-autosave__icon-box--error' : ''} me-3">
            <i class="ti ${type === 'success' ? 'tabler-cloud-check' : 'tabler-alert-circle-filled'} fs-4"></i>
          </div>
          <div class="toast-body p-0 me-auto">
            <div class="d-flex align-items-center gap-1.5 mb-1">
              <span class="badge ${type === 'success' ? 'bg-success' : 'bg-danger'} bg-opacity-20 ${type === 'success' ? 'text-success border-success' : 'text-danger border-danger'} border border-opacity-30 px-2 py-0.5" style="font-size: 0.68rem; font-weight: 700;">
                <i class="ti ${type === 'success' ? 'tabler-database-check' : 'tabler-alert-triangle'} me-1"></i>${type === 'success' ? 'TERSIMPAN DI DATABASE' : 'GAGAL AUTO-SAVE'}
              </span>
            </div>
            <div class="fw-bold text-white small" style="font-size:0.88rem;">${title}</div>
            <div class="text-white-50 fs-7" style="font-size:0.78rem;">${msg}</div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-3 mb-auto" onclick="this.closest('.toast').remove()"></button>
        </div>
      `;

      container.appendChild(toastNode);
      setTimeout(() => {
        toastNode.classList.remove('show');
        setTimeout(() => toastNode.remove(), 350);
      }, 2500);
    };

    document.addEventListener('DOMContentLoaded', function() {
      // Inisialisasi Perfect Scrollbar pada kontainer tabel Roster
      const container = document.getElementById('rosterTableContainer');
      if (container) {
        const initScrollbar = () => {
          const PS = window.PerfectScrollbar || (typeof PerfectScrollbar !== 'undefined' ? PerfectScrollbar : null);
          if (PS) {
            new PS(container, {
              wheelPropagation: false,
              suppressScrollX: false
            });
            return true;
          }
          return false;
        };

        if (!initScrollbar()) {
          // Polling jika plugin belum termuat (karena deferral load order di Vite)
          const interval = setInterval(() => {
            if (initScrollbar()) {
              clearInterval(interval);
            }
          }, 50);
          setTimeout(() => clearInterval(interval), 3000);
        }
      }
    });

    function absensiRoster(config = {}) {
      return {
        counts: { hadir: 0, terlambat: 0, sakit: 0, izin: 0, alpha: 0, dispen: 0, bolos: 0 },
        sesiTerisi: config.sesiTerisi || false,
        confirmMsg: 'Simpan absensi untuk seluruh siswa di kelas ini?',
        isOverwrite: false,
        confirmModal: null,

        waModal: null,
        waSettingsModal: null,
        templateWA: '',
        editorTemplateWA: '',
        generatedWAText: '',
        defaultTemplate: @json($defaultWaTemplate),

        init() {
          this.recount();
          this.initWA();
        },

        initWA() {
          const saved = localStorage.getItem('custom_wa_presensi_template');
          this.templateWA = saved ? saved : this.defaultTemplate;
          this.editorTemplateWA = this.templateWA;
        },

        generateWAText() {
          const mapel = @json($jadwal->mata_pelajaran ?? '-');
          const kelas = @json($jadwal->kelas->nama ?? '-');
          const hariTanggal = @json(\Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d F Y'));
          @php
            $jamKeStr = $jadwal->jam_ke ?? (substr($jadwal->jam_mulai, 0, 5) . ' - ' . substr($jadwal->jam_selesai, 0, 5));
          @endphp
          const jamKe = @json($jamKeStr);
          const namaGuru = @json(auth()->user()->name ?? 'Guru');

          const lists = { alpha: [], sakit: [], izin: [], terlambat: [], bolos: [] };
          let totalSiswa = 0;
          let counts = { hadir: 0, alpha: 0, sakit: 0, izin: 0, terlambat: 0, bolos: 0 };

          document.querySelectorAll('tr.siswa-row-hover').forEach(tr => {
            totalSiswa++;
            const checkedRadio = tr.querySelector('[data-roster-status]:checked');
            const namaSpan = tr.querySelector('.fw-semibold.text-white span');
            const nama = namaSpan ? namaSpan.textContent.trim() : 'Siswa';
            
            if (checkedRadio) {
              const statusVal = checkedRadio.value;
              if (counts[statusVal] !== undefined) counts[statusVal]++;
              if (lists[statusVal]) {
                lists[statusVal].push(nama);
              }
            }
          });

          const formatList = (arr) => {
            if (!arr || arr.length === 0) return '-';
            return arr.map((item, idx) => `${idx + 1}. ${item}`).join('\n');
          };

          let text = this.templateWA;
          text = text.replace(/\{mapel\}/g, mapel);
          text = text.replace(/\{kelas\}/g, kelas);
          text = text.replace(/\{hari_tanggal\}/g, hariTanggal);
          text = text.replace(/\{jam_ke\}/g, jamKe);
          text = text.replace(/\{nama_guru\}/g, namaGuru);
          text = text.replace(/\{jumlah_murid\}/g, totalSiswa);

          text = text.replace(/\{total_hadir\}/g, counts.hadir);
          text = text.replace(/\{total_alpa\}/g, counts.alpha);
          text = text.replace(/\{total_izin\}/g, counts.izin);
          text = text.replace(/\{total_sakit\}/g, counts.sakit);
          text = text.replace(/\{total_terlambat\}/g, counts.terlambat);

          text = text.replace(/\{daftar_alpa\}/g, formatList(lists.alpha));
          text = text.replace(/\{daftar_izin\}/g, formatList(lists.izin));
          text = text.replace(/\{daftar_sakit\}/g, formatList(lists.sakit));
          text = text.replace(/\{daftar_terlambat\}/g, formatList(lists.terlambat));

          this.generatedWAText = text;
        },

        openWAModal() {
          this.initWA();
          this.generateWAText();
          const modalEl = document.getElementById('modalShareWA');
          if (modalEl) {
            this.waModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            this.waModal.show();
          }
        },

        openEditorModal() {
          if (this.waModal) this.waModal.hide();
          this.editorTemplateWA = this.templateWA;
          const modalEditorEl = document.getElementById('modalEditTemplateWA');
          if (modalEditorEl) {
            this.waSettingsModal = bootstrap.Modal.getInstance(modalEditorEl) || new bootstrap.Modal(modalEditorEl);
            this.waSettingsModal.show();
          }
        },

        insertTag(tag) {
          const textarea = document.getElementById('templateWaTextarea');
          if (!textarea) {
            this.editorTemplateWA += ' ' + tag;
            return;
          }
          const start = textarea.selectionStart;
          const end = textarea.selectionEnd;
          const text = this.editorTemplateWA;
          this.editorTemplateWA = text.substring(0, start) + tag + text.substring(end);
          this.$nextTick(() => {
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + tag.length;
          });
        },

        saveCustomTemplate() {
          this.templateWA = this.editorTemplateWA;
          localStorage.setItem('custom_wa_presensi_template', this.editorTemplateWA);
          this.generateWAText();
          if (this.waSettingsModal) this.waSettingsModal.hide();
          this.openWAModal();
          this.showToast('success', 'Template Disimpan', 'Redaksi WA berhasil diperbarui.');
        },

        resetDefaultTemplate() {
          this.editorTemplateWA = this.defaultTemplate;
          this.templateWA = this.defaultTemplate;
          localStorage.removeItem('custom_wa_presensi_template');
          this.generateWAText();
          if (this.waSettingsModal) this.waSettingsModal.hide();
          this.openWAModal();
          this.showToast('success', 'Reset Default', 'Redaksi WA dikembalikan ke format standar sekolah.');
        },

        copyWAText() {
          if (!navigator.clipboard) {
            const ta = document.createElement('textarea');
            ta.value = this.generatedWAText;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
          } else {
            navigator.clipboard.writeText(this.generatedWAText);
          }
          this.showToast('success', 'Berhasil Disalin', 'Teks rekap presensi berhasil disalin ke clipboard!');
        },

        openWAGroup() {
          const encoded = encodeURIComponent(this.generatedWAText);
          const nomorAdmin = @json($cleanWaAdmin ?? '');
          if (nomorAdmin && nomorAdmin.length > 5) {
            window.open(`https://wa.me/${nomorAdmin}?text=${encoded}`, '_blank');
          } else {
            window.open(`https://api.whatsapp.com/send?text=${encoded}`, '_blank');
          }
        },

        getJapriUrl(namaSiswa, statusVal, noHp) {
          const mapel = @json($jadwal->mata_pelajaran ?? '-');
          const statusText = statusVal.charAt(0).toUpperCase() + statusVal.slice(1);
          const msg = `Halo Bapak/Ibu, menginformasikan bahwa ananda ${namaSiswa} hari ini tercatat *${statusText}* pada mata pelajaran ${mapel}. Apabila ada kendala atau konfirmasi, mohon dapat menyampaikan ke Wali Kelas/Guru Mapel. Terima kasih.`;
          const cleanHp = noHp ? noHp.replace(/[^0-9]/g, '') : '';
          const encoded = encodeURIComponent(msg);
          if (cleanHp) {
            let hpFormatted = cleanHp;
            if (hpFormatted.startsWith('0')) hpFormatted = '62' + hpFormatted.slice(1);
            return `https://wa.me/${hpFormatted}?text=${encoded}`;
          }
          return `https://api.whatsapp.com/send?text=${encoded}`;
        },

        // Hitung ulang counter status dari radio button ter-check (live)
        recount() {
          const c = { hadir: 0, terlambat: 0, sakit: 0, izin: 0, alpha: 0, dispen: 0, bolos: 0 };
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
          const modalEl = document.getElementById('modalSimpanAbsensi');
          if (modalEl) {
            this.confirmModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            this.confirmModal.show();
          }
        },

        submitForm() {
          if (this.confirmModal) {
            this.confirmModal.hide();
          }
          const form = document.getElementById('absensiForm');
          if (form) {
            HTMLFormElement.prototype.submit.call(form);
          }
        },

        // Auto-Save Single Row via AJAX
        autoSaveSingle(siswaId, namaSiswa, statusVal, rowEl) {
          if (!{{ $canEdit ? 'true' : 'false' }}) return;

          const singleUrl = "{{ route('admin.absensi-per-jam.store-single', $jadwal->id) }}";
          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "{{ csrf_token() }}";
          const tanggal = "{{ $tanggal }}";

          const lamaInp = rowEl ? rowEl.querySelector('[data-roster-lama]') : null;
          const ketInp = rowEl ? rowEl.querySelector('[data-roster-ket]') : null;

          const lamaVal = (lamaInp && lamaInp.value) ? parseInt(lamaInp.value) : 1;

          const defaultKetMap = {
            sakit: 'Sakit',
            izin: 'Izin',
            alpha: 'Alpha',
            terlambat: `Terlambat ${lamaVal} Menit`,
            bolos: 'Bolos',
            hadir: ''
          };

          if (ketInp) {
            const isUserEdited = ketInp.dataset.userEdited === 'true';
            const curVal = ketInp.value.trim();
            const isDefaultText = curVal === '' ||
              ['Sakit', 'Izin', 'Alpha', 'Bolos', 'Terlambat'].includes(curVal) ||
              /^Terlambat(\s+\d+\s+Menit)?$/i.test(curVal);

            if (!isUserEdited || isDefaultText) {
              ketInp.value = defaultKetMap[statusVal] !== undefined ? defaultKetMap[statusVal] : '';
              ketInp.dataset.userEdited = 'false';
            }
          }

          const payload = {
            _token: csrfToken,
            tanggal: tanggal,
            siswa_id: siswaId,
            status: statusVal,
            lama_terlambat: statusVal === 'terlambat' ? (lamaInp && lamaInp.value ? parseInt(lamaInp.value) : 1) : null,
            keterangan: ketInp ? ketInp.value : ''
          };

          fetch(singleUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              const statusText = statusVal.charAt(0).toUpperCase() + statusVal.slice(1);
              if (rowEl) {
                rowEl.classList.remove('row-saved-flash');
                void rowEl.offsetWidth;
                rowEl.classList.add('row-saved-flash');
              }
              window.showAutoSaveToast('success', 'Tersimpan ke Database', `${namaSiswa}: Status diubah ke ${statusText}`);
            } else {
              window.showAutoSaveToast('error', 'Gagal Auto-Save DB', data.message || 'Gagal menyimpan absensi');
            }
          })
          .catch(err => {
            window.showAutoSaveToast('error', 'Koneksi Bermasalah', 'Gagal terhubung ke server');
          });
        },

        showToast(type, title, msg) {
          window.showAutoSaveToast(type, title, msg);
        }
      };
    }
  </script>
@endsection
