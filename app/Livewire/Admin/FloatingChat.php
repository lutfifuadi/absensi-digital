<?php

namespace App\Livewire\Admin;

use App\Models\ChatLog;
use App\Services\GeminiService;
use Livewire\Component;
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

    public function mount()
    {
        if (auth()->check()) {
            $this->loadHistory();
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
                return [
                    'id' => $log->id,
                    'role' => $log->role,
                    'message' => $log->message,
                    'time' => $log->created_at->format('H:i'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            $this->hasError = true;
            $this->errorMessage = 'Gagal memuat riwayat.';
        }
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

        $this->message = '';
        $this->dispatch('chat-message-sent');

        try {
            // Simpan pesan user ke database
            ChatLog::create([
                'user_id' => $user->id,
                'role' => 'user',
                'message' => $userMessage,
            ]);

            // Panggil Gemini langsung
            $gemini = app(GeminiService::class);
            $history = $gemini->getHistory($user->id, 20);
            $tools = $gemini->getToolDefinitions();
            $response = $gemini->sendWithTools($userMessage, $tools, $history);

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

            $this->messages[] = [
                'id' => uniqid('resp_'),
                'role' => 'assistant',
                'message' => $replyText,
                'time' => now()->format('H:i'),
            ];

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
                'metadata' => ['error' => $e->getMessage()],
            ]);

            $this->messages[] = [
                'id' => uniqid('resp_'),
                'role' => 'assistant',
                'message' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi.',
                'time' => now()->format('H:i'),
            ];
            $this->hasError = true;
            $this->errorMessage = 'Kesalahan sistem: ' . $e->getMessage();
        }

        $this->isLoading = false;
        $this->dispatch('chat-message-received');
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
