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
        Schema::table('holidays', function (Blueprint $table) {
            $table->enum('tingkat', ['X', 'XI', 'XII'])->nullable()->after('jenis');
            $table->foreignId('kelas_id')->nullable()->after('tingkat')->constrained('kelas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropColumn(['tingkat', 'kelas_id']);
        });
    }
};
