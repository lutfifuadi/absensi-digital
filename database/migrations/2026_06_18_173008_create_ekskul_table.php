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
        Schema::create('ekskul', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('kategori', ['wajib', 'pilihan', 'olahraga', 'seni', 'akademik', 'lainnya'])->default('pilihan');
            $table->text('deskripsi')->nullable();
            $table->integer('kuota')->nullable();
            $table->boolean('status')->default(true);
            $table->string('icon')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekskul');
    }
};
