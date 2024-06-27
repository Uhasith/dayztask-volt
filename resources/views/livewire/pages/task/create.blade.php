<div x-init="initFlowbite();" class="w-full mx-auto p-5 lg:px-10 lg:py-5">
    <form wire:submit="create">
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
            </div>
            <div class="mt-5">
                <x-filament::section collapsible :collapsed="empty($description) && empty($attachments)" id="task-details" icon="heroicon-m-document-text"
                    icon-size="md" :style="'background-color: #1E293B;'">
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
    </form>
</div>
