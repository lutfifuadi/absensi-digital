@extends('layouts/layoutMaster')

@section('title', 'Rekap Absensi Kegiatan')

@section('page-style')
<style>
  :root {
    --das-primary: #7367f0;
    --das-primary-soft: rgba(115, 103, 240, 0.12);
    --das-success: #28c76f;
    --das-success-soft: rgba(40, 199, 111, 0.12);
    --das-info: #00cfe8;
    --das-info-soft: rgba(0, 207, 232, 0.12);
    --das-warning: #ff9f43;
    --das-warning-soft: rgba(255, 159, 67, 0.12);
    --das-danger: #ea5455;
    --das-danger-soft: rgba(234, 84, 85, 0.12);
    --das-surface: rgba(15, 23, 42, 0.4);
    --das-surface-hover: rgba(30, 41, 59, 0.6);
    --das-border: rgba(255, 255, 255, 0.06);
    --das-border-hover: rgba(255, 255, 255, 0.12);
    --das-radius: 5px;
  }

  /* HERO */
  .das-hero { position: relative; border-radius: var(--das-radius); overflow: hidden; margin-bottom: 2rem; }
  .das-hero__bg { position: absolute; inset: 0; background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%); z-index: 0; }
  .das-hero__glass { position: absolute; inset: 0; background: radial-gradient(circle at top right, rgba(115,103,240,.15), transparent 40%); z-index: 1; }
  .das-hero__grid-lines { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 40px 40px; z-index: 1; }
  .das-hero__inner { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; padding: 2.5rem; gap: 1.5rem; flex-wrap: wrap; }
  .das-hero__identity { display: flex; align-items: center; gap: 1.25rem; }
  .das-hero__icon { width: 64px; height: 64px; background: rgba(115,103,240,.2); border: 1px solid rgba(115,103,240,.3); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: #a5a2f7; }
  .das-hero__title { font-size: 1.5rem; font-weight: 800; color: white; margin: 0 0 4px; }
  .das-hero__welcome { margin: 0; font-size: .88rem; color: rgba(255,255,255,.6); }

  /* ICON BUTTON */
  .das-icon-btn { width: 36px; height: 36px; border-radius: 5px; border: 1px solid var(--das-border); background: transparent; color: #888; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; text-decoration: none; cursor: pointer; position: relative; }
  .das-icon-btn:hover { background: var(--das-surface-hover); color: white; transform: translateY(-2px); }
  .das-icon-btn--secondary { border-color: var(--das-border); color: #999; }
  .das-icon-btn--secondary:hover { background: var(--das-surface-hover); color: white; border-color: var(--das-border-hover); }
  .das-icon-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-icon-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-icon-btn--sm { width: 30px; height: 30px; font-size: .85rem; }

  /* BUTTONS */
  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #999 !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }

  /* PANEL */
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }
  .das-panel__body { padding: 1.5rem; }

  /* TABLE */
  .das-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
  .das-table thead th { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #666; padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); background: rgba(255,255,255,.02); }
  .das-table tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); color: #ccc; vertical-align: middle; transition: background .2s ease; }
  .das-table tbody tr:hover td { background: var(--das-surface-hover); }

  /* CHIP */
  .das-chip { display: inline-flex; align-items: center; font-size: .65rem; font-weight: 700; padding: 2px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
  .das-chip--success { background: var(--das-success-soft); color: var(--das-success); }
  .das-chip--info { background: var(--das-info-soft); color: var(--das-info); }
  .das-chip--primary { background: var(--das-primary-soft); color: var(--das-primary); }
  .das-chip--warning { background: var(--das-warning-soft); color: var(--das-warning); }
  .das-chip--danger { background: var(--das-danger-soft); color: var(--das-danger); }

  /* FORM ELEMENTS */
  .das-form-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #888; margin-bottom: .5rem; display: block; }
  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #e0e0e0 !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; outline: none !important; box-shadow: none !important; color: white !important; }
  .das-form-control option { background: #1a1a2e; color: #ccc; }

  /* PAGINATION - dark theme override */
  .das-pagination { padding: .75rem 1rem; border-top: 1px solid var(--das-border); }
  .das-pagination nav .pagination { margin: 0; }
  .das-pagination .page-link { background: transparent !important; border: 1px solid var(--das-border) !important; color: #999 !important; font-size: .75rem; padding: .35rem .7rem; margin: 0 2px; border-radius: 4px !important; }
  .das-pagination .page-link:hover { background: var(--das-surface-hover) !important; color: white !important; }
  .das-pagination .page-item.active .page-link { background: var(--das-primary) !important; border-color: var(--das-primary) !important; color: white !important; }
  .das-pagination .page-item.disabled .page-link { opacity: .4; }

  /* ANIMATION */
  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }
  .slide-in-up-delay-1 { animation: slideInUp .5s ease-out .1s both; }
  .slide-in-up-delay-2 { animation: slideInUp .5s ease-out .2s both; }

  /* TOOLTIP */
  .das-tooltip { position: relative; }
  .das-tooltip:hover::after { content: attr(data-tip); position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%); background: #1a1a2e; color: #ccc; font-size: .65rem; font-weight: 600; padding: 4px 10px; border-radius: 4px; border: 1px solid var(--das-border); white-space: nowrap; z-index: 10; }
</style>
@endsection

@section('content')

  {{-- ── HERO HEADER ────────────────────────────────── --}}
  <div class="das-hero slide-in-up">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>
    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__icon">
          <i class="ti tabler-report-analytics"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.6;">
              <li class="breadcrumb-item text-white opacity-60">Modul Khusus</li>
              <li class="breadcrumb-item text-white opacity-60">Absensi Kegiatan</li>
              <li class="breadcrumb-item active text-white opacity-100">Rekap Kehadiran</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">Rekap Kehadiran</h4>
          <p class="das-hero__welcome">Pantau dan audit seluruh riwayat pemindaian kehadiran siswa pada agenda sekolah.</p>
        </div>
      </div>
      <div class="das-hero__actions" style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.absensi-kegiatan.scan') }}"
           class="das-icon-btn das-icon-btn--primary das-tooltip"
           data-tip="Buka Scanner"
           data-bs-toggle="tooltip"
           title="Buka Scanner QR Code">
          <i class="ti tabler-camera"></i>
        </a>
      </div>
    </div>
  </div>

  {{-- ── FILTER PANEL ────────────────────────────────── --}}
  <div class="das-panel slide-in-up-delay-1 mb-4">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot" style="background:var(--das-info);box-shadow:0 0 6px var(--das-info);"></span>
        Filter Agenda
      </div>
    </div>
    <div class="das-panel__body">
      <form action="{{ route('admin.absensi-kegiatan.rekap') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-9">
          <label class="das-form-label">Filter Berdasarkan Agenda</label>
          <select name="kegiatan_id" class="form-select das-form-control">
            <option value="">-- Tampilkan Semua Riwayat --</option>
            @foreach($kegiatans as $k)
              <option value="{{ $k->id }}" {{ request('kegiatan_id') == $k->id ? 'selected' : '' }}>
                {{ $k->nama_kegiatan }} ({{ $k->tanggal_pelaksanaan->format('d M Y') }})
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" class="das-btn das-btn--primary w-100">
            <i class="ti tabler-search"></i> Cari Data
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ── REKAP TABLE PANEL ───────────────────────────── --}}
  <div class="das-panel slide-in-up-delay-2">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot" style="background:var(--das-success);box-shadow:0 0 6px var(--das-success);"></span>
        Riwayat Kehadiran
      </div>
      @if(request('kegiatan_id'))
        <span class="das-chip das-chip--info">Difilter</span>
      @endif
    </div>

    <div class="table-responsive">
      <table class="das-table">
        <thead>
          <tr>
            <th>Identitas Siswa</th>
            <th>Rombel/Kelas</th>
            <th>Nama Agenda</th>
            <th class="text-center">Waktu Scan</th>
            <th class="text-center">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
          <tr>
            <td>
              <div class="fw-bold text-white" style="font-size:.85rem;">{{ $log->siswa->nama }}</div>
              <div style="color:#888;font-size:.7rem;">NIS: {{ $log->siswa->nis }}</div>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:6px;color:#999;font-size:.78rem;">
                <i class="ti tabler-door" style="font-size:.85rem;"></i>
                {{ $log->siswa->kelas->nama }}
              </div>
            </td>
            <td>
              <span class="das-chip das-chip--primary">{{ $log->kegiatan->nama_kegiatan }}</span>
            </td>
            <td class="text-center">
              <div style="display:flex;align-items:center;justify-content:center;gap:6px;color:#ccc;font-size:.82rem;">
                <i class="ti tabler-clock-play" style="color:var(--das-success);font-size:.9rem;"></i>
                {{ $log->jam_absen->format('H:i:s') }}
              </div>
            </td>
            <td class="text-center">
              <span class="das-chip das-chip--success">Hadir</span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="py-5 text-center">
              <div style="display:flex;flex-direction:column;align-items:center;gap:12px;opacity:.5;">
                <i class="ti tabler-database-x" style="font-size:2.8rem;color:#666;"></i>
                <span class="das-chip das-chip--warning" style="font-size:.7rem;padding:4px 14px;">
                  <i class="ti tabler-info-circle me-1"></i> Tidak ditemukan data yang sesuai dengan kriteria filter.
                </span>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($logs->hasPages())
      <div class="das-pagination">
        {{ $logs->appends(request()->query())->links() }}
      </div>
    @endif
  </div>

@endsection
