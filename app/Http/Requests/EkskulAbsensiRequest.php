<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EkskulAbsensiRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'absensi'                => 'required|array|min:1',
            'absensi.*.siswa_id'     => 'required|exists:siswa,id',
            'absensi.*.status'       => 'required|in:hadir,izin,sakit,alpha,terlambat',
            'absensi.*.jam_absen'    => 'nullable|date_format:H:i',
            'absensi.*.keterangan'   => 'nullable|string|max:500',
            'pembina_id'             => 'nullable|exists:guru,id',
        ];
    }

    /**
     * Custom messages for validation.
     */
    public function messages(): array
    {
        return [
            'absensi.required'            => 'Data absensi wajib diisi.',
            'absensi.*.siswa_id.required' => 'Siswa wajib dipilih.',
            'absensi.*.siswa_id.exists'   => 'Siswa tidak ditemukan.',
            'absensi.*.status.required'   => 'Status absensi wajib diisi.',
            'absensi.*.status.in'         => 'Status absensi tidak valid.',
        ];
    }
}
