<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian_lisensi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_klien');
            $table->string('email_klien');
            $table->string('domain')->nullable()->comment('Domain yang didaftarkan, diisi saat konfirmasi');
            $table->string('license_key')->unique()->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'revoked'])->default('pending');
            $table->enum('payment_status', ['menunggu', 'lunas'])->default('menunggu');
            $table->string('download_token', 64)->unique()->nullable()->comment('Token untuk link download sekali pakai');
            $table->text('catatan')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_lisensi');
    }
};
