<?php

namespace App\Livewire\Global;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class Timer extends Component
{
    public $trackedTime, $timerRunning, $taskId;

    public function render()
    {
        return view('livewire.global.timer');
    }
}
