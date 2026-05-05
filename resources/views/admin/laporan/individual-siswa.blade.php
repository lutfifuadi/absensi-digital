@extends('layouts/layoutMaster')

@section('title', 'Laporan Individual Siswa')

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
      <span class="text-muted fw-light">Laporan /</span> Individual Siswa
    </h4>

    {{-- Filter --}}
    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" class="row gy-2 gx-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Bulan</label>
            <input type="number" name="bulan" class="form-control" min="1" max="12"
              value="{{ $bulan }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Tahun</label>
            <input type="number" name="tahun" class="form-control" value="{{ $tahun }}">
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
          </div>
        </form>
      </div>
    </div>

    {{-- Info Siswa --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Informasi Siswa</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-borderless mb-0">
              <tr>
                <th width="140">Nama</th>
                <td>{{ $siswa->nama_lengkap }}</td>
              </tr>
              <tr>
                <th>NIS</th>
                <td>{{ $siswa->nis }}</td>
              </tr>
              <tr>
                <th>Kelas</th>
                <td>{{ $siswa->kelas->nama ?? '-' }}</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            {{-- Summary --}}
            <div class="row gy-2">
              @foreach ([['label' => 'Hadir', 'val' => $summary['hadir'], 'color' => 'success'], ['label' => 'Sakit', 'val' => $summary['sakit'], 'color' => 'info'], ['label' => 'Izin', 'val' => $summary['izin'], 'color' => 'warning'], ['label' => 'Alpha', 'val' => $summary['alpha'], 'color' => 'danger'], ['label' => 'Terlambat', 'val' => $summary['terlambat'], 'color' => 'secondary']] as $s)
                <div class="col-4 col-md-4">
                  <div class="card text-center h-100 border">
                    <div class="card-body py-2">
                      <h5 class="mb-0 text-{{ $s['color'] }}">{{ $s['val'] }}</h5>
                      <small class="text-muted">{{ $s['label'] }}</small>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Detail Absensi --}}
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Detail Absensi —
          {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y') }}</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
                <th>Metode</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              @forelse($absensi as $row)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $row->tanggal->translatedFormat('d M Y (D)') }}</td>
                  <td>{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</td>
                  <td>{{ $row->jam_pulang ? substr($row->jam_pulang, 0, 5) : '-' }}</td>
                  <td>
                    <span
                      class="badge bg-label-{{ match ($row->status) {
                          'hadir' => 'success',
                          'sakit' => 'info',
                          'izin' => 'warning',
                          'alpha' => 'danger',
                          'terlambat' => 'secondary',
                          default => 'light',
                      } }}">{{ ucfirst($row->status) }}</span>
                  </td>
                  <td>{{ ucfirst($row->metode) }}</td>
                  <td>{{ $row->keterangan ?? '-' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-4 text-muted">Tidak ada data absensi.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
