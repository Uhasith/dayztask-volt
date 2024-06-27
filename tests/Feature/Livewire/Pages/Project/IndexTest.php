<?php

use App\Livewire\Pages\Project\Index;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $this->actingAs(User::factory()->withPersonalTeam()->create());
    Livewire::test(Index::class)
        ->assertStatus(200);
});
