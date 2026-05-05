<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin_sakit', function (Blueprint $table) {
            $table->id();
            $table->string('tipe');
            $table->unsignedBigInteger('reference_id');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('jenis');
            $table->text('keterangan')->nullable();
            $table->string('lampiran')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izin_sakit');
    }
};
