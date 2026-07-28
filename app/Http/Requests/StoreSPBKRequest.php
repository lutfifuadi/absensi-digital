<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSPBKRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isGuruBk() || $user->isSuperAdmin() || $user->isRole('admin_sekolah') || $user->isRole('operator'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'siswa_id' => 'required|exists:siswa,id',
            'level_sp' => 'required|in:SP1,SP2,SP3',
            'tanggal_sp' => 'required|date',
            'catatan_tambahan' => 'required|string|max:1000',
        ];
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'siswa_id' => 'Siswa',
            'level_sp' => 'Tingkat SP',
            'tanggal_sp' => 'Tanggal Penerbitan',
            'catatan_tambahan' => 'Catatan Tambahan / Alasan',
        ];
    }
}
