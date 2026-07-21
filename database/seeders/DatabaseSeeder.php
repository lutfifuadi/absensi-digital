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
        // ==========================================
        //  SEEDER DASAR (wajib untuk operasional)
        // ==========================================
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
        $this->call(KategoriPelanggaranSeeder::class);
        $this->call(JenisPelanggaranSeeder::class);

        // ==========================================
        //  DEMO DATA (untuk presentasi / testing)
        //  Komentari baris di bawah jika tidak butuh
        // ==========================================
        $this->call(DemoDataSeeder::class);
    }
}
