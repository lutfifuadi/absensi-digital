<?php
/*
 * Created At: 2026-05-09
 * Description: Import class for Guru data
 */

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use App\Models\Pengaturan;
use App\Support\QrCodeGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuruImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    use Importable;

    protected $domainEmail;

    public function __construct()
    {
        $this->domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
    }

    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            $nip = trim($row['nip']);
            $nama = trim($row['nama_lengkap']);
            
            // Cek apakah user sudah ada berdasarkan username (nip)
            $user = User::where('username', $nip)->first();
            
            if (!$user) {
                $email = strtolower($nip) . '@' . $this->domainEmail;
                $user = User::create([
                    'name' => $nama,
                    'username' => $nip,
                    'email' => $email,
                    'password' => Hash::make($nip), // Default password is NIP
                    'role' => User::ROLE_GURU,
                ]);
            }

            // Update or Create Guru profile
            return Guru::updateOrCreate(
                ['nip' => $nip],
                [
                    'user_id' => $user->id,
                    'nama_lengkap' => $nama,
                    'jenis_kelamin' => trim($row['jenis_kelamin']),
                    'mata_pelajaran' => trim($row['mata_pelajaran']),
                    'jabatan' => trim($row['jabatan'] ?? ''),
                    'no_hp' => trim($row['no_hp'] ?? ''),
                    'status' => strtolower(trim($row['status'])) == 'aktif' ? 'aktif' : 'nonaktif',
                    'qr_code' => QrCodeGenerator::generate('GURU'),
                ]
            );
        });
    }

    public function rules(): array
    {
        return [
            'nip' => ['required', 'string', 'max:50'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P', 'l', 'p'])],
            'mata_pelajaran' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif', 'Aktif', 'Nonaktif'])],
        ];
    }

    public function prepareForValidation($data, $index)
    {
        // Ensure nip is string
        if (isset($data['nip'])) {
            $data['nip'] = (string) $data['nip'];
        }

        // Normalize gender and status for validation
        if (isset($data['jenis_kelamin'])) {
            $data['jenis_kelamin'] = strtoupper((string) $data['jenis_kelamin']);
        }
        if (isset($data['status'])) {
            $data['status'] = strtolower((string) $data['status']);
        }
        return $data;
    }
}
