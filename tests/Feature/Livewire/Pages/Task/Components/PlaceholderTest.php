<?php

use App\Livewire\Pages\Task\Components\Placeholder;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Placeholder::class)
        ->assertStatus(200);
});
