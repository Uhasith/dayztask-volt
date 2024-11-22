<?php

use Livewire\Volt\Component;
use App\Services\Task\TaskService;
use App\Services\Team\TeamService;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use WireUi\Traits\WireUiActions;

new class extends Component {
    use WireUiActions;

    public $checkList = [];

    public $reason = '';
    public $rejectUuid = null;

    public function mount()
    {
        $this->checkList = app(TeamService::class)->getCheckList();
    }

    public function rules()
    {
        return [
            'reason' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'reason.required' => 'Reason is required',
        ];
    }

    public function confirmApprove($uuid)
    {
        $this->dialog()->confirm([
            'title' => 'Are you Sure?',
            'icon' => 'check-badge',
            'description' => 'You want to approve this Task?',
            'accept' => [
                'label' => 'Yes, Approve',
                'method' => 'approveTask',
                'params' => '' . $uuid . '',
            ],
        ]);
    }

    public function confirmReject($uuid)
    {
        $this->rejectUuid = $uuid;
        $this->dispatch('open-modal', id: 'reject-reason-modal');
    }

    public function closeModal()
    {
        $this->rejectUuid = null;
        $this->reason = '';
        $this->dispatch('close-modal', id: 'reject-reason-modal');
    }

    public function approveTask($uuid)
    {
        app(TaskService::class)->approveTask($uuid);
        $this->checkList = app(TeamService::class)->getCheckList();
        $this->dispatch('checkListUpdated');
        app(NotificationService::class)->sendSuccessNotification('Task approved successfully');
    }

    public function rejectTask()
    {
        $this->validate();
        app(TaskService::class)->rejectTask($this->rejectUuid, $this->reason);
        $this->checkList = app(TeamService::class)->getCheckList();
        $this->dispatch('checkListUpdated');
        $this->closeModal();
        app(NotificationService::class)->sendSuccessNotification('Task rejected successfully');
    }

    public function getTotalTrackedTime($taskId)
    {
        $time = app(TaskService::class)->calculateAllUsersTotalTrackedTime($taskId);
        return $time;
    }
}; ?>

<div class="w-full mx-auto p-5 lg:px-10 lg:py-5">
    @foreach ($checkList as $item)
        <div class="flex w-full p-4 flex-col rounded-lg bg-white shadow-sm border border-slate-200 my-6 cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="flex -space-x-4 rtl:space-x-reverse">
                    @foreach ($item->users as $user)
                        <img class="w-10 h-10 border-2 border-white rounded-full dark:border-gray-800"
                            src="{{ $user->profile_photo_url ?? asset('assets/images/no-user-image.png') }}"
                            alt="{{ $user->name }}" x-tooltip.placement.top.raw="{{ $user->name }}">
                    @endforeach
                </div>
                <div class="flex items-center gap-2">
                    {{-- <x-wui-icon name="clipboard-document" class="w-7 h-7 text-blue-500 cursor-pointer" solid
                        x-tooltip.placement.top.raw="View Proof" /> --}}
                    <x-wui-icon name="shield-exclamation" class="w-7 h-7 text-orange-500 cursor-pointer" solid
                        x-tooltip.placement.top.raw="Reject Task" wire:click="confirmReject('{{ $item->uuid }}')" />
                    <x-wui-icon name="check-badge" class="w-7 h-7 text-green-500 cursor-pointer" solid
                        x-tooltip.placement.top.raw="Approve Task" wire:click="confirmApprove('{{ $item->uuid }}')" />
                </div>
            </div>
            <div class="flex items-center gap-4 text-slate-800">
                <div class="flex w-full flex-col">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('projects.tasks.update', $item->uuid) }}" wire:navigate>
                            <h5 class="text-xl font-semibold text-slate-800 hover:text-blue-500 hover:underline">
                                {{ $item->name }}
                            </h5>
                        </a>
                        <div class="flex items-center gap-4">
                            <p class="text-xs uppercase font-bold text-slate-500 mt-0.5">
                                Updated At : {{ $item->updated_at->diffForHumans() }}
                            </p>
                            <p class="text-xs uppercase font-bold text-slate-500 mt-0.5">
                                Created At : {{ $item->created_at->format('Y-m-d') }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('projects.show', $item->project->uuid) }}" wire:navigate>
                        <div class="flex items-center justify-between">
                            <p
                                class="text-xs uppercase font-bold text-slate-500 mt-0.5 hover:text-blue-500 hover:underline">
                                Project : {{ $item->project->title }}
                            </p>
                            <div class="flex items-center gap-4">
                                @if ($item->priority)
                                    <p class="text-xs uppercase font-bold mt-0.5"
                                        x-tooltip.placement.top.raw="Priority">
                                        @if ($item->priority === 'high')
                                            <x-wui-badge flat red label="High" />
                                        @elseif ($item->priority === 'medium')
                                            <x-wui-badge flat sky label="Medium" />
                                        @else
                                            <x-wui-badge flat purple label="Low" />
                                        @endif
                                    </p>
                                @endif
                                @if ($item->deadline)
                                    <p class="text-xs uppercase font-bold text-slate-500 mt-0.5">
                                        Deadline :
                                        {{ $item->deadline ? $item->deadline->format('Y-m-d') : 'No Deadline' }}
                                    </p>
                                @endif
                                <p class="text-xs uppercase font-bold text-slate-500 mt-0.5">
                                    Total Tracked Time : {{ $this->getTotalTrackedTime($item->id) }}
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    @endforeach
    <x-filament::modal id="reject-reason-modal" icon="heroicon-s-building-storefront" icon-color="primary"
        width="2xl">
        <div class="w-full">
            <form wire:submit="rejectTask">
                <div>
                    <div>
                        <x-wui-textarea label="Reason for Rejection" wire:model="reason" placeholder="Type Here" />
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-4">
                        <x-wui-button class="w-full" type="button" secondary label="Cancel" wire:click="closeModal" />
                        <x-wui-button class="w-full" type="submit" spinner="submit" primary label="Confirm Reject" />
                    </div>
                </div>
            </form>
        </div>
    </x-filament::modal>
</div>
