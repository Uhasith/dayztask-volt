<?php

namespace App\Livewire\Global;

use Livewire\Component;

class Timer extends Component
{
    public $trackedTime;

    public $timerRunning;

    public $taskId;

    public function render()
    {
        return view('livewire.global.timer');
    }
}
