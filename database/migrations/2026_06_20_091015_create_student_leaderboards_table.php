<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_leaderboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->onDelete('cascade');
            $table->unsignedInteger('rank');
            $table->integer('score');
            $table->unsignedInteger('total_attendance');
            $table->unsignedInteger('total_present');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_akademik_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_leaderboards');
    }
};
