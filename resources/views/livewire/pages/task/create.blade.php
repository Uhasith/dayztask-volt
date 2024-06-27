<div x-init="initFlowbite();" class="w-full mx-auto p-5 lg:px-10 lg:py-5">
    <form wire:submit="createTask">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <div class="grid grid-cols-1 md:grid-cols-2 items-center justify-between gap-4">
                    <x-wui-input icon="document-text" label="Task Name" placeholder="Task Name" wire:model="name" />
                    <x-wui-select icon="user" label="Assign To" placeholder="Assign To" class="w-[50%]"
                        wire:model="assignTo" multiselect>
                        @foreach ($teamMembers as $key => $member)
                            <x-wui-select.user-option
                                src="{{ !empty($member['profile_photo_path']) ? asset($member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
                                label="{{ $member['name'] }}" value="{{ $member['id'] }}"
                                wire:key="{{ 'filter-option-' . $key }}" />
                        @endforeach
                    </x-wui-select>
                    <x-wui-select icon="exclamation-circle" label="Priority" placeholder="Priority"
                        wire:model="priority" :clearable="false">
                        <x-wui-select.option label="Low" value="low" />
                        <x-wui-select.option label="Medium" value="medium" />
                        <x-wui-select.option label="High" value="high" />
                    </x-wui-select>
                    <x-wui-datetime-picker wire:model="deadline" label="Deadline" placeholder="Task Deadline"
                        without-time :disable-past-dates="true" />

                    <x-wui-number label="Estimate Time" placeholder="0" min="0" wire:model="time" />
                    <x-wui-select icon="clock" label="Time Range" placeholder="Minutes" wire:model="range"
                        :clearable="false">
                        <x-wui-select.option label="Minute" value="minute" />
                        <x-wui-select.option label="Hour" value="hour" />
                        <x-wui-select.option label="Day" value="day" />
                    </x-wui-select>
                </div>
                <div class="mt-5 md:mt-10">
                    <x-filament::section collapsible id="task-sub-tasks" icon="heroicon-m-document-text" icon-size="md"
                        class="filament-wui-dark">
                        <x-slot name="heading">
                            Manage Sub Tasks
                        </x-slot>

                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-md md:text-lg font-medium text-gray-900 dark:text-gray-100">Add Sub Tasks
                            </h2>
                            <x-wui-mini-button rounded secondary icon="plus" wire:click="addSubTask" spinner="addSubTask" x-tooltip.placement.bottom.raw="Add Sub Task" />
                            {{-- <x-wui-mini-button type="button" secondary rounded wire:loading.attr="disabled"
                                x-tooltip.placement.bottom.raw="Add Sub Task" wire:click="addSubTask">
                                <x-mary-icon wire:loading.remove wire:target="addSubTask" name="o-plus" />
                                <x-mary-loading wire:loading wire:target="addSubTask" class="loading-ring" />
                            </x-wui-mini-button> --}}
                        </div>
                        @foreach ($newSubTasks as $key => $subTask)
                            <div wire:key="{{ 'new-sub-task-' . $key }}"
                                class="flex items-center justify-between py-2 gap-5">
                                <x-wui-input icon="document-text" placeholder="Add Sub Task"
                                    wire:model="newSubTasks.{{ $key }}.subTask" />
                                <button type="button" wire:loading.attr="disabled" wire:target="removeSubTask"
                                    x-tooltip.placement.top.raw="Remove"
                                    wire:click="removeSubTask({{ $key }})">
                                    <x-mary-icon name="m-trash"
                                        class="text-red-400 hover:text-red-600 cursor-pointer w-6 h-6" />
                                </button>
                            </div>
                        @endforeach
                    </x-filament::section>
                </div>
            </div>
            <div class="md:mt-5">
                <x-filament::section collapsible :collapsed="empty($description) || empty($attachments)" id="task-details" icon="heroicon-m-document-text"
                    icon-size="md" class="filament-wui-dark">
                    <x-slot name="heading">
                        Task Description and Attachments
                    </x-slot>

                    <div>
                        <label for="description"
                            class="text-sm font-medium disabled:opacity-60 text-gray-700 dark:text-gray-400 invalidated:text-negative-600 dark:invalidated:text-negative-700">Description</label>
                        <x-quill-editor wire:model="description" />
                    </div>
                    <div class="mt-2 px-1">
                        <label for="file"
                            class="text-sm font-medium disabled:opacity-60 text-gray-700 dark:text-gray-400 invalidated:text-negative-600 dark:invalidated:text-negative-700">Attachments</label>
                        <x-file-pond wire:model="attachments" multiple />
                    </div>
                </x-filament::section>
            </div>
        </div>
        <div class="text-center"> 
            <x-wui-button type="submit" amber label="Amber" class="w-[50%] mx-auto mt-10" />
        </div>
    </form>
</div>
