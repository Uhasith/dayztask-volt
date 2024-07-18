<div class="w-full mx-auto p-5 lg:px-10 lg:py-5">
    <div>
        <div class="text-end">
            <x-filament::button type="button" class="my-4" wire:click="$dispatch('openProjectCreateDrawer')">
                Create New Project
            </x-filament::button>
        </div>

        <div class="max-w-lg mx-auto mt-4 md:-mt-14">
            <x-wui-card shadow="xl" rounded="3xl"
                class="px-4 bg-[#eaddd7] dark:bg-[#eaddd7] transform hover:scale-105 transition duration-700 ease-in-out">
                <a href="{{ route('projects.show.all') }}" wire:navigate>
                    <div>
                        <h5
                            class="mb-2 mx-auto text-center text-xl max-w-60 font-bold tracking-tight text-gray-900 dark:text-gray-900 truncate">
                            {{ 'All Projects' }}
                        </h5>

                        <div class="flex items-center justify-center">
                            <img class="w-32 max-h-12 rounded-md" src="{{ asset('assets/images/logo.png') }}"
                                alt="project-logo" />
                        </div>
                    </div>
                </a>
            </x-wui-card>
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
