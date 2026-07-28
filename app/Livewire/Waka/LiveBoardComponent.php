<?php

namespace App\Livewire\Waka;

use App\Services\MonitoringService;
use Livewire\Component;

class LiveBoardComponent extends Component
{
    public string $jamFilter = 'all';
    public bool $isOffline = false;

    public function setJamFilter(string $jamFilter)
    {
        $this->jamFilter = $jamFilter;
    }

    public function render()
    {
        $service = app(MonitoringService::class);
        $liveData = $service->getLiveBoardData($this->jamFilter);

        return view('livewire.waka.live-board-component', [
            'liveData' => $liveData,
        ]);
    }
}
