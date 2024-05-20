<?php

use App\Models\User;
use Livewire\Volt\Volt;

it('project index can render', function () {

    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Volt::test('pages.project.index');
    $component->assertSee('');
});
