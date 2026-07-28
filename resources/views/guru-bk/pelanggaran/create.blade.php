@extends('layouts/layoutMaster')

@section('title', 'Input Pelanggaran Lintas Kelas')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="mb-4">
    <a href="{{ route('bk.pelanggaran.index') }}" class="btn btn-label-secondary btn-sm mb-2">
      <i class="ti tabler-arrow-left me-1"></i> Kembali
    </a>
    <h4 class="fw-bold mb-1">Catat Pelanggaran Siswa (Lintas Kelas)</h4>
    <p class="text-muted mb-0">Guru BK dapat memilih siswa dari kelas manapun untuk dicatat pelanggarannya</p>
  </div>

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible mb-4">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card">
    <div class="card-body p-4">
      <form action="{{ route('bk.pelanggaran.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
          {{-- Pilih Siswa --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Pilih Siswa (Lintas Kelas) <span class="text-danger">*</span></label>
            <select name="siswa_id" class="form-select select2" required>
              <option value="">-- Cari Nama Siswa / NIS / Kelas --</option>
              @foreach($siswas as $siswa)
                <option value="{{ $siswa->id }}" {{ (old('siswa_id', $selectedSiswaId) == $siswa->id) ? 'selected' : '' }}>
                  {{ $siswa->nama_lengkap }} (NIS: {{ $siswa->nis ?? '-' }}) - Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Jenis Pelanggaran --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Jenis Pelanggaran <span class="text-danger">*</span></label>
            <select name="jenis_id" class="form-select select2" required>
              <option value="">-- Pilih Jenis Pelanggaran --</option>
              @foreach($jenisList as $jenis)
                <option value="{{ $jenis->id }}" {{ old('jenis_id') == $jenis->id ? 'selected' : '' }}>
                  [{{ $jenis->kategori->nama_kategori ?? 'Kategori' }}] {{ $jenis->nama_jenis }} (+{{ $jenis->poin }} Poin)
                </option>
              @endforeach
            </select>
          </div>

          {{-- Tanggal Kejadian --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Tanggal Kejadian <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_kejadian" class="form-control" value="{{ old('tanggal_kejadian', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
          </div>

          {{-- Upload Bukti Foto --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Upload Bukti Foto (Opsional)</label>
            <input type="file" name="bukti_foto" class="form-control" accept="image/png, image/jpeg, image/jpg">
            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB.</small>
          </div>

          {{-- Keterangan Detail --}}
          <div class="col-12">
            <label class="form-label fw-semibold">Keterangan / Catatan Kejadian</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Tuliskan kronologi singkat atau catatan khusus dari Guru BK...">{{ old('keterangan') }}</textarea>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('bk.pelanggaran.index') }}" class="btn btn-label-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Simpan Pelanggaran</button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection
