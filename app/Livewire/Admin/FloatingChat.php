<?php

namespace App\Livewire\Admin;

use App\Models\ChatLog;
use App\Services\GeminiService;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class FloatingChat extends Component
{
    public bool $isOpen = false;
    public string $message = '';
    public array $messages = [];
    public bool $isLoading = false;
    public bool $hasError = false;
    public string $errorMessage = '';
    public int $unreadCount = 0;
    public string $userRole = '';
    public string $roleLabel = '';
    public string $schoolName = 'MAN 1 Kota Bandung';

    public function mount()
    {
        if (auth()->check()) {
            $user = auth()->user();
            $this->userRole = $user->role;
            $this->roleLabel = $this->getRoleLabel($user->role);
            $this->schoolName = $this->getSchoolName();
            $this->loadHistory();
        }
    }

    /**
     * Dapatkan label untuk ditampilkan berdasarkan role.
     */
    protected function getRoleLabel(string $role): string
    {
        $labels = [
            'super_admin'   => 'Super Admin',
            'admin_sekolah' => 'Admin Sekolah',
            'operator'      => 'Operator',
            'guru'          => 'Guru',
            'wali_kelas'    => 'Wali Kelas',
            'staff_tu'      => 'Staff TU',
            'siswa'         => 'Siswa',
            'orang_tua'     => 'Orang Tua',
            'piket'         => 'Piket',
        ];

        return $labels[$role] ?? ucfirst($role);
    }

    /**
     * Ambil nama sekolah/lembaga dari pengaturan dengan urutan prioritas:
     * 1. nama_lembaga (primary)
     * 2. nama_sekolah (fallback pertama)
     * 3. SCHOOL_NAME dari .env (fallback kedua)
     * 4. config('app.name') (fallback terakhir)
     */
    protected function getSchoolName(): string
    {
        try {
            $nama = \App\Models\Pengaturan::where('key', 'nama_lembaga')->value('value');
            if (!empty($nama)) {
                return $nama;
            }

            $nama = \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value');
            if (!empty($nama)) {
                return $nama;
            }

            return env('SCHOOL_NAME', config('app.name', 'MAN 1 Kota Bandung'));
        } catch (\Exception $e) {
            return env('SCHOOL_NAME', config('app.name', 'MAN 1 Kota Bandung'));
        }
    }

    public function toggle()
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen) {
            $this->unreadCount = 0;
            $this->dispatch('chat-opened');
            $this->loadHistory();
        }
    }

    public function loadHistory()
    {
        try {
            $logs = ChatLog::where('user_id', auth()->id())
                ->orderBy('created_at', 'asc')
                ->limit(50)
                ->get();

            $this->messages = $logs->map(function ($log) {
                $metadata = is_string($log->metadata) ? json_decode($log->metadata, true) : ($log->metadata ?? []);
                return [
                    'id' => $log->id,
                    'role' => $log->role,
                    'message' => $log->message,
                    'time' => $log->created_at->format('H:i'),
                    'source' => $metadata['source_title'] ?? ($metadata['source'] ?? null),
                ];
            })->toArray();
        } catch (\Exception $e) {
            $this->hasError = true;
            $this->errorMessage = 'Gagal memuat riwayat.';
        }
    }

    /**
     * Kirim quick chip sebagai pesan.
     */
    public function sendQuickChip($chipMessage)
    {
        $this->message = $chipMessage;
        $this->send();
    }

    public function send()
    {
        $this->validate([
            'message' => 'required|string|max:2000',
        ]);

        $this->isLoading = true;
        $this->hasError = false;
        $this->errorMessage = '';

        $userMessage = trim($this->message);
        $user = auth()->user();

        $this->messages[] = [
            'id' => uniqid('msg_'),
            'role' => 'user',
            'message' => $userMessage,
            'time' => now()->format('H:i'),
        ];

        // Simpan pesan user ke database
        ChatLog::create([
            'user_id' => $user->id,
            'role' => 'user',
            'message' => $userMessage,
        ]);

        $this->message = '';
        $this->dispatch('chat-message-sent');
        $this->dispatch('process-ai-response');
    }

    #[On('process-ai-response')]
    public function processAi()
    {
        $user = auth()->user();
        // Ambil pesan terakhir user dari array messages
        $userMessages = array_filter($this->messages, fn($m) => $m['role'] === 'user');
        $lastUserMessage = end($userMessages)['message'];

        try {
            // Panggil Gemini langsung
            $gemini = app(GeminiService::class);
            $history = $gemini->getHistory($user->id, 20);
            $tools = $gemini->getToolDefinitions($user->role);
            $response = $gemini->sendWithTools($lastUserMessage, $tools, $history, $user->role);

            $replyText = 'Maaf, terjadi kesalahan saat memproses pesan Anda.';
            $hasError = false;

            if (isset($response['candidates'][0]['content']['parts'])) {
                $parts = $response['candidates'][0]['content']['parts'];
                $textParts = [];

                foreach ($parts as $part) {
                    if (isset($part['text'])) {
                        $textParts[] = $part['text'];
                    }
                }

                $replyText = !empty($textParts) ? implode("\n\n", $textParts) : 'Maaf, AI tidak memberikan respons teks.';
                $hasError = isset($response['error']) && $response['error'];
            } elseif (isset($response['error'])) {
                $replyText = $response['error'];
                $hasError = true;
            }

            // Deteksi source dari tool call metadata
            $sourceTitle = null;
            if (isset($response['_toolMetadata']['sources'])) {
                $sourceTitle = implode(', ', $response['_toolMetadata']['sources']);
            }

            // Simpan respons AI ke database
            ChatLog::create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'message' => $replyText,
                'metadata' => [
                    'has_tool_calls' => isset($textParts) && isset($parts) && count($parts) > 1,
                    'has_error' => $hasError,
                    'source_title' => $sourceTitle,
                ],
            ]);

            $this->messages[] = [
                'id' => uniqid('resp_'),
                'role' => 'assistant',
                'message' => $replyText,
                'time' => now()->format('H:i'),
                'source' => $sourceTitle,
            ];
            
            // Buka kunci tombol kirim segera
            $this->isLoading = false;
            $this->dispatch('chat-message-received');

            if ($hasError) {
                $this->hasError = true;
                $this->errorMessage = 'AI mengalami kesalahan.';
            }
        } catch (\Exception $e) {
            Log::error('FloatingChat send error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            ChatLog::create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'message' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi.',
                'metadata' => ['error' => $e->getMessage(), 'source_title' => null],
            ]);

            $this->messages[] = [
                'id' => uniqid('resp_'),
                'role' => 'assistant',
                'message' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi.',
                'time' => now()->format('H:i'),
                'source' => null,
            ];
            
            // Buka kunci tombol kirim meskipun error
            $this->isLoading = false;
            $this->dispatch('chat-message-received');
            
            $this->hasError = true;
            $this->errorMessage = 'Kesalahan sistem: ' . $e->getMessage();
        }
    }

    public function clearChat()
    {
        try {
            ChatLog::where('user_id', auth()->id())->delete();
            $this->messages = [];
        } catch (\Exception $e) {
            $this->hasError = true;
            $this->errorMessage = 'Gagal menghapus riwayat.';
        }
    }

    public function render()
    {
        return view('livewire.admin.floating-chat');
    }
}
