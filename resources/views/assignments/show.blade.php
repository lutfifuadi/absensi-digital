@extends('layouts/layoutMaster')

@section('title', 'Detail Penugasan')

@section('content')
  @php
    $user = auth()->user();
    $backUrl = route('admin.assignments.index');
    if ($user->role === 'guru') {
        $backUrl = route('assignments.index');
    } elseif ($user->role === 'siswa') {
        $backUrl = route('siswa.assignments.index');
    }
  @endphp

  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-book"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Detail Penugasan
          </div>
          <h4 class="das-hero__title text-gradient-gold">{{ $assignment->judul }}</h4>
          <p class="das-hero__subtitle">{{ $assignment->mata_pelajaran }} — Kelas {{ $assignment->kelas->nama ?? '-' }}</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ $backUrl }}" class="das-btn das-btn--secondary">
          <i class="ti tabler-arrow-left me-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
      <div class="das-panel mb-4">
        <div class="das-panel__header pb-2 border-bottom border-secondary mb-3">
          <h5 class="text-white mb-0"><i class="ti tabler-align-left me-2 text-info"></i>Instruksi & Deskripsi Tugas</h5>
        </div>
        <div class="das-panel__body text-white-50 lh-lg" style="white-space: pre-wrap;">
          {!! e($assignment->deskripsi) !!}
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="das-panel mb-4">
        <div class="das-panel__header pb-2 border-bottom border-secondary mb-3">
          <h5 class="text-white mb-0"><i class="ti tabler-info-circle me-2 text-warning"></i>Informasi Penugasan</h5>
        </div>
        <div class="das-panel__body text-white-50">
          <div class="mb-3">
            <label class="d-block small text-white-30 fw-bold">GURU PENDIDIK</label>
            <span class="text-white font-semibold">{{ $assignment->guru->nama_lengkap ?? '-' }}</span>
          </div>

          <div class="mb-3">
            <label class="d-block small text-white-30 fw-bold">TANGGAL TUGAS</label>
            <span class="text-white font-semibold">{{ $assignment->tanggal_tugas->format('d F Y') }}</span>
          </div>

          <div class="mb-3">
            <label class="d-block small text-white-30 fw-bold">KELAS</label>
            <span class="text-white font-semibold">{{ $assignment->kelas->nama ?? '-' }}</span>
          </div>

          @if ($assignment->file_lampiran)
            <div class="mb-3">
              <label class="d-block small text-white-30 fw-bold">LAMPIRAN TUGAS</label>
              <a href="{{ asset('storage/' . $assignment->file_lampiran) }}" target="_blank" class="btn btn-sm btn-info mt-1 w-100">
                <i class="ti tabler-download me-1"></i> Download Lampiran
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
