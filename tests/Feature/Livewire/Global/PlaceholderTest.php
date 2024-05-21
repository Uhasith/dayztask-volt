<?php

use App\Livewire\Global\Placeholder;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Placeholder::class)
        ->assertStatus(200);
});
