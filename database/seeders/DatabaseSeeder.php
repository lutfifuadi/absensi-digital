<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        $this->call(JurusanSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(SampleUsersSeeder::class);
        $this->call(HolidaySeeder::class);
        $this->call(EkskulSeeder::class);
        $this->call(GuideCategorySeeder::class);
        $this->call(GuideSeeder::class);
        $this->call(PengaduanSeeder::class);
        $this->call(GeminiApiKeySeeder::class);
        $this->call(NotificationTemplateSeeder::class);
        $this->call(WaAutoreplyKeywordSeeder::class);
    }
}
