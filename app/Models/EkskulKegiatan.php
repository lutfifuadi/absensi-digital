<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulKegiatan extends Model
{
    use HasFactory;

    protected $table = 'ekskul_kegiatan';

    protected $fillable = [
        'ekskul_id',
        'nama_kegiatan',
        'tanggal',
        'deskripsi',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi ke ekskul.
     */
    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id');
    }
}
