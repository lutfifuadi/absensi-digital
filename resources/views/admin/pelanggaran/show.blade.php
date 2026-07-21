@extends('layouts/layoutMaster')

@section('title', 'Detail Catatan Pelanggaran — ' . $pelanggaran->siswa->nama_lengkap)

@section('content')
<div class="das-hero das-hero--with-stats mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        @php
          $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($pelanggaran->siswa->nama_lengkap) . '&size=120&background=7367f0&color=fff';
          if ($pelanggaran->siswa->foto) {
              if (strlen($pelanggaran->siswa->foto) > 30) {
                  $avatarUrl = 'https://drive.google.com/thumbnail?id=' . $pelanggaran->siswa->foto . '&sz=w200&_t=' . time();
              } else {
                  $avatarUrl = asset('storage/' . $pelanggaran->siswa->foto);
              }
          }
        @endphp
        <img class="das-hero__logo" src="{{ $avatarUrl }}" alt="Avatar" style="object-fit: cover;">
        <div class="das-hero__logo-glow"></div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Student ID: {{ $pelanggaran->siswa->nis }}
        </div>
        <h4 class="das-hero__title text-gradient-gold">{{ $pelanggaran->siswa->nama_lengkap }}</h4>
        <p class="das-hero__subtitle">{{ $pelanggaran->siswa->kelas->nama ?? 'Tanpa Kelas' }} • TA {{ $pelanggaran->siswa->tahunAkademik->nama ?? '-' }}</p>
      </div>
    </div>

    <div class="das-hero__actions d-flex gap-2">
      <a href="{{ route('admin.pelanggaran.index') }}" class="das-btn das-btn--secondary">
        <i class="ti tabler-arrow-left"></i> Kembali
      </a>
      @php $userRole = auth()->user()?->role; @endphp
      @if(in_array($userRole, ['super_admin', 'admin_sekolah', 'operator']))
        <a href="{{ route('admin.pelanggaran.edit', $pelanggaran->id) }}" class="das-btn das-btn--warning">
          <i class="ti tabler-edit"></i> Edit Pelanggaran
        </a>
      @endif
    </div>
  </div>

  <div class="das-stats-row">
    {{-- Card 1 (Danger): Total Poin Pelanggaran --}}
    <div class="das-stat-card das-stat-card--danger">
      <div class="das-stat-card__icon"><i class="ti tabler-alert-triangle"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $pelanggaran->siswa->pelanggaranSiswa()->sum('poin_saat_itu') }}</div>
        <div class="das-stat-card__label">Total Poin</div>
      </div>
    </div>
    {{-- Card 2 (Warning): Level SP Aktif --}}
    <div class="das-stat-card das-stat-card--warning">
      <div class="das-stat-card__icon"><i class="ti tabler-shield-alert"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val" style="font-size: 1.15rem; line-height: 1.8;">
          {{ $pelanggaran->siswa->pelanggaranSp()->latest()->first()->level_sp ?? 'Normal' }}
        </div>
        <div class="das-stat-card__label">SP Aktif</div>
      </div>
    </div>
    {{-- Card 3 (Info): Jumlah Total Pelanggaran Tercatat --}}
    <div class="das-stat-card das-stat-card--info">
      <div class="das-stat-card__icon"><i class="ti tabler-clipboard-list"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $pelanggaran->siswa->pelanggaranSiswa()->count() }}</div>
        <div class="das-stat-card__label">Total Kasus</div>
      </div>
    </div>
    {{-- Card 4 (Success): Jumlah Peringatan Diterbitkan --}}
    <div class="das-stat-card das-stat-card--success">
      <div class="das-stat-card__icon"><i class="ti tabler-mail-opened"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $pelanggaran->siswa->pelanggaranSp()->count() }}</div>
        <div class="das-stat-card__label">Surat SP</div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="das-panel mb-4">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot --info"></span>
          Rincian Informasi Pelanggaran
        </div>
      </div>
      <div class="das-panel__body">
        <ul class="list-unstyled mb-0">
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Nama Lengkap Siswa</span>
            <span class="text-white fw-bold">{{ $pelanggaran->siswa->nama_lengkap }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">NIS / NISN</span>
            <span class="text-white fw-bold">{{ $pelanggaran->siswa->nis }} / {{ $pelanggaran->siswa->nisn ?: '-' }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Kelas / Tahun Akademik</span>
            <div>
              <span class="badge bg-secondary me-2">{{ $pelanggaran->siswa->kelas?->nama ?: 'Tidak Ada Kelas' }}</span>
              <span class="text-white fw-bold">{{ $pelanggaran->tahunAkademik?->nama }} ({{ ucfirst($pelanggaran->tahunAkademik?->semester) }})</span>
            </div>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Kategori Pelanggaran</span>
            <span class="das-chip --info">{{ $pelanggaran->jenisPelanggaran?->kategori?->nama }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Jenis Pelanggaran</span>
            <span class="text-white fw-bold text-end" style="max-width: 60%;">{{ $pelanggaran->jenisPelanggaran?->nama }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Poin Pelanggaran</span>
            <span class="das-chip --danger">+{{ $pelanggaran->poin_saat_itu }} Poin</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Tanggal Kejadian</span>
            <span class="text-white fw-bold">{{ $pelanggaran->tanggal_kejadian->translatedFormat('d F Y') }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Dicatat Oleh / Pada</span>
            <span class="text-white fw-bold">
              {{ $pelanggaran->pencatat?->name ?: 'System' }} 
              <span class="text-muted font-monospace small">({{ $pelanggaran->created_at->format('d-m-Y H:i') }} WIB)</span>
            </span>
          </li>
          <li class="d-flex flex-column pt-2">
            <span class="text-muted small mb-2">Keterangan / Kronologi</span>
            <div class="text-white p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); white-space: pre-line;">{{ $pelanggaran->keterangan }}</div>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="das-panel mb-4 text-center">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot --warning"></span>
          Bukti Lampiran Foto
        </div>
      </div>
      <div class="das-panel__body">
        @if($pelanggaran->fotos->count() > 0)
          @php $foto = $pelanggaran->fotos->first(); @endphp
          <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#lightboxModal" class="d-block overflow-hidden rounded mb-2">
            <img src="{{ route('admin.pelanggaran.stream-foto', $foto->id) }}" 
                 alt="Foto Bukti" 
                 class="img-fluid rounded border border-secondary border-opacity-20 cursor-pointer hover-zoom"
                 style="max-height: 250px; object-fit: cover; transition: transform 0.2s;">
          </a>
          <span class="text-muted small mt-2 d-block"><i class="ti tabler-zoom-in"></i> Klik gambar untuk memperbesar</span>
        @else
          <div class="py-5 text-muted">
            <i class="ti tabler-photo-off fs-1 text-secondary mb-3"></i>
            <p class="mb-0 small">Tidak ada lampiran foto bukti untuk pelanggaran ini.</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Lightbox Modal --}}
@if($pelanggaran->fotos->count() > 0)
  <div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content bg-transparent border-0">
        <div class="modal-body text-center p-0 position-relative">
          <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1050;"></button>
          <img src="{{ route('admin.pelanggaran.stream-foto', $pelanggaran->fotos->first()->id) }}" 
               alt="Foto Bukti Besar" 
               class="img-fluid rounded shadow-lg"
               style="max-height: 85vh; object-fit: contain;">
        </div>
      </div>
    </div>
  </div>
@endif

@endsection
