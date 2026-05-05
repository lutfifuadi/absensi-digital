<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (! Schema::hasColumn('siswa', 'no_hp_ortu')) {
                $table->string('no_hp_ortu')->nullable()->after('no_hp');
            }
        });
    }

    public function down()
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (Schema::hasColumn('siswa', 'no_hp_ortu')) {
                $table->dropColumn('no_hp_ortu');
            }
        });
    }
};
