<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            ['tanggal' => '2026-01-01', 'nama' => 'Tahun Baru Masehi', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-01-29', 'nama' => 'Isra Mikraj Nabi Muhammad SAW', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-02-17', 'nama' => 'Tahun Baru Islam 1448 H', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-03-09', 'nama' => 'Nyepi 1946 Saka', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-03-20', 'nama' => 'Idulfitri 1447 H (H-2)', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-03-21', 'nama' => 'Idulfitri 1447 H', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-04-03', 'nama' => 'Wafat Isa Al-Masih', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-04-21', 'nama' => 'Hari Raya Waisak 2569 BE', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-05-01', 'nama' => 'Hari Buruh Internasional', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-05-17', 'nama' => 'Kenaikan Isa Al-Masih', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-05-27', 'nama' => 'Hari Raya Vesak 2569 BE', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-06-01', 'nama' => 'Pentecost', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-07-17', 'nama' => 'Muharram 1448 H', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-08-17', 'nama' => 'Hari Kemerdekaan Republik Indonesia', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-10-20', 'nama' => 'Hari Sumpah Pemuda', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2026-12-25', 'nama' => 'Hari Raya Natalie', 'jenis' => 'national', 'is_national_holiday' => true],

            ['tanggal' => '2027-01-01', 'nama' => 'Tahun Baru Masehi', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-01-10', 'nama' => 'Isra Mikraj Nabi Muhammad SAW', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-01-29', 'nama' => 'Tahun Baru Islam 1448 H', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-02-17', 'nama' => 'Thaun Ampat', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-03-09', 'nama' => 'Nyepi 1947 Saka', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-03-10', 'nama' => 'Idulfitri 1448 H (H-2)', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-03-11', 'nama' => 'Idulfitri 1448 H', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-04-03', 'nama' => 'Jumat Agung', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-04-21', 'nama' => 'Hari Raya Waisak 2570 BE', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-05-01', 'nama' => 'Hari Buruh Internasional', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-05-06', 'nama' => 'Kenaikan Isa Al-Masih', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-05-16', 'nama' => 'Hari Raya Vesak 2570 BE', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-06-06', 'nama' => 'Maundy Thursday /端午节', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-07-06', 'nama' => 'Muharram 1449 H', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-08-17', 'nama' => 'Hari Kemerdekaan Republik Indonesia', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-10-20', 'nama' => 'Hari Sumpah Pemuda', 'jenis' => 'national', 'is_national_holiday' => true],
            ['tanggal' => '2027-12-25', 'nama' => 'Hari Raya Natal', 'jenis' => 'national', 'is_national_holiday' => true],
        ];

        foreach ($holidays as $holiday) {
            DB::table('holidays')->insert([
                'tanggal' => $holiday['tanggal'],
                'nama' => $holiday['nama'],
                'jenis' => $holiday['jenis'],
                'is_national_holiday' => $holiday['is_national_holiday'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}