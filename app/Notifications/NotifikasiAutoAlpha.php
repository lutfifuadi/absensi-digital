<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NotifikasiAutoAlpha extends Notification
{
    use Queueable;

    /**
     * @param string $tipe       Penerima: 'siswa', 'ortu', atau 'wali_kelas'
     * @param string $namaSiswa
     * @param string $namaKelas
     * @param string $tanggal     Format Y-m-d
     * @param string $batasJamMasuk  Format H:i
     * @param string $keterangan
     */
    public function __construct(
        public string $tipe,
        public string $namaSiswa,
        public string $namaKelas,
        public string $tanggal,
        public string $batasJamMasuk,
        public string $keterangan,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $tanggalFormatted = \Carbon\Carbon::parse($this->tanggal)
            ->locale('id')
            ->translatedFormat('d F Y');

        $pesan = match ($this->tipe) {
            'siswa'     => "Anda tercatat alpha otomatis pada {$tanggalFormatted} untuk kelas {$this->namaKelas}. Keterangan: {$this->keterangan}",
            'ortu'      => "Anak Anda, {$this->namaSiswa} ({$this->namaKelas}), tercatat alpha otomatis pada {$tanggalFormatted}. Keterangan: {$this->keterangan}",
            'wali_kelas' => "Siswa {$this->namaSiswa} di kelas {$this->namaKelas} tercatat alpha otomatis pada {$tanggalFormatted}. Keterangan: {$this->keterangan}",
            default      => "Data absensi auto-alpha tersimpan.",
        };

        return [
            'tipe'            => $this->tipe,
            'nama_siswa'      => $this->namaSiswa,
            'kelas'           => $this->namaKelas,
            'tanggal'         => $this->tanggal,
            'batas_jam_masuk' => $this->batasJamMasuk,
            'keterangan'      => $this->keterangan,
            'pesan'           => $pesan,
            'url'             => null,
        ];
    }
}
