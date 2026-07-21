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
        Schema::create('wa_autoreply_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('id');
            $table->string('sender', 20);
            $table->text('message');
            $table->string('keyword_used', 255)->nullable();
            $table->string('match_type', 20)->nullable()->comment('exact, contains, atau null');
            $table->string('template_type', 100)->comment('Tipe template yang dikirim');
            $table->boolean('student_found')->default(false);
            $table->text('student_details')->nullable()->comment('JSON string detail siswa yang ditemukan');
            $table->boolean('is_success')->default(true);
            $table->text('error_message')->nullable();
            $table->boolean('response_sent')->default(true);
            $table->string('ip_address', 45)->nullable()->comment('IP pengirim request webhook');
            $table->timestamps();

            // Index untuk optimasi query
            $table->index('sender');
            $table->index('keyword_used');
            $table->index('template_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_autoreply_logs');
    }
};
