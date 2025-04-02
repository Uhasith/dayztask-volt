<div x-data="{
    check: $wire.entangle('needToCheck'),
    confirm: $wire.entangle('needToConfirm'),
    proof: $wire.entangle('needProof'),
    followup: $wire.entangle('needFollowUp'),
    billable: $wire.entangle('isBillable'),
    newSubs: $wire.entangle('subtasks'),
    oldAddedSubs: $wire.entangle('oldSubtasks').live,
    oldSubsRemoved: $wire.entangle('oldRemovedSubTasks'),
}" class="w-full mx-auto p-5 lg:px-10 lg:py-5 min-h-[calc(100vh - 5rem)]">
    <form wire:submit="updateTask">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 py-5">
            <div>
                <div class="mb-4 flex items-center gap-8">
                    @if ($task->status == 'todo')
                        <x-wui-badge flat purple label="To Do" />
                    @elseif ($task->status == 'doing')
                        <x-wui-badge flat blue label="Doing" />
                    @else
                        <x-wui-badge flat green label="Done" />
                    @endif
                    <a href="{{ route('projects.show', $task->project->uuid) }}" wire:navigate>
                        <x-wui-badge flat red label="Project : {{ $task->project->title }}" />
                    </a>
                </div>
                @if ($task?->rejected_reasons && count($task?->rejected_reasons) > 0)
                    <div>
                        <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                            role="alert">
                            <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                            </svg>
                            <span class="sr-only">Danger</span>
                            <div>
                                <span class="font-medium">Task Rejected Reasons :</span>
                                <ul class="mt-1.5 list-disc list-inside">
                                    @foreach ($task->rejected_reasons as $key => $reason)
                                        <li wire:key="reason-{{ $key }}">
                                            {{ $reason }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 items-center justify-between gap-4">
                    <x-wui-input icon="document-text" label="Task Name" placeholder="Task Name" wire:model="name" />
                    <x-wui-select id="assignTo" icon="user" label="Assign To" placeholder="Assign To"
                        class="w-[50%]" wire:model="assigned_users" multiselect>
                        @foreach ($teamMembers as $key => $member)
                            <x-wui-select.user-option
                                src="{{ !empty($member['profile_photo_path']) ? asset('storage/' . $member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
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
                <x-filament::section collapsible :collapsed="false" persist-collapsed id="task-details"
                    icon="heroicon-m-document-text" icon-size="md" class="filament-wui-dark md:mt-8">
                    <x-slot name="heading">
                        Task Approval Details
                    </x-slot>
                    <div>
                        <div>
                            <div>
                                <x-wui-toggle id="needToCheck" x-model="check"
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
                                                src="{{ !empty($member['profile_photo_path']) ? asset('storage/' . $member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
                                                label="{{ $member['name'] }}" value="{{ $member['id'] }}"
                                                wire:key="{{ 'filter-option-' . $key }}" />
                                        @endforeach
                                    </x-wui-select>

                                    <div class="mt-4 md:mt-8">
                                        <x-wui-toggle id="needProof" x-model="proof" left-label="Need proof ?"
                                            x-on:click="proof = !proof; if (proof === false) { $wire.set('proof_method', null); }"
                                            name="needProof" />
                                    </div>
                                    <div x-show="proof" x-transition
                                        class="grid grid-cols-3 items-center gap-3 md:gap-5 mt-3 md:mt-6">
                                        <x-wui-radio id="needProof" wire:model="proof_method" label="Screenshot"
                                            value="screenshot" md />
                                        <x-wui-radio id="needProof" wire:model="proof_method"
                                            label="Multiple screenshots" value="multiple_screenshots" md />
                                        <x-wui-radio id="needProof" wire:model="proof_method" label="Video"
                                            value="video" md />
                                        <x-wui-radio id="needProof" wire:model="proof_method" label="Comment"
                                            value="comment" md />
                                        <x-wui-radio id="needProof" wire:model="proof_method" label="File"
                                            value="file" md />
                                    </div>
                                    <div class="mt-4 md:mt-8">
                                        <x-wui-toggle id="needToConfirm" x-model="confirm"
                                            left-label="Does this card need to be checked again ?"
                                            x-on:click="confirm = !confirm; if (confirm === false) { $wire.set('confirm_by_user_id', null); }"
                                            name="needToConfirm" />
                                    </div>
                                    <div x-show="confirm" x-transition class="mt-2 md:mt-4">
                                        <x-wui-select id="confirmUser" icon="user" label="Should confirmed by"
                                            placeholder="Should confirmed by" wire:model="confirm_by_user_id">
                                            @foreach ($teamMembers as $key => $member)
                                                <x-wui-select.user-option
                                                    src="{{ !empty($member['profile_photo_path']) ? asset('storage/' . $member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
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
                                    <x-wui-toggle id="needFollowUp" x-model="followup"
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
                                                    src="{{ !empty($member['profile_photo_path']) ? asset('storage/' . $member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
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
                                    <x-wui-toggle id="isBillable" x-model="billable" left-label="Is this Billable ?"
                                        x-on:click="billable = !billable; if (billable === false) { $wire.set('invoice_reference', null); }"
                                        name="isBillable" />
                                </div>

                                <div x-show="billable" x-transition>
                                    <x-wui-card class="mt-3 md:mt-6" shadow="base">
                                        <x-wui-input icon="banknotes" label="Invoice Reference"
                                            placeholder="Invoice Reference" wire:model="invoice_reference"
                                            class="mt-4" />
                                    </x-wui-card>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-filament::section>
                <div class="my-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-md md:text-lg font-medium text-gray-900 dark:text-gray-100 my-4">Comments
                            Section</h2>
                        <x-wui-button xs secondary label="Add comment"
                            x-on:click="$wire.set('addingComment', true);" />
                    </div>
                    @if ($addingComment)
                        <livewire:global.add-comment :model="$task" />
                    @endif
                    @foreach ($task->comments as $comment)
                        <div class="flex items-start gap-2.5 mb-3" wire:key="task-comment-{{ $comment->id }}">
                            <img class="w-8 h-8 rounded-full"
                                src="{{ $comment->user->profile_photo_url ?? asset('assets/images/no-user-image.png') }}"
                                alt="Jese image">
                            <div class="flex flex-col gap-1 w-full max-w-sm">
                                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $comment->user->name }}
                                    </span>
                                    <span
                                        class="text-sm font-normal text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>

                                @if ($editingCommentId === $comment->id)
                                    <!-- Editing Mode -->
                                    <div
                                        class="flex flex-col leading-1.5 p-4 border-gray-200 bg-gray-100 rounded-e-xl rounded-es-xl dark:bg-gray-700">
                                        <textarea class="w-full p-2 text-sm border rounded-md dark:bg-gray-800 dark:text-white" wire:model="editingContent"></textarea>
                                        <button type="button"
                                            class="mt-2 text-sm font-semibold text-white bg-blue-500 rounded-md px-4 py-2"
                                            wire:click="updateComment({{ $comment->id }})">
                                            Save
                                        </button>
                                        <button type="button"
                                            class="mt-2 text-sm font-semibold text-gray-500 dark:text-gray-300"
                                            wire:click="cancelEdit">
                                            Cancel
                                        </button>
                                    </div>
                                @else
                                    <!-- View Mode -->
                                    <div
                                        class="flex flex-col leading-1.5 p-4 border-gray-200 bg-gray-100 rounded-e-xl rounded-es-xl dark:bg-gray-700">
                                        <p class="text-sm font-normal text-gray-900 dark:text-white">
                                            {!! preg_replace('/(@\w+)/', '<span class="highlight-at">$1</span>', nl2br(e($comment->content))) !!}
                                        </p>
                                    </div>

                                    @if (auth()->id() === $comment->user_id)
                                        <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                            <span
                                                class="text-sm font-normal text-blue-500 dark:text-blue-400 cursor-pointer"
                                                wire:click="editComment({{ $comment->id }}, '{{ $comment->content }}')">
                                                Edit
                                            </span>
                                            <span
                                                class="text-sm font-normal text-red-500 dark:text-red-400 cursor-pointer"
                                                wire:click="deleteCommentConfirm({{ $comment->id }})">
                                                Delete
                                            </span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="md:mt-5">
                <x-filament::section collapsible :collapsed="false" persist-collapsed id="task-details"
                    icon="heroicon-m-document-text" icon-size="md" class="filament-wui-dark">
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
                        <x-file-pond wire:model="attachments" multiple :uploads="$old_attachments" />
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
                                <x-wui-mini-button rounded secondary icon="plus"
                                    x-on:click="newSubs.push({ subTask: ''});"
                                    x-tooltip.placement.bottom.raw="Add Sub Task" />
                            </div>
                        </div>
                        <template x-for="(sub, index) in oldAddedSubs" :key="'sub-task-' + index">
                            <div x-show="oldAddedSubs.length > 0"
                                class="flex items-center justify-between py-2 gap-5">
                                <template x-if="!sub.is_completed">
                                    <p x-text="sub.subTask" class="border border-gray-400 p-2 rounded-sm"></p>
                                    {{-- <x-wui-input icon="document-text" placeholder="Add Sub Task"
                                        x-model="sub.subTask" /> --}}
                                </template>

                                <template x-if="sub.is_completed">
                                    <p x-text="sub.subTask" class="line-through text-gray-500"></p>
                                </template>

                                <!-- Checkbox for completion status -->
                                <div class="flex items-center gap-4">
                                    <x-wui-checkbox id="label" x-model="sub.is_completed" :value="true" />
                                    <x-mary-icon name="m-trash"
                                        x-on:click="if(sub.old === true) { oldSubsRemoved.push(sub.id)};   oldAddedSubs.splice(index, 1);"
                                        class="text-red-400 hover:text-red-600 cursor-pointer w-6 h-6"
                                        x-tooltip.placement.top.raw="Remove" />
                                </div>
                            </div>
                        </template>
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
        <div class="text-center mt-5 md:mt-16">
            <x-wui-button type="submit" amber label="Submit" spinner="createTask" class="w-[50%] mx-auto" />
        </div>
    </form>
</div>
