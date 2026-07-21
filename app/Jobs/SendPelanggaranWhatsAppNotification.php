<?php

namespace App\Jobs;

use App\Models\PelanggaranSiswa;
use App\Models\PelanggaranSp;
use App\Models\PelanggaranNotifLog;
use App\Models\Siswa;
use App\Models\Pengaturan;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendPelanggaranWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $siswaId;
    public $pelanggaranId;
    public $spId;
    public $tipeNotif; // 'pelanggaran_baru' atau 'sp_terbit'

    /**
     * Create a new job instance.
     *
     * @param int $siswaId
     * @param int|null $pelanggaranId
     * @param int|null $spId
     * @param string $tipeNotif
     */
    public function __construct(int $siswaId, ?int $pelanggaranId, ?int $spId, string $tipeNotif)
    {
        $this->siswaId = $siswaId;
        $this->pelanggaranId = $pelanggaranId;
        $this->spId = $spId;
        $this->tipeNotif = $tipeNotif;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $waService): void
    {
        $siswa = Siswa::with(['kelas', 'tahunAkademik'])->find($this->siswaId);
        if (!$siswa) {
            Log::error("SendPelanggaranWhatsAppNotification: Siswa dengan ID {$this->siswaId} tidak ditemukan.");
            return;
        }

        // Tentukan no HP penerima: no_hp_ortu ?: no_hp
        $noHp = $siswa->no_hp_ortu ?: $siswa->no_hp;
        if (empty($noHp)) {
            Log::warning("SendPelanggaranWhatsAppNotification: Nomor HP Siswa/Orang Tua untuk siswa {$siswa->nama_lengkap} kosong.");
            return;
        }

        // Ambil data nama lembaga / sekolah
        $lembaga = Pengaturan::where('key', 'nama_sekolah')->value('value') ?: 'Sekolah';

        // Buat pesan berdasarkan template dan tipe notifikasi
        $pesan = '';
        if ($this->tipeNotif === 'pelanggaran_baru') {
            $pelanggaran = PelanggaranSiswa::with('jenisPelanggaran.kategori')->find($this->pelanggaranId);
            if (!$pelanggaran) {
                Log::error("SendPelanggaranWhatsAppNotification: Pelanggaran dengan ID {$this->pelanggaranId} tidak ditemukan.");
                return;
            }

            // Hitung total poin saat ini
            $totalPoin = PelanggaranSiswa::where('siswa_id', $this->siswaId)
                ->where('tahun_akademik_id', $pelanggaran->tahun_akademik_id)
                ->sum('poin_saat_itu');

            $jenis = $pelanggaran->jenisPelanggaran;
            $kategori = $jenis->kategori;

            // Template pesan sesuai spesifikasi PRD Section 11.5 (BR-22)
            $pesan = "Assalamualaikum Wr. Wb.\n"
                . "Bapak/Ibu Orang Tua/Wali dari *{$siswa->nama_lengkap}* (Kelas: {$siswa->kelas?->nama}),\n\n"
                . "Kami menginformasikan bahwa ananda telah melakukan pelanggaran tata tertib sekolah:\n"
                . "- *Kategori*: {$kategori?->nama}\n"
                . "- *Pelanggaran*: {$jenis?->nama}\n"
                . "- *Tanggal*: {$pelanggaran->tanggal_kejadian->format('d-m-Y')}\n"
                . "- *Poin Pelanggaran*: +{$pelanggaran->poin_saat_itu} poin\n"
                . "- *Keterangan*: {$pelanggaran->keterangan}\n\n"
                . "Akumulasi poin pelanggaran ananda saat ini adalah *{$totalPoin} poin*.\n"
                . "Mohon kerjasamanya untuk membimbing ananda agar tidak mengulangi tindakan tersebut.\n\n"
                . "Terima kasih atas perhatian Anda.\n"
                . "Wassalamualaikum Wr. Wb.\n"
                . "Tim Kesiswaan {$lembaga}";
        } elseif ($this->tipeNotif === 'sp_terbit') {
            $sp = PelanggaranSp::find($this->spId);
            if (!$sp) {
                Log::error("SendPelanggaranWhatsAppNotification: SP dengan ID {$this->spId} tidak ditemukan.");
                return;
            }

            // Template pesan sesuai spesifikasi PRD Section 11.5 (BR-23)
            $pesan = "Assalamualaikum Wr. Wb.\n"
                . "Bapak/Ibu Orang Tua/Wali dari *{$siswa->nama_lengkap}* (Kelas: {$siswa->kelas?->nama}),\n\n"
                . "Sehubungan dengan akumulasi poin pelanggaran ananda yang telah mencapai *{$sp->total_poin_saat_sp} poin*,\n"
                . "dengan ini kami sampaikan bahwa sekolah menerbitkan:\n"
                . "⚠️ *{$sp->level_sp} (Surat Peringatan {$sp->level_sp})* ⚠️\n"
                . "pada tanggal {$sp->tanggal_sp->format('d-m-Y')}.\n\n"
                . "Catatan Sekolah:\n"
                . "\"{$sp->catatan_tambahan}\"\n\n"
                . "Kami mengharapkan kehadiran Bapak/Ibu ke sekolah untuk berkoordinasi dengan Wali Kelas/Guru BK guna kebaikan ananda.\n\n"
                . "Terima kasih atas perhatian Anda.\n"
                . "Wassalamualaikum Wr. Wb.\n"
                . "Tim Kesiswaan/Kepala Sekolah {$lembaga}";
        }

        // 1. Buat record awal di tabel pelanggaran_notif_log dengan status pending
        $notifLog = PelanggaranNotifLog::create([
            'pelanggaran_id' => $this->pelanggaranId,
            'sp_id' => $this->spId,
            'siswa_id' => $this->siswaId,
            'penerima_no_hp' => $noHp,
            'tipe_notif' => $this->tipeNotif,
            'status' => 'pending',
            'pesan' => $pesan,
            'dikirim_pada' => null
        ]);

        try {
            // 2. Gunakan WhatsAppService untuk mengirimkan pesan WhatsApp ke no HP
            $sent = $waService->sendMessage($noHp, $pesan, 'Sistem Informasi Pelanggaran Siswa');

            if ($sent) {
                // 3. Jika berhasil, ubah status log jadi sukses
                $notifLog->update([
                    'status' => 'sukses',
                    'dikirim_pada' => now(),
                    'respons_gateway' => json_encode(['status' => true, 'message' => 'Pesan terkirim ke gateway'])
                ]);
            } else {
                // 3. Jika gagal (kembalian false), ubah status log jadi gagal
                $notifLog->update([
                    'status' => 'gagal',
                    'dikirim_pada' => now(),
                    'respons_gateway' => json_encode(['status' => false, 'message' => 'Gagal dikirim oleh gateway'])
                ]);
            }
        } catch (Exception $e) {
            // 3. Jika melempar exception, ubah status log jadi gagal dan catat pesan error
            Log::error("SendPelanggaranWhatsAppNotification Exception: " . $e->getMessage());
            $notifLog->update([
                'status' => 'gagal',
                'dikirim_pada' => now(),
                'respons_gateway' => json_encode(['exception' => $e->getMessage()])
            ]);
        }
    }
}
