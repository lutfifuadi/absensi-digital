<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengaduanRequest extends FormRequest
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
            'nama_lengkap'   => ['required', 'string', 'min:3', 'max:100'],
            'status_pelapor' => ['required', Rule::in(['siswa', 'orang_tua'])],
            'kategori'       => ['required', 'string', 'min:3', 'max:100'],
            'deskripsi'      => ['required', 'string', 'min:10', 'max:2000'],
            'nomor_wa'       => ['required', 'string', 'min:10', 'max:16', 'regex:/^(08|628)[0-9]{8,13}$/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_lengkap.required'   => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min'        => 'Nama lengkap minimal 3 karakter.',
            'nama_lengkap.max'        => 'Nama lengkap maksimal 100 karakter.',
            'status_pelapor.required' => 'Status pelapor wajib dipilih.',
            'status_pelapor.in'       => 'Status pelapor harus siswa atau orang tua.',
            'kategori.required'       => 'Kategori wajib diisi.',
            'kategori.min'            => 'Kategori minimal 3 karakter.',
            'kategori.max'            => 'Kategori maksimal 100 karakter.',
            'deskripsi.required'      => 'Deskripsi wajib diisi.',
            'deskripsi.min'           => 'Deskripsi minimal 10 karakter.',
            'deskripsi.max'           => 'Deskripsi maksimal 2000 karakter.',
            'nomor_wa.required'       => 'Nomor WhatsApp wajib diisi.',
            'nomor_wa.regex'          => 'Nomor WhatsApp tidak valid. Format harus diawali dengan 08 atau 628 dan memiliki panjang 10-16 digit.',
            'nomor_wa.min'            => 'Nomor WhatsApp minimal 10 digit.',
            'nomor_wa.max'            => 'Nomor WhatsApp maksimal 16 digit.',
        ];
    }
}
