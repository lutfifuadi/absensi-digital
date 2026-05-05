<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained()->onDelete('cascade');
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->onDelete('cascade');
            $table->date('date');
            $table->unsignedInteger('total_students');
            $table->unsignedInteger('hadir_tepat_waktu');
            $table->unsignedInteger('terlambat');
            $table->unsignedInteger('sakit');
            $table->unsignedInteger('izin');
            $table->unsignedInteger('alpha');
            $table->decimal('persentase_kehadiran', 5, 2);
            $table->decimal('persentase_keterlambatan', 5, 2);
            $table->boolean('alert_triggered')->default(false);
            $table->text('alert_note')->nullable();
            $table->timestamps();
            $table->unique(['kelas_id', 'tahun_akademik_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_analytics');
    }
};