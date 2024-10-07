<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('global.search');

    $component->assertSee('');
});
