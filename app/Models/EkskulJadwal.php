<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulJadwal extends Model
{
    use HasFactory;

    protected $table = 'ekskul_jadwal';

    protected $fillable = [
        'ekskul_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
    ];

    /**
     * Relasi ke ekskul.
     */
    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id');
    }
}
