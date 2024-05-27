<div class="z-50">
    <x-filament::modal slide-over sticky-header width="3xl" id="project-drawer">
        <x-slot name="heading">
            @if ($showCreateDrawer)
                <label class="text-lg">Create New Project</label>
            @elseif ($showEditDrawer && $project)
                <label class="text-lg">Edit Project</label>
            @endif
        </x-slot>

        <div class="w-full min-h-64 overflow-y-auto px-4">
            @if ($showCreateDrawer)
                <livewire:pages.project.components.create-project />
            @elseif ($showEditDrawer && $project)
                <livewire:pages.project.components.edit-project :record="$project" />
            @endif
        </div>
    </x-filament::modal>
</div>
