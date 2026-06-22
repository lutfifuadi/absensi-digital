@extends('layouts/layoutMaster')

@section('title', isset($guide) ? 'Edit Panduan — ' . $guide->title : 'Tambah Panduan Baru')

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
      min-height: 120px;
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

    /* Role checkboxes */
    .role-checkbox-group {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .role-checkbox-item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid var(--das-border);
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.18s ease;
      font-size: 0.82rem;
      color: #ccc;
    }
    .role-checkbox-item:hover {
      background: rgba(115, 103, 240, 0.08);
      border-color: rgba(115, 103, 240, 0.3);
    }
    .role-checkbox-item input:checked + span {
      color: #fff;
    }
    .role-checkbox-item:has(input:checked) {
      background: var(--das-primary-soft);
      border-color: var(--das-primary);
      color: #fff;
    }
    .role-checkbox-item input[type="checkbox"] {
      accent-color: var(--das-primary);
      width: 16px;
      height: 16px;
    }

    /* Featured image preview */
    .featured-preview {
      max-width: 200px;
      max-height: 120px;
      border-radius: 5px;
      border: 1px solid var(--das-border);
      object-fit: cover;
      margin-top: 8px;
    }

    /* Content textarea */
    #content {
      min-height: 400px;
      font-family: 'Consolas', 'Courier New', monospace;
      font-size: 0.82rem;
      line-height: 1.6;
    }

    /* ANIMATIONS */
    @keyframes slideInUp {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .slide-in-up { animation: slideInUp 0.5s ease-out; }

    /* Custom checkbox toggle for is_featured */
    .das-toggle {
      position: relative;
      width: 44px;
      height: 24px;
      flex-shrink: 0;
    }
    .das-toggle input {
      opacity: 0;
      width: 0;
      height: 0;
    }
    .das-toggle .slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(255,255,255,0.1);
      border-radius: 24px;
      transition: 0.3s;
    }
    .das-toggle .slider::before {
      content: '';
      position: absolute;
      height: 18px;
      width: 18px;
      left: 3px;
      bottom: 3px;
      background: #888;
      border-radius: 50%;
      transition: 0.3s;
    }
    .das-toggle input:checked + .slider {
      background: var(--das-primary);
    }
    .das-toggle input:checked + .slider::before {
      transform: translateX(20px);
      background: white;
    }

    /* Slug auto-generate indicator */
    .slug-preview {
      font-size: 0.78rem;
      color: #888;
      padding: 4px 0;
    }
  </style>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const titleInput = document.getElementById('title');
      const slugInput = document.getElementById('slug');

      // Auto-generate slug from title
      if (titleInput && slugInput) {
        let slugManuallyEdited = slugInput.value.length > 0;

        slugInput.addEventListener('input', function() {
          slugManuallyEdited = true;
        });

        titleInput.addEventListener('input', function() {
          if (!slugManuallyEdited) {
            slugInput.value = titleInput.value
              .toLowerCase()
              .replace(/[^a-z0-9\s-]/g, '')
              .replace(/\s+/g, '-')
              .replace(/-+/g, '-')
              .replace(/^-+|-+$/g, '');
          }
        });

        // Reset manual flag if slug is empty (new form)
        if (!slugInput.value) {
          slugManuallyEdited = false;
        }
      }

      // Featured image preview
      const featuredInput = document.getElementById('featured_image');
      const previewContainer = document.getElementById('featuredPreview');
      if (featuredInput && previewContainer) {
        featuredInput.addEventListener('change', function() {
          const file = this.files[0];
          if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
              previewContainer.innerHTML = `<img src="${e.target.result}" class="featured-preview" alt="Preview">`;
            };
            reader.readAsDataURL(file);
          }
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
          <i class="ti tabler-{{ isset($guide) ? 'edit' : 'book-plus' }}"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:0.6rem; text-transform:uppercase; letter-spacing:1px; opacity:0.6;">
              <li class="breadcrumb-item"><a href="{{ route('admin.guides.index') }}" class="text-white text-decoration-none">Manajemen Panduan</a></li>
              <li class="breadcrumb-item active text-white">{{ isset($guide) ? 'Edit Panduan' : 'Tambah Panduan' }}</li>
            </ol>
          </nav>
          <h4 class="das-hero-mini__title">{{ isset($guide) ? 'Sunting Panduan' : 'Buat Panduan Baru' }}</h4>
        </div>
      </div>
    </div>

    {{-- MAIN FORM PANEL --}}
    <div class="das-panel">
      <div class="das-panel__body">
        <form action="{{ isset($guide) ? route('admin.guides.update', $guide) : route('admin.guides.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          @if (isset($guide))
            @method('PUT')
          @endif

          <div class="row gy-4">
            {{-- Title --}}
            <div class="col-md-8">
              <label class="form-label">Judul Panduan <span class="text-danger">*</span></label>
              <input type="text" name="title" id="title"
                class="form-control @error('title') is-invalid @enderror"
                placeholder="Masukkan judul panduan"
                value="{{ old('title', $guide->title ?? '') }}" required>
              @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Order --}}
            <div class="col-md-2">
              <label class="form-label">Urutan</label>
              <input type="number" name="order" class="form-control @error('order') is-invalid @enderror"
                placeholder="0" value="{{ old('order', $guide->order ?? 0) }}" min="0">
              @error('order')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- is_featured --}}
            <div class="col-md-2 d-flex align-items-end pb-2">
              <label class="d-flex align-items-center gap-3 cursor-pointer" style="cursor:pointer;">
                <div class="das-toggle">
                  <input type="hidden" name="is_featured" value="0">
                  <input type="checkbox" name="is_featured" value="1"
                    {{ old('is_featured', $guide->is_featured ?? false) ? 'checked' : '' }}>
                  <span class="slider"></span>
                </div>
                <span class="form-label mb-0" style="text-transform:none; letter-spacing:0;">Featured</span>
              </label>
            </div>

            {{-- Slug --}}
            <div class="col-md-6">
              <label class="form-label">Slug</label>
              <input type="text" name="slug" id="slug"
                class="form-control @error('slug') is-invalid @enderror"
                placeholder="auto-generated-from-title"
                value="{{ old('slug', $guide->slug ?? '') }}">
              <div class="slug-preview">Biarkan kosong untuk auto-generate dari judul</div>
              @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Category --}}
            <div class="col-md-6">
              <label class="form-label">Kategori</label>
              <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                <option value="">— Pilih Kategori —</option>
                @foreach ($categories as $cat)
                  <option value="{{ $cat->id }}"
                    {{ old('category_id', $guide->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                  </option>
                @endforeach
              </select>
              @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Role Target --}}
            <div class="col-12">
              <label class="form-label">Target Role (centang role yang relevan)</label>
              <div class="role-checkbox-group">
                @php
                  $selectedRoles = old('role_target', $guide->role_target ?? '');
                  $selectedRolesArr = is_array($selectedRoles) ? $selectedRoles : (strlen($selectedRoles) > 0 ? explode(',', $selectedRoles) : []);
                @endphp
                @foreach ($roles as $key => $label)
                  <label class="role-checkbox-item">
                    <input type="checkbox" name="role_target[]" value="{{ $key }}"
                      {{ in_array($key, $selectedRolesArr) ? 'checked' : '' }}>
                    <span>{{ $label }}</span>
                  </label>
                @endforeach
              </div>
              <div class="form-text text-white-50 small mt-1">Pilih role yang akan melihat panduan ini. Jika tidak ada yang dipilih, hanya admin yang bisa melihat.</div>
              @error('role_target')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            {{-- Status --}}
            <div class="col-md-4">
              <label class="form-label">Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="draft" {{ old('status', $guide->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', $guide->status ?? '') == 'published' ? 'selected' : '' }}>Published</option>
                <option value="archived" {{ old('status', $guide->status ?? '') == 'archived' ? 'selected' : '' }}>Archived</option>
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Featured Image --}}
            <div class="col-md-4">
              <label class="form-label">Featured Image</label>
              <input type="file" name="featured_image" id="featured_image"
                class="form-control @error('featured_image') is-invalid @enderror"
                accept="image/jpg,image/jpeg,image/png,image/webp">
              @error('featured_image')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div id="featuredPreview">
                @if (isset($guide) && $guide->featured_image)
                  <img src="{{ asset('storage/' . $guide->featured_image) }}" class="featured-preview" alt="Current featured image">
                @endif
              </div>
            </div>

            {{-- Published At --}}
            <div class="col-md-4">
              <label class="form-label">Tanggal Publikasi</label>
              <input type="datetime-local" name="published_at"
                class="form-control @error('published_at') is-invalid @enderror"
                value="{{ old('published_at', isset($guide) && $guide->published_at ? $guide->published_at->format('Y-m-d\TH:i') : '') }}">
              @error('published_at')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Excerpt --}}
            <div class="col-12">
              <label class="form-label">Ringkasan (Excerpt)</label>
              <textarea name="excerpt" class="form-control @error('excerpt') is-invalid @enderror"
                placeholder="Ringkasan singkat panduan (akan auto-generate dari konten jika dikosongkan)"
                rows="3">{{ old('excerpt', $guide->excerpt ?? '') }}</textarea>
              @error('excerpt')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Content --}}
            <div class="col-12">
              <label class="form-label">Konten <span class="text-danger">*</span></label>
              <textarea name="content" id="content"
                class="form-control @error('content') is-invalid @enderror"
                placeholder="Tulis konten panduan di sini... (HTML support)">{{ old('content', $guide->content ?? '') }}</textarea>
              @error('content')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text text-white-50 small mt-1">
                Konten mendukung format HTML. Anda bisa menggunakan tag &lt;h1&gt;–&lt;h6&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;img&gt;, dsb.
              </div>
            </div>

            {{-- Metadata (hidden) --}}
            <input type="hidden" name="metadata" value="{{ old('metadata', $guide->metadata ?? 'null') }}">
          </div>

          <div class="mt-5 d-flex gap-2">
            <button type="submit" class="das-btn das-btn--primary">
              <i class="ti tabler-device-floppy me-1"></i> {{ isset($guide) ? 'Simpan Perubahan' : 'Simpan Panduan' }}
            </button>
            <a href="{{ route('admin.guides.index') }}" class="das-btn das-btn--ghost">
              Batal
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
