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
        Schema::create('log_pengaduan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaduan_id')->constrained('pengaduan')->onDelete('cascade');
            $table->enum('status_dari', ['baru', 'diproses', 'selesai', 'ditolak']);
            $table->enum('status_ke', ['baru', 'diproses', 'selesai', 'ditolak']);
            $table->text('catatan')->nullable();
            $table->string('diubah_oleh', 50)->comment('sistem atau admin:{id}');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_pengaduan');
    }
};
