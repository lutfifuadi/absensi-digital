<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus konstrain UNIQUE pada kolom guru.nip.
     *
     * Alasan: NIP tidak dijadikan satu-satunya penentu identitas guru di level database.
     * Keunikan NIP tetap dijaga di lapisan aplikasi melalui validasi `unique:guru,nip`
     * pada GuruController (store & update), sedangkan level database cukup menjadi
     * penyimpan data. Ini juga menghindari kegagalan saat integrasi/import data yang
     * memiliki NIP duplikat.
     */
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropUnique('guru_nip_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->string('nip')->unique();
        });
    }
};
