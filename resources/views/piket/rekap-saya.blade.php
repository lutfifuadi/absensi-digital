@extends('layouts/layoutMaster')

@section('title', 'Rekap Shift Piket Saya')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <style>
    .form-control, .form-select, .btn {
      border-radius: 5px !important;
    }

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
  </style>
@endsection

@section('content')
  {{-- HERO HEADER --}}
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
            Piket / Laporan Shift Saya
          </div>
          <h4 class="das-hero__title text-gradient-gold">Rekap Shift Piket Saya</h4>
          <p class="das-hero__subtitle">
            Log dan bukti transaksi presensi yang dicatat oleh: 
            <span class="badge bg-info bg-opacity-25 text-info ms-1">{{ auth()->user()->name ?? session('piket_user_name', 'Guru Piket') }}</span>
          </p>
        </div>
      </div>
      <div class="das-hero__actions">
        <a href="{{ route('piket.rekap-saya.pdf', request()->query()) }}" target="_blank" class="btn das-btn --info">
          <i class="ti tabler-file-download me-1"></i> Cetak Laporan PDF
        </a>
      </div>
    </div>
  </div>

  {{-- STATS SHIFT SAYA --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card card-grad-primary h-100 text-center">
        <div class="card-body">
          <div class="avatar avatar-md mx-auto mb-2">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="ti tabler-scan fs-4"></i>
            </span>
          </div>
          <h3 class="mb-0 fw-bold text-white">{{ $totalScanSaya }}</h3>
          <small class="text-white-50 opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem;">Total Scan Saya</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card card-grad-success h-100 text-center">
        <div class="card-body">
          <div class="avatar avatar-md mx-auto mb-2">
            <span class="avatar-initial rounded bg-label-success">
              <i class="ti tabler-user-check fs-4"></i>
            </span>
          </div>
          <h3 class="mb-0 fw-bold text-white">{{ $hadirSaya }}</h3>
          <small class="text-white-50 opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem;">Tepat Waktu (Hadir)</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card card-grad-warning h-100 text-center">
        <div class="card-body">
          <div class="avatar avatar-md mx-auto mb-2">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="ti tabler-clock fs-4"></i>
            </span>
          </div>
          <h3 class="mb-0 fw-bold text-white">{{ $terlambatSaya }}</h3>
          <small class="text-white-50 opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem;">Terlambat</small>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card card-grad-info h-100 text-center">
        <div class="card-body">
          <div class="avatar avatar-md mx-auto mb-2">
            <span class="avatar-initial rounded bg-label-info">
              <i class="ti tabler-door-exit fs-4"></i>
            </span>
          </div>
          <h3 class="mb-0 fw-bold text-white">{{ $pulangCepatSaya }}</h3>
          <small class="text-white-50 opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem;">Scan Pulang</small>
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER PANEL --}}
  <div class="das-panel mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__head">
      <h5 class="das-panel__title mb-0 fw-bold">
        <i class="ti tabler-filter text-info me-2"></i>Filter Laporan Shift Saya
      </h5>
    </div>
    <div class="das-panel__body py-3">
      <form action="{{ route('piket.rekap-saya') }}" method="GET" class="row g-3">
        <div class="col-12 col-md-3">
          <label class="form-label text-white-50 small fw-bold">Tanggal Shift</label>
          <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control bg-dark text-white border-secondary">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label text-white-50 small fw-bold">Kelas</label>
          <select name="kelas_id" class="form-select bg-dark text-white border-secondary">
            <option value="">-- Semua Kelas --</option>
            @foreach($kelasOptions as $k)
              <option value="{{ $k->id }}" {{ (string)$kelasId === (string)$k->id ? 'selected' : '' }}>
                {{ $k->nama }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label text-white-50 small fw-bold">Status Kehadiran</label>
          <select name="status" class="form-select bg-dark text-white border-secondary">
            <option value="">-- Semua Status --</option>
            <option value="hadir" {{ $status === 'hadir' ? 'selected' : '' }}>Hadir</option>
            <option value="terlambat" {{ $status === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
            <option value="sakit" {{ $status === 'sakit' ? 'selected' : '' }}>Sakit</option>
            <option value="izin" {{ $status === 'izin' ? 'selected' : '' }}>Izin</option>
            <option value="alpha" {{ $status === 'alpha' ? 'selected' : '' }}>Alpha</option>
          </select>
        </div>
        <div class="col-12 col-md-3 d-flex align-items-end gap-2">
          <button type="submit" class="btn das-btn --primary w-100">
            <i class="ti tabler-search me-1"></i> Tampilkan
          </button>
          <a href="{{ route('piket.rekap-saya') }}" class="btn das-btn --secondary" title="Reset Filter">
            <i class="ti tabler-refresh"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- TABEL RIWAYAT SCAN SAYA --}}
  <div class="das-panel">
    <div class="das-panel__head d-flex align-items-center justify-content-between">
      <div class="das-panel__title">
        <i class="ti tabler-list-check text-info me-2"></i>Rincian Transaksi Scan Guru Piket
      </div>
      <span class="badge bg-secondary bg-opacity-25 text-white-50">Total: {{ $logs->total() }} Data</span>
    </div>
    <div class="das-panel__body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="color:inherit;">
          <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
            <tr>
              <th class="text-center" style="width: 50px;">No</th>
              <th>Nama Siswa / NISN</th>
              <th>Kelas</th>
              <th class="text-center">Jam Masuk</th>
              <th class="text-center">Jam Pulang</th>
              <th class="text-center">Status</th>
              <th>Metode / Keterangan</th>
              <th>Pencatat</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $index => $row)
              <tr>
                <td class="text-center text-white-50">{{ $logs->firstItem() + $index }}</td>
                <td>
                  <div class="fw-bold text-white">{{ $row->siswa->nama_lengkap ?? '-' }}</div>
                  <small class="text-white-50 fs-xs">NISN: {{ $row->siswa->nisn ?? '-' }}</small>
                </td>
                <td>
                  <span class="badge bg-secondary bg-opacity-25 text-info">{{ $row->siswa->kelas->nama ?? '-' }}</span>
                </td>
                <td class="text-center font-monospace">
                  {{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}
                </td>
                <td class="text-center font-monospace">
                  {{ $row->jam_pulang ? substr($row->jam_pulang, 0, 5) : '-' }}
                </td>
                <td class="text-center">
                  @if($row->status === 'hadir')
                    <span class="badge bg-success bg-opacity-25 text-success">Hadir</span>
                  @elseif($row->status === 'terlambat')
                    <span class="badge bg-warning bg-opacity-25 text-warning">Terlambat</span>
                  @elseif($row->status === 'sakit')
                    <span class="badge bg-info bg-opacity-25 text-info">Sakit</span>
                  @elseif($row->status === 'izin')
                    <span class="badge bg-info bg-opacity-25 text-info">Izin</span>
                  @elseif($row->status === 'alpha')
                    <span class="badge bg-danger bg-opacity-25 text-danger">Alpha</span>
                  @else
                    <span class="badge bg-secondary bg-opacity-25 text-white-50">{{ ucfirst($row->status) }}</span>
                  @endif
                </td>
                <td>
                  <span class="badge bg-dark border border-secondary text-white-50 small me-1">
                    {{ strtoupper($row->metode ?? 'QR') }}
                  </span>
                  <small class="text-white-50">{{ $row->keterangan ?? '-' }}</small>
                </td>
                <td>
                  <small class="text-info fw-bold">
                    <i class="ti tabler-user-check me-1"></i>
                    {{ $row->pencatat?->guru?->nama_lengkap ?? $row->pencatat?->name ?? auth()->user()->name }}
                  </small>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-5 text-white-50">
                  <i class="ti tabler-info-circle fs-2 d-block mb-2 text-muted"></i>
                  Belum ada transaksi scan presensi yang dicatat oleh akun Anda pada tanggal ini.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($logs->hasPages())
        <div class="p-3 border-top border-white border-opacity-10 d-flex justify-content-between align-items-center">
          <small class="text-white-50">
            Menampilkan {{ $logs->firstItem() }} hingga {{ $logs->lastItem() }} dari {{ $logs->total() }} data
          </small>
          <div>
            {{ $logs->links() }}
          </div>
        </div>
      @endif
    </div>
  </div>
@endsection
