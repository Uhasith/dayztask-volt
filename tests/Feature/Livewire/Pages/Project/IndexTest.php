<?php

use App\Models\User;
use Livewire\Livewire;
use App\Livewire\Pages\Project\Index;

it('renders successfully', function () {
    $this->actingAs(User::factory()->withPersonalTeam()->create());
    Livewire::test(Index::class)
        ->assertStatus(200);
});
