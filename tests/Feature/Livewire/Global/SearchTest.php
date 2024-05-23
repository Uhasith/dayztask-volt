<?php

use App\Livewire\Global\Search;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Search::class)
        ->assertStatus(200);
});
