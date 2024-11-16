<?php

use Livewire\Volt\Component;
use App\Services\Task\TaskService;
use App\Services\Team\TeamService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public $teamMembers = [];
    public $projects = [];

    public $user_id;
    public $project_id = 'All';

    public function mount()
    {
        $this->teamMembers = app(TeamService::class)->getTeamMembers();
        $this->projects = Auth::user()
            ->currentTeam->owner->projects()
            ->where('workspace_id', Auth::user()->current_workspace_id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
        $this->user_id = (string) Auth::user()->id;
    }

    public function updatedProjectId()
    {
        $this->dispatch('projectUpdated', $this->project_id);
    }

    public function updatedUserId()
    {
        $this->dispatch('userUpdated', $this->user_id);
    }
}; ?>

<div class="w-full mx-auto p-5 lg:px-10 lg:py-5">
    <div class="flex items-center justify-end gap-6">
        <x-wui-select id="user" icon="user" label="Select an user" placeholder="Select an user" class="max-w-[20%]"
            wire:model.live="user_id" :clearable="false">
            <x-wui-select.option label="All Users" value="All" />
            @foreach ($teamMembers as $key => $member)
                <x-wui-select.user-option
                    src="{{ !empty($member['profile_photo_path']) ? asset($member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
                    label="{{ $member['name'] }}" value="{{ $member['id'] }}" wire:key="{{ 'user-option-' . $key }}" />
            @endforeach
        </x-wui-select>
        <x-wui-select id="project" icon="user" label="Select a project" placeholder="Select a project"
            class="max-w-[20%]" wire:model.live="project_id" :clearable="false">
            <x-wui-select.option label="All Projects" value="All" />
            @foreach ($projects as $key => $project)
                <x-wui-select.option label="{{ $project['title'] }}" value="{{ $project['id'] }}"
                    wire:key="{{ 'project-option-' . $key }}" />
            @endforeach
        </x-wui-select>
    </div>
    <div class="my-8">
        <livewire:tables.task-track-table :user_id="$user_id" :project_id="$project_id" />
    </div>
</div>
