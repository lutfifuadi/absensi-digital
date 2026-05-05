<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('unique_code', 64)->nullable()->after('subdomain')->unique();
        });

        // Generate for existing schools
        $schools = DB::table('schools')->get();
        foreach ($schools as $school) {
            DB::table('schools')->where('id', $school->id)->update([
                'unique_code' => 'SCH-' . strtoupper(Str::random(12))
            ]);
        }

        // Make it NOT NULL after backfilling
        DB::statement('ALTER TABLE schools MODIFY unique_code VARCHAR(64) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('unique_code');
        });
    }
};
