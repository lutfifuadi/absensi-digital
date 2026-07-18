<?php

namespace Database\Seeders;

use App\Models\Guide;
use App\Models\GuideCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GuideSeeder extends Seeder
{
    /**
     * Mapping file guide ke metadata (category, role_target, order).
     *
     * Format: 'nama-file.md' => [category_slug, role_target, order, is_featured]
     */
    private array $guideMapping = [
        // ── Panduan Publik ─────────────────────────────────────────────────
        '29-faq-umum.md'          => ['faq-troubleshooting', 'public', 1, true],
        '30-troubleshooting.md'   => ['faq-troubleshooting', 'public', 2, false],

        // ── Panduan Siswa ──────────────────────────────────────────────────
        '00-absensi-mandiri-siswa.md'   => ['panduan-siswa', 'siswa', 1, true],
        '00-izin-sakit-siswa.md'        => ['panduan-siswa', 'siswa', 2, true],
        '33-melihat-leaderboard-badge-siswa.md' => ['panduan-siswa', 'siswa', 3, false],
        '34-mengupdate-profil-siswa.md' => ['panduan-siswa', 'siswa', 4, false],

        // ── Panduan Guru ───────────────────────────────────────────────────
        '00-absensi-izin-guru.md'      => ['panduan-guru', 'guru', 1, true],
        '35-mengelola-izin-siswa.md'   => ['panduan-guru', 'guru,wali_kelas', 2, false],
        '36-mengisi-nilai-ekskul.md'   => ['panduan-guru', 'guru', 3, false],

        // ── Panduan Wali Kelas ─────────────────────────────────────────────
        '01-dashboard-wali-kelas.md'      => ['panduan-wali-kelas', 'wali_kelas', 1, true],
        '02-absensi-siswa-per-kelas.md'    => ['panduan-wali-kelas', 'wali_kelas,guru', 2, true],
        '03-rekap-harian.md'              => ['panduan-wali-kelas', 'wali_kelas', 3, false],

        // ── Panduan Orang Tua ──────────────────────────────────────────────
        '00-pantau-anak-ortu.md'  => ['panduan-orang-tua', 'orang_tua', 1, true],

        // ── Panduan Staff TU ────────────────────────────────────────────────
        '04-dashboard-absensi-staff.md' => ['panduan-staff-tu', 'staff_tu', 1, true],
        '05-izin-sakit-staff.md'       => ['panduan-staff-tu', 'staff_tu', 2, false],

        // ── Panduan Operator ───────────────────────────────────────────────
        '06-manajemen-kelas.md'              => ['panduan-operator', 'operator', 1, true],
        '07-manajemen-siswa.md'              => ['panduan-operator', 'operator', 2, true],
        '08-manajemen-guru.md'               => ['panduan-operator', 'operator', 3, false],
        '09-manajemen-staff-tu.md'           => ['panduan-operator', 'operator', 4, false],
        '10-manajemen-wali-kelas.md'         => ['panduan-operator', 'operator', 5, false],
        '11-manajemen-tahun-akademik.md'     => ['panduan-operator', 'operator', 6, false],
        '12-manajemen-jadwal-pelajaran.md'   => ['panduan-operator', 'operator', 7, false],
        '13-manajemen-kegiatan-khusus.md'    => ['panduan-operator', 'operator', 8, false],
        '14-manajemen-ekskul.md'             => ['panduan-operator', 'operator', 9, false],
        '15-absensi-manual.md'               => ['panduan-operator', 'operator', 10, false],
        '16-laporan-export.md'               => ['panduan-operator', 'operator', 11, true],
        '17-pelepasan-kelas-xii.md'          => ['panduan-operator', 'operator', 12, false],
        '20-hari-libur.md'                   => ['panduan-operator', 'operator', 13, false],
        '22-cetak-qr-massal.md'              => ['panduan-operator', 'operator', 14, false],
        '23-naik-kelas-massal.md'            => ['panduan-operator', 'operator', 15, false],
        '24-live-monitor.md'                 => ['panduan-operator', 'operator', 16, false],
        '31-mode-offline.md'                 => ['panduan-operator', 'operator', 17, false],
        '37-manajemen-jurusan.md'            => ['panduan-operator', 'operator', 18, false],

        // ── Panduan Admin Sekolah ──────────────────────────────────────────
        '18-pengaturan-aplikasi.md'    => ['panduan-admin-sekolah', 'admin_sekolah', 1, true],
        '19-wa-gateway.md'             => ['panduan-admin-sekolah', 'admin_sekolah', 2, false],
        '21-gamifikasi.md'             => ['panduan-admin-sekolah', 'admin_sekolah', 3, true],
        '25-analytics-dashboard.md'    => ['panduan-admin-sekolah', 'admin_sekolah', 4, false],
        '32-notifikasi-wa.md'          => ['panduan-admin-sekolah', 'admin_sekolah', 5, false],

        // ── Panduan Super Admin ────────────────────────────────────────────
        '26-user-management.md'       => ['panduan-super-admin', 'super_admin', 1, true],
        '27-manajemen-lisensi.md'     => ['panduan-super-admin', 'super_admin', 2, false],
        '28-update-sistem.md'         => ['panduan-super-admin', 'super_admin', 3, false],

        // ── Fitur Teknis & Integrasi ───────────────────────────────────────
        '00-scan-qr-publik.md'  => ['fitur-teknis-integrasi', 'operator,guru', 1, false],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guidesPath = base_path('docs/guides/tahap-2');

        if (!File::isDirectory($guidesPath)) {
            $this->command->error("Direktori guide tidak ditemukan: {$guidesPath}");
            return;
        }

        // Ambil default author (user pertama, atau buat dummy jika tidak ada)
        $author = User::first();
        if (!$author) {
            $this->command->warn('Tidak ada user ditemukan. Gunakan author_id = 1 (pastikan user ada).');
            // Buat user default jika perlu — tapi asumsikan sudah ada dari seeder lain
            return;
        }

        $categories = GuideCategory::pluck('id', 'slug');
        $countCreated = 0;
        $countSkipped = 0;

        foreach ($this->guideMapping as $filename => [$categorySlug, $roleTarget, $order, $isFeatured]) {
            $filePath = "{$guidesPath}/{$filename}";

            if (!File::exists($filePath)) {
                $this->command->warn("File tidak ditemukan: {$filePath}");
                $countSkipped++;
                continue;
            }

            // Baca konten markdown
            $content = File::get($filePath);

            // Ekstrak judul dari baris pertama (# Judul)
            $title = $this->extractTitle($content);
            if (!$title) {
                $this->command->warn("Judul tidak ditemukan di file: {$filename}");
                $countSkipped++;
                continue;
            }

            // Generate slug dari judul
            $slug = Str::slug($title);

            // Cek apakah sudah ada guide dengan judul yang sama
            if (Guide::where('slug', $slug)->exists()) {
                $this->command->line("  - Skipped (exists): {$title}");
                $countSkipped++;
                continue;
            }

            // Ambil excerpt dari baris setelah judul (paragraf pertama non-kosong)
            $excerpt = $this->extractExcerpt($content, $title);

            // Dapatkan category_id
            $categoryId = $categories[$categorySlug] ?? null;
            if (!$categoryId) {
                $this->command->warn("Kategori tidak ditemukan: {$categorySlug} untuk {$title}");
                $countSkipped++;
                continue;
            }

            // Buat guide
            Guide::create([
                'title'        => $title,
                'slug'         => $slug,
                'content'      => $content,
                'excerpt'      => $excerpt,
                'category_id'  => $categoryId,
                'role_target'  => $roleTarget,
                'author_id'    => $author->id,
                'status'       => 'published',
                'order'        => $order,
                'is_featured'  => $isFeatured,
                'published_at' => now(),
            ]);

            $countCreated++;
        }

        $this->command->info("✅ GuideSeeder selesai: {$countCreated} dibuat, {$countSkipped} dilewati.");
    }

    /**
     * Ekstrak judul dari konten markdown (baris pertama dengan #).
     */
    private function extractTitle(string $content): ?string
    {
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (str_starts_with($trimmed, '# ') && !str_starts_with($trimmed, '## ')) {
                return trim(substr($trimmed, 2));
            }
        }
        return null;
    }

    /**
     * Ekstrak excerpt dari konten — ambil paragraf pertama setelah judul.
     * Cari baris setelah "## Deskripsi Singkat" atau paragraf pertama yang bermakna.
     */
    private function extractExcerpt(string $content, string $title): string
    {
        $lines = explode("\n", $content);
        $foundDesc = false;
        $paragraphs = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Cari heading Deskripsi Singkat
            if (str_contains($trimmed, 'Deskripsi Singkat')) {
                $foundDesc = true;
                continue;
            }

            // Setelah Deskripsi Singkat, ambil paragraf pertama yang bukan heading/kosong
            if ($foundDesc) {
                if (empty($trimmed) || str_starts_with($trimmed, '#')) {
                    if (!empty($paragraphs)) {
                        break;
                    }
                    if (str_starts_with($trimmed, '#')) {
                        continue;
                    }
                    continue;
                }
                $paragraphs[] = strip_tags($trimmed);
                if (count($paragraphs) >= 2) {
                    break;
                }
            }

            // Fallback: ambil paragraf pertama setelah judul
            if (!$foundDesc && !empty($trimmed) && !str_starts_with($trimmed, '#') && !str_starts_with($trimmed, '---')) {
                $paragraphs[] = strip_tags($trimmed);
                if (count($paragraphs) >= 1) {
                    break;
                }
            }
        }

        $excerpt = !empty($paragraphs) ? implode(' ', $paragraphs) : "Panduan tentang {$title}.";
        return Str::limit($excerpt, 250);
    }
}
