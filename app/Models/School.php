<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
        'unique_code',
        'logo_path',
        'address',
        'phone',
        'email',
        'status',
        'settings',
        'subscription_until'
    ];

    protected $casts = [
        'settings' => 'array',
        'subscription_until' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($school) {
            if (empty($school->unique_code)) {
                $school->unique_code = 'SCH-' . strtoupper(\Illuminate\Support\Str::random(12));
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function guru()
    {
        return $this->hasMany(Guru::class);
    }

    public function staffTataUsaha()
    {
        return $this->hasMany(StaffTataUsaha::class);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
}
