@if ($paginator->hasPages())
<nav aria-label="Navigasi Halaman" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
  <div class="text-muted small font-monospace">
    Menampilkan <span class="text-white fw-semibold">{{ $paginator->firstItem() }}</span>–<span class="text-white fw-semibold">{{ $paginator->lastItem() }}</span> dari <span class="text-white fw-semibold">{{ $paginator->total() }}</span> data
  </div>
  <ul class="pagination pagination-sm mb-0 gap-1" style="list-style:none; display:flex; align-items:center; flex-wrap:wrap;">

    {{-- Previous --}}
    @if ($paginator->onFirstPage())
      <li class="page-item disabled">
        <span class="das-page-btn" aria-disabled="true">
          <i class="ti tabler-chevron-left" style="font-size:0.85rem;"></i>
        </span>
      </li>
    @else
      <li class="page-item">
        <a class="das-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya" data-page="{{ $paginator->currentPage() - 1 }}">
          <i class="ti tabler-chevron-left" style="font-size:0.85rem;"></i>
        </a>
      </li>
    @endif

    {{-- Page Numbers --}}
    @foreach ($elements as $element)
      @if (is_string($element))
        <li class="page-item disabled">
          <span class="das-page-btn das-page-dots">{{ $element }}</span>
        </li>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <li class="page-item active">
              <span class="das-page-btn das-page-active" aria-current="page">{{ $page }}</span>
            </li>
          @else
            <li class="page-item">
              <a class="das-page-btn" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a>
            </li>
          @endif
        @endforeach
      @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
      <li class="page-item">
        <a class="das-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Selanjutnya" data-page="{{ $paginator->currentPage() + 1 }}">
          <i class="ti tabler-chevron-right" style="font-size:0.85rem;"></i>
        </a>
      </li>
    @else
      <li class="page-item disabled">
        <span class="das-page-btn" aria-disabled="true">
          <i class="ti tabler-chevron-right" style="font-size:0.85rem;"></i>
        </span>
      </li>
    @endif

  </ul>
</nav>
@endif
