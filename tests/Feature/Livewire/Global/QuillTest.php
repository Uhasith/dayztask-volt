<?php

use App\Livewire\Global\Quill;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Quill::class)
        ->assertStatus(200);
});
