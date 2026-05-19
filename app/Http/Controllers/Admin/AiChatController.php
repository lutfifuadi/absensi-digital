<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatLog;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function index()
    {
        return view('admin.ai-chat');
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = $request->user();
        $message = $validated['message'];

        ChatLog::create([
            'user_id' => $user->id,
            'role' => 'user',
            'message' => $message,
        ]);

        $history = $this->gemini->getHistory($user->id, 20);

        try {
            $tools = $this->gemini->getToolDefinitions();
            $response = $this->gemini->sendWithTools($message, $tools, $history);

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

                $replyText = !empty($textParts) ? implode("\n\n", $textParts) : $replyText;
                $hasError = isset($response['error']) && $response['error'];
            } elseif (isset($response['error'])) {
                $replyText = $response['error'];
                $hasError = true;
            }

            if (isset($response['candidates'][0]['content'])) {
                ChatLog::create([
                    'user_id' => $user->id,
                    'role' => 'assistant',
                    'message' => $replyText,
                    'metadata' => [
                        'has_tool_calls' => isset($textParts) && isset($parts) && !empty($textParts) && count($parts) > 1,
                        'has_error' => $hasError,
                    ],
                ]);
            }

            return response()->json([
                'success' => !$hasError,
                'message' => $replyText,
            ]);

        } catch (\Exception $e) {
            Log::error('AI Chat sendMessage error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            ChatLog::create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'message' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi.',
                'metadata' => ['error' => $e->getMessage()],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi.',
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $logs = ChatLog::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'role' => $log->role,
                    'message' => $log->message,
                    'created_at' => $log->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function clear(Request $request)
    {
        ChatLog::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat chat berhasil dihapus.',
        ]);
    }
}
