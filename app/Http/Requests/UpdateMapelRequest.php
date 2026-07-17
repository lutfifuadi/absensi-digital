<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMapelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mapelId = $this->route('mapel')?->id ?? $this->route('mapel');

        return [
            'kode_mapel' => 'required|string|max:20|unique:mapels,kode_mapel,' . $mapelId,
            'nama_mapel' => 'required|string|max:100',
            'kelompok' => 'required|in:umum,kejuruan,muatan_lokal',
            'status' => 'required|boolean',
        ];
    }
}
