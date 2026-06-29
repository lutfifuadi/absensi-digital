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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'no_hp')) {
                $table->string('no_hp', 20)->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'hubungan')) {
                $table->string('hubungan', 100)->nullable()->after('no_hp');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('aktif')->after('hubungan'); // aktif, nonaktif
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['no_hp', 'hubungan', 'status']);
        });
    }
};
