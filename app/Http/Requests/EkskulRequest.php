<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EkskulRequest extends FormRequest
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
        $ekskulId = $this->route('ekskul');

        return [
            'nama'      => 'required|string|max:255',
            'kategori'  => 'required|in:wajib,pilihan,olahraga,seni,akademik,lainnya',
            'deskripsi' => 'nullable|string|max:2000',
            'kuota'     => 'nullable|integer|min:1',
            'status'    => 'nullable|boolean',
            'icon'      => 'nullable|string|max:100',

            // Jadwal (array optional)
            'jadwal'              => 'nullable|array',
            'jadwal.*.hari'       => 'required_with:jadwal|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jadwal.*.jam_mulai'  => 'required_with:jadwal|date_format:H:i',
            'jadwal.*.jam_selesai' => 'required_with:jadwal|date_format:H:i|after:jadwal.*.jam_mulai',
            'jadwal.*.lokasi'     => 'required_with:jadwal|string|max:255',

            // Pembina (array optional)
            'pembina'             => 'nullable|array',
            'pembina.*.guru_id'   => 'required_with:pembina|exists:guru,id',
            'pembina.*.jabatan'   => 'nullable|string|max:100',
        ];
    }

    /**
     * Custom messages for validation.
     */
    public function messages(): array
    {
        return [
            'nama.required'           => 'Nama ekskul wajib diisi.',
            'nama.max'                => 'Nama ekskul maksimal 255 karakter.',
            'kategori.required'       => 'Kategori ekskul wajib dipilih.',
            'kategori.in'             => 'Kategori ekskul tidak valid.',
            'kuota.integer'           => 'Kuota harus berupa angka.',
            'kuota.min'               => 'Kuota minimal 1.',
            'jadwal.*.hari.required_with'       => 'Hari jadwal wajib diisi.',
            'jadwal.*.hari.in'                  => 'Hari jadwal tidak valid.',
            'jadwal.*.jam_mulai.required_with'  => 'Jam mulai wajib diisi.',
            'jadwal.*.jam_selesai.required_with'=> 'Jam selesai wajib diisi.',
            'jadwal.*.jam_selesai.after'        => 'Jam selesai harus setelah jam mulai.',
            'jadwal.*.lokasi.required_with'     => 'Lokasi jadwal wajib diisi.',
            'pembina.*.guru_id.required_with'   => 'Guru pembina wajib dipilih.',
            'pembina.*.guru_id.exists'          => 'Guru pembina tidak ditemukan.',
        ];
    }
}
