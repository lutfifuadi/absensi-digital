<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL/MariaDB approach to modify ENUM
        DB::statement("ALTER TABLE id_card_templates MODIFY COLUMN type ENUM('siswa', 'guru', 'staff', 'pelepasan') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE id_card_templates MODIFY COLUMN type ENUM('siswa', 'guru', 'staff') NOT NULL");
    }
};
