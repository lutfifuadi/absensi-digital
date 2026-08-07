<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengumumanRead extends Model
{
    use HasFactory;

    protected $table = 'pengumuman_reads';

    public $timestamps = false;

    protected $fillable = [
        'pengumuman_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function pengumuman()
    {
        return $this->belongsTo(Pengumuman::class, 'pengumuman_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
