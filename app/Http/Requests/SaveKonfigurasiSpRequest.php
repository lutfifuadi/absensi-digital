<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveKonfigurasiSpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'batas_sp1' => 'required|integer|min:1',
            'batas_sp2' => 'required|integer|gt:batas_sp1',
            'batas_sp3' => 'required|integer|gt:batas_sp2',
            'notif_wa_aktif' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'batas_sp2.gt' => 'Batas SP2 harus lebih besar dari Batas SP1.',
            'batas_sp3.gt' => 'Batas SP3 harus lebih besar dari Batas SP2.',
        ];
    }
}
