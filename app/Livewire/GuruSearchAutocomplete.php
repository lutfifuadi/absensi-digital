<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Guru;

class GuruSearchAutocomplete extends Component
{
    public $query = '';
    public $gurus = [];
    public $selectedId = null;
    public $selectedName = '';
    public $showDropdown = false;

    public function mount($initialId = null, $initialName = '')
    {
        $this->selectedId = $initialId;
        $this->selectedName = $initialName;
        $this->query = $initialName;
    }

    public function updatedQuery()
    {
        if (strlen($this->query) >= 2) {
            $this->gurus = Guru::where('nama', 'like', '%' . $this->query . '%')
                ->take(5)
                ->get();
            $this->showDropdown = true;
        } else {
            $this->gurus = [];
            $this->showDropdown = false;
        }
        
        // If typing, assume not selected from list anymore unless they select it again
        if ($this->query !== $this->selectedName) {
            $this->selectedId = null;
            $this->dispatch('guruSelected', null, $this->query);
        }
    }

    public function selectGuru($id, $nama)
    {
        $this->selectedId = $id;
        $this->selectedName = $nama;
        $this->query = $nama;
        $this->showDropdown = false;
        $this->dispatch('guruSelected', $id, $nama);
    }

    public function render()
    {
        return view('livewire.guru-search-autocomplete');
    }
}