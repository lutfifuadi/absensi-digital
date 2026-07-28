@extends('layouts/layoutMaster')

@section('title', 'Rekap Pelanggaran Siswa - Guru BK')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">Rekapitulasi Pelanggaran Siswa</h4>
      <p class="text-muted mb-0">Analisis dan pelaporan pelanggaran siswa untuk evaluasi Bimbingan Konseling</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('bk.rekap.export', request()->all()) }}" class="btn btn-success">
        <i class="ti tabler-file-spreadsheet me-1"></i> Export CSV
      </a>
      <a href="{{ route('bk.rekap.pdf', request()->all()) }}" class="btn btn-danger" target="_blank">
        <i class="ti tabler-file-type-pdf me-1"></i> Export PDF
      </a>
    </div>
  </div>

  {{-- Filter Card --}}
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('bk.rekap.index') }}" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Bulan</label>
          <select name="bulan" class="form-select">
            @foreach(range(1, 12) as $m)
              <option value="{{ sprintf('%02d', $m) }}" {{ ($filters['bulan'] ?? '') == sprintf('%02d', $m) ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Tahun</label>
          <select name="tahun" class="form-select">
            @foreach(range(date('Y') - 2, date('Y')) as $y)
              <option value="{{ $y }}" {{ ($filters['tahun'] ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Kelas</label>
          <select name="kelas_id" class="form-select">
            <option value="">-- Semua Kelas --</option>
            @foreach($kelases as $kelas)
              <option value="{{ $kelas->id }}" {{ ($filters['kelas_id'] ?? '') == $kelas->id ? 'selected' : '' }}>
                {{ $kelas->nama_kelas }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary w-100"><i class="ti tabler-filter me-1"></i> Filter Rekap</button>
          <a href="{{ route('bk.rekap.index') }}" class="btn btn-label-secondary"><i class="ti tabler-refresh"></i></a>
        </div>
      </form>
    </div>
  </div>

  <div class="row">
    {{-- Summary Per Kelas --}}
    <div class="col-md-4 mb-4">
      <div class="card h-100">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0 fw-bold">Top Kelas Banyak Pelanggaran</h5>
        </div>
        <ul class="list-group list-group-flush">
          @forelse($rekapKelas as $rk)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong class="text-body">{{ $rk->nama_kelas }}</strong>
                <small class="text-muted d-block">{{ $rk->total_pelanggaran }} kasus kejadian</small>
              </div>
              <span class="badge bg-danger fs-6">{{ $rk->total_poin }} Poin</span>
            </li>
          @empty
            <li class="list-group-item text-center py-4 text-muted">Belum ada data rekap per kelas.</li>
          @endforelse
        </ul>
      </div>
    </div>

    {{-- Detail Pelanggaran List --}}
    <div class="col-md-8 mb-4">
      <div class="card h-100">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0 fw-bold">Rincian Data Kejadian</h5>
        </div>
        <div class="table-responsive text-nowrap">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Siswa & Kelas</th>
                <th>Jenis Pelanggaran</th>
                <th class="text-center">Poin</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pelanggaranList as $p)
                <tr>
                  <td>{{ $p->tanggal_kejadian ? \Carbon\Carbon::parse($p->tanggal_kejadian)->translatedFormat('d M Y') : '-' }}</td>
                  <td>
                    <div>
                      <strong class="text-body">{{ $p->siswa->nama_lengkap ?? '-' }}</strong>
                      <small class="text-muted d-block">{{ $p->siswa->kelas->nama_kelas ?? '-' }}</small>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-label-warning me-1">{{ $p->jenisPelanggaran->kategori->nama_kategori ?? '-' }}</span>
                    <span class="fw-semibold text-body">{{ $p->jenisPelanggaran->nama_jenis ?? '-' }}</span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-danger">+{{ $p->poin_saat_itu }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted">Tidak ada data pelanggaran pada periode ini.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer d-flex justify-content-end">
          {{ $pelanggaranList->withQueryString()->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
