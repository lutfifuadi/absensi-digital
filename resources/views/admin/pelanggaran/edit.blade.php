@extends('layouts/layoutMaster')

@section('title', 'Edit Catatan Pelanggaran')

@section('page-style')
  <style>
    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
    }

    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--bs-info) !important;
    }

    .custom-card-header {
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
  </style>
@endsection

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
      <h4 class="fw-bold mb-1"><span class="text-muted fw-light">Kesiswaan /</span> Edit Pelanggaran</h4>
      <p class="text-muted mb-0">Ubah detail catatan pelanggaran untuk siswa.</p>
    </div>

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible mb-4" role="alert">
        <h5 class="alert-heading mb-2 text-white">Terjadi Kesalahan Validasi:</h5>
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="row">
      <div class="col-lg-8">
        <div class="card bg-glass border-light shadow-sm mb-4">
          <div class="card-header custom-card-header text-white">
            <h5 class="card-title mb-0"><i class="ti ti-edit text-warning me-2"></i> Form Edit Pelanggaran</h5>
          </div>
          <div class="card-body pt-4">
            <form action="{{ route('admin.pelanggaran.update', $pelanggaran->id) }}" method="POST" enctype="multipart/form-data">
              @csrf
              @method('PUT')

              <!-- Info Siswa (Read-only) -->
              <div class="mb-4">
                <label class="form-label text-light fw-medium">Siswa Terkait</label>
                <div class="d-flex align-items-center p-3 rounded bg-dark border border-light">
                  <img src="{{ $pelanggaran->siswa->foto ? asset('storage/foto-siswa/' . $pelanggaran->siswa->foto) : asset('assets/img/avatars/1.png') }}" 
                       alt="Avatar" 
                       class="rounded-circle me-3" 
                       width="48" 
                       height="48" 
                       style="object-fit: cover;">
                  <div>
                    <h6 class="text-white mb-0">{{ $pelanggaran->siswa->nama_lengkap }}</h6>
                    <span class="small text-muted">NIS: {{ $pelanggaran->siswa->nis }} | Kelas: {{ $pelanggaran->siswa->kelas?->nama ?: 'Tidak Ada Kelas' }}</span>
                  </div>
                </div>
              </div>

              <!-- Pemilihan Tahun Akademik -->
              <div class="mb-4">
                <label class="form-label text-light fw-medium required">Tahun Akademik</label>
                <select class="form-select" name="tahun_akademik_id" required>
                  @foreach($tahunAkademiks as $ta)
                    <option value="{{ $ta->id }}" {{ $pelanggaran->tahun_akademik_id == $ta->id ? 'selected' : '' }}>
                      {{ $ta->nama }} ({{ ucfirst($ta->semester) }})
                    </option>
                  @endforeach
                </select>
              </div>

              <!-- Pilihan Jenis Pelanggaran -->
              <div class="mb-4">
                <label class="form-label text-light fw-medium required">Jenis Pelanggaran</label>
                <select class="form-select" name="jenis_id" required>
                  @foreach($kategoris as $kat)
                    @if($kat->jenisPelanggaran->count() > 0)
                      <optgroup label="Kategori: {{ $kat->nama }}">
                        @foreach($kat->jenisPelanggaran as $j)
                          <option value="{{ $j->id }}" {{ $pelanggaran->jenis_id == $j->id ? 'selected' : '' }}>
                            {{ $j->nama }} (+{{ $j->bobot_poin }} Poin)
                          </option>
                        @endforeach
                      </optgroup>
                    @endif
                  @endforeach
                </select>
              </div>

              <!-- Tanggal Kejadian & Upload Bukti -->
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label text-light fw-medium required">Tanggal Kejadian</label>
                  <input type="date" class="form-control" name="tanggal_kejadian" value="{{ $pelanggaran->tanggal_kejadian->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label text-light fw-medium">Ganti Foto Bukti</label>
                  <input type="file" class="form-control" name="foto" accept="image/*">
                  <span class="text-muted small mt-1 d-block">Biarkan kosong jika tidak ingin mengubah foto.</span>
                </div>
              </div>

              <!-- Keterangan Naratif -->
              <div class="mb-4">
                <label class="form-label text-light fw-medium required">Keterangan Kronologi / Catatan</label>
                <textarea class="form-control" name="keterangan" rows="4" placeholder="Ketik keterangan detail pelanggaran..." required>{{ $pelanggaran->keterangan }}</textarea>
              </div>

              <div class="border-top border-light pt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.pelanggaran.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-warning">
                  <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Preview Foto Lama -->
      @if($pelanggaran->fotos->count() > 0)
        <div class="col-lg-4">
          <div class="card bg-glass border-light shadow-sm sticky-top" style="top: 80px;">
            <div class="card-header custom-card-header text-white">
              <h5 class="card-title mb-0"><i class="ti ti-photo text-info me-2"></i> Foto Bukti Saat Ini</h5>
            </div>
            <div class="card-body pt-4 text-center">
              <img src="{{ route('admin.pelanggaran.stream-foto', $pelanggaran->fotos->first()->id) }}" 
                   alt="Foto Bukti" 
                   class="img-fluid rounded border border-light shadow-sm"
                   style="max-height: 300px; object-fit: contain;">
              <span class="text-muted small mt-2 d-block">Nama File: {{ $pelanggaran->fotos->first()->nama_file_asli }}</span>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>
@endsection
