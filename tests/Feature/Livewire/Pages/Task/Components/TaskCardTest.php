<?php

use App\Livewire\Pages\Task\Components\TaskCard;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(TaskCard::class)
        ->assertStatus(200);
});
