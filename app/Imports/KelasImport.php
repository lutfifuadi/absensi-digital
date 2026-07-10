<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KelasImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    use Importable;

    public function model(array $row)
    {
        $tahunAkademik = TahunAkademik::where('nama', trim($row['tahun_akademik']))->first();
        $waliKelas = null;
        
        if (!empty($row['wali_kelas'])) {
            $waliKelas = Guru::where('nama_lengkap', 'like', '%' . trim($row['wali_kelas']) . '%')->first();
        }

        return new Kelas([
            'nama' => trim($row['nama']),
            'tingkat' => strtoupper(trim($row['tingkat'])),
            'jurusan' => trim($row['jurusan']),
            'wali_kelas_id' => $waliKelas ? $waliKelas->id : null,
            'tahun_akademik_id' => $tahunAkademik->id,
            'is_aktif_absensi' => true,
            'kustomisasi_jam' => false,
        ]);
    }

    public function rules(): array
    {
        $tingkatOptions = \App\Helpers\JenjangHelper::getTingkatOptions();
        $tingkatValid = array_merge(
            array_map('strtoupper', $tingkatOptions),
            array_map('strtolower', $tingkatOptions)
        );

        return [
            'nama' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', Rule::in($tingkatValid)],
            'jurusan' => ['required', 'string', 'max:255'],
            'tahun_akademik' => ['required', 'string', 'exists:tahun_akademik,nama'],
        ];
    }

    public function customValidationMessages(): array
    {
        $tingkatOptions = implode(', ', \App\Helpers\JenjangHelper::getTingkatOptions());
        return [
            'tahun_akademik.exists' => 'Tahun akademik tidak ditemukan. Pastikan nama sesuai dengan master data.',
            'tingkat.in' => 'Tingkat harus salah satu dari: ' . $tingkatOptions . '.',
        ];
    }
}
