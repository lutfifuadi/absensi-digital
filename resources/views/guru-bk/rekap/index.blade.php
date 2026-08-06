@extends('layouts/layoutMaster')

@section('title', 'Rekapitulasi Pelanggaran Siswa — BK')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <style>
    .form-control, .form-select, .btn {
      border-radius: 5px !important;
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
            <i class="ti tabler-chart-pie text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            {{ ($isWaliKelasView ?? false) ? 'Portal Wali Kelas' : (($isPiketView ?? false) ? 'Portal Guru Piket' : 'Bimbingan & Konseling (BK)') }}
          </div>
          <h4 class="das-hero__title text-gradient-gold">
            {{ ($isWaliKelasView ?? false) ? 'Rekapitulasi Pelanggaran Kelas ' . ($assignedClass->nama ?? '') : 'Rekapitulasi Pelanggaran Siswa' }}
          </h4>
          <p class="das-hero__subtitle">
            {{ ($isWaliKelasView ?? false) ? 'Pemantauan rekapitulasi poin dan catatan pelanggaran siswa kelas ' . ($assignedClass->nama ?? '') : (($isPiketView ?? false) ? 'Pemantauan rekapitulasi poin dan catatan pelanggaran harian seluruh kelas.' : 'Analisis dan pelaporan pelanggaran siswa untuk evaluasi Bimbingan Konseling.') }}
          </p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="d-flex gap-2 flex-wrap">
          <a href="{{ route('bk.rekap.export', request()->all()) }}" class="das-btn das-btn--success">
            <i class="ti tabler-file-spreadsheet me-1"></i> Export CSV
          </a>
          <a href="{{ route('bk.rekap.pdf', request()->all()) }}" class="das-btn das-btn--danger" target="_blank">
            <i class="ti tabler-file-type-pdf me-1"></i> Export PDF
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER PANEL --}}
  <div class="das-panel mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__body p-4">
      <form method="GET" action="{{ ($isWaliKelasView ?? false) ? route('wali-kelas.rekap-pelanggaran') : (($isPiketView ?? false) ? route('piket.rekap-pelanggaran') : route('bk.rekap.index')) }}" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fw-semibold small text-body-secondary" for="bulan">Bulan</label>
          <select id="bulan" name="bulan" class="form-select">
            @foreach(range(1, 12) as $m)
              <option value="{{ sprintf('%02d', $m) }}" {{ ($filters['bulan'] ?? '') == sprintf('%02d', $m) ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold small text-body-secondary" for="tahun">Tahun</label>
          <select id="tahun" name="tahun" class="form-select">
            @foreach(range(date('Y') - 2, date('Y')) as $y)
              <option value="{{ $y }}" {{ ($filters['tahun'] ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold small text-body-secondary" for="kelas_id">Filter Kelas</label>
          <select id="kelas_id" name="kelas_id" class="form-select" {{ ($isWaliKelasView ?? false) ? 'disabled' : '' }}>
            @if(!($isWaliKelasView ?? false))
              <option value="">-- Semua Kelas --</option>
            @endif
            @foreach($kelases as $kelas)
              <option value="{{ $kelas->id }}" {{ ($filters['kelas_id'] ?? '') == $kelas->id ? 'selected' : '' }}>
                {{ $kelas->nama ?? $kelas->nama_kelas }}
              </option>
            @endforeach
          </select>
          @if($isWaliKelasView ?? false)
            <input type="hidden" name="kelas_id" value="{{ $assignedClass->id ?? '' }}">
          @endif
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="das-btn das-btn--primary flex-grow-1">
            <i class="ti tabler-filter me-1"></i> Filter
          </button>
          <a href="{{ ($isWaliKelasView ?? false) ? route('wali-kelas.rekap-pelanggaran') : (($isPiketView ?? false) ? route('piket.rekap-pelanggaran') : route('bk.rekap.index')) }}" class="das-btn das-btn--secondary">
            <i class="ti tabler-refresh"></i>
          </a>
        </div>

      </form>
    </div>
  </div>

  <div class="row g-4 mb-4">
    {{-- Summary Per Kelas --}}
    <div class="col-md-4">
      <div class="das-panel h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important; background:transparent;">
          <i class="ti tabler-flame text-danger"></i>
          <h6 class="das-panel__title mb-0">Top Kelas Pelanggaran</h6>
        </div>
        <ul class="list-group list-group-flush">
          @forelse($rekapKelas as $rk)
            <li class="list-group-item d-flex justify-content-between align-items-center py-3" style="background:transparent; border-color:rgba(255,255,255,0.05);">
              <div>
                <strong class="text-body d-block">{{ $rk->nama_kelas }}</strong>
                <small class="text-body-secondary">{{ $rk->total_pelanggaran }} kasus kejadian</small>
              </div>
              <span class="badge bg-danger fs-6 fw-bold">{{ $rk->total_poin }} Poin</span>
            </li>
          @empty
            <li class="list-group-item text-center py-4 text-body-secondary" style="background:transparent; border-color:transparent;">
              <i class="ti tabler-chart-pie-off fs-3 d-block mb-1"></i>
              Belum ada data rekap per kelas.
            </li>
          @endforelse
        </ul>
      </div>
    </div>

    {{-- Detail Pelanggaran List --}}
    <div class="col-md-8">
      <div class="das-panel h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
          style="border-color:rgba(255,255,255,0.08) !important; background:transparent;">
          <div class="d-flex align-items-center gap-2">
            <i class="ti tabler-list-check text-info"></i>
            <h6 class="das-panel__title mb-0">Rincian Data Kejadian</h6>
          </div>
          <span class="badge bg-label-info fw-bold" style="font-size:0.75rem;">Total: {{ number_format($pelanggaranList->total()) }} Kejadian</span>
        </div>
        <div class="table-responsive text-nowrap">
          <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead>
              <tr class="table-light">
                <th>Tanggal</th>
                <th>Siswa &amp; Kelas</th>
                <th>Jenis Pelanggaran</th>
                <th class="text-center">Poin</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pelanggaranList as $p)
                <tr>
                  <td>
                    <span class="fw-semibold text-body">{{ $p->tanggal_kejadian ? \Carbon\Carbon::parse($p->tanggal_kejadian)->translatedFormat('d M Y') : '-' }}</span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-sm flex-shrink-0">
                        <span class="avatar-initial rounded-circle bg-label-danger fw-bold">
                          {{ strtoupper(substr($p->siswa->nama_lengkap ?? 'S', 0, 2)) }}
                        </span>
                      </div>
                      <div>
                        <strong class="text-body d-block">{{ $p->siswa->nama_lengkap ?? '-' }}</strong>
                        <small class="text-body-secondary"><span class="badge bg-label-info fw-semibold">{{ $p->siswa->kelas->nama ?? $p->siswa->kelas->nama_kelas ?? '-' }}</span></small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-label-warning me-1 fw-semibold">{{ $p->jenisPelanggaran->kategori->nama ?? $p->jenisPelanggaran->kategori->nama_kategori ?? '-' }}</span>
                    <div class="fw-semibold text-body mt-1">{{ $p->jenisPelanggaran->nama ?? $p->jenisPelanggaran->nama_jenis ?? '-' }}</div>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-danger fs-6 fw-bold">+{{ $p->poin_saat_itu }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center py-5 text-body-secondary">
                    <i class="ti tabler-file-search d-block mb-2 text-secondary fs-1"></i>
                    Tidak ada data pelanggaran pada periode ini.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer d-flex justify-content-end p-3 border-top" style="border-color:rgba(255,255,255,0.08) !important;">
          {{ $pelanggaranList->withQueryString()->links() }}
        </div>
      </div>
    </div>
  </div>

@endsection

