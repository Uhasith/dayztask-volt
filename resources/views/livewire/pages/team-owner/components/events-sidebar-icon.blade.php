<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use App\Models\Event;
new class extends Component {
    public $count = 0;

    public function mount()
    {
        $this->count = $this->countEventsToApprove();
    }

    function countEventsToApprove() : int {
        return Event::whereNull('is_approved')->count();
    }

    #[On('calendar--refresh')]
    public function leaveRquestUpdated()
    {
        $this->count = $this->countEventsToApprove();
    }
}; ?>

<div class="relative">
    <x-mary-menu-item title="Leave Requests" icon="o-calendar-date-range" link="{{ route('event-approvals') }}" x-tooltip.placement.right.raw="Leave Requests" />
    @if ($this->count > 0)
        <x-mary-badge value="{{ $this->count }}" class="badge-error badge-sm absolute -right-1 top-0 rounded-full" />
    @endif
</div>
