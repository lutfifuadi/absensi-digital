<div>
    <div class="das-panel">
        <div class="das-panel__head">
            <div class="das-panel__title">
                <span class="das-panel__icon-dot --primary"></span>
                Asisten AI
                <small class="ms-2" style="font-size:0.7rem;font-weight:400;text-transform:none;letter-spacing:0;color:rgba(255,255,255,0.4);">Tanyakan atau edit data dengan bahasa alami</small>
            </div>
            <div>
                <button type="button" class="das-btn das-btn--ghost-danger" wire:click="clearChat" wire:confirm="Hapus semua riwayat chat?" title="Hapus Riwayat">
                    <i class="ti tabler-trash"></i> Hapus Riwayat
                </button>
            </div>
        </div>

        <div class="das-panel__body chat-panel-body" x-data x-ref="chatContainer" id="chatPanelBody">
            <div class="mb-auto">
                @if(count($messages) === 0)
                    <div class="text-center py-5">
                        <i class="ti tabler-message-chatbot chat-empty-icon"></i>
                        <h5 class="mt-2" style="color:rgba(255,255,255,0.3);">Mulai percakapan dengan AI</h5>
                        <p style="color:rgba(255,255,255,0.25);font-size:0.85rem;">Tanyakan data siswa, minta edit data, atau lihat statistik</p>
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <span class="das-chip --primary">Cari siswa</span>
                            <span class="das-chip --success">Edit data guru</span>
                            <span class="das-chip --warning">Statistik</span>
                        </div>
                    </div>
                @endif
            </div>

            <div wire:loading.class="opacity-50" id="messagesContainer">
                @foreach($messages as $index => $msg)
                    <div class="d-flex mb-2 {{ $msg['role'] === 'user' ? 'justify-content-end' : 'justify-content-start' }}" 
                         @if($msg['role'] === 'assistant' && $index === count($messages) - 1) 
                            x-data="{ 
                                fullText: '{{ str_replace(["\r", "\n"], ['\r', '\n'], addslashes($msg['message'])) }}', 
                                displayedText: '',
                                currentIndex: 0,
                                speed: 20,
                                init() {
                                    if (window.lastProcessedMessageId === '{{ $msg['id'] }}') {
                                        this.displayedText = this.fullText;
                                        return;
                                    }
                                    window.lastProcessedMessageId = '{{ $msg['id'] }}';
                                    this.type();
                                },
                                type() {
                                    if (this.currentIndex < this.fullText.length) {
                                        this.displayedText += this.fullText.charAt(this.currentIndex);
                                        this.currentIndex++;
                                        
                                        // Render markdown
                                        let markdownContainer = $el.querySelector('.markdown-body');
                                        if (markdownContainer) {
                                            markdownContainer.innerHTML = marked.parse(this.displayedText);
                                        }
                                        
                                        setTimeout(() => this.type(), this.speed);
                                        scrollToBottom();
                                    }
                                }
                            }" 
                         @endif>
                        <div class="chat-bubble-{{ $msg['role'] === 'user' ? 'user' : 'ai' }}">
                            <div class="mb-0 markdown-body" style="font-size: 0.85rem;">
                                @if($msg['role'] === 'user' || $index < count($messages) - 1)
                                    {!! \Illuminate\Support\Str::markdown($msg['message']) !!}
                                @endif
                            </div>
                            <small class="chat-ts chat-ts-{{ $msg['role'] === 'user' ? 'user' : 'ai' }}">{{ $msg['time'] }}</small>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($isLoading)
                <div class="d-flex justify-content-start mb-2">
                    <div class="chat-bubble-ai">
                        <div class="d-flex align-items-center gap-2">
                            <div class="typing-indicator">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <small style="color:rgba(255,255,255,0.5); font-weight: 600;">Asisten Mansaba sedang mengetik...</small>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if($hasError)
            <div class="mx-3 mb-2">
                <div class="das-alert das-alert--warning" role="alert">
                    <i class="ti tabler-alert-triangle das-alert__icon"></i>
                    <span>{{ $errorMessage }}</span>
                </div>
            </div>
        @endif

        <div class="chat-footer">
            <form wire:submit="send">
                <div class="input-group">
                    <input type="text" class="form-control das-form-control" placeholder="Ketik pesan..." wire:model="message" wire:keydown.enter="send" {{ $isLoading ? 'disabled' : '' }}>
                    <button class="das-btn --primary" type="submit" {{ $isLoading ? 'disabled' : '' }}>
                        <i class="ti tabler-send"></i>
                        <span class="d-none d-sm-inline ms-50">Kirim</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        window.lastProcessedMessageId = null;

        document.addEventListener('livewire:initialized', function () {
            Livewire.on('chat-message-sent', function () {
                scrollToBottom();
            });
            Livewire.on('chat-message-received', function () {
                scrollToBottom();
            });
            Livewire.on('chat-cleared', function () {
                scrollToBottom();
            });
        });

        function scrollToBottom() {
            setTimeout(function () {
                var container = document.getElementById('chatPanelBody');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }, 100);
        }
    </script>
</div>
