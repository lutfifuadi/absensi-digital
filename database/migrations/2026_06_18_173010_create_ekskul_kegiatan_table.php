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
        Schema::create('ekskul_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekskul_id')->constrained('ekskul')->cascadeOnDelete();
            $table->string('nama_kegiatan');
            $table->date('tanggal');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->index(['ekskul_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekskul_kegiatan');
    }
};
