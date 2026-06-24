<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deploy_logs', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50)->nullable();
            $table->string('commit_hash', 40)->nullable();
            $table->text('commit_message')->nullable();
            $table->enum('status', ['running', 'success', 'failed', 'rolled_back'])->default('running');
            $table->string('backup_path', 255)->nullable();
            $table->longText('log_output')->nullable();
            $table->foreignId('triggered_by')->constrained('users');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deploy_logs');
    }
};
