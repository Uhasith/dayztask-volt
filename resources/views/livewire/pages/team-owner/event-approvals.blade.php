<?php

use Livewire\Volt\Component;

new class extends Component {
    function boot(): void
    {
        $user = auth()->user();
        if (!$user->hasTeamPermission($user->currentTeam, 'leave-approve')) {
            $this->redirect(route('dashboard'));
        }
    }
}; ?>

<div class="py-4">
    <div class="mx-auto sm:px-6 lg:px-8">
        @if (auth()->user()->hasTeamRole(auth()->user()->currentTeam, 'admin'))
            <livewire:tables.event-table />
        @endif
    </div>
</div>
