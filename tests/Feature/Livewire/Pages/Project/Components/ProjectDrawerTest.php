<?php

use App\Livewire\Pages\Project\Components\ProjectDrawer;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ProjectDrawer::class)
        ->assertStatus(200);
});
