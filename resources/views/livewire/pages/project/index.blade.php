<div class="w-full mx-auto p-5 lg:px-10 lg:py-5">
    <div>
        <div class="text-end">
            <x-filament::button type="button" class="my-4" wire:click="$dispatch('openProjectCreateDrawer')">
                Create New Project
            </x-filament::button>
        </div>

        <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-4 py-5">
            @foreach ($projects as $key => $project)
                <livewire:pages.project.components.project-card :project="$project" :key="'project-' . $project['uuid']" />
            @endforeach

        </div>

        <div class="col-span-12 mt-5">
            <x-filament::pagination :paginator="$projects" extreme-links />
        </div>
    </div>
    <livewire:pages.project.components.project-drawer />
</div>
