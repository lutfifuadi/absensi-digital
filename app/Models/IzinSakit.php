<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinSakit extends Model
{
    use HasFactory;

    protected $table = 'izin_sakit';
}

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'reference_id');
    }

    public function staff()
    {
        return $this->belongsTo(StaffTataUsaha::class, 'reference_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
