<div class="position-relative">
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0 text-muted">
            <i class="ti ti-search"></i>
        </span>
        <input 
            type="text" 
            wire:model.live.debounce.300ms="query" 
            class="form-control border-start-0 ps-0" 
            placeholder="Cari nama guru dari daftar..."
            autocomplete="off"
        >
        @if($query)
            <button class="btn btn-outline-secondary border-start-0 border" type="button" wire:click="$set('query', '')">
                <i class="ti ti-x"></i>
            </button>
        @endif
    </div>

    @if($showDropdown && count($gurus) > 0)
        <div class="position-absolute w-100 mt-1 bg-white border rounded shadow-sm z-3" style="max-height: 200px; overflow-y: auto;">
            <div class="list-group list-group-flush">
                @foreach($gurus as $guru)
                    <button 
                        type="button" 
                        class="list-group-item list-group-item-action py-2 px-3 text-start"
                        wire:click="selectGuru({{ $guru->id }}, '{{ addslashes($guru->nama) }}')"
                    >
                        {{ $guru->nama }}
                    </button>
                @endforeach
            </div>
        </div>
    @elseif($showDropdown && strlen($query) >= 2)
        <div class="position-absolute w-100 mt-1 bg-white border rounded shadow-sm z-3 p-2 text-center text-muted small">
            Tidak ditemukan di daftar guru. Nama akan disimpan sebagai teks manual.
        </div>
    @endif
</div>