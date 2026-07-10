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
        Schema::create('google_drive_settings', function (Blueprint $table) {
            $table->id();
            $table->text('google_client_id')->nullable();
            $table->text('google_client_secret')->nullable();
            $table->text('google_redirect_uri')->nullable();
            $table->text('google_root_folder_id')->nullable();
            $table->text('google_access_token')->nullable();
            $table->text('google_refresh_token')->nullable();
            $table->dateTime('google_token_expires_at')->nullable();
            $table->boolean('is_connected')->default(false);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_drive_settings');
    }
};
