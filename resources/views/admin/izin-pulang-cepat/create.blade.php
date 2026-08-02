@extends('layouts/layoutMaster')

@section('title', 'Buat Izin Pulang Cepat')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/select2/select2.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/select2/select2.js'
  ])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Admin / Izin Pulang Cepat /</span> Buat Pengajuan
</h4>

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Form Permohonan Izin Pulang Cepat</h5>
        <a href="{{ route('admin.izin-pulang-cepat.index') }}" class="btn btn-label-secondary">
          <i class="ti ti-arrow-left me-1"></i> Kembali
        </a>
      </div>

      <div class="card-body">
        @if($errors->any())
          <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        <form action="{{ route('admin.izin-pulang-cepat.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label required">Kategori Pengaju</label>
              <select name="kategori" id="kategoriSelect" class="form-select @error('kategori') is-invalid @enderror" required>
                <option value="siswa" {{ old('kategori') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                <option value="guru" {{ old('kategori') == 'guru' ? 'selected' : '' }}>Guru</option>
                <option value="staff" {{ old('kategori') == 'staff' ? 'selected' : '' }}>Staff TU</option>
              </select>
              @error('kategori')
                <div class="invalid-feedback">{{ $message }}</div>
              @error('kategori')
            </div>

            <div class="col-md-6">
              <label class="form-label required">Pilih Subjek Pengaju</label>
              
              <div id="selectSiswaWrapper">
                <select name="reference_id_siswa" id="selectSiswa" class="select2 form-select">
                  <option value="">-- Pilih Siswa --</option>
                  @foreach($siswaOptions as $siswa)
                    <option value="{{ $siswa->id }}" {{ old('reference_id') == $siswa->id ? 'selected' : '' }}>
                      {{ $siswa->nama_lengkap }} ({{ $siswa->nisn ?? 'No NISN' }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div id="selectGuruWrapper" style="display: none;">
                <select name="reference_id_guru" id="selectGuru" class="select2 form-select" disabled>
                  <option value="">-- Pilih Guru --</option>
                  @foreach($guruOptions as $guru)
                    <option value="{{ $guru->id }}" {{ old('reference_id') == $guru->id ? 'selected' : '' }}>
                      {{ $guru->nama_lengkap }} ({{ $guru->nip ?? 'No NIP' }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div id="selectStaffWrapper" style="display: none;">
                <select name="reference_id_staff" id="selectStaff" class="select2 form-select" disabled>
                  <option value="">-- Pilih Staff TU --</option>
                  @foreach($staffOptions as $staff)
                    <option value="{{ $staff->id }}" {{ old('reference_id') == $staff->id ? 'selected' : '' }}>
                      {{ $staff->nama_lengkap }} ({{ $staff->nip ?? 'No NIP' }})
                    </option>
                  @endforeach
                </select>
              </div>

              <input type="hidden" name="reference_id" id="realReferenceId" value="{{ old('reference_id') }}">
              @error('reference_id')
                <div class="text-danger fs-tiny mt-1">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label required">Tanggal Kepulangan</label>
              <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', date('Y-m-d')) }}" required>
              @error('tanggal')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label required">Jam Rencana Keluar</label>
              <input type="time" name="jam_rencana_keluar" class="form-control @error('jam_rencana_keluar') is-invalid @enderror" value="{{ old('jam_rencana_keluar', date('H:i')) }}" required>
              @error('jam_rencana_keluar')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label required">Kategori Alasan</label>
              <select name="jenis_alasan" class="form-select @error('jenis_alasan') is-invalid @enderror" required>
                <option value="sakit" {{ old('jenis_alasan') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                <option value="urusan_keluarga" {{ old('jenis_alasan') == 'urusan_keluarga' ? 'selected' : '' }}>Urusan Keluarga</option>
                <option value="dinas_luar" {{ old('jenis_alasan') == 'dinas_luar' ? 'selected' : '' }}>Dinas Luar</option>
                <option value="dispensasi" {{ old('jenis_alasan') == 'dispensasi' ? 'selected' : '' }}>Dispensasi</option>
                <option value="lainnya" {{ old('jenis_alasan') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
              </select>
              @error('jenis_alasan')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Upload Lampiran (Opsional)</label>
              <input type="file" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
              <small class="text-muted">Format: JPG, PNG, PDF (Maks. 2MB)</small>
              @error('lampiran')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-12">
              <label class="form-label required">Detail Alasan</label>
              <textarea name="alasan" class="form-control @error('alasan') is-invalid @enderror" rows="3" placeholder="Jelaskan alasan izin pulang cepat secara detail..." required>{{ old('alasan') }}</textarea>
              @error('alasan')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <hr class="my-3">
            <h6 class="fw-semibold">Informasi Penjemput (Khusus Siswa / Opsional)</h6>

            <div class="col-md-6">
              <label class="form-label">Nama Penjemput</label>
              <input type="text" name="nama_penjemput" class="form-control @error('nama_penjemput') is-invalid @enderror" placeholder="Contoh: Ayah / Ibu / Wali" value="{{ old('nama_penjemput') }}">
              @error('nama_penjemput')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">No. HP Penjemput</label>
              <input type="text" name="no_hp_penjemput" class="form-control @error('no_hp_penjemput') is-invalid @enderror" placeholder="Contoh: 08123456789" value="{{ old('no_hp_penjemput') }}">
              @error('no_hp_penjemput')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
              <a href="{{ route('admin.izin-pulang-cepat.index') }}" class="btn btn-label-secondary">Batal</a>
              <button type="submit" class="btn btn-primary">Simpan & Ajukan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const kategoriSelect = document.getElementById('kategoriSelect');
    const realRefInput = document.getElementById('realReferenceId');

    const selectSiswaWrapper = document.getElementById('selectSiswaWrapper');
    const selectGuruWrapper = document.getElementById('selectGuruWrapper');
    const selectStaffWrapper = document.getElementById('selectStaffWrapper');

    const selectSiswa = document.getElementById('selectSiswa');
    const selectGuru = document.getElementById('selectGuru');
    const selectStaff = document.getElementById('selectStaff');

    function updateVisibility() {
      const val = kategoriSelect.value;

      selectSiswaWrapper.style.display = 'none';
      selectGuruWrapper.style.display = 'none';
      selectStaffWrapper.style.display = 'none';

      selectSiswa.disabled = true;
      selectGuru.disabled = true;
      selectStaff.disabled = true;

      if (val === 'siswa') {
        selectSiswaWrapper.style.display = 'block';
        selectSiswa.disabled = false;
        realRefInput.value = selectSiswa.value;
      } else if (val === 'guru') {
        selectGuruWrapper.style.display = 'block';
        selectGuru.disabled = false;
        realRefInput.value = selectGuru.value;
      } else if (val === 'staff') {
        selectStaffWrapper.style.display = 'block';
        selectStaff.disabled = false;
        realRefInput.value = selectStaff.value;
      }
    }

    kategoriSelect.addEventListener('change', updateVisibility);

    selectSiswa.addEventListener('change', function() { realRefInput.value = this.value; });
    selectGuru.addEventListener('change', function() { realRefInput.value = this.value; });
    selectStaff.addEventListener('change', function() { realRefInput.value = this.value; });

    updateVisibility();
  });
</script>
@endsection
