@extends('layouts/layoutMaster')

@section('title', 'Rekap Absensi Siswa per Jam — {{ $siswa->nama_lengkap }}')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <style>
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

    .riwayat-table thead th {
      font-size: 0.62rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #666;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      background: rgba(255, 255, 255, 0.02);
    }

    .riwayat-table tbody td {
      vertical-align: middle;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      padding: 0.65rem 0.75rem;
      font-size: 0.8rem;
      color: #ccc;
    }

    .riwayat-table tbody tr:hover td {
      background: rgba(255, 255, 255, 0.03);
    }

    /* Mini stat tile */
    .stat-tile {
      border: 1px solid rgba(255, 255, 255, 0.05);
      background: rgba(255, 255, 255, 0.02);
      border-radius: 10px;
      padding: 0.9rem 1rem;
      height: 100%;
      transition: border-color 0.15s ease, background 0.15s ease;
    }

    .stat-tile:hover {
      border-color: rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.04);
    }
  </style>
@endsection

@section('content')

  @php
    $riwayat = $rekap['riwayat'] ?? collect();
    $perMapel = $rekap['perMapel'] ?? collect();

    $total = $riwayat->count();
    $acc = ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'dispen' => 0, 'bolos' => 0];
    foreach ($riwayat as $r) {
        if (isset($acc[$r['status']])) {
            $acc[$r['status']]++;
        }
    }
    $persen = $total > 0 ? round((($acc['hadir'] + $acc['terlambat']) / $total) * 100, 1) : 0.0;

    // Mapping status → warna & ikon (UI-SPEC-006 §4.1/§5.4)
    $statusMeta = [
        'hadir'     => ['label' => 'Hadir',     'color' => 'success',  'icon' => 'tabler-user-check'],
        'terlambat' => ['label' => 'Terlambat', 'color' => 'warning',  'icon' => 'tabler-clock-exclamation'],
        'alpha'     => ['label' => 'Alpha',     'color' => 'danger',   'icon' => 'tabler-user-x'],
        'izin'      => ['label' => 'Izin',      'color' => 'info',     'icon' => 'tabler-file-description'],
        'sakit'     => ['label' => 'Sakit',     'color' => 'secondary','icon' => 'tabler-stethoscope'],
        'dispen'    => ['label' => 'Dispen',    'color' => 'primary',  'icon' => 'tabler-file-check'],
        'bolos'     => ['label' => 'Bolos',     'color' => 'dark',     'icon' => 'tabler-walk'],
    ];
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
          <div class="das-hero__logo-placeholder" style="width:56px;height:56px;">
            <span class="fw-bold" style="font-size:1.25rem;">
              {{ strtoupper(substr($siswa->nama_lengkap ?? 'S', 0, 1)) }}
            </span>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Absensi / Rekap Absensi Siswa per Jam / Detail Siswa
          </div>
          <h4 class="das-hero__title text-gradient-gold">{{ $siswa->nama_lengkap ?? '-' }}</h4>
          <p class="das-hero__subtitle mb-0">
            NIS {{ $siswa->nis ?? '-' }} · Kelas {{ $siswa->kelas->nama ?? '-' }}
          </p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="d-flex flex-wrap gap-2">
          <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white">
            <i class="ti tabler-calendar me-1"></i>
            {{ \Carbon\Carbon::parse($dari)->locale('id')->translatedFormat('d M Y') }} —
            {{ \Carbon\Carbon::parse($sampai)->locale('id')->translatedFormat('d M Y') }}
          </div>
          <a href="{{ route('admin.absensi-per-jam.rekap', [
                'kelas_id' => $siswa->kelas->id ?? null,
                'dari' => $dari,
                'sampai' => $sampai,
              ]) }}" class="das-btn das-btn--ghost">
            <i class="ti tabler-arrow-left me-1"></i> Kembali ke Rekap
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       FILTER RENTANG (F-5)
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__body">
      <form method="GET" action="{{ route('admin.absensi-per-jam.rekap.siswa', $siswa->id) }}"
        class="row gy-3 gx-3 align-items-end">
        <div class="col-md-4 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-calendar-event me-1"></i> Tanggal Mulai
          </label>
          <input type="date" name="dari" class="form-control" value="{{ $dari }}" style="color-scheme:dark;">
        </div>
        <div class="col-md-4 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-calendar-event me-1"></i> Tanggal Selesai
          </label>
          <input type="date" name="sampai" class="form-control" value="{{ $sampai }}" style="color-scheme:dark;">
        </div>
        <div class="col-md-4 d-flex gap-2">
          <button type="submit" class="das-btn das-btn--primary">
            <i class="ti tabler-search me-1"></i> Tampilkan
          </button>
          <a href="{{ route('admin.absensi-per-jam.rekap.siswa', $siswa->id) }}" class="das-btn das-btn--ghost">
            <i class="ti tabler-refresh me-1"></i> Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       STAT RINGKAS
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
      <div class="stat-tile d-flex flex-column justify-content-center">
        <div class="d-flex align-items-center gap-2">
          <i class="ti tabler-calendar-stats text-info"></i>
          <span class="fw-bold fs-5">{{ $total }}</span>
        </div>
        <small class="text-white-50 mt-1">Total Pertemuan</small>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-tile d-flex flex-column justify-content-center">
        <div class="d-flex align-items-center gap-2">
          <i class="ti tabler-user-check text-success"></i>
          <span class="fw-bold fs-5">{{ $acc['hadir'] }}</span>
        </div>
        <small class="text-white-50 mt-1">Hadir</small>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-tile d-flex flex-column justify-content-center">
        <div class="d-flex align-items-center gap-2">
          <i class="ti tabler-clock-exclamation text-warning"></i>
          <span class="fw-bold fs-5">{{ $acc['terlambat'] }}</span>
        </div>
        <small class="text-white-50 mt-1">Terlambat</small>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-tile d-flex flex-column justify-content-center">
        <div class="d-flex align-items-center gap-2">
          <i class="ti tabler-user-x text-danger"></i>
          <span class="fw-bold fs-5">{{ $acc['alpha'] }}</span>
        </div>
        <small class="text-white-50 mt-1">Alpha</small>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-tile d-flex flex-column justify-content-center">
        <div class="d-flex align-items-center gap-2">
          <i class="ti tabler-file-description text-info"></i>
          <span class="fw-bold fs-5">{{ $acc['izin'] + $acc['sakit'] + $acc['dispen'] }}</span>
        </div>
        <small class="text-white-50 mt-1">Izin/Sakit/Dispen</small>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="stat-tile d-flex flex-column justify-content-center">
        <div class="d-flex align-items-center gap-2">
          <i class="ti tabler-percentage text-primary"></i>
          <span class="fw-bold fs-5">{{ number_format($persen, 1) }}%</span>
        </div>
        <small class="text-white-50 mt-1">Kehadiran</small>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       RINGKASAN PER MATA PELAJARAN (F-5)
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <i class="ti tabler-book text-info"></i> Ringkasan per Mata Pelajaran
      </div>
      <div class="d-flex align-items-center gap-1">
        <span class="das-chip --success">H</span>
        <span class="das-chip --warning">T</span>
        <span class="das-chip --secondary">S</span>
        <span class="das-chip --info">I</span>
        <span class="das-chip --danger">A</span>
        <span class="das-chip --primary">D</span>
      </div>
    </div>

    @if ($perMapel->isEmpty())
      <div class="das-panel__body text-center py-5">
        <div class="d-flex flex-column align-items-center gap-2 opacity-50">
          <i class="ti tabler-book-off" style="font-size:2.5rem;"></i>
          <span class="small">Belum ada data absensi pada rentang tanggal ini.</span>
        </div>
      </div>
    @else
      <div class="table-responsive">
        <table class="das-table align-middle mb-0">
          <thead>
            <tr>
              <th class="ps-4 py-3">Mata Pelajaran</th>
              <th class="py-3 text-center">Total</th>
              <th class="py-3 text-center">H</th>
              <th class="py-3 text-center">T</th>
              <th class="py-3 text-center">S</th>
              <th class="py-3 text-center">I</th>
              <th class="py-3 text-center">A</th>
              <th class="py-3 text-center">D</th>
              <th class="py-3 pe-4 text-center">% Kehadiran</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($perMapel as $m)
              <tr>
                <td class="ps-4 fw-semibold">{{ $m['mata_pelajaran'] }}</td>
                <td class="text-center text-white-50">{{ $m['total'] }}</td>
                <td class="text-center"><span class="badge bg-label-success px-2 py-1 small">{{ $m['hadir'] }}</span></td>
                <td class="text-center"><span class="badge bg-label-warning px-2 py-1 small">{{ $m['terlambat'] }}</span></td>
                <td class="text-center"><span class="badge bg-label-secondary px-2 py-1 small">{{ $m['sakit'] }}</span></td>
                <td class="text-center"><span class="badge bg-label-info px-2 py-1 small">{{ $m['izin'] }}</span></td>
                <td class="text-center"><span class="badge bg-label-danger px-2 py-1 small">{{ $m['alpha'] }}</span></td>
                <td class="text-center"><span class="badge bg-label-primary px-2 py-1 small">{{ $m['dispen'] }}</span></td>
                <td class="pe-4 text-center fw-bold text-info">{{ number_format($m['persen'], 1) }}%</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  {{-- ═══════════════════════════════════════════════════════
       RIWAYAT PER JAM (kronologis)
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <i class="ti tabler-history text-info"></i> Riwayat Absensi per Jam
      </div>
      <span class="das-chip --info">{{ $total }} Catatan</span>
    </div>

    @if ($riwayat->isEmpty())
      <div class="das-panel__body text-center py-5">
        <div class="d-flex flex-column align-items-center gap-2 opacity-50">
          <i class="ti tabler-calendar-off" style="font-size:2.5rem;"></i>
          <span class="small">Tidak ada riwayat absensi pada rentang tanggal ini.</span>
        </div>
      </div>
    @else
      <div class="table-responsive">
        <table class="riwayat-table align-middle mb-0 w-100">
          <thead>
            <tr>
              <th class="ps-4 py-3">Tanggal</th>
              <th class="py-3">Mapel · Jam</th>
              <th class="py-3">Kelas</th>
              <th class="py-3">Guru</th>
              <th class="py-3 text-center">Status</th>
              <th class="py-3 text-center">Lama Terlambat</th>
              <th class="py-3 pe-4">Keterangan</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($riwayat as $r)
              @php
                $meta = $statusMeta[$r['status']] ?? ['label' => ucfirst($r['status']), 'color' => 'secondary', 'icon' => 'tabler-circle'];
              @endphp
              <tr>
                <td class="ps-4">
                  <span class="fw-semibold">{{ \Carbon\Carbon::parse($r['tanggal'])->format('d-m-Y') }}</span>
                  <span class="d-block text-white-50" style="font-size:.7rem;">{{ $r['hari'] }}</span>
                </td>
                <td>
                  <span>{{ $r['mata_pelajaran'] }}</span>
                  <span class="d-block text-white-50" style="font-size:.7rem;">
                    <i class="ti tabler-clock small"></i> {{ $r['jam_mulai'] }}–{{ $r['jam_selesai'] }}
                  </span>
                </td>
                <td class="text-white-50">{{ $r['kelas'] }}</td>
                <td class="text-white-50">{{ $r['guru'] }}</td>
                <td class="text-center">
                  <span class="badge bg-label-{{ $meta['color'] }} px-2 py-1 rounded-pill">
                    <i class="ti {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
                  </span>
                </td>
                <td class="text-center text-white-50">
                  {{ $r['lama_terlambat'] ? $r['lama_terlambat'] . ' mnt' : '-' }}
                </td>
                <td class="pe-4 text-white-50" style="max-width:260px;">
                  <span class="d-inline-block text-truncate" style="max-width:100%;"
                    title="{{ $r['keterangan'] ?? '' }}">{{ $r['keterangan'] ?? '-' }}</span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

@endsection
