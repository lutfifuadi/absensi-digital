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
      $logoSrc = asset('storage/' . $logoLocal);
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
            @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
            @isset($menu->icon)
              <i class="{{ $menu->icon }}"></i>
            @endisset
            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
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
