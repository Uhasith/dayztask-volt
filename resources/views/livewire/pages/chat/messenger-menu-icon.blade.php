<?php

use Livewire\Volt\Component;
use App\Services\Chat\MessengerService;
new class extends Component {
    public $count = 0;
    public $user;

    public function mount()
    {
        $this->user = auth()->user();
        $this->count = app(MessengerService::class)->getMessengerCount();
    }

    public function getListeners()
    {
        return [
            "echo-private:chat.{$this->user->id},MessageSent" => 'chatReceived',
        ];
    }

    #[On('messengerUpdated')] 
    #[On('private-participant.{type}.{id}')] 
    public function chatReceived($type, $id)
    {
        $this->count = app(MessengerService::class)->getMessengerCount();
        $this->dispatch('play-notification-sound', sound: asset('assets/sounds/notification.mp3'));
    }
}; ?>

<div class="relative indicator">
    <x-mary-menu-item title="Messenger" icon="o-chat-bubble-left-right" link="{{ route('messenger') }}" wire:navigate x-tooltip.placement.right.raw="Messenger"/>
    @if ($this->count > 0)
        <x-mary-badge value="{{ $this->count }}" class="badge-error badge-sm indicator-item" />
    @endif
</div>