<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('global.working-task-timer');

    $component->assertSee('');
});
