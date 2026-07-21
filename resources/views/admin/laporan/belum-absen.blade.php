@extends('layouts/layoutMaster')

@section('title', 'Rekap Murid Belum Absen')

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

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

    .student-row-hover {
      transition: background 0.15s ease;
    }

    .student-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
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
            <i class="ti tabler-users-minus text-danger"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Wali Kelas Portal
          </div>
          <h4 class="das-hero__title text-gradient-gold">Rekap Murid Belum Absen</h4>
          <p class="das-hero__subtitle">Melihat daftar siswa kelas bimbingan Anda yang belum tercatat absensinya.</p>
        </div>
      </div>
    </div>
  </div>

  @foreach (['success', 'error'] as $msg)
    @if (session($msg))
      <div class="alert alert-{{ $msg === 'success' ? 'success' : 'danger' }} alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
        <i class="ti {{ $msg === 'success' ? 'tabler-circle-check' : 'tabler-alert-circle' }} fs-5"></i>
        <span>{{ session($msg) }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach

  {{-- FILTER & SUMMARY PANEL --}}
  <div class="card glass-card mb-4">
    <div class="card-body py-3">
      <form method="GET" action="{{ route('wali-kelas.belum-absen') }}" class="row gy-2 gx-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label text-white-50 small fw-bold mb-1">KELAS BIMBINGAN</label>
          <input type="text" class="form-control" value="{{ $kelasWaliNama }}" readonly style="opacity: 0.85;">
        </div>
        <div class="col-md-4">
          <label class="form-label text-white-50 small fw-bold mb-1" for="tanggal">TANGGAL REKAP</label>
          <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ $tanggal }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
          <button type="submit" class="btn btn-outline-info btn-sm">
            <i class="ti tabler-refresh me-1"></i> Refresh Data
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- TABLE LIST --}}
  <div class="card bg-transparent border-0 shadow-none">
    <div class="table-responsive text-nowrap rounded-3">
      <table class="table table-hover align-middle mb-0" style="background: rgba(15, 23, 42, 0.35); border: 1px solid rgba(255,255,255,0.05);">
        <thead style="background: rgba(15, 23, 42, 0.65); border-bottom: 2px solid rgba(255,255,255,0.1);">
          <tr>
            <th class="text-white border-0 py-3" style="width: 50px;">#</th>
            <th class="text-white border-0 py-3">NIS/NISN</th>
            <th class="text-white border-0 py-3">Nama Lengkap</th>
            <th class="text-white border-0 py-3">Status Kehadiran</th>
            <th class="text-white border-0 py-3 text-center" style="width: 150px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($siswaBelumAbsen as $siswa)
            <tr class="student-row-hover" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
              <td class="py-3 text-white-50 small">{{ $loop->iteration }}</td>
              <td class="text-white-50">{{ $siswa->nis ?? ($siswa->nisn ?? '-') }}</td>
              <td class="text-white fw-bold">
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar avatar-xs">
                    <span class="avatar-initial rounded-circle bg-label-secondary" style="font-size: 0.65rem;">
                      {{ strtoupper(substr($siswa->nama_lengkap, 0, 2)) }}
                    </span>
                  </div>
                  {{ $siswa->nama_lengkap }}
                </div>
              </td>
              <td>
                <span class="badge bg-label-danger px-2 py-1">
                  <i class="ti tabler-circle-x me-1 fs-6"></i> Belum Absen
                </span>
              </td>
              <td class="text-center">
                <a href="{{ route('wali-kelas.absensi-manual.create', ['siswa_id' => $siswa->id, 'tanggal' => $tanggal]) }}" class="btn btn-xs btn-info">
                  <i class="ti tabler-edit-circle fs-6 me-1"></i> Absen Manual
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-5 text-white-50">
                <div class="d-flex flex-column align-items-center gap-2">
                  <i class="ti tabler-circle-check fs-1 text-success opacity-50"></i>
                  <span class="fw-semibold text-white">Semua Murid Sudah Absen!</span>
                  <span class="small">Tidak ada siswa yang belum tercatat absensinya pada tanggal {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}.</span>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection
