<?php

use Livewire\Volt\Volt;

it('project card can render', function () {
    $project = [
        'bg_color' => '#ffffff',
        'font_color' => '#000000',
        'title' => 'Test Project',
        'company_logo' => null,
    ];

    $component = Volt::test('pages.project.components.project-card', ['project' => $project]);
    $component->assertSee('');
});
