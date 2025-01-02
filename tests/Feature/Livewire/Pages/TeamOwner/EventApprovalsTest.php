<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('pages.team-owner.event-approvals');

    $component->assertSee('');
});
