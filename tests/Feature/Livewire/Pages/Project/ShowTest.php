<?php

use App\Livewire\Pages\Project\Show;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $project = Project::factory()->create();
    $this->actingAs(User::factory()->withPersonalTeam()->create());
    Livewire::test(Show::class, ['uuid' => $project->uuid])
        ->assertStatus(200);
});
