@extends('layouts/layoutMaster')

@section('title', 'Detail Tiket Izin Pulang Cepat')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
<style>
  @media print {
    body * {
      visibility: hidden;
    }
    #printArea, #printArea * {
      visibility: visible;
    }
    #printArea {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
    }
    .no-print {
      display: none !important;
    }
  }
</style>
@endsection

@section('content')
<h4 class="py-3 mb-4 no-print">
  <span class="text-muted fw-light">Admin / Izin Pulang Cepat /</span> Detail Tiket
</h4>

<div class="row">
  <div class="col-12 col-lg-8">
    <!-- Card Utama Tiket -->
    <div class="card mb-4" id="printArea">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h5 class="mb-0 fw-bold">TIKET IZIN PULANG CEPAT</h5>
          <small class="text-muted">{{ $izin->kode_izin }}</small>
        </div>
        <div class="d-flex align-items-center gap-2">
          @switch($izin->status)
            @case('pending')
              <span class="badge bg-warning">Pending</span>
              @break
            @case('approved')
              <span class="badge bg-success">Approved</span>
              @break
            @case('completed')
              <span class="badge bg-info">Completed</span>
              @break
            @case('rejected')
              <span class="badge bg-danger">Rejected</span>
              @break
            @case('cancelled')
              <span class="badge bg-secondary">Cancelled</span>
              @break
          @endswitch
        </div>
      </div>

      <div class="card-body pt-4">
        <div class="row g-4">
          <!-- Kolom Informasi Subjek -->
          <div class="col-12 col-md-7">
            <h6 class="text-muted text-uppercase fs-tiny fw-semibold mb-3">Informasi Pengaju</h6>
            <div class="d-flex align-items-center mb-4">
              <div>
                <h5 class="mb-0 fw-bold">{{ $reference?->nama_lengkap ?? $izin->user?->name ?? '-' }}</h5>
                <span class="badge bg-label-info mt-1 text-capitalize">{{ $izin->kategori }}</span>
                @if($izin->kategori === 'siswa' && isset($reference?->kelas))
                  <span class="badge bg-label-secondary mt-1">{{ $reference->kelas->nama_kelas }}</span>
                @endif
              </div>
            </div>

            <table class="table table-sm table-borderless">
              <tr>
                <td width="35%" class="fw-semibold text-muted">Tanggal Izin</td>
                <td>: {{ $izin->tanggal ? $izin->tanggal->format('d F Y') : '-' }}</td>
              </tr>
              <tr>
                <td class="fw-semibold text-muted">Rencana Keluar</td>
                <td>: {{ $izin->jam_rencana_keluar }} WIB</td>
              </tr>
              <tr>
                <td class="fw-semibold text-muted">Realisasi Keluar</td>
                <td>: {{ $izin->jam_realisasi_keluar ?? '-' }}</td>
              </tr>
              <tr>
                <td class="fw-semibold text-muted">Kategori Alasan</td>
                <td>: <span class="text-capitalize">{{ str_replace('_', ' ', $izin->jenis_alasan) }}</span></td>
              </tr>
              <tr>
                <td class="fw-semibold text-muted">Detail Alasan</td>
                <td>: {{ $izin->alasan }}</td>
              </tr>
            </table>

            @if($izin->kategori === 'siswa')
              <h6 class="text-muted text-uppercase fs-tiny fw-semibold mt-4 mb-3">Informasi Penjemputan</h6>
              <table class="table table-sm table-borderless">
                <tr>
                  <td width="35%" class="fw-semibold text-muted">Nama Penjemput</td>
                  <td>: {{ $izin->nama_penjemput ?? '-' }}</td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">No. HP Penjemput</td>
                  <td>: {{ $izin->no_hp_penjemput ?? '-' }}</td>
                </tr>
              </table>
            @endif
          </div>

          <!-- Kolom QR Code / Barcode Tiket -->
          <div class="col-12 col-md-5 d-flex flex-column align-items-center justify-content-center border-start">
            @if($qrCode)
              <div class="p-3 bg-white rounded border mb-2">
                {!! $qrCode !!}
              </div>
              <small class="text-muted text-center text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Scan Untuk Verifikasi Satpam</small>
              <div class="fw-bold text-dark fs-5">{{ $izin->kode_izin }}</div>
            @else
              <div class="text-center text-muted py-5">
                <i class="ti ti-qrcode fs-1"></i>
                <div class="mt-2">QR Code Tidak Tersedia</div>
              </div>
            @endif
          </div>
        </div>

        <!-- Bagian Approver & Verifikator Satpam -->
        <div class="row mt-4 pt-4 border-top">
          <div class="col-6">
            <h6 class="text-muted text-uppercase fs-tiny fw-semibold">Disetujui Oleh</h6>
            @if($izin->approver)
              <div class="fw-semibold">{{ $izin->approver->name }}</div>
              <small class="text-muted">{{ $izin->disetujui_pada ? $izin->disetujui_pada->format('d/m/Y H:i') : '-' }}</small>
            @else
              <span class="text-muted italic">- Belum Disetujui -</span>
            @endif
            @if($izin->catatan_approver)
              <div class="mt-2 p-2 bg-light rounded text-muted" style="font-size: 0.85rem;">
                <strong>Catatan:</strong> {{ $izin->catatan_approver }}
              </div>
            @endif
          </div>
          <div class="col-6 border-start">
            <h6 class="text-muted text-uppercase fs-tiny fw-semibold">Diverifikasi Satpam</h6>
            @if($izin->satpam)
              <div class="fw-semibold">{{ $izin->satpam->name }}</div>
              <small class="text-muted">{{ $izin->diverifikasi_satpam_pada ? $izin->diverifikasi_satpam_pada->format('d/m/Y H:i') : '-' }}</small>
            @else
              <span class="text-muted italic">- Belum Diverifikasi Satpam -</span>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4 no-print">
    <!-- Panel Aksi -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Aksi Tiket</h5>
      </div>
      <div class="card-body d-flex flex-column gap-2">
        @if($izin->status === 'pending')
          <button type="button" class="btn btn-success w-100" onclick="confirmApprove('{{ route('admin.izin-pulang-cepat.approve', $izin->id) }}', '{{ $izin->kode_izin }}')">
            <i class="ti ti-check me-2"></i> Setujui Pengajuan
          </button>
          <button type="button" class="btn btn-danger w-100" onclick="openRejectModal('{{ route('admin.izin-pulang-cepat.reject', $izin->id) }}', '{{ $izin->kode_izin }}')">
            <i class="ti ti-x me-2"></i> Tolak Pengajuan
          </button>
        @endif

        <button type="button" class="btn btn-outline-secondary w-100" onclick="window.print()">
          <i class="ti ti-printer me-2"></i> Cetak / Print Tiket
        </button>

        @if($izin->lampiran)
          <a href="{{ asset('storage/' . $izin->lampiran) }}" target="_blank" class="btn btn-outline-info w-100">
            <i class="ti ti-file-text me-2"></i> Lihat Lampiran
          </a>
        @endif

        <a href="{{ route('admin.izin-pulang-cepat.index') }}" class="btn btn-label-secondary w-100">
          Kembali ke Daftar
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Modal Reject -->
<div class="modal fade no-print" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="rejectForm" method="POST" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-header-title mb-0">Tolak Pengajuan Izin (<span id="rejectKodeIzin"></span>)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label required">Catatan Penolakan</label>
          <textarea name="catatan_approver" class="form-control" rows="3" placeholder="Masukkan alasan penolakan..." required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak Permohonan</button>
      </div>
    </form>
  </div>
</div>

<form id="approveForm" method="POST" style="display:none;">
  @csrf
</form>
@endsection

@section('page-script')
<script>
  function confirmApprove(url, kode) {
    Swal.fire({
      title: 'Setujui Izin?',
      text: 'Setujui izin pulang cepat dengan kode ' + kode + '?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Setujui',
      cancelButtonText: 'Batal',
      customClass: {
        confirmButton: 'btn btn-success me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        const form = document.getElementById('approveForm');
        form.action = url;
        form.submit();
      }
    });
  }

  function openRejectModal(url, kode) {
    document.getElementById('rejectKodeIzin').textContent = kode;
    const form = document.getElementById('rejectForm');
    form.action = url;
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
  }
</script>
@endsection
