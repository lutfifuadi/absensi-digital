@php
  use Illuminate\Support\Facades\Route;
  $configData = Helper::appClasses();
  $namaSekolah = \App\Models\Pengaturan::where('key', 'nama_lembaga')->value('value')
    ?? \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value')
    ?? config('variables.templateName');
  
  $logoSrc = null;
  $logoUrl = \App\Models\Pengaturan::where('key', 'logo_url')->value('value');
  if ($logoUrl) {
    $logoSrc = $logoUrl;
  } else {
    $logoLocal = \App\Models\Pengaturan::where('key', 'logo_sekolah')->value('value');
    if ($logoLocal) {
      $logoSrc = asset('uploads/logo/' . $logoLocal);
    }
  }
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu"
  @foreach ($configData['menuAttributes'] as $attribute => $value)
  {{ $attribute }}="{{ $value }}" @endforeach>

  <!-- ! Hide app brand if navbar-full -->
  @if (!isset($navbarFull))
    <div class="app-brand demo">
      <a href="{{ url('/') }}" class="app-brand-link">
        @if($logoSrc)
          <img src="{{ $logoSrc }}" alt="Logo" style="height:40px;max-width:100%;object-fit:contain;">
        @else
          <span class="app-brand-logo demo">@include('_partials.macros')</span>
        @endif
        <span class="app-brand-text demo menu-text fw-bold ms-3">E-Absensi</span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
        <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
        <i class="icon-base ti tabler-x d-block d-xl-none"></i>
      </a>
    </div>
  @endif

  <!-- Menu Search -->
  <div class="menu-search-wrapper px-4 py-2 mb-2 d-none d-lg-block">
    <div class="input-group input-group-merge">
      <span class="input-group-text border-0 bg-transparent ps-0" style="color: var(--das-text-dim, #8b96ab)"><i class="ti tabler-search fs-5"></i></span>
      <input type="text" id="menu-search-input" class="form-control border-0 bg-transparent ps-2" placeholder="Cari menu..." style="box-shadow: none; font-size: 0.85rem; color: inherit;" autocomplete="off">
    </div>
    <div style="height: 1px; background: rgba(255,255,255,0.06); margin-top: 5px;"></div>
  </div>

  <div class="menu-inner-shadow"></div>

  @php
    $currentRole = auth()->check() ? auth()->user()->role : 'guest';
  @endphp

  <ul class="menu-inner py-1">
    @foreach ($menuData[0]->menu as $menu)
      @if (isset($menu->roles) && !in_array($currentRole, $menu->roles, true))
        @continue
      @endif

      {{-- adding active and open class if child is active --}}

      {{-- menu headers --}}
      @if (isset($menu->menuHeader))
        <li class="menu-header small">
          @if (isset($menu->icon))
            <i class="{{ $menu->icon }} me-1" style="font-size: 0.85rem;"></i>
          @endif
          <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
        </li>
      @else
        {{-- active menu method --}}
        @php
          $activeClass = null;
          $currentRouteName = Route::currentRouteName();

          if ($currentRouteName === $menu->slug) {
              $activeClass = 'active';
          } elseif (isset($menu->submenu)) {
              if (gettype($menu->slug) === 'array') {
                  foreach ($menu->slug as $slug) {
                      if (str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0) {
                          $activeClass = 'active open';
                      }
                  }
              } else {
                  if (str_contains($currentRouteName, $menu->slug) and strpos($currentRouteName, $menu->slug) === 0) {
                      $activeClass = 'active open';
                  }
              }
          }
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{ $activeClass }}">
          <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
            class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
            @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif
            data-bs-toggle="tooltip" data-bs-placement="right" title="{{ isset($menu->name) ? __($menu->name) : '' }}">
            @isset($menu->icon)
              <i class="{{ $menu->icon }}"></i>
            @endisset
            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
            @if(isset($menu->target) && $menu->target === '_blank')
              <i class="ti tabler-external-link ms-1 text-muted" style="font-size: 0.75rem;"></i>
            @endif
            @isset($menu->badge)
              <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
            @endisset
          </a>

          {{-- submenu --}}
          @isset($menu->submenu)
            @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
          @endisset
        </li>
      @endif
    @endforeach
  </ul>

</aside>

<style>
  /* Sembunyikan input search saat menu collapsed */
  .layout-menu-collapsed .menu-search-wrapper,
  .layout-menu:not(.layout-menu-expanded) .menu-search-wrapper {
    display: none !important;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('menu-search-input');
  if (!searchInput) return;

  const menuInner = document.querySelector('.menu-inner');
  if (!menuInner) return;

  const menuItems = menuInner.querySelectorAll('.menu-item');
  const menuHeaders = menuInner.querySelectorAll('.menu-header');

  // Simpan state awal class open
  const originalStates = Array.from(menuItems).map(item => {
    return {
      element: item,
      isOpen: item.classList.contains('open')
    };
  });

  searchInput.addEventListener('input', function () {
    const query = searchInput.value.toLowerCase().trim();

    if (query === '') {
      // Kembalikan ke state semula
      menuItems.forEach((item, index) => {
        item.style.display = '';
        const state = originalStates[index];
        if (state && state.isOpen) {
          item.classList.add('open');
        } else {
          item.classList.remove('open');
        }
      });
      menuHeaders.forEach(header => {
        header.style.display = '';
      });
      return;
    }

    // 1. Sembunyikan semua menu-item terlebih dahulu
    menuItems.forEach(item => {
      item.style.display = 'none';
      item.classList.remove('open');
    });

    // 2. Cari menu-item yang cocok
    const matchedItems = [];
    menuItems.forEach(item => {
      const menuLink = item.querySelector(':scope > .menu-link');
      if (menuLink) {
        const menuTextDiv = menuLink.querySelector(':scope > div');
        if (menuTextDiv) {
          const text = menuTextDiv.textContent.toLowerCase();
          if (text.includes(query)) {
            matchedItems.push(item);
          }
        }
      }
    });

    // 3. Tampilkan matched items beserta child dan parent-nya
    matchedItems.forEach(item => {
      item.style.display = '';

      // Tampilkan semua menu-item di bawahnya (sub-menu)
      const childItems = item.querySelectorAll('.menu-item');
      childItems.forEach(child => {
        child.style.display = '';
      });

      // Naik ke atas untuk menampilkan & membuka parent
      let current = item;
      while (current) {
        const parentItem = current.parentElement.closest('.menu-item');
        if (parentItem) {
          parentItem.style.display = '';
          parentItem.classList.add('open');
          current = parentItem;
        } else {
          break;
        }
      }
    });

    // 4. Sembunyikan menu-header jika tidak ada menu-item di bawahnya yang terlihat
    let currentHeader = null;
    let hasVisibleChildren = false;

    Array.from(menuInner.children).forEach(child => {
      if (child.classList.contains('menu-header')) {
        if (currentHeader) {
          currentHeader.style.display = hasVisibleChildren ? '' : 'none';
        }
        currentHeader = child;
        hasVisibleChildren = false;
      } else if (child.classList.contains('menu-item')) {
        if (child.style.display !== 'none') {
          hasVisibleChildren = true;
        }
      }
    });
    if (currentHeader) {
      currentHeader.style.display = hasVisibleChildren ? '' : 'none';
    }
  });
});
</script>
