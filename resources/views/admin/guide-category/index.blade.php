@extends('layouts/layoutMaster')

@section('title', 'Kategori Panduan')

@section('page-style')
  <style>
    /* ═══════════════════════════════════════════════════════
       DASHBOARD DESIGN SYSTEM (ADAPTED)
    ═══════════════════════════════════════════════════════ */
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
      --das-secondary: #a8aaae;
      --das-surface: rgba(15, 23, 42, 0.4);
      --das-surface-hover: rgba(30, 41, 59, 0.6);
      --das-border: rgba(255, 255, 255, 0.06);
      --das-border-hover: rgba(255, 255, 255, 0.12);
      --das-radius: 5px;
    }

    .das-hero {
      position: relative;
      border-radius: var(--das-radius);
      overflow: hidden;
      margin-bottom: 2rem;
    }
    .das-hero__bg {
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%);
      z-index: 0;
    }
    .das-hero__glass {
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top right, rgba(115, 103, 240, 0.15), transparent 40%);
      z-index: 1;
    }
    .das-hero__grid-lines {
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
      background-size: 40px 40px;
      z-index: 1;
    }
    .das-hero__inner {
      position: relative;
      z-index: 2;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 2.5rem;
      gap: 1.5rem;
      flex-wrap: wrap;
    }
    .das-hero__identity {
      display: flex;
      align-items: center;
      gap: 1.25rem;
    }
    .das-hero__icon {
      width: 64px;
      height: 64px;
      background: rgba(115, 103, 240, 0.2);
      border: 1px solid rgba(115, 103, 240, 0.3);
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.75rem;
      color: #a5a2f7;
    }
    .das-hero__title {
      font-size: 1.5rem;
      font-weight: 800;
      color: white;
      margin: 0 0 4px;
    }
    .das-hero__welcome {
      margin: 0;
      font-size: 0.88rem;
      color: rgba(255, 255, 255, 0.6);
    }

    .das-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      border: 1px solid transparent;
      cursor: pointer;
      transition: all 0.18s ease;
      text-decoration: none;
      white-space: nowrap;
    }
    .das-btn--primary {
      background: var(--das-primary);
      color: white !important;
      border-color: var(--das-primary);
    }
    .das-btn--primary:hover {
      background: #6259e8;
      transform: translateY(-2px);
    }
    .das-btn--ghost {
      background: transparent;
      border-color: var(--das-border);
      color: #999 !important;
    }
    .das-btn--ghost:hover {
      background: var(--das-surface-hover);
      color: white !important;
    }

    .das-panel {
      background: var(--das-surface);
      border: 1px solid var(--das-border);
      border-radius: var(--das-radius);
      overflow: hidden;
      backdrop-filter: blur(6px);
    }
    .das-panel__head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.9rem 1.25rem;
      border-bottom: 1px solid var(--das-border);
    }
    .das-panel__title {
      font-size: 0.82rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      display: flex;
      align-items: center;
      gap: 8px;
      color: #ccc;
    }
    .das-panel__icon-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--das-info);
      box-shadow: 0 0 6px var(--das-info);
    }

    .das-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.82rem;
    }
    .das-table thead th {
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #666;
      padding: 0.75rem 1rem;
      border-bottom: 1px solid var(--das-border);
      background: rgba(255, 255, 255, 0.02);
    }
    .das-table tbody td {
      padding: 0.75rem 1rem;
      border-bottom: 1px solid var(--das-border);
      color: #ccc;
      vertical-align: middle;
      transition: background 0.2s ease;
    }
    .das-table tbody tr:hover td {
      background: var(--das-surface-hover);
    }

    .das-chip {
      display: inline-flex;
      align-items: center;
      font-size: 0.65rem;
      font-weight: 700;
      padding: 2px 10px;
      border-radius: 20px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .das-chip--danger { background: var(--das-danger-soft); color: var(--das-danger); }
    .das-chip--warning { background: var(--das-warning-soft); color: var(--das-warning); }
    .das-chip--info { background: var(--das-info-soft); color: var(--das-info); }
    .das-chip--success { background: var(--das-success-soft); color: var(--das-success); }
    .das-chip--secondary { background: rgba(168, 170, 174, 0.12); color: #a8aaae; }
    .das-chip--primary { background: var(--das-primary-soft); color: var(--das-primary); }

    .das-table-btn {
      width: 30px;
      height: 30px;
      border-radius: 5px;
      border: 1px solid var(--das-border);
      background: transparent;
      color: #888;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
      text-decoration: none;
    }
    .das-table-btn:hover {
      background: var(--das-surface-hover);
      color: white;
      transform: translateY(-2px);
    }
    .das-table-btn--info:hover { color: var(--das-info); border-color: var(--das-info); }
    .das-table-btn--danger:hover { color: var(--das-danger); border-color: var(--das-danger); }

    .das-modal {
      background: #1a1a2e !important;
      border: 1px solid var(--das-border) !important;
      border-radius: var(--das-radius) !important;
      overflow: hidden;
    }
    .das-modal-head {
      border-bottom: 1px solid var(--das-border);
      background: rgba(115, 103, 240, 0.05);
      padding: 1.25rem;
    }
    .das-modal-title {
      font-size: 1rem;
      font-weight: 700;
      color: #fff;
      margin: 0;
    }
    .das-modal-body { padding: 1.5rem; }

    .filter-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid var(--das-border) !important;
      color: #ccc !important;
      height: 38px;
      font-size: 0.82rem;
      border-radius: 5px;
      cursor: pointer;
    }
    .filter-select:focus {
      border-color: var(--das-primary) !important;
      box-shadow: 0 0 0 2px var(--das-primary-soft) !important;
    }
    .filter-select option { background: #1a1a2e; color: #ccc; }
    #searchInput::placeholder { color: rgba(255,255,255,0.3); }
    #searchInput:focus {
      outline: none;
      box-shadow: none;
      background: rgba(255,255,255,0.08) !important;
      border-color: rgba(115,103,240,0.5) !important;
    }

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
      border: 1px solid var(--das-border);
      background: transparent;
      color: #888;
      text-decoration: none;
      transition: all 0.18s ease;
      cursor: pointer;
    }
    .das-page-btn:hover {
      background: var(--das-surface-hover);
      color: #fff;
      border-color: var(--das-border-hover);
    }
    .das-page-active {
      background: var(--das-primary) !important;
      color: #fff !important;
      border-color: var(--das-primary) !important;
    }

    @keyframes slideInUp {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .slide-in-up { animation: slideInUp 0.5s ease-out; }
  </style>
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       HERO HEADER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero slide-in-up">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__icon">
          <i class="ti tabler-folder"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:0.65rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">
              <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a></li>
              <li class="breadcrumb-item active text-white opacity-100">Kategori Panduan</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">Kategori Panduan</h4>
          <p class="das-hero__welcome">Kelola kategori untuk mengelompokkan artikel panduan pengguna.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.guide-categories.create') }}" class="das-btn das-btn--primary shadow-sm">
          <i class="ti tabler-plus me-1"></i> Tambah Kategori Baru
        </a>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGES --}}
  @foreach (['success', 'error', 'info'] as $msg)
    @if (session($msg))
      <div class="alert alert-{{ $msg === 'success' ? 'success' : ($msg === 'info' ? 'info' : 'danger') }} alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-lg slide-in-up"
        role="alert" style="border-radius:8px; background: rgba(0,0,0,0.3); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1) !important;">
        <i class="ti tabler-{{ $msg === 'success' ? 'circle-check' : 'alert-circle' }} fs-4 text-{{ $msg === 'success' ? 'success' : ($msg === 'info' ? 'info' : 'danger') }}"></i>
        <div class="text-white small fw-medium">{{ session($msg) }}</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
      </div>
    @endif
  @endforeach

  {{-- MAIN TABLE PANEL --}}
  <div class="das-panel slide-in-up">
    <div class="das-panel__head flex-wrap gap-3">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot"></span>
        Daftar Kategori
      </div>

      <div class="d-flex align-items-center gap-3 flex-grow-1 justify-content-md-end">
        <div class="position-relative flex-grow-1 flex-md-grow-0" style="max-width: 250px;">
          <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size:0.85rem; pointer-events:none;"></i>
          <input type="text" id="searchInput" class="form-control border-0 text-white"
                 placeholder="Cari kategori..."
                 style="background: rgba(255,255,255,0.05); height: 38px; font-size: 0.82rem; padding-left: 2.4rem;">
        </div>

        <select id="perPageSelect" class="filter-select form-select w-auto">
          <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
        </select>

        <span class="das-chip das-chip--info d-none d-sm-inline-flex" id="totalCount">{{ $categories->total() }} Total</span>
      </div>
    </div>

    <div id="categoryTableContainer">
      <div class="table-responsive">
        <table class="das-table">
          <thead>
            <tr>
              <th class="text-center" width="50">#</th>
              <th>Nama Kategori</th>
              <th class="text-center">Slug</th>
              <th class="text-center">Induk</th>
              <th class="text-center">Jumlah Panduan</th>
              <th class="text-end px-4">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($categories as $cat)
              <tr>
                <td class="text-center text-muted font-monospace small">
                  {{ $categories->firstItem() + $loop->index }}
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    @if ($cat->icon)
                      <i class="ti {{ $cat->icon }} text-primary" style="font-size:1.1rem;"></i>
                    @else
                      <i class="ti tabler-folder text-muted" style="font-size:1.1rem;"></i>
                    @endif
                    <span class="fw-bold text-white">{{ $cat->name }}</span>
                    @if($cat->children->count() > 0)
                      <span class="das-chip das-chip--info" style="font-size:0.55rem;">{{ $cat->children->count() }} sub</span>
                    @endif
                  </div>
                </td>
                <td class="text-center">
                  <code class="text-muted small">{{ $cat->slug }}</code>
                </td>
                <td class="text-center">
                  @if ($cat->parent)
                    <span class="das-chip das-chip--secondary">{{ $cat->parent->name }}</span>
                  @else
                    <span class="text-muted small">—</span>
                  @endif
                </td>
                <td class="text-center">
                  <span class="das-chip das-chip--primary">{{ $cat->guides_count }} artikel</span>
                </td>
                <td class="px-4 text-end">
                  <div class="d-flex justify-content-end gap-1">
                    <a href="{{ route('admin.guide-categories.edit', $cat) }}" class="das-table-btn das-table-btn--info" title="Edit Kategori" data-bs-toggle="tooltip">
                      <i class="ti tabler-pencil fs-5"></i>
                    </a>
                    <button type="button" class="das-table-btn das-table-btn--danger" title="Hapus Kategori"
                      data-bs-toggle="modal" data-bs-target="#deleteModal"
                      data-name="{{ $cat->name }}"
                      data-url="{{ route('admin.guide-categories.destroy', $cat) }}">
                      <i class="ti tabler-trash fs-5"></i>
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="py-5 text-center">
                  <div class="d-flex flex-column align-items-center gap-2 opacity-30">
                    <i class="ti tabler-folder-off" style="font-size:3rem;"></i>
                    <span class="small font-monospace">Belum ada kategori</span>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($categories->hasPages())
        <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
          <nav>
            <ul class="pagination justify-content-center mb-0 gap-1">
              <li class="page-item {{ $categories->onFirstPage() ? 'disabled' : '' }}">
                <a class="das-page-btn" href="{{ $categories->previousPageUrl() }}" aria-label="Previous">
                  <i class="ti tabler-chevron-left"></i>
                </a>
              </li>
              @foreach ($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                @if ($page == $categories->currentPage())
                  <li class="page-item active"><span class="das-page-btn das-page-active">{{ $page }}</span></li>
                @else
                  <li class="page-item"><a class="das-page-btn" href="{{ $url }}">{{ $page }}</a></li>
                @endif
              @endforeach
              <li class="page-item {{ !$categories->hasMorePages() ? 'disabled' : '' }}">
                <a class="das-page-btn" href="{{ $categories->nextPageUrl() }}" aria-label="Next">
                  <i class="ti tabler-chevron-right"></i>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      @endif
    </div>
  </div>

  {{-- MODAL DELETE --}}
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between" style="background: rgba(234,84,85,0.05);">
          <h5 class="das-modal-title"><i class="ti tabler-alert-triangle me-2 text-danger"></i>Hapus Kategori</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="delForm" method="POST">
          @csrf @method('DELETE')
          <div class="das-modal-body text-center">
            <div class="mb-4">
              <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                   style="width:70px; height:70px; background: rgba(234,84,85,0.1); border: 1px solid rgba(234,84,85,0.2);">
                <i class="ti tabler-folder-x text-danger fs-1"></i>
              </div>
              <h4 class="text-white mb-2">Hapus <span id="delName" class="text-danger"></span>?</h4>
              <p class="text-muted small">Kategori akan dihapus permanen. Sub-kategori akan dipindahkan ke induknya jika ada.</p>
            </div>
            <div class="d-flex gap-2 justify-content-center pt-2">
              <button type="button" class="das-btn das-btn--ghost min-w-100" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="das-btn px-4 shadow-sm" style="background-color: var(--das-danger); border-color: var(--das-danger); color:white;">Hapus Sekarang</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const container = document.getElementById('categoryTableContainer');
      const searchInput = document.getElementById('searchInput');
      const perPageSelect = document.getElementById('perPageSelect');
      let searchTimeout;

      function fetchData(page = 1) {
        const search = searchInput.value;
        const perPage = perPageSelect.value;
        const url = `{{ route('admin.guide-categories.index') }}?page=${page}&search=${encodeURIComponent(search)}&per_page=${perPage}`;

        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

        fetch(url, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
          container.innerHTML = html;
          container.style.opacity = '1';
          container.style.pointerEvents = 'auto';

          const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
          });
        })
        .catch(error => {
          console.error('Error fetching data:', error);
          container.style.opacity = '1';
          container.style.pointerEvents = 'auto';
        });
      }

      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchData(1), 500);
      });

      perPageSelect.addEventListener('change', () => fetchData(1));

      container.addEventListener('click', function(e) {
        const link = e.target.closest('a.das-page-btn');
        if (link) {
          e.preventDefault();
          const page = link.dataset.page || new URL(link.href).searchParams.get('page') || 1;
          fetchData(page);
        }
      });

      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      const delModal = document.getElementById('deleteModal');
      if (delModal) {
        delModal.addEventListener('show.bs.modal', function(event) {
          const btn = event.relatedTarget;
          delModal.querySelector('#delName').textContent = btn.getAttribute('data-name');
          delModal.querySelector('#delForm').action = btn.getAttribute('data-url');
        });
      }
    });
  </script>
@endsection
