<?php

use App\Livewire\Pages\Project\Components\ProjectCreateForm;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ProjectCreateForm::class)
        ->assertStatus(200);
});
