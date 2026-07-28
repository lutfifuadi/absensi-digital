@extends('layouts/layoutMaster')

@section('title', 'Surat Peringatan (SP) Siswa')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">Daftar Surat Peringatan (SP)</h4>
      <p class="text-muted mb-0">Surat Peringatan formal yang telah diterbitkan oleh Guru BK / Sekolah</p>
    </div>
    <a href="{{ route('bk.sp.create') }}" class="btn btn-warning">
      <i class="ti tabler-file-certificate me-1"></i> Terbitkan SP Baru
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible mb-4">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Filter Card --}}
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('bk.sp.index') }}" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Cari Siswa</label>
          <input type="text" name="search" class="form-control" placeholder="Nama / NIS..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Level SP</label>
          <select name="level_sp" class="form-select">
            <option value="">-- Semua Level --</option>
            <option value="SP1" {{ ($filters['level_sp'] ?? '') == 'SP1' ? 'selected' : '' }}>SP1</option>
            <option value="SP2" {{ ($filters['level_sp'] ?? '') == 'SP2' ? 'selected' : '' }}>SP2</option>
            <option value="SP3" {{ ($filters['level_sp'] ?? '') == 'SP3' ? 'selected' : '' }}>SP3</option>
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
          <button type="submit" class="btn btn-primary w-100"><i class="ti tabler-filter me-1"></i> Filter</button>
          <a href="{{ route('bk.sp.index') }}" class="btn btn-label-secondary"><i class="ti tabler-refresh"></i></a>
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
            <th>Tingkat SP</th>
            <th>Siswa & Kelas</th>
            <th>Total Poin Saat SP</th>
            <th>Tanggal Penerbitan</th>
            <th>Penerbit</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($spList as $sp)
            <tr>
              <td>
                <span class="badge bg-warning text-dark fs-6">{{ $sp->level_sp }}</span>
              </td>
              <td>
                <div>
                  <strong class="text-body">{{ $sp->siswa->nama_lengkap ?? '-' }}</strong>
                  <small class="text-muted d-block">NIS: {{ $sp->siswa->nis ?? '-' }} &bull; <span class="badge bg-label-info">{{ $sp->siswa->kelas->nama_kelas ?? '-' }}</span></small>
                </div>
              </td>
              <td>
                <span class="badge bg-danger fs-6">{{ $sp->total_poin_saat_sp }} Poin</span>
              </td>
              <td>
                {{ $sp->tanggal_sp ? \Carbon\Carbon::parse($sp->tanggal_sp)->translatedFormat('d M Y') : '-' }}
              </td>
              <td>
                <small class="text-muted">{{ $sp->penerbit->name ?? 'Sistem / Auto' }}</small>
              </td>
              <td>
                <small class="text-truncate d-inline-block" style="max-width: 250px;">{{ $sp->catatan_tambahan ?? '-' }}</small>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">Belum ada Surat Peringatan (SP) yang diterbitkan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer d-flex justify-content-end">
      {{ $spList->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
