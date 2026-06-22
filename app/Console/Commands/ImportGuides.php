<?php

namespace App\Console\Commands;

use App\Models\Guide;
use App\Models\GuideCategory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ImportGuides extends Command
{
    protected $signature = 'guides:import {--path= : Path ke folder markdown files}';
    protected $description = 'Import konten panduan dari file markdown ke database';

    // Mapping file pattern ke kategori slug
    protected array $categoryMapping = [
        'scan-qr-publik' => 'panduan-publik',
        'absensi-mandiri-siswa' => 'panduan-siswa',
        'izin-sakit-siswa' => 'panduan-siswa',
        'absensi-izin-guru' => 'panduan-guru',
        'pantau-anak-ortu' => 'panduan-orang-tua',
        'dashboard-wali-kelas' => 'panduan-wali-kelas',
        'absensi-siswa-per-kelas' => 'panduan-wali-kelas',
        'rekap-harian' => 'panduan-wali-kelas',
        'dashboard-absensi-staff' => 'panduan-staff-tu',
        'izin-sakit-staff' => 'panduan-staff-tu',
        'manajemen-kelas' => 'panduan-operator',
        'manajemen-siswa' => 'panduan-operator',
        'manajemen-guru' => 'panduan-operator',
        'manajemen-staff-tu' => 'panduan-operator',
        'manajemen-wali-kelas' => 'panduan-operator',
        'manajemen-tahun-akademik' => 'panduan-operator',
        'manajemen-jadwal-pelajaran' => 'panduan-operator',
        'manajemen-kegiatan-khusus' => 'panduan-operator',
        'manajemen-ekskul' => 'panduan-operator',
        'absensi-manual' => 'panduan-operator',
        'laporan-export' => 'panduan-operator',
        'pelepasan-kelas-xii' => 'panduan-operator',
        'pengaturan-aplikasi' => 'panduan-admin-sekolah',
        'wa-gateway' => 'panduan-admin-sekolah',
        'hari-libur' => 'panduan-admin-sekolah',
        'gamifikasi' => 'panduan-admin-sekolah',
        'cetak-qr-massal' => 'panduan-admin-sekolah',
        'naik-kelas-massal' => 'panduan-admin-sekolah',
        'live-monitor' => 'panduan-admin-sekolah',
        'analytics-dashboard' => 'panduan-admin-sekolah',
        'user-management' => 'panduan-super-admin',
        'manajemen-lisensi' => 'panduan-super-admin',
        'update-sistem' => 'panduan-super-admin',
        'faq-umum' => 'faq-dan-troubleshooting',
        'troubleshooting' => 'faq-dan-troubleshooting',
        'mode-offline' => 'fitur-teknis-dan-integrasi',
        'notifikasi-wa' => 'fitur-teknis-dan-integrasi',
    ];

    // Mapping file ke role_target
    protected array $roleMapping = [
        'scan-qr-publik' => 'public',
        'absensi-mandiri-siswa' => 'siswa',
        'izin-sakit-siswa' => 'siswa',
        'absensi-izin-guru' => 'guru',
        'pantau-anak-ortu' => 'orang_tua',
        'dashboard-wali-kelas' => 'wali_kelas',
        'absensi-siswa-per-kelas' => 'wali_kelas',
        'rekap-harian' => 'wali_kelas,admin_sekolah,operator',
        'dashboard-absensi-staff' => 'staff_tu',
        'izin-sakit-staff' => 'staff_tu',
        'manajemen-kelas' => 'operator,admin_sekolah',
        'manajemen-siswa' => 'operator,admin_sekolah',
        'manajemen-guru' => 'operator,admin_sekolah',
        'manajemen-staff-tu' => 'operator,admin_sekolah',
        'manajemen-wali-kelas' => 'operator,admin_sekolah',
        'manajemen-tahun-akademik' => 'operator,admin_sekolah',
        'manajemen-jadwal-pelajaran' => 'operator,admin_sekolah',
        'manajemen-kegiatan-khusus' => 'operator,admin_sekolah',
        'manajemen-ekskul' => 'operator,admin_sekolah',
        'absensi-manual' => 'operator,admin_sekolah',
        'laporan-export' => 'operator,admin_sekolah',
        'pelepasan-kelas-xii' => 'operator,admin_sekolah',
        'pengaturan-aplikasi' => 'admin_sekolah',
        'wa-gateway' => 'admin_sekolah',
        'hari-libur' => 'admin_sekolah',
        'gamifikasi' => 'admin_sekolah',
        'cetak-qr-massal' => 'admin_sekolah',
        'naik-kelas-massal' => 'admin_sekolah',
        'live-monitor' => 'admin_sekolah,super_admin',
        'analytics-dashboard' => 'admin_sekolah',
        'user-management' => 'super_admin',
        'manajemen-lisensi' => 'super_admin',
        'update-sistem' => 'super_admin',
        'faq-umum' => 'public',
        'troubleshooting' => 'public',
        'mode-offline' => 'public',
        'notifikasi-wa' => 'public',
    ];

    // Mapping file ke order
    protected array $orderMapping = [
        'scan-qr-publik' => 1,
        'absensi-mandiri-siswa' => 1,
        'izin-sakit-siswa' => 2,
        'absensi-izin-guru' => 1,
        'pantau-anak-ortu' => 1,
        'dashboard-wali-kelas' => 1,
        'absensi-siswa-per-kelas' => 2,
        'rekap-harian' => 3,
        'dashboard-absensi-staff' => 1,
        'izin-sakit-staff' => 2,
        'manajemen-kelas' => 1,
        'manajemen-siswa' => 2,
        'manajemen-guru' => 3,
        'manajemen-staff-tu' => 4,
        'manajemen-wali-kelas' => 5,
        'manajemen-tahun-akademik' => 6,
        'manajemen-jadwal-pelajaran' => 7,
        'manajemen-kegiatan-khusus' => 8,
        'manajemen-ekskul' => 9,
        'absensi-manual' => 10,
        'laporan-export' => 11,
        'pelepasan-kelas-xii' => 12,
        'pengaturan-aplikasi' => 1,
        'wa-gateway' => 2,
        'hari-libur' => 3,
        'gamifikasi' => 4,
        'cetak-qr-massal' => 5,
        'naik-kelas-massal' => 6,
        'live-monitor' => 7,
        'analytics-dashboard' => 8,
        'user-management' => 1,
        'manajemen-lisensi' => 2,
        'update-sistem' => 3,
        'faq-umum' => 1,
        'troubleshooting' => 2,
        'mode-offline' => 1,
        'notifikasi-wa' => 2,
    ];

    public function handle()
    {
        $path = $this->option('path') ?? storage_path('..' . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'guides' . DIRECTORY_SEPARATOR . 'tahap-2');

        if (!File::isDirectory($path)) {
            $this->error("Folder tidak ditemukan: {$path}");
            return 1;
        }

        // Cari author (ambil user pertama dengan role super_admin/admin_sekolah)
        $author = User::whereIn('role', ['super_admin', 'admin_sekolah'])->first();
        if (!$author) {
            $author = User::first();
        }

        if (!$author) {
            $this->error('Tidak ada user di database. Buat user dulu.');
            return 1;
        }

        $this->info("Author: {$author->name} (ID: {$author->id})");
        $this->info("Path: {$path}");
        $this->newLine();

        $files = File::files($path);
        $total = 0;
        $imported = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $total++;
            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME); // e.g. "07-manajemen-siswa"
            
            // Extract key: hapus nomor urut di depan
            $key = preg_replace('/^\d+-/', '', $filename); // e.g. "manajemen-siswa"

            // Baca konten markdown
            $content = File::get($file->getRealPath());

            // Parse judul dari baris pertama (# Judul)
            $lines = explode("\n", $content);
            $title = '';
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '# ') && !str_starts_with(trim($line), '##')) {
                    $title = trim(substr(trim($line), 2));
                    break;
                }
            }

            if (empty($title)) {
                $title = Str::title(str_replace('-', ' ', $key));
            }

            // Parse excerpt/deskripsi singkat (ambil setelah ## Deskripsi Singkat)
            $excerpt = '';
            $inDesc = false;
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '## Deskripsi Singkat')) {
                    $inDesc = true;
                    continue;
                }
                if ($inDesc) {
                    $trimmed = trim($line);
                    if (empty($trimmed) || str_starts_with($trimmed, '#')) {
                        break;
                    }
                    $excerpt .= ' ' . $trimmed;
                }
            }
            $excerpt = trim($excerpt);

            // Tentukan kategori
            $categorySlug = $this->categoryMapping[$key] ?? 'faq-dan-troubleshooting';
            $category = GuideCategory::where('slug', $categorySlug)->first();

            if (!$category) {
                $this->warn("Kategori '{$categorySlug}' tidak ditemukan untuk {$filename}. Gunakan kategori pertama.");
                $category = GuideCategory::first();
            }

            // Cek apakah sudah ada guide dengan slug yang sama
            $slug = Str::slug($title);
            $existing = Guide::where('slug', $slug)->first();
            if ($existing) {
                $this->line("⏭️  Skipped (already exists): {$title}");
                $skipped++;
                continue;
            }

            // Buat guide
            $guide = Guide::create([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'excerpt' => $excerpt,
                'category_id' => $category->id,
                'role_target' => $this->roleMapping[$key] ?? 'public',
                'author_id' => $author->id,
                'status' => 'published',
                'order' => $this->orderMapping[$key] ?? 0,
                'is_featured' => in_array($key, ['faq-umum', 'troubleshooting', 'mode-offline']),
                'published_at' => now(),
            ]);

            $this->info("✅ Imported: {$title} (kategori: {$category->name})");
            $imported++;
        }

        $this->newLine();
        $this->table(
            ['Total File', 'Berhasil Diimport', 'Skipped (Sudah Ada)'],
            [[$total, $imported, $skipped]]
        );

        $this->info("Import selesai! Kunjungi /panduan untuk melihat hasilnya.");
    }
}
