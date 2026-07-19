<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $table = 'notification_templates';

    protected $fillable = [
        'type',
        'content',
    ];

    public const TYPES = [
        'hadir_masuk' => 'Hadir Tepat Waktu',
        'terlambat_masuk' => 'Hadir Terlambat',
        'sakit_masuk' => 'Izin Sakit',
        'izin_masuk' => 'Izin Keperluan',
        'alpha_masuk' => 'Tidak Hadir (Alpha)',
        'pulang' => 'Informasi Kepulangan',
        'pelepasan_hadir' => 'Pelepasan Kelas Akhir — Hadir',
        'badge_baru' => 'Ucapan Selamat — Badge Baru',
        'leaderboard_top3' => 'Ucapan Selamat — Top 3 Leaderboard',
        'streak_milestone' => 'Ucapan Selamat — Streak Kehadiran',
        'pengaduan_kode_unik' => 'WhatsApp Pengaduan — Kode Unik',
        'pengaduan_status_update' => 'WhatsApp Pengaduan — Update Status',
        'pengaduan_group_admin' => 'WhatsApp Pengaduan — Grup Admin',
        'autoreply_absen' => 'Autoreply — Cek Absen',
        'autoreply_rekap' => 'Autoreply — Rekap Mingguan',
        'autoreply_bantuan' => 'Autoreply — Menu Bantuan',
        'autoreply_link' => 'Autoreply — Link Portal',
        'autoreply_pengaduan' => 'Autoreply — Pengaduan',
        'autoreply_nomor_tak_dikenal' => 'Autoreply — Nomor Tidak Terdaftar',
    ];
}
