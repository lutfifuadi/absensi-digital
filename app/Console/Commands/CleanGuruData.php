<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CleanGuruData extends Command
{
    protected $signature = 'clean:guru-data';
    protected $description = 'Hapus semua data guru selain 93 NIP yang telah ditentukan';

    public function handle()
    {
        // 93 NIP yang dipertahankan (92 dari Excel AKTIF + Lutfi Fuadi Majid)
        $keepNips = [
            '198801018600013204', // Lutfi Fuadi Majid
            '2454756657300042','6939748651200032','2051762664120003','20219665187002',
            '3551746650200013','20219665187001','3273055401980001','8247755656200013',
            '3843748651200032','20219665188001','6544757659300053','3208084310900004',
            '2452755657210103','3273034506920002','3217141103920004','3273286206900001',
            '3211156512900008','1459746649200003','2138756658300063','6458744646300032',
            '9433747648300152','3547745647300063','853747648300022','6147746648300103',
            '9538743646200043','2443746648300042','8755744644300002','455746650200012',
            '7435743644200040','8335743644200033','8046743646200053','9952746648200042',
            '8252739640200023','9547740642200043','20219665196001','4048756658300063',
            '2547752653200032','5935746651200002','5945746650300012','1242751653200033',
            '1158764666110043','3051750652200033','1837744648200032','2247754656110063',
            '9861710122021','2240754654200003','1965470000041','1950090001014',
            '9651747649300062','6643752654200032','1456758659300033','7356754656300023',
            '7547755657300073','339749651200043','20219665181001','6439753654200003',
            '3273154405910003','3553754656300032','20219665190001','3953746648300062',
            '20109358194001','3277015811020025','5552747651200003','3217100609890009',
            '4257746646200003','3204121311960003','3204121311960004','1234567891011123',
            '49761663200073','3839758659300052','20219665192007','20219665186004',
            '3204114506870006','3205050209950002','3273031809970001','3273154602950004',
            '20219665195002','3171085907970001','3217025702970002','3204095705900002',
            '9457754655300013','2171065511909005','6262751654300013','3273166106910002',
            '3210156810980101','2246765666220003','7540750652200032','20219665179002',
            '6142748650300113','1056756657210073','3204295901990008','20219665193005',
        ];

        $this->info("Memulai pembersihan data guru...");
        $this->info("Total NIP yang dipertahankan: " . count($keepNips));

        DB::beginTransaction();

        try {
            // Cari semua guru yang NIP-nya tidak ada di daftar keep
            $gurusToDelete = Guru::whereNotIn('nip', $keepNips)->get();
            $totalToDelete = $gurusToDelete->count();
            $this->info("Guru yang akan dihapus: {$totalToDelete}");

            $deletedGuru = 0;
            $deletedUser = 0;

            foreach ($gurusToDelete as $guru) {
                $user = User::find($guru->user_id);
                
                // Hapus record guru
                $guru->delete();
                $deletedGuru++;

                // Hapus user jika role-nya guru (bukan admin/siswa/lainnya)
                if ($user && $user->role === 'guru') {
                    $user->delete();
                    $deletedUser++;
                }
            }

            DB::commit();

            $this->info("Pembersihan selesai!");
            $this->info("Guru dihapus: {$deletedGuru}");
            $this->info("User guru dihapus: {$deletedUser}");
            $this->info("Sisa guru di database: " . Guru::count());
            $this->info("Sisa user role guru: " . User::where('role', 'guru')->count());

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
