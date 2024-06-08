<?php

use App\Livewire\Pages\Project\Show;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Show::class)
        ->assertStatus(200);
});
