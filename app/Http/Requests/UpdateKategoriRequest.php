<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'warna' => 'nullable|string|max:7',
            'urutan' => 'nullable|integer|min:0|max:255',
            'is_aktif' => 'nullable|boolean',
        ];
    }
}
