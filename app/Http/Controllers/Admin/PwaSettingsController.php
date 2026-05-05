<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PwaSettingsController extends Controller
{
    public function index()
    {
        $manifestPath = public_path('manifest.json');
        $manifest = [];
        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
        }

        return view('admin.pwa.index', compact('manifest'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'theme_color' => 'required|string|max:20',
            'background_color' => 'required|string|max:20',
            'icon_192' => 'nullable|image|mimes:png|max:2048',
            'icon_192_url' => 'nullable|url|max:1000',
            'icon_512' => 'nullable|image|mimes:png|max:4096',
            'icon_512_url' => 'nullable|url|max:1000',
        ]);

        $manifestPath = public_path('manifest.json');
        $manifest = [];
        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
        }

        $manifest['name'] = $request->name;
        $manifest['short_name'] = $request->short_name;
        $manifest['description'] = $request->description;
        $manifest['theme_color'] = $request->theme_color;
        $manifest['background_color'] = $request->background_color;

        // Ensure icons array exists
        if (!isset($manifest['icons']) || empty($manifest['icons'])) {
            $manifest['icons'] = [
                ["src" => "/assets/img/icons/icon-192x192.png", "sizes" => "192x192", "type" => "image/png"],
                ["src" => "/assets/img/icons/icon-512x512.png", "sizes" => "512x512", "type" => "image/png"]
            ];
        }

        // Handle Icon 192x192
        if ($request->hasFile('icon_192')) {
            $file = $request->file('icon_192');
            $filename = 'icon-192x192.png';
            // PWA requires icon to be in a publicly accessible path
            $iconsDir = public_path('assets/img/icons');
            if (!File::isDirectory($iconsDir)) {
                File::makeDirectory($iconsDir, 0755, true, true);
            }
            $file->move($iconsDir, $filename);
            
            // update manifest array for 192
            $updated = false;
            foreach ($manifest['icons'] as &$icon) {
                if (isset($icon['sizes']) && $icon['sizes'] === '192x192') {
                    // Update cache buster to force browser to re-download the icon
                    $icon['src'] = '/assets/img/icons/' . $filename . '?v=' . time();
                    $updated = true;
                }
            }
            if (!$updated) {
                 $manifest['icons'][] = ["src" => '/assets/img/icons/' . $filename . '?v=' . time(), "sizes" => "192x192", "type" => "image/png"];
            }
        } elseif ($request->filled('icon_192_url')) {
            $url = $request->input('icon_192_url');
            $updated = false;
            foreach ($manifest['icons'] as &$icon) {
                if (isset($icon['sizes']) && $icon['sizes'] === '192x192') {
                    $icon['src'] = $url;
                    $updated = true;
                }
            }
            if (!$updated) {
                 $manifest['icons'][] = ["src" => $url, "sizes" => "192x192", "type" => "image/png"];
            }
        }

        // Handle Icon 512x512
        if ($request->hasFile('icon_512')) {
            $file = $request->file('icon_512');
            $filename = 'icon-512x512.png';
            $iconsDir = public_path('assets/img/icons');
            if (!File::isDirectory($iconsDir)) {
                File::makeDirectory($iconsDir, 0755, true, true);
            }
            $file->move($iconsDir, $filename);

            // update manifest array for 512
            $updated = false;
            foreach ($manifest['icons'] as &$icon) {
                if (isset($icon['sizes']) && $icon['sizes'] === '512x512') {
                    $icon['src'] = '/assets/img/icons/' . $filename . '?v=' . time();
                    $updated = true;
                }
            }
            if (!$updated) {
                $manifest['icons'][] = ["src" => '/assets/img/icons/' . $filename . '?v=' . time(), "sizes" => "512x512", "type" => "image/png"];
           }
        } elseif ($request->filled('icon_512_url')) {
            $url = $request->input('icon_512_url');
            $updated = false;
            foreach ($manifest['icons'] as &$icon) {
                if (isset($icon['sizes']) && $icon['sizes'] === '512x512') {
                    $icon['src'] = $url;
                    $updated = true;
                }
            }
            if (!$updated) {
                $manifest['icons'][] = ["src" => $url, "sizes" => "512x512", "type" => "image/png"];
           }
        }

        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return back()->with('success', 'Pengaturan PWA berhasil diperbarui. Hapus cache browser (Clear Storage di tab Application pada Chrome DevTools) untuk melihat perubahan secara instan pada device.');
    }
}
