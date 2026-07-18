@extends('layouts/layoutMaster')

@section('title', isset($absensiSiswa) ? 'Ubah Absensi Siswa' : 'Tambah Absensi Siswa')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <style>
    .form-alert {
      transition: all .2s ease;
    }

    /* Tambahkan style kustom jika diperlukan, pastikan border-radius maksimal 5px */
    .form-control, .form-select, .btn {
      border-radius: 5px !important;
    }
  </style>
@endsection

@section('content')
  {{-- HERO HEADER --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti {{ isset($absensiSiswa) ? 'tabler-pencil' : 'tabler-calendar-time' }} text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.absensi-siswa.index') }}" class="text-white text-decoration-none">Absensi</a> /
            {{ isset($absensiSiswa) ? 'Ubah' : 'Tambah' }}
          </div>
          <h4 class="das-hero__title text-gradient-gold">
            {{ isset($absensiSiswa) ? 'Ubah Data Absensi' : 'Tambah Absensi Baru' }}
          </h4>
          <p class="das-hero__subtitle">Catat absensi siswa dengan cepat dan konsisten.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      @if ($errors->any())
        <div
          class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm form-alert"
          style="border-radius:8px; background: rgba(234, 84, 85, 0.15); color: #ea5455;">
          <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
          <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="das-panel" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important; background:transparent;">
          <i class="ti tabler-forms text-info"></i>
          <h6 class="das-panel__title mb-0">Informasi Lengkap Absensi</h6>
        </div>
        <div class="das-panel__body p-4">
          <form
            action="{{ isset($absensiSiswa) ? route('admin.absensi-siswa.update', $absensiSiswa) : route('admin.absensi-siswa.store') }}"
            method="POST">
            @csrf
            @if (isset($absensiSiswa))
              @method('PUT')
            @endif

            <div class="row g-4">
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="siswa_id">
                  <i class="ti tabler-user me-1 text-info"></i> Siswa <span class="text-danger">*</span>
                </label>
                <select id="siswa_id" name="siswa_id" class="form-select" required>
                  <option value="">Pilih siswa</option>
                  @foreach ($siswaOptions as $siswa)
                    <option value="{{ $siswa->id }}"
                      {{ old('siswa_id', $absensiSiswa->siswa_id ?? '') == $siswa->id ? 'selected' : '' }}>
                      {{ $siswa->nama_lengkap }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="kelas_id">
                  <i class="ti tabler-door me-1 text-info"></i> Kelas <span class="text-danger">*</span>
                </label>
                <select id="kelas_id" name="kelas_id" class="form-select" required>
                  <option value="">Pilih kelas</option>
                  @foreach ($kelasOptions as $kelas)
                    <option value="{{ $kelas->id }}"
                      {{ old('kelas_id', $absensiSiswa->kelas_id ?? '') == $kelas->id ? 'selected' : '' }}>
                      {{ $kelas->nama }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="tanggal">
                  <i class="ti tabler-calendar me-1 text-info"></i> Tanggal <span class="text-danger">*</span>
                </label>
                <input id="tanggal" type="date" name="tanggal" class="form-control"
                  value="{{ old('tanggal', isset($absensiSiswa) ? $absensiSiswa->tanggal->format('Y-m-d') : '') }}"
                  required>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="jam_masuk">
                  <i class="ti tabler-clock me-1 text-info"></i> Jam Masuk
                </label>
                <input id="jam_masuk" type="time" name="jam_masuk" class="form-control"
                  value="{{ old('jam_masuk', $absensiSiswa->jam_masuk ?? '') }}">
              </div>

              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="jam_pulang">
                  <i class="ti tabler-clock-play me-1 text-info"></i> Jam Pulang
                </label>
                <input id="jam_pulang" type="time" name="jam_pulang" class="form-control"
                  value="{{ old('jam_pulang', $absensiSiswa->jam_pulang ?? '') }}">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="status">
                  <i class="ti tabler-circle-check me-1 text-info"></i> Status <span class="text-danger">*</span>
                </label>
                <select id="status" name="status" class="form-select" required>
                  @php
                    $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();
                    $statuses = ['hadir', 'sakit', 'izin', 'alpha'];
                    if (!in_array($activeJenjang, ['SD/MI', 'SMP/MTs'])) {
                        $statuses[] = 'terlambat';
                    }
                  @endphp
                  @foreach ($statuses as $status)
                    <option value="{{ $status }}"
                      {{ old('status', $absensiSiswa->status ?? '') === $status ? 'selected' : '' }}>
                      {{ ucfirst($status) }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="metode">
                  <i class="ti tabler-layout-grid me-1 text-info"></i> Metode <span class="text-danger">*</span>
                </label>
                <select id="metode" name="metode" class="form-select" required>
                  @foreach (['manual', 'qr', 'rfid'] as $metode)
                    <option value="{{ $metode }}"
                      {{ old('metode', $absensiSiswa->metode ?? '') === $metode ? 'selected' : '' }}>
                      {{ strtoupper($metode) }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="guru_id">
                  <i class="ti tabler-presentation me-1 text-info"></i> Guru
                </label>
                <select id="guru_id" name="guru_id" class="form-select">
                  <option value="">Tidak dipilih</option>
                  @foreach ($guruOptions as $guru)
                    <option value="{{ $guru->id }}"
                      {{ old('guru_id', $absensiSiswa->guru_id ?? '') == $guru->id ? 'selected' : '' }}>
                      {{ $guru->nama_lengkap }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold small" for="keterangan">
                  <i class="ti tabler-message-circle me-1 text-info"></i> Keterangan
                </label>
                <textarea id="keterangan" name="keterangan" class="form-control" rows="3">{{ old('keterangan', $absensiSiswa->keterangan ?? '') }}</textarea>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('admin.absensi-siswa.index') }}" class="btn das-btn --secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn das-btn --primary">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ isset($absensiSiswa) ? 'Perbarui Absensi' : 'Simpan Absensi' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
