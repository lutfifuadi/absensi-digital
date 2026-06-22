@extends('layouts/layoutMaster')

@section('title', 'Tugas Mandiri')

@section('page-style')
  <style>
    .assignment-row-hover {
      transition: background 0.15s ease;
    }
    .assignment-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }
  </style>
@endsection

@section('content')
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-notebook"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Portal Siswa
          </div>
          <h4 class="das-hero__title text-gradient-gold">Tugas Mandiri</h4>
          <p class="das-hero__subtitle">Berikut adalah daftar tugas mandiri yang diberikan oleh guru yang berhalangan hadir.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="card bg-transparent border-0 shadow-none">
    <div class="table-responsive text-nowrap rounded-3">
      <table class="table table-hover align-middle mb-0" style="background: rgba(15, 23, 42, 0.35); border: 1px solid rgba(255,255,255,0.05);">
        <thead style="background: rgba(15, 23, 42, 0.65); border-bottom: 2px solid rgba(255,255,255,0.1);">
          <tr>
            <th class="text-white border-0 py-3">Tanggal Tugas</th>
            <th class="text-white border-0 py-3">Guru Pendidik</th>
            <th class="text-white border-0 py-3">Mata Pelajaran</th>
            <th class="text-white border-0 py-3">Judul Tugas</th>
            <th class="text-white border-0 py-3">Lampiran</th>
            <th class="text-white border-0 py-3 text-center" style="width: 100px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($assignments as $a)
            <tr class="assignment-row-hover" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
              <td class="py-3 text-white">{{ $a->tanggal_tugas->format('d M Y') }}</td>
              <td class="text-white fw-bold">{{ $a->guru->nama_lengkap ?? '-' }}</td>
              <td class="text-white-50">{{ $a->mata_pelajaran }}</td>
              <td class="text-white-50">{{ Str::limit($a->judul, 50) }}</td>
              <td>
                @if ($a->file_lampiran)
                  <a href="{{ asset('storage/' . $a->file_lampiran) }}" target="_blank" class="btn btn-xs btn-outline-info">
                    <i class="ti tabler-download fs-6 me-1"></i> Download
                  </a>
                @else
                  <span class="text-white-20">-</span>
                @endif
              </td>
              <td class="text-center">
                <a href="{{ route('siswa.assignments.show', $a->id) }}" class="btn btn-xs btn-outline-secondary">
                  <i class="ti tabler-eye fs-6 me-1"></i> Lihat Detail
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-5 text-white-50">
                <div class="d-flex flex-column align-items-center gap-2">
                  <i class="ti tabler-notes-off fs-1 text-white-20"></i>
                  <span>Alhamdulillah, tidak ada penugasan khusus hari ini.</span>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-3">
      {{ $assignments->links() }}
    </div>
  </div>
@endsection
