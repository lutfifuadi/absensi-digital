@extends('layouts/layoutMaster')

@section('title', 'Rekap Kehadiran Harian Siswa — Guru Piket')

@section('page-style')
  <style>
    body {
      font-family: 'Product Sans', 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }
    .rekap-card {
      border: 1px solid rgba(0, 0, 0, 0.08);
      border-radius: 8px;
    }
    .font-product-sans {
      font-family: 'Product Sans', sans-serif;
    }
  </style>
@endsection

@section('content')
  <div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
      <h4 class="py-3 mb-0 fw-bold font-product-sans">
        <span class="text-muted fw-light">Piket /</span> Rekap Kehadiran Harian
      </h4>
      <div class="text-muted font-product-sans" style="font-size: 0.9rem;">
        Tanggal Berjalan: <span class="badge bg-primary fs-6">{{ now()->translatedFormat('d F Y') }}</span>
      </div>
    </div>
  </div>

  <div class="card rekap-card mb-4">
    <div class="card-header border-bottom py-3">
      <h5 class="card-title mb-0 font-product-sans fw-bold">Filter Rekap Kehadiran</h5>
    </div>
    <div class="card-body py-3">
      <form action="{{ route('piket.rekap') }}" method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label text-muted small fw-bold">Tanggal</label>
          <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-3">
          <label class="form-label text-muted small fw-bold">Kelas</label>
          <select name="kelas_id" class="form-select" onchange="this.form.submit()">
            <option value="">-- Semua Kelas --</option>
            @foreach($kelas as $k)
              <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label text-muted small fw-bold">Status Kehadiran</label>
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">-- Semua Status --</option>
            <option value="belum_presensi" {{ $status == 'belum_presensi' ? 'selected' : '' }}>Belum Presensi</option>
            <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir</option>
            <option value="terlambat" {{ $status == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
            <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
            <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>Izin</option>
            <option value="alpha" {{ $status == 'alpha' ? 'selected' : '' }}>Alpha</option>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <a href="{{ route('piket.rekap') }}" class="btn btn-outline-secondary w-100 font-product-sans">
            <i class="ti tabler-refresh me-1"></i> Reset Filter
          </a>
        </div>
      </form>
    </div>
  </div>

  <div class="card rekap-card">
    <div class="table-responsive text-nowrap">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th class="font-product-sans fw-bold">No</th>
            <th class="font-product-sans fw-bold">Nama Siswa</th>
            <th class="font-product-sans fw-bold">NIS / NISN</th>
            <th class="font-product-sans fw-bold">Kelas</th>
            <th class="font-product-sans fw-bold">Jam Masuk</th>
            <th class="font-product-sans fw-bold">Jam Pulang</th>
            <th class="font-product-sans fw-bold">Status</th>
            <th class="font-product-sans fw-bold">Keterangan</th>
            <th class="font-product-sans fw-bold text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse($siswaList as $index => $siswa)
            @php
              $absensi = $siswa->absensi->first();
              $statusBadge = 'bg-secondary';
              $statusText = 'Belum Presensi';
              
              if ($absensi) {
                switch($absensi->status) {
                  case 'hadir':
                    $statusBadge = 'bg-success';
                    $statusText = 'Hadir';
                    break;
                  case 'terlambat':
                    $statusBadge = 'bg-warning text-dark';
                    $statusText = 'Terlambat';
                    break;
                  case 'sakit':
                    $statusBadge = 'bg-info';
                    $statusText = 'Sakit';
                    break;
                  case 'izin':
                    $statusBadge = 'bg-primary';
                    $statusText = 'Izin';
                    break;
                  case 'alpha':
                    $statusBadge = 'bg-danger';
                    $statusText = 'Alpha';
                    break;
                }
              }
            @endphp
            <tr>
              <td>{{ $siswaList->firstItem() + $index }}</td>
              <td class="fw-bold">{{ $siswa->nama_lengkap }}</td>
              <td>{{ $siswa->nis }} / {{ $siswa->nisn ?: '-' }}</td>
              <td>{{ $siswa->kelas->nama ?? '-' }}</td>
              <td>{{ $absensi && $absensi->jam_masuk ? $absensi->jam_masuk : '-' }}</td>
              <td>{{ $absensi && $absensi->jam_pulang ? $absensi->jam_pulang : '-' }}</td>
              <td><span class="badge {{ $statusBadge }}">{{ $statusText }}</span></td>
              <td>
                <span class="text-wrap" style="max-width: 200px; display: inline-block;">
                  {{ $absensi && $absensi->keterangan ? $absensi->keterangan : '-' }}
                </span>
              </td>
              <td class="text-center">
                @if($tanggal === now()->toDateString())
                  <button type="button" class="btn btn-sm btn-icon btn-outline-primary btn-edit-status" 
                          data-id="{{ $siswa->id }}"
                          data-nama="{{ $siswa->nama_lengkap }}"
                          data-status="{{ $absensi ? $absensi->status : 'belum_presensi' }}"
                          data-keterangan="{{ $absensi ? $absensi->keterangan : '' }}"
                          title="Ubah Status Kehadiran">
                    <i class="ti tabler-edit"></i>
                  </button>
                @else
                  <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" disabled title="Hanya dapat mengedit data hari ini">
                    <i class="ti tabler-lock"></i>
                  </button>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">
                <i class="ti tabler-users fs-1 mb-2 d-block"></i>
                Tidak ada data siswa ditemukan
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($siswaList->hasPages())
      <div class="card-footer py-3 d-flex justify-content-between align-items-center">
        <div class="small text-muted font-product-sans">
          Menampilkan {{ $siswaList->firstItem() }} sampai {{ $siswaList->lastItem() }} dari {{ $siswaList->total() }} data
        </div>
        <div>
          {{ $siswaList->links() }}
        </div>
      </div>
    @endif
  </div>

  <!-- Modal Edit Status Kehadiran -->
  <div class="modal fade" id="modalEditStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form id="formEditStatus" class="modal-content">
        @csrf
        <input type="hidden" name="siswa_id" id="editSiswaId">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
        
        <div class="modal-header">
          <h5 class="modal-title font-product-sans fw-bold">Ubah Status Kehadiran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Nama Siswa</label>
            <input type="text" id="editSiswaNama" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Status Kehadiran</label>
            <select name="status" id="editSiswaStatus" class="form-select" required>
              <option value="belum_presensi">Belum Presensi (Hapus Absensi)</option>
              <option value="hadir">Hadir</option>
              <option value="terlambat">Terlambat</option>
              <option value="sakit">Sakit</option>
              <option value="izin">Izin</option>
              <option value="alpha">Alpha</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Keterangan / Alasan</label>
            <textarea name="keterangan" id="editSiswaKeterangan" class="form-control" rows="3" placeholder="Masukkan keterangan (opsional, misal: Surat dokter dilampirkan, dll)"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary font-product-sans" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary font-product-sans">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    const editModal = new bootstrap.Modal(document.getElementById('modalEditStatus'));
    
    document.querySelectorAll('.btn-edit-status').forEach(button => {
      button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const nama = this.getAttribute('data-nama');
        const status = this.getAttribute('data-status');
        const keterangan = this.getAttribute('data-keterangan');

        document.getElementById('editSiswaId').value = id;
        document.getElementById('editSiswaNama').value = nama;
        document.getElementById('editSiswaStatus').value = status;
        document.getElementById('editSiswaKeterangan').value = keterangan;
        
        editModal.show();
      });
    });

    document.getElementById('formEditStatus').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const form = this;
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';
      
      try {
        const formData = new FormData(form);
        const response = await fetch("{{ route('piket.rekap.update') }}", {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
          editModal.hide();
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: data.message,
            customClass: {
              confirmButton: 'btn btn-primary font-product-sans'
            },
            buttonsStyling: false
          }).then(() => {
            window.location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: data.message || 'Terjadi kesalahan saat memperbarui status.',
            customClass: {
              confirmButton: 'btn btn-primary font-product-sans'
            },
            buttonsStyling: false
          });
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Gagal menghubungi server.',
          customClass: {
            confirmButton: 'btn btn-primary font-product-sans'
          },
          buttonsStyling: false
        });
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });
  </script>
@endsection
