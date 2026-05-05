<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon');
            $table->text('description');
            $table->enum('badge_type', ['individual', 'class']);
            $table->unsignedInteger('requirement_days');
            $table->enum('requirement_type', ['consecutive', 'total']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('student_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->timestamps();
            $table->unique(['siswa_id', 'badge_id']);
        });

        Schema::create('class_leaderboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->onDelete('cascade');
            $table->unsignedInteger('rank');
            $table->unsignedInteger('total_attendance');
            $table->unsignedInteger('total_present');
            $table->decimal('percentage', 5, 2);
            $table->timestamp('calculated_at');
            $table->timestamps();
            $table->unique(['kelas_id', 'tahun_akademik_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_leaderboards');
        Schema::dropIfExists('student_badges');
        Schema::dropIfExists('badges');
    }
};