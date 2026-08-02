@extends('layouts/layoutMaster')

@section('title', 'Kehadiran Mengajar Saya')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.03) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      border-radius: 12px;
    }

    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: inherit;
      border-radius: 5px;
    }

    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(0, 207, 232, 0.6);
      box-shadow: 0 0 0 3px rgba(0, 207, 232, 0.12);
    }

    .form-select option {
      background: #1e1e2d;
      color: #cdd2e0;
    }

    /* ── Tabel rekap (pola das-theme) ── */
    .das-table {
      font-size: 0.8rem;
      white-space: nowrap;
    }

    .das-table thead th {
      background: rgba(255, 255, 255, 0.04) !important;
      font-size: 0.62rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #888;
      padding: 0.6rem 0.8rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .das-table tbody td {
      padding: 0.55rem 0.8rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      vertical-align: middle;
      color: #ccc;
    }

    .das-table tbody tr:hover td {
      background: rgba(255, 255, 255, 0.03);
    }

    .slot-progress {
      height: 6px;
      border-radius: 99px;
      background: rgba(255, 255, 255, 0.08);
      overflow: hidden;
    }

    .slot-progress > div {
      height: 100%;
      border-radius: 99px;
      background: linear-gradient(90deg, #28c76f, #00cfe8);
    }
  </style>
@endsection

@section('content')

  @php
    $isPartTimeView = !empty($isPartTime) && isset($rekapSlot);
    $isFullTimeView = !$isPartTimeView && isset($rekap);
    $tipeLabel = ($tipeKepegawaian ?? 'full_time') === 'part_time' ? 'Part Time' : 'Full Time';
    $tipeIsPT = ($tipeKepegawaian ?? 'full_time') === 'part_time';
    $bulanNama = \Carbon\Carbon::create((int) ($tahun ?? date('Y')), (int) ($bulan ?? date('m')), 1)->locale('id')->translatedFormat('F Y');
  @endphp

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
            <i class="ti tabler-user-check text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Portal Guru / Kehadiran Mengajar
          </div>
          <h4 class="das-hero__title text-gradient-gold">Kehadiran Mengajar Saya</h4>
          <p class="das-hero__subtitle">
            @if ($isPartTimeView)
              Rekap slot jam mengajar (part time) — dinilai per slot, bukan absensi masuk–pulang.
            @else
              Catatan kehadiran mengajar Anda dari monitoring.
            @endif
          </p>
        </div>
      </div>

      <div class="das-hero__actions">
        <span class="badge {{ $tipeIsPT ? 'bg-label-primary' : 'bg-label-secondary' }} p-2 px-3 border border-white border-opacity-10">
          <i class="ti {{ $tipeIsPT ? 'tabler-briefcase-off' : 'tabler-briefcase' }} me-1"></i>
          {{ $tipeLabel }}
        </span>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       PANEL FILTER BULAN & TAHUN
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__body">
      <form method="GET" action="{{ route('guru.monitoring.index') }}" class="row gy-3 gx-3 align-items-end">
        <div class="col-md-3 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-calendar-month me-1"></i> Bulan
          </label>
          <select name="bulan" class="form-select">
            @for($m = 1; $m <= 12; $m++)
              <option value="{{ $m }}" @selected((int) ($bulan ?? date('m')) == $m)>
                {{ \Carbon\Carbon::create(2000, $m, 1)->locale('id')->translatedFormat('F') }}
              </option>
            @endfor
          </select>
        </div>
        <div class="col-md-3 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-calendar me-1"></i> Tahun
          </label>
          <select name="tahun" class="form-select">
            @for($y = now()->year; $y >= now()->year - 5; $y--)
              <option value="{{ $y }}" @selected((int) ($tahun ?? date('Y')) == $y)>{{ $y }}</option>
            @endfor
          </select>
        </div>
        <div class="col-md-3 col-6">
          <label class="form-label fw-semibold small text-white-50 opacity-50">Aksi</label>
          <div class="d-flex gap-2">
            <button type="submit" class="das-btn das-btn--primary">
              <i class="ti tabler-search me-1"></i> Tampilkan
            </button>
            <a href="{{ route('guru.monitoring.index') }}" class="das-btn das-btn--ghost">
              <i class="ti tabler-refresh me-1"></i> Reset
            </a>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white w-100 text-start">
            <i class="ti tabler-calendar-stats me-1"></i> {{ $bulanNama }}
          </div>
        </div>
      </form>
    </div>
  </div>

  @if ($isPartTimeView)
    {{-- ═══════════════════════════════════════════════════════
         PART TIME: RINGKASAN SLOT BULANAN
    ═══════════════════════════════════════════════════════ --}}
    <div class="row g-6 mb-6">
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card glass-card h-100">
          <div class="card-body p-3 text-center">
            <div class="text-primary fw-bold h4 mb-0">{{ $rekapSlot['total_slot'] ?? 0 }}</div>
            <small class="text-body-secondary">Total Slot</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card glass-card h-100">
          <div class="card-body p-3 text-center">
            <div class="text-success fw-bold h4 mb-0">{{ $rekapSlot['hadir'] ?? 0 }}</div>
            <small class="text-body-secondary">Hadir</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card glass-card h-100">
          <div class="card-body p-3 text-center">
            <div class="text-warning fw-bold h4 mb-0">{{ $rekapSlot['terlambat'] ?? 0 }}</div>
            <small class="text-body-secondary">Terlambat</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card glass-card h-100">
          <div class="card-body p-3 text-center">
            <div class="text-danger fw-bold h4 mb-0">{{ $rekapSlot['tidak_hadir'] ?? 0 }}</div>
            <small class="text-body-secondary">Tidak Hadir</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card glass-card h-100">
          <div class="card-body p-3 text-center">
            <div class="text-white-50 fw-bold h4 mb-0">{{ $rekapSlot['belum_dimonitor'] ?? 0 }}</div>
            <small class="text-body-secondary">Belum Dimonitor</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card glass-card h-100">
          <div class="card-body p-3 text-center">
            <div class="text-info fw-bold h4 mb-0">{{ $rekapSlot['persentase_hadir'] ?? 0 }}%</div>
            <small class="text-body-secondary">% Kehadiran</small>
          </div>
        </div>
      </div>
    </div>

    {{-- Detail slot bulanan --}}
    <div class="das-panel" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <i class="ti tabler-calendar-month text-info"></i> Detail Slot Mengajar — {{ $bulanNama }}
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
          @php
            $totalSlot = $rekapSlot['total_slot'] ?? 0;
            $pct = (float) ($rekapSlot['persentase_hadir'] ?? 0);
          @endphp
          <div class="d-flex align-items-center gap-2">
            <div class="slot-progress" style="width:110px;"><div style="width: {{ min($pct, 100) }}%;"></div></div>
            <span class="das-chip --success">{{ number_format($pct, 1) }}%</span>
          </div>
        </div>
      </div>

      <div class="das-panel__body p-0">
        @if (empty($rekapSlot['detail']))
          <div class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-briefcase-off" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada slot mengajar terjadwal pada bulan ini.</span>
            </div>
          </div>
        @else
          <div class="table-responsive">
            <table class="das-table align-middle mb-0">
              <thead>
                <tr>
                  <th class="ps-3">Tanggal</th>
                  <th class="text-center">Jam Pelajaran</th>
                  <th>Kelas</th>
                  <th>Mata Pelajaran</th>
                  <th class="text-center">Status</th>
                  <th class="pe-3">Keterangan</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($rekapSlot['detail'] as $d)
                  @php
                    $st = match ($d['status']) {
                        'hadir'        => ['Hadir', 'success'],
                        'terlambat'    => ['Terlambat', 'warning'],
                        'tidak_hadir'  => ['Tidak Hadir', 'danger'],
                        default        => ['Belum Dimonitor', 'secondary'],
                    };
                  @endphp
                  <tr>
                    <td class="ps-3">
                      <div class="fw-semibold text-white" style="font-size:.78rem;">
                        {{ \Carbon\Carbon::parse($d['tanggal'])->locale('id')->translatedFormat('d M Y') }}
                      </div>
                      <small class="text-white-50">{{ \Carbon\Carbon::parse($d['tanggal'])->locale('id')->translatedFormat('l') }}</small>
                    </td>
                    <td class="text-center text-nowrap">
                      <span class="badge bg-black bg-opacity-25 border border-white border-opacity-10 px-2 py-1" style="font-size:.68rem; font-weight:600;">
                        {{ $d['jam_mulai'] }} - {{ $d['jam_selesai'] }}
                      </span>
                    </td>
                    <td class="text-nowrap">{{ $d['kelas'] }}</td>
                    <td class="text-nowrap text-capitalize">{{ $d['mata_pelajaran'] ?? '-' }}</td>
                    <td class="text-center">
                      <span class="badge bg-label-{{ $st[1] }} px-3 py-1">{{ $st[0] }}</span>
                    </td>
                    <td class="pe-3" style="max-width:260px; white-space:normal;">
                      <span class="small text-white-50">{{ $d['keterangan'] ?: '-' }}</span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  @elseif ($isFullTimeView)
    {{-- ═══════════════════════════════════════════════════════
         FULL TIME: CATATAN MONITORING (perilaku lama)
    ═══════════════════════════════════════════════════════ --}}
    <div class="das-panel" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <i class="ti tabler-list-check text-info"></i> Catatan Monitoring Kehadiran — {{ $bulanNama }}
        </div>
        <span class="das-chip --info">{{ $rekap->count() }} Catatan</span>
      </div>

      <div class="das-panel__body p-0">
        @if ($rekap->isEmpty())
          <div class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-clipboard-off" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada catatan monitoring kehadiran pada bulan ini.</span>
            </div>
          </div>
        @else
          <div class="table-responsive">
            <table class="das-table align-middle mb-0">
              <thead>
                <tr>
                  <th class="ps-3">Tanggal</th>
                  <th class="text-center">Jam Pelajaran</th>
                  <th>Kelas</th>
                  <th>Mata Pelajaran</th>
                  <th class="text-center">Status</th>
                  <th class="pe-3">Keterangan</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($rekap as $mon)
                  @php
                    $jadwal = $mon->jadwalPelajaran;
                    $st = match ($mon->status) {
                        'hadir'       => ['Hadir', 'success'],
                        'terlambat'   => ['Terlambat', 'warning'],
                        'tidak_hadir' => ['Tidak Hadir', 'danger'],
                        default       => [ucfirst(str_replace('_', ' ', $mon->status)), 'secondary'],
                    };
                    $ket = $mon->keterangan;
                    if ($mon->status === 'terlambat' && $mon->lama_terlambat) {
                        $ket = trim(($ket ? $ket . ' · ' : '') . 'Terlambat ' . $mon->lama_terlambat . ' menit');
                    }
                    if ($mon->keterangan_lain) {
                        $ket = trim(($ket ? $ket . ' · ' : '') . $mon->keterangan_lain);
                    }
                  @endphp
                  <tr>
                    <td class="ps-3">
                      <div class="fw-semibold text-white" style="font-size:.78rem;">
                        {{ \Carbon\Carbon::parse($mon->tanggal)->locale('id')->translatedFormat('d M Y') }}
                      </div>
                      <small class="text-white-50">{{ \Carbon\Carbon::parse($mon->tanggal)->locale('id')->translatedFormat('l') }}</small>
                    </td>
                    <td class="text-center text-nowrap">
                      <span class="badge bg-black bg-opacity-25 border border-white border-opacity-10 px-2 py-1" style="font-size:.68rem; font-weight:600;">
                        {{ $jadwal?->jam_mulai }} - {{ $jadwal?->jam_selesai }}
                      </span>
                    </td>
                    <td class="text-nowrap">{{ $jadwal?->kelas?->nama_kelas ?? $jadwal?->kelas?->nama ?? '-' }}</td>
                    <td class="text-nowrap text-capitalize">{{ $jadwal?->mata_pelajaran ?? '-' }}</td>
                    <td class="text-center">
                      <span class="badge bg-label-{{ $st[1] }} px-3 py-1">{{ $st[0] }}</span>
                    </td>
                    <td class="pe-3" style="max-width:260px; white-space:normal;">
                      <span class="small text-white-50">{{ $ket ?: '-' }}</span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  @endif

@endsection
