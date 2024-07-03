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
            <livewire:pages.task.components.task-card taskId="{{ $task['id'] }}" :key="'task-card-' . $task['uuid']" />
        @endforeach
    </div>

    <div class="col-span-12 mt-10">
        <x-filament::pagination :paginator="$tasks" extreme-links />
    </div>
</div>
