@extends('layouts/layoutMaster')

@section('title', 'Daftar Izin Pulang Cepat')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Admin /</span> Izin Pulang Cepat
</h4>

@if(session('success'))
  <div class="alert alert-success alert-dismissible" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger alert-dismissible" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<div class="card">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <h5 class="mb-0">Daftar Pengajuan Izin Pulang Cepat</h5>
    <a href="{{ route('admin.izin-pulang-cepat.create') }}" class="btn btn-primary">
      <i class="ti ti-plus me-1"></i> Buat Pengajuan Baru
    </a>
  </div>

  <div class="card-body border-bottom">
    <form method="GET" action="{{ route('admin.izin-pulang-cepat.index') }}" class="row g-3">
      <div class="col-12 col-md-3">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label">Kategori</label>
        <select name="kategori" class="form-select">
          <option value="">-- Semua Kategori --</option>
          <option value="siswa" {{ request('kategori') == 'siswa' ? 'selected' : '' }}>Siswa</option>
          <option value="guru" {{ request('kategori') == 'guru' ? 'selected' : '' }}>Guru</option>
          <option value="staff" {{ request('kategori') == 'staff' ? 'selected' : '' }}>Staff TU</option>
        </select>
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">-- Semua Status --</option>
          <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
          <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
          <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
          <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
          <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label">Cari (Kode/Alasan/User)</label>
        <div class="input-group">
          <input type="text" name="search" class="form-control" placeholder="Cari..." value="{{ request('search') }}">
          <button type="submit" class="btn btn-outline-primary"><i class="ti ti-search"></i></button>
        </div>
      </div>
    </form>
  </div>

  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Kode Izin</th>
          <th>Pengaju</th>
          <th>Kategori</th>
          <th>Tanggal & Jam Rencana</th>
          <th>Alasan</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse($izinPulangCepat as $izin)
          <tr>
            <td>
              <a href="{{ route('admin.izin-pulang-cepat.show', $izin->id) }}" class="fw-bold">
                {{ $izin->kode_izin }}
              </a>
            </td>
            <td>
              <div class="d-flex flex-column">
                <span class="fw-semibold">{{ $izin->user?->name ?? '-' }}</span>
                <small class="text-muted">{{ $izin->user?->username }}</small>
              </div>
            </td>
            <td>
              <span class="badge bg-label-info text-capitalize">{{ $izin->kategori }}</span>
            </td>
            <td>
              <div>{{ $izin->tanggal ? $izin->tanggal->format('d/m/Y') : '-' }}</div>
              <small class="text-muted"><i class="ti ti-clock me-1"></i>{{ $izin->jam_rencana_keluar }}</small>
            </td>
            <td>
              <span class="badge bg-label-secondary mb-1 text-capitalize">{{ str_replace('_', ' ', $izin->jenis_alasan) }}</span>
              <div class="text-truncate" style="max-width: 200px;" title="{{ $izin->alasan }}">
                {{ $izin->alasan }}
              </div>
            </td>
            <td>
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
                @default
                  <span class="badge bg-secondary">{{ $izin->status }}</span>
              @endswitch
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.izin-pulang-cepat.show', $izin->id) }}" class="btn btn-sm btn-icon btn-label-info" title="Detail / Scan Tiket">
                  <i class="ti ti-eye"></i>
                </a>

                @if($izin->status === 'pending')
                  <button type="button" class="btn btn-sm btn-icon btn-label-success" title="Setujui" onclick="confirmApprove('{{ route('admin.izin-pulang-cepat.approve', $izin->id) }}', '{{ $izin->kode_izin }}')">
                    <i class="ti ti-check"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-icon btn-label-danger" title="Tolak" onclick="openRejectModal('{{ route('admin.izin-pulang-cepat.reject', $izin->id) }}', '{{ $izin->kode_izin }}')">
                    <i class="ti ti-x"></i>
                  </button>
                @endif

                <button type="button" class="btn btn-sm btn-icon btn-label-secondary" title="Hapus" onclick="confirmDelete('{{ route('admin.izin-pulang-cepat.destroy', $izin->id) }}', '{{ $izin->kode_izin }}')">
                  <i class="ti ti-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-4">Tidak ada data izin pulang cepat.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($izinPulangCepat->hasPages())
    <div class="card-footer">
      {{ $izinPulangCepat->links() }}
    </div>
  @endif
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
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

<form id="deleteForm" method="POST" style="display:none;">
  @csrf
  @method('DELETE')
</form>

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

  function confirmDelete(url, kode) {
    Swal.fire({
      title: 'Hapus/Batal Izin?',
      text: 'Penghapusan izin ' + kode + ' tidak dapat dikembalikan!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      customClass: {
        confirmButton: 'btn btn-danger me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
      }
    });
  }
</script>
@endsection
