<?php

namespace App\Notifications;

use App\Models\JadwalPelajaran;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BelumMonitorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $jadwal;

    /**
     * Create a new notification instance.
     */
    public function __construct(JadwalPelajaran $jadwal)
    {
        $this->jadwal = $jadwal;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $namaGuru = $this->jadwal->guru ? $this->jadwal->guru->nama : 'Tidak diketahui';
        $namaKelas = $this->jadwal->kelas ? $this->jadwal->kelas->nama_kelas : 'Tidak diketahui';
        $jam = substr($this->jadwal->jam_mulai, 0, 5) . ' - ' . substr($this->jadwal->jam_selesai, 0, 5);
        
        return [
            'jadwal_pelajaran_id' => $this->jadwal->id,
            'title' => 'Kelas Belum Dimonitor',
            'message' => "Kelas {$namaKelas} ({$this->jadwal->mata_pelajaran}) dengan guru {$namaGuru} pada jam {$jam} belum dimonitor kehadirannya.",
            'action_url' => '/waka/live-board',
            'icon' => 'warning',
        ];
    }
}
