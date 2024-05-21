<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('pages.project.components.project-create-drawer');

    $component->assertSee('');
});
