@extends('layouts/layoutMaster')

@section('title', 'Detail Catatan Pelanggaran')

@section('page-style')
  <style>
    .custom-card-header {
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    .detail-item {
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      padding: 12px 0;
    }

    .detail-item:last-child {
      border-bottom: none;
    }

    /* Lightbox Modal Style */
    .lightbox-modal .modal-content {
      background: transparent;
      border: none;
    }
  </style>
@endsection

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h4 class="fw-bold mb-1"><span class="text-muted fw-light">Kesiswaan /</span> Detail Pelanggaran</h4>
        <p class="text-muted mb-0">Rincian lengkap riwayat pelanggaran ananda beserta bukti lampiran.</p>
      </div>
      <div>
        <a href="{{ route('admin.pelanggaran.index') }}" class="btn btn-secondary d-flex align-items-center gap-2">
          <i class="ti ti-arrow-left"></i> Kembali ke Riwayat
        </a>
      </div>
    </div>

    <div class="row g-4">
      <!-- Data Rincian Pelanggaran -->
      <div class="col-lg-8">
        <div class="card bg-glass border-light shadow-sm">
          <div class="card-header custom-card-header text-white">
            <h5 class="card-title mb-0"><i class="ti ti-info-circle text-info me-2"></i> Rincian Informasi</h5>
          </div>
          <div class="card-body pt-4">
            <div class="detail-item row">
              <div class="col-sm-4 text-muted small fw-medium">Nama Lengkap Siswa</div>
              <div class="col-sm-8 text-white fw-semibold">{{ $pelanggaran->siswa->nama_lengkap }}</div>
            </div>

            <div class="detail-item row">
              <div class="col-sm-4 text-muted small fw-medium">NIS / NISN</div>
              <div class="col-sm-8 text-white">{{ $pelanggaran->siswa->nis }} / {{ $pelanggaran->siswa->nisn ?: '-' }}</div>
            </div>

            <div class="detail-item row">
              <div class="col-sm-4 text-muted small fw-medium">Kelas / Tahun Akademik</div>
              <div class="col-sm-8 text-white">
                <span class="badge bg-secondary me-2">{{ $pelanggaran->siswa->kelas?->nama ?: 'Tidak Ada Kelas' }}</span>
                <span class="small text-muted">{{ $pelanggaran->tahunAkademik?->nama }} ({{ ucfirst($pelanggaran->tahunAkademik?->semester) }})</span>
              </div>
            </div>

            <div class="detail-item row">
              <div class="col-sm-4 text-muted small fw-medium">Kategori Pelanggaran</div>
              <div class="col-sm-8">
                <span class="badge bg-label-info">{{ $pelanggaran->jenisPelanggaran?->kategori?->nama }}</span>
              </div>
            </div>

            <div class="detail-item row">
              <div class="col-sm-4 text-muted small fw-medium">Jenis Pelanggaran</div>
              <div class="col-sm-8 text-white fw-medium">{{ $pelanggaran->jenisPelanggaran?->nama }}</div>
            </div>

            <div class="detail-item row">
              <div class="col-sm-4 text-muted small fw-medium">Poin Pelanggaran</div>
              <div class="col-sm-8">
                <span class="badge bg-danger rounded-pill fw-bold">+{{ $pelanggaran->poin_saat_itu }} Poin</span>
              </div>
            </div>

            <div class="detail-item row">
              <div class="col-sm-4 text-muted small fw-medium">Tanggal Kejadian</div>
              <div class="col-sm-8 text-white">{{ $pelanggaran->tanggal_kejadian->format('d F Y') }}</div>
            </div>

            <div class="detail-item row">
              <div class="col-sm-4 text-muted small fw-medium">Keterangan / Kronologi</div>
              <div class="col-sm-8 text-white text-wrap" style="white-space: pre-line;">{{ $pelanggaran->keterangan }}</div>
            </div>

            <div class="detail-item row">
              <div class="col-sm-4 text-muted small fw-medium">Dicatat Oleh / Pada</div>
              <div class="col-sm-8 text-white small">
                {{ $pelanggaran->pencatat?->name ?: 'System' }} 
                <span class="text-muted ms-2">({{ $pelanggaran->created_at->format('d-m-Y H:i') }} WIB)</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Lampiran Foto Bukti -->
      <div class="col-lg-4">
        <div class="card bg-glass border-light shadow-sm text-center">
          <div class="card-header custom-card-header text-white">
            <h5 class="card-title mb-0"><i class="ti ti-photo text-warning me-2"></i> Lampiran Foto Bukti</h5>
          </div>
          <div class="card-body pt-4">
            @if($pelanggaran->fotos->count() > 0)
              @php $foto = $pelanggaran->fotos->first(); @endphp
              <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#lightboxModal">
                <img src="{{ route('admin.pelanggaran.stream-foto', $foto->id) }}" 
                     alt="Foto Bukti" 
                     class="img-fluid rounded border border-light shadow-sm cursor-pointer hover-zoom"
                     style="max-height: 250px; object-fit: contain;">
              </a>
              <span class="text-muted small mt-2 d-block">Klik gambar untuk memperbesar</span>
            @else
              <div class="py-5 text-muted">
                <i class="ti ti-photo-off fs-1 text-secondary mb-3"></i>
                <p class="mb-0">Tidak ada lampiran foto bukti untuk pelanggaran ini.</p>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Lightbox Modal -->
  @if($pelanggaran->fotos->count() > 0)
    <div class="modal fade lightbox-modal" id="lightboxModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
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
