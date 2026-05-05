<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Routing\Route;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    View::composer('layouts.sections.menu.verticalMenu', function ($view) {
      $user = auth()->user();
      $role = $user ? $user->role : 'guest';

      $menuFile = match ($role) {
        'super_admin', 'admin_sekolah' => 'vertical_admin.json',
        'operator' => 'vertical_operator.json',
        'staff_tu' => 'vertical_staff_tu.json',
        'siswa' => 'vertical_siswa.json',
        'guru' => 'vertical_guru.json',
        'wali_kelas' => 'vertical_wali_kelas.json',
        'orang_tua' => 'vertical_orang_tua.json',
        default => 'vertical_admin.json',
      };

      $path = base_path('resources/menu/' . $menuFile);
      
      // Fallback if file doesn't exist
      if (!file_exists($path)) {
        $path = base_path('resources/menu/verticalMenu.json');
      }

      $verticalMenuJson = file_get_contents($path);
      $verticalMenuData = json_decode($verticalMenuJson);

      // Filter Multi-Tenant menus if in standalone mode
      if (config('app.multipurpose_mode') === 'standalone') {
        $verticalMenuData->menu = array_values(array_filter($verticalMenuData->menu, function ($item) {
          if (isset($item->menuHeader) && $item->menuHeader === 'Multi-Tenant (SaaS)') {
            return false;
          }
          if (isset($item->url) && $item->url === '/admin/schools') {
            return false;
          }
          return true;
        }));
      }

      $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
      $horizontalMenuData = json_decode($horizontalMenuJson);

      $view->with('menuData', [$verticalMenuData, $horizontalMenuData]);
    });
  }
}
