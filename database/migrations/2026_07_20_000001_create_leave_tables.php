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
        // ==========================================
        // 1. Buat tabel leave_limits
        // ==========================================
        Schema::create('leave_limits', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('leave_type', ['sick', 'permission', 'all']);
            $table->integer('max_days');
            $table->enum('period', ['monthly', 'semester', 'yearly']);
            $table->enum('action_type', ['warning', 'block']);
            $table->json('target_roles');
            $table->json('target_grades')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index untuk mempercepat pencarian rule aktif
            $table->index(['is_active', 'leave_type', 'period']);
        });

        // ==========================================
        // 2. Buat tabel leave_balances
        // ==========================================
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('leave_limit_id');
            $table->string('period_code', 50);
            $table->integer('used_days')->default(0);
            $table->integer('extra_days')->default(0);
            $table->text('dispensation_reason')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('leave_limit_id')
                  ->references('id')
                  ->on('leave_limits')
                  ->onDelete('cascade');

            // Unique constraint: kombinasi user + rule + periode
            $table->unique(['user_id', 'leave_limit_id', 'period_code'], 'uq_leave_balance');

            // Index untuk lookup cepat per user
            $table->index(['user_id', 'period_code']);
        });

        // ==========================================
        // 3. Tambah kolom ke tabel izin_sakit (existing)
        // ==========================================
        Schema::table('izin_sakit', function (Blueprint $table) {
            // Kolom baru untuk tracking batasan perizinan
            $table->boolean('is_overlimit')->default(false)->after('status');
            $table->text('overlimit_reason')->nullable()->after('is_overlimit');
            $table->boolean('is_dispensation')->default(false)->after('overlimit_reason');
            $table->unsignedBigInteger('user_id')->nullable()->after('is_dispensation');

            // FK ke users — user yang mengajukan izin (pelaku login)
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // Index untuk lookup cepat
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan perubahan ke tabel izin_sakit (hapus kolom & FK)
        Schema::table('izin_sakit', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn([
                'is_overlimit',
                'overlimit_reason',
                'is_dispensation',
                'user_id',
            ]);
        });

        // Hapus tabel leave_balances
        Schema::dropIfExists('leave_balances');

        // Hapus tabel leave_limits
        Schema::dropIfExists('leave_limits');
    }
};
