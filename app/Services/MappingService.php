<?php

namespace App\Services;

class MappingService
{
    /**
     * Tipe entitas yang aktif ('siswa' atau 'guru').
     */
    protected string $type;

    /**
     * Daftar kolom database yang didukung saat ini (berdasarkan $type).
     */
    protected array $supportedColumns = [];

    /**
     * Daftar kolom database yang didukung untuk siswa.
     */
    protected array $supportedColumnsSiswa = [
        'nis' => ['nis', 'nipd', 'no induk', 'nomor induk', 'no.induk', 'nomorinduk', 'nis siswa', 'no induk siswa', 'nomor induk siswa'],
        'nama_lengkap' => ['nama', 'nama lengkap', 'nama siswa', 'namalengkap', 'nama_lengkap', 'fullname', 'full name', 'nama_siswa'],
        'nisn' => ['nisn', 'no nisn', 'nomor nisn', 'nisn siswa', 'no induk nasional'],
        'tempat_lahir' => ['tempat lahir', 'tempatlahir', 'tmp lahir', 'tpl', 'birthplace', 'birth place', 'tempat_lahir'],
        'tanggal_lahir' => ['tanggal lahir', 'tgl lahir', 'tanggallahir', 'tgllahir', 'birthdate', 'birth date', 'tanggal_lahir', 'ttl'],
        'jenis_kelamin' => ['jenis kelamin', 'jk', 'kelamin', 'gender', 'jenis_kelamin', 'jeniskelamin', 'l/p'],
        'alamat' => ['alamat', 'address', 'alamat rumah', 'alamatrumah', 'alamat_rumah'],
        'no_hp' => ['no telp', 'no_telp', 'telp', 'telepon', 'phone', 'no hp', 'nohp', 'nomor hp', 'nomor telepon', 'nomorhp', 'no. telp', 'no_hp', 'no telepon'],
        'no_hp_ortu' => ['no hp ortu', 'no_hp_ortu', 'nohportu', 'nomor hp ortu', 'no hp orang tua', 'no_hp_orangtua', 'no hp wali', 'ortu no hp', 'telepon ortu', 'telp ortu'],
        'email' => ['email', 'e-mail', 'alamat email', 'email address'],
        'kelas_nama' => ['kelas', 'nama kelas', 'kelas_nama', 'kelasnama', 'class', 'class name', 'kelas saat ini'],
        'tahun_akademik_nama' => ['tahun akademik', 'tahunakademik', 'tahun_akademik_nama', 'ta', 'academic year', 'tahun_ajaran', 'tahunajaran', 'tahun_akademik', 'tahun ajaran'],
    ];

    /**
     * Daftar kolom database yang didukung untuk guru.
     */
    protected array $supportedColumnsGuru = [
        'nip' => ['nip', 'no induk', 'nomor induk', 'no.induk', 'nomorinduk', 'nip guru', 'no induk guru', 'nomor induk guru'],
        'nama_lengkap' => ['nama', 'nama lengkap', 'nama guru', 'namalengkap', 'nama_lengkap', 'fullname', 'full name', 'nama_guru'],
        'username' => ['username', 'id', 'user name', 'nama pengguna'],
        'email' => ['email', 'e-mail', 'alamat email', 'email address', 'surel'],
        'jenis_kelamin' => ['jenis kelamin', 'jk', 'kelamin', 'gender', 'jenis_kelamin', 'jeniskelamin', 'l/p'],
        'mata_pelajaran' => ['mapel', 'mata pelajaran', 'subjek', 'subject', 'mata_pelajaran', 'matapelajaran'],
        'jabatan' => ['jabatan', 'posisi', 'role', 'position'],
        'no_hp' => ['no telp', 'no_telp', 'telp', 'telepon', 'phone', 'no hp', 'nohp', 'nomor hp', 'nomor telepon', 'nomorhp', 'no. telp', 'no_hp', 'no telepon'],
        'status' => ['status', 'keaktifan', 'status keaktifan', 'status guru'],
    ];

    /**
     * Constructor untuk inisialisasi tipe entitas.
     */
    public function __construct(string $type = 'siswa')
    {
        $this->type = $type;
        $this->supportedColumns = $type === 'guru' ? $this->supportedColumnsGuru : $this->supportedColumnsSiswa;
    }

    /**
     * Dapatkan daftar kolom yang didukung (static).
     */
    public static function getSupportedColumns(?string $type = 'siswa'): array
    {
        return (new self($type ?? 'siswa'))->supportedColumns;
    }

    /**
     * Auto-detect mapping dari header sheet ke kolom database.
     *
     * @param  array  $headers  Daftar header dari baris pertama sheet
     * @param  array|null  $manualMapping  Mapping manual dari admin (opsional, override auto-detect)
     * @return array ['mapping' => ['header1' => 'kolom_db', ...], 'unrecognized' => ['header3', ...], 'total_headers' => int, 'matched' => int]
     */
    public function detectMapping(array $headers, ?array $manualMapping = []): array
    {
        $mapping = [];
        $unrecognized = [];

        foreach ($headers as $header) {
            $normalized = $this->normalize($header);
            $matched = false;

            foreach ($this->supportedColumns as $column => $variations) {
                foreach ($variations as $variation) {
                    if ($normalized === $this->normalize($variation)) {
                        $mapping[$header] = $column;
                        $matched = true;
                        break 2;
                    }
                }
            }

            if (! $matched) {
                $unrecognized[] = $header;
            }
        }

        // Override dengan manual mapping jika ada
        if (! empty($manualMapping)) {
            foreach ($manualMapping as $field => $column) {
                // Cari header yang cocok dengan nilai manual mapping
                $found = false;
                foreach ($headers as $header) {
                    if ($this->normalize($header) === $this->normalize($column)) {
                        $mapping[$header] = $field;
                        $found = true;
                        break;
                    }
                }

                if (! $found) {
                    // Jika header tidak ditemukan, tambahkan sebagai unrecognized
                    // Tapi jangan duplikasi
                    $unrecognized[] = $column;
                }
            }
        }

        return [
            'mapping' => $mapping,
            'unrecognized' => array_unique($unrecognized),
            'total_headers' => count($headers),
            'matched' => count($mapping),
        ];
    }

    /**
     * Gabungkan auto-detect dengan manual mapping.
     * Manual mapping override untuk field yang sama.
     *
     * @param  array  $headers  Daftar header dari sheet
     * @param  array  $manualMapping  Mapping manual dalam format ['kolom_db' => 'header_sheet']
     * @return array ['mapping' => ['header' => 'kolom_db', ...], 'unrecognized' => [...]]
     */
    public function mergeMapping(array $headers, array $manualMapping): array
    {
        $autoResult = $this->detectMapping($headers);
        $finalMapping = $autoResult['mapping'];

        // Manual mapping dalam format ['nis' => 'NIS', 'nama' => 'Nama Lengkap']
        // Cari header yang sesuai di sheet untuk setiap field manual
        foreach ($manualMapping as $field => $headerSource) {
            $normalizedManual = $this->normalize($headerSource);
            $found = false;

            foreach ($headers as $header) {
                if ($this->normalize($header) === $normalizedManual) {
                    // Hapus mapping lama untuk field ini jika ada
                    foreach ($finalMapping as $h => $f) {
                        if ($f === $field) {
                            unset($finalMapping[$h]);
                            break;
                        }
                    }
                    $finalMapping[$header] = $field;
                    $found = true;
                    break;
                }
            }

            if (! $found && ! in_array($headerSource, $autoResult['unrecognized'])) {
                $autoResult['unrecognized'][] = $headerSource;
            }
        }

        // Re-index unrecognized untuk menghapus yang sudah termapping manual
        $unrecognized = [];
        foreach ($autoResult['unrecognized'] as $u) {
            $isMapped = false;
            foreach ($finalMapping as $mappedHeader => $field) {
                if ($u === $mappedHeader) {
                    $isMapped = true;
                    break;
                }
            }
            if (! $isMapped) {
                $unrecognized[] = $u;
            }
        }

        return [
            'mapping' => $finalMapping,
            'unrecognized' => $unrecognized,
            'total_headers' => count($headers),
            'matched' => count($finalMapping),
        ];
    }

    /**
     * Normalisasi string untuk perbandingan.
     * - Trim whitespace
     * - Lowercase
     * - Ganti underscore, dash, titik, spasi ganda dengan spasi tunggal
     * - Hapus karakter non-alfanumerik (kecuali spasi)
     */
    private function normalize(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace(['_', '-', '.', '  '], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = preg_replace('/[^a-z0-9\s]/', '', $value);

        return trim($value);
    }
}
