<div>
    <style>
        .floating-chat-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9997;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7367f0, #9e95f5);
            color: #fff;
            border: none;
            box-shadow: 0 4px 20px rgba(115, 103, 240, 0.4);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .floating-chat-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 28px rgba(115, 103, 240, 0.5);
        }
        .floating-chat-btn .ti {
            font-size: 24px;
        }

        .floating-chat-window {
            position: fixed;
            bottom: 92px;
            right: 24px;
            z-index: 9997;
            width: 360px;
            height: 520px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .floating-chat-header {
            background: linear-gradient(135deg, #7367f0, #9e95f5);
            color: #fff;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .floating-chat-header h6 {
            margin: 0;
            color: #fff;
            font-weight: 600;
        }
        .floating-chat-header small {
            opacity: 0.85;
            color: #fff;
        }
        .floating-chat-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }
        .floating-chat-header .btn-close:hover {
            opacity: 1;
        }

        .floating-chat-body {
            flex: 1;
            overflow-y: auto;
            padding: 14px;
            background: #f8f9fa;
        }

        .floating-chat-footer {
            border-top: 1px solid #e9ecef;
            padding: 10px 14px;
            background: #fff;
        }

        .msg-bubble {
            max-width: 85%;
            padding: 10px 14px;
            border-radius: 16px;
            margin-bottom: 8px;
            word-wrap: break-word;
        }
        .msg-bubble p {
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
        }
        .msg-bubble small {
            font-size: 9px;
        }
        .msg-user {
            background: #7367f0;
            color: #fff;
            border-radius: 16px 16px 4px 16px;
            align-self: flex-end;
        }
        .msg-ai {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 16px 16px 16px 4px;
            align-self: flex-start;
        }

        .floating-unread {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ea5455;
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border: 2px solid #fff;
        }

        @media (max-width: 480px) {
            .floating-chat-window {
                width: calc(100vw - 32px);
                right: 16px;
                bottom: 84px;
                height: 460px;
            }
            .floating-chat-btn {
                bottom: 16px;
                right: 16px;
            }
        }
    </style>

    @auth
        @if(in_array(auth()->user()->role, ['super_admin', 'admin_sekolah', 'operator']))
            <button class="floating-chat-btn" wire:click="toggle" title="Asisten AI">
                @if($isOpen)
                    <i class="ti tabler-x"></i>
                @else
                    <i class="ti tabler-message-chatbot"></i>
                    @if($unreadCount > 0)
                        <span class="floating-unread">{{ $unreadCount }}</span>
                    @endif
                @endif
            </button>

            @if($isOpen)
                <div class="floating-chat-window" wire:click.away="isOpen = false">
                    <div class="floating-chat-header">
                        <div>
                            <h6><i class="ti tabler-message-chatbot me-1"></i> Asisten AI</h6>
                            <small>Tanya atau edit data</small>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm p-0 me-2" style="color: rgba(255,255,255,0.8);" wire:click="clearChat" title="Hapus riwayat">
                                <i class="ti tabler-trash" style="font-size: 16px;"></i>
                            </button>
                            <button class="btn-close btn-close-white" style="font-size: 12px;" wire:click="toggle"></button>
                        </div>
                    </div>

                    <div class="floating-chat-body" id="floatingChatBody">
                        @if(count($messages) === 0 && !$isLoading)
                            <div class="text-center py-4">
                                <i class="ti tabler-message-chatbot" style="font-size: 2.5rem; color: #ddd;"></i>
                                <p class="text-muted small mt-2 mb-0">Ada yang bisa dibantu?</p>
                                <div class="d-flex justify-content-center gap-1 flex-wrap mt-2">
                                    <span class="badge bg-light text-dark" style="cursor:pointer; font-size:11px;" wire:click="$set('message', 'Cari siswa')">Cari siswa</span>
                                    <span class="badge bg-light text-dark" style="cursor:pointer; font-size:11px;" wire:click="$set('message', 'Statistik')">Statistik</span>
                                </div>
                            </div>
                        @endif

                        @foreach($messages as $msg)
                            <div class="d-flex {{ $msg['role'] === 'user' ? 'justify-content-end' : 'justify-content-start' }}">
                                <div class="msg-bubble {{ $msg['role'] === 'user' ? 'msg-user' : 'msg-ai' }}">
                                    <p style="color: {{ $msg['role'] === 'user' ? '#fff' : '#333' }};">{{ $msg['message'] }}</p>
                                    <small style="color: {{ $msg['role'] === 'user' ? 'rgba(255,255,255,0.7)' : '#aaa' }};">{{ $msg['time'] }}</small>
                                </div>
                            </div>
                        @endforeach

                        @if($isLoading)
                            <div class="d-flex justify-content-start">
                                <div class="msg-bubble msg-ai">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                                        <small class="text-muted">AI sedang berpikir...</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($hasError)
                        <div class="px-3 py-1">
                            <div class="alert alert-warning py-1 px-2 mb-0" style="font-size: 11px;">
                                <i class="ti tabler-alert-triangle me-1"></i>{{ $errorMessage }}
                            </div>
                        </div>
                    @endif

                    <div class="floating-chat-footer">
                        <form wire:submit="send">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" placeholder="Ketik pesan..." wire:model="message" wire:keydown.enter="send" {{ $isLoading ? 'disabled' : '' }} style="font-size: 13px;">
                                <button class="btn btn-primary" type="submit" {{ $isLoading ? 'disabled' : '' }} style="padding: 4px 12px;">
                                    <i class="ti tabler-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endif
    @endauth

    <script>
        document.addEventListener('livewire:initialized', function () {
            Livewire.on('chat-message-sent', function () { scrollFloating(); });
            Livewire.on('chat-message-received', function () { scrollFloating(); });
            Livewire.on('chat-opened', function () { scrollFloating(); });
        });

        function scrollFloating() {
            setTimeout(function () {
                var el = document.getElementById('floatingChatBody');
                if (el) el.scrollTop = el.scrollHeight;
            }, 100);
        }
    </script>
</div>
