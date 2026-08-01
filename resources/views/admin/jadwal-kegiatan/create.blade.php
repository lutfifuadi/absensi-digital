@extends('layouts/layoutMaster')

@section('title', 'Tambah Jadwal Kegiatan Berulang')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/select2/select2.scss'
  ])
  <style>
    :root {
      --das-primary: #7367f0;
      --das-surface: rgba(15, 23, 42, 0.4);
      --das-border: rgba(255, 255, 255, 0.08);
    }
    .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: 6px; padding: 1.5rem; backdrop-filter: blur(6px); margin-bottom: 2rem; }
    .das-section-title { font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #a5a2f7; border-bottom: 1px solid var(--das-border); padding-bottom: 0.5rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px; }
  </style>
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/select2/select2.js'
  ])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="fw-bold mb-1 text-white"><i class="ti tabler-calendar-plus me-2 text-primary"></i>Tambah Jadwal Kegiatan Berulang</h4>
      <p class="text-muted mb-0">Definisikan pola kegiatan rutin agar sesi harian ter-generate secara otomatis (PRD-005).</p>
    </div>
    <a href="{{ route('admin.jadwal-kegiatan.index') }}" class="btn btn-outline-secondary">
      <i class="ti tabler-arrow-left me-1"></i> Kembali
    </a>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <form action="{{ route('admin.jadwal-kegiatan.store') }}" method="POST">
    @csrf

    <!-- SECTION 1: INFORMASI UTAMA -->
    <div class="das-panel">
      <div class="das-section-title">
        <i class="ti tabler-info-circle"></i> 1. Informasi Utama Kegiatan
      </div>

      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label fw-semibold">Nama Kegiatan <span class="text-danger">*</span></label>
          <input type="text" name="nama_kegiatan" class="form-control" placeholder="Contoh: Ekstrakurikuler Pramuka, Kebersihan Lingkungan" value="{{ old('nama_kegiatan') }}" required>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Jenis Kegiatan <span class="text-danger">*</span></label>
          <select name="jenis" class="form-select" required>
            <option value="Acara Internal" {{ old('jenis') == 'Acara Internal' ? 'selected' : '' }}>Acara Internal</option>
            <option value="Ekstrakurikuler" {{ old('jenis') == 'Ekstrakurikuler' ? 'selected' : '' }}>Ekstrakurikuler</option>
            <option value="Seminar" {{ old('jenis') == 'Seminar' ? 'selected' : '' }}>Seminar</option>
            <option value="Lomba" {{ old('jenis') == 'Lomba' ? 'selected' : '' }}>Lomba</option>
            <option value="Lainnya" {{ old('jenis') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Tipe Pola Jadwal <span class="text-danger">*</span></label>
          <select name="tipe_jadwal" id="tipeJadwalSelect" class="form-select" required onchange="toggleTipeJadwalUI()">
            <option value="mingguan_1_hari" {{ old('tipe_jadwal', 'mingguan_1_hari') == 'mingguan_1_hari' ? 'selected' : '' }}>Mingguan (1 Hari / Minggu)</option>
            <option value="mingguan_multi_hari" {{ old('tipe_jadwal') == 'mingguan_multi_hari' ? 'selected' : '' }}>Mingguan (Multi-Hari / Minggu)</option>
            <option value="tanggal_kalender" {{ old('tipe_jadwal') == 'tanggal_kalender' ? 'selected' : '' }}>Tanggal Kalender (Per Bulan)</option>
          </select>
        </div>

        <!-- POLA HARI MINGGUAN -->
        <div class="col-md-8" id="polaHariContainer">
          <label class="form-label fw-semibold">Pilih Hari Pelaksanaan <span class="text-danger">*</span></label>
          <div class="d-flex flex-wrap gap-3 p-2 border rounded bg-dark-subtle">
            @php
              $hariList = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'];
              $oldHari = old('hari', []);
            @endphp
            @foreach($hariList as $key => $label)
              <div class="form-check me-2">
                <input class="form-check-input hari-checkbox" type="checkbox" name="hari[]" value="{{ $key }}" id="hari_{{ $key }}" {{ in_array($key, $oldHari) ? 'checked' : '' }}>
                <label class="form-check-label" for="hari_{{ $key }}">{{ $label }}</label>
              </div>
            @endforeach
          </div>
          <small class="text-muted">Untuk "Mingguan 1 Hari", disarankan memilih 1 hari.</small>
        </div>

        <!-- POLA TANGGAL KALENDER -->
        <div class="col-md-8 d-none" id="polaTanggalContainer">
          <label class="form-label fw-semibold">Tanggal Kalender Per Bulan <span class="text-danger">*</span></label>
          <input type="text" name="tanggal_kalender_input" class="form-control" placeholder="Contoh: 5, 20 (pisahkan dengan koma)" value="{{ old('tanggal_kalender_input') }}">
          <small class="text-muted">Masukkan tanggal antara 1 sampai 31 yang dipisahkan koma. Contoh: <code>5, 20</code> berarti setiap tanggal 5 dan 20 per bulan.</small>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Waktu Mulai</label>
          <input type="time" name="waktu_mulai" class="form-control" value="{{ old('waktu_mulai', '14:00') }}">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Waktu Selesai</label>
          <input type="time" name="waktu_selesai" class="form-control" value="{{ old('waktu_selesai', '16:00') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Lokasi Pelaksanaan</label>
          <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Lapangan Utama, Aula Sekolah" value="{{ old('lokasi') }}">
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Keterangan / Catatan</label>
          <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan mengenai jadwal ini...">{{ old('keterangan') }}</textarea>
        </div>
      </div>
    </div>

    <!-- SECTION 2: INTEGRASI EKSTRAKURIKULER & RENTANG WAKTU -->
    <div class="das-panel">
      <div class="das-section-title">
        <i class="ti tabler-link"></i> 2. Integrasi Ekskul & Masa Berlaku
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Tautkan ke Ekstrakurikuler (Opsional)</label>
          <select name="ekskul_id" id="ekskulSelect" class="form-select" onchange="onEkskulChange()">
            <option value="">-- Tidak Terikat Ekskul (Mandiri) --</option>
            @foreach($ekskuls as $e)
              <option value="{{ $e->id }}" {{ old('ekskul_id') == $e->id ? 'selected' : '' }}>{{ $e->nama }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6" id="ekskulJadwalWrapper">
          <label class="form-label fw-semibold">Tautkan ke Jadwal Ekskul Existing</label>
          <select name="ekskul_jadwal_id" id="ekskulJadwalSelect" class="form-select">
            <option value="">-- Buat Waktu / Hari Terpisah --</option>
            @foreach($ekskuls as $e)
              @foreach($e->jadwal as $ej)
                <option value="{{ $ej->id }}" data-ekskul="{{ $e->id }}" {{ old('ekskul_jadwal_id') == $ej->id ? 'selected' : '' }}>
                  {{ $e->nama }} — {{ ucfirst($ej->hari) }} ({{ substr($ej->jam_mulai,0,5) }} - {{ substr($ej->jam_selesai,0,5) }}) @ {{ $ej->lokasi }}
                </option>
              @endforeach
            @endforeach
          </select>
          <small class="text-muted">Jika dipilih, hari, jam, dan lokasi akan otomatis mengikuti data jadwal ekskul.</small>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Tanggal Mulai Berlaku <span class="text-danger">*</span></label>
          <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Tanggal Selesai Berlaku</label>
          <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai') }}">
          <small class="text-muted">Kosongkan jika jadwal berlaku tanpa batas waktu.</small>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">QR Code Prefix</label>
          <input type="text" name="qr_code_prefix" class="form-control text-uppercase" placeholder="Contoh: PRM" value="{{ old('qr_code_prefix') }}">
          <small class="text-muted">Kosongkan untuk auto-generate dari nama kegiatan.</small>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Tahun Akademik</label>
          <select name="tahun_akademik_id" class="form-select">
            <option value="">-- Berlaku Semua Tahun Akademik --</option>
            @foreach($tahunAkademiks as $ta)
              <option value="{{ $ta->id }}" {{ old('tahun_akademik_id', $ta->is_aktif ? $ta->id : '') == $ta->id ? 'selected' : '' }}>
                {{ $ta->nama }} {{ $ta->is_aktif ? '(Aktif)' : '' }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6 d-flex align-items-center gap-4 mt-4">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_wajib" value="1" id="isWajibSwitch" {{ old('is_wajib') ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="isWajibSwitch">Kegiatan Wajib</label>
          </div>

          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_aktif" value="1" id="isAktifSwitch" {{ old('is_aktif', '1') == '1' ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="isAktifSwitch">Status Aktif</label>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION 3: TARGET PESERTA (OPSIONAL) -->
    <div class="das-panel">
      <div class="das-section-title">
        <i class="ti tabler-users"></i> 3. Target Peserta Kegiatan (Opsional)
      </div>
      <p class="text-muted fs-7 mb-3">Kosongkan semua target jika kegiatan ini diperuntukkan untuk <strong>seluruh siswa aktif</strong>.</p>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Target Tingkat</label>
          <div class="d-flex flex-wrap gap-3 p-2 border rounded">
            @foreach($tingkat as $t)
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="target_tingkat[]" value="{{ $t }}" id="tingkat_{{ $t }}" {{ in_array($t, old('target_tingkat', [])) ? 'checked' : '' }}>
                <label class="form-check-label" for="tingkat_{{ $t }}">Tingkat {{ $t }}</label>
              </div>
            @endforeach
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Target Jurusan</label>
          <div class="d-flex flex-wrap gap-2 p-2 border rounded" style="max-height: 120px; overflow-y: auto;">
            @foreach($jurusanList as $j)
              <div class="form-check me-2">
                <input class="form-check-input" type="checkbox" name="target_jurusan[]" value="{{ $j }}" id="jur_{{ \Illuminate\Support\Str::slug($j) }}" {{ in_array($j, old('target_jurusan', [])) ? 'checked' : '' }}>
                <label class="form-check-label" for="jur_{{ \Illuminate\Support\Str::slug($j) }}">{{ $j }}</label>
              </div>
            @endforeach
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Target Gender</label>
          <select name="target_gender" class="form-select">
            <option value="">Semua Gender (L & P)</option>
            <option value="L" {{ old('target_gender') == 'L' ? 'selected' : '' }}>Laki-laki Saja</option>
            <option value="P" {{ old('target_gender') == 'P' ? 'selected' : '' }}>Perempuan Saja</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Target Kelas Spesifik</label>
          <select name="target_peserta[]" class="select2 form-select" multiple>
            @foreach($kelas as $k)
              <option value="{{ $k->id }}" {{ in_array($k->id, old('target_peserta', [])) ? 'selected' : '' }}>{{ $k->nama }} (Tingkat {{ $k->tingkat }})</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Target Siswa Spesifik</label>
          <select name="target_siswa[]" class="select2 form-select" multiple>
            @foreach($siswaList as $s)
              <option value="{{ $s->id }}" {{ in_array($s->id, old('target_siswa', [])) ? 'selected' : '' }}>
                {{ $s->nama_lengkap }} ({{ $s->kelas?->nama ?? '-' }})
              </option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    <!-- BUTTON ACTIONS -->
    <div class="d-flex justify-content-end gap-2 mb-5">
      <a href="{{ route('admin.jadwal-kegiatan.index') }}" class="btn btn-label-secondary">Batal</a>
      <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Simpan Jadwal Berulang</button>
    </div>
  </form>
</div>
@endsection

@section('page-script')
<script>
  function toggleTipeJadwalUI() {
    const tipe = document.getElementById('tipeJadwalSelect').value;
    const polaHari = document.getElementById('polaHariContainer');
    const polaTanggal = document.getElementById('polaTanggalContainer');

    if (tipe === 'tanggal_kalender') {
      polaHari.classList.add('d-none');
      polaTanggal.classList.remove('d-none');
    } else {
      polaHari.classList.remove('d-none');
      polaTanggal.classList.add('d-none');
    }
  }

  function onEkskulChange() {
    const selectedEkskulId = document.getElementById('ekskulSelect').value;
    const ekskulJadwalSelect = document.getElementById('ekskulJadwalSelect');
    const options = ekskulJadwalSelect.options;

    for (let i = 0; i < options.length; i++) {
      const opt = options[i];
      const ekskulAttr = opt.getAttribute('data-ekskul');
      if (!ekskulAttr) continue;

      if (!selectedEkskulId || ekskulAttr === selectedEkskulId) {
        opt.style.display = '';
      } else {
        opt.style.display = 'none';
      }
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    toggleTipeJadwalUI();
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
      jQuery('.select2').select2({ placeholder: 'Pilih opsi...' });
    }
  });
</script>
@endsection
