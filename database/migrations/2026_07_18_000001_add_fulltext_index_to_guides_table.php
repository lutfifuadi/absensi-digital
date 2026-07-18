<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // FULLTEXT hanya didukung oleh MySQL/MariaDB, bukan SQLite
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE guides ADD FULLTEXT guides_title_content_fulltext (title, content)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE guides DROP INDEX guides_title_content_fulltext');
        }
    }
};
