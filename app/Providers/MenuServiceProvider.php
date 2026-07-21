<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
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
      $role = 'guest';
      if ($user) {
        $role = session('active_role', $user->role);
      }

      $menuFile = match ($role) {
        'super_admin', 'admin_sekolah' => 'vertical_admin.json',
        'operator' => 'vertical_operator.json',
        'staff_tu' => 'vertical_staff_tu.json',
        'siswa' => 'vertical_siswa.json',
        'guru' => 'vertical_guru.json',
        'wali_kelas' => 'vertical_wali_kelas.json',
        'piket' => 'vertical_piket.json',
        'orang_tua' => 'vertical_orang_tua.json',
        default => 'vertical_admin.json',
      };

      $verticalMenuData = $this->loadMenuFile($menuFile, $role);

      // Filter Multi-Tenant menus if in standalone mode
      if ($verticalMenuData && config('app.multipurpose_mode') === 'standalone') {
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

      $horizontalMenuData = $this->loadMenuFile('horizontalMenu.json', 'horizontal');
      if (!$horizontalMenuData) {
        $horizontalMenuData = (object) ['menu' => []];
      }

      $view->with('menuData', [$verticalMenuData ?? (object) ['menu' => []], $horizontalMenuData]);
    });
  }

  /**
   * Load menu file dengan validasi dan fallback.
   */
  private function loadMenuFile(string $filename, string $roleContext): ?object
  {
    $path = base_path('resources/menu/' . $filename);

    if (!file_exists($path)) {
      Log::warning("Menu file not found for context: {$roleContext} - {$filename}");

      // Fallback untuk vertical menu
      if ($filename !== 'horizontalMenu.json') {
        $fallbackPath = base_path('resources/menu/vertical_admin.json');
        if (file_exists($fallbackPath)) {
          $content = file_get_contents($fallbackPath);
          $data = json_decode($content);
          if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
          }
        }
      }
      return null;
    }

    $content = file_get_contents($path);
    $data = json_decode($content);

    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::error("JSON parse error in menu file: {$filename} - " . json_last_error_msg());

      // Fallback
      if ($filename !== 'horizontalMenu.json') {
        $fallbackPath = base_path('resources/menu/vertical_admin.json');
        if (file_exists($fallbackPath)) {
          $content = file_get_contents($fallbackPath);
          $data = json_decode($content);
          if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
          }
        }
      }
      return null;
    }

    return $data;
  }
}
