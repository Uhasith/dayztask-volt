<?php

namespace App\Livewire\Pages\Task\Components;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class ProofUploadModal extends Component
{

    #[On('open-modal')] 
    public function onOpenModal($modalId, $taskId)
    {

        Log::info($modalId);
        Log::info($taskId);

    }

    public function render()
    {
        return view('livewire.pages.task.components.proof-upload-modal');
    }
}
