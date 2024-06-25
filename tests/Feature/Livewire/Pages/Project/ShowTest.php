<?php

use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Project;
use App\Livewire\Pages\Project\Show;

it('renders successfully', function () {
    $project = Project::factory()->create();
    $this->actingAs(User::factory()->withPersonalTeam()->create());
    Livewire::test(Show::class, ['uuid' => $project->uuid])
        ->assertStatus(200);
});
