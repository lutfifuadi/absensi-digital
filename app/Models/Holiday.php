<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $table = 'holidays';

    protected $fillable = [
        'tanggal',
        'nama',
        'jenis',
        'is_national_holiday',
        'tingkat',
        'kelas_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_national_holiday' => 'boolean',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}