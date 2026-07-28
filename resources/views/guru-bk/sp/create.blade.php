@extends('layouts/layoutMaster')

@section('title', 'Terbitkan Surat Peringatan (SP)')

@section('content')
  <div class="mb-4">
    <a href="{{ route('bk.sp.index') }}" class="btn btn-label-secondary btn-sm mb-2">
      <i class="ti tabler-arrow-left me-1"></i> Kembali
    </a>
    <h4 class="fw-bold mb-1">Terbitkan Surat Peringatan (SP)</h4>
    <p class="text-muted mb-0">Penerbitan sanksi formal SP1, SP2, atau SP3 secara berurutan</p>
  </div>

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible mb-4">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible mb-4">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row">
    <div class="col-md-7">
      <div class="card mb-4">
        <div class="card-body p-4">
          <form action="{{ route('bk.sp.store') }}" method="POST">
            @csrf

            <div class="mb-3">
              <label class="form-label fw-semibold">Pilih Siswa Target <span class="text-danger">*</span></label>
              <select name="siswa_id" class="form-select select2" onchange="window.location.href='{{ route('bk.sp.create') }}?siswa_id='+this.value" required>
                <option value="">-- Pilih Siswa --</option>
                @foreach($siswas as $siswa)
                  <option value="{{ $siswa->id }}" {{ (old('siswa_id', $selectedSiswaId) == $siswa->id) ? 'selected' : '' }}>
                    {{ $siswa->nama_lengkap }} (NIS: {{ $siswa->nis ?? '-' }}) - Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Tingkat SP <span class="text-danger">*</span></label>
                <select name="level_sp" class="form-select" required>
                  <option value="SP1" {{ old('level_sp') == 'SP1' ? 'selected' : '' }}>SP 1 (Surat Peringatan I)</option>
                  <option value="SP2" {{ old('level_sp') == 'SP2' ? 'selected' : '' }}>SP 2 (Surat Peringatan II)</option>
                  <option value="SP3" {{ old('level_sp') == 'SP3' ? 'selected' : '' }}>SP 3 (Surat Peringatan III)</option>
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Tanggal Penerbitan <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_sp" class="form-control" value="{{ old('tanggal_sp', date('Y-m-d')) }}" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Alasan / Catatan Penerbitan SP <span class="text-danger">*</span></label>
              <textarea name="catatan_tambahan" class="form-control" rows="4" placeholder="Jelaskan dasar pertimbangan pembinaan dan pelanggaran yang mendasari penerbitan SP ini..." required>{{ old('catatan_tambahan') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="{{ route('bk.sp.index') }}" class="btn btn-label-secondary">Batal</a>
              <button type="submit" class="btn btn-warning"><i class="ti tabler-file-certificate me-1"></i> Terbitkan Surat Peringatan</button>
            </div>

          </form>
        </div>
      </div>
    </div>

    {{-- Detail Siswa & Riwayat --}}
    <div class="col-md-5">
      <div class="card mb-4">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0">Ringkasan Siswa</h5>
        </div>
        <div class="card-body pt-4">
          @if($selectedSiswa)
            <div class="d-flex align-items-center mb-3">
              <div class="avatar avatar-md me-3">
                <span class="avatar-initial rounded-circle bg-label-warning fs-4">
                  {{ strtoupper(substr($selectedSiswa->nama_lengkap, 0, 2)) }}
                </span>
              </div>
              <div>
                <h6 class="mb-0 fw-bold">{{ $selectedSiswa->nama_lengkap }}</h6>
                <small class="text-muted">Kelas: {{ $selectedSiswa->kelas->nama_kelas ?? '-' }} &bull; NIS: {{ $selectedSiswa->nis ?? '-' }}</small>
              </div>
            </div>

            <div class="p-3 bg-label-danger rounded mb-3 text-center">
              <span class="d-block mb-1 text-muted">Akumulasi Total Poin Pelanggaran</span>
              <h3 class="mb-0 text-danger fw-bold">{{ $totalPoin }} Poin</h3>
            </div>

            <h6 class="fw-bold mb-2">Riwayat Pelanggaran Terakhir</h6>
            <ul class="list-group list-group-flush border-top">
              @forelse($riwayatPelanggaran as $p)
                <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                  <div>
                    <strong class="text-body d-block">{{ $p->jenisPelanggaran->nama_jenis ?? '-' }}</strong>
                    <small class="text-muted">{{ $p->tanggal_kejadian ? \Carbon\Carbon::parse($p->tanggal_kejadian)->translatedFormat('d M Y') : '-' }}</small>
                  </div>
                  <span class="badge bg-danger">+{{ $p->poin_saat_itu }}</span>
                </li>
              @empty
                <li class="list-group-item px-0 text-muted py-3 text-center">Siswa ini belum memiliki catatan pelanggaran.</li>
              @endforelse
            </ul>
          @else
            <div class="text-center py-4 text-muted">
              <i class="ti tabler-user-search fs-1 d-block mb-2"></i>
              Pilih siswa terlebih dahulu untuk melihat akumulasi poin dan riwayat pelanggaran.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
