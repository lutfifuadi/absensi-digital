<?php

namespace App\Services;

use App\Models\Pengaturan;
use App\Models\Guide;
use App\Models\GuideCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class GeminiService
{
    protected ?string $apiKey = null;
    protected array $apiKeys = [];
    protected string $model;
    protected string $baseUrl;
    protected int $maxRetries = 0; // Akan diset dinamis berdasarkan jumlah API key
    protected int $timeout = 8;
    protected array $toolMetadata = [];

    /**
     * Tier access mapping untuk membatasi tool berdasarkan role.
     */
    protected array $tierAccess = [
        'tier_1' => ['siswa', 'orang_tua'],
        'tier_2' => ['guru', 'wali_kelas', 'staff_tu', 'piket'],
        'tier_3' => ['super_admin', 'admin_sekolah', 'operator'],
    ];

    public function __construct()
    {
        $this->model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-3.5-flash'));
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

        // Fallback dari config/services.php
        $configKey = config('services.gemini.api_key');
        if (!empty($configKey)) {
            $this->apiKeys[] = $configKey;
        }

        // Fallback terakhir: env('GEMINI_API_KEY') langsung (untuk mengakali config caching)
        $directEnvKey = env('GEMINI_API_KEY');
        if (!empty($directEnvKey)) {
            $this->apiKeys[] = $directEnvKey;
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

    public function sendMessage(string $message, array $context = [], ?string $userRole = null): array
    {
        $this->loadApiKeys(); // Pastikan keys terbaru dimuat sebelum mengirim pesan
        $contents = $this->buildContents($message, $context);
        $systemInstruction = $this->buildDynamicSystemInstruction($userRole ?? 'pengguna');

        $payload = [
            'contents' => $contents,
            'system_instruction' => $systemInstruction,
        ];

        return $this->callWithRetry($payload);
    }

    public function sendWithTools(string $message, array $tools, array $context = [], ?string $userRole = null): array
    {
        $this->loadApiKeys(); // Pastikan keys terbaru dimuat sebelum mengirim pesan
        $contents = $this->buildContents($message, $context);

        $role = $userRole ?? 'pengguna';
        $systemInstruction = $this->buildDynamicSystemInstruction($role);

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

                        $functionId = $part['functionCall']['id'] ?? null;
                        $result = $this->executeTool($functionName, $functionArgs, $role);

                        $payload['contents'][] = $content; // Gunakan variabel $content yang sudah di-fix args-nya
                        
                        // Response content harus didecode dulu dari string JSON ke object/array PHP asli
                        // Karena Gemini API mencocokkan ini dengan schema responsenya langsung
                        $decodedResult = json_decode($result, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            // Jika hasil decode adalah array list (sequential), bungkus dalam key-value map
                            if (is_array($decodedResult) && count($decodedResult) > 0 && array_keys($decodedResult) === range(0, count($decodedResult) - 1)) {
                                $responseContent = ['data' => $decodedResult];
                            } else {
                                $responseContent = $decodedResult;
                            }
                        } else {
                            $responseContent = ['result' => $result];
                        }

                        $functionResponse = [
                            'name' => $functionName,
                            'response' => $responseContent,
                        ];

                        if ($functionId) {
                            $functionResponse['id'] = $functionId;
                        }

                        $payload['contents'][] = [
                            'role' => 'tool',
                            'parts' => [
                                [
                                    'functionResponse' => $functionResponse,
                                ],
                            ],
                        ];
                    }
                }

                if (!$hasFunctionCall) {
                    // Tambahkan metadata tool call ke response untuk source attribution
                    if (!empty($this->toolMetadata)) {
                        $response['_toolMetadata'] = $this->toolMetadata;
                    }
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

    /**
     * Build system instruction yang dinamis berdasarkan role user dan nama lembaga.
     */
    protected function buildDynamicSystemInstruction(string $userRole): array
    {
        // Urutan prioritas: nama_lembaga > nama_sekolah > SCHOOL_NAME env > config('app.name')
        $namaLembaga = Pengaturan::where('key', 'nama_lembaga')->value('value');
        if (empty($namaLembaga)) {
            $namaLembaga = Pengaturan::where('key', 'nama_sekolah')->value('value');
        }
        if (empty($namaLembaga)) {
            $namaLembaga = env('SCHOOL_NAME', config('app.name', 'Sekolah'));
        }

        $asistenName = "Asisten {$namaLembaga}";

        $instruction = "Kamu adalah {$asistenName}, asisten AI cerdas untuk sistem presensi sekolah. ";
        $instruction .= "Tugas utama kamu adalah MEMBANTU user dalam menggunakan sistem presensi ini. ";
        $instruction .= "\n\n";
        $instruction .= "### 📚 Yang Kamu Miliki:\n";
        $instruction .= "- Kamu memiliki akses penuh ke **Panduan Sistem** yang bisa dicari menggunakan tool `cari_panduan`.\n";
        $instruction .= "- Kamu bisa memberikan **step-by-step guidance** berdasarkan panduan yang tersedia.\n";
        $instruction .= "- Kamu tahu **daftar fitur** yang tersedia untuk setiap role melalui tool `get_fitur_sistem`.\n";
        $instruction .= "- Kamu juga memiliki akses ke data sistem (siswa, guru, kelas) sesuai level akses user.\n";
        $instruction .= "\n\n";
        $instruction .= "### 🎯 Cara Merespon:\n";
        $instruction .= "- Gunakan **bahasa Indonesia** yang sopan, ramah, dan profesional.\n";
        $instruction .= "- SELALU berikan jawaban dalam **format Markdown** yang rapi:\n";
        $instruction .= "  - Gunakan **tebal** untuk poin penting atau angka.\n";
        $instruction .= "  - Gunakan bullet points atau tabel untuk menyajikan data list.\n";
        $instruction .= "  - Gunakan emoji yang relevan (misal: 👨‍🎓, 👩‍🏫, 🏫, 📊, 📖) agar tampilan menarik.\n";
        $instruction .= "  - Berikan jawaban yang **padat** dan tidak terlalu banyak baris kosong antar paragraf.\n";
        $instruction .= "\n\n";
        $instruction .= "### 👤 Role User Saat Ini: **{$userRole}**\n";
        $instruction .= "- Jawablah sesuai dengan role user yang sedang chat.\n";
        $instruction .= "- Jika user bertanya **cara melakukan sesuatu** di sistem, GUNAKAN tool `cari_panduan` untuk mencari panduan yang relevan.\n";
        $instruction .= "- Jika user bertanya **fitur apa saja yang tersedia** untuk role-nya, gunakan tool `get_fitur_sistem`.\n";
        $instruction .= "- Jangan pernah memberikan informasi sensitif seperti password.\n";
        $instruction .= "- Jika ada pertanyaan di luar konteks sistem presensi, arahkan kembali ke topik yang relevan.\n";

        return [
            'parts' => [
                ['text' => $instruction],
            ],
        ];
    }

    /**
     * Build system instruction statis (legacy, untuk backward compatibility).
     */
    protected function buildSystemInstruction(): array
    {
        return $this->buildDynamicSystemInstruction('pengguna');
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
        Log::info('Gemini Payload Request: ' . json_encode($payload, JSON_UNESCAPED_UNICODE));
        if (empty($this->apiKey)) {
            Log::error('Gemini API key not configured');
            return $this->errorResponse('API Key Gemini belum dikonfigurasi. Set GEMINI_API_KEY di .env atau di Pengaturan.');
        }

        // Set retry count dinamis berdasarkan jumlah API key yang tersedia
        $this->maxRetries = max(1, count($this->apiKeys));

        $lastError = null;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            // Cari index key saat ini untuk keperluan logging (masked)
            $currentKeyIndex = array_search($this->apiKey, $this->apiKeys, true);
            $keyLabel = ($currentKeyIndex !== false) ? "#{$currentKeyIndex}" : '#unknown';
            $maskedKey = substr($this->apiKey, 0, 5) . '...' . substr($this->apiKey, -5);

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
                $errorMessage = $errorData['error']['message'] ?? $body;

                // Logging error dengan key index dan masked key
                Log::warning("Gemini API call failed with key{$keyLabel} ({$maskedKey}): {$status} {$errorMessage}");

                // Deteksi masalah 'args' atau unknown name
                if ($status === 400 && str_contains($body, 'args')) {
                    Log::warning("Gemini API Error 400 (args issues) detected with key{$keyLabel}. Attempting next key.");
                    if ($this->rotateToNextKey()) {
                        continue;
                    }
                }

                if ($status === 429 || ($status === 400 && str_contains($body, 'API_KEY_INVALID'))) {
                    Log::warning("Gemini API rate limit / invalid key{$keyLabel} ({$maskedKey}) — rotating (attempt {$attempt})");
                    if ($this->rotateToNextKey()) {
                        continue;
                    }
                    sleep(min(5 * $attempt, 30));
                    continue;
                }

                if ($status === 403) {
                    Log::warning("Gemini API forbidden key{$keyLabel} ({$maskedKey}) — rotating (attempt {$attempt})");
                    if ($this->rotateToNextKey()) {
                        continue;
                    }
                    return $this->errorResponse('Semua API Key tidak valid atau tidak memiliki izin.');
                }

                if ($status === 503) {
                    Log::warning("Gemini API 503 Service Unavailable with key{$keyLabel} ({$maskedKey}, attempt {$attempt})");
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
                    Log::warning("Gemini API 500 Internal Server Error with key{$keyLabel} ({$maskedKey}, attempt {$attempt})");
                    if ($attempt < $this->maxRetries) {
                        if ($this->rotateToNextKey()) {
                            continue;
                        }
                        sleep(2 * $attempt);
                        continue;
                    }
                }

                Log::error("Gemini API error with key{$keyLabel} ({$maskedKey}, attempt {$attempt})", [
                    'status' => $status,
                    'body' => $body,
                ]);

                if ($this->rotateToNextKey()) {
                    continue;
                }

                return $this->errorResponse('Layanan AI sedang bermasalah. Silakan coba lagi nanti.');

            } catch (\Exception $e) {
                $lastError = $e;
                Log::warning("Gemini API connection error with key{$keyLabel} ({$maskedKey}, attempt {$attempt}): " . $e->getMessage());

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
            'total_keys' => count($this->apiKeys),
        ]);

        return $this->errorResponse('Gagal terhubung ke layanan AI setelah beberapa percobaan.');
    }

    /**
     * Mendapatkan tier user berdasarkan role.
     */
    protected function getUserTier(string $role): string
    {
        foreach ($this->tierAccess as $tier => $roles) {
            if (in_array($role, $roles)) {
                return $tier;
            }
        }
        return 'tier_1'; // Default tier terendah untuk role yang tidak dikenal
    }

    /**
     * Mendapatkan daftar tool yang diizinkan untuk suatu tier.
     */
    protected function getAllowedToolsForTier(string $role): array
    {
        $tier = $this->getUserTier($role);

        $tools = [
            'tier_1' => ['cari_panduan', 'get_fitur_sistem'],
            'tier_2' => ['cari_panduan', 'get_fitur_sistem', 'get_siswa', 'get_guru', 'get_kelas', 'cari_siswa', 'cari_guru', 'statistik_data'],
            'tier_3' => ['cari_panduan', 'get_fitur_sistem', 'get_siswa', 'update_siswa', 'get_guru', 'update_guru', 'get_kelas', 'update_kelas', 'cari_siswa', 'cari_guru', 'statistik_data'],
        ];

        return $tools[$tier] ?? $tools['tier_1'];
    }

    protected function executeTool(string $functionName, array $args, ?string $userRole = null): string
    {
        try {
            $user = auth()->user();
            $role = $userRole ?? ($user ? $user->role : 'pengguna');

            // Cek izin berdasarkan tier
            $allowedTools = $this->getAllowedToolsForTier($role);
            if (!in_array($functionName, $allowedTools)) {
                return json_encode(['error' => 'Anda tidak memiliki izin untuk menggunakan fitur ini.']);
            }

            $result = match ($functionName) {
                'cari_panduan' => $this->toolCariPanduan($args, $role),
                'get_fitur_sistem' => $this->toolGetFiturSistem($args),
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

            // Kumpulkan metadata untuk source attribution
            if ($functionName === 'cari_panduan') {
                $decoded = json_decode($result, true);
                if (is_array($decoded) && !isset($decoded['error'])) {
                    $sources = [];
                    // Hasil dari cari_panduan bisa berupa array guides atau array dengan key 'data'
                    $guides = $decoded['data'] ?? $decoded;
                    if (is_array($guides)) {
                        foreach ($guides as $guide) {
                            if (isset($guide['title'])) {
                                $sources[] = $guide['title'];
                            }
                        }
                    }
                    if (!empty($sources)) {
                        $this->toolMetadata = [
                            'tool' => 'cari_panduan',
                            'sources' => $sources,
                        ];
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Gemini tool execution error', [
                'function' => $functionName,
                'error' => $e->getMessage(),
            ]);
            return json_encode(['error' => 'Gagal mengeksekusi perintah: ' . $e->getMessage()]);
        }
    }

    /**
     * Tool: Mencari panduan penggunaan fitur berdasarkan kata kunci.
     */
    protected function toolCariPanduan(array $args, string $role): string
    {
        $keyword = $args['keyword'] ?? $args['query'] ?? '';
        $roleFilter = $args['role'] ?? $role;

        if (empty($keyword) || strlen($keyword) < 2) {
            return json_encode(['error' => 'Kata kunci pencarian minimal 2 karakter.']);
        }

        try {
            $query = Guide::where('status', 'published')
                ->where(function ($q) use ($roleFilter) {
                    $q->whereNull('role_target')
                      ->orWhere('role_target', 'public')
                      ->orWhere('role_target', $roleFilter)
                      ->orWhere('role_target', 'LIKE', $roleFilter . ',%')
                      ->orWhere('role_target', 'LIKE', '%,' . $roleFilter)
                      ->orWhere('role_target', 'LIKE', '%,' . $roleFilter . ',%');
                });

            // Coba FULLTEXT search dulu
            try {
                $guides = $query->clone()
                    ->select('guides.*', 'guide_categories.name as category_name')
                    ->leftJoin('guide_categories', 'guides.category_id', '=', 'guide_categories.id')
                    ->whereRaw('MATCH(guides.title, guides.content) AGAINST(? IN BOOLEAN MODE)', [$keyword])
                    ->limit(3)
                    ->get();
            } catch (\Exception $e) {
                // Fallback ke LIKE search jika FULLTEXT error
                $guides = $query->clone()
                    ->select('guides.*', 'guide_categories.name as category_name')
                    ->leftJoin('guide_categories', 'guides.category_id', '=', 'guide_categories.id')
                    ->where(function ($q) use ($keyword) {
                        $q->where('guides.title', 'LIKE', "%{$keyword}%")
                          ->orWhere('guides.content', 'LIKE', "%{$keyword}%");
                    })
                    ->limit(3)
                    ->get();
            }

            if ($guides->isEmpty()) {
                return json_encode(['message' => "Tidak ditemukan panduan dengan kata kunci '{$keyword}'. Silakan coba kata kunci lain.", 'data' => []]);
            }

            $results = [];
            foreach ($guides as $guide) {
                // Parse steps dari content (ambil poin-poin penting)
                $steps = $this->extractStepsFromContent($guide->content);

                $results[] = [
                    'title' => $guide->title,
                    'category' => $guide->category_name ?? 'Umum',
                    'excerpt' => $guide->excerpt ?? substr(strip_tags($guide->content), 0, 200) . '...',
                    'steps' => $steps,
                ];
            }

            return json_encode($results);
        } catch (\Exception $e) {
            Log::error('Error searching guides', [
                'keyword' => $keyword,
                'error' => $e->getMessage(),
            ]);
            return json_encode(['error' => 'Gagal mencari panduan: ' . $e->getMessage()]);
        }
    }

    /**
     * Ekstrak langkah-langkah dari konten panduan (markdown).
     */
    protected function extractStepsFromContent(string $content): array
    {
        $steps = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Ambil baris yang dimulai dengan angka (1., 2., dst) atau strip (-)
            if (preg_match('/^(\d+[\.\)]|[-*])\s+(.+)/', $trimmed, $matches)) {
                $steps[] = trim($matches[2]);
            }
            // Batasi maksimal 10 steps
            if (count($steps) >= 10) {
                break;
            }
        }

        // Jika tidak ada step terstruktur, ambil kalimat pertama sebagai step tunggal
        if (empty($steps)) {
            $firstSentence = substr(strip_tags($content), 0, 150);
            $steps[] = $firstSentence;
        }

        return $steps;
    }

    /**
     * Tool: Mendapatkan daftar fitur sistem berdasarkan role.
     */
    protected function toolGetFiturSistem(array $args): string
    {
        $role = $args['role'] ?? 'pengguna';

        try {
            $categories = GuideCategory::whereHas('guides', function ($q) {
                $q->where('status', 'published');
            })->ordered()->get();

            $fitur = [];
            foreach ($categories as $category) {
                $guidesQuery = $category->guides()->where('status', 'published')
                    ->where(function ($q) use ($role) {
                        $q->whereNull('role_target')
                          ->orWhere('role_target', 'public')
                          ->orWhere('role_target', $role)
                          ->orWhere('role_target', 'LIKE', $role . ',%')
                          ->orWhere('role_target', 'LIKE', '%,' . $role)
                          ->orWhere('role_target', 'LIKE', '%,' . $role . ',%');
                    });

                $count = $guidesQuery->count();
                $guideTitles = $guidesQuery->pluck('title')->toArray();

                if ($count > 0) {
                    $fitur[] = [
                        'kategori' => $category->name,
                        'jumlah_panduan' => $count,
                        'panduan' => $guideTitles,
                    ];
                }
            }

            return json_encode([
                'role' => $role,
                'fitur' => $fitur,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting fitur sistem', [
                'role' => $role,
                'error' => $e->getMessage(),
            ]);
            return json_encode(['error' => 'Gagal mendapatkan daftar fitur: ' . $e->getMessage()]);
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

    /**
     * Mendapatkan tool definitions yang difilter berdasarkan role user.
     */
    public function getToolDefinitions(?string $userRole = null): array
    {
        $role = $userRole ?? 'pengguna';
        $allowedTools = $this->getAllowedToolsForTier($role);

        $allTools = [
            [
                'name' => 'cari_panduan',
                'description' => 'Mencari panduan penggunaan fitur di sistem presensi berdasarkan kata kunci. Gunakan tool ini ketika user bertanya cara melakukan sesuatu.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'query' => [
                            'type' => 'STRING',
                            'description' => 'Kata kunci pencarian panduan (min 2 karakter)'
                        ],
                        'role' => [
                            'type' => 'STRING',
                            'description' => 'Role user untuk filter panduan yang sesuai'
                        ],
                    ],
                    'required' => ['query'],
                ],
            ],
            [
                'name' => 'get_fitur_sistem',
                'description' => 'Mendapatkan daftar fitur yang tersedia di sistem presensi berdasarkan role pengguna.',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'role' => [
                            'type' => 'STRING',
                            'description' => 'Role user untuk menampilkan fitur yang relevan'
                        ],
                    ],
                    'required' => ['role'],
                ],
            ],
            [
                'name' => 'get_siswa',
                'description' => 'Mendapatkan data siswa berdasarkan ID, NISN, atau kelas',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'id' => ['type' => 'INTEGER', 'description' => 'ID siswa (opsional)'],
                        'nisn' => ['type' => 'STRING', 'description' => 'NISN siswa (opsional)'],
                        'kelas_id' => ['type' => 'INTEGER', 'description' => 'ID kelas (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'update_siswa',
                'description' => 'Mengupdate data siswa (nama, alamat, no_hp, status, dll)',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'id' => ['type' => 'INTEGER', 'description' => 'ID siswa'],
                        'nama_lengkap' => ['type' => 'STRING', 'description' => 'Nama lengkap siswa'],
                        'nis' => ['type' => 'STRING', 'description' => 'NIS siswa'],
                        'jenis_kelamin' => ['type' => 'STRING', 'description' => 'Jenis kelamin (L/P)'],
                        'tempat_lahir' => ['type' => 'STRING', 'description' => 'Tempat lahir'],
                        'tanggal_lahir' => ['type' => 'STRING', 'description' => 'Tanggal lahir'],
                        'alamat' => ['type' => 'STRING', 'description' => 'Alamat'],
                        'no_hp' => ['type' => 'STRING', 'description' => 'Nomor HP'],
                        'no_hp_ortu' => ['type' => 'STRING', 'description' => 'Nomor HP orang tua'],
                        'status' => ['type' => 'STRING', 'description' => 'Status (aktif/nonaktif/lulus/keluar)'],
                    ],
                    'required' => ['id'],
                ],
            ],
            [
                'name' => 'get_guru',
                'description' => 'Mendapatkan data guru berdasarkan ID atau NIP',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'id' => ['type' => 'INTEGER', 'description' => 'ID guru (opsional)'],
                        'nip' => ['type' => 'STRING', 'description' => 'NIP guru (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'update_guru',
                'description' => 'Mengupdate data guru (nama, mapel, jabatan, dll)',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'id' => ['type' => 'INTEGER', 'description' => 'ID guru'],
                        'nama_lengkap' => ['type' => 'STRING', 'description' => 'Nama lengkap guru'],
                        'nip' => ['type' => 'STRING', 'description' => 'NIP guru'],
                        'jenis_kelamin' => ['type' => 'STRING', 'description' => 'Jenis kelamin (L/P)'],
                        'mata_pelajaran' => ['type' => 'STRING', 'description' => 'Mata pelajaran'],
                        'jabatan' => ['type' => 'STRING', 'description' => 'Jabatan'],
                        'no_hp' => ['type' => 'STRING', 'description' => 'Nomor HP'],
                        'status' => ['type' => 'STRING', 'description' => 'Status (aktif/nonaktif)'],
                    ],
                    'required' => ['id'],
                ],
            ],
            [
                'name' => 'get_kelas',
                'description' => 'Mendapatkan data kelas berdasarkan ID atau nama',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'id' => ['type' => 'INTEGER', 'description' => 'ID kelas (opsional)'],
                        'nama' => ['type' => 'STRING', 'description' => 'Nama kelas untuk pencarian (opsional)'],
                    ],
                ],
            ],
            [
                'name' => 'update_kelas',
                'description' => 'Mengupdate data kelas (nama, tingkat, jurusan)',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'id' => ['type' => 'INTEGER', 'description' => 'ID kelas'],
                        'nama' => ['type' => 'STRING', 'description' => 'Nama kelas'],
                        'tingkat' => ['type' => 'STRING', 'description' => 'Tingkat (10/11/12)'],
                        'jurusan' => ['type' => 'STRING', 'description' => 'Jurusan'],
                        'status' => ['type' => 'STRING', 'description' => 'Status (aktif/nonaktif)'],
                    ],
                    'required' => ['id'],
                ],
            ],
            [
                'name' => 'cari_siswa',
                'description' => 'Mencari siswa berdasarkan nama, NISN, atau NIS',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'keyword' => ['type' => 'STRING', 'description' => 'Kata kunci pencarian (nama, NISN, atau NIS)'],
                    ],
                    'required' => ['keyword'],
                ],
            ],
            [
                'name' => 'cari_guru',
                'description' => 'Mencari guru berdasarkan nama atau NIP',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'keyword' => ['type' => 'STRING', 'description' => 'Kata kunci pencarian (nama atau NIP)'],
                    ],
                    'required' => ['keyword'],
                ],
            ],
            [
                'name' => 'statistik_data',
                'description' => 'Mendapatkan statistik data (total siswa, guru, kelas, user)',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => (object) [],
                ],
            ],
        ];

        // Filter tools berdasarkan allowed tools untuk role ini
        return array_values(array_filter($allTools, function ($tool) use ($allowedTools) {
            return in_array($tool['name'], $allowedTools);
        }));
    }
}
