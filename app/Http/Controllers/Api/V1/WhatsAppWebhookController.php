<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengaturan;
use App\Models\WaAutoreplyKeyword;
use App\Models\NotificationTemplate;
use App\Models\Siswa;
use App\Models\AbsensiSiswa;
use App\Jobs\SendWhatsAppMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\WaAutoreplyLog;

class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            // 1. Validasi Token
            $tokenParam = $request->query('token');
            $dbToken = Pengaturan::where('key', 'wa_autoreply_webhook_token')->value('value');

            if (!$dbToken || $tokenParam !== $dbToken) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token webhook tidak valid.'
                ], 403);
            }

            // 2. Validasi Global Status
            $autoreplyEnabled = Pengaturan::where('key', 'wa_autoreply_enabled')->value('value');
            if ($autoreplyEnabled !== 'Ya') {
                return response()->json([
                    'status' => false,
                    'message' => 'Autoreply dinonaktifkan.'
                ], 200);
            }

            // 3. Ambil Payload dari Fonnte / MPWA
            $sender = $request->input('from') ?: $request->input('sender');
            $messageRaw = $request->input('message');

            if (empty($sender) || empty($messageRaw)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payload tidak lengkap.'
                ], 200);
            }

            // Log awal: pesan diterima
            Log::info('WhatsApp Autoreply: Pesan diterima', [
                'sender' => $sender,
                'message' => $messageRaw,
                'ip' => $request->ip()
            ]);

            // 4. Pencegahan Infinite Loop
            $botSender = Pengaturan::where('key', 'wa_autoreply_sender')->value('value');
            $notifSender = Pengaturan::where('key', 'wa_nomor_notifikasi')->value('value');

            // Membersihkan karakter non-digit agar perbandingan nomor handphone akurat
            $cleanSender = preg_replace('/\D/', '', $sender);
            $cleanBotSender = $botSender ? preg_replace('/\D/', '', $botSender) : null;
            $cleanNotifSender = $notifSender ? preg_replace('/\D/', '', $notifSender) : null;

            if (($cleanBotSender && $cleanSender === $cleanBotSender) || ($cleanNotifSender && $cleanSender === $cleanNotifSender)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pesan berasal dari nomor bot sendiri, diabaikan untuk mencegah infinite loop.'
                ], 200);
            }

            // 5. Sanitasi Pesan
            $message = strtolower(trim($messageRaw));

            // 6. Matching Keyword
            $matchedKeyword = null;

            // Cari yang exact match dulu
            $matchedKeyword = WaAutoreplyKeyword::where('is_active', true)
                ->where('match_type', 'exact')
                ->whereRaw('LOWER(keyword) = ?', [$message])
                ->first();

            // Jika tidak ada, cari yang contains match
            if (!$matchedKeyword) {
                // Cari keyword yang terkandung di dalam pesan
                // Untuk SQLite / MySQL cross-compatibility, kita bisa pakai operator LIKE dengan bind parameters atau concating
                // Kita gunakan binding dengan string literal manual agar kompatibel dengan SQLite dan MySQL
                $matchedKeyword = WaAutoreplyKeyword::where('is_active', true)
                    ->where('match_type', 'contains')
                    ->where(function ($query) use ($message) {
                        // Ambil semua keyword contains, lalu kita filter di PHP atau pakai query compatible.
                        // Karena data keyword sedikit, mengambil semua active contains keyword lalu mencocokkannya di PHP adalah cara paling aman & compatible.
                    })
                    ->get()
                    ->first(function ($item) use ($message) {
                        return str_contains($message, strtolower($item->keyword));
                    });
            }

            // 7. Jika tidak ada keyword yang cocok, set default menggunakan keyword/template bantuan
            $templateType = null;
            $isValidationRequired = false;

            if ($matchedKeyword) {
                $templateType = $matchedKeyword->notification_template_type;
                $isValidationRequired = (bool) $matchedKeyword->is_validation_required;
            } else {
                // Default ke template bantuan / keyword bantuan
                $templateType = 'autoreply_bantuan';
                $isValidationRequired = false;
            }

            // Log: keyword match
            Log::info('WhatsApp Autoreply: Keyword match', [
                'sender' => $sender,
                'message' => $message,
                'keyword' => $matchedKeyword?->keyword ?? 'default_bantuan',
                'match_type' => $matchedKeyword?->match_type ?? 'default',
                'template_type' => $templateType,
                'is_validation_required' => $isValidationRequired,
            ]);

            // Dapatkan content template
            $templateContent = NotificationTemplate::where('type', $templateType)->value('content');
            if (empty($templateContent)) {
                // Jika template tidak ketemu di DB, coba cari default dari model/konstanta atau string default
                $templateContent = "Halo, ketik menu bantuan untuk mengetahui daftar keyword layanan kami.";
            }

            // Ambil sender dan api_key dari konfigurasi notifikasi
            $customSender = Pengaturan::where('key', 'wa_nomor_notifikasi')->value('value');
            $customApiKey = Pengaturan::where('key', 'wa_api_key')->value('value');

            // 8. Proses Keyword & Autentikasi
            if (!$isValidationRequired) {
                // Langsung render template statis/global (tidak membutuhkan data siswa)
                $renderedMessage = $this->renderStaticMessage($templateContent);

                // Kirim lewat Job asinkron
                SendWhatsAppMessage::dispatch(
                    $sender,
                    $renderedMessage,
                    'Autoreply Layanan',
                    false, // Tidak perlu force validate cached number agar respon instan
                    0,
                    $customSender,
                    $customApiKey
                );

                // Log: static message sent
                $this->logAutoreply([
                    'sender' => $sender,
                    'message' => $messageRaw,
                    'keyword_used' => $matchedKeyword?->keyword,
                    'match_type' => $matchedKeyword?->match_type,
                    'template_type' => $templateType,
                    'student_found' => false,
                    'is_success' => true,
                    'response_sent' => true,
                    'ip_address' => $request->ip(),
                ]);
            } else {
                // Cari data siswa berdasarkan no_hp_ortu atau no_hp yang sama dengan pengirim
                // Kita cari dengan pembersihan karakter non-digit agar pencocokan no hp fleksibel
                // DB no_hp_ortu / no_hp bisa saja berformat +62, 08, atau 62.
                // Kita gunakan helper LIKE atau pencarian yang mencocokkan beberapa variasi format nomor hp
                $siswaList = Siswa::where(function ($query) use ($cleanSender) {
                    // Karena cleanSender hanya angka (misal 628123456789 atau 08123456789), kita cari yang mirip di DB
                    // Kita bisa bersihkan juga no hp di DB atau cari substring
                    $query->whereRaw("REPLACE(REPLACE(REPLACE(no_hp_ortu, '+', ''), '-', ''), ' ', '') LIKE ?", ['%' . substr($cleanSender, 2)])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(no_hp, '+', ''), '-', ''), ' ', '') LIKE ?", ['%' . substr($cleanSender, 2)]);
                })->get();

                if ($siswaList->isEmpty()) {
                    // Balas menggunakan template nomor tidak dikenal
                    $unrecognizedTemplate = NotificationTemplate::where('type', 'autoreply_nomor_tak_dikenal')->value('content')
                        ?: "Maaf, nomor handphone Anda belum terdaftar di sistem kami sebagai nomor siswa atau orang tua siswa. Silakan hubungi tata usaha sekolah.";
                    
                    $renderedMessage = $this->renderStaticMessage($unrecognizedTemplate);
                    
                    SendWhatsAppMessage::dispatch(
                        $sender,
                        $renderedMessage,
                        'Autoreply Layanan',
                        false,
                        0,
                        $customSender,
                        $customApiKey
                    );

                    // Log: student not found, unrecognized number template
                    $this->logAutoreply([
                        'sender' => $sender,
                        'message' => $messageRaw,
                        'keyword_used' => $matchedKeyword?->keyword,
                        'match_type' => $matchedKeyword?->match_type,
                        'template_type' => 'autoreply_nomor_tak_dikenal',
                        'student_found' => false,
                        'is_success' => true,
                        'response_sent' => true,
                        'ip_address' => $request->ip(),
                    ]);
                    Log::info('WhatsApp Autoreply: Nomor tidak dikenal, kirim template nomor_tak_dikenal', [
                        'sender' => $sender,
                    ]);
                } else {
                    // Loop untuk masing-masing siswa (karena 1 nomor ortu bisa memiliki multi-siswa)
                    foreach ($siswaList as $siswa) {
                        $renderedMessage = $this->renderDynamicMessage($templateContent, $siswa, $matchedKeyword ? $matchedKeyword->keyword : '');
                        
                        SendWhatsAppMessage::dispatch(
                            $sender,
                            $renderedMessage,
                            'Autoreply Layanan',
                            false,
                            $siswa->id,
                            $customSender,
                            $customApiKey
                        );
                    }

                    // Log: dynamic message sent to validated students
                    $studentNames = $siswaList->pluck('nama_lengkap')->toArray();
                    $this->logAutoreply([
                        'sender' => $sender,
                        'message' => $messageRaw,
                        'keyword_used' => $matchedKeyword?->keyword,
                        'match_type' => $matchedKeyword?->match_type,
                        'template_type' => $templateType,
                        'student_found' => true,
                        'student_details' => $studentNames,
                        'is_success' => true,
                        'response_sent' => true,
                        'ip_address' => $request->ip(),
                    ]);
                    Log::info('WhatsApp Autoreply: Pesan dinamis dikirim', [
                        'sender' => $sender,
                        'keyword' => $matchedKeyword?->keyword,
                        'template_type' => $templateType,
                        'students' => $studentNames,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Autoreply diproses sukses.'
            ], 200);

            } catch (\Exception $e) {
            Log::error('WhatsApp Autoreply Webhook Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            // Log ke database
            try {
                $this->logAutoreply([
                    'sender' => $request->input('sender') ?? 'unknown',
                    'message' => $request->input('message') ?? '',
                    'is_success' => false,
                    'error_message' => $e->getMessage(),
                    'response_sent' => false,
                    'ip_address' => $request->ip(),
                ]);
            } catch (\Exception $logErr) {
                // ignore log error
            }

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }

    /**
     * Catat log autoreply ke database
     */
    private function logAutoreply(array $data): void
    {
        try {
            WaAutoreplyLog::create($data);
            
            // Bersihkan log yang sudah lebih dari 24 jam (efisiensi tanpa cron)
            WaAutoreplyLog::where('created_at', '<', now()->subDay())->delete();
            
        } catch (\Exception $e) {
            Log::warning('Gagal menyimpan log autoreply: ' . $e->getMessage());
        }
    }

    /**
     * Render pesan dinamis dengan placeholders
     */
    private function renderDynamicMessage(string $template, Siswa $siswa, string $keyword): string
    {
        $today = Carbon::today();
        
        // Ambil data absensi hari ini
        $absensiHariIni = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $today)
            ->first();

        $tanggal = $today->translatedFormat('d F Y');
        $status = $absensiHariIni ? strtoupper($absensiHariIni->status) : 'BELUM ABSEN';

        // jam_masuk & jam_pulang format H:i jika ada, default -
        $jamMasuk = '-';
        if ($absensiHariIni && $absensiHariIni->jam_masuk) {
            try {
                $jamMasuk = Carbon::parse($absensiHariIni->jam_masuk)->format('H:i');
            } catch (\Exception $e) {
                $jamMasuk = date('H:i', strtotime($absensiHariIni->jam_masuk));
            }
        }

        $jamPulang = '-';
        if ($absensiHariIni && $absensiHariIni->jam_pulang) {
            try {
                $jamPulang = Carbon::parse($absensiHariIni->jam_pulang)->format('H:i');
            } catch (\Exception $e) {
                $jamPulang = date('H:i', strtotime($absensiHariIni->jam_pulang));
            }
        }

        // Untuk backwards compatibility, {waktu} mengambil jam masuk absensi hari ini jika ada, default -
        $waktu = $jamMasuk;

        // Ambil kelas
        $kelas = $siswa->kelas?->nama ?? '-';

        // Ambil lembaga
        $lembaga = Pengaturan::where('key', 'nama_lembaga')->value('value') ?: 'MAN 1 Kota Bandung';

        // Hitung rekap detail (6 hari terakhir Senin-Sabtu pada minggu berjalan)
        $rekap = $this->getRekapKehadiranDetail($siswa->id);

        // Ambil link portal
        $linkPortal = $this->getLinkPortal();
        $linkPengaduan = url('/pengaduan');

        // Ganti placeholders
        $replacements = [
            '{nama}' => $siswa->nama_lengkap,
            '{kelas}' => $kelas,
            '{tanggal}' => $tanggal,
            '{waktu}' => $waktu,
            '{status}' => $status,
            '{jam_masuk}' => $jamMasuk,
            '{jam_pulang}' => $jamPulang,
            '{lembaga}' => $lembaga,
            '{rekap_detail}' => $rekap['rekap_detail'],
            '{rekap_kehadiran}' => $rekap['rekap_kehadiran'],
            '{total_hadir}' => $rekap['total_hadir'],
            '{total_terlambat}' => $rekap['total_terlambat'],
            '{total_izin_sakit}' => $rekap['total_izin_sakit'],
            '{total_alpha}' => $rekap['total_alpha'],
            '{link_portal}' => $linkPortal,
            '{portal_url}' => $linkPortal,
            '{link_pengaduan}' => $linkPengaduan,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Menghitung rekap kehadiran 6 hari terakhir (Senin-Sabtu) pada minggu berjalan
     */
    private function getRekapKehadiranDetail(int $siswaId): array
    {
        // Cari awal minggu ini (Senin)
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endOfWeek = $startOfWeek->copy()->addDays(5)->endOfDay();

        // Buat rentang 6 hari (Senin sampai Sabtu)
        $dates = [];
        for ($i = 0; $i < 6; $i++) {
            $dates[] = $startOfWeek->copy()->addDays($i)->toDateString();
        }

        // Ambil data absensi untuk rentang tanggal tersebut
        $absensi = AbsensiSiswa::where('siswa_id', $siswaId)
            ->whereBetween('tanggal', [$startOfWeek, $endOfWeek])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });

        // Susun teks detail per hari & hitung statistiknya
        $lines = [];
        $counts = [
            'HADIR' => 0,
            'TERLAMBAT' => 0,
            'SAKIT' => 0,
            'IZIN' => 0,
            'ALPHA' => 0,
            'BELUM ABSEN' => 0,
        ];

        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        foreach ($dates as $index => $dateString) {
            $dayName = $dayNames[$index];
            $carbonDate = Carbon::parse($dateString);
            
            // Format tampilan tanggal: "13 Jul"
            $dateFormatted = $carbonDate->translatedFormat('d M');

            if ($absensi->has($dateString)) {
                $statusItem = strtoupper($absensi->get($dateString)->status);
                
                // Format jam masuk untuk detail kehadiran
                $waktuScan = '-';
                if ($absensi->get($dateString)->jam_masuk) {
                    try {
                        $waktuScan = Carbon::parse($absensi->get($dateString)->jam_masuk)->format('H:i');
                    } catch (\Exception $e) {
                        $waktuScan = date('H:i', strtotime($absensi->get($dateString)->jam_masuk));
                    }
                }
                
                // Tambah counter
                if (array_key_exists($statusItem, $counts)) {
                    $counts[$statusItem]++;
                } else {
                    $counts['ALPHA']++;
                }

                $lines[] = "- {$dayName}, {$dateFormatted}: {$statusItem} ({$waktuScan})";
            } else {
                // Cek apakah tanggal tersebut di masa depan
                if (Carbon::parse($dateString)->isFuture()) {
                    $lines[] = "- {$dayName}, {$dateFormatted}: -";
                } else {
                    $counts['BELUM ABSEN']++;
                    $lines[] = "- {$dayName}, {$dateFormatted}: ALPHA / BELUM ABSEN";
                }
            }
        }

        $rekapKehadiran = implode("\n", $lines);
        
        // Tambahkan ringkasan total
        $summary = "\nRingkasan Kehadiran:\n";
        $summary .= "• Hadir/Tepat Waktu: {$counts['HADIR']}\n";
        $summary .= "• Terlambat: {$counts['TERLAMBAT']}\n";
        $summary .= "• Sakit: {$counts['SAKIT']}\n";
        $summary .= "• Izin: {$counts['IZIN']}\n";
        $summary .= "• Alpha/Belum Absen: " . ($counts['ALPHA'] + $counts['BELUM ABSEN']);

        $rekapDetail = $rekapKehadiran . $summary;

        return [
            'rekap_kehadiran' => $rekapKehadiran,
            'total_hadir' => $counts['HADIR'],
            'total_terlambat' => $counts['TERLAMBAT'],
            'total_izin_sakit' => $counts['IZIN'] + $counts['SAKIT'],
            'total_alpha' => $counts['ALPHA'] + $counts['BELUM ABSEN'],
            'rekap_detail' => $rekapDetail,
        ];
    }

    /**
     * Render static message templates
     */
    private function renderStaticMessage(string $templateContent): string
    {
        $lembaga = Pengaturan::where('key', 'nama_lembaga')->value('value') ?: 'MAN 1 Kota Bandung';
        $portalUrl = $this->getLinkPortal();
        $linkPengaduan = url('/pengaduan');

        $replacements = [
            '{lembaga}' => $lembaga,
            '{portal_url}' => $portalUrl,
            '{link_portal}' => $portalUrl,
            '{link_pengaduan}' => $linkPengaduan,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $templateContent);
    }

    /**
     * Mengambil link login portal orang tua / user
     */
    private function getLinkPortal(): string
    {
        // Cek domain/url dari konfigurasi atau gunakan url aplikasi saat ini
        $appUrl = Pengaturan::where('key', 'link_portal_ortu')->value('value');
        if (empty($appUrl)) {
            $appUrl = url('/login');
        }
        return $appUrl;
    }
}
