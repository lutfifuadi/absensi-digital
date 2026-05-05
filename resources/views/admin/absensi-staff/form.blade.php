@extends('layouts/layoutMaster')

@section('title', isset($absensiStaff) ? 'Ubah Absensi Staff TU' : 'Tambah Absensi Staff TU')

@section('page-style')
  <style>
    .form-alert {
      transition: all .2s ease;
    }

    .form-card {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
  </style>
@endsection

@section('content')
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 4px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
              style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
              <i class="ti {{ isset($absensiStaff) ? 'tabler-pencil' : 'tabler-plus' }} text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  <li class="breadcrumb-item"><a href="{{ route('admin.absensi-staff.index') }}"
                      class="text-white text-decoration-none">Absensi</a></li>
                  <li class="breadcrumb-item active text-white">{{ isset($absensiStaff) ? 'Ubah' : 'Tambah' }}</li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                {{ isset($absensiStaff) ? 'Ubah Absensi Staff TU' : 'Tambah Absensi Staff TU' }}
              </h4>
              <p class="mb-0 text-white opacity-60 small">Catat kehadiran staff tata usaha dengan mudah dan cepat.</p>
            </div>
          </div>
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

      <div class="card border-0 shadow-sm form-card">
        <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
          style="border-color:rgba(255,255,255,0.08) !important; background:transparent;">
          <i class="ti tabler-forms text-info"></i>
          <h6 class="card-title mb-0">Form Absensi Staff TU</h6>
        </div>
        <div class="card-body p-4">
          <form
            action="{{ isset($absensiStaff) ? route('admin.absensi-staff.update', $absensiStaff) : route('admin.absensi-staff.store') }}"
            method="POST">
            @csrf
            @if (isset($absensiStaff))
              @method('PUT')
            @endif

            <div class="row g-4">
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="staff_id">
                  <i class="ti tabler-user me-1 text-info"></i> Staff TU <span class="text-danger">*</span>
                </label>
                <select id="staff_id" name="staff_id" class="form-select" required>
                  <option value="">Pilih staff</option>
                  @foreach ($staffOptions as $staff)
                    <option value="{{ $staff->id }}"
                      {{ old('staff_id', $absensiStaff->staff_id ?? '') == $staff->id ? 'selected' : '' }}>
                      {{ $staff->nama_lengkap }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="tanggal">
                  <i class="ti tabler-calendar me-1 text-info"></i> Tanggal <span class="text-danger">*</span>
                </label>
                <input id="tanggal" type="date" name="tanggal" class="form-control"
                  value="{{ old('tanggal', isset($absensiStaff) ? $absensiStaff->tanggal->format('Y-m-d') : '') }}"
                  required>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="jam_masuk">
                  <i class="ti tabler-clock-measure me-1 text-info"></i> Jam Masuk
                </label>
                <input id="jam_masuk" type="time" name="jam_masuk" class="form-control"
                  value="{{ old('jam_masuk', $absensiStaff->jam_masuk ?? '') }}">
              </div>

              <div class="col-md-4">
                <label class="form-label fw-semibold small" for="jam_pulang">
                  <i class="ti tabler-clock-check me-1 text-info"></i> Jam Pulang
                </label>
                <input id="jam_pulang" type="time" name="jam_pulang" class="form-control"
                  value="{{ old('jam_pulang', $absensiStaff->jam_pulang ?? '') }}">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="status">
                  <i class="ti tabler-circle-check me-1 text-info"></i> Status <span class="text-danger">*</span>
                </label>
                <select id="status" name="status" class="form-select" required>
                  @foreach (['hadir', 'sakit', 'izin', 'alpha', 'terlambat'] as $status)
                    <option value="{{ $status }}"
                      {{ old('status', $absensiStaff->status ?? '') === $status ? 'selected' : '' }}>
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
                      {{ old('metode', $absensiStaff->metode ?? '') === $metode ? 'selected' : '' }}>
                      {{ strtoupper($metode) }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold small" for="keterangan">
                  <i class="ti tabler-message-circle me-1 text-info"></i> Keterangan
                </label>
                <textarea id="keterangan" name="keterangan" class="form-control" rows="3">{{ old('keterangan', $absensiStaff->keterangan ?? '') }}</textarea>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
              style="border-color:rgba(255,255,255,0.08) !important;">
              <a href="{{ route('admin.absensi-staff.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Kembali
              </a>
              <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ isset($absensiStaff) ? 'Perbarui Absensi' : 'Simpan Absensi' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
