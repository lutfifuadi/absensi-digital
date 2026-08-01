@extends('layouts/layoutMaster')

@section('title', 'Jadwal Kegiatan Berulang')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/select2/select2.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/select2/select2.js'
  ])
@endsection

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

  .das-hero { position: relative; border-radius: var(--das-radius); overflow: hidden; margin-bottom: 2rem; }
  .das-hero__bg { position: absolute; inset: 0; background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%); z-index: 0; }
  .das-hero__glass { position: absolute; inset: 0; background: radial-gradient(circle at top right, rgba(115,103,240,.15), transparent 40%); z-index: 1; }
  .das-hero__grid-lines { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 40px 40px; z-index: 1; }
  .das-hero__inner { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; padding: 2.5rem; gap: 1.5rem; flex-wrap: wrap; }
  .das-hero__identity { display: flex; align-items: center; gap: 1.25rem; }
  .das-hero__icon { width: 64px; height: 64px; background: rgba(115,103,240,.2); border: 1px solid rgba(115,103,240,.3); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: #a5a2f7; }
  .das-hero__title { font-size: 1.5rem; font-weight: 800; color: white; margin: 0 0 4px; }
  .das-hero__welcome { margin: 0; font-size: .88rem; color: rgba(255,255,255,.6); }

  .das-btn { display: inline-flex; align-items: center; gap: 6px; font-size: .8rem; font-weight: 600; padding: .55rem 1.15rem; border-radius: 6px; border: 1px solid transparent; cursor: pointer; transition: all .2s ease; text-decoration: none; white-space: nowrap; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
  .das-btn--primary { background: linear-gradient(135deg, #7367f0 0%, #5e50ee 100%); color: #ffffff !important; border-color: rgba(255,255,255,0.2); box-shadow: 0 4px 14px rgba(115, 103, 240, 0.4); }
  .das-btn--primary:hover { background: linear-gradient(135deg, #6259e8 0%, #4b3ec4 100%); transform: translateY(-2px); box-shadow: 0 6px 18px rgba(115, 103, 240, 0.5); }
  .das-btn--ghost { background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff !important; backdrop-filter: blur(8px); }
  .das-btn--ghost:hover { background: rgba(255, 255, 255, 0.22); border-color: rgba(255, 255, 255, 0.45); color: #ffffff !important; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); }
  .das-btn--info { background: var(--das-info-soft); border-color: rgba(0,207,232,.3); color: var(--das-info) !important; }
  .das-btn--info:hover { background: var(--das-info); color: white !important; }

  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: visible; backdrop-filter: blur(6px); margin-bottom: 2rem; }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }

  .das-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
  .das-table th { padding: .75rem 1rem; color: rgba(255,255,255,.5); font-weight: 600; text-transform: uppercase; font-size: .7rem; letter-spacing: .5px; border-bottom: 1px solid var(--das-border); text-align: left; }
  .das-table td { padding: 1rem; border-bottom: 1px solid var(--das-border); vertical-align: middle; }
  .das-table tr:hover td { background: var(--das-surface-hover); }

  .das-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 4px; font-size: .72rem; font-weight: 600; }
  .das-badge--success { background: var(--das-success-soft); color: var(--das-success); border: 1px solid rgba(40,199,111,.2); }
  .das-badge--warning { background: var(--das-warning-soft); color: var(--das-warning); border: 1px solid rgba(255,159,67,.2); }
  .das-badge--primary { background: var(--das-primary-soft); color: var(--das-primary); border: 1px solid rgba(115,103,240,.2); }
  .das-badge--info { background: var(--das-info-soft); color: var(--das-info); border: 1px solid rgba(0,207,232,.2); }
  .das-badge--danger { background: var(--das-danger-soft); color: var(--das-danger); border: 1px solid rgba(234,84,85,.2); }

  .filter-bar { display: flex; gap: 1rem; flex-wrap: wrap; padding: 1rem 1.25rem; border-bottom: 1px solid var(--das-border); align-items: center; background: rgba(0,0,0,0.1); justify-content: flex-end; }
  .filter-bar .select2-container { min-width: 190px; }
  body > .select2-container { z-index: 9999 !important; }
  html, body { overflow-x: hidden !important; }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="ti tabler-check me-1"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- HERO BANNER -->
  <div class="das-hero">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>
    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__icon">
          <i class="ti tabler-calendar-repeat"></i>
        </div>
        <div>
          <h1 class="das-hero__title">Jadwal Kegiatan Berulang</h1>
          <p class="das-hero__welcome">Kelola definisi kegiatan rutin (mingguan atau bulanan) secara efisien dan otomatis (PRD-005).</p>
        </div>
      </div>
      <div class="d-flex gap-2 flex-wrap align-items-center">
        <a href="{{ route('admin.kegiatan.index') }}" class="das-btn das-btn--warning">
          <i class="ti tabler-arrow-left"></i> Kelola Kegiatan
        </a>
      </div>
    </div>
  </div>

  <!-- PANEL MAIN TABLE -->
  <div class="das-panel">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot"></span> Daftar Jadwal Kegiatan Berulang
      </div>
      <span class="text-muted fs-7">Total: {{ $jadwals->total() }} Jadwal</span>
    </div>

    <!-- FILTER BAR WITH SELECT2 (EXACT 'TAMBAH JADWAL' SETUP) -->
    <form action="{{ route('admin.jadwal-kegiatan.index') }}" method="GET" class="filter-bar" id="filterForm">
      <div>
        <select name="jenis" class="select2 form-select select2-filter" data-placeholder="-- Semua Jenis --">
          <option value="">-- Semua Jenis --</option>
          @foreach($jenisList as $j)
            <option value="{{ $j }}" {{ request('jenis') == $j ? 'selected' : '' }}>{{ $j }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <select name="is_aktif" class="select2 form-select select2-filter" data-placeholder="-- Semua Status --">
          <option value="">-- Semua Status --</option>
          <option value="1" {{ request('is_aktif') === '1' ? 'selected' : '' }}>Aktif Saja</option>
          <option value="0" {{ request('is_aktif') === '0' ? 'selected' : '' }}>Nonaktif Saja</option>
        </select>
      </div>
      <div>
        <select name="tahun_akademik_id" class="select2 form-select select2-filter" data-placeholder="-- Semua Tahun Akademik --">
          <option value="">-- Semua Tahun Akademik --</option>
          @foreach($tahunAkademiks as $ta)
            <option value="{{ $ta->id }}" {{ request('tahun_akademik_id') == $ta->id ? 'selected' : '' }}>
              {{ $ta->nama }} {{ $ta->is_aktif ? '(Aktif)' : '' }}
            </option>
          @endforeach
        </select>
      </div>
      @if(request()->anyFilled(['jenis', 'is_aktif', 'tahun_akademik_id']))
        <a href="{{ route('admin.jadwal-kegiatan.index') }}" class="das-btn das-btn--ghost py-1">Reset Filter</a>
      @endif
    </form>

    <div class="table-responsive">
      <table class="das-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama Kegiatan</th>
            <th>Tipe Jadwal</th>
            <th>Pola Hari / Tanggal</th>
            <th>Waktu & Lokasi</th>
            <th>Ekskul</th>
            <th>Status</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($jadwals as $index => $j)
            <tr>
              <td>{{ $jadwals->firstItem() + $index }}</td>
              <td>
                <div class="fw-bold text-white fs-6">{{ $j->nama_kegiatan }}</div>
                <div class="text-muted fs-7">
                  <span class="badge bg-label-secondary">{{ $j->jenis }}</span>
                  @if($j->is_wajib)
                    <span class="badge bg-label-danger ms-1">Wajib</span>
                  @endif
                </div>
              </td>
              <td>
                @if($j->tipe_jadwal === 'mingguan_1_hari')
                  <span class="das-badge das-badge--primary"><i class="ti tabler-calendar"></i> Mingguan (1 Hari)</span>
                @elseif($j->tipe_jadwal === 'mingguan_multi_hari')
                  <span class="das-badge das-badge--info"><i class="ti tabler-calendar-event"></i> Mingguan (Multi-Hari)</span>
                @else
                  <span class="das-badge das-badge--warning"><i class="ti tabler-calendar-stats"></i> Tanggal Kalender</span>
                @endif
              </td>
              <td>
                @if($j->tipe_jadwal === 'tanggal_kalender')
                  <span class="fw-semibold text-warning">Tanggal: {{ is_array($j->tanggal_kalender) ? implode(', ', $j->tanggal_kalender) : '-' }}</span>
                @else
                  <span class="fw-semibold text-info">Hari: {{ is_array($j->hari) ? implode(', ', array_map('ucfirst', $j->hari)) : '-' }}</span>
                @endif
              </td>
              <td>
                <div><i class="ti tabler-clock me-1 text-muted"></i>{{ $j->waktu_mulai ? substr($j->waktu_mulai, 0, 5) : '-' }} - {{ $j->waktu_selesai ? substr($j->waktu_selesai, 0, 5) : '-' }}</div>
                <div class="fs-7 text-muted"><i class="ti tabler-map-pin me-1"></i>{{ $j->lokasi ?: '-' }}</div>
              </td>
              <td>
                @if($j->ekskul)
                  <div class="fw-semibold text-white">{{ $j->ekskul->nama }}</div>
                  @if($j->ekskul_jadwal_id)
                    <span class="badge bg-label-info fs-7">Ikuti Jadwal Ekskul</span>
                  @endif
                @else
                  <span class="text-muted fs-7">Mandiri</span>
                @endif
              </td>
              <td>
                @if($j->is_aktif)
                  <span class="das-badge das-badge--success"><i class="ti tabler-check"></i> Aktif</span>
                @else
                  <span class="das-badge das-badge--danger"><i class="ti tabler-x"></i> Nonaktif</span>
                @endif
              </td>
              <td class="text-end">
                <div class="d-inline-flex gap-1">
                  <a href="{{ route('admin.jadwal-kegiatan.rekap', $j->id) }}" class="das-btn das-btn--info py-1 px-2" title="Rekap Kehadiran">
                    <i class="ti tabler-report-analytics"></i> Rekap
                  </a>
                  <a href="{{ route('admin.jadwal-kegiatan.edit', $j->id) }}" class="das-btn das-btn--ghost py-1 px-2" title="Edit Jadwal">
                    <i class="ti tabler-pencil"></i>
                  </a>
                  <form action="{{ route('admin.jadwal-kegiatan.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Menghapus jadwal berulang ini TIDAK akan menghapus data absensi historis yang sudah tercatat. Lanjutkan menghapus?');" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="das-btn das-btn--ghost text-danger py-1 px-2" title="Hapus Jadwal">
                      <i class="ti tabler-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">
                <i class="ti tabler-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                Belum ada jadwal kegiatan berulang yang dibuat. Klik tombol <strong>Tambah Jadwal Berulang</strong> untuk membuat baru.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($jadwals->hasPages())
      <div class="p-3 border-top border-secondary">
        {{ $jadwals->links() }}
      </div>
    @endif
  </div>
</div>
@endsection

@section('page-script')
  <script type="module">
    $(function() {
      const select2 = $('.select2');
      if (select2.length) {
        select2.each(function () {
          var $this = $(this);
          $this.select2({
            placeholder: $this.data('placeholder'),
            dropdownParent: $('body')
          });
        });

        $('.select2-filter').on('change', function() {
          $(this).closest('form').submit();
        });
      }
    });
  </script>
@endsection
