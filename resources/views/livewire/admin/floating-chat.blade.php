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
            background: rgba(15, 23, 42, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: slideUp 0.3s ease;
            backdrop-filter: blur(16px);
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
            flex-shrink: 0;
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
            background: rgba(15, 23, 42, 0.8);
        }

        .floating-chat-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding: 10px 14px;
            background: rgba(15, 23, 42, 0.95);
            flex-shrink: 0;
        }

        .msg-bubble {
            max-width: 85%;
            padding: 10px 14px;
            border-radius: 5px;
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
            background: linear-gradient(135deg, #7367f0, #9e95f5);
            color: #fff;
            border-radius: 5px;
            align-self: flex-end;
        }
        .msg-ai {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 5px;
            align-self: flex-start;
            backdrop-filter: blur(6px);
        }
        .msg-ai p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.4rem;
        }
        .markdown-body p:last-child {
            margin-bottom: 0;
        }
        .markdown-body ul {
            margin-bottom: 0.4rem;
            padding-left: 1.1rem;
        }
        .markdown-body li {
            margin-bottom: 0.2rem;
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
            border: 2px solid rgba(15, 23, 42, 0.98);
        }

        /* Floating input styling */
        .floating-chat-footer .das-form-control {
            background: rgba(255, 255, 255, 0.04) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #f1f5f9 !important;
            border-radius: 5px !important;
            font-size: 13px !important;
            transition: border-color 0.2s, box-shadow 0.2s !important;
        }
        .floating-chat-footer .das-form-control:focus {
            border-color: rgba(115, 103, 240, 0.5) !important;
            box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.1) !important;
            background: rgba(255, 255, 255, 0.07) !important;
        }
        .floating-chat-footer .das-btn--primary {
            background: #7367f0;
            color: white;
            border: 1px solid #7367f0;
            padding: 4px 12px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
            transition: all 0.18s ease;
            cursor: pointer;
        }
        .floating-chat-footer .das-btn--primary:hover {
            background: #6259e8;
        }

        /* Floating empty state */
        .floating-empty-icon {
            font-size: 2.5rem;
            color: rgba(255, 255, 255, 0.1);
        }
        .floating-empty-text {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.85rem;
            margin-top: 8px;
        }

        /* Floating alert */
        .floating-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 11px;
            background: rgba(255, 159, 67, 0.06);
            border: 1px solid rgba(255, 159, 67, 0.2);
            color: rgba(255, 255, 255, 0.75);
            border-radius: 5px;
            backdrop-filter: blur(6px);
        }
        .floating-alert i {
            color: #ff9f43;
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Floating chip badges */
        .floating-chip {
            display: inline-flex;
            align-items: center;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .floating-chip.--primary {
            background: rgba(115, 103, 240, 0.15);
            color: #a5a2f7;
        }
        .floating-chip.--primary:hover {
            background: rgba(115, 103, 240, 0.25);
        }
        .floating-chip.--success {
            background: rgba(40, 199, 111, 0.15);
            color: #28c76f;
        }
        .floating-chip.--success:hover {
            background: rgba(40, 199, 111, 0.25);
        }
        .floating-chip.--warning {
            background: rgba(255, 159, 67, 0.15);
            color: #ff9f43;
        }
        .floating-chip.--warning:hover {
            background: rgba(255, 159, 67, 0.25);
        }

        /* Trash button in floating header */
        .floating-trash-btn {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            padding: 2px 6px;
            border-radius: 4px;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .floating-trash-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        /* Typing Indicator Floating */
        .typing-indicator {
            display: flex;
            gap: 2px;
        }
        .typing-indicator span {
            width: 3px;
            height: 3px;
            background: #7367f0;
            border-radius: 50%;
            animation: typingBounce 1.4s infinite ease-in-out both;
        }
        .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
        .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typingBounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
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

        /* Markdown override agar font tidak kebesaran */
        .markdown-body {
            font-size: 13px !important;
        }
        .markdown-body p, 
        .markdown-body li, 
        .markdown-body ul, 
        .markdown-body ol,
        .markdown-body span {
            font-size: 13px !important;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.9);
        }
        .markdown-body h1,
        .markdown-body h2,
        .markdown-body h3,
        .markdown-body h4,
        .markdown-body h5,
        .markdown-body h6 {
            font-size: 14px !important;
            font-weight: 700;
            margin-top: 8px;
            margin-bottom: 4px;
            color: #fff !important;
        }
        .markdown-body ul, .markdown-body ol {
            padding-left: 1.2rem;
            margin-bottom: 8px;
        }
    </style>

    @auth
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
                        <h6><i class="ti tabler-message-chatbot me-1"></i> Asisten {{ $schoolName }}</h6>
                        <small>👤 {{ $roleLabel }}</small>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="floating-trash-btn" wire:click="clearChat" title="Hapus riwayat">
                            <i class="ti tabler-trash" style="font-size: 16px;"></i>
                        </button>
                        <button class="btn-close btn-close-white" style="font-size: 12px;" wire:click="toggle"></button>
                    </div>
                </div>

                <div class="floating-chat-body" id="floatingChatBody">
                    @if(count($messages) === 0 && !$isLoading)
                        <div class="text-center py-4">
                            <i class="ti tabler-message-chatbot floating-empty-icon"></i>
                            <p class="floating-empty-text">Ada yang bisa dibantu?</p>
                            <div class="d-flex justify-content-center gap-1 flex-wrap mt-2">
                                {{-- Chip untuk semua role --}}
                                <span class="floating-chip --primary" wire:click="sendQuickChip('📚 Panduan Fitur')">📚 Panduan Fitur</span>
                                <span class="floating-chip --success" wire:click="sendQuickChip('❓ Cara Absen')">❓ Cara Absen</span>

                                {{-- Chip spesifik berdasarkan role --}}
                                @if(in_array($userRole, ['guru', 'wali_kelas', 'staff_tu']))
                                    <span class="floating-chip --warning" wire:click="sendQuickChip('👨‍🏫 Fitur untuk Guru')">👨‍🏫 Untuk Guru</span>
                                @elseif(in_array($userRole, ['siswa']))
                                    <span class="floating-chip --warning" wire:click="sendQuickChip('👨‍🎓 Fitur untuk Siswa')">👨‍🎓 Untuk Siswa</span>
                                @elseif(in_array($userRole, ['orang_tua']))
                                    <span class="floating-chip --warning" wire:click="sendQuickChip('👨‍👩‍👧‍👦 Fitur untuk Orang Tua')">👨‍👩‍👧‍👦 Untuk Orang Tua</span>
                                @else
                                    <span class="floating-chip --warning" wire:click="sendQuickChip('📊 Statistik')">📊 Statistik</span>
                                @endif
                            </div>
                        </div>
                    @endif

        @foreach($messages as $index => $msg)
                            <div class="d-flex {{ $msg['role'] === 'user' ? 'justify-content-end' : 'justify-content-start' }}"
                                 @if($msg['role'] === 'assistant' && $index === count($messages) - 1)
                                    x-data="{
                                        fullText: '{{ str_replace(["\r", "\n"], ['\r', '\n'], addslashes($msg['message'])) }}',
                                        displayedText: '',
                                        currentIndex: 0,
                                        speed: 15,
                                        init() {
                                            if (window.lastProcessedFloatingId === '{{ $msg['id'] }}') {
                                                this.displayedText = this.fullText;
                                                return;
                                            }
                                            window.lastProcessedFloatingId = '{{ $msg['id'] }}';
                                            this.type();
                                        },
                                        type() {
                                            if (this.currentIndex < this.fullText.length) {
                                                this.displayedText += this.fullText.charAt(this.currentIndex);
                                                this.currentIndex++;
                                                
                                                // Render markdown
                                                $el.querySelector('.markdown-body').innerHTML = marked.parse(this.displayedText);
                                                
                                                setTimeout(() => this.type(), this.speed);
                                                scrollFloating();
                                            }
                                        }
                                    }"
                                 @endif>
                                <div class="msg-bubble {{ $msg['role'] === 'user' ? 'msg-user' : 'msg-ai' }}">
                                    <div class="mb-0 markdown-body" style="font-size: 13px;">
                                        @if($msg['role'] === 'user' || $index < count($messages) - 1)
                                            {!! \Illuminate\Support\Str::markdown($msg['message']) !!}
                                        @endif
                                    </div>
                                    @if($msg['role'] === 'assistant' && !empty($msg['source']))
                                        <div style="font-size: 10px; color: rgba(255,255,255,0.35); border-top: 1px solid rgba(255,255,255,0.06); margin-top: 6px; padding-top: 4px;">
                                            📖 Sumber: {{ $msg['source'] }}
                                        </div>
                                    @endif
                                    <small style="color: {{ $msg['role'] === 'user' ? 'rgba(255,255,255,0.7)' : 'rgba(255,255,255,0.4)' }};">{{ $msg['time'] }}</small>
                                </div>
                            </div>
                        @endforeach

                        @if($isLoading)
                            <div class="d-flex justify-content-start">
                                <div class="msg-bubble msg-ai">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="typing-indicator">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                        </div>
                                        <small style="color:rgba(255,255,255,0.5); font-weight: 600;">Mengetik...</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($hasError)
                        <div class="px-3 py-1">
                            <div class="floating-alert">
                                <i class="ti tabler-alert-triangle"></i>
                                <span>{{ $errorMessage }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="floating-chat-footer">
                        <form wire:submit="send">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control das-form-control" placeholder="Ketik pesan..." wire:model="message" wire:keydown.enter="send" {{ $isLoading ? 'disabled' : '' }}>
                                <button class="das-btn--primary" type="submit" {{ $isLoading ? 'disabled' : '' }}>
                                    <i class="ti tabler-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        window.lastProcessedFloatingId = null;

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
