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
        Schema::table('pengumuman', function (Blueprint $table) {
            $table->boolean('is_popup')->default(false)->after('is_pinned');
            $table->boolean('force_read')->default(false)->after('is_popup');
        });

        Schema::create('pengumuman_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengumuman_id')->constrained('pengumuman')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('read_at')->useCurrent();
            $table->unique(['pengumuman_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumuman_reads');

        Schema::table('pengumuman', function (Blueprint $table) {
            $table->dropColumn(['is_popup', 'force_read']);
        });
    }
};
