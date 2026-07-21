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
        Schema::create('pelanggaran_jenis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('pelanggaran_kategori')->onDelete('cascade');
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();
            $table->tinyInteger('bobot_poin')->unsigned();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_jenis');
    }
};
