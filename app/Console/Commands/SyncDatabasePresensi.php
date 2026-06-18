<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class SyncDatabasePresensi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sync-presensi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data dari presensi_mansaba ke percobaan_absen';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai sinkronisasi database...");

        // Konfigurasi database dinamis
        Config::set('database.connections.live_presensi', [
            'driver' => 'mysql',
            'host' => '103.197.191.226',
            'port' => '3306',
            'database' => 'presensi_mansaba',
            'username' => 'presensi_mansaba',
            'password' => 'presensi_mansaba',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]);

        Config::set('database.connections.experimental_presensi', [
            'driver' => 'mysql',
            'host' => '103.197.191.226',
            'port' => '3306',
            'database' => 'percobaan_absen',
            'username' => 'percobaan_absen',
            'password' => 'percobaan_absen',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]);

        try {
            $liveConn = DB::connection('live_presensi');
            $expConn = DB::connection('experimental_presensi');

            // Ambil daftar tabel dari live
            $tables = $liveConn->select('SHOW TABLES');
            $dbName = 'Tables_in_presensi_mansaba';

            $this->info("Menemukan " . count($tables) . " tabel.");

            // Matikan foreign key checks di experimental
            $expConn->statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tables as $table) {
                $tableName = $table->$dbName;
                $this->line("Processing table: <info>{$tableName}</info>");

                // Hapus data lama di experimental
                $expConn->table($tableName)->truncate();

                // Ambil data dari live dan masukkan ke experimental dalam chunks
                $primaryKey = $this->getPrimaryKey($liveConn, $tableName);
                
                if ($primaryKey) {
                    $liveConn->table($tableName)->orderBy($primaryKey)->chunk(500, function ($rows) use ($expConn, $tableName) {
                        $data = array_map(function ($row) {
                            return (array) $row;
                        }, $rows->toArray());

                        $expConn->table($tableName)->insert($data);
                    });
                } else {
                    // Jika tidak ada primary key, ambil semua data sekaligus (hati-hati dengan tabel besar)
                    $rows = $liveConn->table($tableName)->get();
                    if ($rows->count() > 0) {
                        $data = array_map(function ($row) {
                            return (array) $row;
                        }, $rows->toArray());
                        $expConn->table($tableName)->insert($data);
                    }
                }
            }

            // Hidupkan kembali foreign key checks
            $expConn->statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info("Sinkronisasi selesai dengan sukses!");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Gagal melakukan sinkronisasi: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function getPrimaryKey($connection, $table)
    {
        $columns = $connection->select("SHOW KEYS FROM {$table} WHERE Key_name = 'PRIMARY'");
        return count($columns) > 0 ? $columns[0]->Column_name : null;
    }
}
