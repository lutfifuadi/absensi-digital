@extends('layouts/layoutMaster')

@section('title', 'Dashboard Siswa')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <link rel="stylesheet" href="{{ asset('css/dashboards/siswa.css') }}?v=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <style>
    .barcode-svg-container svg {
      width: 100% !important;
      height: 100% !important;
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
    
    $pelepasanKegiatanId = $pengaturanSiswa['pelepasan_kegiatan_id'] ?? null;
    $absenPelepasan = null;
    if ($pelepasanKegiatanId && $siswaRecord) {
        $absenPelepasan = \App\Models\AbsensiKegiatan::where('kegiatan_id', $pelepasanKegiatanId)
            ->where('siswa_id', $siswaRecord->id)
            ->first();
    }
    
    $logoSekolah = $pengaturanSiswa['logo_sekolah'] ?? null;
    $namaSekolah = $pengaturanSiswa['nama_sekolah'] ?? 'Sistem Absensi';
    
    $absenMandiriEnabled = (($pengaturanSiswa['fitur_absensi_mandiri'] ?? '1') == '1' || ($pengaturanSiswa['fitur_absensi_mandiri'] ?? '') === 'Ya');
    $aktifkanBunyi = ($pengaturanSiswa['aktifkan_bunyi_notif_absensi'] ?? '') === 'Ya';
    $freqHadir = (int)($pengaturanSiswa['freq_bunyi_hadir'] ?? 880);
    $freqTerlambat = (int)($pengaturanSiswa['freq_bunyi_terlambat'] ?? 440);
    $freqStreak = (int)($pengaturanSiswa['freq_bunyi_streak'] ?? 523);
    $freqEarly = (int)($pengaturanSiswa['freq_bunyi_early'] ?? 698);
    $freqNormal = (int)($pengaturanSiswa['freq_bunyi_normal'] ?? 523);
    $freqLate = (int)($pengaturanSiswa['freq_bunyi_late'] ?? 349);
    $freqCheckout = (int)($pengaturanSiswa['freq_bunyi_checkout'] ?? 392);
    
    $chartDaysCategories = !empty($chartDays) ? $chartDays : ['Sn','Sl','Rb','Km','Jm','Sb','Mg'];

    $fotoSiswaUrl = asset('assets/img/avatars/1.png');
    if ($siswaRecord && $siswaRecord->foto) {
        if (strlen($siswaRecord->foto) > 30 && !str_contains($siswaRecord->foto, '/') && !str_contains($siswaRecord->foto, '\\')) {
            try {
                $gdrive = app(\App\Services\GoogleDriveService::class);
                $fotoSiswaUrl = $gdrive->getPhotoBase64($siswaRecord->foto) ?: asset('assets/img/avatars/1.png');
            } catch (\Exception $e) {
                $fotoSiswaUrl = asset('assets/img/avatars/1.png');
            }
        } else {
            $fotoSiswaUrl = asset('storage/' . $siswaRecord->foto);
        }
    }

    $tanggalLahirFormatted = ($siswaRecord && $siswaRecord->tanggal_lahir)
        ? \Carbon\Carbon::parse($siswaRecord->tanggal_lahir)->locale('id')->translatedFormat('d F Y')
        : 'Belum diisi';
    $tanggalLahirRaw = ($siswaRecord && $siswaRecord->tanggal_lahir)
        ? \Carbon\Carbon::parse($siswaRecord->tanggal_lahir)->format('Y-m-d')
        : '';
  @endphp

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER — Identitas Siswa + Live Clock
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-6">
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__scanline" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
      {{-- Identitas Siswa --}}
      <div class="das-hero__identity d-flex align-items-center gap-3">
        <div class="das-hero__avatar-wrapper position-relative flex-shrink-0" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalUploadFotoSiswa" title="Klik untuk mengunggah / mengubah pas foto resmi">
          <img src="{{ $fotoSiswaUrl }}" alt="Pas Foto Siswa" id="das-student-avatar-img" class="rounded-circle border border-2 border-warning shadow-sm" style="width: 70px; height: 70px; object-fit: cover;">
          <span class="badge bg-primary rounded-circle position-absolute bottom-0 end-0 p-1 shadow" style="transform: translate(15%, 15%); border: 2px solid white;" title="Ganti Foto">
            <i class="ti tabler-camera fs-6"></i>
          </span>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="das-hero__pulse-dot" aria-hidden="true"></span>
            Portal Siswa Aktif
          </div>
          <h3 class="das-hero__school text-gradient-gold mb-1" style="font-size: 1.35rem; line-height: 1.3;">{{ $namaSekolah }}</h3>
          <p class="das-hero__welcome mb-1">Selamat datang kembali, <strong>{{ $user->name }}</strong> 👋</p>
          <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
            <div class="px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1 shadow-xs" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); backdrop-filter: blur(6px);">
              <i class="ti tabler-calendar-heart text-warning" style="font-size: 0.85rem;"></i>
              <span class="text-white-50" style="font-size: 0.75rem;">Tgl Lahir:</span>
              <span id="display-tanggal-lahir" class="fw-bold text-warning" style="font-size: 0.75rem;">{{ $tanggalLahirFormatted }}</span>
            </div>
            <button type="button" class="btn btn-xs btn-warning fw-bold d-inline-flex align-items-center gap-1 px-3 py-1 shadow-sm" style="border-radius: 20px; font-size: 0.75rem; transition: all 0.2s ease;" data-bs-toggle="modal" data-bs-target="#modalUpdateTanggalLahir" title="Perbarui Data & Biodata Mandiri">
              <i class="ti tabler-edit" style="font-size: 0.85rem;"></i> Edit Biodata
            </button>
          </div>
        </div>
      </div>

      {{-- Clock Widget --}}
      <div class="das-hero__clock" role="status" aria-live="off">
        <div class="das-hero__date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        <div class="das-hero__time">
          <span id="live-clock">00:00:00</span>
          <span class="das-hero__live-badge"><span class="das-hero__pulse-dot" aria-hidden="true"></span>LIVE</span>
        </div>
        <div class="das-hero__tz">WAKTU INDONESIA BARAT (WIB)</div>
      </div>
    </div>
  </div>{{-- /das-hero --}}

  {{-- WIDGET PENGUMUMAN TARGET --}}
  <x-pengumuman-widget />

  {{-- ═══════════════════════════════════════════════════════
       CONTEXTUAL ALERT: BELUM ABSEN MASUK HARI INI
  ═══════════════════════════════════════════════════════ --}}
  @php
    $sudahAbsenMasuk = $absensiSaya && !empty($absensiSaya->jam_masuk);
    $isIzinSakitHariIni = $absensiSaya && in_array($absensiSaya->status, ['sakit', 'izin']);
    $isAlphaHariIni = $absensiSaya && $absensiSaya->status === 'alpha';
    $isJamMasukPagi = now()->format('H:i') < '07:15';
  @endphp
  @if($isAlphaHariIni)
    @php
      $isAutoAlpha = $absensiSaya && $absensiSaya->metode === 'auto-alpha';
    @endphp
    {{-- STATUS ALPHA (TIDAK HADIR TANPA KETERANGAN) --}}
    <div class="row mb-6">
      <div class="col-12">
        <div class="card border border-danger border-opacity-40 shadow-sm" style="background: linear-gradient(135deg, rgba(234, 84, 85, 0.16) 0%, rgba(234, 84, 85, 0.04) 100%);">
          <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded bg-label-danger fs-3"><i class="ti tabler-ban"></i></span>
              </div>
              <div>
                <h6 class="mb-1 text-danger fw-bold d-flex align-items-center gap-2">
                  <span>Status Presensi Hari Ini: {{ $isAutoAlpha ? 'ALPHA OTOMATIS' : 'ALPHA' }}</span>
                  <span class="badge bg-danger text-white font-monospace" style="font-size: 0.65rem;">TIDAK HADIR</span>
                </h6>
                <p class="text-body-secondary mb-0 small">
                  @if($isAutoAlpha)
                    Sistem menandai Anda <strong>Alpha Otomatis</strong> karena belum melakukan absensi masuk hingga batas waktu sekolah. Jika Anda sebenarnya sakit atau berhalangan, segera ajukan Surat Izin susulan.
                  @else
                    Sistem mencatat Anda <strong>Alpha (Tidak Hadir Tanpa Keterangan)</strong> pada hari ini ({{ now()->locale('id')->translatedFormat('l, d F Y') }}). Jika ada kendala darurat, segera ajukan Surat Izin.
                  @endif
                </p>
              </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('siswa.izin-sakit.index') }}" class="btn btn-danger btn-sm fw-bold shadow-sm">
                <i class="ti tabler-clipboard-check me-1"></i> Ajukan Surat Izin/Sakit
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  @elseif(!$sudahAbsenMasuk && !$isIzinSakitHariIni)
    <div class="row mb-6">
      <div class="col-12">
        @if($isJamMasukPagi)
          <div class="card border border-warning border-opacity-30 shadow-sm" style="background: linear-gradient(135deg, rgba(255, 159, 67, 0.12) 0%, rgba(255, 159, 67, 0.04) 100%);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-md">
                  <span class="avatar-initial rounded bg-label-warning pulse-amber"><i class="ti tabler-clock-exclamation fs-3"></i></span>
                </div>
                <div>
                  <h6 class="mb-1 text-warning fw-bold d-flex align-items-center gap-2">
                    <span>Pengingat Presensi Pagi</span>
                    <span class="badge font-monospace" style="font-size: 0.65rem; background: rgba(255, 159, 67, 0.15); color: #ff9f43; border: 1px solid rgba(255, 159, 67, 0.3); border-radius: 4px;">Sebelum 07:15 WIB</span>
                  </h6>
                  <p class="text-body-secondary mb-0 small">Anda belum melakukan absensi masuk hari ini ({{ now()->locale('id')->translatedFormat('l, d F Y') }}). Silakan lakukan Absen Mandiri atau scan di sekolah.</p>
                </div>
              </div>
              <div class="d-flex gap-2">
                <a href="#btnAbsenMasuk" class="btn btn-warning btn-sm fw-bold shadow-sm">
                  <i class="ti tabler-gps me-1"></i> Absen Mandiri
                </a>
              </div>
            </div>
          </div>
        @else
          <div class="card border border-danger border-opacity-30 shadow-sm" style="background: linear-gradient(135deg, rgba(234, 84, 85, 0.12) 0%, rgba(234, 84, 85, 0.04) 100%);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-md">
                  <span class="avatar-initial rounded bg-label-danger"><i class="ti tabler-alert-circle fs-3"></i></span>
                </div>
                <div>
                  <h6 class="mb-1 text-danger fw-bold d-flex align-items-center gap-2">
                    <span>Belum Absen Masuk Hari Ini</span>
                    <span class="badge" style="font-size: 0.65rem; background: rgba(234, 84, 85, 0.15); color: #ea5455; border: 1px solid rgba(234, 84, 85, 0.3); border-radius: 4px;">Belum Hadir</span>
                  </h6>
                  <p class="text-body-secondary mb-0 small">Sistem belum mencatat kehadiran masuk Anda untuk hari ini. Jika Anda berhalangan hadir (sakit/izin), segera ajukan Surat Izin.</p>
                </div>
              </div>
              <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('siswa.izin-sakit.index') }}" class="btn btn-danger btn-sm fw-bold shadow-sm">
                  <i class="ti tabler-clipboard-check me-1"></i> Ajukan Izin/Sakit
                </a>
                <a href="#btnAbsenMasuk" class="btn btn-outline-danger btn-sm fw-bold">
                  <i class="ti tabler-gps me-1"></i> Absen Mandiri
                </a>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  @endif


  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: STATS ROW — 4 Card Statistik Dynamic
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    {{-- Card 1: Kelas Aktif --}}
    <div class="col-lg-3 col-sm-6">
      <div class="card card-grad-primary h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti tabler-door fs-4"></i>
              </span>
            </div>
            <h4 class="mb-0 fw-semibold text-truncate">{{ $kelasNama }}</h4>
          </div>
          <p class="mb-1 text-body-secondary text-nowrap">Kelas Saya</p>
          <p class="mb-0">
            <span class="text-primary fw-medium me-2">Kelas Aktif</span>
            <small class="text-body-secondary">semester ini</small>
          </p>
        </div>
      </div>
    </div>

    {{-- Card 2: Streak Kehadiran (Gamified) --}}
    <div class="col-lg-3 col-sm-6">
      <div class="card card-grad-success h-100 position-relative overflow-hidden">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti tabler-flame fs-4 text-warning"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-0 fw-bold">{{ $attendance_streak ?? 0 }} Hari</h4>
              @php
                $streakVal = $attendance_streak ?? 0;
                $streakBadge = '🌱 Starter';
                if ($streakVal >= 30) $streakBadge = '🏆 Legend';
                elseif ($streakVal >= 15) $streakBadge = '⚡ Lightning';
                elseif ($streakVal >= 5) $streakBadge = '🔥 Flame Level';
              @endphp
              <span class="streak-flame-badge mt-1 py-0 px-2" style="font-size:0.65rem;">
                <i class="ti tabler-flame"></i> {{ $streakBadge }}
              </span>
            </div>
          </div>
          <p class="mb-1 text-body-secondary text-nowrap">Kehadiran Beruntun</p>
          <p class="mb-0">
            <span class="text-success fw-medium me-1">Streak Aktif</span>
            <small class="text-body-secondary">tanpa terlambat</small>
          </p>
        </div>
      </div>
    </div>

    {{-- Card 3: Izin Disetujui --}}
    <div class="col-lg-3 col-sm-6">
      <a href="{{ route('siswa.izin-sakit.index') }}" class="text-decoration-none">
        <div class="card card-grad-info h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="ti tabler-clipboard-check fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $izinDisetujui }} / {{ $totalIzinSaya }}</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Izin Disetujui</p>
            <p class="mb-0">
              <span class="text-info fw-medium me-2">Surat Izin/Sakit</span>
              <small class="text-body-secondary">lihat detail <i class="ti tabler-chevron-right ms-1"></i></small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 4: Persentase Kehadiran --}}
    <div class="col-lg-3 col-sm-6">
      <div class="card card-grad-warning h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti tabler-percentage fs-4"></i>
              </span>
            </div>
            <h4 class="mb-0 fw-semibold">{{ $persentaseKehadiran ?? 0 }}%</h4>
          </div>
          <p class="mb-1 text-body-secondary text-nowrap">Tingkat Kehadiran</p>
          <p class="mb-0">
            <span class="text-warning fw-medium me-2">{{ $statsHadir ?? 0 }} dari {{ $totalAbsenBulanIni ?? 0 }} hari</span>
            <small class="text-body-secondary">bulan ini</small>
          </p>
        </div>
      </div>
    </div>
  </div>{{-- /row g-6 mb-6 (Stats Row) --}}


  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: KONFIRMASI KEHADIRAN PELEPASAN (Khusus Kelas XII)
  ═══════════════════════════════════════════════════════ --}}
  @if($siswaRecord && $siswaRecord->kelas && (trim($siswaRecord->kelas->tingkat) === 'XII' || trim($siswaRecord->kelas->tingkat) === '12'))
    <div class="row mb-6">
      <div class="col-12">
        @if($absenPelepasan)
          <div class="card border border-success border-opacity-20 shadow-sm" style="background: rgba(40, 199, 111, 0.08);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-md">
                  <span class="avatar-initial rounded bg-label-success"><i class="ti tabler-circle-check fs-3"></i></span>
                </div>
                <div>
                  <h6 class="mb-0 text-success fw-bold">Presensi Pelepasan Terkonfirmasi!</h6>
                  <p class="text-body-secondary mb-0 small">Anda telah terkonfirmasi <strong>HADIR</strong> pada acara pelepasan kelas XII.</p>
                </div>
              </div>
              <div>
                <span class="badge bg-label-success p-2 px-3 border border-success border-opacity-20 font-monospace fs-6">
                  Jam Absen: {{ \Carbon\Carbon::parse($absenPelepasan->jam_absen)->format('H:i:s') }}
                </span>
              </div>
            </div>
          </div>
        @else
          <div class="card border border-danger border-opacity-20 shadow-sm" style="background: rgba(234, 84, 85, 0.08);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-md">
                  <span class="avatar-initial rounded bg-label-danger"><i class="ti tabler-circle-x fs-3"></i></span>
                </div>
                <div>
                  <h6 class="mb-0 text-danger fw-bold">Presensi Pelepasan Belum Tercatat</h6>
                  <p class="text-body-secondary mb-0 small">Silakan tunjukkan QR Code pada Kartu Pelepasan Anda kepada panitia saat acara berlangsung.</p>
                </div>
              </div>
              <div>
                <span class="badge bg-label-danger p-2 px-3 border border-danger border-opacity-20 fs-6">
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
       SECTION 9: MENU CEPAT GRID (COMPACT HORIZONTAL PILLS)
  ═══════════════════════════════════════════════════════ --}}
  <div class="card card-grad-gold mb-6">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <div class="avatar avatar-sm">
          <span class="avatar-initial rounded bg-label-warning">
            <i class="ti tabler-layout-grid fs-5"></i>
          </span>
        </div>
        <div>
          <h6 class="card-title mb-0 fw-bold">Menu Cepat Portal</h6>
        </div>
      </div>
      <span class="badge bg-label-warning px-2 py-1 font-monospace" style="font-size: 0.65rem;">8 Akses Pintas</span>
    </div>
    <div class="card-body pt-2 pb-3">
      <div class="siswa-quick-grid">
        {{-- 1. Izin & Sakit --}}
        <a href="{{ route('siswa.izin-sakit.index') }}" class="siswa-quick-item siswa-quick-item--success text-decoration-none">
          <span class="siswa-quick-item__icon"><i class="ti tabler-stethoscope"></i></span>
          <span class="siswa-quick-item__label">Izin &amp; Sakit</span>
        </a>
        {{-- 2. Papan Peringkat --}}
        <a href="{{ route('siswa.leaderboard') }}" class="siswa-quick-item siswa-quick-item--warning text-decoration-none">
          <span class="siswa-quick-item__icon"><i class="ti tabler-trophy"></i></span>
          <span class="siswa-quick-item__label">Peringkat</span>
        </a>
        {{-- 3. Riwayat Absensi --}}
        <a href="{{ route('siswa.absensi') }}" class="siswa-quick-item siswa-quick-item--info text-decoration-none">
          <span class="siswa-quick-item__icon"><i class="ti tabler-history"></i></span>
          <span class="siswa-quick-item__label">Riwayat</span>
        </a>
        {{-- 4. Penugasan --}}
        <a href="{{ route('siswa.assignments.index') }}" class="siswa-quick-item siswa-quick-item--primary text-decoration-none">
          <span class="siswa-quick-item__icon"><i class="ti tabler-clipboard-list"></i></span>
          <span class="siswa-quick-item__label">Penugasan</span>
        </a>
        {{-- 5. Profil Saya --}}
        <a href="{{ route('siswa.profile') }}" class="siswa-quick-item siswa-quick-item--info text-decoration-none">
          <span class="siswa-quick-item__icon"><i class="ti tabler-user"></i></span>
          <span class="siswa-quick-item__label">Profil</span>
        </a>
        {{-- 6. Download Kartu --}}
        <a href="{{ route('siswa.download-kartu') }}" target="_blank" class="siswa-quick-item siswa-quick-item--primary text-decoration-none">
          <span class="siswa-quick-item__icon"><i class="ti tabler-id-badge"></i></span>
          <span class="siswa-quick-item__label">Kartu Pelajar</span>
        </a>
        {{-- 7. Pengaturan --}}
        <a href="{{ route('siswa.profile') }}" class="siswa-quick-item siswa-quick-item--secondary text-decoration-none">
          <span class="siswa-quick-item__icon"><i class="ti tabler-settings"></i></span>
          <span class="siswa-quick-item__label">Pengaturan</span>
        </a>
        {{-- 8. Pengaduan --}}
        @fitur('fitur_pengaduan')
        <a href="{{ route('siswa.pengaduan') }}" class="siswa-quick-item siswa-quick-item--danger text-decoration-none">
          <span class="siswa-quick-item__icon"><i class="ti tabler-flag"></i></span>
          <span class="siswa-quick-item__label">Pengaduan</span>
        </a>
        @endfitur
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 4: ACTION CARDS — Tombol Download Kartu Pelajar & Pelepasan
  ═══════════════════════════════════════════════════════ --}}
  @php
    $isKelasXII = $siswaRecord && $siswaRecord->kelas && (trim($siswaRecord->kelas->tingkat) === 'XII' || trim($siswaRecord->kelas->tingkat) === '12');
  @endphp
  <div class="row g-6 mb-6">
    @if($isKelasXII)
    <div class="col-md-6 col-12">
      <a href="{{ route('siswa.download-kartu-pelepasan') }}" class="text-decoration-none">
        <div class="card card-grad-gold h-100 shadow-sm hover-elevation">
          <div class="card-body p-4 d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-lg">
                <span class="avatar-initial rounded bg-label-warning fs-3"><i class="ti tabler-id"></i></span>
              </div>
              <div>
                <h6 class="mb-1 text-white fw-bold">Unduh Kartu Pelepasan</h6>
                <small class="text-body-secondary">Khusus siswa kelas XII — cetak tanda kelulusan</small>
              </div>
            </div>
            <div class="avatar avatar-sm">
              <span class="avatar-initial rounded-circle bg-label-warning"><i class="ti tabler-chevron-right"></i></span>
            </div>
          </div>
        </div>
      </a>
    </div>
    @endif

    <div class="@if($isKelasXII) col-md-6 @else col-12 @endif col-12">
      <a href="{{ route('siswa.download-kartu') }}" target="_blank" class="text-decoration-none">
        <div class="card card-grad-primary h-100 shadow-sm hover-elevation">
          <div class="card-body p-4 d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-lg">
                <span class="avatar-initial rounded bg-label-primary fs-3"><i class="ti tabler-id-badge"></i></span>
              </div>
              <div>
                <h6 class="mb-1 text-white fw-bold">Unduh Kartu Pelajar</h6>
                <small class="text-body-secondary">Kartu identitas resmi siswa — cetak atau simpan</small>
              </div>
            </div>
            <div class="avatar avatar-sm">
              <span class="avatar-initial rounded-circle bg-label-primary"><i class="ti tabler-chevron-right"></i></span>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>


  {{-- ═══════════════════════════════════════════════════════
       SECTION 5: RINGKASAN REKAP BULAN INI & PROGRESS BAR
  ═══════════════════════════════════════════════════════ --}}
  <div class="card card-grad-primary mb-6">
    <div class="card-header pb-2 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <div class="avatar">
          <span class="avatar-initial rounded bg-label-success">
            <i class="ti tabler-chart-dots fs-4"></i>
          </span>
        </div>
        <div>
          <h5 class="card-title mb-0">Ringkasan Kehadiran Bulan Ini</h5>
          <small class="text-body-secondary">Statistik & akumulasi presensi</small>
        </div>
      </div>
      <span class="badge bg-label-success p-2">Bulan Ini</span>
    </div>
    <div class="card-body pt-3">
      <div class="row g-4 mb-4 text-center">
        <div class="col-6 col-md-3">
          <div class="p-3 rounded bg-label-success bg-opacity-10 border border-success border-opacity-10">
            <div class="avatar mx-auto mb-2"><span class="avatar-initial rounded bg-label-success"><i class="ti tabler-circle-check fs-4"></i></span></div>
            <h3 class="mb-0 text-success fw-bold" id="count-hadir">0</h3>
            <small class="text-body-secondary fw-semibold">Hadir</small>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 rounded bg-label-warning bg-opacity-10 border border-warning border-opacity-10">
            <div class="avatar mx-auto mb-2"><span class="avatar-initial rounded bg-label-warning"><i class="ti tabler-heart fs-4"></i></span></div>
            <h3 class="mb-0 text-warning fw-bold" id="count-sakit">0</h3>
            <small class="text-body-secondary fw-semibold">Sakit</small>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 rounded bg-label-info bg-opacity-10 border border-info border-opacity-10">
            <div class="avatar mx-auto mb-2"><span class="avatar-initial rounded bg-label-info"><i class="ti tabler-clipboard-check fs-4"></i></span></div>
            <h3 class="mb-0 text-info fw-bold" id="count-izin">0</h3>
            <small class="text-body-secondary fw-semibold">Izin</small>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 rounded bg-label-danger bg-opacity-10 border border-danger border-opacity-10">
            <div class="avatar mx-auto mb-2"><span class="avatar-initial rounded bg-label-danger"><i class="ti tabler-ban fs-4"></i></span></div>
            <h3 class="mb-0 text-danger fw-bold" id="count-alpha">0</h3>
            <small class="text-body-secondary fw-semibold">Alpha</small>
          </div>
        </div>
      </div>

      {{-- Progress Bar --}}
      <div class="siswa-progress-card border p-3 rounded bg-label-secondary bg-opacity-10">
        <div class="siswa-progress-header d-flex justify-content-between mb-2">
          <span class="siswa-progress-label small fw-bold text-body"><i class="ti tabler-trending-up me-1 text-primary"></i> Persentase Kehadiran Bulan Ini</span>
          <span class="siswa-progress-value fw-bold text-primary" id="progress-text">{{ $persentaseKehadiran ?? 0 }}%</span>
        </div>
        <div class="siswa-progress-bar-track progress" style="height: 10px; border-radius: 6px;">
          <div class="siswa-progress-bar-fill progress-bar bg-success" id="progress-fill" style="width: 0%; border-radius: 6px;" data-target="{{ $persentaseKehadiran ?? 0 }}"></div>
        </div>
        <div class="siswa-progress-footer d-flex justify-content-between mt-2 small text-body-secondary">
          <span>{{ $statsHadir ?? 0 }} hari hadir</span>
          <span>{{ $totalAbsenBulanIni ?? 0 }} total hari efektif</span>
        </div>
      </div>
    </div>
  </div>


  {{-- ═══════════════════════════════════════════════════════
       SECTION 6: MAIN CONTENT — Absensi Mandiri & Geofencing / Barcode
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    {{-- ABSENSI MANDIRI PANEL --}}
    <div class="col-lg-8 col-md-12">
      <div class="card card-grad-primary h-100">
        <div class="card-header pb-2 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti tabler-gps fs-4"></i>
              </span>
            </div>
            <div>
              <h5 class="card-title mb-0">Absensi Mandiri (GPS Location)</h5>
              <small class="text-body-secondary">Presensi berbasis koordinat Geofencing</small>
            </div>
          </div>
          <span class="badge bg-label-primary p-2">GPS Active</span>
        </div>
        <div class="card-body pt-3 d-flex flex-column justify-content-center align-items-center text-center" style="min-height: 250px;">
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
                  <span class="text-success small fw-bold text-uppercase mb-1" style="font-size:0.65rem; letter-spacing:1px;">Jam Masuk</span>
                  <div class="text-success fw-bold font-monospace fs-4" style="text-shadow: 0 0 10px rgba(40, 199, 111, 0.3);">{{ $absensiSaya->jam_masuk }}</div>
                </div>
                <div class="p-3 rounded border d-flex flex-column align-items-center justify-content-center" 
                     style="min-width: 130px; background: rgba(0, 207, 232, 0.08); border-color: rgba(0, 207, 232, 0.2) !important; backdrop-filter: blur(10px);">
                  <span class="text-info small fw-bold text-uppercase mb-1" style="font-size:0.65rem; letter-spacing:1px;">Jam Pulang</span>
                  <div class="text-info fw-bold font-monospace fs-4" style="text-shadow: 0 0 10px rgba(0, 207, 232, 0.3);">{{ $absensiSaya->jam_pulang }}</div>
                </div>
              </div>
            </div>
          @elseif($absenMandiriEnabled)
            {{-- KASUS 2: ABSEN MANDIRI AKTIF --}}
            <div class="w-100 py-2 px-3" style="max-width: 500px;">
              <div class="row g-3">
                <div class="col-12">
                  @if($absensiSaya && $absensiSaya->jam_masuk)
                    <div class="p-3 rounded h-100 border d-flex flex-column align-items-center justify-content-center" 
                         style="background: rgba(40, 199, 111, 0.08); border-color: rgba(40, 199, 111, 0.2) !important; backdrop-filter: blur(10px); min-height: 110px;">
                      <i class="ti tabler-circle-check text-success fs-2 mb-1"></i>
                      <div class="text-success fw-bold font-monospace fs-4" style="text-shadow: 0 0 10px rgba(40, 199, 111, 0.3);">{{ $absensiSaya->jam_masuk }}</div>
                      <div class="text-success small fw-bold mt-1 text-uppercase" style="font-size:0.6rem; letter-spacing:0.5px;">Tercatat Masuk</div>
                    </div>
                  @elseif($absensiSaya && $absensiSaya->status === 'alpha')
                    <div class="p-3 rounded h-100 border d-flex flex-column align-items-center justify-content-center" 
                         style="background: rgba(234, 84, 85, 0.08); border-color: rgba(234, 84, 85, 0.2) !important; backdrop-filter: blur(10px); min-height: 110px;">
                      <i class="ti tabler-lock text-danger fs-2 mb-1"></i>
                      <div class="text-danger fw-bold font-monospace fs-6">SESI MASUK TUTUP (ALPHA)</div>
                      <div class="text-danger small fw-semibold mt-1 text-uppercase" style="font-size:0.6rem; letter-spacing:0.5px;">Tercatat Tidak Hadir</div>
                    </div>
                  @else
                    <button type="button" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-lg h-100 d-flex align-items-center justify-content-center gap-2" id="btnAbsenMasuk">
                      <i class="ti tabler-login fs-2"></i>
                      <span>Absen Masuk</span>
                    </button>
                  @endif
                </div>
                <div class="col-12">
                  @if($absensiSaya && $absensiSaya->jam_pulang)
                    <div class="p-3 rounded h-100 border d-flex flex-column align-items-center justify-content-center" 
                         style="background: rgba(0, 207, 232, 0.08); border-color: rgba(0, 207, 232, 0.2) !important; backdrop-filter: blur(10px); min-height: 110px;">
                      <i class="ti tabler-circle-check text-info fs-2 mb-1"></i>
                      <div class="text-info fw-bold font-monospace fs-4" style="text-shadow: 0 0 10px rgba(0, 207, 232, 0.3);">{{ $absensiSaya->jam_pulang }}</div>
                      <div class="text-info small fw-bold mt-1 text-uppercase" style="font-size:0.6rem; letter-spacing:0.5px;">Tercatat Pulang</div>
                    </div>
                  @else
                    <button type="button" class="btn btn-warning btn-lg w-100 py-3 fw-bold shadow-lg h-100 d-flex align-items-center justify-content-center gap-2 {{ !$absensiSaya ? 'opacity-50' : '' }}" id="btnAbsenPulang" {{ !$absensiSaya ? 'disabled' : '' }}>
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
              <p class="text-body-secondary small mx-auto" style="max-width:320px;">Silakan hubungi Guru Piket atau Wali Kelas untuk melakukan pencatatan kehadiran.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- PANDUAN & BARCODE PANEL --}}
    <div class="col-lg-4 col-md-12">
      <div class="card shadow-sm mb-6">
        <div class="card-header pb-2">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="ti tabler-gps fs-4"></i>
              </span>
            </div>
            <div>
              <h5 class="card-title mb-0">Panduan Geofencing</h5>
              <small class="text-body-secondary">Akses lokasi perangkat</small>
            </div>
          </div>
        </div>
        <div class="card-body pt-3">
          <div class="d-flex flex-column gap-3">
            <div class="d-flex align-items-start gap-3">
              <div class="text-info fs-4 position-relative" style="top:2px;"><i class="ti tabler-gps"></i></div>
              <div>
                <div class="fw-bold small mb-1">Aktifkan GPS Perangkat</div>
                <p class="text-body-secondary small mb-0">Pastikan fitur Lokasi/GPS menyala sebelum menekan tombol absen.</p>
              </div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="text-info fs-4 position-relative" style="top:2px;"><i class="ti tabler-browser-check"></i></div>
              <div>
                <div class="fw-bold small mb-1">Izinkan Akses Browser</div>
                <p class="text-body-secondary small mb-0">Tekan "Allow/Izinkan" saat browser meminta informasi lokasi Anda.</p>
              </div>
            </div>
          </div>
          
          <div class="mt-3 p-3 bg-label-warning bg-opacity-10 rounded border border-warning border-opacity-10">
            <div class="d-flex align-items-center gap-2 text-warning mb-1">
              <i class="ti tabler-alert-triangle small"></i>
              <span class="small fw-bold">Penting:</span>
            </div>
            <p class="text-body-secondary mb-0" style="font-size: 0.75rem; line-height: 1.4;">Absensi mandiri hanya dapat dilakukan jika posisi GPS Anda berada dalam radius area madrasah yang ditentukan.</p>
          </div>
        </div>
      </div>

      @if($siswaRecord)
      {{-- 3D VIRTUAL STUDENT PASS (FLIP CARD) --}}
      <div class="card card-grad-gold mb-6 overflow-hidden">
        <div class="card-header pb-2 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti tabler-id-badge fs-4"></i>
              </span>
            </div>
            <div>
              <h5 class="card-title mb-0">Virtual Student Pass</h5>
              <small class="text-body-secondary">Kartu Pelajar & Barcode Perpus 3D</small>
            </div>
          </div>
          <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30">Interactive 3D</span>
        </div>
        <div class="card-body pt-3">
          <div class="siswa-vcard-scene" x-data="{ flipped: false }">
            <div class="siswa-vcard" :class="{ 'is-flipped': flipped }" @click="flipped = !flipped" title="Klik untuk membalik kartu">
              {{-- FRONT FACE --}}
              <div class="siswa-vcard__face siswa-vcard__front">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <div class="d-flex align-items-center gap-2">
                    <i class="ti tabler-school text-warning fs-5"></i>
                    <span class="fw-bold text-gradient-gold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">{{ $namaSekolah }}</span>
                  </div>
                  <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30 px-2 py-0.5 font-monospace" style="font-size: 0.6rem;">STUDENT PASS</span>
                </div>
                
                <div class="d-flex align-items-center gap-3 my-2">
                  <div class="siswa-vcard__chip me-1"></div>
                  <div>
                    <h6 class="mb-0 text-white fw-bold" style="letter-spacing: 0.5px; font-size: 0.95rem;">{{ $user->name }}</h6>
                    <small class="text-body-secondary" style="font-size: 0.7rem;">Kelas: {{ $kelasNama }} | NIS: {{ $siswaRecord->nis ?: ($siswaRecord->nisn ?: '-') }}</small>
                  </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-1 pt-2 border-top border-white border-opacity-10">
                  <span class="text-warning small font-monospace" style="font-size: 0.65rem;"><i class="ti tabler-arrows-shuffle me-1"></i> Tap untuk lihat Barcode</span>
                  <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-20" style="font-size:0.6rem;">AKTIF</span>
                </div>
              </div>

              {{-- BACK FACE (BARCODE 1D PERPUSTAKAAN) --}}
              <div class="siswa-vcard__face siswa-vcard__back">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-bold small text-white-50" style="font-size: 0.68rem;"><i class="ti tabler-barcode me-1 text-warning"></i> BARCODE PERPUSTAKAAN</span>
                  <span class="text-white-50 small" style="font-size: 0.62rem;">Tap untuk balik</span>
                </div>

                <div class="p-2 bg-white rounded d-flex align-items-center justify-content-center border shadow-sm mx-auto my-1" style="width: 100%; height: 75px;">
                  <div class="barcode-svg-container" style="width: 100%; height: 100%;">
                    {!! App\Support\BarcodeGenerator::renderSvg($siswaRecord->nis ?: $siswaRecord->nisn ?: 'SISWA' . $siswaRecord->id) !!}
                  </div>
                </div>

                <div class="text-center font-monospace text-warning small fw-bold" style="font-size: 0.72rem;">
                  ID: {{ App\Support\BarcodeGenerator::getFormattedData($siswaRecord->nis ?: $siswaRecord->nisn ?: 'SISWA' . $siswaRecord->id) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>


  {{-- ═══════════════════════════════════════════════════════
       SECTION 7: ANALYTICS CHARTS — Tren 7 Hari & Donut
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    <div class="col-lg-8">
      <div class="card card-grad-primary h-100">
        <div class="card-header pb-2 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti tabler-chart-area-line fs-4"></i>
              </span>
            </div>
            <div>
              <h5 class="card-title mb-0">Tren Kehadiran — 7 Hari Terakhir</h5>
              <small class="text-body-secondary">Grafik aktivitas presensi</small>
            </div>
          </div>
        </div>
        <div class="card-body pt-3">
          <div id="siswaAreaChart" style="min-height: 220px;"></div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card card-grad-primary h-100">
        <div class="card-header pb-2 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti tabler-chart-pie fs-4"></i>
              </span>
            </div>
            <div>
              <h5 class="card-title mb-0">Distribusi Bulan Ini</h5>
              <small class="text-body-secondary">Komposisi status</small>
            </div>
          </div>
        </div>
        <div class="card-body pt-3">
          <div id="siswaDonutChart" style="min-height: 220px;"></div>
        </div>
      </div>
    </div>
  </div>


  {{-- ═══════════════════════════════════════════════════════
       SECTION 8: RIWAYAT ABSENSI TERBARU (5 Entri)
  ═══════════════════════════════════════════════════════ --}}
  <div class="card shadow-sm mb-6">
    <div class="card-header pb-2 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <div class="avatar">
          <span class="avatar-initial rounded bg-label-info">
            <i class="ti tabler-history fs-4"></i>
          </span>
        </div>
        <div>
          <h5 class="card-title mb-0">Riwayat Absensi Terbaru</h5>
          <small class="text-body-secondary">5 Catatan presensi terakhir</small>
        </div>
      </div>
      <a href="{{ route('siswa.absensi') }}" class="btn btn-sm btn-label-secondary">
        <i class="ti tabler-external-link me-1"></i> Lihat Semua
      </a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive text-nowrap">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Tanggal</th>
              <th>Jam Masuk</th>
              <th>Jam Pulang</th>
              <th>Status</th>
              <th>Metode</th>
            </tr>
          </thead>
          <tbody>
            @forelse($riwayatAbsensi as $item)
            <tr>
              <td class="fw-medium">
                {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d M Y') }}
              </td>
              <td>
                @if($item->jam_masuk)
                  <span class="font-monospace fw-bold text-info">{{ \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') }}</span>
                @else
                  <span class="text-body-secondary">—</span>
                @endif
              </td>
              <td>
                @if($item->jam_pulang)
                  <span class="font-monospace text-body-secondary">{{ \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') }}</span>
                @else
                  <span class="text-body-secondary">—</span>
                @endif
              </td>
              <td>
                @php
                  $statusBadge = match($item->status) {
                    'hadir', 'terlambat' => 'bg-label-success',
                    'sakit' => 'bg-label-info',
                    'izin' => 'bg-label-warning',
                    'alpha' => 'bg-label-danger',
                    default => 'bg-label-secondary'
                  };
                  $statusText = match($item->status) {
                    'hadir' => 'Hadir',
                    'terlambat' => 'Terlambat',
                    'sakit' => 'Sakit',
                    'izin' => 'Izin',
                    'alpha' => 'Alpha',
                    default => ucfirst($item->status)
                  };
                @endphp
                <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
              </td>
              <td>
                @php
                  $metodeIcon = match($item->metode) {
                    'mandiri' => '<i class="ti tabler-gps me-1"></i> GPS Mandiri',
                    'qr' => '<i class="ti tabler-qrcode me-1"></i> Scan QR',
                    'manual' => '<i class="ti tabler-edit me-1"></i> Manual',
                    default => '<i class="ti tabler-help-circle me-1"></i> ' . ucfirst($item->metode ?? '—')
                  };
                @endphp
                <span class="small">{!! $metodeIcon !!}</span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center py-5 text-body-secondary">
                <i class="ti tabler-history fs-2 d-block mb-2"></i>
                Belum ada riwayat absensi yang tercatat.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnMasuk = document.getElementById('btnAbsenMasuk');
    const btnPulang = document.getElementById('btnAbsenPulang');
    const msgBox = document.getElementById('absenMessage');

    // Live Clock
    const clockElement = document.getElementById('live-clock');
    if (clockElement) {
        if (window._clockInterval) clearInterval(window._clockInterval);
        window._clockInterval = setInterval(() => {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            clockElement.textContent = `${hours}:${minutes}:${seconds}`;
        }, 1000);

        document.addEventListener('livewire:navigating', () => {
            if (window._clockInterval) clearInterval(window._clockInterval);
        });
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

    let audioCtx = null;
    function getAudioContext() {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        return audioCtx;
    }
    
    const playSound = (type) => {
        if (!bunyiAktif) return;
        
        const ctx = getAudioContext();
        const oscillator = ctx.createOscillator();
        const gainNode = ctx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(ctx.destination);
        
        const now = ctx.currentTime;
        
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

{{-- ApexCharts CDN --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts" defer id="apexcharts-cdn"></script>

<script>
function _initChartsAndCounters() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function animateCounter(el, target, duration = 1000) {
        if (prefersReducedMotion) { el.innerText = target; return; }
        const start = performance.now();
        function step(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            el.innerText = Math.floor(progress * target);
            if (progress < 1) requestAnimationFrame(step);
            else el.innerText = target;
        }
        requestAnimationFrame(step);
    }

    function initCounters() {
        const counters = [
            { id: 'count-hadir', target: {{ $statsHadir ?? 0 }} },
            { id: 'count-sakit', target: {{ $statsSakit ?? 0 }} },
            { id: 'count-izin', target: {{ $statsIzin ?? 0 }} },
            { id: 'count-alpha', target: {{ $statsAlpha ?? 0 }} }
        ];
        counters.forEach(function(item) {
            const el = document.getElementById(item.id);
            if (!el) return;
            if (item.target === 0) { el.innerText = '0'; return; }
            animateCounter(el, item.target, 1000);
        });
    }
    initCounters();

    const progressFill = document.getElementById('progress-fill');
    if (progressFill) {
        const target = parseFloat(progressFill.getAttribute('data-target')) || 0;
        setTimeout(function() {
            progressFill.style.width = target + '%';
            if (target < 50) {
                progressFill.classList.add('bg-danger');
            } else if (target < 75) {
                progressFill.classList.add('bg-warning');
            }
        }, 300);
    }

    if (typeof ApexCharts !== 'undefined') {
        const chartFont = "'Inter', 'Plus Jakarta Sans', sans-serif";
        const areaEl = document.querySelector('#siswaAreaChart');
        let areaChart;
        if (areaEl) {
            const seriesData = [];
            @if(isset($chartHadir) && count($chartHadir) > 0)
                seriesData.push({ name: 'Hadir', data: @json($chartHadir) });
                seriesData.push({ name: 'Sakit', data: @json($chartSakit) });
                seriesData.push({ name: 'Izin', data: @json($chartIzin) });
                seriesData.push({ name: 'Alpha', data: @json($chartAlpha) });
            @endif

            areaChart = new ApexCharts(areaEl, {
                series: seriesData.length > 0 ? seriesData : [
                    { name: 'Hadir', data: [0,0,0,0,0,0,0] },
                    { name: 'Sakit', data: [0,0,0,0,0,0,0] },
                    { name: 'Izin', data: [0,0,0,0,0,0,0] },
                    { name: 'Alpha', data: [0,0,0,0,0,0,0] }
                ],
                chart: {
                    type: 'area',
                    height: 220,
                    background: 'transparent',
                    fontFamily: chartFont,
                    toolbar: { show: false },
                    animations: { enabled: !prefersReducedMotion, easing: 'easeinout', speed: 800 }
                },
                theme: { mode: 'dark' },
                colors: ['#2FBF71', '#3AB7E0', '#F0A63B', '#EF5A5A'],
                stroke: { curve: 'smooth', width: 2.5 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.04, stops: [0, 90, 100] }
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: @json($chartDaysCategories),
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#8B96AB', fontSize: '11px' } }
                },
                yaxis: {
                    labels: { style: { colors: '#8B96AB' } },
                    min: 0,
                    tickAmount: 2
                },
                grid: { borderColor: 'rgba(231,236,245,0.06)', strokeDashArray: 4 },
                legend: { position: 'top', horizontalAlign: 'right', labels: { colors: '#8B96AB' } },
                tooltip: { theme: 'dark', y: { formatter: function(v) { return v + ' Hari'; } } },
                responsive: [
                    { breakpoint: 768, options: { chart: { height: 180 }, legend: { position: 'bottom', horizontalAlign: 'center' } } }
                ]
            });
            areaChart.render();
        }

        const donutEl = document.querySelector('#siswaDonutChart');
        let donutChart;
        if (donutEl) {
            donutChart = new ApexCharts(donutEl, {
                chart: {
                    type: 'donut',
                    height: 220,
                    background: 'transparent',
                    fontFamily: chartFont,
                    animations: { enabled: !prefersReducedMotion }
                },
                theme: { mode: 'dark' },
                series: [{{ $statsHadir ?? 0 }}, {{ $statsSakit ?? 0 }}, {{ $statsIzin ?? 0 }}, {{ $statsAlpha ?? 0 }}],
                labels: ['Hadir', 'Sakit', 'Izin', 'Alpha'],
                colors: ['#2FBF71', '#3AB7E0', '#F0A63B', '#EF5A5A'],
                legend: { show: false },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 3, colors: ['#121B2E'] },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '78%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    color: '#8B96AB',
                                    formatter: function() {
                                        return '{{ ($statsHadir ?? 0) + ($statsSakit ?? 0) + ($statsIzin ?? 0) + ($statsAlpha ?? 0) }}';
                                    }
                                },
                                value: { color: '#E7ECF5', fontWeight: 700 }
                            }
                        }
                    }
                },
                tooltip: { theme: 'dark', y: { formatter: function(v) { return v + ' Hari'; } } },
                responsive: [
                    { breakpoint: 576, options: { chart: { height: 190 } } }
                ]
            });
            donutChart.render();
        }
    }
}

if ('requestIdleCallback' in window) {
    requestIdleCallback(function() {
        requestAnimationFrame(function() {
            _initChartsAndCounters();
        });
    }, { timeout: 2000 });
} else {
    setTimeout(function() {
        _initChartsAndCounters();
    }, 100);
}
</script>

{{-- MODAL UPLOAD & CROP PAS FOTO SISWA --}}
<div class="modal fade" id="modalUploadFotoSiswa" tabindex="-1" aria-labelledby="modalUploadFotoSiswaLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title text-white d-flex align-items-center gap-2" id="modalUploadFotoSiswaLabel">
          <i class="ti tabler-camera fs-4"></i> Unggah Pas Foto Resmi (Square 1:1)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        
        {{-- Alert Petunjuk Foto Resmi --}}
        <div class="alert alert-info d-flex align-items-start gap-3 mb-4 rounded-3 border-0 shadow-sm" style="background-color: #eef2ff; color: #3730a3;">
          <i class="ti tabler-info-circle fs-3 flex-shrink-0 text-primary mt-1"></i>
          <div>
            <strong>Ketentuan Pas Foto Resmi:</strong>
            <ul class="mb-0 ps-3 mt-1 small">
              <li>Wajib menggunakan <strong>Pas Foto Resmi / Seragam Sekolah</strong> dengan latar belakang sesuai ketentuan <strong>{{ $namaSekolah }}</strong>.</li>
              <li>Format foto berupa <strong>Square 1:1</strong> (geser & sesuaikan posisi pada kanvas).</li>
              <li>Ukuran akhir file foto <strong>Maksimal 250 KB</strong> (otomatis dikompresi sistem).</li>
            </ul>
          </div>
        </div>

        {{-- Step 1: Input Select File --}}
        <div class="mb-3 text-center" id="containerSelectFoto">
          <label for="inputFotoResmi" class="btn btn-outline-primary btn-lg rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2" style="cursor: pointer;">
            <i class="ti tabler-upload fs-4"></i> Pilih File Foto Dari HP / Perangkat
          </label>
          <input type="file" id="inputFotoResmi" class="d-none" accept="image/jpeg,image/png,image/jpg,image/webp">
          <div class="form-text mt-2">Dukungan format: JPG, PNG, WEBP (Bisa ambil dari galeri atau kamera HP).</div>
        </div>

        {{-- Step 2: Cropper Canvas Container --}}
        <div id="cropperWrapper" class="d-none">
          <div class="row align-items-center mb-3">
            <div class="col-md-7">
              <div class="img-container bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center" style="max-height: 380px; min-height: 250px;">
                <img id="imageToCrop" src="" alt="Foto untuk dipotong" style="max-width: 100%; display: block;">
              </div>
            </div>
            <div class="col-md-5 text-center mt-3 mt-md-0">
              <div class="fw-semibold text-secondary mb-2 small text-uppercase">Pratinjau Hasil Crop (1:1)</div>
              <div class="preview-box mx-auto rounded-circle overflow-hidden shadow border border-3 border-primary mb-3" style="width: 140px; height: 140px;"></div>
              
              {{-- Realtime Size Indicator --}}
              <div class="card bg-light border-0 p-3 mb-3 text-start">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="small text-muted fw-semibold">Ukuran Hasil:</span>
                  <span id="badgeUkuranFile" class="badge bg-success fs-7">0 KB</span>
                </div>
                <div class="progress mb-1" style="height: 6px;">
                  <div id="progressBarUkuran" class="progress-bar bg-success" role="progressbar" style="width: 0%;"></div>
                </div>
                <div id="textKeteranganUkuran" class="small text-muted fs-8">Sesuai ketentuan (maks 250 KB)</div>
              </div>

              {{-- Controls --}}
              <div class="btn-group btn-group-sm w-100 shadow-sm" role="group">
                <button type="button" class="btn btn-outline-secondary" id="btnZoomIn" title="Perbesar"><i class="ti tabler-zoom-in"></i></button>
                <button type="button" class="btn btn-outline-secondary" id="btnZoomOut" title="Perkecil"><i class="ti tabler-zoom-out"></i></button>
                <button type="button" class="btn btn-outline-secondary" id="btnRotateLeft" title="Putar Kiri"><i class="ti tabler-rotate-counterclockwise"></i></button>
                <button type="button" class="btn btn-outline-secondary" id="btnRotateRight" title="Putar Kanan"><i class="ti tabler-rotate-clockwise"></i></button>
                <button type="button" class="btn btn-outline-secondary" id="btnResetCrop" title="Reset"><i class="ti tabler-refresh"></i></button>
              </div>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer bg-light px-4 py-3">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
        <label for="inputFotoResmi" class="btn btn-outline-primary d-none" id="btnGantiFotoLain">Pilih Foto Lain</label>
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 d-none" id="btnSimpanFotoSiswa">
          <span class="spinner-border spinner-border-sm d-none" id="spinnerUploadFoto" role="status" aria-hidden="true"></span>
          <i class="ti tabler-check fs-5" id="iconCheckFoto"></i> Simpan & Upload Pas Foto
        </button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL UPDATE BIODATA / TANGGAL LAHIR SISWA --}}
<div class="modal fade" id="modalUpdateTanggalLahir" tabindex="-1" aria-labelledby="modalUpdateTanggalLahirLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title text-white d-flex align-items-center gap-2" id="modalUpdateTanggalLahirLabel">
          <i class="ti tabler-user-edit fs-4"></i> Perbarui Biodata Mandiri Siswa
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formUpdateTanggalLahir">
        @csrf
        <div class="modal-body p-4">
          <div class="alert alert-primary d-flex align-items-start gap-3 mb-4 rounded-3 border-0 shadow-sm" style="background-color: #eef2ff; color: #3730a3;">
            <i class="ti tabler-info-circle fs-3 flex-shrink-0 text-primary mt-1"></i>
            <div class="small">
              Silakan perbarui data tanggal lahir, tempat lahir, nomor kontak, dan alamat Anda secara mandiri di sistem.
            </div>
          </div>

          <div class="row g-3">
            <div class="col-12">
              <label for="inputNamaLengkapSiswa" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="ti tabler-user"></i></span>
                <input type="text" id="inputNamaLengkapSiswa" name="nama_lengkap" class="form-control" value="{{ $siswaRecord ? $siswaRecord->nama_lengkap : $user->name }}" placeholder="Masukkan Nama Lengkap Anda" required>
              </div>
            </div>

            <div class="col-md-6">
              <label for="inputTanggalLahirSiswa" class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="ti tabler-calendar"></i></span>
                <input type="date" id="inputTanggalLahirSiswa" name="tanggal_lahir" class="form-control" value="{{ $tanggalLahirRaw }}" max="{{ date('Y-m-d') }}" required>
              </div>
            </div>

            <div class="col-md-6">
              <label for="inputTempatLahirSiswa" class="form-label fw-semibold">Tempat Lahir</label>
              <div class="input-group">
                <span class="input-group-text"><i class="ti tabler-map-pin"></i></span>
                <input type="text" id="inputTempatLahirSiswa" name="tempat_lahir" class="form-control" value="{{ $siswaRecord ? $siswaRecord->tempat_lahir : '' }}" placeholder="Kota / Kabupaten Kelahiran">
              </div>
            </div>

            <div class="col-md-6">
              <label for="inputNoHpSiswa" class="form-label fw-semibold">No. HP Siswa</label>
              <div class="input-group">
                <span class="input-group-text"><i class="ti tabler-phone"></i></span>
                <input type="text" id="inputNoHpSiswa" name="no_hp" class="form-control" value="{{ $siswaRecord ? $siswaRecord->no_hp : '' }}" placeholder="08xxxxxxxxxx">
              </div>
            </div>

            <div class="col-md-6">
              <label for="inputNoHpOrtu" class="form-label fw-semibold">No. HP Orang Tua / Wali</label>
              <div class="input-group">
                <span class="input-group-text"><i class="ti tabler-phone-call"></i></span>
                <input type="text" id="inputNoHpOrtu" name="no_hp_ortu" class="form-control" value="{{ $siswaRecord ? $siswaRecord->no_hp_ortu : '' }}" placeholder="08xxxxxxxxxx">
              </div>
            </div>

            <div class="col-12">
              <label for="inputAlamatSiswa" class="form-label fw-semibold">Alamat Tempat Tinggal</label>
              <textarea id="inputAlamatSiswa" name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap tempat tinggal">{{ $siswaRecord ? $siswaRecord->alamat : '' }}</textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light px-4 py-3">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" id="btnSimpanTanggalLahir">
            <span class="spinner-border spinner-border-sm d-none" id="spinnerTanggalLahir" role="status" aria-hidden="true"></span>
            <i class="ti tabler-check fs-5" id="iconCheckTanggalLahir"></i> Simpan Perubahan Biodata
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const inputFoto = document.getElementById('inputFotoResmi');
  const cropperWrapper = document.getElementById('cropperWrapper');
  const imageToCrop = document.getElementById('imageToCrop');
  const btnGantiFotoLain = document.getElementById('btnGantiFotoLain');
  const btnSimpanFotoSiswa = document.getElementById('btnSimpanFotoSiswa');
  const badgeUkuranFile = document.getElementById('badgeUkuranFile');
  const progressBarUkuran = document.getElementById('progressBarUkuran');
  const textKeteranganUkuran = document.getElementById('textKeteranganUkuran');
  const spinnerUpload = document.getElementById('spinnerUploadFoto');
  const iconCheckFoto = document.getElementById('iconCheckFoto');

  let cropper = null;
  let finalBlob = null;

  if (inputFoto) {
    inputFoto.addEventListener('change', function(e) {
      const files = e.target.files;
      if (files && files.length > 0) {
        const file = files[0];
        if (!file.type.match(/^image\/(jpeg|png|jpg|webp)$/)) {
          if (window.Swal) {
            Swal.fire({
              icon: 'error',
              title: 'Format Tidak Sesuai',
              text: 'Harap pilih file gambar dengan format JPG, PNG, atau WEBP.'
            });
          } else {
            alert('Harap pilih file gambar dengan format JPG, PNG, atau WEBP.');
          }
          return;
        }

        const reader = new FileReader();
        reader.onload = function(evt) {
          imageToCrop.src = evt.target.result;
          cropperWrapper.classList.remove('d-none');
          btnGantiFotoLain.classList.remove('d-none');
          btnSimpanFotoSiswa.classList.remove('d-none');

          if (cropper) {
            cropper.destroy();
          }

          cropper = new Cropper(imageToCrop, {
            aspectRatio: 1, // Square 1:1
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.9,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            preview: '.preview-box',
            crop: function() {
              updateCanvasBlob();
            }
          });
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Zoom & Rotate Controls
  document.getElementById('btnZoomIn')?.addEventListener('click', () => cropper && cropper.zoom(0.1));
  document.getElementById('btnZoomOut')?.addEventListener('click', () => cropper && cropper.zoom(-0.1));
  document.getElementById('btnRotateLeft')?.addEventListener('click', () => cropper && cropper.rotate(-90));
  document.getElementById('btnRotateRight')?.addEventListener('click', () => cropper && cropper.rotate(90));
  document.getElementById('btnResetCrop')?.addEventListener('click', () => cropper && cropper.reset());

  // Convert cropped canvas to compressed blob (quality auto-tuning <= 250KB)
  function updateCanvasBlob() {
    if (!cropper) return;
    const canvas = cropper.getCroppedCanvas({
      width: 600,
      height: 600,
      imageSmoothingEnabled: true,
      imageSmoothingQuality: 'high'
    });

    if (!canvas) return;

    let quality = 0.9;
    function generateBlob(q) {
      canvas.toBlob(function(blob) {
        if (!blob) return;
        const sizeInKB = Math.round(blob.size / 1024);
        if (sizeInKB > 250 && q > 0.3) {
          generateBlob(q - 0.15);
          return;
        }

        finalBlob = blob;
        badgeUkuranFile.textContent = sizeInKB + ' KB';
        const percent = Math.min(100, Math.round((sizeInKB / 250) * 100));
        progressBarUkuran.style.width = percent + '%';

        if (sizeInKB <= 250) {
          badgeUkuranFile.className = 'badge bg-success fs-7';
          progressBarUkuran.className = 'progress-bar bg-success';
          textKeteranganUkuran.textContent = 'Sesuai ketentuan (' + sizeInKB + ' KB / maks 250 KB)';
          btnSimpanFotoSiswa.disabled = false;
        } else {
          badgeUkuranFile.className = 'badge bg-danger fs-7';
          progressBarUkuran.className = 'progress-bar bg-danger';
          textKeteranganUkuran.textContent = 'Ukuran melebihi 250 KB! (' + sizeInKB + ' KB)';
          btnSimpanFotoSiswa.disabled = true;
        }
      }, 'image/jpeg', q);
    }

    generateBlob(quality);
  }

  // Handle Save / Upload AJAX
  if (btnSimpanFotoSiswa) {
    btnSimpanFotoSiswa.addEventListener('click', function() {
      if (!finalBlob) {
        if (window.Swal) Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Silakan pilih foto terlebih dahulu.' });
        else alert('Silakan pilih foto terlebih dahulu.');
        return;
      }

      btnSimpanFotoSiswa.disabled = true;
      spinnerUpload.classList.remove('d-none');
      iconCheckFoto.classList.add('d-none');

      const formData = new FormData();
      formData.append('foto', finalBlob, 'pas_foto_siswa.jpg');

      fetch('{{ route("siswa.upload-foto") }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(response => response.json().then(data => ({ status: response.status, body: data })))
      .then(({ status, body }) => {
        btnSimpanFotoSiswa.disabled = false;
        spinnerUpload.classList.add('d-none');
        iconCheckFoto.classList.remove('d-none');

        if (status === 200 && body.success) {
          const avatarImg = document.getElementById('das-student-avatar-img');
          if (avatarImg && body.photo_url) {
            avatarImg.src = body.photo_url;
          }

          const modalEl = document.getElementById('modalUploadFotoSiswa');
          const modalIns = bootstrap.Modal.getInstance(modalEl);
          if (modalIns) modalIns.hide();

          Swal.fire({
            icon: 'success',
            title: '<span style="font-weight: 700; color: #0f172a; font-size: 1.25rem;">Pas Foto Resmi Berhasil Disimpan!</span>',
            html: `
              <div class="py-2 text-center">
                <div class="mb-3 position-relative d-inline-block">
                <p class="text-muted small mb-0">Pas foto telah berhasil diperbarui & disinkronkan ke Kartu Pelajar Digital.</p>
              </div>
            `,
            confirmButtonText: 'Selesai & Refresh',
            customClass: {
              popup: 'rounded-4 border-0 shadow-lg p-3',
              confirmButton: 'btn btn-primary px-4 py-2 rounded-pill shadow-sm fw-semibold'
            },
            buttonsStyling: false
          }).then(() => {
            window.location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: '<span style="font-weight: 700; color: #991b1b;">Gagal Mengunggah Foto</span>',
            text: data.message || 'Terjadi kesalahan saat memproses foto.',
            customClass: {
              popup: 'rounded-4 border-0 shadow-lg p-3',
              confirmButton: 'btn btn-danger px-4 py-2 rounded-pill shadow-sm fw-semibold'
            },
            buttonsStyling: false
          });
        }
      })
      .catch(err => {
        btnSimpanFotoSiswa.disabled = false;
        spinnerUpload.classList.add('d-none');
        iconCheckFoto.classList.remove('d-none');
        console.error(err);
        Swal.fire({
          icon: 'error',
          title: '<span style="font-weight: 700; color: #991b1b;">Kesalahan Sistem</span>',
          text: 'Terjadi masalah saat mengirim data ke server.',
          customClass: {
            popup: 'rounded-4 border-0 shadow-lg p-3',
            confirmButton: 'btn btn-danger px-4 py-2 rounded-pill shadow-sm fw-semibold'
          },
          buttonsStyling: false
        });
      });
    });
  }

  // Handle Update Tanggal Lahir Siswa
  const formUpdateTgl = document.getElementById('formUpdateTanggalLahir');
  if (formUpdateTgl) {
    formUpdateTgl.addEventListener('submit', function(e) {
      e.preventDefault();

      const btnSimpan = document.getElementById('btnSimpanTanggalLahir');
      const spinner = document.getElementById('spinnerTanggalLahir');
      const iconCheck = document.getElementById('iconCheckTanggalLahir');
      const inputTgl = document.getElementById('inputTanggalLahirSiswa');

      if (!inputTgl.value) {
        Swal.fire({
          icon: 'warning',
          title: 'Perhatian',
          text: 'Harap masukkan tanggal lahir yang valid.'
        });
        return;
      }

      btnSimpan.disabled = true;
      spinner.classList.remove('d-none');
      iconCheck.classList.add('d-none');

      const formData = new FormData(this);

      fetch('{{ route("siswa.update-biodata") }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(response => response.json().then(data => ({ status: response.status, body: data })))
      .then(({ status, body }) => {
        btnSimpan.disabled = false;
        spinner.classList.add('d-none');
        iconCheck.classList.remove('d-none');

        if (status === 200 && body.success) {
          const modalEl = document.getElementById('modalUpdateTanggalLahir');
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) {
            modalInstance.hide();
          }

          const displaySpan = document.getElementById('display-tanggal-lahir');
          if (displaySpan && body.formatted_tanggal_lahir) {
            displaySpan.textContent = body.formatted_tanggal_lahir;
          }

          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: body.message || 'Biodata Anda berhasil diperbarui.',
            timer: 2000,
            showConfirmButton: false
          });
        } else {
          let errorMsg = body.message || 'Gagal memperbarui biodata.';
          if (body.errors) {
            const errList = Object.values(body.errors).flat();
            errorMsg = errList.join(' ');
          }
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: errorMsg
          });
        }
      })
      .catch(err => {
        btnSimpan.disabled = false;
        spinner.classList.add('d-none');
        iconCheck.classList.remove('d-none');
        console.error(err);
        Swal.fire({
          icon: 'error',
          title: 'Kesalahan Sistem',
          text: 'Terjadi masalah saat memperbarui biodata.'
        });
      });
    });
  }
});
</script>
@endsection
