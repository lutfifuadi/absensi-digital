<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreAbsensiPerJamRequest — validasi simpan absensi siswa per jam (PRD-006, F-1/F-4).
 *
 * Otorisasi dilakukan di Controller via Policy (Gate::authorize('isi', ...)),
 * sehingga authorize() di sini selalu true.
 */
class StoreAbsensiPerJamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jadwal_pelajaran_id' => ['required', 'exists:jadwal_pelajaran,id'],
            'tanggal'             => ['required', 'date', 'after_or_equal:today'],
            'rows'                => ['required', 'array', 'min:1'],
            'rows.*.siswa_id'     => ['required', 'exists:siswa,id'],
            'rows.*.status'       => ['required', Rule::in(['hadir', 'terlambat', 'sakit', 'izin', 'alpha', 'dispen'])],
            // BR-07: status terlambat wajib mengisi lama_terlambat (menit > 0)
            'rows.*.lama_terlambat' => ['required_if:rows.*.status,terlambat', 'nullable', 'integer', 'min:1'],
            'rows.*.keterangan'     => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'jadwal_pelajaran_id.required' => 'Jadwal pelajaran wajib diisi.',
            'jadwal_pelajaran_id.exists'   => 'Jadwal pelajaran tidak ditemukan.',
            'tanggal.required'             => 'Tanggal wajib diisi.',
            'tanggal.after_or_equal'       => 'Tanggal tidak boleh sebelum hari ini.',
            'rows.required'                => 'Data absensi siswa tidak boleh kosong.',
            'rows.min'                     => 'Minimal 1 baris absensi harus diisi.',
            'rows.*.siswa_id.required'     => 'Siswa wajib dipilih.',
            'rows.*.siswa_id.exists'       => 'Siswa tidak ditemukan.',
            'rows.*.status.required'       => 'Status kehadiran wajib diisi untuk setiap siswa.',
            'rows.*.status.in'             => 'Status kehadiran tidak valid.',
            'rows.*.lama_terlambat.required_if' => 'Lama keterlambatan (menit) wajib diisi untuk status terlambat.',
            'rows.*.lama_terlambat.min'    => 'Lama keterlambatan minimal 1 menit.',
            'rows.*.keterangan.max'        => 'Keterangan maksimal 500 karakter.',
        ];
    }
}
