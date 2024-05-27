<div>
    <div class="relative">
        <div class="absolute z-10 block top-3 right-4 cursor-pointer"
            wire:click="$dispatch('openProjectEditDrawer', { id: {{ $project['id'] }} })">
            <x-mary-icon name="o-bars-3" class="w-8 h-8 text-black/50" />
        </div>
        <div wire:click="send"
            class="rounded-xl shadow-xl transform hover:scale-105 transition duration-700 ease-in-out relative">
            <div style="background-color: {{ !empty($project['bg_color']) ? $project['bg_color'] : '#eaddd7' }}"
                class="rounded-xl flex flex-wrap items-start justify-start gap-x-4 gap-y-2 p-3 hover:shadow-md transition-shadow duration-300 ease-in-out">
                <div
                    class="w-full flex flex-col gap-2 items-center flex-wrap justify-center 
                text-[18px] font-semibold leading-10 tracking-tight transition-colors duration-300 ease-in-out">
                    <div
                        class="bg-white/30 backdrop-opacity-10 w-full h-28 flex flex-col items-center justify-center rounded-lg p-2">
                        <h5 class="mb-2 text-xl max-w-60 font-bold tracking-tight text-gray-900 dark:text-white truncate"
                            style="color: {{ !empty($project['font_color']) ? $project['font_color'] : '#000000' }}">
                            {{ $project['title'] ?? 'No Title' }}
                        </h5>

                        <div class="flex items-center justify-center">
                            @if (isset($project['company_logo']))
                                <img class="w-32 max-h-12 rounded-md" src="{{ $project['company_logo'] }}"
                                    alt="project-logo" />
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
