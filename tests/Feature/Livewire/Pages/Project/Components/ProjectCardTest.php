<?php

use App\Models\User;
use Livewire\Livewire;
use App\Models\Project;
use App\Livewire\Pages\Project\Components\ProjectCard;

it('renders successfully', function () {
    $this->actingAs(User::factory()->withPersonalTeam()->create());
    $project = Project::factory()->create();
    Livewire::test(ProjectCard::class, ['project' => $project])
        ->assertStatus(200);
});

