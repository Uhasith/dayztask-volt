<?php

use App\Livewire\Global\Workspace;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Workspace::class)
        ->assertStatus(200);
});
