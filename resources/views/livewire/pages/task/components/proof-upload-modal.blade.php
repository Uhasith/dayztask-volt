<div class="z-50">
    <x-filament::modal id="proof-upload-modal" icon="heroicon-s-cloud-arrow-up" icon-color="primary" alignment="center"
        width="2xl">
        <x-slot name="heading">
            Upload Proof
        </x-slot>

        <div class="mt-2 px-1">
            @if ($task)
                @if ($task->proof_method == 'screenshot' || $task->proof_method == 'multiple_screenshots')
                    <x-file-pond wire:model="attachments" multiple />
                @elseif ($task->proof_method == 'file')
                    <x-file-pond wire:model="attachments" accept="application/pdf,.doc,.docx,.rtf,.txt" />
                @elseif ($task->proof_method == 'video')
                    <x-wui-textarea label="Video Link" placeholder="https://www.youtube.com/watch?v=..."
                        wire:model="proof_video_link" />
                @else
                    <x-wui-textarea label="Comment" placeholder="Comment" wire:model="proof_comment" />
                @endif
            @endif
        </div>

        <x-slot name="footer">
            @if ($task)
                @if (
                    $task->proof_method == 'screenshot' ||
                        $task->proof_method == 'multiple_screenshots' ||
                        $task->proof_method == 'file')
                    <x-validation-errors class="mb-4 ml-4" />
                @else
                    <div class="flex justify-end">
                        <x-filament::button color="primary" type="button" wire:click="uploadProof">
                            Upload
                        </x-filament::button>
                    </div>
                @endif
            @endif
        </x-slot>
    </x-filament::modal>
</div>
