<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('pages.team-owner.components.events-sidebar-icon');

    $component->assertSee('');
});
