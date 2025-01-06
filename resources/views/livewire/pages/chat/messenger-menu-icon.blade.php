<?php

use Livewire\Volt\Component;
use App\Services\Chat\MessengerService;
new class extends Component {
    public $count = 0;

    public function mount()
    {
        $this->count = app(MessengerService::class)->getMessengerCount();
    }

    #[On('messengerUpdated')] 
    public function messengerUpdated()
    {
        $this->count = app(MessengerService::class)->getMessengerCount();
    }
}; ?>

<div class="relative">
    <x-mary-menu-item title="Messenger" icon="o-chat-bubble-left-right" link="{{ route('messenger') }}" wire:navigate x-tooltip.placement.right.raw="Messenger"/>
    @if ($this->count > 0)
        <x-mary-badge value="{{ $this->count }}" class="badge-error badge-sm absolute -right-1 top-0 rounded-full" />
    @endif
</div>