<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('pages.checklist.components.checklist-sidebar-icon');

    $component->assertSee('');
});
