<div>
    <x-wui-card rounded="3xl">
        <div class="flex items-center justify-between">
            <p class="text-lg max-w-[80%] truncate">{{ $task['name'] }}</p>
            <div class="flex items-center gap-3">
                {{-- <x-wui-badge flat lime label="3" class="cursor-pointer"
                            x-tooltip.placement.top.raw="Comments Count">
                            <x-slot name="prepend" class="relative flex items-center w-2 h-2 mr-1">
                                <span
                                    class="absolute inline-flex w-full h-full rounded-full opacity-75 bg-lime-500 animate-ping"></span>

                                <span class="relative inline-flex w-2 h-2 rounded-full bg-lime-500"></span>
                            </x-slot>
                        </x-wui-badge>
                        <x-wui-button 2xs positive label="Mark as done" /> --}}
                @if ($userAlredyTrackingThisTask)
                    <x-mary-icon name="m-pause" class=" text-blue-400 hover:text-blue-600 cursor-pointer"
                        x-tooltip.placement.top.raw="Stop Tracking" wire:click="stopTracking('{{ $task['uuid'] }}')"  />
                @else
                    <x-mary-icon name="m-play" class=" text-blue-400 hover:text-blue-600 cursor-pointer"
                        x-tooltip.placement.top.raw="Start Tracking"
                        wire:click="startTracking('{{ $task['uuid'] }}')" />
                @endif

                <x-mary-icon name="m-pencil-square" class="text-green-400 hover:text-green-600 cursor-pointer"
                    x-tooltip.placement.top.raw="Update" />
                <x-mary-icon name="m-trash" class="text-red-400 hover:text-red-600 cursor-pointer"
                    x-tooltip.placement.top.raw="Delete" wire:click="deleteTaskDialog('{{ $task['uuid'] }}')" />
            </div>
        </div>
        <div class="flex items-center justify-between py-1">
            <p class="text-md max-w-[50%] truncate">Project : {{ $task['project']['title'] }}</p>
            <div class="px-1">
                <livewire:global.timer :trackedTime="$trackedTime" :timerRunning="$timerRunning"
                    :taskId="$taskId" />
            </div>
        </div>
        <div class="flex items-center justify-between py-1">
            <p class="text-xs">Deadline : 2024/06/30</p>
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
</div>
