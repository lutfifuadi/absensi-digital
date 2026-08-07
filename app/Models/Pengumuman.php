<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'slug',
        'konten',
        'kategori',
        'target',
        'target_kelas_id',
        'lampiran',
        'is_pinned',
        'is_aktif',
        'tanggal_mulai',
        'tanggal_selesai',
        'created_by',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_aktif' => 'boolean',
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->judul) . '-' . Str::random(5);
            }
        });
    }

    public function targetKelas()
    {
        return $this->belongsTo(Kelas::class, 'target_kelas_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeAktif($query)
    {
        $now = now();
        return $query->where('is_aktif', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('tanggal_mulai')
                  ->orWhere('tanggal_mulai', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('tanggal_selesai')
                  ->orWhere('tanggal_selesai', '>=', $now);
            });
    }

    public function scopeTargetForUser($query, $user)
    {
        if (!$user) {
            return $query->where('target', 'semua');
        }

        // Super Admin & Admin Sekolah & Operator melihat semua
        if ($user->hasAnyRole(['super_admin', 'admin_sekolah', 'operator'])) {
            return $query;
        }

        $userRoles = is_array($user->roles) ? $user->roles : [];
        if (!empty($user->role)) {
            $userRoles[] = $user->role;
        }
        $userRoles = array_unique(array_filter($userRoles));

        return $query->where(function ($q) use ($user, $userRoles) {
            // Target 'semua' berlaku untuk semua user
            $q->where('target', 'semua');

            if (array_intersect($userRoles, ['guru', 'wali_kelas', 'guru_bk', 'piket', 'waka_kurikulum'])) {
                $q->orWhere('target', 'guru');
            }

            if (in_array('siswa', $userRoles, true)) {
                $q->orWhere('target', 'siswa');

                // Jika user siswa, cek kelasnya
                $siswa = $user->siswa;
                if ($siswa && $siswa->kelas_id) {
                    $q->orWhere(function ($subQ) use ($siswa) {
                        $subQ->where('target', 'kelas')
                             ->where('target_kelas_id', $siswa->kelas_id);
                    });
                }
            }

            if (array_intersect($userRoles, ['orang_tua', 'wali'])) {
                $q->orWhere('target', 'orang_tua');
            }

            if (array_intersect($userRoles, ['staff', 'staff_tu', 'tata_usaha'])) {
                $q->orWhere('target', 'staff');
            }
        });
    }
}
