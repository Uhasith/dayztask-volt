<?php

use App\Livewire\Pages\Task\Components\ProofUploadModal;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ProofUploadModal::class)
        ->assertStatus(200);
});
