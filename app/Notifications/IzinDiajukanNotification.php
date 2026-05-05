<?php

namespace App\Notifications;

use App\Models\IzinSakit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IzinDiajukanNotification extends Notification
{
    use Queueable;

    public function __construct(public IzinSakit $izin) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $nama = match($this->izin->tipe) {
            'siswa' => $this->izin->siswa?->nama_lengkap ?? '-',
            'guru'  => $this->izin->guru?->nama_lengkap ?? '-',
            default => $this->izin->staff?->nama_lengkap ?? '-',
        };

        return [
            'izin_id'  => $this->izin->id,
            'tipe'     => $this->izin->tipe,
            'jenis'    => $this->izin->jenis,
            'nama'     => $nama,
            'periode'  => $this->izin->tanggal_mulai->format('d M Y') . ' - ' . $this->izin->tanggal_selesai->format('d M Y'),
            'pesan'    => "Pengajuan {$this->izin->jenis} dari {$nama} menunggu persetujuan.",
            'url'      => route('admin.izin-sakit.edit', $this->izin),
