<?php

use App\Livewire\Pages\Project\ShowAll;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ShowAll::class)
        ->assertStatus(200);
});
