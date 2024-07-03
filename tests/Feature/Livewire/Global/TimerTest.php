<?php

use App\Livewire\Global\Timer;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Timer::class)
        ->assertStatus(200);
});
