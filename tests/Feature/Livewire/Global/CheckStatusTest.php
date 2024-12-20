<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('global.check-status');

    $component->assertSee('');
});
