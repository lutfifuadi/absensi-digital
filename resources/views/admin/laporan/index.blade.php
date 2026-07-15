@extends('layouts/layoutMaster')

@section('title', 'Laporan Absensi Siswa')

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

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

    .pivot-table th,
    .pivot-table td {
      border-color: rgba(255, 255, 255, 0.08) !important;
    }

    .sticky-col {
      position: sticky;
      background: #1e1e2d !important;
      z-index: 1;
    }

    .sticky-header {
      position: sticky;
      top: 0;
      background: #1e1e2d !important;
      z-index: 3;
    }

    /* Status Colors for Dark Mode Pivot */
    .st-hadir { background: rgba(40, 199, 111, 0.15) !important; color: #28c76f !important; }
    .st-sakit { background: rgba(0, 207, 232, 0.15) !important; color: #00cfe8 !important; }
    .st-izin { background: rgba(255, 159, 67, 0.15) !important; color: #ff9f43 !important; }
    .st-alpha { background: rgba(234, 84, 85, 0.15) !important; color: #ea5455 !important; }
    .st-terlambat { background: rgba(168, 170, 174, 0.15) !important; color: #a8aaae !important; }

    /* PAGINATION */
    .das-page-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 32px;
      height: 32px;
      padding: 0 8px;
      font-size: 0.78rem;
      font-weight: 600;
      border-radius: 5px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: transparent;
      color: #888;
      text-decoration: none;
      transition: all 0.18s ease;
      cursor: pointer;
      line-height: 1;
      font-family: inherit;
    }

    .das-page-btn:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      border-color: rgba(255, 255, 255, 0.12);
    }

    .das-page-active {
      background: #7367f0 !important;
      color: #fff !important;
      border-color: #7367f0 !important;
    }

    .das-page-dots {
      border-color: transparent;
      background: transparent;
      color: #555;
      pointer-events: none;
    }

    .page-item.disabled .das-page-btn {
      opacity: 0.35;
      pointer-events: none;
    }
  </style>
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-report-analytics"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Data & Analitik Kehadiran
          </div>
          <h4 class="das-hero__title text-gradient-gold">Laporan Absensi</h4>
          <p class="das-hero__subtitle">Rekapitulasi absensi siswa, guru, dan staff tata usaha secara komprehensif.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: FILTER & EXPORT
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot --primary"></span>
        Filter Rekapitulasi
      </div>
    </div>
    <div class="das-panel__body">
      <form method="GET" action="{{ route('admin.laporan.index') }}" class="row gy-3 gx-3 align-items-end">
        <div class="col-12 col-sm-6 col-md-3">
          <label class="form-label text-white-50 small fw-bold">KELAS</label>
          <select class="form-select form-select-sm" name="kelas_id" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);">
            <option value="">Semua Kelas</option>
            @foreach ($kelasOptions as $k)
              <option value="{{ $k->id }}" @selected($filters['kelas_id'] == $k->id)>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-sm-3 col-md-2">
          <label class="form-label text-white-50 small fw-bold">BULAN</label>
          <select class="form-select form-select-sm" name="bulan" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);">
            @for ($m = 1; $m <= 12; $m++)
              <option value="{{ $m }}" @selected($filters['bulan'] == $m)>
                {{ \Carbon\Carbon::createFromDate(null, $m)->locale('id')->translatedFormat('F') }}</option>
            @endfor
          </select>
        </div>
        <div class="col-6 col-sm-3 col-md-2">
          <label class="form-label text-white-50 small fw-bold">TAHUN</label>
          <input type="number" class="form-control form-control-sm" name="tahun" value="{{ $filters['tahun'] }}" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);">
        </div>
        <div class="col-12 col-sm-6 col-md-2">
          <button type="submit" class="das-btn das-btn--info w-100">
            <i class="ti tabler-search"></i> Terapkan
          </button>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <div class="d-flex gap-2">
            <a href="{{ route('admin.laporan.exportExcel', request()->query()) }}" class="das-btn das-btn--success flex-grow-1">
              <i class="ti tabler-file-spreadsheet"></i> EXCEL
            </a>
            <a href="{{ route('admin.laporan.exportPdf', request()->query()) }}" class="das-btn das-btn--primary flex-grow-1" style="background: #ea5455; border-color: #ea5455;">
              <i class="ti tabler-file-type-pdf"></i> PDF
            </a>
          </div>
        </div>
      </form>

      <div class="mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
        <div class="mb-2">
          <small class="text-white-50 fw-bold opacity-75">REKAP KHUSUS:</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a href="{{ route('admin.laporan.exportExcelGuru', ['bulan' => $filters['bulan'], 'tahun' => $filters['tahun']]) }}"
            class="das-btn das-btn--ghost das-btn--ghost-sm">
            <i class="ti tabler-file-spreadsheet me-1"></i> Excel Guru
          </a>
          <a href="{{ route('admin.laporan.exportExcelStaff', ['bulan' => $filters['bulan'], 'tahun' => $filters['tahun']]) }}"
            class="das-btn das-btn--ghost das-btn--ghost-sm">
            <i class="ti tabler-file-spreadsheet me-1"></i> Excel Staff TU
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- Reset Data Section (Super Admin Only) --}}
  @if(auth()->check() && auth()->user()->isSuperAdmin())
  <div class="das-panel mb-4 border border-danger">
    <div class="das-panel__head">
      <div class="das-panel__title text-danger">
        <span class="das-panel__icon-dot --danger"></span>
        Reset Data Absensi
      </div>
    </div>
    <div class="das-panel__body">
      <div class="alert alert-warning d-flex align-items-center justify-content-between mb-0">
        <div>
          <i class="ti tabler-alert-triangle me-2"></i>
          <strong>Peringatan!</strong> Tindakan ini akan menghapus SEMUA data kehadiran siswa.
        </div>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#resetModal">
          <i class="ti tabler-trash me-1"></i> Reset Data
        </button>
      </div>
    </div>
  </div>

  <div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content bg-dark">
        <div class="modal-header border-secondary">
          <h5 class="modal-title text-danger" id="resetModalLabel">
            <i class="ti tabler-trash me-2"></i>Konfirmasi Reset Data
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Anda akan menghapus SEMUA data kehadiran siswa. Tindakan ini tidak dapat dibatalkan!</p>
          <div class="mb-3">
            <label for="confirmReset" class="form-label">Ketik <code>RESET</code> untuk konfirmasi:</label>
            <input type="text" class="form-control" id="confirmReset" name="confirm" placeholder="RESET">
          </div>
        </div>
        <div class="modal-footer border-secondary">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <form action="{{ route('admin.laporan.reset') }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" id="submitReset" disabled>
              <i class="ti tabler-trash me-1"></i> Ya, Reset Sekarang
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.getElementById('confirmReset')?.addEventListener('input', function(e) {
      document.getElementById('submitReset').disabled = e.target.value !== 'RESET';
    });
  </script>
  @endif

  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: SUMMARY STATS
  ═══════════════════════════════════════════════════════ --}}
  <div class="row gy-3 mb-4">
    @php
      $stats = [
        ['label' => 'Total', 'val' => $summary->total ?? 0, 'color' => 'secondary', 'icon' => 'tabler-list'],
        ['label' => 'Hadir', 'val' => $summary->hadir ?? 0, 'color' => 'success', 'icon' => 'tabler-circle-check'],
        ['label' => 'Izin', 'val' => $summary->izin ?? 0, 'color' => 'warning', 'icon' => 'tabler-file-description'],
        ['label' => 'Sakit', 'val' => $summary->sakit ?? 0, 'color' => 'info', 'icon' => 'tabler-stethoscope'],
        ['label' => 'Alpha', 'val' => $summary->alpha ?? 0, 'color' => 'danger', 'icon' => 'tabler-x'],
        ['label' => 'Terlambat', 'val' => $summary->terlambat ?? 0, 'color' => 'primary', 'icon' => 'tabler-clock'],
      ];
    @endphp
    @foreach ($stats as $s)
      <div class="col-6 col-sm-4 col-md-2">
        <div class="das-panel h-100 text-center">
          <div class="das-panel__body p-3">
            <div class="avatar avatar-sm mx-auto mb-2">
              <span class="avatar-initial rounded bg-label-{{ $s['color'] }}">
                <i class="ti {{ $s['icon'] }}"></i>
              </span>
            </div>
            <h4 class="mb-0 text-white fw-bold">{{ $s['val'] }}</h4>
            <div class="text-white-50 small mt-1" style="font-size:0.65rem; letter-spacing: 0.5px; text-transform: uppercase;">{{ $s['label'] }}</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Pivot Table --}}
  @if ($siswaList->isNotEmpty())
    <div class="card glass-card">
      <div class="card-header border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2" 
        style="background:transparent; border-color: rgba(255,255,255,0.08) !important;">
        <h6 class="card-title mb-0 text-white">
          <i class="ti tabler-table text-info"></i> Tabel Rekap — 
          <span class="text-info">{{ $kelas->nama ?? 'Semua' }}</span> —
          {{ \Carbon\Carbon::createFromDate($filters['tahun'], $filters['bulan'], 1)->locale('id')->translatedFormat('F Y') }}
        </h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive" style="overflow-x:auto;">
          <table class="table table-hover align-middle mb-0 pivot-table" style="font-size:12px; min-width:900px; color:inherit;">
            <thead style="background:rgba(255,255,255,0.02);">
              <tr>
                <th class="text-center sticky-col start-0" style="width:30px; z-index:4; border-right: 1px solid rgba(255,255,255,0.08);">#</th>
                <th class="sticky-col" style="min-width:160px; left:30px; z-index:4; border-right: 1px solid rgba(255,255,255,0.08);">Nama Siswa</th>
                @foreach ($dates as $date)
                  <th class="text-center px-1" style="width:22px; opacity: 0.6;"
                    title="{{ \Carbon\Carbon::parse($date)->translatedFormat('D, d M') }}">
                    {{ (int) \Carbon\Carbon::parse($date)->format('d') }}
                  </th>
                @endforeach
                <th class="text-center text-success fw-bold" title="Hadir">H</th>
                <th class="text-center text-info fw-bold" title="Sakit">S</th>
                <th class="text-center text-warning fw-bold" title="Izin">I</th>
                <th class="text-center text-danger fw-bold" title="Alpha">A</th>
                <th class="text-center text-secondary fw-bold" title="Terlambat">T</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($siswaList as $siswa)
                @php
                  $pivot = $absensiPivot[$siswa->id] ?? [];
                  $cH = collect($pivot)->filter(fn($v) => $v === 'hadir')->count();
                  $cS = collect($pivot)->filter(fn($v) => $v === 'sakit')->count();
                  $cI = collect($pivot)->filter(fn($v) => $v === 'izin')->count();
                  $cA = collect($pivot)->filter(fn($v) => $v === 'alpha')->count();
                  $cT = collect($pivot)->filter(fn($v) => $v === 'terlambat')->count();
                @endphp
                <tr>
                  <td class="text-center sticky-col start-0 text-white-50" style="z-index:1; border-right: 1px solid rgba(255,255,255,0.08);">{{ ($siswaList->currentPage() - 1) * $siswaList->perPage() + $loop->iteration }}</td>
                  <td class="sticky-col fw-semibold text-white" style="left:30px; z-index:1; border-right: 1px solid rgba(255,255,255,0.08);">{{ $siswa->nama_lengkap }}</td>
                  @foreach ($dates as $date)
                    @php $st = $pivot[$date] ?? null; @endphp
                    <td class="text-center px-0 {{ $st ? 'st-'.$st : '' }}"
                      title="{{ ucfirst($st ?? '') }}" style="border-right: 1px solid rgba(255,255,255,0.05);">
                      {{ $st ? strtoupper(substr($st, 0, 1)) : '' }}
                    </td>
                  @endforeach
                  <td class="text-center fw-bold text-success">{{ $cH }}</td>
                  <td class="text-center fw-bold text-info">{{ $cS }}</td>
                  <td class="text-center fw-bold text-warning">{{ $cI }}</td>
                  <td class="text-center fw-bold text-danger">{{ $cA }}</td>
                  <td class="text-center fw-bold text-secondary">{{ $cT }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @if ($siswaList instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $siswaList->hasPages())
          <div class="card-footer border-top py-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3" style="background:transparent; border-color: rgba(255,255,255,0.08) !important;">
            <div class="text-white-50 small">
              Menampilkan {{ $siswaList->firstItem() }} sampai {{ $siswaList->lastItem() }} dari {{ $siswaList->total() }} siswa
            </div>
            <div>
              {{ $siswaList->links('vendor.pagination.users') }}
            </div>
          </div>
        @endif
      </div>
    </div>
  @else
    <div class="card glass-card">
      <div class="card-body text-center py-5">
        <div class="opacity-50">
           <i class="ti tabler-table-off d-block mb-3" style="font-size:3rem;"></i>
           <p class="mb-0">Pilih kelas untuk melihat tabel rekapitulasi data siswa.</p>
        </div>
      </div>
    </div>
  @endif
@endsection