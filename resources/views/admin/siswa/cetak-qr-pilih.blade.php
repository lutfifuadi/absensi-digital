@extends('layouts/layoutMaster')
@section('title', 'Generate QR Massal Siswa')

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
      <span class="text-muted fw-light">Siswa /</span> Generate QR Massal
    </h4>

    <div class="card" style="max-width:480px;">
      <div class="card-body">
        <p class="text-muted small mb-3">Pilih kelas tertentu atau unduh kartu QR semua siswa aktif sekaligus.</p>
        <form method="GET" action="{{ route('admin.siswa.cetak-qr') }}">
          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Kelas</label>
            <select class="form-select" name="kelas_id" required>
              <option value="">-- Pilih Kelas --</option>
              <option value="semua">🗂 Semua Kelas (Aktif)</option>
              @foreach ($kelasOptions as $k)
                <option value="{{ $k->id }}">{{ $k->nama }}</option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="ti tabler-download me-1"></i>Download PDF Kartu QR
          </button>
        </form>
      </div>
    </div>
  </div>
@endsection
