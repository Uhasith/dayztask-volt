<div class="z-50">
    <x-filament::modal slide-over sticky-header width="3xl" :close-by-clicking-away="false" id="create-project">
        <x-slot name="heading">
            <label class="text-lg">Create New Project</label>
        </x-slot>

        <div class="w-full min-h-64 overflow-y-auto px-4">
            @if ($showDrawer)
                <livewire:pages.project.components.create-project />
            @endif
        </div>
    </x-filament::modal>
</div>
