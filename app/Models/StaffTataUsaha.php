<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffTataUsaha extends Model
{
    use \App\Traits\HasTenant;

    use HasFactory;

    protected $table = 'staff_tata_usaha';

    protected $fillable = [
        'user_id',
        'nip',
        'nama_lengkap',
        'jenis_kelamin',
        'jabatan',
        'no_hp',
        'foto',
        'status',
        'qr_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
