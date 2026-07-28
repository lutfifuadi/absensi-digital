@extends('layouts/layoutMaster')

@section('title', 'Riwayat Pelanggaran Siswa Lintas Kelas')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">Riwayat Pelanggaran Lintas Kelas</h4>
      <p class="text-muted mb-0">Daftar pencatatan pelanggaran seluruh siswa di sekolah</p>
    </div>
    <a href="{{ route('bk.pelanggaran.create') }}" class="btn btn-primary">
      <i class="ti tabler-plus me-1"></i> Catat Pelanggaran Baru
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible mb-4" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- Filter Card --}}
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('bk.pelanggaran.index') }}" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Cari Siswa</label>
          <input type="text" name="search" class="form-control" placeholder="Nama / NIS..." value="{{ $filters['search'] ?? '' }}">
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
        <div class="col-md-3">
          <label class="form-label">Kategori Pelanggaran</label>
          <select name="kategori_id" class="form-select">
            <option value="">-- Semua Kategori --</option>
            @foreach($kategories as $kat)
              <option value="{{ $kat->id }}" {{ ($filters['kategori_id'] ?? '') == $kat->id ? 'selected' : '' }}>
                {{ $kat->nama_kategori }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary w-100"><i class="ti tabler-filter me-1"></i> Filter</button>
          <a href="{{ route('bk.pelanggaran.index') }}" class="btn btn-label-secondary"><i class="ti tabler-refresh"></i></a>
        </div>
      </form>
    </div>
  </div>

  {{-- Table Card --}}
  <div class="card">
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Siswa & Kelas</th>
            <th>Kategori & Jenis</th>
            <th class="text-center">Poin</th>
            <th>Pencatat</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pelanggaran as $item)
            <tr>
              <td>
                <span class="fw-semibold">{{ $item->tanggal_kejadian ? \Carbon\Carbon::parse($item->tanggal_kejadian)->translatedFormat('d M Y') : '-' }}</span>
              </td>
              <td>
                <div>
                  <strong class="text-body">{{ $item->siswa->nama_lengkap ?? '-' }}</strong>
                  <small class="text-muted d-block">NIS: {{ $item->siswa->nis ?? '-' }} &bull; <span class="badge bg-label-info">{{ $item->siswa->kelas->nama_kelas ?? '-' }}</span></small>
                </div>
              </td>
              <td>
                <span class="badge bg-label-warning me-1">{{ $item->jenisPelanggaran->kategori->nama_kategori ?? '-' }}</span>
                <div class="fw-semibold text-body mt-1">{{ $item->jenisPelanggaran->nama_jenis ?? '-' }}</div>
              </td>
              <td class="text-center">
                <span class="badge bg-danger fs-6">+{{ $item->poin_saat_itu }}</span>
              </td>
              <td>
                <small class="text-muted">{{ $item->pencatat->name ?? '-' }}</small>
              </td>
              <td class="text-end">
                <a href="{{ route('bk.pelanggaran.show', $item->id) }}" class="btn btn-sm btn-icon btn-label-primary">
                  <i class="ti tabler-eye"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">Tidak ada data pelanggaran siswa yang sesuai.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer d-flex justify-content-end">
      {{ $pelanggaran->withQueryString()->links() }}
    </div>
  </div>

</div>
@endsection
