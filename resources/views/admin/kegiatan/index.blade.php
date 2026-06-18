@extends('layouts/layoutMaster')

@section('title', 'Manajemen Kegiatan')

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
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #999 !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }

  /* PANEL */
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }

  /* TABLE */
  .das-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
  .das-table thead th { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #666; padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); background: rgba(255,255,255,.02); }
  .das-table tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); color: #ccc; vertical-align: middle; transition: background .2s ease; }
  .das-table tbody tr:hover td { background: var(--das-surface-hover); }

  /* CHIP */
  .das-chip { display: inline-flex; align-items: center; font-size: .65rem; font-weight: 700; padding: 2px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
  .das-chip--info { background: var(--das-info-soft); color: var(--das-info); }
  .das-chip--primary { background: var(--das-primary-soft); color: var(--das-primary); }

  /* ACTION BUTTONS */
  .das-table-btn { width: 30px; height: 30px; border-radius: 5px; border: 1px solid var(--das-border); background: transparent; color: #888; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; text-decoration: none; cursor: pointer; }
  .das-table-btn:hover { background: var(--das-surface-hover); color: white; transform: translateY(-2px); }
  .das-table-btn--warning:hover { color: var(--das-warning); border-color: var(--das-warning); }
  .das-table-btn--danger:hover { color: var(--das-danger); border-color: var(--das-danger); }

  /* MODAL */
  .das-modal { background: #1a1a2e !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; overflow: hidden; }
  .das-modal-head { border-bottom: 1px solid var(--das-border); background: rgba(115,103,240,.05); padding: 1.25rem; }
  .das-modal-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
  .das-modal-body { padding: 1.5rem; }

  /* FORM ELEMENTS */
  .das-form-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #888; margin-bottom: .5rem; display: block; }
  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #e0e0e0 !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; outline: none !important; box-shadow: none !important; color: white !important; }
  .das-form-control option { background: #1a1a2e; color: #ccc; }

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
          <i class="ti tabler-calendar-event"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.6;">
              <li class="breadcrumb-item text-white opacity-60">Modul Khusus</li>
              <li class="breadcrumb-item active text-white opacity-100">Manajemen Kegiatan</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">Manajemen Kegiatan</h4>
          <p class="das-hero__welcome">Kelola agenda sekolah, ekstrakurikuler, dan acara internal dalam satu dashboard.</p>
        </div>
      </div>
      <div>
        <button type="button" class="das-btn das-btn--primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreate">
          <i class="ti tabler-plus me-1"></i> Tambah Kegiatan
        </button>
      </div>
    </div>
  </div>

  {{-- ── FLASH MESSAGES ──────────────────────────────── --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-lg slide-in-up" 
         style="border-radius:8px;background:rgba(0,0,0,.3);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.1)!important;">
      <i class="ti tabler-circle-check fs-4 text-success"></i>
      <div class="text-white small fw-medium">{{ session('success') }}</div>
      <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ── MAIN PANEL ────────────────────────────── --}}
  <div class="das-panel slide-in-up">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot"></span>
        Daftar Seluruh Kegiatan
      </div>
      <span class="das-chip das-chip--info">{{ $kegiatans->total() }} Kegiatan</span>
    </div>

    <div class="table-responsive">
      <table class="das-table">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Informasi Kegiatan</th>
            <th>Waktu &amp; Lokasi</th>
            <th>Deskripsi</th>
            <th class="text-end px-4">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($kegiatans as $k)
          <tr>
            <td class="text-muted font-monospace small text-center">{{ ($kegiatans->currentPage()-1) * $kegiatans->perPage() + $loop->iteration }}</td>
            <td>
              <div class="d-flex align-items-center gap-3">
                <div style="width:38px;height:38px;border-radius:8px;background:var(--das-primary-soft);border:1px solid rgba(115,103,240,.2);display:flex;align-items:center;justify-content:center;color:var(--das-primary);">
                  <i class="ti tabler-star"></i>
                </div>
                <div>
                  <div class="fw-bold text-white mb-0" style="font-size:.85rem;">{{ $k->nama_kegiatan }}</div>
                  <div class="das-chip das-chip--primary mt-1" style="font-size:.6rem;padding:1px 8px;">{{ $k->jenis }}</div>
                </div>
              </div>
            </td>
            <td>
              <div class="text-white small fw-medium">{{ $k->tanggal_pelaksanaan?->translatedFormat('d F Y') }}</div>
              <div class="text-muted small mb-1" style="font-size:.7rem;">
                <i class="ti tabler-map-pin" style="font-size:.8rem;"></i> {{ $k->lokasi ?? '-' }}
              </div>
              <div class="d-flex flex-wrap gap-1">
                @if(($k->target_tingkat && count($k->target_tingkat) > 0) || ($k->target_peserta && count($k->target_peserta) > 0))
                  @if($k->target_tingkat && count($k->target_tingkat) > 0)
                    @foreach($k->target_tingkat as $t)
                      <span class="das-chip das-chip--primary" style="font-size:.55rem;padding:0px 6px;">Tingkat {{ $t }}</span>
                    @endforeach
                  @endif
                  
                  @if($k->target_peserta && count($k->target_peserta) > 0)
                    @php
                      $targetKelas = \App\Models\Kelas::whereIn('id', $k->target_peserta)->pluck('nama')->toArray();
                    @endphp
                    @foreach(array_slice($targetKelas, 0, 3) as $namaKelas)
                      <span class="das-chip das-chip--info" style="font-size:.55rem;padding:0px 6px;">{{ $namaKelas }}</span>
                    @endforeach
                    @if(count($targetKelas) > 3)
                      <span class="das-chip das-chip--info" style="font-size:.55rem;padding:0px 6px;">+{{ count($targetKelas) - 3 }}</span>
                    @endif
                  @endif
                @else
                  <span class="das-chip das-chip--success" style="font-size:.55rem;padding:0px 6px;">Seluruh Siswa</span>
                @endif
              </div>
            </td>
            <td>
              <div class="text-muted small" style="max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $k->keterangan ?? '-' }}
              </div>
            </td>
            <td class="px-4 text-end">
              <div class="d-flex justify-content-end gap-1">
                <a href="{{ route('admin.kegiatan.edit', $k->id) }}" class="das-table-btn das-table-btn--warning" title="Edit Kegiatan" data-bs-toggle="tooltip">
                  <i class="ti tabler-edit fs-5"></i>
                </a>
                <form action="{{ route('admin.kegiatan.destroy', $k->id) }}" method="POST" class="d-inline">
                  @csrf @method('DELETE')
                  <button type="submit" class="das-table-btn das-table-btn--danger" title="Hapus Kegiatan" data-bs-toggle="tooltip" onclick="return confirm('Yakin ingin menghapus kegiatan ini?')">
                    <i class="ti tabler-trash fs-5"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="py-5 text-center">
              <div class="d-flex flex-column align-items-center gap-2 opacity-30">
                <i class="ti tabler-calendar-off" style="font-size:3rem;"></i>
                <span class="small font-monospace uppercase">Belum ada data kegiatan</span>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($kegiatans->hasPages())
    <div class="px-4 py-3 border-top" style="border-color:var(--das-border)!important;">
      {{ $kegiatans->links() }}
    </div>
    @endif
  </div>

  {{-- ── MODAL CREATE ─────────────────────────────────── --}}
  <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title"><i class="ti tabler-calendar-plus me-2 text-primary"></i>Buat Agenda Baru</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.kegiatan.store') }}" method="POST">
          @csrf
          <div class="das-modal-body">
            <div class="mb-3">
              <label class="das-form-label">Nama Kegiatan <span class="text-danger">*</span></label>
              <input type="text" name="nama_kegiatan" class="form-control das-form-control" placeholder="Contoh: Upacara Bendera" required>
            </div>
            
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="das-form-label">Jenis <span class="text-danger">*</span></label>
                <select name="jenis" class="form-select das-form-control" required>
                  <option value="EKSTRAKURIKULER">Ekstrakurikuler</option>
                  <option value="UJIAN">Ujian</option>
                  <option value="RAPAT">Rapat</option>
                  <option value="LAINNYA">Lainnya</option>
                </select>
              </div>
              <div class="col-6">
                <label class="das-form-label">Tanggal <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_pelaksanaan" class="form-control das-form-control" value="{{ date('Y-m-d') }}" required>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="das-form-label">Waktu Mulai <span class="text-danger">*</span></label>
                <input type="time" name="waktu_mulai" class="form-control das-form-control" required>
              </div>
              <div class="col-6">
                <label class="das-form-label">Waktu Selesai <span class="text-danger">*</span></label>
                <input type="time" name="waktu_selesai" class="form-control das-form-control" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="das-form-label">Lokasi</label>
              <input type="text" name="lokasi" class="form-control das-form-control" placeholder="Nama Ruangan/Lapangan">
            </div>

            <div class="mb-0">
              <label class="das-form-label">Deskripsi Singkat</label>
              <textarea name="keterangan" class="form-control das-form-control" rows="3" placeholder="Tuliskan keterangan jika ada"></textarea>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="das-btn das-btn--primary px-4">Simpan Agenda</button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(el => new bootstrap.Tooltip(el));
  });
</script>
@endsection
