<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('pages.chat.messenger');

    $component->assertSee('');
});
