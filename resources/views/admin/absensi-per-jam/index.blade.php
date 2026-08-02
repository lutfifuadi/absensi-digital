@extends('layouts/layoutMaster')

@section('title', 'Absensi Siswa per Jam')

@section('page-style')
  <style>
    .jadwal-row-hover {
      transition: background 0.15s ease;
    }

    .jadwal-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    /* Override form control dark — pola jadwal/index */
    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: inherit;
      border-radius: 8px;
    }

    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(0, 207, 232, 0.6);
      box-shadow: 0 0 0 3px rgba(0, 207, 232, 0.12);
    }

    .form-select option {
      background: #1e1e2d;
      color: #cdd2e0;
    }

    .form-control[disabled],
    .form-select[disabled] {
      opacity: 0.45;
      cursor: not-allowed;
    }
  </style>
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       HERO HEADER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-clipboard-check text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            @if ($isAdmin)
              Absensi / Absensi Siswa per Jam
            @else
              Kelas Saya / Absensi Siswa per Jam
            @endif
          </div>
          <h4 class="das-hero__title text-gradient-gold">Absensi Siswa per Jam</h4>
          <p class="das-hero__subtitle">Isi kehadiran siswa per jam pelajaran hari ini.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white">
          <i class="ti tabler-calendar me-1"></i>
          {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d F Y') }}
        </div>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGE --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5"></i>
      <span>{{ session('error') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- INFO: input dinonaktifkan untuk non-admin di luar tanggal hari ini --}}
  @if (!$isAdmin && $tanggal !== now()->toDateString())
    <div class="alert alert-info alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-info-circle fs-5"></i>
      <span>Pengisian absensi dinonaktifkan untuk tanggal selain hari ini. Anda hanya dapat mengisi absensi pada
        tanggal hari ini.</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════
       PANEL JADWAL AKTIF
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <i class="ti tabler-calendar-event text-info"></i> Jadwal Aktif Hari Ini
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        {{-- Filter tanggal (GET ke index) --}}
        <form method="GET" action="{{ route('admin.absensi-per-jam.index') }}" class="d-flex align-items-center gap-2">
          <input type="date" name="tanggal" class="form-control form-control-sm" style="min-width:150px;"
            value="{{ $tanggal }}" @if (!$isAdmin) disabled @endif>
          <button type="submit" class="das-btn das-btn--ghost" @if (!$isAdmin) disabled @endif>
            <i class="ti tabler-calendar me-1"></i> Tampilkan
          </button>
        </form>
        <span class="das-chip --info">{{ $jadwalList->count() }} Jadwal</span>
      </div>
    </div>

    <div class="das-panel__body p-0">
      @forelse($jadwalList as $item)
        @php
          $statusBadge = match (true) {
              $item->sedang_berlangsung => ['label' => 'Sedang Berlangsung', 'color' => 'success'],
              $item->berikutnya => ['label' => 'Berikutnya', 'color' => 'info'],
              $item->selesai => ['label' => 'Selesai', 'color' => 'secondary'],
              default => ['label' => 'Belum Mulai', 'color' => 'secondary'],
          };
        @endphp

        <div class="jadwal-row-hover d-flex align-items-center gap-3 px-4 py-3 border-bottom flex-wrap"
          style="border-color:rgba(255,255,255,0.06) !important;">
          {{-- Avatar mapel --}}
          <div class="avatar avatar-sm flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="ti tabler-book"></i>
            </span>
          </div>

          {{-- Info jadwal --}}
          <div class="flex-grow-1" style="min-width:220px;">
            <div class="d-flex align-items-center flex-wrap gap-2">
              <span class="fw-semibold" style="font-size:.85rem;">{{ $item->kelas->nama ?? '-' }}</span>
              <span class="text-white-50 small d-inline-flex align-items-center gap-1">
                <i class="ti tabler-clock small"></i>
                {{ substr($item->jam_mulai, 0, 5) }} – {{ substr($item->jam_selesai, 0, 5) }}
              </span>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
              <span class="small fw-medium">{{ $item->mata_pelajaran }}</span>
              <span class="text-white-50 small">· Guru: {{ $item->guru->nama_lengkap ?? '-' }}</span>
            </div>
            <div class="d-flex flex-wrap gap-1 mt-2">
              @if ($item->sedang_berlangsung)
                <span class="badge bg-label-success px-2 py-1 rounded-pill small">
                  <span class="pulse-dot me-1" style="vertical-align:middle;"></span>{{ $statusBadge['label'] }}
                </span>
              @else
                <span class="badge bg-label-{{ $statusBadge['color'] }} px-2 py-1 rounded-pill small">{{ $statusBadge['label'] }}</span>
              @endif

              @if ($item->sudah_diisi)
                <span class="badge bg-label-primary px-2 py-1 rounded-pill small">
                  <i class="ti tabler-check me-1"></i>Terisi
                </span>
              @endif

              @if ($item->is_pengganti)
                <span class="badge bg-label-warning px-2 py-1 rounded-pill small">
                  <i class="ti tabler-user-swap me-1"></i>Pengganti
                </span>
              @endif
            </div>
          </div>

          {{-- Aksi --}}
          <div class="flex-shrink-0">
            <a href="{{ route('admin.absensi-per-jam.show', ['jadwal' => $item->id, 'tanggal' => $tanggal]) }}"
              class="das-btn {{ $item->sudah_diisi ? 'das-btn--ghost' : 'das-btn--primary' }}">
              <i class="ti {{ $item->sudah_diisi ? 'tabler-pencil' : 'tabler-clipboard-check' }} me-1"></i>
              {{ $item->sudah_diisi ? 'Edit Absensi' : 'Isi Absensi' }}
            </a>
          </div>
        </div>
      @empty
        <div class="text-center py-5">
          <div class="d-flex flex-column align-items-center gap-2 opacity-50">
            <i class="ti tabler-calendar-off" style="font-size:2.5rem;"></i>
            <span class="small">Tidak ada jadwal pada tanggal ini.</span>
          </div>
        </div>
      @endforelse
    </div>
  </div>

@endsection
