<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $table = 'assignments';

    protected $fillable = [
        'guru_id',
        'kelas_id',
        'mata_pelajaran',
        'judul',
        'deskripsi',
        'tanggal_tugas',
        'file_lampiran',
    ];

    protected $casts = [
        'tanggal_tugas' => 'date',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}
