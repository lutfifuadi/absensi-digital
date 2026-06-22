<div class="table-responsive">
  <table class="das-table">
    <thead>
      <tr>
        <th class="text-center" width="50">#</th>
        <th>Judul Panduan</th>
        <th class="text-center">Kategori</th>
        <th class="text-center">Role Target</th>
        <th class="text-center">Status</th>
        <th class="text-center d-none d-md-table-cell">Penulis</th>
        <th class="text-center d-none d-lg-table-cell">Tgl Buat</th>
        <th class="text-end px-4">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($guides as $guide)
        <tr>
          <td class="text-center text-muted font-monospace small">
            {{ $guides->firstItem() + $loop->index }}
          </td>
          <td>
            <div class="d-flex align-items-center gap-3">
              @if ($guide->featured_image)
                <img src="{{ asset('storage/' . $guide->featured_image) }}" alt="{{ $guide->title }}" class="das-thumb">
              @else
                <div class="das-thumb d-flex align-items-center justify-content-center" style="background: rgba(115,103,240,0.15); border: 1px solid var(--das-border);">
                  <i class="ti tabler-file-text text-muted" style="font-size:0.85rem;"></i>
                </div>
              @endif
              <div>
                <div class="fw-bold mb-0 text-white" style="font-size:0.85rem; max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                  {{ $guide->title }}
                  @if ($guide->is_featured)
                    <i class="ti tabler-star-filled text-warning ms-1" style="font-size:0.7rem;" title="Featured"></i>
                  @endif
                </div>
                <div class="text-muted small" style="font-size:0.72rem; max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                  {{ $guide->excerpt ? strip_tags($guide->excerpt) : '—' }}
                </div>
              </div>
            </div>
          </td>
          <td class="text-center">
            @if ($guide->category)
              <span class="das-chip das-chip--primary">{{ $guide->category->name }}</span>
            @else
              <span class="das-chip das-chip--secondary">—</span>
            @endif
          </td>
          <td class="text-center">
            @php
              $targets = $guide->role_target ? explode(',', $guide->role_target) : ['public'];
            @endphp
            <div class="d-flex flex-wrap justify-content-center gap-1" style="max-width:160px; margin:0 auto;">
              @foreach ($targets as $target)
                @php
                  $roleClass = match (trim($target)) {
                      'super_admin' => 'das-chip--danger',
                      'admin_sekolah' => 'das-chip--warning',
                      'guru' => 'das-chip--info',
                      'siswa' => 'das-chip--success',
                      'wali_kelas' => 'das-chip--success',
                      'orang_tua' => 'das-chip--primary',
                      'public' => 'das-chip--primary',
                      default => 'das-chip--secondary',
                  };
                @endphp
                <span class="das-chip {{ $roleClass }}" style="font-size:0.6rem;">{{ str_replace('_', ' ', trim(ucfirst($target))) }}</span>
              @endforeach
            </div>
          </td>
          <td class="text-center">
            @php
              $statusClass = match ($guide->status) {
                  'published' => 'das-chip--success',
                  'draft' => 'das-chip--warning',
                  'archived' => 'das-chip--secondary',
                  default => 'das-chip--secondary',
              };
            @endphp
            <span class="das-chip {{ $statusClass }}">{{ ucfirst($guide->status) }}</span>
          </td>
          <td class="text-center d-none d-md-table-cell">
            <span class="text-muted small">{{ $guide->author?->name ?? '—' }}</span>
          </td>
          <td class="text-center d-none d-lg-table-cell text-muted font-monospace small">
            {{ $guide->created_at->format('d/m/Y') }}
          </td>
          <td class="px-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              {{-- Preview link to public page --}}
              @if ($guide->status === 'published' && $guide->category)
                <a href="{{ route('guide.show', [$guide->category->slug, $guide->slug]) }}" class="das-table-btn das-table-btn--success" title="Lihat Halaman Publik" data-bs-toggle="tooltip" target="_blank">
                  <i class="ti tabler-eye fs-5"></i>
                </a>
              @endif

              <a href="{{ route('admin.guides.edit', $guide) }}" class="das-table-btn das-table-btn--info" title="Edit Panduan" data-bs-toggle="tooltip">
                <i class="ti tabler-pencil fs-5"></i>
              </a>

              <button type="button" class="das-table-btn das-table-btn--danger" title="Hapus Panduan"
                data-bs-toggle="modal" data-bs-target="#deleteModal"
                data-name="{{ $guide->title }}"
                data-url="{{ route('admin.guides.destroy', $guide) }}">
                <i class="ti tabler-trash fs-5"></i>
              </button>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="py-5 text-center">
            <div class="d-flex flex-column align-items-center gap-2 opacity-30">
              <i class="ti tabler-book-off" style="font-size:3rem;"></i>
              <span class="small font-monospace">Belum ada panduan</span>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($guides->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
    <nav>
      <ul class="pagination justify-content-center mb-0 gap-1">
        {{-- Previous Page Link --}}
        <li class="page-item {{ $guides->onFirstPage() ? 'disabled' : '' }}">
          <a class="das-page-btn" href="{{ $guides->previousPageUrl() }}" data-page="{{ $guides->currentPage() - 1 }}" aria-label="Previous">
            <i class="ti tabler-chevron-left"></i>
          </a>
        </li>

        {{-- Pagination Elements --}}
        @foreach ($guides->getUrlRange(1, $guides->lastPage()) as $page => $url)
          @if ($page == $guides->currentPage())
            <li class="page-item active">
              <span class="das-page-btn das-page-active">{{ $page }}</span>
            </li>
          @else
            <li class="page-item">
              <a class="das-page-btn" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a>
            </li>
          @endif
        @endforeach

        {{-- Next Page Link --}}
        <li class="page-item {{ !$guides->hasMorePages() ? 'disabled' : '' }}">
          <a class="das-page-btn" href="{{ $guides->nextPageUrl() }}" data-page="{{ $guides->currentPage() + 1 }}" aria-label="Next">
            <i class="ti tabler-chevron-right"></i>
          </a>
        </li>
      </ul>
    </nav>
  </div>
@endif
