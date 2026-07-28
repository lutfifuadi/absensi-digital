<?php

namespace App\Http\Requests\Api\V2\Monitoring;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePiketMonitoringRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:hadir,tidak_hadir,terlambat'],
            'keterangan' => ['required_if:status,tidak_hadir', 'nullable', 'in:sakit,izin,dinas_luar,alfa'],
            'keterangan_lain' => ['nullable', 'string', 'max:500'],
            'lama_terlambat' => ['required_if:status,terlambat', 'nullable', 'integer', 'min:1', 'max:120'],
            'ada_pengganti' => ['required', 'boolean'],
            'guru_pengganti_id' => ['nullable', 'integer', 'exists:guru,id'],
            'guru_pengganti_nama' => ['nullable', 'string', 'max:191'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'keterangan.required_if' => 'Keterangan wajib diisi jika status adalah tidak hadir.',
            'lama_terlambat.required_if' => 'Lama keterlambatan wajib diisi jika status adalah terlambat.',
        ];
    }
}
