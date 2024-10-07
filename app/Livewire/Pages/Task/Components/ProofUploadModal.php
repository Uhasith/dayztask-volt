<?php

namespace App\Livewire\Pages\Task\Components;

use Livewire\Attributes\On;
use Livewire\Component;

class ProofUploadModal extends Component
{
    public $taskId;

    #[On('open-proof-modal')]
    public function onOpenModal($modalId, $taskId)
    {
        $this->taskId = $taskId;
        $this->dispatch('open-modal', id: $modalId);

    }

    public function render()
    {
        return view('livewire.pages.task.components.proof-upload-modal');
    }
}
