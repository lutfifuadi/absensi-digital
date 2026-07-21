<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJenisPelanggaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => 'required|exists:pelanggaran_kategori,id',
            'nama' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'bobot_poin' => 'required|integer|between:1,100',
            'is_aktif' => 'nullable|boolean',
        ];
    }
}
