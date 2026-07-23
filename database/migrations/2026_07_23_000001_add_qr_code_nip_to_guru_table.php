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
            if (!Schema::hasColumn('guru', 'qr_code_nip')) {
                $table->string('qr_code_nip')->nullable()->after('qr_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (Schema::hasColumn('guru', 'qr_code_nip')) {
                $table->dropColumn('qr_code_nip');
            }
        });
    }
};
