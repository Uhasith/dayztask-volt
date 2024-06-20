<div class="w-full mx-auto p-5 lg:px-10 lg:py-5">
    <div>
        <div class="text-end">
            <x-filament::button type="button" class="my-4" wire:click="$dispatch('openProjectCreateDrawer')">
                Create New Project
            </x-filament::button>
        </div>

        <div class="grid grid-cols-12 gap-4 overflow-auto py-5">
            @foreach ($projects as $key => $project)
                <div class="col-span-12 md:col-span-6 lg:col-span-4 cursor-pointer">
                    <livewire:pages.project.components.project-card :project="$project" :key="'project-' . $key" />
                </div>
            @endforeach

        </div>

        <div class="col-span-12 mt-5">
            <x-filament::pagination :paginator="$projects" extreme-links />
        </div>
    </div>
    <livewire:pages.project.components.project-drawer />
</div>
