<div x-data="{ bgColor: '#eaddd7', fontColor: '{{ $project['font_color'] ?? '#000000' }}' }">
    <x-wui-card shadow="xl" rounded="3xl" x-bind:style="{ backgroundColor: bgColor }"
        class="px-4 transform hover:scale-105 transition duration-700 ease-in-out">
        <div class="absolute z-10 block top-3 right-6 cursor-pointer"
            wire:click="$dispatch('openProjectEditDrawer', { id: {{ $project['id'] }} })">
            <x-mary-icon name="o-bars-3" class="w-8 h-8 text-black/50" />
        </div>
        <a href="{{ route('projects.show', $project['uuid']) }}" wire:navigate>
            <div>
                <h5 x-bind:style="{ color: fontColor }" class="mb-2 mx-auto text-center text-xl max-w-60 font-bold tracking-tight text-gray-900 dark:text-white truncate">
                    {{ $project['title'] ?? 'No Title' }}
                </h5>

                <div class="flex items-center justify-center">
                    @if (isset($project['company_logo']))
                       <img class="w-32 max-h-12 rounded-md" src="{{ asset('assets/images/logo.png') }}" alt="project-logo" />
                    @endif
                </div>
            </div>
        </a>
    </x-wui-card>
</div>
