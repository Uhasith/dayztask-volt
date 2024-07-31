<div x-init="initFlowbite();" x-data="{
    check: $wire.entangle('needToCheck'),
    confirm: $wire.entangle('needToConfirm'),
    proof: $wire.entangle('needProof'),
    followup: $wire.entangle('needFollowUp'),
    billable: $wire.entangle('isBillable'),
    newSubs: $wire.entangle('subtasks')
}"
    class="w-full mx-auto p-5 lg:px-10 lg:py-5 min-h-[calc(100vh - 5rem)]">
    <form wire:submit="createTask">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <div class="grid grid-cols-1 md:grid-cols-2 items-center justify-between gap-4">
                    <x-wui-input icon="document-text" label="Task Name" placeholder="Task Name" wire:model="name" />
                    <x-wui-select id="assignTo" icon="user" label="Assign To" placeholder="Assign To"
                        class="w-[50%]" wire:model="assigned_users" multiselect>
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
                        without-time without-timezone :disable-past-dates="true" />

                    <x-wui-number label="Estimate Time" placeholder="0" min="1" wire:model="estimate_time" />
                    <x-wui-select icon="clock" label="Time Range" placeholder="Minutes" wire:model="range"
                        :clearable="false">
                        <x-wui-select.option label="Minute" value="minute" />
                        <x-wui-select.option label="Hour" value="hour" />
                        <x-wui-select.option label="Day" value="day" />
                    </x-wui-select>
                </div>

                <div class="md:mt-8 px-1">
                    <div>
                        <x-wui-toggle id="needToCheck" x-bind:checked="check"
                            left-label="Does this task need to be checked once done ?"
                            x-on:click="check = !check; if (check === false) { confirm = false; $wire.set('check_by_user_id', null);
                        $wire.set('confirm_by_user_id', null); $wire.set('proof_method', null); proof = false; }"
                            name="needToCheck" />
                    </div>

                    <div x-show="check" x-transition>
                        <x-wui-card class="mt-3 md:mt-6" shadow="base">
                            <x-wui-select id="checkUser" icon="user" label="Should checked by"
                                placeholder="Should checked by" wire:model="check_by_user_id">
                                @foreach ($teamMembers as $key => $member)
                                    <x-wui-select.user-option
                                        src="{{ !empty($member['profile_photo_path']) ? asset($member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
                                        label="{{ $member['name'] }}" value="{{ $member['id'] }}"
                                        wire:key="{{ 'filter-option-' . $key }}" />
                                @endforeach
                            </x-wui-select>

                            <div class="mt-4 md:mt-8">
                                <x-wui-toggle id="needProof" x-bind:checked="proof" left-label="Need proof ?"
                                    x-on:click="proof = !proof; if (proof === false) { $wire.set('proof_method', null); }"
                                    name="needProof" />
                            </div>

                            <div x-show="proof" x-transition
                                class="grid grid-cols-3 items-center gap-3 md:gap-5 mt-3 md:mt-6">
                                <x-wui-radio id="needProof" wire:model="proof_method" label="Screenshot"
                                    value="screenshot" md />
                                <x-wui-radio id="needProof" wire:model="proof_method" label="Multiple screenshots"
                                    value="multiple_screenshots" md />
                                <x-wui-radio id="needProof" wire:model="proof_method" label="Video" value="video"
                                    md />
                                <x-wui-radio id="needProof" wire:model="proof_method" label="Comment" value="comment"
                                    md />
                                <x-wui-radio id="needProof" wire:model="proof_method" label="File" value="file"
                                    md />
                            </div>

                            <div class="mt-4 md:mt-8">
                                <x-wui-toggle id="needToConfirm" x-bind:checked="confirm"
                                    left-label="Does this card need to be checked again ?"
                                    x-on:click="confirm = !confirm; if (confirm === false) { $wire.set('confirm_by_user_id', null); }"
                                    name="needToConfirm" />
                            </div>

                            <div x-show="confirm" x-transition class="mt-2 md:mt-4">
                                <x-wui-select id="confirmUser" icon="user" label="Should confirmed by"
                                    placeholder="Should confirmed by" wire:model="confirm_by_user_id">
                                    @foreach ($teamMembers as $key => $member)
                                        <x-wui-select.user-option
                                            src="{{ !empty($member['profile_photo_path']) ? asset($member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
                                            label="{{ $member['name'] }}" value="{{ $member['id'] }}"
                                            wire:key="{{ 'filter-option-' . $key }}" />
                                    @endforeach
                                </x-wui-select>
                            </div>

                        </x-wui-card>
                    </div>

                </div>

                <div class="grid grid-col-1 md:grid-cols-2 gap-5">

                    <div>
                        <div class="mt-4 md:mt-8">
                            <x-wui-toggle id="needFollowUp" x-bind:checked="followup"
                                left-label="Is there a follow up on this task ?"
                                x-on:click="followup = !followup; if (followup === false) { $wire.set('follow_up_user_id', null);
                        $wire.set('confirm_by_user_id', null); $wire.set('proof_method', null); }"
                                name="needFollowUp" />
                        </div>

                        <div x-show="followup" x-transition>
                            <x-wui-card class="mt-3 md:mt-6" shadow="base">
                                <x-wui-select id="followUpUser" icon="user" label="Who should follow up ?"
                                    placeholder="Who should follow up ?" wire:model="follow_up_user_id">
                                    @foreach ($teamMembers as $key => $member)
                                        <x-wui-select.user-option
                                            src="{{ !empty($member['profile_photo_path']) ? asset($member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
                                            label="{{ $member['name'] }}" value="{{ $member['id'] }}"
                                            wire:key="{{ 'filter-option-' . $key }}" />
                                    @endforeach
                                </x-wui-select>
                                <x-wui-input icon="document-text" label="Follow Up Message"
                                    placeholder="What should be followed up ?" wire:model="follow_up_message"
                                    class="mt-4" />
                            </x-wui-card>
                        </div>
                    </div>

                    <div>
                        <div class="mt-4 md:mt-8">
                            <x-wui-toggle id="isBillable" x-bind:checked="billable" left-label="Is this Billable ?"
                                x-on:click="billable = !billable; if (billable === false) { $wire.set('invoice_reference', null); }"
                                name="isBillable" />
                        </div>

                        <div x-show="billable" x-transition>
                            <x-wui-card class="mt-3 md:mt-6" shadow="base">
                                <x-wui-input icon="banknotes" label="Invoice Reference"
                                    placeholder="Invoice Reference" wire:model="invoice_reference" class="mt-4" />
                            </x-wui-card>
                        </div>
                    </div>



                </div>

            </div>
            <div class="md:mt-5">
                <x-filament::section collapsible :collapsed="false" persist-collapsed id="task-details" icon="heroicon-m-document-text"
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

                <div class="mt-5 md:mt-10">
                    <x-filament::section collapsible id="task-sub-tasks" icon="heroicon-m-document-text"
                        icon-size="md" class="filament-wui-dark">
                        <x-slot name="heading">
                            Manage Sub Tasks
                        </x-slot>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-md md:text-lg font-medium text-gray-900 dark:text-gray-100">Add Sub Tasks
                            </h2>
                            <div class="flex item-center justify-center gap-5">
                                {{-- <x-mary-loading wire:loading wire:target="addSubTask"
                                    class="text-primary-500 loading-bars w-5 h-5 my-auto" /> --}}
                                <x-wui-mini-button rounded secondary icon="plus"
                                    x-on:click="newSubs.push({ subTask: ''});"
                                    x-tooltip.placement.bottom.raw="Add Sub Task" />
                            </div>
                            {{-- <x-wui-mini-button type="button" secondary rounded wire:loading.attr="disabled"
                                x-tooltip.placement.bottom.raw="Add Sub Task" wire:click="addSubTask">
                                <x-mary-icon wire:loading.remove wire:target="addSubTask" name="o-plus" />
                                <x-mary-loading wire:loading wire:target="addSubTask" class="loading-ring" />
                            </x-wui-mini-button> --}}
                        </div>
                        <template x-for="(sub, index) in newSubs" :key="'sub-task-' + index">
                            <div x-show="newSubs.length > 0" class="flex items-center justify-between py-2 gap-5">
                                <x-wui-input icon="document-text" placeholder="Add Sub Task" x-model="sub.subTask" />
                                <x-mary-icon name="m-trash" x-on:click="newSubs.splice(index, 1);"
                                    class="text-red-400 hover:text-red-600 cursor-pointer w-6 h-6"
                                    x-tooltip.placement.top.raw="Remove" />
                            </div>
                        </template>
                    </x-filament::section>
                </div>


            </div>
        </div>
        <div class="text-center mt-5 md:mt-10">
            <x-wui-button type="submit" amber label="Submit" spinner="createTask" class="w-[50%] mx-auto" />
        </div>
    </form>
</div>
