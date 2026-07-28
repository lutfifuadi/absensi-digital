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
        DB::table('roles')->updateOrInsert(
            ['slug' => 'waka_kurikulum'],
            [
                'name' => 'WAKA Kurikulum',
                'description' => 'Akses live board monitoring kehadiran guru dan rekap pembelajaran.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('slug', 'waka_kurikulum')->delete();
    }
};
