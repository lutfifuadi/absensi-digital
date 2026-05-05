@extends('layouts/layoutMaster')

@section('title', 'Kelola Hari Libur')

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

  /* BUTTONS */
  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #aaa !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }
  .das-btn--sm { padding: .35rem .7rem; font-size: .72rem; }

  /* PANEL */
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); flex-wrap: wrap; gap: .75rem; }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
  .das-panel__body { padding: 1.5rem; }

  /* CHIP */
  .das-chip { display: inline-flex; align-items: center; font-size: .65rem; font-weight: 700; padding: 2px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
  .das-chip--danger  { background: var(--das-danger-soft);  color: var(--das-danger); }
  .das-chip--info    { background: var(--das-info-soft);    color: var(--das-info); }
  .das-chip--primary { background: var(--das-primary-soft); color: var(--das-primary); }
  .das-chip--success { background: var(--das-success-soft); color: var(--das-success); }

  /* TABLE */
  .das-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
  .das-table thead th { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #555; padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); background: rgba(255,255,255,.02); }
  .das-table tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); color: #ccc; vertical-align: middle; transition: background .15s ease; }
  .das-table tbody tr:hover td { background: var(--das-surface-hover); }
  .das-table tbody tr:last-child td { border-bottom: none; }

  /* ACTION BUTTONS */
  .das-table-btn { width: 30px; height: 30px; border-radius: 5px; border: 1px solid var(--das-border); background: transparent; color: #666; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; cursor: pointer; }
  .das-table-btn:hover { background: var(--das-surface-hover); color: white; transform: translateY(-2px); }
  .das-table-btn--danger:hover { color: var(--das-danger); border-color: var(--das-danger); }

  /* FORM ELEMENTS */
  .das-form-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #777; margin-bottom: .5rem; display: block; }
  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #ddd !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control::placeholder { color: rgba(255,255,255,.2) !important; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; box-shadow: none !important; outline: none !important; color: white !important; }
  .das-form-text { font-size: .73rem; color: #555; margin-top: .35rem; }

  /* SELECT DARK */
  .das-select { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; color: #ddd !important; border-radius: var(--das-radius) !important; }
  .das-select:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; box-shadow: none !important; }
  .das-select option { background: #1a1a2e; color: #ccc; }

  /* MODAL */
  .das-modal { background: #1a1a2e !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; overflow: hidden; }
  .das-modal-head { border-bottom: 1px solid var(--das-border); background: rgba(234,84,85,.05); padding: 1.25rem; }
  .das-modal-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
  .das-modal-body { padding: 1.5rem; }

  /* INFO BOX */
  .das-info-box { background: rgba(0,207,232,.05); border: 1px solid rgba(0,207,232,.12); border-radius: var(--das-radius); padding: .9rem 1rem; }

  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }
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
          <i class="ti tabler-calendar-plus"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.6;">
              <li class="breadcrumb-item text-white">Monitoring &amp; Kalender</li>
              <li class="breadcrumb-item active text-white opacity-100">Kelola Hari Libur</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">Kelola Hari Libur</h4>
          <p class="das-hero__welcome">Konfigurasi hari libur sekolah untuk perhitungan kehadiran yang akurat.</p>
        </div>
      </div>
      <div>
        <a href="{{ route('admin.kalender-absensi') }}" class="das-btn das-btn--ghost">
          <i class="ti tabler-calendar-stats me-1"></i> Lihat Kalender
        </a>
      </div>
    </div>
  </div>

  {{-- ── FLASH MESSAGES ──────────────────────────────── --}}
  @foreach (['success', 'error'] as $msg)
    @if (session($msg))
      <div class="alert alert-{{ $msg === 'success' ? 'success' : 'danger' }} alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 slide-in-up"
           role="alert" style="border-radius:8px;background:rgba(0,0,0,.3);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.08)!important;">
        <i class="ti tabler-{{ $msg === 'success' ? 'circle-check' : 'alert-circle' }} fs-5 text-{{ $msg === 'success' ? 'success' : 'danger' }}"></i>
        <div class="text-white small fw-medium">{{ session($msg) }}</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach

  <div class="row g-4 slide-in-up">

    {{-- ── PANEL FORM TAMBAH ────────────────────────── --}}
    <div class="col-md-4">
      <div class="das-panel h-100">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot" style="background:var(--das-primary);box-shadow:0 0 6px var(--das-primary);"></span>
            Tambah Libur Sekolah
          </div>
        </div>
        <div class="das-panel__body">
          <form action="{{ route('admin.holidays.store') }}" method="POST">
            @csrf

            <div class="mb-3">
              <label class="das-form-label">Tanggal <span class="text-danger">*</span></label>
              <input type="date" name="tanggal"
                     class="form-control das-form-control @error('tanggal') is-invalid @enderror"
                     value="{{ old('tanggal') }}" required>
              @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
              <label class="das-form-label">Nama Libur <span class="text-danger">*</span></label>
              <input type="text" name="nama"
                     class="form-control das-form-control @error('nama') is-invalid @enderror"
                     placeholder="Contoh: Libur Semester Ganjil"
                     value="{{ old('nama') }}" required>
              @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
              <div class="das-form-text">Nama ini akan tampil di Kalender Absensi.</div>
            </div>

            <input type="hidden" name="jenis" value="school">

            <button type="submit" class="das-btn das-btn--primary w-100">
              <i class="ti tabler-device-floppy me-1"></i> Simpan Hari Libur
            </button>
          </form>

          <div class="das-info-box mt-4">
            <div class="small text-info d-flex gap-2">
              <i class="ti tabler-info-circle flex-shrink-0 mt-1"></i>
              <span>Hanya hari libur <strong>sekolah</strong> yang dapat ditambah &amp; dihapus. Libur nasional dikelola otomatis oleh sistem.</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── PANEL DAFTAR LIBUR ────────────────────────── --}}
    <div class="col-md-8">
      <div class="das-panel">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot" style="background:var(--das-info);box-shadow:0 0 6px var(--das-info);"></span>
            Daftar Hari Libur
          </div>
          <div class="d-flex align-items-center gap-2">
            @if($holidays->isNotEmpty())
              <span class="das-chip das-chip--primary">{{ $holidays->count() }} Libur</span>
            @endif
            <form method="GET" class="d-flex align-items-center gap-2">
              <label class="das-form-label mb-0" style="font-size:.7rem;">Tahun:</label>
              <select name="year" class="form-select form-select-sm das-select" style="width:90px;" onchange="this.form.submit()">
                @foreach (range(now()->year - 1, now()->year + 2) as $y)
                  <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endforeach
              </select>
            </form>
          </div>
        </div>

        <div class="table-responsive">
          <table class="das-table">
            <thead>
              <tr>
                <th width="40">#</th>
                <th>Tanggal</th>
                <th>Nama Hari Libur</th>
                <th class="text-center">Jenis</th>
                <th class="text-end px-4">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($holidays as $holiday)
              <tr>
                <td class="text-muted font-monospace small text-center">{{ $loop->iteration }}</td>
                <td>
                  <div class="fw-bold text-white" style="font-size:.83rem;">
                    {{ \Carbon\Carbon::parse($holiday->tanggal)->translatedFormat('d F Y') }}
                  </div>
                  <div class="text-muted" style="font-size:.7rem;">
                    {{ \Carbon\Carbon::parse($holiday->tanggal)->translatedFormat('l') }}
                  </div>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div style="width:6px;height:6px;border-radius:50%;background:{{ $holiday->jenis === 'national' ? 'var(--das-danger)' : 'var(--das-info)' }};flex-shrink:0;"></div>
                    <span style="font-size:.83rem;">{{ $holiday->nama }}</span>
                  </div>
                </td>
                <td class="text-center">
                  @if($holiday->jenis === 'national')
                    <span class="das-chip das-chip--danger">
                      <i class="ti tabler-flag me-1" style="font-size:.65rem;"></i>Nasional
                    </span>
                  @else
                    <span class="das-chip das-chip--info">
                      <i class="ti tabler-school me-1" style="font-size:.65rem;"></i>Sekolah
                    </span>
                  @endif
                </td>
                <td class="text-end px-4">
                  @if($holiday->jenis === 'school')
                    <button type="button" class="das-table-btn das-table-btn--danger"
                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                            data-id="{{ $holiday->id }}"
                            data-name="{{ $holiday->nama }}"
                            data-url="{{ route('admin.holidays.destroy', $holiday->id) }}"
                            title="Hapus Libur">
                      <i class="ti tabler-trash" style="font-size:.9rem;"></i>
                    </button>
                  @else
                    <span class="text-muted small">—</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="py-5 text-center">
                  <div class="d-flex flex-column align-items-center gap-2 opacity-25">
                    <i class="ti tabler-calendar-off" style="font-size:3rem;"></i>
                    <span class="small font-monospace">Tidak ada data hari libur pada tahun {{ $year }}</span>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($holidays->isNotEmpty())
        <div class="px-4 py-3 border-top d-flex gap-3" style="border-color:var(--das-border)!important;">
          <span class="small text-muted">Total: <strong class="text-white">{{ $holidays->count() }}</strong> libur</span>
          <span class="small text-muted">·</span>
          <span class="das-chip das-chip--danger" style="font-size:.6rem;">{{ $holidays->where('jenis','national')->count() }} Nasional</span>
          <span class="das-chip das-chip--info" style="font-size:.6rem;">{{ $holidays->where('jenis','school')->count() }} Sekolah</span>
        </div>
        @endif
      </div>
    </div>

  </div>

  {{-- ── MODAL HAPUS ─────────────────────────────────── --}}
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title">
            <i class="ti tabler-alert-triangle me-2 text-danger"></i>Hapus Hari Libur
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form id="delForm" method="POST">
          @csrf @method('DELETE')
          <div class="das-modal-body text-center">
            <div class="mb-4">
              <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                   style="width:70px;height:70px;background:rgba(234,84,85,.1);border:1px solid rgba(234,84,85,.2);">
                <i class="ti tabler-trash-x text-danger fs-1"></i>
              </div>
              <h4 class="text-white mb-2">Hapus "<span id="delName" class="text-danger"></span>"?</h4>
              <p class="text-muted small">Tindakan ini tidak dapat dibatalkan. Hari libur ini akan dihapus dari sistem.</p>
            </div>
            <div class="d-flex gap-2 justify-content-center">
              <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="das-btn das-btn--primary px-4"
                      style="background-color:var(--das-danger);border-color:var(--das-danger);">
                <i class="ti tabler-trash me-1"></i> Ya, Hapus
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const delModal = document.getElementById('deleteModal');
  if (delModal) {
    delModal.addEventListener('show.bs.modal', function (e) {
      const btn = e.relatedTarget;
      delModal.querySelector('#delName').textContent = btn.getAttribute('data-name');
      delModal.querySelector('#delForm').action      = btn.getAttribute('data-url');
    });
  }
});
</script>
@endsection