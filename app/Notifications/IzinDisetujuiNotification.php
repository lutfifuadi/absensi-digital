<?php

namespace App\Notifications;

use App\Models\IzinSakit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IzinDisetujuiNotification extends Notification
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
            'izin_id' => $this->izin->id,
            'tipe'    => $this->izin->tipe,
            'jenis'   => $this->izin->jenis,
            'nama'    => $nama,
            'status'  => $this->izin->status,
            'pesan'   => "Pengajuan {$this->izin->jenis} untuk {$nama} telah " . ($this->izin->status === 'disetujui' ? 'disetujui' : 'ditolak') . ".",
            'url'     => route('admin.izin-sakit.edit', $this->izin),
