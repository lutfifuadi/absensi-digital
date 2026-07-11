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
        Schema::create('upload_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_batch');
            $table->enum('sumber', ['web', 'zip'])->default('web');
            $table->string('file_zip')->nullable();
            $table->integer('total_items')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
        });

        Schema::create('upload_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_batch_id')->constrained('upload_batches')->cascadeOnDelete();
            $table->foreignId('siswa_id')->nullable()->constrained('siswa')->nullOnDelete();
            $table->string('original_filename');
            $table->string('stored_path')->nullable();
            $table->string('google_drive_file_id')->nullable();
            $table->string('old_file_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->tinyInteger('retry_count')->default(0);
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('upload_batch_id');
            $table->index('siswa_id');
            $table->index('status');

            // Unique constraint
            $table->unique(['upload_batch_id', 'original_filename'], 'upload_batch_items_unique_filename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_batch_items');
        Schema::dropIfExists('upload_batches');
    }
};
