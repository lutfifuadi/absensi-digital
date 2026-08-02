@extends('layouts/layoutMaster')

@section('title', 'Rekap Absensi Siswa per Jam')

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

    /* ── Matriks rekap: sticky col + header (pola admin/laporan/index) ── */
    .rekap-table {
      border-collapse: separate;
      border-spacing: 0;
      font-size: 0.8rem;
      white-space: nowrap;
    }

    .rekap-table thead th {
      position: sticky;
      top: 0;
      background: #1e1e2d !important;
      z-index: 3;
      font-size: 0.62rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #888;
      padding: 0.6rem 0.7rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      text-align: center;
    }

    .rekap-table tbody td {
      padding: 0.5rem 0.7rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      vertical-align: middle;
      color: #ccc;
      text-align: center;
    }

    .rekap-table tbody tr:hover td {
      background: rgba(255, 255, 255, 0.03);
    }

    .rekap-table .sticky-col {
      position: sticky;
      left: 0;
      background: #1e1e2d !important;
      z-index: 2;
      text-align: left;
    }

    .rekap-table thead th.sticky-col {
      z-index: 4;
    }

    /* ── Cell status .st-* — mapping warna UI-SPEC-006 §4.1/§5.2 ── */
    .st-cell {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 26px;
      height: 24px;
      border-radius: 4px;
      font-size: 0.72rem;
      font-weight: 800;
    }

    .st-hadir { background: rgba(40, 199, 111, 0.15) !important; color: #28c76f !important; }
    .st-terlambat { background: rgba(255, 159, 67, 0.15) !important; color: #ff9f43 !important; }
    .st-sakit { background: rgba(168, 170, 174, 0.15) !important; color: #a8aaae !important; }
    .st-izin { background: rgba(0, 207, 232, 0.15) !important; color: #00cfe8 !important; }
    .st-alpha { background: rgba(234, 84, 85, 0.15) !important; color: #ea5455 !important; }
    .st-dispen { background: rgba(115, 103, 240, 0.15) !important; color: #7367f0 !important; }

    .st-dash {
      color: rgba(255, 255, 255, 0.25);
      font-weight: 400;
    }
  </style>
@endsection

@section('content')

  @php
    // Kode rekap H/T/S/I/A/D → class .st-* (UI-SPEC-006 §4.1)
    $stClass = [
        'H' => 'st-hadir',
        'T' => 'st-terlambat',
        'S' => 'st-sakit',
        'I' => 'st-izin',
        'A' => 'st-alpha',
        'D' => 'st-dispen',
    ];
    // Warna badge akumulasi (sinkron §4.1)
    $accMeta = [
        'hadir'     => ['label' => 'H', 'color' => 'success'],
        'terlambat' => ['label' => 'T', 'color' => 'warning'],
        'sakit'     => ['label' => 'S', 'color' => 'secondary'],
        'izin'      => ['label' => 'I', 'color' => 'info'],
        'alpha'     => ['label' => 'A', 'color' => 'danger'],
        'dispen'    => ['label' => 'D', 'color' => 'primary'],
    ];
    $hasFilter = !empty($filters['kelas_id']);
    $exportParams = array_filter($filters);
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
            <i class="ti tabler-calendar-stats text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Absensi / Rekap Absensi Siswa per Jam
          </div>
          <h4 class="das-hero__title text-gradient-gold">Rekap Absensi per Jam</h4>
          <p class="das-hero__subtitle">Rekapitulasi kehadiran siswa per jam pelajaran per kelas dan mata pelajaran.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white">
          <i class="ti tabler-calendar me-1"></i>
          {{ \Carbon\Carbon::parse($filters['dari'])->locale('id')->translatedFormat('d M Y') }} —
          {{ \Carbon\Carbon::parse($filters['sampai'])->locale('id')->translatedFormat('d M Y') }}
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       PANEL FILTER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__body">
      <form method="GET" action="{{ route('admin.absensi-per-jam.rekap') }}" class="row gy-3 gx-3 align-items-end">
        <div class="col-md-3 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-calendar-event me-1"></i> Tanggal Mulai
          </label>
          <input type="date" name="dari" class="form-control" value="{{ $filters['dari'] }}" style="color-scheme:dark;">
        </div>
        <div class="col-md-3 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-calendar-event me-1"></i> Tanggal Selesai
          </label>
          <input type="date" name="sampai" class="form-control" value="{{ $filters['sampai'] }}" style="color-scheme:dark;">
        </div>
        <div class="col-md-3 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-door me-1"></i> Kelas <span class="text-danger">*</span>
          </label>
          <select name="kelas_id" class="form-select" required>
            <option value="">-- Pilih Kelas --</option>
            @foreach ($kelasOptions as $k)
              <option value="{{ $k->id }}" @selected($filters['kelas_id'] == $k->id)>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 col-6">
          <label class="form-label fw-semibold small text-white-50">
            <i class="ti tabler-book me-1"></i> Mata Pelajaran <span class="text-white-50 opacity-50">(opsional)</span>
          </label>
          <select name="mapel_id" class="form-select">
            <option value="">-- Semua Mapel --</option>
            @foreach ($mapelOptions as $m)
              <option value="{{ $m->id }}" @selected($filters['mapel_id'] == $m->id)>{{ $m->nama_mapel }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 d-flex flex-wrap gap-2 mt-1">
          <button type="submit" class="das-btn das-btn--primary">
            <i class="ti tabler-search me-1"></i> Tampilkan
          </button>
          <a href="{{ route('admin.absensi-per-jam.rekap') }}" class="das-btn das-btn--ghost">
            <i class="ti tabler-refresh me-1"></i> Reset
          </a>
          <a href="{{ route('admin.absensi-per-jam.rekap.export', $exportParams) }}"
            class="das-btn das-btn--success {{ $hasFilter ? '' : 'disabled' }}"
            @if (!$hasFilter) aria-disabled="true" @endif>
            <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       PANEL MATRIKS REKAP
  ═══════════════════════════════════════════════════════ --}}
  @if (!$rekap)
    {{-- Belum ada filter / belum tampil --}}
    <div class="das-panel">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <i class="ti tabler-grid-dots text-info"></i> Matriks Rekap
        </div>
      </div>
      <div class="das-panel__body text-center py-5">
        <div class="d-flex flex-column align-items-center gap-2 opacity-50">
          <i class="ti tabler-filter-search" style="font-size:2.5rem;"></i>
          <span class="small">Pilih kelas dan rentang tanggal, lalu klik <strong>Tampilkan</strong> untuk melihat
            matriks rekap.</span>
        </div>
      </div>
    </div>
  @else
    @php
      $kelas = $rekap['kelas'] ?? null;
      $siswaList = $rekap['siswa'] ?? collect();
      $pertemuan = $rekap['pertemuan'] ?? collect();
      $pivot = $rekap['pivot'] ?? [];
      $akumulasi = $rekap['akumulasi'] ?? [];
    @endphp

    <div class="das-panel">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <i class="ti tabler-grid-dots text-info"></i> Matriks Rekap —
          {{ $kelas->nama ?? '-' }} · {{ $filters['dari'] }} s/d {{ $filters['sampai'] }}
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
          {{-- Legend H·T·S·I·A·D --}}
          <div class="d-flex align-items-center gap-1">
            <span class="das-chip --success">H</span>
            <span class="das-chip --warning">T</span>
            <span class="das-chip --secondary">S</span>
            <span class="das-chip --info">I</span>
            <span class="das-chip --danger">A</span>
            <span class="das-chip --primary">D</span>
          </div>

          {{-- Rekap per Siswa (F-5) — bila daftar siswa tersedia --}}
          @if ($siswaList->isNotEmpty())
            <select class="form-select form-select-sm" style="min-width:170px;"
              aria-label="Rekap per siswa"
              onchange="if(this.value) window.location = this.value;">
              <option value="">Rekap per Siswa...</option>
              @foreach ($siswaList as $s)
                <option value="{{ route('admin.absensi-per-jam.rekap.siswa', $s->id) }}?dari={{ $filters['dari'] }}&sampai={{ $filters['sampai'] }}">
                  {{ $s->nama_lengkap }} ({{ $s->nis }})
                </option>
              @endforeach
            </select>
          @endif
        </div>
      </div>

      @if ($pertemuan->isEmpty())
        <div class="das-panel__body text-center py-5">
          <div class="d-flex flex-column align-items-center gap-2 opacity-50">
            <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
            <span class="small">Belum ada data absensi per jam pada rentang dan filter ini.</span>
          </div>
        </div>
      @else
        <div class="table-responsive">
          <table class="rekap-table align-middle mb-0">
            <thead>
              <tr>
                <th class="sticky-col" style="min-width:200px; text-align:left; padding-left:1rem;">Nama Siswa</th>
                @foreach ($pertemuan as $p)
                  <th title="{{ $p['mata_pelajaran'] }} · {{ $p['jam_mulai'] }}-{{ $p['jam_selesai'] }}">
                    {{ \Carbon\Carbon::parse($p['tanggal'])->format('d-m') }}<br>
                    <span class="fw-400" style="font-size:0.58rem; opacity:0.65;">{{ $p['jam_mulai'] }}</span>
                  </th>
                @endforeach
                <th class="text-center">H</th>
                <th class="text-center">T</th>
                <th class="text-center">S</th>
                <th class="text-center">I</th>
                <th class="text-center">A</th>
                <th class="text-center">D</th>
                <th class="text-center" style="min-width:80px;">% Hadir</th>
              </tr>
            </thead>
            <tbody>
              @forelse($siswaList as $siswa)
                @php
                  $acc = $akumulasi[$siswa->id] ?? [];
                @endphp
                <tr>
                  <td class="sticky-col" style="padding-left:1rem;">
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-xs flex-shrink-0">
                        <span class="avatar-initial rounded-circle bg-label-info" style="font-size:0.62rem;">
                          {{ strtoupper(substr($siswa->nama_lengkap ?? 'S', 0, 1)) }}
                        </span>
                      </div>
                      <div>
                        <div class="fw-semibold" style="font-size:.78rem;">{{ $siswa->nama_lengkap ?? '-' }}</div>
                        <div class="text-white-50" style="font-size:.65rem;">{{ $siswa->nis ?? '-' }}</div>
                      </div>
                    </div>
                  </td>

                  @foreach ($pertemuan as $p)
                    @php
                      $kode = $pivot[$siswa->id][$p['key']] ?? null;
                    @endphp
                    <td>
                      @if ($kode)
                        <span class="st-cell {{ $stClass[$kode] ?? '' }}">{{ $kode }}</span>
                      @else
                        <span class="st-dash">-</span>
                      @endif
                    </td>
                  @endforeach

                  @foreach (['hadir', 'terlambat', 'sakit', 'izin', 'alpha', 'dispen'] as $key)
                    <td class="text-center">
                      <span class="badge bg-label-{{ $accMeta[$key]['color'] }} px-2 py-1 small">
                        {{ $acc[$key] ?? 0 }}
                      </span>
                    </td>
                  @endforeach

                  <td class="text-center">
                    <span class="fw-bold text-info">{{ number_format($acc['persen'] ?? 0, 1) }}%</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="{{ $pertemuan->count() + 8 }}" class="text-center py-5 text-white-50 opacity-50 small">
                    Tidak ada siswa aktif di kelas ini.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      @endif
    </div>
  @endif

@endsection
