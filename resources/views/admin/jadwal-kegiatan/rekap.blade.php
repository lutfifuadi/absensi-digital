@extends('layouts/layoutMaster')

@section('title', 'Rekap Kehadiran Jadwal Berulang - ' . $jadwalKegiatan->nama_kegiatan)

@section('page-style')
<style>
  :root {
    --das-primary: #7367f0;
    --das-success: #28c76f;
    --das-warning: #ff9f43;
    --das-danger: #ea5455;
    --das-surface: rgba(15, 23, 42, 0.4);
    --das-border: rgba(255, 255, 255, 0.08);
  }
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: 6px; padding: 1.25rem; backdrop-filter: blur(6px); margin-bottom: 1.5rem; }
  .stat-card { background: rgba(255,255,255,0.03); border: 1px solid var(--das-border); border-radius: 6px; padding: 1.25rem; }
  .das-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
  .das-table th { padding: .75rem 1rem; color: rgba(255,255,255,.5); font-weight: 600; text-transform: uppercase; font-size: .7rem; letter-spacing: .5px; border-bottom: 1px solid var(--das-border); text-align: left; }
  .das-table td { padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); vertical-align: middle; }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="fw-bold mb-1 text-white">
        <i class="ti tabler-report-analytics me-2 text-info"></i>Rekap Kehadiran: {{ $jadwalKegiatan->nama_kegiatan }}
      </h4>
      <p class="text-muted mb-0">Laporan akumulasi kehadiran siswa per siklus untuk jadwal kegiatan berulang (PRD-005).</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.jadwal-kegiatan.index') }}" class="btn btn-outline-secondary">
        <i class="ti tabler-arrow-left me-1"></i> Kembali ke Daftar
      </a>
    </div>
  </div>

  <!-- FILTER PERIODE -->
  <div class="das-panel mb-4">
    <form action="{{ route('admin.jadwal-kegiatan.rekap', $jadwalKegiatan->id) }}" method="GET" class="row g-3 align-items-center">
      <div class="col-md-3">
        <label class="form-label fw-semibold">Bulan</label>
        <select name="bulan" class="form-select">
          @for($m = 1; $m <= 12; $m++)
            <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == sprintf('%02d', $m) ? 'selected' : '' }}>
              {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
            </option>
          @endfor
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Tahun</label>
        <select name="tahun" class="form-select">
          @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>

      <div class="col-md-3 d-flex align-items-end gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="ti tabler-filter me-1"></i> Filter Laporan</button>
        <a href="{{ route('admin.jadwal-kegiatan.rekap', $jadwalKegiatan->id) }}" class="btn btn-label-secondary">Reset</a>
      </div>
    </form>
  </div>

  <!-- STATS OVERVIEW -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="stat-card">
        <div class="text-muted fs-7 font-uppercase fw-semibold mb-1">Total Sesi Terlaksana</div>
        <div class="fs-2 fw-bold text-info">{{ $totalSesi }} Sesi</div>
        <div class="fs-7 text-muted">Periode {{ \Carbon\Carbon::create(null, (int)$bulan, 1)->translatedFormat('F') }} {{ $tahun }}</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card">
        <div class="text-muted fs-7 font-uppercase fw-semibold mb-1">Target Siswa Terdaftar</div>
        <div class="fs-2 fw-bold text-primary">{{ count($rekapSiswa) }} Siswa</div>
        <div class="fs-7 text-muted">Berdasarkan filter target kegiatan</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card">
        @php
          $avgPct = count($rekapSiswa) > 0 ? round(array_sum(array_column($rekapSiswa, 'persentase')) / count($rekapSiswa), 1) : 0;
        @endphp
        <div class="text-muted fs-7 font-uppercase fw-semibold mb-1">Rata-Rata Kehadiran</div>
        <div class="fs-2 fw-bold text-success">{{ $avgPct }}%</div>
        <div class="fs-7 text-muted">Akumulasi seluruh peserta target</div>
      </div>
    </div>
  </div>

  <!-- TABLE REKAP -->
  <div class="das-panel">
    <div class="table-responsive">
      <table class="das-table">
        <thead>
          <tr>
            <th>#</th>
            <th>NIS / NISN</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th class="text-center">Hadir</th>
            <th class="text-center">Izin</th>
            <th class="text-center">Sakit</th>
            <th class="text-center">Alpha</th>
            <th class="text-center">Total Sesi</th>
            <th class="text-center">Persentase</th>
            <th class="text-end">Detail</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rekapSiswa as $idx => $row)
            @php $s = $row['siswa']; @endphp
            <tr>
              <td>{{ $idx + 1 }}</td>
              <td><span class="text-muted fs-7">{{ $s->nis ?: ($s->nisn ?: '-') }}</span></td>
              <td><div class="fw-bold text-white">{{ $s->nama_lengkap }}</div></td>
              <td><span class="badge bg-label-info">{{ $s->kelas?->nama ?? '-' }}</span></td>
              <td class="text-center"><span class="badge bg-label-success">{{ $row['hadir'] }}</span></td>
              <td class="text-center"><span class="badge bg-label-info">{{ $row['izin'] }}</span></td>
              <td class="text-center"><span class="badge bg-label-warning">{{ $row['sakit'] }}</span></td>
              <td class="text-center"><span class="badge bg-label-danger">{{ $row['alpha'] }}</span></td>
              <td class="text-center fw-bold">{{ $row['total_sesi'] }}</td>
              <td class="text-center">
                <span class="fw-bold {{ $row['persentase'] >= 75 ? 'text-success' : ($row['persentase'] >= 50 ? 'text-warning' : 'text-danger') }}">
                  {{ $row['persentase'] }}%
                </span>
              </td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-info py-1 px-2" data-bs-toggle="modal" data-bs-target="#detailModal_{{ $s->id }}">
                  <i class="ti tabler-eye"></i> Detail
                </button>

                <!-- MODAL DETAIL SISWA -->
                <div class="modal fade" id="detailModal_{{ $s->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content bg-dark text-start">
                      <div class="modal-header border-secondary">
                        <h5 class="modal-header-title text-white mb-0">
                          <i class="ti tabler-user me-2 text-info"></i>Detail Kehadiran: {{ $s->nama_lengkap }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="table-responsive">
                          <table class="table table-dark table-striped table-sm fs-7 mb-0">
                            <thead>
                              <tr>
                                <th>Tanggal</th>
                                <th>Status Kehadiran</th>
                                <th>Jam Absen</th>
                              </tr>
                            </thead>
                            <tbody>
                              @forelse($kegiatans as $keg)
                                @php $det = $row['details'][$keg->id] ?? null; @endphp
                                <tr>
                                  <td>{{ $keg->tanggal_pelaksanaan ? $keg->tanggal_pelaksanaan->format('d/m/Y') : '-' }}</td>
                                  <td>
                                    @if($det && $det['status'] === 'hadir')
                                      <span class="badge bg-success">Hadir</span>
                                    @elseif($det && $det['status'] === 'izin')
                                      <span class="badge bg-info">Izin</span>
                                    @elseif($det && $det['status'] === 'sakit')
                                      <span class="badge bg-warning">Sakit</span>
                                    @else
                                      <span class="badge bg-danger">Alpha / Belum Absen</span>
                                    @endif
                                  </td>
                                  <td>{{ $det['jam_absen'] ?? '-' }}</td>
                                </tr>
                              @empty
                                <tr>
                                  <td colspan="3" class="text-center text-muted">Belum ada sesi pada periode ini.</td>
                                </tr>
                              @endforelse
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="text-center py-4 text-muted">Tidak ada data rekap untuk kriteria yang dipilih.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
