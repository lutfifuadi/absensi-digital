@extends('layouts/layoutMaster')

@section('title', 'Catatan Pelanggaran — ' . $anak->nama_lengkap)

@section('content')

{{-- ── HERO HEADER ────────────────────────────────── --}}
<div class="card bg-danger text-white mb-4 overflow-hidden position-relative" style="border-radius: 12px; background: linear-gradient(135deg, #450a0a 0%, #7f1d1d 40%, #dc2626 100%) !important;">
  <div class="card-body p-4 position-relative" style="z-index: 2;">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="avatar avatar-lg bg-white bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
          <i class="ti tabler-alert-triangle text-white fs-2"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1 mb-1" style="font-size:0.75rem;">
              <li class="breadcrumb-item"><a href="{{ route('ortu.dashboard') }}" class="text-white opacity-75">Dashboard</a></li>
              <li class="breadcrumb-item active text-white fw-semibold">Pelanggaran Siswa</li>
            </ol>
          </nav>
          <h4 class="text-white mb-0 fw-bold">Catatan & Poin Pelanggaran Anak</h4>
          <p class="text-white opacity-75 small mb-0 mt-1">
            <i class="ti tabler-user me-1"></i>{{ $anak->nama_lengkap }} ({{ $anak->kelas->nama ?? 'Tanpa Kelas' }})
          </p>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('ortu.dashboard') }}" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
          <i class="ti tabler-arrow-left"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</div>

{{-- ── STATS CARDS ────────────────────────────────── --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="avatar avatar-md {{ $totalPoin > 0 ? 'bg-label-danger' : 'bg-label-success' }} rounded p-2">
          <i class="ti tabler-shield-alert fs-3"></i>
        </div>
        <div>
          <h4 class="mb-0 fw-bold {{ $totalPoin > 0 ? 'text-danger' : 'text-success' }}">{{ $totalPoin }} Poin</h4>
          <small class="text-muted">Akumulasi Poin Pelanggaran</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="avatar avatar-md bg-label-warning rounded p-2">
          <i class="ti tabler-clipboard-list fs-3"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-bold text-warning">{{ $pelanggarans->count() }}</h5>
          <small class="text-muted">Total Kejadian</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3 d-flex align-items-center gap-3">
        <div class="avatar avatar-md bg-label-primary rounded p-2">
          <i class="ti tabler-file-certificate fs-3"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-bold text-primary">{{ $spList->count() }}</h5>
          <small class="text-muted">Surat Peringatan (SP)</small>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── RIWAYAT SURAT PERINGATAN (JIKA ADA) ────────────────────────────────── --}}
@if($spList->isNotEmpty())
  <div class="card border-0 shadow-sm mb-4 border-start border-danger border-4">
    <div class="card-header bg-transparent py-3">
      <h5 class="card-title mb-0 text-danger fw-bold d-flex align-items-center gap-2">
        <i class="ti tabler-file-alert fs-4"></i> Riwayat Surat Peringatan (SP)
      </h5>
    </div>
    <div class="card-body pt-0">
      <div class="row g-3">
        @foreach($spList as $sp)
          <div class="col-md-6">
            <div class="p-3 bg-label-danger rounded border border-danger border-opacity-25">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="badge bg-danger text-uppercase">{{ $sp->jenis_sp }}</span>
                <small class="text-muted"><i class="ti tabler-calendar me-1"></i>{{ \Carbon\Carbon::parse($sp->created_at)->locale('id')->translatedFormat('d M Y') }}</small>
              </div>
              <p class="mb-1 fw-bold text-dark" style="font-size:0.9rem;">{{ $sp->judul ?? 'Surat Peringatan' }}</p>
              <small class="text-muted d-block">{{ $sp->keterangan ?? 'Harap menjadi perhatian bagi orang tua/wali siswa.' }}</small>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
@endif

{{-- ── RIWAYAT CATATAN PELANGGARAN ────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3">
    <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
      <i class="ti tabler-list-details text-danger fs-4"></i> Daftar Pelanggaran Siswa
    </h5>
    <span class="badge bg-label-danger rounded-pill">{{ $pelanggarans->count() }} Catatan</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Tanggal</th>
          <th>Kategori & Jenis Pelanggaran</th>
          <th>Poin</th>
          <th>Dicatat Oleh</th>
          <th>Keterangan / Bukti</th>
        </tr>
      </thead>
      <tbody>
        @forelse($pelanggarans as $index => $p)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>
              <div class="fw-semibold">{{ \Carbon\Carbon::parse($p->tanggal_kejadian)->locale('id')->translatedFormat('d M Y') }}</div>
              <small class="text-muted">{{ \Carbon\Carbon::parse($p->created_at)->format('H:i') }} WIB</small>
            </td>
            <td>
              <div class="badge bg-label-secondary mb-1" style="font-size:0.7rem;">
                {{ $p->jenisPelanggaran?->kategori?->nama_kategori ?? 'Umum' }}
              </div>
              <div class="fw-bold text-dark">{{ $p->jenisPelanggaran?->nama_pelanggaran ?? 'Pelanggaran Tata Tertib' }}</div>
            </td>
            <td>
              <span class="badge bg-danger fs-6 px-2 py-1">+{{ $p->poin_saat_itu ?? $p->jenisPelanggaran?->poin ?? 0 }}</span>
            </td>
            <td>
              <div class="small fw-semibold">{{ $p->pencatat?->name ?? 'Guru BK / Piket' }}</div>
            </td>
            <td>
              <div>{{ $p->keterangan ?: '-' }}</div>
              @if($p->fotos->isNotEmpty())
                <div class="d-flex gap-2 mt-2">
                  @foreach($p->fotos as $foto)
                    <a href="{{ route('admin.pelanggaran.stream-foto', $foto->id) }}" target="_blank" class="d-inline-block">
                      <img src="{{ route('admin.pelanggaran.stream-foto', $foto->id) }}" alt="Foto Pelanggaran" class="rounded border" style="width:48px;height:48px;object-fit:cover;">
                    </a>
                  @endforeach
                </div>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center py-5">
              <div class="text-muted">
                <i class="ti tabler-shield-check fs-1 d-block mb-2 text-success"></i>
                <p class="mb-0 fw-semibold text-success">Alhamdulillah, anak Anda tidak memiliki catatan pelanggaran.</p>
                <small class="text-muted">Tetap jaga kedisiplinan dan pertahankan prestasi terbaik anak Anda.</small>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
