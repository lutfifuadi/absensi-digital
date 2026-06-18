<?php

namespace App\Livewire\Admin;

use App\Models\ChatLog;
use App\Services\GeminiService;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class AiChat extends Component
{
    public string $message = '';
    public array $messages = [];
    public bool $isLoading = false;
    public bool $hasError = false;
    public string $errorMessage = '';

    protected $listeners = [];

    public function mount()
    {
        $this->loadHistory();
    }

    public function loadHistory()
    {
        try {
            $logs = ChatLog::where('user_id', auth()->id())
                ->orderBy('created_at', 'asc')
                ->limit(100)
                ->get();

            $this->messages = $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'role' => $log->role,
                    'message' => $log->message,
                    'time' => $log->created_at->format('H:i'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            $this->hasError = true;
            $this->errorMessage = 'Gagal memuat riwayat chat.';
        }
    }

    public function send()
    {
        // Tingkatkan execution time khusus untuk request ini
        @set_time_limit(120);

        $this->validate([
            'message' => 'required|string|max:2000',
        ]);

        $this->isLoading = true;
        $this->hasError = false;
        $this->errorMessage = '';

        $userMessage = trim($this->message);
        $user = auth()->user();

        // 1. Tampilkan bubble user dulu & simpan ke DB
        $this->messages[] = [
            'id' => uniqid('msg_'),
            'role' => 'user',
            'message' => $userMessage,
            'time' => now()->format('H:i'),
        ];

        ChatLog::create([
            'user_id' => $user->id,
            'role' => 'user',
            'message' => $userMessage,
        ]);

        $this->message = '';
        
        // 2. Dispatch event agar UI scroll dan memicu pemrosesan AI setelah pesan terkirim
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
            $tools = $gemini->getToolDefinitions();
            $response = $gemini->sendWithTools($lastUserMessage, $tools, $history);
            
            // ... (logika proses respons sama seperti sebelumnya)

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

            // Simpan respons AI ke database
            ChatLog::create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'message' => $replyText,
                'metadata' => [
                    'has_tool_calls' => isset($textParts) && isset($parts) && count($parts) > 1,
                    'has_error' => $hasError,
                ],
            ]);

            // Tampilkan bubble AI — langsung aktifkan tombol agar user bisa bertanya lagi
            $this->messages[] = [
                'id' => uniqid('resp_'),
                'role' => 'assistant',
                'message' => $replyText,
                'time' => now()->format('H:i'),
            ];
            
            // Buka kunci tombol kirim segera setelah pesan AI muncul (typing animasi tetap jalan di frontend)
            $this->isLoading = false;
            $this->dispatch('chat-message-received');

            if ($hasError) {
                $this->hasError = true;
                $this->errorMessage = 'AI mengalami kesalahan saat memproses permintaan.';
            }
        } catch (\Exception $e) {
            Log::error('AI Chat send error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            // Simpan error ke database
            ChatLog::create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'message' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi.',
                'metadata' => ['error' => $e->getMessage()],
            ]);

            $this->messages[] = [
                'id' => uniqid('resp_'),
                'role' => 'assistant',
                'message' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi.',
                'time' => now()->format('H:i'),
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
            $this->dispatch('chat-cleared');
        } catch (\Exception $e) {
            $this->hasError = true;
            $this->errorMessage = 'Gagal menghapus riwayat chat.';
        }
    }

    public function render()
    {
        return view('livewire.admin.ai-chat');
    }
}
