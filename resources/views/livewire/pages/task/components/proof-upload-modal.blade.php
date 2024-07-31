<div class="z-50">
    <x-filament::modal id="proof-upload-modal" icon="heroicon-s-cloud-arrow-up" icon-color="primary" alignment="center" width="2xl">
        <x-slot name="heading">
            Upload Proof
        </x-slot>


        <div class="mt-2 px-1">
            <x-file-pond wire:model="attachments" multiple />
        </div>


        <x-slot name="footer">
            {{-- Modal footer content --}}
        </x-slot>
        {{-- Modal content --}}
    </x-filament::modal>
</div>
