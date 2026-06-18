@extends('layouts/layoutMaster')

@section('title', 'Ekstrakurikuler')

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
    --das-radius: 5px;
  }

  /* ── HERO ── */
  .das-hero { position: relative; border-radius: var(--das-radius); overflow: hidden; margin-bottom: 2rem; }
  .das-hero__bg { position: absolute; inset: 0; background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%); z-index: 0; }
  .das-hero__glass { position: absolute; inset: 0; background: radial-gradient(circle at top right, rgba(115,103,240,.15), transparent 40%); z-index: 1; }
  .das-hero__grid-lines { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 40px 40px; z-index: 1; }
  .das-hero__inner { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; padding: 2.5rem; gap: 1.5rem; flex-wrap: wrap; }
  .das-hero__identity { display: flex; align-items: center; gap: 1.25rem; }
  .das-hero__icon { width: 64px; height: 64px; background: rgba(115,103,240,.2); border: 1px solid rgba(115,103,240,.3); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: #a5a2f7; }
  .das-hero__title { font-size: 1.5rem; font-weight: 800; color: white; margin: 0 0 4px; }
  .das-hero__welcome { margin: 0; font-size: .88rem; color: rgba(255,255,255,.6); }

  /* ── BUTTONS ── */
  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #999 !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }

  /* ── PANEL ── */
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }

  /* ── CHIP ── */
  .das-chip { display: inline-flex; align-items: center; font-size: .65rem; font-weight: 700; padding: 2px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
  .das-chip--info { background: var(--das-info-soft); color: var(--das-info); }
  .das-chip--primary { background: var(--das-primary-soft); color: var(--das-primary); }
  .das-chip--success { background: var(--das-success-soft); color: var(--das-success); }
  .das-chip--warning { background: var(--das-warning-soft); color: var(--das-warning); }
  .das-chip--danger { background: var(--das-danger-soft); color: var(--das-danger); }

  /* ── FORM ── */
  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #e0e0e0 !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; outline: none !important; box-shadow: none !important; color: white !important; }
  .das-form-control option { background: #1a1a2e; color: #ccc; }

  /* ── CARD GRID ── */
  .ekskul-card { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; transition: all .25s ease; position: relative; }
  .ekskul-card:hover { border-color: rgba(115,103,240,.4); transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.3); }
  .ekskul-card__header { padding: 1.25rem 1.25rem .75rem; display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; }
  .ekskul-card__icon { width: 48px; height: 48px; border-radius: var(--das-radius); background: var(--das-primary-soft); border: 1px solid rgba(115,103,240,.2); display: flex; align-items: center; justify-content: center; font-size: 1.35rem; color: var(--das-primary); flex-shrink: 0; }
  .ekskul-card__body { padding: 0 1.25rem 1rem; }
  .ekskul-card__title { font-size: .9rem; font-weight: 700; color: white; margin-bottom: 4px; line-height: 1.3; }
  .ekskul-card__meta { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: .5rem; }
  .ekskul-card__footer { padding: .75rem 1.25rem; border-top: 1px solid var(--das-border); display: flex; align-items: center; justify-content: space-between; gap: .5rem; }

  /* ── ACTION ICON BTN ── */
  .icon-btn { width: 30px; height: 30px; border-radius: 5px; border: 1px solid var(--das-border); background: transparent; color: #888; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; text-decoration: none; cursor: pointer; }
  .icon-btn:hover { background: var(--das-surface-hover); color: white; transform: translateY(-2px); }
  .icon-btn--warning:hover { color: var(--das-warning); border-color: var(--das-warning); }
  .icon-btn--danger:hover { color: var(--das-danger); border-color: var(--das-danger); }
  .icon-btn--success:hover { color: var(--das-success); border-color: var(--das-success); }
  .icon-btn--info:hover { color: var(--das-info); border-color: var(--das-info); }

  /* ── EMPTY ── */
  .empty-state { padding: 4rem 2rem; text-align: center; }

  /* ── ANIMATIONS ── */
  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }

  /* ── MODAL ── */
  .das-modal { background: #1a1a2e !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; overflow: hidden; }
  .das-modal-head { border-bottom: 1px solid var(--das-border); background: rgba(115,103,240,.05); padding: 1.25rem; }
  .das-modal-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
  .das-modal-body { padding: 1.5rem; }
</style>
@endsection

@section('content')

  {{-- ═══════════ HERO HEADER ═══════════ --}}
  <div class="das-hero slide-in-up">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>
    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__icon">
          <i class="ti tabler-trophy"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.6;">
              <li class="breadcrumb-item text-white opacity-60">Modul Khusus</li>
              <li class="breadcrumb-item active text-white opacity-100">Ekstrakurikuler</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">Ekstrakurikuler</h4>
          <p class="das-hero__welcome">Kelola kegiatan ekstrakurikuler, anggota, dan absensi dalam satu tempat.</p>
        </div>
      </div>
      <div>
        <a href="{{ route('admin.ekskul.create') }}" class="das-btn das-btn--primary">
          <i class="ti tabler-plus me-1"></i> Tambah Ekskul
        </a>
      </div>
    </div>
  </div>

  {{-- ═══════════ FLASH MESSAGES ═══════════ --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-lg slide-in-up"
      role="alert" style="border-radius:8px;background:rgba(0,0,0,.3);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.1)!important;">
      <i class="ti tabler-circle-check fs-4 text-success"></i>
      <div class="text-white small fw-medium">{{ session('success') }}</div>
      <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-lg slide-in-up"
      role="alert" style="border-radius:8px;background:rgba(0,0,0,.3);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.1)!important;">
      <i class="ti tabler-alert-circle fs-4 text-danger"></i>
      <div class="text-white small fw-medium">{{ session('error') }}</div>
      <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ═══════════ FILTER BAR ═══════════ --}}
  <div class="das-panel slide-in-up mb-4">
    <div class="p-3">
      <form method="GET" action="{{ route('admin.ekskul.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4 col-sm-6">
          <label class="text-white-50 small fw-semibold mb-1 d-block" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;">
            <i class="ti tabler-search me-1" style="font-size:.75rem;"></i> Cari Nama
          </label>
          <input type="text" name="search" class="form-control das-form-control"
            placeholder="Cari nama atau deskripsi ekskul..."
            value="{{ request('search') }}">
        </div>
        <div class="col-md-3 col-sm-6">
          <label class="text-white-50 small fw-semibold mb-1 d-block" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;">
            <i class="ti tabler-category me-1" style="font-size:.75rem;"></i> Kategori
          </label>
          <select name="kategori" class="form-select das-form-control">
            <option value="">Semua Kategori</option>
            @foreach(['wajib'=>'Wajib','pilihan'=>'Pilihan','olahraga'=>'Olahraga','seni'=>'Seni','akademik'=>'Akademik','lainnya'=>'Lainnya'] as $val => $label)
              <option value="{{ $val }}" {{ request('kategori') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 col-sm-6">
          <label class="text-white-50 small fw-semibold mb-1 d-block" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;">
            <i class="ti tabler-circle-check me-1" style="font-size:.75rem;"></i> Status
          </label>
          <select name="status" class="form-select das-form-control">
            <option value="">Semua Status</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
          </select>
        </div>
        <div class="col-md-2 col-sm-6 d-flex gap-2">
          <button type="submit" class="das-btn das-btn--primary w-100 justify-content-center">
            <i class="ti tabler-filter me-1"></i> Filter
          </button>
          @if(request()->anyFilled(['search', 'kategori', 'status']))
            <a href="{{ route('admin.ekskul.index') }}" class="das-btn das-btn--ghost justify-content-center" title="Reset Filter">
              <i class="ti tabler-x"></i>
            </a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- ═══════════ CARD GRID ═══════════ --}}
  <div class="das-panel slide-in-up">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot"></span>
        Daftar Ekstrakurikuler
      </div>
      <span class="das-chip das-chip--info">{{ $ekskuls->total() }} Ekskul</span>
    </div>

    <div class="p-3">
      @forelse($ekskuls->chunk(3) as $chunk)
        <div class="row g-3 mb-3">
          @foreach($chunk as $ekskul)
            <div class="col-lg-4 col-md-6">
              <div class="ekskul-card h-100 d-flex flex-column">
                <div class="ekskul-card__header">
                  <div class="ekskul-card__icon">
                    <i class="ti {{ $ekskul->icon ? 'tabler-' . $ekskul->icon : 'tabler-star' }}"></i>
                  </div>
                  <div class="d-flex flex-column align-items-end gap-1">
                    @php
                      $kategoriColors = [
                        'wajib'    => '--primary',
                        'pilihan'  => '--info',
                        'olahraga' => '--success',
                        'seni'     => '--warning',
                        'akademik' => '--primary',
                        'lainnya'  => '--info',
                      ];
                      $kategoriLabels = [
                        'wajib'    => 'Wajib',
                        'pilihan'  => 'Pilihan',
                        'olahraga' => 'Olahraga',
                        'seni'     => 'Seni',
                        'akademik' => 'Akademik',
                        'lainnya'  => 'Lainnya',
                      ];
                      $chipColor = $kategoriColors[$ekskul->kategori] ?? '--info';
                    @endphp
                    <span class="das-chip das-chip{{ $chipColor }}">
                      {{ $kategoriLabels[$ekskul->kategori] ?? ucfirst($ekskul->kategori) }}
                    </span>
                    <span class="das-chip {{ $ekskul->status ? 'das-chip--success' : 'das-chip--danger' }}">
                      <span class="me-1" style="width:6px;height:6px;border-radius:50%;display:inline-block;background:{{ $ekskul->status ? 'var(--das-success)' : 'var(--das-danger)' }};"></span>
                      {{ $ekskul->status ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </div>
                </div>
                <div class="ekskul-card__body flex-grow-1">
                  <div class="ekskul-card__title">{{ $ekskul->nama }}</div>
                  @if($ekskul->deskripsi)
                    <p class="text-white-50 small mb-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                      {{ $ekskul->deskripsi }}
                    </p>
                  @endif
                  <div class="d-flex align-items-center gap-2 text-white-50 small">
                    <i class="ti tabler-users" style="font-size:.8rem;"></i>
                    <span>{{ $ekskul->anggota_count }} Anggota</span>
                    @if($ekskul->kuota)
                      <span class="text-muted">/ {{ $ekskul->kuota }} kuota</span>
                    @endif
                  </div>
                </div>
                <div class="ekskul-card__footer">
                  <div class="d-flex gap-1">
                    {{-- Anggota --}}
                    <a href="{{ route('admin.ekskul.anggota.index', $ekskul->id) }}" class="icon-btn icon-btn--info"
                      title="Kelola Anggota" data-bs-toggle="tooltip">
                      <i class="ti tabler-users fs-5"></i>
                    </a>
                    {{-- Absensi --}}
                    <a href="{{ route('admin.ekskul.absensi.index', $ekskul->id) }}" class="icon-btn icon-btn--success"
                      title="Absensi" data-bs-toggle="tooltip">
                      <i class="ti tabler-clipboard-check fs-5"></i>
                    </a>
                    {{-- Toggle Status --}}
                    <form action="{{ route('admin.ekskul.toggle-status', $ekskul->id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('{{ $ekskul->status ? 'Nonaktifkan' : 'Aktifkan' }} ekskul ini?')">
                      @csrf
                      <button type="submit" class="icon-btn {{ $ekskul->status ? 'icon-btn--warning' : 'icon-btn--success' }}"
                        title="{{ $ekskul->status ? 'Nonaktifkan' : 'Aktifkan' }}" data-bs-toggle="tooltip">
                        <i class="ti {{ $ekskul->status ? 'tabler-toggle-right' : 'tabler-toggle-left' }} fs-5"></i>
                      </button>
                    </form>
                  </div>
                  <div class="d-flex gap-1">
                    {{-- Edit --}}
                    <a href="{{ route('admin.ekskul.edit', $ekskul->id) }}" class="icon-btn icon-btn--warning"
                      title="Edit" data-bs-toggle="tooltip">
                      <i class="ti tabler-edit fs-5"></i>
                    </a>
                    {{-- Hapus --}}
                    <form action="{{ route('admin.ekskul.destroy', $ekskul->id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Yakin ingin menghapus ekskul ini? Data terkait (anggota, absensi) tidak akan terhapus secara langsung.')">
                      @csrf @method('DELETE')
                      <button type="submit" class="icon-btn icon-btn--danger"
                        title="Hapus" data-bs-toggle="tooltip">
                        <i class="ti tabler-trash fs-5"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
        @if (!$loop->last)
          <div style="border-top:1px solid var(--das-border);margin:0 0 1rem;"></div>
        @endif
      @empty
        <div class="empty-state">
          <div class="d-flex flex-column align-items-center gap-2 opacity-40">
            <i class="ti tabler-trophy-off" style="font-size:3.5rem;"></i>
            <h6 class="text-white mb-1">Belum Ada Ekstrakurikuler</h6>
            <p class="text-white-50 small mb-3">Tambahkan ekskul pertama untuk memulai manajemen kegiatan.</p>
            <a href="{{ route('admin.ekskul.create') }}" class="das-btn das-btn--primary">
              <i class="ti tabler-plus me-1"></i> Tambah Ekskul
            </a>
          </div>
        </div>
      @endforelse
    </div>

    {{-- Pagination --}}
    @if($ekskuls->hasPages())
      <div class="px-4 py-3 border-top" style="border-color:var(--das-border)!important;">
        {{ $ekskuls->withQueryString()->links() }}
      </div>
    @endif
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
