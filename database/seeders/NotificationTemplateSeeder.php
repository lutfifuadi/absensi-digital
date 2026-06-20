<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'hadir_masuk' => [
                'label' => 'Hadir Tepat Waktu',
                'content' => <<<'EOS'
Bismillah…
Assalamualaikum warahmatullahi wabarakatuh

Semoga keberkahan selalu menyertai Orang tua/Wali dari ananda {nama}.
Kami informasikan bahwa KEHADIRAN Siswa/Siswi {lembaga}, hari {hari} tanggal {tanggal}

- Nama Siswa/i : {nama}
- Kelas/Kegiatan : {kelas}
- Jam Datang : {jam} WIB
- Status : {status}

{nama} telah {status} dan melakukan presensi kehadiran di Madrasah. Mohon doanya, agar {nama} selalu mempertahankan ketepatan waktu dan semangat belajar.

Terima kasih atas perhatiannya.

Wassalamualaikum warahmatullaahi wabarakatuh
Admin Kesiswaan {lembaga}
EOS,
            ],
            'terlambat_masuk' => [
                'label' => 'Hadir Terlambat',
                'content' => <<<'EOS'
Bismillah…
Assalamualaikum warahmatullahi wabarakatuh

Yth. Orang tua/wali dari {nama}.
Kami informasikan bahwa {nama} kelas {kelas} hari ini ({tanggal}) datang terlambat.

- Nama Siswa/i : {nama}
- Kelas/Kegiatan : {kelas}
- Jam Datang : {jam} WIB
- Status : {status}

{nama} terlambat {jam} menit dari jadwal seharusnya. Mohon doanya agar anak dapat bangkit lebih awal dan selalu tepat waktu.
Kami {status} dengan penuh harap untuk kemajuan {nama}.

Terima kasih atas perhatiannya.

Wassalamualaikum warahmatullaahi wabarakatuh
Admin Kesiswaan {lembaga}
EOS,
            ],
            'sakit_masuk' => [
                'label' => 'Izin Sakit',
                'content' => <<<'EOS'
Bismillah…
Assalamualaikum warahmatullahi wabarakatuh

Yth. Orang tua/wali dari {nama}.
Berikut informasi kehadiran anak hari {hari} tanggal {tanggal}:

- Nama Siswa/i : {nama}
- Kelas/Kegiatan : {kelas}
- Jam Datang : {jam} WIB
- Status : {status}

{nama} hadir dengan status {status}. 
{keterangan}

Mohon doa agar {nama} segera sehat dan dapat mengikuti pembelajaran dengan baik.

Wassalamualaikum warahmatullaahi wabarakatuh
Admin Kesiswaan {lembaga}
EOS,
            ],
            'izin_masuk' => [
                'label' => 'Izin Keperluan',
                'content' => <<<'EOS'
Bismillah…
Assalamualaikum warahmatullahi wabarakatuh

Yth. Orang tua/wali dari {nama}.
Berikut informasi kehadiran anak hari {hari} tanggal {tanggal}:

- Nama Siswa/i : {nama}
- Kelas/Kegiatan : {kelas}
- Jam Datang : {jam} WIB
- Status : {status}

{nama} hadir dengan status {status}.
{keterangan}

Terima kasih atas informasinya. Mohon doa agar kegiatan berjalan lancar.

Wassalamualaikum warahmatullaahi wabarakatuh
Admin Kesiswaan {lembaga}
EOS,
            ],
            'alpha_masuk' => [
                'label' => 'Tidak Hadir (Alpha)',
                'content' => <<<'EOS'
Bismillah…
Assalamualaikum warahmatullahi wabarakatuh

Yth. Orang tua/wali dari {nama}.
Kami wajib memberitahu bahwa {nama} kelas {kelas} TIDAK HADIR pada hari {hari} tanggal {tanggal}.

- Nama Siswa/i : {nama}
- Kelas/Kegiatan : {kelas}
- Status : {status}

{nama} tidak hadir dan tidak ada keterangan dari orang tua/wali.
Kami mohon segera mengkonfirmasi kondisi anak agar kami dapat membantu.

Keselamatan dan kebaikan anak adalah prioritas kami.

Wassalamualaikum warahmatullaahi wabarakatuh
Admin Kesiswaan {lembaga}
EOS,
            ],
            'pelepasan_hadir' => [
                'label' => 'Pelepasan Kelas XII — Hadir',
                'content' => <<<'EOS'
Assalamu'alaikum Wr. Wb.
Yth. Bapak/Ibu Orang Tua/Wali dari *{nama}* – Kelas *{kelas}*,

Dengan hormat, kami sampaikan bahwa putra/putri Bapak/Ibu telah tercatat hadir pada acara *Pelepasan Kelas XII {lembaga}* pada pukul *{jam}* WIB.

Kehadiran ini menjadi kebanggaan tersendiri bagi kami dan semoga menjadi awal yang baik bagi langkah putra/putri menuju masa depan yang gemilang.

Wassalamu'alaikum Wr. Wb.
Admin Kesiswaan {lembaga}
EOS,
            ],
            'pulang' => [
                'label' => 'Informasi Kepulangan',
                'content' => <<<'EOS'
Bismillah…
Assalamualaikum warahmatullahi wabarakatuh

Semoga keberkahan selalu menyertai Orang tua/Wali dari ananda {nama}.
Kami informasikan bahwa {nama} kelas {kelas} telah selesai kegiatan pembelajaran hari ini.

- Nama Siswa/i : {nama}
- Kelas/Kelajaran : {kelas}
- Jam Pulang : {jam} WIB

{nama} {status} dari Madrasah dengan keadaan baik. Mohon doanya agar anak sampai di rumah dengan selamat.

Terima kasih atas kepercayaan Anda kepad kami.

Wassalamualaikum warahmatullaahi wabarakatuh
Admin Kesiswaan {lembaga}
EOS,
            ],
            'badge_baru' => [
                'label' => 'Ucapan Selamat — Badge Baru',
                'content' => <<<'EOS'
Assalamu'alaikum Wr. Wb.
Yth. Bapak/Ibu Orang Tua/Wali dari {nama} — Kelas {kelas},

Alhamdulillah! 🎉 Kami dengan bangga menginformasikan bahwa putra/putri Bapak/Ibu telah berhasil meraih Badge *"{badge}"* di {lembaga}.

Pencapaian ini diberikan karena dedikasi dan kedisiplinan {nama} dalam hal kehadiran di sekolah.

Terima kasih atas dukungan Bapak/Ibu selama ini. Semoga prestasi ini menjadi motivasi untuk terus berkembang.

Wassalamu'alaikum Wr. Wb.
Admin Kesiswaan {lembaga}
EOS,
            ],
            'leaderboard_top3' => [
                'label' => 'Ucapan Selamat — Top 3 Leaderboard',
                'content' => <<<'EOS'
Assalamu'alaikum Wr. Wb.
Yth. Bapak/Ibu Orang Tua/Wali dari {nama} — Kelas {kelas},

Alhamdulillah! 🏆 Kami dengan bangga menginformasikan bahwa putra/putri Bapak/Ibu berhasil meraih *Peringkat #{rank}* sebagai siswa terajin dalam hal kehadiran di {lembaga} dengan skor keaktifan {score} poin.

Pencapaian ini menunjukkan kedisiplinan dan semangat belajar yang luar biasa dari {nama}. Semoga terus dipertahankan dan ditingkatkan.

Terima kasih atas dukungan dan kerjasamanya.

Wassalamu'alaikum Wr. Wb.
Admin Kesiswaan {lembaga}
EOS,
            ],
            'streak_milestone' => [
                'label' => 'Ucapan Selamat — Streak Kehadiran',
                'content' => <<<'EOS'
Assalamu'alaikum Wr. Wb.
Yth. Bapak/Ibu Orang Tua/Wali dari {nama} — Kelas {kelas},

Alhamdulillah! 🌟 Kami dengan bangga menginformasikan bahwa putra/putri Bapak/Ibu telah berhasil hadir tepat waktu selama *{streak} hari berturut-turut* di {lembaga}.

Kedisiplinan yang luar biasa! Semoga {nama} terus istiqomah dan menjadi teladan bagi teman-temannya.

Terima kasih atas dukungan Bapak/Ibu di rumah.

Wassalamu'alaikum Wr. Wb.
Admin Kesiswaan {lembaga}
EOS,
            ],
        ];

        foreach ($templates as $type => $data) {
            NotificationTemplate::updateOrCreate(
                ['type' => $type],
                ['content' => $data['content']]
            );
        }

        echo "Notification templates seeded successfully!" . PHP_EOL;
    }
}