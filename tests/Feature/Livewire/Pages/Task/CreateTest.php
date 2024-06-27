<?php

use App\Livewire\Pages\Task\Create;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $this->actingAs(User::factory()->withPersonalTeam()->create());
    $project = Project::factory()->create();
    Livewire::test(Create::class, ['uuid' => $project->uuid])
        ->assertStatus(200);
});
