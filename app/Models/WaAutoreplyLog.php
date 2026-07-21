<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaAutoreplyLog extends Model
{
    use HasFactory;

    protected $table = 'wa_autoreply_logs';

    protected $guarded = ['id'];

    protected $casts = [
        'student_found' => 'boolean',
        'is_success' => 'boolean',
        'response_sent' => 'boolean',
        'student_details' => 'array',
        'created_at' => 'datetime',
    ];

    /*
    |----------------------------------------------------------------------
    | Scope Query
    |----------------------------------------------------------------------
    */

    /**
     * Scope untuk filter log yang sukses.
     */
    public function scopeSuccess($query)
    {
        return $query->where('is_success', true);
    }

    /**
     * Scope untuk filter log yang gagal.
     */
    public function scopeFailed($query)
    {
        return $query->where('is_success', false);
    }

    /**
     * Scope untuk filter berdasarkan nomor pengirim.
     */
    public function scopeBySender($query, $sender)
    {
        return $query->where('sender', $sender);
    }

    /**
     * Scope untuk filter berdasarkan tipe template.
     */
    public function scopeByTemplate($query, $templateType)
    {
        return $query->where('template_type', $templateType);
    }

    /**
     * Scope untuk filter log hari ini.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /*
    |----------------------------------------------------------------------
    | Accessor
    |----------------------------------------------------------------------
    */

    /**
     * Accessor untuk mengambil nama-nama siswa dari student_details.
     * Return string gabungan nama siswa, atau 'Tidak ada data' jika kosong.
     */
    public function getStudentNamesAttribute(): string
    {
        if (is_null($this->student_details)) {
            return 'Tidak ada data';
        }

        $details = $this->student_details;

        // Jika student_details adalah array of objects dengan properti 'nama'
        if (is_array($details)) {
            $names = array_map(function ($item) {
                return $item['nama'] ?? $item['name'] ?? json_encode($item);
            }, $details);

            return implode(', ', array_filter($names));
        }

        return 'Tidak ada data';
    }

    /**
     * Accessor untuk menampilkan label keyword.
     * Return keyword_used jika ada, atau '(Default: Bantuan)' jika null.
     */
    public function getKeywordLabelAttribute(): string
    {
        return $this->keyword_used ?? '(Default: Bantuan)';
    }
}
