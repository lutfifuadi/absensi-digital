@extends('layouts/layoutMaster')

@section('title', 'Rekap Kehadiran Harian Siswa — Guru Piket')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <style>
    .form-control, .form-select, .btn {
      border-radius: 5px !important;
    }

    /* PAGINATION STYLING SINKRON */
    .das-page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: 5px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: transparent;
        color: #888;
        text-decoration: none;
        transition: all 0.18s ease;
        cursor: pointer;
        line-height: 1;
        font-family: inherit;
    }

    .das-page-btn:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        border-color: rgba(255, 255, 255, 0.12);
    }

    .das-page-active {
        background: #7367f0 !important;
        color: #fff !important;
        border-color: #7367f0 !important;
    }

    .das-page-dots {
        border-color: transparent;
        background: transparent;
        color: #555;
        pointer-events: none;
    }

    .page-item.disabled .das-page-btn {
        opacity: 0.35;
        pointer-events: none;
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
            <i class="ti tabler-calendar-stats text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            Piket / Rekap Kehadiran
          </div>
          <h4 class="das-hero__title text-gradient-gold">Rekap Kehadiran Harian</h4>
          <p class="das-hero__subtitle">Audit log absensi harian dan status kehadiran siswa.</p>
        </div>
      </div>
      <div class="das-hero__actions">
        <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white">
          <i class="ti tabler-calendar me-1"></i> {{ now()->translatedFormat('d F Y') }}
        </div>
      </div>
    </div>
  </div>

  <div class="das-panel mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__head">
      <h5 class="das-panel__title mb-0 fw-bold">
        <i class="ti tabler-filter text-info me-2"></i>Filter Rekap Kehadiran
      </h5>
    </div>
    <div class="das-panel__body py-3">
      <form action="{{ route('piket.rekap') }}" method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label text-muted small fw-bold"><i class="ti tabler-calendar me-1"></i>Tanggal</label>
          <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-3">
          <label class="form-label text-muted small fw-bold"><i class="ti tabler-door me-1"></i>Kelas</label>
          <select name="kelas_id" class="form-select" onchange="this.form.submit()">
            <option value="">-- Semua Kelas --</option>
            @foreach($kelas as $k)
              <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label text-muted small fw-bold"><i class="ti tabler-circle-check me-1"></i>Status Kehadiran</label>
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
          <a href="{{ route('piket.rekap') }}" class="btn das-btn --secondary w-100">
            <i class="ti tabler-refresh me-1"></i> Reset Filter
          </a>
        </div>
      </form>
    </div>
  </div>

  <div class="das-panel mb-4">
    <div class="das-panel__head border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Kehadiran Siswa
      </h6>
      <span class="das-chip --info">Total: {{ $siswaList->total() }} Siswa</span>
    </div>
    <div class="das-table-wrap">
      <table class="das-table">
        <thead>
          <tr>
            <th class="ps-4 py-3" width="60">No</th>
            <th class="py-3">Nama Siswa</th>
            <th class="py-3">Kelas</th>
            <th class="py-3 text-center">Jam Masuk</th>
            <th class="py-3 text-center">Jam Pulang</th>
            <th class="py-3 text-center">Status</th>
            <th class="py-3">Keterangan</th>
            <th class="py-3 text-center pe-4" width="80">Aksi</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse($siswaList as $index => $siswa)
            @php
              $absensi = $siswa->absensi->first();
              $statusChip = '--dark';
              $statusText = 'Belum Presensi';
              
              if ($absensi) {
                switch($absensi->status) {
                  case 'hadir':
                    $statusChip = '--success';
                    $statusText = 'Hadir';
                    break;
                  case 'terlambat':
                    $statusChip = '--primary';
                    $statusText = 'Terlambat';
                    break;
                  case 'sakit':
                    $statusChip = '--info';
                    $statusText = 'Sakit';
                    break;
                  case 'izin':
                    $statusChip = '--warning';
                    $statusText = 'Izin';
                    break;
                  case 'alpha':
                    $statusChip = '--danger';
                    $statusText = 'Alpha';
                    break;
                }
              }
            @endphp
            <tr class="rekap-row-hover">
              <td class="ps-4">{{ $siswaList->firstItem() + $index }}</td>
              <td class="fw-bold text-nowrap">{{ $siswa->nama_lengkap }}</td>
              <td>{{ $siswa->kelas->nama ?? '-' }}</td>
              <td class="text-center">{{ $absensi && $absensi->jam_masuk ? $absensi->jam_masuk : '-' }}</td>
              <td class="text-center">{{ $absensi && $absensi->jam_pulang ? $absensi->jam_pulang : '-' }}</td>
              <td class="text-center"><span class="das-chip {{ $statusChip }}">{{ $statusText }}</span></td>
              <td>
                <span class="text-wrap" style="max-width: 200px; display: inline-block;">
                  {{ $absensi && $absensi->keterangan ? $absensi->keterangan : '-' }}
                </span>
              </td>
              <td class="text-center pe-4">
                @if($tanggal === now()->toDateString())
                  <button type="button" class="btn btn-sm btn-icon btn-label-primary btn-edit-status" 
                          data-id="{{ $siswa->id }}"
                          data-nama="{{ $siswa->nama_lengkap }}"
                          data-status="{{ $absensi ? $absensi->status : 'belum_presensi' }}"
                          data-keterangan="{{ $absensi ? $absensi->keterangan : '' }}"
                          title="Ubah Status Kehadiran">
                    <i class="ti tabler-edit"></i>
                  </button>
                @else
                  <button type="button" class="btn btn-sm btn-icon btn-label-secondary" disabled title="Hanya dapat mengedit data hari ini">
                    <i class="ti tabler-lock"></i>
                  </button>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">
                <i class="ti tabler-users fs-1 mb-2 d-block"></i>
                Tidak ada data siswa ditemukan
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($siswaList->hasPages())
      <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center" style="border-color: var(--das-border) !important;">
        <div class="small text-white-50">
          Menampilkan {{ $siswaList->firstItem() }} sampai {{ $siswaList->lastItem() }} dari {{ $siswaList->total() }} data
        </div>
        <div>
          {{ $siswaList->links('vendor.pagination.users') }} <!-- Gunakan custom pagination -->
        </div>
      </div>
    @endif
  </div>

  <!-- Modal Edit Status Kehadiran -->
  <div class="modal fade" id="modalEditStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form id="formEditStatus" class="modal-content das-modal">
        @csrf
        <input type="hidden" name="siswa_id" id="editSiswaId">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
        
        <div class="das-modal__head">
          <h5 class="das-modal__title fw-bold"><i class="ti tabler-edit me-2 text-info"></i>Ubah Status Kehadiran</h5>
          <button type="button" class="das-modal__close" data-bs-dismiss="modal" aria-label="Close"><i class="ti tabler-x"></i></button>
        </div>
        <div class="das-modal__body">
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
        <div class="das-modal__foot d-flex justify-content-end gap-2">
          <button type="button" class="btn das-btn --secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn das-btn --primary">Simpan Perubahan</button>
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
              confirmButton: 'btn das-btn --primary'
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
              confirmButton: 'btn das-btn --primary'
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
            confirmButton: 'btn das-btn --primary'
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
