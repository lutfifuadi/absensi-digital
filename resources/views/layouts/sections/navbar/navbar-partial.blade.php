@php
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Route;
  $currentSchool = app()->has('current_school') ? app('current_school') : null;
  $namaSekolah = $currentSchool ? $currentSchool->name : (\App\Models\Pengaturan::where('key', 'nama_lembaga')->value('value')
    ?? \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value')
    ?? config('variables.templateName'));
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
  <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4 ms-0">
    <a href="{{ url('/') }}" class="app-brand-link">
      <span class="app-brand-logo demo">@include('_partials.macros')</span>
      <span class="app-brand-text demo menu-text fw-bold">{{ $namaSekolah }}</span>
    </a>

    <!-- Display menu close icon only for horizontal-menu with navbar-full -->
    @if (isset($menuHorizontal))
      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
        <i class="icon-base ti tabler-x icon-sm d-flex align-items-center justify-content-center"></i>
      </a>
    @endif
  </div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
  <div
    class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
      <i class="icon-base ti tabler-menu-2 icon-md"></i>
    </a>
  </div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
  @if ($configData['hasCustomizer'] == true)
    <!-- Style Switcher -->
    <div class="navbar-nav align-items-center">
      <li class="nav-item dropdown me-2 me-xl-0">
        <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);"
          data-bs-toggle="dropdown">
          <i class="icon-base ti tabler-sun icon-md theme-icon-active"></i>
          <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="nav-theme-text">
          <li>
            <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light"
              aria-pressed="false">
              <span><i class="icon-base ti tabler-sun icon-22px me-3" data-icon="sun"></i>Light</span>
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark"
              aria-pressed="true">
              <span><i class="icon-base ti tabler-moon-stars icon-22px me-3" data-icon="moon-stars"></i>Dark</span>
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"
              aria-pressed="false">
              <span><i class="icon-base ti tabler-device-desktop-analytics icon-22px me-3"
                  data-icon="device-desktop-analytics"></i>System</span>
            </button>
          </li>
        </ul>
      </li>
    </div>
    <!-- / Style Switcher-->
  @endif
  <ul class="navbar-nav flex-row align-items-center ms-auto">
    <!-- Academic Year Switcher -->
    @auth
      @php
        $allTahun = \App\Models\TahunAkademik::orderByDesc('tanggal_mulai')->get();
        $currentTahunId = session('tahun_ajaran_id');
        $currentTahunLabel = $allTahun->firstWhere('id', $currentTahunId);
        $currentTahunNama = $currentTahunLabel ? $currentTahunLabel->nama . ' (' . ucfirst($currentTahunLabel->semester) . ')' : 'Pilih Tahun Ajaran';
      @endphp
      <li class="nav-item dropdown me-2 me-xl-1">
        <a class="nav-link dropdown-toggle hide-arrow p-2 rounded" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(var(--bs-primary-rgb), 0.1);">
          <i class="icon-base ti tabler-calendar-stats icon-md text-primary"></i>
          <span class="d-none d-md-inline-block ms-1 fw-bold text-primary">{{ $currentTahunNama }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-2">
          <li>
            <h6 class="dropdown-header text-uppercase fw-bold"><i class="ti tabler-calendar me-2"></i>Tahun Ajaran</h6>
          </li>
          @foreach($allTahun as $thn)
            <li>
              <form action="{{ route('admin.set-tahun-akademik') }}" method="POST">
                @csrf
                <input type="hidden" name="tahun_akademik_id" value="{{ $thn->id }}">
                <button type="submit" class="dropdown-item d-flex align-items-center {{ $currentTahunId == $thn->id ? 'active fw-bold' : '' }}">
                  <span class="flex-grow-1">{{ $thn->nama }} - {{ ucfirst($thn->semester) }}</span>
                  @if($thn->is_aktif)
                    <span class="badge bg-label-success ms-2" style="font-size:0.65rem">Aktif</span>
                  @endif
                </button>
              </form>
            </li>
          @endforeach
        </ul>
      </li>
    @endauth
    <!-- / Academic Year Switcher -->

    <!-- Notification Bell -->
    @auth
      @php
        $unreadNotifs = Auth::user()->unreadNotifications->take(10);
        $unreadCount = Auth::user()->unreadNotifications->count();
      @endphp
      <li class="nav-item dropdown me-3 me-xl-1">
        <a class="nav-link dropdown-toggle hide-arrow p-0 position-relative" href="javascript:void(0);"
          data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
          <i class="icon-base ti tabler-bell icon-md"></i>
          @if ($unreadCount > 0)
            <span
              class="badge bg-danger badge-notifications position-absolute top-0 start-100 translate-middle rounded-pill">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
          @endif
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0" style="min-width:320px;max-width:360px;">
          <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h5 class="text-body mb-0 me-auto fw-semibold">Notifikasi</h5>
              @if ($unreadCount > 0)
                <a href="javascript:void(0);" class="dropdown-notifications-all text-body" data-bs-toggle="tooltip"
                  data-bs-placement="top" title="Tandai semua sudah dibaca" id="btn-mark-all-read">
                  <i class="icon-base ti tabler-mail-opened fs-4"></i>
                </a>
              @endif
            </div>
          </li>
          <li class="dropdown-notifications-list scrollable-container" style="max-height:300px;overflow-y:auto;">
            @forelse($unreadNotifs as $notif)
              @php
                $data = $notif->data;
                $jenis = $data['jenis'] ?? '';
                $icon = 'tabler-bell';
                $bg = 'info';

                if ($jenis === 'sakit') {
                    $bg = 'warning';
                    $icon = 'tabler-first-aid-kit';
                } elseif ($jenis === 'izin') {
                    $bg = 'info';
                    $icon = 'tabler-calendar-off';
                } elseif (isset($data['icon'])) {
                    $bg = $data['color'] ?? 'info';
                    $icon = 'tabler-' . $data['icon'];
                }
              @endphp
              <ul class="list-group list-group-flush">
                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                  <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar">
                        <span class="avatar-initial rounded-circle bg-label-{{ $bg }}">
                          <i class="icon-base ti {{ $icon }} icon-sm"></i>
                        </span>
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-1 small fw-semibold">{{ $data['title'] ?? 'Notifikasi' }}</h6>
                      <p class="mb-0 small text-body-secondary">{{ \Illuminate\Support\Str::limit($data['message'] ?? '', 60) }}</p>
                      <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="flex-shrink-0 ms-2">
                      <button class="btn-close btn-mark-read" style="font-size:.65rem;"
                        data-notif-id="{{ $notif->id }}" title="Tandai dibaca"></button>
                    </div>
                  </div>
                </li>
              </ul>
            @empty
              <div class="text-center py-4 text-muted">
                <i class="ti tabler-bell-off fs-3 d-block mb-1"></i>
                <small>Tidak ada notifikasi baru</small>
              </div>
            @endforelse
          </li>
          @if ($unreadCount > 0)
            <li class="border-top">
              <a href="{{ route('admin.izin-sakit.index') }}"
                class="dropdown-item d-flex justify-content-center p-3 fw-semibold">
                Lihat Semua Izin/Sakit
              </a>
            </li>
          @endif
        </ul>
      </li>
    @endauth
    <!-- / Notification Bell -->

    <!-- User -->
    <li class="nav-item navbar-dropdown dropdown-user dropdown">
      <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
          <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}" alt
            class="rounded-circle" />
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item mt-0"
            href="{{ Auth::check() && Auth::user()->role === 'orang_tua' ? route('ortu.pengaturan') : (Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);') }}">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-2">
                <div class="avatar avatar-online">
                  <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}"
                    alt class="rounded-circle" />
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">
                  @if (Auth::check())
                    {{ Auth::user()->name }}
                  @else
                    John Doe
                  @endif
                </h6>
                <small class="text-body-secondary">
                  @if (Auth::check())
                    {{ ucwords(str_replace('_', ' ', Auth::user()->role)) }}
                  @else
                    Admin
                  @endif
                </small>
              </div>
            </div>
          </a>
        </li>
        <li>
          <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        <li>
          <a class="dropdown-item"
            href="{{ Auth::check() && Auth::user()->role === 'orang_tua' ? route('ortu.pengaturan') : (Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);') }}">
            <i class="icon-base ti tabler-user me-3 icon-md"></i><span class="align-middle">My Profile</span> </a>
        </li>
        @if (Auth::check() && Laravel\Jetstream\Jetstream::hasApiFeatures())
          <li>
            <a class="dropdown-item" href="{{ route('api-tokens.index') }}">
              <i class="icon-base ti tabler-settings me-3 icon-md"></i><span class="align-middle">API Tokens</span>
            </a>
          </li>
        @endif
        @if (Auth::User() && Laravel\Jetstream\Jetstream::hasTeamFeatures())
          <li>
            <div class="dropdown-divider my-1 mx-n2"></div>
          </li>
          <li>
            <h6 class="dropdown-header">Manage Team</h6>
          </li>
          <li>
            <div class="dropdown-divider my-1"></div>
          </li>
          <li>
            <a class="dropdown-item"
              href="{{ Auth::user() ? route('teams.show', Auth::user()->currentTeam->id) : 'javascript:void(0)' }}">
              <i class="icon-base bx bx-cog icon-md me-3"></i><span>Team Settings</span>
            </a>
          </li>
          @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
            <li>
              <a class="dropdown-item" href="{{ route('teams.create') }}">
                <i class="icon-base bx bx-user icon-md me-3"></i><span>Create New Team</span>
              </a>
            </li>
          @endcan
          @if (Auth::user()->allTeams()->count() > 1)
            <li>
              <div class="dropdown-divider my-1"></div>
            </li>
            <li>
              <h6 class="dropdown-header">Switch Teams</h6>
            </li>
            <li>
              <div class="dropdown-divider my-1"></div>
            </li>
          @endif
          @if (Auth::user())
            @foreach (Auth::user()->allTeams() as $team)
              {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}

              <x-switchable-team :team="$team" />
            @endforeach
          @endif
        @endif
        <li>
          <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        @if (Auth::check())
          <li>
            <a class="dropdown-item" href="{{ route('logout') }}"
              onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Logout</span>
            </a>
          </li>
          <form method="POST" id="logout-form" action="{{ route('logout') }}">
            @csrf
          </form>
        @else
          <li>
            <div class="d-grid px-2 pt-2 pb-1">
              <a class="btn btn-sm btn-danger d-flex"
                href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}" target="_blank">
                <small class="align-middle">Login</small>
                <i class="icon-base ti tabler-login ms-2 icon-14px"></i>
              </a>
            </div>
          </li>
        @endif
      </ul>
    </li>
    <!--/ User -->
  </ul>
</div>
