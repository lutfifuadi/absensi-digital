<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    use Importable;

    public function model(array $row)
    {
        $kelas = Kelas::where('nama', trim($row['kelas']))->first();
        $tahunAkademik = TahunAkademik::where('nama', trim($row['tahun_akademik']))->first();

        $tanggalLahir = $row['tanggal_lahir'];
        if (is_numeric($tanggalLahir)) {
            $tanggalLahir = Date::excelToDateTimeObject($tanggalLahir)->format('Y-m-d');
        } else {
            $tanggalLahir = Carbon::parse($tanggalLahir)->format('Y-m-d');
        }

        return new Siswa([
            'nis' => trim($row['nis']),
            'nisn' => trim($row['nisn']),
            'nama_lengkap' => trim($row['nama_lengkap']),
            'jenis_kelamin' => trim($row['jenis_kelamin']),
            'tempat_lahir' => trim($row['tempat_lahir']),
            'tanggal_lahir' => $tanggalLahir,
            'alamat' => trim($row['alamat'] ?? ''),
            'no_hp' => trim($row['no_hp'] ?? ''),
            'no_hp_ortu' => trim($row['no_hp_ortu'] ?? ''),
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $tahunAkademik->id,
            'status' => trim($row['status']),
            'qr_code' => trim($row['nisn']),
        ]);
    }

    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:50', 'unique:siswa,nis'],
            'nisn' => ['required', 'string', 'max:50', 'unique:siswa,nisn'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'kelas' => ['required', 'string', 'exists:kelas,nama'],
            'tahun_akademik' => ['required', 'string', 'exists:tahun_akademik,nama'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif', 'alumni'])],
            'no_hp_ortu' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'kelas.exists' => 'Kelas tidak ditemukan. Pastikan nama kelas sesuai dengan data di master kelas.',
            'tahun_akademik.exists' => 'Tahun akademik tidak ditemukan. Pastikan nama tahun akademik sesuai.',
        ];
    }
}
