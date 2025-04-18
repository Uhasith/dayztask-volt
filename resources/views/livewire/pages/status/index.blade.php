<?php

use Livewire\Volt\Component;
use App\Services\Notifications\NotificationService;
use App\Services\Task\TaskService;
use App\Services\Team\TeamService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;

new class extends Component {
    public $teamMembers = [];
    public $teamMembersData = [];

    public function mount()
    {
        $this->teamMembers = app(TeamService::class)->getTeamMembersIds();
        $this->teamMembersData = app(TeamService::class)->getTeamMembersData($this->teamMembers);
    }

    #[On('start-tracking')]
    public function listenStartTracking($id)
    {
        $this->redirectRoute('status.index');
    }

    #[On('end-tracking')]
    public function listenEndTracking($id)
    {
        $this->redirectRoute('status.index');
    }
}; ?>

<div class="w-full mx-auto p-5 lg:px-10 lg:py-5" x-init="initFlowbite();">
    <div>
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold my-4 text-center">Team Status Dashboard</h1>
            <div class="flex items-stretch justify-between gap-8 mt-8">
                <div class="w-full flex-grow">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg h-full">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center">
                                        Working On
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center">
                                        Tracking Details
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center">
                                        Open Tasks
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center">
                                        Missed Deadline
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teamMembersData as $teamMember)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                                        <th scope="row"
                                            class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                                            <x-wui-avatar lg
                                                src="{{ !empty($teamMember['profile_photo_url']) ? $teamMember['profile_photo_url'] : asset('assets/images/no-user-image.png') }}" />
                                            <div class="ps-3">
                                                <div class="text-base font-semibold">{{ $teamMember['name'] }}</div>
                                                <div class="font-normal text-gray-500">{{ $teamMember['email'] }}</div>
                                            </div>
                                        </th>
                                        <td class="px-6 py-4">
                                            @if (!$teamMember['no_task_found'])
                                                @if ($teamMember['timer_running'])
                                                    <span class="text-green-500">
                                                        {{ $teamMember['tracking_task']['name'] }}</span>
                                                @else
                                                    <span class="text-orange-500">
                                                        {{ $teamMember['tracking_task']['name'] }}</span>
                                                @endif
                                            @else
                                                <span class="text-gray-500">No Task Found</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if (!$teamMember['no_task_found'])
                                                <livewire:global.timer class="" :key="'userStatusTimer-' .
                                                    $teamMember['tracking_task_id'] .
                                                    '-' .
                                                    $teamMember['id']"
                                                    :trackedTime="$teamMember['tracked_time']" :timerRunning="$teamMember['timer_running']" :taskId="$teamMember['tracking_task_id']" />
                                            @else
                                                <span class="text-gray-500">No Task Found</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            {{ $teamMember['open_task_count'] }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            {{ $teamMember['missed_deadline_count'] }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center">
                                                <template
                                                    x-if="$store.onlineUsers.users.includes({{ $teamMember['id'] }})">
                                                    <div class="flex items-center">
                                                        <div class="h-2.5 w-2.5 rounded-full bg-green-500 me-2"></div>
                                                        Online
                                                    </div>
                                                </template>
                                                <template
                                                    x-if="!$store.onlineUsers.users.includes({{ $teamMember['id'] }})">
                                                    <div class="flex items-center">
                                                        <div class="h-2.5 w-2.5 rounded-full bg-gray-400 me-2"></div>
                                                        Offline
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Example Team Member Card -->
                {{-- <div class="w-[30%] flex-grow">
                    <div class="rounded-lg border bg-white px-4 pt-8 pb-10 shadow-lg h-full">
                        <div class="relative mx-auto w-36 rounded-full">
                            <span
                                class="absolute right-0 m-3 h-3 w-3 rounded-full bg-green-500 ring-2 ring-green-300 ring-offset-2"></span>
                            <img class="mx-auto h-auto w-full rounded-full"
                                src="{{ asset('assets/images/no-user-image.png') }}" alt="" />
                        </div>
                        <h1 class="my-1 text-center text-xl font-bold leading-8 text-gray-900">Michael Simbal</h1>
                        <h3 class="font-lg text-semibold text-center leading-6 text-gray-600">Marketing Exec. at
                            Denva Corp</h3>
                        <p class="text-center text-sm leading-6 text-gray-500 hover:text-gray-600">Lorem ipsum
                            dolor sit amet consectetur, adipisicing elit. Architecto, placeat!</p>
                        <ul
                            class="mt-3 divide-y rounded bg-gray-100 py-2 px-3 text-gray-600 shadow-sm hover:text-gray-700 hover:shadow">
                            <li class="flex items-center py-3 text-sm">
                                <span>Status</span>
                                <span class="ml-auto"><span
                                        class="rounded-full bg-green-200 py-1 px-2 text-xs font-medium text-green-700">Open
                                        for side gigs</span></span>
                            </li>
                            <li class="flex items-center py-3 text-sm">
                                <span>Joined On</span>
                                <span class="ml-auto">Apr 08, 2022</span>
                            </li>
                        </ul>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
</div>
