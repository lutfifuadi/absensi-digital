@extends('layouts/layoutMaster')

@section('title', 'Detail Pengumuman')

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
          <i class="ti tabler-arrow-left me-1"></i> Kembali ke Daftar Pengumuman
        </a>
        <h4 class="fw-bold mb-0 text-white">{{ $item->judul }}</h4>
      </div>
      <div class="d-flex gap-2">
        @if($item->lampiran)
          <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank" class="btn btn-info">
            <i class="ti tabler-download me-1"></i> Unduh Lampiran
          </a>
        @endif
      </div>
    </div>

    <div class="card bg-dark text-white border border-secondary mb-4 shadow-sm" style="border-radius: 12px;">
      <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3 pb-3 border-bottom border-secondary">
          <span class="badge bg-primary text-uppercase">{{ $item->kategori }}</span>
          <span class="badge bg-label-info">
            <i class="ti tabler-target me-1"></i> Target: {{ strtoupper($item->target) }}
            @if($item->targetKelas)
              ({{ $item->targetKelas->nama }})
            @endif
          </span>
          @if($item->is_pinned)
            <span class="badge bg-warning text-dark"><i class="ti tabler-pin-filled me-1"></i> Disematkan</span>
          @endif
          <span class="ms-auto text-white-50 small">
            <i class="ti tabler-calendar me-1"></i> {{ $item->created_at ? $item->created_at->translatedFormat('d F Y H:i') : '' }}
          </span>
        </div>

        <div class="fs-6 lh-lg text-white mb-4" style="white-space: pre-line;">
          {{ $item->konten }}
        </div>

        @if($item->tanggal_mulai || $item->tanggal_selesai)
          <div class="p-3 rounded bg-white bg-opacity-10 text-white-50 small">
            <i class="ti tabler-clock me-1"></i> <strong>Masa Tampil:</strong> 
            {{ $item->tanggal_mulai ? $item->tanggal_mulai->translatedFormat('d F Y H:i') : 'Awal' }} 
            sampai 
            {{ $item->tanggal_selesai ? $item->tanggal_selesai->translatedFormat('d F Y H:i') : 'Selamanya' }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
