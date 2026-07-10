@extends('layouts/layoutMaster')

@section('title', $kelas->exists ? 'Ubah Kelas' : 'Tambah Kelas')

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
              <i class="ti {{ $kelas->exists ? 'tabler-pencil' : 'tabler-plus' }} text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}"
                      class="text-white text-decoration-none">Master Data</a></li>
                  <li class="breadcrumb-item"><a href="{{ route('admin.kelas.index') }}"
                      class="text-white text-decoration-none">Kelas</a></li>
                  <li class="breadcrumb-item active text-white">{{ $kelas->exists ? 'Ubah' : 'Tambah' }}</li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                {{ $kelas->exists ? 'Ubah Kelas' : 'Tambah Kelas' }}
              </h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-7">

      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
          style="border-radius:8px;">
          <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
          <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="card border-0 shadow-sm"
        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
        <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
          <i class="ti tabler-forms text-info"></i>
          <h6 class="card-title mb-0">Formulir Kelas</h6>
        </div>
        <div class="card-body p-4">
          <form action="{{ $kelas->exists ? route('admin.kelas.update', $kelas) : route('admin.kelas.store') }}"
            method="POST">
            @csrf
            @if ($kelas->exists)
              @method('PUT')
            @endif

            <div class="mb-4">
              <label class="form-label fw-semibold small" for="nama">
                <i class="ti tabler-door me-1 text-info"></i> Nama Kelas
              </label>
              <input id="nama" name="nama" type="text" class="form-control @error('nama') is-invalid @enderror"
                placeholder="Contoh: X IPA 1" value="{{ old('nama', $kelas->nama) }}" required>
              @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="tingkat">
                  <i class="ti tabler-stairs me-1 text-info"></i> Tingkat
                </label>
                <select id="tingkat" name="tingkat" class="form-select @error('tingkat') is-invalid @enderror" required>
                  <option value="">Pilih tingkat</option>
                  @foreach(\App\Helpers\JenjangHelper::getTingkatOptions() as $tingkat)
                    <option value="{{ $tingkat }}" {{ old('tingkat', $kelas->tingkat) === $tingkat ? 'selected' : '' }}>{{ $tingkat }}</option>
                  @endforeach
                </select>
                @error('tingkat')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-8">
                <label class="form-label fw-semibold small" for="jurusan">
                  <i class="ti tabler-books me-1 text-info"></i> Jurusan
                </label>
                <input id="jurusan" name="jurusan" type="text"
                  class="form-control @error('jurusan') is-invalid @enderror" placeholder="Contoh: IPA"
                  value="{{ old('jurusan', $kelas->jurusan) }}" required>
                @error('jurusan')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold small" for="tahun_akademik_id">
                <i class="ti tabler-calendar-stats me-1 text-info"></i> Tahun Akademik
              </label>
              <select id="tahun_akademik_id" name="tahun_akademik_id"
                class="form-select @error('tahun_akademik_id') is-invalid @enderror" required>
                <option value="">Pilih tahun akademik</option>
                @foreach ($tahunAkademikOptions as $tahun)
                  <option value="{{ $tahun->id }}"
                    {{ old('tahun_akademik_id', $kelas->tahun_akademik_id) == $tahun->id ? 'selected' : '' }}>
                    {{ $tahun->nama }} — {{ ucfirst($tahun->semester) }}
                    @if ($tahun->is_aktif)
                      ✓ Aktif
                    @endif
                  </option>
                @endforeach
              </select>
              @error('tahun_akademik_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold small" for="wali_kelas_id">
                <i class="ti tabler-user-check me-1 text-info"></i> Wali Kelas
                <span class="text-white-50 fw-normal">(opsional)</span>
              </label>
              <select id="wali_kelas_id" name="wali_kelas_id"
                class="form-select @error('wali_kelas_id') is-invalid @enderror">
                <option value="">— Tidak ada wali kelas —</option>
                @foreach ($guruOptions as $guru)
                  <option value="{{ $guru->id }}"
                    {{ old('wali_kelas_id', $kelas->wali_kelas_id) == $guru->id ? 'selected' : '' }}>
                    {{ $guru->nama_lengkap }}{{ $guru->nip ? ' (' . $guru->nip . ')' : '' }}
                  </option>
                @endforeach
              </select>
              <div class="form-text text-white-50 small">Daftar hanya menampilkan guru aktif dengan role <strong>wali_kelas</strong>.</div>
              @error('wali_kelas_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <hr class="my-4 border-secondary opacity-25">
            <h6 class="mb-3 text-info fw-semibold"><i class="ti tabler-settings-automation me-2"></i>Pengaturan Absensi Khusus</h6>

            <div class="mb-4">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="is_aktif_absensi" name="is_aktif_absensi" value="1"
                  {{ old('is_aktif_absensi', $kelas->exists ? $kelas->is_aktif_absensi : true) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="is_aktif_absensi">Sistem Absensi Aktif</label>
              </div>
              <small class="text-white-50 d-block mt-1">Jika dimatikan, siswa di kelas ini tidak akan ditandai Alpha meskipun tidak absen (Cocok untuk Kelas XII pasca ujian).</small>
            </div>

            <div class="mb-4">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="kustomisasi_jam" name="kustomisasi_jam" value="1"
                  {{ old('kustomisasi_jam', $kelas->kustomisasi_jam) ? 'checked' : '' }} onchange="toggleJamKhusus()">
                <label class="form-check-label fw-semibold" for="kustomisasi_jam">Gunakan Jam Masuk/Pulang Khusus</label>
              </div>
              <small class="text-white-50 d-block mt-1 mb-3">Jika dimatikan, kelas ini akan mengikuti jam masuk global dari menu Pengaturan.</small>

              <div class="row g-3" id="jam_khusus_container" style="{{ old('kustomisasi_jam', $kelas->kustomisasi_jam) ? '' : 'display:none;' }}">
                <div class="col-md-6">
                  <label class="form-label small" for="jam_masuk">Jam Masuk</label>
                  <input type="time" class="form-control bg-dark border-secondary text-white" id="jam_masuk" name="jam_masuk" 
                    value="{{ old('jam_masuk', $kelas->jam_masuk ? \Carbon\Carbon::parse($kelas->jam_masuk)->format('H:i') : '') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label small" for="jam_pulang">Jam Pulang</label>
                  <input type="time" class="form-control bg-dark border-secondary text-white" id="jam_pulang" name="jam_pulang" 
                    value="{{ old('jam_pulang', $kelas->jam_pulang ? \Carbon\Carbon::parse($kelas->jam_pulang)->format('H:i') : '') }}">
                </div>
              </div>
            </div>

            <div class="d-flex align-items-center gap-3 pt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ $kelas->exists ? 'Perbarui' : 'Simpan' }}
              </button>
              <a href="{{ route('admin.kelas.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

@endsection

@push('scripts')
<script>
  function toggleJamKhusus() {
    const isChecked = document.getElementById('kustomisasi_jam').checked;
    const container = document.getElementById('jam_khusus_container');
    if (isChecked) {
      container.style.display = 'flex';
    } else {
      container.style.display = 'none';
      document.getElementById('jam_masuk').value = '';
      document.getElementById('jam_pulang').value = '';
    }
  }
</script>
@endpush
