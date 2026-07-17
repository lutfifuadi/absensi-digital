<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMapelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_mapel' => 'required|string|max:20|unique:mapels,kode_mapel',
            'nama_mapel' => 'required|string|max:100',
            'kelompok' => 'required|in:umum,kejuruan,muatan_lokal',
            'status' => 'required|boolean',
        ];
    }
}
