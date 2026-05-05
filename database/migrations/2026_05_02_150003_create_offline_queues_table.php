<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_queues', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->json('payload');
            $table->string('device_uuid');
            $table->enum('status', ['pending', 'synced', 'failed'])->default('pending');
            $table->unsignedInteger('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::table('authorized_devices', function (Blueprint $table) {
            $table->boolean('offline_mode_enabled')->default(false);
            $table->integer('max_retry_attempts')->default(3);
        });
    }

    public function down(): void
    {
        Schema::table('authorized_devices', function (Blueprint $table) {
            $table->dropColumn(['offline_mode_enabled', 'max_retry_attempts']);
        });
        Schema::dropIfExists('offline_queues');
    }
};