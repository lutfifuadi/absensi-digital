<?php

namespace App\Services;

use App\Models\Pengaturan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected ?string $apiKey = null;
    protected array $apiKeys = [];
    protected string $model;
    protected string $baseUrl;
    protected int $maxRetries = 2;
    protected int $timeout = 8;

    public function __construct()
    {
        $this->model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-3-flash-preview'));
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
        $this->loadApiKeys();
    }

    protected function loadApiKeys(): void
    {
        // Coba ambil dari key baru 'gemini_api_key'
        $stored = Pengaturan::where('key', 'gemini_api_key')->value('value');

        if ($stored) {
            // Coba decode sebagai JSON (untuk format ["key1", "key2"])
            $decoded = json_decode($stored, true);
            
            if (is_array($decoded)) {
                $this->apiKeys = array_values(array_filter($decoded, fn($k) => !empty($k)));
            } else {
                // Jika bukan JSON, mungkin format koma: key1, key2, key3
                $parts = explode(',', $stored);
                $this->apiKeys = array_values(array_filter(array_map('trim', $parts), fn($k) => !empty($k)));
            }
        }

        // Fallback ke key lama 'gemini_api_keys' jika masih kosong
        if (empty($this->apiKeys)) {
            $oldStored = Pengaturan::where('key', 'gemini_api_keys')->value('value');
            if ($oldStored) {
                $decodedOld = json_decode($oldStored, true);
                if (is_array($decodedOld)) {
                    $this->apiKeys = array_values(array_filter($decodedOld, fn($k) => !empty($k)));
                }
            }
        }

        $envKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
        if (!empty($envKey)) {
            $this->apiKeys[] = $envKey;
        }

        $this->apiKeys = array_values(array_unique($this->apiKeys));

        $currentIndex = (int) Pengaturan::where('key', 'gemini_last_key_index')->value('value');
        $currentIndex = min($currentIndex, max(count($this->apiKeys) - 1, 0));
        $this->apiKey = !empty($this->apiKeys) ? $this->apiKeys[$currentIndex] : null;
    }

    public function rotateToNextKey(): bool
    {
        if (empty($this->apiKeys)) {
            Log::error('GeminiService: No API keys available for rotation');
            return false;
        }

        $lastIndex = (int) Pengaturan::where('key', 'gemini_last_key_index')->value('value');
        $nextIndex = ($lastIndex + 1) % count($this->apiKeys);

        $this->apiKey = $this->apiKeys[$nextIndex];

        Pengaturan::updateOrCreate(
            ['key' => 'gemini_last_key_index'],
            ['value' => (string) $nextIndex, 'group' => 'ai']
        );

        Log::info('GeminiService: rotated to next API key', [
            'from_index' => $lastIndex,
            'to_index' => $nextIndex,
            'total_keys' => count($this->apiKeys),
            'new_key_masked' => substr($this->apiKey, 0, 5) . '...' . substr($this->apiKey, -5)
        ]);

        return true;
    }

    public function sendMessage(string $message, array $context = []): array
    {
        $this->loadApiKeys(); // Pastikan keys terbaru dimuat sebelum mengirim pesan
        $contents = $this->buildContents($message, $context);
        $systemInstruction = $this->buildSystemInstruction();

        $payload = [
            'contents' => $contents,
            'system_instruction' => $systemInstruction,
        ];

        return $this->callWithRetry($payload);
    }

    public function sendWithTools(string $message, array $tools, array $context = []): array
    {
        $this->loadApiKeys(); // Pastikan keys terbaru dimuat sebelum mengirim pesan
        $contents = $this->buildContents($message, $context);

        $systemInstruction = $this->buildSystemInstruction();

        $payload = [
            'contents' => $contents,
            'system_instruction' => $systemInstruction,
            'tools' => [
                ['function_declarations' => $tools]
            ],
        ];

        $attempt = 0;
        $maxToolRounds = 5; // Tingkatkan sedikit batas round-trip tool

        while ($attempt < $maxToolRounds) {
            $response = $this->callWithRetry($payload);

            if (isset($response['candidates'][0]['content']['parts'])) {
                $parts = $response['candidates'][0]['content']['parts'];
                $hasFunctionCall = false;

                // Pastikan functionCall.args yang kosong di-encode sebagai objek {} bukan array []
                $content = $response['candidates'][0]['content'];
                if (isset($content['parts'])) {
                    foreach ($content['parts'] as &$p) {
                        if (isset($p['functionCall']) && (empty($p['functionCall']['args']) || is_array($p['functionCall']['args']) && count($p['functionCall']['args']) === 0)) {
                            $p['functionCall']['args'] = (object)[];
                        }
                    }
                }

                foreach ($parts as $part) {
                    if (isset($part['functionCall'])) {
                        $hasFunctionCall = true;
                        $functionName = $part['functionCall']['name'];
                        $functionArgs = $part['functionCall']['args'] ?? [];
                        
                        // Konversi object ke array jika perlu
                        if (is_object($functionArgs)) {
                            $functionArgs = (array) $functionArgs;
                        }

                        $result = $this->executeTool($functionName, $functionArgs);

                        $payload['contents'][] = $content; // Gunakan variabel $content yang sudah di-fix args-nya
                        $payload['contents'][] = [
                            'role' => 'function',
                            'parts' => [
                                [
                                    'functionResponse' => [
                                        'name' => $functionName,
                                        'response' => [
                                            'name' => $functionName,
                                            'content' => $result,
                                        ],
                                    ],
                                ],
                            ],
                        ];
                    }
                }

                if (!$hasFunctionCall) {
                    return $response;
                }
            }

            $attempt++;
        }

        return [
            'candidates' => [
                [
                    'content' => [
                        'role' => 'model',
                        'parts' => [
                            ['text' => 'Maaf, saya tidak dapat menyelesaikan permintaan Anda setelah beberapa kali percobaan. Silakan coba lagi dengan instruksi yang lebih jelas.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getHistory(int $userId, int $limit = 50): array
    {
        return \App\Models\ChatLog::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function clearHistory(int $userId): void
    {
        \App\Models\ChatLog::where('user_id', $userId)->delete();
    }

    protected function buildSystemInstruction(): array
    {
        $instruction = 'Kamu adalah asisten AI untuk sistem absensi sekolah bernama "Asisten Mansaba". ';
        $instruction .= 'Tugasmu membantu user mengelola data seperti siswa, guru, kelas, dan pengaturan lainnya. ';
        $instruction .= 'Gunakan bahasa Indonesia yang sopan, ramah, dan profesional. ';
        $instruction .= 'SELALU berikan jawaban dalam format Markdown yang rapi: ';
        $instruction .= '- Gunakan **tebal** untuk poin penting atau angka. ';
        $instruction .= '- Gunakan bullet points atau tabel untuk menyajikan data list. ';
        $instruction .= '- Gunakan emoji yang relevan (misal: 👨‍🎓, 👩‍🏫, 🏫, 📊) agar tampilan menarik. ';
        $instruction .= '- Berikan jawaban yang padat dan tidak terlalu banyak baris kosong antar paragraf. ';
        $instruction .= 'Jika user meminta edit data, gunakan tool yang tersedia. ';
        $instruction .= 'Jangan pernah memberikan informasi sensitif seperti password.';

        return [
            'parts' => [
                ['text' => $instruction],
            ],
        ];
    }

    protected function buildContents(string $message, array $context): array
    {
        $contents = [];

        if (!empty($context)) {
            foreach ($context as $msg) {
                $role = $msg['role'] ?? 'user';
                if ($role === 'assistant') {
                    $role = 'model';
                }
                
                // Pastikan format content sesuai dengan dokumentasi Gemini
                $parts = [];
                if (isset($msg['parts'])) {
                    $parts = $msg['parts'];
                } else {
                    $text = $msg['message'] ?? $msg['text'] ?? '';
                    if (!empty($text)) {
                        $parts[] = ['text' => $text];
                    }
                }

                if (!empty($parts)) {
                    $contents[] = [
                        'role' => $role,
                        'parts' => $parts,
                    ];
                }
            }
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        return $contents;
    }

    protected function callWithRetry(array $payload): array
    {
        if (empty($this->apiKey)) {
            Log::error('Gemini API key not configured');
            return $this->errorResponse('API Key Gemini belum dikonfigurasi. Set GEMINI_API_KEY di .env atau di Pengaturan.');
        }

        $lastError = null;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $url = "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}";

                // Pastikan payload di-encode dengan benar untuk Gemini API terbaru
                // Khususnya penanganan empty args pada functionCall
                $jsonPayload = json_encode($payload);

                $response = Http::timeout($this->timeout)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->withBody($jsonPayload, 'application/json')
                    ->post($url);

                if ($response->successful()) {
                    return $response->json();
                }

                $status = $response->status();
                $body = $response->body();
                $errorData = $response->json();

                // Deteksi masalah 'args' atau unknown name
                if ($status === 400 && str_contains($body, 'args')) {
                    Log::warning("Gemini API Error 400 (args issues) detected. Attempting to fix payload structure.");
                    // Payload sudah di-handle di sendWithTools, jika masih 400 berarti ada isu lain
                }

                if ($status === 429 || ($status === 400 && str_contains($body, 'API_KEY_INVALID'))) {
                    Log::warning("Gemini API Issue (Status: {$status}) — rotating key (attempt {$attempt})");
                    if ($this->rotateToNextKey()) {
                        continue;
                    }
                    sleep(min(5 * $attempt, 30));
                    continue;
                }

                if ($status === 403) {
                    Log::warning("Gemini API forbidden — rotating key (attempt {$attempt})");
                    if ($this->rotateToNextKey()) {
                        continue;
                    }
                    return $this->errorResponse('Semua API Key tidak valid atau tidak memiliki izin.');
                }

                if ($status === 503) {
                    Log::warning("Gemini API 503 Service Unavailable (attempt {$attempt})");
                    if ($attempt < $this->maxRetries) {
                        sleep(1); // Jeda 1 detik sebelum ganti key biar server sempat pulih
                        if ($this->rotateToNextKey()) {
                            continue;
                        }
                        sleep(2 * $attempt);
                        continue;
                    }
                }

                if ($status === 500) {
                    Log::warning("Gemini API 500 Internal Server Error (attempt {$attempt})");
                    if ($attempt < $this->maxRetries) {
                        if ($this->rotateToNextKey()) {
                            continue;
                        }
                        sleep(2 * $attempt);
                        continue;
                    }
                }

                Log::error("Gemini API error (attempt {$attempt})", [
                    'status' => $status,
                    'body' => $body,
                ]);

                if ($this->rotateToNextKey()) {
                    continue;
                }

                return $this->errorResponse('Layanan AI sedang bermasalah. Silakan coba lagi nanti.');

            } catch (\Exception $e) {
                $lastError = $e;
                Log::warning("Gemini API connection error (attempt {$attempt})", [
                    'error' => $e->getMessage(),
                ]);

                if ($this->rotateToNextKey()) {
                    continue;
                }

                if ($attempt < $this->maxRetries) {
                    sleep(2 * $attempt);
                }
            }
        }

        Log::error('Gemini API max retries exceeded', [
            'last_error' => $lastError ? $lastError->getMessage() : 'Unknown',
        ]);

        return $this->errorResponse('Gagal terhubung ke layanan AI setelah beberapa percobaan.');
    }

    protected function executeTool(string $functionName, array $args): string
    {
        try {
            $user = auth()->user();

            $allowedRoles = ['super_admin', 'admin_sekolah', 'operator'];
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return json_encode(['error' => 'Anda tidak memiliki izin untuk melakukan operasi ini.']);
            }

            $result = match ($functionName) {
                'get_siswa' => $this->toolGetSiswa($args),
                'update_siswa' => $this->toolUpdateSiswa($args, $user),
                'get_guru' => $this->toolGetGuru($args),
                'update_guru' => $this->toolUpdateGuru($args, $user),
                'get_kelas' => $this->toolGetKelas($args),
                'update_kelas' => $this->toolUpdateKelas($args, $user),
                'cari_siswa' => $this->toolCariSiswa($args),
                'cari_guru' => $this->toolCariGuru($args),
                'statistik_data' => $this->toolStatistik(),
                default => json_encode(['error' => "Tool '{$functionName}' tidak dikenal."]),
            };

            return $result;
        } catch (\Exception $e) {
            Log::error('Gemini tool execution error', [
                'function' => $functionName,
                'error' => $e->getMessage(),
            ]);
            return json_encode(['error' => 'Gagal mengeksekusi perintah: ' . $e->getMessage()]);
        }
    }

    protected function toolGetSiswa(array $args): string
    {
        $query = \App\Models\Siswa::with(['kelas', 'user']);
        if (!empty($args['id'])) {
            $query->where('id', $args['id']);
        }
        if (!empty($args['nisn'])) {
            $query->where('nisn', $args['nisn']);
        }
        if (!empty($args['kelas_id'])) {
            $query->where('kelas_id', $args['kelas_id']);
        }
        $siswa = $query->limit(20)->get();

        return $siswa->toJson();
    }

    protected function toolUpdateSiswa(array $args, $user): string
    {
        if (!in_array($user->role, ['super_admin', 'admin_sekolah', 'operator'])) {
            return json_encode(['error' => 'Izin ditolak.']);
        }

        $siswa = \App\Models\Siswa::find($args['id'] ?? 0);
        if (!$siswa) {
            return json_encode(['error' => 'Siswa tidak ditemukan.']);
        }

        $allowedFields = ['nama_lengkap', 'nis', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'no_hp', 'no_hp_ortu', 'status'];
        $updates = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $args)) {
                $updates[$field] = $args[$field];
            }
        }

        if (empty($updates)) {
            return json_encode(['error' => 'Tidak ada field yang valid untuk diupdate.']);
        }

        $siswa->update($updates);

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update',
            'description' => "AI Chat: Update siswa {$siswa->nama_lengkap} (ID: {$siswa->id})",
        ]);

        return json_encode(['success' => true, 'message' => "Data siswa {$siswa->nama_lengkap} berhasil diupdate.", 'data' => $siswa->toArray()]);
    }

    protected function toolGetGuru(array $args): string
    {
        $query = \App\Models\Guru::with('user');
        if (!empty($args['id'])) {
            $query->where('id', $args['id']);
        }
        if (!empty($args['nip'])) {
            $query->where('nip', $args['nip']);
        }
        $guru = $query->limit(20)->get();

        return $guru->toJson();
    }

    protected function toolUpdateGuru(array $args, $user): string
    {
        if (!in_array($user->role, ['super_admin', 'admin_sekolah'])) {
            return json_encode(['error' => 'Izin ditolak.']);
        }

        $guru = \App\Models\Guru::find($args['id'] ?? 0);
        if (!$guru) {
            return json_encode(['error' => 'Guru tidak ditemukan.']);
        }

        $allowedFields = ['nama_lengkap', 'nip', 'jenis_kelamin', 'mata_pelajaran', 'jabatan', 'no_hp', 'status'];
        $updates = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $args)) {
                $updates[$field] = $args[$field];
            }
        }

        if (empty($updates)) {
            return json_encode(['error' => 'Tidak ada field yang valid untuk diupdate.']);
        }

        $guru->update($updates);

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update',
            'description' => "AI Chat: Update guru {$guru->nama_lengkap} (ID: {$guru->id})",
        ]);

        return json_encode(['success' => true, 'message' => "Data guru {$guru->nama_lengkap} berhasil diupdate.", 'data' => $guru->toArray()]);
    }

    protected function toolGetKelas(array $args): string
    {
        $query = \App\Models\Kelas::with('waliKelas');
        if (!empty($args['id'])) {
            $query->where('id', $args['id']);
        }
        if (!empty($args['nama'])) {
            $query->where('nama', 'like', '%' . $args['nama'] . '%');
        }
        $kelas = $query->limit(20)->get();

        return $kelas->toJson();
    }

    protected function toolUpdateKelas(array $args, $user): string
    {
        if (!in_array($user->role, ['super_admin', 'admin_sekolah'])) {
            return json_encode(['error' => 'Izin ditolak.']);
        }

        $kelas = \App\Models\Kelas::find($args['id'] ?? 0);
        if (!$kelas) {
            return json_encode(['error' => 'Kelas tidak ditemukan.']);
        }

        $allowedFields = ['nama', 'tingkat', 'jurusan', 'status'];
        $updates = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $args)) {
                $updates[$field] = $args[$field];
            }
        }

        if (empty($updates)) {
            return json_encode(['error' => 'Tidak ada field yang valid untuk diupdate.']);
        }

        $kelas->update($updates);

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update',
            'description' => "AI Chat: Update kelas {$kelas->nama} (ID: {$kelas->id})",
        ]);

        return json_encode(['success' => true, 'message' => "Data kelas {$kelas->nama} berhasil diupdate.", 'data' => $kelas->toArray()]);
    }

    protected function toolCariSiswa(array $args): string
    {
        $keyword = $args['keyword'] ?? '';
        if (empty($keyword)) {
            return json_encode(['error' => 'Kata kunci pencarian tidak boleh kosong.']);
        }

        $siswa = \App\Models\Siswa::where('nama_lengkap', 'like', "%{$keyword}%")
            ->orWhere('nisn', 'like', "%{$keyword}%")
            ->orWhere('nis', 'like', "%{$keyword}%")
            ->with('kelas')
            ->limit(10)
            ->get();

        return $siswa->toJson();
    }

    protected function toolCariGuru(array $args): string
    {
        $keyword = $args['keyword'] ?? '';
        if (empty($keyword)) {
            return json_encode(['error' => 'Kata kunci pencarian tidak boleh kosong.']);
        }

        $guru = \App\Models\Guru::where('nama_lengkap', 'like', "%{$keyword}%")
            ->orWhere('nip', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();

        return $guru->toJson();
    }

    protected function toolStatistik(): string
    {
        $totalSiswa = \App\Models\Siswa::count();
        $totalGuru = \App\Models\Guru::count();
        $totalKelas = \App\Models\Kelas::count();
        $totalUser = \App\Models\User::count();

        return json_encode([
            'total_siswa' => $totalSiswa,
            'total_guru' => $totalGuru,
            'total_kelas' => $totalKelas,
            'total_user' => $totalUser,
        ]);
    }

    protected function errorResponse(string $message): array
    {
        return [
            'candidates' => [
                [
                    'content' => [
                        'role' => 'model',
                        'parts' => [
                            ['text' => $message],
                        ],
                    ],
                ],
            ],
            'error' => true,
        ];
    }

    public function getToolDefinitions(): array
    {
        return [
            [
                'name' => 'get_siswa',
                'description' => 'Mendapatkan data siswa berdasarkan ID, NISN, atau kelas',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'ID siswa (opsional)'],
                        'nisn' => ['type' => 'string', 'description' => 'NISN siswa (opsional)'],
                        'kelas_id' => ['type' => 'integer', 'description' => 'ID kelas (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'update_siswa',
                'description' => 'Mengupdate data siswa (nama, alamat, no_hp, status, dll)',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'ID siswa'],
                        'nama_lengkap' => ['type' => 'string', 'description' => 'Nama lengkap siswa'],
                        'nis' => ['type' => 'string', 'description' => 'NIS siswa'],
                        'jenis_kelamin' => ['type' => 'string', 'description' => 'Jenis kelamin (L/P)'],
                        'tempat_lahir' => ['type' => 'string', 'description' => 'Tempat lahir'],
                        'tanggal_lahir' => ['type' => 'string', 'description' => 'Tanggal lahir'],
                        'alamat' => ['type' => 'string', 'description' => 'Alamat'],
                        'no_hp' => ['type' => 'string', 'description' => 'Nomor HP'],
                        'no_hp_ortu' => ['type' => 'string', 'description' => 'Nomor HP orang tua'],
                        'status' => ['type' => 'string', 'description' => 'Status (aktif/nonaktif/lulus/keluar)'],
                    ],
                    'required' => ['id'],
                ],
            ],
            [
                'name' => 'get_guru',
                'description' => 'Mendapatkan data guru berdasarkan ID atau NIP',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'ID guru (opsional)'],
                        'nip' => ['type' => 'string', 'description' => 'NIP guru (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'update_guru',
                'description' => 'Mengupdate data guru (nama, mapel, jabatan, dll)',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'ID guru'],
                        'nama_lengkap' => ['type' => 'string', 'description' => 'Nama lengkap guru'],
                        'nip' => ['type' => 'string', 'description' => 'NIP guru'],
                        'jenis_kelamin' => ['type' => 'string', 'description' => 'Jenis kelamin (L/P)'],
                        'mata_pelajaran' => ['type' => 'string', 'description' => 'Mata pelajaran'],
                        'jabatan' => ['type' => 'string', 'description' => 'Jabatan'],
                        'no_hp' => ['type' => 'string', 'description' => 'Nomor HP'],
                        'status' => ['type' => 'string', 'description' => 'Status (aktif/nonaktif)'],
                    ],
                    'required' => ['id'],
                ],
            ],
            [
                'name' => 'get_kelas',
                'description' => 'Mendapatkan data kelas berdasarkan ID atau nama',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'ID kelas (opsional)'],
                        'nama' => ['type' => 'string', 'description' => 'Nama kelas untuk pencarian (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'update_kelas',
                'description' => 'Mengupdate data kelas (nama, tingkat, jurusan)',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'description' => 'ID kelas'],
                        'nama' => ['type' => 'string', 'description' => 'Nama kelas'],
                        'tingkat' => ['type' => 'string', 'description' => 'Tingkat (10/11/12)'],
                        'jurusan' => ['type' => 'string', 'description' => 'Jurusan'],
                        'status' => ['type' => 'string', 'description' => 'Status (aktif/nonaktif)'],
                    ],
                    'required' => ['id'],
                ],
            ],
            [
                'name' => 'cari_siswa',
                'description' => 'Mencari siswa berdasarkan nama, NISN, atau NIS',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'keyword' => ['type' => 'string', 'description' => 'Kata kunci pencarian (nama, NISN, atau NIS)'],
                    ],
                    'required' => ['keyword'],
                ],
            ],
            [
                'name' => 'cari_guru',
                'description' => 'Mencari guru berdasarkan nama atau NIP',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'keyword' => ['type' => 'string', 'description' => 'Kata kunci pencarian (nama atau NIP)'],
                    ],
                    'required' => ['keyword'],
                ],
            ],
            [
                'name' => 'statistik_data',
                'description' => 'Mendapatkan statistik data (total siswa, guru, kelas, user)',
                'parameters' => [
                    'type' => 'object',
                    'properties' => (object) [],
                ],
            ],
        ];
    }
}
