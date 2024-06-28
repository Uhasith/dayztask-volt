<div class="w-full mx-auto p-5 lg:px-10 lg:py-5" x-data>
    <div class="grid grid-cols-12 items-center">
        <div class="col-span-12 md:col-span-3 px-2 md:mt-0">
            <x-wui-input icon="magnifying-glass" placeholder="Search Task ..." />
        </div>
        <div class="col-span-12 md:col-span-5 mt-4 md:mt-0 px-2 md:px-5 flex items-center justify-end gap-5 text-end">
            <x-wui-select placeholder="Filter By" class="w-[50%]" wire:model.live="filterBy">
                @foreach ($teamMembers as $key => $member)
                    <x-wui-select.user-option
                        src="{{ !empty($member['profile_photo_path']) ? asset($member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
                        label="{{ $member['name'] }}" value="{{ $member['id'] }}"
                        wire:key="{{ 'filter-option-' . $key }}" />
                @endforeach
            </x-wui-select>
            <x-wui-select placeholder="Sort By" class="w-[50%]" wire:model.live="sortBy">
                @foreach ($teamMembers as $key => $member)
                    <x-wui-select.user-option
                        src="{{ !empty($member['profile_photo_path']) ? asset($member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
                        label="{{ $member['name'] }}" value="{{ $member['id'] }}"
                        wire:key="{{ 'sort-option-' . $key }}" />
                @endforeach
            </x-wui-select>
        </div>
        <div class="col-span-12 md:col-span-4 mt-4 md:mt-0 flex items-center justify-end gap-4 md:gap-6">
            <x-wui-button primary label="My Tasks" x-tooltip.placement.bottom.raw="Show only My Tasks" />
            <x-wui-button positive label="Completed" x-tooltip.placement.bottom.raw="Show Completed Tasks" />
            <x-wui-mini-button info icon="document-text" x-tooltip.placement.bottom.raw="Project Notes" />
            <a href="{{ route('projects.tasks.create', $project['uuid']) }}" wire:navigate>
                <x-wui-mini-button primary icon="plus" x-tooltip.placement.bottom.raw="Create New Task" />
            </a>
        </div>
    </div>
    <div class="mt-5 md:mt-10 grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($tasks as $task)
            <x-wui-card rounded="3xl" wire:key="task-{{ $task['uuid'] }}">
                <div class="flex items-center justify-between">
                    <p class="text-lg max-w-[80%] truncate">{{ $task['name'] }}</p>
                    <div class="flex items-center gap-3">
                        <x-wui-badge flat lime label="3" class="cursor-pointer"
                            x-tooltip.placement.top.raw="Comments Count">
                            <x-slot name="prepend" class="relative flex items-center w-2 h-2 mr-1">
                                <span
                                    class="absolute inline-flex w-full h-full rounded-full opacity-75 bg-lime-500 animate-ping"></span>

                                <span class="relative inline-flex w-2 h-2 rounded-full bg-lime-500"></span>
                            </x-slot>
                        </x-wui-badge>
                        <x-wui-button 2xs positive label="Mark as done" />
                        @if (true)
                            <x-mary-icon name="m-pause" class=" text-blue-400 hover:text-blue-600 cursor-pointer"
                                x-tooltip.placement.top.raw="Stop Tracking" />
                        @else
                            <x-mary-icon name="m-play" class=" text-blue-400 hover:text-blue-600 cursor-pointer"
                                x-tooltip.placement.top.raw="Start Tracking" />
                        @endif

                        <x-mary-icon name="m-pencil-square" class="text-green-400 hover:text-green-600 cursor-pointer"
                            x-tooltip.placement.top.raw="Update" />
                        <x-mary-icon name="m-trash" class="text-red-400 hover:text-red-600 cursor-pointer"
                            x-tooltip.placement.top.raw="Delete"
                            wire:click="deleteTaskDialog('{{ $task['uuid'] }}')" />
                    </div>
                </div>
                <div class="flex items-center justify-between py-1">
                    <p class="text-md max-w-[50%] truncate">Project : {{ $task['project']['title'] }}</p>
                    <div class="px-1">
                        <x-wui-badge flat amber label="04:25:00" />
                    </div>
                </div>
                <div class="flex items-center justify-between py-1">
                    <p class="text-xs">Deadline : 2024/06/30</p>
                    <div class="flex gap-2 items-center">
                        <x-wui-avatar 2xs src="{{ asset('assets/images/no-user-image.png') }}" />
                        <x-wui-avatar 2xs src="{{ asset('assets/images/no-user-image.png') }}" />
                        <x-wui-avatar 2xs src="{{ asset('assets/images/no-user-image.png') }}" />
                    </div>
                    <div class="px-1">
                        @if ($task['priority'] === 'high')
                            <x-wui-badge flat red label="High" />
                        @elseif ($task['priority'] === 'medium')
                            <x-wui-badge flat sky label="Medium" />
                        @else
                            <x-wui-badge flat purple label="Low" />
                        @endif
                    </div>
                </div>
            </x-wui-card>
        @endforeach
    </div>

    <div class="col-span-12 mt-10">
        <x-filament::pagination :paginator="$tasks" extreme-links />
    </div>
</div>
