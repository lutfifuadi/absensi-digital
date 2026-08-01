<?php

use App\Support\PengaturanDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom tenant_id jika belum ada
        if (!Schema::hasColumn('pengaturan', 'tenant_id')) {
            Schema::table('pengaturan', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
            });
        }

        // 2. Normalisasi nilai boolean ('Ya' -> '1', 'Tidak' -> '0', 'true' -> '1', 'false' -> '0')
        $toggles = PengaturanDefaults::toggleFeatures();
        foreach ($toggles as $key => $meta) {
            DB::table('pengaturan')
                ->where('key', $key)
                ->whereIn('value', ['Ya', 'true', 'TRUE', '1'])
                ->update(['value' => '1']);

            DB::table('pengaturan')
                ->where('key', $key)
                ->whereIn('value', ['Tidak', 'false', 'FALSE', '0'])
                ->update(['value' => '0']);
        }

        // 3. Normalisasi umum untuk value 'Ya' / 'Tidak' / 'true' / 'false' jika tipe boolean
        DB::table('pengaturan')
            ->whereIn('value', ['Ya', 'true', 'TRUE'])
            ->whereIn('key', array_keys($toggles))
            ->update(['value' => '1']);

        DB::table('pengaturan')
            ->whereIn('value', ['Tidak', 'false', 'FALSE'])
            ->whereIn('key', array_keys($toggles))
            ->update(['value' => '0']);

        // 4. Konsolidasi key duplikat (Copy data jika canonical belum terisi)
        $this->copyIfEmpty('nama_sekolah', 'nama_lembaga');
        $this->copyIfEmpty('telepon_lembaga', 'no_telp_lembaga');
        $this->copyIfEmpty('kontak_lembaga', 'no_telp_lembaga');
        $this->copyIfEmpty('logo_url', 'logo_sekolah');
        $this->copyIfEmpty('gemini_api_keys', 'gemini_api_key');

        // 5. Pastikan semua key SOT terisi di database
        $defaults = PengaturanDefaults::definitions();
        foreach ($defaults as $key => $meta) {
            $exists = DB::table('pengaturan')->where('key', $key)->exists();
            if (!$exists) {
                DB::table('pengaturan')->insert([
                    'key'        => $key,
                    'value'      => $meta['default'],
                    'group'      => $meta['group'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Copy value dari key asal ke key tujuan jika key tujuan belum ada / kosong.
     */
    private function copyIfEmpty(string $fromKey, string $toKey): void
    {
        $fromRow = DB::table('pengaturan')->where('key', $fromKey)->first();
        if (!$fromRow || trim((string) $fromRow->value) === '') {
            return;
        }

        $toRow = DB::table('pengaturan')->where('key', $toKey)->first();
        if (!$toRow) {
            $meta = PengaturanDefaults::get($toKey);
            DB::table('pengaturan')->insert([
                'key'        => $toKey,
                'value'      => $fromRow->value,
                'group'      => $meta['group'] ?? 'umum',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (trim((string) $toRow->value) === '') {
            DB::table('pengaturan')
                ->where('key', $toKey)
                ->update(['value' => $fromRow->value, 'updated_at' => now()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pengaturan', 'tenant_id')) {
            Schema::table('pengaturan', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
