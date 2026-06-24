<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            ALTER TABLE tahun_akademik
            ADD COLUMN is_aktif_unique TINYINT(1) 
            GENERATED ALWAYS AS (CASE WHEN is_aktif = 1 THEN 1 ELSE NULL END) VIRTUAL
        ');

        DB::statement('
            CREATE UNIQUE INDEX idx_tahun_akademik_hanya_satu_aktif 
            ON tahun_akademik (is_aktif_unique)
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX idx_tahun_akademik_hanya_satu_aktif ON tahun_akademik');
        DB::statement('ALTER TABLE tahun_akademik DROP COLUMN is_aktif_unique');
    }
};
