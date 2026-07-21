@extends('layouts/layoutMaster')

@section('title', 'Redaksi Notifikasi')

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

  /* ── BUTTONS ─────────────────────────────────────────── */
  .das-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: .75rem;
    font-weight: 700;
    padding: .55rem 1.25rem;
    border-radius: 5px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all .25s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    white-space: nowrap;
    letter-spacing: 0.3px;
    position: relative;
    overflow: hidden;
  }

  /* Shine overlay */
  .das-btn::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
    opacity: 0;
    transition: opacity .4s ease;
    pointer-events: none;
  }
  .das-btn:hover::after {
    opacity: 1;
  }

  /* Primary — Gradient Ungu Premium */
  .das-btn--primary {
    background: linear-gradient(135deg, #7367f0 0%, #5e50ee 100%);
    color: white !important;
    border-color: rgba(115, 103, 240, 0.4);
    box-shadow: 0 4px 16px rgba(115, 103, 240, 0.25);
  }
  .das-btn--primary:hover {
    background: linear-gradient(135deg, #8579f5 0%, #6b5df0 100%);
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(115, 103, 240, 0.4);
    border-color: rgba(115, 103, 240, 0.6);
  }

  /* Ghost */
  .das-btn--ghost {
    background: transparent;
    border-color: var(--das-border);
    color: #999 !important;
  }
  .das-btn--ghost:hover {
    background: var(--das-surface-hover);
    color: white !important;
    transform: translateY(-1px);
  }

  /* Info — Gradient Cyan/Biru Premium */
  .das-btn--info {
    background: linear-gradient(135deg, #00cfe8 0%, #00b4cc 100%);
    color: white !important;
    border-color: rgba(0, 207, 232, 0.4);
    box-shadow: 0 4px 16px rgba(0, 207, 232, 0.25);
  }
  .das-btn--info:hover {
    background: linear-gradient(135deg, #08ddf5 0%, #00c4dd 100%);
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(0, 207, 232, 0.4);
    border-color: rgba(0, 207, 232, 0.6);
  }

  /* Secondary — Glass Premium */
  .das-btn--secondary {
    background: linear-gradient(135deg, rgba(168, 179, 191, 0.18) 0%, rgba(168, 179, 191, 0.08) 100%);
    border-color: rgba(168, 179, 191, 0.25);
    color: #c8d0d6 !important;
    backdrop-filter: blur(4px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  .das-btn--secondary:hover {
    background: linear-gradient(135deg, rgba(168, 179, 191, 0.28) 0%, rgba(168, 179, 191, 0.14) 100%);
    border-color: rgba(168, 179, 191, 0.45);
    color: white !important;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
  }

  /* Success — Gradient Hijau Premium */
  .das-btn--success {
    background: linear-gradient(135deg, #28c76f 0%, #1fad5e 100%);
    color: white !important;
    border-color: rgba(40, 199, 111, 0.4);
    box-shadow: 0 4px 16px rgba(40, 199, 111, 0.25);
  }
  .das-btn--success:hover {
    background: linear-gradient(135deg, #33d77b 0%, #26bc69 100%);
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(40, 199, 111, 0.4);
    border-color: rgba(40, 199, 111, 0.6);
  }

  /* Warning — Gradient Oranye Premium */
  .das-btn--warning {
    background: linear-gradient(135deg, #ff9f43 0%, #f0892a 100%);
    color: white !important;
    border-color: rgba(255, 159, 67, 0.4);
    box-shadow: 0 4px 16px rgba(255, 159, 67, 0.25);
  }
  .das-btn--warning:hover {
    background: linear-gradient(135deg, #ffab54 0%, #fa9335 100%);
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(255, 159, 67, 0.4);
  }

  /* Danger — Gradient Merah Premium */
  .das-btn--danger {
    background: linear-gradient(135deg, #ea5455 0%, #d63a3b 100%);
    color: white !important;
    border-color: rgba(234, 84, 85, 0.4);
    box-shadow: 0 4px 16px rgba(234, 84, 85, 0.25);
  }
  .das-btn--danger:hover {
    background: linear-gradient(135deg, #f26364 0%, #e04445 100%);
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(234, 84, 85, 0.4);
    border-color: rgba(234, 84, 85, 0.6);
  }

  /* Icon animation in buttons */
  .das-btn i {
    transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 1.1rem;
  }
  .das-btn:hover i {
    transform: scale(1.15);
  }
  .das-btn--info:hover i.tabler-download {
    animation: btn-icon-bounce 0.6s ease;
  }
  .das-btn--secondary:hover i.tabler-upload {
    animation: btn-icon-bounce 0.6s ease;
  }
  .das-btn--primary:hover i.tabler-plus {
    animation: btn-icon-spin 0.5s ease;
  }

  @keyframes btn-icon-bounce {
    0%, 100% { transform: translateY(0) scale(1.15); }
    40% { transform: translateY(-4px) scale(1.2); }
    60% { transform: translateY(-2px) scale(1.18); }
  }
  @keyframes btn-icon-spin {
    0% { transform: rotate(0deg) scale(1.15); }
    100% { transform: rotate(180deg) scale(1.15); }
  }

  /* PANEL */
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__body { padding: 1.25rem; }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }

  .form-control,
  .form-select {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    color: #fff !important;
    border-radius: 5px !important;
  }

  .form-control:focus,
  .form-select:focus {
    background: rgba(255, 255, 255, 0.08) !important;
    border-color: var(--das-info) !important;
  }

  .form-control::placeholder {
    color: rgba(255, 255, 255, 0.35);
  }

  /* TABLE */
  .das-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
  .das-table thead th { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #666; padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); background: rgba(255,255,255,.02); }
  .das-table tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); color: #ccc; vertical-align: middle; transition: background .2s ease; }
  .das-table tbody tr:hover td { background: var(--das-surface-hover); }

  /* CHIP */
  .das-chip { display: inline-flex; align-items: center; font-size: .65rem; font-weight: 700; padding: 2px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
  .das-chip--danger  { background: var(--das-danger-soft);  color: var(--das-danger); }
  .das-chip--warning { background: var(--das-warning-soft); color: var(--das-warning); }
  .das-chip--info    { background: var(--das-info-soft);    color: var(--das-info); }
  .das-chip--success { background: var(--das-success-soft); color: var(--das-success); }
  .das-chip--primary { background: var(--das-primary-soft); color: var(--das-primary); }
  .das-chip--secondary { background: rgba(168,170,174,.12); color: #a8aaae; }

  /* ACTION BUTTONS */
  .das-table-btn { width: 30px; height: 30px; border-radius: 5px; border: 1px solid var(--das-border); background: transparent; color: #888; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; text-decoration: none; }
  .das-table-btn:hover { background: var(--das-surface-hover); color: white; transform: translateY(-2px); }
  .das-table-btn--info:hover    { color: var(--das-info);    border-color: var(--das-info); }
  .das-table-btn--warning:hover { color: var(--das-warning); border-color: var(--das-warning); }
  .das-table-btn--danger:hover  { color: var(--das-danger);  border-color: var(--das-danger); }

  /* PAGINATION */
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

  /* MODAL */
  .das-modal { background: #1a1a2e !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; overflow: hidden; }
  .das-modal-head { border-bottom: 1px solid var(--das-border); background: rgba(234,84,85,.05); padding: 1.25rem; }
  .das-modal-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
  .das-modal-body { padding: 1.5rem; }

  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }

  .type-icon { width: 38px; height: 38px; border-radius: 8px; background: var(--das-primary-soft); border: 1px solid rgba(115,103,240,.2); display: flex; align-items: center; justify-content: center; color: var(--das-primary); font-size: .95rem; flex-shrink: 0; }
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
          <i class="ti tabler-bell-ringing"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.6;">
              <li class="breadcrumb-item"><a href="{{ route('admin.pengaturan.index') }}" class="text-white text-decoration-none">Pengaturan</a></li>
              <li class="breadcrumb-item active text-white opacity-100">Redaksi Notifikasi</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">Redaksi Notifikasi</h4>
          <p class="das-hero__welcome">Kelola pesan WhatsApp otomatis yang dikirim ke orang tua siswa.</p>
        </div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.notification-templates.export') }}" class="das-btn das-btn--info shadow-sm">
          <i class="ti tabler-download"></i> Export JSON
        </a>
        <button type="button" class="das-btn das-btn--success shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
          <i class="ti tabler-upload"></i> Import JSON
        </button>
        <a href="{{ route('admin.notification-templates.create') }}" class="das-btn das-btn--primary shadow-sm">
          <i class="ti tabler-plus me-1"></i> Buat Redaksi Baru
        </a>
      </div>
    </div>
  </div>

  {{-- ── FLASH MESSAGES ──────────────────────────────── --}}
  @foreach (['success', 'error', 'info'] as $msg)
    @if (session($msg))
      <div class="alert alert-{{ $msg === 'success' ? 'success' : ($msg === 'info' ? 'info' : 'danger') }} alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-lg slide-in-up"
           role="alert" style="border-radius:8px;background:rgba(0,0,0,.3);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.1)!important;">
        <i class="ti tabler-{{ $msg === 'success' ? 'circle-check' : 'alert-circle' }} fs-4 text-{{ $msg === 'success' ? 'success' : ($msg === 'info' ? 'info' : 'danger') }}"></i>
        <div class="text-white small fw-medium">{{ session($msg) }}</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach

  {{-- ── FILTER CARD ──────────────────────────────────── --}}
  <div class="das-panel mb-4 slide-in-up">
    <div class="das-panel__body">
      <form id="filterForm" method="GET" class="row gy-3 gx-3 align-items-end">
        <div class="col-md-5">
          <label class="form-label text-white-50 small fw-bold">Cari Redaksi</label>
          <input type="text" name="search" class="form-control"
            placeholder="Cari isi pesan / tipe…" value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label text-white-50 small fw-bold">Tipe Notifikasi</label>
          <select name="type" class="form-select">
            <option value="">Semua Tipe</option>
            @foreach ($types as $key => $val)
              <option value="{{ $key }}" @selected(request('type') === $key)>{{ $val }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <div class="d-flex gap-2">
            <button type="submit" class="btn das-btn das-btn--info w-100 justify-content-center">
              <i class="ti tabler-search me-1"></i> Cari
            </button>
            <a href="{{ route('admin.notification-templates.index') }}" class="btn das-btn das-btn--secondary" title="Reset">
              <i class="ti tabler-refresh"></i>
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- ── MAIN TABLE PANEL ────────────────────────────── --}}
  <div class="das-panel slide-in-up" id="templatesTableCard">
    <div class="das-panel__head flex-wrap gap-3">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot"></span>
        Daftar Redaksi Notifikasi
      </div>
      <span class="das-chip das-chip--info" id="totalTemplatesChip">{{ $templates->total() }} Template</span>
    </div>

    <div class="table-responsive">
      <table class="das-table">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Tipe &amp; Pemicu</th>
            <th>Cuplikan Konten</th>
            <th class="d-none d-md-table-cell">Terakhir Diperbarui</th>
            <th class="text-end px-4">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($templates as $template)
          <tr>
            <td class="text-muted font-monospace small text-center">{{ $loop->iteration }}</td>
            <td>
              <div class="d-flex align-items-center gap-3">
                <div class="type-icon">
                  <i class="ti tabler-message-dots"></i>
                </div>
                <div>
                  <div class="fw-bold text-white mb-1" style="font-size:.85rem;">{{ $types[$template->type] ?? $template->type }}</div>
                  <code class="small" style="color:var(--das-info);font-size:.72rem;">{{ $template->type }}</code>
                </div>
              </div>
            </td>
            <td>
              <div class="text-muted text-truncate" style="max-width:400px;font-size:.8rem;">
                {{ $template->content }}
              </div>
            </td>
            <td class="d-none d-md-table-cell text-muted font-monospace small">
              {{ $template->updated_at->translatedFormat('d M Y, H:i') }}
            </td>
            <td class="px-4 text-end">
              <div class="d-flex justify-content-end gap-1">
                <a href="{{ route('admin.notification-templates.edit', $template->id) }}"
                   class="das-table-btn das-table-btn--info" title="Edit Redaksi" data-bs-toggle="tooltip">
                  <i class="ti tabler-pencil fs-5"></i>
                </a>
                <button type="button" class="das-table-btn das-table-btn--danger" title="Hapus Redaksi"
                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                        data-id="{{ $template->id }}"
                        data-name="{{ $types[$template->type] ?? $template->type }}"
                        data-url="{{ route('admin.notification-templates.destroy', $template->id) }}">
                  <i class="ti tabler-trash fs-5"></i>
                </button>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="py-5 text-center">
              <div class="d-flex flex-column align-items-center gap-2 opacity-30">
                <i class="ti tabler-message-off" style="font-size:3rem;"></i>
                <span class="small font-monospace">Belum ada redaksi notifikasi</span>
              </div>
              <a href="{{ route('admin.notification-templates.create') }}" class="das-btn das-btn--primary mt-3">
                Buat Redaksi Pertama
              </a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($templates->hasPages())
    <div class="px-4 py-3 border-top" style="border-color:var(--das-border)!important;">
      {{ $templates->links('vendor.pagination.users') }}
    </div>
    @endif
  </div>

  {{-- ── MODAL DELETE ─────────────────────────────────── --}}
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title"><i class="ti tabler-alert-triangle me-2 text-danger"></i>Hapus Redaksi</h5>
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
              <h4 class="text-white mb-2">Hapus <span id="delName" class="text-danger"></span>?</h4>
              <p class="text-muted small">Tindakan ini tidak dapat dibatalkan. Redaksi ini akan dihapus permanen dari sistem.</p>
            </div>
            <div class="d-flex gap-2 justify-content-center pt-2">
              <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="das-btn das-btn--danger px-4">
                <i class="ti tabler-trash-x me-1"></i> Hapus Sekarang
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ── MODAL IMPORT ─────────────────────────────────── --}}
  <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between"
             style="background:rgba(0,207,232,.05);border-bottom-color:rgba(0,207,232,.15);">
          <h5 class="das-modal-title"><i class="ti tabler-upload me-2" style="color:var(--das-info);"></i>Import Template Notifikasi</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.notification-templates.import') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="das-modal-body">
            <div class="text-center mb-4">
              <div class="d-flex align-items-center justify-content-center mx-auto mb-3"
                   style="width:70px;height:70px;background:rgba(0,207,232,.1);border:1px solid rgba(0,207,232,.2);border-radius:5px;">
                <i class="ti tabler-file-import" style="color:var(--das-info);font-size:1.75rem;"></i>
              </div>
              <h4 class="text-white mb-2" style="font-size:1rem;">Upload File JSON</h4>
              <p class="text-muted small mb-0">Upload file JSON hasil export untuk mengimpor template notifikasi. Template dengan <code class="text-info">type</code> yang sudah ada akan diperbarui kontennya.</p>
            </div>

            <div class="mb-4">
              <label for="import_file" class="form-label text-white-50 small fw-bold mb-2">
                <i class="ti tabler-file-text me-1"></i> File JSON
              </label>
              <input type="file" name="import_file" id="import_file" class="form-control"
                     accept=".json,application/json" required>
              <div class="form-text text-muted small mt-2">
                <i class="ti tabler-info-circle me-1"></i> Format: JSON, maksimal 2 MB.
              </div>
            </div>

            <div class="d-flex gap-2 justify-content-center pt-2">
              <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="das-btn das-btn--info px-4">
                <i class="ti tabler-upload me-1"></i> Import Sekarang
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
  const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltips.map(el => new bootstrap.Tooltip(el));

  const delModal = document.getElementById('deleteModal');
  if (delModal) {
    delModal.addEventListener('show.bs.modal', function (e) {
      const btn = e.relatedTarget;
      delModal.querySelector('#delName').textContent = btn.getAttribute('data-name');
      delModal.querySelector('#delForm').action = btn.getAttribute('data-url');
    });
  }

  // AJAX search & filter
  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    const searchInput = filterForm.querySelector('input[name="search"]');
    const typeSelect = filterForm.querySelector('select[name="type"]');
    const tableCard = document.getElementById('templatesTableCard');
    const resetButton = filterForm.querySelector('a.das-btn--secondary');

    let searchTimeout;
    let lastSearch = searchInput.value.trim();

    function fetchData(url) {
      if (tableCard) {
        tableCard.style.opacity = '0.5';
        tableCard.style.pointerEvents = 'none';
      }

      fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(res => res.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newCard = doc.getElementById('templatesTableCard');
        if (tableCard && newCard) {
          tableCard.innerHTML = newCard.innerHTML;
        }
        // re-init tooltips
        const newTooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        newTooltips.map(el => new bootstrap.Tooltip(el));
      })
      .catch(err => {
        console.error('Fetch error:', err);
      })
      .finally(() => {
        if (tableCard) {
          tableCard.style.opacity = '1';
          tableCard.style.pointerEvents = 'auto';
        }
      });
    }

    function triggerSearch() {
      const params = new URLSearchParams();
      const searchValue = searchInput.value.trim();
      const typeValue = typeSelect.value;

      if (searchValue) {
        params.append('search', searchValue);
      }
      if (typeValue) {
        params.append('type', typeValue);
      }

      const url = `${window.location.pathname}?${params.toString()}`;
      fetchData(url);
    }

    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      const val = searchInput.value.trim();
      if (val !== lastSearch) {
        lastSearch = val;
        searchTimeout = setTimeout(triggerSearch, 400);
      }
    });

    typeSelect.addEventListener('change', triggerSearch);

    filterForm.addEventListener('submit', function(e) {
      e.preventDefault();
      triggerSearch();
    });

    if (resetButton) {
      resetButton.addEventListener('click', function(e) {
        e.preventDefault();
        filterForm.reset();
        searchInput.value = '';
        typeSelect.value = '';
        lastSearch = '';
        triggerSearch();
      });
    }

    // AJAX pagination clicks
    if (tableCard) {
      tableCard.addEventListener('click', function(e) {
        const link = e.target.closest('a.das-page-btn');
        if (link) {
          e.preventDefault();
          fetchData(link.href);
        }
      });
    }
  }
});
</script>
@endsection
