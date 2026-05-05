@extends('layouts/layoutMaster')

@section('title', 'Absensi Siswa')

@section('page-style')
  <style>
    .absensi-row-hover {
      transition: background 0.15s ease;
    }

    .absensi-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .absensi-action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      transition: all 0.2s ease;
      border: none;
      background: rgba(255, 255, 255, 0.05);
      color: inherit;
    }

    .absensi-action-btn:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.1);
    }
  </style>
@endsection

@section('content')
  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-calendar-check"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Log Kehadiran Realtime
          </div>
          <h4 class="das-hero__title text-gradient-gold">Absensi Siswa</h4>
          <p class="das-hero__subtitle">Catat dan pantau kehadiran siswa harian dengan efisien.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="d-flex gap-2">
          <a href="{{ route('admin.absensi-siswa.scan') }}" class="das-btn das-btn--success">
            <i class="ti tabler-qrcode me-1"></i> Mode Scanner
          </a>
          <a href="{{ route('admin.absensi-siswa.create') }}" class="das-btn das-btn--primary">
            <i class="ti tabler-plus me-1"></i> Input Manual
          </a>
        </div>
      </div>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px; background: var(--das-success-soft); color: var(--das-success);">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: DATA TABLE
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot --info"></span>
        Daftar Kehadiran Terbaru
      </div>
      <div class="das-chip --info">{{ count($absensi) }} Baris Data</div>
    </div>
    <div class="table-responsive">
      <table class="das-table">
        <thead>
          <tr>
            <th class="text-center" width="50">#</th>
            <th>NAMA SISWA</th>
            <th>KELAS</th>
            <th class="text-center">TANGGAL</th>
            <th class="text-center">JAM MASUK</th>
            <th class="text-center">JAM PULANG</th>
            <th class="text-center">STATUS</th>
            <th class="text-center">METODE</th>
            <th class="text-end pe-4">AKSI</th>
          </tr>
        </thead>
        <tbody>
          @forelse($absensi as $item)
            <tr>
              <td class="text-center text-white-50">{{ $loop->iteration }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="https://ui-avatars.com/api/?name={{ urlencode($item->siswa->nama_lengkap ?? '-') }}&background=7367f0&color=fff"
                       class="das-avatar" width="28">
                  <span class="fw-semibold text-white">{{ $item->siswa->nama_lengkap ?? '-' }}</span>
                </div>
              </td>
              <td><span class="das-chip --info">{{ $item->kelas->nama ?? '-' }}</span></td>
              <td class="text-center">{{ $item->tanggal->format('d M Y') }}</td>
              <td class="text-center">
                <span class="fw-bold text-warning">{{ $item->jam_masuk ?? '-' }}</span>
              </td>
              <td class="text-center">
                <span class="fw-bold text-info">{{ $item->jam_pulang ?? '-' }}</span>
              </td>
              <td class="text-center">
                <span class="das-chip --{{ match ($item->status) {
                    'hadir' => 'success',
                    'sakit' => 'info',
                    'izin' => 'warning',
                    'alpha' => 'danger',
                    'terlambat' => 'primary',
                    default => 'dark',
                } }}">
                  {{ ucfirst($item->status) }}
                </span>
              </td>
              <td class="text-center">
                <span class="badge bg-label-{{ $item->metode === 'qr' ? 'primary' : 'secondary' }} px-2 py-1">
                  {{ strtoupper($item->metode) }}
                </span>
              </td>
              <td class="pe-4 text-end">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ route('admin.absensi-siswa.edit', $item) }}" class="absensi-action-btn text-warning"
                    title="Edit" data-bs-toggle="tooltip">
                    <i class="ti tabler-pencil fs-5"></i>
                  </a>
                  <button type="button" class="absensi-action-btn text-danger" title="Hapus" data-bs-toggle="tooltip"
                    onclick="confirmDelete(
                      '{{ route('admin.absensi-siswa.destroy', $item) }}',
                      '{{ addslashes($item->siswa->nama_lengkap ?? '-') }}',
                      '{{ $item->tanggal->format('d M Y') }}'
                    )">
                    <i class="ti tabler-trash fs-5"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7">
                <div class="das-empty-state">
                  <i class="ti tabler-calendar-off"></i>
                  <span>Belum ada data absensi tercatat.</span>
                  <a href="{{ route('admin.absensi-siswa.create') }}" class="das-btn das-btn--primary mt-2">Tambah Kehadiran</a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
      <div class="modal-content">
        <div class="modal-body text-center p-5">
          <div class="mb-4">
            <span
              style="
              display: inline-flex;
              align-items: center;
              justify-content: center;
              width: 80px; height: 80px;
              border-radius: 50%;
              background: rgba(234, 84, 85, 0.12);
              font-size: 2.5rem;
            ">🗑️</span>
          </div>

          <h5 class="modal-title mb-1" id="deleteConfirmModalLabel">Hapus Data Absensi?</h5>
          <p class="text-muted mb-1" style="font-size: 0.9rem;">
            Anda akan menghapus data absensi:
          </p>
          <p class="fw-bold mb-0" id="modal-siswa-name" style="font-size: 1rem;"></p>
          <p class="text-muted" id="modal-siswa-date" style="font-size: 0.82rem;"></p>

          <div class="alert alert-warning py-2 px-3 mt-3 mb-0" role="alert" style="font-size: 0.82rem;">
            <i class="ti tabler-alert-triangle me-1"></i>
            Tindakan ini <strong>tidak dapat dibatalkan</strong>.
          </div>
        </div>

        <div class="modal-footer d-flex justify-content-center gap-3 border-0 pt-0 pb-4">
          <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Batal
          </button>
          <form id="deleteForm" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger px-4">
              <i class="ti tabler-trash me-1"></i> Ya, Hapus!
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });

    function confirmDelete(actionUrl, siswaName, tanggal) {
      document.getElementById('deleteForm').action = actionUrl;
      document.getElementById('modal-siswa-name').textContent = siswaName;
      document.getElementById('modal-siswa-date').textContent = 'Tanggal: ' + tanggal;

      const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
      modal.show();
    }
  </script>
@endsection
