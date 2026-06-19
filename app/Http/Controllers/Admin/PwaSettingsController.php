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
            if (!is_array($manifest)) {
                $manifest = [];
            }
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

        try {
            $manifestPath = public_path('manifest.json');
            $manifest = [];
            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true);
                if (!is_array($manifest)) {
                    $manifest = [];
                }
            }

            $manifest['name'] = $request->name;
            $manifest['short_name'] = $request->short_name;
            $manifest['description'] = $request->description;
            $manifest['theme_color'] = $request->theme_color;
            $manifest['background_color'] = $request->background_color;

            // Ensure baseline PWA manifest requirements are met
            if (!isset($manifest['start_url'])) {
                $manifest['start_url'] = '/dashboard';
            }
            if (!isset($manifest['display'])) {
                $manifest['display'] = 'standalone';
            }
            if (!isset($manifest['orientation'])) {
                $manifest['orientation'] = 'portrait-primary';
            }

            // Ensure icons array exists
            if (!isset($manifest['icons']) || !is_array($manifest['icons']) || empty($manifest['icons'])) {
                $manifest['icons'] = [
                    ["src" => "/assets/img/icons/icon-192x192.png", "sizes" => "192x192", "type" => "image/png"],
                    ["src" => "/assets/img/icons/icon-512x512.png", "sizes" => "512x512", "type" => "image/png"]
                ];
            }

            $iconsDir = public_path('assets/img/icons');

            // Handle Icon 192x192
            if ($request->hasFile('icon_192')) {
                $file = $request->file('icon_192');
                $filename = 'icon-192x192.png';
                
                // Ensure icons directory exists and is writable
                if (!File::isDirectory($iconsDir)) {
                    if (!File::makeDirectory($iconsDir, 0755, true, true)) {
                        throw new \Exception("Gagal membuat direktori untuk menyimpan icon di: {$iconsDir}");
                    }
                }

                if (!File::isWritable($iconsDir)) {
                    throw new \Exception("Direktori icon tidak dapat ditulis (permission denied): {$iconsDir}");
                }

                try {
                    $file->move($iconsDir, $filename);
                } catch (\Exception $e) {
                    throw new \Exception("Gagal memindahkan file upload icon-192: " . $e->getMessage());
                }
                
                // update manifest array for 192 without using reference loops
                $updated = false;
                foreach ($manifest['icons'] as $key => $icon) {
                    if (isset($icon['sizes']) && $icon['sizes'] === '192x192') {
                        // Update cache buster to force browser to re-download the icon
                        $manifest['icons'][$key]['src'] = '/assets/img/icons/' . $filename . '?v=' . time();
                        $updated = true;
                    }
                }
                if (!$updated) {
                     $manifest['icons'][] = ["src" => '/assets/img/icons/' . $filename . '?v=' . time(), "sizes" => "192x192", "type" => "image/png"];
                }
            } elseif ($request->filled('icon_192_url')) {
                $url = $request->input('icon_192_url');
                $updated = false;
                foreach ($manifest['icons'] as $key => $icon) {
                    if (isset($icon['sizes']) && $icon['sizes'] === '192x192') {
                        $manifest['icons'][$key]['src'] = $url;
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
                
                // Ensure icons directory exists and is writable
                if (!File::isDirectory($iconsDir)) {
                    if (!File::makeDirectory($iconsDir, 0755, true, true)) {
                        throw new \Exception("Gagal membuat direktori untuk menyimpan icon di: {$iconsDir}");
                    }
                }

                if (!File::isWritable($iconsDir)) {
                    throw new \Exception("Direktori icon tidak dapat ditulis (permission denied): {$iconsDir}");
                }

                try {
                    $file->move($iconsDir, $filename);
                } catch (\Exception $e) {
                    throw new \Exception("Gagal memindahkan file upload icon-512: " . $e->getMessage());
                }

                // update manifest array for 512 without using reference loops
                $updated = false;
                foreach ($manifest['icons'] as $key => $icon) {
                    if (isset($icon['sizes']) && $icon['sizes'] === '512x512') {
                        $manifest['icons'][$key]['src'] = '/assets/img/icons/' . $filename . '?v=' . time();
                        $updated = true;
                    }
                }
                if (!$updated) {
                    $manifest['icons'][] = ["src" => '/assets/img/icons/' . $filename . '?v=' . time(), "sizes" => "512x512", "type" => "image/png"];
               }
            } elseif ($request->filled('icon_512_url')) {
                $url = $request->input('icon_512_url');
                $updated = false;
                foreach ($manifest['icons'] as $key => $icon) {
                    if (isset($icon['sizes']) && $icon['sizes'] === '512x512') {
                        $manifest['icons'][$key]['src'] = $url;
                        $updated = true;
                    }
                }
                if (!$updated) {
                    $manifest['icons'][] = ["src" => $url, "sizes" => "512x512", "type" => "image/png"];
               }
            }

            // Check if manifest.json path is writable
            if (File::exists($manifestPath) && !File::isWritable($manifestPath)) {
                throw new \Exception("File manifest.json tidak dapat ditulis (permission denied): {$manifestPath}");
            } elseif (!File::exists($manifestPath) && !File::isWritable(dirname($manifestPath))) {
                throw new \Exception("Folder public tidak dapat ditulis sehingga tidak bisa membuat file manifest.json");
            }

            // Write to manifest.json
            File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return back()->with('success', 'Pengaturan PWA berhasil diperbarui. Hapus cache browser (Clear Storage di tab Application pada Chrome DevTools) untuk melihat perubahan secara instan pada device.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PWA Settings Update Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan pengaturan PWA: ' . $e->getMessage());
        }
    }
}
