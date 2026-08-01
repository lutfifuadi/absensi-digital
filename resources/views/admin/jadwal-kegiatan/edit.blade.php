@extends('layouts/layoutMaster')

@section('title', 'Edit Jadwal Kegiatan Berulang')

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
      <h4 class="fw-bold mb-1 text-white"><i class="ti tabler-pencil me-2 text-warning"></i>Edit Jadwal Kegiatan Berulang</h4>
      <p class="text-muted mb-0">Perbarui parameter jadwal. Perubahan hanya berlaku untuk sesi mendatang (PRD-005).</p>
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

  <form action="{{ route('admin.jadwal-kegiatan.update', $jadwalKegiatan->id) }}" method="POST">
    @csrf
    @method('PUT')

    <!-- SECTION 1: INFORMASI UTAMA -->
    <div class="das-panel">
      <div class="das-section-title">
        <i class="ti tabler-info-circle"></i> 1. Informasi Utama Kegiatan
      </div>

      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label fw-semibold">Nama Kegiatan <span class="text-danger">*</span></label>
          <input type="text" name="nama_kegiatan" class="form-control" value="{{ old('nama_kegiatan', $jadwalKegiatan->nama_kegiatan) }}" required>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Jenis Kegiatan <span class="text-danger">*</span></label>
          <select name="jenis" class="form-select" required>
            @foreach(['Seminar', 'Ekstrakurikuler', 'Lomba', 'Acara Internal', 'Lainnya'] as $j)
              <option value="{{ $j }}" {{ old('jenis', $jadwalKegiatan->jenis) == $j ? 'selected' : '' }}>{{ $j }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Tipe Pola Jadwal</label>
          <input type="text" class="form-control bg-dark-subtle text-muted" value="{{ str_replace('_', ' ', strtoupper($jadwalKegiatan->tipe_jadwal)) }}" disabled>
          <small class="text-warning"><i class="ti ti-alert-triangle me-1"></i>Tipe jadwal tidak dapat diubah setelah dibuat (BR-2).</small>
        </div>

        <!-- POLA HARI MINGGUAN -->
        @if($jadwalKegiatan->tipe_jadwal !== 'tanggal_kalender')
          <div class="col-md-8">
            <label class="form-label fw-semibold">Pilih Hari Pelaksanaan <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-3 p-2 border rounded bg-dark-subtle">
              @php
                $hariList = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'];
                $existingHari = is_array($jadwalKegiatan->hari) ? $jadwalKegiatan->hari : [];
                $oldHari = old('hari', $existingHari);
              @endphp
              @foreach($hariList as $key => $label)
                <div class="form-check me-2">
                  <input class="form-check-input" type="checkbox" name="hari[]" value="{{ $key }}" id="hari_{{ $key }}" {{ in_array($key, $oldHari) ? 'checked' : '' }}>
                  <label class="form-check-label" for="hari_{{ $key }}">{{ $label }}</label>
                </div>
              @endforeach
            </div>
          </div>
        @else
          <!-- POLA TANGGAL KALENDER -->
          <div class="col-md-8">
            <label class="form-label fw-semibold">Tanggal Kalender Per Bulan <span class="text-danger">*</span></label>
            @php
              $existingDates = is_array($jadwalKegiatan->tanggal_kalender) ? implode(', ', $jadwalKegiatan->tanggal_kalender) : '';
            @endphp
            <input type="text" name="tanggal_kalender_input" class="form-control" value="{{ old('tanggal_kalender_input', $existingDates) }}">
            <small class="text-muted">Masukkan tanggal 1-31 dipisahkan koma. Contoh: <code>5, 20</code></small>
          </div>
        @endif

        <div class="col-md-3">
          <label class="form-label fw-semibold">Waktu Mulai</label>
          <input type="time" name="waktu_mulai" class="form-control" value="{{ old('waktu_mulai', $jadwalKegiatan->waktu_mulai ? substr($jadwalKegiatan->waktu_mulai,0,5) : '') }}">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Waktu Selesai</label>
          <input type="time" name="waktu_selesai" class="form-control" value="{{ old('waktu_selesai', $jadwalKegiatan->waktu_selesai ? substr($jadwalKegiatan->waktu_selesai,0,5) : '') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Lokasi Pelaksanaan</label>
          <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi', $jadwalKegiatan->lokasi) }}">
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Keterangan / Catatan</label>
          <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $jadwalKegiatan->keterangan) }}</textarea>
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
          <select name="ekskul_id" class="form-select">
            <option value="">-- Tidak Terikat Ekskul (Mandiri) --</option>
            @foreach($ekskuls as $e)
              <option value="{{ $e->id }}" {{ old('ekskul_id', $jadwalKegiatan->ekskul_id) == $e->id ? 'selected' : '' }}>{{ $e->nama }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Tautkan ke Jadwal Ekskul Existing</label>
          <select name="ekskul_jadwal_id" class="form-select">
            <option value="">-- Buat Waktu / Hari Terpisah --</option>
            @foreach($ekskuls as $e)
              @foreach($e->jadwal as $ej)
                <option value="{{ $ej->id }}" {{ old('ekskul_jadwal_id', $jadwalKegiatan->ekskul_jadwal_id) == $ej->id ? 'selected' : '' }}>
                  {{ $e->nama }} — {{ ucfirst($ej->hari) }} ({{ substr($ej->jam_mulai,0,5) }} - {{ substr($ej->jam_selesai,0,5) }}) @ {{ $ej->lokasi }}
                </option>
              @endforeach
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Tanggal Mulai Berlaku <span class="text-danger">*</span></label>
          <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', $jadwalKegiatan->tanggal_mulai?->format('Y-m-d')) }}" required>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Tanggal Selesai Berlaku</label>
          <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', $jadwalKegiatan->tanggal_selesai?->format('Y-m-d')) }}">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">QR Code Prefix</label>
          <input type="text" name="qr_code_prefix" class="form-control text-uppercase" value="{{ old('qr_code_prefix', $jadwalKegiatan->qr_code_prefix) }}">
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Tahun Akademik</label>
          <select name="tahun_akademik_id" class="form-select">
            <option value="">-- Berlaku Semua Tahun Akademik --</option>
            @foreach($tahunAkademiks as $ta)
              <option value="{{ $ta->id }}" {{ old('tahun_akademik_id', $jadwalKegiatan->tahun_akademik_id) == $ta->id ? 'selected' : '' }}>
                {{ $ta->nama }} {{ $ta->is_aktif ? '(Aktif)' : '' }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6 d-flex align-items-center gap-4 mt-4">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_wajib" value="1" id="isWajibSwitch" {{ old('is_wajib', $jadwalKegiatan->is_wajib) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="isWajibSwitch">Kegiatan Wajib</label>
          </div>

          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_aktif" value="1" id="isAktifSwitch" {{ old('is_aktif', $jadwalKegiatan->is_aktif) ? 'checked' : '' }}>
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

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Target Tingkat</label>
          <div class="d-flex flex-wrap gap-3 p-2 border rounded">
            @php $oldTingkat = old('target_tingkat', $jadwalKegiatan->target_tingkat ?? []); @endphp
            @foreach($tingkat as $t)
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="target_tingkat[]" value="{{ $t }}" id="tingkat_{{ $t }}" {{ in_array($t, $oldTingkat) ? 'checked' : '' }}>
                <label class="form-check-label" for="tingkat_{{ $t }}">Tingkat {{ $t }}</label>
              </div>
            @endforeach
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Target Jurusan</label>
          <div class="d-flex flex-wrap gap-2 p-2 border rounded" style="max-height: 120px; overflow-y: auto;">
            @php $oldJurusan = old('target_jurusan', $jadwalKegiatan->target_jurusan ?? []); @endphp
            @foreach($jurusanList as $j)
              <div class="form-check me-2">
                <input class="form-check-input" type="checkbox" name="target_jurusan[]" value="{{ $j }}" id="jur_{{ \Illuminate\Support\Str::slug($j) }}" {{ in_array($j, $oldJurusan) ? 'checked' : '' }}>
                <label class="form-check-label" for="jur_{{ \Illuminate\Support\Str::slug($j) }}">{{ $j }}</label>
              </div>
            @endforeach
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Target Gender</label>
          <select name="target_gender" class="form-select">
            <option value="">Semua Gender (L & P)</option>
            <option value="L" {{ old('target_gender', $jadwalKegiatan->target_gender) == 'L' ? 'selected' : '' }}>Laki-laki Saja</option>
            <option value="P" {{ old('target_gender', $jadwalKegiatan->target_gender) == 'P' ? 'selected' : '' }}>Perempuan Saja</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Target Kelas Spesifik</label>
          @php $oldPeserta = old('target_peserta', $jadwalKegiatan->target_peserta ?? []); @endphp
          <select name="target_peserta[]" class="select2 form-select" multiple>
            @foreach($kelas as $k)
              <option value="{{ $k->id }}" {{ in_array($k->id, $oldPeserta) ? 'selected' : '' }}>{{ $k->nama }} (Tingkat {{ $k->tingkat }})</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Target Siswa Spesifik</label>
          @php $oldSiswa = old('target_siswa', $jadwalKegiatan->target_siswa ?? []); @endphp
          <select name="target_siswa[]" class="select2 form-select" multiple>
            @foreach($siswaList as $s)
              <option value="{{ $s->id }}" {{ in_array($s->id, $oldSiswa) ? 'selected' : '' }}>
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
      <button type="submit" class="btn btn-warning"><i class="ti tabler-check me-1"></i> Update Jadwal Berulang</button>
    </div>
  </form>
</div>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
      jQuery('.select2').select2({ placeholder: 'Pilih opsi...' });
    }
  });
</script>
@endsection
