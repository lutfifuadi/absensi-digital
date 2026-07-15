<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:holidays {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi hari libur nasional Indonesia dari API luar ke dalam database local';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $yearInput = $this->argument('year');
        $year = $yearInput ? (int) $yearInput : (int) now()->year;

        $this->info("Memulai sinkronisasi hari libur nasional untuk tahun: {$year} (beserta tahun " . ($year - 1) . " dan " . ($year + 1) . ")...");

        try {
            $response = Http::withoutVerifying()->get('https://raw.githubusercontent.com/guangrei/APIHariLibur_V2/main/calendar.json');

            if (!$response->successful()) {
                $this->error("Gagal mengambil data dari API hari libur. Status code: " . $response->status());
                return Command::FAILURE;
            }

            $data = $response->json();
            $years = [$year - 1, $year, $year + 1];
            $count = 0;

            foreach ($data as $date => $info) {
                if (isset($info['holiday']) && $info['holiday'] === true) {
                    $parsedYear = (int) date('Y', strtotime($date));

                    if (in_array($parsedYear, $years)) {
                        $summary = $info['summary'][0] ?? 'Hari Libur Nasional';

                        Holiday::updateOrCreate(
                            ['tanggal' => $date],
                            [
                                'nama' => $summary,
                                'jenis' => 'national',
                                'is_national_holiday' => true
                            ]
                        );
                        $count++;
                    }
                }
            }

            $this->info("Sinkronisasi selesai! Berhasil memperbarui/membuat {$count} hari libur nasional.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Terjadi error saat sinkronisasi: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
