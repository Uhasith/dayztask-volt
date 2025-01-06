<?php

use Livewire\Volt\Component;
use App\Services\Team\TeamService;
use Livewire\Attributes\On;

new class extends Component {
    public $count = 0;

    public function mount()
    {
        $this->count = app(TeamService::class)->getCheckListCount();
    }

    #[On('checkListUpdated')]
    public function checkListUpdated()
    {
        $this->count = app(TeamService::class)->getCheckListCount();
    }
}; ?>

<div class="relative">
    <x-mary-menu-item title="Checklist" icon="o-clipboard-document-list" link="{{ route('checklist.index') }}" x-tooltip.placement.right.raw="Checklist" />
    @if ($this->count > 0)
        <x-mary-badge value="{{ $this->count }}" class="badge-error badge-sm absolute -right-1 top-0 rounded-full" />
    @endif
</div>
