<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BkLogKonseling extends Model
{
    use HasFactory;

    protected $table = 'bk_log_konseling';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_konseling' => 'date',
    ];

    public function kasus()
    {
        return $this->belongsTo(BkKasus::class, 'bk_kasus_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function konselor()
    {
        return $this->belongsTo(Guru::class, 'guru_bk_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
