<?php

namespace App\Console\Commands;

use App\Models\IdCardTemplate;
use Illuminate\Console\Command;

class MigrateIdCardConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'idcard:migrate-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi struktur JSON config ID Card Template ke format dua sisi (Front & Back)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $templates = IdCardTemplate::all();
        $migratedCount = 0;

        $defaultElements = [
            'photo' => ['x' => 39, 'y' => 50, 'w' => 75, 'h' => 100, 'z_index' => 1, 'show' => false],
            'qr' => ['x' => 49, 'y' => 165, 'w' => 55, 'h' => 55, 'z_index' => 1, 'show' => false],
            'barcode' => ['x' => 39, 'y' => 195, 'w' => 75, 'h' => 25, 'z_index' => 1, 'show' => false],
            'name' => ['x' => 0, 'y' => 20, 'size' => 10, 'color' => '#000000', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'uppercase'],
            'nis' => ['x' => 0, 'y' => 32, 'size' => 7, 'color' => '#555555', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'nisn' => ['x' => 0, 'y' => 40, 'size' => 7, 'color' => '#555555', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'nip' => ['x' => 0, 'y' => 32, 'size' => 7, 'color' => '#555555', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'class' => ['x' => 0, 'y' => 152, 'size' => 8, 'color' => '#555555', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'gender' => ['x' => 0, 'y' => 222, 'size' => 6, 'color' => '#555555', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'ttl' => ['x' => 0, 'y' => 228, 'size' => 6, 'color' => '#555555', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'masa_berlaku' => ['x' => 0, 'y' => 234, 'size' => 6, 'color' => '#555555', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'logo_lembaga' => ['x' => 10, 'y' => 10, 'w' => 25, 'h' => 25, 'z_index' => 1, 'show' => false],
            'logo_dinas' => ['x' => 10, 'y' => 40, 'w' => 25, 'h' => 25, 'z_index' => 1, 'show' => false],
            'nama_lembaga' => ['x' => 40, 'y' => 12, 'size' => 8, 'color' => '#000000', 'z_index' => 1, 'show' => false, 'align' => 'left', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'alamat_lembaga' => ['x' => 40, 'y' => 22, 'size' => 5, 'color' => '#333333', 'z_index' => 1, 'show' => false, 'align' => 'left', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'tempat_tanggal_terbit' => ['x' => 0, 'y' => 222, 'size' => 6, 'color' => '#333333', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'ttd_kepala_sekolah' => ['x' => 50, 'y' => 228, 'w' => 30, 'h' => 12, 'z_index' => 1, 'show' => false],
            'cap_lembaga' => ['x' => 30, 'y' => 225, 'w' => 20, 'h' => 20, 'z_index' => 1, 'show' => false],
            'nama_kepala_sekolah' => ['x' => 0, 'y' => 240, 'size' => 6, 'color' => '#000000', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'nip_kepala_sekolah' => ['x' => 0, 'y' => 246, 'size' => 5, 'color' => '#333333', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'custom_text_1' => ['x' => 10, 'y' => 140, 'size' => 8, 'color' => '#000000', 'z_index' => 1, 'show' => false, 'align' => 'center', 'content' => 'Teks Kustom 1', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'custom_text_2' => ['x' => 10, 'y' => 150, 'size' => 8, 'color' => '#000000', 'z_index' => 1, 'show' => false, 'align' => 'center', 'content' => 'Teks Kustom 2', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'custom_text_3' => ['x' => 10, 'y' => 160, 'size' => 8, 'color' => '#000000', 'z_index' => 1, 'show' => false, 'align' => 'center', 'content' => 'Teks Kustom 3', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'divider_1' => ['x' => 10, 'y' => 170, 'w' => 133, 'h' => 2, 'color' => '#cccccc', 'z_index' => 1, 'show' => false],
            'divider_2' => ['x' => 10, 'y' => 180, 'w' => 133, 'h' => 2, 'color' => '#cccccc', 'z_index' => 1, 'show' => false],
        ];

        foreach ($templates as $template) {
            $config = $template->config;
            if (isset($config['elements']) && !isset($config['front']['elements'])) {
                $frontElements = $config['elements'];
                
                // Build back elements based on front elements (with show = false)
                $backElements = [];
                foreach ($frontElements as $k => $v) {
                    $backElements[$k] = array_merge($v, ['show' => false]);
                }
                // Ensure all default elements exist in back
                foreach ($defaultElements as $k => $v) {
                    if (!isset($backElements[$k])) {
                        $backElements[$k] = $v;
                    }
                }

                $newConfig = [
                    'canvas' => $config['canvas'] ?? ['width' => 153, 'height' => 243, 'border_radius' => 5],
                    'front' => [
                        'elements' => $frontElements,
                    ],
                    'back' => [
                        'elements' => $backElements,
                    ],
                ];

                $template->config = $newConfig;
                $template->save();
                $migratedCount++;
            }
        }

        $this->info("{$migratedCount} dari " . count($templates) . " template berhasil dimigrasikan ke format dua sisi.");
        return Command::SUCCESS;
    }
}
