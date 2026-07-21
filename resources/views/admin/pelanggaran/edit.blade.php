@extends('layouts/layoutMaster')

@section('title', 'Ubah Catatan Pelanggaran')

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
  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 4px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
              style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
              <i class="ti tabler-pencil text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}"
                      class="text-white text-decoration-none">Master Data</a></li>
                  <li class="breadcrumb-item"><a href="{{ route('admin.pelanggaran.index') }}"
                      class="text-white text-decoration-none">Pelanggaran</a></li>
                  <li class="breadcrumb-item active text-white">Ubah</li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                Ubah Catatan Pelanggaran
              </h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ALERT ERRORS --}}
  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
      style="border-radius:8px; background: rgba(234, 84, 85, 0.15); color: #ea5455;">
      <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
      <div>
        <span class="fw-semibold d-block mb-1">Terjadi Kesalahan Validasi:</span>
        <ul class="mb-0 ps-3 small">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row g-4">
    <!-- Form Edit Utama -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm"
        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
        <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
          <i class="ti tabler-pencil text-warning"></i>
          <h6 class="card-title mb-0 text-white">Form Edit Pelanggaran</h6>
        </div>
        <div class="card-body p-4">
          <form action="{{ route('admin.pelanggaran.update', $pelanggaran->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Info Siswa (Read-only) -->
            <div class="mb-4">
              <label class="form-label fw-semibold small text-white">
                <i class="ti tabler-user me-1 text-info"></i> Siswa Terkait
              </label>
              <div class="d-flex align-items-center p-3 rounded border" style="background:rgba(255,255,255,0.04); border-color:rgba(255,255,255,0.08) !important;">
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
              <label class="form-label fw-semibold small text-white" for="tahun_akademik_id">
                <i class="ti tabler-calendar-stats me-1 text-info"></i> Tahun Akademik <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="tahun_akademik_id" name="tahun_akademik_id" required>
                @foreach($tahunAkademiks as $ta)
                  <option value="{{ $ta->id }}" {{ $pelanggaran->tahun_akademik_id == $ta->id ? 'selected' : '' }}>
                    {{ $ta->nama }} ({{ ucfirst($ta->semester) }})
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Pilihan Jenis Pelanggaran -->
            <div class="mb-4">
              <label class="form-label fw-semibold small text-white" for="jenis_id">
                <i class="ti tabler-alert-triangle me-1 text-info"></i> Jenis Pelanggaran <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="jenis_id" name="jenis_id" required>
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
                <label class="form-label fw-semibold small text-white" for="tanggal_kejadian">
                  <i class="ti tabler-calendar me-1 text-info"></i> Tanggal Kejadian <span class="text-danger">*</span>
                </label>
                <input type="date" id="tanggal_kejadian" class="form-control" name="tanggal_kejadian" value="{{ $pelanggaran->tanggal_kejadian->format('Y-m-d') }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small text-white" for="foto">
                  <i class="ti tabler-camera me-1 text-info"></i> Ganti Foto Bukti
                </label>
                <input type="file" id="foto" class="form-control" name="foto" accept="image/*">
                <span class="text-muted small mt-1 d-block">Biarkan kosong jika tidak ingin mengubah foto.</span>
              </div>
            </div>

            <!-- Keterangan Naratif -->
            <div class="mb-4">
              <label class="form-label fw-semibold small text-white" for="keterangan">
                <i class="ti tabler-file-description me-1 text-info"></i> Keterangan Kronologi / Catatan <span class="text-danger">*</span>
              </label>
              <textarea id="keterangan" class="form-control" name="keterangan" rows="4" placeholder="Ketik keterangan detail pelanggaran..." required>{{ $pelanggaran->keterangan }}</textarea>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('admin.pelanggaran.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn btn-warning fw-semibold px-4 shadow-sm">
                <i class="ti tabler-device-floppy me-1"></i> Simpan Perubahan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Preview Foto Lama -->
    @if($pelanggaran->fotos->count() > 0)
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top"
          style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;top: 80px;">
          <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
            style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
            <i class="ti tabler-photo text-info"></i>
            <h6 class="card-title mb-0 text-white">Foto Bukti Saat Ini</h6>
          </div>
          <div class="card-body p-4 text-center">
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
@endsection
