<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL ENUM cannot be modified directly via Laravel Schema builder
        // Using raw SQL to modify the enum
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE `upload_batch_items` MODIFY COLUMN `status` ENUM('pending', 'processing', 'success', 'failed', 'cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE `upload_batch_items` MODIFY COLUMN `status` ENUM('pending', 'processing', 'success', 'failed') NOT NULL DEFAULT 'pending'");
        }
    }
};
