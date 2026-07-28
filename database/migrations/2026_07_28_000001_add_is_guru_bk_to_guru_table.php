<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (!Schema::hasColumn('guru', 'is_guru_bk')) {
                $table->boolean('is_guru_bk')->default(false)->after('jabatan');
            }
            if (!Schema::hasColumn('guru', 'konseling_limit')) {
                $table->integer('konseling_limit')->default(10)->after('is_guru_bk');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn(['is_guru_bk', 'konseling_limit']);
        });
    }
};
