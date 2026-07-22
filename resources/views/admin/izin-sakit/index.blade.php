@extends('layouts/layoutMaster')

@section('title', 'Izin & Sakit')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
  <style>
    .izin-sakit-row-hover {
      transition: background 0.15s ease;
    }

    .izin-sakit-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .izin-sakit-action-btn {
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

    .izin-sakit-action-btn:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.1);
    }

    /* SWEETALERT2 ULTRA-PREMIUM UI/UX DESIGN SYSTEM */
    .das-swal-popup {
      background: #1e1e30 !important;
      background: linear-gradient(145deg, #1e1e30 0%, #161625 100%) !important;
      backdrop-filter: blur(20px) saturate(180%) !important;
      border: 1px solid rgba(255, 255, 255, 0.12) !important;
      border-radius: 12px !important;
      padding: 2rem 1.75rem 1.75rem !important;
      box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 40px rgba(115, 103, 240, 0.1) !important;
      width: 440px !important;
      max-width: 90vw !important;
    }

    .das-swal-icon {
      margin: 0.25rem auto 1.25rem !important;
      border-width: 2px !important;
      transform: scale(1.1);
    }
    .das-swal-icon.swal2-question {
      border-color: #00cfe8 !important;
      color: #00cfe8 !important;
    }
    .das-swal-icon.swal2-warning {
      border-color: #ff9f43 !important;
      color: #ff9f43 !important;
    }
    .das-swal-icon.swal2-error {
      border-color: #ea5455 !important;
      color: #ea5455 !important;
    }

    .das-swal-title {
      color: #ffffff !important;
      font-weight: 700 !important;
      font-size: 1.35rem !important;
      letter-spacing: -0.3px !important;
      text-align: center !important;
      padding: 0 !important;
      margin-bottom: 0.5rem !important;
    }

    .das-swal-html {
      color: rgba(255, 255, 255, 0.75) !important;
      font-size: 0.92rem !important;
      line-height: 1.5 !important;
      margin: 0.75rem 0 1.5rem !important;
      padding: 0 !important;
    }

    .das-swal-actions {
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 12px !important;
      margin-top: 1.25rem !important;
      width: 100% !important;
    }

    .das-swal-confirm-approve {
      background: linear-gradient(135deg, #28c76f 0%, #20b061 100%) !important;
      color: #fff !important;
      padding: 0.65rem 1.5rem !important;
      font-weight: 600 !important;
      font-size: 0.85rem !important;
      border-radius: 8px !important;
      border: none !important;
      box-shadow: 0 4px 14px rgba(40, 199, 111, 0.35) !important;
      transition: all 0.2s ease !important;
    }
    .das-swal-confirm-approve:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 6px 20px rgba(40, 199, 111, 0.5) !important;
    }

    .das-swal-confirm-danger {
      background: linear-gradient(135deg, #ea5455 0%, #d64344 100%) !important;
      color: #fff !important;
      padding: 0.65rem 1.5rem !important;
      font-weight: 600 !important;
      font-size: 0.85rem !important;
      border-radius: 8px !important;
      border: none !important;
      box-shadow: 0 4px 14px rgba(234, 84, 85, 0.35) !important;
      transition: all 0.2s ease !important;
    }
    .das-swal-confirm-danger:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 6px 20px rgba(234, 84, 85, 0.5) !important;
    }

    .das-swal-cancel {
      background: rgba(255, 255, 255, 0.06) !important;
      color: rgba(255, 255, 255, 0.8) !important;
      border: 1px solid rgba(255, 255, 255, 0.12) !important;
      padding: 0.65rem 1.5rem !important;
      font-weight: 600 !important;
      font-size: 0.85rem !important;
      border-radius: 8px !important;
      transition: all 0.2s ease !important;
    }
    .das-swal-cancel:hover {
      background: rgba(255, 255, 255, 0.12) !important;
      color: #fff !important;
      border-color: rgba(255, 255, 255, 0.25) !important;
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
            <i class="ti tabler-medical-cross"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Pusat Pengajuan Dispensasi
          </div>
          <h4 class="das-hero__title text-gradient-gold">Izin & Sakit</h4>
          <p class="das-hero__subtitle">Proses pengajuan dispensasi kehadiran siswa, guru, dan staff.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.izin-sakit.create') }}" class="das-btn das-btn--info">
          <i class="ti tabler-plus me-1"></i> Tambah Pengajuan
        </a>
      </div>
    </div>
  </div>

  @foreach (['success', 'error'] as $msg)
    @if (session($msg))
      <div
        class="alert alert-{{ $msg === 'success' ? 'success' : 'danger' }} alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
        role="alert" style="border-radius:8px; background: var(--das-{{ $msg === 'success' ? 'success' : 'danger' }}-soft); color: var(--das-{{ $msg === 'success' ? 'success' : 'danger' }});">
        <i class="ti {{ $msg === 'success' ? 'tabler-circle-check' : 'tabler-alert-circle' }} fs-5"></i>
        <span>{{ session($msg) }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: FILTERS
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4">
    <div class="das-panel__body py-3">
      <form method="GET" class="row gy-2 gx-2 align-items-end">
        <div class="col-6 col-md-3">
          <label class="form-label text-white-50 small mb-1 fw-bold">KATEGORI</label>
          <select name="tipe" class="form-select form-select-sm" onchange="this.form.submit()" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);">
            <option value="">Semua Tipe</option>
            @foreach (['siswa', 'guru', 'staff'] as $t)
              <option value="{{ $t }}" @selected(request('tipe') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label text-white-50 small mb-1 fw-bold">STATUS</label>
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="background: rgba(15, 23, 42, 0.4); color: white; border: 1px solid rgba(255,255,255,0.1);">
            <option value="">Semua Status</option>
            @foreach (['pending', 'disetujui', 'ditolak'] as $s)
              <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>
      </form>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: DATA LIST
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel mb-4">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot --warning"></span>
        Daftar Pengajuan
      </div>
      <div class="das-chip --warning">{{ $izinSakit->total() }} Record</div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="color:inherit;">
          <thead
            style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
            <tr>
              <th class="ps-4 py-3" style="width:46px;">#</th>
              <th class="py-3">Tipe</th>
              <th class="py-3">Nama</th>
              <th class="py-3 text-center">Jenis</th>
              <th class="py-3 text-center d-none d-md-table-cell">Periode</th>
              <th class="py-3 text-center">Status</th>
              <th class="py-3 text-center d-none d-lg-table-cell">Lampiran</th>
              <th class="py-3 pe-4 text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($izinSakit as $item)
              <tr class="izin-sakit-row-hover">
                <td class="ps-4 text-white-50 small">{{ $izinSakit->firstItem() + $loop->index }}</td>
                <td><span class="badge bg-label-secondary">{{ ucfirst($item->tipe) }}</span></td>
                <td>
                  <div class="fw-medium">
                    @if ($item->tipe === 'siswa')
                      {{ $item->siswa->nama_lengkap ?? '-' }}
                    @elseif($item->tipe === 'guru')
                      {{ $item->guru->nama_lengkap ?? '-' }}
                    @else
                      {{ $item->staff->nama_lengkap ?? '-' }}
                    @endif
                  </div>
                  <small class="text-muted d-md-none">{{ $item->tanggal_mulai->format('d/m') }} –
                    {{ $item->tanggal_selesai->format('d/m/Y') }}</small>
                </td>
                <td class="text-center">
                  <span class="badge bg-label-{{ $item->jenis === 'sakit' ? 'info' : 'warning' }} text-capitalize px-2">
                    {{ ucfirst($item->jenis) }}
                  </span>
                </td>
                <td class="text-center d-none d-md-table-cell">
                  {{ $item->tanggal_mulai->format('d M') }} – {{ $item->tanggal_selesai->format('d M Y') }}
                </td>
                <td class="text-center">
                  <span
                    class="badge bg-label-{{ match ($item->status) {
                        'pending' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'secondary',
                    } }} text-capitalize px-2">{{ ucfirst($item->status) }}</span>
                </td>
                <td class="text-center d-none d-lg-table-cell">
                  @if ($item->lampiran)
                    <a href="{{ Storage::url($item->lampiran) }}" target="_blank" class="btn btn-icon btn-sm btn-info"
                      title="Lihat Lampiran" data-bs-toggle="tooltip">
                      <i class="ti tabler-paperclip"></i>
                    </a>
                  @else
                    <span class="text-muted">–</span>
                  @endif
                </td>
                @php
                  $namaPengaju = match($item->tipe) {
                      'siswa' => $item->siswa->nama_lengkap ?? 'Siswa',
                      'guru' => $item->guru->nama_lengkap ?? 'Guru',
                      'staff' => $item->staff->nama_lengkap ?? 'Staff',
                      default => 'Pengajuan'
                  };
                @endphp
                <td class="pe-4 text-end">
                  <div class="d-flex justify-content-end gap-1 flex-wrap">
                    @if ($item->status === 'pending' && auth()->user()->role !== 'siswa')
                      <form action="{{ route('admin.izin-sakit.approve', $item) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="disetujui">
                        <button type="button" class="izin-sakit-action-btn text-success btn-approve-confirm" title="Setujui" data-bs-toggle="tooltip" data-nama="{{ $namaPengaju }}">
                          <i class="ti tabler-check fs-5"></i>
                        </button>
                      </form>
                      <form action="{{ route('admin.izin-sakit.approve', $item) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="ditolak">
                        <button type="button" class="izin-sakit-action-btn text-danger btn-reject-confirm" title="Tolak" data-bs-toggle="tooltip" data-nama="{{ $namaPengaju }}">
                          <i class="ti tabler-x fs-5"></i>
                        </button>
                      </form>
                    @endif
                    <a href="{{ route('admin.izin-sakit.edit', $item) }}" class="izin-sakit-action-btn text-warning"
                      title="Edit" data-bs-toggle="tooltip">
                      <i class="ti tabler-pencil fs-5"></i>
                    </a>
                    <form action="{{ route('admin.izin-sakit.destroy', $item) }}" method="POST" class="d-inline">
                      @csrf @method('DELETE')
                      <button type="button" class="izin-sakit-action-btn text-danger btn-delete-confirm" title="Hapus" data-bs-toggle="tooltip" data-nama="{{ $namaPengaju }}">
                        <i class="ti tabler-trash fs-5"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-5">
                  <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                    <i class="ti tabler-file-off" style="font-size:2.5rem;"></i>
                    <span class="small">Belum ada pengajuan izin/sakit.</span>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if ($izinSakit->hasPages())
      <div class="card-footer">{{ $izinSakit->links() }}</div>
    @endif
  </div>
@endsection

@section('page-script')
  <script type="module">
    $(function() {
      // Setujui Modal Confirm
      $('.btn-approve-confirm').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const nama = $(this).data('nama') || 'pengajuan ini';

        Swal.fire({
          title: 'Setujui Pengajuan?',
          html: `<div class="mb-1">Apakah Anda yakin ingin menyetujui pengajuan izin/sakit untuk:</div><div class="fw-bold text-info fs-6 mt-1">${nama}</div>`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: '<i class="ti tabler-check me-1"></i> Ya, Setujui',
          cancelButtonText: 'Batal',
          customClass: {
            popup: 'das-swal-popup',
            icon: 'das-swal-icon',
            title: 'das-swal-title',
            htmlContainer: 'das-swal-html',
            actions: 'das-swal-actions',
            confirmButton: 'btn das-swal-confirm-approve',
            cancelButton: 'btn das-swal-cancel'
          },
          buttonsStyling: false,
          reverseButtons: true,
          focusCancel: true
        }).then(function(result) {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });

      // Tolak Modal Confirm
      $('.btn-reject-confirm').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const nama = $(this).data('nama') || 'pengajuan ini';

        Swal.fire({
          title: 'Tolak Pengajuan?',
          html: `<div class="mb-1">Apakah Anda yakin ingin menolak pengajuan izin/sakit untuk:</div><div class="fw-bold text-warning fs-6 mt-1">${nama}</div>`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: '<i class="ti tabler-x me-1"></i> Ya, Tolak',
          cancelButtonText: 'Batal',
          customClass: {
            popup: 'das-swal-popup',
            icon: 'das-swal-icon',
            title: 'das-swal-title',
            htmlContainer: 'das-swal-html',
            actions: 'das-swal-actions',
            confirmButton: 'btn das-swal-confirm-danger',
            cancelButton: 'btn das-swal-cancel'
          },
          buttonsStyling: false,
          reverseButtons: true,
          focusCancel: true
        }).then(function(result) {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });

      // Hapus Modal Confirm
      $('.btn-delete-confirm').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const nama = $(this).data('nama') || 'pengajuan ini';

        Swal.fire({
          title: 'Hapus Pengajuan?',
          html: `<div class="mb-1">Data pengajuan izin/sakit untuk:</div><div class="fw-bold text-danger fs-6 my-1">${nama}</div><div class="small text-white-50">akan dihapus secara permanen dari sistem.</div>`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: '<i class="ti tabler-trash me-1"></i> Ya, Hapus',
          cancelButtonText: 'Batal',
          customClass: {
            popup: 'das-swal-popup',
            icon: 'das-swal-icon',
            title: 'das-swal-title',
            htmlContainer: 'das-swal-html',
            actions: 'das-swal-actions',
            confirmButton: 'btn das-swal-confirm-danger',
            cancelButton: 'btn das-swal-cancel'
          },
          buttonsStyling: false,
          reverseButtons: true,
          focusCancel: true
        }).then(function(result) {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  </script>
@endsection
