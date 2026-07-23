@extends('layouts/layoutMaster')

@section('title', 'Monitor Penugasan Guru')

@php use Illuminate\Support\Str; @endphp

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
            <i class="ti tabler-book-upload"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Monitor Akademik
          </div>
          <h4 class="das-hero__title text-gradient-gold">Monitor Penugasan Guru</h4>
          <p class="das-hero__subtitle">Pantau seluruh penugasan mandiri yang didistribusikan oleh guru ketika berhalangan hadir.</p>
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

  <div class="das-panel mb-4">
    <div class="das-panel__body py-3">
      <form method="GET" class="row gy-2 gx-2 align-items-end">
        <div class="col-6 col-md-3">
          <label class="form-label text-white-50 small mb-1 fw-bold">KELAS</label>
          <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);">
            <option value="">Semua Kelas</option>
            @foreach ($kelasOptions as $k)
              <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label text-white-50 small mb-1 fw-bold">CARI TUGAS / GURU</label>
          <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control" placeholder="Judul / Guru / Mapel..." value="{{ request('search') }}" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);">
            <button class="btn btn-outline-secondary" type="submit" style="border: 1px solid rgba(255,255,255,0.1);"><i class="ti tabler-search"></i></button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card bg-transparent border-0 shadow-none">
    <div class="table-responsive text-nowrap rounded-3">
      <table class="table table-hover align-middle mb-0" style="background: rgba(15, 23, 42, 0.35); border: 1px solid rgba(255,255,255,0.05);">
        <thead style="background: rgba(15, 23, 42, 0.65); border-bottom: 2px solid rgba(255,255,255,0.1);">
          <tr>
            <th class="text-white border-0 py-3">Tanggal Tugas</th>
            <th class="text-white border-0 py-3">Guru Pendidik</th>
            <th class="text-white border-0 py-3">Kelas</th>
            <th class="text-white border-0 py-3">Mata Pelajaran</th>
            <th class="text-white border-0 py-3">Judul Tugas</th>
            <th class="text-white border-0 py-3">Lampiran</th>
            <th class="text-white border-0 py-3 text-center" style="width: 120px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($assignments as $a)
            <tr class="assignment-row-hover" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
              <td class="py-3 text-white">{{ $a->tanggal_tugas->format('d M Y') }}</td>
              <td class="text-white fw-bold">{{ $a->guru->nama_lengkap ?? '-' }}</td>
              <td class="text-white fw-bold">{{ $a->kelas->nama ?? '-' }}</td>
              <td class="text-white-50">{{ $a->mata_pelajaran }}</td>
              <td class="text-white-50">{{ \Illuminate\Support\Str::limit($a->judul, 40) }}</td>
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
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <a href="{{ route('admin.assignments.show', $a->id) }}" class="btn btn-xs btn-outline-secondary">
                    <i class="ti tabler-eye fs-6"></i>
                  </a>
                  <form action="{{ route('admin.assignments.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus penugasan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-outline-danger">
                      <i class="ti tabler-trash fs-6"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5 text-white-50">
                <div class="d-flex flex-column align-items-center gap-2">
                  <i class="ti tabler-notes-off fs-1 text-white-20"></i>
                  <span>Tidak ada data penugasan yang ditemukan.</span>
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
