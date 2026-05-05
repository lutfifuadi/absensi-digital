@extends('layouts/layoutMaster')

@section('title', 'Izin & Sakit')

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
                <td class="pe-4 text-end">
                  <div class="d-flex justify-content-end gap-1 flex-wrap">
                    @if ($item->status === 'pending' && auth()->user()->role !== 'siswa')
                      <form action="{{ route('admin.izin-sakit.approve', $item) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="disetujui">
                        <button class="izin-sakit-action-btn text-success" title="Setujui" data-bs-toggle="tooltip"
                          onclick="return confirm('Setujui izin ini?')">
                          <i class="ti tabler-check fs-5"></i>
                        </button>
                      </form>
                      <form action="{{ route('admin.izin-sakit.approve', $item) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="ditolak">
                        <button class="izin-sakit-action-btn text-danger" title="Tolak" data-bs-toggle="tooltip"
                          onclick="return confirm('Tolak izin ini?')">
                          <i class="ti tabler-x fs-5"></i>
                        </button>
                      </form>
                    @endif
                    <a href="{{ route('admin.izin-sakit.edit', $item) }}" class="izin-sakit-action-btn text-warning"
                      title="Edit" data-bs-toggle="tooltip">
                      <i class="ti tabler-pencil fs-5"></i>
                    </a>
                    <form action="{{ route('admin.izin-sakit.destroy', $item) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus pengajuan ini?')">
                      @csrf @method('DELETE')
                      <button class="izin-sakit-action-btn text-danger" title="Hapus" data-bs-toggle="tooltip">
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
