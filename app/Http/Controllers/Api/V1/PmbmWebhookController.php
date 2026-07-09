<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\TahunAkademik;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PmbmWebhookController extends BaseSyncController
{
    public function __construct(protected SyncService $syncService) {}

    /**
     * Menerima data siswa dari sistem PMBM (mode Push).
     * PMBM mengirim payload ini saat siswa melakukan verifikasi kehadiran / check-in.
     *
     * Header yang diperlukan: X-API-KEY
     */
    public function receive(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nisn'                 => 'required|string|max:20',
            'nama_lengkap'         => 'required|string|max:255',
            'email'                => 'required|email|max:255',
            'jenis_kelamin'        => 'nullable|string|in:L,P',
            'tempat_lahir'         => 'nullable|string|max:100',
            'tanggal_lahir'        => 'nullable|string|max:2',
            'bulan_lahir'          => 'nullable|string|max:2',
            'tahun_lahir'          => 'nullable|string|max:4',
            'nomor_hp'             => 'nullable|string|max:20',
            'nomor_hp_ayah'        => 'nullable|string|max:20',
            'nomor_hp_ibu'         => 'nullable|string|max:20',
            'nomor_hp_wali'        => 'nullable|string|max:20',
            'status'               => 'nullable|string|in:aktif,lulus,lulus_cadangan,pindah,keluar',
            'daftar_ulang_selesai' => 'nullable|boolean',
            'wawancara_selesai'    => 'required|boolean',
            'nomor_pendaftaran'    => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal.', $validator->errors()->toArray(), 422);
        }

        $data = $validator->validated();

        // Syarat sinkronisasi ke sistem absensi: status=lulus/lulus_cadangan DAN daftar_ulang_selesai=true DAN wawancara_selesai=true
        $statusPmbm         = strtolower(trim($data['status'] ?? ''));
        $daftarUlangSelesai = (bool) ($data['daftar_ulang_selesai'] ?? false);
        $wawancaraSelesai   = (bool) ($data['wawancara_selesai'] ?? false);

        if (!in_array($statusPmbm, ['lulus', 'lulus_cadangan']) || !$daftarUlangSelesai || !$wawancaraSelesai) {
            Log::info('PMBM Webhook: Data siswa dilewati — belum memenuhi syarat sinkronisasi.', [
                'nisn'                 => $data['nisn'],
                'status'               => $statusPmbm,
                'daftar_ulang_selesai' => $daftarUlangSelesai,
                'wawancara_selesai'    => $wawancaraSelesai,
            ]);
            return $this->sendResponse(
                ['nisn' => $data['nisn']],
                'Data siswa tidak memenuhi syarat sinkronisasi ke absensi.',
                200
            );
        }

        // Gabungkan tanggal lahir menjadi format Y-m-d jika komponen tersedia
        $tanggalLahir = null;
        if (!empty($data['tahun_lahir']) && !empty($data['bulan_lahir']) && !empty($data['tanggal_lahir'])) {
            $tanggalLahir = sprintf(
                '%s-%02d-%02d',
                $data['tahun_lahir'],
                (int) $data['bulan_lahir'],
                (int) $data['tanggal_lahir']
            );
        }

        // Email: generate dari NISN + domain lembaga
        $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'sekolah.sch.id';
        $emailSiswa  = strtolower($data['nisn']) . '@' . $domainEmail;

        // Kelas X default dari TA aktif
        $tahunAkademikDefault = TahunAkademik::where('is_aktif', true)->value('id');
        $kelasXDefault = Kelas::where('tingkat', 'X')
            ->when($tahunAkademikDefault, fn ($q) => $q->where('tahun_akademik_id', $tahunAkademikDefault))
            ->orderBy('nama')
            ->value('nama');

        // Normalisasi payload ke format yang dipahami SyncService
        // lulus/lulus_cadangan dari PMBM = siswa baru masuk → aktif di sistem absensi
        $payload = [
            'nisn'          => $data['nisn'],
            'username'      => $data['nisn'],
            'nama_lengkap'  => $data['nama_lengkap'],
            'email'         => $emailSiswa,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? 'L',
            'tempat_lahir'  => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => $tanggalLahir,
            'no_hp'         => $data['nomor_hp'] ?? null,
            'no_hp_ortu'    => $data['nomor_hp_ayah'] ?? $data['nomor_hp_ibu'] ?? $data['nomor_hp_wali'] ?? null,
            'kelas_nama'    => $kelasXDefault,
            'tahun_akademik_id_default' => $tahunAkademikDefault,
            'qr_code'       => $data['qr_code'] ?? $data['nisn'] ?? null,
            'status'        => 'aktif',
        ];

        try {
            $siswa = $this->syncService->syncSiswa($payload);

            Log::info('PMBM Webhook: Siswa berhasil disinkronisasi.', [
                'nisn' => $data['nisn'],
                'id'   => $siswa->id,
            ]);

            return $this->sendResponse(
                ['id' => $siswa->id, 'nisn' => $siswa->nisn],
                'Data siswa berhasil diterima dan disinkronisasi.',
                200
            );
        } catch (\Exception $e) {
            Log::error('PMBM Webhook: Gagal sinkronisasi siswa.', [
                'nisn'  => $data['nisn'],
                'error' => $e->getMessage(),
            ]);

            return $this->sendError('Gagal memproses data siswa.', ['error' => $e->getMessage()], 500);
        }
    }
}
