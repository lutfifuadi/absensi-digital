@extends('layouts/layoutMaster')

@section('title', 'Rekap Absensi Kehadiran Saya')

@section('page-style')
<style>
  .kpi-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    padding: 1.25rem;
    height: 100%;
    transition: transform .2s ease, box-shadow .2s ease;
  }
  .kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
  }
  .kpi-label {
    font-size: 0.725rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.5);
    margin-bottom: 0.4rem;
  }
  .kpi-value {
    font-size: 1.85rem;
    font-weight: 800;
    line-height: 1;
    color: #fff;
  }
  .kpi-sub {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.45);
    margin-top: 0.35rem;
  }
  .kpi-icon {
    width: 40px;
    height: 40px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
  }
  .rekap-table th {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.45);
    border-bottom: 1px solid rgba(255,255,255,0.08) !important;
    background: transparent !important;
    padding: 0.75rem 1rem;
  }
  .rekap-table td {
    color: rgba(255,255,255,0.85);
    border-bottom: 1px solid rgba(255,255,255,0.05) !important;
    padding: 0.85rem 1rem;
    vertical-align: middle;
  }
  .rekap-table tbody tr:hover td {
    background: rgba(255,255,255,0.03);
  }
</style>
@endsection

@section('content')

{{-- HERO HEADER --}}
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div class="das-hero__identity d-flex align-items-center gap-3">
      <div class="das-hero__logo-wrapper flex-shrink-0">
        <div class="das-hero__logo-placeholder bg-warning text-dark fw-bold">
          <i class="ti tabler-user-check fs-4"></i>
        </div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Rekapan Presensi Harian Guru
        </div>
        <h4 class="das-hero__title text-gradient-gold mb-0">{{ $guru->nama_lengkap }}</h4>
        <p class="das-hero__subtitle text-white-50 mb-0 small">
          NIP: {{ $guru->nip ?? '-' }} &bull; Status: <span class="badge bg-label-warning px-2 py-0 fs-tiny">{{ strtoupper(str_replace('_', ' ', $guru->tipe_kepegawaian ?? 'FULL TIME')) }}</span>
        </p>
      </div>
    </div>
    
    <div class="d-flex gap-2">
      <button onclick="window.print()" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1 shadow-xs">
        <i class="ti tabler-printer fs-6"></i> Cetak Rekap
      </button>
    </div>
  </div>
</div>

{{-- PANEL FILTER & PERIODE --}}
<div class="das-panel mb-4">
  <div class="das-panel__body">
    <form method="GET" action="{{ route('guru.rekap-absensi-saya') }}" class="row g-3 align-items-end">
      <div class="col-6 col-md-3 col-lg-2">
        <label class="form-label text-white-50 small fw-semibold">Pilih Bulan</label>
        <select name="bulan" class="form-select form-select-sm" style="background:rgba(15,23,42,.6);color:#fff;border:1px solid rgba(255,255,255,.15);">
          @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
              {{ \Carbon\Carbon::create(2000, $m, 1)->locale('id')->translatedFormat('F') }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-6 col-md-3 col-lg-2">
        <label class="form-label text-white-50 small fw-semibold">Pilih Tahun</label>
        <select name="tahun" class="form-select form-select-sm" style="background:rgba(15,23,42,.6);color:#fff;border:1px solid rgba(255,255,255,.15);">
          @foreach(range(now()->year - 2, now()->year + 1) as $y)
            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-12 col-md-3 col-lg-2">
        <button type="submit" class="btn btn-sm btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-1 shadow-sm">
          <i class="ti tabler-filter fs-6"></i> Tampilkan
        </button>
      </div>

      <div class="col-12 col-md-3 col-lg-6 text-md-end">
        <span class="text-white-50 small">
          <i class="ti tabler-calendar-heart me-1 text-warning"></i> Periode: <strong class="text-white">{{ \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y') }}</strong>
        </span>
      </div>
    </form>
  </div>
</div>

{{-- KPI STATS CARDS --}}
<div class="row g-3 mb-4">
  {{-- Hadir Tepat Waktu --}}
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="kpi-label">Hadir Tepat Waktu</div>
        <div class="kpi-icon" style="background:rgba(40,199,111,.15);color:#28c76f;">
          <i class="ti tabler-circle-check"></i>
        </div>
      </div>
      <div class="kpi-value text-success">{{ number_format($stats['count_hadir']) }}</div>
      <div class="kpi-sub">Hari kerja tepat waktu</div>
    </div>
  </div>

  {{-- Terlambat --}}
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="kpi-label">Terlambat</div>
        <div class="kpi-icon" style="background:rgba(255,159,67,.15);color:#ff9f43;">
          <i class="ti tabler-clock-check"></i>
        </div>
      </div>
      <div class="kpi-value text-warning">{{ number_format($stats['count_terlambat']) }}</div>
      <div class="kpi-sub">Presensi terlambat</div>
    </div>
  </div>

  {{-- Izin & Sakit --}}
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="kpi-label">Izin & Sakit</div>
        <div class="kpi-icon" style="background:rgba(0,207,232,.15);color:#00cfe8;">
          <i class="ti tabler-file-text"></i>
        </div>
      </div>
      <div class="kpi-value text-info">{{ number_format($stats['count_izin_sakit']) }}</div>
      <div class="kpi-sub">Keterangan resmi</div>
    </div>
  </div>

  {{-- Alpha / Tanpa Ket. --}}
  <div class="col-6 col-lg-3">
    <div class="kpi-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="kpi-label">Alpha / Tanpa Ket.</div>
        <div class="kpi-icon" style="background:rgba(234,84,85,.15);color:#ea5455;">
          <i class="ti tabler-alert-circle"></i>
        </div>
      </div>
      <div class="kpi-value text-danger">{{ number_format($stats['count_alpha']) }}</div>
      <div class="kpi-sub">Kehadiran: <strong class="text-white">{{ $stats['persentase_kehadiran'] }}%</strong></div>
    </div>
  </div>
</div>

{{-- TABEL RIWAYAT PRESENSI --}}
<div class="card bg-dark border border-secondary border-opacity-20 shadow-sm">
  <div class="card-header bg-transparent border-bottom border-secondary border-opacity-20 py-3 d-flex align-items-center justify-content-between">
    <h5 class="card-title text-white mb-0 d-flex align-items-center gap-2" style="font-size:1rem;">
      <i class="ti tabler-history text-warning"></i> Riwayat Presensi Masuk & Pulang Harian
    </h5>
    <span class="badge bg-secondary bg-opacity-20 text-white-50">{{ $absensiList->count() }} Data Record</span>
  </div>

  <div class="table-responsive">
    <table class="table rekap-table mb-0">
      <thead>
        <tr>
          <th style="width: 50px;">No</th>
          <th>Tanggal & Hari</th>
          <th class="text-center">Jam Masuk</th>
          <th class="text-center">Jam Pulang</th>
          <th class="text-center">Status</th>
          <th class="text-center">Metode</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($absensiList as $idx => $row)
          @php
            $carbonDate = \Carbon\Carbon::parse($row->tanggal);
            $isWeekend  = $carbonDate->isSunday();
          @endphp
          <tr>
            <td class="text-center text-white-50">{{ $idx + 1 }}</td>
            <td>
              <div class="fw-semibold text-white">{{ $carbonDate->locale('id')->translatedFormat('d F Y') }}</div>
              <div class="small text-white-50">{{ $carbonDate->locale('id')->translatedFormat('l') }}</div>
            </td>

            {{-- Jam Masuk --}}
            <td class="text-center">
              @if($row->jam_masuk)
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2 py-1">
                  <i class="ti tabler-login me-1 fs-tiny"></i> {{ substr($row->jam_masuk, 0, 5) }}
                </span>
              @else
                <span class="text-white-50 small">-</span>
              @endif
            </td>

            {{-- Jam Pulang --}}
            <td class="text-center">
              @if($row->jam_pulang)
                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20 px-2 py-1">
                  <i class="ti tabler-logout me-1 fs-tiny"></i> {{ substr($row->jam_pulang, 0, 5) }}
                </span>
              @else
                <span class="text-white-50 small">-</span>
              @endif
            </td>

            {{-- Status Badge --}}
            <td class="text-center">
              @if($row->status === 'hadir')
                <span class="badge bg-success text-white px-2 py-1">Hadir</span>
              @elseif($row->status === 'terlambat')
                <span class="badge bg-warning text-dark px-2 py-1">Terlambat</span>
              @elseif($row->status === 'izin')
                <span class="badge bg-info text-white px-2 py-1">Izin</span>
              @elseif($row->status === 'sakit')
                <span class="badge bg-secondary text-white px-2 py-1">Sakit</span>
              @elseif($row->status === 'alpha')
                <span class="badge bg-danger text-white px-2 py-1">Alpha</span>
              @else
                <span class="badge bg-secondary text-white px-2 py-1">{{ ucfirst($row->status) }}</span>
              @endif
            </td>

            {{-- Metode --}}
            <td class="text-center">
              <span class="badge bg-secondary bg-opacity-20 text-white-50 px-2 py-1" style="font-size:0.7rem;">
                {{ strtoupper($row->metode ?? 'SYSTEM') }}
              </span>
            </td>

            {{-- Keterangan --}}
            <td class="small text-white-50">
              {{ $row->keterangan ?: '-' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-5 text-white-50">
              <div class="mb-2"><i class="ti tabler-calendar-off fs-1 opacity-25"></i></div>
              <h6>Belum ada data presensi pada bulan ini.</h6>
              <p class="small text-white-50 mb-0">Silakan pilih bulan atau tahun lain pada filter di atas.</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
