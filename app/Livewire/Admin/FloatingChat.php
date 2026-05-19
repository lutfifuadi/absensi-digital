<?php

namespace App\Livewire\Admin;

use App\Models\ChatLog;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

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

        $this->messages[] = [
            'id' => uniqid('msg_'),
            'role' => 'user',
            'message' => $userMessage,
            'time' => now()->format('H:i'),
        ];

        $this->message = '';
        $this->dispatch('chat-message-sent');

        try {
            $response = Http::withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post(route('admin.ai-chat.send'), [
                'message' => $userMessage,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->messages[] = [
                    'id' => uniqid('resp_'),
                    'role' => 'assistant',
                    'message' => $data['message'] ?? 'Tidak ada respons.',
                    'time' => now()->format('H:i'),
                ];

                if (!$data['success']) {
                    $this->hasError = true;
                    $this->errorMessage = 'AI mengalami kesalahan.';
                }
            } else {
                $this->messages[] = [
                    'id' => uniqid('resp_'),
                    'role' => 'assistant',
                    'message' => 'Maaf, terjadi kesalahan komunikasi dengan server.',
                    'time' => now()->format('H:i'),
                ];
                $this->hasError = true;
                $this->errorMessage = 'Gagal terhubung ke server.';
            }
        } catch (\Exception $e) {
            $this->messages[] = [
                'id' => uniqid('resp_'),
                'role' => 'assistant',
                'message' => 'Maaf, terjadi kesalahan jaringan.',
                'time' => now()->format('H:i'),
            ];
            $this->hasError = true;
            $this->errorMessage = 'Kesalahan koneksi.';
        }

        $this->isLoading = false;
        $this->dispatch('chat-message-received');
    }

    public function clearChat()
    {
        try {
            Http::withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->delete(route('admin.ai-chat.clear'));

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
