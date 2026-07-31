@extends('layouts/layoutMaster')

@section('title', 'Dashboard Guru — ' . ($pengaturanArr['nama_sekolah'] ?? 'Sistem Absensi'))

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .glass-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25) !important;
      background: rgba(255, 255, 255, 0.06) !important;
    }
    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }

    /* Live Clock Badge */
    .das-hero__time {
      font-family: monospace;
      font-weight: 700;
      font-size: 1.6rem;
      letter-spacing: 1px;
    }

    /* Schedule Status Badges */
    .schedule-badge-active {
      background: rgba(40, 199, 111, 0.15) !important;
      color: #28c76f !important;
      border: 1px solid rgba(40, 199, 111, 0.3);
      animation: pulseGlow 2s infinite;
    }
    @keyframes pulseGlow {
      0% { box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.4); }
      70% { box-shadow: 0 0 0 8px rgba(40, 199, 111, 0); }
      100% { box-shadow: 0 0 0 0 rgba(40, 199, 111, 0); }
    }

    /* Kalender Styles */
    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 6px;
      text-align: center;
    }
    .calendar-header-day {
      font-weight: 600;
      font-size: 0.75rem;
      color: #8b949e;
      text-transform: uppercase;
      padding-bottom: 8px;
    }
    .calendar-cell {
      aspect-ratio: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 600;
      position: relative;
      cursor: default;
      transition: all 0.15s ease;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .calendar-cell-empty {
      background: transparent !important;
      border: none !important;
    }
    .calendar-cell-today {
      border: 2px solid #7367f0 !important;
      box-shadow: 0 0 10px rgba(115, 103, 240, 0.4);
    }
    .calendar-cell-holiday {
      background: rgba(234, 84, 85, 0.1) !important;
      color: #ea5455 !important;
    }
    .calendar-cell-hadir {
      background: rgba(40, 199, 111, 0.15) !important;
      color: #28c76f !important;
    }
    .calendar-cell-terlambat {
      background: rgba(255, 159, 67, 0.15) !important;
      color: #ff9f43 !important;
    }
    .calendar-cell-izin {
      background: rgba(115, 103, 240, 0.15) !important;
      color: #7367f0 !important;
    }
    .calendar-cell-sakit {
      background: rgba(0, 207, 221, 0.15) !important;
      color: #00cfdd !important;
    }
    .calendar-cell-alpha {
      background: rgba(234, 84, 85, 0.15) !important;
      color: #ea5455 !important;
    }

    /* Stat circles */
    .stat-circle {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 6px;
      font-weight: 700;
      font-size: 1.1rem;
    }
    .stat-circle-success { background: rgba(40, 199, 111, 0.12); color: #28c76f; }
    .stat-circle-warning { background: rgba(255, 159, 67, 0.12); color: #ff9f43; }
    .stat-circle-info { background: rgba(0, 207, 221, 0.12); color: #00cfdd; }
    .stat-circle-danger { background: rgba(234, 84, 85, 0.12); color: #ea5455; }
    .stat-circle-primary { background: rgba(115, 103, 240, 0.12); color: #7367f0; }

    /* Quick Action Card */
    .quick-action-card {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 1rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      text-decoration: none;
      color: #fff;
      transition: all 0.2s ease;
    }
    .quick-action-card:hover {
      background: rgba(255, 255, 255, 0.07);
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.3);
      color: #fff;
    }
  </style>
@endsection

@section('content')
  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER — Identitas Guru & Live Clock
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          @if (isset($pengaturanArr['logo_sekolah']))
            <img src="{{ asset('uploads/logo/' . $pengaturanArr['logo_sekolah']) }}" alt="Logo Sekolah" class="das-hero__logo">
          @else
            <div class="das-hero__logo-placeholder">
              <i class="ti tabler-chalkboard-teacher text-info" aria-hidden="true"></i>
            </div>
          @endif
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="das-hero__pulse-dot" aria-hidden="true"></span>
            Portal Pendidik — Panel Guru
          </div>
          <h1 class="das-hero__school">{{ $user->name }}</h1>
          <p class="das-hero__welcome">
            {{ $guru->jabatan ?? 'Guru Pengajar' }} {{ $guru->nip ? '• NIP: ' . $guru->nip : '' }}
          </p>
        </div>
      </div>

      {{-- Clock --}}
      <div class="das-hero__clock" role="status">
        <div class="das-hero__date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        <div class="das-hero__time text-gradient-info">
          <span id="live-clock">00:00:00</span>
          <span class="das-hero__live-badge"><span class="das-hero__pulse-dot" aria-hidden="true"></span>LIVE</span>
        </div>
        <div class="das-hero__tz">WAKTU INDONESIA BARAT (WIB)</div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: 4 STAT CARDS INTERAKTIF (MURNI DATA GURU)
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-4 mb-4">
    {{-- Card 1: Presensi Saya Hari Ini --}}
    <div class="col-lg-3 col-sm-6">
      <div class="card card-grad-success h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti tabler-user-check fs-4"></i>
              </span>
            </div>
            <div>
              <h5 class="mb-0 fw-semibold text-white">
                @if($hadir_saya)
                  {{ ucfirst($hadir_saya->status) }}
                @else
                  Belum Absen
                @endif
              </h5>
              <small class="text-white-50">Presensi Mandiri Hari Ini</small>
            </div>
          </div>
          <div class="pt-2 border-top border-white-10 d-flex justify-content-between text-white-50 extra-small">
            <span>Masuk: <strong class="text-white">{{ $hadir_saya->jam_masuk ?? '-' }}</strong></span>
            <span>Pulang: <strong class="text-white">{{ $hadir_saya->jam_pulang ?? '-' }}</strong></span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 2: Jadwal Mengajar Hari Ini --}}
    <div class="col-lg-3 col-sm-6">
      <div class="card card-grad-info h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-info">
                <i class="ti tabler-calendar-event fs-4"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-0 fw-bold text-white">{{ $jadwalHariIni->count() }} Kelas</h4>
              <small class="text-white-50">Jadwal Mengajar Hari Ini</small>
            </div>
          </div>
          <div class="pt-2 border-top border-white-10 d-flex justify-content-between text-white-50 extra-small">
            <span>Status Jam: <strong class="text-info">{{ $jadwalSekarang ? 'Mengajar Sekarang' : ($jadwalHariIni->isNotEmpty() ? 'Ada Jadwal' : 'Tidak Ada Class') }}</strong></span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 3: Total Izin & Sakit Guru Bulan Ini --}}
    <div class="col-lg-3 col-sm-6">
      <div class="card card-grad-warning h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti tabler-stethoscope fs-4"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-0 fw-bold text-white">{{ $total_izin_bulan_ini }} Hari</h4>
              <small class="text-white-50">Izin / Sakit Saya Bulan Ini</small>
            </div>
          </div>
          <div class="pt-2 border-top border-white-10 d-flex justify-content-between text-white-50 extra-small">
            <span>Status Pengajuan: <strong class="text-warning">Terdaftar</strong></span>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 4: Attendance Streak --}}
    <div class="col-lg-3 col-sm-6">
      <div class="card card-grad-primary h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-3">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti tabler-flame fs-4"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-0 fw-bold text-white">{{ $attendance_streak }} Hari</h4>
              <small class="text-white-50">Attendance Streak Guru</small>
            </div>
          </div>
          <div class="pt-2 border-top border-white-10 d-flex justify-content-between text-white-50 extra-small">
            <span>Total Absen Bulan Ini: <strong class="text-white">{{ $total_absen_bulan_ini }} Hari</strong></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: CONTENT ROW — JADWAL MENGAJAR & AKSES CEPAT PENDIDIK
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-4 mb-4">
    {{-- Left Side: Jadwal Mengajar Guru Hari Ini --}}
    <div class="col-lg-8">
      <div class="das-panel h-100">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
          <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2 text-white">
            <i class="ti tabler-clock-play text-info fs-4"></i> Jadwal Pelajaran Mengajar ({{ now()->locale('id')->isoFormat('dddd') }})
          </h6>
          <a href="{{ route('guru.absensi-cepat') }}" class="btn das-btn --info btn-sm">
            <i class="ti tabler-bolt me-1"></i> Buka Absensi Cepat
          </a>
        </div>
        <div class="das-panel__body p-0">
          @if($jadwalHariIni->isNotEmpty())
            <div class="table-responsive">
              <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                  <tr style="background: rgba(255,255,255,0.03); font-size:0.75rem; text-transform:uppercase;">
                    <th class="ps-4">Jam Ke</th>
                    <th>Jam Mengajar</th>
                    <th>Kelas</th>
                    <th>Mata Pelajaran</th>
                    <th class="text-center">Status Jam</th>
                    <th class="pe-4 text-end">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($jadwalHariIni as $j)
                    @php
                      $jamMulai = substr($j->jam_mulai, 0, 5);
                      $jamSelesai = substr($j->jam_selesai, 0, 5);
                      $jamSekarangStr = now()->format('H:i');
                      $isSekarang = ($jamSekarangStr >= $jamMulai && $jamSekarangStr <= $jamSelesai);
                      $isSelesai = ($jamSekarangStr > $jamSelesai);
                    @endphp
                    <tr style="{{ $isSekarang ? 'background: rgba(40, 199, 111, 0.08);' : '' }}">
                      <td class="ps-4 text-white-50 fw-bold">{{ $j->jam_ke ?? $loop->iteration }}</td>
                      <td class="text-white fw-medium">
                        <i class="ti tabler-clock text-info me-1"></i> {{ $jamMulai }} - {{ $jamSelesai }}
                      </td>
                      <td>
                        <span class="badge bg-label-info fw-bold">{{ $j->kelas->nama ?? '-' }}</span>
                      </td>
                      <td class="text-white">{{ $j->mapel->nama_mapel ?? '-' }}</td>
                      <td class="text-center">
                        @if($isSekarang)
                          <span class="badge schedule-badge-active px-3 py-1">
                            <span class="pulse-dot me-1"></span> Mengajar Sekarang
                          </span>
                        @elseif($isSelesai)
                          <span class="badge bg-label-secondary text-white-50">Selesai</span>
                        @else
                          <span class="badge bg-label-warning text-warning">Mendatang</span>
                        @endif
                      </td>
                      <td class="pe-4 text-end">
                        <a href="{{ route('guru.absensi-cepat', ['kelas_id' => $j->kelas_id]) }}" class="btn btn-sm das-btn --success">
                          <i class="ti tabler-bolt me-1"></i> Absen Kelas
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center py-5">
              <i class="ti tabler-calendar-off text-white-50 fs-1 mb-2 opacity-50"></i>
              <h6 class="text-white fw-bold">Tidak Ada Jadwal Mengajar Hari Ini</h6>
              <p class="text-white-50 small mb-0">Tidak terdapat jadwal mengajar yang terdaftar untuk hari ini.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Right Side: Quick Action Menu & Status Presensi Mandiri --}}
    <div class="col-lg-4">
      <h6 class="text-white-50 small fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">Akses Cepat Pendidik</h6>
      <div class="d-flex flex-column gap-3 mb-4">
        <a href="{{ route('guru.absensi.scan') }}" class="quick-action-card">
          <div class="stat-icon bg-label-success text-success">
            <i class="ti tabler-qrcode"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white">Scan QR Absensi Saya</h6>
            <small class="text-white-50">Lakukan absensi masuk / pulang mandiri.</small>
          </div>
        </a>

        <a href="{{ route('guru.absensi-cepat') }}" class="quick-action-card">
          <div class="stat-icon bg-label-info text-info">
            <i class="ti tabler-bolt"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white">Absensi Cepat Siswa</h6>
            <small class="text-white-50">Input absensi massal siswa per kelas.</small>
          </div>
        </a>

        <a href="{{ route('guru.izin-sakit.index') }}" class="quick-action-card">
          <div class="stat-icon bg-label-warning text-warning">
            <i class="ti tabler-file-text"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white">Pengajuan Izin / Sakit</h6>
            <small class="text-white-50">Ajukan atau kelola surat izin berhalangan.</small>
          </div>
        </a>

        <a href="{{ route('assignments.index') }}" class="quick-action-card">
          <div class="stat-icon bg-label-primary text-primary">
            <i class="ti tabler-book-upload"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white">Penugasan Siswa</h6>
            <small class="text-white-50">Kelola tugas & materi pelajaran siswa.</small>
          </div>
        </a>
      </div>

      {{-- Status Presensi Guru --}}
      <div class="das-panel p-4 text-center">
        <div class="avatar avatar-xl bg-label-success mx-auto mb-3" style="width:64px; height:64px;">
          <span class="avatar-initial rounded-circle"><i class="ti tabler-user-check fs-2"></i></span>
        </div>
        <h6 class="text-white fw-bold mb-1">Presensi Guru Hari Ini</h6>
        <p class="text-white-50 small mb-3">
          @if($hadir_saya)
            Status: <strong class="text-success">{{ strtoupper($hadir_saya->status) }}</strong> ({{ $hadir_saya->jam_masuk ?? '-' }})
          @else
            Anda belum melakukan presensi masuk hari ini.
          @endif
        </p>
        <a href="{{ route('guru.absensi.scan') }}" class="btn das-btn --success w-100 py-2 fw-bold">
          <i class="ti tabler-qrcode me-1"></i> {{ $hadir_saya ? 'Buka Scanner Presensi' : 'Absen Sekarang' }}
        </a>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 4: KALENDER & REKAP KEHADIRAN BULANAN GURU
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2 text-white">
        <i class="ti tabler-calendar-month text-primary fs-4"></i> Kalender & Riwayat Kehadiran Saya
      </h6>
      <form action="{{ route('guru.dashboard') }}" method="GET" class="d-flex gap-2">
        <select name="month" class="form-select form-select-sm bg-dark text-white border-secondary">
          @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
              {{ \Carbon\Carbon::create(2000, $m, 1)->locale('id')->translatedFormat('F') }}
            </option>
          @endfor
        </select>
        <select name="year" class="form-select form-select-sm bg-dark text-white border-secondary">
          @for($y = now()->year; $y >= now()->year - 2; $y--)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
        <button type="submit" class="btn das-btn --primary btn-sm">Filter</button>
      </form>
    </div>
    <div class="das-panel__body p-4">
      {{-- Rekap Bulanan --}}
      <div class="row text-center mb-4 g-2">
        <div class="col-6 col-md">
          <div class="p-2 rounded" style="background: rgba(40, 199, 111, 0.08); border: 1px solid rgba(40, 199, 111, 0.15);">
            <div class="stat-circle stat-circle-success mx-auto">{{ $rekapBulanan['hadir'] }}</div>
            <small class="text-white-50">Hadir</small>
          </div>
        </div>
        <div class="col-6 col-md">
          <div class="p-2 rounded" style="background: rgba(255, 159, 67, 0.08); border: 1px solid rgba(255, 159, 67, 0.15);">
            <div class="stat-circle stat-circle-warning mx-auto">{{ $rekapBulanan['terlambat'] }}</div>
            <small class="text-white-50">Terlambat</small>
          </div>
        </div>
        <div class="col-6 col-md">
          <div class="p-2 rounded" style="background: rgba(0, 207, 221, 0.08); border: 1px solid rgba(0, 207, 221, 0.15);">
            <div class="stat-circle stat-circle-info mx-auto">{{ $rekapBulanan['sakit'] }}</div>
            <small class="text-white-50">Sakit</small>
          </div>
        </div>
        <div class="col-6 col-md">
          <div class="p-2 rounded" style="background: rgba(115, 103, 240, 0.08); border: 1px solid rgba(115, 103, 240, 0.15);">
            <div class="stat-circle stat-circle-primary mx-auto">{{ $rekapBulanan['izin'] }}</div>
            <small class="text-white-50">Izin</small>
          </div>
        </div>
        <div class="col-12 col-md">
          <div class="p-2 rounded" style="background: rgba(234, 84, 85, 0.08); border: 1px solid rgba(234, 84, 85, 0.15);">
            <div class="stat-circle stat-circle-danger mx-auto">{{ $rekapBulanan['alpha'] }}</div>
            <small class="text-white-50">Alpha</small>
          </div>
        </div>
      </div>

      {{-- Kalender Bulanan --}}
      @php
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
        $firstDayOfWeek = $startOfMonth->dayOfWeek;
        $offset = ($firstDayOfWeek + 6) % 7;
      @endphp

      <div class="calendar-grid mb-3">
        <div class="calendar-header-day">Sen</div>
        <div class="calendar-header-day">Sel</div>
        <div class="calendar-header-day">Rab</div>
        <div class="calendar-header-day">Kam</div>
        <div class="calendar-header-day">Jum</div>
        <div class="calendar-header-day">Sab</div>
        <div class="calendar-header-day">Min</div>

        @for($i = 0; $i < $offset; $i++)
          <div class="calendar-cell calendar-cell-empty"></div>
        @endfor

        @for($day = 1; $day <= $daysInMonth; $day++)
          @php
            $dateStr = \Carbon\Carbon::create($year, $month, $day)->toDateString();
            $abs = $rawAbsensiBulan->get($dateStr);
            $isToday = \Carbon\Carbon::today()->toDateString() == $dateStr;
            $isHoliday = isset($holidays[$dateStr]) || \Carbon\Carbon::create($year, $month, $day)->isSunday();

            $cellClass = '';
            $tooltip = '';

            if ($abs) {
              $cellClass = 'calendar-cell-' . $abs->status;
              $tooltip = strtoupper($abs->status) . ' (' . ($abs->jam_masuk ?? '-') . ')';
            } elseif ($isHoliday) {
              $cellClass = 'calendar-cell-holiday';
              $tooltip = $holidays[$dateStr] ?? 'Libur Akhir Pekan';
            }
          @endphp
          <div class="calendar-cell {{ $cellClass }} {{ $isToday ? 'calendar-cell-today' : '' }}"
               data-bs-toggle="tooltip"
               data-bs-placement="top"
               title="{{ $day }} {{ \Carbon\Carbon::create(2000, $month, 1)->locale('id')->translatedFormat('F') }}: {{ $tooltip ?: 'Tidak ada catatan' }}">
            <span>{{ $day }}</span>
          </div>
        @endfor
      </div>

      {{-- Legenda --}}
      <div class="d-flex flex-wrap justify-content-center gap-3 pt-2 border-top border-secondary">
        <div class="d-flex align-items-center gap-1 text-white-50 small">
          <span style="width:10px; height:10px; border-radius:50%; background:#28c76f; display:inline-block;"></span> Hadir
        </div>
        <div class="d-flex align-items-center gap-1 text-white-50 small">
          <span style="width:10px; height:10px; border-radius:50%; background:#ff9f43; display:inline-block;"></span> Terlambat
        </div>
        <div class="d-flex align-items-center gap-1 text-white-50 small">
          <span style="width:10px; height:10px; border-radius:50%; background:#00cfdd; display:inline-block;"></span> Sakit
        </div>
        <div class="d-flex align-items-center gap-1 text-white-50 small">
          <span style="width:10px; height:10px; border-radius:50%; background:#7367f0; display:inline-block;"></span> Izin
        </div>
        <div class="d-flex align-items-center gap-1 text-white-50 small">
          <span style="width:10px; height:10px; border-radius:50%; background:#ea5455; display:inline-block;"></span> Alpa
        </div>
        <div class="d-flex align-items-center gap-1 text-white-50 small">
          <span style="width:10px; height:10px; border-radius:50%; background:#ea5455; opacity:0.5; display:inline-block;"></span> Libur
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    function updateClock() {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      const el = document.getElementById('live-clock');
      if (el) {
        el.textContent = `${hours}:${minutes}:${seconds}`;
      }
    }
    updateClock();
    setInterval(updateClock, 1000);
  });
</script>
@endsection
