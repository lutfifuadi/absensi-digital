<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePelanggaranBKRequest extends FormRequest
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
            'jenis_id' => 'required|exists:pelanggaran_jenis,id',
            'tanggal_kejadian' => 'required|date|before_or_equal:today',
            'keterangan' => 'nullable|string|max:500',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'siswa_id' => 'Siswa',
            'jenis_id' => 'Jenis Pelanggaran',
            'tanggal_kejadian' => 'Tanggal Kejadian',
            'keterangan' => 'Keterangan Detail',
            'bukti_foto' => 'Bukti Foto',
        ];
    }
}
