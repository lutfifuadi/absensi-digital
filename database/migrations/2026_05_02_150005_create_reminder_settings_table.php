<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_settings', function (Blueprint $table) {
            $table->id();
            $table->string('reminder_type');
            $table->boolean('is_enabled')->default(true);
            $table->enum('channel', ['whatsapp', 'sms', 'both'])->default('whatsapp');
            $table->integer('send_before_minutes')->default(30);
            $table->text('custom_message')->nullable();
            $table->boolean('notify_parent')->default(true);
            $table->timestamps();
            $table->unique(['reminder_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_settings');
    }
};