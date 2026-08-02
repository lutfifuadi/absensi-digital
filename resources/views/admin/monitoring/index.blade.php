@extends('layouts/layoutMaster')

@section('title', 'Rekap Monitoring Kehadiran Guru')

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

    /* ── Tabel rekap monitoring (pola das-theme) ── */
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
      height: 5px;
      border-radius: 99px;
      background: rgba(255, 255, 255, 0.08);
      overflow: hidden;
      min-width: 70px;
    }

    .slot-progress > div {
      height: 100%;
      border-radius: 99px;
      background: linear-gradient(90deg, #28c76f, #00cfe8);
    }

    .mon-delete-btn {
      background: transparent;
      border: none;
      color: #ea5455;
      opacity: 0.6;
      transition: all 0.2s;
    }

    .mon-delete-btn:hover {
      opacity: 1;
      transform: scale(1.1);
    }
  </style>
@endsection

@section('content')

  @php
    $stMeta = [
        'hadir'     => ['label' => 'Hadir',      'color' => 'success'],
        'terlambat' => ['label' => 'Terlambat',  'color' => 'warning'],
        'tidak_hadir' => ['label' => 'Tidak Hadir', 'color' => 'danger'],
    ];
    $statusOptions = array_keys($stMeta);
    $aggregate = [
        'guru'       => $rekapSlot->count(),
        'total_slot' => $rekapSlot->sum('total_slot'),
        'hadir'      => $rekapSlot->sum('hadir'),
        'terlambat'  => $rekapSlot->sum('terlambat'),
        'tidak_hadir'=> $rekapSlot->sum('tidak_hadir'),
        'belum_dimonitor' => $rekapSlot->sum('belum_dimonitor'),
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
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-briefcase text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Kehadiran Guru / Rekap Monitoring
          </div>
          <h4 class="das-hero__title text-gradient-gold">Rekap Monitoring Kehadiran Guru</h4>
          <p class="das-hero__subtitle">Rekapitulasi kehadiran guru per slot jam mengajar, termasuk ringkasan slot untuk guru part time.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white">
          <i class="ti tabler-calendar me-1"></i>
          {{ \Carbon\Carbon::parse($filters['tanggal_dari'] ?? date('Y-m-d'))->locale('id')->translatedFormat('d M Y') }} —
          {{ \Carbon\Carbon::parse($filters['tanggal_sampai'] ?? date('Y-m-d'))->locale('id')->translatedFormat('d M Y') }}
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       PANEL FILTER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__body">
      <form method="GET" action="{{ route('admin.monitoring.index') }}" class="row gy-3 gx-3 align-items-end">
        <div class="col-md-3 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-calendar-event me-1"></i> Tanggal Mulai
          </label>
          <input type="date" name="tanggal_dari" class="form-control" value="{{ $filters['tanggal_dari'] ?? '' }}" style="color-scheme:dark;">
        </div>
        <div class="col-md-3 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-calendar-event me-1"></i> Tanggal Selesai
          </label>
          <input type="date" name="tanggal_sampai" class="form-control" value="{{ $filters['tanggal_sampai'] ?? '' }}" style="color-scheme:dark;">
        </div>
        <div class="col-md-2 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-door me-1"></i> Kelas
          </label>
          <select name="kelas_id" class="form-select">
            <option value="">-- Semua Kelas --</option>
            @foreach ($kelases as $k)
              <option value="{{ $k->id }}" @selected(($filters['kelas_id'] ?? '') == $k->id)>{{ $k->nama_kelas ?? $k->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-user me-1"></i> Guru
          </label>
          <select name="guru_id" class="form-select">
            <option value="">-- Semua Guru --</option>
            @foreach ($gurus as $g)
              <option value="{{ $g->id }}" @selected(($filters['guru_id'] ?? '') == $g->id)>{{ $g->nama ?? $g->nama_lengkap }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-status-change me-1"></i> Status
          </label>
          <select name="status" class="form-select">
            <option value="">-- Semua Status --</option>
            @foreach ($statusOptions as $st)
              <option value="{{ $st }}" @selected(($filters['status'] ?? '') == $st)>{{ $stMeta[$st]['label'] }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-briefcase me-1"></i> Kategori Kepegawaian
          </label>
          <select name="tipe_kepegawaian" class="form-select">
            <option value="">-- Semua Kategori --</option>
            @foreach ($tipeOptions as $val => $label)
              <option value="{{ $val }}" @selected(($filters['tipe_kepegawaian'] ?? '') == $val)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 col-6">
          <label class="form-label fw-semibold small text-white-50 opacity-50">Aksi</label>
          <div class="d-flex gap-2">
            <button type="submit" class="das-btn das-btn--primary">
              <i class="ti tabler-search me-1"></i> Tampilkan
            </button>
            <a href="{{ route('admin.monitoring.index') }}" class="das-btn das-btn--ghost">
              <i class="ti tabler-refresh me-1"></i> Reset
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       PANEL RINGKASAN SLOT PER GURU (PRD-007 F-3)
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <i class="ti tabler-briefcase text-info"></i> Ringkasan Slot Mengajar per Guru
      </div>

      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="badge bg-label-primary px-2 py-1">
          <i class="ti tabler-briefcase-off me-1"></i> Part time dinilai per slot
        </span>
        <span class="das-chip --primary">{{ $aggregate['guru'] }} Guru</span>
        <span class="das-chip --info">{{ $aggregate['total_slot'] }} Slot</span>
      </div>
    </div>

    <div class="das-panel__body p-0">
      @if ($rekapSlot->isEmpty())
        <div class="text-center py-5">
          <div class="d-flex flex-column align-items-center gap-2 opacity-50">
            <i class="ti tabler-briefcase-off" style="font-size:2.5rem;"></i>
            <span class="small">Belum ada ringkasan slot pada rentang tanggal ini.</span>
          </div>
        </div>
      @else
        <div class="table-responsive">
          <table class="das-table align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-3">Guru</th>
                <th class="text-center">Kategori</th>
                <th class="text-center">Total Slot</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">Terlambat</th>
                <th class="text-center">Tidak Hadir</th>
                <th class="text-center">Belum Dimonitor</th>
                <th class="pe-3 text-center">% Kehadiran</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($rekapSlot as $row)
                @php
                  $isPT = ($row['tipe_kepegawaian'] ?? 'full_time') === 'part_time';
                  $pct = (float) ($row['persentase_hadir'] ?? 0);
                @endphp
                <tr>
                  <td class="ps-3">
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-xs flex-shrink-0">
                        <span class="avatar-initial rounded-circle {{ $isPT ? 'bg-label-primary' : 'bg-label-info' }}" style="font-size:0.62rem;">
                          {{ strtoupper(substr($row['nama'] ?? '-', 0, 1)) }}
                        </span>
                      </div>
                      <div class="fw-semibold" style="font-size:.8rem;">{{ $row['nama'] ?? '-' }}</div>
                    </div>
                  </td>
                  <td class="text-center">
                    <span class="badge {{ $isPT ? 'bg-label-primary' : 'bg-label-secondary' }} px-2 py-1" style="font-size:0.62rem;">
                      <i class="ti {{ $isPT ? 'tabler-briefcase-off' : 'tabler-briefcase' }} me-1"></i>
                      {{ $isPT ? 'Part Time' : 'Full Time' }}
                    </span>
                  </td>
                  <td class="text-center fw-bold text-white">{{ $row['total_slot'] }}</td>
                  <td class="text-center">
                    <span class="badge bg-label-success px-2 py-1">{{ $row['hadir'] }}</span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-label-warning px-2 py-1">{{ $row['terlambat'] }}</span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-label-danger px-2 py-1">{{ $row['tidak_hadir'] }}</span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-label-secondary px-2 py-1">{{ $row['belum_dimonitor'] }}</span>
                  </td>
                  <td class="pe-3">
                    <div class="d-flex align-items-center gap-2 justify-content-end">
                      <div class="slot-progress flex-grow-1">
                        <div style="width: {{ min($pct, 100) }}%;"></div>
                      </div>
                      <span class="fw-bold text-info small" style="min-width:44px; text-align:right;">{{ number_format($pct, 1) }}%</span>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       PANEL DETAIL MONITORING
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <i class="ti tabler-list-check text-info"></i> Detail Monitoring Kehadiran
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="das-chip --info">{{ $rekap->total() }} Catatan</span>
      </div>
    </div>

    <div class="das-panel__body p-0">
      @if ($rekap->isEmpty())
        <div class="text-center py-5">
          <div class="d-flex flex-column align-items-center gap-2 opacity-50">
            <i class="ti tabler-clipboard-off" style="font-size:2.5rem;"></i>
            <span class="small">Belum ada catatan monitoring pada filter ini.</span>
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
                <th>Guru</th>
                <th>Mata Pelajaran</th>
                <th class="text-center">Status</th>
                <th>Keterangan</th>
                <th>Guru Pengganti</th>
                <th>Pencatat</th>
                <th class="pe-3 text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($rekap as $mon)
                @php
                  $jadwal = $mon->jadwalPelajaran;
                  $kelasNama = $jadwal?->kelas?->nama_kelas ?? $jadwal?->kelas?->nama ?? '-';
                  $guru = $jadwal?->guru;
                  $guruNama = $guru?->nama ?? $guru?->nama_lengkap ?? '-';
                  $guruTipe = $guru?->tipe_kepegawaian ?? null;
                  $st = $stMeta[$mon->status] ?? ['label' => ucfirst(str_replace('_', ' ', $mon->status)), 'color' => 'secondary'];
                  $ket = $mon->keterangan;
                  if ($mon->status === 'terlambat' && $mon->lama_terlambat) {
                      $ket = trim(($ket ? $ket . ' · ' : '') . 'Terlambat ' . $mon->lama_terlambat . ' menit');
                  }
                  if ($mon->keterangan_lain) {
                      $ket = trim(($ket ? $ket . ' · ' : '') . $mon->keterangan_lain);
                  }
                  $pengganti = $mon->ada_pengganti
                      ? ($mon->guruPengganti?->nama ?? $mon->guruPengganti?->nama_lengkap ?? $mon->guru_pengganti_nama ?? '-')
                      : '-';
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
                  <td class="text-nowrap">{{ $kelasNama }}</td>
                  <td class="text-nowrap">
                    <div class="fw-semibold text-white" style="font-size:.78rem;">{{ $guruNama }}</div>
                    @if ($guruTipe)
                      <small class="badge {{ $guruTipe === 'part_time' ? 'bg-label-primary' : 'bg-label-secondary' }} px-2 py-1 mt-1" style="font-size:0.58rem;">
                        {{ $guruTipe === 'part_time' ? 'Part Time' : 'Full Time' }}
                      </small>
                    @endif
                  </td>
                  <td class="text-nowrap text-capitalize">{{ $jadwal?->mata_pelajaran ?? '-' }}</td>
                  <td class="text-center">
                    <span class="badge bg-label-{{ $st['color'] }} px-3 py-1 text-capitalize">{{ $st['label'] }}</span>
                  </td>
                  <td style="max-width:240px; white-space:normal;">
                    <span class="small text-white-50">{{ $ket ?: '-' }}</span>
                  </td>
                  <td class="text-nowrap small">{{ $pengganti }}</td>
                  <td class="text-nowrap small">{{ $mon->pencatat?->name ?? '-' }}</td>
                  <td class="pe-3 text-end">
                    <form method="POST" action="{{ route('admin.monitoring.destroy', $mon->id) }}"
                          onsubmit="return confirm('Hapus catatan monitoring ini?');" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="mon-delete-btn" title="Hapus">
                        <i class="ti tabler-trash fs-5"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        @if ($rekap->hasPages())
          <div class="px-4 py-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
            {{ $rekap->links('vendor.pagination.users') }}
          </div>
        @endif
      @endif
    </div>
  </div>

@endsection
