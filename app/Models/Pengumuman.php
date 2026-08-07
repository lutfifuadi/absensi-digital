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

        // Super Admin & Admin Sekolah melihat semua
        if ($user->hasRole(['super_admin', 'admin_sekolah', 'operator'])) {
            return $query;
        }

        $roles = $user->roles->pluck('name')->toArray();
        if (empty($roles) && isset($user->role)) {
            $roles = [$user->role];
        }

        return $query->where(function ($q) use ($user, $roles) {
            // Target 'semua' berlaku untuk semua user
            $q->where('target', 'semua');

            if (in_array('guru', $roles)) {
                $q->orWhere('target', 'guru');
            }

            if (in_array('siswa', $roles)) {
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

            if (in_array('orang_tua', $roles) || in_array('wali', $roles)) {
                $q->orWhere('target', 'orang_tua');
            }

            if (in_array('staff', $roles) || in_array('tata_usaha', $roles)) {
                $q->orWhere('target', 'staff');
            }
        });
    }
}
