<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengaduanRequest;
use App\Models\Pengaduan;
use App\Models\LogPengaduan;
use App\Services\WhatsAppValidatorService;
use App\Jobs\SendPengaduanKodeUnikJob;
use App\Jobs\SendPengaduanGroupNotifJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengaduanController extends Controller
{
    protected WhatsAppValidatorService $whatsappValidator;

    public function __construct(WhatsAppValidatorService $whatsappValidator)
    {
        $this->whatsappValidator = $whatsappValidator;
    }

    /**
     * Tampilkan form pengaduan (web view).
     */
    public function form()
    {
        return view('pengaduan.form');
    }

    /**
     * Tampilkan form cek status pengaduan (web view).
     */
    public function cekForm()
    {
        return view('pengaduan.cek');
    }

    /**
     * POST /api/pengaduan — Submit pengaduan baru.
     */
    public function submit(StorePengaduanRequest $request)
    {
        $validated = $request->validated();

        // Validasi nomor WA terdaftar
        if (!$this->whatsappValidator->validateNomor($validated['nomor_wa'])) {
            return response()->json([
                'message' => 'Nomor WhatsApp tidak valid atau tidak terdaftar.',
                'errors'  => [
                    'nomor_wa' => ['Nomor WhatsApp tidak valid atau tidak terdaftar.'],
                ],
            ], 422);
        }

        try {
            $pengaduan = DB::transaction(function () use ($validated) {
                // Generate kode unik: PGN-YYYYMMDD-NNN
                $datePrefix = now()->format('Ymd');
                $lastToday = Pengaduan::whereDate('created_at', today())
                    ->where('kode_unik', 'like', "PGN-{$datePrefix}-%")
                    ->orderBy('kode_unik', 'desc')
                    ->first();

                if ($lastToday) {
                    $lastNumber = (int) substr($lastToday->kode_unik, -3);
                    $newNumber = $lastNumber + 1;
                } else {
                    $newNumber = 1;
                }

                $kodeUnik = sprintf("PGN-{$datePrefix}-%03d", $newNumber);

                // Simpan pengaduan
                $pengaduan = Pengaduan::create([
                    'kode_unik'      => $kodeUnik,
                    'nama_lengkap'   => $validated['nama_lengkap'],
                    'status_pelapor' => $validated['status_pelapor'],
                    'kategori'       => $validated['kategori'],
                    'deskripsi'      => $validated['deskripsi'],
                    'nomor_wa'       => $validated['nomor_wa'],
                    'status'         => 'baru',
                ]);

                // Buat log awal
                LogPengaduan::create([
                    'pengaduan_id' => $pengaduan->id,
                    'status_dari'  => null,
                    'status_ke'    => 'baru',
                    'catatan'      => 'Pengaduan baru dibuat.',
                    'diubah_oleh'  => 'sistem',
                ]);

                return $pengaduan;
            });

            // Dispatch job kirim WA kode unik ke pelapor
            SendPengaduanKodeUnikJob::dispatch(
                $pengaduan->nomor_wa,
                $pengaduan->kode_unik,
                $pengaduan->nama_lengkap
            );

            // Dispatch job kirim notifikasi ke grup admin
            SendPengaduanGroupNotifJob::dispatch(
                $pengaduan->kode_unik,
                $pengaduan->nama_lengkap,
                $pengaduan->status_pelapor,
                $pengaduan->kategori,
                $pengaduan->deskripsi
            );

            return response()->json([
                'message'   => 'Pengaduan berhasil dikirim. Silakan catat kode unik untuk mengecek status.',
                'kode_unik' => $pengaduan->kode_unik,
                'data'      => $pengaduan->only(['kode_unik', 'nama_lengkap', 'status', 'created_at']),
            ], 201);

        } catch (\Exception $e) {
            Log::error('PengaduanController@submit Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan pengaduan. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * GET /api/pengaduan/cek?kode=xxx — Cek status pengaduan berdasarkan kode unik.
     */
    public function cekStatus(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:30',
        ]);

        $pengaduan = Pengaduan::with('logs')
            ->where('kode_unik', $request->kode)
            ->first();

        if (!$pengaduan) {
            return response()->json([
                'message' => 'Pengaduan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'kode_unik'      => $pengaduan->kode_unik,
                'nama_lengkap'   => $pengaduan->nama_lengkap,
                'status_pelapor' => $pengaduan->status_pelapor,
                'kategori'       => $pengaduan->kategori,
                'deskripsi'      => $pengaduan->deskripsi,
                'status'         => $pengaduan->status,
                'status_label'   => $pengaduan->status_label,
                'status_color'   => $pengaduan->status_color,
                'catatan_admin'  => $pengaduan->catatan_admin,
                'created_at'     => $pengaduan->created_at,
                'updated_at'     => $pengaduan->updated_at,
            ],
            'logs' => $pengaduan->logs->map(function ($log) {
                return [
                    'status_dari' => $log->status_dari,
                    'status_ke'   => $log->status_ke,
                    'catatan'     => $log->catatan,
                    'diubah_oleh' => $log->diubah_oleh,
                    'created_at'  => $log->created_at,
                ];
            }),
        ]);
    }

    /**
     * GET /api/pengaduan/cek-wa — Cek validasi nomor WhatsApp secara real-time.
     */
    public function cekWa(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nomor_wa' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/'],
        ], [
            'nomor_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'nomor_wa.string'   => 'Nomor WhatsApp harus berupa string.',
            'nomor_wa.regex'    => 'Nomor WhatsApp tidak valid. Format harus diawali dengan 08 dan memiliki panjang 10-15 digit.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid'   => false,
                'message' => $validator->errors()->first('nomor_wa'),
            ], 422);
        }

        try {
            $nomor_wa = $request->query('nomor_wa');
            $isValid = $this->whatsappValidator->validateNomor($nomor_wa);

            if ($isValid) {
                return response()->json([
                    'valid'   => true,
                    'message' => 'Nomor WhatsApp valid dan aktif.',
                ], 200);
            }

            return response()->json([
                'valid'   => false,
                'message' => 'Nomor WhatsApp tidak terdaftar atau tidak aktif.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('PengaduanController@cekWa Error: ' . $e->getMessage());

            return response()->json([
                'valid'   => false,
                'message' => 'Terjadi kesalahan saat memeriksa nomor WhatsApp. Silakan coba lagi.',
            ], 500);
        }
    }
}
