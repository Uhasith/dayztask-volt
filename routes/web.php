<?php

use App\Livewire\Pages\Project\Index;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Livewire\Pages\Project\ProjectPage;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/projects', Index::class)->lazy()->name('projects.index');

});
