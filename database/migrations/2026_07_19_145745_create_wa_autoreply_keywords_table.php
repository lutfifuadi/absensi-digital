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
        Schema::create('wa_autoreply_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->unique()->index();
            $table->string('match_type')->default('exact'); // 'exact', 'contains' dll
            $table->boolean('is_validation_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('notification_template_type')->nullable(); // Relasi loose ke type di notification_templates
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_autoreply_keywords');
    }
};
