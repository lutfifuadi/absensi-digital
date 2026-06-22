@extends('layouts/layoutMaster')

@section('title', isset($category) ? 'Edit Kategori — ' . $category->name : 'Tambah Kategori Baru')

@section('page-style')
  <style>
    /* ═══════════════════════════════════════════════════════
       DASHBOARD DESIGN SYSTEM (ADAPTED)
    ═══════════════════════════════════════════════════════ */
    :root {
      --das-primary: #7367f0;
      --das-primary-soft: rgba(115, 103, 240, 0.12);
      --das-surface: rgba(15, 23, 42, 0.4);
      --das-border: rgba(255, 255, 255, 0.06);
      --das-radius: 5px;
    }

    .das-hero-mini {
      position: relative;
      border-radius: var(--das-radius);
      overflow: hidden;
      margin-bottom: 2rem;
      background: linear-gradient(135deg, #1e1b4b 0%, #312d89 60%, #4338ca 100%);
    }
    .das-hero-mini__inner {
      position: relative;
      z-index: 2;
      padding: 2rem 2.5rem;
      display: flex;
      align-items: center;
      gap: 1.25rem;
    }
    .das-hero-mini__icon {
      width: 52px;
      height: 52px;
      background: rgba(115, 103, 240, 0.2);
      border: 1px solid rgba(115, 103, 240, 0.3);
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: #a5a2f7;
    }
    .das-hero-mini__title {
      font-size: 1.25rem;
      font-weight: 800;
      color: white;
      margin: 0;
    }

    .das-panel {
      background: var(--das-surface);
      border: 1px solid var(--das-border);
      border-radius: var(--das-radius);
      overflow: hidden;
      backdrop-filter: blur(8px);
    }
    .das-panel__body {
      padding: 2.5rem;
    }

    .form-label {
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #777;
      margin-bottom: 0.5rem;
    }
    .form-control, .form-select {
      background: rgba(255, 255, 255, 0.03) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      color: #fff !important;
      border-radius: 5px;
      padding: 0.65rem 1rem;
      font-size: 0.88rem;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--das-primary) !important;
      box-shadow: 0 0 0 2px var(--das-primary-soft) !important;
      background: rgba(255, 255, 255, 0.05) !important;
    }
    .form-control.is-invalid {
      border-color: #ea5455 !important;
    }
    textarea.form-control {
      min-height: 100px;
    }

    .das-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.8rem;
      font-weight: 700;
      padding: 0.65rem 1.5rem;
      border-radius: 5px;
      border: 1px solid transparent;
      cursor: pointer;
      transition: all 0.18s ease;
      text-decoration: none;
    }
    .das-btn--primary {
      background: var(--das-primary);
      color: white !important;
      border-color: var(--das-primary);
    }
    .das-btn--primary:hover {
      background: #6259e8;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(115, 103, 240, 0.3);
    }
    .das-btn--ghost {
      background: transparent;
      border-color: var(--das-border);
      color: #999 !important;
    }
    .das-btn--ghost:hover {
      background: rgba(255, 255, 255, 0.05);
      color: white !important;
    }

    .slug-preview {
      font-size: 0.78rem;
      color: #888;
      padding: 4px 0;
    }

    /* Icon picker grid */
    .icon-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      max-height: 200px;
      overflow-y: auto;
      padding: 12px;
      background: rgba(0,0,0,0.15);
      border-radius: 5px;
      border: 1px solid var(--das-border);
    }
    .icon-grid::-webkit-scrollbar {
      width: 4px;
    }
    .icon-grid::-webkit-scrollbar-thumb {
      background: rgba(255,255,255,0.15);
      border-radius: 4px;
    }
    .icon-option {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 42px;
      height: 42px;
      border-radius: 5px;
      border: 1px solid var(--das-border);
      background: transparent;
      color: #aaa;
      font-size: 1.2rem;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .icon-option:hover {
      background: rgba(115, 103, 240, 0.1);
      border-color: rgba(115, 103, 240, 0.3);
      color: #fff;
    }
    .icon-option.selected {
      background: var(--das-primary-soft);
      border-color: var(--das-primary);
      color: var(--das-primary);
    }

    @keyframes slideInUp {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .slide-in-up { animation: slideInUp 0.5s ease-out; }
  </style>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Auto-generate slug from name
      const nameInput = document.getElementById('name');
      const slugInput = document.getElementById('slug');
      if (nameInput && slugInput) {
        let slugManuallyEdited = slugInput.value.length > 0;

        slugInput.addEventListener('input', function() {
          slugManuallyEdited = true;
        });

        nameInput.addEventListener('input', function() {
          if (!slugManuallyEdited) {
            slugInput.value = nameInput.value
              .toLowerCase()
              .replace(/[^a-z0-9\s-]/g, '')
              .replace(/\s+/g, '-')
              .replace(/-+/g, '-')
              .replace(/^-+|-+$/g, '');
          }
        });

        if (!slugInput.value) {
          slugManuallyEdited = false;
        }
      }

      // Icon picker
      const iconInput = document.getElementById('icon');
      const iconOptions = document.querySelectorAll('.icon-option');
      if (iconOptions.length > 0) {
        iconOptions.forEach(function(el) {
          el.addEventListener('click', function() {
            iconOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            iconInput.value = this.dataset.icon;
          });
        });
      }
    });
  </script>
@endsection

@section('content')
  <div class="slide-in-up">
    {{-- HERO MINI --}}
    <div class="das-hero-mini">
      <div class="das-hero-mini__inner">
        <div class="das-hero-mini__icon">
          <i class="ti tabler-{{ isset($category) ? 'edit' : 'folder-plus' }}"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:0.6rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">
              <li class="breadcrumb-item"><a href="{{ route('admin.guide-categories.index') }}" class="text-white text-decoration-none">Kategori Panduan</a></li>
              <li class="breadcrumb-item active text-white">{{ isset($category) ? 'Edit Kategori' : 'Tambah Kategori' }}</li>
            </ol>
          </nav>
          <h4 class="das-hero-mini__title">{{ isset($category) ? 'Sunting Kategori' : 'Buat Kategori Baru' }}</h4>
        </div>
      </div>
    </div>

    {{-- MAIN FORM PANEL --}}
    <div class="das-panel">
      <div class="das-panel__body">
        <form action="{{ isset($category) ? route('admin.guide-categories.update', $category) : route('admin.guide-categories.store') }}" method="POST">
          @csrf
          @if (isset($category))
            @method('PUT')
          @endif

          <div class="row gy-4">
            {{-- Name --}}
            <div class="col-md-6">
              <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
              <input type="text" name="name" id="name"
                class="form-control @error('name') is-invalid @enderror"
                placeholder="Masukkan nama kategori"
                value="{{ old('name', $category->name ?? '') }}" required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Order --}}
            <div class="col-md-3">
              <label class="form-label">Urutan</label>
              <input type="number" name="order" class="form-control @error('order') is-invalid @enderror"
                placeholder="0" value="{{ old('order', $category->order ?? 0) }}" min="0">
              @error('order')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Parent Category --}}
            <div class="col-md-3">
              <label class="form-label">Kategori Induk</label>
              <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                <option value="">— None (Root) —</option>
                @foreach ($parentCategories as $parent)
                  <option value="{{ $parent->id }}"
                    {{ old('parent_id', $category->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                    {{ $parent->name }}
                  </option>
                @endforeach
              </select>
              @error('parent_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Slug --}}
            <div class="col-md-6">
              <label class="form-label">Slug</label>
              <input type="text" name="slug" id="slug"
                class="form-control @error('slug') is-invalid @enderror"
                placeholder="auto-generated-from-name"
                value="{{ old('slug', $category->slug ?? '') }}">
              <div class="slug-preview">Biarkan kosong untuk auto-generate dari nama</div>
              @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Icon --}}
            <div class="col-md-6">
              <label class="form-label">Icon</label>
              <input type="text" name="icon" id="icon"
                class="form-control @error('icon') is-invalid @enderror"
                placeholder="tabler-icon-name atau pilih di bawah"
                value="{{ old('icon', $category->icon ?? '') }}">
              @error('icon')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text text-white-50 small mb-2">Pilih icon dari daftar Tabler Icons di bawah:</div>
              <div class="icon-grid">
                @php
                  $icons = [
                    'tabler-book', 'tabler-book-2', 'tabler-books', 'tabler-file-text', 'tabler-file-description',
                    'tabler-folder', 'tabler-folder-open', 'tabler-category', 'tabler-tags',
                    'tabler-settings', 'tabler-tool', 'tabler-wrench', 'tabler-adjustments',
                    'tabler-user', 'tabler-users', 'tabler-user-check', 'tabler-user-shield',
                    'tabler-school', 'tabler-building-school', 'tabler-door', 'tabler-chalkboard-teacher',
                    'tabler-calendar', 'tabler-clock', 'tabler-calendar-stats',
                    'tabler-clipboard-check', 'tabler-clipboard-list', 'tabler-checklist',
                    'tabler-star', 'tabler-heart', 'tabler-thumb-up', 'tabler-award',
                    'tabler-info-circle', 'tabler-help-circle', 'tabler-question-mark',
                    'tabler-phone', 'tabler-mail', 'tabler-message', 'tabler-chat',
                    'tabler-shield', 'tabler-lock', 'tabler-key', 'tabler-shield-check',
                    'tabler-database', 'tabler-server', 'tabler-cloud', 'tabler-devices',
                    'tabler-link', 'tabler-share', 'tabler-download', 'tabler-upload',
                    'tabler-search', 'tabler-filter', 'tabler-sort-ascending',
                    'tabler-home', 'tabler-dashboard', 'tabler-layout',
                    'tabler-map', 'tabler-globe', 'tabler-compass',
                  ];
                  $selectedIcon = old('icon', $category->icon ?? '');
                @endphp
                @foreach ($icons as $icon)
                  <div class="icon-option {{ $selectedIcon === $icon ? 'selected' : '' }}" data-icon="{{ $icon }}" title="{{ $icon }}">
                    <i class="ti {{ $icon }}"></i>
                  </div>
                @endforeach
              </div>
            </div>

            {{-- Description --}}
            <div class="col-12">
              <label class="form-label">Deskripsi</label>
              <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                placeholder="Deskripsi singkat tentang kategori ini (opsional)">{{ old('description', $category->description ?? '') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mt-5 d-flex gap-2">
            <button type="submit" class="das-btn das-btn--primary">
              <i class="ti tabler-device-floppy me-1"></i> {{ isset($category) ? 'Simpan Perubahan' : 'Simpan Kategori' }}
            </button>
            <a href="{{ route('admin.guide-categories.index') }}" class="das-btn das-btn--ghost">
              Batal
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
