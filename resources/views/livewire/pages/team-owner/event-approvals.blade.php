<?php

use Livewire\Volt\Component;

new class extends Component {

    function boot() : void {
        $user = auth()->user();
        if(!$user->hasTeamPermission($user->currentTeam, 'leave-approve')){
            $this->redirect(route('dashboard'));
        }
    }
}; ?>

<div class="py-4">
    <div class="mx-auto sm:px-6 lg:px-8">
        <livewire:teamowner.event-table />
    </div>
</div>