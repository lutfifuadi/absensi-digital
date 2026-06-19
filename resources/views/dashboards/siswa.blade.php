@extends('layouts/layoutMaster')

@section('title', 'Dashboard Siswa')

@section('page-style')
  <style>
    /* Pulse Effect untuk Status Absensi */
    .absen-pulse {
      box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.7);
      animation: absen-pulse 2s infinite;
    }

    @keyframes absen-pulse {
      0% { box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.4); }
      70% { box-shadow: 0 0 0 15px rgba(40, 199, 111, 0); }
      100% { box-shadow: 0 0 0 0 rgba(40, 199, 111, 0); }
    }

    /* 
     * ── RESPONSIVE OVERRIDES UNTUK DASHBOARD SISWA ──
     */

    /* Default (PC & Laptop): 3 kolom stats */
    .das-stats-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr) !important;
      gap: 1.25rem !important;
      bottom: -45px !important;
    }

    .das-hero--with-stats {
      margin-bottom: 5.5rem !important;
    }

    /* ── ACTION CARDS (Tombol Kartu yang Menonjol) ── */
    .siswa-action-row {
      margin-top: -2.25rem;
      margin-bottom: 1.5rem;
    }

    .siswa-action-card {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1.25rem 1.5rem;
      border-radius: 5px;
      text-decoration: none !important;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      min-height: 80px;
    }

    .siswa-action-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      opacity: 0;
      transition: opacity 0.3s ease;
      border-radius: inherit;
    }

    .siswa-action-card:hover::before {
      opacity: 1;
    }

    .siswa-action-card:hover {
      transform: translateY(-3px);
    }

    .siswa-action-card:active {
      transform: translateY(0px);
    }

    .siswa-action-card__icon {
      width: 52px;
      height: 52px;
      min-width: 52px;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      position: relative;
      z-index: 1;
      flex-shrink: 0;
    }

    .siswa-action-card__body {
      flex: 1;
      min-width: 0;
      position: relative;
      z-index: 1;
    }

    .siswa-action-card__title {
      font-weight: 700;
      font-size: 1rem;
      line-height: 1.3;
      margin-bottom: 2px;
    }

    .siswa-action-card__subtitle {
      font-size: 0.75rem;
      opacity: 0.7;
      line-height: 1.3;
    }

    .siswa-action-card__arrow {
      font-size: 1.3rem;
      opacity: 0.5;
      transition: opacity 0.2s, transform 0.2s;
      position: relative;
      z-index: 1;
      flex-shrink: 0;
    }

    .siswa-action-card:hover .siswa-action-card__arrow {
      opacity: 1;
      transform: translateX(4px);
    }

    /* Variant: Warning (Kartu Pelepasan) */
    .siswa-action-card--warning {
      background: linear-gradient(135deg, rgba(255, 159, 67, 0.18) 0%, rgba(255, 159, 67, 0.05) 100%);
      border: 1px solid rgba(255, 159, 67, 0.35);
    }

    .siswa-action-card--warning::before {
      background: rgba(255, 159, 67, 0.05);
    }

    .siswa-action-card--warning:hover {
      box-shadow: 0 0 15px rgba(255, 159, 67, 0.25) !important;
      border-color: rgba(255, 159, 67, 0.6) !important;
    }

    .siswa-action-card--warning .siswa-action-card__icon {
      background: linear-gradient(135deg, rgba(255, 159, 67, 0.3), rgba(255, 159, 67, 0.1));
      color: #ff9f43;
    }

    .siswa-action-card--warning .siswa-action-card__title {
      color: #ff9f43 !important;
      text-shadow: 0 0 8px rgba(255, 159, 67, 0.2);
    }

    /* Variant: Primary (Kartu Pelajar) */
    .siswa-action-card--primary {
      background: linear-gradient(135deg, rgba(115, 103, 240, 0.18) 0%, rgba(115, 103, 240, 0.05) 100%);
      border: 1px solid rgba(115, 103, 240, 0.35);
    }

    .siswa-action-card--primary::before {
      background: rgba(115, 103, 240, 0.05);
    }

    .siswa-action-card--primary:hover {
      box-shadow: 0 0 15px rgba(115, 103, 240, 0.25) !important;
      border-color: rgba(115, 103, 240, 0.6) !important;
    }

    .siswa-action-card--primary .siswa-action-card__icon {
      background: linear-gradient(135deg, rgba(115, 103, 240, 0.3), rgba(115, 103, 240, 0.1));
      color: #7367f0;
    }

    .siswa-action-card--primary .siswa-action-card__title {
      color: #7367f0 !important;
      text-shadow: 0 0 8px rgba(115, 103, 240, 0.2);
    }

    /* Variant: Success */
    .siswa-action-card--success {
      background: linear-gradient(135deg, rgba(40, 199, 111, 0.18) 0%, rgba(40, 199, 111, 0.05) 100%);
      border: 1px solid rgba(40, 199, 111, 0.35);
    }

    .siswa-action-card--success::before {
      background: rgba(40, 199, 111, 0.05);
    }

    .siswa-action-card--success:hover {
      box-shadow: 0 0 15px rgba(40, 199, 111, 0.25) !important;
      border-color: rgba(40, 199, 111, 0.6) !important;
    }

    .siswa-action-card--success .siswa-action-card__icon {
      background: linear-gradient(135deg, rgba(40, 199, 111, 0.3), rgba(40, 199, 111, 0.1));
      color: #28c76f;
    }

    .siswa-action-card--success .siswa-action-card__title {
      color: #28c76f !important;
      text-shadow: 0 0 8px rgba(40, 199, 111, 0.2);
    }

    /* Tablet */
    @media (max-width: 991px) {
      .das-hero--with-stats {
        margin-bottom: 6.5rem !important;
      }
      .das-stats-row {
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 0.75rem !important;
        bottom: -75px !important;
        left: 1rem !important;
        right: 1rem !important;
      }
      .das-stat-card {
        padding: 0.8rem 0.6rem !important;
      }
      .das-stat-card__val {
        font-size: 1.05rem !important;
      }
      .das-stat-card__label {
        font-size: 0.7rem !important;
      }
      .siswa-action-card {
        padding: 1rem 1.25rem;
        min-height: 72px;
      }
      .siswa-action-card__icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        font-size: 1.3rem;
      }
    }

    /* Mobile */
    @media (max-width: 767px) {
      .das-hero--with-stats {
        margin-bottom: 12.8rem !important;
      }
      
      .das-hero__inner {
        padding: 1.5rem 1rem 4.5rem 1rem !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 1.5rem !important;
      }

      .das-hero__clock {
        display: none !important;
      }

      .das-stats-row {
        grid-template-columns: 1fr !important;
        gap: 0.75rem !important;
        bottom: -155px !important;
        left: 0.75rem !important;
        right: 0.75rem !important;
      }
      
      .das-stat-card {
        padding: 0.75rem 1rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 1rem !important;
        height: auto !important;
      }

      .das-stat-card__icon {
        margin-bottom: 0 !important;
        width: 40px !important;
        height: 40px !important;
        font-size: 1.25rem !important;
      }

      .das-stat-card__body {
        text-align: left !important;
      }

      /* Action cards mobile: full-width & lebih menonjol */
      .siswa-action-card {
        padding: 1.1rem 1.25rem;
        min-height: 76px;
      }

      .siswa-action-card__icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        font-size: 1.5rem;
      }

      .siswa-action-card__title {
        font-size: 1rem;
      }

      .siswa-action-card__subtitle {
        font-size: 0.75rem;
      }
    }
  </style>
@endsection

@section('content')
  @php
    $siswaRecord = \App\Models\Siswa::with('kelas')->where('user_id', $user->id)->first();
    $kelasNama = $siswaRecord && $siswaRecord->kelas ? $siswaRecord->kelas->nama : 'Belum Ada Kelas';
    
    $totalIzinSaya = $siswaRecord
        ? \App\Models\IzinSakit::where('tipe', 'siswa')->where('reference_id', $siswaRecord->id)->count()
        : 0;
        
    $izinDisetujui = $siswaRecord
        ? \App\Models\IzinSakit::where('tipe', 'siswa')
            ->where('reference_id', $siswaRecord->id)
            ->where('status', 'disetujui')
            ->count()
        : 0;
        
    $absensiSaya = $siswaRecord
        ? \App\Models\AbsensiSiswa::where('siswa_id', $siswaRecord->id)->whereDate('tanggal', today())->first()
        : null;
    
    $pelepasanKegiatanId = \App\Models\Pengaturan::where('key', 'pelepasan_kegiatan_id')->value('value');
    $absenPelepasan = null;
    if ($pelepasanKegiatanId && $siswaRecord) {
        $absenPelepasan = \App\Models\AbsensiKegiatan::where('kegiatan_id', $pelepasanKegiatanId)
            ->where('siswa_id', $siswaRecord->id)
            ->first();
    }
    
    $logoSekolah = \App\Models\Pengaturan::where('key', 'logo_sekolah')->value('value');
    $namaSekolah = \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value') ?: 'Sistem Absensi';
    
    $absenMandiriEnabled = \App\Models\Pengaturan::where('key', 'izinkan_lokasi_absensi_mandiri')->value('value') === 'Ya';
    $aktifkanBunyi = \App\Models\Pengaturan::where('key', 'aktifkan_bunyi_notif_absensi')->value('value') === 'Ya';
    $freqHadir = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_hadir')->value('value') ?: 880);
    $freqTerlambat = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_terlambat')->value('value') ?: 440);
    $freqStreak = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_streak')->value('value') ?: 523);
    $freqEarly = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_early')->value('value') ?: 698);
    $freqNormal = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_normal')->value('value') ?: 523);
    $freqLate = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_late')->value('value') ?: 349);
    $freqCheckout = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_checkout')->value('value') ?: 392);
  @endphp

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER — Identitas Siswa + Live Clock
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero das-hero--with-stats mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      {{-- Identitas Siswa --}}
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          @if ($logoSekolah)
            <img src="{{ asset('uploads/logo/' . $logoSekolah) }}" alt="Logo" class="das-hero__logo">
          @else
            <div class="das-hero__logo-placeholder">
              <i class="ti tabler-school"></i>
            </div>
          @endif
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Portal Siswa Aktif
          </div>
          <h4 class="das-hero__school text-gradient-gold">{{ $namaSekolah }}</h4>
          <p class="das-hero__welcome">Selamat datang kembali, <strong>{{ $user->name }}</strong> 👋</p>
        </div>
      </div>

      {{-- Clock --}}
      <div class="das-hero__clock glass-card">
        <div class="das-hero__date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        <div class="das-hero__time">
          <span id="live-clock">00:00:00</span>
          <div class="das-hero__status-indicator">
            <span class="das-hero__live-badge">LIVE</span>
          </div>
        </div>
        <div class="das-hero__tz">WAKTU INDONESIA BARAT (WIB)</div>
      </div>
    </div>

    {{-- STATS ROW (Mengambang di bawah hero) --}}
    <div class="das-stats-row">
      <div class="das-stat-card das-stat-card--warning">
        <div class="das-stat-card__icon">
          <i class="ti tabler-door"></i>
        </div>
        <div class="das-stat-card__body">
          <div class="das-stat-card__val">{{ $kelasNama }}</div>
          <div class="das-stat-card__label">Kelas Aktif</div>
        </div>
      </div>

      <a href="{{ route('admin.izin-sakit.index') }}" class="das-stat-card das-stat-card--info text-decoration-none">
        <div class="das-stat-card__icon">
          <i class="ti tabler-circle-check"></i>
        </div>
        <div class="das-stat-card__body">
          <div class="das-stat-card__val">{{ $izinDisetujui }} / {{ $totalIzinSaya }}</div>
          <div class="das-stat-card__label">Izin Disetujui</div>
        </div>
        <div class="das-stat-card__side-info">
          <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
        </div>
      </a>

      <div class="das-stat-card das-stat-card--success">
        <div class="das-stat-card__icon">
          <i class="ti tabler-flame"></i>
        </div>
        <div class="das-stat-card__body">
          <div class="das-stat-card__val">{{ $attendance_streak ?? 0 }} Hari</div>
          <div class="das-stat-card__label">Kehadiran Beruntun</div>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1.5: KONFIRMASI KEHADIRAN PELEPASAN (Khusus Kelas XII)
  ═══════════════════════════════════════════════════════ --}}
  @if($siswaRecord && $siswaRecord->kelas && (trim($siswaRecord->kelas->tingkat) === 'XII' || trim($siswaRecord->kelas->tingkat) === '12'))
    <div class="row mb-4">
      <div class="col-12">
        @if($absenPelepasan)
          <div class="das-panel" style="border-left: 4px solid var(--das-success) !important; background: rgba(40, 199, 111, 0.06);">
            <div class="das-panel__body p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
              <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-md bg-label-success rounded p-1"><i class="ti tabler-circle-check fs-4"></i></div>
                <div>
                  <h6 class="mb-0 text-success fw-bold">Presensi Pelepasan Terkonfirmasi!</h6>
                  <p class="text-white-50 mb-0 small">Anda telah terkonfirmasi <strong>HADIR</strong> pada acara pelepasan kelas XII.</p>
                </div>
              </div>
              <div class="text-md-end text-start">
                <span class="badge bg-label-success p-2 px-3 border border-success border-opacity-20 font-monospace">
                  Jam Absen: {{ \Carbon\Carbon::parse($absenPelepasan->jam_absen)->format('H:i:s') }}
                </span>
              </div>
            </div>
          </div>
        @else
          <div class="das-panel" style="border-left: 4px solid var(--das-danger) !important; background: rgba(234, 84, 85, 0.06);">
            <div class="das-panel__body p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
              <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-md bg-label-danger rounded p-1"><i class="ti tabler-circle-x fs-4"></i></div>
                <div>
                  <h6 class="mb-0 text-danger fw-bold">Presensi Pelepasan Belum Tercatat</h6>
                  <p class="text-white-50 mb-0 small">Silakan tunjukkan QR Code pada Kartu Pelepasan Anda kepada panitia saat acara berlangsung.</p>
                </div>
              </div>
              <div>
                <span class="badge bg-label-danger p-2 px-3 border border-danger border-opacity-20">
                  BELUM HADIR
                </span>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: ACTION CARDS — Tombol Download Kartu yang Menonjol
  ═══════════════════════════════════════════════════════ --}}
  @php
    $isKelasXII = $siswaRecord && $siswaRecord->kelas && (trim($siswaRecord->kelas->tingkat) === 'XII' || trim($siswaRecord->kelas->tingkat) === '12');
  @endphp
  <div class="siswa-action-row" @if($isKelasXII) style="margin-top: 0.5rem !important;" @endif>
    <div class="row g-3">
      @if($siswaRecord && $siswaRecord->kelas && (trim($siswaRecord->kelas->tingkat) === 'XII' || trim($siswaRecord->kelas->tingkat) === '12'))
      <div class="col-md-6 col-12">
        <a href="{{ route('siswa.download-kartu-pelepasan') }}" class="siswa-action-card siswa-action-card--warning">
          <div class="siswa-action-card__icon">
            <i class="ti tabler-id"></i>
          </div>
          <div class="siswa-action-card__body">
            <div class="siswa-action-card__title">Unduh Kartu Pelepasan</div>
            <div class="siswa-action-card__subtitle">Khusus siswa kelas XII — unduh kartu tanda kelulusan</div>
          </div>
          <div class="siswa-action-card__arrow">
            <i class="ti tabler-chevron-right"></i>
          </div>
        </a>
      </div>
      @endif
      <div class="@if($siswaRecord && $siswaRecord->kelas && (trim($siswaRecord->kelas->tingkat) === 'XII' || trim($siswaRecord->kelas->tingkat) === '12')) col-md-6 @else col-12 @endif col-12">
        <a href="{{ route('siswa.download-kartu') }}" target="_blank" class="siswa-action-card siswa-action-card--primary">
          <div class="siswa-action-card__icon">
            <i class="ti tabler-id-badge"></i>
          </div>
          <div class="siswa-action-card__body">
            <div class="siswa-action-card__title">Unduh Kartu Pelajar</div>
            <div class="siswa-action-card__subtitle">Kartu identitas siswa — cetak atau simpan sebagai bukti</div>
          </div>
          <div class="siswa-action-card__arrow">
            <i class="ti tabler-chevron-right"></i>
          </div>
        </a>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: MAIN CONTENT — Absensi & Panduan
  ═══════════════════════════════════════════════════════ --}}
  <div class="row gy-4 mb-4">
    {{-- ABSENSI MANDIRI PANEL --}}
    <div class="col-lg-8 col-md-12">
      <div class="das-panel h-100">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot --danger"></span>
            Absensi Mandiri (Lokasi Terdeteksi)
          </div>
        </div>
        <div class="das-panel__body d-flex flex-column justify-content-center align-items-center text-center py-4" style="min-height: 250px;">
          @if($absensiSaya && $absensiSaya->jam_masuk && $absensiSaya->jam_pulang)
            {{-- KASUS 1: SUDAH MASUK & PULANG --}}
            <div class="text-center py-3 w-100">
              <div class="avatar avatar-xl bg-label-success mx-auto mb-3 shadow-lg" style="width:72px; height:72px;">
                <span class="avatar-initial rounded-circle"><i class="ti tabler-circle-check fs-1"></i></span>
              </div>
              <h4 class="mb-1 text-white fw-bold">Selesai Untuk Hari Ini!</h4>
              <p class="text-success mb-4 fw-bold fs-6">{{ $greeting_message ?? 'Terima kasih, Anda sudah melakukan absensi hari ini.' }}</p>
              
              <div class="d-flex gap-3 justify-content-center flex-wrap">
                <div class="p-3 rounded border d-flex flex-column align-items-center justify-content-center" 
                     style="min-width: 130px; background: rgba(40, 199, 111, 0.08); border-color: rgba(40, 199, 111, 0.2) !important; backdrop-filter: blur(10px);">
                  <span class="text-success small fw-bold text-uppercase mb-1" style="font-size:0.6rem; letter-spacing:1px;">Jam Masuk</span>
                  <div class="text-success fw-bold font-monospace fs-4" style="text-shadow: 0 0 10px rgba(40, 199, 111, 0.3);">{{ $absensiSaya->jam_masuk }}</div>
                </div>
                <div class="p-3 rounded border d-flex flex-column align-items-center justify-content-center" 
                     style="min-width: 130px; background: rgba(0, 207, 232, 0.08); border-color: rgba(0, 207, 232, 0.2) !important; backdrop-filter: blur(10px);">
                  <span class="text-info small fw-bold text-uppercase mb-1" style="font-size:0.6rem; letter-spacing:1px;">Jam Pulang</span>
                  <div class="text-info fw-bold font-monospace fs-4" style="text-shadow: 0 0 10px rgba(0, 207, 232, 0.3);">{{ $absensiSaya->jam_pulang }}</div>
                </div>
              </div>
            </div>
          @elseif($absenMandiriEnabled)
            {{-- KASUS 2: ABSEN MANDIRI AKTIF --}}
            <div class="w-100 py-2 px-3" style="max-width: 500px;">
              <div class="row g-3">
                <div class="col-6">
                  @if($absensiSaya && $absensiSaya->jam_masuk)
                    <div class="p-3 rounded h-100 border d-flex flex-column align-items-center justify-content-center" 
                         style="background: rgba(40, 199, 111, 0.08); border-color: rgba(40, 199, 111, 0.2) !important; backdrop-filter: blur(10px); min-height: 110px;">
                      <i class="ti tabler-circle-check text-success fs-2 mb-1"></i>
                      <div class="text-success fw-bold font-monospace fs-4" style="text-shadow: 0 0 10px rgba(40, 199, 111, 0.3);">{{ $absensiSaya->jam_masuk }}</div>
                      <div class="text-success small fw-bold mt-1 text-uppercase" style="font-size:0.6rem; letter-spacing:0.5px;">Tercatat Masuk</div>
                    </div>
                  @else
                    <button type="button" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-lg h-100 d-flex flex-column align-items-center justify-content-center gap-1" id="btnAbsenMasuk">
                      <i class="ti tabler-login fs-2"></i>
                      <span>Absen Masuk</span>
                    </button>
                  @endif
                </div>
                <div class="col-6">
                  @if($absensiSaya && $absensiSaya->jam_pulang)
                    <div class="p-3 rounded h-100 border d-flex flex-column align-items-center justify-content-center" 
                         style="background: rgba(0, 207, 232, 0.08); border-color: rgba(0, 207, 232, 0.2) !important; backdrop-filter: blur(10px); min-height: 110px;">
                      <i class="ti tabler-circle-check text-info fs-2 mb-1"></i>
                      <div class="text-info fw-bold font-monospace fs-4" style="text-shadow: 0 0 10px rgba(0, 207, 232, 0.3);">{{ $absensiSaya->jam_pulang }}</div>
                      <div class="text-info small fw-bold mt-1 text-uppercase" style="font-size:0.6rem; letter-spacing:0.5px;">Tercatat Pulang</div>
                    </div>
                  @else
                    <button type="button" class="btn btn-warning btn-lg w-100 py-3 fw-bold shadow-lg h-100 d-flex flex-column align-items-center justify-content-center gap-1 {{ !$absensiSaya ? 'opacity-50' : '' }}" id="btnAbsenPulang" {{ !$absensiSaya ? 'disabled' : '' }}>
                      <i class="ti tabler-logout fs-2"></i>
                      <span>Absen Pulang</span>
                    </button>
                  @endif
                </div>
              </div>
              
              <div id="absenMessage" class="mt-4 p-2 rounded bg-black bg-opacity-10 small fw-bold d-none"></div>
              
              @if(!$absensiSaya)
                <div class="mt-4 p-3 rounded bg-label-info border border-info border-opacity-10">
                  <p class="mb-0 text-white small"><i class="ti tabler-info-circle me-1"></i> Silakan tekan tombol <strong>Absen Masuk</strong> untuk memulai hari.</p>
                </div>
              @endif
            </div>
          @else
            {{-- KASUS 3: ABSEN MANDIRI NONAKTIF --}}
            <div class="py-4 w-100">
              <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3" style="width:72px; height:72px;">
                <span class="avatar-initial rounded-circle"><i class="ti tabler-lock fs-1"></i></span>
              </div>
              <h5 class="text-white fw-bold">Absensi Mandiri Nonaktif</h5>
              <p class="text-white-50 opacity-50 small mx-auto" style="max-width:320px;">Silakan hubungi Guru Piket atau Wali Kelas untuk melakukan pencatatan kehadiran.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- PANDUAN GEOFENCING PANEL --}}
    <div class="col-lg-4 col-md-12">
      <div class="das-panel h-100">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot --info"></span>
            Panduan Geofencing
          </div>
        </div>
        <div class="das-panel__body d-flex flex-column justify-content-between p-4">
          <div class="d-flex flex-column gap-3">
            <div class="d-flex align-items-start gap-3">
              <div class="text-info fs-4 position-relative" style="top:2px;"><i class="ti tabler-gps"></i></div>
              <div>
                <div class="text-white small fw-bold mb-1">Aktifkan GPS Perangkat</div>
                <p class="text-white-50 small mb-0 opacity-75">Pastikan fitur Lokasi/GPS menyala sebelum menekan tombol absen.</p>
              </div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="text-info fs-4 position-relative" style="top:2px;"><i class="ti tabler-browser-check"></i></div>
              <div>
                <div class="text-white small fw-bold mb-1">Izinkan Akses Browser</div>
                <p class="text-white-50 small mb-0 opacity-75">Tekan "Allow/Izinkan" saat browser meminta informasi lokasi Anda.</p>
              </div>
            </div>
          </div>
          
          <div class="mt-4 p-3 bg-black bg-opacity-20 rounded border border-white border-opacity-10">
            <div class="d-flex align-items-center gap-2 text-warning mb-1">
              <i class="ti tabler-alert-triangle small"></i>
              <span class="small fw-bold">Penting:</span>
            </div>
            <p class="text-white-50 mb-0" style="font-size: 0.75rem; line-height: 1.4;">Absensi mandiri hanya dapat dilakukan jika posisi GPS Anda berada dalam radius area madrasah yang ditentukan.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 4: BOTTOM PANEL — Menu Cepat
  ═══════════════════════════════════════════════════════ --}}
  <div class="row gy-4">
    <div class="col-12">
      <div class="das-panel">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot --primary"></span>
            Menu Cepat
          </div>
        </div>
        <div class="das-panel__body">
          <a href="{{ route('admin.izin-sakit.index') }}" class="siswa-action-card siswa-action-card--success mb-0">
            <div class="siswa-action-card__icon">
              <i class="ti tabler-stethoscope"></i>
            </div>
            <div class="siswa-action-card__body">
              <div class="siswa-action-card__title">Pengajuan Izin & Sakit</div>
              <div class="siswa-action-card__subtitle">Ajukan surat keterangan sakit atau izin berhalangan hadir</div>
            </div>
            <div class="siswa-action-card__arrow">
              <i class="ti tabler-chevron-right"></i>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnMasuk = document.getElementById('btnAbsenMasuk');
    const btnPulang = document.getElementById('btnAbsenPulang');
    const msgBox = document.getElementById('absenMessage');

    // Live Clock
    const clockElement = document.getElementById('live-clock');
    if (clockElement) {
        setInterval(() => {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            clockElement.textContent = `${hours}:${minutes}:${seconds}`;
        }, 1000);
    }

    const bunyiAktif = {{ $aktifkanBunyi ? 'true' : 'false' }};
    const freqs = {
        hadir: {{ $freqHadir }},
        terlambat: {{ $freqTerlambat }},
        streak: {{ $freqStreak }},
        early: {{ $freqEarly }},
        normal: {{ $freqNormal }},
        late: {{ $freqLate }},
        checkout: {{ $freqCheckout }}
    };

    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    
    const playSound = (type) => {
        if (!bunyiAktif) return;
        
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        
        const now = audioCtx.currentTime;
        
        let freq = freqs[type] || freqs.normal;
        if (type === 'streak_5' || type === 'streak_10' || type === 'streak_30') {
            freq = freqs.streak;
        }
        
        const soundConfigs = {
            'hadir': { freq: freq, type: 'sine', duration: 0.15, pattern: 'single' },
            'terlambat': { freq: freq, type: 'sine', duration: 0.2, pattern: 'descend' },
            'streak_5': { freq: freq, type: 'triangle', duration: 0.3, pattern: 'ascend' },
            'streak_10': { freq: freq, type: 'triangle', duration: 0.4, pattern: 'fanfare' },
            'streak_30': { freq: freq, type: 'triangle', duration: 0.5, pattern: 'fanfare' },
            'early': { freq: freqs.early, type: 'sine', duration: 0.2, pattern: 'energetic' },
            'normal': { freq: freqs.normal, type: 'sine', duration: 0.1, pattern: 'single' },
            'late': { freq: freqs.late, type: 'sine', duration: 0.25, pattern: 'descend' },
            'checkout': { freq: freqs.checkout, type: 'sine', duration: 0.3, pattern: 'warm' }
        };
        
        const config = soundConfigs[type] || soundConfigs['normal'];
        
        oscillator.type = config.type;
        
        if (config.pattern === 'descend') {
            oscillator.frequency.setValueAtTime(config.freq, now);
            oscillator.frequency.linearRampToValueAtTime(config.freq * 0.5, now + config.duration);
        } else if (config.pattern === 'ascend') {
            oscillator.frequency.setValueAtTime(config.freq * 0.75, now);
            oscillator.frequency.linearRampToValueAtTime(config.freq, now + config.duration);
        } else if (config.pattern === 'fanfare') {
            oscillator.frequency.setValueAtTime(config.freq * 0.5, now);
            oscillator.frequency.setValueAtTime(config.freq, now + 0.1);
            oscillator.frequency.setValueAtTime(config.freq * 1.25, now + 0.2);
            oscillator.frequency.setValueAtTime(config.freq * 1.5, now + config.duration - 0.1);
        } else if (config.pattern === 'pop') {
            oscillator.frequency.setValueAtTime(config.freq * 1.5, now);
            oscillator.frequency.exponentialRampToValueAtTime(config.freq, now + config.duration);
        } else if (config.pattern === 'energetic') {
            oscillator.frequency.setValueAtTime(config.freq * 0.8, now);
            oscillator.frequency.linearRampToValueAtTime(config.freq * 1.2, now + config.duration);
        } else if (config.pattern === 'warm') {
            oscillator.frequency.setValueAtTime(config.freq * 0.75, now);
            oscillator.frequency.setValueAtTime(config.freq, now + config.duration * 0.5);
            oscillator.frequency.linearRampToValueAtTime(config.freq * 0.5, now + config.duration);
        } else {
            oscillator.frequency.setValueAtTime(config.freq, now);
        }
        
        gainNode.gain.setValueAtTime(0.3, now);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + config.duration);
        
        oscillator.start(now);
        oscillator.stop(now + config.duration);
    };

    const handleAbsen = (btn) => {
        if (!btn) return;
        
        btn.addEventListener('click', function () {
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mb-1"></span><span>Scanning...</span>';
            
            msgBox.innerHTML = 'Mendapatkan lokasi...';
            msgBox.className = 'mt-4 p-2 rounded bg-black bg-opacity-10 small fw-bold text-info d-block';
            msgBox.classList.remove('d-none');

            if (!navigator.geolocation) {
                showMsg('Browser tidak mendukung Geolocation.', 'text-danger');
                resetBtn(btn, originalHtml);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    showMsg('Lokasi ditemukan. Mengirim data...', 'text-info');

                    fetch('{{ route('siswa.absensi-mandiri.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ 
                            lat: lat, 
                            lng: lng, 
                            accuracy: position.coords.accuracy
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            showMsg('<i class="ti tabler-check"></i> ' + data.message, 'text-success');
                            
                            const status = data.status || 'hadir';
                            const milestone = data.milestone_type;
                            const timeCtx = data.time_context;
                            
                            playSound(status);
                            
                            if (milestone) {
                                setTimeout(() => playSound(milestone), 200);
                            } else if (timeCtx) {
                                setTimeout(() => playSound(timeCtx), 200);
                            }
                            
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            showMsg('<i class="ti tabler-alert-circle"></i> ' + data.message, 'text-danger');
                            resetBtn(btn, originalHtml);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showMsg('Terjadi kesalahan jaringan.', 'text-danger');
                        resetBtn(btn, originalHtml);
                    });
                },
                function(error) {
                    let errStr = 'Gagal mendapatkan lokasi.';
                    if(error.code === error.PERMISSION_DENIED) errStr = 'Akses lokasi ditolak.';
                    showMsg(errStr, 'text-danger');
                    resetBtn(btn, originalHtml);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });
    };

    handleAbsen(btnMasuk);
    handleAbsen(btnPulang);

    function showMsg(text, className) {
        msgBox.innerHTML = text;
        msgBox.className = 'mt-4 p-2 rounded bg-black bg-opacity-10 small fw-bold ' + className;
        msgBox.classList.remove('d-none');
    }

    function resetBtn(btn, html) {
        btn.disabled = false;
        btn.innerHTML = html;
    }
});
</script>
@endpush
