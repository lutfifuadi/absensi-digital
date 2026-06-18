<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulPembina extends Model
{
    use HasFactory;

    protected $table = 'ekskul_pembina';

    protected $fillable = [
        'ekskul_id',
        'guru_id',
        'jabatan',
    ];

    /**
     * Relasi ke ekskul.
     */
    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id');
    }

    /**
     * Relasi ke guru.
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
}
