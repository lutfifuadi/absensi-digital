<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_sheet_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('spreadsheet_id');
            $table->string('sheet_range')->default('Sheet1!A:Z');
            $table->text('credentials_json');
            $table->json('column_mapping')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->text('last_sync_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_sheet_settings');
    }
};
