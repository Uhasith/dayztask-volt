<?php

namespace App\Livewire\Pages\Task\Components;

use App\Models\Task;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ProofUploadModal extends Component
{
    use WithFileUploads;

    #[Locked]
    public $taskId;

    public $task;

    public $proof_video_link;

    public $proof_comment;

    public $attachments = [];

    public function rules(): array
    {
        $rules = [];

        switch ($this->task->proof_method) {
            case 'screenshot':
                $rules['attachments'] = 'array|min:1';
                $rules['attachments.*'] = 'image|max:1024';
                break;

            case 'multiple_screenshots':
                $rules['attachments'] = 'array|min:2';
                $rules['attachments.*'] = 'image|max:1024';
                break;

            case 'file':
                $rules['attachments'] = 'array|min:1';
                $rules['attachments.*'] = 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/rtf,text/plain|max:1024';
                break;

            case 'video':
                $rules['proof_video_link'] = 'required|url';
                break;

            case 'comment':
                $rules['proof_comment'] = 'required|string';
                break;
        }

        return $rules;
    }

    public function updatedAttachments()
    {
        $this->validate();

        $optimizerChain = OptimizerChainFactory::create();

        foreach ($this->attachments as $attachment) {
            // Retrieve the actual path to the uploaded file
            $path = $attachment->getRealPath();

            // Optimize the image (if applicable)
            $optimizerChain->optimize($path);

            // Determine the collection name based on the proof method
            $collectionName = match ($this->task->proof_method) {
                'screenshot' => 'proof_screenshot',
                'multiple_screenshots' => 'proof_multiple_screenshots',
                'file' => 'proof_file',
                default => 'proof_screenshot', // Default value if needed
            };

            // Add the file to the media collection
            $this->task->addMedia($path)
                ->usingFileName($attachment->getClientOriginalName()) // Set original filename if needed
                ->toMediaCollection($collectionName);
        }

        // Dispatch an event to close the modal
        $this->dispatch('close-modal', id: 'proof-upload-modal');

        // Send a success notification
        app(NotificationService::class)->sendSuccessNotification('Task proof uploaded successfully');
    }

    // public function updatedProofVideoLink($value)
    // {
    //     $this->validate();
    // }

    // public function updatedProofComment($value)
    // {
    //     $this->validate();
    // }

    #[On('open-proof-modal')]
    public function onOpenModal($modalId, $taskId)
    {
        $this->taskId = $taskId;
        Log::info($taskId);
        $this->task = Task::where('id', $taskId)->first();
        $this->dispatch('open-modal', id: $modalId);
    }

    public function render()
    {
        return view('livewire.pages.task.components.proof-upload-modal');
    }
}
