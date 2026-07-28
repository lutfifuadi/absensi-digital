@extends('layouts/layoutMaster')

@section('title', 'Detail Pelanggaran Siswa')

@section('content')
  <div class="mb-4">
    <a href="{{ route('bk.pelanggaran.index') }}" class="btn btn-label-secondary btn-sm mb-2">
      <i class="ti tabler-arrow-left me-1"></i> Kembali
    </a>
    <h4 class="fw-bold mb-1">Detail Catatan Pelanggaran</h4>
  </div>

  <div class="row">
    <div class="col-md-7">
      <div class="card mb-4">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0">Informasi Pelanggaran</h5>
        </div>
        <div class="card-body pt-4">
          <table class="table table-borderless">
            <tr>
              <td class="fw-semibold text-muted" style="width: 180px;">Nama Siswa</td>
              <td>: <strong>{{ $pelanggaran->siswa->nama_lengkap ?? '-' }}</strong></td>
            </tr>
            <tr>
              <td class="fw-semibold text-muted">NIS / NISN</td>
              <td>: {{ $pelanggaran->siswa->nis ?? '-' }} / {{ $pelanggaran->siswa->nisn ?? '-' }}</td>
            </tr>
            <tr>
              <td class="fw-semibold text-muted">Kelas</td>
              <td>: <span class="badge bg-label-info">{{ $pelanggaran->siswa->kelas->nama_kelas ?? '-' }}</span></td>
            </tr>
            <tr>
              <td class="fw-semibold text-muted">Kategori</td>
              <td>: <span class="badge bg-label-warning">{{ $pelanggaran->jenisPelanggaran->kategori->nama_kategori ?? '-' }}</span></td>
            </tr>
            <tr>
              <td class="fw-semibold text-muted">Jenis Pelanggaran</td>
              <td>: <strong>{{ $pelanggaran->jenisPelanggaran->nama_jenis ?? '-' }}</strong></td>
            </tr>
            <tr>
              <td class="fw-semibold text-muted">Poin Terpengaruh</td>
              <td>: <span class="badge bg-danger fs-6">+{{ $pelanggaran->poin_saat_itu }} Poin</span></td>
            </tr>
            <tr>
              <td class="fw-semibold text-muted">Tanggal Kejadian</td>
              <td>: {{ $pelanggaran->tanggal_kejadian ? \Carbon\Carbon::parse($pelanggaran->tanggal_kejadian)->translatedFormat('l, d F Y') : '-' }}</td>
            </tr>
            <tr>
              <td class="fw-semibold text-muted">Dicatat Oleh</td>
              <td>: {{ $pelanggaran->pencatat->name ?? '-' }}</td>
            </tr>
            <tr>
              <td class="fw-semibold text-muted">Keterangan / Kronologi</td>
              <td>: {{ $pelanggaran->keterangan ?? 'Tidak ada catatan khusus.' }}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card mb-4">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0">Bukti Foto</h5>
        </div>
        <div class="card-body text-center pt-4">
          @if($pelanggaran->fotos && $pelanggaran->fotos->count() > 0)
            @foreach($pelanggaran->fotos as $foto)
              <img src="{{ asset('storage/' . $foto->foto_path) }}" class="img-fluid rounded border mb-2" alt="Bukti Pelanggaran" style="max-height: 300px;">
            @endforeach
          @else
            <div class="py-4 text-muted">
              <i class="ti tabler-photo-off fs-1 d-block mb-2"></i>
              Tidak ada bukti foto diunggah.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
