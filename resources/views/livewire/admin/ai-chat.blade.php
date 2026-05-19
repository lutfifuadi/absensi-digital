<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h4 class="card-title mb-0">Asisten AI</h4>
                <small class="text-muted">Tanyakan atau edit data dengan bahasa alami</small>
            </div>
            <div>
                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="clearChat" wire:confirm="Hapus semua riwayat chat?" title="Hapus Riwayat">
                    <i class="ti tabler-trash me-50"></i>Hapus Riwayat
                </button>
            </div>
        </div>

        <div class="card-body" style="height: 500px; overflow-y: auto; display: flex; flex-direction: column;" x-data x-ref="chatContainer">
            <div class="mb-auto" wire:ignore>
                @if(count($messages) === 0)
                    <div class="text-center py-5">
                        <i class="ti tabler-message-chatbot" style="font-size: 4rem; color: #ddd;"></i>
                        <h5 class="mt-2 text-muted">Mulai percakapan dengan AI</h5>
                        <p class="text-muted small">Tanyakan data siswa, minta edit data, atau lihat statistik</p>
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <span class="badge bg-light text-dark p-2">"Cari siswa"</span>
                            <span class="badge bg-light text-dark p-2">"Edit data guru"</span>
                            <span class="badge bg-light text-dark p-2">"Statistik"</span>
                        </div>
                    </div>
                @endif
            </div>

            <div wire:loading.class="opacity-50" id="messagesContainer">
                @foreach($messages as $msg)
                    <div class="d-flex mb-2 {{ $msg['role'] === 'user' ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="p-3 rounded" style="max-width: 80%; {{ $msg['role'] === 'user' ? 'background-color: #7367f0; color: white; border-radius: 18px 18px 4px 18px;' : 'background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 18px 18px 18px 4px;' }}">
                            <p class="mb-0" style="white-space: pre-wrap; {{ $msg['role'] === 'user' ? 'color: white;' : '' }}">{{ $msg['message'] }}</p>
                            <small class="{{ $msg['role'] === 'user' ? 'text-white-50' : 'text-muted' }}" style="font-size: 10px;">{{ $msg['time'] }}</small>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($isLoading)
                <div class="d-flex justify-content-start mb-2">
                    <div class="p-3 rounded" style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 18px 18px 18px 4px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                            <small class="text-muted">AI sedang berpikir...</small>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if($hasError)
            <div class="mx-3 mb-2">
                <div class="alert alert-warning alert-dismissible py-1 px-2 mb-0" role="alert">
                    <small><i class="ti tabler-alert-triangle me-50"></i>{{ $errorMessage }}</small>
                    <button type="button" class="btn-close py-1" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="card-footer border-top">
            <form wire:submit="send">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Ketik pesan..." wire:model="message" wire:keydown.enter="send" {{ $isLoading ? 'disabled' : '' }}>
                    <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" {{ $isLoading ? 'disabled' : '' }}>
                        <i class="ti tabler-send" wire:loading.remove></i>
                        <span wire:loading><span class="spinner-border spinner-border-sm" role="status"></span></span>
                        <span class="d-none d-sm-inline ms-50">Kirim</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
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
                var container = document.querySelector('.card-body');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }, 100);
        }
    </script>
</div>
