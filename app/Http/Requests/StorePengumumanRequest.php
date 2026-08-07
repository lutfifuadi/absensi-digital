<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengumumanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul'           => 'required|string|max:255',
            'konten'          => 'required|string',
            'kategori'        => 'required|string|in:informasi,penting,kegiatan,mendesak,libur',
            'target'          => 'required|string|in:semua,guru,siswa,orang_tua,staff,kelas',
            'target_kelas_id' => 'nullable|required_if:target,kelas|exists:kelas,id',
            'lampiran'        => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'is_pinned'       => 'nullable|boolean',
            'is_popup'        => 'nullable|boolean',
            'force_read'      => 'nullable|boolean',
            'is_aktif'        => 'nullable|boolean',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required'           => 'Judul pengumuman wajib diisi.',
            'konten.required'          => 'Isi pengumuman wajib diisi.',
            'target_kelas_id.required_if' => 'Kelas target wajib dipilih jika sasaran pengumuman adalah spesifik kelas.',
            'lampiran.max'             => 'Ukuran file lampiran maksimal 10 MB.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ];
    }
}
