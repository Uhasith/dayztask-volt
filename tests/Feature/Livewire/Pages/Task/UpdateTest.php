<?php

use App\Livewire\Pages\Task\Update;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Update::class)
        ->assertStatus(200);
});
