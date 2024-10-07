<?php

namespace App\Livewire\Pages\Task\Components;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class ProofUploadModal extends Component
{
    use WithFileUploads;

    public $taskId;
    public $attachments = [];

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
