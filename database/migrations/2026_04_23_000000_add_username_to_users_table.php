<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
        });

        $users = DB::table('users')->select('id', 'email')->get();
        foreach ($users as $user) {
            $base = Str::of($user->email)->before('@')->lower()->replaceMatches('/[^a-z0-9._-]/', '');
            $base = $base->isEmpty() ? 'user' . $user->id : $base;

            $username = (string) $base;
            $counter = 1;
            while (DB::table('users')->where('username', $username)->exists()) {
                $username = (string) $base . $counter;
                $counter++;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => $username]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
