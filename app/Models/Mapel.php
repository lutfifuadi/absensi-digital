<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mapel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mapels';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'kelompok',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
