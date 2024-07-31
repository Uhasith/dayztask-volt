<div x-init="initFlowbite();">
    <x-wui-card rounded="3xl" class="cursor-pointer">
        <div class="flex items-center justify-between">
            <div class="max-w-[80%]">
                <p class="text-lg font-semibold truncate">{{ $task['name'] }}</p>
            </div>

            <div class="flex items-center gap-4">
                {{-- <div>
                    <x-wui-badge flat lime label="3" class="cursor-pointer"
                        x-tooltip.placement.top.raw="Comments Count">
                        <x-slot name="prepend" class="relative flex items-center size-2 mr-1">
                            <span
                                class="absolute inline-flex w-full h-full rounded-full opacity-75 bg-lime-500 animate-ping"></span>

                            <span class="relative inline-flex size-2 rounded-full bg-lime-500"
                                x-tooltip.placement.top.raw="Comments"></span>
                        </x-slot>
                    </x-wui-badge>
                </div> --}}

                @if ($task['users']->pluck('id')->contains(auth()->id()))

                    @if ($taskStatus == 'todo')
                        <div>
                            <x-wui-button 2xs positive label="Mark as done"
                                x-tooltip.placement.top.raw="Mark as completed" wire:click="markAsDone" />
                        </div>
                    @elseif ($taskStatus == 'done')
                        <div>
                            <x-wui-button 2xs orange label="Revert as todo" x-tooltip.placement.top.raw="Revert as todo"
                                wire:click="revertToTodo" />
                        </div>
                    @endif

                    @if ($userAlredyTrackingThisTask)
                        <div>
                            <x-mary-icon name="m-pause" class=" text-blue-400 hover:text-blue-600 cursor-pointer"
                                x-tooltip.placement.top.raw="Stop Tracking"
                                wire:click="stopTracking('{{ $task['uuid'] }}')" />
                        </div>
                    @else
                        <div>
                            <x-mary-icon name="m-play" class=" text-blue-400 hover:text-blue-600 cursor-pointer"
                                x-tooltip.placement.top.raw="Start Tracking"
                                wire:click="startTracking('{{ $task['uuid'] }}')" />
                        </div>
                    @endif

                @endif

                <div>
                    <x-mary-icon name="m-eye" class=" text-gray-400 hover:text-gray-600 cursor-pointer"
                        data-popover-target="popover-user-profile-{{ $task['uuid'] }}" />
                </div>

                <a href="{{ route('projects.tasks.update', $task['uuid']) }}" wire:navigate>
                    <x-mary-icon name="m-pencil-square" class="text-green-400 hover:text-green-600 cursor-pointer"
                        x-tooltip.placement.top.raw="Update" />
                </a>

                <div>
                    <x-mary-icon name="m-trash" class="text-red-400 hover:text-red-600 cursor-pointer"
                        x-tooltip.placement.top.raw="Delete" wire:click="deleteTaskDialog('{{ $task['uuid'] }}')" />
                </div>

            </div>
        </div>
        <div class="flex items-center justify-between py-1">
            <p class="text-md max-w-[50%] font-semibold truncate">Project : {{ $task['project']['title'] }}</p>
            <div class="px-1">
                <livewire:global.timer :trackedTime="$trackedTime" :timerRunning="$timerRunning" :taskId="$taskId"
                    wire:key="authUserTimer-{{ $task['uuid'] }}" />
            </div>
        </div>
        <div class="flex items-center justify-between py-1">
            <p class="text-xs font-semibold">Deadline : 2024/06/30</p>
            <div class="flex gap-2 items-center">
                @foreach ($task['users'] as $user)
                    <x-wui-avatar 2xs
                        src="{{ !empty($user['profile_photo_path']) ? asset($user['profile_photo_path']) : asset('assets/images/no-user-image.png') }}" />
                @endforeach
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

    <div data-popover id="popover-user-profile-{{ $task['uuid'] }}" role="tooltip"
        class="absolute z-50 invisible inline-block w-64 text-sm text-gray-500 transition-opacity duration-300 bg-white border border-gray-200 rounded-lg shadow-sm opacity-0 dark:text-gray-400 dark:bg-gray-800 dark:border-gray-600">
        <div class="p-3">
            <div class="mb-4">
                @foreach ($task['users'] as $user)
                    @if ($user['id'] != auth()->user()->id)
                        <div class="flex items-center justify-between mb-2" wire:key="userTimer-{{ $user['uuid'] }}">
                            <div class="flex items-center gap-2">
                                <x-wui-avatar 2xs
                                    src="{{ !empty($user['profile_photo_path']) ? asset($user['profile_photo_path']) : asset('assets/images/no-user-image.png') }}" />
                                <p class="text-xs font-semibold leading-none text-gray-900 dark:text-white">
                                    {{ $user['name'] }}
                                </p>
                            </div>
                            <div class="px-1">
                                <livewire:global.timer wire:key="userTimer-{{ $user['uuid'] }}" :trackedTime="$user['trackedTime']"
                                    :timerRunning="$user['timerRunning']" :taskId="$taskId" />
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if ($subTasksCount > 0)
                <div wire:transition class="space-y-2 my-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Sub Tasks ({{ $subTasksCount }})</h3>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4 dark:bg-gray-700">
                        <div class="bg-red-600 h-2.5 rounded-full" style="width: {{ $completedPrecent }}%"></div>
                    </div>
                </div>
            @endif

            <div class="my-2 px-1 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">Estimated Time</h3>
                <p class="font-semibold text-blue-600 dark:text-blue-500 ">{{ $task['estimate_time'] }}</p>
            </div>

            <div class="my-2 px-1 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">Deadline</h3>
                <p class="font-semibold text-blue-600 dark:text-blue-500 ">
                    {{ $task['deadline'] ? $task['deadline']->format('Y-m-d') : 'No Deadline' }}</p>
            </div>

            <div class="my-2 px-1 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">Total Tracked Time</h3>
                <p class="font-semibold text-blue-600 dark:text-blue-500 ">{{ $totalTrackedTime }}</p>
            </div>

            {{-- <ul class="flex text-sm">
                <li class="me-2">
                    <a href="#" class="hover:underline">
                        <span class="font-semibold text-gray-900 dark:text-white">799</span>
                        <span>Following</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="hover:underline">
                        <span class="font-semibold text-gray-900 dark:text-white">3,758</span>
                        <span>Followers</span>
                    </a>
                </li>
            </ul> --}}
        </div>
        <div data-popper-arrow></div>
    </div>
</div>
