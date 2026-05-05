<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff_tata_usaha')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('status')->default('hadir');
            $table->text('keterangan')->nullable();
            $table->string('metode')->default('manual');
            $table->timestamps();

            $table->unique(['staff_id', 'tanggal'], 'absensi_staff_unique_staff_tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_staff');
    }
};
