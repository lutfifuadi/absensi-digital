@extends('layouts/layoutMaster')

@section('title', 'Scan QR Admin')

@section('content')
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">Scan QR Absensi Admin</h4>
          <p class="card-text">Pilih kategori scan, masukkan QR code yang terbaca, lalu proses absensi untuk siswa, guru, pegawai, atau kegiatan khusus.</p>
        </div>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form action="{{ route('admin.scan-qr.store') }}" method="POST">
        @csrf

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label for="scan_type" class="form-label">Kategori Scan</label>
            <select id="scan_type" name="scan_type" class="form-select">
              <option value="siswa" {{ old('scan_type') === 'siswa' ? 'selected' : '' }}>Siswa</option>
              <option value="guru" {{ old('scan_type') === 'guru' ? 'selected' : '' }}>Guru</option>
              <option value="pegawai" {{ old('scan_type') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
              <option value="kegiatan-khusus" {{ old('scan_type') === 'kegiatan-khusus' ? 'selected' : '' }}>Kegiatan Khusus</option>
            </select>
          </div>

          <div class="col-12 col-md-6 d-none" id="kegiatan-group">
            <label for="kegiatan_id" class="form-label">Pilih Kegiatan Khusus</label>
            <select id="kegiatan_id" name="kegiatan_id" class="form-select">
              <option value="">-- Pilih Kegiatan --</option>
              @foreach($kegiatans as $kegiatan)
                <option value="{{ $kegiatan->id }}" {{ old('kegiatan_id') == $kegiatan->id ? 'selected' : '' }}>{{ $kegiatan->nama }} ({{ $kegiatan->tanggal_pelaksanaan }})</option>
              @endforeach
            </select>
          </div>

          <div class="col-12">
            <label for="qr_code" class="form-label">QR Code</label>
            <input id="qr_code" name="qr_code" type="text" class="form-control" value="{{ old('qr_code') }}" placeholder="Masukkan atau tempel QR code" required />
            <div class="form-text">Gunakan QR code yang valid. Hanya karakter alfanumerik, garis bawah, dan strip yang didukung.</div>
          </div>

          <div class="col-12 d-flex flex-column flex-sm-row align-items-center gap-2">
            <button type="submit" class="btn btn-primary">Proses Scan</button>
            <small class="text-muted mb-0">Pastikan tipe scan sesuai dengan QR sebelum memproses.</small>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const scanType = document.getElementById('scan_type');
      const kegiatanGroup = document.getElementById('kegiatan-group');

      function toggleKegiatanGroup() {
        if (!kegiatanGroup || !scanType) {
          return;
        }
        kegiatanGroup.classList.toggle('d-none', scanType.value !== 'kegiatan-khusus');
      }

      if (scanType) {
        scanType.addEventListener('change', toggleKegiatanGroup);
      }

      toggleKegiatanGroup();
    });
  </script>
@endsection
