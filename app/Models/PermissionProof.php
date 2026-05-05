<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionProof extends Model
{
    use HasFactory;

    protected $table = 'permission_proofs';

    protected $fillable = [
        'izin_sakit_id',
        'filename',
        'original_filename',
        'mime_type',
        'file_path',
        'file_size',
        'status',
        'approval_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function izinSakit()
    {
        return $this->belongsTo(IzinSakit::class, 'izin_sakit_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}